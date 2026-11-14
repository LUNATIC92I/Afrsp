<?php
/**
 * Ajoute à une base DÉJÀ installée les domaines et formations du catalogue qui n'y figurent pas encore
 * (repérés par leur code), avec chapitres, leçons, examen, banque de questions et médias.
 * Idempotent : les éléments existants ne sont jamais modifiés ; les inscriptions et résultats sont conservés.
 *   php database/catalog-sync.php            # aperçu
 *   php database/catalog-sync.php --apply    # import
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/pdf.php';

$apply = in_array('--apply', $argv, true);
$catalog = require APP_ROOT . '/database/seed/catalog.php';
$newCats = array_values(array_filter($catalog['categories'], static fn ($c) => !q_val('SELECT 1 FROM categories WHERE code = ?', [$c['code']])));
$newCourses = array_values(array_filter($catalog['courses'], static fn ($c) => !q_val('SELECT 1 FROM courses WHERE code = ?', [$c['code']])));
printf("Domaines à ajouter : %s\nFormations à ajouter : %s\n",
    $newCats ? implode(', ', array_map(static fn ($c) => $c['code'] . ' (' . $c['name'] . ')', $newCats)) : 'aucun',
    $newCourses ? implode(', ', array_map(static fn ($c) => $c['code'], $newCourses)) : 'aucune');
if (!$apply) { echo "Aperçu uniquement : relancez avec --apply pour importer.\n"; exit; }
if (!$newCats && !$newCourses) { echo "Catalogue déjà à jour.\n"; exit; }

$catName = array_column($catalog['categories'], 'name', 'code');
db()->beginTransaction();
try {
    foreach ($newCats as $c) {
        q('INSERT INTO categories (code, slug, name, description, accent, sort_order) VALUES (?,?,?,?,?, (SELECT n FROM (SELECT COALESCE(MAX(sort_order),0)+1 n FROM categories) x))',
            [$c['code'], $c['slug'], $c['name'], $c['description'], $c['accent']]);
    }
    $nl = $nq = 0;
    foreach ($newCourses as $c) {
        $catId = (int) q_val('SELECT id FROM categories WHERE code = ?', [$c['category']]);
        q('INSERT INTO courses (category_id, code, slug, title, subtitle, summary, description, objectives, skills, prerequisites, level, duration_hours, price_xof, promo_price_xof, is_featured, status, sort_order)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,\'published\', (SELECT n FROM (SELECT COALESCE(MAX(sort_order),0)+1 n FROM courses) x))',
            [$catId, $c['code'], $c['slug'], $c['title'], $c['subtitle'], $c['summary'], $c['description'], $c['objectives'], $c['skills'], $c['prerequisites'],
             $c['level'], $c['hours'], $c['price'] ?? 900000, $c['promo'] ?? 170000, $c['featured'] ? 1 : 0]);
        $courseId = (int) db()->lastInsertId();
        // Médias : briefing vidéo (s'il a été généré) et support PDF
        $videoMid = $pdfMid = null;
        $video = 'seed-' . strtolower($c['code']) . '.mp4';
        if (is_file(APP_ROOT . '/uploads/videos/' . $video)) {
            q('INSERT INTO media (kind, title, original_name, stored_name, mime_type, size_bytes, attribution) VALUES (\'video\',?,?,?,\'video/mp4\',?,?)',
                ['Briefing vidéo : ' . $c['title'], $video, $video, filesize(APP_ROOT . '/uploads/videos/' . $video), 'Production AFRICA\'SPY (visuels originaux)']);
            $videoMid = (int) db()->lastInsertId();
        } else {
            fwrite(STDERR, "! vidéo manquante : uploads/videos/$video (python3 resources/tools/generate_videos.py)\n");
        }
        $pdf = 'seed-' . strtolower($c['code']) . '.pdf';
        $pdfPath = APP_ROOT . '/uploads/documents/' . $pdf;
        if (!is_file($pdfPath)) {
            $chs = array_map(static fn ($ch) => ['title' => $ch['title'], 'summary' => $ch['summary'], 'lessons' => $ch['lessons']], $c['chapters']);
            file_put_contents($pdfPath, render_course_support_pdf(array_merge($c, ['duration_hours' => $c['hours'], 'category_name' => $catName[$c['category']]]), $chs));
        }
        q('INSERT INTO media (kind, title, original_name, stored_name, mime_type, size_bytes) VALUES (\'document\',?,?,?,\'application/pdf\',?)',
            ['Support de cours : ' . $c['title'], 'AFRICASPY-' . $c['code'] . '-support.pdf', $pdf, filesize($pdfPath)]);
        $pdfMid = (int) db()->lastInsertId();
        q('INSERT INTO exams (course_id, title, instructions, pass_mark, max_attempts, question_count, time_limit_min, require_completion, is_active) VALUES (?,?,?,?,?,?,?,1,1)',
            [$courseId, 'Examen final : ' . $c['title'], "Questionnaire à choix multiples tiré au sort dans la banque de questions de la formation. Une seule bonne réponse par question. Le chronomètre démarre à l'ouverture de l'examen ; à expiration, les réponses saisies sont soumises automatiquement.",
             $c['exam']['pass'], $c['exam']['attempts'], $c['exam']['count'], $c['exam']['time']]);
        $examId = (int) db()->lastInsertId();
        foreach ($c['chapters'] as $ci => $ch) {
            q('INSERT INTO chapters (course_id, title, summary, sort_order) VALUES (?,?,?,?)', [$courseId, $ch['title'], $ch['summary'], $ci + 1]);
            $chId = (int) db()->lastInsertId();
            foreach ($ch['lessons'] as $li => $l) {
                q('INSERT INTO lessons (chapter_id, title, type, content, media_id, duration_minutes, is_downloadable, sort_order) VALUES (?,?,?,?,?,?,1,?)',
                    [$chId, $l['title'], $l['type'], $l['content'], $l['type'] === 'video' ? $videoMid : ($l['type'] === 'document' ? $pdfMid : null), $l['minutes'], $li + 1]);
                $nl++;
            }
            foreach ($ch['quiz'] as [$prompt, $opts, $correct, $exp]) {
                q('INSERT INTO questions (exam_id, chapter_id, prompt, explanation, is_active) VALUES (?,?,?,?,1)', [$examId, $chId, $prompt, $exp]);
                $qid = (int) db()->lastInsertId();
                foreach ($opts as $k => $label) {
                    q('INSERT INTO answers (question_id, label, is_correct, sort_order) VALUES (?,?,?,?)', [$qid, $label, $k === $correct ? 1 : 0, $k + 1]);
                }
                $nq++;
            }
        }
    }
    db()->commit();
    printf("Import terminé : %d domaine(s), %d formation(s), %d leçons, %d questions.\n", count($newCats), count($newCourses), $nl, $nq);
} catch (Throwable $e) {
    db()->rollBack();
    fwrite(STDERR, 'Échec, aucune modification : ' . $e->getMessage() . "\n");
    exit(1);
}
