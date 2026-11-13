<?php
/** Administration : correction des travaux rendus (exercices, études de cas, simulations). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
$me = require_permission('submissions.review');

if (is_post()) {
    verify_csrf();
    $s = q_one('SELECT s.*, u.email, u.first_name, l.title, ch.course_id FROM submissions s JOIN users u ON u.id = s.user_id JOIN lessons l ON l.id = s.lesson_id JOIN chapters ch ON ch.id = l.chapter_id WHERE s.id = ?', [input_int('id')]);
    $decision = input('decision');
    $feedback = trim((string) ($_POST['feedback'] ?? ''));
    $grade = input('grade');
    if (!$s) {
        flash('error', 'Travail introuvable.');
    } elseif (!in_array($decision, ['reviewed', 'revision'], true)) {
        flash('error', 'Décision invalide.');
    } elseif (mb_strlen($feedback) < 20 || mb_strlen($feedback) > 5000) {
        flash('error', 'Le commentaire doit contenir entre 20 et 5 000 caractères : il guide l\'apprenant.');
    } elseif ($decision === 'reviewed' && (!ctype_digit($grade) || (int) $grade > 20)) {
        flash('error', 'Indiquez une note entière entre 0 et 20.');
    } else {
        q('UPDATE submissions SET status = ?, grade = ?, feedback = ?, reviewer_id = ?, reviewed_at = UTC_TIMESTAMP() WHERE id = ?',
            [$decision, $decision === 'reviewed' ? (int) $grade : null, $feedback, $me['id'], $s['id']]);
        audit('submission.' . $decision, 'submission', (int) $s['id'], $s['title'] . ($decision === 'reviewed' ? ' | ' . $grade . '/20' : ''));
        send_mail($s['email'], "AFRICA'SPY : " . ($decision === 'reviewed' ? 'Votre travail a été corrigé' : 'Reprise demandée sur votre travail'),
            "Bonjour {$s['first_name']},\n\n" . ($decision === 'reviewed' ? "Votre travail « {$s['title']} » a été corrigé : {$grade}/20." : "Le formateur vous demande de reprendre votre travail « {$s['title']} ».")
            . "\n\nCommentaire :\n$feedback\n\nConsulter : " . absolute_url('learner/learning.php', ['course' => $s['course_id'], 'lesson' => $s['lesson_id']]) . "\n\nAFRICA'SPY Intelligence Academy");
        flash('success', $decision === 'reviewed' ? 'Correction enregistrée et apprenant notifié.' : 'Reprise demandée ; l\'apprenant a été notifié.');
    }
    back(array_filter(['status' => input('f_status') ?: null]));
}

$appArea = 'admin';
$activeApp = 'submissions';
if ($id = input_int('id')) {
    $s = q_one('SELECT s.*, u.first_name, u.last_name, u.email, l.title AS lesson_title, l.type, l.content AS lesson_content, c.code, c.title AS course_title, r.first_name AS rf, r.last_name AS rl
                  FROM submissions s JOIN users u ON u.id = s.user_id JOIN lessons l ON l.id = s.lesson_id JOIN chapters ch ON ch.id = l.chapter_id JOIN courses c ON c.id = ch.course_id
                  LEFT JOIN users r ON r.id = s.reviewer_id WHERE s.id = ?', [$id]);
    if (!$s) render_error_page(404, 'Travail introuvable', 'Ce dépôt n\'existe pas.');
    $pageTitle = 'Correction';
    $crumb = $s['code'] . ' | ' . $s['lesson_title'];
    require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <section class="box reveal">
    <div class="box__head"><h2>Travail de <?= e($s['first_name'] . ' ' . $s['last_name']) ?></h2><span class="mono">v<?= (int) $s['version'] ?> | <?= e(fmt_datetime($s['submitted_at'])) ?></span></div>
    <div class="box__body"><div class="prose" style="white-space:pre-line"><?= e($s['content']) ?></div>
      <p class="mono dim mt-2" style="font-size:.74rem"><?= mb_strlen($s['content']) ?> caractères</p></div>
  </section>
  <div class="stack">
    <form class="box reveal" method="post" novalidate>
      <div class="box__head"><h2>Évaluation</h2><?= $s['status'] === 'reviewed' ? '<span class="status-pill status-pill--on">Corrigé par ' . e($s['rf'] . ' ' . $s['rl']) . '</span>' : '' ?></div>
      <div class="box__body">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
        <?= fld('grade', 'Note sur 20 (pour une correction définitive)', $s['grade'] !== null ? (string) $s['grade'] : '', 'number', ['min' => 0, 'max' => 20]) ?>
        <?= fld('feedback', 'Commentaire à l\'apprenant (points forts, axes de progrès, référence au barème)', (string) $s['feedback'], 'textarea', ['rows' => 8, 'required' => true, 'maxlength' => 5000]) ?>
        <div class="flex">
          <button class="btn btn--primary" type="submit" name="decision" value="reviewed">Valider la correction</button>
          <button class="btn btn--gold" type="submit" name="decision" value="revision">Demander une reprise</button>
        </div>
      </div>
    </form>
    <details class="box reveal"><summary class="box__head" style="cursor:pointer"><h2>Consignes et barème de l'exercice</h2></summary><div class="box__body prose"><?= sanitize_html($s['lesson_content']) ?></div></details>
  </div>
</div>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}
$status = input('status', 'submitted');
$where = $status !== 'all' && in_array($status, ['submitted', 'reviewed', 'revision'], true) ? 's.status = ?' : '1=1';
$par = $where === '1=1' ? [] : [$status];
$pg = paginate((int) q_val("SELECT COUNT(*) FROM submissions s WHERE $where", $par), 30);
$rows = q_all("SELECT s.id, s.status, s.grade, s.version, s.submitted_at, s.reviewed_at, u.first_name, u.last_name, l.title, l.type, c.code
                 FROM submissions s JOIN users u ON u.id = s.user_id JOIN lessons l ON l.id = s.lesson_id JOIN chapters ch ON ch.id = l.chapter_id JOIN courses c ON c.id = ch.course_id
                WHERE $where ORDER BY (s.status = 'submitted') DESC, s.submitted_at ASC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $par);
$counts = array_column(q_all('SELECT status, COUNT(*) n FROM submissions GROUP BY status'), 'n', 'status');
$avg = q_val("SELECT ROUND(AVG(grade),1) FROM submissions WHERE status = 'reviewed'");
$pageTitle = 'Travaux à corriger';
require APP_ROOT . '/includes/app-header.php'; ?>
<section class="stats">
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--gold)"></i>À corriger</div><div class="stat__value"><?= (int) ($counts['submitted'] ?? 0) ?></div><div class="stat__hint">Les plus anciens en premier</div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--red)"></i>Reprises demandées</div><div class="stat__value"><?= (int) ($counts['revision'] ?? 0) ?></div></div>
  <div class="stat"><div class="stat__label"><i class="dot" style="color:var(--green)"></i>Corrigés</div><div class="stat__value"><?= (int) ($counts['reviewed'] ?? 0) ?></div><div class="stat__hint">Note moyenne : <?= $avg !== null ? e((string) $avg) . ' / 20' : '-' ?></div></div>
</section>
<form class="toolbar reveal" method="get"><select class="select" name="status" data-autosubmit><?php foreach (['submitted' => 'À corriger', 'revision' => 'Reprises demandées', 'reviewed' => 'Corrigés', 'all' => 'Tous'] as $k => $l): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></form>
<section class="box reveal"><div class="table-wrap"><table class="table">
  <thead><tr><th>Déposé le</th><th>Apprenant</th><th>Formation</th><th>Travail</th><th>Statut</th><th class="num">Note</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="dim">Aucun travail dans cette catégorie.</td></tr><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <tr><td><?= e(fmt_datetime($r['submitted_at'])) ?></td><td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td><td><?= e($r['code']) ?></td><td><?= e($r['title']) ?><br><small class="dim"><?= e(lesson_type_label($r['type'])) ?> | v<?= (int) $r['version'] ?></small></td>
      <td><?= ['submitted' => '<span class="status-pill status-pill--warn">À corriger</span>', 'revision' => '<span class="status-pill status-pill--bad">Reprise</span>', 'reviewed' => '<span class="status-pill status-pill--on">Corrigé</span>'][$r['status']] ?></td>
      <td class="num"><?= $r['grade'] !== null ? (int) $r['grade'] . '/20' : '-' ?></td>
      <td><a class="btn btn--sm <?= $r['status'] === 'submitted' ? 'btn--primary' : '' ?>" href="<?= e(url('admin/submissions.php', ['id' => $r['id']])) ?>"><?= $r['status'] === 'submitted' ? 'Corriger' : 'Ouvrir' ?></a></td></tr>
  <?php endforeach; ?>
  </tbody></table></div></section>
<?= pagination_links($pg, ['status' => $status]) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
