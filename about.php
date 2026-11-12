<?php
/** AFRICA'SPY — Notre institution. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Notre institution';
$pageDescription = "Histoire, vision, mission, valeurs, approche pédagogique et engagements éthiques d'AFRICA'SPY.";
$activeNav = 'about';
require APP_ROOT . '/includes/header.php';

$values = [
    ['Rigueur', 'Des jugements fondés sur des faits vérifiés, des sources évaluées et une expression honnête de l\'incertitude.', '<path d="M8 32h32M24 8v32" /><circle cx="24" cy="24" r="14"/>'],
    ['Légalité', 'Chaque pratique enseignée respecte le droit applicable. Ce qui est illégal n\'est jamais enseigné, même « pour information ».', '<path d="M24 6l16 6v12c0 10-7 16-16 20C15 40 8 34 8 24V12z"/><path d="M17 24l5 5 9-10"/>'],
    ['Intégrité', 'Indépendance d\'analyse, absence de conflits d\'intérêts, refus de la manipulation et de la désinformation.', '<path d="M8 24h32"/><path d="M14 16l-6 8 6 8M34 16l6 8-6 8"/>'],
    ['Discrétion', 'Protection des sources, des données personnelles et des informations confiées par les organisations.', '<rect x="10" y="20" width="28" height="20" rx="3"/><path d="M16 20v-6a8 8 0 0 1 16 0v6"/>'],
    ['Ancrage africain', 'Des cas, des langues, des institutions et des enjeux du continent, sans clichés ni lecture extérieure plaquée.', '<circle cx="24" cy="24" r="16"/><path d="M8 24h32M24 8c6 6 6 26 0 32M24 8c-6 6-6 26 0 32"/>'],
    ['Transmission', 'Une pédagogie exigeante et accessible, qui forme à penser par soi-même plutôt qu\'à appliquer des recettes.', '<path d="M8 14l16-6 16 6-16 6z"/><path d="M14 17v10c4 4 16 4 20 0V17"/>'],
];
?>
<main id="main">
  <section class="page-hero">
    <div class="page-hero__bg" style="background-image:url('<?= e(url('assets/images/ops-room.webp')) ?>')" data-parallax="0.1"></div>
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><span>Institution</span></nav>
      <p class="eyebrow">Dossier AFSPY/INST/001</p>
      <h1 data-split>Notre institution</h1>
      <p class="reveal">AFRICA'SPY est une académie privée de formation en ligne consacrée aux métiers du renseignement, du contre-espionnage et de l'intelligence stratégique, conçue en Afrique, pour l'Afrique.</p>
    </div>
  </section>

  <div class="container section dossier">
    <nav class="dossier__index" aria-label="Sommaire">
      <a href="#histoire">01 · Histoire</a><a href="#vision">02 · Vision</a><a href="#mission">03 · Mission</a><a href="#valeurs">04 · Valeurs</a>
      <a href="#pedagogie">05 · Pédagogie</a><a href="#ethique">06 · Éthique &amp; légalité</a><a href="#ambition">07 · Ambition</a>
    </nav>
    <div>
      <section class="dossier__section" id="histoire">
        <p class="eyebrow">01 // Histoire</p>
        <h2 class="reveal">Née d'un constat</h2>
        <div class="prose reveal">
          <p>Sur le continent, la demande de compétences en analyse de l'information, en protection des organisations et en anticipation des risques n'a jamais été aussi forte. Administrations, entreprises, banques, ONG, médias et universités font face à des menaces nouvelles — fraudes numériques, désinformation, ingérences économiques — et à des transformations géopolitiques rapides.</p>
          <p>Or l'offre de formation dans ces domaines reste rare, souvent coûteuse, éloignée des réalités africaines, ou au contraire nourrie de fantasmes sur « l'espionnage ». AFRICA'SPY est née de la volonté de proposer un parcours sérieux, structuré et accessible, qui forme des professionnels de l'information plutôt que des agents de fiction.</p>
        </div>
        <ol class="timeline reveal">
          <li><span class="mono">PHASE 1 · CONCEPTION</span><h3>Un référentiel pédagogique</h3><p>Définition de six domaines de compétences, de leurs objectifs et d'une charte éthique intangible.</p></li>
          <li><span class="mono">PHASE 2 · PRODUCTION</span><h3>Des contenus développés</h3><p>Rédaction des leçons, exercices, études de cas fictives, banques de questions et supports PDF.</p></li>
          <li><span class="mono">PHASE 3 · OUVERTURE</span><h3>La plateforme en ligne</h3><p>Ouverture de l'espace apprenant, du système d'examens et de la certification vérifiable.</p></li>
        </ol>
      </section>

      <section class="dossier__section" id="vision">
        <p class="eyebrow">02 // Vision</p>
        <h2 class="reveal">Une Afrique qui anticipe</h2>
        <div class="split-media">
          <div class="prose reveal"><p class="lead">Nous voulons contribuer à une Afrique qui comprend finement son environnement, anticipe les menaces et protège ses intérêts stratégiques — par la compétence de ses propres professionnels.</p><p>Cette vision suppose des analystes capables de penser contre leurs propres biais, de travailler avec des sources ouvertes en plusieurs langues, et de rendre compte avec honnêteté de ce qu'ils savent et de ce qu'ils ignorent.</p></div>
          <figure class="media-frame img-reveal"><img src="<?= e(url('assets/images/geo-table.webp')) ?>" alt="Table d'analyse géopolitique montrant une carte de l'Afrique annotée" loading="lazy" width="1600" height="1000"><figcaption>Analyse géopolitique · visuel original</figcaption></figure>
        </div>
      </section>

      <section class="dossier__section" id="mission">
        <p class="eyebrow">03 // Mission</p>
        <h2 class="reveal">Former, outiller, certifier</h2>
        <div class="prose reveal">
          <ul>
            <li><strong>Former</strong> aux fondamentaux et aux spécialités du renseignement, avec des contenus réellement développés.</li>
            <li><strong>Outiller</strong> : méthodes d'analyse structurée, grilles d'évaluation, modèles de rapports, connaissance des outils légitimes.</li>
            <li><strong>Évaluer et certifier</strong> les acquis par des examens rigoureux et des certificats vérifiables publiquement.</li>
            <li><strong>Sensibiliser</strong> les organisations à la protection de l'information et à la culture de sécurité.</li>
          </ul>
        </div>
      </section>

      <section class="dossier__section" id="valeurs">
        <p class="eyebrow">04 // Valeurs</p>
        <h2 class="reveal">Six principes de conduite</h2>
        <div class="values" data-stagger="90">
          <?php foreach ($values as [$t, $d, $svg]): ?>
          <div class="value"><svg class="value__glyph" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><?= $svg ?></svg><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="dossier__section" id="pedagogie">
        <p class="eyebrow">05 // Approche pédagogique</p>
        <h2 class="reveal">Apprendre en analyste</h2>
        <div class="prose reveal"><p>Chaque formation suit une progression en quatre phases, du concept à la mise en situation. Les contenus sont rédigés pour être lus, compris et appliqués — pas seulement survolés.</p></div>
        <ol class="pedagogy reveal">
          <li><strong>Comprendre</strong><span>Leçons développées, briefing vidéo, support PDF téléchargeable.</span></li>
          <li><strong>Vérifier</strong><span>QCM formatif à la fin de chaque chapitre, avec explications.</span></li>
          <li><strong>Pratiquer</strong><span>Exercices guidés et études de cas sur scénarios fictifs et données synthétiques.</span></li>
          <li><strong>Certifier</strong><span>Examen final chronométré, tiré au sort, et certificat vérifiable.</span></li>
        </ol>
      </section>

      <section class="dossier__section" id="ethique">
        <p class="eyebrow eyebrow--gold">06 // Éthique, légalité et libertés</p>
        <h2 class="reveal">Notre charte</h2>
        <div class="charter reveal">
          <span class="charter__stamp">CHARTE · ART. 1–8</span>
          <ol>
            <li>AFRICA'SPY n'enseigne que des pratiques <strong>légales</strong>, dans le respect des constitutions, des lois nationales et des instruments africains et internationaux relatifs aux droits humains et à la protection des données.</li>
            <li>Aucune technique d'<strong>intrusion informatique</strong>, d'<strong>interception de communications</strong> ou de <strong>surveillance clandestine</strong> de personnes n'est enseignée. Aucun plan de fabrication de dispositif clandestin n'est fourni.</li>
            <li>Les activités réservées par la loi aux autorités publiques (interception, recrutement de sources clandestines) sont étudiées uniquement sous l'angle <strong>conceptuel, historique et juridique</strong>.</li>
            <li>Les exercices numériques utilisent des <strong>environnements isolés</strong>, des <strong>données synthétiques</strong> et des <strong>scénarios fictifs</strong> ou autorisés.</li>
            <li>Le respect de la <strong>vie privée</strong> et des <strong>libertés individuelles</strong> est un critère d'évaluation à part entière : une réponse efficace mais illégale est une mauvaise réponse.</li>
            <li>Les apprenants s'engagent à n'utiliser leurs compétences qu'à des fins légitimes ; tout usage abusif constaté entraîne l'exclusion de la plateforme et la révocation des certificats.</li>
            <li>AFRICA'SPY ne revendique <strong>aucune reconnaissance gouvernementale, accréditation officielle ou partenariat institutionnel</strong>. Ses certificats attestent la réussite d'un parcours interne et ne valent pas diplôme d'État.</li>
            <li>Les données personnelles des apprenants sont traitées avec <strong>minimisation</strong> et sécurité, uniquement pour les besoins de la formation.</li>
          </ol>
        </div>
      </section>

      <section class="dossier__section" id="ambition">
        <p class="eyebrow">07 // Ambition</p>
        <h2 class="reveal">Développer les compétences du continent</h2>
        <div class="prose reveal">
          <p>Notre ambition est de contribuer, à notre échelle, au développement d'une génération de professionnels africains de l'analyse et de la sécurité de l'information : analystes, veilleurs, responsables sécurité, enquêteurs internes, journalistes d'investigation, chercheurs.</p>
          <p>Nous souhaitons enrichir progressivement le catalogue — langues nationales, cas régionaux, nouveaux domaines — en restant fidèles à une exigence simple : chaque compétence enseignée doit servir la décision, la protection et le droit.</p>
        </div>
        <div class="flex mt-2"><a class="btn btn--primary" href="<?= e(url('academy.php')) ?>">Découvrir les formations</a><a class="btn btn--ghost" href="<?= e(url('contact.php')) ?>">Nous contacter</a></div>
      </section>
    </div>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
