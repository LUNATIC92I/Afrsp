<?php
/** Sécurité du compte : double authentification (application ou e-mail), codes de secours, appareils de confiance. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require_once APP_ROOT . '/includes/pdf.php';

$u = require_login();
$row = q_one('SELECT totp_enabled, mfa_method, totp_secret, totp_recovery, password_hash FROM users WHERE id = ?', [$u['id']]);
$enabled = (int) $row['totp_enabled'] === 1;
$required = mfa_required_for_current();
$newCodes = null;

/** Active la 2FA avec la méthode donnée et renvoie les codes de secours en clair. */
$activate = static function (string $method, ?string $secret, ?int $step) use ($u): array {
    [$codes, $hashes] = recovery_codes_generate();
    q('UPDATE users SET totp_secret = ?, totp_enabled = 1, mfa_method = ?, totp_last_step = ?, totp_recovery = ? WHERE id = ?',
        [$secret ? totp_encrypt($secret) : null, $method, $step, $hashes, $u['id']]);
    unset($_SESSION['totp_setup'], $_SESSION['mfa_email_setup']);
    audit('security.mfa_enabled', 'user', (int) $u['id'], mfa_method_label($method));
    mfa_notify((int) $u['id'], 'enabled', mfa_method_label($method));
    flash('success', 'Double authentification activée. Conservez vos codes de secours en lieu sûr.');
    return $codes;
};

if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('mfa_manage:user', (string) $u['id'], 20, 900);
    $action = input('action');
    $pwdOk = password_verify((string) ($_POST['password'] ?? ''), $row['password_hash']);

    // ---- Activation : application d'authentification
    if ($action === 'start' && !$enabled) {
        unset($_SESSION['mfa_email_setup']);
        $_SESSION['totp_setup'] = base32_encode(random_bytes(20));
        redirect('learner/security.php');
    }
    if ($action === 'confirm' && !$enabled && !empty($_SESSION['totp_setup'])) {
        $step = totp_verify($_SESSION['totp_setup'], input('code'));
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } elseif ($step === null) {
            flash('error', 'Code invalide. Vérifiez que l\'heure de votre téléphone est réglée automatiquement.');
        } else {
            $newCodes = $activate('totp', $_SESSION['totp_setup'], $step);
            $enabled = true;
        }
    }
    // ---- Activation : code par e-mail
    if ($action === 'start_email' && !$enabled && mfa_email_allowed()) {
        unset($_SESSION['totp_setup']);
        if (rate_limit('mfa_mail:user', (string) $u['id'], 6, 900)) {
            mfa_email_send($u, 'setup');
            $_SESSION['mfa_email_setup'] = true;
            flash('info', 'Un code de confirmation vient d\'être envoyé à ' . $u['email'] . '.');
        } else {
            flash('error', 'Trop d\'envois de codes. Patientez 15 minutes.');
        }
        redirect('learner/security.php');
    }
    if ($action === 'confirm_email' && !$enabled && !empty($_SESSION['mfa_email_setup'])) {
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } elseif (!mfa_email_verify((int) $u['id'], 'setup', input('code'))) {
            flash('error', 'Code invalide ou expiré. Demandez un nouveau code si nécessaire.');
        } else {
            $newCodes = $activate('email', null, null);
            $enabled = true;
        }
    }
    if ($action === 'cancel_setup') {
        unset($_SESSION['totp_setup'], $_SESSION['mfa_email_setup']);
        redirect('learner/security.php');
    }

    // ---- Désactivation (mot de passe + code de la méthode active ou code de secours)
    if ($action === 'send_code' && $enabled && $row['mfa_method'] === 'email') {
        if (rate_limit('mfa_mail:user', (string) $u['id'], 6, 900)) {
            mfa_email_send($u, 'setup');
            flash('info', 'Code envoyé à ' . $u['email'] . '.');
        } else {
            flash('error', 'Trop d\'envois de codes. Patientez 15 minutes.');
        }
        redirect('learner/security.php');
    }
    if ($action === 'disable' && $enabled) {
        $code = trim(input('code'));
        $codeOk = false;
        if ($pwdOk) {
            if (preg_match('/^[A-Za-z0-9]{5}-[A-Za-z0-9]{5}$/', $code)) {
                $codeOk = recovery_code_use((int) $u['id'], $code);
            } elseif ($row['mfa_method'] === 'email') {
                $codeOk = mfa_email_verify((int) $u['id'], 'setup', $code);
            } else {
                $secret = totp_decrypt($row['totp_secret']);
                $codeOk = $secret && totp_verify($secret, $code) !== null;
            }
        }
        if (!$pwdOk || !$codeOk) {
            flash('error', 'Mot de passe ou code invalide.');
        } elseif ($required) {
            flash('error', 'La double authentification est obligatoire pour votre compte : vous pouvez changer de méthode, mais pas la désactiver.');
        } else {
            mfa_reset((int) $u['id']);
            audit('security.mfa_disabled', 'user', (int) $u['id']);
            mfa_notify((int) $u['id'], 'disabled');
            flash('success', 'Double authentification désactivée.');
            redirect('learner/security.php');
        }
    }
    // ---- Changer de méthode : désactive puis relance la configuration (mot de passe exigé)
    if ($action === 'switch' && $enabled) {
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } else {
            $target = input('method') === 'email' && mfa_email_allowed() ? 'email' : 'totp';
            mfa_reset((int) $u['id']);
            audit('security.mfa_method_change', 'user', (int) $u['id'], 'vers ' . mfa_method_label($target));
            flash('info', 'Configurez maintenant votre nouvelle méthode. Tant qu\'elle n\'est pas confirmée, votre compte n\'est protégé que par le mot de passe.');
            if ($target === 'email') {
                mfa_email_send($u, 'setup');
                $_SESSION['mfa_email_setup'] = true;
            } else {
                $_SESSION['totp_setup'] = base32_encode(random_bytes(20));
            }
            redirect('learner/security.php');
        }
    }
    // ---- Codes de secours
    if ($action === 'recovery' && $enabled) {
        if (!$pwdOk) {
            flash('error', 'Mot de passe incorrect.');
        } else {
            [$newCodes, $hashes] = recovery_codes_generate();
            q('UPDATE users SET totp_recovery = ? WHERE id = ?', [$hashes, $u['id']]);
            audit('security.mfa_recovery_regenerated', 'user', (int) $u['id']);
            mfa_notify((int) $u['id'], 'recovery_regenerated');
            flash('success', 'Nouveaux codes de secours générés : les anciens ne sont plus valables.');
        }
    }
    // ---- Appareils de confiance
    if ($action === 'forget_device') {
        mfa_forget_devices((int) $u['id'], input_int('device'));
        audit('security.mfa_device_forgotten', 'user', (int) $u['id']);
        flash('success', 'Appareil retiré : le code lui sera de nouveau demandé.');
        redirect('learner/security.php');
    }
    if ($action === 'forget_all') {
        mfa_forget_devices((int) $u['id']);
        audit('security.mfa_device_forgotten', 'user', (int) $u['id'], 'tous');
        flash('success', 'Tous les appareils de confiance ont été retirés.');
        redirect('learner/security.php');
    }
    if ($newCodes === null && !in_array($action, ['confirm', 'confirm_email'], true)) {
        redirect('learner/security.php');
    }
    $row = q_one('SELECT totp_enabled, mfa_method, totp_secret, totp_recovery, password_hash FROM users WHERE id = ?', [$u['id']]);
}
$setup = !$enabled ? ($_SESSION['totp_setup'] ?? null) : null;
$emailSetup = !$enabled && !$setup && !empty($_SESSION['mfa_email_setup']);
$method = $row['mfa_method'] ?: ($enabled ? 'totp' : null);
$remaining = $enabled ? count(json_decode((string) $row['totp_recovery'], true) ?: []) : 0;
$devices = q_all('SELECT id, selector, label, ip_address, created_at, last_used_at, expires_at FROM mfa_trusted_devices WHERE user_id = ? AND expires_at > UTC_TIMESTAMP() ORDER BY COALESCE(last_used_at, created_at) DESC', [$u['id']]);
$currentSel = preg_match('/^([a-f0-9]{24})\./', (string) ($_COOKIE[mfa_device_cookie()] ?? ''), $m) ? $m[1] : null;
$sessions = q_all("SELECT created_at, ip_address, details FROM audit_logs WHERE user_id = ? AND action = 'auth.login' ORDER BY id DESC LIMIT 8", [$u['id']]);
$pwdField = static fn (string $id) => '<div class="field"><label for="' . $id . '">Mot de passe</label><input class="input" type="password" id="' . $id . '" name="password" autocomplete="current-password" required></div>';
$pageTitle = 'Sécurité du compte';
$appArea = 'learner';
$activeApp = 'security';
require APP_ROOT . '/includes/app-header.php'; ?>
<?php if ($required && !$enabled): ?>
  <div class="alert alert--warning reveal"><strong>Action requise.</strong> La politique de sécurité de l'Académie impose la double authentification pour <?= mfa_policy() === 'all' ? 'tous les comptes' : 'les comptes du personnel' ?>. Les autres pages restent inaccessibles tant qu'elle n'est pas activée.</div>
<?php endif; ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Double authentification (2FA)</h2><?= $enabled ? '<span class="status-pill status-pill--on">Activée</span>' : '<span class="status-pill status-pill--warn">Désactivée</span>' ?></div>
    <div class="box__body">
      <p>La double authentification protège votre compte même si votre mot de passe est dérobé : après le mot de passe, un code à usage unique vous est demandé.</p>
      <?php if ($newCodes): ?>
        <div class="alert alert--warning"><strong>Codes de secours, affichés une seule fois.</strong> Chaque code permet une connexion si vous perdez votre téléphone ou l'accès à votre messagerie. Imprimez-les ou conservez-les dans un gestionnaire de mots de passe.</div>
        <ul class="recovery mono"><?php foreach ($newCodes as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
      <?php endif; ?>

      <?php if (!$enabled && !$setup && !$emailSetup): ?>
        <div class="mfa-methods">
          <div class="mfa-method">
            <h3>Application d'authentification <span class="tag tag--green">Recommandé</span></h3>
            <p class="dim">Google Authenticator, Microsoft Authenticator, FreeOTP, Aegis ou 2FAS. Fonctionne sans réseau ; le code change toutes les 30 secondes.</p>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="start"><button class="btn btn--primary" type="submit">Configurer l'application</button></form>
          </div>
          <?php if (mfa_email_allowed()): ?>
          <div class="mfa-method">
            <h3>Code par e-mail</h3>
            <p class="dim">Un code à 6 chiffres est envoyé à <?= e($u['email']) ?> à chaque connexion. Plus simple, mais moins sûr si votre messagerie est compromise.</p>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="start_email"><button class="btn" type="submit">Recevoir un code de confirmation</button></form>
          </div>
          <?php endif; ?>
        </div>
      <?php elseif ($setup): ?>
        <ol class="mfa-steps">
          <li>Scannez ce QR Code avec votre application d'authentification :<div class="mfa-qr"><img src="<?= e(qr_data_uri(totp_uri($setup, $u['email']))) ?>" alt="QR Code de configuration de la double authentification" width="200" height="200"></div>
            <small class="dim">Ou saisissez la clé manuellement : <code class="mono"><?= e(trim(chunk_split($setup, 4, ' '))) ?></code></small></li>
          <li>Saisissez le code affiché et votre mot de passe pour confirmer :</li>
        </ol>
        <form method="post" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="confirm">
          <div class="form-row">
            <div class="field"><label for="code">Code à 6 chiffres</label><input class="input mono" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required></div>
            <?= $pwdField('password') ?>
          </div>
          <button class="btn btn--primary" type="submit">Confirmer l'activation</button>
        </form>
        <form method="post" class="mt-1"><?= csrf_field() ?><input type="hidden" name="action" value="cancel_setup"><button class="btn btn--ghost btn--sm" type="submit">Annuler</button></form>
      <?php elseif ($emailSetup): ?>
        <p>Saisissez le code reçu à <strong><?= e($u['email']) ?></strong> (valable 10 minutes) et votre mot de passe :</p>
        <form method="post" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="confirm_email">
          <div class="form-row">
            <div class="field"><label for="code">Code reçu par e-mail</label><input class="input mono" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required></div>
            <?= $pwdField('password') ?>
          </div>
          <button class="btn btn--primary" type="submit">Confirmer l'activation</button>
        </form>
        <div class="flex mt-1" style="gap:10px">
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="start_email"><button class="btn btn--sm" type="submit">Renvoyer un code</button></form>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel_setup"><button class="btn btn--ghost btn--sm" type="submit">Annuler</button></form>
        </div>
      <?php else: ?>
        <dl class="kv"><dt>Méthode</dt><dd><?= e(mfa_method_label($method)) ?></dd><dt>Codes de secours restants</dt><dd class="mono"><?= $remaining ?> / 8<?= $remaining <= 2 ? ' <span class="status-pill status-pill--warn">À régénérer</span>' : '' ?></dd></dl>
        <form method="post" class="mt-1" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="recovery">
          <?= $pwdField('pw1') ?>
          <button class="btn btn--gold btn--sm" type="submit">Régénérer les codes de secours</button>
        </form>
        <hr>
        <form method="post" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="switch">
          <div class="form-row">
            <?= sel_method($method) ?>
            <?= $pwdField('pw3') ?>
          </div>
          <button class="btn btn--sm" type="submit">Changer de méthode</button>
        </form>
        <?php if (!$required): ?>
        <hr>
        <?php if ($method === 'email'): ?>
          <form method="post" class="mb-1"><?= csrf_field() ?><input type="hidden" name="action" value="send_code"><button class="btn btn--ghost btn--sm" type="submit">Recevoir un code pour désactiver</button></form>
        <?php endif; ?>
        <form method="post" novalidate data-confirm="Désactiver la double authentification réduit la protection de votre compte. Continuer ?"><?= csrf_field() ?><input type="hidden" name="action" value="disable">
          <div class="form-row">
            <div class="field"><label for="code2"><?= $method === 'email' ? 'Code reçu par e-mail' : 'Code actuel' ?> ou code de secours</label><input class="input mono" id="code2" name="code" maxlength="11" required></div>
            <?= $pwdField('pw2') ?>
          </div>
          <button class="btn btn--danger btn--sm" type="submit">Désactiver</button>
        </form>
        <?php else: ?>
          <p class="disclaimer mt-1">La double authentification est obligatoire pour votre compte : elle ne peut pas être désactivée.</p>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </section>
  <div class="stack">
    <?php if (mfa_trust_days() > 0): ?>
    <section class="box reveal">
      <div class="box__head"><h2>Appareils de confiance</h2><span class="mono"><?= mfa_trust_days() ?> jours</span></div>
      <ul class="feed">
        <?php if (!$devices): ?><li><span class="dim">Aucun appareil. Cochez « Faire confiance à cet appareil » lors de la vérification pour ne plus saisir de code sur un appareil personnel.</span><time></time></li><?php endif; ?>
        <?php foreach ($devices as $d): ?>
          <li><span><strong><?= e((string) $d['label']) ?></strong><?= $d['selector'] === $currentSel ? ' <span class="tag tag--green">Cet appareil</span>' : '' ?><br><small class="dim">Ajouté le <?= e(fmt_datetime($d['created_at'])) ?> | IP <?= e((string) $d['ip_address']) ?> | expire le <?= e(fmt_date($d['expires_at'])) ?></small></span>
            <time><?= action_form('forget_device', ['device' => $d['id']], 'Retirer') ?></time></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($devices): ?><div class="box__body"><?= action_form('forget_all', [], 'Retirer tous les appareils', 'btn--sm btn--danger') ?></div><?php endif; ?>
    </section>
    <?php endif; ?>
    <section class="box reveal">
      <div class="box__head"><h2>Connexions récentes</h2><span class="mono">Signalez toute connexion inconnue</span></div>
      <ul class="feed">
        <?php if (!$sessions): ?><li><span class="dim">Aucune connexion enregistrée.</span><time></time></li><?php endif; ?>
        <?php foreach ($sessions as $s): ?><li><span><?= e(fmt_datetime($s['created_at'])) ?> | IP <?= e((string) $s['ip_address']) ?><?= $s['details'] ? ' | ' . e($s['details']) : '' ?></span><time><?= e(time_ago($s['created_at'])) ?></time></li><?php endforeach; ?>
      </ul>
      <div class="box__body"><p class="disclaimer">Une connexion que vous ne reconnaissez pas ? Changez immédiatement votre mot de passe depuis votre profil : toutes les autres sessions et tous les appareils de confiance seront déconnectés. Vous recevez une alerte par e-mail à chaque changement de vos réglages de sécurité.</p></div>
    </section>
  </div>
</div>
<?php require APP_ROOT . '/includes/app-footer.php';

/** Sélecteur de méthode pour le changement. */
function sel_method(?string $current): string
{
    $o = '<option value="totp"' . ($current !== 'totp' ? ' selected' : '') . '>Application d\'authentification</option>';
    if (mfa_email_allowed()) $o .= '<option value="email"' . ($current === 'totp' ? ' selected' : '') . '>Code par e-mail</option>';
    return '<div class="field"><label for="f_method">Nouvelle méthode</label><select class="select" id="f_method" name="method">' . $o . '</select></div>';
}

/** Petit formulaire POST protégé CSRF. */
function action_form(string $action, array $hidden, string $label, string $cls = 'btn--sm btn--ghost'): string
{
    $h = csrf_field() . '<input type="hidden" name="action" value="' . e($action) . '">';
    foreach ($hidden as $k => $v) $h .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    return '<form method="post">' . $h . '<button class="btn ' . e($cls) . '" type="submit">' . e($label) . '</button></form>';
}
