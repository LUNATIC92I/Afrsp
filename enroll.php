<?php
/** Inscription d'un apprenant à une formation (POST + CSRF). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/payments.php';

if (!is_post()) {
    redirect('academy.php');
}
verify_csrf();
$u = require_login();
$course = find_course((string) input_int('course_id'));
if (!$course) {
    flash('error', 'Formation introuvable ou non publiée.');
    redirect('academy.php');
}
if (!is_enrolled((int) $u['id'], (int) $course['id']) && !course_is_free($course) && !can('courses.manage')) {
    // Formation payante : l'accès s'ouvre uniquement après paiement confirmé
    redirect('checkout.php', ['course' => $course['slug']]);
}
if (!is_enrolled((int) $u['id'], (int) $course['id'])) {
    q('INSERT IGNORE INTO course_enrollments (user_id, course_id, last_activity) VALUES (?, ?, UTC_TIMESTAMP())', [$u['id'], $course['id']]);
    recompute_progress((int) $u['id'], (int) $course['id']);
    flash('success', 'Inscription confirmée à « ' . $course['title'] . ' ». Bonne formation.');
}
redirect('learner/learning.php', ['course' => $course['id']]);
