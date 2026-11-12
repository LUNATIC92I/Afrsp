<?php
/** MODULE E — OUTILS DU RENSEIGNEMENT · MODULE F — INTELLIGENCE STRATÉGIQUE ET GÉOPOLITIQUE */
return [
[
  'code' => 'AFS-E101', 'category' => 'E', 'slug' => 'outils-du-renseignement',
  'title' => 'Outils du renseignement : usages légitimes',
  'subtitle' => 'Logiciels d\'investigation et équipements professionnels, de la prise en main à l\'éthique d\'usage.',
  'summary' => 'Découvrir les principaux outils logiciels et matériels de l\'analyste — Maltego, SpiderFoot, theHarvester, Shodan, Google Earth, Wireshark, Autopsy, Volatility, Recon-ng et équipements d\'observation — avec leurs usages légitimes, leurs limites et leur cadre légal.',
  'level' => 'intermediaire', 'hours' => 16, 'featured' => true,
  'description' => '<p>Un outil n\'est ni bon ni mauvais : c\'est l\'usage, le mandat et le cadre légal qui le rendent légitime. Cette formation présente les outils de référence de l\'investigation numérique et de l\'observation professionnelle, <strong>pour des usages défensifs, journalistiques, académiques et d\'enquête autorisée</strong>.</p><p>La galerie visuelle de l\'Académie (page « Outils ») complète ce cours avec des fiches descriptives détaillées.</p>',
  'objectives' => "Connaître la fonction et l'usage légitime des principaux outils logiciels\nChoisir l'outil adapté à une question d'investigation\nComprendre les principes de l'investigation numérique légale (forensique)\nConnaître les équipements d'observation et de protection documentaire\nAppliquer une grille d'évaluation éthique avant tout usage",
  'skills' => "Construire un graphe de relations à partir de données publiques\nLire une capture réseau sur son propre réseau\nComprendre une chaîne de traitement forensique\nRédiger une note de cadrage d'usage d'outil",
  'prerequisites' => "Disciplines de collecte (recommandé)\nAisance informatique",
  'chapters' => [
    [
      'title' => 'Outils de recherche et de cartographie OSINT',
      'summary' => 'Maltego, SpiderFoot, theHarvester, Recon-ng, Shodan, Google Earth.',
      'lessons' => [
        ['title' => 'Briefing vidéo : la boîte à outils de l\'analyste', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Panorama visuel des familles d\'outils et de la règle d\'or : mandat, légalité, proportionnalité.</p>'],
        ['title' => 'Panorama des outils OSINT', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<table><thead><tr><th>Outil</th><th>Fonction</th><th>Usage légitime type</th></tr></thead><tbody>
<tr><td><strong>Maltego</strong></td><td>Visualisation de liens sous forme de graphe ; « transformations » interrogeant des sources</td><td>Cartographier les relations publiques entre entreprises et domaines dans une enquête de conformité</td></tr>
<tr><td><strong>SpiderFoot</strong></td><td>Automatisation de la collecte ouverte autour d'un indicateur</td><td>Évaluer l'exposition de sa propre organisation</td></tr>
<tr><td><strong>theHarvester</strong></td><td>Recensement d'adresses e-mail, sous-domaines et hôtes publics</td><td>Audit de surface d'attaque mandaté</td></tr>
<tr><td><strong>Recon-ng</strong></td><td>Cadre modulaire de reconnaissance en ligne de commande</td><td>Industrialiser une collecte ouverte documentée</td></tr>
<tr><td><strong>Shodan</strong></td><td>Moteur recensant les services exposés sur Internet</td><td>Repérer ses propres équipements exposés par erreur ; études statistiques</td></tr>
<tr><td><strong>Google Earth</strong></td><td>Imagerie et historique géographique</td><td>Géolocalisation, suivi environnemental, préparation logistique</td></tr>
</tbody></table>
<h3>Bonnes pratiques communes</h3>
<ul><li>Respecter les conditions d'utilisation et les licences ;</li><li>Ne fournir aux outils que les clés d'API légitimement obtenues ;</li><li>Limiter la collecte de données personnelles au strict nécessaire ;</li><li>Conserver un journal des requêtes pour la traçabilité.</li></ul>
<div class="warning">Ces outils ne doivent jamais servir à profiler, suivre ou harceler une personne privée. Un tel usage peut constituer une infraction (atteinte à la vie privée, harcèlement) et viole la charte AFRICA'SPY.</div>
HTML],
      ],
      'quiz' => [
        ['Quelle est la fonction principale de Maltego ?', ['Analyser la mémoire vive', 'Visualiser des relations sous forme de graphe', 'Capturer le trafic réseau', 'Éditer des images'], 1, 'Maltego représente des liens entre entités.'],
        ['Un usage légitime de Shodan est :', ['Accéder aux caméras d\'autrui', 'Repérer ses propres équipements exposés par erreur', 'Prendre le contrôle de serveurs', 'Contourner une authentification'], 1, 'Il sert notamment à mesurer sa propre exposition.'],
        ['Profiler une personne privée avec ces outils :', ['Est une pratique OSINT standard', 'Peut constituer une infraction et viole la charte', 'Est autorisé si la personne est connue', 'Est obligatoire en formation'], 1, 'C\'est une ligne rouge.'],
      ],
    ],
    [
      'title' => 'Outils d\'analyse réseau et d\'investigation numérique',
      'summary' => 'Wireshark, Autopsy, Volatility.',
      'lessons' => [
        ['title' => 'Analyse réseau et forensique', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<h3>Wireshark — analyse de protocoles</h3>
<p>Wireshark capture et décode le trafic d'un réseau. Usage légitime : diagnostiquer une panne, comprendre un protocole, analyser une capture fournie dans un cadre pédagogique ou d'incident. <strong>Capturer le trafic d'un réseau dont on n'est pas responsable est illégal.</strong></p>
<h3>Autopsy — investigation de supports</h3>
<p>Interface graphique d'investigation de disques : chronologie des fichiers, recherche par mots-clés, récupération d'éléments supprimés, extraction de métadonnées. Utilisé par les enquêteurs autorisés et les équipes de réponse à incident, sur des <strong>copies</strong> de supports.</p>
<h3>Volatility — analyse de la mémoire vive</h3>
<p>Cadre d'analyse d'images mémoire : processus actifs, connexions, artefacts de logiciels malveillants. Indispensable en réponse à incident, car certaines menaces ne laissent aucune trace sur disque.</p>
<h3>Principes de l'investigation numérique</h3>
<ol><li><strong>Mandat</strong> : autorisation écrite ou cadre judiciaire ;</li><li><strong>Préservation</strong> : copie bit à bit, empreinte cryptographique ;</li><li><strong>Traçabilité</strong> : chaîne de possession documentée ;</li><li><strong>Reproductibilité</strong> : un autre expert doit aboutir aux mêmes constats ;</li><li><strong>Neutralité</strong> : rechercher les éléments à charge comme à décharge.</li></ol>
HTML],
      ],
      'quiz' => [
        ['Capturer le trafic d\'un réseau dont on n\'est pas responsable :', ['Est légal avec Wireshark', 'Est illégal', 'Est recommandé en formation', 'Est sans conséquence'], 1, 'Sans autorisation, c\'est une interception illicite.'],
        ['Volatility sert à analyser :', ['La mémoire vive', 'Les images satellites', 'Les réseaux sociaux', 'Les documents PDF uniquement'], 0, 'C\'est un outil d\'analyse mémoire.'],
        ['Sur quoi travaille-t-on lors d\'une investigation de support ?', ['Sur l\'original directement', 'Sur une copie vérifiée par empreinte', 'Sur une capture d\'écran', 'Sur un résumé'], 1, 'L\'original est préservé.'],
      ],
    ],
    [
      'title' => 'Équipements physiques et matériels',
      'summary' => 'Observation, photographie, navigation, communications, stations, protection documentaire.',
      'lessons' => [
        ['title' => 'Les équipements de l\'analyste de terrain', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<table><thead><tr><th>Équipement</th><th>Usage légitime</th><th>Limites et règles</th></tr></thead><tbody>
<tr><td>Jumelles d'observation</td><td>Observation de paysages, de sites publics, d'infrastructures, études environnementales</td><td>Jamais vers des espaces privés ni pour suivre une personne</td></tr>
<tr><td>Appareil photo professionnel</td><td>Documentation de sites, reportage, constat</td><td>Droit à l'image, interdictions de photographier certains sites sensibles</td></tr>
<tr><td>GPS de navigation</td><td>Orientation, relevés de terrain, sécurité des missions</td><td>Ne jamais poser de traceur sur un bien d'autrui</td></tr>
<tr><td>Communications sécurisées</td><td>Liaison des équipes, coordination de crise</td><td>Respect des réglementations radio nationales</td></tr>
<tr><td>Station d'analyse numérique</td><td>Traitement de données, forensique autorisée</td><td>Isolement, chiffrement, contrôle d'accès</td></tr>
<tr><td>Protection des documents</td><td>Coffres, broyeurs, sacoches sécurisées</td><td>Adaptés au niveau de classification</td></tr>
</tbody></table>
<div class="warning">L'Académie ne fournit aucun plan de fabrication, aucune information sur des dispositifs clandestins (micros, traceurs dissimulés) ni aucune méthode d'interception. Leur usage par des particuliers est illégal dans la plupart des pays.</div>
HTML],
      ],
      'quiz' => [
        ['Un usage légitime des jumelles d\'observation est :', ['Observer l\'intérieur d\'un domicile', 'Étudier un paysage ou une infrastructure publique', 'Suivre un individu', 'Lire des documents à distance'], 1, 'Observation de lieux publics uniquement.'],
        ['Poser un traceur GPS sur le véhicule d\'autrui est :', ['Une technique de terrain courante', 'Illégal dans la plupart des pays', 'Autorisé pour les entreprises', 'Recommandé pour la sécurité'], 1, 'C\'est une atteinte à la vie privée.'],
        ['Un broyeur de documents relève de :', ['La protection documentaire', 'L\'observation', 'La navigation', 'L\'interception'], 0, 'Il protège contre la récupération de documents jetés.'],
      ],
    ],
    [
      'title' => 'Cadre légal et éthique d\'usage des outils',
      'summary' => 'La note de cadrage avant toute utilisation.',
      'lessons' => [
        ['title' => 'Avant d\'utiliser un outil', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Le professionnel rédige, avant toute investigation, une <strong>note de cadrage</strong> qui répond à six questions :</p>
<ol><li><strong>Finalité</strong> : quelle question précise ?</li><li><strong>Mandat</strong> : qui autorise, par écrit ?</li><li><strong>Périmètre</strong> : quelles cibles, quelles exclusions ?</li><li><strong>Base légale</strong> : quelles lois s'appliquent (données personnelles, cybercriminalité) ?</li><li><strong>Proportionnalité</strong> : l'outil choisi est-il le moins intrusif possible ?</li><li><strong>Conservation</strong> : combien de temps garde-t-on les données, et comment les détruit-on ?</li></ol>
<p>Cette discipline protège les personnes concernées, le client et l'analyste lui-même.</p>
HTML],
      ],
      'quiz' => [
        ['Quel document rédiger avant une investigation outillée ?', ['Un communiqué de presse', 'Une note de cadrage', 'Un contrat de travail', 'Une facture'], 1, 'Elle formalise finalité, mandat, périmètre et légalité.'],
        ['Le principe de proportionnalité impose de choisir :', ['L\'outil le plus puissant', 'L\'outil le moins intrusif suffisant', 'L\'outil le plus récent', 'L\'outil gratuit'], 1, 'L\'intrusion doit être minimale.'],
        ['Pourquoi définir une durée de conservation des données ?', ['Pour respecter les règles de protection des données', 'Pour vendre les données', 'Par superstition', 'Ce n\'est pas utile'], 0, 'Conserver indéfiniment est contraire au droit des données.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Choisir l\'outil juste et le justifier.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Tableau des outils, usages légitimes et modèle de note de cadrage.</p>'],
        ['title' => 'Exercice : rédiger une note de cadrage', 'type' => 'exercise', 'minutes' => 40, 'content' => <<<'HTML'
<p><strong>Mandat fictif :</strong> une banque régionale vous charge par écrit d'évaluer l'exposition publique de ses propres services en ligne.</p>
<ol><li>Rédigez la note de cadrage en six rubriques.</li><li>Sélectionnez trois outils et justifiez chaque choix.</li><li>Listez explicitement ce qui est hors périmètre (employés à titre privé, fournisseurs non mandants…).</li><li>Prévoyez la restitution et la destruction des données.</li></ol>
HTML],
        ['title' => 'Étude de cas : la dérive d\'un outil', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Un analyste mandaté pour auditer l'exposition d'une entreprise utilise ensuite les mêmes outils pour réunir des informations sur la vie privée d'un dirigeant concurrent, « par curiosité ». L'affaire est révélée.</p>
<ol><li>Quelles limites du mandat ont été franchies ?</li><li>Quelles infractions sont potentiellement constituées ?</li><li>Quelles conséquences pour l'analyste, son cabinet et le client ?</li><li>Quels contrôles internes auraient prévenu la dérive ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-F101', 'category' => 'F', 'slug' => 'intelligence-economique-veille-strategique',
  'title' => 'Intelligence économique et veille stratégique',
  'subtitle' => 'Anticiper, protéger et influencer légalement dans l\'économie africaine.',
  'summary' => 'Mettre en place un dispositif d\'intelligence économique : veille stratégique, protection des intérêts économiques et production de notes stratégiques à destination des décideurs.',
  'level' => 'fondamental', 'hours' => 14, 'featured' => true,
  'description' => '<p>Avec la Zone de libre-échange continentale africaine (ZLECAf), l\'essor des entreprises africaines et la compétition mondiale pour les ressources, l\'intelligence économique devient un levier de souveraineté. Cette formation en présente les trois piliers : <strong>veille</strong>, <strong>protection</strong> et <strong>influence légitime</strong>.</p>',
  'objectives' => "Définir l'intelligence économique et ses trois piliers\nConcevoir un dispositif de veille stratégique\nIdentifier et protéger les intérêts économiques d'une organisation\nRédiger une note stratégique claire et argumentée",
  'skills' => "Définir des axes de veille\nSélectionner et qualifier des sources\nProduire un bulletin de veille\nStructurer une note stratégique",
  'prerequisites' => "Aucun prérequis\nIntérêt pour l'économie et les entreprises",
  'chapters' => [
    [
      'title' => 'Intelligence économique',
      'summary' => 'Définition, piliers et enjeux africains.',
      'lessons' => [
        ['title' => 'Briefing vidéo : l\'information comme avantage', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les trois piliers de l\'intelligence économique.</p>'],
        ['title' => 'Les trois piliers', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>L'intelligence économique (IE) est la maîtrise et la protection de l'information stratégique utile aux acteurs économiques. Elle se pratique <strong>exclusivement par des moyens légaux</strong> : c'est ce qui la distingue de l'espionnage industriel.</p>
<h3>1. La veille</h3><p>Surveiller l'environnement : concurrents, technologies, réglementations, marchés, signaux faibles.</p>
<h3>2. La protection</h3><p>Sécuriser le patrimoine informationnel : savoir-faire, données, brevets, réputation.</p>
<h3>3. L'influence</h3><p>Agir légitimement sur son environnement : participation aux normes, plaidoyer transparent, communication, réseaux professionnels.</p>
<h3>Enjeux africains</h3>
<ul><li>La ZLECAf ouvre un marché de plus de 1,3 milliard de personnes : connaître les règles d'origine et les acteurs devient crucial ;</li><li>Les ressources minières et agricoles attirent une compétition internationale intense ;</li><li>Le paiement mobile et la fintech africaine sont devenus des secteurs d'innovation mondiale à protéger.</li></ul>
HTML],
      ],
      'quiz' => [
        ['Qu\'est-ce qui distingue l\'intelligence économique de l\'espionnage industriel ?', ['Le budget', 'L\'usage exclusif de moyens légaux', 'Le secteur d\'activité', 'La nationalité des acteurs'], 1, 'L\'IE est légale par définition.'],
        ['Quels sont les trois piliers de l\'IE ?', ['Veille, protection, influence', 'Achat, vente, stockage', 'Collecte, piratage, revente', 'Audit, contrôle, sanction'], 0, 'Ce triptyque structure toute démarche.'],
        ['La ZLECAf est :', ['Une agence de renseignement', 'La Zone de libre-échange continentale africaine', 'Une banque régionale', 'Un satellite'], 1, 'Elle redéfinit le paysage économique africain.'],
      ],
    ],
    [
      'title' => 'Veille stratégique',
      'summary' => 'Organiser la surveillance de l\'environnement.',
      'lessons' => [
        ['title' => 'Monter un dispositif de veille', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<h3>Les axes de veille</h3>
<table><thead><tr><th>Type</th><th>Objet</th><th>Sources ouvertes</th></tr></thead><tbody>
<tr><td>Concurrentielle</td><td>Produits, prix, stratégies</td><td>Rapports annuels, salons, presse économique</td></tr>
<tr><td>Technologique</td><td>Innovations, brevets</td><td>Bases de brevets, publications scientifiques</td></tr>
<tr><td>Réglementaire</td><td>Lois, normes, fiscalité</td><td>Journaux officiels, communautés économiques régionales</td></tr>
<tr><td>Sociétale</td><td>Opinion, attentes</td><td>Réseaux sociaux publics, études</td></tr>
</tbody></table>
<h3>Le processus</h3>
<ol><li>Définir les <strong>facteurs critiques de succès</strong> et les questions clés ;</li><li>Sélectionner et qualifier les sources ;</li><li>Mettre en place des alertes et une revue régulière ;</li><li>Analyser et diffuser un bulletin court ;</li><li>Mesurer l'utilité auprès des destinataires.</li></ol>
<div class="callout">Les <strong>signaux faibles</strong> — informations fragmentaires annonçant un changement — sont la vraie valeur ajoutée de la veille. Ils ne prennent sens que s'ils sont rapprochés et discutés.</div>
HTML],
      ],
      'quiz' => [
        ['La surveillance des journaux officiels relève de la veille :', ['Concurrentielle', 'Réglementaire', 'Technologique', 'Sociétale'], 1, 'Lois, décrets et normes.'],
        ['Qu\'est-ce qu\'un signal faible ?', ['Une information fragmentaire annonçant un changement', 'Un mauvais réseau mobile', 'Une rumeur infondée', 'Une source non fiable'], 0, 'Il annonce une évolution potentielle.'],
        ['Par quoi commence un dispositif de veille ?', ['L\'achat d\'un logiciel', 'La définition des questions clés et des facteurs critiques de succès', 'La création d\'un compte social', 'L\'embauche d\'un détective'], 1, 'On sait d\'abord ce que l\'on cherche.'],
      ],
    ],
    [
      'title' => 'Protection des intérêts économiques',
      'summary' => 'Savoir-faire, propriété intellectuelle et réputation.',
      'lessons' => [
        ['title' => 'Protéger la valeur', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<ul><li><strong>Propriété intellectuelle</strong> : brevets, marques, dessins — en Afrique, via l'OAPI (espace francophone) et l'ARIPO (espace anglophone) en complément des offices nationaux ;</li><li><strong>Secret des affaires</strong> : protection des informations confidentielles par des mesures raisonnables (contrats, accès restreints) ;</li><li><strong>Contrats</strong> : clauses de confidentialité et de non-concurrence dans les partenariats ;</li><li><strong>Réputation</strong> : surveillance des atteintes, plan de réponse aux campagnes de dénigrement.</li></ul>
<h3>Situations à risque</h3>
<p>Joint-ventures, transferts de technologie, levées de fonds, délégations étrangères, recrutement de profils issus de la concurrence, stagiaires et visites de site.</p>
<div class="note">Protéger n'est pas se fermer : il s'agit de partager <em>ce qu'il faut, avec qui il faut, au bon moment</em>.</div>
HTML],
      ],
      'quiz' => [
        ['Quel office régional gère la propriété intellectuelle dans l\'espace francophone africain ?', ['ARIPO', 'OAPI', 'OMC', 'BCEAO'], 1, 'L\'Organisation africaine de la propriété intellectuelle.'],
        ['Le secret des affaires protège une information si :', ['Elle est publiée', 'Des mesures raisonnables de protection ont été prises', 'Elle est ancienne', 'Elle est en anglais'], 1, 'La protection doit être organisée.'],
        ['Laquelle est une situation à risque ?', ['Une levée de fonds avec due diligence', 'Un jour férié', 'Une réunion d\'équipe interne', 'Un inventaire de stock'], 0, 'Elle implique un partage important d\'informations.'],
      ],
    ],
    [
      'title' => 'Élaboration de notes stratégiques',
      'summary' => 'Synthétiser pour décider.',
      'lessons' => [
        ['title' => 'La note stratégique', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>La note stratégique éclaire une décision de long terme. Elle tient en deux à quatre pages.</p>
<ol><li><strong>Question posée</strong> et contexte en trois lignes ;</li><li><strong>Messages clés</strong> (3 à 5 puces) ;</li><li><strong>Analyse</strong> : facteurs, acteurs, tendances ;</li><li><strong>Scénarios</strong> : tendanciel, favorable, défavorable ;</li><li><strong>Options</strong> avec avantages, risques, coûts ;</li><li><strong>Recommandation</strong> argumentée ;</li><li><strong>Indicateurs de suivi</strong>.</li></ol>
<p>Outils utiles : analyse PESTEL, SWOT, cartographie des parties prenantes, matrice des scénarios.</p>
HTML],
      ],
      'quiz' => [
        ['Combien de scénarios une note stratégique présente-t-elle classiquement ?', ['Un seul', 'Trois (tendanciel, favorable, défavorable)', 'Dix', 'Aucun'], 1, 'Trois scénarios contrastés.'],
        ['PESTEL analyse les facteurs :', ['Politiques, économiques, sociaux, technologiques, environnementaux, légaux', 'Prix, emballage, stock, transport', 'Personnes, équipes, salaires', 'Produits, essais, livraisons'], 0, 'Un outil d\'analyse de l\'environnement.'],
        ['Où place-t-on les messages clés ?', ['En annexe', 'En tête, après la question posée', 'À la fin', 'Nulle part'], 1, 'Le décideur doit les lire en premier.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Bulletin de veille et note stratégique.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Modèles de bulletin de veille et de note stratégique.</p>'],
        ['title' => 'Exercice : bulletin de veille', 'type' => 'exercise', 'minutes' => 45, 'content' => <<<'HTML'
<p>Pour une PME fictive de transformation de cajou souhaitant exporter dans la ZLECAf :</p>
<ol><li>Définissez trois questions clés de veille.</li><li>Proposez dix sources ouvertes qualifiées.</li><li>Rédigez un bulletin d'une page avec trois informations commentées et un signal faible.</li></ol>
HTML],
        ['title' => 'Étude de cas : la négociation de licence', 'type' => 'case_study', 'minutes' => 35, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Une startup de paiement mobile négocie une licence de sa technologie avec un groupe étranger. Lors des réunions, le partenaire demande des détails techniques de plus en plus précis, sans engagement signé.</p>
<ol><li>Quels risques pour la startup ?</li><li>Quelles protections juridiques mettre en place avant la prochaine réunion ?</li><li>Rédigez les messages clés d'une note stratégique au conseil d'administration.</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-F201', 'category' => 'F', 'slug' => 'geopolitique-africaine-risques-pays',
  'title' => 'Géopolitique africaine, conflits et risques pays',
  'subtitle' => 'Lire les dynamiques du continent pour anticiper les risques.',
  'summary' => 'Analyser les grandes dynamiques géopolitiques africaines, comprendre les conflits et évaluer méthodiquement le risque pays pour éclairer les décisions d\'investissement, de coopération ou d\'implantation.',
  'level' => 'avance', 'hours' => 16, 'featured' => false,
  'description' => '<p>L\'Afrique n\'est pas un bloc : 54 États, des communautés économiques régionales, des espaces transfrontaliers et des dynamiques très contrastées. Cette formation donne des grilles de lecture rigoureuses et nuancées, loin des clichés, pour analyser les situations et évaluer les risques.</p>',
  'objectives' => "Identifier les grandes dynamiques géopolitiques du continent\nAnalyser un conflit selon une grille structurée\nConduire une analyse de risque pays complète\nÉviter les biais et stéréotypes dans l'analyse",
  'skills' => "Utiliser une grille d'analyse de conflit\nConstruire une matrice de risque pays\nIdentifier les sources régionales fiables\nFormuler des scénarios prospectifs",
  'prerequisites' => "Intelligence économique et veille stratégique (recommandé)\nCulture générale en histoire et relations internationales",
  'chapters' => [
    [
      'title' => 'Analyse géopolitique africaine',
      'summary' => 'Acteurs, espaces et dynamiques.',
      'lessons' => [
        ['title' => 'Briefing vidéo : un continent pluriel', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les grandes clés de lecture géopolitique du continent.</p>'],
        ['title' => 'Grilles de lecture du continent', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<h3>Les acteurs</h3>
<ul><li><strong>Union africaine</strong> : Conseil de paix et de sécurité, Agenda 2063 ;</li><li><strong>Communautés économiques régionales</strong> : CEDEAO, CEEAC, SADC, EAC, IGAD, UMA, COMESA… ;</li><li><strong>États</strong> et leurs intérêts propres ;</li><li><strong>Acteurs non étatiques</strong> : entreprises, diasporas, organisations religieuses, groupes armés ;</li><li><strong>Puissances extérieures</strong> et nouveaux partenaires.</li></ul>
<h3>Les espaces stratégiques</h3>
<table><thead><tr><th>Espace</th><th>Enjeux</th></tr></thead><tbody>
<tr><td>Sahel</td><td>Sécurité, gouvernance, climat, migrations</td></tr>
<tr><td>Golfe de Guinée</td><td>Sûreté maritime, hydrocarbures, ports</td></tr>
<tr><td>Corne de l'Afrique</td><td>Mer Rouge, routes commerciales, eau du Nil</td></tr>
<tr><td>Grands Lacs</td><td>Ressources minières, frontières, déplacés</td></tr>
<tr><td>Afrique australe</td><td>Intégration industrielle, énergie, minerais critiques</td></tr>
</tbody></table>
<h3>Les dynamiques transversales</h3>
<p>Démographie (population très jeune), urbanisation rapide, transition énergétique et minerais critiques, numérisation, effets du changement climatique, recompositions des alliances internationales.</p>
<div class="callout">Méthode : toujours croiser l'échelle locale, nationale, régionale et internationale. Une crise locale a souvent des racines et des effets à d'autres échelles.</div>
HTML],
      ],
      'quiz' => [
        ['Quel organe de l\'UA traite des questions de sécurité ?', ['Le Conseil de paix et de sécurité', 'La BAD', 'L\'OAPI', 'Le NEPAD'], 0, 'Le CPS est l\'organe décisionnel en la matière.'],
        ['Quel enjeu caractérise le Golfe de Guinée ?', ['La sûreté maritime', 'L\'eau du Nil', 'La désertification du Kalahari', 'Les glaciers'], 0, 'Piraterie et sécurité portuaire.'],
        ['Pourquoi croiser plusieurs échelles d\'analyse ?', ['Pour allonger le rapport', 'Parce qu\'une crise locale a souvent des racines et effets à d\'autres échelles', 'Par obligation légale', 'Ce n\'est pas utile'], 1, 'Local, national, régional et international interagissent.'],
      ],
    ],
    [
      'title' => 'Analyse des conflits',
      'summary' => 'Causes, acteurs, dynamiques et sorties de crise.',
      'lessons' => [
        ['title' => 'Grille d\'analyse des conflits', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>Un conflit s'analyse selon trois dimensions complémentaires.</p>
<h3>1. Profil</h3><p>Contexte historique, géographique, politique, économique et social.</p>
<h3>2. Causes</h3>
<ul><li><strong>Structurelles</strong> : inégalités, accès à la terre et à l'eau, gouvernance ;</li><li><strong>Accélératrices</strong> : crise économique, élection contestée, choc climatique ;</li><li><strong>Déclencheurs</strong> : incident, assassinat, décision politique.</li></ul>
<h3>3. Acteurs</h3>
<p>Pour chaque acteur : intérêts, positions, capacités, alliances, et ceux qui ont intérêt à la paix (les « connecteurs ») autant que ceux qui ont intérêt au conflit.</p>
<h3>Dynamiques et scénarios</h3>
<p>On identifie les tendances, les fenêtres d'opportunité pour la paix et les indicateurs d'alerte précoce. Les mécanismes africains d'alerte précoce (comme le système continental de l'UA ou ECOWARN de la CEDEAO) reposent sur ce type d'indicateurs.</p>
<div class="warning">L'analyse de conflit exige une grande prudence : éviter les lectures ethnicisantes simplistes, donner la parole aux sources locales et respecter la dignité des victimes.</div>
HTML],
      ],
      'quiz' => [
        ['Une élection contestée est généralement une cause :', ['Structurelle', 'Accélératrice', 'Inexistante', 'Géographique'], 1, 'Elle accentue des tensions existantes.'],
        ['Que sont les « connecteurs » dans l\'analyse de conflit ?', ['Des câbles réseau', 'Des acteurs ou éléments favorisant la paix', 'Des groupes armés', 'Des journalistes étrangers'], 1, 'Ils sont des leviers de résolution.'],
        ['ECOWARN est le système d\'alerte précoce de :', ['La SADC', 'La CEDEAO', 'L\'EAC', 'L\'ONU'], 1, 'Mécanisme de la CEDEAO.'],
      ],
    ],
    [
      'title' => 'Analyse des risques pays',
      'summary' => 'Une méthode structurée et transparente.',
      'lessons' => [
        ['title' => 'Évaluer le risque pays', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>Le risque pays mesure la probabilité que des événements liés à un pays affectent négativement un projet ou une organisation.</p>
<table><thead><tr><th>Dimension</th><th>Exemples d'indicateurs</th></tr></thead><tbody>
<tr><td>Politique</td><td>Stabilité institutionnelle, calendrier électoral, relations régionales</td></tr>
<tr><td>Sécuritaire</td><td>Incidents, zones à risque, criminalité</td></tr>
<tr><td>Économique</td><td>Croissance, inflation, dette, change</td></tr>
<tr><td>Juridique</td><td>Sécurité des contrats, fiscalité, efficacité judiciaire</td></tr>
<tr><td>Opérationnel</td><td>Infrastructures, logistique, énergie</td></tr>
<tr><td>Environnemental et social</td><td>Climat, acceptation locale, droits humains</td></tr>
</tbody></table>
<h3>Méthode</h3>
<ol><li>Définir le projet (le risque dépend toujours de l'exposition) ;</li><li>Noter chaque dimension avec des critères explicites ;</li><li>Pondérer selon le projet ;</li><li>Analyser les tendances, pas seulement l'instantané ;</li><li>Proposer des mesures d'atténuation.</li></ol>
<div class="note">Un score ne remplace pas l'analyse. Les notations globales doivent être lues avec leurs hypothèses ; un même pays peut présenter des risques très différents selon les régions et les secteurs.</div>
HTML],
      ],
      'quiz' => [
        ['Le risque pays dépend :', ['Uniquement du pays', 'Du pays et de l\'exposition du projet', 'Du drapeau national', 'De la monnaie uniquement'], 1, 'Un même pays présente des risques différents selon les projets.'],
        ['La sécurité des contrats relève de la dimension :', ['Juridique', 'Sécuritaire', 'Environnementale', 'Opérationnelle'], 0, 'Elle concerne le cadre légal et judiciaire.'],
        ['Pourquoi analyser les tendances ?', ['Parce qu\'un instantané peut être trompeur', 'Pour simplifier', 'Par habitude', 'Ce n\'est pas nécessaire'], 0, 'La trajectoire compte autant que la situation présente.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Matrice de risque pays et analyse de conflit fictive.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Grille d\'analyse de conflit et matrice de risque pays.</p>'],
        ['title' => 'Exercice : matrice de risque pays', 'type' => 'exercise', 'minutes' => 50, 'content' => <<<'HTML'
<p><strong>Projet fictif :</strong> implantation d'une unité de transformation agricole dans la « République de Kanaga » (pays imaginaire côtier d'Afrique de l'Ouest, 18 millions d'habitants, élections dans 14 mois, inflation à 9 %, port moderne, tensions foncières dans le nord).</p>
<ol><li>Notez chacune des six dimensions de 1 à 5 avec justification.</li><li>Pondérez selon le projet.</li><li>Rédigez trois scénarios à deux ans.</li><li>Proposez cinq mesures d'atténuation.</li></ol>
HTML],
        ['title' => 'Étude de cas : crise dans la région des « Trois Rivières »', 'type' => 'case_study', 'minutes' => 40, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique entièrement fictif.</strong> Dans une région frontalière imaginaire, une sécheresse prolongée aggrave les tensions entre agriculteurs et éleveurs. Un incident sur un point d'eau dégénère. Des rumeurs circulent en ligne.</p>
<ol><li>Réalisez le profil, l'analyse des causes et la cartographie des acteurs.</li><li>Identifiez trois connecteurs pour la paix.</li><li>Proposez cinq indicateurs d'alerte précoce.</li><li>Quelles précautions de vocabulaire et de sources adopter ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 8, 'time' => 20],
],
];
