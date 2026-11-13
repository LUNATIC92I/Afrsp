<?php
/**
 * AFRICA'SPY : messagerie transactionnelle.
 *
 * Configuration : les paramètres saisis dans Administration / Paramètres (table settings,
 * mot de passe SMTP chiffré) priment sur config/config.local.php.
 * Transports : smtp (recommandé), mail (fonction mail() du serveur), log (développement :
 * chaque message complet est enregistré en .eml dans storage/mail/, rien n'est envoyé).
 */
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

function mail_secret_key(): string
{
    return hash('sha256', 'afspy-smtp|' . config('app_key'), true);
}

function mail_encrypt_secret(string $plain): string
{
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($plain, $nonce, mail_secret_key()));
}

function mail_decrypt_secret(string $stored): string
{
    $raw = base64_decode($stored, true);
    if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return '';
    $p = sodium_crypto_secretbox_open(substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), mail_secret_key());
    return $p === false ? '' : $p;
}

/** Expéditeur par défaut : no-reply@<domaine de base_url> (à remplacer par une adresse réelle). */
function mail_default_from(): string
{
    $host = strtolower((string) parse_url((string) config('base_url'), PHP_URL_HOST));
    if (!str_contains($host, '.') || filter_var($host, FILTER_VALIDATE_IP)) $host = 'localhost.localdomain';
    return 'no-reply@' . preg_replace('/^www\./', '', $host);
}

/** Configuration effective de la messagerie. */
function mail_config(): array
{
    $c = (array) config('mail', []);
    $s = static fn (string $k) => setting($k);
    if (in_array($s('mail_transport'), ['smtp', 'mail', 'log'], true)) {
        $c['transport'] = $s('mail_transport');
        if ($c['transport'] === 'smtp') {
            $c['smtp_host'] = $s('smtp_host');
            $c['smtp_port'] = (int) ($s('smtp_port') ?: 587);
            $c['smtp_secure'] = $s('smtp_secure') === 'none' ? '' : $s('smtp_secure');
            $c['smtp_user'] = $s('smtp_user');
            $c['smtp_pass'] = $s('smtp_pass_enc') !== '' ? mail_decrypt_secret($s('smtp_pass_enc')) : '';
        }
    }
    if ($s('mail_from') !== '') $c['from'] = $s('mail_from');
    if ($s('mail_from_name') !== '') $c['from_name'] = $s('mail_from_name');
    $c['reply_to'] = $s('mail_reply_to') ?: setting('contact_email');
    $c['transport'] ??= 'log';
    return $c;
}

/**
 * Envoie un courriel. Compatible avec l'ancienne signature (texte seul).
 * $opts : html (string), attachments ([[contenu, nom, type MIME]]), reply_to (string), tag (string)
 * @return bool succès (en mode log : message enregistré)
 */
function send_mail(string $to, string $subject, string $text, array $opts = []): bool
{
    $cfg = mail_config();
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    try {
        $m = new PHPMailer(true);
        $m->CharSet = 'UTF-8';
        $m->Encoding = 'base64';
        $m->XMailer = ' ';
        $from = filter_var($cfg['from'] ?? '', FILTER_VALIDATE_EMAIL) ? $cfg['from'] : mail_default_from();
        $m->setFrom($from, (string) ($cfg['from_name'] ?? "AFRICA'SPY"));
        $m->addAddress($to);
        $reply = $opts['reply_to'] ?? ($cfg['reply_to'] ?? '');
        if ($reply && filter_var($reply, FILTER_VALIDATE_EMAIL)) $m->addReplyTo($reply);
        $m->Subject = $subject;
        if (!empty($opts['html'])) {
            $m->isHTML(true);
            $m->Body = $opts['html'];
            $m->AltBody = $text;
            $logo = APP_ROOT . '/assets/images/logo-email.png';
            if (str_contains($opts['html'], 'cid:afspy-logo') && is_file($logo)) {
                $m->addEmbeddedImage($logo, 'afspy-logo', 'africaspy.png', 'base64', 'image/png');
            }
        } else {
            $m->Body = $text;
        }
        foreach ($opts['attachments'] ?? [] as [$content, $name, $type]) {
            $m->addStringAttachment($content, $name, 'base64', $type);
        }
        $m->addCustomHeader('X-AFSPY-Tag', preg_replace('/[^a-z0-9_.-]/i', '', (string) ($opts['tag'] ?? 'general')));

        if ($cfg['transport'] === 'smtp') {
            $m->isSMTP();
            $m->Host = (string) $cfg['smtp_host'];
            $m->Port = (int) $cfg['smtp_port'];
            $m->SMTPAuth = ($cfg['smtp_user'] ?? '') !== '';
            $m->Username = (string) ($cfg['smtp_user'] ?? '');
            $m->Password = (string) ($cfg['smtp_pass'] ?? '');
            $m->SMTPSecure = (string) ($cfg['smtp_secure'] ?? '');
            $m->SMTPAutoTLS = ($cfg['smtp_secure'] ?? '') !== '';
            $m->Timeout = 15;
            $ok = $m->send();
        } elseif ($cfg['transport'] === 'mail') {
            $ok = $m->send();
        } else {
            // Mode journal : message MIME complet conservé pour inspection, rien n'est envoyé
            $m->preSend();
            $file = APP_ROOT . '/storage/mail/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '-' . preg_replace('/[^a-z0-9_.-]/i', '', (string) ($opts['tag'] ?? 'mail')) . '.eml';
            $ok = (bool) file_put_contents($file, $m->getSentMIMEMessage(), LOCK_EX);
            file_put_contents(APP_ROOT . '/storage/logs/mail.log', sprintf("[%s] TO:%s | %s\n%s\n-----\n", gmdate('c'), $to, $subject, $text), FILE_APPEND | LOCK_EX);
        }
        return $ok;
    } catch (Throwable $e) {
        error_log('[AFRICASPY mail] ' . $e->getMessage());
        return false;
    }
}

/* =====================================================================
 *  Gabarit HTML (tables + styles en ligne : compatible Gmail, Outlook, mobiles)
 * ===================================================================*/

function mail_layout(string $preheader, string $title, string $bodyHtml, ?array $cta = null): string
{
    $esc = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $btn = $cta ? '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0 8px"><tr><td style="border-radius:4px;background:#087CFF;background-image:linear-gradient(135deg,#087CFF,#00C8FF)">'
        . '<a href="' . $esc($cta[1]) . '" style="display:inline-block;padding:14px 26px;font:600 14px/1 Arial,Helvetica,sans-serif;letter-spacing:2px;text-transform:uppercase;color:#031019;text-decoration:none">' . $esc($cta[0]) . '</a></td></tr></table>' : '';
    $site = $esc(absolute_url('index.php'));
    $legal = $esc(setting('seller_legal_name', "AFRICA'SPY Intelligence Academy"));
    $addr = $esc(setting('seller_address', setting('contact_address')));
    return <<<HTML
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light dark"><title>{$esc($title)}</title></head>
<body style="margin:0;padding:0;background:#EEF1F5">
<div style="display:none;max-height:0;overflow:hidden;opacity:0">{$esc($preheader)}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#EEF1F5"><tr><td align="center" style="padding:24px 12px">
  <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#FFFFFF;border-radius:8px;overflow:hidden">
    <tr><td style="background:#05070B;padding:22px 28px;border-bottom:3px solid #D5B36A">
      <table role="presentation" cellpadding="0" cellspacing="0"><tr>
        <td style="padding-right:14px"><img src="cid:afspy-logo" width="52" height="52" alt="" style="display:block"></td>
        <td style="font:900 20px/1.1 Arial,Helvetica,sans-serif;letter-spacing:4px;color:#E8F2FA">AFRICA<span style="color:#D5B36A">'</span>SPY<br><span style="font:400 10px/1.8 'Courier New',monospace;letter-spacing:3px;color:#AEBBC9">INTELLIGENCE ACADEMY</span></td>
      </tr></table>
    </td></tr>
    <tr><td style="padding:32px 32px 12px;font:16px/1.65 Arial,Helvetica,sans-serif;color:#1B2430">
      <h1 style="margin:0 0 16px;font:700 22px/1.3 Arial,Helvetica,sans-serif;color:#05070B">{$esc($title)}</h1>
      {$bodyHtml}
      {$btn}
    </td></tr>
    <tr><td style="padding:20px 32px 28px;font:12px/1.6 Arial,Helvetica,sans-serif;color:#6B7686;border-top:1px solid #E3E7EC">
      {$legal}<br>{$addr}<br>
      <a href="{$site}" style="color:#087CFF">{$site}</a><br>
      Message automatique : merci de ne pas répondre directement si aucune adresse de réponse n'est indiquée. Vos données sont traitées conformément à nos mentions de confidentialité.
    </td></tr>
  </table>
</td></tr></table>
</body></html>
HTML;
}

/* =====================================================================
 *  Messages types
 * ===================================================================*/

/** E-mail de bienvenue envoyé automatiquement après l'inscription. */
function mail_welcome(array $user): bool
{
    if (setting('welcome_mail_enabled', '1') !== '1') return false;
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $catalog = absolute_url('academy.php');
    $dash = absolute_url('learner/dashboard.php');
    $security = absolute_url('learner/security.php');
    $html = '<p>Bonjour ' . $e($user['first_name']) . ',</p>'
        . '<p>Votre compte AFRICA\'SPY est créé. Bienvenue dans l\'Académie !</p>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:18px 0;border:1px solid #E3E7EC;border-radius:6px"><tr><td style="padding:16px 18px;font:14px/1.7 Arial,Helvetica,sans-serif">'
        . '<strong style="color:#05070B">Identifiant de connexion :</strong> ' . $e($user['email']) . '<br>'
        . '<strong style="color:#05070B">Date d\'inscription :</strong> ' . $e(fmt_datetime(gmdate('Y-m-d H:i:s'))) . '</td></tr></table>'
        . '<p style="margin-bottom:6px"><strong>Vos prochaines étapes</strong></p>'
        . '<ol style="padding-left:20px;margin-top:0">'
        . '<li>Choisissez une formation dans le <a href="' . $e($catalog) . '" style="color:#087CFF">catalogue</a>.</li>'
        . '<li>Réglez-la par Mobile Money, carte ou virement : l\'accès s\'ouvre dès confirmation et votre facture PDF vous est envoyée.</li>'
        . '<li>Suivez les leçons, validez les QCM, rendez vos travaux, puis passez l\'examen final pour obtenir votre certificat vérifiable.</li></ol>'
        . '<p style="padding:12px 14px;background:#F4F8FB;border-left:3px solid #00C8FF;font-size:14px">Conseil sécurité : activez la <a href="' . $e($security) . '" style="color:#087CFF">double authentification</a> sur votre compte. AFRICA\'SPY ne vous demandera jamais votre mot de passe par e-mail.</p>'
        . '<p style="font-size:14px;color:#4A5666">En rejoignant l\'Académie, vous vous êtes engagé(e) à respecter notre charte éthique : n\'utiliser les compétences acquises qu\'à des fins légales et légitimes.</p>';
    $text = "Bonjour {$user['first_name']},\n\nVotre compte AFRICA'SPY est créé. Bienvenue dans l'Académie !\n\nIdentifiant : {$user['email']}\n\n"
        . "Prochaines étapes :\n1. Choisissez une formation : $catalog\n2. Réglez-la (Mobile Money, carte ou virement) : votre facture PDF vous est envoyée.\n3. Suivez les leçons et passez l'examen final pour obtenir votre certificat.\n\n"
        . "Conseil : activez la double authentification : $security\nAFRICA'SPY ne vous demandera jamais votre mot de passe par e-mail.\n\nVotre espace : $dash";
    return send_mail($user['email'], "Bienvenue à l'Académie AFRICA'SPY", $text, [
        'html' => mail_layout('Votre compte est prêt : découvrez les formations.', 'Bienvenue, ' . $user['first_name'], $html, ['Accéder à mon espace', $dash]),
        'tag' => 'welcome',
    ]);
}

/** Réinitialisation du mot de passe. */
function mail_password_reset(array $user, string $link, int $ttlMinutes): bool
{
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $html = '<p>Bonjour ' . $e($user['first_name']) . ',</p><p>Une demande de réinitialisation du mot de passe a été effectuée pour votre compte.</p>'
        . '<p>Ce lien est valable <strong>' . $ttlMinutes . ' minutes</strong> et ne peut servir qu\'une seule fois.</p>'
        . '<p style="font-size:14px;color:#4A5666">Si vous n\'êtes pas à l\'origine de cette demande, ignorez ce message : votre mot de passe reste inchangé.</p>';
    $text = "Bonjour {$user['first_name']},\n\nUne demande de réinitialisation de mot de passe a été effectuée pour votre compte.\nCe lien est valable $ttlMinutes minutes et ne peut servir qu'une fois :\n\n$link\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.";
    return send_mail($user['email'], "AFRICA'SPY : Réinitialisation de votre mot de passe", $text, [
        'html' => mail_layout('Lien de réinitialisation valable ' . $ttlMinutes . ' minutes.', 'Réinitialisation du mot de passe', $html, ['Choisir un nouveau mot de passe', $link]),
        'tag' => 'reset',
    ]);
}

/** Message de test depuis l'administration. */
function mail_test(string $to): bool
{
    $cfg = mail_config();
    $html = '<p>Ce message confirme que la messagerie de la plateforme fonctionne.</p><p style="font:13px/1.7 \'Courier New\',monospace;color:#4A5666">Transport : ' . htmlspecialchars($cfg['transport']) . '<br>Expéditeur : ' . htmlspecialchars((string) ($cfg['from'] ?? '')) . '<br>Envoyé le : ' . gmdate('d/m/Y H:i') . ' UTC</p>';
    return send_mail($to, "AFRICA'SPY : E-mail de test", "La messagerie de la plateforme fonctionne. Transport : {$cfg['transport']}.", [
        'html' => mail_layout('Test de configuration de la messagerie.', 'Messagerie opérationnelle', $html), 'tag' => 'test',
    ]);
}
