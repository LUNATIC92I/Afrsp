<?php
/** Administration : médiathèque (images, documents PDF, vidéos). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('media.manage');

if (is_post()) {
    verify_csrf();
    if (input('action') === 'upload') {
        $kind = input('kind');
        $res = in_array($kind, ['image', 'document', 'video'], true) ? handle_upload($_FILES['file'] ?? [], $kind, input('title'), input('attribution') ?: null) : 'Type invalide.';
        flash(is_string($res) ? 'error' : 'success', is_string($res) ? $res : 'Fichier ajouté à la médiathèque.');
    }
    if (input('action') === 'delete') {
        $m = q_one('SELECT * FROM media WHERE id = ?', [input_int('id')]);
        $used = $m ? (int) q_val('SELECT (SELECT COUNT(*) FROM lessons WHERE media_id = ?) + (SELECT COUNT(*) FROM courses WHERE cover_media_id = ?)', [$m['id'], $m['id']]) : 0;
        if (!$m) {
            flash('error', 'Média introuvable.');
        } elseif ($used) {
            flash('error', "Ce média est utilisé par $used leçon(s) ou formation(s). Retirez-le d'abord.");
        } else {
            $path = media_path($m);
            q('DELETE FROM media WHERE id = ?', [$m['id']]);
            if (is_file($path)) @unlink($path);
            audit('media.delete', 'media', (int) $m['id'], $m['original_name']);
            flash('success', 'Média supprimé.');
        }
    }
    back(array_filter(['kind' => input('filter') ?: null]));
}
$kind = input('kind');
$rows = q_all('SELECT m.*, (SELECT COUNT(*) FROM lessons l WHERE l.media_id = m.id) + (SELECT COUNT(*) FROM courses c WHERE c.cover_media_id = m.id) used FROM media m WHERE (? = "" OR m.kind = ?) ORDER BY m.id DESC', [$kind, $kind]);
$pageTitle = 'Médiathèque';
$appArea = 'admin';
$activeApp = 'media';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Fichiers</h2>
      <form method="get" class="toolbar"><select class="select" name="kind" data-autosubmit><option value="">Tous</option><?php foreach (['image' => 'Images', 'document' => 'Documents PDF', 'video' => 'Vidéos'] as $k => $l): ?><option value="<?= $k ?>" <?= $kind === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></form></div>
    <div class="table-wrap"><table class="table"><thead><tr><th>Aperçu</th><th>Titre</th><th>Type</th><th class="num">Taille</th><th class="num">Usages</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $m): ?>
      <tr>
        <td><?php if ($m['kind'] === 'image'): ?><img src="<?= e(url('uploads/courses/' . rawurlencode($m['stored_name']))) ?>" alt="" style="width:64px;height:40px;object-fit:cover;border-radius:4px"><?php else: ?><span class="tag"><?= $m['kind'] === 'video' ? 'MP4' : 'PDF' ?></span><?php endif; ?></td>
        <td><strong><?= e($m['title']) ?></strong><br><small class="dim"><?= e($m['original_name']) ?><?= $m['attribution'] ? ' | © ' . e($m['attribution']) : '' ?></small></td>
        <td><?= e($m['mime_type']) ?></td><td class="num"><?= e(fmt_bytes((int) $m['size_bytes'])) ?></td><td class="num"><?= (int) $m['used'] ?></td>
        <td><div class="actions"><?php if ($m['kind'] !== 'image'): ?><a class="btn btn--sm" href="<?= e(url('download.php', ['media' => $m['id']])) ?>" target="_blank" rel="noopener" data-no-transition>Ouvrir</a><?php endif; ?><?= action_btn('delete', ['id' => $m['id'], 'filter' => $kind], 'Supprimer', 'btn--sm btn--danger', 'Supprimer définitivement ce fichier ?') ?></div></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  </section>
  <form class="box reveal" method="post" enctype="multipart/form-data">
    <div class="box__head"><h2>Téléverser</h2></div>
    <div class="box__body">
      <?= csrf_field() ?><input type="hidden" name="action" value="upload">
      <?= sel('kind', 'Type', ['image' => 'Image (JPG, PNG, WebP | 4 Mo)', 'document' => 'Document PDF (25 Mo)', 'video' => 'Vidéo MP4/WebM (200 Mo)'], 'image') ?>
      <?= fld('title', 'Titre', '', 'text', ['maxlength' => 190]) ?>
      <?= fld('file', 'Fichier', '', 'file', ['required' => true]) ?>
      <?= fld('attribution', 'Crédit / licence (à conserver pour les visuels tiers)', '', 'text', ['maxlength' => 255]) ?>
      <p class="disclaimer">Le type réel du fichier est vérifié côté serveur (signature MIME). Les documents et vidéos ne sont accessibles qu'aux apprenants inscrits, via un lien protégé.</p>
      <button class="btn btn--primary" type="submit">Téléverser</button>
    </div>
  </form>
</div>
<?php require APP_ROOT . '/includes/app-footer.php';
