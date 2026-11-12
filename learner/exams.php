<?php
/** Espace apprenant — examens finaux : liste, passage chronométré, résultats, historique. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/learning.php';

$u = require_login();
$uid = (int) $u['id'];

/* ------------------------------------------------------------ Actions POST */
if (is_post()) {
    verify_csrf();
    $action = input('action');
    if ($action === 'start') {
        $course = find_course((string) input_int('course_id'));
        if (!$course || !is_enrolled($uid, (int) $course['id'])) {
            flash('error', 'Vous devez être inscrit à la formation.');
            redirect('learner/exams.php');
        }
        $exam = course_exam((int) $course['id']);
        $res = $exam ? start_exam_attempt($exam, $uid, (int) $course['id']) : 'Aucun examen pour cette formation.';
        if (is_string($res)) {
            flash('error', $res);
            redirect('learner/exams.php', ['course' => $course['id']]);
        }
        redirect('learner/exams.php', ['attempt' => $res]);
    }
    if ($action === 'submit') {
        $attempt = q_one('SELECT * FROM exam_attempts WHERE id = ? AND user_id = ?', [input_int('attempt_id'), $uid]);
        if (!$attempt) {
            render_error_page(404, 'Tentative introuvable', 'Cette tentative n\'existe pas.');
        }
        if ($attempt['submitted_at'] !== null) {
            redirect('learner/exams.php', ['result' => $attempt['id']]);
        }
        $exam = q_one('SELECT * FROM exams WHERE id = ?', [$attempt['exam_id']]);
        $given = [];
        foreach ((array) ($_POST['answers'] ?? []) as $qid => $aid) {
            if (ctype_digit((string) $qid) && ctype_digit((string) $aid)) $given[(int) $qid] = (int) $aid;
        }
        try {
            $r = grade_attempt($attempt, $exam, $given);
            flash($r['passed'] ? 'success' : 'warning', $r['passed'] ? 'Examen réussi ! Votre certificat a été généré.' : 'Examen non validé. Révisez puis retentez si des tentatives restent disponibles.');
        } catch (RuntimeException $e) {
            flash('info', $e->getMessage());
        }
        redirect('learner/exams.php', ['result' => $attempt['id']]);
    }
    redirect('learner/exams.php');
}

$appArea = 'learner';
$activeApp = 'exams';

/* ------------------------------------------------------------ Passage d'examen */
if ($aid = input_int('attempt')) {
    $attempt = q_one('SELECT ea.*, e.title, e.pass_mark, e.course_id, c.code FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id WHERE ea.id = ? AND ea.user_id = ?', [$aid, $uid]);
    if (!$attempt) render_error_page(404, 'Tentative introuvable', 'Cette tentative n\'existe pas.');
    if ($attempt['submitted_at'] !== null) redirect('learner/exams.php', ['result' => $aid]);
    $remaining = strtotime($attempt['expires_at'] . ' UTC') - time();
    if ($remaining < -EXAM_GRACE_SECONDS) {
        $exam = q_one('SELECT * FROM exams WHERE id = ?', [$attempt['exam_id']]);
        try { grade_attempt($attempt, $exam, []); } catch (RuntimeException) {}
        flash('warning', 'Le temps imparti pour cette tentative est écoulé.');
        redirect('learner/exams.php', ['result' => $aid]);
    }
    $questions = attempt_questions($attempt);
    $pageTitle = $attempt['title'];
    $crumb = $attempt['code'] . ' · Examen en cours';
    require APP_ROOT . '/includes/app-header.php'; ?>
<form method="post" action="<?= e(url('learner/exams.php')) ?>" data-exam data-remaining="<?= max(0, $remaining) ?>">
  <?= csrf_field() ?><input type="hidden" name="action" value="submit"><input type="hidden" name="attempt_id" value="<?= (int) $attempt['id'] ?>">
  <div class="exam-bar">
    <div><span class="mono dim" style="font-size:.7rem;letter-spacing:.16em">TEMPS RESTANT</span><div class="timer" data-timer role="timer" aria-live="off">--:--</div></div>
    <div class="exam-progress"><span class="mono dim" style="font-size:.7rem;letter-spacing:.16em">RÉPONDU <span data-answered>0</span></span><div class="progress mt-1"><span data-exam-bar></span></div></div>
    <button class="btn btn--primary" type="submit">Soumettre l'examen</button>
  </div>
  <div class="box"><div class="box__body">
    <p class="dim">Seuil de réussite : <?= (int) $attempt['pass_mark'] ?> %. Une seule bonne réponse par question. À l'expiration du temps, vos réponses sont soumises automatiquement.</p>
    <?php foreach ($questions as $i => $q): ?>
    <fieldset class="q">
      <legend><span class="n">Q<?= sprintf('%02d', $i + 1) ?></span><?= e($q['prompt']) ?></legend>
      <?php foreach ($q['answers'] as $a): ?>
      <label class="opt"><input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= (int) $a['id'] ?>"><span><?= e($a['label']) ?></span></label>
      <?php endforeach; ?>
    </fieldset>
    <?php endforeach; ?>
    <button class="btn btn--primary" type="submit">Soumettre l'examen</button>
  </div></div>
</form>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}

/* ------------------------------------------------------------ Résultat */
if ($rid = input_int('result')) {
    $a = q_one('SELECT ea.*, e.title, e.pass_mark, e.max_attempts, e.course_id, c.code, c.title AS course_title FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id WHERE ea.id = ? AND ea.user_id = ? AND ea.submitted_at IS NOT NULL', [$rid, $uid]);
    if (!$a) render_error_page(404, 'Résultat introuvable', 'Ce résultat n\'existe pas.');
    $review = q_all('SELECT q.prompt, aa.is_correct, aa.answer_id FROM attempt_answers aa JOIN questions q ON q.id = aa.question_id WHERE aa.attempt_id = ?', [$rid]);
    $cert = q_one("SELECT certificate_no FROM certificates WHERE user_id = ? AND course_id = ? AND status = 'valid'", [$uid, $a['course_id']]);
    $pageTitle = 'Résultat d\'examen';
    $crumb = $a['code'];
    require APP_ROOT . '/includes/app-header.php'; ?>
<section class="box result-hero <?= $a['passed'] ? 'result-hero--pass' : 'result-hero--fail' ?> reveal">
  <p class="mono dim"><?= e($a['code']) ?> · <?= e(fmt_datetime($a['submitted_at'])) ?></p>
  <div class="result-hero__score"><span data-count="<?= (int) round((float) $a['score']) ?>">0</span>%</div>
  <h2 style="margin-top:14px"><?= $a['passed'] ? 'EXAMEN RÉUSSI' : 'EXAMEN NON VALIDÉ' ?></h2>
  <p><?= (int) $a['correct_count'] ?> bonne<?= $a['correct_count'] > 1 ? 's' : '' ?> réponse<?= $a['correct_count'] > 1 ? 's' : '' ?> sur <?= (int) $a['total_count'] ?> · seuil <?= (int) $a['pass_mark'] ?> %</p>
  <div class="flex" style="justify-content:center;margin-top:20px">
    <?php if ($cert): ?><a class="btn btn--gold" href="<?= e(url('learner/certificate.php', ['no' => $cert['certificate_no']])) ?>" data-no-transition>Télécharger mon certificat</a><?php endif; ?>
    <a class="btn btn--ghost" href="<?= e(url('learner/exams.php', ['course' => $a['course_id']])) ?>">Retour à l'examen</a>
    <a class="btn" href="<?= e(url('learner/learning.php', ['course' => $a['course_id']])) ?>">Réviser la formation</a>
  </div>
</section>
<section class="box reveal">
  <div class="box__head"><h2>Analyse de la tentative</h2><span class="mono">Les bonnes réponses ne sont pas affichées afin de préserver la banque de questions</span></div>
  <ul class="feed">
    <?php foreach ($review as $i => $r): ?>
    <li><span><span class="mono" style="color:var(--gold)">Q<?= sprintf('%02d', $i + 1) ?></span> <?= e($r['prompt']) ?></span><time class="<?= $r['is_correct'] ? 'score--pass' : 'score--fail' ?>"><?= $r['is_correct'] ? '✓ Correct' : ($r['answer_id'] ? '✗ Incorrect' : '— Sans réponse') ?></time></li>
    <?php endforeach; ?>
  </ul>
</section>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}

/* ------------------------------------------------------------ Détail d'un examen */
if ($cid = input_int('course')) {
    $course = find_course((string) $cid);
    if (!$course || !is_enrolled($uid, $cid)) {
        flash('info', 'Inscrivez-vous à la formation pour accéder à son examen.');
        redirect($course ? 'course.php?slug=' . rawurlencode($course['slug']) : 'academy.php');
    }
    $exam = course_exam($cid);
    $st = $exam ? exam_status($exam, $uid, $cid) : null;
    $pageTitle = 'Examen final';
    $crumb = $course['code'];
    require APP_ROOT . '/includes/app-header.php'; ?>
<?php if (!$exam): ?>
  <div class="empty"><strong>Pas d'examen</strong>Cette formation ne comporte pas encore d'examen final.</div>
<?php else: ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2><?= e($exam['title']) ?></h2><span class="mono"><?= e($course['code']) ?></span></div>
    <div class="box__body">
      <p><?= e($exam['instructions']) ?></p>
      <dl class="kv mt-2">
        <dt>Questions</dt><dd><?= (int) min($exam['question_count'], $exam['bank_size']) ?> (tirées dans une banque de <?= (int) $exam['bank_size'] ?>)</dd>
        <dt>Durée</dt><dd><?= (int) $exam['time_limit_min'] ?> minutes</dd>
        <dt>Seuil de réussite</dt><dd><?= (int) $exam['pass_mark'] ?> %</dd>
        <dt>Tentatives</dt><dd><?= (int) $st['used'] ?> utilisée(s) sur <?= (int) $exam['max_attempts'] ?></dd>
        <dt>Meilleur score</dt><dd><?= $st['best'] !== null ? e(number_format($st['best'], 0)) . ' %' : '—' ?></dd>
      </dl>
      <div class="mt-2">
        <?php if ($st['passed']): $cert = q_one("SELECT certificate_no FROM certificates WHERE user_id = ? AND course_id = ? AND status = 'valid'", [$uid, $cid]); ?>
          <div class="alert alert--success">Examen réussi. Félicitations.</div>
          <?php if ($cert): ?><a class="btn btn--gold" href="<?= e(url('learner/certificate.php', ['no' => $cert['certificate_no']])) ?>" data-no-transition>Télécharger le certificat</a><?php endif; ?>
        <?php elseif ($st['open']): ?>
          <a class="btn btn--primary" href="<?= e(url('learner/exams.php', ['attempt' => $st['open']['id']])) ?>">Reprendre la tentative en cours</a>
        <?php elseif ($st['can_start']): ?>
          <form method="post" action="<?= e(url('learner/exams.php')) ?>" data-confirm="Le chronomètre démarre dès l'ouverture de l'examen. Commencer maintenant ?">
            <?= csrf_field() ?><input type="hidden" name="action" value="start"><input type="hidden" name="course_id" value="<?= $cid ?>">
            <button class="btn btn--primary" type="submit">Démarrer l'examen</button>
          </form>
        <?php else: ?>
          <div class="alert alert--warning"><?= e($st['reason']) ?></div>
          <a class="btn btn--ghost" href="<?= e(url('learner/learning.php', ['course' => $cid])) ?>">Continuer la formation</a>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Historique des tentatives</h2></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>#</th><th>Date</th><th class="num">Score</th><th></th></tr></thead><tbody>
      <?php if (!$st['attempts']): ?><tr><td colspan="4" class="dim">Aucune tentative soumise.</td></tr><?php endif; ?>
      <?php foreach (array_reverse($st['attempts']) as $i => $a): ?>
      <tr><td><?= $i + 1 ?></td><td><?= e(fmt_datetime($a['submitted_at'])) ?></td><td class="num"><span class="score <?= $a['passed'] ? 'score--pass' : 'score--fail' ?>"><?= e(number_format((float) $a['score'], 0)) ?> %</span></td><td><a class="link-arrow" href="<?= e(url('learner/exams.php', ['result' => $a['id']])) ?>">Détail</a></td></tr>
      <?php endforeach; ?>
    </tbody></table></div>
  </section>
</div>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}

/* ------------------------------------------------------------ Vue d'ensemble */
$courses = q_all('SELECT c.id, c.code, c.title, ce.progress, e.pass_mark, e.max_attempts,
                         (SELECT COUNT(*) FROM exam_attempts ea WHERE ea.exam_id = e.id AND ea.user_id = ce.user_id) AS used,
                         (SELECT MAX(score) FROM exam_attempts ea WHERE ea.exam_id = e.id AND ea.user_id = ce.user_id AND ea.submitted_at IS NOT NULL) AS best,
                         (SELECT MAX(passed) FROM exam_attempts ea WHERE ea.exam_id = e.id AND ea.user_id = ce.user_id) AS passed
                    FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id LEFT JOIN exams e ON e.course_id = c.id
                   WHERE ce.user_id = ? ORDER BY ce.enrolled_at DESC', [$uid]);
$history = q_all('SELECT ea.*, c.code, c.title FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id WHERE ea.user_id = ? AND ea.submitted_at IS NOT NULL ORDER BY ea.submitted_at DESC', [$uid]);
$pageTitle = 'Examens & résultats';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="box reveal">
  <div class="box__head"><h2>Examens finaux</h2></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Formation</th><th>Progression</th><th>Tentatives</th><th class="num">Meilleur score</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php if (!$courses): ?><tr><td colspan="6"><div class="empty"><strong>Aucune formation suivie</strong>Les examens apparaissent après inscription.</div></td></tr><?php endif; ?>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td><span class="mono cyan" style="font-size:.72rem"><?= e($c['code']) ?></span><br><?= e($c['title']) ?></td>
        <td><?= (int) $c['progress'] ?> %</td>
        <td><?= (int) $c['used'] ?> / <?= (int) $c['max_attempts'] ?></td>
        <td class="num"><?= $c['best'] !== null ? e(number_format((float) $c['best'], 0)) . ' %' : '—' ?></td>
        <td><?= $c['passed'] ? '<span class="status-pill status-pill--on">● Réussi</span>' : ((int) $c['progress'] >= 100 ? '<span class="status-pill status-pill--warn">● Disponible</span>' : '<span class="status-pill status-pill--off">● Verrouillé</span>') ?></td>
        <td><a class="btn btn--sm" href="<?= e(url('learner/exams.php', ['course' => $c['id']])) ?>">Ouvrir</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<section class="box reveal">
  <div class="box__head"><h2>Historique complet</h2><span class="mono"><?= count($history) ?> tentative(s)</span></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Date</th><th>Formation</th><th class="num">Bonnes réponses</th><th class="num">Score</th><th></th></tr></thead>
    <tbody>
    <?php if (!$history): ?><tr><td colspan="5" class="dim">Aucun examen soumis.</td></tr><?php endif; ?>
    <?php foreach ($history as $h): ?>
      <tr><td><?= e(fmt_datetime($h['submitted_at'])) ?></td><td><?= e($h['code']) ?> · <?= e($h['title']) ?></td><td class="num"><?= (int) $h['correct_count'] ?> / <?= (int) $h['total_count'] ?></td><td class="num"><span class="score <?= $h['passed'] ? 'score--pass' : 'score--fail' ?>"><?= e(number_format((float) $h['score'], 0)) ?> %</span></td><td><a class="link-arrow" href="<?= e(url('learner/exams.php', ['result' => $h['id']])) ?>">Détail</a></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php require APP_ROOT . '/includes/app-footer.php';
