<?php
/** Administration — journal des activités administratives et de sécurité. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('audit.view');

$f = input('action_filter');
$where = '1=1';
$p = [];
if ($f !== '') { $where .= ' AND a.action LIKE ?'; $p[] = str_replace(['%', '_'], ['\\%', '\\_'], $f) . '%'; }
$pg = paginate((int) q_val("SELECT COUNT(*) FROM audit_logs a WHERE $where", $p), 40);
$rows = q_all("SELECT a.*, u.first_name, u.last_name, u.email FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id WHERE $where ORDER BY a.id DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
$families = array_column(q_all("SELECT DISTINCT SUBSTRING_INDEX(action, '.', 1) f FROM audit_logs ORDER BY f"), 'f');
$pageTitle = 'Journal d\'audit';
$appArea = 'admin';
$activeApp = 'audit';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="toolbar reveal" method="get"><select class="select" name="action_filter" data-autosubmit><option value="">Toutes les actions</option><?php foreach ($families as $fa): ?><option value="<?= e($fa) ?>" <?= $f === $fa ? 'selected' : '' ?>><?= e($fa) ?></option><?php endforeach; ?></select></form>
<section class="box reveal"><div class="box__head"><h2>Événements</h2><span class="mono"><?= $pg['total'] ?> entrée(s) · conservation illimitée</span></div><div class="table-wrap"><table class="table">
  <thead><tr><th>Date (UTC)</th><th>Utilisateur</th><th>Action</th><th>Objet</th><th>Détails</th><th>IP</th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr><td class="mono" style="font-size:.78rem;white-space:nowrap"><?= e($r['created_at']) ?></td><td><?= $r['email'] ? e($r['first_name'] . ' ' . $r['last_name']) . '<br><small class="dim">' . e($r['email']) . '</small>' : '<span class="dim">—</span>' ?></td>
      <td><span class="tag tag--cyan"><?= e($r['action']) ?></span></td><td class="mono" style="font-size:.78rem"><?= e(($r['entity_type'] ?? '') . ($r['entity_id'] ? ' #' . $r['entity_id'] : '')) ?></td><td><small><?= e((string) $r['details']) ?></small></td><td class="mono" style="font-size:.78rem"><?= e((string) $r['ip_address']) ?></td></tr>
  <?php endforeach; ?></tbody></table></div></section>
<?= pagination_links($pg, ['action_filter' => $f]) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
