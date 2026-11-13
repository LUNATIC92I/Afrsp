<?php
/**
 * AFRICA'SPY — construit database/africaspy.sql (schéma + données initiales)
 * à partir de database/schema.sql et du catalogue database/seed/catalog.php.
 * Génère également les supports PDF de chaque formation (uploads/documents/seed-*.pdf).
 *
 * Usage (CLI uniquement) : php database/build-sql.php [--no-pdf]
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/pdf.php';

$catalog = require APP_ROOT . '/database/seed/catalog.php';
$withPdf = !in_array('--no-pdf', $argv, true);

function sqlv($v): string
{
    if ($v === null) return 'NULL';
    if (is_bool($v)) return $v ? '1' : '0';
    if (is_int($v) || is_float($v)) return (string) $v;
    return "'" . strtr((string) $v, ['\\' => '\\\\', "'" => "''", "\0" => '\\0', "\r" => '\\r', "\n" => '\\n', "\x1a" => '\\Z']) . "'";
}

function ins(string $table, array $rows): string
{
    if (!$rows) return '';
    $cols = array_keys($rows[0]);
    $out = '';
    foreach (array_chunk($rows, 50) as $chunk) {
        $vals = array_map(static fn ($r) => '(' . implode(',', array_map('sqlv', array_values($r))) . ')', $chunk);
        $out .= 'INSERT INTO `' . $table . '` (`' . implode('`,`', $cols) . "`) VALUES\n" . implode(",\n", $vals) . ";\n";
    }
    return $out . "\n";
}

// --------------------------------------------------------------------- RBAC
$roles = [
    ['id' => 1, 'slug' => 'admin', 'name' => 'Administrateur', 'description' => 'Accès complet à la plateforme et aux paramètres.'],
    ['id' => 2, 'slug' => 'instructor', 'name' => 'Responsable pédagogique', 'description' => 'Gestion des formations, examens, médias et annonces.'],
    ['id' => 3, 'slug' => 'learner', 'name' => 'Apprenant', 'description' => 'Accès à l\'espace apprenant.'],
];
$perms = [
    'admin.access' => 'Accéder à l\'espace d\'administration',
    'stats.view' => 'Consulter les statistiques',
    'users.manage' => 'Gérer les utilisateurs et leurs rôles',
    'courses.manage' => 'Gérer catégories, formations, chapitres et leçons',
    'exams.manage' => 'Gérer les examens et la banque de questions',
    'results.view' => 'Consulter les résultats d\'examens',
    'certificates.manage' => 'Gérer (révoquer, régénérer) les certificats',
    'media.manage' => 'Gérer la médiathèque',
    'announcements.manage' => 'Gérer les annonces',
    'settings.manage' => 'Modifier les paramètres institutionnels',
    'audit.view' => 'Consulter le journal d\'audit',
    'messages.view' => 'Consulter les messages de contact',
    'payments.manage' => 'Gérer les paiements et valider les règlements',
    'submissions.review' => 'Corriger les travaux des apprenants',
];
$permRows = [];
$i = 0;
foreach ($perms as $slug => $label) {
    $permRows[] = ['id' => ++$i, 'slug' => $slug, 'label' => $label];
}
$rp = [];
foreach ($permRows as $p) {
    $rp[] = ['role_id' => 1, 'permission_id' => $p['id']];
    if (in_array($p['slug'], ['admin.access', 'stats.view', 'courses.manage', 'exams.manage', 'results.view', 'media.manage', 'announcements.manage', 'submissions.review'], true)) {
        $rp[] = ['role_id' => 2, 'permission_id' => $p['id']];
    }
}

// --------------------------------------------------------------- Catalogue
$cats = [];
foreach ($catalog['categories'] as $k => $c) {
    $cats[$c['code']] = ['id' => $k + 1, 'code' => $c['code'], 'slug' => $c['slug'], 'name' => $c['name'], 'description' => $c['description'], 'accent' => $c['accent'], 'sort_order' => $k + 1];
}
$courses = $chapters = $lessons = $exams = $questions = $answers = $media = [];
$cid = $chid = $lid = $qid = $aid = $mid = 0;
@mkdir(APP_ROOT . '/uploads/documents', 0775, true);

foreach ($catalog['courses'] as $sort => $c) {
    $cid++;
    $courses[] = [
        'id' => $cid, 'category_id' => $cats[$c['category']]['id'], 'code' => $c['code'], 'slug' => $c['slug'], 'title' => $c['title'],
        'subtitle' => $c['subtitle'], 'summary' => $c['summary'], 'description' => $c['description'], 'objectives' => $c['objectives'],
        'skills' => $c['skills'], 'prerequisites' => $c['prerequisites'], 'level' => $c['level'], 'duration_hours' => $c['hours'],
        'price_xof' => $c['price'] ?? 900000, 'promo_price_xof' => $c['promo'] ?? 170000,
        'is_featured' => $c['featured'] ? 1 : 0, 'status' => 'published', 'sort_order' => $sort + 1,
    ];
    // Médias de la formation : vidéo de briefing + support PDF
    $videoFile = 'seed-' . strtolower($c['code']) . '.mp4';
    $videoMid = null;
    if (is_file(APP_ROOT . '/uploads/videos/' . $videoFile)) {
        $videoMid = ++$mid;
        $media[] = ['id' => $mid, 'kind' => 'video', 'title' => 'Briefing vidéo — ' . $c['title'], 'original_name' => $videoFile, 'stored_name' => $videoFile,
            'mime_type' => 'video/mp4', 'size_bytes' => filesize(APP_ROOT . '/uploads/videos/' . $videoFile), 'attribution' => 'Production AFRICA\'SPY (visuels originaux)'];
    } else {
        fwrite(STDERR, "! vidéo manquante : $videoFile\n");
    }
    $pdfFile = 'seed-' . strtolower($c['code']) . '.pdf';
    $pdfPath = APP_ROOT . '/uploads/documents/' . $pdfFile;
    if ($withPdf) {
        $chs = array_map(static fn ($ch) => ['title' => $ch['title'], 'summary' => $ch['summary'], 'lessons' => $ch['lessons']], $c['chapters']);
        file_put_contents($pdfPath, render_course_support_pdf(array_merge($c, ['duration_hours' => $c['hours'], 'category_name' => $cats[$c['category']]['name']]), $chs));
        fwrite(STDOUT, "PDF  $pdfFile " . round(filesize($pdfPath) / 1024) . " Ko\n");
    }
    $pdfMid = ++$mid;
    $media[] = ['id' => $mid, 'kind' => 'document', 'title' => 'Support de cours — ' . $c['title'], 'original_name' => 'AFRICASPY-' . $c['code'] . '-support.pdf', 'stored_name' => $pdfFile,
        'mime_type' => 'application/pdf', 'size_bytes' => is_file($pdfPath) ? filesize($pdfPath) : 0, 'attribution' => null];

    $exams[] = ['id' => $cid, 'course_id' => $cid, 'title' => 'Examen final — ' . $c['title'],
        'instructions' => "Questionnaire à choix multiples tiré au sort dans la banque de questions de la formation. Une seule bonne réponse par question. Le chronomètre démarre à l'ouverture de l'examen ; à expiration, les réponses saisies sont automatiquement soumises.",
        'pass_mark' => $c['exam']['pass'], 'max_attempts' => $c['exam']['attempts'], 'question_count' => $c['exam']['count'], 'time_limit_min' => $c['exam']['time'],
        'require_completion' => 1, 'is_active' => 1];

    foreach ($c['chapters'] as $ci => $ch) {
        $chid++;
        $chapters[] = ['id' => $chid, 'course_id' => $cid, 'title' => $ch['title'], 'summary' => $ch['summary'], 'sort_order' => $ci + 1];
        foreach ($ch['lessons'] as $li => $l) {
            $lid++;
            $lessons[] = ['id' => $lid, 'chapter_id' => $chid, 'title' => $l['title'], 'type' => $l['type'], 'content' => $l['content'], 'video_url' => null,
                'media_id' => $l['type'] === 'video' ? $videoMid : ($l['type'] === 'document' ? $pdfMid : null),
                'duration_minutes' => $l['minutes'], 'is_downloadable' => 1, 'sort_order' => $li + 1];
        }
        foreach ($ch['quiz'] as [$prompt, $opts, $correct, $exp]) {
            $qid++;
            $questions[] = ['id' => $qid, 'exam_id' => $cid, 'chapter_id' => $chid, 'prompt' => $prompt, 'explanation' => $exp, 'is_active' => 1];
            foreach ($opts as $oi => $label) {
                $aid++;
                $answers[] = ['id' => $aid, 'question_id' => $qid, 'label' => $label, 'is_correct' => $oi === $correct ? 1 : 0, 'sort_order' => $oi + 1];
            }
        }
    }
}

$settings = [
    'institution_name' => "AFRICA'SPY — Intelligence Academy",
    'contact_email' => 'contact@africaspy.example',
    'contact_phone' => '',
    'contact_address' => 'Adresse à compléter dans Administration › Paramètres',
    'footer_tagline' => "Académie africaine de formation aux métiers du renseignement, du contre-espionnage et de l'intelligence stratégique — dans le strict respect du droit et des libertés.",
    'certificate_signatory_name' => 'Direction académique',
    'certificate_signatory_title' => "Direction académique AFRICA'SPY",
    'signature_is_fictitious' => '1',
    'registration_open' => '1',
    'payment_manual_enabled' => '1',
    'payment_manual_instructions' => "Virement bancaire ou dépôt Mobile Money au nom d'AFRICA'SPY.\nIndiquez impérativement la référence de paiement dans le libellé.\nCoordonnées bancaires : à compléter dans Administration › Paramètres.",
    'promo_label' => 'Offre de lancement',
    'promo_ends_at' => '',
    'mfa_required_staff' => '0',
    'security_contact' => '',
    'seller_legal_name' => "AFRICA'SPY — Intelligence Academy", 'seller_address' => '', 'seller_tax_id' => '', 'seller_registration' => '', 'seller_email' => '',
    'tax_rate' => '0', 'tax_label' => 'TVA', 'tax_exempt_mention' => 'TVA non applicable — à adapter selon votre régime fiscal',
    'invoice_footer' => 'Facture acquittée. Formation en ligne à usage personnel, non cessible.',
    'mail_transport' => '', 'mail_from' => '', 'mail_from_name' => "AFRICA'SPY Academy", 'mail_reply_to' => '',
    'smtp_host' => '', 'smtp_port' => '587', 'smtp_secure' => 'tls', 'smtp_user' => '', 'smtp_pass_enc' => '', 'welcome_mail_enabled' => '1',
];
$settingRows = array_map(static fn ($k, $v) => ['setting_key' => $k, 'setting_value' => $v], array_keys($settings), $settings);

$ann = [['id' => 1, 'title' => 'Bienvenue à l\'Académie', 'body' => "Votre espace est opérationnel. Commencez par la formation « Fondements du renseignement », socle commun de tous les parcours. Chaque chapitre se valide par une courte évaluation QCM ; l'examen final s'ouvre lorsque toutes les leçons sont terminées.", 'level' => 'info', 'audience' => 'learners', 'is_published' => 1]];

$sql = "-- =====================================================================\n"
     . "--  AFRICA'SPY — base de données complète (schéma + données initiales)\n"
     . "--  Généré le " . gmdate('Y-m-d H:i') . " UTC par database/build-sql.php\n"
     . "--  Le compte administrateur n'est PAS inclus : il est créé de manière\n"
     . "--  sécurisée par l'installateur (install/ ou php install/cli.php).\n"
     . "-- =====================================================================\n\n"
     . file_get_contents(APP_ROOT . '/database/schema.sql') . "\n\n-- ------------------------------ Données initiales\n\n"
     . ins('roles', $roles) . ins('permissions', $permRows) . ins('role_permissions', $rp)
     . ins('settings', $settingRows) . ins('categories', array_values($cats)) . ins('media', $media)
     . ins('courses', $courses) . ins('chapters', $chapters) . ins('lessons', $lessons)
     . ins('exams', $exams) . ins('questions', $questions) . ins('answers', $answers) . ins('announcements', $ann);

file_put_contents(APP_ROOT . '/database/africaspy.sql', $sql);
printf("SQL  database/africaspy.sql : %d formations, %d chapitres, %d leçons, %d questions, %d réponses (%d Ko)\n",
    count($courses), count($chapters), count($lessons), count($questions), count($answers), round(strlen($sql) / 1024));
