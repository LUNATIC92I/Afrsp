<?php
/** Administration : examens finaux et banque de questions QCM. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('exams.manage');

if (is_post()) {
    verify_csrf();
    $exam = q_one('SELECT * FROM exams WHERE id = ?', [input_int('exam_id')]);
    if (!$exam) { flash('error', 'Examen introuvable.'); back(); }
    $cid = (int) $exam['course_id'];
    switch (input('action')) {
        case 'settings':
            $d = ['title' => input('title'), 'instructions' => input('instructions'), 'pass_mark' => input('pass_mark'), 'max_attempts' => input('max_attempts'), 'question_count' => input('question_count'), 'time_limit_min' => input('time_limit_min')];
            $err = validate($d, ['title' => 'required|max:190', 'instructions' => 'max:2000', 'pass_mark' => 'required|int', 'max_attempts' => 'required|int', 'question_count' => 'required|int', 'time_limit_min' => 'required|int'],
                ['title' => 'Titre', 'pass_mark' => 'Seuil', 'max_attempts' => 'Tentatives', 'question_count' => 'Nombre de questions', 'time_limit_min' => 'Durée']);
            if ((int) $d['pass_mark'] < 1 || (int) $d['pass_mark'] > 100) $err[] = 'Le seuil doit être compris entre 1 et 100 %.';
            if ((int) $d['max_attempts'] < 1 || (int) $d['max_attempts'] > 20) $err[] = 'Tentatives : entre 1 et 20.';
            if ((int) $d['question_count'] < 1 || (int) $d['question_count'] > 100) $err[] = 'Questions : entre 1 et 100.';
            if ((int) $d['time_limit_min'] < 1 || (int) $d['time_limit_min'] > 600) $err[] = 'Durée : entre 1 et 600 minutes.';
            if ($err) { flash('error', implode(' ', $err)); break; }
            q('UPDATE exams SET title=?, instructions=?, pass_mark=?, max_attempts=?, question_count=?, time_limit_min=?, require_completion=?, is_active=? WHERE id=?',
                [$d['title'], $d['instructions'], (int) $d['pass_mark'], (int) $d['max_attempts'], (int) $d['question_count'], (int) $d['time_limit_min'], isset($_POST['require_completion']) ? 1 : 0, isset($_POST['is_active']) ? 1 : 0, $exam['id']]);
            audit('exam.settings', 'exam', (int) $exam['id'], "seuil={$d['pass_mark']} tentatives={$d['max_attempts']}");
            flash('success', 'Paramètres de l\'examen enregistrés.');
            break;
        case 'question':
            $qid = input_int('question_id');
            $prompt = input('prompt');
            $raw = array_values(array_map(static fn ($a) => trim((string) $a), (array) ($_POST['answers'] ?? [])));
            $correctRaw = input_int('correct', -1);
            $answers = $flags = [];
            foreach ($raw as $i => $label) {
                if ($label === '') continue;
                $answers[] = mb_substr($label, 0, 500);
                $flags[] = $i === $correctRaw ? 1 : 0;
            }
            $correct = input_int('correct', -1);
            $chapterId = input_int('chapter_id') ?: null;
            $err = [];
            if (mb_strlen($prompt) < 5) $err[] = 'L\'énoncé est trop court.';
            if (count($answers) < 2) $err[] = 'Au moins deux réponses sont nécessaires.';
            if ($correct < 0 || $correct >= count($_POST['answers'] ?? []) || trim((string) ($_POST['answers'][$correct] ?? '')) === '') $err[] = 'Désignez une bonne réponse non vide.';
            if ($chapterId && !q_val('SELECT 1 FROM chapters WHERE id = ? AND course_id = ?', [$chapterId, $cid])) $err[] = 'Chapitre invalide.';
            if ($err) { flash('error', implode(' ', $err)); set_old($_POST); back(['course' => $cid, 'q' => $qid ?: 'new']); }
            $pdo = db();
            $pdo->beginTransaction();
            if ($qid) {
                q('UPDATE questions SET prompt = ?, explanation = ?, chapter_id = ?, is_active = ? WHERE id = ? AND exam_id = ?', [$prompt, input('explanation'), $chapterId, isset($_POST['is_active']) ? 1 : 0, $qid, $exam['id']]);
                if (q_val('SELECT COUNT(*) FROM attempt_answers WHERE question_id = ?', [$qid])) {
                    // Question déjà utilisée : on met à jour les libellés existants sans casser l'historique
                    $old = q_all('SELECT id FROM answers WHERE question_id = ? ORDER BY sort_order, id', [$qid]);
                    foreach ($answers as $i => $label) {
                        if (isset($old[$i])) q('UPDATE answers SET label = ?, is_correct = ?, sort_order = ? WHERE id = ?', [$label, $flags[$i], $i + 1, $old[$i]['id']]);
                        else q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $flags[$i], $i + 1]);
                    }
                    for ($i = count($answers); $i < count($old); $i++) q('DELETE FROM answers WHERE id = ?', [$old[$i]['id']]);
                } else {
                    q('DELETE FROM answers WHERE question_id = ?', [$qid]);
                    foreach ($answers as $i => $label) q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $flags[$i], $i + 1]);
                }
                audit('question.update', 'question', $qid);
            } else {
                q('INSERT INTO questions (exam_id, chapter_id, prompt, explanation, is_active) VALUES (?,?,?,?,1)', [$exam['id'], $chapterId, $prompt, input('explanation')]);
                $qid = (int) $pdo->lastInsertId();
                foreach ($answers as $i => $label) q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $flags[$i], $i + 1]);
                audit('question.create', 'question', $qid);
            }
            $pdo->commit();
            flash('success', 'Question enregistrée.');
            break;
        case 'delete_question':
            $qid = input_int('question_id');
            if (q_val('SELECT COUNT(*) FROM attempt_answers WHERE question_id = ?', [$qid])) {
                q('UPDATE questions SET is_active = 0 WHERE id = ? AND exam_id = ?', [$qid, $exam['id']]);
                flash('info', 'Question déjà utilisée dans des examens : elle a été désactivée pour préserver l\'historique.');
            } else {
                q('DELETE FROM questions WHERE id = ? AND exam_id = ?', [$qid, $exam['id']]);
                flash('success', 'Question supprimée.');
            }
            audit('question.delete', 'question', $qid);
            break;
    }
    back(['course' => $cid]);
}

$courses = admin_courses_options();
$cid = input_int('course') ?: (int) array_key_first($courses);
$exam = q_one('SELECT e.*, c.code, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.course_id = ?', [$cid]);
if (!$exam && $cid && q_val('SELECT 1 FROM courses WHERE id = ?', [$cid])) {
    q('INSERT INTO exams (course_id, title) VALUES (?, ?)', [$cid, 'Examen final']);
    $exam = q_one('SELECT e.*, c.code, c.title AS course_title FROM exams e JOIN courses c ON c.id = e.course_id WHERE e.course_id = ?', [$cid]);
}
$chapters = $exam ? ['' => 'Aucun (examen final uniquement)'] + array_column(q_all('SELECT id, title FROM chapters WHERE course_id = ? ORDER BY sort_order', [$cid]), 'title', 'id') : [];
$questions = $exam ? q_all('SELECT q.*, ch.title AS chapter_title, (SELECT COUNT(*) FROM answers a WHERE a.question_id = q.id) n,
                                   (SELECT ROUND(AVG(aa.is_correct)*100) FROM attempt_answers aa WHERE aa.question_id = q.id) rate
                              FROM questions q LEFT JOIN chapters ch ON ch.id = q.chapter_id WHERE q.exam_id = ? ORDER BY ch.sort_order, q.id', [$exam['id']]) : [];
$qEdit = input('q');
$editQ = ctype_digit($qEdit) ? q_one('SELECT * FROM questions WHERE id = ? AND exam_id = ?', [(int) $qEdit, $exam['id'] ?? 0]) : null;
$editAnswers = $editQ ? q_all('SELECT * FROM answers WHERE question_id = ? ORDER BY sort_order, id', [$editQ['id']]) : [];
$pageTitle = 'Examens & QCM';
$appArea = 'admin';
$activeApp = 'exams';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="toolbar reveal" method="get"><?= sel('course', 'Formation', $courses, $cid, ['data-autosubmit' => true]) ?></form>
<?php if ($exam): $active = count(array_filter($questions, static fn ($q) => $q['is_active'])); ?>
<div class="grid-2">
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2>Paramètres de l'examen final</h2><span class="mono"><?= e($exam['code']) ?></span></div>
    <div class="box__body form-grid">
      <?= csrf_field() ?><input type="hidden" name="action" value="settings"><input type="hidden" name="exam_id" value="<?= (int) $exam['id'] ?>">
      <?= fld('title', 'Intitulé', $exam['title'], 'text', ['data-full' => true, 'maxlength' => 190]) ?>
      <?= fld('pass_mark', 'Seuil de réussite (%)', (string) $exam['pass_mark'], 'number', ['min' => 1, 'max' => 100]) ?>
      <?= fld('max_attempts', 'Tentatives autorisées', (string) $exam['max_attempts'], 'number', ['min' => 1, 'max' => 20]) ?>
      <?= fld('question_count', 'Questions tirées par examen', (string) $exam['question_count'], 'number', ['min' => 1, 'max' => 100], "Banque active : $active question(s).") ?>
      <?= fld('time_limit_min', 'Durée (minutes)', (string) $exam['time_limit_min'], 'number', ['min' => 1, 'max' => 600]) ?>
      <?= fld('instructions', 'Consignes', (string) $exam['instructions'], 'textarea', ['data-full' => true, 'rows' => 3]) ?>
      <label class="check full"><input type="checkbox" name="require_completion" value="1" <?= $exam['require_completion'] ? 'checked' : '' ?>> Exiger 100 % des leçons avant l'examen</label>
      <label class="check full"><input type="checkbox" name="is_active" value="1" <?= $exam['is_active'] ? 'checked' : '' ?>> Examen actif</label>
      <div class="full"><button class="btn btn--primary" type="submit">Enregistrer</button></div>
    </div>
  </form>
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2><?= $editQ ? 'Modifier la question #' . (int) $editQ['id'] : 'Nouvelle question' ?></h2><?php if ($editQ): ?><a class="link-arrow" href="<?= e(url('admin/exams.php', ['course' => $cid])) ?>">Nouvelle</a><?php endif; ?></div>
    <div class="box__body">
      <?= csrf_field() ?><input type="hidden" name="action" value="question"><input type="hidden" name="exam_id" value="<?= (int) $exam['id'] ?>"><input type="hidden" name="question_id" value="<?= (int) ($editQ['id'] ?? 0) ?>">
      <?= fld('prompt', 'Énoncé', old('prompt', $editQ['prompt'] ?? ''), 'textarea', ['rows' => 3, 'required' => true]) ?>
      <?= sel('chapter_id', 'Chapitre (QCM formatif)', $chapters, $editQ['chapter_id'] ?? '') ?>
      <p class="label">Réponses : cochez la bonne</p>
      <div id="answers-list">
        <?php $list = $editAnswers ?: [['label' => '', 'is_correct' => 1], ['label' => '', 'is_correct' => 0], ['label' => '', 'is_correct' => 0], ['label' => '', 'is_correct' => 0]];
        foreach ($list as $i => $a): ?>
        <div class="answer-row"><input type="radio" name="correct" value="<?= $i ?>" <?= $a['is_correct'] ? 'checked' : '' ?> aria-label="Bonne réponse"><input class="input" name="answers[]" maxlength="500" value="<?= e($a['label']) ?>" placeholder="Réponse <?= $i + 1 ?>"><button class="btn btn--sm btn--danger" type="button" data-remove-row aria-label="Retirer">×</button></div>
        <?php endforeach; ?>
      </div>
      <button class="btn btn--sm mb-2" type="button" data-add-answer="answers-list">+ Ajouter une réponse</button>
      <?= fld('explanation', 'Explication (affichée après le QCM de chapitre)', $editQ['explanation'] ?? '', 'textarea', ['rows' => 3]) ?>
      <?php if ($editQ): ?><label class="check mb-2"><input type="checkbox" name="is_active" value="1" <?= $editQ['is_active'] ? 'checked' : '' ?>> Question active</label><?php endif; ?>
      <button class="btn btn--primary" type="submit">Enregistrer la question</button>
    </div>
  </form>
</div>
<section class="box reveal">
  <div class="box__head"><h2>Banque de questions</h2><span class="mono"><?= count($questions) ?> question(s) | <?= $active ?> active(s)</span></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>#</th><th>Énoncé</th><th>Chapitre</th><th class="num">Rép.</th><th class="num">Taux de bonnes réponses</th><th>État</th><th></th></tr></thead>
    <tbody><?php foreach ($questions as $q): ?>
      <tr><td class="mono"><?= (int) $q['id'] ?></td><td><?= e(str_limit($q['prompt'], 110)) ?></td><td><small><?= e($q['chapter_title'] ?? '-') ?></small></td><td class="num"><?= (int) $q['n'] ?></td><td class="num"><?= $q['rate'] !== null ? (int) $q['rate'] . ' %' : '-' ?></td>
        <td><?= $q['is_active'] ? '<span class="status-pill status-pill--on">Active</span>' : '<span class="status-pill status-pill--off">Inactive</span>' ?></td>
        <td><div class="actions"><a class="btn btn--sm" href="<?= e(url('admin/exams.php', ['course' => $cid, 'q' => $q['id']])) ?>">Modifier</a><?= action_btn('delete_question', ['exam_id' => $exam['id'], 'question_id' => $q['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer cette question ?') ?></div></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
</section>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php';
