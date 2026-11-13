<?php
/** Connexion sécurisée (anti force brute, régénération de session). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_guest();

$authError = null;
if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('login:ip', client_ip(), 60, 900);
    $email = input('email');
    $err = ($email === '' || ($_POST['password'] ?? '') === '') ? 'Saisissez votre adresse e-mail et votre mot de passe.' : attempt_login($email, (string) $_POST['password']);
    if ($err === MFA_REQUIRED) {
        redirect('login-2fa.php');
    }
    if ($err === null) {
        audit('auth.login');
        $intended = $_SESSION['intended'] ?? '';
        unset($_SESSION['intended']);
        flash('success', 'Connexion établie. Bienvenue, ' . current_user()['first_name'] . '.');
        // Redirection uniquement vers un chemin interne
        if (is_string($intended) && $intended !== '' && str_starts_with($intended, base_path() . '/') && !str_starts_with($intended, '//') && !preg_match('#logout|login#', $intended)) {
            header('Location: ' . $intended, true, 303);
            exit;
        }
        redirect(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php');
    }
    $authError = $err;
    set_old(['email' => $email]);
}
$pageTitle = 'Connexion';
$authTitle = 'Connexion';
$authIntro = 'Accédez à votre espace de formation sécurisé.';
$authTerminal = 'Canal chiffré · identification';
$authBody = function (): void { ?>
  <form method="post" action="<?= e(url('login.php')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Adresse e-mail</label><input class="input" type="email" id="email" name="email" required autocomplete="username" value="<?= e(old('email')) ?>" autofocus></div>
    <div class="field"><label for="password">Mot de passe</label><input class="input" type="password" id="password" name="password" required autocomplete="current-password"></div>
    <div class="flex between mb-2"><a href="<?= e(url('forgot-password.php')) ?>" style="font-size:.9rem">Mot de passe oublié ?</a></div>
    <button class="btn btn--primary btn--block" type="submit">Se connecter</button>
  </form>
  <p class="auth__alt">Pas encore de compte ? <a href="<?= e(url('register.php')) ?>">Rejoindre l'Académie</a></p>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
