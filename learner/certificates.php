<?php
/** Espace apprenant — certificats obtenus. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$u = require_login();
$certs = q_all('SELECT ct.*, c.code, c.slug FROM certificates ct JOIN courses c ON c.id = ct.course_id WHERE ct.user_id = ? ORDER BY ct.issued_at DESC', [$u['id']]);
$pageTitle = 'Mes certificats';
$appArea = 'learner';
$activeApp = 'certificates';
require APP_ROOT . '/includes/app-header.php';
?>
<p class="dim reveal" style="max-width:780px">Chaque certificat comporte un numéro unique et un QR Code renvoyant vers la page publique de vérification. Il atteste la réussite d'un parcours de formation privé et ne constitue pas un diplôme d'État.</p>
<?php if (!$certs): ?>
  <div class="empty reveal"><strong>Aucun certificat pour le moment</strong>Terminez une formation et réussissez son examen final pour obtenir votre premier certificat.</div>
<?php endif; ?>
<div class="cert-list" data-stagger="90">
<?php foreach ($certs as $c): ?>
  <article class="cert-card">
    <span class="mono"><?= e($c['certificate_no']) ?></span>
    <h3><?= e($c['course_title']) ?></h3>
    <p class="dim" style="margin:0"><?= e($c['code']) ?> · Délivré le <?= e(fmt_date($c['issued_at'])) ?> · Score <?= e(number_format((float) $c['score'], 0)) ?> %</p>
    <?php if ($c['status'] === 'revoked'): ?><p class="tag tag--red mt-1">Révoqué</p><?php endif; ?>
    <div class="flex">
      <?php if ($c['status'] === 'valid'): ?>
      <a class="btn btn--sm btn--gold" href="<?= e(url('learner/certificate.php', ['no' => $c['certificate_no'], 'dl' => 1])) ?>" data-no-transition>Télécharger (PDF)</a>
      <a class="btn btn--sm" href="<?= e(url('learner/certificate.php', ['no' => $c['certificate_no']])) ?>" target="_blank" rel="noopener" data-no-transition>Afficher</a>
      <?php endif; ?>
      <a class="btn btn--sm btn--ghost" href="<?= e(url('verify-certificate.php', ['code' => $c['certificate_no']])) ?>" target="_blank" rel="noopener">Vérification publique</a>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
