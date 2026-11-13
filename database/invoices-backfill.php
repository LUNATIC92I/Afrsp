<?php
/**
 * Émet les factures (et avoirs) manquants pour les paiements déjà encaissés avant la mise en place
 * de la facturation. Idempotent. Aucun e-mail n'est envoyé.
 *   php database/invoices-backfill.php
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/invoices.php';
$n = $c = 0;
foreach (q_all("SELECT id, status FROM payments WHERE status IN ('paid','refunded') ORDER BY COALESCE(paid_at, created_at), id") as $p) {
    if (!q_val("SELECT 1 FROM invoices WHERE payment_id = ? AND doc_type = 'invoice'", [$p['id']])) { issue_invoice((int) $p['id']); $n++; }
    if ($p['status'] === 'refunded' && !q_val("SELECT 1 FROM invoices WHERE payment_id = ? AND doc_type = 'credit_note'", [$p['id']])) { issue_credit_note((int) $p['id']); $c++; }
}
echo "Factures émises : $n | Avoirs émis : $c\n";
