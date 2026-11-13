<?php
/** Espace apprenant : mes formations et historique. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

$u = require_login();
$rows = q_all('SELECT ce.*, c.title, c.code, c.slug, c.level, c.duration_hours, cat.code AS category_code, cat.name AS category_name,
                      (SELECT certificate_no FROM certificates ct WHERE ct.user_id = ce.user_id AND ct.course_id = ce.course_id AND ct.status = \'valid\') AS cert_no
                 FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id JOIN categories cat ON cat.id = c.category_id
                WHERE ce.user_id = ? ORDER BY ce.status, COALESCE(ce.last_activity, ce.enrolled_at) DESC', [$u['id']]);
$pageTitle = 'Mes formations';
$appArea = 'learner';
$activeApp = 'courses';
require APP_ROOT . '/includes/app-header.php';
?>
<section class="box reveal">
  <div class="box__head"><h2>Historique des formations</h2><a class="btn btn--sm btn--ghost" href="<?= e(url('academy.php')) ?>">S'inscrire à une formation</a></div>
  <div class="table-wrap"><table class="table">
    <thead><tr><th>Formation</th><th>Domaine</th><th>Inscription</th><th>Progression</th><th>Statut</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="6"><div class="empty"><strong>Vous n'êtes inscrit à aucune formation</strong><a href="<?= e(url('academy.php')) ?>">Parcourir le catalogue</a></div></td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><span class="mono cyan" style="font-size:.72rem"><?= e($r['code']) ?></span><br><strong><?= e($r['title']) ?></strong></td>
        <td><?= e($r['category_code']) ?> | <?= e(level_label($r['level'])) ?></td>
        <td><?= e(fmt_date($r['enrolled_at'], false)) ?></td>
        <td style="min-width:160px"><div class="progress"><span data-p="<?= (int) $r['progress'] ?>"></span></div><small class="mono dim"><?= (int) $r['progress'] ?> %</small></td>
        <td><?= $r['status'] === 'completed' ? '<span class="status-pill status-pill--on">Certifiée</span>' : '<span class="status-pill status-pill--warn">En cours</span>' ?></td>
        <td><div class="actions">
          <a class="btn btn--sm" href="<?= e(url('learner/learning.php', ['course' => $r['course_id']])) ?>"><?= $r['status'] === 'completed' ? 'Revoir' : 'Continuer' ?></a>
          <?php if ($r['cert_no']): ?><a class="btn btn--sm btn--gold" href="<?= e(url('learner/certificate.php', ['no' => $r['cert_no']])) ?>" data-no-transition>Certificat</a><?php endif; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</section>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
