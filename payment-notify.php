<?php
/**
 * Notification serveur à serveur de CinetPay (notify_url).
 * Le contenu reçu n'est jamais cru sur parole : la transaction est revérifiée
 * auprès de l'API CinetPay avant toute ouverture d'accès.
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(200); // CinetPay effectue un ping GET de disponibilité
    exit('OK');
}
if (!payments_cinetpay_enabled()) {
    http_response_code(404);
    exit;
}
$ref = (string) ($_POST['cpm_trans_id'] ?? '');
if (!preg_match('/^PAY-\d{6}-[A-Z0-9]{8}$/', $ref) || (string) ($_POST['cpm_site_id'] ?? '') !== (string) config('payments.cinetpay_site_id')) {
    http_response_code(400);
    exit('Bad request');
}
// Vérification HMAC facultative (x-token) si la clé secrète est configurée
if ($secret = (string) config('payments.cinetpay_secret')) {
    $fields = ['cpm_site_id', 'cpm_trans_id', 'cpm_trans_date', 'cpm_amount', 'cpm_currency', 'signature', 'payment_method', 'cel_phone_num', 'cpm_phone_prefixe', 'cpm_language', 'cpm_version', 'cpm_payment_config', 'cpm_page_action', 'cpm_custom', 'cpm_designation', 'cpm_error_message'];
    $data = implode('', array_map(static fn ($f) => (string) ($_POST[$f] ?? ''), $fields));
    $token = (string) ($_SERVER['HTTP_X_TOKEN'] ?? '');
    if (!hash_equals(hash_hmac('sha256', $data, $secret), $token)) {
        audit('payment.notify_bad_token', 'payment', null, $ref);
        http_response_code(401);
        exit('Invalid token');
    }
}
$p = q_one('SELECT * FROM payments WHERE reference = ?', [$ref]);
if ($p) {
    try {
        cinetpay_sync($p);
    } catch (Throwable $e) {
        error_log('[AFRICASPY notify] ' . $e->getMessage());
        http_response_code(503);
        exit('Retry');
    }
}
http_response_code(200);
echo 'OK';
