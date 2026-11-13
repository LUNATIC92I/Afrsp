<?php
/**
 * AFRICA'SPY : fonctions utilitaires transverses.
 */
declare(strict_types=1);

/* =====================================================================
 *  Échappement & URL
 * ===================================================================*/

/** Échappement HTML systématique (protection XSS). */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/** Chemin de base de l'application (sous-dossier éventuel), détecté automatiquement. */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    if (PHP_SAPI === 'cli' || empty($_SERVER['SCRIPT_FILENAME'])) {
        $cfg = (string) config('base_url');
        return $base = rtrim((string) parse_url($cfg, PHP_URL_PATH), '/');
    }
    $script = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME']));
    $root   = str_replace('\\', '/', APP_ROOT);
    $rel    = substr($script, strlen($root));
    $name   = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base   = ($rel !== '' && str_ends_with($name, $rel)) ? substr($name, 0, -strlen($rel)) : '';
    return $base = rtrim($base, '/');
}

function url(string $path = '', array $query = []): string
{
    $u = base_path() . '/' . ltrim($path, '/');
    if ($query) {
        $u .= '?' . http_build_query($query);
    }
    return $u;
}

/** URL absolue (QR codes, e-mails). Utilise base_url si configurée. */
function absolute_url(string $path = '', array $query = []): string
{
    $cfg = rtrim((string) config('base_url'), '/');
    if ($cfg !== '' && preg_match('#^https?://[^/\s]+#i', $cfg, $m)) {
        $origin = $m[0];
    } elseif (config('env') === 'development') {
        // Développement uniquement : l'hôte de la requête est accepté s'il est local
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $origin = (is_https() ? 'https' : 'http') . '://' . (preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$/', $host) ? $host : 'localhost');
    } else {
        // En production, l'en-tête Host n'est jamais utilisé (empoisonnement des liens envoyés par e-mail)
        throw new RuntimeException('base_url doit être définie dans config/config.local.php pour générer des liens absolus.');
    }
    return $origin . url($path, $query);
}

/** URL d'un fichier statique avec empreinte de version (cache-busting). */
function asset(string $path): string
{
    $file = APP_ROOT . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? substr((string) filemtime($file), -6) : '1';
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https' && request_from_trusted_proxy());
}

function redirect(string $path, array $query = []): never
{
    $target = preg_match('#^https?://#', $path) ? $path : url($path, $query);
    header('Location: ' . $target, true, 303);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** La requête provient-elle d'un proxy inverse déclaré (config 'trusted_proxies') ? */
function request_from_trusted_proxy(): bool
{
    $proxies = (array) config('trusted_proxies', []);
    return $proxies && in_array($_SERVER['REMOTE_ADDR'] ?? '', $proxies, true);
}

/** Adresse IP du client ; X-Forwarded-For n'est lu que derrière un proxy de confiance. */
function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (request_from_trusted_proxy() && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $chain = array_map('trim', explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR']));
        // On remonte la chaîne depuis la droite en ignorant les proxys de confiance
        for ($i = count($chain) - 1; $i >= 0; $i--) {
            if (!in_array($chain[$i], (array) config('trusted_proxies', []), true)) { $ip = $chain[$i]; break; }
        }
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/* =====================================================================
 *  En-têtes de sécurité & session
 * ===================================================================*/

function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "img-src 'self' data: blob:; font-src 'self'; media-src 'self' blob:; connect-src 'self'; "
        . "frame-src https://www.youtube-nocookie.com https://player.vimeo.com; object-src 'none'; "
        . "base-uri 'self'; form-action 'self'; frame-ancestors 'self'" . (is_https() ? '; upgrade-insecure-requests' : ''));
    if (is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.sid_length', '48');
    ini_set('session.sid_bits_per_character', '6');
    // En HTTPS à la racine du domaine, le préfixe __Host- interdit tout cookie de session forgé par un sous-domaine
    session_name((is_https() && base_path() === '' ? '__Host-' : '') . (string) config('session_name', 'AFSPY_SESS'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => (base_path() ?: '') . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $now  = time();
    $idle = (int) config('session_idle', 7200);
    if (isset($_SESSION['_last_seen']) && ($now - (int) $_SESSION['_last_seen']) > $idle) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_flash'][] = ['type' => 'info', 'msg' => 'Votre session a expiré après une période d\'inactivité. Veuillez vous reconnecter.'];
    }
    // Durée de vie absolue d'une session authentifiée (12 h par défaut)
    if (isset($_SESSION['_auth_at']) && ($now - (int) $_SESSION['_auth_at']) > (int) config('session_absolute', 43200)) {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['_flash'][] = ['type' => 'info', 'msg' => 'Pour votre sécurité, veuillez vous reconnecter.'];
    }
    $_SESSION['_last_seen'] = $now;
    // Rotation périodique de l'identifiant de session
    if (!isset($_SESSION['_rotated']) || $now - (int) $_SESSION['_rotated'] > 900) {
        session_regenerate_id(true);
        $_SESSION['_rotated'] = $now;
    }
}

/* =====================================================================
 *  CSRF
 * ===================================================================*/

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($sent) || empty($_SESSION['_csrf']) || !hash_equals($_SESSION['_csrf'], $sent)) {
        if (is_ajax()) {
            json_response(['ok' => false, 'message' => 'Jeton de sécurité invalide ou expiré. Rechargez la page.'], 419);
        }
        render_error_page(419, 'Jeton de sécurité expiré', 'Le formulaire a expiré ou n\'a pas pu être authentifié. Revenez en arrière et réessayez.');
    }
}

/* =====================================================================
 *  Messages flash & anciennes valeurs de formulaire
 * ===================================================================*/

function flash(string $type, string $msg): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $msg];
}

function flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

function set_old(array $data): void
{
    unset($data['password'], $data['password_confirm'], $data['current_password'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}

function old(string $key, $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    return (string) ($old[$key] ?? $default);
}

/* =====================================================================
 *  Validation serveur
 * ===================================================================*/

function input(string $key, string $default = ''): string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $default;
}

function input_int(string $key, int $default = 0): int
{
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    return (is_string($v) || is_int($v)) && preg_match('/^-?\d+$/', (string) $v) ? (int) $v : $default;
}

/**
 * Valide un tableau de données selon des règles simples.
 * Règles : required, email, min:n, max:n, in:a|b, int, url, same:champ
 */
function validate(array $data, array $rules, array $labels = []): array
{
    $errors = [];
    foreach ($rules as $field => $ruleset) {
        $value = isset($data[$field]) && is_string($data[$field]) ? trim($data[$field]) : ($data[$field] ?? '');
        $label = $labels[$field] ?? $field;
        foreach (explode('|', $ruleset) as $rule) {
            [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
            $len = is_string($value) ? mb_strlen($value) : 0;
            $fail = match ($name) {
                'required' => $value === '' || $value === null,
                'email'    => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL),
                'min'      => $value !== '' && $len < (int) $arg,
                'max'      => $len > (int) $arg,
                'in'       => $value !== '' && !in_array($value, explode(',', (string) $arg), true),
                'int'      => $value !== '' && !preg_match('/^-?\d+$/', (string) $value),
                'url'      => $value !== '' && !filter_var($value, FILTER_VALIDATE_URL),
                'same'     => $value !== ($data[$arg] ?? null),
                'name'     => $value !== '' && !preg_match("/^[\p{L}][\p{L}\p{M}' .\-]*$/u", (string) $value),
                default    => false,
            };
            if ($fail) {
                $errors[$field] = match ($name) {
                    'required' => "Le champ « $label » est obligatoire.",
                    'email'    => "L'adresse e-mail saisie n'est pas valide.",
                    'min'      => "« $label » doit contenir au moins $arg caractères.",
                    'max'      => "« $label » ne doit pas dépasser $arg caractères.",
                    'in'       => "La valeur de « $label » n'est pas autorisée.",
                    'int'      => "« $label » doit être un nombre entier.",
                    'url'      => "« $label » doit être une URL valide (https://...).",
                    'same'     => "La confirmation de « $label » ne correspond pas.",
                    'name'     => "« $label » contient des caractères non autorisés.",
                    default    => "« $label » est invalide.",
                };
                break;
            }
        }
    }
    return $errors;
}

/** Politique de mot de passe : 10 caractères minimum, lettres + chiffres. */
function password_policy_error(string $pwd): ?string
{
    if (mb_strlen($pwd) < 10) {
        return 'Le mot de passe doit contenir au moins 10 caractères.';
    }
    if (mb_strlen($pwd) > 128) {
        return 'Le mot de passe ne doit pas dépasser 128 caractères.';
    }
    if (!preg_match('/\p{L}/u', $pwd) || !preg_match('/\d/', $pwd)) {
        return 'Le mot de passe doit combiner des lettres et des chiffres.';
    }
    return null;
}

/* =====================================================================
 *  HTML riche : nettoyage par liste blanche (contenus de cours)
 * ===================================================================*/

function sanitize_html(?string $html): string
{
    $html = (string) $html;
    if (trim($html) === '') {
        return '';
    }
    $allowed = [
        'p' => [], 'br' => [], 'strong' => [], 'em' => [], 'b' => [], 'i' => [], 'u' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [],
        'a' => ['href', 'title'], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => ['colspan', 'scope'], 'td' => ['colspan'], 'code' => [], 'pre' => [], 'hr' => [],
        'figure' => [], 'figcaption' => [], 'span' => [], 'div' => [], 'dl' => [], 'dt' => [], 'dd' => [],
        'small' => [], 'sup' => [], 'sub' => [], 'mark' => [], 'abbr' => ['title'],
    ];
    $dropWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'textarea', 'select', 'button', 'svg', 'math', 'template', 'noscript', 'link', 'meta', 'base'];

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();

    $walk = function (DOMNode $node) use (&$walk, $allowed, $dropWithContent): void {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
                $node->removeChild($child);
                continue;
            }
            if (!$child instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($child->tagName);
            if (in_array($tag, $dropWithContent, true)) {
                $node->removeChild($child);
                continue;
            }
            $walk($child);
            if (!isset($allowed[$tag])) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            foreach (iterator_to_array($child->attributes) as $attr) {
                $an = strtolower($attr->name);
                if ($an === 'class' && preg_match('/^[a-z0-9 _-]{1,80}$/i', $attr->value)) {
                    continue;
                }
                if (!in_array($an, $allowed[$tag], true)) {
                    $child->removeAttribute($attr->name);
                    continue;
                }
                if ($an === 'href') {
                    $href = trim($attr->value);
                    if (!preg_match('#^(https?://|mailto:|/(?![/\\\\])|\#)#i', $href) || str_contains($href, '\\')) {
                        $child->removeAttribute('href');
                    } else {
                        $child->setAttribute('rel', 'noopener noreferrer');
                        if (preg_match('#^https?://#i', $href)) {
                            $child->setAttribute('target', '_blank');
                        }
                    }
                }
            }
        }
    };
    $root = $doc->getElementById('__root');
    if (!$root) {
        return e($html);
    }
    $walk($root);
    $out = '';
    foreach ($root->childNodes as $n) {
        $out .= $doc->saveHTML($n);
    }
    return $out;
}

/* =====================================================================
 *  Paramètres institutionnels (table settings)
 * ===================================================================*/

function settings_all(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (q_all('SELECT setting_key, setting_value FROM settings') as $r) {
                $cache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (Throwable) {
            $cache = [];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $all = settings_all();
    return isset($all[$key]) && $all[$key] !== '' ? (string) $all[$key] : $default;
}

function set_setting(string $key, string $value): void
{
    q('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)', [$key, $value]);
}

/* =====================================================================
 *  Journal d'audit
 * ===================================================================*/

function audit(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void
{
    try {
        q('INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?,?,?,?,?,?)', [
            current_user()['id'] ?? null, $action, $entityType, $entityId,
            $details !== null ? mb_substr($details, 0, 500) : null, client_ip(),
        ]);
    } catch (Throwable $e) {
        error_log('[AFRICASPY audit] ' . $e->getMessage());
    }
}

/* =====================================================================
 *  Formatage
 * ===================================================================*/

function local_dt(?string $utc): ?DateTimeImmutable
{
    if (!$utc) {
        return null;
    }
    try {
        return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone((string) config('timezone', 'UTC')));
    } catch (Throwable) {
        return null;
    }
}

function fmt_date(?string $utc, bool $long = true): string
{
    $d = local_dt($utc);
    if (!$d) {
        return '-';
    }
    static $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    return $long ? ($d->format('j') . ' ' . $months[(int) $d->format('n') - 1] . ' ' . $d->format('Y')) : $d->format('d/m/Y');
}

function fmt_datetime(?string $utc): string
{
    $d = local_dt($utc);
    return $d ? $d->format('d/m/Y | H:i') : '-';
}

function time_ago(?string $utc): string
{
    if (!$utc) {
        return '-';
    }
    $diff = time() - strtotime($utc . ' UTC');
    return match (true) {
        $diff < 60     => 'à l\'instant',
        $diff < 3600   => 'il y a ' . floor($diff / 60) . ' min',
        $diff < 86400  => 'il y a ' . floor($diff / 3600) . ' h',
        $diff < 604800 => 'il y a ' . floor($diff / 86400) . ' j',
        default        => fmt_date($utc, false),
    };
}

function level_label(string $level): string
{
    return ['fondamental' => 'Fondamental', 'intermediaire' => 'Intermédiaire', 'avance' => 'Avancé'][$level] ?? ucfirst($level);
}

function level_rank(string $level): int
{
    return ['fondamental' => 1, 'intermediaire' => 2, 'avance' => 3][$level] ?? 1;
}

function lesson_type_label(string $type): string
{
    return [
        'text' => 'Leçon', 'video' => 'Vidéo', 'document' => 'Support PDF',
        'exercise' => 'Exercice pratique', 'case_study' => 'Étude de cas',
    ][$type] ?? 'Leçon';
}

function lines(?string $text): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text) ?: []), fn ($l) => $l !== ''));
}

function str_limit(string $s, int $max): string
{
    $s = trim(preg_replace('/\s+/', ' ', strip_tags($s)));
    return mb_strlen($s) > $max ? rtrim(mb_substr($s, 0, $max - 1)) . '...' : $s;
}

function slugify(string $s): string
{
    $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s) ?: strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-') ?: 'item';
}

function fmt_bytes(int $b): string
{
    $u = ['o', 'Ko', 'Mo', 'Go'];
    $i = 0;
    while ($b >= 1024 && $i < 3) {
        $b /= 1024;
        $i++;
    }
    return round($b, $i ? 1 : 0) . ' ' . $u[$i];
}

function initials(string $first, string $last): string
{
    return mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
}

/* =====================================================================
 *  Pagination
 * ===================================================================*/

function paginate(int $total, int $perPage = 20): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    $page  = min($pages, max(1, input_int('page', 1)));
    return ['page' => $page, 'pages' => $pages, 'per' => $perPage, 'offset' => ($page - 1) * $perPage, 'total' => $total];
}

function pagination_links(array $p, array $keep = []): string
{
    if ($p['pages'] <= 1) {
        return '';
    }
    $path = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
    $html = '<nav class="pager" aria-label="Pagination">';
    for ($i = 1; $i <= $p['pages']; $i++) {
        $qs = http_build_query(array_merge($keep, ['page' => $i]));
        $html .= $i === $p['page']
            ? '<span class="pager__item is-current" aria-current="page">' . $i . '</span>'
            : '<a class="pager__item" href="' . e($path . '?' . $qs) . '">' . $i . '</a>';
    }
    return $html . '</nav>';
}

/* =====================================================================
 *  Téléversements sécurisés
 * ===================================================================*/

/**
 * Valide et enregistre un fichier téléversé. Vérification MIME réelle (finfo),
 * extension imposée par le type détecté, taille limitée, nom aléatoire.
 * @return array{id:int,stored:string}|string  id média ou message d'erreur
 */
function handle_upload(array $file, string $kind, string $title = '', ?string $attribution = null)
{
    $map = [
        'image'    => ['dir' => 'uploads/courses',   'max' => config('uploads.max_image'),    'mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp']],
        'document' => ['dir' => 'uploads/documents', 'max' => config('uploads.max_document'), 'mimes' => ['application/pdf' => 'pdf']],
        'video'    => ['dir' => 'uploads/videos',    'max' => config('uploads.max_video'),    'mimes' => ['video/mp4' => 'mp4', 'video/webm' => 'webm']],
    ];
    if (!isset($map[$kind])) {
        return 'Type de média inconnu.';
    }
    if (!isset($file['error']) || is_array($file['error'])) {
        return 'Téléversement invalide.';
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'Aucun fichier sélectionné.';
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'Le fichier dépasse la taille autorisée par le serveur.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'Le téléversement a échoué. Réessayez.';
    }
    $cfg = $map[$kind];
    if ($file['size'] <= 0 || $file['size'] > $cfg['max']) {
        return 'Fichier trop volumineux (maximum ' . fmt_bytes((int) $cfg['max']) . ').';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    if (!isset($cfg['mimes'][$mime])) {
        return 'Format non autorisé (' . e($mime) . '). Formats acceptés : ' . implode(', ', array_values($cfg['mimes'])) . '.';
    }
    if ($kind === 'image' && @getimagesize($file['tmp_name']) === false) {
        return 'L\'image est corrompue ou illisible.';
    }
    if ($kind === 'image' && !reencode_image($file['tmp_name'], $mime)) {
        return 'L\'image n\'a pas pu être traitée. Essayez un autre fichier JPG, PNG ou WebP.';
    }
    $stored = bin2hex(random_bytes(16)) . '.' . $cfg['mimes'][$mime];
    $dest = APP_ROOT . '/' . $cfg['dir'] . '/' . $stored;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Impossible d\'enregistrer le fichier sur le serveur.';
    }
    @chmod($dest, 0640);
    $original = mb_substr(preg_replace('/[^\p{L}\p{N} ._()\-]/u', '_', basename((string) $file['name'])), 0, 200);
    q('INSERT INTO media (kind, title, original_name, stored_name, mime_type, size_bytes, attribution, uploaded_by) VALUES (?,?,?,?,?,?,?,?)', [
        $kind, $title !== '' ? mb_substr($title, 0, 190) : $original, $original, $stored, $mime, (int) $file['size'],
        $attribution ? mb_substr($attribution, 0, 255) : null, current_user()['id'] ?? null,
    ]);
    $id = (int) db()->lastInsertId();
    audit('media.upload', 'media', $id, $kind . ' | ' . $original);
    return ['id' => $id, 'stored' => $stored];
}

/**
 * Réencode une image avec GD : supprime métadonnées (EXIF, GPS) et toute charge
 * dissimulée (fichiers « polyglottes »). Limite aussi les dimensions extrêmes.
 */
function reencode_image(string $path, string $mime): bool
{
    if (!function_exists('imagecreatefromstring')) {
        return true;
    }
    $info = @getimagesize($path);
    if (!$info || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 40_000_000) {
        return false;
    }
    $img = @imagecreatefromstring((string) file_get_contents($path));
    if (!$img) {
        return false;
    }
    $ok = match ($mime) {
        'image/png'  => (imagesavealpha($img, true) && imagepng($img, $path, 6)),
        'image/webp' => imagewebp($img, $path, 85),
        default      => imagejpeg($img, $path, 88),
    };
    imagedestroy($img);
    return (bool) $ok;
}

function media_path(array $m): string
{
    $dir = ['image' => 'uploads/courses', 'document' => 'uploads/documents', 'video' => 'uploads/videos'][$m['kind']] ?? 'uploads';
    return APP_ROOT . '/' . $dir . '/' . basename($m['stored_name']);
}

/** URL publique d'une image de couverture (les images sont publiques ; PDF et vidéos passent par download.php). */
function course_cover_url(array $course): string
{
    if (!empty($course['cover_stored'])) {
        return url('uploads/courses/' . rawurlencode($course['cover_stored']));
    }
    return url('assets/images/covers/' . e($course['category_code'] ?? 'A') . '.svg');
}

/* Le courriel est géré par includes/mailer.php (send_mail, gabarits, pièces jointes). */

/* =====================================================================
 *  Pages d'erreur
 * ===================================================================*/

function render_error_page(int $code, string $title, string $message, ?string $debug = null): never
{
    if (!headers_sent()) {
        http_response_code($code);
    }
    $pageTitle = $title;
    $bodyClass = 'page-error';
    {
        try {
            require APP_ROOT . '/includes/header.php';
            echo '<main id="main" class="error-screen"><div class="error-screen__inner reveal is-visible">';
            echo '<p class="mono error-screen__code">STATUS ' . (int) $code . ' // ACCESS REPORT</p>';
            echo '<h1 class="error-screen__title">' . e($title) . '</h1><p class="error-screen__msg">' . e($message) . '</p>';
            if ($debug) {
                echo '<pre class="error-screen__debug">' . e($debug) . '</pre>';
            }
            echo '<div class="error-screen__actions"><a class="btn btn--primary" href="' . e(url('index.php')) . '">Retour à l\'accueil</a></div></div></main>';
            require APP_ROOT . '/includes/footer.php';
        } catch (Throwable) {
            echo '<h1>' . e($title) . '</h1><p>' . e($message) . '</p>';
        }
    }
    exit;
}
