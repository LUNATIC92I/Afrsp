<?php
/** Administration : gestion des formations. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';

$admin = require_permission('courses.manage');

if (is_post()) {
    verify_csrf();
    $action = input('action');
    $id = input_int('id');
    if ($action === 'save') {
        $d = [
            'category_id' => input_int('category_id'), 'code' => strtoupper(input('code')), 'title' => input('title'), 'subtitle' => input('subtitle'),
            'summary' => input('summary'), 'description' => sanitize_html($_POST['description'] ?? ''), 'objectives' => input('objectives'),
            'skills' => input('skills'), 'prerequisites' => input('prerequisites'), 'level' => input('level'), 'duration_hours' => input('duration_hours'),
            'status' => input('status'), 'sort_order' => input('sort_order', '0'), 'slug' => input('slug'),
            'price_xof' => input('price_xof', '0'), 'promo_price_xof' => input('promo_price_xof'),
        ];
        $errors = validate($d, [
            'category_id' => 'required', 'code' => 'required|max:20', 'title' => 'required|min:4|max:190', 'subtitle' => 'max:255',
            'summary' => 'required|min:20|max:2000', 'level' => 'required|in:fondamental,intermediaire,avance', 'duration_hours' => 'required|int',
            'status' => 'required|in:draft,published', 'sort_order' => 'int', 'slug' => 'max:160',
            'price_xof' => 'required|int', 'promo_price_xof' => 'int',
        ], ['category_id' => 'Catégorie', 'code' => 'Code', 'title' => 'Titre', 'summary' => 'Résumé', 'level' => 'Niveau', 'duration_hours' => 'Durée', 'status' => 'Statut']);
        if (!preg_match('/^[A-Z0-9\-]{2,20}$/', $d['code'])) $errors['code'] = 'Code : majuscules, chiffres et tirets uniquement (ex. AFS-A301).';
        if ((int) $d['price_xof'] < 0 || ($d['promo_price_xof'] !== '' && ((int) $d['promo_price_xof'] < 0 || (int) $d['promo_price_xof'] > (int) $d['price_xof']))) $errors['price'] = 'Le prix promotionnel doit être positif et inférieur ou égal au prix de référence.';
        if ((int) $d['price_xof'] % 5 !== 0 || ($d['promo_price_xof'] !== '' && (int) $d['promo_price_xof'] % 5 !== 0)) $errors['price5'] = 'Les montants en FCFA doivent être des multiples de 5.';
        $d['slug'] = slugify($d['slug'] !== '' ? $d['slug'] : $d['title']);
        if (q_val('SELECT 1 FROM courses WHERE code = ? AND id <> ?', [$d['code'], $id])) $errors['code'] = 'Ce code est déjà utilisé.';
        if (q_val('SELECT 1 FROM courses WHERE slug = ? AND id <> ?', [$d['slug'], $id])) $errors['slug'] = 'Cette adresse (slug) est déjà utilisée.';
        if (!q_val('SELECT 1 FROM categories WHERE id = ?', [$d['category_id']])) $errors['category_id'] = 'Catégorie invalide.';
        $coverId = input_int('cover_media_id') ?: null;
        if (!$errors && !empty($_FILES['cover']['name'])) {
            $up = handle_upload($_FILES['cover'], 'image', 'Couverture : ' . $d['title'], input('cover_attribution') ?: null);
            if (is_string($up)) $errors['cover'] = $up; else $coverId = $up['id'];
        }
        if ($errors) {
            flash('error', implode(' ', $errors));
            set_old($_POST);
            back(['action' => $id ? 'edit' : 'new', 'id' => $id ?: null]);
        }
        $vals = [$d['category_id'], $d['code'], $d['slug'], $d['title'], $d['subtitle'] ?: null, $d['summary'], $d['description'], $d['objectives'], $d['skills'],
            $d['prerequisites'], $d['level'], (int) $d['duration_hours'], (int) $d['price_xof'], $d['promo_price_xof'] === '' ? null : (int) $d['promo_price_xof'], $coverId, isset($_POST['is_featured']) ? 1 : 0, $d['status'], (int) $d['sort_order']];
        if ($id) {
            q('UPDATE courses SET category_id=?, code=?, slug=?, title=?, subtitle=?, summary=?, description=?, objectives=?, skills=?, prerequisites=?, level=?, duration_hours=?, price_xof=?, promo_price_xof=?, cover_media_id=?, is_featured=?, status=?, sort_order=? WHERE id=?', array_merge($vals, [$id]));
            audit('course.update', 'course', $id, $d['code']);
            flash('success', 'Formation mise à jour.');
        } else {
            q('INSERT INTO courses (category_id, code, slug, title, subtitle, summary, description, objectives, skills, prerequisites, level, duration_hours, price_xof, promo_price_xof, cover_media_id, is_featured, status, sort_order, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', array_merge($vals, [$admin['id']]));
            $id = (int) db()->lastInsertId();
            q('INSERT INTO exams (course_id, title, instructions, pass_mark, max_attempts, question_count, time_limit_min) VALUES (?, ?, ?, 70, 3, 10, 25)',
                [$id, 'Examen final : ' . $d['title'], 'Questionnaire à choix multiples tiré au sort dans la banque de questions. Une seule bonne réponse par question.']);
            audit('course.create', 'course', $id, $d['code']);
            flash('success', 'Formation créée (brouillon conseillé tant que les chapitres ne sont pas rédigés). Ajoutez maintenant ses chapitres.');
        }
        back(['action' => 'edit', 'id' => $id]);
    }
    if ($action === 'toggle' && $id) {
        q("UPDATE courses SET status = IF(status = 'published', 'draft', 'published') WHERE id = ?", [$id]);
        audit('course.toggle', 'course', $id);
        flash('success', 'Statut de publication modifié.');
        back();
    }
    if ($action === 'delete' && $id) {
        $n = (int) q_val('SELECT COUNT(*) FROM certificates WHERE course_id = ?', [$id]);
        if ($n > 0) {
            flash('error', "Suppression impossible : $n certificat(s) ont été délivrés pour cette formation. Dépubliez-la plutôt.");
        } else {
            $code = q_val('SELECT code FROM courses WHERE id = ?', [$id]);
            q('DELETE FROM courses WHERE id = ?', [$id]);
            audit('course.delete', 'course', $id, (string) $code);
            flash('success', 'Formation supprimée avec ses chapitres, leçons et examen.');
        }
        back();
    }
    back();
}

$action = input('action');
$appArea = 'admin';
$activeApp = 'courses';
$cats = array_column(q_all('SELECT id, CONCAT(code, " | ", name) n FROM categories ORDER BY sort_order'), 'n', 'id');

if ($action === 'new' || $action === 'edit') {
    $c = $action === 'edit' ? q_one('SELECT * FROM courses WHERE id = ?', [input_int('id')]) : null;
    if ($action === 'edit' && !$c) render_error_page(404, 'Formation introuvable', 'Cet élément n\'existe pas.');
    $v = static fn ($k, $def = '') => old($k, (string) ($c[$k] ?? $def));
    $images = ['' => 'Couverture du domaine (par défaut)'] + array_column(q_all("SELECT id, title FROM media WHERE kind = 'image' ORDER BY id DESC"), 'title', 'id');
    $pageTitle = $c ? 'Modifier : ' . $c['code'] : 'Nouvelle formation';
    $crumb = 'Formations';
    require APP_ROOT . '/includes/app-header.php'; ?>
<?php if ($c): ?>
<div class="toolbar reveal">
  <a class="btn btn--sm" href="<?= e(url('admin/chapters.php', ['course' => $c['id']])) ?>">Chapitres</a>
  <a class="btn btn--sm" href="<?= e(url('admin/exams.php', ['course' => $c['id']])) ?>">Examen &amp; QCM</a>
  <a class="btn btn--sm btn--ghost" href="<?= e(url('course.php', ['slug' => $c['slug']])) ?>" target="_blank" rel="noopener">Voir la fiche publique</a>
  <a class="btn btn--sm btn--ghost" href="<?= e(url('learner/learning.php', ['course' => $c['id']])) ?>">Aperçu du cours</a>
</div>
<?php endif; ?>
<form class="box reveal" method="post" enctype="multipart/form-data" novalidate>
  <div class="box__head"><h2><?= $c ? 'Fiche formation' : 'Créer une formation' ?></h2><span class="mono"><?= $c ? e($c['code']) : 'Nouveau' ?></span></div>
  <div class="box__body form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($c['id'] ?? 0) ?>">
    <?= sel('category_id', 'Domaine / catégorie', $cats, $v('category_id')) ?>
    <?= fld('code', 'Code de classification', $v('code'), 'text', ['required' => true, 'maxlength' => 20, 'placeholder' => 'AFS-A301']) ?>
    <?= fld('title', 'Titre', $v('title'), 'text', ['required' => true, 'maxlength' => 190, 'data-full' => true]) ?>
    <?= fld('subtitle', 'Sous-titre', $v('subtitle'), 'text', ['maxlength' => 255, 'data-full' => true]) ?>
    <?= fld('slug', 'Adresse (slug)', $v('slug'), 'text', ['maxlength' => 160], 'Laisser vide pour la générer depuis le titre.') ?>
    <?= sel('level', 'Niveau', ['fondamental' => 'Fondamental', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé'], $v('level', 'fondamental')) ?>
    <?= fld('duration_hours', 'Durée indicative (heures)', $v('duration_hours', '10'), 'number', ['min' => 1, 'max' => 500]) ?>
    <?= fld('price_xof', 'Prix de référence (FCFA)', $v('price_xof', '900000'), 'number', ['min' => 0, 'step' => 5, 'required' => true], '0 = formation gratuite.') ?>
    <?= fld('promo_price_xof', 'Prix promotionnel (FCFA)', $v('promo_price_xof', '170000'), 'number', ['min' => 0, 'step' => 5], 'Laisser vide pour aucune promotion.') ?>
    <?= fld('sort_order', 'Ordre d\'affichage', $v('sort_order', '0'), 'number') ?>
    <?= sel('status', 'Statut', ['draft' => 'Brouillon (invisible)', 'published' => 'Publiée'], $v('status', 'draft')) ?>
    <div class="field"><label class="check" style="margin-top:30px"><input type="checkbox" name="is_featured" value="1" <?= ($c['is_featured'] ?? 0) ? 'checked' : '' ?>> Mettre à la une (page d'accueil)</label></div>
    <?= fld('summary', 'Présentation courte', $v('summary'), 'textarea', ['required' => true, 'maxlength' => 2000, 'data-full' => true, 'rows' => 3]) ?>
    <?= fld('description', 'Présentation générale (HTML autorisé : p, h3, ul, strong, table...)', $v('description'), 'textarea', ['data-full' => true, 'data-code' => true, 'rows' => 8]) ?>
    <?= fld('objectives', 'Objectifs pédagogiques (un par ligne)', $v('objectives'), 'textarea', ['rows' => 6]) ?>
    <?= fld('skills', 'Compétences à acquérir (une par ligne)', $v('skills'), 'textarea', ['rows' => 6]) ?>
    <?= fld('prerequisites', 'Prérequis (un par ligne)', $v('prerequisites'), 'textarea', ['rows' => 4, 'data-full' => true]) ?>
    <?= sel('cover_media_id', 'Image de couverture existante', $images, $v('cover_media_id')) ?>
    <?= fld('cover', 'Ou téléverser une image (JPG, PNG, WebP | 4 Mo max.)', '', 'file', ['accept' => 'image/jpeg,image/png,image/webp']) ?>
    <?= fld('cover_attribution', 'Crédit / licence de l\'image (si applicable)', '', 'text', ['maxlength' => 255, 'data-full' => true]) ?>
    <div class="full flex"><button class="btn btn--primary" type="submit">Enregistrer</button><a class="btn" href="<?= e(url('admin/courses.php')) ?>">Annuler</a></div>
  </div>
</form>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}

$q = input('q');
$rows = q_all('SELECT c.*, cat.code AS cat_code,
                      (SELECT COUNT(*) FROM chapters ch WHERE ch.course_id = c.id) chapters,
                      (SELECT COUNT(*) FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = c.id) lessons,
                      (SELECT COUNT(*) FROM questions qq JOIN exams e ON e.id = qq.exam_id WHERE e.course_id = c.id) questions,
                      (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = c.id) enrolled
                 FROM courses c JOIN categories cat ON cat.id = c.category_id
                WHERE (? = "" OR c.title LIKE ? OR c.code LIKE ?) ORDER BY cat.sort_order, c.sort_order, c.id', [$q, "%$q%", "%$q%"]);
$pageTitle = 'Formations';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="toolbar reveal">
  <form class="toolbar grow" method="get"><input class="input grow" name="q" value="<?= e($q) ?>" placeholder="Rechercher par titre ou code"><button class="btn btn--sm" type="submit">Filtrer</button></form>
  <a class="btn btn--primary btn--sm" href="<?= e(url('admin/courses.php', ['action' => 'new'])) ?>">+ Nouvelle formation</a>
</div>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Formation</th><th class="num">Prix</th><th>Niveau</th><th class="num">Chap.</th><th class="num">Leçons</th><th class="num">QCM</th><th class="num">Inscrits</th><th>Statut</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><span class="mono cyan" style="font-size:.72rem"><?= e($r['code']) ?></span><?= $r['is_featured'] ? ' <span class="tag tag--gold" style="font-size:.6rem;padding:1px 6px">À la une</span>' : '' ?><br><strong><?= e($r['title']) ?></strong></td>
      <td class="num"><?php if ($r['promo_price_xof'] !== null && $r['promo_price_xof'] < $r['price_xof']): ?><s class="dim"><?= number_format((int) $r['price_xof'], 0, ',', ' ') ?></s><br><?php endif; ?><strong class="gold"><?= number_format((int) ($r['promo_price_xof'] ?? $r['price_xof']), 0, ',', ' ') ?></strong> <small>FCFA</small></td>
      <td><?= e(level_label($r['level'])) ?></td>
      <td class="num"><?= (int) $r['chapters'] ?></td><td class="num"><?= (int) $r['lessons'] ?></td><td class="num"><?= (int) $r['questions'] ?></td><td class="num"><?= (int) $r['enrolled'] ?></td>
      <td><?= $r['status'] === 'published' ? '<span class="status-pill status-pill--on">Publiée</span>' : '<span class="status-pill status-pill--off">Brouillon</span>' ?></td>
      <td><div class="actions">
        <a class="btn btn--sm" href="<?= e(url('admin/courses.php', ['action' => 'edit', 'id' => $r['id']])) ?>">Modifier</a>
        <a class="btn btn--sm" href="<?= e(url('admin/chapters.php', ['course' => $r['id']])) ?>">Chapitres</a>
        <?= action_btn('toggle', ['id' => $r['id']], $r['status'] === 'published' ? 'Dépublier' : 'Publier') ?>
        <?= action_btn('delete', ['id' => $r['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer définitivement cette formation, ses chapitres, leçons, questions et inscriptions ?') ?>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></section>
<?php require APP_ROOT . '/includes/app-footer.php';
