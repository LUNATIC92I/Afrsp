<?php
/** MODULE D : CYBERESPIONNAGE ET RENSEIGNEMENT NUMÉRIQUE */
return [
[
  'code' => 'AFS-D101', 'category' => 'D', 'slug' => 'renseignement-numerique-osint-avance',
  'title' => 'Renseignement numérique et OSINT avancé',
  'subtitle' => 'Cyberespionnage, empreintes numériques et investigation sur métadonnées.',
  'summary' => 'Comprendre les mécanismes du cyberespionnage, conduire des recherches OSINT avancées, analyser des empreintes numériques et exploiter les métadonnées, exclusivement sur données synthétiques et dans des scénarios autorisés.',
  'level' => 'avance', 'hours' => 20, 'featured' => true,
  'description' => '<p>Le numérique est devenu le principal terrain de collecte et d\'affrontement informationnel. Cette formation donne aux analystes la culture technique nécessaire pour <strong>comprendre les menaces</strong> et <strong>mener des investigations ouvertes rigoureuses</strong>.</p><p><strong>Cadre pédagogique :</strong> tous les exercices utilisent des environnements isolés, des jeux de données synthétiques fournis par l\'Académie et des scénarios fictifs. Aucune technique d\'intrusion n\'est enseignée.</p>',
  'objectives' => "Expliquer les modes opératoires du cyberespionnage\nConduire une recherche OSINT avancée méthodique et légale\nAnalyser une empreinte numérique organisationnelle\nExtraire et interpréter des métadonnées de fichiers\nDocumenter une investigation de façon reproductible",
  'skills' => "Utiliser des opérateurs de recherche avancés\nPivoter entre indicateurs (domaine, IP, certificat)\nLire les métadonnées EXIF et documentaires\nRéduire sa propre empreinte numérique",
  'prerequisites' => "Disciplines de collecte (recommandé)\nBonne pratique de l'informatique (navigateur, fichiers, tableur)",
  'chapters' => [
    [
      'title' => 'Introduction au cyberespionnage',
      'summary' => 'Acteurs, objectifs et chaîne d\'attaque.',
      'lessons' => [
        ['title' => 'Briefing vidéo : le terrain numérique', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Panorama visuel des menaces numériques et de la posture de l\'analyste.</p>'],
        ['title' => 'Comprendre le cyberespionnage', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le cyberespionnage désigne l'accès non autorisé à des systèmes d'information pour y dérober des données, de façon discrète et souvent prolongée. Il se distingue du cybercrime (motivation financière directe) et du sabotage (destruction), même si les frontières sont poreuses.</p>
<h3>Les acteurs</h3>
<ul><li>Groupes liés à des États, souvent désignés par les éditeurs de sécurité sous le terme <strong>APT</strong> (<em>Advanced Persistent Threat</em>) ;</li><li>Mercenaires numériques et sociétés de surveillance commerciale ;</li><li>Groupes criminels revendant des accès.</li></ul>
<h3>La chaîne d'attaque (modèle conceptuel)</h3>
<ol><li>Reconnaissance (souvent en sources ouvertes) ;</li><li>Accès initial (hameçonnage ciblé, identifiants volés) ;</li><li>Installation et persistance ;</li><li>Mouvement latéral ;</li><li>Exfiltration.</li></ol>
<p>Connaître cette chaîne sert à <strong>placer des défenses à chaque étape</strong> : moins d'informations exposées publiquement, authentification forte, détection des comportements anormaux, segmentation des réseaux, surveillance des flux sortants.</p>
<div class="note">Le cadre de référence MITRE ATT&amp;CK, librement consultable, décrit de façon structurée les tactiques et techniques observées. Il est utilisé ici comme langage commun de défense.</div>
HTML],
      ],
      'quiz' => [
        ['Que signifie l\'acronyme APT ?', ['Advanced Persistent Threat', 'Automatic Protection Tool', 'African Police Taskforce', 'Applied Penetration Test'], 0, 'Désigne des groupes sophistiqués et persistants.'],
        ['Pourquoi étudier la chaîne d\'attaque ?', ['Pour mener des attaques', 'Pour placer des défenses à chaque étape', 'Pour obtenir une certification de hacker', 'Par curiosité historique'], 1, 'C\'est un outil de conception défensive.'],
        ['Quelle étape repose souvent sur les sources ouvertes ?', ['La reconnaissance', 'L\'exfiltration', 'La persistance', 'Le mouvement latéral'], 0, 'D\'où l\'intérêt de réduire son exposition publique.'],
      ],
    ],
    [
      'title' => 'OSINT avancé',
      'summary' => 'Opérateurs, pivots et hygiène de l\'enquêteur.',
      'lessons' => [
        ['title' => 'Techniques de recherche avancées', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>L'OSINT avancé se caractérise par la précision des requêtes, la capacité à <strong>pivoter</strong> d'un indice à l'autre et une rigoureuse hygiène opérationnelle.</p>
<h3>Opérateurs de recherche</h3>
<table><thead><tr><th>Opérateur</th><th>Usage</th><th>Exemple</th></tr></thead><tbody>
<tr><td><code>site:</code></td><td>Limiter à un domaine</td><td><code>site:gouv.sn rapport annuel</code></td></tr>
<tr><td><code>filetype:</code></td><td>Type de document</td><td><code>filetype:pdf budget santé 2025</code></td></tr>
<tr><td><code>"..."</code></td><td>Expression exacte</td><td><code>"appel d'offres" "fibre optique"</code></td></tr>
<tr><td><code>-</code></td><td>Exclure un terme</td><td><code>jaguar -voiture</code></td></tr>
</tbody></table>
<h3>Le pivot</h3>
<p>Un indicateur en révèle d'autres : un nom de domaine mène à son historique d'enregistrement public, à ses certificats TLS (journaux de transparence), aux autres sites hébergés sur la même infrastructure. Chaque pivot est noté dans un journal d'enquête.</p>
<h3>Hygiène de l'enquêteur</h3>
<ul><li>Environnement de travail dédié et séparé de ses comptes personnels ;</li><li>Aucune interaction avec les cibles (pas de « like », pas de message, pas de demande d'ami) ;</li><li>Respect des conditions d'utilisation des plateformes ;</li><li>Collecte minimale de données personnelles.</li></ul>
HTML],
      ],
      'quiz' => [
        ['Que permet l\'opérateur filetype: ?', ['Exclure un mot', 'Limiter la recherche à un type de fichier', 'Rechercher une image', 'Traduire une page'], 1, 'Ex. filetype:pdf.'],
        ['Qu\'appelle-t-on « pivoter » en OSINT ?', ['Changer de moteur de recherche', 'Passer d\'un indicateur à d\'autres indicateurs liés', 'Tourner l\'écran', 'Abandonner une piste'], 1, 'Domaine puis certificat puis infrastructure, etc.'],
        ['Quelle règle d\'hygiène est essentielle ?', ['Contacter la cible pour vérifier', 'Ne pas interagir avec les cibles', 'Utiliser son compte personnel', 'Collecter un maximum de données personnelles'], 1, 'L\'enquêteur observe sans interagir.'],
      ],
    ],
    [
      'title' => 'Analyse des empreintes numériques',
      'summary' => 'Ce qu\'une organisation expose, et comment le réduire.',
      'lessons' => [
        ['title' => 'Empreinte numérique organisationnelle', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>L'empreinte numérique d'une organisation est l'ensemble des traces publiquement observables : sites, sous-domaines, adresses e-mail, profils des employés, documents publiés, dépôts de code, offres d'emploi détaillant les technologies utilisées...</p>
<h3>Pourquoi l'analyser ?</h3>
<p>Un attaquant commence par là. L'analyser <strong>pour sa propre organisation</strong> (ou pour un client qui l'a mandaté par écrit) permet de réduire la surface d'attaque.</p>
<h3>Grille d'audit d'exposition</h3>
<ul><li><strong>Domaines et sous-domaines</strong> : services oubliés, environnements de test exposés ;</li><li><strong>Documents publics</strong> : métadonnées révélant noms d'utilisateurs, versions logicielles ;</li><li><strong>Personnes</strong> : organigramme reconstituable, formats d'adresses e-mail ;</li><li><strong>Fuites</strong> : identifiants apparus dans des fuites publiques connues (à vérifier via des services de notification légitimes).</li></ul>
<div class="warning">Un audit d'exposition sur une organisation tierce nécessite un <strong>mandat écrit</strong>. Sans autorisation, même une collecte « passive » peut poser problème juridiquement et déontologiquement.</div>
HTML],
      ],
      'quiz' => [
        ['Qu\'est-ce que l\'empreinte numérique d\'une organisation ?', ['Son logo', 'L\'ensemble de ses traces publiquement observables', 'Son chiffre d\'affaires', 'Son adresse postale'], 1, 'Sites, documents, profils, sous-domaines...'],
        ['Un audit d\'exposition sur une organisation tierce nécessite :', ['Rien du tout', 'Un mandat écrit', 'Un compte premium', 'L\'accord d\'un concurrent'], 1, 'Sans autorisation, la démarche est contestable.'],
        ['Pourquoi les offres d\'emploi peuvent-elles être sensibles ?', ['Elles révèlent parfois les technologies utilisées', 'Elles sont toujours fausses', 'Elles contiennent des mots de passe', 'Elles sont illégales'], 0, 'Elles informent un attaquant sur l\'environnement technique.'],
      ],
    ],
    [
      'title' => 'Métadonnées et investigation numérique',
      'summary' => 'Lire les données cachées des fichiers.',
      'lessons' => [
        ['title' => 'Exploiter les métadonnées', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Les métadonnées sont des « données sur les données » : auteur d'un document, logiciel utilisé, dates de création et de modification, modèle d'appareil photo, parfois coordonnées GPS.</p>
<h3>Types courants</h3>
<table><thead><tr><th>Format</th><th>Métadonnées typiques</th></tr></thead><tbody>
<tr><td>Photo (EXIF)</td><td>Appareil, date, réglages, éventuellement GPS</td></tr>
<tr><td>PDF</td><td>Auteur, créateur, producteur, dates</td></tr>
<tr><td>Bureautique</td><td>Auteur, dernier modificateur, révisions, commentaires</td></tr>
</tbody></table>
<h3>Précautions d'interprétation</h3>
<ul><li>Les métadonnées peuvent être <strong>absentes</strong> (supprimées par les réseaux sociaux) ou <strong>falsifiées</strong> ;</li><li>L'horloge d'un appareil peut être mal réglée ;</li><li>Une métadonnée est un indice, jamais une preuve isolée.</li></ul>
<h3>Préservation de la preuve</h3>
<p>Dans un cadre d'enquête autorisée, on travaille sur une <strong>copie</strong>, on calcule l'<strong>empreinte cryptographique</strong> (SHA-256) de l'original et on consigne chaque manipulation (chaîne de traçabilité).</p>
<div class="callout"><strong>Réflexe défensif :</strong> nettoyez les métadonnées des documents que votre organisation publie.</div>
HTML],
      ],
      'quiz' => [
        ['Que désigne EXIF ?', ['Un format de métadonnées pour les images', 'Un logiciel espion', 'Un protocole réseau', 'Une norme de chiffrement'], 0, 'EXIF stocke les informations de prise de vue.'],
        ['Pourquoi calculer l\'empreinte SHA-256 d\'un fichier original ?', ['Pour le compresser', 'Pour prouver qu\'il n\'a pas été modifié', 'Pour le chiffrer', 'Pour le renommer'], 1, 'L\'empreinte garantit l\'intégrité.'],
        ['Une métadonnée de date isolée constitue :', ['Une preuve irréfutable', 'Un indice à recouper', 'Une information sans valeur', 'Une donnée confidentielle'], 1, 'Elle peut être erronée ou falsifiée.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Laboratoire sur données synthétiques.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Opérateurs de recherche, grille d\'audit d\'exposition et procédure de préservation des fichiers.</p>'],
        ['title' => 'Exercice : audit d\'exposition d\'une organisation fictive', 'type' => 'exercise', 'minutes' => 50, 'content' => <<<'HTML'
<p><strong>Environnement :</strong> dossier de données synthétiques « Kilimanjaro Logistics » (organisation fictive) : liste de sous-domaines, trois documents PDF, cinq profils professionnels fictifs, une offre d'emploi.</p>
<ol><li>Recensez toutes les informations exploitables par un attaquant.</li><li>Déduisez le format probable des adresses e-mail.</li><li>Identifiez les métadonnées sensibles des documents.</li><li>Classez les expositions par gravité.</li><li>Rédigez cinq recommandations de réduction de l'exposition.</li></ol>
<div class="note">Toutes les données sont fictives. Ne transposez jamais l'exercice sur une organisation réelle sans mandat écrit.</div>
HTML],
        ['title' => 'Étude de cas : le document trop bavard', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Un ministère publie en ligne un rapport PDF. Ses métadonnées révèlent le nom d'utilisateur réseau de l'auteur, la version d'un logiciel obsolète et le chemin d'un serveur interne. Quelques semaines plus tard, une campagne d'hameçonnage très ciblée vise précisément ce service.</p>
<ol><li>Quelles informations ont pu servir à la reconnaissance ?</li><li>Quelle procédure de publication aurait évité la fuite ?</li><li>Comment sensibiliser les équipes de communication ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-D201', 'category' => 'D', 'slug' => 'cyber-threat-intelligence-defense',
  'title' => 'Cyber Threat Intelligence et défense des communications',
  'subtitle' => 'CTI, infrastructures, communications sécurisées et détection.',
  'summary' => 'Produire et exploiter du renseignement sur les menaces cyber (CTI), analyser des infrastructures numériques, sécuriser les communications et détecter les activités suspectes à partir de journaux synthétiques.',
  'level' => 'avance', 'hours' => 20, 'featured' => false,
  'description' => '<p>La Cyber Threat Intelligence transforme des données techniques en <strong>décisions de défense</strong>. Cette formation fait le lien entre analystes de renseignement et équipes techniques (SOC, CSIRT), en couvrant les niveaux stratégique, opérationnel et tactique de la CTI.</p><p>Les travaux pratiques reposent sur des journaux et indicateurs synthétiques fournis par l\'Académie.</p>',
  'objectives' => "Définir la CTI et ses trois niveaux\nUtiliser le modèle en diamant et MITRE ATT&CK\nAnalyser une infrastructure numérique à partir de données publiques\nMettre en place des communications sécurisées\nDétecter des activités suspectes dans des journaux",
  'skills' => "Structurer un rapport CTI\nQualifier des indicateurs de compromission\nChoisir des outils de communication chiffrée\nLire des journaux d'authentification",
  'prerequisites' => "Renseignement numérique et OSINT avancé\nNotions de réseaux informatiques (adresse IP, DNS)",
  'chapters' => [
    [
      'title' => 'Intelligence sur les menaces : CTI',
      'summary' => 'Niveaux, cycle et modèles d\'analyse.',
      'lessons' => [
        ['title' => 'Briefing vidéo : de la donnée à la décision', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les niveaux de la CTI et leurs destinataires.</p>'],
        ['title' => 'Les fondamentaux de la CTI', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>La CTI applique le cycle du renseignement au domaine cyber. Elle répond à des questions comme : <em>qui pourrait nous viser, comment, et que devons-nous surveiller ?</em></p>
<h3>Trois niveaux</h3>
<table><thead><tr><th>Niveau</th><th>Destinataire</th><th>Contenu</th></tr></thead><tbody>
<tr><td>Stratégique</td><td>Direction</td><td>Tendances, acteurs, impacts métier</td></tr>
<tr><td>Opérationnel</td><td>Responsables sécurité</td><td>Campagnes, modes opératoires (TTP)</td></tr>
<tr><td>Tactique</td><td>SOC / équipes techniques</td><td>Indicateurs de compromission (IoC)</td></tr>
</tbody></table>
<h3>Le modèle en diamant</h3>
<p>Chaque événement d'intrusion est décrit par quatre sommets reliés : <strong>adversaire</strong>, <strong>capacité</strong>, <strong>infrastructure</strong>, <strong>victime</strong>. Connaître un sommet permet de rechercher les autres.</p>
<h3>La pyramide de la douleur</h3>
<p>Bloquer une adresse IP gêne peu un attaquant (il en change facilement) ; détecter ses <strong>TTP</strong> (comportements) l'oblige à modifier ses méthodes, ce qui est beaucoup plus coûteux pour lui. La CTI vise donc le haut de la pyramide.</p>
HTML],
      ],
      'quiz' => [
        ['Les indicateurs de compromission (IoC) relèvent principalement du niveau :', ['Stratégique', 'Opérationnel', 'Tactique', 'Politique'], 2, 'Ils servent directement aux équipes techniques.'],
        ['Quels sont les quatre sommets du modèle en diamant ?', ['Adversaire, capacité, infrastructure, victime', 'Qui, quoi, où, quand', 'Prévenir, détecter, réagir, rétablir', 'Source, date, lieu, auteur'], 0, 'Ils structurent l\'analyse d\'un événement.'],
        ['Selon la pyramide de la douleur, qu\'est-ce qui gêne le plus un attaquant ?', ['Le blocage d\'une adresse IP', 'La détection de ses TTP', 'Le blocage d\'une empreinte de fichier', 'Le changement de mot de passe d\'un utilisateur'], 1, 'Changer de comportement est coûteux pour l\'adversaire.'],
      ],
    ],
    [
      'title' => 'Analyse des infrastructures numériques',
      'summary' => 'DNS, IP, certificats : lire Internet.',
      'lessons' => [
        ['title' => 'Comprendre une infrastructure', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Toute activité en ligne repose sur une infrastructure : noms de domaine, serveurs, adresses IP, certificats, hébergeurs. Ces éléments laissent des traces publiques exploitables légalement.</p>
<h3>Sources ouvertes d'infrastructure</h3>
<ul><li><strong>WHOIS / RDAP</strong> : informations d'enregistrement (souvent masquées) ;</li><li><strong>DNS historique</strong> : évolution des résolutions d'un domaine ;</li><li><strong>Journaux de transparence des certificats</strong> : certificats émis pour un domaine ;</li><li><strong>Moteurs d'exposition</strong> recensant les services visibles sur Internet ;</li><li><strong>Registres régionaux</strong> (AFRINIC pour l'Afrique) : attribution des blocs d'adresses.</li></ul>
<h3>Exemple de raisonnement (données fictives)</h3>
<p>Un domaine d'hameçonnage imitant une banque est hébergé sur une IP qui sert aussi trois autres domaines récemment enregistrés, imitant d'autres institutions financières. Hypothèse : une même campagne. Action : signalement aux CSIRT et aux registrars, alerte aux clients.</p>
<div class="note">L'analyse d'infrastructure reste passive : on consulte des registres publics, on n'interagit pas avec les systèmes d'un tiers au-delà d'un usage normal.</div>
HTML],
      ],
      'quiz' => [
        ['Quel registre attribue les adresses IP en Afrique ?', ['ICANN Africa', 'AFRINIC', 'AFRIPOL', 'AFREXIMBANK'], 1, 'AFRINIC est le registre Internet régional africain.'],
        ['Les journaux de transparence des certificats permettent :', ['De lire les messages chiffrés', 'D\'identifier les certificats émis pour un domaine', 'De pirater un site', 'De connaître le mot de passe d\'un serveur'], 1, 'Ils sont publics et utiles pour découvrir des domaines.'],
        ['Plusieurs domaines frauduleux sur une même IP suggèrent :', ['Une coïncidence certaine', 'Une possible campagne commune', 'Une erreur de l\'hébergeur', 'Un site légitime'], 1, 'C\'est une hypothèse à confirmer.'],
      ],
    ],
    [
      'title' => 'Sécurité des communications',
      'summary' => 'Chiffrement, authentification et bonnes pratiques.',
      'lessons' => [
        ['title' => 'Communiquer en sécurité', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Protéger ses communications est un droit et une nécessité professionnelle : journalistes, avocats, ONG, entreprises et administrations y sont confrontés.</p>
<h3>Notions clés</h3>
<ul><li><strong>Chiffrement en transit</strong> (TLS) : protège le trajet entre vous et le serveur ;</li><li><strong>Chiffrement de bout en bout</strong> : seuls l'émetteur et le destinataire peuvent lire ;</li><li><strong>Vérification des clés</strong> : comparer les codes de sécurité pour éviter l'interposition ;</li><li><strong>Métadonnées</strong> : même chiffrées, les communications révèlent qui parle à qui et quand.</li></ul>
<h3>Bonnes pratiques</h3>
<ol><li>Authentification multifacteur sur toutes les messageries professionnelles ;</li><li>Applications à jour, verrouillage des appareils ;</li><li>Messages éphémères pour les échanges sensibles ;</li><li>Séparation des usages personnels et professionnels ;</li><li>Prudence avec les réseaux Wi-Fi publics.</li></ol>
<div class="callout">La sécurité d'une communication dépend du maillon le plus faible : un téléphone non verrouillé annule le meilleur chiffrement.</div>
HTML],
      ],
      'quiz' => [
        ['Le chiffrement de bout en bout garantit que :', ['Le serveur peut lire les messages', 'Seuls l\'émetteur et le destinataire peuvent lire le contenu', 'Les métadonnées sont invisibles', 'Le message ne peut pas être supprimé'], 1, 'Le fournisseur n\'a pas accès au contenu.'],
        ['Même chiffrées, les communications peuvent révéler :', ['Leur contenu exact', 'Des métadonnées (qui, quand)', 'Les mots de passe', 'Rien du tout'], 1, 'Les métadonnées restent souvent visibles.'],
        ['Pourquoi vérifier les codes de sécurité d\'un contact ?', ['Pour accélérer l\'envoi', 'Pour s\'assurer qu\'aucun tiers ne s\'est interposé', 'Pour sauvegarder les messages', 'Par politesse'], 1, 'Cela protège contre l\'attaque de l\'intercepteur.'],
      ],
    ],
    [
      'title' => 'Détection des activités numériques suspectes',
      'summary' => 'Lire des journaux et repérer l\'anormal.',
      'lessons' => [
        ['title' => 'Détecter l\'anomalie', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>La détection consiste à repérer, dans le flux d'événements d'un système <strong>que l'on est autorisé à surveiller</strong>, ce qui s'écarte du comportement normal.</p>
<h3>Signaux typiques dans les journaux d'authentification</h3>
<ul><li>Nombreux échecs de connexion suivis d'un succès ;</li><li>Connexion depuis un pays inhabituel ou à une heure atypique ;</li><li>« Voyage impossible » : deux connexions éloignées géographiquement en peu de temps ;</li><li>Création de règles de transfert automatique dans une messagerie ;</li><li>Volume inhabituel de téléchargements.</li></ul>
<h3>Extrait de journal synthétique</h3>
<pre><code>2026-03-04 02:11:07 user=a.diallo  src=196.0.2.14  result=FAIL
2026-03-04 02:11:09 user=a.diallo  src=196.0.2.14  result=FAIL
2026-03-04 02:11:12 user=a.diallo  src=196.0.2.14  result=SUCCESS
2026-03-04 02:14:40 user=a.diallo  action=create_forward_rule dest=external</code></pre>
<p>Lecture : connexion nocturne après échecs, puis création d'une règle de transfert vers l'extérieur : scénario classique de compromission de messagerie. Réponse : suspendre la session, réinitialiser le mot de passe, supprimer la règle, enquêter sur l'étendue.</p>
<div class="note">Une alerte n'est pas une preuve : il faut qualifier (vrai ou faux positif) avant d'agir, et documenter.</div>
HTML],
      ],
      'quiz' => [
        ['Qu\'est-ce qu\'un « voyage impossible » ?', ['Une mission annulée', 'Deux connexions trop éloignées géographiquement en peu de temps', 'Un visa refusé', 'Une panne réseau'], 1, 'Indice fort d\'utilisation d\'identifiants par un tiers.'],
        ['Une règle de transfert automatique vers l\'extérieur créée la nuit est :', ['Normale', 'Un signal suspect à qualifier', 'Une mise à jour système', 'Sans importance'], 1, 'C\'est un mode opératoire courant d\'exfiltration.'],
        ['Avant d\'agir sur une alerte, il faut :', ['La transmettre à la presse', 'La qualifier et la documenter', 'L\'ignorer', 'Éteindre tous les serveurs'], 1, 'Distinguer vrai et faux positif.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Rapport CTI et analyse de journaux synthétiques.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Modèle de rapport CTI, modèle en diamant et liste des signaux de détection.</p>'],
        ['title' => 'Exercice : analyser un journal synthétique', 'type' => 'exercise', 'minutes' => 50, 'content' => <<<'HTML'
<p>Le jeu de données fourni (fictif) contient 200 lignes d'authentification d'une organisation imaginaire sur une semaine.</p>
<ol><li>Établissez le profil normal (horaires, origines, volumes).</li><li>Identifiez au moins trois anomalies et qualifiez-les.</li><li>Placez l'incident principal dans le modèle en diamant.</li><li>Associez les comportements observés à des techniques MITRE ATT&amp;CK.</li><li>Rédigez un rapport CTI tactique d'une page avec recommandations.</li></ol>
<pre><code>2026-03-01 08:02:44 user=k.mensah src=41.0.0.21 result=SUCCESS
2026-03-02 23:47:10 user=k.mensah src=203.0.113.9 result=SUCCESS
2026-03-02 23:47:58 user=k.mensah action=mass_download files=412</code></pre>
HTML],
        ['title' => 'Étude de cas : campagne contre le secteur bancaire', 'type' => 'case_study', 'minutes' => 35, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> En trois semaines, quatre banques d'une même zone monétaire signalent des courriels imitant leur autorité de régulation et contenant une pièce jointe piégée. Le CSIRT régional coordonne l'analyse.</p>
<ol><li>Quelles informations partager entre banques, et sous quel format (niveau TLP) ?</li><li>Quels indicateurs tactiques et quels TTP retenir ?</li><li>Quel message stratégique adresser aux directions générales ?</li><li>Quelles mesures de sensibilisation des clients ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
];
