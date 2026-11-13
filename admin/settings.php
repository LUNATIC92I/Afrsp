<?php
/** Administration : paramètres institutionnels. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('settings.manage');

$keys = [
    'institution_name' => ['Nom de l\'institution', 'text', 190], 'contact_email' => ['E-mail de contact public', 'email', 190],
    'contact_phone' => ['Téléphone', 'text', 60], 'contact_address' => ['Adresse', 'textarea', 400], 'footer_tagline' => ['Texte de présentation (pied de page)', 'textarea', 400],
    'payment_manual_instructions' => ['Instructions de virement / dépôt (coordonnées bancaires, numéros Mobile Money marchands)', 'textarea', 1500],
    'promo_label' => ['Libellé de la promotion', 'text', 60], 'promo_ends_at' => ['Fin de la promotion (AAAA-MM-JJ HH:MM, UTC : vide = sans échéance)', 'text', 20],
    'security_contact' => ['E-mail de signalement des vulnérabilités (security.txt)', 'email', 190],
    'certificate_signatory_name' => ['Signataire affiché sur les certificats', 'text', 120], 'certificate_signatory_title' => ['Qualité du signataire', 'text', 160],
];
if (is_post() && in_array(input('action'), ['mail', 'mail_test', 'billing'], true)) {
    verify_csrf();
    $action = input('action');
    if ($action === 'mail') {
        $t = input('mail_transport');
        $err = [];
        if (!in_array($t, ['smtp', 'mail', 'log'], true)) $err[] = 'Mode d\'envoi invalide.';
        foreach (['mail_from' => 'Adresse d\'expédition', 'mail_reply_to' => 'Adresse de réponse'] as $k => $l) {
            if (input($k) !== '' && !filter_var(input($k), FILTER_VALIDATE_EMAIL)) $err[] = "$l invalide.";
        }
        if ($t === 'smtp') {
            if (!preg_match('/^[a-z0-9.-]{3,190}$/i', input('smtp_host'))) $err[] = 'Serveur SMTP invalide.';
            if (input_int('smtp_port') < 1 || input_int('smtp_port') > 65535) $err[] = 'Port SMTP invalide.';
            if (!in_array(input('smtp_secure'), ['tls', 'ssl', 'none'], true)) $err[] = 'Chiffrement invalide.';
            if (input('mail_from') === '') $err[] = 'L\'adresse d\'expédition est obligatoire en SMTP.';
        }
        if ($err) { flash('error', implode(' ', $err)); back(['tab' => 'mail']); }
        foreach (['mail_transport', 'mail_from', 'mail_from_name', 'mail_reply_to', 'smtp_host', 'smtp_secure', 'smtp_user'] as $k) set_setting($k, mb_substr(input($k), 0, 190));
        set_setting('smtp_port', (string) input_int('smtp_port', 587));
        set_setting('welcome_mail_enabled', isset($_POST['welcome_mail_enabled']) ? '1' : '0');
        // Mot de passe en écriture seule, chiffré ; vide = conservé ; case cochée = effacé
        if (isset($_POST['smtp_pass_clear'])) set_setting('smtp_pass_enc', '');
        elseif ((string) ($_POST['smtp_pass'] ?? '') !== '') set_setting('smtp_pass_enc', mail_encrypt_secret((string) $_POST['smtp_pass']));
        audit('settings.mail', 'settings', null, 'transport=' . $t);
        flash('success', 'Configuration de la messagerie enregistrée. Envoyez un e-mail de test pour la valider.');
        back(['tab' => 'mail']);
    }
    if ($action === 'mail_test') {
        rate_limit_or_fail('mail_test:user', (string) current_user()['id'], 10, 3600);
        $to = input('test_to') ?: current_user()['email'];
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { flash('error', 'Adresse de test invalide.'); back(['tab' => 'mail']); }
        $ok = mail_test($to);
        audit('settings.mail_test', 'settings', null, ($ok ? 'ok' : 'échec') . ' | ' . $to);
        $mode = mail_config()['transport'];
        flash($ok ? 'success' : 'error', $ok ? ($mode === 'log' ? 'Message de test enregistré dans storage/mail/ (mode journal : aucun envoi réel).' : "E-mail de test envoyé à $to.") : 'Échec de l\'envoi : vérifiez le serveur, le port, le chiffrement et les identifiants (détail dans storage/logs/php-error.log).');
        back(['tab' => 'mail']);
    }
    if ($action === 'billing') {
        $rate = str_replace(',', '.', input('tax_rate', '0'));
        if (!is_numeric($rate) || (float) $rate < 0 || (float) $rate > 50) { flash('error', 'Taux de taxe invalide (0 à 50 %).'); back(['tab' => 'billing']); }
        if (input('seller_email') !== '' && !filter_var(input('seller_email'), FILTER_VALIDATE_EMAIL)) { flash('error', 'E-mail du vendeur invalide.'); back(['tab' => 'billing']); }
        foreach (['seller_legal_name' => 190, 'seller_address' => 400, 'seller_tax_id' => 60, 'seller_registration' => 60, 'seller_email' => 190, 'tax_label' => 30, 'tax_exempt_mention' => 190, 'invoice_footer' => 400] as $k => $max) {
            set_setting($k, mb_substr(input($k), 0, $max));
        }
        set_setting('tax_rate', (string) round((float) $rate, 2));
        audit('settings.billing', 'settings', null, 'taux=' . $rate);
        flash('success', 'Informations de facturation enregistrées. Elles s\'appliquent aux prochaines factures (les factures émises restent inchangées).');
        back(['tab' => 'billing']);
    }
}
if (is_post()) {
    verify_csrf();
    $err = [];
    foreach ($keys as $k => [$label, $type, $max]) {
        $v = input($k);
        if (mb_strlen($v) > $max) $err[] = "« $label » est trop long.";
        if ($type === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) $err[] = 'E-mail invalide.';
        if ($k === 'promo_ends_at' && $v !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2})?$/', $v)) $err[] = 'Date de fin de promotion invalide (format AAAA-MM-JJ HH:MM).';
    }
    if ($err) { flash('error', implode(' ', $err)); back(); }
    foreach ($keys as $k => $_) set_setting($k, input($k));
    set_setting('signature_is_fictitious', isset($_POST['signature_is_fictitious']) ? '1' : '0');
    set_setting('mfa_required_staff', isset($_POST['mfa_required_staff']) ? '1' : '0');
    set_setting('payment_manual_enabled', isset($_POST['payment_manual_enabled']) ? '1' : '0');
    set_setting('registration_open', isset($_POST['registration_open']) ? '1' : '0');
    audit('settings.update', 'settings', null, implode(',', array_keys($keys)));
    flash('success', 'Paramètres enregistrés. Utilisez « Régénérer » dans Certificats pour appliquer la nouvelle signature aux PDF existants.');
    back();
}
$pageTitle = 'Paramètres';
$appArea = 'admin';
$activeApp = 'settings';
require APP_ROOT . '/includes/app-header.php';
$mc = mail_config(); ?>
<div class="grid-2 grid-2--even">
<form class="box reveal" method="post" novalidate id="mail">
  <div class="box__head"><h2>Messagerie (e-mails automatiques)</h2><span class="status-pill <?= $mc['transport'] === 'log' ? 'status-pill--warn' : 'status-pill--on' ?>"><?= e(['smtp' => 'SMTP', 'mail' => 'mail() du serveur', 'log' => 'Journal (aucun envoi)'][$mc['transport']] ?? $mc['transport']) ?></span></div>
  <div class="box__body">
    <?= csrf_field() ?><input type="hidden" name="action" value="mail">
    <?= sel('mail_transport', 'Mode d\'envoi', ['smtp' => 'SMTP (recommandé)', 'mail' => 'Fonction mail() de l\'hébergeur', 'log' => 'Journal : développement, aucun envoi réel'], setting('mail_transport') ?: $mc['transport']) ?>
    <div class="form-row"><?= fld('mail_from', 'Adresse d\'expédition', setting('mail_from') ?: (filter_var($mc['from'] ?? '', FILTER_VALIDATE_EMAIL) && !str_ends_with((string) $mc['from'], '@localhost') ? $mc['from'] : mail_default_from()), 'email', ['maxlength' => 190, 'placeholder' => 'no-reply@votre-domaine.org']) ?><?= fld('mail_from_name', 'Nom d\'expéditeur', setting('mail_from_name', "AFRICA'SPY Academy"), 'text', ['maxlength' => 120]) ?></div>
    <?= fld('mail_reply_to', 'Adresse de réponse (facultatif)', setting('mail_reply_to'), 'email', ['maxlength' => 190]) ?>
    <div class="form-row"><?= fld('smtp_host', 'Serveur SMTP', setting('smtp_host'), 'text', ['maxlength' => 190, 'placeholder' => 'smtp.votre-fournisseur.com']) ?><?= fld('smtp_port', 'Port', setting('smtp_port', '587'), 'number', ['min' => 1, 'max' => 65535]) ?></div>
    <div class="form-row"><?= sel('smtp_secure', 'Chiffrement', ['tls' => 'STARTTLS (port 587)', 'ssl' => 'SSL/TLS (port 465)', 'none' => 'Aucun (déconseillé)'], setting('smtp_secure', 'tls')) ?><?= fld('smtp_user', 'Identifiant SMTP', setting('smtp_user'), 'text', ['maxlength' => 190, 'autocomplete' => 'off']) ?></div>
    <?= fld('smtp_pass', 'Mot de passe SMTP', '', 'password', ['autocomplete' => 'new-password', 'placeholder' => setting('smtp_pass_enc') !== '' ? 'Mot de passe enregistré : laisser vide pour le conserver' : '']) ?>
    <?php if (setting('smtp_pass_enc') !== ''): ?><label class="check mb-2"><input type="checkbox" name="smtp_pass_clear" value="1"> Effacer le mot de passe enregistré</label><?php endif; ?>
    <label class="check mb-2"><input type="checkbox" name="welcome_mail_enabled" value="1" <?= setting('welcome_mail_enabled', '1') === '1' ? 'checked' : '' ?>> Envoyer l'e-mail de bienvenue après chaque inscription</label>
    <p class="disclaimer">Le mot de passe SMTP est chiffré (libsodium) avec la clé applicative et n'est jamais réaffiché. Pour la délivrabilité, configurez SPF, DKIM et DMARC sur le domaine d'expédition.</p>
    <button class="btn btn--primary" type="submit">Enregistrer la messagerie</button>
  </div>
</form>
<div class="stack">
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2>Tester l'envoi</h2></div>
    <div class="box__body"><?= csrf_field() ?><input type="hidden" name="action" value="mail_test">
      <?= fld('test_to', 'Destinataire du test', current_user()['email'], 'email', ['maxlength' => 190]) ?>
      <button class="btn btn--gold" type="submit">Envoyer un e-mail de test</button>
      <p class="dim mt-1" style="font-size:.85rem">E-mails automatiques : bienvenue à l'inscription, réinitialisation du mot de passe, confirmation de paiement avec facture PDF, avoir en cas de remboursement, correction des travaux.</p>
    </div>
  </form>
  <form class="box reveal" method="post" novalidate id="billing">
    <div class="box__head"><h2>Facturation</h2></div>
    <div class="box__body"><?= csrf_field() ?><input type="hidden" name="action" value="billing">
      <?= fld('seller_legal_name', 'Raison sociale', setting('seller_legal_name', setting('institution_name')), 'text', ['maxlength' => 190]) ?>
      <?= fld('seller_address', 'Adresse du siège', setting('seller_address'), 'textarea', ['rows' => 2, 'maxlength' => 400]) ?>
      <div class="form-row"><?= fld('seller_tax_id', 'N° d\'identification fiscale (NIF / IFU / NCC)', setting('seller_tax_id'), 'text', ['maxlength' => 60]) ?><?= fld('seller_registration', 'RCCM', setting('seller_registration'), 'text', ['maxlength' => 60]) ?></div>
      <?= fld('seller_email', 'E-mail de facturation', setting('seller_email'), 'email', ['maxlength' => 190]) ?>
      <div class="form-row"><?= fld('tax_rate', 'Taux de taxe (%)', setting('tax_rate', '0'), 'number', ['min' => 0, 'max' => 50, 'step' => '0.01'], 'Les prix affichés sont TTC ; la taxe est extraite du prix.') ?><?= fld('tax_label', 'Libellé de la taxe', setting('tax_label', 'TVA'), 'text', ['maxlength' => 30]) ?></div>
      <?= fld('tax_exempt_mention', 'Mention si taux = 0', setting('tax_exempt_mention'), 'text', ['maxlength' => 190]) ?>
      <?= fld('invoice_footer', 'Pied de facture', setting('invoice_footer'), 'textarea', ['rows' => 2, 'maxlength' => 400]) ?>
      <p class="disclaimer">Vérifiez votre régime fiscal (par exemple TVA à 18 % dans plusieurs pays de l'UEMOA) avec votre expert-comptable avant la mise en service.</p>
      <button class="btn btn--primary" type="submit">Enregistrer la facturation</button>
    </div>
  </form>
</div>
</div>
<form class="box reveal" method="post" novalidate>
  <div class="box__head"><h2>Paramètres institutionnels</h2></div>
  <div class="box__body form-grid">
    <?= csrf_field() ?>
    <?php foreach ($keys as $k => [$label, $type, $max]): ?><?= fld($k, $label, setting($k), $type, ['maxlength' => $max] + ($type === 'textarea' ? ['rows' => 3] : [])) ?><?php endforeach; ?>
    <label class="check full"><input type="checkbox" name="signature_is_fictitious" value="1" <?= setting('signature_is_fictitious', '1') === '1' ? 'checked' : '' ?>> Signature institutionnelle <strong>fictive</strong> (mention explicite sur les certificats : à décocher uniquement si un signataire réel et habilité est désigné)</label>
    <label class="check full"><input type="checkbox" name="mfa_required_staff" value="1" <?= setting('mfa_required_staff', '0') === '1' ? 'checked' : '' ?>> <strong>Double authentification obligatoire</strong> pour les administrateurs et responsables pédagogiques (recommandé ; activez d'abord la vôtre dans « Sécurité du compte »)</label>
    <label class="check full"><input type="checkbox" name="payment_manual_enabled" value="1" <?= setting('payment_manual_enabled', '1') === '1' ? 'checked' : '' ?>> Proposer le paiement par virement / dépôt (validation manuelle dans Administration / Paiements)</label>
    <label class="check full"><input type="checkbox" name="registration_open" value="1" <?= setting('registration_open', '1') === '1' ? 'checked' : '' ?>> Inscriptions publiques ouvertes</label>
    <div class="full"><button class="btn btn--primary" type="submit">Enregistrer</button></div>
  </div>
</form>
<section class="box reveal"><div class="box__head"><h2>Configuration technique (lecture seule)</h2></div><div class="box__body">
  <dl class="kv">
    <dt>URL de base</dt><dd class="mono"><?= e(config('base_url') ?: '(détection automatique)') ?></dd>
    <dt>Environnement</dt><dd><?= e(config('env')) ?></dd>
    <dt>Paiement CinetPay</dt><dd><?= config('payments.cinetpay_enabled') && config('payments.cinetpay_apikey') ? 'activé (site ' . e((string) config('payments.cinetpay_site_id')) . ')' : 'non configuré : voir la section 5 du README' ?></dd>
    <dt>Paiement de test</dt><dd><?= config('payments.sandbox') && config('env') !== 'production' ? '<span class="gold">actif (développement uniquement)</span>' : 'désactivé' ?></dd>
    <dt>Transport e-mail</dt><dd><?= e(config('mail.transport')) ?><?= config('mail.transport') === 'log' ? ' : les e-mails sont écrits dans storage/logs/mail.log' : '' ?></dd>
    <dt>PHP</dt><dd><?= e(PHP_VERSION) ?></dd>
    <dt>Base de données</dt><dd><?= e((string) q_val('SELECT VERSION()')) ?></dd>
  </dl>
  <p class="disclaimer mt-2">Les identifiants de base de données, la clé applicative et la configuration SMTP sont définis dans <code>config/config.local.php</code> (hors dépôt, inaccessible depuis le Web).</p>
</div></section>
<?php require APP_ROOT . '/includes/app-footer.php';
