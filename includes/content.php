<?php
/**
 * AFRICA'SPY : contenus éditoriaux statiques des pages publiques
 * (centre de commandement, zones d'étude, radar, galerie d'outils) et carte SVG.
 */
declare(strict_types=1);

/** Panneaux du centre de commandement (domaines d'expertise). */
function command_panels(): array
{
    return [
        ['id' => 'analysis', 'code' => 'CMD-01', 'name' => 'Intelligence Analysis', 'fr' => 'Analyse du renseignement', 'cat' => 'fondements-du-renseignement',
         'lead' => 'Transformer des informations éparses en jugements fiables, sourcés et utiles à la décision.',
         'points' => ['Cycle du renseignement et besoins en renseignement', 'Évaluation des sources et cotation', 'Analyse des hypothèses concurrentes', 'Expression normalisée de l\'incertitude'],
         'detail' => 'L\'analyste est le cœur de tout dispositif de renseignement. Il reçoit des éléments hétérogènes (articles, images, témoignages, données) et doit en tirer des jugements défendables. Nos formations l\'entraînent à lutter contre les biais cognitifs, à séparer les faits des interprétations et à rédiger des produits clairs pour des décideurs pressés.', 'glyph' => 'analysis'],
        ['id' => 'humint', 'code' => 'CMD-02', 'name' => 'Human Intelligence', 'fr' => 'Renseignement humain', 'cat' => 'espionnage-renseignement-humain',
         'lead' => 'Recueillir, auprès de personnes consentantes, des informations fiables, dans le strict respect du droit.',
         'points' => ['Entretien d\'information et écoute active', 'Évaluation de la fiabilité des sources', 'Protection et confidentialité des sources', 'Limites scientifiques de l\'analyse comportementale'],
         'detail' => 'Le renseignement humain apporte ce qu\'aucun capteur ne fournit : les intentions, le contexte, les nuances culturelles. L\'Académie l\'enseigne sous sa forme légitime (entretiens transparents, observation non intrusive, protection des interlocuteurs) et rappelle les lignes rouges absolues : corruption, chantage, usurpation d\'identité.', 'glyph' => 'humint'],
        ['id' => 'counter', 'code' => 'CMD-03', 'name' => 'Counterintelligence', 'fr' => 'Contre-espionnage', 'cat' => 'contre-espionnage',
         'lead' => 'Protéger l\'information, les personnes et les organisations contre les ingérences.',
         'points' => ['Menaces internes et risques d\'infiltration', 'Classification et protection de l\'information', 'Détection de l\'ingénierie sociale', 'Exercices de simulation de crise'],
         'detail' => 'Entreprises, administrations, universités et ONG africaines détiennent des informations convoitées. Le contre-espionnage enseigné ici est strictement défensif : cartographier ses actifs, réduire ses vulnérabilités, sensibiliser les équipes et réagir aux incidents, sans surveillance abusive des personnels.', 'glyph' => 'counter'],
        ['id' => 'cyber', 'code' => 'CMD-04', 'name' => 'Cyber Intelligence', 'fr' => 'Renseignement numérique', 'cat' => 'cyberespionnage-renseignement-numerique',
         'lead' => 'Comprendre les menaces numériques et produire du renseignement exploitable par les défenseurs.',
         'points' => ['OSINT avancé et empreintes numériques', 'Métadonnées et investigation', 'Cyber Threat Intelligence (CTI)', 'Détection d\'activités suspectes'],
         'detail' => 'Le cyberespace est devenu le premier terrain de collecte et d\'affrontement informationnel. Nos parcours forment des analystes capables de dialoguer avec les équipes techniques, de qualifier des menaces et de recommander des défenses, exclusivement sur données synthétiques et dans des environnements isolés.', 'glyph' => 'cyber'],
        ['id' => 'strategic', 'code' => 'CMD-05', 'name' => 'Strategic Analysis', 'fr' => 'Analyse stratégique', 'cat' => 'intelligence-strategique-geopolitique',
         'lead' => 'Lire les dynamiques du continent pour anticiper les risques et saisir les opportunités.',
         'points' => ['Intelligence économique et veille', 'Géopolitique africaine', 'Analyse des conflits et alerte précoce', 'Risques pays et notes stratégiques'],
         'detail' => 'ZLECAf, transition énergétique, minerais critiques, démographie, recompositions diplomatiques : l\'Afrique vit des transformations majeures. L\'analyse stratégique fournit aux décideurs publics et privés des grilles de lecture rigoureuses, nuancées et débarrassées des clichés.', 'glyph' => 'strategic'],
    ];
}

/** Petits pictogrammes SVG du centre de commandement. */
function command_glyph(string $kind): string
{
    $g = [
        'analysis'  => '<circle cx="50" cy="50" r="34" /><circle cx="50" cy="50" r="22" stroke-dasharray="3 5"/><path d="M50 16v68M16 50h68"/><path d="M28 64l12-14 10 8 22-26" stroke="#D5B36A" stroke-width="2.4"/>',
        'humint'    => '<circle cx="36" cy="38" r="10"/><path d="M18 76c2-14 10-22 18-22s16 8 18 22"/><circle cx="66" cy="38" r="10"/><path d="M48 76c2-14 10-22 18-22s16 8 18 22"/><path d="M42 24h16" stroke="#D5B36A" stroke-width="2.4"/>',
        'counter'   => '<path d="M50 12l30 12v22c0 20-13 34-30 42C33 80 20 66 20 46V24z"/><path d="M36 50l10 10 18-22" stroke="#D5B36A" stroke-width="2.4"/>',
        'cyber'     => '<circle cx="50" cy="22" r="6"/><circle cx="22" cy="72" r="6"/><circle cx="78" cy="72" r="6"/><circle cx="50" cy="54" r="8" stroke="#D5B36A" stroke-width="2.4"/><path d="M50 28v18M44 58L27 68M56 58l17 10"/>',
        'strategic' => '<path d="M14 74l22-12 24 10 26-14V22L60 36 36 26 14 38z"/><path d="M36 26v36M60 36v36"/><circle cx="60" cy="50" r="5" stroke="#D5B36A" stroke-width="2.4"/>',
    ];
    return '<svg viewBox="0 0 100 100" fill="none" stroke="#00F0D0" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($g[$kind] ?? '') . '</svg>';
}

/**
 * Zones d'étude de la carte holographique : thèmes pédagogiques régionaux.
 * Il ne s'agit PAS d'implantations physiques d'AFRICA'SPY.
 */
function study_zones(): array
{
    return [
        ['id' => 'maghreb', 'lon' => 5, 'lat' => 32, 'name' => 'Afrique du Nord & Méditerranée', 'theme' => 'Interfaces euro-africaines, migrations, énergie',
         'issues' => 'Routes migratoires, gazoducs et interconnexions électriques, coopération sécuritaire méditerranéenne.', 'domain' => 'Analyse stratégique', 'course' => 'geopolitique-africaine-risques-pays'],
        ['id' => 'sahel', 'lon' => 2, 'lat' => 15, 'name' => 'Bande sahélo-saharienne', 'theme' => 'Sécurité, gouvernance, climat',
         'issues' => 'Espaces transfrontaliers, effets du changement climatique sur le pastoralisme, enjeux de gouvernance et d\'alerte précoce.', 'domain' => 'Analyse des conflits', 'course' => 'geopolitique-africaine-risques-pays'],
        ['id' => 'guinea', 'lon' => 4, 'lat' => 3, 'name' => 'Golfe de Guinée', 'theme' => 'Sûreté maritime & économie portuaire',
         'issues' => 'Piraterie et criminalité maritime, sécurité des ports, hydrocarbures offshore, coopération régionale de Yaoundé.', 'domain' => 'Contre-espionnage & protection', 'course' => 'securite-organisationnelle-contre-mesures'],
        ['id' => 'horn', 'lon' => 44, 'lat' => 9, 'name' => 'Corne de l\'Afrique', 'theme' => 'Mer Rouge & routes commerciales',
         'issues' => 'Détroit de Bab el-Mandeb, câbles sous-marins, rivalités portuaires, gestion de l\'eau du bassin du Nil.', 'domain' => 'Renseignement numérique', 'course' => 'cyber-threat-intelligence-defense'],
        ['id' => 'lakes', 'lon' => 29, 'lat' => -2, 'name' => 'Grands Lacs', 'theme' => 'Ressources minières & traçabilité',
         'issues' => 'Minerais stratégiques, traçabilité des chaînes d\'approvisionnement, déplacements de populations, imagerie satellitaire.', 'domain' => 'Intelligence géospatiale', 'course' => 'disciplines-de-collecte'],
        ['id' => 'south', 'lon' => 26, 'lat' => -27, 'name' => 'Afrique australe', 'theme' => 'Industrie, finance & minerais critiques',
         'issues' => 'Places financières, intégration industrielle de la SADC, minerais de la transition énergétique, cybersécurité bancaire.', 'domain' => 'Intelligence économique', 'course' => 'intelligence-economique-veille-strategique'],
    ];
}

/** Projection identique à celle des tracés (repère 740 × 730). */
function geo_xy(float $lon, float $lat): array
{
    return [round(($lon + 20) * 10, 1), round((38 - $lat) * 10, 1)];
}

function africa_map_svg(bool $zones = true): string
{
    static $countries = null;
    $countries ??= json_decode((string) file_get_contents(APP_ROOT . '/includes/data/africa-map.json'), true) ?: [];
    $grat = '';
    for ($lon = -20; $lon <= 50; $lon += 10) {
        [$x] = geo_xy($lon, 0);
        $grat .= "M$x,0V730";
    }
    for ($lat = 30; $lat >= -30; $lat -= 10) {
        [, $y] = geo_xy(0, $lat);
        $grat .= "M0,{$y}H740";
    }
    $paths = '';
    foreach ($countries as $c) {
        $paths .= '<path class="country" d="' . e($c['d']) . '"><title>' . e($c['name']) . '</title></path>';
    }
    $z = '';
    $links = '';
    if ($zones) {
        $list = study_zones();
        foreach ($list as $i => $zn) {
            [$x, $y] = geo_xy($zn['lon'], $zn['lat']);
            $next = $list[($i + 1) % count($list)];
            [$x2, $y2] = geo_xy($next['lon'], $next['lat']);
            $mx = ($x + $x2) / 2 + ($y2 - $y) * .18;
            $my = ($y + $y2) / 2 - ($x2 - $x) * .18;
            $links .= "<path class=\"link\" d=\"M$x,$y Q$mx,$my $x2,$y2\"/>";
            $z .= '<g class="zone" tabindex="0" role="button" data-zone="' . e($zn['id']) . '" aria-label="' . e($zn['name']) . ' : zone d\'étude" transform="translate(' . $x . ' ' . $y . ')">'
                . '<circle class="zone__pulse" r="12"/><circle class="zone__ring" r="11"/><circle class="zone__core" r="4"/>'
                . '<text class="zone__label" x="18" y="4">' . e(mb_strtoupper(explode(' & ', $zn['name'])[0])) . '</text></g>';
        }
    }
    return '<svg class="holomap__svg" viewBox="-20 -10 780 750" role="img" aria-label="Carte de l\'Afrique : zones d\'étude pédagogiques">'
        . '<path class="graticule" d="' . $grat . '"/><g>' . $paths . '</g><g>' . $links . '</g><g>' . $z . '</g></svg>';
}

/** Éléments pédagogiques affichés au survol du radar (purement décoratif). */
function radar_items(): array
{
    return [
        ['t' => 'Cycle du renseignement', 'd' => 'Orientation, collecte, exploitation, analyse, diffusion.'],
        ['t' => 'Code de l\'Amirauté', 'd' => 'Coter séparément la source (A à F) et l\'information (1 à 6).'],
        ['t' => 'Biais de confirmation', 'd' => 'Retenir ce qui confirme l\'idée initiale : l\'ennemi n°1 de l\'analyste.'],
        ['t' => 'Moindre privilège', 'd' => 'Chacun n\'accède qu\'aux informations nécessaires à sa mission.'],
        ['t' => 'Signal faible', 'd' => 'Information fragmentaire annonçant un changement à venir.'],
        ['t' => 'BLUF', 'd' => 'Bottom Line Up Front : la conclusion en tête du rapport.'],
        ['t' => 'Pyramide de la douleur', 'd' => 'Détecter les comportements de l\'adversaire plutôt que ses adresses IP.'],
    ];
}

/** Galerie des outils (module E) : logiciels et équipements, usages légitimes. */
function tools_catalog(): array
{
    return [
        'software' => [
            ['id' => 'maltego', 'name' => 'Maltego', 'glyph' => 'MG', 'cat' => 'Cartographie de liens', 'desc' => 'Visualisation des relations entre entités publiques sous forme de graphe.',
             'use' => 'Enquêtes de conformité, cartographie de structures d\'entreprises à partir de registres publics, journalisme d\'investigation.', 'limits' => 'Respect des licences des sources interrogées ; minimisation des données personnelles.', 'type' => 'Logiciel propriétaire (édition communautaire disponible)'],
            ['id' => 'spiderfoot', 'name' => 'SpiderFoot', 'glyph' => 'SF', 'cat' => 'Automatisation OSINT', 'desc' => 'Collecte automatisée d\'informations ouvertes autour d\'un indicateur.',
             'use' => 'Évaluation de l\'exposition de sa propre organisation ou d\'un client mandant.', 'limits' => 'Mandat écrit indispensable pour toute cible tierce ; modules actifs à désactiver hors périmètre autorisé.', 'type' => 'Logiciel libre'],
            ['id' => 'theharvester', 'name' => 'theHarvester', 'glyph' => 'TH', 'cat' => 'Recensement d\'exposition', 'desc' => 'Recensement d\'adresses e-mail, de sous-domaines et d\'hôtes publics.',
             'use' => 'Audit de surface d\'attaque mandaté, sensibilisation à l\'exposition des adresses professionnelles.', 'limits' => 'Ne pas constituer de fichiers de contacts à des fins de prospection ou d\'hameçonnage.', 'type' => 'Logiciel libre'],
            ['id' => 'recon-ng', 'name' => 'Recon-ng', 'glyph' => 'RN', 'cat' => 'Cadre de reconnaissance', 'desc' => 'Cadre modulaire de collecte ouverte en ligne de commande.',
             'use' => 'Industrialiser et documenter une collecte ouverte reproductible.', 'limits' => 'Clés d\'API légitimes uniquement ; respect des conditions d\'utilisation des services.', 'type' => 'Logiciel libre'],
            ['id' => 'shodan', 'name' => 'Shodan', 'glyph' => 'SH', 'cat' => 'Moteur d\'exposition', 'desc' => 'Moteur recensant les services et équipements visibles sur Internet.',
             'use' => 'Repérer ses propres équipements exposés par erreur, études statistiques sur la sécurité d\'un pays ou d\'un secteur.', 'limits' => 'Aucune connexion à un équipement tiers ; toute tentative d\'accès non autorisé est une infraction.', 'type' => 'Service en ligne (offres gratuites et payantes)'],
            ['id' => 'google-earth', 'name' => 'Google Earth', 'glyph' => 'GE', 'cat' => 'Imagerie géospatiale', 'desc' => 'Imagerie satellitaire, relief et historique des prises de vue.',
             'use' => 'Géolocalisation de contenus publics, suivi environnemental, préparation logistique de missions.', 'limits' => 'Ne jamais localiser ni diffuser le domicile d\'une personne privée.', 'type' => 'Service gratuit'],
            ['id' => 'wireshark', 'name' => 'Wireshark', 'glyph' => 'WS', 'cat' => 'Analyse de protocoles', 'desc' => 'Capture et décodage du trafic réseau.',
             'use' => 'Diagnostic de son propre réseau, analyse de captures pédagogiques ou d\'incidents.', 'limits' => 'Capturer le trafic d\'un réseau dont on n\'est pas responsable constitue une interception illicite.', 'type' => 'Logiciel libre'],
            ['id' => 'autopsy', 'name' => 'Autopsy', 'glyph' => 'AU', 'cat' => 'Investigation de supports', 'desc' => 'Plateforme d\'investigation numérique de disques et de supports.',
             'use' => 'Réponse à incident et enquêtes autorisées, sur copies vérifiées par empreinte cryptographique.', 'limits' => 'Mandat ou cadre judiciaire ; chaîne de traçabilité obligatoire.', 'type' => 'Logiciel libre'],
            ['id' => 'volatility', 'name' => 'Volatility', 'glyph' => 'VO', 'cat' => 'Analyse mémoire', 'desc' => 'Analyse d\'images de mémoire vive (processus, connexions, artefacts).',
             'use' => 'Réponse à incident sur des systèmes de son organisation, recherche sur des échantillons pédagogiques.', 'limits' => 'Uniquement sur des systèmes dont on a la responsabilité ou dans un cadre mandaté.', 'type' => 'Logiciel libre'],
        ],
        'equipment' => [
            ['id' => 'binoculars', 'name' => 'Jumelles d\'observation', 'img' => 'binoculars.svg', 'cat' => 'Observation', 'desc' => 'Optique de terrain pour l\'observation de paysages et de sites publics.',
             'use' => 'Études environnementales, observation d\'infrastructures publiques, sécurité de missions.', 'limits' => 'Jamais vers des espaces privés ni pour suivre une personne.', 'specs' => 'Grossissement 8× à 10×, diamètre 42 mm, traitement antireflet.'],
            ['id' => 'camera', 'name' => 'Appareil photographique professionnel', 'img' => 'camera.svg', 'cat' => 'Documentation', 'desc' => 'Boîtier et objectifs pour la documentation de sites et le constat.',
             'use' => 'Documentation de terrain, reportage, constat d\'état de lieux.', 'limits' => 'Droit à l\'image, interdictions de photographier certains sites sensibles.', 'specs' => 'Capteur plein format ou APS-C, téléobjectif 70 à 300 mm, horodatage.'],
            ['id' => 'gps', 'name' => 'GPS de navigation', 'img' => 'gps.svg', 'cat' => 'Navigation', 'desc' => 'Récepteur portable pour l\'orientation et les relevés de terrain.',
             'use' => 'Orientation, relevés géoréférencés, sécurité des équipes en zone isolée.', 'limits' => 'Ne jamais poser de traceur sur un bien d\'autrui.', 'specs' => 'Multi-constellations (GPS, Galileo, GLONASS), cartes topographiques, précision d\'environ 3 m.'],
            ['id' => 'radio', 'name' => 'Matériel de communication sécurisée', 'img' => 'radio.svg', 'cat' => 'Communications', 'desc' => 'Radios et terminaux chiffrés pour la coordination des équipes.',
             'use' => 'Coordination de crise, liaison d\'équipes en mission, continuité des communications.', 'limits' => 'Respect des réglementations radio nationales et des licences de fréquences.', 'specs' => 'Chiffrement standardisé, canaux programmables, autonomie > 12 h.'],
            ['id' => 'workstation', 'name' => 'Station d\'analyse numérique', 'img' => 'workstation.svg', 'cat' => 'Analyse', 'desc' => 'Poste isolé dédié au traitement et à l\'investigation de données.',
             'use' => 'Analyse de données synthétiques, forensique autorisée, traitement de sources ouvertes.', 'limits' => 'Isolement réseau, chiffrement intégral, contrôle d\'accès nominatif.', 'specs' => 'Multi-écrans, stockage chiffré, bloqueur d\'écriture matériel.'],
            ['id' => 'safe', 'name' => 'Équipements de protection des documents', 'img' => 'document-safe.svg', 'cat' => 'Protection', 'desc' => 'Coffres, broyeurs et sacoches sécurisées pour l\'information sensible.',
             'use' => 'Stockage et destruction sécurisés selon le niveau de classification.', 'limits' => 'Adapter le niveau de protection à la sensibilité réelle des documents.', 'specs' => 'Coffre certifié, broyeur à coupe croisée, sacoche à fermeture inviolable.'],
        ],
    ];
}
