<?php
/**
 * AFRICA'SPY — configuration applicative.
 *
 * Les valeurs sensibles (base de données, clé applicative, SMTP) ne sont JAMAIS
 * écrites ici : elles sont lues depuis config/config.local.php, fichier généré
 * par l'installateur, exclu du dépôt Git et protégé contre l'accès HTTP.
 * Les variables d'environnement (AFRICASPY_*) ont priorité si elles existent.
 */
declare(strict_types=1);

$defaults = [
    'app_name'      => "AFRICA'SPY",
    'env'           => 'production',          // production | development
    'base_url'      => '',                    // ex. https://academy.example.org (sans / final)
    'app_key'       => '',                    // 64 caractères hex — signature des certificats
    'timezone'      => 'Africa/Abidjan',
    'session_name'  => 'AFSPY_SESS',
    'session_idle'  => 7200,                  // déconnexion après 2 h d'inactivité
    'session_absolute' => 43200,              // reconnexion obligatoire après 12 h
    'trusted_proxies' => [],                  // IP des proxys inverses / CDN de confiance (X-Forwarded-For, X-Forwarded-Proto)
    'db' => [
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'name'     => 'africaspy',
        'user'     => '',
        'pass'     => '',
        'charset'  => 'utf8mb4',
    ],
    'mail' => [
        'transport'  => 'log',                // log | mail | smtp
        'from'       => 'no-reply@localhost',
        'from_name'  => "AFRICA'SPY Academy",
        'smtp_host'  => '',
        'smtp_port'  => 587,
        'smtp_user'  => '',
        'smtp_pass'  => '',
        'smtp_secure'=> 'tls',
    ],
    'uploads' => [
        'max_image'    => 4 * 1024 * 1024,
        'max_document' => 25 * 1024 * 1024,
        'max_video'    => 200 * 1024 * 1024,
    ],
    'payments' => [
        // CinetPay : Orange Money, MTN MoMo, Moov, Wave, cartes Visa/Mastercard (zone UEMOA/CEMAC)
        'cinetpay_enabled' => false,
        'cinetpay_apikey'  => '',
        'cinetpay_site_id' => '',
        'cinetpay_secret'  => '',             // clé secrète (vérification HMAC x-token des notifications)
        'cinetpay_api'     => 'https://api-checkout.cinetpay.com/v2',
        // Mode test : simulation locale du paiement. JAMAIS en production.
        'sandbox'          => false,
    ],
    'security' => [
        'login_max_per_email' => 5,           // échecs tolérés par couple compte + IP
        'login_max_global'    => 50,          // échecs tolérés par compte, toutes IP confondues
        'login_max_per_ip'    => 20,          // échecs tolérés par adresse IP
        'login_window_min'    => 15,          // fenêtre de comptage (minutes)
        'reset_ttl_min'       => 60,          // validité d'un lien de réinitialisation
        'register_max_per_ip' => 5,           // créations de compte par IP et par heure
    ],
];

$local = [];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
}

$config = array_replace_recursive($defaults, is_array($local) ? $local : []);

// Surcharge optionnelle par variables d'environnement (hébergements type PaaS)
$envMap = [
    'AFRICASPY_DB_HOST' => ['db', 'host'], 'AFRICASPY_DB_PORT' => ['db', 'port'],
    'AFRICASPY_DB_NAME' => ['db', 'name'], 'AFRICASPY_DB_USER' => ['db', 'user'],
    'AFRICASPY_DB_PASS' => ['db', 'pass'], 'AFRICASPY_APP_KEY' => ['app_key'],
    'AFRICASPY_BASE_URL' => ['base_url'],  'AFRICASPY_ENV'     => ['env'],
];
foreach ($envMap as $var => $path) {
    $val = getenv($var);
    if ($val === false || $val === '') continue;
    if (count($path) === 2) { $config[$path[0]][$path[1]] = $val; } else { $config[$path[0]] = $val; }
}

$config['installed'] = is_file($localFile) || getenv('AFRICASPY_DB_USER') !== false;

return $config;
