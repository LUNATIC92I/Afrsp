<?php
/**
 * Routeur pour le serveur PHP intégré (développement local uniquement) :
 *   php -S 127.0.0.1:8080 resources/dev-router.php
 * Reproduit les interdictions d'accès du fichier .htaccess.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$deny = '#(^|/)\.|^/(config|includes|database|vendor|storage|resources|certificates)(/|$)|^/uploads/(documents|videos)(/|$)|^/install/(installer\.php|cli\.php|install\.lock)$|^/(composer\.(json|lock)|README\.md|SECURITY\.md)$|^/uploads/.*\.(php\d?|phtml|phar)$#i';
if ($path === '/.well-known/security.txt') {
    $_SERVER['SCRIPT_NAME'] = '/security-txt.php';
    $_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/security-txt.php';
    require dirname(__DIR__) . '/security-txt.php';
    return true;
}
if (preg_match($deny, $path)) {
    http_response_code(403);
    echo 'Accès refusé.';
    return true;
}
return false;
