<?php
/** Seconde étape de connexion : code de l'application, code reçu par e-mail ou code de secours. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pending = $_SESSION['mfa_pending'] ?? null;
if (!$pending || time() - (int) $pending['at'] > 600) {
    unset($_SESSION['mfa_pending']);
    flash('info', 'Étape de vérification expirée. Reconnectez-vous.');
    redirect('login.php');
}
$method = ($pending['method'] ?? 'totp') === 'email' ? 'email' : 'totp';
$user = q_one('SELECT id, first_name, email, totp_secret, totp_last_step, totp_recovery FROM users WHERE id = ? AND status = \'active\'', [$pending['uid']]);
if (!$user) {
    unset($_SESSION['mfa_pending']);
    redirect('login.php');
}
$authError = null;
if (is_post()) {
    verify_csrf();
    if (input('action') === 'resend' && $method === 'email') {
        if (rate_limit('mfa_mail:user', (string) $user['id'], 6, 900)) {
            mfa_email_send($user, 'login');
            flash('info', 'Un nouveau code vient de vous être envoyé. Seul le dernier code reçu est valable.');
        } else {
            flash('error', 'Trop d\'envois de codes. Patientez 15 minutes ou utilisez un code de secours.');
        }
        redirect('login-2fa.php');
    }
    rate_limit_or_fail('mfa:user', (string) $user['id'], 10, 900, 'Trop de codes erronés. Patientez 15 minutes avant de réessayer.');
    $code = trim(input('code'));
    $ok = false;
    $viaRecovery = false;
    if (preg_match('/^[A-Za-z0-9]{5}-[A-Za-z0-9]{5}$/', $code)) {
        $ok = $viaRecovery = recovery_code_use((int) $user['id'], $code);
    } elseif ($method === 'email') {
        $ok = mfa_email_verify((int) $user['id'], 'login', $code);
    } elseif (($secret = totp_decrypt($user['totp_secret'])) && ($step = totp_verify($secret, $code, $user['totp_last_step'] !== null ? (int) $user['totp_last_step'] : null)) !== null) {
        q('UPDATE users SET totp_last_step = ? WHERE id = ?', [$step, $user['id']]);
        $ok = true;
    }
    if ($ok) {
        $trust = !empty($_POST['trust']) && mfa_trust_days() > 0;
        login_user((int) $user['id']);
        audit('auth.login', null, null, 'avec double authentification (' . ($viaRecovery ? 'code de secours' : mfa_method_label($method)) . ')');
        if ($viaRecovery) {
            $left = count(json_decode((string) q_val('SELECT totp_recovery FROM users WHERE id = ?', [$user['id']]), true) ?: []);
            audit('auth.mfa_recovery_used', 'user', (int) $user['id']);
            mfa_notify((int) $user['id'], 'recovery_used', (string) $left);
            flash('warning', "Code de secours utilisé : il n'est plus valable (il vous en reste $left). Régénérez-en depuis « Sécurité du compte » si besoin.");
        } else {
            flash('success', 'Connexion établie.');
        }
        if ($trust) {
            mfa_trust_device((int) $user['id']);
            mfa_notify((int) $user['id'], 'device_trusted', mfa_device_label());
        }
        redirect(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php');
    }
    $_SESSION['mfa_pending']['tries'] = (int) $pending['tries'] + 1;
    if ($_SESSION['mfa_pending']['tries'] >= 5) {
        unset($_SESSION['mfa_pending']);
        audit('auth.mfa_failed', 'user', (int) $user['id']);
        flash('error', 'Trop de codes invalides. Reconnectez-vous.');
        redirect('login.php');
    }
    $authError = $method === 'email'
        ? 'Code invalide ou expiré. Vérifiez le dernier e-mail reçu, ou demandez un nouveau code.'
        : 'Code invalide ou déjà utilisé. Vérifiez que l\'heure de votre téléphone est réglée automatiquement.';
}
$masked = preg_replace_callback('/^(.)(.*)(@.*)$/u', static fn ($m) => $m[1] . str_repeat('*', min(6, max(2, mb_strlen($m[2])))) . $m[3], $user['email']);
$pageTitle = 'Vérification';
$authTitle = 'Double authentification';
$authIntro = $method === 'email'
    ? "Un code à 6 chiffres vient d'être envoyé à $masked. Il est valable 10 minutes."
    : 'Saisissez le code à 6 chiffres affiché par votre application d\'authentification.';
$authTerminal = 'Second facteur requis';
$authBody = function () use ($method): void { ?>
  <form method="post" action="<?= e(url('login-2fa.php')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="code">Code de vérification</label><input class="input mono" id="code" name="code" required autocomplete="one-time-code" inputmode="numeric" maxlength="11" autofocus placeholder="123456" style="font-size:1.4rem;letter-spacing:.3em;text-align:center"></div>
    <?php if (mfa_trust_days() > 0): ?>
      <label class="check mb-2"><input type="checkbox" name="trust" value="1"> Faire confiance à cet appareil pendant <?= mfa_trust_days() ?> jours (à éviter sur un ordinateur partagé)</label>
    <?php endif; ?>
    <button class="btn btn--primary btn--block" type="submit">Vérifier</button>
  </form>
  <?php if ($method === 'email'): ?>
    <form method="post" action="<?= e(url('login-2fa.php')) ?>" class="mt-1"><?= csrf_field() ?><input type="hidden" name="action" value="resend"><button class="btn btn--ghost btn--sm btn--block" type="submit">Renvoyer un code</button></form>
  <?php endif; ?>
  <p class="auth__alt"><?= $method === 'email' ? 'Plus accès à votre messagerie ?' : 'Téléphone perdu ?' ?> Saisissez l'un de vos codes de secours (format XXXXX-XXXXX), ou <a href="<?= e(url('contact.php')) ?>">contactez l'administration</a>.</p>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
