<?php
/**
 * Installation en ligne de commande :
 *   php install/cli.php --db-host=127.0.0.1 --db-name=africaspy --db-user=user --db-pass=*** \
 *       --base-url=https://academy.example.org --admin-email=admin@example.org \
 *       --admin-first=Prénom --admin-last=Nom
 * Le mot de passe administrateur est demandé de façon interactive (non affiché)
 * ou lu depuis la variable d'environnement AFRICASPY_ADMIN_PASSWORD.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('AFSPY_INSTALLER', true);
require dirname(__DIR__) . '/includes/bootstrap.php';
require __DIR__ . '/installer.php';

$o = getopt('', ['db-host:', 'db-port:', 'db-name:', 'db-user:', 'db-pass:', 'base-url:', 'admin-email:', 'admin-first:', 'admin-last:']);
foreach (installer_requirements() as $label => $ok) {
    echo ($ok ? '[ OK ] ' : '[FAIL] ') . $label . PHP_EOL;
}
$pwd = getenv('AFRICASPY_ADMIN_PASSWORD') ?: '';
if ($pwd === '') {
    echo 'Mot de passe administrateur (10 car. min., lettres + chiffres) : ';
    if (DIRECTORY_SEPARATOR === '/') { system('stty -echo'); }
    $pwd = trim((string) fgets(STDIN));
    if (DIRECTORY_SEPARATOR === '/') { system('stty echo'); }
    echo PHP_EOL;
}
$err = installer_run([
    'db_host' => $o['db-host'] ?? '127.0.0.1', 'db_port' => $o['db-port'] ?? 3306, 'db_name' => $o['db-name'] ?? 'africaspy',
    'db_user' => $o['db-user'] ?? '', 'db_pass' => $o['db-pass'] ?? '', 'base_url' => $o['base-url'] ?? '',
    'email' => $o['admin-email'] ?? '', 'first_name' => $o['admin-first'] ?? 'Admin', 'last_name' => $o['admin-last'] ?? 'AFRICASPY',
    'password' => $pwd,
]);
echo $err ? "ÉCHEC : $err\n" : "Installation terminée. Connectez-vous avec l'adresse administrateur fournie.\n";
exit($err ? 1 : 0);
