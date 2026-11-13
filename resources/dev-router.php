<?php
/**
 * Routeur pour le serveur PHP intégré (développement local uniquement) :
 *   php -S 127.0.0.1:8080 resources/dev-router.php
 * Reproduit les interdictions d'accès du fichier .htaccess.
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$deny = '#(^|/)\.|^/(config|includes|database|vendor|storage|resources|certificates)(/|$)|^/uploads/(documents|videos)(/|$)|^/install/(installer\.php|cli\.php|install\.lock)$|^/(composer\.(json|lock)|README\.md)$|^/uploads/.*\.(php\d?|phtml|phar)$#i';
if (preg_match($deny, $path)) {
    http_response_code(403);
    echo 'Accès refusé.';
    return true;
}
return false;
