<?php
/** Retour de paiement : statut vérifié côté serveur, instructions de virement, accès accordé. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

$u = require_login();
$p = q_one('SELECT p.*, c.title, c.code, c.slug FROM payments p JOIN courses c ON c.id = p.course_id WHERE p.reference = ? AND p.user_id = ?', [input('ref'), $u['id']]);
if (!$p) {
    render_error_page(404, 'Commande introuvable', 'Cette référence de paiement n\'existe pas ou ne vous appartient pas.');
}
if ($p['provider'] === 'cinetpay' && $p['status'] === 'pending' && payments_cinetpay_enabled()) {
    try {
        $p['status'] = cinetpay_sync($p);
    } catch (Throwable $e) {
        error_log('[AFRICASPY pay] ' . $e->getMessage());
    }
}
$st = $p['status'];
$pageTitle = 'Statut du paiement';
require APP_ROOT . '/includes/header.php';
?>
<main id="main" class="section">
  <div class="container container--narrow">
    <ol class="checkout-steps" aria-label="Étapes"><li class="is-done">Compte</li><li class="<?= $st === 'paid' ? 'is-done' : 'is-current' ?>">Paiement</li><li class="<?= $st === 'paid' ? 'is-current' : '' ?>">Accès à la formation</li></ol>
    <?php if ($st === 'paid'): ?>
    <section class="granted panel corners" data-granted>
      <div class="granted__rings" aria-hidden="true"><i></i><i></i><i></i></div>
      <p class="granted__code mono">AUTORISATION <?= e($p['reference']) ?></p>
      <h1 class="granted__title" data-decrypt>ACCESS GRANTED</h1>
      <p class="lead">Paiement de <strong><?= e(fmt_xof((int) $p['amount_xof'])) ?></strong> confirmé. La formation « <?= e($p['title']) ?> » est désormais ouverte dans votre espace.</p>
      <div class="flex" style="justify-content:center">
        <a class="btn btn--primary" href="<?= e(url('learner/learning.php', ['course' => $p['course_id']])) ?>" data-magnetic>Commencer la formation</a>
        <a class="btn btn--gold" href="<?= e(url('learner/receipt.php', ['ref' => $p['reference']])) ?>" data-no-transition>Télécharger le reçu</a>
      </div>
    </section>
    <?php elseif ($st === 'pending' && $p['provider'] === 'manual'): ?>
    <section class="panel corners" style="padding:clamp(22px,4vw,40px)">
      <p class="eyebrow eyebrow--gold">Virement / dépôt · en attente de réception</p>
      <h1 style="font-size:clamp(1.5rem,3vw,2.2rem)">Instructions de paiement</h1>
      <dl class="kv mt-2">
        <dt>Référence à indiquer</dt><dd><strong class="mono gold" style="font-size:1.3rem;letter-spacing:.1em"><?= e($p['reference']) ?></strong></dd>
        <dt>Montant exact</dt><dd><strong><?= e(fmt_xof((int) $p['amount_xof'])) ?></strong></dd>
        <dt>Formation</dt><dd><?= e($p['code']) ?> · <?= e($p['title']) ?></dd>
      </dl>
      <div class="charter mt-2"><span class="charter__stamp">COORDONNÉES</span><p class="mb-0" style="white-space:pre-line"><?= e(setting('payment_manual_instructions')) ?></p></div>
      <p class="dim mt-2">Dès réception, l'administration valide le paiement : vous recevez un e-mail et la formation apparaît dans votre espace. Vous pouvez suivre l'état depuis <a href="<?= e(url('learner/payments.php')) ?>">Mes paiements</a>.</p>
    </section>
    <?php elseif ($st === 'pending'): ?>
    <section class="panel corners center" style="padding:40px" data-pay-poll="<?= e($p['reference']) ?>">
      <span class="loader" aria-hidden="true"></span>
      <h1 style="font-size:1.6rem;margin-top:16px">Confirmation en cours…</h1>
      <p class="dim">Nous attendons la confirmation de l'opérateur. Cette page se met à jour automatiquement.</p>
    </section>
    <?php else: ?>
    <section class="panel corners center" style="padding:40px">
      <p class="error-screen__code mono">PAIEMENT <?= e(mb_strtoupper(['failed' => 'refusé', 'cancelled' => 'annulé', 'refunded' => 'remboursé'][$st] ?? $st)) ?></p>
      <h1 style="font-size:1.6rem">Le paiement n'a pas abouti</h1>
      <p class="dim">Aucun accès n'a été ouvert. Vous pouvez réessayer avec le même moyen ou en choisir un autre.</p>
      <a class="btn btn--primary" href="<?= e(url('checkout.php', ['course' => $p['slug']])) ?>">Réessayer</a>
    </section>
    <?php endif; ?>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
