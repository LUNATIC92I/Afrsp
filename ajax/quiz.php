<?php
/** AJAX : correction d'une évaluation formative de chapitre. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

if (!is_post()) json_response(['ok' => false, 'message' => 'Méthode non autorisée.'], 405);
verify_csrf();
$u = require_login();
rate_limit_or_fail('quiz:user', (string) $u['id'], 120, 600);
$ch = q_one('SELECT id, course_id FROM chapters WHERE id = ?', [input_int('chapter_id')]);
if (!$ch || (!is_enrolled((int) $u['id'], (int) $ch['course_id']) && !can('courses.manage'))) {
    json_response(['ok' => false, 'message' => 'Évaluation inaccessible.'], 403);
}
$given = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
$qs = q_all('SELECT q.id, q.explanation FROM questions q JOIN exams e ON e.id = q.exam_id WHERE e.course_id = ? AND q.chapter_id = ? AND q.is_active = 1', [$ch['course_id'], $ch['id']]);
$out = [];
$score = 0;
foreach ($qs as $q) {
    $correctId = (int) q_val('SELECT id FROM answers WHERE question_id = ? AND is_correct = 1 ORDER BY id LIMIT 1', [$q['id']]);
    $chosen = isset($given[$q['id']]) && ctype_digit((string) $given[$q['id']]) ? (int) $given[$q['id']] : null;
    $ok = $chosen === $correctId;
    $score += $ok ? 1 : 0;
    $out[] = ['question' => (int) $q['id'], 'chosen' => $chosen, 'correct' => $correctId, 'ok' => $ok, 'explanation' => (string) $q['explanation']];
}
$total = count($qs);
json_response(['ok' => true, 'score' => $score, 'total' => $total, 'results' => $out,
    'message' => $total ? "Résultat : $score / $total" . ($score === $total ? '. Parfait.' : '. Relisez les explications.') : 'Aucune question.']);
