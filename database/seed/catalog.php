<?php
/**
 * Catalogue initial AFRICA'SPY : domaines (catégories) + formations.
 * Chargé par database/build-sql.php pour générer database/africaspy.sql.
 */
$__catalog = [
    'categories' => [
        ['code' => 'A', 'slug' => 'fondements-du-renseignement', 'name' => 'Fondements du renseignement', 'accent' => '#00C8FF',
         'description' => 'Le socle commun : cycle du renseignement, histoire des services, disciplines de collecte et méthodes d\'analyse.'],
        ['code' => 'B', 'slug' => 'espionnage-renseignement-humain', 'name' => 'Espionnage et renseignement humain', 'accent' => '#D5B36A',
         'description' => 'Le facteur humain : collecte éthique et légale, entretien, évaluation et protection des sources, rédaction.'],
        ['code' => 'C', 'slug' => 'contre-espionnage', 'name' => 'Contre-espionnage', 'accent' => '#FF4057',
         'description' => 'Protéger l\'information et les personnes : menaces internes, ingénierie sociale, analyse des menaces, contre-mesures défensives.'],
        ['code' => 'D', 'slug' => 'cyberespionnage-renseignement-numerique', 'name' => 'Cyberespionnage et renseignement numérique', 'accent' => '#865DFF',
         'description' => 'Le terrain numérique : OSINT avancé, empreintes, métadonnées, Cyber Threat Intelligence, détection, sur données synthétiques.'],
        ['code' => 'E', 'slug' => 'outils-du-renseignement', 'name' => 'Outils du renseignement', 'accent' => '#00F0D0',
         'description' => 'Les outils logiciels et matériels de l\'analyste, présentés avec leurs usages légitimes et leur cadre légal.'],
        ['code' => 'F', 'slug' => 'intelligence-strategique-geopolitique', 'name' => 'Intelligence stratégique et géopolitique', 'accent' => '#1AFF9C',
         'description' => 'Intelligence économique, veille, géopolitique africaine, analyse des conflits et des risques pays.'],
        ['code' => 'G', 'slug' => 'renseignement-militaire', 'name' => 'Renseignement militaire', 'accent' => '#087CFF',
         'description' => 'Appui à la décision du commandement : environnement opérationnel, estimation, sécurité des opérations, opérations de paix et droit des conflits armés.'],
    ],
    'courses' => array_merge(
        require __DIR__ . '/courses_A.php',
        require __DIR__ . '/courses_B.php',
        require __DIR__ . '/courses_C.php',
        require __DIR__ . '/courses_D.php',
        require __DIR__ . '/courses_EF.php',
        require __DIR__ . '/courses_G.php'
    ),
];

// Fusion des enrichissements (approfondissements, QCM supplémentaires, glossaire, bibliographie)
$__enrich = require __DIR__ . '/enrichment.php';
foreach ($__catalog['courses'] as &$__c) {
    $__e = $__enrich[$__c['code']] ?? null;
    if (!$__e) continue;
    foreach ($__e['chapters'] as $__i => $__add) {
        if (!isset($__c['chapters'][$__i])) continue;
        array_push($__c['chapters'][$__i]['lessons'], ...$__add['lessons']);
        array_push($__c['chapters'][$__i]['quiz'], ...$__add['quiz']);
    }
    $__last = count($__c['chapters']) - 1;
    array_push($__c['chapters'][$__last]['lessons'], ...$__e['final']);
    // Chapitre « Mise en situation » inséré avant l'atelier final
    if (!empty($__e['scenario'])) {
        array_splice($__c['chapters'], $__last, 0, [$__e['scenario']]);
    }
    // Banque de questions élargie : examens plus complets
    $__c['exam']['count'] = 15;
    $__c['exam']['time'] = 35;
}
unset($__c);
return $__catalog;
