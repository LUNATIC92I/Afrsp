<?php
/** Gabarit commun des pages d'authentification. Attend $authTitle, $authIntro, $authBody (callable). */
declare(strict_types=1);
$bodyClass = 'page-auth';
require APP_ROOT . '/includes/header.php';
?>
<main id="main" class="auth">
  <div class="auth__visual" aria-hidden="true">
    <div class="auth__quote">
      <p class="eyebrow">Intelligence Portal</p>
      <p>« Un renseignement exact, livré à temps, au bon destinataire, dans le respect du droit. »</p>
      <p class="mono dim" style="font-size:.75rem;letter-spacing:.2em">AFSPY // PRINCIPE N°1</p>
    </div>
  </div>
  <div class="auth__panel">
    <div class="auth__box reveal">
      <p class="auth__terminal">&gt; <?= e($authTerminal ?? 'Authentification requise') ?><span style="animation:blink 1s steps(1) infinite">_</span></p>
      <h1><?= e($authTitle) ?></h1>
      <p><?= e($authIntro) ?></p>
      <?php if (!empty($authError)): ?><div class="alert alert--error" role="alert"><?= e($authError) ?></div><?php endif; ?>
      <?php $authBody(); ?>
    </div>
  </div>
</main>
<?php $hideChrome = true; require APP_ROOT . '/includes/footer.php'; ?>
