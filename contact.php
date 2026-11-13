<?php
/** AFRICA'SPY : Contact (envoi AJAX, repli formulaire classique). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/contact-handler.php';

if (is_post()) {
    verify_csrf();
    [$ok, $msg, $errors] = handle_contact($_POST);
    if (is_ajax()) {
        json_response(['ok' => $ok, 'message' => $msg, 'errors' => $errors], $ok ? 200 : 422);
    }
    flash($ok ? 'success' : 'error', $msg);
    if (!$ok) set_old($_POST);
    redirect('contact.php');
}
$pageTitle = 'Contact';
$activeNav = 'contact';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
  <section class="page-hero">
    <div class="page-hero__bg" style="background-image:url('<?= e(url('assets/images/geo-table.webp')) ?>')"></div>
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><span>Contact</span></nav>
      <p class="eyebrow">Canal sécurisé | réponse sous 72 h ouvrées</p>
      <h1 data-split>Contacter l'Académie</h1>
      <p class="reveal">Questions sur les formations, les certificats, les inscriptions de groupes ou les partenariats pédagogiques.</p>
    </div>
  </section>
  <section class="section">
    <div class="container contact">
      <div class="contact__info reveal reveal--left">
        <p class="eyebrow">Coordonnées</p>
        <dl>
          <?php if ($v = setting('contact_email')): ?><div><dt>E-mail</dt><dd><a href="mailto:<?= e($v) ?>"><?= e($v) ?></a></dd></div><?php endif; ?>
          <?php if ($v = setting('contact_phone')): ?><div><dt>Téléphone</dt><dd><?= e($v) ?></dd></div><?php endif; ?>
          <?php if ($v = setting('contact_address')): ?><div><dt>Adresse</dt><dd><?= nl2br(e($v)) ?></dd></div><?php endif; ?>
          <div><dt>Vérification de certificat</dt><dd><a href="<?= e(url('verify-certificate.php')) ?>">Service public en ligne</a></dd></div>
        </dl>
        <p class="disclaimer">Ne transmettez jamais d'informations sensibles, confidentielles ou relatives à des tiers par ce formulaire. AFRICA'SPY ne réalise aucune enquête ni prestation de surveillance.</p>
      </div>
      <form class="panel corners reveal" style="padding:clamp(22px,4vw,40px)" method="post" action="<?= e(url('contact.php')) ?>" data-ajax-form novalidate>
        <?= csrf_field() ?>
        <div class="form-row">
          <div class="field"><label for="full_name">Nom complet</label><input class="input" id="full_name" name="full_name" required maxlength="160" value="<?= e(old('full_name', current_user() ? current_user()['first_name'] . ' ' . current_user()['last_name'] : '')) ?>"></div>
          <div class="field"><label for="email">E-mail</label><input class="input" type="email" id="email" name="email" required maxlength="190" value="<?= e(old('email', current_user()['email'] ?? '')) ?>"></div>
        </div>
        <div class="field"><label for="subject">Objet</label>
          <select class="select" id="subject" name="subject">
            <?php foreach (['Information sur les formations', 'Inscription de groupe / organisation', 'Certificat et vérification', 'Assistance technique', 'Autre demande'] as $s): ?><option <?= old('subject') === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label for="message">Message</label><textarea class="textarea" id="message" name="message" required minlength="20" maxlength="4000"><?= e(old('message')) ?></textarea></div>
        <div class="sr-only" aria-hidden="true"><label for="website">Ne pas remplir</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <label class="check mb-2"><input type="checkbox" name="consent" value="1" required> J'accepte que mes données soient utilisées uniquement pour traiter ma demande.</label>
        <button class="btn btn--primary btn--block" type="submit">Transmettre le message</button>
      </form>
    </div>
  </section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
