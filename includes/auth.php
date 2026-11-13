<?php
/**
 * AFRICA'SPY : authentification, contrôle d'accès par rôle, anti force brute.
 */
declare(strict_types=1);

function current_user(bool $refresh = false): ?array
{
    static $user = false;
    static $forId = null;
    $id = $_SESSION['uid'] ?? null;
    // Cache invalidé si l'identité de session change (connexion / déconnexion dans la même requête)
    if (!$refresh && $user !== false && $forId === $id) {
        return $user;
    }
    $forId = $id;
    if (!$id) {
        return $user = null;
    }
    $row = q_one(
        'SELECT u.id, u.role_id, u.first_name, u.last_name, u.email, u.country, u.organization, u.bio, u.status, u.totp_enabled,
                u.created_at, u.last_login_at, r.slug AS role_slug, r.name AS role_name
           FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?',
        [$id]
    );
    // Compte supprimé, suspendu, ou empreinte de session invalidée (changement de mot de passe)
    if (!$row || $row['status'] !== 'active' || ($_SESSION['upw'] ?? '') !== session_fingerprint((int) $row['id'])) {
        unset($_SESSION['uid'], $_SESSION['upw'], $_SESSION['perms']);
        return $user = null;
    }
    return $user = $row;
}

/** Empreinte liée au hash du mot de passe : un changement de mot de passe invalide les autres sessions. */
function session_fingerprint(int $userId): string
{
    $hash = (string) q_val('SELECT password_hash FROM users WHERE id = ?', [$userId]);
    return hash_hmac('sha256', $hash, (string) config('app_key', 'africaspy'));
}

function user_permissions(): array
{
    $u = current_user();
    if (!$u) {
        return [];
    }
    if (!isset($_SESSION['perms']) || ($_SESSION['perms_role'] ?? null) !== $u['role_id']) {
        $_SESSION['perms'] = array_column(q_all(
            'SELECT p.slug FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ?',
            [$u['role_id']]
        ), 'slug');
        $_SESSION['perms_role'] = $u['role_id'];
    }
    return $_SESSION['perms'];
}

function can(string $permission): bool
{
    return in_array($permission, user_permissions(), true);
}

function is_staff(): bool
{
    return can('admin.access');
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        if (is_ajax()) {
            json_response(['ok' => false, 'message' => 'Session expirée. Veuillez vous reconnecter.'], 401);
        }
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? null;
        flash('info', 'Authentification requise pour accéder à cet espace.');
        redirect('login.php');
    }
    return $u;
}

/** Le personnel doit activer la 2FA si l'option est imposée dans les paramètres. */
function enforce_staff_mfa(array $u): void
{
    if (is_staff() && !(int) $u['totp_enabled'] && setting('mfa_required_staff', '0') === '1'
        && !str_ends_with((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/learner/security.php')) {
        flash('warning', 'La double authentification est obligatoire pour les comptes du personnel. Activez-la pour continuer.');
        redirect('learner/security.php');
    }
}

function require_permission(string $permission): array
{
    $u = require_login();
    enforce_staff_mfa($u);
    if (!can($permission)) {
        audit('access.denied', null, null, $permission . ' | ' . ($_SERVER['REQUEST_URI'] ?? ''));
        if (is_ajax()) {
            json_response(['ok' => false, 'message' => 'Accès refusé.'], 403);
        }
        render_error_page(403, 'Accès refusé', 'Votre niveau d\'habilitation ne permet pas d\'accéder à cette ressource.');
    }
    return $u;
}

function require_guest(): void
{
    if (current_user()) {
        redirect(is_staff() ? 'admin/dashboard.php' : 'learner/dashboard.php');
    }
}

/* ------------------------------------------------------------------
 *  Protection contre la force brute
 * ----------------------------------------------------------------*/

/**
 * Verrouillage anti force brute, conçu pour ne pas permettre à un tiers de bloquer un compte :
 *  - par couple compte + IP (5 échecs / 15 min) : bloque l'attaquant, pas le titulaire ailleurs ;
 *  - par IP (20 échecs / 15 min) : bloque le balayage de nombreux comptes ;
 *  - par compte toutes IP (50 échecs / 15 min) : freine les attaques distribuées.
 * Renvoie la durée de blocage en minutes, ou null.
 */
function login_locked(string $email): ?int
{
    $win = (int) config('security.login_window_min', 15);
    $ip = client_ip();
    $count = static fn (string $where, array $p) => (int) q_val(
        "SELECT COUNT(*) FROM login_attempts WHERE $where AND success = 0 AND attempted_at > (UTC_TIMESTAMP() - INTERVAL ? MINUTE)", array_merge($p, [$win]));
    if ($count('email = ? AND ip_address = ?', [$email, $ip]) >= (int) config('security.login_max_per_email', 5)
        || $count('ip_address = ?', [$ip]) >= (int) config('security.login_max_per_ip', 20)
        || $count('email = ?', [$email]) >= (int) config('security.login_max_global', 50)) {
        return $win;
    }
    return null;
}

function record_login_attempt(string $email, bool $success): void
{
    q('INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES (?,?,?,UTC_TIMESTAMP())', [$email, client_ip(), $success ? 1 : 0]);
    if ($success) {
        // Un succès efface les échecs récents de ce compte depuis cette IP
        q('DELETE FROM login_attempts WHERE email = ? AND ip_address = ? AND success = 0', [$email, client_ip()]);
    }
    if (random_int(1, 50) === 1) {
        q('DELETE FROM login_attempts WHERE attempted_at < (UTC_TIMESTAMP() - INTERVAL 7 DAY)');
    }
}

/**
 * Tente une connexion. Renvoie null en cas de succès, sinon un message d'erreur générique.
 */
const MFA_REQUIRED = '__mfa__';

function attempt_login(string $email, string $password): ?string
{
    $email = mb_strtolower(trim($email));
    if ($minutes = login_locked($email)) {
        audit('auth.locked', null, null, mb_substr($email, 0, 190));
        return "Trop de tentatives infructueuses. Par mesure de sécurité, l'accès est temporairement suspendu ($minutes minutes).";
    }
    $user = q_one('SELECT id, password_hash, status, role_id, totp_enabled FROM users WHERE email = ?', [$email]);
    // Vérification à temps constant même si le compte n'existe pas
    $hash = $user['password_hash'] ?? '$2y$10$894BRSesx9x6nbleA6MFGOSHDyAbiGkCxBRB63ZX403IoqnQfj6iq';
    $valid = password_verify($password, $hash);
    if (!$user || !$valid) {
        record_login_attempt($email, false);
        return 'Identifiants incorrects. Vérifiez votre adresse e-mail et votre mot de passe.';
    }
    if ($user['status'] !== 'active') {
        record_login_attempt($email, false);
        return 'Ce compte est suspendu. Contactez l\'administration de l\'Académie.';
    }
    if (password_needs_upgrade($user['password_hash'])) {
        // Mise à niveau transparente vers Argon2id lors de la connexion
        q('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($password), $user['id']]);
    }
    record_login_attempt($email, true);
    if ((int) $user['totp_enabled'] === 1) {
        // Seconde étape : le compte n'est ouvert qu'après le code TOTP
        session_regenerate_id(true);
        $_SESSION['mfa_pending'] = ['uid' => (int) $user['id'], 'at' => time(), 'tries' => 0];
        return MFA_REQUIRED;
    }
    login_user((int) $user['id']);
    return null;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = $userId;
    $_SESSION['upw'] = session_fingerprint($userId);
    $_SESSION['_auth_at'] = time();
    unset($_SESSION['mfa_pending']);
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    unset($_SESSION['perms']);
    q('UPDATE users SET last_login_at = UTC_TIMESTAMP() WHERE id = ?', [$userId]);
    current_user(true);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}
