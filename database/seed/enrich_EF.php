<?php
/** Enrichissements pédagogiques — modules E et F. */
return [
'AFS-E101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : choisir l\'outil selon la question', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>L'outil découle de la question, jamais l'inverse. Une matrice simple aide à choisir.</p>
<table><thead><tr><th>Question</th><th>Outil adapté</th><th>Alternative manuelle</th></tr></thead><tbody>
<tr><td>Quelles relations publiques entre ces sociétés ?</td><td>Maltego (graphe)</td><td>Registres de commerce + tableur</td></tr>
<tr><td>Que voit-on de notre organisation sur Internet ?</td><td>SpiderFoot, theHarvester</td><td>Moteurs de recherche + journaux de certificats</td></tr>
<tr><td>Nos équipements sont-ils exposés ?</td><td>Shodan (recherche sur nos plages d'adresses)</td><td>Inventaire interne</td></tr>
<tr><td>Où cette photo publique a-t-elle été prise ?</td><td>Google Earth, imagerie historique</td><td>Cartes topographiques</td></tr>
</tbody></table>
<p>L'outil automatise ; il ne remplace ni la vérification, ni le jugement, ni la responsabilité de l'analyste.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Commencer par la question et le mandat.</li><li>Préférer la méthode la plus simple qui suffit.</li><li>Vérifier manuellement les résultats automatisés.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel principe guide le choix d\'un outil ?', ['Le plus récent', 'La question posée et le mandat', 'Le plus cher', 'Le plus populaire'], 1, 'L\'outil découle de la question.'],
        ['Les résultats d\'un outil automatisé doivent être :', ['Publiés tels quels', 'Vérifiés manuellement', 'Effacés', 'Ignorés'], 1, 'L\'automatisation produit aussi des erreurs.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : monter un laboratoire d\'entraînement isolé', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Pour s'exercer légalement à l'analyse réseau et forensique, on construit un <strong>laboratoire isolé</strong>.</p>
<h3>Architecture minimale</h3>
<ul><li>Un ordinateur hôte avec un logiciel de virtualisation ;</li><li>Des machines virtuelles connectées à un <strong>réseau interne isolé</strong> (sans accès à Internet ni au réseau de l'organisation) ;</li><li>Des <strong>instantanés</strong> pour revenir à un état propre ;</li><li>Des jeux de données d'entraînement publiés à des fins pédagogiques (captures réseau d'exemple, images disque de démonstration fournies par des projets éducatifs).</li></ul>
<h3>Règles du laboratoire</h3>
<ol><li>Aucune donnée réelle de personnes ;</li><li>Aucun échantillon malveillant hors d'un environnement spécifiquement conçu et autorisé ;</li><li>Documentation de chaque exercice.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>L'isolement réseau est la première règle.</li><li>Les instantanés permettent de recommencer sans risque.</li><li>Utiliser des jeux de données pédagogiques publiés.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle est la première règle d\'un laboratoire d\'entraînement ?', ['La puissance de calcul', 'L\'isolement réseau', 'Le nombre d\'écrans', 'La rapidité de connexion'], 1, 'Sans isolement, un exercice peut déborder sur des systèmes réels.'],
        ['À quoi servent les instantanés (snapshots) ?', ['À photographier l\'écran', 'À revenir à un état propre de la machine virtuelle', 'À accélérer Internet', 'À chiffrer les e-mails'], 1, 'Ils permettent de recommencer un exercice dans les mêmes conditions.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : entretien et chaîne de responsabilité du matériel', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les équipements de terrain sont des actifs sensibles : leur perte peut exposer des données et des personnes.</p>
<h3>Bonnes pratiques de gestion</h3>
<ul><li><strong>Inventaire</strong> nominatif (numéro de série, affectataire, date de sortie et de retour) ;</li><li><strong>Chiffrement</strong> des cartes mémoire et appareils qui le permettent ;</li><li><strong>Effacement</strong> des données de mission au retour, après archivage réglementaire ;</li><li><strong>Contrôle</strong> du respect des réglementations locales (photographie de sites, radiocommunications, drones) avant chaque mission.</li></ul>
<div class="note">Dans de nombreux pays, l'usage de drones et de certains équipements radio est soumis à autorisation préalable. Se renseigner auprès des autorités de l'aviation civile et de régulation des télécommunications.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un matériel perdu est un incident de sécurité.</li><li>Inventaire et chiffrement réduisent le risque.</li><li>La réglementation locale prime.</li></ul></div>
HTML]],
      'quiz' => [
        ['La perte d\'un appareil de terrain doit être traitée comme :', ['Un simple oubli', 'Un incident de sécurité', 'Un événement sans importance', 'Une faute pénale de l\'employé'], 1, 'Elle peut exposer des données.'],
        ['Avant d\'utiliser un drone en mission, il faut :', ['Rien de particulier', 'Vérifier la réglementation et obtenir les autorisations nécessaires', 'Le peindre', 'Désactiver le GPS'], 1, 'L\'usage des drones est souvent soumis à autorisation.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : l\'éthique face aux outils de surveillance commerciale', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Des enquêtes publiées par des laboratoires de recherche (par exemple le Citizen Lab de l'Université de Toronto) et par des consortiums de journalistes ont documenté l'usage abusif de logiciels espions commerciaux contre des journalistes, des avocats et des défenseurs des droits humains, y compris en Afrique.</p>
<h3>Ce que ces affaires enseignent</h3>
<ul><li>Un outil « réservé aux autorités » peut être détourné en l'absence de contrôle ;</li><li>Les victimes sont souvent celles qui exercent un contre-pouvoir ;</li><li>La responsabilité concerne aussi les vendeurs et les intermédiaires.</li></ul>
<h3>Position de l'Académie</h3>
<p>AFRICA'SPY n'enseigne l'usage d'aucun logiciel espion. Ces affaires sont étudiées pour comprendre les risques, renforcer la protection des personnes exposées et nourrir le débat sur le contrôle démocratique.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les abus documentés justifient des garde-fous stricts.</li><li>Protéger les personnes à risque fait partie du métier.</li><li>L'éthique s'applique à toute la chaîne.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qui a documenté publiquement des abus de logiciels espions commerciaux ?', ['Des laboratoires de recherche et des journalistes', 'Personne', 'Uniquement les fabricants', 'Les opérateurs téléphoniques'], 0, 'Le Citizen Lab et des consortiums de journalistes notamment.'],
        ['Pourquoi étudier ces affaires dans un cursus éthique ?', ['Pour apprendre à utiliser ces logiciels', 'Pour comprendre les risques et protéger les personnes exposées', 'Pour les vendre', 'Par curiosité'], 1, 'L\'objectif est défensif et éthique.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-F101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : modèles nationaux d\'intelligence économique', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Plusieurs pays ont structuré une politique publique d'intelligence économique. On cite souvent :</p>
<ul><li>le <strong>Japon</strong>, avec une coordination historique entre l'État et les grandes entreprises pour la veille technologique ;</li><li>la <strong>France</strong>, dont le rapport Martre (1994) a popularisé le concept et qui dispose d'un service dédié à la sécurité économique ;</li><li>les <strong>États-Unis</strong>, avec un dispositif de contrôle des investissements étrangers dans les secteurs sensibles.</li></ul>
<h3>Et en Afrique ?</h3>
<p>Plusieurs pays (notamment le Maroc, le Sénégal ou la Côte d'Ivoire) ont lancé des formations, des cellules ou des réflexions nationales en intelligence économique. Les enjeux prioritaires sont la valorisation des ressources, la protection des entreprises nationales et l'accompagnement à l'export dans la ZLECAf.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>L'IE est aussi une politique publique.</li><li>Chaque modèle reflète une culture économique.</li><li>L'Afrique construit ses propres dispositifs.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel rapport a popularisé le concept d\'intelligence économique en France ?', ['Le rapport Martre (1994)', 'Le rapport Brundtland', 'Le rapport Meadows', 'Le Livre blanc de 2050'], 0, 'Le rapport Martre a été publié en 1994.'],
        ['Quel enjeu est prioritaire pour l\'IE africaine dans la ZLECAf ?', ['La réduction des échanges', 'L\'accompagnement à l\'export et la protection des entreprises', 'La fermeture des frontières', 'L\'abandon des ressources'], 1, 'L\'IE accompagne la conquête de marchés.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : outils et sources de veille gratuits', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Une veille efficace peut être construite avec des moyens modestes.</p>
<table><thead><tr><th>Besoin</th><th>Ressources ouvertes</th></tr></thead><tbody>
<tr><td>Commerce et prix</td><td>Statistiques douanières nationales, bases de l'OMC et de la CNUCED, Trade Map (ITC)</td></tr>
<tr><td>Brevets</td><td>Bases de l'OMPI (Patentscope), offices OAPI et ARIPO</td></tr>
<tr><td>Réglementation</td><td>Journaux officiels, sites de la CEDEAO, de l'UEMOA, de la CEMAC, du secrétariat de la ZLECAf</td></tr>
<tr><td>Économie</td><td>Rapports de la Banque africaine de développement, du FMI, de la Banque mondiale</td></tr>
<tr><td>Actualité sectorielle</td><td>Alertes de moteurs de recherche, flux RSS, newsletters professionnelles</td></tr>
</tbody></table>
<h3>Organiser le flux</h3>
<p>Un agrégateur RSS, un tableur de qualification des sources et un rendez-vous hebdomadaire de 30 minutes suffisent pour démarrer.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les sources institutionnelles sont gratuites et fiables.</li><li>La régularité compte plus que la quantité.</li><li>Qualifier chaque source avant de l'ajouter.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle base permet de rechercher des brevets au niveau international ?', ['Patentscope (OMPI)', 'Shodan', 'Wireshark', 'Trade Map'], 0, 'L\'OMPI propose Patentscope.'],
        ['Que suffit-il pour démarrer une veille ?', ['Un budget important', 'Un agrégateur, un tableur de qualification et un rendez-vous régulier', 'Une équipe de dix personnes', 'Un logiciel d\'espionnage'], 1, 'La méthode prime sur les moyens.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : protéger ses marques et son image', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La <strong>réputation</strong> est un actif économique. Elle se protège juridiquement et se surveille.</p>
<h3>Protection juridique</h3>
<ul><li>Enregistrer ses marques auprès de l'OAPI (un dépôt couvre les 17 États membres) ou de l'ARIPO et des offices nationaux concernés ;</li><li>Enregistrer les noms de domaine correspondants ;</li><li>Réserver ses noms sur les principaux réseaux sociaux.</li></ul>
<h3>Gestion des atteintes</h3>
<ol><li>Détecter (veille d'image) ;</li><li>Qualifier : critique légitime, erreur, ou campagne coordonnée ?</li><li>Répondre de façon proportionnée : correction factuelle, droit de réponse, action juridique en cas de diffamation ou de contrefaçon.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un dépôt OAPI couvre tous les États membres.</li><li>Toute critique n'est pas une attaque.</li><li>La réponse doit être proportionnée et factuelle.</li></ul></div>
HTML]],
      'quiz' => [
        ['Un dépôt de marque auprès de l\'OAPI couvre :', ['Un seul pays', 'L\'ensemble des États membres de l\'OAPI', 'Le monde entier', 'Uniquement l\'Europe'], 1, 'L\'OAPI fonctionne comme un office unique pour ses membres.'],
        ['Face à une critique en ligne, la première étape est :', ['Porter plainte immédiatement', 'Qualifier la situation', 'Supprimer ses comptes', 'Répondre agressivement'], 1, 'Il faut distinguer critique légitime, erreur et campagne.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : la méthode des scénarios', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La prospective par scénarios, popularisée notamment par les travaux de la Shell dans les années 1970, aide les décideurs à se préparer à plusieurs futurs plutôt qu'à parier sur un seul.</p>
<h3>Méthode des deux axes</h3>
<ol><li>Identifier les facteurs qui influencent la question ;</li><li>Retenir les deux incertitudes <strong>les plus importantes et les plus incertaines</strong> ;</li><li>Les croiser pour obtenir quatre scénarios contrastés ;</li><li>Nommer et décrire chaque scénario ; identifier ses indicateurs ;</li><li>Tester les options stratégiques dans chacun.</li></ol>
<div class="note"><strong>Exemple fictif.</strong> Axes : « intégration régionale rapide / lente » et « prix des matières premières hauts / bas ». Quatre scénarios en découlent, chacun avec ses implications pour une PME exportatrice.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un scénario n'est pas une prévision.</li><li>Choisir des incertitudes réellement indépendantes.</li><li>Les options robustes fonctionnent dans plusieurs scénarios.</li></ul></div>
HTML]],
      'quiz' => [
        ['Dans la méthode des deux axes, combien de scénarios obtient-on ?', ['Deux', 'Trois', 'Quatre', 'Dix'], 2, 'Le croisement de deux axes donne quatre quadrants.'],
        ['Qu\'est-ce qu\'une option stratégique robuste ?', ['Une option la moins chère', 'Une option qui fonctionne dans plusieurs scénarios', 'Une option secrète', 'Une option unique'], 1, 'Elle résiste à l\'incertitude.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-F201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : l\'architecture africaine de paix et de sécurité', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>L'<strong>Architecture africaine de paix et de sécurité (APSA)</strong>, mise en place par l'Union africaine à partir du Protocole de 2002 relatif au Conseil de paix et de sécurité, repose sur plusieurs piliers :</p>
<ul><li>le <strong>Conseil de paix et de sécurité</strong> (CPS), organe décisionnel ;</li><li>le <strong>Groupe des sages</strong>, chargé de la diplomatie préventive ;</li><li>le <strong>Système continental d'alerte rapide</strong> (CEWS) ;</li><li>la <strong>Force africaine en attente</strong> ;</li><li>le <strong>Fonds pour la paix</strong>.</li></ul>
<p>Les communautés économiques régionales et les mécanismes régionaux sont associés au dispositif selon un principe de <strong>subsidiarité</strong>.</p>
<h3>Agenda 2063</h3>
<p>Adopté en 2015, l'Agenda 2063 fixe la vision de long terme de l'Union africaine, dont l'initiative « Faire taire les armes ».</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le CPS est l'organe décisionnel de l'APSA.</li><li>L'alerte précoce est un pilier institutionnel.</li><li>Le niveau régional joue un rôle central.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel pilier de l\'APSA est chargé de la diplomatie préventive ?', ['Le Fonds pour la paix', 'Le Groupe des sages', 'La Force africaine en attente', 'La BAD'], 1, 'Le Groupe des sages mène la diplomatie préventive.'],
        ['En quelle année l\'Agenda 2063 a-t-il été adopté ?', ['1963', '2002', '2015', '2063'], 2, 'L\'Agenda 2063 a été adopté en 2015.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : données ouvertes sur les conflits', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>L'analyse quantitative des conflits s'appuie sur des bases de données publiques et documentées :</p>
<ul><li><strong>ACLED</strong> (Armed Conflict Location & Event Data) : événements de violence politique et de manifestations, géolocalisés et datés ;</li><li><strong>UCDP</strong> (Uppsala Conflict Data Program) : conflits armés et victimes, avec une longue série historique ;</li><li>Rapports d'organisations humanitaires et de l'ONU (OCHA, HCR) sur les déplacements de populations.</li></ul>
<h3>Précautions d'usage</h3>
<ul><li>Lire la méthodologie : définitions, sources, délais de mise à jour ;</li><li>Ne pas confondre augmentation des événements et augmentation de la couverture médiatique ;</li><li>Croiser avec des sources qualitatives locales.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les bases ouvertes permettent d'objectiver les tendances.</li><li>Chaque base a ses définitions : ne pas mélanger sans précaution.</li><li>Les chiffres doivent être contextualisés.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que recense principalement ACLED ?', ['Les brevets', 'Les événements de violence politique et de manifestations', 'Les cours de bourse', 'Les naissances'], 1, 'ACLED est une base d\'événements géolocalisés.'],
        ['Une hausse des événements dans une base peut aussi refléter :', ['Une erreur informatique uniquement', 'Une meilleure couverture médiatique', 'Une baisse de la violence', 'Rien d\'autre'], 1, 'La couverture des sources influence les données.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : risque pays et développement durable', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les investisseurs intègrent de plus en plus les critères <strong>ESG</strong> (environnementaux, sociaux et de gouvernance) dans l'évaluation du risque pays et projet.</p>
<h3>Référentiels utiles</h3>
<ul><li>Les <strong>Normes de performance de la SFI</strong> (Société financière internationale, groupe Banque mondiale) sur la durabilité environnementale et sociale ;</li><li>Les <strong>Principes directeurs des Nations unies</strong> relatifs aux entreprises et aux droits de l'homme (2011) ;</li><li>Les engagements de transparence sur les industries extractives (Initiative pour la transparence dans les industries extractives, ITIE), à laquelle adhèrent de nombreux pays africains.</li></ul>
<h3>Pourquoi c'est un enjeu de risque</h3>
<p>Un projet mal accepté localement (conflit foncier, atteinte environnementale) s'expose à des blocages, des recours et une perte de réputation : le risque social est un risque opérationnel.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>L'acceptation locale conditionne la viabilité d'un projet.</li><li>Des référentiels internationaux existent et sont exigés par les financeurs.</li><li>ESG et risque pays se recoupent largement.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que signifie ESG ?', ['Économie, Sécurité, Géopolitique', 'Environnemental, Social et de Gouvernance', 'Énergie, Services, Gaz', 'Éthique, Stratégie, Garantie'], 1, 'Trois familles de critères extra-financiers.'],
        ['Pourquoi le risque social est-il un risque opérationnel ?', ['Il ne l\'est pas', 'Un projet mal accepté peut être bloqué ou contesté', 'Il ne concerne que la communication', 'Il est couvert par l\'assurance'], 1, 'Conflits et recours peuvent arrêter un projet.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
];
