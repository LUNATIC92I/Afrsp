<?php
/**
 * AFRICA'SPY — factures et avoirs PDF.
 *
 * - Numérotation continue et chronologique par année (FAC-2026-000001, AV-2026-000001),
 *   attribuée sous verrou transactionnel : aucun trou ni doublon.
 * - Les informations du vendeur et de l'acheteur sont figées (instantané JSON) à l'émission :
 *   une facture émise n'est jamais modifiée ; une annulation passe par un avoir.
 * - Montants en FCFA (XOF), prix TTC ; taux de taxe paramétrable (0 par défaut, avec mention).
 */
declare(strict_types=1);

require_once APP_ROOT . '/includes/payments.php';

function invoice_next_number(string $type): string
{
    $year = (int) gmdate('Y');
    q('INSERT IGNORE INTO invoice_sequences (doc_type, year, last_no) VALUES (?, ?, 0)', [$type, $year]);
    $n = (int) q_val('SELECT last_no FROM invoice_sequences WHERE doc_type = ? AND year = ? FOR UPDATE', [$type, $year]) + 1;
    q('UPDATE invoice_sequences SET last_no = ? WHERE doc_type = ? AND year = ?', [$n, $type, $year]);
    return ($type === 'invoice' ? 'FAC' : 'AV') . '-' . $year . '-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
}

function invoice_seller_snapshot(): array
{
    return [
        'name' => setting('seller_legal_name', setting('institution_name', "AFRICA'SPY — Intelligence Academy")),
        'address' => setting('seller_address', setting('contact_address')),
        'tax_id' => setting('seller_tax_id'), 'registration' => setting('seller_registration'),
        'email' => setting('seller_email', setting('contact_email')),
    ];
}

/** Émet la facture d'un paiement encaissé (idempotent). Renvoie la ligne de facture. */
function issue_invoice(int $paymentId): ?array
{
    $existing = q_one("SELECT * FROM invoices WHERE payment_id = ? AND doc_type = 'invoice'", [$paymentId]);
    if ($existing) return $existing;
    $p = q_one("SELECT p.*, c.code, c.title, u.first_name, u.last_name, u.email, u.organization, u.country
                  FROM payments p JOIN courses c ON c.id = p.course_id JOIN users u ON u.id = p.user_id WHERE p.id = ? AND p.status IN ('paid','refunded')", [$paymentId]);
    if (!$p) return null;
    $rate = max(0.0, min(100.0, (float) str_replace(',', '.', setting('tax_rate', '0'))));
    $ttc = (int) $p['amount_xof'];
    $ht = (int) round($ttc / (1 + $rate / 100));
    $pdo = db();
    $own = !$pdo->inTransaction();
    if ($own) $pdo->beginTransaction();
    try {
        $no = invoice_next_number('invoice');
        q('INSERT INTO invoices (doc_type, invoice_no, payment_id, user_id, issued_at, seller, buyer, label, list_price_xof, discount_xof, amount_ht_xof, tax_rate, tax_label, tax_xof, amount_ttc_xof)
           VALUES (\'invoice\', ?, ?, ?, COALESCE(?, UTC_TIMESTAMP()), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $no, $p['id'], $p['user_id'], $p['paid_at'],
            json_encode(invoice_seller_snapshot(), JSON_UNESCAPED_UNICODE),
            json_encode(['name' => trim($p['first_name'] . ' ' . $p['last_name']), 'email' => $p['email'], 'organization' => $p['organization'], 'country' => $p['country']], JSON_UNESCAPED_UNICODE),
            'Formation en ligne ' . $p['code'] . ' — ' . $p['title'],
            (int) $p['list_price_xof'], max(0, (int) $p['list_price_xof'] - $ttc), $ht, $rate, setting('tax_label', 'TVA'), $ttc - $ht, $ttc,
        ]);
        $id = (int) $pdo->lastInsertId();
        if ($own) $pdo->commit();
    } catch (Throwable $e) {
        if ($own && $pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    audit('invoice.issue', 'invoice', $id, $no . ' · ' . $p['reference']);
    return q_one('SELECT * FROM invoices WHERE id = ?', [$id]);
}

/** Émet l'avoir annulant la facture d'un paiement remboursé (idempotent). */
function issue_credit_note(int $paymentId, string $reason = 'Remboursement'): ?array
{
    $existing = q_one("SELECT * FROM invoices WHERE payment_id = ? AND doc_type = 'credit_note'", [$paymentId]);
    if ($existing) return $existing;
    $inv = q_one("SELECT * FROM invoices WHERE payment_id = ? AND doc_type = 'invoice'", [$paymentId]) ?? issue_invoice($paymentId);
    if (!$inv) return null;
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $no = invoice_next_number('credit_note');
        q('INSERT INTO invoices (doc_type, invoice_no, payment_id, related_id, user_id, issued_at, seller, buyer, label, list_price_xof, discount_xof, amount_ht_xof, tax_rate, tax_label, tax_xof, amount_ttc_xof)
           VALUES (\'credit_note\', ?, ?, ?, ?, UTC_TIMESTAMP(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
            $no, $paymentId, $inv['id'], $inv['user_id'], json_encode(invoice_seller_snapshot(), JSON_UNESCAPED_UNICODE), $inv['buyer'],
            mb_substr('Avoir sur facture ' . $inv['invoice_no'] . ' — ' . $reason, 0, 255),
            -(int) $inv['list_price_xof'], -(int) $inv['discount_xof'], -(int) $inv['amount_ht_xof'], $inv['tax_rate'], $inv['tax_label'], -(int) $inv['tax_xof'], -(int) $inv['amount_ttc_xof'],
        ]);
        $id = (int) $pdo->lastInsertId();
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    audit('invoice.credit_note', 'invoice', $id, $no . ' · annule ' . $inv['invoice_no']);
    return q_one('SELECT * FROM invoices WHERE id = ?', [$id]);
}

/** Rendu HTML → PDF d'une facture ou d'un avoir. */
function invoice_pdf(array $inv): string
{
    require_once APP_ROOT . '/includes/pdf.php';
    require_once APP_ROOT . '/includes/logo.php';
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $money = static fn (int $v) => ($v < 0 ? '− ' : '') . number_format(abs($v), 0, ',', ' ') . ' FCFA';
    $seller = json_decode($inv['seller'], true) ?: [];
    $buyer = json_decode($inv['buyer'], true) ?: [];
    $p = q_one('SELECT reference, provider, payment_method, provider_txn, paid_at FROM payments WHERE id = ?', [$inv['payment_id']]) ?: [];
    $related = $inv['related_id'] ? q_val('SELECT invoice_no FROM invoices WHERE id = ?', [$inv['related_id']]) : null;
    $isCredit = $inv['doc_type'] === 'credit_note';
    $logo = svg_data_uri(logo_svg('gold', 'x', true));
    $rate = (float) $inv['tax_rate'];
    $taxLine = $rate > 0
        ? '<tr><td>' . $e($inv['tax_label']) . ' (' . $e(rtrim(rtrim(number_format($rate, 2, ',', ''), '0'), ',')) . ' %)</td><td class="r">' . $money((int) $inv['tax_xof']) . '</td></tr>'
        : '<tr><td colspan="2" class="mention">' . $e(setting('tax_exempt_mention', 'TVA non applicable')) . '</td></tr>';
    $sellerLines = implode('<br>', array_filter([$e($seller['address'] ?? ''), ($seller['tax_id'] ?? '') ? 'N° d\'identification fiscale : ' . $e($seller['tax_id']) : '', ($seller['registration'] ?? '') ? 'RCCM : ' . $e($seller['registration']) : '', $e($seller['email'] ?? '')]));
    $buyerLines = implode('<br>', array_filter(['<strong>' . $e($buyer['name'] ?? '') . '</strong>', $e($buyer['organization'] ?? ''), $e($buyer['country'] ?? ''), $e($buyer['email'] ?? '')]));
    $fonts = pdf_fonts_css();
    $title = $isCredit ? 'AVOIR' : 'FACTURE';
    $status = $isCredit ? '<span class="stamp stamp--credit">AVOIR</span>' : '<span class="stamp">ACQUITTÉE</span>';
    $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>' . $fonts . '
      @page{margin:16mm 16mm 20mm} body{font-family:Inter;font-size:9.6pt;color:#1b2430}
      .head{display:table;width:100%} .head>div{display:table-cell;vertical-align:top}
      .brand img{width:20mm;float:left;margin-right:4mm} .brand b{font-family:Orbitron;font-weight:900;font-size:15pt;letter-spacing:2.5pt;color:#05070B} .brand small{display:block;font-family:ShareTechMono;font-size:7pt;letter-spacing:2.5pt;color:#8A6A2A;margin-top:1mm}
      .doc{text-align:right} .doc h1{font-family:Orbitron;font-size:20pt;letter-spacing:3pt;margin:0;color:#05070B} .doc .no{font-family:ShareTechMono;font-size:11pt;color:#8A6A2A;letter-spacing:1pt}
      .doc table{margin-left:auto;margin-top:2mm;font-size:8.8pt} .doc td{padding:.4mm 0 .4mm 4mm}
      .parties{display:table;width:100%;margin:9mm 0 6mm} .parties>div{display:table-cell;width:50%;vertical-align:top;padding-right:6mm}
      .k{font-family:ShareTechMono;font-size:7.5pt;letter-spacing:1.5pt;color:#6b7686;text-transform:uppercase;margin-bottom:1.5mm;border-bottom:1px solid #d9dee5;padding-bottom:1mm}
      table.lines{width:100%;border-collapse:collapse} table.lines th{background:#05070B;color:#E8F2FA;font-family:Rajdhani;font-weight:600;font-size:9pt;text-align:left;padding:2.4mm 3mm;letter-spacing:.6pt}
      table.lines td{padding:3mm;border-bottom:1px solid #e3e7ec;vertical-align:top} .r{text-align:right;white-space:nowrap}
      table.tot{width:46%;margin-left:54%;margin-top:4mm;border-collapse:collapse} table.tot td{padding:1.8mm 3mm;border-bottom:1px solid #eef1f5} table.tot tr.grand td{font-size:12pt;font-weight:700;border-top:2px solid #05070B;border-bottom:0;color:#05070B}
      .mention{font-size:8pt;color:#6b7686;font-style:italic}
      .stamp{display:inline-block;padding:1.5mm 4mm;border:1.5pt solid #0a9a5c;color:#0a9a5c;font-family:Orbitron;font-weight:700;letter-spacing:2pt;font-size:9pt} .stamp--credit{border-color:#c0392b;color:#c0392b}
      .pay{margin-top:8mm;padding:3.5mm 4mm;background:#F4F8FB;border-left:3px solid #00C8FF;font-size:8.8pt}
      .foot{position:fixed;bottom:-12mm;left:0;right:0;font-size:7.4pt;color:#6b7686;border-top:1px solid #e3e7ec;padding-top:2mm}
    </style></head><body>
    <div class="head"><div class="brand"><img src="' . $logo . '" alt=""><b>AFRICA\'SPY</b><small>INTELLIGENCE ACADEMY</small></div>
      <div class="doc"><h1>' . $title . '</h1><div class="no">N° ' . $e($inv['invoice_no']) . '</div>
        <table><tr><td>Date d\'émission</td><td><strong>' . $e(fmt_date($inv['issued_at'], false)) . '</strong></td></tr>'
        . ($related ? '<tr><td>Facture d\'origine</td><td><strong>' . $e($related) . '</strong></td></tr>' : '')
        . '<tr><td>Référence paiement</td><td>' . $e($p['reference'] ?? '') . '</td></tr></table></div></div>
    <div class="parties"><div><div class="k">Émetteur</div><strong>' . $e($seller['name'] ?? '') . '</strong><br>' . $sellerLines . '</div>
      <div><div class="k">Client</div>' . $buyerLines . '</div></div>
    <table class="lines"><thead><tr><th>Désignation</th><th class="r">Qté</th><th class="r">Prix de référence</th><th class="r">Remise</th><th class="r">Montant TTC</th></tr></thead><tbody>
      <tr><td>' . $e($inv['label']) . '<br><span class="mention">Accès personnel en ligne : leçons, supports, évaluations, examen final et certificat.</span></td><td class="r">1</td><td class="r">' . $money((int) $inv['list_price_xof']) . '</td><td class="r">' . ((int) $inv['discount_xof'] ? $money(-(int) $inv['discount_xof']) : '—') . '</td><td class="r">' . $money((int) $inv['amount_ttc_xof']) . '</td></tr>
    </tbody></table>
    <table class="tot"><tr><td>Total hors taxes</td><td class="r">' . $money((int) $inv['amount_ht_xof']) . '</td></tr>' . $taxLine . '
      <tr class="grand"><td>' . ($isCredit ? 'Total de l\'avoir' : 'Total TTC') . '</td><td class="r">' . $money((int) $inv['amount_ttc_xof']) . '</td></tr></table>
    <div class="pay">' . $status . ' &nbsp; '
        . ($isCredit ? 'Avoir annulant la facture ' . $e((string) $related) . '. Le remboursement est effectué via le moyen de paiement d\'origine.'
            : 'Réglée le ' . $e(fmt_datetime($p['paid_at'] ?? $inv['issued_at'])) . ' par ' . $e(payment_provider_label((string) ($p['provider'] ?? ''))) . (($p['payment_method'] ?? '') ? ' (' . $e($p['payment_method']) . ')' : '') . (($p['provider_txn'] ?? '') ? ' — transaction ' . $e($p['provider_txn']) : '') . '.')
        . '</div>
    <div class="foot">' . $e(setting('invoice_footer', 'Facture acquittée.')) . ' Montants exprimés en francs CFA (XOF). ' . $e($seller['name'] ?? '') . ($seller['tax_id'] ?? '' ? ' · NIF ' . $e($seller['tax_id']) : '') . '</div>
    </body></html>';
    $pdf = pdf_engine();
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    return $pdf->output();
}

/** Chemin du PDF (généré une fois puis conservé tel quel). */
function invoice_file(array $inv): string
{
    $name = $inv['file_name'] ?: (preg_replace('/[^A-Z0-9\-]/', '', $inv['invoice_no']) . '-' . bin2hex(random_bytes(5)) . '.pdf');
    $path = APP_ROOT . '/storage/invoices/' . $name;
    if (!is_file($path)) {
        file_put_contents($path, invoice_pdf($inv), LOCK_EX);
        @chmod($path, 0640);
        if ($inv['file_name'] !== $name) q('UPDATE invoices SET file_name = ? WHERE id = ?', [$name, $inv['id']]);
    }
    return $path;
}

/** E-mail de confirmation de paiement avec la facture PDF en pièce jointe. */
function mail_payment_confirmed(int $paymentId): bool
{
    $p = q_one('SELECT p.*, c.title, c.code, u.first_name, u.email FROM payments p JOIN courses c ON c.id = p.course_id JOIN users u ON u.id = p.user_id WHERE p.id = ?', [$paymentId]);
    if (!$p) return false;
    $inv = issue_invoice($paymentId);
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $learn = absolute_url('learner/learning.php', ['course' => $p['course_id']]);
    $amount = number_format((int) $p['amount_xof'], 0, ',', ' ') . ' FCFA';
    $html = '<p>Bonjour ' . $e($p['first_name']) . ',</p><p>Votre paiement est confirmé : la formation est désormais ouverte dans votre espace.</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #E3E7EC;border-radius:6px;font:14px/1.7 Arial,Helvetica,sans-serif">'
        . '<tr><td style="padding:10px 16px;color:#6B7686">Formation</td><td style="padding:10px 16px;text-align:right"><strong>' . $e($p['code']) . ' — ' . $e($p['title']) . '</strong></td></tr>'
        . '<tr><td style="padding:10px 16px;color:#6B7686;border-top:1px solid #EEF1F5">Montant réglé</td><td style="padding:10px 16px;text-align:right;border-top:1px solid #EEF1F5"><strong>' . $amount . '</strong></td></tr>'
        . '<tr><td style="padding:10px 16px;color:#6B7686;border-top:1px solid #EEF1F5">Référence de paiement</td><td style="padding:10px 16px;text-align:right;border-top:1px solid #EEF1F5;font-family:Courier New,monospace">' . $e($p['reference']) . '</td></tr>'
        . ($inv ? '<tr><td style="padding:10px 16px;color:#6B7686;border-top:1px solid #EEF1F5">Facture</td><td style="padding:10px 16px;text-align:right;border-top:1px solid #EEF1F5;font-family:Courier New,monospace">' . $e($inv['invoice_no']) . '</td></tr>' : '')
        . '</table><p>Votre facture est jointe à ce message au format PDF. Elle reste téléchargeable à tout moment depuis « Mes paiements ».</p>';
    $text = "Bonjour {$p['first_name']},\n\nVotre paiement de $amount pour la formation {$p['code']} — {$p['title']} est confirmé.\nRéférence : {$p['reference']}" . ($inv ? "\nFacture : {$inv['invoice_no']} (PDF en pièce jointe)" : '') . "\n\nCommencer la formation : $learn";
    $att = $inv ? [[file_get_contents(invoice_file($inv)), 'Facture-' . $inv['invoice_no'] . '.pdf', 'application/pdf']] : [];
    return send_mail($p['email'], "AFRICA'SPY — Paiement confirmé" . ($inv ? ' · Facture ' . $inv['invoice_no'] : ''), $text, [
        'html' => mail_layout('Votre formation est ouverte. Facture jointe.', 'Paiement confirmé', $html, ['Commencer la formation', $learn]),
        'attachments' => $att, 'tag' => 'payment',
    ]);
}

/** E-mail d'avoir après remboursement. */
function mail_credit_note(int $paymentId): bool
{
    $cn = q_one("SELECT * FROM invoices WHERE payment_id = ? AND doc_type = 'credit_note'", [$paymentId]);
    $p = q_one('SELECT p.reference, u.first_name, u.email FROM payments p JOIN users u ON u.id = p.user_id WHERE p.id = ?', [$paymentId]);
    if (!$cn || !$p) return false;
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $html = '<p>Bonjour ' . $e($p['first_name']) . ',</p><p>Le remboursement de votre paiement <span style="font-family:Courier New,monospace">' . $e($p['reference']) . '</span> a été enregistré. Vous trouverez ci-joint l\'avoir <strong>' . $e($cn['invoice_no']) . '</strong>.</p>';
    return send_mail($p['email'], "AFRICA'SPY — Avoir " . $cn['invoice_no'], "Bonjour {$p['first_name']},\n\nLe remboursement de votre paiement {$p['reference']} a été enregistré. Avoir {$cn['invoice_no']} en pièce jointe.", [
        'html' => mail_layout('Avoir suite à remboursement.', 'Remboursement enregistré', $html),
        'attachments' => [[file_get_contents(invoice_file($cn)), 'Avoir-' . $cn['invoice_no'] . '.pdf', 'application/pdf']], 'tag' => 'credit-note',
    ]);
}
