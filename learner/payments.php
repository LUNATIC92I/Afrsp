<?php
/** Espace apprenant — historique des paiements et reçus. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

$u = require_login();
$rows = q_all('SELECT p.*, c.title, c.code, c.slug FROM payments p JOIN courses c ON c.id = p.course_id WHERE p.user_id = ? ORDER BY p.created_at DESC', [$u['id']]);
$total = array_sum(array_map(static fn ($r) => $r['status'] === 'paid' ? (int) $r['amount_xof'] : 0, $rows));
$pageTitle = 'Mes paiements';
$appArea = 'learner';
$activeApp = 'payments';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="stats" data-stagger="70">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Formations achetées</div><div class="stat__value" data-count="<?= count(array_filter($rows, static fn ($r) => $r['status'] === 'paid')) ?>">0</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>Total réglé</div><div class="stat__value" style="font-size:1.5rem"><?= e(fmt_xof($total)) ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--cyan)"></i>En attente</div><div class="stat__value" data-count="<?= count(array_filter($rows, static fn ($r) => $r['status'] === 'pending')) ?>">0</div></div>
</section>
<section class="box reveal">
  <div class="box__head"><h2>Historique</h2><a class="btn btn--sm btn--ghost" href="<?= e(url('academy.php')) ?>">Catalogue</a></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Référence</th><th>Formation</th><th>Date</th><th>Moyen</th><th class="num">Montant</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="7"><div class="empty"><strong>Aucun paiement</strong>Achetez une formation depuis le catalogue pour y accéder.</div></td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td class="mono" style="font-size:.8rem"><?= e($r['reference']) ?></td>
        <td><span class="mono cyan" style="font-size:.72rem"><?= e($r['code']) ?></span><br><?= e($r['title']) ?></td>
        <td><?= e(fmt_datetime($r['paid_at'] ?? $r['created_at'])) ?></td>
        <td><small><?= e(payment_provider_label($r['provider'])) ?></small></td>
        <td class="num"><?= e(fmt_xof((int) $r['amount_xof'])) ?></td>
        <td><?= payment_status_pill($r['status']) ?></td>
        <td><div class="actions">
          <?php if ($r['status'] === 'paid'): ?><a class="btn btn--sm btn--gold" href="<?= e(url('learner/receipt.php', ['ref' => $r['reference']])) ?>" data-no-transition>Reçu PDF</a>
          <?php elseif ($r['status'] === 'pending'): ?><a class="btn btn--sm" href="<?= e(url($r['provider'] === 'sandbox' ? 'payment-sandbox.php' : 'payment-return.php', ['ref' => $r['reference']])) ?>"><?= $r['provider'] === 'manual' ? 'Instructions' : 'Finaliser' ?></a>
          <?php else: ?><a class="btn btn--sm" href="<?= e(url('checkout.php', ['course' => $r['slug']])) ?>">Réessayer</a><?php endif; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php require APP_ROOT . '/includes/app-footer.php';
