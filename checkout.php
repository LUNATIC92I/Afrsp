<?php
/** Paiement d'une formation : récapitulatif, choix du moyen de paiement, création de la commande. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

$course = find_course(input('course'));
if (!$course) {
    render_error_page(404, 'Formation introuvable', 'Cette formation n\'existe pas ou n\'est plus publiée.');
}
$u = current_user();
if (!$u) {
    $_SESSION['intended'] = url('checkout.php', ['course' => $course['slug']]);
    flash('info', 'Créez votre compte (ou connectez-vous) pour finaliser l\'achat de la formation.');
    redirect('register.php', ['formation' => $course['slug']]);
}
if (is_enrolled((int) $u['id'], (int) $course['id'])) {
    redirect('learner/learning.php', ['course' => $course['id']]);
}
if (course_is_free($course)) {
    q('INSERT IGNORE INTO course_enrollments (user_id, course_id, last_activity) VALUES (?, ?, UTC_TIMESTAMP())', [$u['id'], $course['id']]);
    redirect('learner/learning.php', ['course' => $course['id']]);
}
$methods = payment_methods();
$error = null;
if (is_post()) {
    verify_csrf();
    rate_limit_or_fail('checkout:user', (string) $u['id'], 20, 3600, 'Trop de tentatives de paiement. Réessayez dans une heure ou contactez l\'Académie.');
    $method = input('method');
    $phone = preg_replace('/[^\d+]/', '', input('phone'));
    if (!isset($methods[$method])) {
        $error = 'Choisissez un moyen de paiement disponible.';
    } elseif (empty($_POST['terms'])) {
        $error = 'Vous devez accepter les conditions de vente et la charte éthique.';
    } elseif ($phone !== '' && !preg_match('/^\+?\d{8,15}$/', $phone)) {
        $error = 'Numéro de téléphone invalide (8 à 15 chiffres, indicatif facultatif).';
    } else {
        $p = create_payment($u, $course, $method, $phone ?: null);
        if ($method === 'cinetpay') {
            try {
                $urlPay = cinetpay_init($p, $u, $course);
                header('Location: ' . $urlPay, true, 303);
                exit;
            } catch (Throwable $e) {
                error_log('[AFRICASPY pay] ' . $e->getMessage());
                $error = 'Le paiement en ligne est momentanément indisponible. Réessayez ou choisissez le virement. (' . $e->getMessage() . ')';
            }
        } elseif ($method === 'sandbox') {
            redirect('payment-sandbox.php', ['ref' => $p['reference']]);
        } else {
            redirect('payment-return.php', ['ref' => $p['reference']]);
        }
    }
}
$pending = q_all("SELECT * FROM payments WHERE user_id = ? AND course_id = ? AND status = 'pending' ORDER BY id DESC", [$u['id'], $course['id']]);
$deadline = promo_deadline();
$cover = !empty($course['cover_stored']) ? url('uploads/courses/' . rawurlencode($course['cover_stored'])) : url('assets/images/covers/' . $course['category_code'] . '.webp');

$pageTitle = 'Paiement : ' . $course['title'];
$activeNav = 'academy';
require APP_ROOT . '/includes/header.php';
?>
<main id="main" class="section checkout-page">
  <div class="container">
    <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('academy.php')) ?>">Académie</a><span aria-hidden="true">/</span><a href="<?= e(url('course.php', ['slug' => $course['slug']])) ?>"><?= e($course['code']) ?></a><span aria-hidden="true">/</span><span>Paiement</span></nav>
    <p class="eyebrow" data-decrypt>Accès sécurisé | étape 2 / 3</p>
    <h1 class="checkout-title">Débloquer la formation</h1>
    <ol class="checkout-steps" aria-label="Étapes">
      <li class="is-done">Compte</li><li class="is-current" aria-current="step">Paiement</li><li>Accès à la formation</li>
    </ol>
    <?php if ($error): ?><div class="alert alert--error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <?php foreach ($pending as $pp): if ($pp['provider'] !== 'manual') continue; ?>
      <div class="alert alert--warning">Une demande de virement est en attente (référence <strong class="mono"><?= e($pp['reference']) ?></strong>). <a href="<?= e(url('payment-return.php', ['ref' => $pp['reference']])) ?>">Voir les instructions</a></div>
    <?php endforeach; ?>
    <div class="checkout">
      <form class="checkout__form panel corners" method="post" novalidate>
        <?= csrf_field() ?>
        <h2 class="checkout__h">Moyen de paiement</h2>
        <?php if (!$methods): ?>
          <div class="alert alert--warning">Aucun moyen de paiement n'est configuré pour le moment. <a href="<?= e(url('contact.php')) ?>">Contactez l'Académie</a>.</div>
        <?php endif; ?>
        <div class="paymethods" role="radiogroup" aria-label="Moyen de paiement">
          <?php $first = true; foreach ($methods as $key => $m): ?>
          <label class="paymethod paymethod--<?= e($key) ?>">
            <input type="radio" name="method" value="<?= e($key) ?>" <?= (input('method') === $key || (!input('method') && $first)) ? 'checked' : '' ?>>
            <span class="paymethod__icon" aria-hidden="true"><?= $key === 'manual' ? '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h14l-3-3M20 16H6l3 3"/></svg>' : ($key === 'sandbox' ? '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/></svg>' : '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2.5" width="12" height="19" rx="2.5"/><path d="M10.5 18.5h3"/></svg>') ?></span>
            <span><strong><?= e($m['label']) ?></strong><small><?= e($m['hint']) ?></small></span>
          </label>
          <?php $first = false; endforeach; ?>
        </div>
        <?php if (isset($methods['cinetpay'])): ?>
        <div class="field mt-2"><label for="phone">Numéro Mobile Money (facultatif)</label><input class="input" id="phone" name="phone" inputmode="tel" placeholder="+225 07 00 00 00 00" value="<?= e(input('phone')) ?>"><span class="hint">Pré-remplit le guichet sécurisé ; vous pourrez le modifier.</span></div>
        <?php endif; ?>
        <label class="check mt-2"><input type="checkbox" name="terms" value="1" required> <span>J'accepte les <a href="<?= e(url('legal.php#vente')) ?>" target="_blank" rel="noopener">conditions de vente</a> et la <a href="<?= e(url('about.php#ethique')) ?>" target="_blank" rel="noopener">charte éthique</a>. L'accès est personnel et non cessible.</span></label>
        <button class="btn btn--primary btn--block btn--pay mt-2" type="submit" <?= $methods ? '' : 'disabled' ?> data-magnetic>
          <span class="btn__stack">Payer <?= e(fmt_xof(course_price($course))) ?><small>Paiement sécurisé | reçu PDF immédiat</small></span>
        </button>
        <p class="checkout__secure mono"><span aria-hidden="true"><svg viewBox="0 0 24 24" width="13" height="13" style="vertical-align:-2px" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg></span> Aucune donnée bancaire n'est saisie ni stockée sur AFRICA'SPY.</p>
      </form>

      <aside class="order panel" aria-label="Récapitulatif de commande">
        <div class="order__img"><img src="<?= e($cover) ?>" alt="" width="960" height="600"></div>
        <div class="order__body">
          <p class="mono cyan" style="font-size:.72rem;letter-spacing:.16em"><?= e($course['code']) ?> | <?= e(level_label($course['level'])) ?></p>
          <h2 class="order__title"><?= e($course['title']) ?></h2>
          <ul class="order__incl">
            <li><?= (int) $course['chapter_count'] ?> chapitres | <?= (int) $course['lesson_count'] ?> leçons, vidéos et supports PDF</li>
            <li>Exercices pratiques et études de cas</li>
            <li>QCM corrigés et examen final</li>
            <li>Certificat PDF vérifiable par QR Code</li>
            <li>Accès illimité à votre rythme</li>
          </ul>
          <div class="order__lines">
            <div><span>Prix de référence</span><span><?= e(fmt_xof((int) $course['price_xof'])) ?></span></div>
            <?php if (course_discount_pct($course)): ?><div class="order__discount"><span><?= e(setting('promo_label', 'Remise')) ?> (-<?= course_discount_pct($course) ?> %)</span><span>- <?= e(fmt_xof((int) $course['price_xof'] - course_price($course))) ?></span></div><?php endif; ?>
            <div class="order__total"><span>Total à payer</span><span data-count-price="<?= course_price($course) ?>"><?= e(fmt_xof(course_price($course))) ?></span></div>
          </div>
          <?php if ($deadline): ?><div class="countdown" data-countdown="<?= $deadline ?>" aria-live="off"><span class="mono">Fin de l'offre dans</span><b data-cd>-</b></div><?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
