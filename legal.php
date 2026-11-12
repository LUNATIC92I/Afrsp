<?php
/** Mentions légales et politique de confidentialité. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Mentions et confidentialité';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
  <section class="page-hero"><div class="container"><p class="eyebrow">Informations légales</p><h1>Mentions &amp; confidentialité</h1></div></section>
  <section class="section section--tight"><div class="container container--narrow prose">
    <h2>Éditeur</h2>
    <p><?= e(setting('institution_name', "AFRICA'SPY — Intelligence Academy")) ?>, organisme de formation privé. <?= e(setting('contact_address')) ?> — Contact : <?= e(setting('contact_email')) ?>.</p>
    <p>AFRICA'SPY ne revendique aucune reconnaissance gouvernementale, accréditation officielle ou partenariat institutionnel. Ses certificats attestent la réussite d'un parcours interne et ne constituent pas des diplômes d'État.</p>
    <h2>Données personnelles</h2>
    <ul>
      <li><strong>Données collectées</strong> : nom, prénom, e-mail, mot de passe (stocké uniquement sous forme de hachage), informations de profil facultatives, progression pédagogique, résultats d'examens, certificats.</li>
      <li><strong>Finalité</strong> : fourniture du service de formation, délivrance et vérification des certificats, sécurité de la plateforme.</li>
      <li><strong>Vérification publique</strong> : la page de vérification n'affiche, pour un numéro donné, que le nom du titulaire, l'intitulé de la formation, le score et la date.</li>
      <li><strong>Sécurité</strong> : connexions chiffrées (HTTPS recommandé), protection contre la force brute, journalisation des actions administratives.</li>
      <li><strong>Droits</strong> : vous pouvez demander l'accès, la rectification ou la suppression de vos données via la page Contact, conformément à la législation applicable (notamment la Convention de Malabo et les lois nationales).</li>
      <li><strong>Cookies</strong> : un unique cookie de session technique, indispensable à la connexion. Aucun cookie publicitaire ni traceur tiers.</li>
    </ul>
    <h2>Visuels</h2>
    <p>Les illustrations, cartes et vidéos de la plateforme sont des créations originales générées pour AFRICA'SPY à partir de données cartographiques du domaine public (Natural Earth). Les noms d'outils logiciels cités sont des marques de leurs détenteurs, mentionnées à titre pédagogique sans affiliation.</p>
  </div></section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
