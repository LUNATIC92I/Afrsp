<?php
/** AFRICA'SPY : fiche détaillée d'une formation. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

$slug = input('slug');
$course = $slug !== '' ? find_course($slug) : null;
if (!$course) {
    render_error_page(404, 'Formation introuvable', 'Cette formation n\'existe pas ou n\'est plus publiée.');
}
$me = current_user();
$outline = course_outline((int) $course['id'], $me['id'] ?? null);
$types = lesson_type_counts((int) $course['id']);
$exam = course_exam((int) $course['id']);
$quizTotal = array_sum(array_column($outline, 'quiz_count'));
$minutes = (int) q_val('SELECT COALESCE(SUM(l.duration_minutes),0) FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ?', [$course['id']]);
$enrolledCount = (int) q_val('SELECT COUNT(*) FROM course_enrollments WHERE course_id = ?', [$course['id']]);
$hasCert = $me ? (bool) q_val("SELECT 1 FROM certificates WHERE user_id = ? AND course_id = ? AND status = 'valid'", [$me['id'], $course['id']]) : false;
$pendingPay = $me ? q_one("SELECT reference, provider FROM payments WHERE user_id = ? AND course_id = ? AND status = 'pending' ORDER BY id DESC LIMIT 1", [$me['id'], $course['id']]) : null;
$deadline = promo_deadline();
$cover = !empty($course['cover_stored']) ? url('uploads/courses/' . rawurlencode($course['cover_stored'])) : url('assets/images/covers/' . $course['category_code'] . '.webp');

$pageTitle = $course['title'];
$pageDescription = str_limit($course['summary'], 160);
$activeNav = 'academy';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
  <section class="course-hero" style="--accent:<?= e($course['accent']) ?>">
    <div class="course-hero__bg" data-parallax="0.12"><img src="<?= e($cover) ?>" alt="" width="960" height="600"></div>
    <div class="container">
      <nav class="crumbs" aria-label="Fil d'Ariane"><a href="<?= e(url('index.php')) ?>">Accueil</a><span aria-hidden="true">/</span><a href="<?= e(url('academy.php')) ?>">Académie</a><span aria-hidden="true">/</span><a href="<?= e(url('academy.php', ['domaine' => $course['category_slug']])) ?>">Module <?= e($course['category_code']) ?></a></nav>
      <p class="course-hero__code mono reveal"><?= e($course['code']) ?> // <?= e(mb_strtoupper($course['category_name'])) ?></p>
      <h1 class="reveal"><?= e($course['title']) ?></h1>
      <p class="subtitle reveal"><?= e($course['subtitle']) ?></p>
      <dl class="meta-strip reveal">
        <div><dt>Niveau</dt><dd><?= e(level_label($course['level'])) ?></dd></div>
        <div><dt>Durée</dt><dd><?= (int) $course['duration_hours'] ?> h</dd></div>
        <div><dt>Chapitres</dt><dd><?= (int) $course['chapter_count'] ?></dd></div>
        <div><dt>Leçons</dt><dd><?= (int) $course['lesson_count'] ?></dd></div>
        <div><dt>Prix</dt><dd><?php if (course_discount_pct($course)): ?><s class="dim" style="font-weight:400"><?= e(fmt_xof((int) $course['price_xof'])) ?></s> <?php endif; ?><span class="gold"><?= e(fmt_xof(course_price($course))) ?></span></dd></div>
        <div><dt>Certification</dt><dd><?= $exam ? 'Oui | ' . (int) $exam['pass_mark'] . ' %' : 'Non' ?></dd></div>
      </dl>
    </div>
  </section>

  <div class="container course-layout">
    <article>
      <section class="course-block reveal">
        <h2 data-idx="01">Présentation générale</h2>
        <div class="prose"><p class="lead"><?= e($course['summary']) ?></p><?= sanitize_html($course['description']) ?></div>
      </section>
      <section class="course-block reveal">
        <h2 data-idx="02">Objectifs pédagogiques</h2>
        <ul class="checklist"><?php foreach (lines($course['objectives']) as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
      </section>
      <section class="course-block reveal">
        <h2 data-idx="03">Compétences à acquérir</h2>
        <ul class="checklist"><?php foreach (lines($course['skills']) as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
      </section>
      <section class="course-block reveal">
        <h2 data-idx="04">Prérequis</h2>
        <ul><?php foreach (lines($course['prerequisites']) as $l): ?><li><?= e($l) ?></li><?php endforeach; ?></ul>
      </section>
      <section class="course-block">
        <h2 data-idx="05" class="reveal">Programme détaillé</h2>
        <div class="syllabus reveal">
          <?php foreach ($outline as $i => $ch): ?>
          <details <?= $i === 0 ? 'open' : '' ?>>
            <summary>
              <span class="syllabus__n"><?= sprintf('%02d', $i + 1) ?></span>
              <span class="syllabus__t"><?= e($ch['title']) ?></span>
              <span class="syllabus__c"><?= count($ch['lessons']) ?> élément<?= count($ch['lessons']) > 1 ? 's' : '' ?><?= $ch['quiz_count'] ? ' | QCM ' . (int) $ch['quiz_count'] . ' q.' : '' ?></span>
            </summary>
            <div class="syllabus__body">
              <?php if ($ch['summary']): ?><p><?= e($ch['summary']) ?></p><?php endif; ?>
              <ul class="syllabus__lessons">
                <?php foreach ($ch['lessons'] as $l): ?>
                <li><span><?= $l['done'] ? '<span class="tag tag--green">Terminé</span> ' : '' ?><?= e($l['title']) ?></span><span class="tag"><?= e(lesson_type_label($l['type'])) ?> | <?= (int) $l['duration_minutes'] ?> min</span></li>
                <?php endforeach; ?>
                <?php if ($ch['quiz_count']): ?><li><span>Évaluation du chapitre</span><span class="tag tag--cyan">QCM | <?= (int) $ch['quiz_count'] ?> questions</span></li><?php endif; ?>
              </ul>
            </div>
          </details>
          <?php endforeach; ?>
        </div>
      </section>
      <section class="course-block reveal">
        <h2 data-idx="06">Ressources et évaluations</h2>
        <div class="resources" data-stagger="80">
          <div class="resource"><b><?= (int) ($types['text'] ?? 0) ?></b><span>Leçons développées</span></div>
          <div class="resource"><b><?= (int) ($types['video'] ?? 0) ?></b><span>Vidéos pédagogiques</span></div>
          <div class="resource"><b><?= (int) ($types['document'] ?? 0) ?></b><span>Supports PDF</span></div>
          <div class="resource"><b><?= (int) ($types['exercise'] ?? 0) ?></b><span>Exercices pratiques</span></div>
          <div class="resource"><b><?= (int) ($types['case_study'] ?? 0) ?></b><span>Études de cas</span></div>
          <div class="resource"><b><?= (int) $quizTotal ?></b><span>Questions QCM</span></div>
        </div>
        <?php if ($exam): ?>
        <div class="alert mt-2"><strong>Examen final :</strong> <?= (int) $exam['question_count'] ?> questions tirées au sort dans une banque de <?= (int) $exam['bank_size'] ?>, <?= (int) $exam['time_limit_min'] ?> minutes, seuil de réussite <?= (int) $exam['pass_mark'] ?> %, <?= (int) $exam['max_attempts'] ?> tentative<?= $exam['max_attempts'] > 1 ? 's' : '' ?> maximum. Réussite = certificat PDF vérifiable.</div>
        <?php endif; ?>
      </section>
    </article>

    <aside class="course-aside panel corners" aria-label="Inscription">
      <p class="eyebrow" style="margin-bottom:12px"><?= e($course['code']) ?></p>
      <?php if ($course['enrolled']): ?>
        <p class="label">Votre progression</p>
        <div class="progress mt-1" role="progressbar" aria-valuenow="<?= (int) $course['progress'] ?>" aria-valuemin="0" aria-valuemax="100"><span data-p="<?= (int) $course['progress'] ?>"></span></div>
        <p class="mono mt-1" style="font-size:.8rem"><?= (int) $course['progress'] ?> % complété</p>
        <a class="btn btn--primary mt-1" href="<?= e(url('learner/learning.php', ['course' => $course['id']])) ?>"><?= $course['progress'] > 0 ? 'Reprendre la formation' : 'Commencer la formation' ?></a>
        <?php if ($hasCert): ?><a class="btn btn--gold mt-1" href="<?= e(url('learner/certificates.php')) ?>">Mon certificat</a><?php endif; ?>
      <?php else: ?>
        <?= price_block($course, 'price--aside') ?>
        <?php if ($deadline): ?><div class="countdown" data-countdown="<?= $deadline ?>"><span class="mono">Fin de l'offre dans</span><b data-cd>-</b></div><?php endif; ?>
        <a class="btn btn--primary btn--pay mt-1" href="<?= e(url('checkout.php', ['course' => $course['slug']])) ?>" data-magnetic><span class="btn__stack"><?= course_is_free($course) ? 'Accéder gratuitement' : 'Acheter la formation' ?><small><?= course_is_free($course) ? 'Accès immédiat' : 'Mobile Money | carte | virement' ?></small></span></a>
        <?php if ($pendingPay): ?><p class="dim mt-1" style="font-size:.85rem">Paiement en attente : <a href="<?= e(url('payment-return.php', ['ref' => $pendingPay['reference']])) ?>"><?= e($pendingPay['reference']) ?></a></p><?php endif; ?>
        <?php if (!$me): ?><p class="dim mt-1" style="font-size:.85rem">Déjà membre ? <a href="<?= e(url('login.php')) ?>">Connectez-vous</a></p><?php endif; ?>
      <?php endif; ?>
      <ul>
        <li><span>Domaine</span><span><?= e($course['category_code']) ?> | <?= e(explode(' et ', $course['category_name'])[0]) ?></span></li>
        <li><span>Niveau</span><span><?= e(level_label($course['level'])) ?></span></li>
        <li><span>Durée indicative</span><span><?= (int) $course['duration_hours'] ?> h</span></li>
        <li><span>Temps de lecture estimé</span><span><?= intdiv($minutes, 60) ?> h <?= sprintf('%02d', $minutes % 60) ?></span></li>
        <li><span>Apprenants inscrits</span><span><?= $enrolledCount ?></span></li>
        <li><span>Accès</span><span>Illimité, à votre rythme</span></li>
        <li><span>Paiement</span><span>Unique, en FCFA</span></li>
      </ul>
      <p class="disclaimer">Certificat d'organisme de formation privé, vérifiable en ligne. Il ne constitue pas un diplôme d'État.</p>
    </aside>
  </div>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
