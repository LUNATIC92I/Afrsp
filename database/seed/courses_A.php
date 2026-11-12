<?php
/**
 * MODULE A — FONDEMENTS DU RENSEIGNEMENT
 * Format des questions : [énoncé, [réponses], index_correct, explication]
 */
return [
[
  'code' => 'AFS-A101', 'category' => 'A', 'slug' => 'fondements-du-renseignement',
  'title' => 'Fondements du renseignement',
  'subtitle' => 'Cycle, histoire et méthodes d\'analyse : le socle commun de tous les métiers de l\'intelligence.',
  'summary' => 'Comprendre ce qu\'est le renseignement, d\'où il vient, comment il se produit et comment l\'information brute devient un produit d\'aide à la décision fiable, sourcé et éthique.',
  'level' => 'fondamental', 'hours' => 14, 'featured' => true,
  'description' => '<p>Cette formation pose les bases indispensables à tout parcours AFRICA\'SPY. Elle distingue <strong>l\'information</strong> (donnée brute), <strong>le renseignement</strong> (information évaluée, recoupée et mise en perspective pour un décideur) et <strong>la connaissance</strong> (savoir durable accumulé par une organisation).</p><p>Vous étudierez le cycle du renseignement, les grandes étapes de l\'histoire des services — en accordant une place réelle aux expériences africaines —, la typologie des disciplines de collecte et les méthodes structurées d\'analyse qui permettent de lutter contre les biais cognitifs.</p><p>Tout au long du parcours, le cadre légal et éthique est traité comme une compétence professionnelle à part entière, et non comme une contrainte annexe.</p>',
  'objectives' => "Définir le renseignement et le distinguer de l'information et de la connaissance\nDécrire chaque phase du cycle du renseignement et ses points de défaillance\nSituer les grandes évolutions historiques des services, dont les trajectoires africaines\nIdentifier les principales disciplines de collecte et leurs limites\nAppliquer deux méthodes d'analyse structurée à un problème simple",
  'skills' => "Formuler un besoin en renseignement précis\nÉvaluer une information selon sa source et sa crédibilité\nRédiger une synthèse analytique courte et sourcée\nReconnaître les biais cognitifs courants\nRaisonner en hypothèses concurrentes",
  'prerequisites' => "Aucun prérequis technique\nBonne maîtrise du français écrit\nCuriosité pour l'actualité internationale et africaine",
  'chapters' => [
    [
      'title' => 'Introduction au renseignement',
      'summary' => 'Définitions, finalités et cycle du renseignement.',
      'lessons' => [
        ['title' => 'Briefing vidéo : le métier d\'analyste', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Ce briefing visuel présente en quelques minutes les notions clés de la formation : la différence entre information et renseignement, le cycle de production et les exigences éthiques du métier. Il ne comporte pas de narration : prenez le temps de lire chaque écran.</p>'],
        ['title' => 'Qu\'est-ce que le renseignement ?', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Le renseignement est <strong>une information élaborée pour éclairer une décision</strong>. Il se définit moins par la nature de ses sources que par sa finalité : réduire l'incertitude d'un décideur — chef d'État, dirigeant d'entreprise, responsable de la sécurité d'une organisation — face à une situation qui le concerne.</p>
<h3>Trois niveaux à ne pas confondre</h3>
<table><thead><tr><th>Niveau</th><th>Définition</th><th>Exemple</th></tr></thead><tbody>
<tr><td>Donnée / information</td><td>Fait brut, non évalué</td><td>« Un convoi de camions a été vu près de la frontière. »</td></tr>
<tr><td>Renseignement</td><td>Information évaluée, recoupée, interprétée pour un besoin précis</td><td>« Le convoi correspond probablement à une rotation logistique saisonnière ; aucun indice de menace (confiance modérée). »</td></tr>
<tr><td>Connaissance</td><td>Savoir accumulé et structuré dans la durée</td><td>Base documentaire sur les flux commerciaux transfrontaliers de la région.</td></tr>
</tbody></table>
<h3>Les trois niveaux de décision</h3>
<ul>
<li><strong>Stratégique</strong> : grandes orientations, horizon de plusieurs années (stabilité régionale, dépendances économiques).</li>
<li><strong>Opérationnel</strong> : conduite d'un ensemble d'actions sur une zone ou un secteur.</li>
<li><strong>Tactique</strong> : décision immédiate, sur le terrain ou en situation de crise.</li>
</ul>
<h3>Le cycle du renseignement</h3>
<p>La production suit un cycle classique en cinq phases, qui structure tout le reste du parcours :</p>
<ol>
<li><strong>Orientation</strong> : le décideur exprime un besoin ; l'analyste le traduit en questions précises (<em>besoins en renseignement</em>).</li>
<li><strong>Collecte</strong> : recherche d'informations par des moyens légaux et adaptés.</li>
<li><strong>Exploitation</strong> : tri, traduction, mise en forme, évaluation des sources.</li>
<li><strong>Analyse</strong> : interprétation, recoupement, formulation de jugements avec un niveau de confiance.</li>
<li><strong>Diffusion</strong> : livraison d'un produit clair au bon destinataire, au bon moment, puis retour d'expérience.</li>
</ol>
<div class="callout"><strong>À retenir.</strong> Un renseignement exact mais livré trop tard ou au mauvais destinataire n'a aucune valeur. La pertinence et le délai font partie de la qualité.</div>
<h3>Ce que le renseignement n'est pas</h3>
<p>Le renseignement n'est ni de la surveillance indiscriminée, ni de la propagande, ni une activité au-dessus des lois. Dans un État de droit, il s'exerce dans un cadre légal, sous contrôle, avec des finalités définies. C'est ce cadre qui garantit sa légitimité et, à terme, son efficacité.</p>
HTML],
      ],
      'quiz' => [
        ['Quelle est la caractéristique principale qui distingue le renseignement de la simple information ?', ['Il provient toujours de sources secrètes', 'Il a été évalué et mis en perspective pour éclairer une décision', 'Il est obligatoirement classifié', 'Il concerne uniquement la défense nationale'], 1, 'Le renseignement se définit par sa finalité (aider une décision) et par le travail d\'évaluation, non par le secret de ses sources.'],
        ['Dans le cycle du renseignement, quelle phase consiste à traduire le besoin du décideur en questions précises ?', ['La collecte', 'L\'exploitation', 'L\'orientation', 'La diffusion'], 2, 'L\'orientation transforme une préoccupation générale en besoins en renseignement exploitables.'],
        ['Un renseignement exact livré après la décision qu\'il devait éclairer :', ['Garde toute sa valeur', 'N\'a quasiment plus de valeur opérationnelle', 'Devient automatiquement stratégique', 'Doit être reclassifié'], 1, 'Le délai fait partie de la qualité : un produit tardif ne remplit plus sa fonction.'],
      ],
    ],
    [
      'title' => 'Histoire des services de renseignement',
      'summary' => 'Des messagers royaux aux agences contemporaines, avec un regard africain.',
      'lessons' => [
        ['title' => 'Grandes étapes historiques', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>L'histoire du renseignement est aussi ancienne que celle du pouvoir politique. Elle éclaire les pratiques actuelles et rappelle que les dérives passées ont conduit à l'instauration de contrôles démocratiques.</p>
<h3>Antiquité et royaumes africains</h3>
<p>Les traités militaires anciens, dont <em>L'Art de la guerre</em> attribué à Sun Tzu, consacrent déjà un chapitre à l'emploi des informateurs. En Afrique, les grands ensembles politiques — empires du Ghana, du Mali et Songhaï, royaume du Dahomey, empire éthiopien, royaumes swahilis — s'appuyaient sur des réseaux de messagers, de commerçants caravaniers et de conseillers chargés de rapporter la situation des provinces et des voisins. Les routes transsahariennes étaient autant des voies d'échange que des canaux d'information.</p>
<h3>L'âge des cabinets et des chiffres</h3>
<p>Du XVI<sup>e</sup> au XIX<sup>e</sup> siècle, les monarchies européennes structurent des « cabinets noirs » chargés d'ouvrir le courrier diplomatique et de déchiffrer les messages. La cryptologie devient une discipline à part entière. La période coloniale voit ensuite les administrations imposer en Afrique des services de police politique tournés vers le contrôle des populations — un héritage que les États indépendants ont dû réformer.</p>
<h3>Le XX<sup>e</sup> siècle : institutionnalisation</h3>
<ul>
<li><strong>Les deux guerres mondiales</strong> industrialisent la collecte (écoutes radio, reconnaissance aérienne) et le décryptement.</li>
<li><strong>La guerre froide</strong> crée de grandes agences permanentes et fait de l'Afrique un terrain de rivalités extérieures.</li>
<li><strong>Les indépendances</strong> conduisent les États africains à bâtir leurs propres services, souvent dans l'urgence et parfois au service exclusif du pouvoir en place.</li>
</ul>
<h3>Depuis les années 1990 : contrôle et coopération</h3>
<p>La fin de la guerre froide, puis la montée du terrorisme transnational, de la criminalité organisée et des menaces numériques, ont transformé les priorités. Deux tendances marquent la période : l'encadrement législatif des services (lois, commissions de contrôle, juges) et le développement de coopérations régionales, notamment sous l'égide de l'Union africaine et des communautés économiques régionales.</p>
<blockquote>Leçon de l'histoire : les services les plus efficaces dans la durée sont ceux qui bénéficient de la confiance des citoyens, donc ceux qui respectent la loi.</blockquote>
HTML],
      ],
      'quiz' => [
        ['Que désignait l\'expression « cabinet noir » dans les monarchies européennes ?', ['Une prison secrète', 'Un service chargé d\'ouvrir et de déchiffrer le courrier', 'Une unité militaire d\'élite', 'Un conseil des ministres restreint'], 1, 'Les cabinets noirs interceptaient et déchiffraient la correspondance diplomatique.'],
        ['Quel rôle jouaient les caravanes transsahariennes dans les empires sahéliens ?', ['Aucun rôle informationnel', 'Elles étaient aussi des canaux de circulation de l\'information', 'Elles servaient uniquement au transport militaire', 'Elles étaient interdites aux commerçants'], 1, 'Commerce et information circulaient ensemble sur les routes caravanières.'],
        ['Quelle tendance caractérise l\'évolution des services depuis les années 1990 ?', ['La suppression de tout contrôle', 'Un encadrement législatif accru et des coopérations régionales', 'L\'abandon du renseignement humain', 'La privatisation totale des services'], 1, 'Lois, organes de contrôle et coopérations régionales se sont développés.'],
      ],
    ],
    [
      'title' => 'Les différentes disciplines du renseignement',
      'summary' => 'Panorama des « INT » et complémentarité des sources.',
      'lessons' => [
        ['title' => 'Panorama des disciplines de collecte', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Les professionnels classent les sources par grandes familles, désignées par des acronymes anglais. Chacune a ses forces, ses faiblesses et son cadre juridique propre.</p>
<table><thead><tr><th>Discipline</th><th>Source</th><th>Atout</th><th>Limite</th></tr></thead><tbody>
<tr><td><strong>HUMINT</strong></td><td>Personnes</td><td>Intentions, contexte, nuances</td><td>Fiabilité variable, risques humains</td></tr>
<tr><td><strong>OSINT</strong></td><td>Sources ouvertes et publiques</td><td>Volume, coût, légalité d'accès</td><td>Bruit, désinformation</td></tr>
<tr><td><strong>SIGINT</strong></td><td>Signaux électromagnétiques</td><td>Couverture, rapidité</td><td>Strictement réservé aux autorités habilitées par la loi</td></tr>
<tr><td><strong>GEOINT</strong></td><td>Imagerie et données géospatiales</td><td>Vision objective du terrain</td><td>Ne révèle pas les intentions</td></tr>
<tr><td><strong>FININT</strong></td><td>Flux financiers</td><td>Traçabilité des réseaux</td><td>Accès encadré (cellules de renseignement financier)</td></tr>
<tr><td><strong>TECHINT</strong></td><td>Matériels et technologies</td><td>Connaissance des capacités</td><td>Expertise rare</td></tr>
</tbody></table>
<h3>La fusion des sources (all-source)</h3>
<p>Aucune discipline ne suffit seule. L'analyste « toutes sources » croise des éléments hétérogènes : une image satellite montre un chantier, un article de presse locale évoque un projet d'infrastructure, un registre public identifie l'entreprise adjudicataire. C'est la convergence d'indices indépendants qui fonde un jugement solide.</p>
<div class="note"><strong>Principe d'indépendance.</strong> Deux articles qui reprennent la même dépêche ne constituent pas deux sources : ils n'en font qu'une. Remonter à l'origine de l'information est un réflexe fondamental.</div>
HTML],
      ],
      'quiz' => [
        ['Que signifie l\'acronyme OSINT ?', ['Operational Security Intelligence', 'Open Source Intelligence', 'Official Signals Intelligence', 'Organized Surveillance Intelligence'], 1, 'OSINT : renseignement d\'origine sources ouvertes.'],
        ['Deux articles de presse reprenant la même dépêche d\'agence constituent :', ['Deux sources indépendantes', 'Une seule source en réalité', 'Une source HUMINT', 'Une preuve définitive'], 1, 'Il faut remonter à l\'origine : une même dépêche reprise reste une seule source.'],
        ['Quelle limite principale présente le GEOINT ?', ['Il est illégal', 'Il ne révèle pas directement les intentions des acteurs', 'Il ne fonctionne que de nuit', 'Il ne couvre pas l\'Afrique'], 1, 'L\'imagerie montre des faits physiques, pas des intentions.'],
      ],
    ],
    [
      'title' => 'Analyse et exploitation de l\'information',
      'summary' => 'Évaluer, recouper, raisonner en hypothèses, exprimer l\'incertitude.',
      'lessons' => [
        ['title' => 'Méthodes d\'analyse structurée', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>L'analyse consiste à transformer un ensemble d'éléments évalués en <strong>jugements</strong> utiles. Le principal ennemi de l'analyste n'est pas le manque d'information mais <strong>les biais cognitifs</strong>.</p>
<h3>Biais fréquents</h3>
<ul>
<li><strong>Biais de confirmation</strong> : retenir ce qui confirme l'idée initiale.</li>
<li><strong>Ancrage</strong> : rester prisonnier de la première estimation.</li>
<li><strong>Effet miroir</strong> : supposer que l'autre raisonne comme nous.</li>
<li><strong>Pensée de groupe</strong> : consensus artificiel au sein d'une équipe.</li>
</ul>
<h3>L'analyse des hypothèses concurrentes (ACH)</h3>
<p>Méthode formalisée par Richards J. Heuer : on liste <em>toutes</em> les hypothèses plausibles, puis on évalue chaque indice au regard de chacune. On cherche moins les indices qui confirment que ceux qui <strong>infirment</strong>. L'hypothèse retenue est celle qui subit le moins d'incohérences.</p>
<table><thead><tr><th>Indice</th><th>H1 : exercice logistique</th><th>H2 : préparation d'incident</th><th>H3 : activité commerciale</th></tr></thead><tbody>
<tr><td>Convoi observé à date habituelle</td><td>Cohérent</td><td>Neutre</td><td>Cohérent</td></tr>
<tr><td>Aucun discours public hostile</td><td>Cohérent</td><td>Incohérent</td><td>Cohérent</td></tr>
<tr><td>Marchandises déclarées en douane</td><td>Neutre</td><td>Incohérent</td><td>Cohérent</td></tr>
</tbody></table>
<h3>Exprimer l'incertitude</h3>
<p>Un jugement analytique s'accompagne toujours d'un niveau de confiance et d'un vocabulaire de probabilité stable : <em>presque certain, probable, possible, peu probable</em>. Cette échelle doit être définie à l'avance et partagée avec les lecteurs pour éviter toute ambiguïté.</p>
<div class="callout"><strong>Bonne pratique :</strong> séparer clairement les <em>faits</em> (ce qui est établi), les <em>jugements</em> (ce que l'on en conclut) et les <em>lacunes</em> (ce que l'on ignore encore).</div>
HTML],
      ],
      'quiz' => [
        ['Dans la méthode ACH, sur quels indices l\'analyste concentre-t-il son attention ?', ['Ceux qui confirment l\'hypothèse favorite', 'Ceux qui infirment les hypothèses', 'Ceux qui proviennent de la presse', 'Ceux qui sont les plus récents'], 1, 'L\'ACH privilégie la réfutation : l\'hypothèse retenue est la moins contredite.'],
        ['Supposer qu\'un acteur étranger raisonne exactement comme nous relève :', ['De l\'effet miroir', 'De l\'ancrage', 'De la pensée de groupe', 'Du principe d\'indépendance'], 0, 'L\'effet miroir projette notre propre logique sur autrui.'],
        ['Pourquoi utiliser une échelle de probabilité définie à l\'avance ?', ['Pour impressionner le lecteur', 'Pour éviter les ambiguïtés d\'interprétation des jugements', 'Parce que la loi l\'impose', 'Pour masquer les lacunes'], 1, 'Un vocabulaire stable garantit que « probable » signifie la même chose pour tous.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Exercice guidé et étude de cas de synthèse.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Téléchargez la fiche de synthèse de la formation : définitions, cycle du renseignement, tableau des disciplines, liste des biais et échelle de probabilité. Elle constitue votre aide-mémoire pour l\'examen final.</p>'],
        ['title' => 'Exercice : de l\'information au renseignement', 'type' => 'exercise', 'minutes' => 40, 'content' => <<<'HTML'
<p><strong>Contexte fictif.</strong> Le directeur sécurité d'une entreprise de logistique opérant entre deux pays voisins vous demande si la réouverture annoncée d'un poste frontière présente des risques pour ses convois.</p>
<h3>Consignes</h3>
<ol>
<li>Reformulez la demande en <strong>trois besoins en renseignement</strong> précis et vérifiables.</li>
<li>Pour chacun, identifiez <strong>deux sources ouvertes légales</strong> que vous consulteriez (journaux officiels, presse régionale, communiqués des douanes, rapports d'organisations régionales…).</li>
<li>Proposez une <strong>grille d'évaluation</strong> des sources (fiabilité de la source / crédibilité de l'information).</li>
<li>Rédigez un <strong>jugement analytique de cinq lignes</strong> en séparant faits, jugements et lacunes, avec un niveau de confiance.</li>
</ol>
<div class="note"><strong>Corrigé indicatif.</strong> Un bon besoin est daté et mesurable : « Quels sont les horaires et procédures effectifs du poste X depuis sa réouverture ? » plutôt que « Est-ce dangereux ? ». Le jugement doit mentionner explicitement ce qui reste inconnu.</div>
HTML],
        ['title' => 'Étude de cas : l\'erreur d\'analyse', 'type' => 'case_study', 'minutes' => 35, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Une cellule d'analyse d'une organisation régionale reçoit, sur trois semaines, une série de signalements de rassemblements dans une ville moyenne. L'analyste principal, convaincu dès le premier jour d'une préparation de troubles, rédige des notes de plus en plus alarmistes. Il s'avère finalement qu'il s'agissait de la préparation d'un festival culturel annuel, annoncée dans la presse locale en langue nationale.</p>
<h3>Questions d'analyse</h3>
<ol>
<li>Quels biais cognitifs identifiez-vous ? À quel moment se sont-ils installés ?</li>
<li>Quelle source simple aurait permis de lever le doute ? Pourquoi n'a-t-elle pas été consultée ?</li>
<li>Construisez la matrice ACH qui aurait dû être réalisée dès la première semaine.</li>
<li>Quelles mesures organisationnelles (relecture contradictoire, « avocat du diable », diversité linguistique de l'équipe) proposeriez-vous ?</li>
</ol>
<blockquote>Enseignement : la maîtrise des langues et des contextes locaux est une compétence analytique de premier plan sur le continent africain.</blockquote>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-A201', 'category' => 'A', 'slug' => 'disciplines-de-collecte',
  'title' => 'Disciplines de collecte : HUMINT, OSINT, SIGINT, GEOINT',
  'subtitle' => 'Fonctionnement, apports et cadre juridique des grandes familles de sources.',
  'summary' => 'Une immersion méthodique dans les quatre grandes disciplines de collecte, avec leurs usages légitimes, leurs limites et les garde-fous légaux qui les encadrent.',
  'level' => 'intermediaire', 'hours' => 18, 'featured' => false,
  'description' => '<p>Après les fondements, cette formation approfondit les quatre disciplines de collecte les plus structurantes. L\'objectif n\'est pas de former des opérateurs techniques mais des <strong>professionnels capables de comprendre, commander et exploiter</strong> ces sources de manière éclairée.</p><p>Chaque chapitre précise ce qu\'un acteur privé ou un chercheur peut faire légalement, et ce qui relève exclusivement des autorités publiques habilitées par la loi.</p>',
  'objectives' => "Expliquer le fonctionnement et l'apport de chaque discipline\nDistinguer les activités ouvertes à tous de celles réservées aux autorités habilitées\nMener une recherche OSINT méthodique et documentée\nLire et interpréter une image satellitaire simple\nCroiser plusieurs disciplines dans un produit unique",
  'skills' => "Planifier une recherche en sources ouvertes\nDocumenter la traçabilité de ses sources\nInterpréter des indices géospatiaux\nIdentifier les contraintes légales d'une collecte\nArticuler plusieurs disciplines",
  'prerequisites' => "Avoir suivi « Fondements du renseignement » ou disposer de connaissances équivalentes\nAisance avec les outils numériques courants",
  'chapters' => [
    [
      'title' => 'Renseignement humain : HUMINT',
      'summary' => 'Ce que les personnes peuvent apporter, et à quelles conditions.',
      'lessons' => [
        ['title' => 'Briefing vidéo : quatre familles de sources', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Présentation visuelle des quatre disciplines étudiées et de leurs garde-fous juridiques.</p>'],
        ['title' => 'Le renseignement d\'origine humaine', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le HUMINT regroupe toutes les informations obtenues <strong>auprès de personnes</strong>. Dans sa forme la plus courante et la plus légitime, il ressemble au travail d'un journaliste d'investigation ou d'un chercheur de terrain : entretiens, échanges avec des experts, participation à des conférences, débriefing de voyageurs ou de collaborateurs.</p>
<h3>Ce qu'apporte le HUMINT</h3>
<ul><li>Les <strong>intentions</strong> et les motivations, invisibles pour les capteurs techniques ;</li><li>Le <strong>contexte</strong> culturel, linguistique et social ;</li><li>L'accès à des milieux fermés, dans les limites de la loi.</li></ul>
<h3>Ses fragilités</h3>
<ul><li>La source peut se tromper, exagérer ou manipuler ;</li><li>Elle peut s'exposer à des risques personnels ;</li><li>La relation peut créer des dépendances ou des conflits d'intérêts.</li></ul>
<h3>Cadre</h3>
<p>Le recrutement de sources clandestines relève exclusivement des services de l'État agissant dans un cadre légal. Pour un acteur privé (entreprise, ONG, cabinet de conseil), le HUMINT se limite à des <strong>échanges transparents et consentis</strong> : jamais de corruption, de chantage, de fausse identité ou d'incitation à violer une obligation de confidentialité.</p>
<div class="warning"><strong>Ligne rouge.</strong> Obtenir d'un salarié des informations couvertes par le secret de son employeur expose à des poursuites (violation du secret des affaires, corruption, complicité). L'éthique est ici aussi une protection juridique.</div>
HTML],
      ],
      'quiz' => [
        ['Pour une entreprise privée, quelle pratique HUMINT est légitime ?', ['Rémunérer un salarié concurrent pour obtenir des documents internes', 'Interroger de manière transparente un expert lors d\'une conférence publique', 'Se présenter sous une fausse identité', 'Exercer une pression sur un ancien employé'], 1, 'Seuls les échanges transparents et consentis sont acceptables pour un acteur privé.'],
        ['Quel apport spécifique le HUMINT offre-t-il par rapport aux capteurs techniques ?', ['Une couverture géographique totale', 'L\'accès aux intentions et motivations', 'Une fiabilité garantie', 'Une absence de risques'], 1, 'Les intentions humaines échappent largement aux capteurs techniques.'],
        ['Le recrutement de sources clandestines relève :', ['De tout citoyen', 'Des services de l\'État agissant dans un cadre légal', 'Des entreprises de sécurité privée', 'Des journalistes'], 1, 'Cette activité est réservée aux autorités habilitées par la loi.'],
      ],
    ],
    [
      'title' => 'Renseignement de sources ouvertes : OSINT',
      'summary' => 'Méthode, traçabilité et légalité de la recherche ouverte.',
      'lessons' => [
        ['title' => 'Méthodologie OSINT', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>L'OSINT exploite les informations <strong>publiquement et légalement accessibles</strong> : presse, publications officielles, registres publics, données ouvertes, travaux académiques, réseaux sociaux publics, imagerie commerciale. On estime souvent qu'une large majorité du renseignement utile à la décision peut être obtenue de cette manière.</p>
<h3>Une méthode en six temps</h3>
<ol>
<li><strong>Cadrer</strong> la question et les limites (ce que l'on ne cherchera pas).</li>
<li><strong>Planifier</strong> les sources et les mots-clés, y compris dans les langues locales (wolof, haoussa, swahili, amharique, arabe…).</li>
<li><strong>Collecter</strong> en archivant systématiquement : URL, date, capture, empreinte du fichier.</li>
<li><strong>Vérifier</strong> : origine, date réelle, géolocalisation, cohérence.</li>
<li><strong>Analyser</strong> et recouper.</li>
<li><strong>Restituer</strong> avec les références permettant à un tiers de refaire le chemin.</li>
</ol>
<h3>Les limites légales et éthiques</h3>
<ul>
<li>« Accessible » ne veut pas dire « librement exploitable » : les lois sur la protection des données personnelles s'appliquent (par exemple la Convention de Malabo de l'Union africaine et les lois nationales).</li>
<li>Pas de contournement d'authentification, pas de faux profils pour infiltrer des groupes privés, pas de collecte massive de données personnelles sans base légale.</li>
<li>Principe de <strong>minimisation</strong> : ne collecter que ce qui est nécessaire à la question posée.</li>
</ul>
<div class="callout">Un bon livrable OSINT est <strong>reproductible</strong> : un relecteur doit pouvoir retrouver chaque élément grâce aux références et aux captures horodatées.</div>
HTML],
      ],
      'quiz' => [
        ['Pourquoi archiver URL, date et capture lors d\'une collecte OSINT ?', ['Pour alourdir le rapport', 'Pour garantir la traçabilité et la reproductibilité', 'Parce que les sites l\'exigent', 'Pour contourner les droits d\'auteur'], 1, 'La traçabilité permet la vérification par un tiers et protège contre la disparition des contenus.'],
        ['Une information publiquement accessible sur une personne :', ['Peut être exploitée sans aucune limite', 'Reste soumise aux règles de protection des données personnelles', 'Devient propriété de l\'analyste', 'Est toujours exacte'], 1, 'L\'accessibilité ne lève pas les obligations légales de protection des données.'],
        ['Créer un faux profil pour entrer dans un groupe privé est :', ['Une technique OSINT standard', 'Une pratique contraire aux principes éthiques et souvent illégale', 'Obligatoire pour les analystes', 'Recommandé par la Convention de Malabo'], 1, 'L\'OSINT éthique se limite aux contenus réellement publics, sans tromperie.'],
      ],
    ],
    [
      'title' => 'Renseignement d\'origine électromagnétique : SIGINT',
      'summary' => 'Comprendre une discipline réservée aux États, et ses garde-fous.',
      'lessons' => [
        ['title' => 'Le SIGINT : principes et contrôle démocratique', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Le SIGINT désigne le renseignement issu des <strong>signaux électromagnétiques</strong>. Il se divise traditionnellement en COMINT (communications) et ELINT (signaux électroniques non communicants, comme les radars). Cette formation l'aborde <strong>sous un angle conceptuel, historique et juridique</strong> : l'interception de communications est un acte réservé aux autorités habilitées et strictement encadré par la loi.</p>
<h3>Pourquoi l'étudier ?</h3>
<ul><li>Pour comprendre la place de cette discipline dans les politiques de sécurité ;</li><li>Pour mesurer les enjeux de souveraineté numérique et de protection des communications ;</li><li>Pour maîtriser le débat public sur l'équilibre entre sécurité et libertés.</li></ul>
<h3>Repères historiques</h3>
<p>Le décryptement des communications a pesé sur l'issue de plusieurs conflits du XX<sup>e</sup> siècle. Les révélations publiques des années 2010 sur la surveillance de masse ont, à l'inverse, montré les risques d'une collecte sans contrôle suffisant et relancé partout la réflexion législative.</p>
<h3>Les garde-fous d'un État de droit</h3>
<table><thead><tr><th>Principe</th><th>Traduction concrète</th></tr></thead><tbody>
<tr><td>Légalité</td><td>Finalités et techniques définies par une loi publique</td></tr>
<tr><td>Nécessité et proportionnalité</td><td>Mesure ciblée, limitée dans le temps, justifiée par une menace</td></tr>
<tr><td>Autorisation préalable</td><td>Décision d'une autorité indépendante ou d'un juge</td></tr>
<tr><td>Contrôle a posteriori</td><td>Commission parlementaire ou administrative indépendante</td></tr>
<tr><td>Recours</td><td>Possibilité pour un citoyen de saisir une juridiction</td></tr>
</tbody></table>
<div class="warning">Toute interception de communications par un particulier ou une entreprise constitue une infraction pénale dans la quasi-totalité des législations. Aucune technique d'interception n'est enseignée à l'Académie.</div>
HTML],
      ],
      'quiz' => [
        ['Que désigne le COMINT ?', ['Le renseignement issu des communications', 'Le renseignement commercial', 'Le renseignement d\'imagerie', 'Le renseignement financier'], 0, 'COMINT = Communications Intelligence, sous-ensemble du SIGINT.'],
        ['Dans un État de droit, une mesure d\'interception doit notamment être :', ['Secrète et sans contrôle', 'Nécessaire, proportionnée et autorisée par une autorité indépendante', 'Décidée par l\'opérateur téléphonique', 'Permanente et générale'], 1, 'Légalité, proportionnalité, autorisation et contrôle sont les piliers du cadre.'],
        ['L\'interception de communications par une entreprise privée :', ['Est légale si elle est discrète', 'Constitue en principe une infraction pénale', 'Est autorisée pour la veille concurrentielle', 'Relève de l\'OSINT'], 1, 'Elle est réservée aux autorités publiques habilitées par la loi.'],
      ],
    ],
    [
      'title' => 'Renseignement géospatial : GEOINT',
      'summary' => 'Lire le territoire à partir d\'images et de données géographiques.',
      'lessons' => [
        ['title' => 'Imagerie et analyse géospatiale', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le GEOINT combine imagerie (satellitaire, aérienne), cartographie et données géoréférencées pour décrire et comprendre une situation sur le terrain. L'essor de l'imagerie commerciale et des programmes ouverts (comme Sentinel du programme européen Copernicus ou Landsat) a démocratisé cette discipline.</p>
<h3>Notions de base</h3>
<ul>
<li><strong>Résolution spatiale</strong> : taille au sol d'un pixel (de 10 m pour Sentinel-2 à moins d'un mètre pour l'imagerie commerciale très haute résolution).</li>
<li><strong>Résolution temporelle</strong> : fréquence de revisite d'une zone.</li>
<li><strong>Bandes spectrales</strong> : l'infrarouge permet par exemple d'évaluer l'état de la végétation.</li>
</ul>
<h3>Applications légitimes en Afrique</h3>
<ul><li>Suivi de la déforestation et de l'orpaillage illégal ;</li><li>Évaluation des dégâts après inondations ou sécheresses ;</li><li>Suivi des déplacements de populations à des fins humanitaires ;</li><li>Vérification d'informations diffusées en ligne (géolocalisation d'une vidéo).</li></ul>
<h3>Méthode de photo-interprétation</h3>
<p>On observe successivement : la <em>forme</em>, la <em>taille</em>, l'<em>ombre</em> (qui renseigne sur la hauteur et l'heure), la <em>texture</em>, le <em>motif</em> et l'<em>association</em> (ce qui se trouve autour). Comparer deux dates d'une même zone (analyse diachronique) est souvent plus parlant qu'une image isolée.</p>
<div class="note">Le GEOINT montre <strong>ce qui est</strong>, rarement <strong>pourquoi</strong>. Il doit être croisé avec d'autres sources pour interpréter les intentions.</div>
HTML],
      ],
      'quiz' => [
        ['Que mesure la résolution spatiale d\'une image ?', ['La fréquence de revisite', 'La taille au sol représentée par un pixel', 'Le nombre de couleurs', 'Le poids du fichier'], 1, 'La résolution spatiale correspond à la taille au sol d\'un pixel.'],
        ['Quel critère de photo-interprétation renseigne sur la hauteur d\'un objet ?', ['La texture', 'L\'ombre', 'Le motif', 'La couleur'], 1, 'La longueur de l\'ombre permet d\'estimer la hauteur, connaissant l\'heure et la position du soleil.'],
        ['Pourquoi comparer deux images d\'une même zone à des dates différentes ?', ['Pour améliorer la résolution', 'Pour détecter les changements (analyse diachronique)', 'Pour réduire le coût', 'Pour éviter les nuages'], 1, 'L\'analyse diachronique révèle les évolutions du terrain.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Exercice de recherche ouverte et étude de cas multi-sources.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Aide-mémoire des quatre disciplines : définitions, apports, limites et cadre légal.</p>'],
        ['title' => 'Exercice : géolocaliser une image publique', 'type' => 'exercise', 'minutes' => 45, 'content' => <<<'HTML'
<p><strong>Objectif.</strong> Apprendre à vérifier la localisation d'une photographie <em>publiée par une institution</em> (communiqué officiel, rapport d'ONG) en n'utilisant que des sources ouvertes, sans jamais chercher à localiser une personne privée.</p>
<h3>Étapes</h3>
<ol>
<li>Relevez les indices visibles : relief, végétation, panneaux, architecture, langue des inscriptions, sens de circulation.</li>
<li>Formulez une hypothèse de région, puis comparez avec une imagerie satellitaire libre.</li>
<li>Vérifiez l'heure approximative grâce aux ombres.</li>
<li>Documentez chaque étape par une capture annotée.</li>
<li>Concluez avec un niveau de confiance et les éléments manquants.</li>
</ol>
<div class="warning">Règle déontologique : l'exercice porte uniquement sur des lieux et non sur des personnes. Ne jamais diffuser l'emplacement d'un domicile privé.</div>
HTML],
        ['title' => 'Étude de cas : vérifier une vidéo virale', 'type' => 'case_study', 'minutes' => 35, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Une vidéo présentée comme montrant des affrontements récents dans une capitale régionale circule massivement. Plusieurs médias s'apprêtent à la reprendre.</p>
<h3>Démarche attendue</h3>
<ol>
<li><strong>Recherche d'antériorité</strong> par recherche inversée d'images clés : la vidéo a-t-elle déjà circulé ?</li>
<li><strong>Géolocalisation</strong> via les bâtiments visibles et l'imagerie libre.</li>
<li><strong>Chronolocalisation</strong> : météo, ombres, événements datés visibles.</li>
<li><strong>Croisement</strong> avec la presse locale et les communiqués officiels.</li>
</ol>
<p><strong>Dénouement :</strong> la vidéo datait de six ans et avait été tournée dans un autre pays. Discutez l'impact qu'aurait eu sa diffusion et le rôle d'un analyste dans la lutte contre la désinformation.</p>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
];
