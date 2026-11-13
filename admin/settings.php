<?php
/** Administration — paramètres institutionnels. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('settings.manage');

$keys = [
    'institution_name' => ['Nom de l\'institution', 'text', 190], 'contact_email' => ['E-mail de contact public', 'email', 190],
    'contact_phone' => ['Téléphone', 'text', 60], 'contact_address' => ['Adresse', 'textarea', 400], 'footer_tagline' => ['Texte de présentation (pied de page)', 'textarea', 400],
    'certificate_signatory_name' => ['Signataire affiché sur les certificats', 'text', 120], 'certificate_signatory_title' => ['Qualité du signataire', 'text', 160],
];
if (is_post()) {
    verify_csrf();
    $err = [];
    foreach ($keys as $k => [$label, $type, $max]) {
        $v = input($k);
        if (mb_strlen($v) > $max) $err[] = "« $label » est trop long.";
        if ($type === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $err[] = 'E-mail invalide.';
    }
    if ($err) { flash('error', implode(' ', $err)); back(); }
    foreach ($keys as $k => $_) set_setting($k, input($k));
    set_setting('signature_is_fictitious', isset($_POST['signature_is_fictitious']) ? '1' : '0');
    set_setting('registration_open', isset($_POST['registration_open']) ? '1' : '0');
    audit('settings.update', 'settings', null, implode(',', array_keys($keys)));
    flash('success', 'Paramètres enregistrés. Utilisez « Régénérer » dans Certificats pour appliquer la nouvelle signature aux PDF existants.');
    back();
}
$pageTitle = 'Paramètres';
$appArea = 'admin';
$activeApp = 'settings';
require APP_ROOT . '/includes/app-header.php'; ?>
<form class="box reveal" method="post" novalidate>
  <div class="box__head"><h2>Paramètres institutionnels</h2></div>
  <div class="box__body form-grid">
    <?= csrf_field() ?>
    <?php foreach ($keys as $k => [$label, $type, $max]): ?><?= fld($k, $label, setting($k), $type, ['maxlength' => $max] + ($type === 'textarea' ? ['rows' => 3] : [])) ?><?php endforeach; ?>
    <label class="check full"><input type="checkbox" name="signature_is_fictitious" value="1" <?= setting('signature_is_fictitious', '1') === '1' ? 'checked' : '' ?>> Signature institutionnelle <strong>fictive</strong> (mention explicite sur les certificats — à décocher uniquement si un signataire réel et habilité est désigné)</label>
    <label class="check full"><input type="checkbox" name="registration_open" value="1" <?= setting('registration_open', '1') === '1' ? 'checked' : '' ?>> Inscriptions publiques ouvertes</label>
    <div class="full"><button class="btn btn--primary" type="submit">Enregistrer</button></div>
  </div>
</form>
<section class="box reveal"><div class="box__head"><h2>Configuration technique (lecture seule)</h2></div><div class="box__body">
  <dl class="kv">
    <dt>URL de base</dt><dd class="mono"><?= e(config('base_url') ?: '(détection automatique)') ?></dd>
    <dt>Environnement</dt><dd><?= e(config('env')) ?></dd>
    <dt>Transport e-mail</dt><dd><?= e(config('mail.transport')) ?><?= config('mail.transport') === 'log' ? ' — les e-mails sont écrits dans storage/logs/mail.log' : '' ?></dd>
    <dt>PHP</dt><dd><?= e(PHP_VERSION) ?></dd>
    <dt>Base de données</dt><dd><?= e((string) q_val('SELECT VERSION()')) ?></dd>
  </dl>
  <p class="disclaimer mt-2">Les identifiants de base de données, la clé applicative et la configuration SMTP sont définis dans <code>config/config.local.php</code> (hors dépôt, inaccessible depuis le Web).</p>
</div></section>
<?php require APP_ROOT . '/includes/app-footer.php';
