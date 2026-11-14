<?php
/**
 * MODULE G : RENSEIGNEMENT MILITAIRE
 * Approche doctrinale, analytique et défensive : appui à la décision du commandement, analyse de
 * l'environnement, estimation, sécurité des opérations, opérations de paix, droit des conflits armés.
 * Aucune procédure de ciblage ni technique offensive. Scénarios et données entièrement fictifs.
 * Format des questions : [énoncé, [réponses], index_correct, explication]
 */
return [
[
  'code' => 'AFS-G101', 'category' => 'G', 'slug' => 'fondements-du-renseignement-militaire',
  'title' => 'Fondements du renseignement militaire',
  'subtitle' => 'Éclairer la décision du commandement : environnement, capacités, intentions et sécurité des opérations.',
  'summary' => 'Comprendre le rôle du renseignement militaire, analyser un environnement opérationnel, estimer des capacités et des intentions, protéger ses propres informations et produire des synthèses utiles au chef, dans le respect du droit.',
  'level' => 'intermediaire', 'hours' => 16, 'featured' => true,
  'description' => '<p>Cette formation présente le renseignement militaire tel qu\'il est enseigné dans les écoles d\'état-major : une fonction d\'<strong>appui à la décision</strong> du commandement, soumise à l\'autorité politique et au <strong>droit des conflits armés</strong>. Elle couvre l\'analyse de l\'environnement opérationnel (terrain, climat, population, information), l\'estimation des capacités et des intentions, la sécurité des opérations et la rédaction de produits clairs.</p><p>Le contenu est <strong>doctrinal et analytique</strong> : il ne comporte ni procédure de ciblage, ni technique offensive. Tous les exercices reposent sur des scénarios et des données fictifs.</p>',
  'objectives' => "Situer le renseignement militaire aux niveaux stratégique, opératif et tactique\nTraduire l'intention du chef en besoins prioritaires en renseignement\nAnalyser un environnement opérationnel avec les grilles PMESII et ASCOPE\nDistinguer capacités et intentions, et formuler une estimation avec un niveau de confiance\nAppliquer les principes de sécurité des opérations et de contre-ingérence défensive\nRédiger un bulletin et présenter un briefing renseignement",
  'skills' => "Formuler des besoins prioritaires en renseignement\nConduire une analyse d'environnement structurée\nConstruire une liste d'indicateurs d'alerte\nÉvaluer des hypothèses concurrentes (la plus probable, la plus dangereuse)\nProtéger l'information de sa propre organisation\nPrésenter un briefing de cinq minutes",
  'prerequisites' => "Formation AFS-A101 recommandée (cycle du renseignement)\nIntérêt pour les questions de défense et de sécurité\nAucune expérience militaire requise",
  'chapters' => [
    [
      'title' => 'Le renseignement militaire : rôle et organisation',
      'summary' => 'Appuyer la décision du chef : niveaux, besoins prioritaires, principes et cadre légal.',
      'lessons' => [
        ['title' => 'Briefing vidéo : renseigner le commandement', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Vue d\'ensemble du module : la place du renseignement auprès du chef, les trois niveaux de décision et les principes qui encadrent la fonction.</p>'],
        ['title' => 'Renseigner pour décider', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le renseignement militaire a une finalité unique : <strong>éclairer la décision du chef</strong> pour réduire l'incertitude, préserver la vie des personnels, protéger les populations et utiliser au mieux des moyens toujours limités. Il ne décide pas : il propose une compréhension de la situation, des hypothèses et un niveau de confiance.</p>
<h3>Trois niveaux, trois horizons</h3>
<table><thead><tr><th>Niveau</th><th>Destinataire</th><th>Questions typiques</th></tr></thead><tbody>
<tr><td>Stratégique</td><td>Autorités politiques, haut commandement</td><td>Quelles menaces durables pour le pays ? Quelles évolutions régionales sur plusieurs années ?</td></tr>
<tr><td>Opératif</td><td>Commandant d'une opération ou d'un théâtre</td><td>Comment la situation peut-elle évoluer dans la zone dans les semaines à venir ?</td></tr>
<tr><td>Tactique</td><td>Chef d'unité sur le terrain</td><td>Que se passe-t-il aujourd'hui sur cet axe, dans ce secteur ?</td></tr>
</tbody></table>
<h3>Les besoins prioritaires en renseignement</h3>
<p>Le chef exprime ce qu'il doit savoir pour décider. L'officier renseignement traduit cette intention en <strong>besoins prioritaires en renseignement</strong> (BPR) : quelques questions précises, hiérarchisées, liées à une décision et à une échéance. Exemple fictif : « D'ici dix jours, les tensions entre éleveurs et agriculteurs de la vallée risquent-elles de dégénérer en violences pendant la période de transhumance ? »</p>
<h3>Les principes de la fonction</h3>
<ul>
<li><strong>Direction centralisée</strong> : un seul responsable oriente et priorise les efforts ;</li>
<li><strong>Objectivité</strong> : dire ce que l'on sait, ce que l'on ignore et ce que l'on suppose, même si cela déplaît ;</li>
<li><strong>Continuité et réactivité</strong> : la situation évolue, l'analyse aussi ;</li>
<li><strong>Accessibilité</strong> : un renseignement que le chef ne reçoit pas ou ne comprend pas est inutile ;</li>
<li><strong>Sécurité</strong> : protéger les sources, les méthodes et les personnes.</li>
</ul>
<h3>Un cadre légal non négociable</h3>
<p>Dans un État de droit, le renseignement militaire est subordonné au pouvoir civil, contrôlé (inspection, commissions parlementaires) et soumis au <strong>droit international humanitaire</strong> et aux droits humains. Ce cadre n'est pas un frein : il garantit la légitimité de l'action et la confiance des populations, sans laquelle aucune opération ne réussit durablement.</p>
<div class="callout"><strong>À retenir.</strong> Le renseignement militaire sert la décision, dans le respect du droit. Il n'a de valeur que s'il est pertinent, à temps et compris par le chef.</div>
HTML],
      ],
      'quiz' => [
        ['Quelle est la finalité première du renseignement militaire ?', ['Remplacer la décision du chef', 'Éclairer la décision du commandement en réduisant l\'incertitude', 'Produire des archives', 'Surveiller la population'], 1, 'Le renseignement propose une compréhension et des hypothèses ; le chef décide.'],
        ['Les besoins prioritaires en renseignement sont :', ['Une liste exhaustive de toutes les informations possibles', 'Quelques questions précises, hiérarchisées et liées à une décision', 'Les demandes des journalistes', 'Le budget du service'], 1, 'Peu nombreux, précis et datés : ils orientent tout l\'effort de recherche.'],
        ['Le principe d\'objectivité impose de :', ['Confirmer l\'avis du chef', 'Distinguer ce que l\'on sait, ce que l\'on ignore et ce que l\'on suppose', 'Ne jamais exprimer d\'incertitude', 'Présenter une seule hypothèse'], 1, 'Un analyste qui dit ce que le chef veut entendre le met en danger.'],
      ],
    ],
    [
      'title' => 'Comprendre l\'environnement opérationnel',
      'summary' => 'Terrain, climat, population et information : une analyse globale avant toute action.',
      'lessons' => [
        ['title' => 'Analyser l\'environnement opérationnel', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Avant toute décision, le commandement doit comprendre <strong>l'environnement dans lequel il agit</strong>. Une opération qui ignore le terrain, les saisons ou la population échoue, même avec des moyens supérieurs.</p>
<h3>Le terrain et le climat</h3>
<p>L'analyse du terrain recense les éléments qui facilitent ou gênent les déplacements et l'observation : obstacles (fleuves, reliefs, zones inondables), couverts, points d'observation, axes de communication, points clés (ponts, gués, carrefours). Le climat et les saisons transforment le terrain : la saison des pluies coupe des pistes, la saison sèche ouvre des passages et déclenche les mouvements de transhumance.</p>
<h3>La grille PMESII</h3>
<p>Pour ne rien oublier, on décrit l'environnement selon six dimensions :</p>
<table><thead><tr><th>Dimension</th><th>Questions</th></tr></thead><tbody>
<tr><td>Politique</td><td>Qui gouverne localement ? Quelles autorités sont reconnues par la population ?</td></tr>
<tr><td>Militaire et sécuritaire</td><td>Quelles forces, publiques ou non, sont présentes ? Avec quelles relations ?</td></tr>
<tr><td>Économique</td><td>De quoi vivent les habitants ? Quels marchés, quelles ressources disputées ?</td></tr>
<tr><td>Social</td><td>Quelles communautés, quelles langues, quelles tensions ou solidarités ?</td></tr>
<tr><td>Infrastructures</td><td>Routes, ponts, santé, eau, énergie, télécommunications</td></tr>
<tr><td>Information</td><td>Comment circule l'information ? Radios locales, réseaux sociaux, rumeurs</td></tr>
</tbody></table>
<h3>La population au centre</h3>
<p>Dans la plupart des crises africaines contemporaines, la population n'est pas un « décor » : elle est l'enjeu. Comprendre ses besoins, ses peurs et ses perceptions des forces armées est indispensable pour la protéger et éviter que l'action militaire n'aggrave la crise.</p>
<div class="callout"><strong>Vigilance éthique.</strong> L'analyse porte sur des dynamiques collectives, jamais sur la stigmatisation d'une communauté. Associer une ethnie ou une religion à une menace est une faute analytique et morale qui alimente les violences.</div>
HTML],
      ],
      'quiz' => [
        ['Pourquoi les saisons sont-elles importantes dans l\'analyse du terrain ?', ['Elles n\'ont aucun effet', 'Elles modifient la praticabilité des axes et les mouvements de population', 'Elles changent uniquement la température', 'Elles concernent seulement l\'aviation'], 1, 'Pluies, crues et transhumance transforment le terrain et les dynamiques locales.'],
        ['La grille PMESII sert à :', ['Classer les armements', 'Décrire l\'environnement selon six dimensions sans en oublier', 'Évaluer les soldats', 'Rédiger les ordres'], 1, 'Politique, militaire, économique, social, infrastructures, information.'],
        ['Associer une communauté entière à une menace est :', ['Une simplification utile', 'Une faute analytique et morale qui alimente les violences', 'Une méthode recommandée', 'Sans conséquence'], 1, 'L\'analyse porte sur des dynamiques et des acteurs, jamais sur la stigmatisation.'],
      ],
    ],
    [
      'title' => 'Évaluer les capacités et les intentions',
      'summary' => 'Ce qu\'un acteur peut faire, ce qu\'il veut faire : estimer sans se tromper de certitude.',
      'lessons' => [
        ['title' => 'Capacités, intentions et hypothèses', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Toute estimation repose sur une distinction fondamentale : <strong>les capacités</strong> (ce qu'un acteur est en mesure de faire : effectifs, équipements, logistique, savoir-faire, soutiens) et <strong>les intentions</strong> (ce qu'il veut faire : objectifs, motivations, contraintes politiques). Les capacités s'observent plus facilement ; les intentions se déduisent et restent incertaines.</p>
<h3>Le piège de l'image miroir</h3>
<p>L'erreur la plus fréquente consiste à prêter à l'autre notre propre logique : « il ne fera pas cela, ce serait irrationnel ». Or l'acteur observé a ses propres contraintes, sa culture, ses calculs. De nombreuses surprises stratégiques de l'histoire viennent de ce biais.</p>
<h3>Raisonner en hypothèses</h3>
<p>Plutôt qu'une prédiction unique, l'analyste présente au chef plusieurs hypothèses d'évolution :</p>
<ul>
<li><strong>L'hypothèse la plus probable</strong>, sur laquelle on planifie en priorité ;</li>
<li><strong>L'hypothèse la plus dangereuse</strong>, contre laquelle on se prémunit ;</li>
<li>éventuellement une hypothèse de rupture, peu probable mais à fort impact.</li>
</ul>
<p>Chaque hypothèse est accompagnée des <strong>indicateurs</strong> qui permettraient de la confirmer ou de l'infirmer, et d'un niveau de confiance.</p>
<h3>Exprimer la confiance</h3>
<table><thead><tr><th>Formulation</th><th>Sens</th></tr></thead><tbody>
<tr><td>Presque certain</td><td>Plus de 90 % de probabilité estimée</td></tr>
<tr><td>Probable</td><td>Entre 60 et 90 %</td></tr>
<tr><td>Possible</td><td>Entre 30 et 60 %</td></tr>
<tr><td>Peu probable</td><td>Moins de 30 %</td></tr>
</tbody></table>
<div class="callout"><strong>À retenir.</strong> Une estimation honnête dit « probable » quand elle n'est pas certaine. Le chef doit savoir sur quoi reposent les jugements pour décider en connaissance de cause.</div>
HTML],
      ],
      'quiz' => [
        ['Les capacités d\'un acteur désignent :', ['Ses intentions secrètes', 'Ce qu\'il est en mesure de faire', 'Ses déclarations publiques', 'Son histoire'], 1, 'Effectifs, équipements, logistique, savoir-faire et soutiens.'],
        ['Le biais de l\'image miroir consiste à :', ['Utiliser des miroirs d\'observation', 'Prêter à l\'autre sa propre logique', 'Copier un rapport', 'Inverser des cartes'], 1, 'L\'acteur observé raisonne selon ses propres contraintes et calculs.'],
        ['Pourquoi présenter l\'hypothèse la plus dangereuse ?', ['Pour effrayer le chef', 'Pour s\'en prémunir même si elle n\'est pas la plus probable', 'Parce qu\'elle est toujours vraie', 'Pour remplacer l\'hypothèse probable'], 1, 'On planifie sur le probable et on se protège contre le dangereux.'],
      ],
    ],
    [
      'title' => 'Sources, sécurité des opérations et diffusion',
      'summary' => 'Les disciplines de recherche, la protection de ses propres informations et les produits du renseignement.',
      'lessons' => [
        ['title' => 'Des sources au produit fini', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<h3>Les grandes disciplines</h3>
<p>Le renseignement militaire combine plusieurs familles de sources, chacune encadrée par la loi et par des autorisations :</p>
<ul>
<li><strong>Renseignement d'origine humaine</strong> : contacts, entretiens, comptes rendus de patrouilles, échanges avec les autorités et les populations ;</li>
<li><strong>Renseignement d'origine image et géospatial</strong> : cartographie, imagerie, observation ;</li>
<li><strong>Renseignement d'origine électromagnétique</strong> : domaine strictement réservé aux services habilités, sous contrôle légal ;</li>
<li><strong>Sources ouvertes</strong> : presse, rapports publics, réseaux sociaux, souvent la première base de compréhension.</li>
</ul>
<p>Aucune source n'est suffisante seule : c'est le <strong>recoupement</strong> qui produit un jugement solide.</p>
<h3>Protéger ses propres informations : la sécurité des opérations</h3>
<p>La contre-ingérence défensive vise à empêcher qu'un adversaire ne comprenne nos intentions, nos capacités ou nos vulnérabilités. La <strong>sécurité des opérations</strong> (OPSEC) suit une démarche simple : identifier les informations critiques, analyser les menaces, repérer les vulnérabilités, évaluer le risque, appliquer des contre-mesures. Les téléphones personnels et les réseaux sociaux sont aujourd'hui la première source de fuites.</p>
<h3>Les produits du renseignement</h3>
<table><thead><tr><th>Produit</th><th>Usage</th></tr></thead><tbody>
<tr><td>Bulletin de renseignement</td><td>Point de situation périodique, factuel et court</td></tr>
<tr><td>Synthèse</td><td>Analyse d'une question précise, avec jugement et confiance</td></tr>
<tr><td>Estimation</td><td>Évolutions possibles à moyen terme, hypothèses et indicateurs</td></tr>
<tr><td>Briefing</td><td>Présentation orale au chef, en quelques minutes</td></tr>
</tbody></table>
<p>La diffusion obéit au <strong>besoin d'en connaître</strong> : chacun reçoit ce qui lui est nécessaire, au niveau de classification approprié.</p>
HTML],
      ],
      'quiz' => [
        ['Pourquoi recouper les sources ?', ['Pour allonger les rapports', 'Parce qu\'aucune source n\'est suffisante seule', 'Pour retarder la diffusion', 'C\'est inutile'], 1, 'Le recoupement réduit le risque d\'erreur et de manipulation.'],
        ['La sécurité des opérations vise à :', ['Espionner ses alliés', 'Empêcher un adversaire de comprendre nos intentions et vulnérabilités', 'Publier nos plans', 'Surveiller les journalistes'], 1, 'C\'est une démarche défensive de protection de nos propres informations.'],
        ['Le besoin d\'en connaître signifie que :', ['Tout le monde reçoit tout', 'Chacun reçoit ce qui lui est nécessaire pour sa mission', 'Seul le chef est informé', 'Les informations ne sont jamais partagées'], 1, 'Il limite la diffusion sans bloquer ceux qui ont besoin de l\'information.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Appliquer la méthode à un scénario fictif : bulletin, estimation et retour d\'expérience.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Le support de cours de la formation, à télécharger : notions clés, grilles d\'analyse et modèles de produits.</p>'],
        ['title' => 'Exercice : rédiger un bulletin de situation', 'type' => 'exercise', 'minutes' => 45, 'content' => <<<'HTML'
<div class="note"><strong>Scénario entièrement fictif.</strong> Pays, organisations, personnes et données sont imaginaires.</div>
<p>Vous êtes officier renseignement d'un bataillon déployé dans la région fictive du Haut-Sorou (République de Kassanié, pays imaginaire), en appui des autorités pour sécuriser une période de marchés hebdomadaires. Vous disposez de cinq informations : une radio locale signale des rumeurs de vols de bétail ; une patrouille a constaté la fermeture d'un gué après de fortes pluies ; un chef de village demande davantage de patrouilles ; un message anonyme sur un réseau social annonce des affrontements « samedi » ; le service de santé signale une épidémie de rougeole dans deux villages.</p>
<h3>Consignes</h3>
<ol><li>Évaluez chaque information (fiabilité de la source, crédibilité, recoupements possibles).</li><li>Rédigez un bulletin de 15 lignes maximum : situation, faits évalués, appréciation, points d'attention.</li><li>Proposez deux besoins de renseignement complémentaires et les moyens légaux de les satisfaire.</li><li>Identifiez ce qui relève d'autres acteurs (santé, autorités civiles) et doit leur être transmis.</li></ol>
HTML],
        ['title' => 'Étude de cas : l\'estimation qui rassurait trop', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<div class="note"><strong>Cas fictif inspiré de situations historiques documentées.</strong></div>
<p>Pendant plusieurs semaines, une cellule d'analyse estime qu'un groupe armé fictif « n'a ni l'intérêt ni les moyens » de s'en prendre à une ville moyenne. Des indicateurs contraires existent (recrutements, achats de carburant, départ de commerçants) mais sont jugés « anecdotiques ». L'attaque survient. L'enquête montre que l'équipe raisonnait selon sa propre logique et n'avait jamais formulé d'hypothèse alternative.</p>
<h3>Questions</h3>
<ol><li>Quels biais reconnaissez-vous ?</li><li>Quels indicateurs auraient dû être suivis et comment ?</li><li>Quelle méthode aurait permis de remettre en cause l'estimation dominante ?</li><li>Comment présenter au chef une incertitude sans le paralyser ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-G201', 'category' => 'G', 'slug' => 'renseignement-militaire-operations-de-paix',
  'title' => 'Renseignement militaire, opérations de paix et coopération régionale',
  'subtitle' => 'Protection des civils, partage du renseignement entre partenaires et contrôle démocratique.',
  'summary' => 'Appliquer le renseignement militaire aux opérations de paix et de stabilisation en Afrique : cadres onusien et africain, protection des civils, coopération et partage entre partenaires, droit international humanitaire et contrôle démocratique.',
  'level' => 'avance', 'hours' => 18, 'featured' => false,
  'description' => '<p>Ce second volet du module G s\'adresse aux officiers, cadres de défense et analystes appelés à servir dans des opérations de paix ou des mécanismes régionaux de sécurité. Il présente les principes du renseignement dans les missions de l\'ONU et de l\'Union africaine, l\'analyse des menaces pesant sur les civils, les conditions d\'un partage efficace entre partenaires et les <strong>limites légales et éthiques</strong> de la fonction.</p><p>Le contenu est <strong>doctrinal</strong> et s\'appuie sur des textes publics ; les exercices utilisent des scénarios fictifs.</p>',
  'objectives' => "Situer le renseignement dans les opérations de paix de l'ONU et de l'Union africaine\nAnalyser les menaces pesant sur les civils et proposer des mesures de protection\nOrganiser le partage du renseignement entre partenaires de confiance\nAppliquer les principes du droit international humanitaire à la fonction renseignement\nConnaître les mécanismes de contrôle démocratique et les lignes rouges",
  'skills' => "Construire une matrice menaces, vulnérabilités et capacités de protection\nConcevoir un mécanisme d'alerte respectueux des populations\nMarquer et diffuser une information dans une coalition\nIdentifier les risques juridiques et éthiques d'une demande de renseignement",
  'prerequisites' => "Formation AFS-G101 ou expérience équivalente\nNotions de géopolitique africaine (AFS-F201 recommandée)",
  'chapters' => [
    [
      'title' => 'Le renseignement dans les opérations de paix',
      'summary' => 'Principes du maintien de la paix, missions africaines et place du renseignement.',
      'lessons' => [
        ['title' => 'Briefing vidéo : le renseignement au service de la paix', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les spécificités du renseignement dans une mission de paix : mandat, consentement, impartialité et protection des civils.</p>'],
        ['title' => 'Mandats, principes et place du renseignement', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Les opérations de paix reposent sur trois principes constants : <strong>le consentement</strong> des parties principales, <strong>l'impartialité</strong> et <strong>le non-recours à la force</strong>, sauf en légitime défense et pour la défense du mandat. Le continent africain accueille une grande partie des missions de l'ONU, ainsi que des missions conduites par l'Union africaine et les communautés économiques régionales, appuyées par la Force africaine en attente.</p>
<h3>Un renseignement longtemps tabou</h3>
<p>Le mot « renseignement » a longtemps été évité dans les missions de l'ONU, par crainte de paraître espionner l'État hôte. L'expérience a montré qu'une mission « aveugle » protège mal les civils et ses propres personnels. L'ONU a donc adopté une politique publique du <strong>renseignement de maintien de la paix</strong>, fondée sur des principes stricts :</p>
<ul>
<li>respect du mandat, de la Charte des Nations unies et du droit international, dont les droits humains ;</li>
<li>respect de la souveraineté de l'État hôte et des États voisins ;</li>
<li>exclusion des activités clandestines ;</li>
<li>finalité limitée : sûreté du personnel, protection des civils, mise en œuvre du mandat ;</li>
<li>protection des sources et de l'information.</li>
</ul>
<h3>Qui produit le renseignement dans une mission ?</h3>
<p>Plusieurs composantes contribuent : la composante militaire, la police, les sections civiles (affaires politiques, droits humains, protection des civils), la sûreté. Leur production est rassemblée par des structures intégrées, présentées dans l'approfondissement de ce chapitre.</p>
<div class="callout"><strong>À retenir.</strong> Dans une mission de paix, le renseignement est encadré par le mandat et par le droit. Il sert d'abord à protéger : les civils, les personnels et la mission elle-même.</div>
HTML],
      ],
      'quiz' => [
        ['Quels sont les trois principes fondamentaux du maintien de la paix ?', ['Vitesse, surprise, puissance', 'Consentement, impartialité, non-recours à la force sauf légitime défense et défense du mandat', 'Secret, contrôle, sanction', 'Neutralité, silence, retrait'], 1, 'Ces trois principes encadrent toutes les opérations de paix.'],
        ['La politique de renseignement de maintien de la paix de l\'ONU exclut :', ['L\'analyse de sources ouvertes', 'Les activités clandestines', 'La protection des sources', 'Le partage interne à la mission'], 1, 'Le renseignement de maintien de la paix respecte la souveraineté et exclut toute activité clandestine.'],
        ['La finalité première du renseignement dans une mission de paix est :', ['Influencer les élections', 'Protéger les civils, le personnel et la mise en œuvre du mandat', 'Surveiller l\'État hôte', 'Collecter des données commerciales'], 1, 'Sa finalité est limitée par le mandat.'],
      ],
    ],
    [
      'title' => 'Protection des civils et analyse des menaces',
      'summary' => 'Anticiper les violences contre les populations et proposer des mesures de protection.',
      'lessons' => [
        ['title' => 'Analyser les menaces pesant sur les civils', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>La <strong>protection des civils</strong> figure au cœur de la plupart des mandats récents. Le renseignement y contribue en répondant à trois questions : qui menace qui, où et quand ? Quelles sont les vulnérabilités des populations ? Quelles capacités de protection existent ?</p>
<h3>La matrice menaces, vulnérabilités, capacités</h3>
<table><thead><tr><th>Élément</th><th>Questions</th></tr></thead><tbody>
<tr><td>Menaces</td><td>Quels acteurs, quelles motivations, quels modes d'action déjà observés contre les civils ?</td></tr>
<tr><td>Vulnérabilités</td><td>Villages isolés, déplacés, marchés, points d'eau, trajets vers les champs, périodes sensibles</td></tr>
<tr><td>Capacités de protection</td><td>Forces de l'État, mission, autorités locales, mécanismes communautaires, acteurs humanitaires</td></tr>
</tbody></table>
<h3>Le temps et le calendrier</h3>
<p>Les violences suivent souvent des calendriers : jours de marché, élections, transhumance, récoltes, anniversaires d'événements. Un calendrier des périodes sensibles est un outil simple et très efficace.</p>
<h3>Les sources au plus près des populations</h3>
<p>Les populations sont les premières informées des menaces. Des mécanismes d'alerte communautaire peuvent transmettre rapidement une alerte, à condition de <strong>ne jamais exposer ceux qui parlent</strong>. Le principe « ne pas nuire » prime : une information obtenue au prix de la sécurité d'un informateur ou d'un village est une faute.</p>
<div class="callout"><strong>À retenir.</strong> Le produit attendu n'est pas un simple constat : c'est une recommandation de protection, à temps, pour ceux qui peuvent agir.</div>
HTML],
      ],
      'quiz' => [
        ['La matrice d\'analyse de la protection des civils croise :', ['Prix, stocks et ventes', 'Menaces, vulnérabilités et capacités de protection', 'Armes, munitions et véhicules', 'Partis, sondages et votes'], 1, 'Elle permet de prioriser les mesures de protection.'],
        ['Pourquoi établir un calendrier des périodes sensibles ?', ['Pour planifier les congés', 'Parce que les violences suivent souvent des rythmes : marchés, élections, transhumance', 'Pour remplacer l\'analyse', 'C\'est une obligation administrative'], 1, 'Anticiper les périodes à risque permet d\'adapter la protection.'],
        ['Le principe « ne pas nuire » impose :', ['De publier toutes les sources', 'De ne jamais exposer les personnes qui transmettent une alerte', 'De refuser toute information locale', 'D\'informer les groupes armés'], 1, 'La sécurité des informateurs et des communautés prime.'],
      ],
    ],
    [
      'title' => 'Coopération et partage du renseignement',
      'summary' => 'Partager entre partenaires sans perdre la confiance ni la maîtrise de l\'information.',
      'lessons' => [
        ['title' => 'Partager entre partenaires', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Les menaces transfrontalières (groupes armés, trafics, enlèvements) rendent la <strong>coopération régionale</strong> indispensable. Pourtant, le partage du renseignement reste difficile : manque de confiance, crainte de compromettre des sources, niveaux de classification différents, langues et procédures hétérogènes.</p>
<h3>Des mécanismes africains de coopération</h3>
<p>Plusieurs cadres existent sur le continent : le Comité des services de renseignement et de sécurité de l'Afrique (CISSA), créé en 2004, le Système continental d'alerte rapide de l'Union africaine, des cellules régionales de liaison et les structures des communautés économiques régionales. Leur efficacité dépend de la confiance construite entre professionnels.</p>
<h3>Les règles d'un partage maîtrisé</h3>
<ul>
<li><strong>Contrôle de l'émetteur</strong> : celui qui fournit une information décide de sa rediffusion éventuelle ;</li>
<li><strong>Besoin d'en connaître</strong> et marquage clair des restrictions de diffusion ;</li>
<li><strong>Assainissement</strong> : partager le jugement utile sans révéler la source ou la méthode ;</li>
<li><strong>Accords écrits</strong> précisant les finalités autorisées, la protection des données et le respect du droit ;</li>
<li><strong>Réciprocité et retour</strong> : informer le partenaire de l'usage et de la valeur de ce qu'il a fourni.</li>
</ul>
<h3>Partager sans se rendre complice</h3>
<p>Un État ou une mission ne doit pas transmettre une information s'il existe un risque sérieux qu'elle soit utilisée pour commettre des violations graves (torture, exécutions, détentions arbitraires). Les accords de partage doivent prévoir ces garanties.</p>
<div class="callout"><strong>À retenir.</strong> La confiance se construit lentement et se perd en une fuite. Un partage efficace est un partage encadré.</div>
HTML],
      ],
      'quiz' => [
        ['Le principe du contrôle de l\'émetteur signifie que :', ['Le destinataire rediffuse librement', 'Celui qui fournit l\'information décide de sa rediffusion', 'L\'information est publique', 'Seul le président décide'], 1, 'Il protège les sources et la confiance entre partenaires.'],
        ['« Assainir » une information avant partage consiste à :', ['La supprimer', 'Transmettre le jugement utile sans révéler la source ou la méthode', 'La traduire', 'L\'imprimer'], 1, 'On partage la substance en protégeant ce qui doit l\'être.'],
        ['Un État doit refuser de partager une information lorsque :', ['Le partenaire la demande poliment', 'Il existe un risque sérieux qu\'elle serve à des violations graves', 'Elle est en anglais', 'Elle est ancienne'], 1, 'Le partage ne doit jamais rendre complice de violations graves.'],
      ],
    ],
    [
      'title' => 'Droit, éthique et contrôle démocratique',
      'summary' => 'Le droit international humanitaire, les droits humains et le contrôle de la fonction renseignement.',
      'lessons' => [
        ['title' => 'Le cadre juridique et éthique', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<h3>Le droit international humanitaire</h3>
<p>En conflit armé, le droit international humanitaire (DIH) impose trois principes : <strong>distinction</strong> entre combattants et civils, <strong>proportionnalité</strong> et <strong>précaution</strong>. Le renseignement y joue un rôle protecteur : une information exacte, à jour et nuancée permet au commandement de respecter ces principes et d'éviter des pertes civiles. Une information erronée ou biaisée peut, à l'inverse, conduire à des violations graves.</p>
<h3>Les droits humains en toutes circonstances</h3>
<p>L'interdiction de la torture et des traitements cruels, inhumains ou dégradants est <strong>absolue</strong> : aucune circonstance, aucune urgence ne la justifie. Les <em>Lignes directrices de Robben Island</em> (Commission africaine des droits de l'homme et des peuples, 2002) en rappellent les exigences pour le continent. Tout entretien avec une personne détenue relève de règles strictes et de personnels habilités.</p>
<h3>Le contrôle démocratique</h3>
<p>Le renseignement militaire est soumis à plusieurs niveaux de contrôle : hiérarchique, inspections internes, autorités judiciaires, commissions parlementaires, parfois organes indépendants. Les Nations unies ont recensé des bonnes pratiques en la matière (compilation A/HRC/14/46, 2010) : base légale claire, mandat défini, contrôle externe, voies de recours.</p>
<h3>Les données personnelles</h3>
<p>Les bases de données et les listes constituées en opérations contiennent des informations sur des personnes. Elles doivent être justifiées, protégées, limitées dans le temps et corrigées en cas d'erreur.</p>
<div class="callout"><strong>À retenir.</strong> Le droit n'est pas l'adversaire du renseignement : il en est la condition de légitimité, d'efficacité durable et de confiance des populations.</div>
HTML],
      ],
      'quiz' => [
        ['Quels sont les trois grands principes du DIH dans la conduite des hostilités ?', ['Vitesse, masse, surprise', 'Distinction, proportionnalité, précaution', 'Secret, silence, discrétion', 'Commandement, contrôle, communication'], 1, 'Le renseignement aide le commandement à les respecter.'],
        ['L\'interdiction de la torture est :', ['Levée en cas d\'urgence', 'Absolue, sans aucune exception', 'Limitée au temps de paix', 'Laissée à l\'appréciation du chef'], 1, 'Aucune circonstance ne peut la justifier.'],
        ['Les Lignes directrices de Robben Island portent sur :', ['Le commerce maritime', 'La prévention de la torture en Afrique', 'Les élections', 'La pêche'], 1, 'Adoptées en 2002 par la Commission africaine des droits de l\'homme et des peuples.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Analyse de protection des civils et règles de partage sur un scénario fictif.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Le support de cours de la formation : principes, matrices d\'analyse et règles de partage.</p>'],
        ['title' => 'Exercice : matrice de protection des civils', 'type' => 'exercise', 'minutes' => 50, 'content' => <<<'HTML'
<div class="note"><strong>Scénario entièrement fictif.</strong> Pays, organisations, personnes et données sont imaginaires.</div>
<p>Une mission régionale fictive est déployée dans la province de Lowani (pays imaginaire), où des déplacés ont rejoint trois villages après des violences. La saison des récoltes commence ; un marché transfrontalier se tient chaque jeudi ; deux groupes armés fictifs se disputent le contrôle d'une route.</p>
<h3>Consignes</h3>
<ol><li>Construisez la matrice menaces, vulnérabilités et capacités de protection.</li><li>Établissez un calendrier des périodes sensibles pour le mois à venir.</li><li>Proposez trois mesures de protection hiérarchisées et les acteurs responsables (mission, autorités, humanitaires).</li><li>Indiquez les informations à partager avec les partenaires et les précautions de partage.</li></ol>
HTML],
        ['title' => 'Étude de cas : l\'informateur exposé', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<div class="note"><strong>Cas fictif à visée pédagogique.</strong></div>
<p>Un rapport de patrouille mentionnant le nom et le village d'un habitant qui avait signalé des mouvements suspects est diffusé largement, puis transmis à un partenaire sans assainissement. Quelques jours plus tard, l'habitant est menacé et quitte la région ; plus personne dans le village n'accepte de parler à la mission.</p>
<h3>Questions</h3>
<ol><li>Quelles règles n'ont pas été respectées ?</li><li>Quelles conséquences pour la personne, pour la communauté et pour la mission ?</li><li>Quelles procédures auraient évité cette situation ?</li><li>Comment rétablir la confiance ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
];
