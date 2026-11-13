<?php
/** AJAX : statut d'une commande (polling de la page de retour). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';
$u = require_login();
rate_limit_or_fail('paystatus:user', (string) $u['id'], 120, 600);
$p = q_one('SELECT * FROM payments WHERE reference = ? AND user_id = ?', [input('ref'), $u['id']]);
if (!$p) json_response(['ok' => false], 404);
if ($p['provider'] === 'cinetpay' && $p['status'] === 'pending' && payments_cinetpay_enabled()) {
    try { $p['status'] = cinetpay_sync($p); } catch (Throwable) {}
}
json_response(['ok' => true, 'status' => $p['status']]);
