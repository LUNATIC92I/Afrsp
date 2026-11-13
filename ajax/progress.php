<?php
/** AJAX : marque une leçon comme terminée et renvoie la progression. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

if (!is_post()) json_response(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
verify_csrf();
$u = require_login();
rate_limit_or_fail('progress:user', (string) $u['id'], 600, 600);
$lesson = q_one('SELECT l.id, l.chapter_id, ch.course_id FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE l.id = ?', [input_int('lesson_id')]);
if (!$lesson || !is_enrolled((int) $u['id'], (int) $lesson['course_id'])) {
    json_response(['ok' => false, 'message' => 'Leçon inaccessible.'], 403);
}
q('INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed_at) VALUES (?, ?, UTC_TIMESTAMP())', [$u['id'], $lesson['id']]);
$pct = recompute_progress((int) $u['id'], (int) $lesson['course_id']);
$chapterLeft = (int) q_val('SELECT COUNT(*) FROM lessons l WHERE l.chapter_id = ? AND NOT EXISTS (SELECT 1 FROM lesson_progress lp WHERE lp.lesson_id = l.id AND lp.user_id = ?)', [$lesson['chapter_id'], $u['id']]);
json_response(['ok' => true, 'progress' => $pct, 'chapter_complete' => $chapterLeft === 0, 'course_complete' => $pct >= 100,
    'message' => $pct >= 100 ? 'Toutes les leçons sont terminées : l\'examen final est débloqué.' : ($chapterLeft === 0 ? 'Chapitre terminé. Validez vos acquis avec le QCM.' : 'Leçon enregistrée.')]);
