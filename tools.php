<?php
/** AFRICA'SPY — Module E : galerie des outils du renseignement. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/content.php';

$tools = tools_catalog();
$course = q_one("SELECT slug, title FROM courses WHERE code = 'AFS-E101' AND status = 'published'");
$pageTitle = 'Outils du renseignement';
$pageDescription = "Galerie des outils logiciels et des équipements de l'analyste, avec leurs usages légitimes et leur cadre légal.";
$activeNav = 'tools';
require APP_ROOT . '/includes/header.php';

$fiche = static function (array $t, string $kind): string {
    $rows = '<dt>Catégorie</dt><dd>' . e($t['cat']) . '</dd>'
        . ($kind === 'software' ? '<dt>Type</dt><dd>' . e($t['type']) . '</dd>' : '<dt>Caractéristiques</dt><dd>' . e($t['specs']) . '</dd>')
        . '<dt>Usages légitimes</dt><dd>' . e($t['use']) . '</dd><dt>Limites & cadre légal</dt><dd>' . e($t['limits']) . '</dd>';
    return '<div class="modal" id="tool-' . e($t['id']) . '" hidden role="dialog" aria-modal="true" aria-labelledby="tt-' . e($t['id']) . '"><div class="modal__box corners fiche">'
        . '<button class="modal__close" type="button" data-modal-close aria-label="Fermer">×</button>'
        . '<p class="modal__code">Fiche technique · ' . ($kind === 'software' ? 'Logiciel' : 'Équipement') . '</p>'
        . ($kind === 'equipment' ? '<div class="gear__fig" style="border-radius:8px;margin:14px 0"><img src="' . e(url('assets/images/equipment/' . $t['img'])) . '" alt=""></div>' : '')
        . '<h3 id="tt-' . e($t['id']) . '" style="font-size:1.6rem">' . e($t['name']) . '</h3><p>' . e($t['desc']) . '</p><dl>' . $rows . '</dl></div></div>';
};
?>
<main id="main">
  <section class="page-hero">
    <div class="page-hero__bg" style="background-image:url('<?= e(url('assets/images/cyber-lab.webp')) ?>')" data-parallax="0.1"></div>
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><a href="<?= e(url('academy.php')) ?>">Académie</a><span aria-hidden="true">/</span><span>Module E</span></nav>
      <p class="eyebrow">Module E // Galerie technique</p>
      <h1 data-split>Outils du renseignement</h1>
      <p class="reveal">Logiciels d'investigation et équipements professionnels présentés avec leur fonction, leurs usages légitimes et leurs limites légales. Sélectionnez un élément pour ouvrir sa fiche descriptive.</p>
      <?php if ($course): ?><a class="btn btn--ghost mt-1 reveal" href="<?= e(url('course.php', ['slug' => $course['slug']])) ?>">Suivre la formation associée</a><?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow">E.1 // Outils logiciels</p>
        <h2>Investigation numérique et sources ouvertes</h2>
        <p>Les noms cités sont des marques de leurs détenteurs respectifs ; ils sont présentés à titre pédagogique, sans affiliation.</p>
      </div>
      <div class="tool-grid" data-stagger="60">
        <?php foreach ($tools['software'] as $t): ?>
        <button class="tool" type="button" data-modal-open="tool-<?= e($t['id']) ?>">
          <span class="tool__glyph" aria-hidden="true"><?= e($t['glyph']) ?></span>
          <span class="tool__cat"><?= e($t['cat']) ?></span>
          <h3><?= e($t['name']) ?></h3>
          <p><?= e($t['desc']) ?></p>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section scanner" style="padding-top:0">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow">E.2 // Outils physiques et matériels</p>
        <h2>Équipements de l'analyste de terrain</h2>
        <p>Illustrations techniques originales. Aucun dispositif clandestin, aucun plan de fabrication, aucune méthode d'interception n'est présenté.</p>
      </div>
      <div class="gear-grid" data-stagger="90">
        <?php foreach ($tools['equipment'] as $t): ?>
        <button class="gear" type="button" data-modal-open="tool-<?= e($t['id']) ?>">
          <span class="gear__fig"><img src="<?= e(url('assets/images/equipment/' . $t['img'])) ?>" alt="" loading="lazy" width="320" height="220"></span>
          <span class="gear__body"><span class="tool__cat"><?= e($t['cat']) ?></span><h3><?= e($t['name']) ?></h3><p><?= e($t['desc']) ?></p></span>
        </button>
        <?php endforeach; ?>
      </div>
      <div class="charter mt-3 reveal">
        <span class="charter__stamp">RÈGLE D'OR</span>
        <p class="mb-0"><strong>Mandat, légalité, proportionnalité.</strong> Avant toute utilisation d'un outil, rédigez une note de cadrage : finalité, autorisation écrite, périmètre, base légale, choix de l'outil le moins intrusif et durée de conservation des données. Aucun de ces outils ne doit servir à profiler, suivre ou surveiller une personne sans autorisation légale.</p>
      </div>
    </div>
  </section>
  <?php foreach ($tools['software'] as $t) echo $fiche($t, 'software'); ?>
  <?php foreach ($tools['equipment'] as $t) echo $fiche($t, 'equipment'); ?>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
