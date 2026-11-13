<?php
/** Espace apprenant : tableau de bord personnalisé (données réelles MySQL). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

$u = require_login();
$uid = (int) $u['id'];
$enrolled = q_all('SELECT ce.*, c.title, c.code, c.slug, c.duration_hours, cat.code AS category_code, m.stored_name AS cover_stored
                     FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN categories cat ON cat.id = c.category_id
                     LEFT JOIN media m ON m.id = c.cover_media_id
                    WHERE ce.user_id = ? ORDER BY COALESCE(ce.last_activity, ce.enrolled_at) DESC', [$uid]);
$stats = [
    'enrolled'  => count($enrolled),
    'completed' => count(array_filter($enrolled, static fn ($e) => $e['status'] === 'completed')),
    'lessons'   => (int) q_val('SELECT COUNT(*) FROM lesson_progress WHERE user_id = ?', [$uid]),
    'certs'     => (int) q_val("SELECT COUNT(*) FROM certificates WHERE user_id = ? AND status = 'valid'", [$uid]),
    'avg'       => q_val('SELECT ROUND(AVG(score)) FROM exam_attempts WHERE user_id = ? AND submitted_at IS NOT NULL', [$uid]),
];
$avgProgress = $enrolled ? (int) round(array_sum(array_column($enrolled, 'progress')) / count($enrolled)) : 0;

// Prochaine étape pédagogique pour chaque formation en cours
$next = [];
foreach (array_filter($enrolled, static fn ($e) => $e['status'] === 'active') as $e) {
    $l = q_one('SELECT l.id, l.title, l.type, ch.title AS chapter FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id
                 WHERE ch.course_id = ? AND NOT EXISTS (SELECT 1 FROM lesson_progress lp WHERE lp.lesson_id = l.id AND lp.user_id = ?)
                 ORDER BY ch.sort_order, l.sort_order, l.id LIMIT 1', [$e['course_id'], $uid]);
    $next[] = $l ? ['course' => $e, 'lesson' => $l, 'kind' => 'lesson'] : ['course' => $e, 'lesson' => null, 'kind' => 'exam'];
}
$attempts = q_all('SELECT ea.*, c.title, c.code, e.pass_mark FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id
                    WHERE ea.user_id = ? AND ea.submitted_at IS NOT NULL ORDER BY ea.submitted_at DESC LIMIT 5', [$uid]);
// Activité : leçons terminées sur 14 jours
$rows = array_column(q_all('SELECT DATE(completed_at) d, COUNT(*) n FROM lesson_progress WHERE user_id = ? AND completed_at >= UTC_DATE() - INTERVAL 13 DAY GROUP BY DATE(completed_at)', [$uid]), 'n', 'd');
$activity = [];
for ($i = 13; $i >= 0; $i--) {
    $d = gmdate('Y-m-d', strtotime("-$i day"));
    $activity[] = ['label' => gmdate('d/m', strtotime($d)), 'value' => (int) ($rows[$d] ?? 0)];
}
$suggest = $enrolled ? [] : find_courses(['featured' => true, 'limit' => 3]);

$pageTitle = 'Tableau de bord';
$appArea = 'learner';
$activeApp = 'dashboard';
require APP_ROOT . '/includes/app-header.php';
?>
<section class="welcome reveal">
  <p class="welcome__code">Session ouverte | Agent <?= e(strtoupper(substr(hash('crc32b', (string) $uid), 0, 6))) ?> | <span data-utc-clock>UTC</span></p>
  <h1>WELCOME TO AFRICA'SPY INTELLIGENCE ACADEMY.</h1>
  <p>Bonjour <?= e($u['first_name']) ?>. <?php if ($next): ?>Votre prochaine étape : <strong><?= e($next[0]['lesson']['title'] ?? 'examen final de « ' . $next[0]['course']['title'] . ' »') ?></strong>.<?php elseif ($enrolled): ?>Toutes vos formations sont terminées. Explorez le catalogue pour poursuivre votre parcours.<?php else: ?>Commencez par choisir une formation dans le catalogue.<?php endif; ?></p>
  <div class="welcome__next">
    <?php if ($next): $n = $next[0]; ?>
      <a class="btn btn--primary" href="<?= e(url('learner/learning.php', array_filter(['course' => $n['course']['course_id'], 'lesson' => $n['lesson']['id'] ?? null]))) ?>"><?= $n['kind'] === 'lesson' ? 'Reprendre' : 'Accéder à l\'examen' ?></a>
    <?php endif; ?>
    <a class="btn btn--ghost" href="<?= e(url('academy.php')) ?>">Catalogue des formations</a>
  </div>
</section>

<section class="stats" data-stagger="70" aria-label="Indicateurs personnels">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--cyan)"></i>Formations suivies</div><div class="stat__value" data-count="<?= $stats['enrolled'] ?>">0</div><div class="stat__hint"><?= $stats['completed'] ?> terminée<?= $stats['completed'] > 1 ? 's' : '' ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--turq)"></i>Leçons terminées</div><div class="stat__value" data-count="<?= $stats['lessons'] ?>">0</div><div class="stat__hint">Progression moyenne <?= $avgProgress ?> %</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>Score moyen aux examens</div><div class="stat__value"><?= $stats['avg'] !== null ? '<span data-count="' . (int) $stats['avg'] . '">0</span><small>%</small>' : '-' ?></div><div class="stat__hint"><?= count($attempts) ? 'Sur vos tentatives soumises' : 'Aucun examen passé' ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Certificats obtenus</div><div class="stat__value" data-count="<?= $stats['certs'] ?>">0</div><div class="stat__hint"><a href="<?= e(url('learner/certificates.php')) ?>">Consulter</a></div></div>
</section>

<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Formations en cours</h2><a class="link-arrow" href="<?= e(url('learner/courses.php')) ?>">Tout voir</a></div>
    <div class="box__body--flush">
      <?php if (!$enrolled): ?>
        <div class="box__body"><div class="empty"><strong>Aucune formation</strong>Inscrivez-vous à une première formation pour démarrer.</div></div>
      <?php endif; ?>
      <?php foreach (array_slice($enrolled, 0, 5) as $e): $img = $e['cover_stored'] ? url('uploads/courses/' . rawurlencode($e['cover_stored'])) : url('assets/images/covers/' . $e['category_code'] . '.webp'); ?>
      <div class="track">
        <div class="track__img"><img src="<?= e($img) ?>" alt="" loading="lazy"></div>
        <div>
          <span class="track__code"><?= e($e['code']) ?></span>
          <p class="track__title"><?= e($e['title']) ?></p>
          <div class="progress" role="progressbar" aria-valuenow="<?= (int) $e['progress'] ?>" aria-valuemin="0" aria-valuemax="100"><span data-p="<?= (int) $e['progress'] ?>"></span></div>
          <p class="track__meta"><?= (int) $e['progress'] ?> % | <?= $e['status'] === 'completed' ? 'Terminée le ' . e(fmt_date($e['completed_at'], false)) : 'Dernière activité ' . e(time_ago($e['last_activity'] ?? $e['enrolled_at'])) ?></p>
        </div>
        <a class="btn btn--sm" href="<?= e(url('learner/learning.php', ['course' => $e['course_id']])) ?>"><?= $e['status'] === 'completed' ? 'Revoir' : 'Continuer' ?></a>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Activité | 14 jours</h2><span class="mono">Leçons terminées</span></div>
    <div class="box__body">
      <div class="chart" data-chart="bars"><canvas aria-label="Histogramme des leçons terminées par jour" role="img"></canvas><script type="application/json"><?= json_encode($activity) ?></script></div>
    </div>
  </section>
</div>

<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Prochaines étapes</h2><span class="mono">Plan pédagogique</span></div>
    <ul class="feed">
      <?php if (!$next): ?><li><span>Aucune étape en attente.</span><time></time></li><?php endif; ?>
      <?php foreach ($next as $n): ?>
      <li><span><strong><?= e($n['course']['code']) ?></strong> : <?= $n['kind'] === 'lesson' ? e($n['lesson']['chapter'] . ' / ' . $n['lesson']['title']) : 'Toutes les leçons sont terminées : passez l\'examen final' ?></span>
        <a class="link-arrow" href="<?= e($n['kind'] === 'lesson' ? url('learner/learning.php', ['course' => $n['course']['course_id'], 'lesson' => $n['lesson']['id']]) : url('learner/exams.php', ['course' => $n['course']['course_id']])) ?>">Go</a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Derniers examens</h2><a class="link-arrow" href="<?= e(url('learner/exams.php')) ?>">Historique</a></div>
    <div class="table-wrap"><table class="table">
      <thead><tr><th>Formation</th><th>Date</th><th class="num">Score</th></tr></thead>
      <tbody>
      <?php if (!$attempts): ?><tr><td colspan="3" class="dim">Aucun examen soumis.</td></tr><?php endif; ?>
      <?php foreach ($attempts as $a): ?>
        <tr><td><?= e($a['code']) ?></td><td><?= e(fmt_datetime($a['submitted_at'])) ?></td><td class="num"><span class="score <?= $a['passed'] ? 'score--pass' : 'score--fail' ?>"><?= e(number_format((float) $a['score'], 0)) ?> %</span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </section>
</div>

<?php if ($suggest): ?>
<section class="box reveal">
  <div class="box__head"><h2>Formations recommandées</h2></div>
  <div class="box__body"><div class="bento"><?php foreach ($suggest as $c) echo course_card($c); ?></div></div>
</section>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
