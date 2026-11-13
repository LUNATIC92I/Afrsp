<?php
/** Administration : résultats d'examens et réouverture de tentatives. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('results.view');

if (is_post()) {
    verify_csrf();
    if (!can('exams.manage')) render_error_page(403, 'Accès refusé', 'Action réservée aux gestionnaires d\'examens.');
    if (input('action') === 'grant') {
        // Réouverture : supprime la plus ancienne tentative échouée pour libérer une tentative
        $a = q_one('SELECT id, user_id, exam_id FROM exam_attempts WHERE id = ? AND (passed = 0 OR submitted_at IS NULL)', [input_int('attempt_id')]);
        if ($a) {
            q('DELETE FROM exam_attempts WHERE id = ?', [$a['id']]);
            audit('exam.grant_attempt', 'exam_attempt', (int) $a['id'], 'user=' . $a['user_id'] . ' exam=' . $a['exam_id']);
            flash('success', 'Tentative retirée : l\'apprenant dispose d\'une tentative supplémentaire.');
        }
    }
    back(array_filter(['course' => input_int('course') ?: null]));
}
$course = input_int('course');
$status = input('status');
$where = 'ea.submitted_at IS NOT NULL';
$p = [];
if ($course) { $where .= ' AND e.course_id = ?'; $p[] = $course; }
if ($status === 'pass') $where .= ' AND ea.passed = 1';
if ($status === 'fail') $where .= ' AND ea.passed = 0';
$pg = paginate((int) q_val("SELECT COUNT(*) FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id WHERE $where", $p), 30);
$rows = q_all("SELECT ea.*, u.first_name, u.last_name, u.email, c.code, e.pass_mark, TIMESTAMPDIFF(SECOND, ea.started_at, ea.submitted_at) dur
                 FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id JOIN users u ON u.id = ea.user_id
                WHERE $where ORDER BY ea.submitted_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
$agg = q_one("SELECT COUNT(*) n, SUM(ea.passed) ok, ROUND(AVG(ea.score)) avg FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id WHERE $where", $p);
$pageTitle = 'Résultats';
$appArea = 'admin';
$activeApp = 'results';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="toolbar reveal" method="get">
  <select class="select" name="course"><?php foreach (admin_courses_options(true) as $k => $v): ?><option value="<?= e($k) ?>" <?= (string) $course === (string) $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
  <select class="select" name="status"><option value="">Tous résultats</option><option value="pass" <?= $status === 'pass' ? 'selected' : '' ?>>Réussis</option><option value="fail" <?= $status === 'fail' ? 'selected' : '' ?>>Échoués</option></select>
  <button class="btn btn--sm" type="submit">Filtrer</button>
</form>
<section class="stats">
  <div class="stat"><div class="stat__label">Examens soumis</div><div class="stat__value"><?= (int) $agg['n'] ?></div></div>
  <div class="stat"><div class="stat__label">Réussites</div><div class="stat__value"><?= (int) $agg['ok'] ?><small><?= $agg['n'] ? round($agg['ok'] * 100 / $agg['n']) . ' %' : '' ?></small></div></div>
  <div class="stat"><div class="stat__label">Score moyen</div><div class="stat__value"><?= $agg['avg'] !== null ? (int) $agg['avg'] . '<small>%</small>' : '-' ?></div></div>
</section>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Date</th><th>Apprenant</th><th>Formation</th><th class="num">Bonnes rép.</th><th class="num">Durée</th><th class="num">Score</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="dim">Aucun résultat.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e(fmt_datetime($r['submitted_at'])) ?></td><td><?= e($r['first_name'] . ' ' . $r['last_name']) ?><br><small class="dim"><?= e($r['email']) ?></small></td><td><?= e($r['code']) ?></td>
      <td class="num"><?= (int) $r['correct_count'] ?> / <?= (int) $r['total_count'] ?></td><td class="num"><?= gmdate('i:s', max(0, (int) $r['dur'])) ?></td>
      <td class="num"><span class="score <?= $r['passed'] ? 'score--pass' : 'score--fail' ?>"><?= e(number_format((float) $r['score'], 0)) ?> %</span></td>
      <td><div class="actions"><?php if (!$r['passed'] && can('exams.manage')): ?><?= action_btn('grant', ['attempt_id' => $r['id'], 'course' => $course], 'Accorder une tentative', 'btn--sm', 'Retirer cette tentative échouée afin d\'accorder une tentative supplémentaire ?') ?><?php endif; ?></div></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?= pagination_links($pg, array_filter(['course' => $course, 'status' => $status])) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
