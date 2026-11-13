<?php
/** Assistant d'installation web : se verrouille automatiquement après usage. */
declare(strict_types=1);
define('AFSPY_INSTALLER', true);
require dirname(__DIR__) . '/includes/bootstrap.php';
require __DIR__ . '/installer.php';

if (installer_locked()) {
    render_error_page(404, 'Installation verrouillée', "La plateforme est déjà installée. Pour réinstaller, supprimez config/config.local.php et install/install.lock sur le serveur.");
}
$checks = installer_requirements();
$allOk = !in_array(false, $checks, true);
$error = null;
if (is_post()) {
    verify_csrf();
    $data = [
        'db_host' => input('db_host'), 'db_port' => input('db_port', '3306'), 'db_name' => input('db_name'), 'db_user' => input('db_user'),
        'db_pass' => (string) ($_POST['db_pass'] ?? ''), 'base_url' => input('base_url'), 'first_name' => input('first_name'),
        'last_name' => input('last_name'), 'email' => input('email'), 'password' => (string) ($_POST['password'] ?? ''),
        'contact_email' => input('contact_email'),
    ];
    if (($data['password']) !== (string) ($_POST['password_confirm'] ?? '')) {
        $error = 'Les deux mots de passe administrateur ne correspondent pas.';
    } elseif (!$allOk) {
        $error = 'Certains prérequis ne sont pas satisfaits.';
    } else {
        $error = installer_run($data);
    }
    if ($error === null) {
        flash('success', 'Installation terminée. Connectez-vous avec votre compte administrateur.');
        redirect('login.php');
    }
    set_old($data);
}
$pageTitle = 'Installation';
$hideChrome = true;
$noVeil = true;
require APP_ROOT . '/includes/header.php';
$guessUrl = (is_https() ? 'https' : 'http') . '://' . preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
?>
<main id="main" class="section">
  <div class="container container--narrow">
    <div class="flex mb-2"><?= logo_lockup('holo') ?></div>
    <p class="eyebrow">Déploiement initial</p>
    <h1>Installation de la plateforme</h1>
    <p class="dim">Cet assistant crée la base de données, importe le catalogue pédagogique et crée le compte administrateur. Il se verrouille ensuite définitivement.</p>
    <div class="panel corners" style="padding:24px;margin:24px 0">
      <h2 class="mono" style="font-size:.9rem;letter-spacing:.2em">VÉRIFICATION DES PRÉREQUIS</h2>
      <ul style="list-style:none;padding:0;margin:0;display:grid;gap:6px">
      <?php foreach ($checks as $label => $ok): ?>
        <li class="flex"><span class="tag <?= $ok ? 'tag--green' : 'tag--red' ?>"><?= $ok ? 'OK' : 'ÉCHEC' ?></span> <?= e($label) ?></li>
      <?php endforeach; ?>
      </ul>
    </div>
    <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="panel" style="padding:28px" autocomplete="off">
      <?= csrf_field() ?>
      <h2 class="mono" style="font-size:.9rem;letter-spacing:.2em">BASE DE DONNÉES MYSQL</h2>
      <p class="dim">La base doit exister (créez-la depuis votre hébergeur, interclassement utf8mb4_unicode_ci).</p>
      <div class="form-row">
        <div class="field"><label for="db_host">Hôte</label><input class="input" id="db_host" name="db_host" required value="<?= e(old('db_host', '127.0.0.1')) ?>"></div>
        <div class="field"><label for="db_port">Port</label><input class="input" id="db_port" name="db_port" inputmode="numeric" value="<?= e(old('db_port', '3306')) ?>"></div>
        <div class="field"><label for="db_name">Nom de la base</label><input class="input" id="db_name" name="db_name" required value="<?= e(old('db_name', 'africaspy')) ?>"></div>
        <div class="field"><label for="db_user">Utilisateur</label><input class="input" id="db_user" name="db_user" required value="<?= e(old('db_user')) ?>"></div>
      </div>
      <div class="field"><label for="db_pass">Mot de passe de la base</label><input class="input" type="password" id="db_pass" name="db_pass"></div>
      <div class="field"><label for="base_url">URL publique du site</label><input class="input" type="url" required id="base_url" name="base_url" value="<?= e(old('base_url', $guessUrl)) ?>"><span class="hint">Obligatoire : utilisée dans les liens envoyés par e-mail et les QR codes (jamais déduite de la requête, pour éviter l'empoisonnement des liens).</span></div>
      <hr>
      <h2 class="mono" style="font-size:.9rem;letter-spacing:.2em">COMPTE ADMINISTRATEUR INITIAL</h2>
      <div class="form-row">
        <div class="field"><label for="first_name">Prénom</label><input class="input" id="first_name" name="first_name" required value="<?= e(old('first_name')) ?>"></div>
        <div class="field"><label for="last_name">Nom</label><input class="input" id="last_name" name="last_name" required value="<?= e(old('last_name')) ?>"></div>
      </div>
      <div class="field"><label for="email">E-mail de connexion</label><input class="input" type="email" id="email" name="email" required value="<?= e(old('email')) ?>"></div>
      <div class="form-row">
        <div class="field"><label for="password">Mot de passe</label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"><div class="pw-meter" data-pw-meter="password"><span></span></div><span class="hint">10 caractères minimum, lettres et chiffres.</span></div>
        <div class="field"><label for="password_confirm">Confirmation</label><input class="input" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"></div>
      </div>
      <div class="field"><label for="contact_email">E-mail de contact public (facultatif)</label><input class="input" type="email" id="contact_email" name="contact_email" value="<?= e(old('contact_email')) ?>"></div>
      <button class="btn btn--primary btn--block" type="submit" <?= $allOk ? '' : 'disabled' ?>>Installer AFRICA'SPY</button>
    </form>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php';
