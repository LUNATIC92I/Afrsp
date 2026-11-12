<?php
/**
 * AFRICA'SPY — moteur d'examen et de certification.
 */
declare(strict_types=1);

require_once APP_ROOT . '/includes/catalog.php';

const EXAM_GRACE_SECONDS = 120;

/** Démarre (ou reprend) une tentative d'examen. Renvoie l'id de tentative ou un message d'erreur. */
function start_exam_attempt(array $exam, int $userId, int $courseId)
{
    $st = exam_status($exam, $userId, $courseId);
    if ($st['open']) {
        return (int) $st['open']['id'];
    }
    if (!$st['can_start']) {
        return $st['reason'] ?? 'Examen indisponible.';
    }
    $ids = array_map('intval', array_column(q_all('SELECT id FROM questions WHERE exam_id = ? AND is_active = 1', [$exam['id']]), 'id'));
    shuffle($ids);
    $ids = array_slice($ids, 0, max(1, min((int) $exam['question_count'], count($ids))));
    q('INSERT INTO exam_attempts (exam_id, user_id, question_ids, started_at, expires_at, total_count) VALUES (?, ?, ?, UTC_TIMESTAMP(), UTC_TIMESTAMP() + INTERVAL ? MINUTE, ?)',
        [$exam['id'], $userId, json_encode($ids), max(1, (int) $exam['time_limit_min']), count($ids)]);
    $id = (int) db()->lastInsertId();
    audit('exam.start', 'exam_attempt', $id);
    return $id;
}

/** Questions d'une tentative, réponses mélangées de façon stable (sans révéler la bonne réponse). */
function attempt_questions(array $attempt): array
{
    $ids = array_map('intval', json_decode($attempt['question_ids'], true) ?: []);
    if (!$ids) {
        return [];
    }
    $in = implode(',', array_fill(0, count($ids), '?'));
    $qs = array_column(q_all("SELECT id, prompt FROM questions WHERE id IN ($in)", $ids), null, 'id');
    $ans = q_all("SELECT id, question_id, label FROM answers WHERE question_id IN ($in) ORDER BY sort_order, id", $ids);
    $by = [];
    foreach ($ans as $a) {
        $by[$a['question_id']][] = $a;
    }
    $out = [];
    foreach ($ids as $qid) {
        if (!isset($qs[$qid])) continue;
        $opts = $by[$qid] ?? [];
        usort($opts, static fn ($x, $y) => strcmp(md5($attempt['id'] . '-' . $x['id']), md5($attempt['id'] . '-' . $y['id'])));
        $out[] = $qs[$qid] + ['answers' => $opts];
    }
    return $out;
}

/**
 * Correction automatique et enregistrement. $given : [question_id => answer_id]
 * @return array{score:float,passed:bool,correct:int,total:int,certificate:?string,late:bool}
 */
function grade_attempt(array $attempt, array $exam, array $given): array
{
    $late = time() > strtotime($attempt['expires_at'] . ' UTC') + EXAM_GRACE_SECONDS;
    $ids = array_map('intval', json_decode($attempt['question_ids'], true) ?: []);
    $correctMap = [];
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        foreach (q_all("SELECT id, question_id, is_correct FROM answers WHERE question_id IN ($in)", $ids) as $a) {
            $correctMap[(int) $a['question_id']][(int) $a['id']] = (int) $a['is_correct'];
        }
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Verrou : empêche une double soumission concurrente
        $locked = q_one('SELECT submitted_at FROM exam_attempts WHERE id = ? FOR UPDATE', [$attempt['id']]);
        if (!$locked || $locked['submitted_at'] !== null) {
            $pdo->rollBack();
            throw new RuntimeException('Cette tentative a déjà été soumise.');
        }
        $correct = 0;
        foreach ($ids as $qid) {
            $aid = $late ? null : (isset($given[$qid]) ? (int) $given[$qid] : null);
            if ($aid !== null && !isset($correctMap[$qid][$aid])) {
                $aid = null; // réponse n'appartenant pas à la question : ignorée
            }
            $ok = $aid !== null && ($correctMap[$qid][$aid] ?? 0) === 1;
            $correct += $ok ? 1 : 0;
            q('INSERT INTO attempt_answers (attempt_id, question_id, answer_id, is_correct) VALUES (?,?,?,?)', [$attempt['id'], $qid, $aid, $ok ? 1 : 0]);
        }
        $total = max(1, count($ids));
        $score = round($correct * 100 / $total, 2);
        $passed = $score >= (int) $exam['pass_mark'];
        q('UPDATE exam_attempts SET submitted_at = UTC_TIMESTAMP(), score = ?, correct_count = ?, passed = ? WHERE id = ?', [$score, $correct, $passed ? 1 : 0, $attempt['id']]);
        $certNo = null;
        if ($passed) {
            q("UPDATE course_enrollments SET status = 'completed', completed_at = COALESCE(completed_at, UTC_TIMESTAMP()) WHERE user_id = ? AND course_id = ?", [$attempt['user_id'], $exam['course_id']]);
            $certNo = issue_certificate((int) $attempt['user_id'], (int) $exam['course_id'], (int) $attempt['id'], $score);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
    audit('exam.submit', 'exam_attempt', (int) $attempt['id'], "score=$score passed=" . ($passed ? 1 : 0));
    if ($certNo) {
        try {
            certificate_file($certNo); // génération immédiate du PDF
        } catch (Throwable $e) {
            error_log('[AFRICASPY cert] ' . $e->getMessage());
        }
    }
    return ['score' => $score, 'passed' => $passed, 'correct' => $correct, 'total' => $total, 'certificate' => $certNo, 'late' => $late];
}

/* ------------------------------------------------------------------
 *  Certificats
 * ----------------------------------------------------------------*/

function certificate_signature(string $no, int $userId, int $courseId, string $issuedAt): string
{
    return hash_hmac('sha256', $no . '|' . $userId . '|' . $courseId . '|' . $issuedAt, (string) config('app_key'));
}

/** Crée le certificat (idempotent : un certificat par apprenant et par formation). */
function issue_certificate(int $userId, int $courseId, ?int $attemptId, float $score): string
{
    $existing = q_one('SELECT certificate_no FROM certificates WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
    if ($existing) {
        return $existing['certificate_no'];
    }
    $u = q_one('SELECT first_name, last_name FROM users WHERE id = ?', [$userId]);
    $c = q_one('SELECT title FROM courses WHERE id = ?', [$courseId]);
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
        $rand = '';
        for ($i = 0; $i < 8; $i++) {
            $rand .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $no = 'AFSPY-' . gmdate('Y') . '-' . $rand;
    } while (q_val('SELECT 1 FROM certificates WHERE certificate_no = ?', [$no]));
    $issuedAt = gmdate('Y-m-d H:i:s');
    q('INSERT INTO certificates (certificate_no, verify_hash, user_id, course_id, attempt_id, holder_name, course_title, score, issued_at) VALUES (?,?,?,?,?,?,?,?,?)', [
        $no, certificate_signature($no, $userId, $courseId, $issuedAt), $userId, $courseId, $attemptId,
        trim($u['first_name'] . ' ' . mb_strtoupper($u['last_name'])), $c['title'], $score, $issuedAt,
    ]);
    audit('certificate.issue', 'certificate', (int) db()->lastInsertId(), $no);
    return $no;
}

/** Chemin du PDF du certificat (généré à la demande s'il n'existe pas). */
function certificate_file(string $no, bool $regenerate = false): string
{
    require_once APP_ROOT . '/includes/pdf.php';
    $row = q_one('SELECT c.*, co.code AS course_code FROM certificates c JOIN courses co ON co.id = c.course_id WHERE c.certificate_no = ?', [$no]);
    if (!$row) {
        throw new RuntimeException('Certificat introuvable.');
    }
    $file = $row['file_name'] ?: (preg_replace('/[^A-Z0-9\-]/', '', $no) . '-' . bin2hex(random_bytes(6)) . '.pdf');
    $path = APP_ROOT . '/certificates/generated/' . $file;
    if ($regenerate || !is_file($path)) {
        file_put_contents($path, render_certificate_pdf($row), LOCK_EX);
        @chmod($path, 0640);
        if ($row['file_name'] !== $file) {
            q('UPDATE certificates SET file_name = ? WHERE id = ?', [$file, $row['id']]);
        }
    }
    return $path;
}

/** Envoie un fichier au navigateur, avec prise en charge des requêtes partielles (vidéo). */
function stream_file(string $path, string $mime, string $downloadName, bool $inline = true): never
{
    if (!is_file($path)) {
        render_error_page(404, 'Fichier introuvable', 'La ressource demandée n\'est plus disponible.');
    }
    $size = filesize($path);
    $start = 0;
    $end = $size - 1;
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: ' . $mime);
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=3600');
    header('Accept-Ranges: bytes');
    header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . preg_replace('/[^A-Za-z0-9._\-]/', '_', $downloadName) . '"');
    if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $m)) {
        if ($m[1] === '' && $m[2] !== '') {
            $start = max(0, $size - (int) $m[2]);
        } else {
            $start = (int) $m[1];
            $end = $m[2] !== '' ? min((int) $m[2], $size - 1) : $end;
        }
        if ($start > $end || $start >= $size) {
            http_response_code(416);
            header("Content-Range: bytes */$size");
            exit;
        }
        http_response_code(206);
        header("Content-Range: bytes $start-$end/$size");
    }
    header('Content-Length: ' . ($end - $start + 1));
    $fp = fopen($path, 'rb');
    fseek($fp, $start);
    $left = $end - $start + 1;
    while ($left > 0 && !feof($fp)) {
        $chunk = fread($fp, min(8192 * 8, $left));
        echo $chunk;
        $left -= strlen($chunk);
        flush();
    }
    fclose($fp);
    exit;
}
