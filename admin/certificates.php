<?php
/** Administration — certificats : consultation, révocation, rétablissement, régénération. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require APP_ROOT . '/includes/learning.php';
require_permission('certificates.manage');

if (is_post()) {
    verify_csrf();
    $c = q_one('SELECT * FROM certificates WHERE id = ?', [input_int('id')]);
    if ($c) {
        switch (input('action')) {
            case 'revoke':
                $reason = input('reason') ?: 'Révocation administrative';
                q("UPDATE certificates SET status = 'revoked', revoked_reason = ? WHERE id = ?", [mb_substr($reason, 0, 255), $c['id']]);
                audit('certificate.revoke', 'certificate', (int) $c['id'], $c['certificate_no'] . ' · ' . $reason);
                flash('success', 'Certificat révoqué : la vérification publique le signale désormais comme non valide.');
                break;
            case 'restore':
                q("UPDATE certificates SET status = 'valid', revoked_reason = NULL WHERE id = ?", [$c['id']]);
                audit('certificate.restore', 'certificate', (int) $c['id'], $c['certificate_no']);
                flash('success', 'Certificat rétabli.');
                break;
            case 'regenerate':
                certificate_file($c['certificate_no'], true);
                audit('certificate.regenerate', 'certificate', (int) $c['id'], $c['certificate_no']);
                flash('success', 'PDF régénéré (paramètres de signature actuels).');
                break;
        }
    }
    back();
}
$q = input('q');
$pg = paginate((int) q_val('SELECT COUNT(*) FROM certificates WHERE (? = "" OR certificate_no LIKE ? OR holder_name LIKE ?)', [$q, "%$q%", "%$q%"]), 30);
$rows = q_all("SELECT ct.*, c.code FROM certificates ct JOIN courses c ON c.id = ct.course_id WHERE (? = '' OR ct.certificate_no LIKE ? OR ct.holder_name LIKE ?) ORDER BY ct.issued_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", [$q, "%$q%", "%$q%"]);
$pageTitle = 'Certificats';
$appArea = 'admin';
$activeApp = 'certificates';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="toolbar reveal" method="get"><input class="input grow" name="q" value="<?= e($q) ?>" placeholder="Numéro ou titulaire"><button class="btn btn--sm" type="submit">Rechercher</button></form>
<section class="box reveal"><div class="box__head"><h2>Certificats délivrés</h2><span class="mono"><?= $pg['total'] ?></span></div><div class="table-wrap"><table class="table">
  <thead><tr><th>Numéro</th><th>Titulaire</th><th>Formation</th><th>Délivré le</th><th class="num">Score</th><th>Statut</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="dim">Aucun certificat.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr><td class="mono"><?= e($r['certificate_no']) ?></td><td><?= e($r['holder_name']) ?></td><td><?= e($r['code']) ?></td><td><?= e(fmt_date($r['issued_at'], false)) ?></td><td class="num"><?= e(number_format((float) $r['score'], 0)) ?> %</td>
      <td><?= $r['status'] === 'valid' ? '<span class="status-pill status-pill--on">● Valide</span>' : '<span class="status-pill status-pill--bad" title="' . e($r['revoked_reason']) . '">● Révoqué</span>' ?></td>
      <td><div class="actions">
        <a class="btn btn--sm" href="<?= e(url('learner/certificate.php', ['no' => $r['certificate_no']])) ?>" target="_blank" rel="noopener" data-no-transition>PDF</a>
        <a class="btn btn--sm" href="<?= e(url('verify-certificate.php', ['code' => $r['certificate_no']])) ?>" target="_blank" rel="noopener">Vérifier</a>
        <?= action_btn('regenerate', ['id' => $r['id']], 'Régénérer') ?>
        <?php if ($r['status'] === 'valid'): ?>
        <form method="post" data-confirm="Révoquer ce certificat ? Il apparaîtra comme non valide lors de la vérification publique."><?= csrf_field() ?><input type="hidden" name="action" value="revoke"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input class="input" style="min-height:38px;width:150px" name="reason" placeholder="Motif"><button class="btn btn--sm btn--danger" type="submit">Révoquer</button></form>
        <?php else: ?><?= action_btn('restore', ['id' => $r['id']], 'Rétablir') ?><?php endif; ?>
      </div></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?= pagination_links($pg, ['q' => $q]) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
