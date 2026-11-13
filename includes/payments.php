<?php
/**
 * AFRICA'SPY — paiement des formations (FCFA / XOF).
 *
 * Canaux :
 *  - cinetpay : passerelle agréée (Mobile Money Orange/MTN/Moov/Wave, cartes bancaires).
 *               Le statut n'est JAMAIS déduit du retour navigateur : il est toujours
 *               revérifié auprès de l'API CinetPay (/payment/check) avant tout accès.
 *  - manual   : virement / dépôt ; l'accès est ouvert après validation par un administrateur.
 *  - sandbox  : simulation locale pour le développement et les tests automatisés ;
 *               désactivée d'office lorsque env = production.
 */
declare(strict_types=1);

require_once APP_ROOT . '/includes/catalog.php';

function fmt_xof(int $amount): string
{
    return number_format($amount, 0, ',', "\u{202F}") . "\u{00A0}FCFA";
}

/** Prix effectivement facturé (prix promotionnel s'il existe et est inférieur). */
function course_price(array $c): int
{
    $list = (int) ($c['price_xof'] ?? 0);
    $promo = isset($c['promo_price_xof']) && $c['promo_price_xof'] !== null ? (int) $c['promo_price_xof'] : null;
    return ($promo !== null && $promo < $list) ? $promo : $list;
}

function course_discount_pct(array $c): int
{
    $list = (int) ($c['price_xof'] ?? 0);
    $price = course_price($c);
    return ($list > 0 && $price < $list) ? (int) round(100 - $price * 100 / $list) : 0;
}

function course_is_free(array $c): bool
{
    return course_price($c) <= 0;
}

/** Fin de promotion configurée (null si aucune échéance : aucune date n'est inventée). */
function promo_deadline(): ?int
{
    $v = setting('promo_ends_at');
    $t = $v !== '' ? strtotime($v . ' UTC') : false;
    return ($t && $t > time()) ? $t : null;
}

function payments_sandbox_enabled(): bool
{
    return (bool) config('payments.sandbox', false) && config('env') !== 'production';
}

function payments_cinetpay_enabled(): bool
{
    return (bool) config('payments.cinetpay_enabled', false) && config('payments.cinetpay_apikey') && config('payments.cinetpay_site_id');
}

function payments_manual_enabled(): bool
{
    return setting('payment_manual_enabled', '1') === '1';
}

/** Moyens de paiement disponibles, dans l'ordre d'affichage. */
function payment_methods(): array
{
    $m = [];
    if (payments_cinetpay_enabled()) {
        $m['cinetpay'] = ['label' => 'Mobile Money ou carte bancaire', 'hint' => 'Orange Money, MTN MoMo, Moov Money, Wave, Visa, Mastercard — paiement sécurisé via CinetPay. Accès immédiat après confirmation.'];
    }
    if (payments_manual_enabled()) {
        $m['manual'] = ['label' => 'Virement bancaire ou dépôt', 'hint' => 'Vous recevez une référence à indiquer lors du versement. L\'accès est ouvert après vérification par l\'administration.'];
    }
    if (payments_sandbox_enabled()) {
        $m['sandbox'] = ['label' => 'Paiement de test (environnement de développement)', 'hint' => 'Simulation locale sans débit réel. Indisponible en production.'];
    }
    return $m;
}

/** L'apprenant a-t-il réglé cette formation ? */
function has_paid(int $userId, int $courseId): bool
{
    return (bool) q_val("SELECT 1 FROM payments WHERE user_id = ? AND course_id = ? AND status = 'paid' LIMIT 1", [$userId, $courseId]);
}

function new_payment_reference(): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $r = '';
        for ($i = 0; $i < 8; $i++) {
            $r .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $ref = 'PAY-' . gmdate('ymd') . '-' . $r;
    } while (q_val('SELECT 1 FROM payments WHERE reference = ?', [$ref]));
    return $ref;
}

/** Crée (ou réutilise) une commande en attente pour ce canal. */
function create_payment(array $user, array $course, string $provider, ?string $phone = null): array
{
    $amount = course_price($course);
    $existing = q_one("SELECT * FROM payments WHERE user_id = ? AND course_id = ? AND provider = ? AND status = 'pending' AND amount_xof = ? AND created_at > UTC_TIMESTAMP() - INTERVAL 2 DAY ORDER BY id DESC LIMIT 1",
        [$user['id'], $course['id'], $provider, $amount]);
    if ($existing) {
        return $existing;
    }
    $ref = new_payment_reference();
    q('INSERT INTO payments (reference, user_id, course_id, amount_xof, list_price_xof, provider, payer_phone, created_at) VALUES (?,?,?,?,?,?,?,UTC_TIMESTAMP())',
        [$ref, $user['id'], $course['id'], $amount, (int) $course['price_xof'], $provider, $phone]);
    $id = (int) db()->lastInsertId();
    audit('payment.create', 'payment', $id, "$ref · {$course['code']} · $amount XOF · $provider");
    return q_one('SELECT * FROM payments WHERE id = ?', [$id]);
}

/**
 * Confirme un paiement et ouvre l'accès (idempotent, transactionnel).
 * Le montant confirmé par la passerelle doit correspondre au montant commandé.
 */
function mark_payment_paid(int $paymentId, array $info = [], ?int $validatedBy = null): bool
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $p = q_one('SELECT * FROM payments WHERE id = ? FOR UPDATE', [$paymentId]);
        if (!$p) {
            $pdo->rollBack();
            return false;
        }
        if ($p['status'] === 'paid') {
            $pdo->commit();
            return true;
        }
        if (isset($info['amount']) && (int) $info['amount'] !== (int) $p['amount_xof']) {
            q("UPDATE payments SET status = 'failed', admin_note = ? WHERE id = ?", ['Montant reçu (' . (int) $info['amount'] . ') différent du montant attendu', $p['id']]);
            $pdo->commit();
            audit('payment.amount_mismatch', 'payment', (int) $p['id'], $p['reference']);
            return false;
        }
        q("UPDATE payments SET status = 'paid', paid_at = UTC_TIMESTAMP(), provider_txn = COALESCE(?, provider_txn), payment_method = COALESCE(?, payment_method),
                                provider_data = COALESCE(?, provider_data), validated_by = ? WHERE id = ?",
            [$info['txn'] ?? null, $info['method'] ?? null, isset($info['raw']) ? mb_substr(json_encode($info['raw'], JSON_UNESCAPED_UNICODE), 0, 60000) : null, $validatedBy, $p['id']]);
        q('INSERT IGNORE INTO course_enrollments (user_id, course_id, last_activity) VALUES (?, ?, UTC_TIMESTAMP())', [$p['user_id'], $p['course_id']]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    recompute_progress((int) $p['user_id'], (int) $p['course_id']);
    audit('payment.paid', 'payment', (int) $p['id'], $p['reference'] . ' · ' . $p['amount_xof'] . ' XOF' . ($validatedBy ? ' · validé manuellement' : ''));
    $u = q_one('SELECT email, first_name FROM users WHERE id = ?', [$p['user_id']]);
    $c = q_one('SELECT title FROM courses WHERE id = ?', [$p['course_id']]);
    if ($u) {
        send_mail($u['email'], "AFRICA'SPY — Paiement confirmé ({$p['reference']})",
            "Bonjour {$u['first_name']},\n\nVotre paiement de " . number_format((int) $p['amount_xof'], 0, ',', ' ') . " FCFA pour la formation « {$c['title']} » est confirmé.\nRéférence : {$p['reference']}\n\nVotre accès est ouvert : " . absolute_url('learner/courses.php') . "\n\nAFRICA'SPY — Intelligence Academy");
    }
    return true;
}

function mark_payment_status(int $paymentId, string $status, ?string $note = null, array $raw = []): void
{
    q("UPDATE payments SET status = ?, admin_note = COALESCE(?, admin_note), provider_data = COALESCE(?, provider_data) WHERE id = ? AND status = 'pending'",
        [$status, $note, $raw ? mb_substr(json_encode($raw, JSON_UNESCAPED_UNICODE), 0, 60000) : null, $paymentId]);
    audit('payment.' . $status, 'payment', $paymentId, $note);
}

/* ------------------------------------------------------------------
 *  CinetPay
 * ----------------------------------------------------------------*/

function cinetpay_post(string $endpoint, array $body): array
{
    $ch = curl_init(rtrim((string) config('payments.cinetpay_api'), '/') . $endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE), CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($res === false) {
        throw new RuntimeException('Passerelle de paiement injoignable : ' . $err);
    }
    $j = json_decode((string) $res, true);
    if (!is_array($j)) {
        throw new RuntimeException('Réponse invalide de la passerelle de paiement.');
    }
    return $j;
}

/** Initialise la transaction et renvoie l'URL du guichet de paiement CinetPay. */
function cinetpay_init(array $payment, array $user, array $course): string
{
    $amount = (int) $payment['amount_xof'];
    if ($amount % 5 !== 0) {
        throw new RuntimeException('Le montant doit être un multiple de 5 FCFA.');
    }
    $j = cinetpay_post('/payment', [
        'apikey' => config('payments.cinetpay_apikey'), 'site_id' => config('payments.cinetpay_site_id'),
        'transaction_id' => $payment['reference'], 'amount' => $amount, 'currency' => 'XOF',
        'description' => mb_substr('Formation ' . $course['code'], 0, 100),
        'notify_url' => absolute_url('payment-notify.php'), 'return_url' => absolute_url('payment-return.php', ['ref' => $payment['reference']]),
        'channels' => 'ALL', 'lang' => 'FR',
        'customer_name' => $user['last_name'], 'customer_surname' => $user['first_name'], 'customer_email' => $user['email'],
        'customer_phone_number' => $payment['payer_phone'] ?: '', 'customer_country' => 'CI',
        'metadata' => (string) $payment['id'],
    ]);
    if (($j['code'] ?? '') !== '201' || empty($j['data']['payment_url'])) {
        throw new RuntimeException('Initialisation refusée par la passerelle : ' . ($j['message'] ?? 'erreur inconnue') . (isset($j['description']) ? ' — ' . $j['description'] : ''));
    }
    q('UPDATE payments SET provider_txn = ? WHERE id = ?', [$j['data']['payment_token'] ?? null, $payment['id']]);
    return (string) $j['data']['payment_url'];
}

/**
 * Interroge CinetPay sur l'état réel d'une transaction et met la commande à jour.
 * @return string statut local après synchronisation
 */
function cinetpay_sync(array $payment): string
{
    if ($payment['status'] !== 'pending') {
        return $payment['status'];
    }
    $j = cinetpay_post('/payment/check', ['apikey' => config('payments.cinetpay_apikey'), 'site_id' => config('payments.cinetpay_site_id'), 'transaction_id' => $payment['reference']]);
    $data = $j['data'] ?? [];
    $status = strtoupper((string) ($data['status'] ?? ''));
    if (($j['code'] ?? '') === '00' && $status === 'ACCEPTED') {
        if (strtoupper((string) ($data['currency'] ?? 'XOF')) !== 'XOF') {
            mark_payment_status((int) $payment['id'], 'failed', 'Devise inattendue', $data);
            return 'failed';
        }
        return mark_payment_paid((int) $payment['id'], ['amount' => (int) ($data['amount'] ?? 0), 'txn' => $data['operator_id'] ?? null, 'method' => $data['payment_method'] ?? null, 'raw' => $data]) ? 'paid' : 'failed';
    }
    if (in_array($status, ['REFUSED', 'CANCELED', 'CANCELLED'], true)) {
        mark_payment_status((int) $payment['id'], $status === 'REFUSED' ? 'failed' : 'cancelled', 'Statut passerelle : ' . $status, $data);
        return $status === 'REFUSED' ? 'failed' : 'cancelled';
    }
    return 'pending';
}

/* ------------------------------------------------------------------
 *  Reçu PDF
 * ----------------------------------------------------------------*/

function payment_receipt_pdf(array $p): string
{
    require_once APP_ROOT . '/includes/pdf.php';
    require_once APP_ROOT . '/includes/logo.php';
    $logo = svg_data_uri(logo_svg('gold', 'x', true));
    $fonts = pdf_fonts_css();
    $row = static fn ($k, $v) => '<tr><td class="k">' . e($k) . '</td><td>' . $v . '</td></tr>';
    $saving = (int) $p['list_price_xof'] - (int) $p['amount_xof'];
    $html = '<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>' . $fonts . '
      @page{margin:18mm} body{font-family:Inter;font-size:10pt;color:#1b2430}
      .head{background:#05070B;color:#E8F2FA;padding:8mm 10mm;margin:-18mm -18mm 10mm} .head img{width:22mm;float:left;margin-right:6mm}
      .brand{font-family:Orbitron;font-weight:900;font-size:16pt;letter-spacing:3pt;padding-top:3mm} .brand small{display:block;font-family:ShareTechMono;font-size:7.5pt;color:#AEBBC9;font-weight:400;letter-spacing:3pt;margin-top:2mm}
      h1{font-family:Orbitron;font-size:15pt;margin:0 0 2mm} .ref{font-family:ShareTechMono;color:#B08A3E;letter-spacing:1.5pt}
      table{width:100%;border-collapse:collapse;margin-top:7mm} td{padding:2.6mm 3mm;border-bottom:1px solid #e3e7ec} td.k{width:55mm;font-family:ShareTechMono;font-size:8pt;color:#6b7686;letter-spacing:1pt;text-transform:uppercase}
      .total td{font-size:13pt;font-weight:700;border-bottom:2px solid #05070B} .paid{display:inline-block;padding:1.5mm 4mm;border:1.5pt solid #0a9a5c;color:#0a9a5c;font-family:Orbitron;font-weight:700;letter-spacing:2pt}
      .foot{margin-top:14mm;font-size:8pt;color:#6b7686}
    </style></head><body><div class="head"><img src="' . $logo . '" alt=""><div class="brand">AFRICA\'SPY<small>INTELLIGENCE ACADEMY</small></div><div style="clear:both"></div></div>
    <h1>Reçu de paiement</h1><p class="ref">' . e($p['reference']) . '</p><p class="paid">PAYÉ</p>
    <table>' . $row('Date de paiement', e(fmt_datetime($p['paid_at']))) . $row('Apprenant', e($p['first_name'] . ' ' . $p['last_name']) . '<br><small>' . e($p['email']) . '</small>')
        . $row('Formation', e($p['course_title']) . ' <small>(' . e($p['course_code']) . ')</small>') . $row('Prix de référence', e(fmt_xof((int) $p['list_price_xof'])))
        . ($saving > 0 ? $row('Remise promotionnelle', '− ' . e(fmt_xof($saving))) : '') . $row('Moyen de paiement', e(payment_provider_label($p['provider']) . ($p['payment_method'] ? ' · ' . $p['payment_method'] : '')))
        . ($p['provider_txn'] ? $row('Transaction', '<span style="font-family:ShareTechMono">' . e($p['provider_txn']) . '</span>') : '')
        . '<tr class="total"><td class="k">Montant réglé</td><td>' . e(fmt_xof((int) $p['amount_xof'])) . '</td></tr></table>
    <p class="foot">' . e(setting('institution_name', "AFRICA'SPY — Intelligence Academy")) . ' · ' . e(setting('contact_address')) . ' · ' . e(setting('contact_email')) . '<br>Reçu généré électroniquement. Montants exprimés en francs CFA (XOF).</p></body></html>';
    $pdf = pdf_engine();
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->render();
    return $pdf->output();
}

function payment_provider_label(string $p): string
{
    return ['cinetpay' => 'Mobile Money / carte (CinetPay)', 'manual' => 'Virement / dépôt', 'sandbox' => 'Paiement de test'][$p] ?? $p;
}

function payment_status_pill(string $s): string
{
    $map = ['paid' => ['on', 'Payé'], 'pending' => ['warn', 'En attente'], 'failed' => ['bad', 'Échoué'], 'cancelled' => ['off', 'Annulé'], 'refunded' => ['off', 'Remboursé']];
    [$cls, $label] = $map[$s] ?? ['off', $s];
    return '<span class="status-pill status-pill--' . $cls . '">● ' . e($label) . '</span>';
}

/** Bloc de prix réutilisable (fiche formation, cartes, paiement). */
function price_block(array $c, string $extra = ''): string
{
    $price = course_price($c);
    $list = (int) $c['price_xof'];
    $pct = course_discount_pct($c);
    if ($price <= 0) {
        return '<div class="price ' . e($extra) . '"><span class="price__now">Gratuit</span></div>';
    }
    return '<div class="price ' . e($extra) . '">'
        . ($pct ? '<span class="price__badge">−' . $pct . ' %</span><span class="price__old"><s>' . e(fmt_xof($list)) . '</s></span>' : '')
        . '<span class="price__now" data-price="' . $price . '">' . e(fmt_xof($price)) . '</span>'
        . ($pct ? '<span class="price__label">' . e(setting('promo_label', 'Prix promotionnel')) . '</span>' : '') . '</div>';
}
