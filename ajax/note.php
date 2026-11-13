<?php
/** AJAX : enregistre les notes personnelles d'un apprenant sur une leçon (sauvegarde automatique). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

if (!is_post()) json_response(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
verify_csrf();
$u = require_login();
rate_limit_or_fail('note:user', (string) $u['id'], 240, 600);
$lesson = q_one('SELECT l.id, ch.course_id FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE l.id = ?', [input_int('lesson_id')]);
if (!$lesson || !is_enrolled((int) $u['id'], (int) $lesson['course_id'])) {
    json_response(['ok' => false, 'message' => 'Leçon inaccessible.'], 403);
}
$content = (string) ($_POST['content'] ?? '');
if (mb_strlen($content) > 20000) {
    json_response(['ok' => false, 'message' => 'Notes trop longues (20 000 caractères maximum).'], 422);
}
if (trim($content) === '') {
    q('DELETE FROM lesson_notes WHERE user_id = ? AND lesson_id = ?', [$u['id'], $lesson['id']]);
} else {
    q('INSERT INTO lesson_notes (user_id, lesson_id, content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE content = VALUES(content)', [$u['id'], $lesson['id'], $content]);
}
json_response(['ok' => true, 'saved_at' => gmdate('H:i:s') . ' UTC']);
