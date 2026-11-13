<?php
/** Interface de formation immersive : lecteur, chapitres, progression, QCM de chapitre. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

$u = require_login();
$course = find_course((string) input_int('course'), !is_staff());
if (!$course) {
    render_error_page(404, 'Formation introuvable', 'Cette formation n\'existe pas ou n\'est pas publiée.');
}
$cid = (int) $course['id'];
$enrolled = is_enrolled((int) $u['id'], $cid);
if (!$enrolled && !can('courses.manage')) {
    flash('info', 'Inscrivez-vous à la formation pour accéder aux cours.');
    redirect('course.php', ['slug' => $course['slug']]);
}
$outline = course_outline($cid, (int) $u['id']);
$flat = [];
foreach ($outline as $ci => $ch) {
    foreach ($ch['lessons'] as $l) {
        $flat[] = $l + ['chapter_index' => $ci, 'chapter_title' => $ch['title']];
    }
}
$quizChapter = input_int('quiz');
$lessonId = input_int('lesson');
$current = null;
if (!$quizChapter) {
    foreach ($flat as $i => $l) {
        if ($lessonId ? (int) $l['id'] === $lessonId : !$l['done']) { $current = $i; break; }
    }
    $current ??= $lessonId ? null : 0;
    if ($current === null || !isset($flat[$current])) {
        render_error_page(404, 'Leçon introuvable', 'Cette leçon n\'appartient pas à la formation.');
    }
}
$lesson = $current !== null ? q_one('SELECT l.*, m.mime_type, m.original_name, m.size_bytes FROM lessons l LEFT JOIN media m ON m.id = l.media_id WHERE l.id = ?', [$flat[$current]['id']]) : null;
$quiz = [];
$quizCh = null;
if ($quizChapter) {
    foreach ($outline as $ch) if ((int) $ch['id'] === $quizChapter) $quizCh = $ch;
    if (!$quizCh) render_error_page(404, 'Évaluation introuvable', 'Ce chapitre n\'appartient pas à la formation.');
    $qs = q_all('SELECT q.id, q.prompt FROM questions q JOIN exams e ON e.id = q.exam_id WHERE e.course_id = ? AND q.chapter_id = ? AND q.is_active = 1 ORDER BY q.id', [$cid, $quizChapter]);
    foreach ($qs as $q) {
        $q['answers'] = q_all('SELECT id, label FROM answers WHERE question_id = ? ORDER BY sort_order, id', [$q['id']]);
        $quiz[] = $q;
    }
}
$submission = ($lesson && $enrolled && in_array($lesson['type'], ['exercise', 'case_study'], true))
    ? (q_one('SELECT * FROM submissions WHERE user_id = ? AND lesson_id = ?', [$u['id'], $lesson['id']]) ?: ['status' => null, 'content' => '', 'grade' => null, 'feedback' => null, 'version' => 0])
    : null;
$note = ($lesson && $enrolled) ? (string) q_val('SELECT content FROM lesson_notes WHERE user_id = ? AND lesson_id = ?', [$u['id'], $lesson['id']]) : null;
$progress = $enrolled ? (int) q_val('SELECT progress FROM course_enrollments WHERE user_id = ? AND course_id = ?', [$u['id'], $cid]) : 0;

/** Lecteur vidéo : fichier hébergé (protégé) ou intégration YouTube/Vimeo respectueuse de la vie privée. */
$videoEmbed = static function (array $l): string {
    if ($l['media_id']) {
        return '<div class="player"><video controls preload="metadata" playsinline src="' . e(url('download.php', ['media' => $l['media_id']])) . '">Votre navigateur ne lit pas la vidéo.</video></div>';
    }
    $u = (string) $l['video_url'];
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $u, $m)) {
        return '<div class="player"><iframe src="https://www.youtube-nocookie.com/embed/' . e($m[1]) . '" title="Vidéo pédagogique" allow="encrypted-media; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>';
    }
    if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $u, $m)) {
        return '<div class="player"><iframe src="https://player.vimeo.com/video/' . e($m[1]) . '?dnt=1" title="Vidéo pédagogique" allow="fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe></div>';
    }
    return '<div class="alert alert--warning">Vidéo non disponible pour le moment.</div>';
};

$pageTitle = $course['title'];
$crumb = $course['code'] . ' | Formation';
$appArea = 'learner';
$activeApp = 'courses';
require APP_ROOT . '/includes/app-header.php';
?>
<div class="learn" data-learn data-course="<?= $cid ?>">
  <nav class="learn__nav" aria-label="Plan de la formation">
    <div class="learn__course">
      <span class="mono"><?= e($course['code']) ?></span>
      <h2><?= e($course['title']) ?></h2>
      <div class="progress" role="progressbar" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100" aria-label="Progression"><span data-p="<?= $progress ?>" data-course-progress></span></div>
      <p class="mono dim mt-1" style="font-size:.7rem"><span data-course-progress-label><?= $progress ?></span> % complété</p>
      <?php if (!$enrolled): ?><p class="tag tag--gold mt-1">Aperçu administrateur</p><?php endif; ?>
    </div>
    <?php foreach ($outline as $ci => $ch): ?>
    <div class="learn__chap">
      <p>Chapitre <?= sprintf('%02d', $ci + 1) ?></p>
      <h3><?= e($ch['title']) ?></h3>
      <ul class="learn__lessons">
        <?php foreach ($ch['lessons'] as $l): ?>
        <li class="<?= $l['done'] ? 'is-done' : '' ?>" data-lesson-item="<?= (int) $l['id'] ?>"><a href="<?= e(url('learner/learning.php', ['course' => $cid, 'lesson' => $l['id']])) ?>" <?= $lesson && (int) $lesson['id'] === (int) $l['id'] ? 'aria-current="page"' : '' ?>><span class="st"></span><span><?= e($l['title']) ?></span><span class="ty"><?= e(lesson_type_label($l['type'])) ?></span></a></li>
        <?php endforeach; ?>
        <?php if ($ch['quiz_count']): ?>
        <li><a href="<?= e(url('learner/learning.php', ['course' => $cid, 'quiz' => $ch['id']])) ?>" <?= $quizChapter === (int) $ch['id'] ? 'aria-current="page"' : '' ?>><span class="st">?</span><span>Évaluation QCM</span><span class="ty"><?= (int) $ch['quiz_count'] ?> q.</span></a></li>
        <?php endif; ?>
      </ul>
    </div>
    <?php endforeach; ?>
    <div class="learn__course"><a class="btn btn--gold btn--block" href="<?= e(url('learner/exams.php', ['course' => $cid])) ?>">Examen final</a></div>
  </nav>

  <div class="learn__main">
  <?php if ($lesson): $prev = $flat[$current - 1] ?? null; $nextL = $flat[$current + 1] ?? null; $done = (bool) $flat[$current]['done']; ?>
    <header class="lesson-head reveal">
      <p class="mono">Chapitre <?= sprintf('%02d', $flat[$current]['chapter_index'] + 1) ?> | <?= e($flat[$current]['chapter_title']) ?> | <?= e(lesson_type_label($lesson['type'])) ?> | <?= (int) $lesson['duration_minutes'] ?> min</p>
      <h1><?= e($lesson['title']) ?></h1>
    </header>
    <div class="lesson-body reveal">
      <?php if ($lesson['type'] === 'video'): ?><?= $videoEmbed($lesson) ?><?php endif; ?>
      <?php if ($lesson['type'] === 'document' && $lesson['media_id']): ?>
      <div class="doc-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 2h9l5 5v15H6z"/><path d="M14 2v6h6M9 13h7M9 17h7"/></svg>
        <div style="flex:1"><strong style="color:var(--white)"><?= e($lesson['original_name']) ?></strong><br><span class="mono dim" style="font-size:.72rem">PDF | <?= e(fmt_bytes((int) $lesson['size_bytes'])) ?></span></div>
        <?php if ($lesson['is_downloadable']): ?>
        <a class="btn btn--sm" href="<?= e(url('download.php', ['media' => $lesson['media_id']])) ?>" target="_blank" rel="noopener" data-no-transition>Consulter</a>
        <a class="btn btn--sm btn--gold" href="<?= e(url('download.php', ['media' => $lesson['media_id'], 'dl' => 1])) ?>" data-no-transition>Télécharger</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="prose"><?= sanitize_html($lesson['content']) ?></div>
    </div>
    <?php if ($submission !== null): $st = $submission['status']; ?>
    <section class="work reveal" aria-labelledby="work-title">
      <div class="work__head">
        <h2 id="work-title">Votre travail</h2>
        <?php if ($st === 'submitted'): ?><span class="status-pill status-pill--warn">Déposé | en attente de correction (version <?= (int) $submission['version'] ?>)</span>
        <?php elseif ($st === 'revision'): ?><span class="status-pill status-pill--bad">Reprise demandée</span>
        <?php elseif ($st === 'reviewed'): ?><span class="status-pill status-pill--on">Corrigé</span>
        <?php else: ?><span class="status-pill status-pill--off">Non déposé</span><?php endif; ?>
      </div>
      <?php if ($st === 'reviewed' || $st === 'revision'): ?>
      <div class="work__review">
        <?php if ($submission['grade'] !== null): ?><div class="work__grade"><b><?= (int) $submission['grade'] ?></b><small>/ 20</small></div><?php endif; ?>
        <div><p class="label">Commentaire du formateur</p><p style="white-space:pre-line;margin:0"><?= e((string) $submission['feedback']) ?></p></div>
      </div>
      <?php endif; ?>
      <?php if ($st === 'reviewed'): ?>
        <details class="mt-1"><summary class="link-arrow">Relire mon travail rendu</summary><div class="work__readonly"><?= nl2br(e($submission['content'])) ?></div></details>
      <?php else: ?>
      <form method="post" action="<?= e(url('ajax/submission.php')) ?>" data-ajax-form data-work>
        <?= csrf_field() ?><input type="hidden" name="lesson_id" value="<?= (int) $lesson['id'] ?>">
        <label class="sr-only" for="work-content">Votre réponse</label>
        <textarea class="textarea work__text" id="work-content" name="content" minlength="200" maxlength="30000" placeholder="Rédigez ici votre réponse en suivant les consignes et le barème ci-dessus. Les données utilisées doivent rester fictives." data-counter><?= e($submission['content']) ?></textarea>
        <div class="work__foot"><span class="mono dim" data-count-out>0 caractère</span><button class="btn btn--primary" type="submit"><?= $st ? 'Déposer la nouvelle version' : 'Déposer mon travail' ?></button></div>
      </form>
      <?php endif; ?>
    </section>
    <?php endif; ?>
    <?php if ($note !== null): ?>
    <details class="notes" <?= $note !== '' ? 'open' : '' ?>>
      <summary><span>Mes notes personnelles</span><span class="mono dim notes__state" data-note-state><?= $note !== '' ? 'enregistrées' : 'privées | sauvegarde automatique' ?></span></summary>
      <label class="sr-only" for="lesson-notes">Notes personnelles sur cette leçon</label>
      <textarea class="textarea notes__text" id="lesson-notes" maxlength="20000" data-note="<?= (int) $lesson['id'] ?>" placeholder="Idées clés, questions, exemples de votre contexte professionnel... Visible par vous seul."><?= e($note) ?></textarea>
    </details>
    <?php endif; ?>
    <footer class="lesson-foot">
      <div>
        <?php if ($enrolled): ?>
        <span class="lesson-done" data-done-label <?= $done ? '' : 'hidden' ?>>Leçon terminée</span>
        <button class="btn btn--primary" type="button" data-complete="<?= (int) $lesson['id'] ?>" <?= $done ? 'hidden' : '' ?>>Marquer comme terminée</button>
        <?php endif; ?>
      </div>
      <div class="flex">
        <?php if ($prev): ?><a class="btn btn--sm" href="<?= e(url('learner/learning.php', ['course' => $cid, 'lesson' => $prev['id']])) ?>">Précédent</a><?php endif; ?>
        <?php if ($nextL): ?><a class="btn btn--sm btn--ghost" data-next href="<?= e(url('learner/learning.php', ['course' => $cid, 'lesson' => $nextL['id']])) ?>">Suivant</a>
        <?php else: ?><a class="btn btn--sm btn--gold" href="<?= e(url('learner/exams.php', ['course' => $cid])) ?>">Examen final</a><?php endif; ?>
      </div>
    </footer>
  <?php else: ?>
    <header class="lesson-head reveal">
      <p class="mono">Évaluation formative | non comptabilisée dans l'examen final</p>
      <h1>QCM : <?= e($quizCh['title']) ?></h1>
      <p class="dim">Répondez à toutes les questions puis validez : la correction et les explications s'affichent immédiatement. Vous pouvez recommencer autant de fois que nécessaire.</p>
    </header>
    <form class="quiz" data-quiz action="<?= e(url('ajax/quiz.php')) ?>" method="post">
      <?= csrf_field() ?><input type="hidden" name="chapter_id" value="<?= (int) $quizCh['id'] ?>">
      <?php foreach ($quiz as $i => $q): ?>
      <fieldset class="q" data-q="<?= (int) $q['id'] ?>">
        <legend><span class="n">Q<?= $i + 1 ?></span><?= e($q['prompt']) ?></legend>
        <?php foreach ($q['answers'] as $a): ?>
        <label class="opt" data-a="<?= (int) $a['id'] ?>"><input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= (int) $a['id'] ?>" required><span><?= e($a['label']) ?></span></label>
        <?php endforeach; ?>
      </fieldset>
      <?php endforeach; ?>
      <button class="btn btn--primary" type="submit">Valider mes réponses</button>
      <p class="quiz__result" data-quiz-result aria-live="polite"></p>
    </form>
  <?php endif; ?>
  </div>
</div>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
