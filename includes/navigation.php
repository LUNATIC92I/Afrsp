<?php
/** Navigation institutionnelle principale. */
declare(strict_types=1);
$activeNav = $activeNav ?? '';
$navItems = [
    'home'    => ['index.php', 'Accueil'],
    'about'   => ['about.php', 'Institution'],
    'academy' => ['academy.php', 'Académie'],
    'tools'   => ['tools.php', 'Outils'],
    'verify'  => ['verify-certificate.php', 'Vérification'],
    'contact' => ['contact.php', 'Contact'],
];
$me = current_user();
?>
<nav class="main-nav" id="main-nav" aria-label="Navigation principale">
  <ul>
<?php foreach ($navItems as $key => [$href, $label]): ?>
    <li><a href="<?= e(url($href)) ?>"<?= $activeNav === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
  </ul>
</nav>
<div class="header-actions">
<?php if ($me): ?>
  <a class="user-chip" href="<?= e(url(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php')) ?>" title="Mon espace">
    <span class="user-chip__av"><?= e(initials($me['first_name'], $me['last_name'])) ?></span><?= e($me['first_name']) ?>
  </a>
  <form method="post" action="<?= e(url('logout.php')) ?>"><?= csrf_field() ?><button class="btn btn--sm" type="submit">Déconnexion</button></form>
<?php else: ?>
  <a class="btn" href="<?= e(url('login.php')) ?>">Connexion</a>
  <a class="btn btn--signup" href="<?= e(url('register.php')) ?>">Inscription</a>
<?php endif; ?>
</div>
