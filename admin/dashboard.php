<?php
/** Administration — tableau de bord général (statistiques réelles). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';

require_permission('admin.access');
$canStats = can('stats.view');
$m = platform_metrics();
$k = [
    'users'     => (int) q_val('SELECT COUNT(*) FROM users'),
    'new30'     => (int) q_val("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'learner' AND u.created_at >= UTC_TIMESTAMP() - INTERVAL 30 DAY"),
    'attempts'  => (int) q_val('SELECT COUNT(*) FROM exam_attempts WHERE submitted_at IS NOT NULL'),
    'passed'    => (int) q_val('SELECT COUNT(*) FROM exam_attempts WHERE passed = 1'),
    'active7'   => (int) q_val('SELECT COUNT(DISTINCT user_id) FROM lesson_progress WHERE completed_at >= UTC_TIMESTAMP() - INTERVAL 7 DAY'),
    'msgs'      => (int) q_val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
];
$rate = $k['attempts'] ? round($k['passed'] * 100 / $k['attempts']) : null;
// Série 30 jours : inscriptions aux formations et nouveaux comptes
$enr = array_column(q_all('SELECT DATE(enrolled_at) d, COUNT(*) n FROM course_enrollments WHERE enrolled_at >= UTC_DATE() - INTERVAL 29 DAY GROUP BY DATE(enrolled_at)'), 'n', 'd');
$reg = array_column(q_all('SELECT DATE(created_at) d, COUNT(*) n FROM users WHERE created_at >= UTC_DATE() - INTERVAL 29 DAY GROUP BY DATE(created_at)'), 'n', 'd');
$labels = $s1 = $s2 = [];
for ($i = 29; $i >= 0; $i--) {
    $d = gmdate('Y-m-d', strtotime("-$i day"));
    $labels[] = gmdate('d/m', strtotime($d));
    $s1[] = (int) ($enr[$d] ?? 0);
    $s2[] = (int) ($reg[$d] ?? 0);
}
$perCourse = q_all('SELECT c.code, c.title, COUNT(DISTINCT ce.user_id) enr,
                           SUM(ce.status = \'completed\') done,
                           (SELECT COUNT(*) FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id WHERE e.course_id = c.id AND ea.submitted_at IS NOT NULL) att,
                           (SELECT COUNT(*) FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id WHERE e.course_id = c.id AND ea.passed = 1) ok,
                           (SELECT ROUND(AVG(ea.score)) FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id WHERE e.course_id = c.id AND ea.submitted_at IS NOT NULL) avg
                      FROM courses c LEFT JOIN course_enrollments ce ON ce.course_id = c.id GROUP BY c.id ORDER BY enr DESC, c.sort_order');
$passBars = array_map(static fn ($r) => ['label' => substr($r['code'], 4), 'value' => (int) $r['ok']], $perCourse);
$logs = can('audit.view') ? q_all('SELECT a.*, u.first_name, u.last_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.id DESC LIMIT 12') : [];

$pageTitle = 'Tableau de bord';
$appArea = 'admin';
$activeApp = 'dashboard';
require APP_ROOT . '/includes/app-header.php';
?>
<section class="welcome reveal">
  <p class="welcome__code">Console d'administration · <span data-utc-clock>UTC</span></p>
  <h1>Centre de pilotage AFRICA'SPY</h1>
  <p>Vue consolidée de l'activité pédagogique. Toutes les valeurs sont calculées en temps réel depuis la base de données.</p>
  <div class="welcome__next">
    <?php if (can('courses.manage')): ?><a class="btn btn--primary" href="<?= e(url('admin/courses.php', ['action' => 'new'])) ?>">Nouvelle formation</a><?php endif; ?>
    <?php if (can('announcements.manage')): ?><a class="btn btn--ghost" href="<?= e(url('admin/announcements.php')) ?>">Publier une annonce</a><?php endif; ?>
    <?php if ($k['msgs'] && can('messages.view')): ?><a class="btn btn--gold" href="<?= e(url('admin/messages.php')) ?>"><?= $k['msgs'] ?> message(s) non lu(s)</a><?php endif; ?>
  </div>
</section>
<section class="stats" data-stagger="60">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--cyan)"></i>Apprenants</div><div class="stat__value" data-count="<?= $m['learners'] ?>">0</div><div class="stat__hint">+<?= $k['new30'] ?> sur 30 jours · <?= $k['users'] ?> comptes au total</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--turq)"></i>Inscriptions</div><div class="stat__value" data-count="<?= $m['enrollments'] ?>">0</div><div class="stat__hint"><?= $k['active7'] ?> apprenant(s) actif(s) sur 7 jours</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>Taux de réussite</div><div class="stat__value"><?= $rate !== null ? '<span data-count="' . $rate . '">0</span><small>%</small>' : '—' ?></div><div class="stat__hint"><?= $k['passed'] ?> réussite(s) / <?= $k['attempts'] ?> examen(s)</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Certificats valides</div><div class="stat__value" data-count="<?= $m['certificates'] ?>">0</div><div class="stat__hint"><?= $m['courses'] ?> formations · <?= $m['questions'] ?> questions</div></div>
</section>
<?php if ($canStats): ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Statistiques d'inscription · 30 jours</h2></div>
    <div class="box__body">
      <div class="chart" data-chart="lines"><canvas role="img" aria-label="Courbes des inscriptions et nouveaux comptes"></canvas><script type="application/json"><?= json_encode(['labels' => $labels, 'series' => [['values' => $s1, 'color' => '#00C8FF'], ['values' => $s2, 'color' => '#D5B36A']]]) ?></script></div>
      <div class="chart__legend"><span><i style="background:#00C8FF"></i>Inscriptions aux formations</span><span><i style="background:#D5B36A"></i>Nouveaux comptes</span></div>
    </div>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Réussites par formation</h2></div>
    <div class="box__body"><div class="chart" data-chart="bars"><canvas role="img" aria-label="Nombre d'examens réussis par formation"></canvas><script type="application/json"><?= json_encode($passBars) ?></script></div></div>
  </section>
</div>
<section class="box reveal">
  <div class="box__head"><h2>Statistiques de réussite par formation</h2></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Formation</th><th class="num">Inscrits</th><th class="num">Certifiés</th><th class="num">Examens</th><th class="num">Réussites</th><th class="num">Score moyen</th></tr></thead>
    <tbody><?php foreach ($perCourse as $r): ?>
      <tr><td><span class="mono cyan" style="font-size:.72rem"><?= e($r['code']) ?></span> <?= e($r['title']) ?></td><td class="num"><?= (int) $r['enr'] ?></td><td class="num"><?= (int) $r['done'] ?></td><td class="num"><?= (int) $r['att'] ?></td><td class="num"><?= (int) $r['ok'] ?><?= $r['att'] ? ' (' . round($r['ok'] * 100 / $r['att']) . ' %)' : '' ?></td><td class="num"><?= $r['avg'] !== null ? (int) $r['avg'] . ' %' : '—' ?></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
</section>
<?php endif; ?>
<?php if ($logs): ?>
<section class="box reveal">
  <div class="box__head"><h2>Activité récente</h2><a class="link-arrow" href="<?= e(url('admin/audit.php')) ?>">Journal complet</a></div>
  <ul class="feed"><?php foreach ($logs as $l): ?>
    <li><span><strong><?= e($l['action']) ?></strong> — <?= e(trim(($l['first_name'] ?? '') . ' ' . ($l['last_name'] ?? '')) ?: 'Système / visiteur') ?><?= $l['details'] ? ' · ' . e(str_limit($l['details'], 90)) : '' ?></span><time><?= e(time_ago($l['created_at'])) ?></time></li>
  <?php endforeach; ?></ul>
</section>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
