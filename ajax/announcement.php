<?php
/** AJAX — marque une annonce comme lue. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
if (!is_post()) json_response(['ok' => false], 405);
verify_csrf();
$u = require_login();
$id = input_int('id');
if ($id && q_val('SELECT 1 FROM announcements WHERE id = ? AND is_published = 1', [$id])) {
    q('INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)', [$id, $u['id']]);
}
json_response(['ok' => true]);
