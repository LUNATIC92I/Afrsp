<?php
/** AJAX — dépôt (ou nouveau dépôt après demande de reprise) d'un travail sur un exercice ou une étude de cas. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

if (!is_post()) json_response(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
verify_csrf();
$u = require_login();
rate_limit_or_fail('submission:user', (string) $u['id'], 30, 3600, 'Trop de dépôts en peu de temps. Réessayez plus tard.');
$lesson = q_one("SELECT l.id, l.title, l.type, ch.course_id FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE l.id = ? AND l.type IN ('exercise','case_study')", [input_int('lesson_id')]);
if (!$lesson || !is_enrolled((int) $u['id'], (int) $lesson['course_id'])) {
    json_response(['ok' => false, 'message' => 'Ce travail ne peut pas être déposé.'], 403);
}
$content = trim((string) ($_POST['content'] ?? ''));
$len = mb_strlen($content);
if ($len < 200) {
    json_response(['ok' => false, 'message' => 'Votre travail est trop court (200 caractères minimum, actuellement ' . $len . ').'], 422);
}
if ($len > 30000) {
    json_response(['ok' => false, 'message' => 'Votre travail dépasse 30 000 caractères.'], 422);
}
$existing = q_one('SELECT id, status, version FROM submissions WHERE user_id = ? AND lesson_id = ?', [$u['id'], $lesson['id']]);
if ($existing && $existing['status'] === 'reviewed') {
    json_response(['ok' => false, 'message' => 'Ce travail a déjà été corrigé ; il ne peut plus être modifié.'], 409);
}
if ($existing) {
    q("UPDATE submissions SET content = ?, status = 'submitted', version = version + 1, submitted_at = UTC_TIMESTAMP() WHERE id = ?", [$content, $existing['id']]);
    $id = (int) $existing['id'];
} else {
    q('INSERT INTO submissions (user_id, lesson_id, content, submitted_at) VALUES (?,?,?,UTC_TIMESTAMP())', [$u['id'], $lesson['id'], $content]);
    $id = (int) db()->lastInsertId();
}
// Le dépôt vaut validation de la leçon
q('INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed_at) VALUES (?, ?, UTC_TIMESTAMP())', [$u['id'], $lesson['id']]);
$pct = recompute_progress((int) $u['id'], (int) $lesson['course_id']);
audit('submission.submit', 'submission', $id, $lesson['title']);
json_response(['ok' => true, 'progress' => $pct, 'message' => 'Travail déposé. Un formateur le corrigera ; vous serez notifié par e-mail. Vous pouvez le modifier tant qu\'il n\'est pas corrigé.']);
