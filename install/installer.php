<?php
/**
 * AFRICA'SPY : logique d'installation partagée (assistant web et CLI).
 */
declare(strict_types=1);

function installer_requirements(): array
{
    $checks = [
        'PHP 8.1 ou plus récent'                => version_compare(PHP_VERSION, '8.1.0', '>='),
        'Extension PDO MySQL'      => extension_loaded('pdo_mysql'),
        'Extension mbstring'       => extension_loaded('mbstring'),
        'Extension GD (QR Code)'   => extension_loaded('gd'),
        'Extension fileinfo (MIME)' => extension_loaded('fileinfo'),
        'Extension DOM (PDF)'      => extension_loaded('dom'),
        'Extension intl'           => extension_loaded('intl'),
        'Dépendances vendor/'      => is_file(APP_ROOT . '/vendor/autoload.php'),
    ];
    foreach (['config', 'storage/logs', 'storage/cache', 'storage/fonts', 'certificates/generated', 'uploads/courses', 'uploads/documents', 'uploads/videos'] as $d) {
        $checks["Dossier inscriptible : $d/"] = is_dir(APP_ROOT . "/$d") && is_writable(APP_ROOT . "/$d");
    }
    return $checks;
}

function installer_locked(): bool
{
    return is_file(APP_ROOT . '/install/install.lock') || is_file(APP_ROOT . '/config/config.local.php');
}

/**
 * Installe la plateforme. Renvoie null en cas de succès ou un message d'erreur.
 * $d : db_host, db_port, db_name, db_user, db_pass, base_url, first_name, last_name, email, password
 */
function installer_run(array $d): ?string
{
    if (installer_locked()) {
        return 'La plateforme est déjà installée.';
    }
    foreach (['db_host', 'db_name', 'db_user', 'first_name', 'last_name', 'email', 'password'] as $k) {
        if (trim((string) ($d[$k] ?? '')) === '') {
            return "Champ requis manquant : $k.";
        }
    }
    if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $d['db_name'])) {
        return 'Nom de base de données invalide (lettres, chiffres, underscore).';
    }
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
        return 'Adresse e-mail administrateur invalide.';
    }
    if ($err = password_policy_error_for((string) $d['password'], [$d['email'], $d['first_name'], $d['last_name']])) {
        return $err;
    }
    if (empty($d['base_url']) || !filter_var($d['base_url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $d['base_url'])) {
        return 'URL publique du site requise et valide (ex. https://academy.example.org) : elle sécurise les liens envoyés par e-mail et les QR codes.';
    }
    try {
        $pdo = new PDO(sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $d['db_host'], (int) ($d['db_port'] ?: 3306), $d['db_name']),
            $d['db_user'], (string) $d['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => true]);
    } catch (Throwable $e) {
        return 'Connexion à la base impossible : ' . $e->getMessage();
    }
    $sql = file_get_contents(APP_ROOT . '/database/africaspy.sql');
    if (!$sql) {
        return 'Fichier database/africaspy.sql introuvable.';
    }
    try {
        $sql = preg_replace('/^--.*$/m', '', $sql);
        foreach (preg_split('/;\s*\n/', $sql) as $stmt) {
            if (trim($stmt) !== '') {
                $pdo->exec($stmt);
            }
        }
        $pdo->prepare('INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (1, ?, ?, ?, ?)')
            ->execute([trim($d['first_name']), trim($d['last_name']), mb_strtolower(trim($d['email'])), hash_password((string) $d['password'])]);
        $pdo->prepare('INSERT INTO audit_logs (user_id, action, details) VALUES (LAST_INSERT_ID(), ?, ?)')->execute(['install', 'Installation initiale de la plateforme']);
        if (!empty($d['contact_email'])) {
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'contact_email'")->execute([$d['contact_email']]);
        }
    } catch (Throwable $e) {
        return 'Erreur lors de la création de la base : ' . $e->getMessage();
    }
    $cfg = [
        'env' => 'production',
        'base_url' => rtrim((string) ($d['base_url'] ?? ''), '/'),
        'app_key' => bin2hex(random_bytes(32)),
        'db' => ['host' => $d['db_host'], 'port' => (int) ($d['db_port'] ?: 3306), 'name' => $d['db_name'], 'user' => $d['db_user'], 'pass' => (string) $d['db_pass']],
    ];
    $php = "<?php\n// Généré par l'installateur AFRICA'SPY le " . gmdate('c') . " : NE PAS VERSIONNER.\nreturn " . var_export($cfg, true) . ";\n";
    if (file_put_contents(APP_ROOT . '/config/config.local.php', $php, LOCK_EX) === false) {
        return 'Impossible d\'écrire config/config.local.php (droits d\'écriture).';
    }
    @chmod(APP_ROOT . '/config/config.local.php', 0640);
    file_put_contents(APP_ROOT . '/install/install.lock', gmdate('c'));
    return null;
}
