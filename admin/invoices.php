<?php
/** Administration — registre des factures et avoirs, export comptable CSV. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require APP_ROOT . '/includes/payments.php';
require_permission('payments.manage');

$type = in_array(input('type'), ['invoice', 'credit_note'], true) ? input('type') : '';
$q = input('q');
$from = preg_match('/^\d{4}-\d{2}-\d{2}$/', input('from')) ? input('from') : '';
$to = preg_match('/^\d{4}-\d{2}-\d{2}$/', input('to')) ? input('to') : '';
$where = '1=1';
$par = [];
if ($type !== '') { $where .= ' AND i.doc_type = ?'; $par[] = $type; }
if ($q !== '') { $where .= ' AND (i.invoice_no LIKE ? OR p.reference LIKE ? OR u.email LIKE ? OR u.last_name LIKE ?)'; array_push($par, "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($from !== '') { $where .= ' AND i.issued_at >= ?'; $par[] = $from . ' 00:00:00'; }
if ($to !== '') { $where .= ' AND i.issued_at < ? + INTERVAL 1 DAY'; $par[] = $to . ' 00:00:00'; }
$base = "FROM invoices i JOIN payments p ON p.id = i.payment_id JOIN users u ON u.id = i.user_id LEFT JOIN invoices r ON r.id = i.related_id WHERE $where";

if (input('export') === 'csv') {
    rate_limit_or_fail('invoice_export:user', (string) current_user()['id'], 30, 3600);
    audit('invoice.export', 'invoice', null, trim("$type $from $to $q"));
    $rows = q_all("SELECT i.*, p.reference, p.provider, r.invoice_no AS related_no $base ORDER BY i.issued_at, i.id", $par);
    // Neutralise les formules (injection CSV) dans les champs texte
    $cell = static fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="factures-' . gmdate('Ymd-His') . '.csv"');
    header('Cache-Control: no-store');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Numéro', 'Type', 'Date', 'Référence paiement', 'Moyen', 'Client', 'E-mail', 'Libellé', 'Prix catalogue', 'Remise', 'Montant HT', 'Taux taxe %', 'Taxe', 'Montant TTC', 'Pièce liée'], ';', '"', '');
    foreach ($rows as $r) {
        $b = json_decode((string) $r['buyer'], true) ?: [];
        fputcsv($out, array_map($cell, [
            $r['invoice_no'], $r['doc_type'] === 'invoice' ? 'Facture' : 'Avoir', substr((string) $r['issued_at'], 0, 10), $r['reference'], payment_provider_label($r['provider']),
            (string) ($b['name'] ?? ''), (string) ($b['email'] ?? ''), $r['label'], (int) $r['list_price_xof'], (int) $r['discount_xof'],
            (int) $r['amount_ht_xof'], (string) $r['tax_rate'], (int) $r['tax_xof'], (int) $r['amount_ttc_xof'], (string) ($r['related_no'] ?? ''),
        ]), ';', '"', '');
    }
    fclose($out);
    exit;
}

$k = q_one("SELECT COALESCE(SUM(amount_ttc_xof),0) net, COALESCE(SUM(CASE WHEN doc_type='invoice' THEN amount_ttc_xof END),0) billed,
                   COALESCE(SUM(CASE WHEN doc_type='credit_note' THEN amount_ttc_xof END),0) credited, COALESCE(SUM(tax_xof),0) tax,
                   SUM(doc_type='invoice') n_inv, SUM(doc_type='credit_note') n_cn FROM invoices WHERE YEAR(issued_at) = YEAR(UTC_DATE())");
$pg = paginate((int) q_val("SELECT COUNT(*) $base", $par), 30);
$rows = q_all("SELECT i.*, p.reference, u.first_name, u.last_name, u.email, r.invoice_no AS related_no $base ORDER BY i.issued_at DESC, i.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
$filters = array_filter(['type' => $type, 'q' => $q, 'from' => $from, 'to' => $to]);
$pageTitle = 'Factures';
$appArea = 'admin';
$activeApp = 'invoices';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="stats" data-stagger="60">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Facturé <?= gmdate('Y') ?> (TTC)</div><div class="stat__value" style="font-size:1.45rem"><?= e(fmt_xof((int) $k['billed'])) ?></div><div class="stat__hint"><?= (int) $k['n_inv'] ?> facture(s)</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--red)"></i>Avoirs <?= gmdate('Y') ?></div><div class="stat__value" style="font-size:1.45rem"><?= e(fmt_xof(abs((int) $k['credited']))) ?></div><div class="stat__hint"><?= (int) $k['n_cn'] ?> avoir(s)</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>Net facturé</div><div class="stat__value" style="font-size:1.45rem"><?= e(fmt_xof((int) $k['net'])) ?></div><div class="stat__hint">dont taxe : <?= e(fmt_xof((int) $k['tax'])) ?></div></div>
</section>
<form class="toolbar reveal" method="get">
  <input class="input grow" name="q" value="<?= e($q) ?>" placeholder="Numéro, référence, e-mail ou nom">
  <select class="select" name="type"><option value="">Factures et avoirs</option><option value="invoice" <?= $type === 'invoice' ? 'selected' : '' ?>>Factures</option><option value="credit_note" <?= $type === 'credit_note' ? 'selected' : '' ?>>Avoirs</option></select>
  <input class="input" type="date" name="from" value="<?= e($from) ?>" aria-label="Du">
  <input class="input" type="date" name="to" value="<?= e($to) ?>" aria-label="Au">
  <button class="btn btn--sm" type="submit">Filtrer</button>
  <a class="btn btn--sm btn--gold" href="<?= e(url('admin/invoices.php', $filters + ['export' => 'csv'])) ?>" data-no-transition>Export CSV</a>
</form>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Numéro</th><th>Date</th><th>Client</th><th>Libellé</th><th class="num">HT</th><th class="num">Taxe</th><th class="num">TTC</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="8" class="dim">Aucune pièce comptable. Les factures sont émises automatiquement à la confirmation de chaque paiement.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="mono" style="font-size:.8rem;white-space:nowrap"><?= e($r['invoice_no']) ?><br><small class="dim"><?= $r['doc_type'] === 'invoice' ? 'Facture' : 'Avoir · ' . e((string) $r['related_no']) ?></small></td>
      <td style="white-space:nowrap"><small><?= e(fmt_datetime($r['issued_at'])) ?></small></td>
      <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?><br><small class="dim"><?= e($r['email']) ?></small></td>
      <td><small><?= e($r['label']) ?><br><span class="dim mono"><?= e($r['reference']) ?></span></small></td>
      <td class="num" style="white-space:nowrap"><?= e(fmt_xof((int) $r['amount_ht_xof'])) ?></td>
      <td class="num" style="white-space:nowrap"><?= e(fmt_xof((int) $r['tax_xof'])) ?></td>
      <td class="num" style="white-space:nowrap"><?= e(fmt_xof((int) $r['amount_ttc_xof'])) ?></td>
      <td><a class="btn btn--sm" href="<?= e(url('learner/invoice.php', ['no' => $r['invoice_no']])) ?>" data-no-transition>PDF</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?= pagination_links($pg, $filters) ?>
<p class="disclaimer">Numérotation continue et chronologique par année. Une facture émise n'est jamais modifiée : un remboursement donne lieu à un avoir. Conservez ces pièces pendant la durée légale applicable (souvent 10 ans).</p>
<?php require APP_ROOT . '/includes/app-footer.php';
