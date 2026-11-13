<?php
/** Administration — paiements : suivi, validation des virements, annulation, remboursement. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require APP_ROOT . '/includes/payments.php';
$me = require_permission('payments.manage');

if (is_post()) {
    verify_csrf();
    $p = q_one('SELECT * FROM payments WHERE id = ?', [input_int('id')]);
    $note = mb_substr(input('note'), 0, 255) ?: null;
    if ($p) {
        switch (input('action')) {
            case 'validate':
                if ($p['status'] !== 'pending') { flash('error', 'Seule une commande en attente peut être validée.'); break; }
                if ($p['provider'] === 'cinetpay') { flash('error', 'Les paiements CinetPay sont confirmés uniquement par la passerelle (bouton « Revérifier »).'); break; }
                q('UPDATE payments SET admin_note = ? WHERE id = ?', [$note ?? 'Réception vérifiée', $p['id']]);
                mark_payment_paid((int) $p['id'], ['method' => 'Virement / dépôt'], (int) $me['id']);
                flash('success', 'Paiement ' . $p['reference'] . ' validé : la formation est ouverte à l\'apprenant.');
                break;
            case 'recheck':
                try { $s = cinetpay_sync($p); flash('info', 'Statut passerelle : ' . $s); } catch (Throwable $e) { flash('error', $e->getMessage()); }
                break;
            case 'cancel':
                mark_payment_status((int) $p['id'], 'cancelled', $note ?? 'Annulé par l\'administration');
                flash('success', 'Commande annulée.');
                break;
            case 'refund':
                if ($p['status'] !== 'paid') { flash('error', 'Seul un paiement encaissé peut être remboursé.'); break; }
                q("UPDATE payments SET status = 'refunded', admin_note = ? WHERE id = ?", [$note ?? 'Remboursement', $p['id']]);
                $cert = q_val("SELECT 1 FROM certificates WHERE user_id = ? AND course_id = ? AND status = 'valid'", [$p['user_id'], $p['course_id']]);
                if (!$cert && !has_paid((int) $p['user_id'], (int) $p['course_id'])) {
                    q('DELETE FROM course_enrollments WHERE user_id = ? AND course_id = ?', [$p['user_id'], $p['course_id']]);
                }
                audit('payment.refunded', 'payment', (int) $p['id'], $p['reference'] . ' · ' . ($note ?? ''));
                flash('success', 'Paiement marqué remboursé' . ($cert ? ' (certificat déjà délivré : accès conservé, révoquez le certificat si nécessaire).' : ' : l\'accès à la formation a été retiré.'));
                break;
        }
    }
    back(array_filter(['status' => input('f_status') ?: null]));
}
$status = input('status');
$q = input('q');
$where = '1=1';
$par = [];
if ($status !== '') { $where .= ' AND p.status = ?'; $par[] = $status; }
if ($q !== '') { $where .= ' AND (p.reference LIKE ? OR u.email LIKE ? OR u.last_name LIKE ?)'; array_push($par, "%$q%", "%$q%", "%$q%"); }
$pg = paginate((int) q_val("SELECT COUNT(*) FROM payments p JOIN users u ON u.id = p.user_id WHERE $where", $par), 30);
$rows = q_all("SELECT p.*, u.first_name, u.last_name, u.email, c.code FROM payments p JOIN users u ON u.id = p.user_id JOIN courses c ON c.id = p.course_id WHERE $where ORDER BY p.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
$k = q_one("SELECT COALESCE(SUM(CASE WHEN status='paid' THEN amount_xof END),0) revenue, SUM(status='paid') paid, SUM(status='pending') pending,
                   COALESCE(SUM(CASE WHEN status='paid' AND paid_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY THEN amount_xof END),0) rev30,
                   COALESCE(SUM(CASE WHEN status='paid' THEN list_price_xof - amount_xof END),0) discount FROM payments");
$daily = array_column(q_all("SELECT DATE(paid_at) d, SUM(amount_xof) n FROM payments WHERE status='paid' AND paid_at >= UTC_DATE() - INTERVAL 29 DAY GROUP BY DATE(paid_at)"), 'n', 'd');
$chart = [];
for ($i = 29; $i >= 0; $i--) { $d = gmdate('Y-m-d', strtotime("-$i day")); $chart[] = ['label' => gmdate('d/m', strtotime($d)), 'value' => round(((int) ($daily[$d] ?? 0)) / 1000)]; }
$pageTitle = 'Paiements';
$appArea = 'admin';
$activeApp = 'payments';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="stats" data-stagger="60">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Chiffre d'affaires encaissé</div><div class="stat__value" style="font-size:1.45rem"><?= e(fmt_xof((int) $k['revenue'])) ?></div><div class="stat__hint"><?= (int) $k['paid'] ?> paiement(s) · 30 j : <?= e(fmt_xof((int) $k['rev30'])) ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>En attente</div><div class="stat__value" data-count="<?= (int) $k['pending'] ?>">0</div><div class="stat__hint">Virements à vérifier / paiements non confirmés</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--cyan)"></i>Remises accordées</div><div class="stat__value" style="font-size:1.45rem"><?= e(fmt_xof((int) $k['discount'])) ?></div><div class="stat__hint">Écart prix de référence / prix promotionnel</div></div>
</section>
<section class="box reveal"><div class="box__head"><h2>Encaissements · 30 jours</h2><span class="mono">milliers de FCFA</span></div>
  <div class="box__body"><div class="chart" data-chart="bars"><canvas role="img" aria-label="Encaissements quotidiens"></canvas><script type="application/json"><?= json_encode($chart) ?></script></div></div></section>
<form class="toolbar reveal" method="get">
  <input class="input grow" name="q" value="<?= e($q) ?>" placeholder="Référence, e-mail ou nom">
  <select class="select" name="status"><option value="">Tous les statuts</option><?php foreach (['pending' => 'En attente', 'paid' => 'Payés', 'failed' => 'Échoués', 'cancelled' => 'Annulés', 'refunded' => 'Remboursés'] as $sk => $sl): ?><option value="<?= $sk ?>" <?= $status === $sk ? 'selected' : '' ?>><?= $sl ?></option><?php endforeach; ?></select>
  <button class="btn btn--sm" type="submit">Filtrer</button>
</form>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Référence</th><th>Apprenant</th><th>Formation</th><th>Moyen</th><th class="num">Montant</th><th>Date</th><th>Statut</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="8" class="dim">Aucun paiement.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td class="mono" style="font-size:.78rem"><?= e($r['reference']) ?><?= $r['provider_txn'] ? '<br><small class="dim">' . e($r['provider_txn']) . '</small>' : '' ?></td>
      <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?><br><small class="dim"><?= e($r['email']) ?></small></td>
      <td><?= e($r['code']) ?></td>
      <td><small><?= e(payment_provider_label($r['provider'])) ?><?= $r['payer_phone'] ? '<br>' . e($r['payer_phone']) : '' ?></small></td>
      <td class="num"><?= e(fmt_xof((int) $r['amount_xof'])) ?></td>
      <td><small><?= e(fmt_datetime($r['paid_at'] ?? $r['created_at'])) ?></small></td>
      <td><?= payment_status_pill($r['status']) ?><?= $r['admin_note'] ? '<br><small class="dim">' . e($r['admin_note']) . '</small>' : '' ?></td>
      <td><div class="actions">
        <?php if ($r['status'] === 'pending' && $r['provider'] !== 'cinetpay'): ?><?= action_btn('validate', ['id' => $r['id'], 'f_status' => $status], 'Valider la réception', 'btn--sm btn--primary', 'Confirmer avoir reçu ' . fmt_xof((int) $r['amount_xof']) . ' avec la référence ' . $r['reference'] . ' ? L\'accès sera ouvert immédiatement.') ?><?php endif; ?>
        <?php if ($r['status'] === 'pending' && $r['provider'] === 'cinetpay'): ?><?= action_btn('recheck', ['id' => $r['id'], 'f_status' => $status], 'Revérifier') ?><?php endif; ?>
        <?php if ($r['status'] === 'pending'): ?><?= action_btn('cancel', ['id' => $r['id'], 'f_status' => $status], 'Annuler', 'btn--sm', 'Annuler cette commande ?') ?><?php endif; ?>
        <?php if ($r['status'] === 'paid'): ?><a class="btn btn--sm btn--gold" href="<?= e(url('learner/receipt.php', ['ref' => $r['reference']])) ?>" data-no-transition>Reçu</a><?= action_btn('refund', ['id' => $r['id'], 'f_status' => $status], 'Rembourser', 'btn--sm btn--danger', 'Marquer ce paiement comme remboursé ? L\'accès sera retiré si aucun certificat n\'a été délivré. (Le remboursement lui-même s\'effectue auprès de l\'opérateur.)') ?><?php endif; ?>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?= pagination_links($pg, array_filter(['status' => $status, 'q' => $q])) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
