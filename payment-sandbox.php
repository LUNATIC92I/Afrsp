<?php
/** Guichet de paiement SIMULÉ — développement et tests uniquement (désactivé en production). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

if (!payments_sandbox_enabled()) {
    render_error_page(404, 'Indisponible', 'Le mode de paiement de test est désactivé sur ce serveur.');
}
$u = require_login();
$p = q_one("SELECT p.*, c.title, c.code FROM payments p JOIN courses c ON c.id = p.course_id WHERE p.reference = ? AND p.user_id = ? AND p.provider = 'sandbox'", [input('ref'), $u['id']]);
if (!$p) {
    render_error_page(404, 'Commande introuvable', 'Cette référence de paiement n\'existe pas.');
}
if (is_post() && $p['status'] === 'pending') {
    verify_csrf();
    if (input('outcome') === 'success') {
        mark_payment_paid((int) $p['id'], ['amount' => (int) $p['amount_xof'], 'txn' => 'SANDBOX-' . strtoupper(bin2hex(random_bytes(4))), 'method' => 'Simulation']);
    } else {
        mark_payment_status((int) $p['id'], 'failed', 'Refus simulé (mode test)');
    }
    redirect('payment-return.php', ['ref' => $p['reference']]);
}
$pageTitle = 'Guichet de paiement (test)';
$hideChrome = true;
require APP_ROOT . '/includes/header.php';
?>
<main id="main" class="sandbox">
  <div class="sandbox__box panel corners reveal">
    <p class="sandbox__flag">MODE TEST — AUCUN DÉBIT RÉEL</p>
    <p class="mono dim">Guichet simulé · <?= e($p['reference']) ?></p>
    <h1><?= e(fmt_xof((int) $p['amount_xof'])) ?></h1>
    <p><?= e($p['code']) ?> · <?= e($p['title']) ?></p>
    <?php if ($p['status'] !== 'pending'): ?>
      <div class="alert">Cette commande est déjà traitée (<?= e($p['status']) ?>).</div>
      <a class="btn btn--ghost" href="<?= e(url('payment-return.php', ['ref' => $p['reference']])) ?>">Voir le statut</a>
    <?php else: ?>
    <form method="post" class="flex mt-2" style="justify-content:center">
      <?= csrf_field() ?>
      <button class="btn btn--primary" name="outcome" value="success" type="submit">Simuler un paiement accepté</button>
      <button class="btn btn--danger" name="outcome" value="failure" type="submit">Simuler un refus</button>
    </form>
    <?php endif; ?>
    <p class="disclaimer mt-2">Ce guichet n'existe qu'en environnement de développement. En production, l'apprenant est redirigé vers le guichet sécurisé CinetPay (Mobile Money, cartes) ou reçoit les instructions de virement.</p>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
