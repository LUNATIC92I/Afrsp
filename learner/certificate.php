<?php
/** Téléchargement du certificat PDF par son titulaire (ou le personnel habilité). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/learning.php';

$u = require_login();
$no = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', input('no')));
$cert = q_one('SELECT * FROM certificates WHERE certificate_no = ?', [$no]);
if (!$cert || ((int) $cert['user_id'] !== (int) $u['id'] && !can('certificates.manage'))) {
    render_error_page(404, 'Certificat introuvable', 'Ce certificat n\'existe pas ou ne vous appartient pas.');
}
if ($cert['status'] === 'revoked') {
    render_error_page(410, 'Certificat révoqué', 'Ce certificat a été révoqué et ne peut plus être téléchargé.');
}
$path = certificate_file($cert['certificate_no']);
stream_file($path, 'application/pdf', 'AFRICASPY-certificat-' . $cert['certificate_no'] . '.pdf', input('dl') !== '1');
