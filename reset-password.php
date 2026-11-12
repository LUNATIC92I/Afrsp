<?php
/** Définition d'un nouveau mot de passe à partir d'un jeton à usage unique. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$token = input('token');
$row = preg_match('/^[a-f0-9]{64}$/', $token)
    ? q_one('SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > UTC_TIMESTAMP()', [hash('sha256', $token)])
    : null;
$authError = $row ? null : 'Ce lien est invalide, a déjà été utilisé ou a expiré. Effectuez une nouvelle demande.';
if ($row && is_post()) {
    verify_csrf();
    $pwd = (string) ($_POST['password'] ?? '');
    if ($pwd !== (string) ($_POST['password_confirm'] ?? '')) {
        $authError = 'Les deux mots de passe ne correspondent pas.';
    } elseif ($e = password_policy_error($pwd)) {
        $authError = $e;
    } else {
        q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($pwd, PASSWORD_DEFAULT), $row['user_id']]);
        q('UPDATE password_resets SET used_at = UTC_TIMESTAMP() WHERE user_id = ? AND used_at IS NULL', [$row['user_id']]);
        q('DELETE FROM login_attempts WHERE email = ?', [$row['email']]);
        logout_user();
        start_secure_session();
        login_user((int) $row['user_id']);
        audit('auth.reset_done', 'user', (int) $row['user_id']);
        flash('success', 'Mot de passe mis à jour. Vos autres sessions ont été fermées.');
        redirect(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php');
    }
}
$pageTitle = 'Nouveau mot de passe';
$authTitle = 'Nouveau mot de passe';
$authIntro = 'Choisissez un mot de passe robuste que vous n\'utilisez nulle part ailleurs.';
$authTerminal = 'Jeton de récupération';
$authBody = function () use ($row, $token): void {
    if (!$row) { echo '<a class="btn btn--ghost" href="' . e(url('forgot-password.php')) . '">Nouvelle demande</a>'; return; } ?>
  <form method="post" action="<?= e(url('reset-password.php', ['token' => $token])) ?>" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="password">Nouveau mot de passe</label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"><div class="pw-meter" data-pw-meter="password"><span></span></div></div>
    <div class="field"><label for="password_confirm">Confirmation</label><input class="input" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"></div>
    <button class="btn btn--primary btn--block" type="submit">Enregistrer</button>
  </form>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
