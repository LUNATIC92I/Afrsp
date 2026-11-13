<?php
/** Administration : catégories (domaines / modules). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('courses.manage');

if (is_post()) {
    verify_csrf();
    $id = input_int('id');
    if (input('action') === 'save') {
        $d = ['code' => strtoupper(input('code')), 'name' => input('name'), 'description' => input('description'), 'accent' => input('accent', '#00C8FF'), 'sort_order' => input('sort_order', '0')];
        $err = validate($d, ['code' => 'required|max:10', 'name' => 'required|max:150', 'description' => 'max:1000', 'sort_order' => 'int'], ['code' => 'Code', 'name' => 'Nom']);
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $d['accent'])) $err['accent'] = 'Couleur invalide.';
        if (q_val('SELECT 1 FROM categories WHERE code = ? AND id <> ?', [$d['code'], $id])) $err['code'] = 'Code déjà utilisé.';
        $slug = slugify($d['name']);
        if (q_val('SELECT 1 FROM categories WHERE slug = ? AND id <> ?', [$slug, $id])) $slug .= '-' . strtolower($d['code']);
        if ($err) { flash('error', implode(' ', $err)); back(['edit' => $id ?: null]); }
        if ($id) {
            q('UPDATE categories SET code=?, slug=?, name=?, description=?, accent=?, sort_order=? WHERE id=?', [$d['code'], $slug, $d['name'], $d['description'], $d['accent'], (int) $d['sort_order'], $id]);
            audit('category.update', 'category', $id, $d['name']);
        } else {
            q('INSERT INTO categories (code, slug, name, description, accent, sort_order) VALUES (?,?,?,?,?,?)', [$d['code'], $slug, $d['name'], $d['description'], $d['accent'], (int) $d['sort_order']]);
            audit('category.create', 'category', (int) db()->lastInsertId(), $d['name']);
        }
        flash('success', 'Catégorie enregistrée.');
    } elseif (input('action') === 'delete' && $id) {
        if (q_val('SELECT COUNT(*) FROM courses WHERE category_id = ?', [$id])) {
            flash('error', 'Cette catégorie contient des formations : déplacez-les avant de la supprimer.');
        } else {
            q('DELETE FROM categories WHERE id = ?', [$id]);
            audit('category.delete', 'category', $id);
            flash('success', 'Catégorie supprimée.');
        }
    }
    back();
}
$edit = input_int('edit') ? q_one('SELECT * FROM categories WHERE id = ?', [input_int('edit')]) : null;
$rows = categories_with_counts();
$pageTitle = 'Catégories';
$appArea = 'admin';
$activeApp = 'categories';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <section class="box reveal"><div class="box__head"><h2>Domaines de formation</h2></div><div class="table-wrap"><table class="table">
    <thead><tr><th>Code</th><th>Nom</th><th class="num">Formations</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?>
      <tr><td><span class="mono" style="color:<?= e($r['accent']) ?>"><?= e($r['code']) ?></span></td><td><strong><?= e($r['name']) ?></strong><br><small class="dim"><?= e(str_limit((string) $r['description'], 90)) ?></small></td><td class="num"><?= (int) $r['course_count'] ?></td>
      <td><div class="actions"><a class="btn btn--sm" href="<?= e(url('admin/categories.php', ['edit' => $r['id']])) ?>">Modifier</a><?= action_btn('delete', ['id' => $r['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer cette catégorie ?') ?></div></td></tr>
    <?php endforeach; ?></tbody></table></div></section>
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2><?= $edit ? 'Modifier' : 'Nouvelle catégorie' ?></h2><?php if ($edit): ?><a class="link-arrow" href="<?= e(url('admin/categories.php')) ?>">Nouvelle</a><?php endif; ?></div>
    <div class="box__body">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
      <?= fld('code', 'Code (lettre du module)', $edit['code'] ?? '', 'text', ['required' => true, 'maxlength' => 10]) ?>
      <?= fld('name', 'Nom', $edit['name'] ?? '', 'text', ['required' => true, 'maxlength' => 150]) ?>
      <?= fld('description', 'Description', $edit['description'] ?? '', 'textarea', ['rows' => 4]) ?>
      <div class="form-row"><?= fld('accent', 'Couleur d\'accent', $edit['accent'] ?? '#00C8FF', 'color') ?><?= fld('sort_order', 'Ordre', (string) ($edit['sort_order'] ?? 0), 'number') ?></div>
      <button class="btn btn--primary" type="submit">Enregistrer</button>
    </div>
  </form>
</div>
<?php require APP_ROOT . '/includes/app-footer.php';
