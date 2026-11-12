<?php
/**
 * Téléchargement protégé des médias (PDF, vidéos) : réservé aux apprenants
 * inscrits à une formation utilisant le média, ou au personnel habilité.
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require APP_ROOT . '/includes/learning.php';

$u = require_login();
$media = q_one('SELECT * FROM media WHERE id = ?', [input_int('media')]);
if (!$media || $media['kind'] === 'image') {
    render_error_page(404, 'Ressource introuvable', 'Ce document n\'existe pas.');
}
$allowed = can('media.manage') || can('courses.manage');
if (!$allowed) {
    $allowed = (bool) q_val(
        'SELECT 1 FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id JOIN courses c ON c.id = ch.course_id
           JOIN course_enrollments ce ON ce.course_id = c.id AND ce.user_id = ?
          WHERE l.media_id = ? AND c.status = \'published\' AND (l.type = \'video\' OR l.is_downloadable = 1) LIMIT 1',
        [$u['id'], $media['id']]
    );
}
if (!$allowed) {
    audit('download.denied', 'media', (int) $media['id']);
    render_error_page(403, 'Accès refusé', 'Inscrivez-vous à la formation correspondante pour accéder à cette ressource.');
}
$inline = $media['kind'] === 'video' || input('dl') !== '1';
stream_file(media_path($media), $media['mime_type'], $media['original_name'], $inline);
