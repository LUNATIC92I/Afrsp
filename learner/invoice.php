<?php
/** Téléchargement d'une facture ou d'un avoir PDF (titulaire ou gestionnaire des paiements). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/invoices.php';
require APP_ROOT . '/includes/learning.php';

$u = require_login();
$inv = q_one('SELECT * FROM invoices WHERE invoice_no = ?', [strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', input('no')))]);
if (!$inv || ((int) $inv['user_id'] !== (int) $u['id'] && !can('payments.manage'))) {
    render_error_page(404, 'Document introuvable', 'Cette facture n\'existe pas ou ne vous appartient pas.');
}
stream_file(invoice_file($inv), 'application/pdf', ($inv['doc_type'] === 'invoice' ? 'Facture-' : 'Avoir-') . $inv['invoice_no'] . '.pdf', input('dl') !== '1');
