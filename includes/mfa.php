<?php
/**
 * AFRICA'SPY : double authentification (2FA).
 * Méthodes : application d'authentification (TOTP, RFC 6238) ou code à usage unique par e-mail.
 * Compléments : codes de secours, appareils de confiance, politique d'obligation, alertes e-mail.
 * Les primitives TOTP et codes de secours sont dans security.php.
 */
declare(strict_types=1);

const MFA_EMAIL_TTL = 600;        // validité d'un code e-mail (secondes)
const MFA_EMAIL_MAX_TRIES = 5;    // essais par code e-mail

/* =====================================================================
 *  Politique
 * ===================================================================*/

/** optional | staff | all */
function mfa_policy(): string
{
    $p = setting('mfa_policy', 'optional');
    return in_array($p, ['optional', 'staff', 'all'], true) ? $p : 'optional';
}

function mfa_required_for_current(): bool
{
    return match (mfa_policy()) { 'all' => true, 'staff' => is_staff(), default => false };
}

function mfa_email_allowed(): bool
{
    return setting('mfa_email_enabled', '1') === '1';
}

/** Durée de confiance d'un appareil en jours (0 = option désactivée). */
function mfa_trust_days(): int
{
    return max(0, min(90, (int) setting('mfa_trust_days', '30')));
}

function mfa_method_label(?string $m): string
{
    return ['totp' => 'Application d\'authentification', 'email' => 'Code par e-mail'][$m ?? ''] ?? 'Aucune';
}

/** Désactive entièrement la 2FA d'un compte (secret, méthode, codes, appareils de confiance). */
function mfa_reset(int $userId): void
{
    q('UPDATE users SET totp_secret = NULL, totp_enabled = 0, mfa_method = NULL, totp_last_step = NULL, totp_recovery = NULL WHERE id = ?', [$userId]);
    q('DELETE FROM mfa_email_codes WHERE user_id = ?', [$userId]);
    mfa_forget_devices($userId);
}

/* =====================================================================
 *  Codes à usage unique par e-mail
 * ===================================================================*/

function mfa_code_hash(int $userId, string $purpose, string $code): string
{
    return hash_hmac('sha256', $userId . '|' . $purpose . '|' . $code, 'afspy-mfa-mail|' . config('app_key'));
}

/** Génère, enregistre (haché) et envoie un code à 6 chiffres. Le code précédent est remplacé. */
function mfa_email_send(array $user, string $purpose): bool
{
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    q('REPLACE INTO mfa_email_codes (user_id, purpose, code_hash, tries, expires_at, created_at) VALUES (?, ?, ?, 0, UTC_TIMESTAMP() + INTERVAL ? SECOND, UTC_TIMESTAMP())',
        [$user['id'], $purpose, mfa_code_hash((int) $user['id'], $purpose, $code), MFA_EMAIL_TTL]);
    $minutes = intdiv(MFA_EMAIL_TTL, 60);
    $what = $purpose === 'setup' ? 'activer la double authentification par e-mail' : 'terminer votre connexion';
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $html = '<p>Bonjour ' . $e($user['first_name']) . ',</p><p>Voici votre code de vérification pour ' . $what . ' :</p>'
        . '<p style="margin:22px 0;font:700 32px/1 \'Courier New\',monospace;letter-spacing:10px;color:#05070B;background:#F4F8FB;border:1px solid #E3E7EC;border-radius:6px;padding:18px;text-align:center">' . $code . '</p>'
        . '<p>Ce code est valable <strong>' . $minutes . ' minutes</strong> et ne peut servir qu\'une fois.</p>'
        . '<p style="font-size:14px;color:#4A5666">Demande effectuée depuis l\'adresse IP ' . $e(client_ip()) . ' le ' . $e(gmdate('d/m/Y à H:i')) . ' UTC. Si vous n\'êtes pas à l\'origine de cette demande, votre mot de passe est probablement connu d\'un tiers : changez-le sans attendre. Ne communiquez jamais ce code, même à l\'Académie.</p>';
    $text = "Bonjour {$user['first_name']},\n\nVotre code de vérification pour $what : $code\nValable $minutes minutes, à usage unique.\n\nSi vous n'êtes pas à l'origine de cette demande, changez votre mot de passe sans attendre. Ne communiquez jamais ce code.";
    return send_mail($user['email'], "AFRICA'SPY : code de vérification $code", $text, [
        'html' => mail_layout('Votre code de vérification, valable ' . $minutes . ' minutes.', 'Code de vérification', $html), 'tag' => 'mfa-code',
    ]);
}

/** Vérifie un code e-mail ; le consomme s'il est valide. Au-delà de 5 erreurs, le code est annulé. */
function mfa_email_verify(int $userId, string $purpose, string $code): bool
{
    $code = preg_replace('/\D/', '', $code);
    $row = q_one('SELECT code_hash, tries, expires_at < UTC_TIMESTAMP() AS expired FROM mfa_email_codes WHERE user_id = ? AND purpose = ?', [$userId, $purpose]);
    if (!$row || (int) $row['expired'] === 1 || (int) $row['tries'] >= MFA_EMAIL_MAX_TRIES || strlen($code) !== 6) {
        if ($row) q('UPDATE mfa_email_codes SET tries = tries + 1 WHERE user_id = ? AND purpose = ?', [$userId, $purpose]);
        return false;
    }
    if (!hash_equals($row['code_hash'], mfa_code_hash($userId, $purpose, $code))) {
        q('UPDATE mfa_email_codes SET tries = tries + 1 WHERE user_id = ? AND purpose = ?', [$userId, $purpose]);
        return false;
    }
    q('DELETE FROM mfa_email_codes WHERE user_id = ? AND purpose = ?', [$userId, $purpose]);
    return true;
}

/* =====================================================================
 *  Appareils de confiance (cookie signé : sélecteur + validateur haché en base)
 * ===================================================================*/

function mfa_device_cookie(): string
{
    return (is_https() && base_path() === '' ? '__Host-' : '') . 'AFSPY_TD';
}

function mfa_device_label(): string
{
    $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
    $os = preg_match('/Android/i', $ua) ? 'Android' : (preg_match('/iPhone|iPad/i', $ua) ? 'iOS' : (preg_match('/Windows/i', $ua) ? 'Windows' : (preg_match('/Mac OS X/i', $ua) ? 'macOS' : (preg_match('/Linux/i', $ua) ? 'Linux' : 'Système inconnu'))));
    $br = preg_match('/Edg\//', $ua) ? 'Edge' : (preg_match('/Firefox\//', $ua) ? 'Firefox' : (preg_match('/Chrome\//', $ua) ? 'Chrome' : (preg_match('/Safari\//', $ua) ? 'Safari' : 'Navigateur')));
    return "$br sur $os";
}

function mfa_set_device_cookie(string $value, int $expires): void
{
    setcookie(mfa_device_cookie(), $value, [
        'expires' => $expires, 'path' => base_path() === '' ? '/' : base_path() . '/',
        'secure' => is_https(), 'httponly' => true, 'samesite' => 'Lax',
    ]);
}

/** Mémorise l'appareil courant pour la durée configurée. */
function mfa_trust_device(int $userId): void
{
    $days = mfa_trust_days();
    if ($days === 0) return;
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    q('INSERT INTO mfa_trusted_devices (user_id, selector, token_hash, label, ip_address, expires_at) VALUES (?, ?, ?, ?, ?, UTC_TIMESTAMP() + INTERVAL ? DAY)',
        [$userId, $selector, hash('sha256', $validator), mfa_device_label(), client_ip(), $days]);
    mfa_set_device_cookie($selector . '.' . $validator, time() + $days * 86400);
    audit('security.mfa_device_trusted', 'user', $userId, mfa_device_label());
}

/** L'appareil courant est-il de confiance pour ce compte ? */
function mfa_device_trusted(int $userId): bool
{
    if (mfa_trust_days() === 0) return false;
    $raw = (string) ($_COOKIE[mfa_device_cookie()] ?? '');
    if (!preg_match('/^([a-f0-9]{24})\.([a-f0-9]{64})$/', $raw, $m)) return false;
    $row = q_one('SELECT id, user_id, token_hash FROM mfa_trusted_devices WHERE selector = ? AND expires_at > UTC_TIMESTAMP() AND created_at > UTC_TIMESTAMP() - INTERVAL ? DAY',
        [$m[1], mfa_trust_days()]);
    if (!$row || (int) $row['user_id'] !== $userId || !hash_equals($row['token_hash'], hash('sha256', $m[2]))) {
        return false;
    }
    q('UPDATE mfa_trusted_devices SET last_used_at = UTC_TIMESTAMP(), ip_address = ? WHERE id = ?', [client_ip(), $row['id']]);
    return true;
}

/** Oublie un appareil (ou tous) ; efface aussi le cookie local. */
function mfa_forget_devices(int $userId, ?int $deviceId = null): void
{
    if ($deviceId) {
        q('DELETE FROM mfa_trusted_devices WHERE id = ? AND user_id = ?', [$deviceId, $userId]);
    } else {
        q('DELETE FROM mfa_trusted_devices WHERE user_id = ?', [$userId]);
        if (isset($_COOKIE[mfa_device_cookie()]) && (int) ($_SESSION['uid'] ?? 0) === $userId) {
            mfa_set_device_cookie('', time() - 3600);
        }
    }
}

/* =====================================================================
 *  Alertes de sécurité par e-mail
 * ===================================================================*/

function mfa_notify(int $userId, string $event, string $detail = ''): void
{
    if (setting('mfa_notify', '1') !== '1') return;
    $u = q_one('SELECT first_name, email FROM users WHERE id = ?', [$userId]);
    if (!$u) return;
    $events = [
        'enabled' => ['Double authentification activée', 'La double authentification vient d\'être activée sur votre compte (méthode : %s).'],
        'disabled' => ['Double authentification désactivée', 'La double authentification vient d\'être désactivée sur votre compte. Votre compte n\'est plus protégé que par votre mot de passe.'],
        'admin_reset' => ['Double authentification réinitialisée', 'Un administrateur de l\'Académie a réinitialisé la double authentification de votre compte, à votre demande. Réactivez-la dès votre prochaine connexion.'],
        'recovery_regenerated' => ['Nouveaux codes de secours', 'De nouveaux codes de secours ont été générés : les anciens ne sont plus valables.'],
        'recovery_used' => ['Code de secours utilisé', 'Un code de secours vient d\'être utilisé pour vous connecter. Il vous en reste %s.'],
        'device_trusted' => ['Nouvel appareil de confiance', 'L\'appareil « %s » a été enregistré comme appareil de confiance : le code ne lui sera plus demandé pendant ' . mfa_trust_days() . ' jours.'],
    ];
    if (!isset($events[$event])) return;
    [$title, $msg] = $events[$event];
    $msg = sprintf($msg, $detail);
    $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $when = gmdate('d/m/Y à H:i') . ' UTC';
    $link = absolute_url('learner/security.php');
    $html = '<p>Bonjour ' . $e($u['first_name']) . ',</p><p>' . $e($msg) . '</p>'
        . '<p style="font:13px/1.7 \'Courier New\',monospace;color:#4A5666">Date : ' . $e($when) . '<br>Adresse IP : ' . $e(client_ip()) . '<br>Appareil : ' . $e(mfa_device_label()) . '</p>'
        . '<p style="padding:12px 14px;background:#FFF6F7;border-left:3px solid #FF4057;font-size:14px">Vous n\'êtes pas à l\'origine de cette opération ? Changez immédiatement votre mot de passe et contactez l\'administration de l\'Académie.</p>';
    $text = "Bonjour {$u['first_name']},\n\n$msg\n\nDate : $when\nAdresse IP : " . client_ip() . "\nAppareil : " . mfa_device_label() . "\n\nVous n'êtes pas à l'origine de cette opération ? Changez immédiatement votre mot de passe : $link";
    send_mail($u['email'], "AFRICA'SPY : alerte de sécurité, " . mb_strtolower($title), $text, [
        'html' => mail_layout($title . '.', $title, $html, ['Vérifier la sécurité du compte', $link]), 'tag' => 'mfa-' . $event,
    ]);
}
