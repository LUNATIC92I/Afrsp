<?php
/** Pied de page institutionnel. */
declare(strict_types=1);
require_once APP_ROOT . '/includes/logo.php';
$year = gmdate('Y');
?>
<?php if (empty($hideChrome)): ?>
<footer class="site-footer">
  <?= logo_svg('mono', 'footer-watermark', true) ?>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="<?= e(url('index.php')) ?>" aria-label="Accueil"><?= logo_lockup('gold') ?></a>
        <p><?= e(setting('footer_tagline', "Académie africaine de formation aux métiers du renseignement, du contre-espionnage et de l'intelligence stratégique, dans le strict respect du droit et des libertés.")) ?></p>
      </div>
      <div class="footer-col">
        <h3>Académie</h3>
        <ul>
          <li><a href="<?= e(url('academy.php')) ?>">Catalogue des formations</a></li>
          <li><a href="<?= e(url('tools.php')) ?>">Outils du renseignement</a></li>
          <li><a href="<?= e(url('verify-certificate.php')) ?>">Vérifier un certificat</a></li>
          <li><a href="<?= e(url('register.php')) ?>">Rejoindre l'Académie</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Institution</h3>
        <ul>
          <li><a href="<?= e(url('about.php')) ?>">Notre institution</a></li>
          <li><a href="<?= e(url('about.php#ethique')) ?>">Charte éthique</a></li>
          <li><a href="<?= e(url('about.php#pedagogie')) ?>">Approche pédagogique</a></li>
          <li><a href="<?= e(url('contact.php')) ?>">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Portail</h3>
        <ul>
          <li><a href="<?= e(url('login.php')) ?>">Connexion</a></li>
          <li><a href="<?= e(url('forgot-password.php')) ?>">Mot de passe oublié</a></li>
          <li><a href="<?= e(url('legal.php')) ?>">Mentions &amp; confidentialité</a></li>
          <?php if ($e = setting('contact_email')): ?><li><a href="mailto:<?= e($e) ?>"><?= e($e) ?></a></li><?php endif; ?>
        </ul>
      </div>
    </div>
    <p class="footer-legal">AFRICA'SPY est un organisme de formation privé. Ses certificats attestent la réussite d'un parcours pédagogique interne ; ils ne constituent ni un diplôme d'État ni une qualification officiellement reconnue. Les enseignements portent exclusivement sur des pratiques légales, éthiques et défensives, avec des données synthétiques et des scénarios autorisés.</p>
    <div class="footer-bottom">
      <span>© <?= e($year) ?> AFRICA'SPY | Intelligence Academy</span>
      <span class="mono" data-utc-clock>UTC --:--:--</span>
    </div>
  </div>
</footer>
<?php endif; ?>
</body>
</html>
