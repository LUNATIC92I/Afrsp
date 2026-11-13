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
    <h2 id="vente">Conditions de vente</h2>
    <ul>
      <li><strong>Prix</strong> : chaque formation est vendue à l'unité, en francs CFA (XOF), toutes taxes applicables comprises selon la législation du pays de l'éditeur. Le prix promotionnel affiché (par exemple 170 000 FCFA au lieu de 900 000 FCFA) est celui facturé au moment de la commande.</li>
      <li><strong>Paiement</strong> : Mobile Money ou carte bancaire via un prestataire de paiement agréé, ou virement / dépôt. Aucune donnée bancaire n'est saisie ni conservée par AFRICA'SPY.</li>
      <li><strong>Accès</strong> : ouvert dès la confirmation du paiement par le prestataire, ou après vérification d'un virement par l'administration. L'accès est personnel, non cessible et sans limite de durée tant que la plateforme est exploitée.</li>
      <li><strong>Reçu</strong> : un reçu PDF est disponible dans l'espace « Mes paiements ».</li>
      <li><strong>Remboursement</strong> : toute demande est à adresser via la page Contact ; un remboursement entraîne la fermeture de l'accès à la formation concernée.</li>
      <li><strong>Usage</strong> : l'accès suppose le respect de la charte éthique ; un usage abusif peut entraîner la fermeture du compte.</li>
    </ul>
    <h2 id="securite">Signalement de vulnérabilités</h2>
    <p>Si vous découvrez une faille de sécurité, merci de nous la signaler de manière confidentielle via la page Contact (objet « Assistance technique ») ou l'adresse indiquée dans <code>/.well-known/security.txt</code>. Ne testez que dans le respect de la loi, sans accéder aux données d'autrui ni perturber le service ; nous nous engageons à répondre rapidement et à ne pas engager de poursuites contre un signalement de bonne foi.</p>
    <h2>Visuels</h2>
    <p>Les illustrations, cartes et vidéos de la plateforme sont des créations originales générées pour AFRICA'SPY à partir de données cartographiques du domaine public (Natural Earth). Les noms d'outils logiciels cités sont des marques de leurs détenteurs, mentionnées à titre pédagogique sans affiliation.</p>
  </div></section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
