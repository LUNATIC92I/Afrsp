<?php
/**
 * AFRICA'SPY — primitives de sécurité :
 * limitation de débit, hachage des mots de passe, politique de mot de passe,
 * double authentification TOTP (RFC 6238) avec codes de secours.
 */
declare(strict_types=1);

/* =====================================================================
 *  Limitation de débit (compteur à fenêtre fixe, atomique en base)
 * ===================================================================*/

/**
 * Comptabilise un événement et indique s'il reste sous le seuil.
 * @param string $action identifiant de l'action (ex. 'reset:ip')
 * @param string $key    discriminant (adresse IP, e-mail, identifiant…)
 */
function rate_limit(string $action, string $key, int $max, int $windowSeconds): bool
{
    $bucket = substr($action, 0, 40) . ':' . hash('sha256', $key);
    try {
        q('INSERT INTO rate_limits (bucket, hits, window_start) VALUES (?, 1, UTC_TIMESTAMP())
           ON DUPLICATE KEY UPDATE
             hits = IF(window_start < UTC_TIMESTAMP() - INTERVAL ? SECOND, 1, hits + 1),
             window_start = IF(window_start < UTC_TIMESTAMP() - INTERVAL ? SECOND, UTC_TIMESTAMP(), window_start)',
            [$bucket, $windowSeconds, $windowSeconds]);
        $hits = (int) q_val('SELECT hits FROM rate_limits WHERE bucket = ?', [$bucket]);
        if (random_int(1, 200) === 1) {
            q('DELETE FROM rate_limits WHERE window_start < UTC_TIMESTAMP() - INTERVAL 2 DAY');
        }
    } catch (Throwable $e) {
        error_log('[AFRICASPY rate_limit] ' . $e->getMessage());
        return true; // en cas d'incident technique, on ne bloque pas le service
    }
    return $hits <= $max;
}

/** Variante bloquante : répond 429 (JSON ou page) si le seuil est dépassé. */
function rate_limit_or_fail(string $action, string $key, int $max, int $windowSeconds, string $message = 'Trop de requêtes. Veuillez patienter quelques minutes avant de réessayer.'): void
{
    if (rate_limit($action, $key, $max, $windowSeconds)) {
        return;
    }
    audit('security.rate_limited', null, null, $action);
    header('Retry-After: ' . $windowSeconds);
    if (is_ajax()) {
        json_response(['ok' => false, 'message' => $message], 429);
    }
    render_error_page(429, 'Trop de requêtes', $message);
}

/* =====================================================================
 *  Mots de passe
 * ===================================================================*/

function password_algo(): string|int
{
    return defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
}

function password_options(): array
{
    return defined('PASSWORD_ARGON2ID') ? ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 1] : ['cost' => 12];
}

function hash_password(string $plain): string
{
    return password_hash($plain, password_algo(), password_options());
}

function password_needs_upgrade(string $hash): bool
{
    return password_needs_rehash($hash, password_algo(), password_options());
}

/** Mots de passe trop courants (extrait des listes publiques de fuites, adapté au contexte). */
function is_common_password(string $pwd): bool
{
    static $list = ['password123', 'motdepasse1', 'azerty1234', 'qwerty1234', '1234567890', '0123456789', 'abcdef1234', 'abc1234567',
        'motdepasse123', 'password1234', 'iloveyou12', 'welcome123', 'admin12345', 'administrator1', 'bienvenue1', 'bienvenue123',
        'soleil1234', 'football10', 'africaspy1', 'africaspy123', 'azertyuiop1', 'qwertyuiop1', 'changeme123', 'secret12345', 'letmein123',
        'passw0rd123', 'p4ssw0rd123', 'test123456', 'utilisateur1', 'jesuis1234', 'aaaaaaaaa1', '1111111111a', 'a123456789', '123456789a'];
    $n = mb_strtolower($pwd);
    return in_array($n, $list, true) || preg_match('/^(.)\1+\d*$/u', $n) === 1 || preg_match('/^(?:0123456789|1234567890)+[a-z]*$/', $n) === 1;
}

/** Politique renforcée : longueur, diversité, mots de passe courants, données personnelles. */
function password_policy_error_for(string $pwd, array $context = []): ?string
{
    if ($e = password_policy_error($pwd)) {
        return $e;
    }
    if (is_common_password($pwd)) {
        return 'Ce mot de passe est trop courant. Choisissez une phrase de passe plus personnelle.';
    }
    $low = mb_strtolower($pwd);
    foreach ($context as $c) {
        $c = mb_strtolower(trim((string) $c));
        if ($c === '') continue;
        $local = str_contains($c, '@') ? strstr($c, '@', true) : $c;
        // Chaque segment significatif (prénom, nom, partie de l'adresse) est interdit dans le mot de passe
        foreach (array_merge([$local], preg_split('/[^\p{L}]+/u', $local) ?: []) as $part) {
            if (mb_strlen($part) >= 4 && str_contains($low, $part)) {
                return 'Le mot de passe ne doit pas contenir votre nom ou votre adresse e-mail.';
            }
        }
    }
    return null;
}

/* =====================================================================
 *  Double authentification TOTP (RFC 6238, SHA-1, 30 s, 6 chiffres)
 * ===================================================================*/

function base32_encode(string $bin): string
{
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($bin) as $c) $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    $out = '';
    foreach (str_split($bits, 5) as $chunk) $out .= $alpha[bindec(str_pad($chunk, 5, '0'))];
    return $out;
}

function base32_decode(string $b32): string
{
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32));
    $bits = '';
    foreach (str_split($b32) as $c) $bits .= str_pad(decbin(strpos($alpha, $c)), 5, '0', STR_PAD_LEFT);
    $out = '';
    foreach (str_split($bits, 8) as $byte) if (strlen($byte) === 8) $out .= chr(bindec($byte));
    return $out;
}

function totp_code(string $secretB32, int $step): string
{
    $hash = hash_hmac('sha1', pack('N2', 0, $step), base32_decode($secretB32), true);
    $o = ord($hash[19]) & 0x0F;
    $v = ((ord($hash[$o]) & 0x7F) << 24) | (ord($hash[$o + 1]) << 16) | (ord($hash[$o + 2]) << 8) | ord($hash[$o + 3]);
    return str_pad((string) ($v % 1000000), 6, '0', STR_PAD_LEFT);
}

/** Vérifie un code (tolérance ±1 pas) ; renvoie le pas utilisé, ou null. Refuse la réutilisation. */
function totp_verify(string $secretB32, string $code, ?int $lastStep = null): ?int
{
    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6) return null;
    $now = intdiv(time(), 30);
    for ($d = -1; $d <= 1; $d++) {
        $step = $now + $d;
        if ($lastStep !== null && $step <= $lastStep) continue; // anti-rejeu
        if (hash_equals(totp_code($secretB32, $step), $code)) return $step;
    }
    return null;
}

function totp_key(): string
{
    return hash('sha256', 'afspy-totp|' . config('app_key'), true);
}

function totp_encrypt(string $secretB32): string
{
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    return base64_encode($nonce . sodium_crypto_secretbox($secretB32, $nonce, totp_key()));
}

function totp_decrypt(?string $stored): ?string
{
    if (!$stored) return null;
    $raw = base64_decode($stored, true);
    if ($raw === false || strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) return null;
    $plain = sodium_crypto_secretbox_open(substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), totp_key());
    return $plain === false ? null : $plain;
}

/** Génère 8 codes de secours ; renvoie [codes en clair, JSON des empreintes]. */
function recovery_codes_generate(): array
{
    $codes = [];
    for ($i = 0; $i < 8; $i++) {
        $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 5) . '-' . substr(bin2hex(random_bytes(5)), 0, 5));
    }
    $hashes = array_map(static fn ($c) => hash_hmac('sha256', $c, (string) config('app_key')), $codes);
    return [$codes, json_encode($hashes)];
}

/** Consomme un code de secours s'il est valide. */
function recovery_code_use(int $userId, string $code): bool
{
    $row = q_one('SELECT totp_recovery FROM users WHERE id = ?', [$userId]);
    $hashes = json_decode((string) ($row['totp_recovery'] ?? ''), true) ?: [];
    $h = hash_hmac('sha256', strtoupper(trim($code)), (string) config('app_key'));
    foreach ($hashes as $i => $stored) {
        if (hash_equals($stored, $h)) {
            unset($hashes[$i]);
            q('UPDATE users SET totp_recovery = ? WHERE id = ?', [json_encode(array_values($hashes)), $userId]);
            return true;
        }
    }
    return false;
}

function totp_uri(string $secretB32, string $email): string
{
    $issuer = "AFRICA'SPY";
    return 'otpauth://totp/' . rawurlencode($issuer . ':' . $email) . '?secret=' . $secretB32 . '&issuer=' . rawurlencode($issuer) . '&algorithm=SHA1&digits=6&period=30';
}
