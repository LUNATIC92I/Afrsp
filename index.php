<?php
/** AFRICA'SPY — page d'accueil. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';
require APP_ROOT . '/includes/content.php';
require_once APP_ROOT . '/includes/logo.php';

$featured = find_courses(['featured' => true, 'limit' => 6]);
if (count($featured) < 6) {
    $ids = array_column($featured, 'id');
    foreach (find_courses() as $c) {
        if (count($featured) >= 6) break;
        if (!in_array($c['id'], $ids, true)) $featured[] = $c;
    }
}
$m = platform_metrics();
$panels = command_panels();
$zones = study_zones();
$offerList = (int) q_val("SELECT MAX(price_xof) FROM courses WHERE status = 'published'");
$offerPrice = (int) q_val("SELECT MIN(COALESCE(LEAST(promo_price_xof, price_xof), price_xof)) FROM courses WHERE status = 'published'");
$offerPct = $offerList > 0 && $offerPrice < $offerList ? (int) round(100 - $offerPrice * 100 / $offerList) : 0;
$deadline = promo_deadline();
$tickerItems = ['Analyse structurée', 'HUMINT éthique', 'OSINT', 'GEOINT', 'Contre-espionnage', 'Cyber Threat Intelligence', 'Protection des sources', 'Intelligence économique', 'Risques pays', 'Géopolitique africaine', 'Certification vérifiable'];

$pageTitle = '';
$activeNav = 'home';
$bodyClass = 'page-home';
require APP_ROOT . '/includes/header.php';
?>
<div class="intro" id="intro" hidden aria-hidden="true">
  <div class="intro__grid"></div>
  <div class="intro__sweep"></div>
  <div class="intro__log">
    <span>&gt; Initialisation du terminal sécurisé…</span>
    <span>&gt; Chargement de la cartographie continentale</span>
    <span>&gt; Synchronisation horloge UTC</span>
    <span>&gt; Cadre éthique et légal : vérifié</span>
    <span>&gt; Accès accordé — AFRICA'SPY Academy</span>
  </div>
  <div class="intro__core">
    <?= logo_svg('holo', 'intro__mark', true) ?>
    <div class="intro__word">AFRICA<i>'</i>SPY</div>
    <div class="intro__sub">Intelligence Academy</div>
  </div>
  <div class="intro__coords">LAT 00°00'00" · LON 20°00'00"<br>SECTEUR : CONTINENT AFRICAIN</div>
  <button class="intro__skip" type="button">Passer l'introduction</button>
</div>

<main id="main">
  <!-- ============================== HERO -->
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero__layer hero__bg" data-parallax="0.18" aria-hidden="true"></div>
    <div class="hero__layer hero__shade" aria-hidden="true"></div>
    <div class="hero__layer hero__grid" aria-hidden="true"></div>
    <canvas class="hero__canvas" data-particles aria-hidden="true"></canvas>
    <div class="container hero__inner">
      <div class="hero__copy">
        <p class="hero__coords reveal" aria-hidden="true">
          <span><i class="dot" style="color:var(--green)"></i> SYSTÈME OPÉRATIONNEL</span>
          <span>N 09°04′ · E 07°29′</span>
          <span data-utc-clock>UTC --:--:--</span>
        </p>
        <h1 class="hero__title" id="hero-title" data-split>AFRICA'SPY</h1>
        <p class="hero__tagline reveal">The future of intelligence starts here.</p>
        <p class="hero__slogan reveal">Comprendre l'information. Anticiper les menaces. Protéger les intérêts stratégiques.</p>
        <p class="hero__text reveal">AFRICA'SPY est une académie africaine dédiée aux métiers du renseignement, du contre-espionnage et de l'intelligence stratégique. Nous formons des analystes rigoureux, éthiques et respectueux du droit, capables d'éclairer les décisions publiques et privées du continent.</p>
        <div class="hero__ctas reveal">
          <a class="btn btn--primary" href="<?= e(url('academy.php')) ?>"><span class="btn__stack">Explore the Academy<small>Découvrir nos formations</small></span></a>
          <a class="btn btn--ghost" href="<?= e(url(current_user() ? 'learner/dashboard.php' : 'register.php')) ?>"><span class="btn__stack">Access the Intelligence Portal<small>Rejoindre l'Académie</small></span></a>
        </div>
      </div>
      <div class="hero__hud reveal reveal--zoom" aria-hidden="true">
        <div class="orbit orbit--1"><i></i></div><div class="orbit orbit--2"><i></i></div><div class="orbit orbit--3"><i></i></div>
        <div class="hero__emblem"><?= logo_svg('holo', 'hero-mark', true) ?></div>
        <div class="hero__readout hero__readout--tl">CLASSIFICATION <b>PUBLIQUE</b><br>PROTOCOLE <b>ÉTHIQUE-01</b></div>
        <div class="hero__readout hero__readout--br">DOMAINES <b><?= (int) $m['domains'] ?></b><br>FORMATIONS <b><?= (int) $m['courses'] ?></b></div>
      </div>
    </div>
    <div class="hero__scroll" aria-hidden="true"><i></i>Défiler</div>
  </section>

  <!-- ============================== BANDEAU DÉFILANT -->
  <div class="ticker" aria-hidden="true">
    <div class="ticker__track">
      <?php for ($k = 0; $k < 2; $k++): foreach ($tickerItems as $t): ?><span><?= e($t) ?></span><i>◆</i><?php endforeach; endfor; ?>
    </div>
  </div>

  <!-- ============================== PRÉSENTATION -->
  <section class="section" id="institution" aria-labelledby="brief-title">
    <div class="container brief">
      <div class="reveal reveal--left">
        <p class="eyebrow" data-decrypt>01 // Dossier institutionnel</p>
        <h2 id="brief-title" class="brief__statement">Une académie pour former les <em>analystes</em> dont l'Afrique a besoin — et non des espions de cinéma.</h2>
      </div>
      <div class="reveal">
        <div class="brief__cols">
          <p>Le renseignement est d'abord un métier de méthode : poser les bonnes questions, chercher l'information par des moyens légaux, l'évaluer sans complaisance et la restituer clairement à ceux qui doivent décider. C'est ce métier que l'Académie enseigne.</p>
          <p>Nos parcours couvrent les fondements du renseignement, le renseignement humain, le contre-espionnage, le renseignement numérique, les outils professionnels et l'intelligence stratégique. Chacun combine leçons développées, exercices pratiques, études de cas fictives et évaluations.</p>
          <p>Notre ligne est intangible : légalité, proportionnalité, protection des libertés individuelles. Aucune technique de surveillance illégale, d'intrusion ou d'interception n'est enseignée.</p>
          <p>Les exercices numériques reposent sur des données synthétiques et des environnements isolés. Les certificats attestent la réussite d'un parcours interne et sont vérifiables en ligne.</p>
        </div>
        <div class="brief__sig"><span>Réf. AFSPY/INST/001</span><a class="link-arrow" href="<?= e(url('about.php')) ?>">Notre institution</a></div>
      </div>
    </div>
  </section>

  <!-- ============================== CENTRE DE COMMANDEMENT -->
  <section class="section section--tight" id="expertise" aria-labelledby="cmd-title">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow" data-decrypt>02 // Domaines d'expertise</p>
        <h2 id="cmd-title">Interactive Command Center</h2>
        <p>Sélectionnez un poste pour explorer chacun des cinq domaines d'expertise de l'Académie.</p>
      </div>
      <div class="command reveal reveal--depth" data-command>
        <div class="command__rail" role="tablist" aria-label="Domaines d'expertise">
          <div class="command__status">Poste d'analyse · <b>en ligne</b><br>Sélection : <span data-cmd-current>CMD-01</span></div>
          <?php foreach ($panels as $i => $p): ?>
          <button class="cmd-tab" role="tab" id="tab-<?= e($p['id']) ?>" aria-controls="panel-<?= e($p['id']) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" data-code="<?= e($p['code']) ?>">
            <span class="cmd-tab__code"><?= e(substr($p['code'], -2)) ?></span><span class="cmd-tab__name"><?= e($p['name']) ?></span>
          </button>
          <?php endforeach; ?>
        </div>
        <div class="command__stage">
          <?php foreach ($panels as $i => $p): ?>
          <div class="cmd-panel" role="tabpanel" id="panel-<?= e($p['id']) ?>" aria-labelledby="tab-<?= e($p['id']) ?>" <?= $i ? 'hidden' : '' ?>>
            <div>
              <p class="cmd-panel__sub"><?= e($p['code']) ?> · <?= e($p['fr']) ?></p>
              <h3><?= e($p['name']) ?></h3>
              <p class="lead"><?= e($p['lead']) ?></p>
              <ul><?php foreach ($p['points'] as $pt): ?><li><?= e($pt) ?></li><?php endforeach; ?></ul>
              <div class="flex">
                <button class="btn btn--ghost btn--sm" type="button" data-modal-open="modal-<?= e($p['id']) ?>">Ouvrir le dossier</button>
                <a class="link-arrow" href="<?= e(url('academy.php', ['domaine' => $p['cat']])) ?>">Formations du domaine</a>
              </div>
            </div>
            <div class="cmd-viz"><?= command_glyph($p['glyph']) ?><span class="cmd-viz__label"><?= e($p['code']) ?></span></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php foreach ($panels as $p): ?>
      <div class="modal" id="modal-<?= e($p['id']) ?>" hidden role="dialog" aria-modal="true" aria-labelledby="mt-<?= e($p['id']) ?>">
        <div class="modal__box corners">
          <button class="modal__close" type="button" data-modal-close aria-label="Fermer">×</button>
          <p class="modal__code"><?= e($p['code']) ?> // Dossier de domaine</p>
          <h3 id="mt-<?= e($p['id']) ?>" style="font-family:var(--f-display);font-size:1.6rem"><?= e($p['name']) ?> — <?= e($p['fr']) ?></h3>
          <p><?= e($p['detail']) ?></p>
          <ul><?php foreach ($p['points'] as $pt): ?><li><?= e($pt) ?></li><?php endforeach; ?></ul>
          <a class="btn btn--primary btn--sm" href="<?= e(url('academy.php', ['domaine' => $p['cat']])) ?>">Voir les formations</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ============================== AFRIQUE HOLOGRAPHIQUE -->
  <section class="section scanner" id="carte" aria-labelledby="map-title">
    <div class="container">
      <div class="section-head reveal">
        <p class="eyebrow" data-decrypt>03 // Holographic Africa</p>
        <h2 id="map-title">Un continent, des terrains d'étude</h2>
        <p>Chaque point lumineux ouvre une fiche pédagogique régionale reliée à nos formations. Déplacez le pointeur pour faire pivoter la carte.</p>
      </div>
      <div class="holomap">
        <div class="holomap__stage" data-holomap>
          <div class="holomap__tilt"><?= africa_map_svg(true) ?></div>
        </div>
        <div class="holomap__card panel corners" data-zone-card aria-live="polite">
          <p class="mono" data-f="code">ZONE D'ÉTUDE · SÉLECTION</p>
          <h3 data-f="name">Sélectionnez une zone</h3>
          <p data-f="theme" class="lead">Cliquez sur un point lumineux de la carte pour afficher la fiche régionale.</p>
          <dl>
            <dt>Enjeux</dt><dd data-f="issues">—</dd>
            <dt>Domaine</dt><dd data-f="domain">—</dd>
          </dl>
          <a class="link-arrow" data-f="link" href="<?= e(url('academy.php')) ?>">Formation associée</a>
          <p class="holomap__note">Ces zones sont des thèmes d'étude pédagogique. Elles ne représentent ni des implantations d'AFRICA'SPY, ni une activité de surveillance.</p>
        </div>
      </div>
    </div>
    <script type="application/json" id="zones-data"><?= json_encode(array_map(static fn ($z) => $z + ['url' => url('course.php', ['slug' => $z['course']])], $zones), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
  </section>

  <!-- ============================== FORMATIONS POPULAIRES -->
  <section class="section" id="formations" aria-labelledby="courses-title">
    <div class="container">
      <div class="section-head reveal flex between" style="max-width:none;align-items:flex-end">
        <div style="max-width:720px">
          <p class="eyebrow" data-decrypt>04 // Formations à la une</p>
          <h2 id="courses-title">Modules d'entraînement</h2>
          <p>Une sélection de parcours complets : leçons développées, briefings vidéo, supports PDF, exercices, études de cas, QCM et examen final certifiant.</p>
        </div>
        <a class="btn btn--ghost" href="<?= e(url('academy.php')) ?>">Catalogue complet</a>
      </div>
      <div class="bento" data-stagger="110">
        <?php foreach ($featured as $c): ?><?= course_card($c) ?><?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- ============================== OFFRE -->
  <section class="section section--tight offer-section" id="offre" aria-labelledby="offer-title" data-spotlight>
    <div class="container offer">
      <div class="reveal reveal--left">
        <p class="eyebrow eyebrow--gold" data-decrypt>04B // <?= e(setting('promo_label', 'Offre de lancement')) ?></p>
        <h2 id="offer-title">Accédez à une formation complète</h2>
        <p class="lead">Chaque formation se règle une seule fois et reste accessible sans limite de durée : leçons, briefing vidéo, support PDF, exercices, études de cas, QCM, examen final et certificat vérifiable.</p>
        <ul class="checklist" style="grid-template-columns:1fr">
          <li>Paiement par Mobile Money, carte bancaire ou virement</li>
          <li>Accès ouvert dès la confirmation du paiement</li>
          <li>Reçu PDF disponible dans votre espace</li>
        </ul>
      </div>
      <div class="offer-card reveal reveal--zoom" data-tilt>
        <div class="offer-card__inner">
          <p class="offer-card__tag mono">PRIX PAR FORMATION · FCFA</p>
          <?php if ($offerPct): ?>
          <p class="offer-card__old"><s data-strike><?= e(fmt_xof($offerList)) ?></s> <span class="price__badge">−<?= $offerPct ?> %</span></p>
          <?php endif; ?>
          <p class="offer-card__price"><span data-count="<?= $offerPrice ?>">0</span><small>FCFA</small></p>
          <p class="offer-card__label"><?= e(setting('promo_label', 'Prix promotionnel')) ?> · au lieu de <?= e(fmt_xof($offerList)) ?></p>
          <?php if ($deadline): ?><div class="countdown" data-countdown="<?= $deadline ?>"><span class="mono">Fin de l'offre dans</span><b data-cd>—</b></div><?php endif; ?>
          <a class="btn btn--primary btn--block" href="<?= e(url('academy.php')) ?>" data-magnetic>Choisir ma formation</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================== CHIFFRES CLÉS -->
  <section class="section section--tight" aria-labelledby="metrics-title">
    <div class="container metrics">
      <div>
        <div class="section-head reveal" style="margin-bottom:28px">
          <p class="eyebrow" data-decrypt>05 // Indicateurs</p>
          <h2 id="metrics-title">L'Académie en chiffres</h2>
        </div>
        <div class="metrics__grid reveal">
          <div class="metric"><div class="metric__value" data-count="<?= $m['courses'] ?>">0</div><div class="metric__label">Formations publiées</div></div>
          <div class="metric"><div class="metric__value" data-count="<?= $m['chapters'] ?>">0</div><div class="metric__label">Chapitres</div></div>
          <div class="metric"><div class="metric__value" data-count="<?= $m['lessons'] ?>">0</div><div class="metric__label">Leçons, exercices et études de cas</div></div>
          <div class="metric"><div class="metric__value" data-count="<?= $m['questions'] ?>">0</div><div class="metric__label">Questions QCM en banque</div></div>
          <div class="metric"><div class="metric__value"><span data-count="<?= $m['hours'] ?>">0</span><small>h</small></div><div class="metric__label">Heures de formation</div></div>
          <div class="metric"><div class="metric__value" data-count="<?= $m['certificates'] ?>">0</div><div class="metric__label">Certificats délivrés</div></div>
        </div>
        <p class="metrics__note">Indicateurs calculés en temps réel à partir de la base de données de la plateforme.</p>
      </div>
      <div class="reveal reveal--zoom">
        <div class="radar" data-radar>
          <canvas width="380" height="380" aria-hidden="true"></canvas>
          <div class="radar__tip" role="tooltip"></div>
        </div>
        <p class="radar__caption">Intelligence radar · notions clés · visualisation décorative</p>
        <script type="application/json" id="radar-data"><?= json_encode(radar_items(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?></script>
      </div>
    </div>
  </section>

  <!-- ============================== MISSION & VISION -->
  <section class="section mission" aria-labelledby="mission-title">
    <div class="mission__bg" data-parallax="0.12" aria-hidden="true"></div>
    <svg class="mission__topo" data-draw viewBox="0 0 1200 600" preserveAspectRatio="none" aria-hidden="true">
      <?php for ($k = 0; $k < 9; $k++): ?>
      <path d="M0 <?= 120 + $k * 48 ?> C 200 <?= 60 + $k * 50 ?>, 400 <?= 200 + $k * 40 ?>, 600 <?= 130 + $k * 46 ?> S 1000 <?= 70 + $k * 52 ?>, 1200 <?= 150 + $k * 44 ?>" fill="none" stroke="<?= $k % 3 ? 'rgba(0,200,255,.18)' : 'rgba(213,179,106,.3)' ?>" stroke-width="1"/>
      <?php endfor; ?>
    </svg>
    <div class="container">
      <div class="section-head section-head--center reveal">
        <p class="eyebrow eyebrow--gold">06 // Mission &amp; vision</p>
        <h2 id="mission-title">Servir la décision, protéger les libertés</h2>
      </div>
      <div class="mission__grid" data-stagger="140">
        <div class="mission__item"><span class="kicker-num">// MISSION</span><h3>Former</h3><p>Développer, sur le continent, des compétences de haut niveau en analyse, en protection de l'information et en intelligence stratégique, accessibles en ligne et adaptées aux réalités africaines.</p></div>
        <div class="mission__item"><span class="kicker-num">// VISION</span><h3>Anticiper</h3><p>Contribuer à une Afrique qui comprend mieux son environnement, anticipe ses risques et protège ses intérêts stratégiques grâce à des professionnels compétents et intègres.</p></div>
        <div class="mission__item"><span class="kicker-num">// ENGAGEMENT</span><h3>Respecter</h3><p>Enseigner exclusivement des pratiques légales, proportionnées et respectueuses des droits fondamentaux, de la vie privée et des libertés individuelles.</p></div>
      </div>
    </div>
  </section>

  <!-- ============================== CERTIFICATION -->
  <section class="section" id="certification" aria-labelledby="cert-title">
    <div class="container certif">
      <div class="reveal reveal--left">
        <div class="cert-preview" aria-hidden="true">
          <div class="cert-preview__inner">
            <?= logo_svg('gold', 'cert-mark', true) ?>
            <div class="cert-preview__title">CERTIFICAT DE RÉUSSITE</div>
            <div class="cert-preview__course">décerné à</div>
            <div class="cert-preview__name">Nom de l'apprenant</div>
            <div class="cert-preview__course">Fondements du renseignement · AFS-A101</div>
            <div class="cert-preview__foot"><span>N° AFSPY-2026-XXXXXXXX</span><span class="cert-preview__qr"></span></div>
          </div>
        </div>
      </div>
      <div class="reveal">
        <p class="eyebrow eyebrow--gold">07 // Certification</p>
        <h2 id="cert-title" data-decrypt>Une certification vérifiable</h2>
        <p>Chaque formation se conclut par un examen final chronométré, tiré au sort dans une banque de questions. En cas de réussite, un certificat PDF est généré automatiquement.</p>
        <ol class="steps">
          <li><span><strong>Suivre</strong> toutes les leçons et valider les évaluations de chapitre.</span></li>
          <li><span><strong>Réussir</strong> l'examen final (seuil et nombre de tentatives définis par formation).</span></li>
          <li><span><strong>Télécharger</strong> le certificat : numéro unique, QR Code, signature institutionnelle.</span></li>
          <li><span><strong>Partager</strong> : tout tiers peut en vérifier l'authenticité en ligne.</span></li>
        </ol>
        <form class="verify-inline" action="<?= e(url('verify-certificate.php')) ?>" method="get" role="search">
          <label class="sr-only" for="vcode">Numéro de certificat</label>
          <input class="input" id="vcode" name="code" placeholder="AFSPY-2026-XXXXXXXX" maxlength="32" required>
          <button class="btn btn--gold" type="submit">Vérifier</button>
        </form>
        <p class="disclaimer mt-2">Les certificats AFRICA'SPY attestent la réussite d'un parcours de formation privé. Ils ne constituent ni un diplôme d'État ni une qualification officiellement reconnue.</p>
      </div>
    </div>
  </section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
