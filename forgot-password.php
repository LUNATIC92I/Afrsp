<?php
/** Demande de réinitialisation du mot de passe (réponse identique que le compte existe ou non). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_guest();

$sent = false;
$authError = null;
if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('reset_request:ip', client_ip(), 10, 3600, 'Trop de demandes de réinitialisation depuis votre connexion. Réessayez dans une heure.');
    $email = mb_strtolower(input('email'));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $authError = 'Adresse e-mail invalide.';
    } else {
        $user = q_one("SELECT id, first_name FROM users WHERE email = ? AND status = 'active'", [$email]);
        $recent = $user ? (int) q_val('SELECT COUNT(*) FROM password_resets WHERE user_id = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)', [$user['id']]) : 0;
        if ($user && $recent < 3) {
            $token = bin2hex(random_bytes(32));
            q('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (?, ?, UTC_TIMESTAMP() + INTERVAL ? MINUTE, UTC_TIMESTAMP())',
                [$user['id'], hash('sha256', $token), (int) config('security.reset_ttl_min', 60)]);
            $link = absolute_url('reset-password.php', ['token' => $token]);
            send_mail($email, "AFRICA'SPY — Réinitialisation de votre mot de passe",
                "Bonjour {$user['first_name']},\n\nUne demande de réinitialisation de mot de passe a été effectuée pour votre compte.\nCe lien est valable " . (int) config('security.reset_ttl_min', 60) . " minutes et ne peut servir qu'une fois :\n\n$link\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message : votre mot de passe reste inchangé.\n\nAFRICA'SPY — Intelligence Academy");
            audit('auth.reset_request', 'user', (int) $user['id']);
        }
        $sent = true;
    }
}
$pageTitle = 'Mot de passe oublié';
$authTitle = 'Réinitialiser le mot de passe';
$authIntro = 'Indiquez l\'adresse e-mail de votre compte : vous recevrez un lien sécurisé à usage unique.';
$authTerminal = 'Procédure de récupération';
$authBody = function () use ($sent): void {
    if ($sent) { ?>
  <div class="alert alert--success" role="status">Si un compte actif correspond à cette adresse, un lien de réinitialisation vient d'être envoyé. Vérifiez votre boîte de réception (et les courriers indésirables).</div>
  <a class="btn btn--ghost" href="<?= e(url('login.php')) ?>">Retour à la connexion</a>
<?php return; } ?>
  <form method="post" action="<?= e(url('forgot-password.php')) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="email">Adresse e-mail</label><input class="input" type="email" id="email" name="email" required autocomplete="email" autofocus></div>
    <button class="btn btn--primary btn--block" type="submit">Envoyer le lien</button>
  </form>
  <p class="auth__alt"><a href="<?= e(url('login.php')) ?>">Retour à la connexion</a></p>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
