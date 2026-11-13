<?php
/** Administration : chapitres d'une formation (ajout, édition, ordre, suppression). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('courses.manage');

$courseId = input_int('course');
if (is_post()) {
    verify_csrf();
    $id = input_int('id');
    $courseId = input_int('course_id');
    if (!q_val('SELECT 1 FROM courses WHERE id = ?', [$courseId])) { flash('error', 'Formation invalide.'); back(); }
    switch (input('action')) {
        case 'save':
            $d = ['title' => input('title'), 'summary' => input('summary')];
            if ($e = validate($d, ['title' => 'required|max:190', 'summary' => 'max:1000'], ['title' => 'Titre'])) { flash('error', implode(' ', $e)); break; }
            if ($id) {
                q('UPDATE chapters SET title = ?, summary = ? WHERE id = ? AND course_id = ?', [$d['title'], $d['summary'], $id, $courseId]);
                audit('chapter.update', 'chapter', $id);
            } else {
                $pos = (int) q_val('SELECT COALESCE(MAX(sort_order),0)+1 FROM chapters WHERE course_id = ?', [$courseId]);
                q('INSERT INTO chapters (course_id, title, summary, sort_order) VALUES (?,?,?,?)', [$courseId, $d['title'], $d['summary'], $pos]);
                audit('chapter.create', 'chapter', (int) db()->lastInsertId());
            }
            flash('success', 'Chapitre enregistré.');
            break;
        case 'move':
            $ch = q_one('SELECT id, sort_order FROM chapters WHERE id = ? AND course_id = ?', [$id, $courseId]);
            $dir = input('dir') === 'up' ? '<' : '>';
            $other = $ch ? q_one("SELECT id, sort_order FROM chapters WHERE course_id = ? AND sort_order $dir ? ORDER BY sort_order " . ($dir === '<' ? 'DESC' : 'ASC') . ' LIMIT 1', [$courseId, $ch['sort_order']]) : null;
            if ($ch && $other) {
                q('UPDATE chapters SET sort_order = ? WHERE id = ?', [$other['sort_order'], $ch['id']]);
                q('UPDATE chapters SET sort_order = ? WHERE id = ?', [$ch['sort_order'], $other['id']]);
            }
            break;
        case 'delete':
            q('DELETE FROM chapters WHERE id = ? AND course_id = ?', [$id, $courseId]);
            audit('chapter.delete', 'chapter', $id);
            foreach (q_all('SELECT DISTINCT user_id FROM course_enrollments WHERE course_id = ?', [$courseId]) as $r) recompute_progress((int) $r['user_id'], $courseId);
            flash('success', 'Chapitre supprimé avec ses leçons.');
            break;
    }
    back(['course' => $courseId]);
}
$courses = admin_courses_options();
$courseId = $courseId ?: (int) array_key_first($courses);
$course = q_one('SELECT * FROM courses WHERE id = ?', [$courseId]);
$chapters = $course ? q_all('SELECT ch.*, (SELECT COUNT(*) FROM lessons l WHERE l.chapter_id = ch.id) n, (SELECT COUNT(*) FROM questions q WHERE q.chapter_id = ch.id) qn FROM chapters ch WHERE course_id = ? ORDER BY sort_order, id', [$courseId]) : [];
$edit = input_int('edit') ? q_one('SELECT * FROM chapters WHERE id = ? AND course_id = ?', [input_int('edit'), $courseId]) : null;
$pageTitle = 'Chapitres';
$appArea = 'admin';
$activeApp = 'chapters';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="toolbar reveal" method="get"><?= sel('course', 'Formation', $courses, $courseId, ['data-autosubmit' => true]) ?></form>
<?php if ($course): ?>
<div class="grid-2">
  <section class="box reveal"><div class="box__head"><h2><?= e($course['code']) ?> | Programme</h2><span class="mono"><?= count($chapters) ?> chapitre(s)</span></div>
    <ol class="sortable-list">
    <?php if (!$chapters): ?><li><span></span><span class="dim">Aucun chapitre.</span><span></span></li><?php endif; ?>
    <?php foreach ($chapters as $i => $ch): ?>
      <li><span class="mono gold"><?= sprintf('%02d', $i + 1) ?></span>
        <span><strong><?= e($ch['title']) ?></strong><br><small class="dim"><?= (int) $ch['n'] ?> leçon(s) | <?= (int) $ch['qn'] ?> question(s)</small></span>
        <span class="actions">
          <?= action_btn('move', ['id' => $ch['id'], 'course_id' => $courseId, 'dir' => 'up'], 'Monter') ?><?= action_btn('move', ['id' => $ch['id'], 'course_id' => $courseId, 'dir' => 'down'], 'Descendre') ?>
          <a class="btn btn--sm" href="<?= e(url('admin/lessons.php', ['chapter' => $ch['id']])) ?>">Leçons</a>
          <a class="btn btn--sm" href="<?= e(url('admin/chapters.php', ['course' => $courseId, 'edit' => $ch['id']])) ?>">Modifier</a>
          <?= action_btn('delete', ['id' => $ch['id'], 'course_id' => $courseId], 'Supprimer', 'btn--sm btn--danger', 'Supprimer ce chapitre et toutes ses leçons ? Les questions associées seront détachées du chapitre.') ?>
        </span></li>
    <?php endforeach; ?>
    </ol>
  </section>
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2><?= $edit ? 'Modifier le chapitre' : 'Ajouter un chapitre' ?></h2></div>
    <div class="box__body">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="course_id" value="<?= $courseId ?>"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
      <?= fld('title', 'Titre du chapitre', $edit['title'] ?? '', 'text', ['required' => true, 'maxlength' => 190]) ?>
      <?= fld('summary', 'Résumé', $edit['summary'] ?? '', 'textarea', ['rows' => 4, 'maxlength' => 1000]) ?>
      <button class="btn btn--primary" type="submit">Enregistrer</button>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php';
