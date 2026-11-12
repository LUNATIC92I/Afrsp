<?php
/**
 * Catalogue initial AFRICA'SPY : domaines (catégories) + formations.
 * Chargé par database/build-sql.php pour générer database/africaspy.sql.
 */
return [
    'categories' => [
        ['code' => 'A', 'slug' => 'fondements-du-renseignement', 'name' => 'Fondements du renseignement', 'accent' => '#00C8FF',
         'description' => 'Le socle commun : cycle du renseignement, histoire des services, disciplines de collecte et méthodes d\'analyse.'],
        ['code' => 'B', 'slug' => 'espionnage-renseignement-humain', 'name' => 'Espionnage et renseignement humain', 'accent' => '#D5B36A',
         'description' => 'Le facteur humain : collecte éthique et légale, entretien, évaluation et protection des sources, rédaction.'],
        ['code' => 'C', 'slug' => 'contre-espionnage', 'name' => 'Contre-espionnage', 'accent' => '#FF4057',
         'description' => 'Protéger l\'information et les personnes : menaces internes, ingénierie sociale, analyse des menaces, contre-mesures défensives.'],
        ['code' => 'D', 'slug' => 'cyberespionnage-renseignement-numerique', 'name' => 'Cyberespionnage et renseignement numérique', 'accent' => '#865DFF',
         'description' => 'Le terrain numérique : OSINT avancé, empreintes, métadonnées, Cyber Threat Intelligence, détection — sur données synthétiques.'],
        ['code' => 'E', 'slug' => 'outils-du-renseignement', 'name' => 'Outils du renseignement', 'accent' => '#00F0D0',
         'description' => 'Les outils logiciels et matériels de l\'analyste, présentés avec leurs usages légitimes et leur cadre légal.'],
        ['code' => 'F', 'slug' => 'intelligence-strategique-geopolitique', 'name' => 'Intelligence stratégique et géopolitique', 'accent' => '#1AFF9C',
         'description' => 'Intelligence économique, veille, géopolitique africaine, analyse des conflits et des risques pays.'],
    ],
    'courses' => array_merge(
        require __DIR__ . '/courses_A.php',
        require __DIR__ . '/courses_B.php',
        require __DIR__ . '/courses_C.php',
        require __DIR__ . '/courses_D.php',
        require __DIR__ . '/courses_EF.php'
    ),
];
