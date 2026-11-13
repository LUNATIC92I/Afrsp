<?php
/**
 * Applique les enrichissements pédagogiques à une base DÉJÀ installée (idempotent) :
 *   php database/enrich-apply.php
 * Ajoute les leçons d'approfondissement, les questions QCM, le glossaire et la bibliographie
 * aux formations existantes (repérées par leur code). Les éléments déjà présents (même titre
 * ou même énoncé) sont ignorés ; la progression des apprenants inscrits est recalculée.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

$enrich = require APP_ROOT . '/database/seed/enrichment.php';
$stats = ['lessons' => 0, 'questions' => 0, 'skipped' => 0];
$addLesson = static function (int $chapterId, array $l) use (&$stats): void {
    if (q_val('SELECT 1 FROM lessons WHERE chapter_id = ? AND title = ?', [$chapterId, $l['title']])) { $stats['skipped']++; return; }
    $pos = (int) q_val('SELECT COALESCE(MAX(sort_order),0)+1 FROM lessons WHERE chapter_id = ?', [$chapterId]);
    q('INSERT INTO lessons (chapter_id, title, type, content, duration_minutes, is_downloadable, sort_order) VALUES (?,?,?,?,?,1,?)',
        [$chapterId, $l['title'], $l['type'], $l['content'], $l['minutes'], $pos]);
    $stats['lessons']++;
};
db()->beginTransaction();
foreach ($enrich as $code => $e) {
    $course = q_one('SELECT id FROM courses WHERE code = ?', [$code]);
    if (!$course) { fwrite(STDERR, "Formation $code absente : ignorée\n"); continue; }
    $chapters = q_all('SELECT id FROM chapters WHERE course_id = ? ORDER BY sort_order, id', [$course['id']]);
    $exam = (int) q_val('SELECT id FROM exams WHERE course_id = ?', [$course['id']]);
    foreach ($e['chapters'] as $i => $add) {
        if (!isset($chapters[$i])) continue;
        $chId = (int) $chapters[$i]['id'];
        foreach ($add['lessons'] as $l) $addLesson($chId, $l);
        foreach ($add['quiz'] as [$prompt, $opts, $correct, $exp]) {
            if (!$exam || q_val('SELECT 1 FROM questions WHERE exam_id = ? AND prompt = ?', [$exam, $prompt])) { $stats['skipped']++; continue; }
            q('INSERT INTO questions (exam_id, chapter_id, prompt, explanation, is_active) VALUES (?,?,?,?,1)', [$exam, $chId, $prompt, $exp]);
            $qid = (int) db()->lastInsertId();
            foreach ($opts as $k => $label) q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $k === $correct ? 1 : 0, $k + 1]);
            $stats['questions']++;
        }
    }
    if ($chapters) foreach ($e['final'] as $l) $addLesson((int) end($chapters)['id'], $l);
    // Chapitre « Mise en situation » inséré avant le dernier chapitre (atelier)
    if (!empty($e['scenario']) && $chapters) {
        $sc = $e['scenario'];
        $scId = (int) q_val('SELECT id FROM chapters WHERE course_id = ? AND title = ?', [$course['id'], $sc['title']]);
        if (!$scId) {
            $last = q_one('SELECT id, sort_order FROM chapters WHERE course_id = ? ORDER BY sort_order DESC, id DESC LIMIT 1', [$course['id']]);
            q('UPDATE chapters SET sort_order = sort_order + 1 WHERE id = ?', [$last['id']]);
            q('INSERT INTO chapters (course_id, title, summary, sort_order) VALUES (?,?,?,?)', [$course['id'], $sc['title'], $sc['summary'], (int) $last['sort_order']]);
            $scId = (int) db()->lastInsertId();
            $stats['chapters'] = ($stats['chapters'] ?? 0) + 1;
        }
        foreach ($sc['lessons'] as $l) $addLesson($scId, $l);
        foreach ($sc['quiz'] as [$prompt, $opts, $correct, $exp]) {
            if (!$exam || q_val('SELECT 1 FROM questions WHERE exam_id = ? AND prompt = ?', [$exam, $prompt])) { $stats['skipped']++; continue; }
            q('INSERT INTO questions (exam_id, chapter_id, prompt, explanation, is_active) VALUES (?,?,?,?,1)', [$exam, $scId, $prompt, $exp]);
            $qid = (int) db()->lastInsertId();
            foreach ($opts as $k => $label) q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $k === $correct ? 1 : 0, $k + 1]);
            $stats['questions']++;
        }
    }
    // Examens plus complets, uniquement si les réglages d'origine n'ont pas été modifiés par un administrateur
    if ($exam) q('UPDATE exams SET question_count = 15, time_limit_min = 35 WHERE id = ? AND question_count IN (8, 10) AND time_limit_min IN (20, 25)', [$exam]);
}
db()->commit();
// Les nouvelles leçons modifient le pourcentage de progression des apprenants inscrits
foreach (q_all('SELECT user_id, course_id FROM course_enrollments') as $r) recompute_progress((int) $r['user_id'], (int) $r['course_id']);
printf("Chapitres ajoutés : %d | Leçons ajoutées : %d | Questions ajoutées : %d | Éléments déjà présents : %d\n", $stats['chapters'] ?? 0, $stats['lessons'], $stats['questions'], $stats['skipped']);
