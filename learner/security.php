<?php
/** Sécurité du compte : activation / désactivation de la double authentification, codes de secours. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require_once APP_ROOT . '/includes/pdf.php';

$u = require_login();
$row = q_one('SELECT totp_enabled, totp_secret, totp_recovery, password_hash FROM users WHERE id = ?', [$u['id']]);
$enabled = (int) $row['totp_enabled'] === 1;
$newCodes = null;

if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('mfa_manage:user', (string) $u['id'], 20, 900);
    $action = input('action');
    $pwdOk = password_verify((string) ($_POST['password'] ?? ''), $row['password_hash']);
    if ($action === 'start' && !$enabled) {
        $_SESSION['totp_setup'] = base32_encode(random_bytes(20));
        redirect('learner/security.php');
    }
    if ($action === 'confirm' && !$enabled && !empty($_SESSION['totp_setup'])) {
        $step = totp_verify($_SESSION['totp_setup'], input('code'));
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } elseif ($step === null) {
            flash('error', 'Code invalide. Vérifiez que l\'heure de votre téléphone est automatique.');
        } else {
            [$newCodes, $hashes] = recovery_codes_generate();
            q('UPDATE users SET totp_secret = ?, totp_enabled = 1, totp_last_step = ?, totp_recovery = ? WHERE id = ?', [totp_encrypt($_SESSION['totp_setup']), $step, $hashes, $u['id']]);
            unset($_SESSION['totp_setup']);
            audit('security.mfa_enabled', 'user', (int) $u['id']);
            $enabled = true;
            flash('success', 'Double authentification activée. Conservez vos codes de secours en lieu sûr.');
        }
    }
    if ($action === 'disable' && $enabled) {
        $secret = totp_decrypt($row['totp_secret']);
        if (!$pwdOk || !$secret || totp_verify($secret, input('code')) === null) {
            flash('error', 'Mot de passe ou code invalide.');
        } elseif (is_staff() && setting('mfa_required_staff', '0') === '1') {
            flash('error', 'La double authentification est obligatoire pour les comptes du personnel.');
        } else {
            q('UPDATE users SET totp_secret = NULL, totp_enabled = 0, totp_last_step = NULL, totp_recovery = NULL WHERE id = ?', [$u['id']]);
            audit('security.mfa_disabled', 'user', (int) $u['id']);
            flash('success', 'Double authentification désactivée.');
            redirect('learner/security.php');
        }
    }
    if ($action === 'recovery' && $enabled) {
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } else {
            [$newCodes, $hashes] = recovery_codes_generate();
            q('UPDATE users SET totp_recovery = ? WHERE id = ?', [$hashes, $u['id']]);
            audit('security.mfa_recovery_regenerated', 'user', (int) $u['id']);
            flash('success', 'Nouveaux codes de secours générés : les anciens ne sont plus valables.');
        }
    }
    if ($newCodes === null && $action !== 'confirm') {
        redirect('learner/security.php');
    }
}
$setup = !$enabled ? ($_SESSION['totp_setup'] ?? null) : null;
$remaining = $enabled ? count(json_decode((string) q_val('SELECT totp_recovery FROM users WHERE id = ?', [$u['id']]), true) ?: []) : 0;
$sessions = q_all("SELECT created_at, ip_address, details FROM audit_logs WHERE user_id = ? AND action = 'auth.login' ORDER BY id DESC LIMIT 8", [$u['id']]);
$pageTitle = 'Sécurité du compte';
$appArea = 'learner';
$activeApp = 'security';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Double authentification (2FA)</h2><?= $enabled ? '<span class="status-pill status-pill--on">Activée</span>' : '<span class="status-pill status-pill--warn">Désactivée</span>' ?></div>
    <div class="box__body">
      <p>La double authentification protège votre compte même si votre mot de passe est dérobé : un code à 6 chiffres, généré toutes les 30 secondes par une application (Google Authenticator, Microsoft Authenticator, FreeOTP, Aegis...), est demandé à chaque connexion.</p>
      <?php if ($newCodes): ?>
        <div class="alert alert--warning"><strong>Codes de secours : affichés une seule fois.</strong> Chaque code ne sert qu'une fois si vous perdez votre téléphone. Imprimez-les ou conservez-les dans un gestionnaire de mots de passe.</div>
        <ul class="recovery mono"><?php foreach ($newCodes as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>
      <?php if (!$enabled && !$setup): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="start"><button class="btn btn--primary" type="submit">Activer la double authentification</button></form>
      <?php elseif ($setup): ?>
        <ol class="mfa-steps">
          <li>Scannez ce QR Code avec votre application d'authentification :<div class="mfa-qr"><img src="<?= e(qr_data_uri(totp_uri($setup, $u['email']))) ?>" alt="QR Code de configuration de la double authentification" width="200" height="200"></div>
            <small class="dim">Ou saisissez la clé manuellement : <code class="mono"><?= e(trim(chunk_split($setup, 4, ' '))) ?></code></small></li>
          <li>Saisissez le code affiché et votre mot de passe pour confirmer :</li>
        </ol>
        <form method="post" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="confirm">
          <div class="form-row"><?= '' ?>
            <div class="field"><label for="code">Code à 6 chiffres</label><input class="input mono" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required></div>
            <div class="field"><label for="password">Mot de passe</label><input class="input" type="password" id="password" name="password" autocomplete="current-password" required></div>
          </div>
          <button class="btn btn--primary" type="submit">Confirmer l'activation</button>
        </form>
      <?php else: ?>
        <p class="mono dim" style="font-size:.8rem">Codes de secours restants : <?= $remaining ?> / 8</p>
        <form method="post" class="mt-1" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="recovery">
          <div class="field"><label for="pw1">Mot de passe</label><input class="input" type="password" id="pw1" name="password" autocomplete="current-password" required></div>
          <button class="btn btn--gold btn--sm" type="submit">Régénérer les codes de secours</button>
        </form>
        <hr>
        <form method="post" novalidate data-confirm="Désactiver la double authentification réduit la protection de votre compte. Continuer ?"><?= csrf_field() ?><input type="hidden" name="action" value="disable">
          <div class="form-row">
            <div class="field"><label for="code2">Code actuel</label><input class="input mono" id="code2" name="code" inputmode="numeric" maxlength="6" required></div>
            <div class="field"><label for="pw2">Mot de passe</label><input class="input" type="password" id="pw2" name="password" autocomplete="current-password" required></div>
          </div>
          <button class="btn btn--danger btn--sm" type="submit">Désactiver</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Connexions récentes</h2><span class="mono">Signalez toute connexion inconnue</span></div>
    <ul class="feed">
      <?php if (!$sessions): ?><li><span class="dim">Aucune connexion enregistrée.</span><time></time></li><?php endif; ?>
      <?php foreach ($sessions as $s): ?><li><span><?= e(fmt_datetime($s['created_at'])) ?> | IP <?= e((string) $s['ip_address']) ?><?= $s['details'] ? ' | ' . e($s['details']) : '' ?></span><time><?= e(time_ago($s['created_at'])) ?></time></li><?php endforeach; ?>
    </ul>
    <div class="box__body"><p class="disclaimer">Une connexion que vous ne reconnaissez pas ? Changez immédiatement votre mot de passe depuis votre profil : toutes les autres sessions seront fermées.</p></div>
  </section>
</div>
<?php require APP_ROOT . '/includes/app-footer.php';
