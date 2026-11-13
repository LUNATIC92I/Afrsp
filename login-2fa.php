<?php
/** Seconde étape de connexion : code TOTP (application d'authentification) ou code de secours. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pending = $_SESSION['mfa_pending'] ?? null;
if (!$pending || time() - (int) $pending['at'] > 300) {
    unset($_SESSION['mfa_pending']);
    flash('info', 'Étape de vérification expirée. Reconnectez-vous.');
    redirect('login.php');
}
$authError = null;
if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('mfa:user', (string) $pending['uid'], 10, 900, 'Trop de codes erronés. Patientez 15 minutes avant de réessayer.');
    $code = input('code');
    $user = q_one('SELECT id, email, totp_secret, totp_last_step FROM users WHERE id = ? AND status = \'active\'', [$pending['uid']]);
    $secret = $user ? totp_decrypt($user['totp_secret']) : null;
    $ok = false;
    if ($user && $secret && ($step = totp_verify($secret, $code, $user['totp_last_step'] !== null ? (int) $user['totp_last_step'] : null)) !== null) {
        q('UPDATE users SET totp_last_step = ? WHERE id = ?', [$step, $user['id']]);
        $ok = true;
    } elseif ($user && preg_match('/^[A-Za-z0-9]{5}-[A-Za-z0-9]{5}$/', trim($code)) && recovery_code_use((int) $user['id'], $code)) {
        $ok = true;
        audit('auth.mfa_recovery_used', 'user', (int) $user['id']);
        flash('warning', 'Code de secours utilisé. Il n\'est plus valable : pensez à en régénérer depuis « Sécurité du compte ».');
    }
    if ($ok) {
        login_user((int) $user['id']);
        audit('auth.login', null, null, 'avec double authentification');
        flash('success', 'Connexion établie.');
        redirect(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php');
    }
    $_SESSION['mfa_pending']['tries'] = (int) $pending['tries'] + 1;
    if ($_SESSION['mfa_pending']['tries'] >= 5) {
        unset($_SESSION['mfa_pending']);
        audit('auth.mfa_failed', 'user', (int) $pending['uid']);
        flash('error', 'Trop de codes invalides. Reconnectez-vous.');
        redirect('login.php');
    }
    $authError = 'Code invalide ou déjà utilisé. Vérifiez l\'heure de votre téléphone et réessayez.';
}
$pageTitle = 'Vérification';
$authTitle = 'Double authentification';
$authIntro = 'Saisissez le code à 6 chiffres affiché par votre application d\'authentification, ou l\'un de vos codes de secours.';
$authTerminal = 'Second facteur requis';
$authBody = function (): void { ?>
  <form method="post" action="<?= e(url('login-2fa.php')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="code">Code de vérification</label><input class="input mono" id="code" name="code" required autocomplete="one-time-code" inputmode="numeric" maxlength="11" autofocus placeholder="123456" style="font-size:1.4rem;letter-spacing:.3em;text-align:center"></div>
    <button class="btn btn--primary btn--block" type="submit">Vérifier</button>
  </form>
  <p class="auth__alt">Appareil perdu ? Utilisez un code de secours, ou <a href="<?= e(url('contact.php')) ?>">contactez l'administration</a>.</p>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
