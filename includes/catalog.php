<?php
/**
 * AFRICA'SPY — accès aux données pédagogiques (catalogue, progression, examens).
 */
declare(strict_types=1);

const COURSE_SELECT = 'SELECT c.*, cat.code AS category_code, cat.name AS category_name, cat.slug AS category_slug, cat.accent,
        m.stored_name AS cover_stored,
        (SELECT COUNT(*) FROM chapters ch WHERE ch.course_id = c.id) AS chapter_count,
        (SELECT COUNT(*) FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = c.id) AS lesson_count
   FROM courses c
   JOIN categories cat ON cat.id = c.category_id
   LEFT JOIN media m ON m.id = c.cover_media_id';

function categories_with_counts(): array
{
    return q_all("SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id AND c.status = 'published') AS course_count
                    FROM categories cat ORDER BY cat.sort_order, cat.code");
}

/**
 * Liste filtrée des formations publiées.
 * $f : category (slug), level, q (recherche), featured (bool), limit
 */
function find_courses(array $f = []): array
{
    $where = ["c.status = 'published'"];
    $p = [];
    if (!empty($f['category'])) {
        $where[] = 'cat.slug = ?';
        $p[] = $f['category'];
    }
    if (!empty($f['level']) && in_array($f['level'], ['fondamental', 'intermediaire', 'avance'], true)) {
        $where[] = 'c.level = ?';
        $p[] = $f['level'];
    }
    if (!empty($f['q'])) {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_substr($f['q'], 0, 80)) . '%';
        $where[] = '(c.title LIKE ? OR c.summary LIKE ? OR c.code LIKE ? OR EXISTS (SELECT 1 FROM chapters ch WHERE ch.course_id = c.id AND ch.title LIKE ?))';
        array_push($p, $like, $like, $like, $like);
    }
    if (!empty($f['featured'])) {
        $where[] = 'c.is_featured = 1';
    }
    $sql = COURSE_SELECT . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY cat.sort_order, c.sort_order, c.id';
    if (!empty($f['limit'])) {
        $sql .= ' LIMIT ' . (int) $f['limit'];
    }
    return attach_progress(q_all($sql, $p));
}

function find_course(string $slugOrId, bool $publishedOnly = true): ?array
{
    $col = ctype_digit($slugOrId) ? 'c.id' : 'c.slug';
    $row = q_one(COURSE_SELECT . " WHERE $col = ?" . ($publishedOnly ? " AND c.status = 'published'" : ''), [$slugOrId]);
    return $row ? (attach_progress([$row])[0]) : null;
}

/** Ajoute la progression de l'utilisateur connecté (null si non inscrit). */
function attach_progress(array $courses): array
{
    $u = current_user();
    if (!$u || !$courses) {
        return array_map(static fn ($c) => $c + ['enrolled' => false, 'progress' => null], $courses);
    }
    $ids = array_column($courses, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $rows = q_all("SELECT course_id, progress, status FROM course_enrollments WHERE user_id = ? AND course_id IN ($in)", array_merge([$u['id']], $ids));
    $map = array_column($rows, null, 'course_id');
    return array_map(static fn ($c) => $c + [
        'enrolled' => isset($map[$c['id']]),
        'progress' => isset($map[$c['id']]) ? (int) $map[$c['id']]['progress'] : null,
        'enroll_status' => $map[$c['id']]['status'] ?? null,
    ], $courses);
}

/** Chapitres + leçons d'une formation (avec état de complétion pour un utilisateur). */
function course_outline(int $courseId, ?int $userId = null): array
{
    $chapters = q_all('SELECT * FROM chapters WHERE course_id = ? ORDER BY sort_order, id', [$courseId]);
    $lessons = q_all('SELECT l.id, l.chapter_id, l.title, l.type, l.duration_minutes, l.sort_order, l.media_id, l.video_url, l.is_downloadable'
        . ($userId ? ', (SELECT 1 FROM lesson_progress lp WHERE lp.lesson_id = l.id AND lp.user_id = ' . (int) $userId . ') AS done' : ', NULL AS done')
        . ' FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ? ORDER BY ch.sort_order, l.sort_order, l.id', [$courseId]);
    $quizCount = array_column(q_all('SELECT q.chapter_id, COUNT(*) n FROM questions q JOIN exams e ON e.id = q.exam_id WHERE e.course_id = ? AND q.is_active = 1 AND q.chapter_id IS NOT NULL GROUP BY q.chapter_id', [$courseId]), 'n', 'chapter_id');
    $by = [];
    foreach ($lessons as $l) {
        $by[$l['chapter_id']][] = $l;
    }
    foreach ($chapters as &$ch) {
        $ch['lessons'] = $by[$ch['id']] ?? [];
        $ch['quiz_count'] = (int) ($quizCount[$ch['id']] ?? 0);
    }
    return $chapters;
}

function lesson_type_counts(int $courseId): array
{
    $rows = q_all('SELECT l.type, COUNT(*) n FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ? GROUP BY l.type', [$courseId]);
    return array_column($rows, 'n', 'type');
}

function is_enrolled(int $userId, int $courseId): bool
{
    return (bool) q_val('SELECT 1 FROM course_enrollments WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
}

/** Recalcule la progression (% de leçons terminées) et la persiste. */
function recompute_progress(int $userId, int $courseId): int
{
    $total = (int) q_val('SELECT COUNT(*) FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ?', [$courseId]);
    $done = (int) q_val('SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON l.id = lp.lesson_id JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ? AND lp.user_id = ?', [$courseId, $userId]);
    $pct = $total > 0 ? (int) floor($done * 100 / $total) : 0;
    q('UPDATE course_enrollments SET progress = ?, last_activity = UTC_TIMESTAMP() WHERE user_id = ? AND course_id = ?', [$pct, $userId, $courseId]);
    return $pct;
}

function course_exam(int $courseId): ?array
{
    return q_one('SELECT e.*, (SELECT COUNT(*) FROM questions q WHERE q.exam_id = e.id AND q.is_active = 1) AS bank_size FROM exams e WHERE e.course_id = ?', [$courseId]);
}

/** Statut de l'examen pour un apprenant : tentatives, meilleur score, éligibilité. */
function exam_status(array $exam, int $userId, int $courseId): array
{
    $attempts = q_all('SELECT * FROM exam_attempts WHERE exam_id = ? AND user_id = ? ORDER BY started_at DESC', [$exam['id'], $userId]);
    $submitted = array_values(array_filter($attempts, static fn ($a) => $a['submitted_at'] !== null));
    $open = null;
    foreach ($attempts as $a) {
        if ($a['submitted_at'] === null && strtotime($a['expires_at'] . ' UTC') > time()) {
            $open = $a;
            break;
        }
    }
    $passed = (bool) array_filter($submitted, static fn ($a) => (int) $a['passed'] === 1);
    $used = count($attempts) - ($open ? 1 : 0);
    $progress = (int) q_val('SELECT progress FROM course_enrollments WHERE user_id = ? AND course_id = ?', [$userId, $courseId]);
    $reason = null;
    if (!$exam['is_active']) {
        $reason = 'L\'examen est temporairement désactivé.';
    } elseif ($passed) {
        $reason = 'Examen déjà réussi.';
    } elseif (!$open && $used >= (int) $exam['max_attempts']) {
        $reason = 'Nombre maximal de tentatives atteint. Contactez l\'administration pour une réouverture.';
    } elseif ($exam['require_completion'] && $progress < 100) {
        $reason = 'Terminez toutes les leçons de la formation pour débloquer l\'examen final (' . $progress . ' %).';
    } elseif ((int) $exam['bank_size'] === 0) {
        $reason = 'La banque de questions est vide.';
    }
    return [
        'attempts' => $submitted, 'open' => $open, 'passed' => $passed, 'used' => $used,
        'remaining' => max(0, (int) $exam['max_attempts'] - $used), 'can_start' => $reason === null || $open !== null, 'reason' => $reason,
        'best' => $submitted ? max(array_map(static fn ($a) => (float) $a['score'], $submitted)) : null,
    ];
}

/** Indicateurs institutionnels réels (aucune donnée inventée). */
function platform_metrics(): array
{
    return [
        'courses'      => (int) q_val("SELECT COUNT(*) FROM courses WHERE status = 'published'"),
        'domains'      => (int) q_val("SELECT COUNT(DISTINCT category_id) FROM courses WHERE status = 'published'"),
        'chapters'     => (int) q_val("SELECT COUNT(*) FROM chapters ch JOIN courses c ON c.id = ch.course_id WHERE c.status = 'published'"),
        'lessons'      => (int) q_val("SELECT COUNT(*) FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id JOIN courses c ON c.id = ch.course_id WHERE c.status = 'published'"),
        'questions'    => (int) q_val('SELECT COUNT(*) FROM questions WHERE is_active = 1'),
        'hours'        => (int) q_val("SELECT COALESCE(SUM(duration_hours),0) FROM courses WHERE status = 'published'"),
        'learners'     => (int) q_val("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.slug = 'learner'"),
        'enrollments'  => (int) q_val('SELECT COUNT(*) FROM course_enrollments'),
        'certificates' => (int) q_val("SELECT COUNT(*) FROM certificates WHERE status = 'valid'"),
    ];
}

/* ------------------------------------------------------------------
 *  Rendu : carte holographique de formation
 * ----------------------------------------------------------------*/
function course_card(array $c, string $extraClass = ''): string
{
    $href = url('course.php', ['slug' => $c['slug']]);
    $img = !empty($c['cover_stored']) ? url('uploads/courses/' . rawurlencode($c['cover_stored'])) : url('assets/images/covers/' . $c['category_code'] . '.webp');
    $prog = '';
    if ($c['enrolled'] ?? false) {
        $prog = '<div class="progress" role="progressbar" aria-valuenow="' . (int) $c['progress'] . '" aria-valuemin="0" aria-valuemax="100" aria-label="Progression"><span data-p="' . (int) $c['progress'] . '"></span></div>'
            . '<div class="holo-card__meta"><span>Progression <b>' . (int) $c['progress'] . ' %</b></span></div>';
    }
    return '<a class="holo-card ' . e($extraClass) . '" href="' . e($href) . '" style="--accent:' . e($c['accent']) . '" data-tilt>'
        . '<span class="holo-card__img"><img src="' . e($img) . '" alt="" loading="lazy" decoding="async" width="960" height="600"></span>'
        . '<span class="holo-card__top"><span class="holo-card__code">' . e($c['code']) . '</span><span>' . e(level_label($c['level'])) . '</span></span>'
        . '<span class="holo-card__body"><h3 class="holo-card__title">' . e($c['title']) . '</h3>'
        . '<p class="holo-card__desc">' . e(str_limit($c['summary'], 210)) . '</p>'
        . '<span class="holo-card__meta"><span>Durée <b>' . (int) $c['duration_hours'] . ' h</b></span><span>Chapitres <b>' . (int) $c['chapter_count'] . '</b></span><span>Domaine <b>' . e($c['category_code']) . '</b></span></span>'
        . $prog
        . '<span class="holo-card__cta">' . (($c['enrolled'] ?? false) ? 'Reprendre la formation →' : 'Consulter la formation →') . '</span></span></a>';
}

/** Résultats du catalogue groupés par domaine (rendu partagé page + AJAX). */
function render_catalog(array $courses, array $categories): string
{
    if (!$courses) {
        return '<div class="empty"><strong>Aucune formation ne correspond</strong>Modifiez vos filtres ou votre recherche.</div>';
    }
    $by = [];
    foreach ($courses as $c) {
        $by[$c['category_id']][] = $c;
    }
    $html = '';
    foreach ($categories as $cat) {
        if (empty($by[$cat['id']])) continue;
        $html .= '<section class="domain" style="--accent:' . e($cat['accent']) . '" aria-labelledby="dom-' . e($cat['code']) . '">'
            . '<header class="domain__head reveal"><span class="domain__code" aria-hidden="true">' . e($cat['code']) . '</span>'
            . '<h2 id="dom-' . e($cat['code']) . '">Module ' . e($cat['code']) . ' · ' . e($cat['name']) . '</h2><p>' . e($cat['description']) . '</p></header>'
            . '<div class="modules" data-stagger="90">';
        foreach ($by[$cat['id']] as $c) {
            $html .= course_card($c);
        }
        if ($cat['code'] === 'E') {
            $html .= '<a class="holo-card" href="' . e(url('tools.php')) . '" style="--accent:' . e($cat['accent']) . '" data-tilt>'
                . '<span class="holo-card__img"><img src="' . e(url('assets/images/cyber-lab.webp')) . '" alt="" loading="lazy"></span>'
                . '<span class="holo-card__top"><span class="holo-card__code">AFS-E-GAL</span><span>Galerie</span></span>'
                . '<span class="holo-card__body"><h3 class="holo-card__title">Galerie des outils du renseignement</h3>'
                . '<p class="holo-card__desc">Fiches descriptives des outils logiciels et des équipements professionnels : fonction, usages légitimes, limites légales.</p>'
                . '<span class="holo-card__cta">Ouvrir la galerie →</span></span></a>';
        }
        $html .= '</div></section>';
    }
    return $html;
}

function catalog_label(int $n): string
{
    return $n === 0 ? 'Aucune formation' : ($n === 1 ? '1 formation' : $n . ' formations');
}
