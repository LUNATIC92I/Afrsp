<?php
/** AJAX : catalogue filtré (GET, lecture seule). */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/catalog.php';

rate_limit_or_fail('catalog:ip', client_ip(), 300, 600);
$categories = categories_with_counts();
$domaine = input('domaine');
if ($domaine !== '' && !in_array($domaine, array_column($categories, 'slug'), true)) {
    $domaine = '';
}
$courses = find_courses(['category' => $domaine, 'level' => input('niveau'), 'q' => input('q')]);
json_response(['ok' => true, 'count' => count($courses), 'label' => catalog_label(count($courses)), 'html' => render_catalog($courses, $categories)]);
