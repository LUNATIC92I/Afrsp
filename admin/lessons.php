<?php
/** Administration — leçons d'un chapitre (texte, vidéo, PDF, exercice, étude de cas). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('courses.manage');

$recompute = static function (int $chapterId): void {
    $cid = (int) q_val('SELECT course_id FROM chapters WHERE id = ?', [$chapterId]);
    foreach (q_all('SELECT user_id FROM course_enrollments WHERE course_id = ?', [$cid]) as $r) {
        recompute_progress((int) $r['user_id'], $cid);
    }
};

if (is_post()) {
    verify_csrf();
    $chapterId = input_int('chapter_id');
    $id = input_int('id');
    if (!q_val('SELECT 1 FROM chapters WHERE id = ?', [$chapterId])) { flash('error', 'Chapitre invalide.'); redirect('admin/chapters.php'); }
    $action = input('action');
    if ($action === 'save') {
        $d = ['title' => input('title'), 'type' => input('type'), 'duration_minutes' => input('duration_minutes', '15'), 'video_url' => input('video_url')];
        $err = validate($d, ['title' => 'required|max:190', 'type' => 'required|in:text,video,document,exercise,case_study', 'duration_minutes' => 'required|int', 'video_url' => 'url|max:255'],
            ['title' => 'Titre', 'type' => 'Type', 'duration_minutes' => 'Durée', 'video_url' => 'URL vidéo']);
        if ($d['video_url'] !== '' && !preg_match('~^https://(www\.)?(youtube\.com|youtu\.be|vimeo\.com)/~', $d['video_url'])) $err['video_url'] = 'Seules les URL YouTube et Vimeo (https) sont acceptées.';
        $mediaId = input_int('media_id') ?: null;
        if (!$err && !empty($_FILES['file']['name'])) {
            $kind = $d['type'] === 'video' ? 'video' : 'document';
            $up = handle_upload($_FILES['file'], $kind, $d['title'], input('attribution') ?: null);
            if (is_string($up)) $err['file'] = $up; else $mediaId = $up['id'];
        }
        if ($d['type'] === 'video' && !$mediaId && $d['video_url'] === '') $err['media'] = 'Une leçon vidéo nécessite un fichier vidéo ou une URL YouTube/Vimeo.';
        if ($d['type'] === 'document' && !$mediaId) $err['media'] = 'Une leçon « Support PDF » nécessite un document PDF.';
        if ($err) { flash('error', implode(' ', $err)); set_old($_POST); back(['chapter' => $chapterId, 'edit' => $id ?: 'new']); }
        $vals = [$d['title'], $d['type'], sanitize_html($_POST['content'] ?? ''), $d['type'] === 'video' ? ($d['video_url'] ?: null) : null,
            in_array($d['type'], ['video', 'document'], true) ? $mediaId : null, (int) $d['duration_minutes'], isset($_POST['is_downloadable']) ? 1 : 0];
        if ($id) {
            q('UPDATE lessons SET title=?, type=?, content=?, video_url=?, media_id=?, duration_minutes=?, is_downloadable=? WHERE id=? AND chapter_id=?', array_merge($vals, [$id, $chapterId]));
            audit('lesson.update', 'lesson', $id, $d['title']);
        } else {
            $pos = (int) q_val('SELECT COALESCE(MAX(sort_order),0)+1 FROM lessons WHERE chapter_id = ?', [$chapterId]);
            q('INSERT INTO lessons (title, type, content, video_url, media_id, duration_minutes, is_downloadable, chapter_id, sort_order) VALUES (?,?,?,?,?,?,?,?,?)', array_merge($vals, [$chapterId, $pos]));
            $id = (int) db()->lastInsertId();
            audit('lesson.create', 'lesson', $id, $d['title']);
            $recompute($chapterId);
        }
        flash('success', 'Leçon enregistrée.');
        back(['chapter' => $chapterId, 'edit' => $id]);
    }
    if ($action === 'move') {
        $l = q_one('SELECT id, sort_order FROM lessons WHERE id = ? AND chapter_id = ?', [$id, $chapterId]);
        $dir = input('dir') === 'up' ? '<' : '>';
        $o = $l ? q_one("SELECT id, sort_order FROM lessons WHERE chapter_id = ? AND sort_order $dir ? ORDER BY sort_order " . ($dir === '<' ? 'DESC' : 'ASC') . ' LIMIT 1', [$chapterId, $l['sort_order']]) : null;
        if ($l && $o) {
            q('UPDATE lessons SET sort_order = ? WHERE id = ?', [$o['sort_order'], $l['id']]);
            q('UPDATE lessons SET sort_order = ? WHERE id = ?', [$l['sort_order'], $o['id']]);
        }
    }
    if ($action === 'delete') {
        q('DELETE FROM lessons WHERE id = ? AND chapter_id = ?', [$id, $chapterId]);
        audit('lesson.delete', 'lesson', $id);
        $recompute($chapterId);
        flash('success', 'Leçon supprimée.');
    }
    back(['chapter' => $chapterId]);
}

$chapter = q_one('SELECT ch.*, c.code, c.title AS course_title, c.id AS cid FROM chapters ch JOIN courses c ON c.id = ch.course_id WHERE ch.id = ?', [input_int('chapter')]);
if (!$chapter) {
    // Sans chapitre : liste de sélection
    $chs = q_all('SELECT ch.id, ch.title, c.code FROM chapters ch JOIN courses c ON c.id = ch.course_id ORDER BY c.sort_order, ch.sort_order');
    $pageTitle = 'Leçons & médias';
    $appArea = 'admin';
    $activeApp = 'lessons';
    require APP_ROOT . '/includes/app-header.php'; ?>
<section class="box reveal"><div class="box__head"><h2>Choisir un chapitre</h2></div><ul class="feed">
  <?php foreach ($chs as $c): ?><li><span><span class="mono cyan"><?= e($c['code']) ?></span> · <?= e($c['title']) ?></span><a class="link-arrow" href="<?= e(url('admin/lessons.php', ['chapter' => $c['id']])) ?>">Leçons</a></li><?php endforeach; ?>
</ul></section>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}
$lessons = q_all('SELECT l.*, m.original_name FROM lessons l LEFT JOIN media m ON m.id = l.media_id WHERE l.chapter_id = ? ORDER BY l.sort_order, l.id', [$chapter['id']]);
$editId = input('edit');
$edit = ctype_digit($editId) ? q_one('SELECT * FROM lessons WHERE id = ? AND chapter_id = ?', [(int) $editId, $chapter['id']]) : null;
$showForm = $edit || $editId === 'new';
$mediaOpts = ['' => '— Aucun / nouveau fichier ci-dessous —'] + array_column(q_all("SELECT id, CONCAT(UPPER(kind), ' · ', title) t FROM media WHERE kind IN ('video','document') ORDER BY id DESC"), 't', 'id');
$v = static fn ($k, $d = '') => old($k, (string) ($edit[$k] ?? $d));

$pageTitle = 'Leçons';
$crumb = $chapter['code'] . ' › ' . $chapter['title'];
$appArea = 'admin';
$activeApp = 'lessons';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="toolbar reveal">
  <a class="btn btn--sm" href="<?= e(url('admin/chapters.php', ['course' => $chapter['cid']])) ?>">← Chapitres de <?= e($chapter['code']) ?></a>
  <a class="btn btn--sm btn--primary" href="<?= e(url('admin/lessons.php', ['chapter' => $chapter['id'], 'edit' => 'new'])) ?>">+ Nouvelle leçon</a>
</div>
<section class="box reveal"><div class="box__head"><h2><?= e($chapter['title']) ?></h2><span class="mono"><?= count($lessons) ?> élément(s)</span></div>
  <ol class="sortable-list">
  <?php if (!$lessons): ?><li><span></span><span class="dim">Aucune leçon dans ce chapitre.</span><span></span></li><?php endif; ?>
  <?php foreach ($lessons as $i => $l): ?>
    <li><span class="mono gold"><?= sprintf('%02d', $i + 1) ?></span>
      <span><strong><?= e($l['title']) ?></strong><br><small class="dim"><?= e(lesson_type_label($l['type'])) ?> · <?= (int) $l['duration_minutes'] ?> min<?= $l['original_name'] ? ' · ' . e($l['original_name']) : '' ?><?= $l['video_url'] ? ' · ' . e($l['video_url']) : '' ?></small></span>
      <span class="actions">
        <?= action_btn('move', ['id' => $l['id'], 'chapter_id' => $chapter['id'], 'dir' => 'up'], '↑') ?><?= action_btn('move', ['id' => $l['id'], 'chapter_id' => $chapter['id'], 'dir' => 'down'], '↓') ?>
        <a class="btn btn--sm" href="<?= e(url('admin/lessons.php', ['chapter' => $chapter['id'], 'edit' => $l['id']])) ?>">Modifier</a>
        <?= action_btn('delete', ['id' => $l['id'], 'chapter_id' => $chapter['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer cette leçon ? La progression des apprenants sera recalculée.') ?>
      </span></li>
  <?php endforeach; ?>
  </ol>
</section>
<?php if ($showForm): ?>
<form class="box reveal" method="post" enctype="multipart/form-data" novalidate>
  <div class="box__head"><h2><?= $edit ? 'Modifier la leçon' : 'Nouvelle leçon' ?></h2></div>
  <div class="box__body form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="chapter_id" value="<?= (int) $chapter['id'] ?>"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <?= fld('title', 'Titre', $v('title'), 'text', ['required' => true, 'maxlength' => 190, 'data-full' => true]) ?>
    <?= sel('type', 'Type de leçon', ['text' => 'Leçon (texte)', 'video' => 'Vidéo', 'document' => 'Support PDF', 'exercise' => 'Exercice pratique', 'case_study' => 'Étude de cas'], $v('type', 'text'), ['data-lesson-type' => true]) ?>
    <?= fld('duration_minutes', 'Durée estimée (minutes)', $v('duration_minutes', '15'), 'number', ['min' => 1, 'max' => 600]) ?>
    <div class="full" data-show-for="video"><?= fld('video_url', 'URL YouTube ou Vimeo (facultatif si fichier)', $v('video_url'), 'url', ['maxlength' => 255, 'placeholder' => 'https://www.youtube.com/watch?v=…']) ?></div>
    <div data-show-for="video,document"><?= sel('media_id', 'Média existant', $mediaOpts, $v('media_id')) ?></div>
    <div data-show-for="video,document"><?= fld('file', 'Ou téléverser (PDF ≤ 25 Mo · MP4/WebM ≤ 200 Mo)', '', 'file', ['accept' => 'application/pdf,video/mp4,video/webm']) ?></div>
    <div class="full" data-show-for="video,document"><?= fld('attribution', 'Crédit / licence du média (si applicable)', '', 'text', ['maxlength' => 255]) ?></div>
    <div class="full" data-show-for="document"><label class="check"><input type="checkbox" name="is_downloadable" value="1" <?= ($edit['is_downloadable'] ?? 1) ? 'checked' : '' ?>> Téléchargement autorisé pour les apprenants inscrits</label></div>
    <?= fld('content', 'Contenu (HTML : h3, p, ul, ol, table, blockquote, div class="callout|note|warning")', $v('content'), 'textarea', ['data-full' => true, 'data-code' => true, 'rows' => 18]) ?>
    <div class="full flex"><button class="btn btn--primary" type="submit">Enregistrer la leçon</button><a class="btn" href="<?= e(url('admin/lessons.php', ['chapter' => $chapter['id']])) ?>">Fermer</a></div>
  </div>
</form>
<?php endif; ?>
<?php require APP_ROOT . '/includes/app-footer.php';
