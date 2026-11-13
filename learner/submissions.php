<?php
/** Espace apprenant : mes travaux rendus et leurs corrections. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
$u = require_login();
$rows = q_all('SELECT s.*, l.title, l.type, c.id AS course_id, c.code FROM submissions s JOIN lessons l ON l.id = s.lesson_id JOIN chapters ch ON ch.id = l.chapter_id JOIN courses c ON c.id = ch.course_id WHERE s.user_id = ? ORDER BY s.submitted_at DESC', [$u['id']]);
$graded = array_filter($rows, static fn ($r) => $r['grade'] !== null);
$pageTitle = 'Mes travaux';
$appArea = 'learner';
$activeApp = 'submissions';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="stats">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--cyan)"></i>Travaux déposés</div><div class="stat__value"><?= count($rows) ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Corrigés</div><div class="stat__value"><?= count($graded) ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>Moyenne</div><div class="stat__value"><?= $graded ? number_format(array_sum(array_column($graded, 'grade')) / count($graded), 1, ',', '') . '<small>/20</small>' : '-' ?></div></div>
</section>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Travail</th><th>Formation</th><th>Déposé le</th><th>Statut</th><th class="num">Note</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="6"><div class="empty"><strong>Aucun travail déposé</strong>Les exercices, simulations et études de cas de vos formations peuvent être rendus pour correction.</div></td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e($r['title']) ?><br><small class="dim"><?= e(lesson_type_label($r['type'])) ?></small></td><td><?= e($r['code']) ?></td><td><?= e(fmt_datetime($r['submitted_at'])) ?></td>
      <td><?= ['submitted' => '<span class="status-pill status-pill--warn">En attente</span>', 'revision' => '<span class="status-pill status-pill--bad">Reprise demandée</span>', 'reviewed' => '<span class="status-pill status-pill--on">Corrigé</span>'][$r['status']] ?></td>
      <td class="num"><?= $r['grade'] !== null ? '<strong class="gold">' . (int) $r['grade'] . '</strong>/20' : '-' ?></td>
      <td><a class="btn btn--sm" href="<?= e(url('learner/learning.php', ['course' => $r['course_id'], 'lesson' => $r['lesson_id']])) ?>">Ouvrir</a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?php require APP_ROOT . '/includes/app-footer.php';
