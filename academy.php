<?php
/** AFRICA'SPY : Académie : catalogue dynamique (catégories, filtres, recherche). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

$categories = categories_with_counts();
$domaine = input('domaine');
$niveau = input('niveau');
$search = input('q');
if ($domaine !== '' && !in_array($domaine, array_column($categories, 'slug'), true)) {
    $domaine = '';
}
$courses = find_courses(['category' => $domaine, 'level' => $niveau, 'q' => $search]);
$total = (int) q_val("SELECT COUNT(*) FROM courses WHERE status = 'published'");

$pageTitle = 'Académie de formation';
$pageDescription = "Catalogue des formations AFRICA'SPY : fondements du renseignement, renseignement humain, contre-espionnage, renseignement numérique, outils, intelligence stratégique.";
$activeNav = 'academy';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
  <section class="page-hero">
    <div class="page-hero__bg" style="background-image:url('<?= e(url('assets/images/cyber-lab.webp')) ?>')" data-parallax="0.1"></div>
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><span>Académie</span></nav>
      <p class="eyebrow">Catalogue | <?= (int) $total ?> formations | <?= count($categories) ?> domaines</p>
      <h1 data-split>Académie de formation</h1>
      <p class="reveal">Sept domaines, des parcours progressifs du niveau fondamental au niveau avancé. Chaque formation comprend des leçons développées, un briefing vidéo, un support PDF, des exercices, des études de cas, des QCM par chapitre et un examen final certifiant.</p>
    </div>
  </section>

  <section class="section section--tight">
    <div class="container">
      <form class="filters" data-catalog data-domaine="<?= e($domaine) ?>" action="<?= e(url('academy.php')) ?>" method="get" role="search">
        <div class="chips" role="group" aria-label="Filtrer par domaine">
          <button class="chip" type="button" data-domain="" aria-pressed="<?= $domaine === '' ? 'true' : 'false' ?>">Tous <b><?= (int) $total ?></b></button>
          <?php foreach ($categories as $cat): ?>
          <button class="chip" type="button" data-domain="<?= e($cat['slug']) ?>" aria-pressed="<?= $domaine === $cat['slug'] ? 'true' : 'false' ?>" title="<?= e($cat['name']) ?>"><?= e($cat['code']) ?> | <?= e(explode(' ', $cat['name'])[0] === 'Espionnage' ? 'Renseignement humain' : (mb_strlen($cat['name']) > 22 ? explode(' et ', $cat['name'])[0] : $cat['name'])) ?> <b><?= (int) $cat['course_count'] ?></b></button>
          <?php endforeach; ?>
          <input type="hidden" name="domaine" value="<?= e($domaine) ?>">
        </div>
        <label class="sr-only" for="niveau">Niveau</label>
        <select class="select" id="niveau" name="niveau">
          <option value="">Tous niveaux</option>
          <?php foreach (['fondamental', 'intermediaire', 'avance'] as $lv): ?>
          <option value="<?= $lv ?>" <?= $niveau === $lv ? 'selected' : '' ?>><?= e(level_label($lv)) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
          <label class="sr-only" for="q">Rechercher</label>
          <input class="input" id="q" name="q" type="search" placeholder="Rechercher une formation, un chapitre..." value="<?= e($search) ?>" maxlength="80">
        </div>
      </form>
      <div class="catalog-status"><span data-catalog-status aria-live="polite"><?= e(catalog_label(count($courses))) ?></span><span class="mono">Tri : domaine | progression pédagogique</span></div>
      <div id="catalog-results"><?= render_catalog($courses, $categories) ?></div>
    </div>
  </section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
