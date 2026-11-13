<?php
/** Reçu de paiement PDF (titulaire ou gestionnaire des paiements). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';
require APP_ROOT . '/includes/learning.php';

$u = require_login();
$p = q_one("SELECT p.*, c.title AS course_title, c.code AS course_code, u.first_name, u.last_name, u.email
              FROM payments p JOIN courses c ON c.id = p.course_id JOIN users u ON u.id = p.user_id WHERE p.reference = ? AND p.status = 'paid'", [input('ref')]);
if (!$p || ((int) $p['user_id'] !== (int) $u['id'] && !can('payments.manage'))) {
    render_error_page(404, 'Reçu introuvable', 'Ce reçu n\'existe pas ou ne vous appartient pas.');
}
$tmp = tempnam(APP_ROOT . '/storage/cache', 'rcpt');
file_put_contents($tmp, payment_receipt_pdf($p));
register_shutdown_function(static fn () => @unlink($tmp));
stream_file($tmp, 'application/pdf', 'AFRICASPY-recu-' . $p['reference'] . '.pdf', false);
