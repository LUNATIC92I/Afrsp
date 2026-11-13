<?php
/** Administration : annonces aux apprenants. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
$me = require_permission('announcements.manage');

if (is_post()) {
    verify_csrf();
    $id = input_int('id');
    if (input('action') === 'save') {
        $d = ['title' => input('title'), 'body' => input('body'), 'level' => input('level'), 'audience' => input('audience')];
        $err = validate($d, ['title' => 'required|max:190', 'body' => 'required|max:4000', 'level' => 'required|in:info,important,alert', 'audience' => 'required|in:all,learners,public'], ['title' => 'Titre', 'body' => 'Message']);
        if ($err) { flash('error', implode(' ', $err)); back(['edit' => $id ?: null]); }
        $pub = isset($_POST['is_published']) ? 1 : 0;
        if ($id) {
            q('UPDATE announcements SET title=?, body=?, level=?, audience=?, is_published=? WHERE id=?', [$d['title'], $d['body'], $d['level'], $d['audience'], $pub, $id]);
            audit('announcement.update', 'announcement', $id, $d['title']);
        } else {
            q('INSERT INTO announcements (title, body, level, audience, is_published, published_at, created_by) VALUES (?,?,?,?,?,UTC_TIMESTAMP(),?)', [$d['title'], $d['body'], $d['level'], $d['audience'], $pub, $me['id']]);
            audit('announcement.create', 'announcement', (int) db()->lastInsertId(), $d['title']);
        }
        flash('success', 'Annonce enregistrée.');
    } elseif (input('action') === 'delete' && $id) {
        q('DELETE FROM announcements WHERE id = ?', [$id]);
        audit('announcement.delete', 'announcement', $id);
        flash('success', 'Annonce supprimée.');
    }
    back();
}
$edit = input_int('edit') ? q_one('SELECT * FROM announcements WHERE id = ?', [input_int('edit')]) : null;
$rows = q_all('SELECT a.*, (SELECT COUNT(*) FROM announcement_reads r WHERE r.announcement_id = a.id) read_count FROM announcements a ORDER BY a.published_at DESC');
$pageTitle = 'Annonces';
$appArea = 'admin';
$activeApp = 'announcements';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <section class="box reveal"><div class="box__head"><h2>Annonces publiées</h2></div><div class="table-wrap"><table class="table">
    <thead><tr><th>Titre</th><th>Niveau</th><th>Public</th><th class="num">Lectures</th><th>Date</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><strong><?= e($r['title']) ?></strong><?= $r['is_published'] ? '' : ' <span class="tag">brouillon</span>' ?><br><small class="dim"><?= e(str_limit($r['body'], 90)) ?></small></td>
        <td><?= e(['info' => 'Info', 'important' => 'Important', 'alert' => 'Alerte'][$r['level']]) ?></td><td><?= e(['all' => 'Tous', 'learners' => 'Apprenants', 'public' => 'Public'][$r['audience']]) ?></td>
        <td class="num"><?= (int) $r['read_count'] ?></td><td><?= e(fmt_date($r['published_at'], false)) ?></td>
        <td><div class="actions"><a class="btn btn--sm" href="<?= e(url('admin/announcements.php', ['edit' => $r['id']])) ?>">Modifier</a><?= action_btn('delete', ['id' => $r['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer cette annonce ?') ?></div></td></tr>
    <?php endforeach; ?></tbody></table></div></section>
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2><?= $edit ? 'Modifier' : 'Nouvelle annonce' ?></h2></div>
    <div class="box__body">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
      <?= fld('title', 'Titre', $edit['title'] ?? '', 'text', ['required' => true, 'maxlength' => 190]) ?>
      <?= fld('body', 'Message', $edit['body'] ?? '', 'textarea', ['required' => true, 'rows' => 6, 'maxlength' => 4000]) ?>
      <div class="form-row"><?= sel('level', 'Niveau', ['info' => 'Information', 'important' => 'Important', 'alert' => 'Alerte'], $edit['level'] ?? 'info') ?><?= sel('audience', 'Destinataires', ['learners' => 'Apprenants', 'all' => 'Tous les utilisateurs', 'public' => 'Public (archivage)'], $edit['audience'] ?? 'learners') ?></div>
      <label class="check mb-2"><input type="checkbox" name="is_published" value="1" <?= ($edit['is_published'] ?? 1) ? 'checked' : '' ?>> Publier immédiatement</label>
      <button class="btn btn--primary" type="submit">Enregistrer</button>
    </div>
  </form>
</div>
<?php require APP_ROOT . '/includes/app-footer.php';
