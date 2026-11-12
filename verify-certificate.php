<?php
/** AFRICA'SPY — vérification publique des certificats (numéro ou QR Code). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', input('code')));
$result = null;
if ($code !== '') {
    // Limitation simple : 30 vérifications / 10 min par session
    $_SESSION['verify_hits'] = array_values(array_filter($_SESSION['verify_hits'] ?? [], static fn ($t) => $t > time() - 600));
    if (count($_SESSION['verify_hits']) >= 30) {
        $result = ['state' => 'throttled'];
    } else {
        $_SESSION['verify_hits'][] = time();
        $row = q_one('SELECT c.*, co.code AS course_code FROM certificates c JOIN courses co ON co.id = c.course_id WHERE c.certificate_no = ?', [$code]);
        $valid = $row && hash_equals($row['verify_hash'], hash_hmac('sha256', $row['certificate_no'] . '|' . $row['user_id'] . '|' . $row['course_id'] . '|' . $row['issued_at'], (string) config('app_key')));
        $result = !$row ? ['state' => 'unknown'] : ($row['status'] === 'revoked' ? ['state' => 'revoked', 'row' => $row] : ($valid ? ['state' => 'valid', 'row' => $row] : ['state' => 'tampered']));
    }
}
$pageTitle = 'Vérification de certificat';
$pageDescription = "Vérifiez l'authenticité d'un certificat AFRICA'SPY par son numéro ou son QR Code.";
$activeNav = 'verify';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
  <section class="page-hero">
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><span>Vérification</span></nav>
      <p class="eyebrow eyebrow--gold">Service public · authentification</p>
      <h1 data-split>Vérifier un certificat</h1>
      <p class="reveal">Saisissez le numéro figurant sur le certificat (format AFSPY-AAAA-XXXXXXXX) ou scannez son QR Code.</p>
    </div>
  </section>
  <section class="section section--tight">
    <div class="container container--narrow">
      <div class="panel corners verify-box">
        <form class="verify-inline" method="get" action="<?= e(url('verify-certificate.php')) ?>" role="search" style="margin:0">
          <label class="sr-only" for="code">Numéro de certificat</label>
          <input class="input" id="code" name="code" value="<?= e($code) ?>" placeholder="AFSPY-2026-XXXXXXXX" maxlength="32" required autocomplete="off">
          <button class="btn btn--gold" type="submit">Vérifier</button>
        </form>
        <?php if ($result): $st = $result['state']; ?>
          <?php if ($st === 'valid'): $r = $result['row']; ?>
          <div class="verify-result verify-result--valid" role="status">
            <div class="verify-result__status"><span class="dot"></span> CERTIFICAT AUTHENTIQUE</div>
            <dl>
              <dt>Numéro</dt><dd class="mono"><?= e($r['certificate_no']) ?></dd>
              <dt>Titulaire</dt><dd><?= e($r['holder_name']) ?></dd>
              <dt>Formation</dt><dd><?= e($r['course_title']) ?> <span class="dim mono">(<?= e($r['course_code']) ?>)</span></dd>
              <dt>Score à l'examen</dt><dd><?= e(number_format((float) $r['score'], 0)) ?> %</dd>
              <dt>Date d'obtention</dt><dd><?= e(fmt_date($r['issued_at'])) ?></dd>
              <dt>Émetteur</dt><dd>AFRICA'SPY — Intelligence Academy (organisme de formation privé)</dd>
            </dl>
            <p class="disclaimer mt-2">Ce certificat atteste la réussite d'un parcours de formation interne. Il ne constitue ni un diplôme d'État ni une qualification officiellement reconnue.</p>
          </div>
          <?php elseif ($st === 'revoked'): ?>
          <div class="verify-result verify-result--invalid" role="alert"><div class="verify-result__status">CERTIFICAT RÉVOQUÉ</div><p class="mt-1">Ce numéro correspond à un certificat révoqué le cas échéant par l'administration de l'Académie. Il n'est plus valide.</p></div>
          <?php elseif ($st === 'throttled'): ?>
          <div class="verify-result verify-result--invalid" role="alert"><div class="verify-result__status">LIMITE ATTEINTE</div><p class="mt-1">Trop de vérifications successives. Réessayez dans quelques minutes.</p></div>
          <?php else: ?>
          <div class="verify-result verify-result--invalid" role="alert"><div class="verify-result__status">AUCUN CERTIFICAT VALIDE</div><p class="mt-1">Aucun certificat authentique ne correspond à ce numéro. Vérifiez la saisie ; en cas de doute sur un document présenté, contactez l'Académie.</p></div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
