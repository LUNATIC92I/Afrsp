<?php
/**
 * AFRICA'SPY — amorçage commun à toutes les pages.
 * Charge la configuration, sécurise la session, émet les en-têtes de sécurité.
 */
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('APP_START', microtime(true));

$GLOBALS['__config'] = require APP_ROOT . '/config/app.php';

function config(string $key, $default = null)
{
    $value = $GLOBALS['__config'];
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }
    return $value;
}

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

$isDev = config('env') === 'development';
error_reporting(E_ALL);
ini_set('display_errors', $isDev ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', APP_ROOT . '/storage/logs/php-error.log');

if (is_file(APP_ROOT . '/vendor/autoload.php')) {
    require APP_ROOT . '/vendor/autoload.php';
}
require APP_ROOT . '/config/database.php';
require APP_ROOT . '/includes/functions.php';
require APP_ROOT . '/includes/security.php';
require APP_ROOT . '/includes/auth.php';

// Installation non effectuée : redirection vers l'assistant (sauf CLI).
if (!config('installed') && PHP_SAPI !== 'cli' && !defined('AFSPY_INSTALLER')) {
    header('Location: ' . url('install/index.php'));
    exit;
}

if (PHP_SAPI !== 'cli') {
    send_security_headers();
    start_secure_session();
}

set_exception_handler(static function (Throwable $e): void {
    error_log('[AFRICASPY] ' . get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, $e->getMessage() . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'message' => 'Erreur interne. L\'incident a été journalisé.']);
        return;
    }
    $debug = config('env') === 'development' ? $e->getMessage() : null;
    render_error_page(500, 'Incident système', 'Une erreur inattendue est survenue. L\'incident a été journalisé et sera analysé.', $debug);
});
