<?php
/** MODULE B — ESPIONNAGE ET RENSEIGNEMENT HUMAIN */
return [
[
  'code' => 'AFS-B101', 'category' => 'B', 'slug' => 'renseignement-humain-principes-ethique',
  'title' => 'Renseignement humain : principes et éthique',
  'subtitle' => 'Entretien, observation et évaluation des sources, dans un cadre légal et respectueux des personnes.',
  'summary' => 'Maîtriser les principes du renseignement d\'origine humaine tels qu\'ils peuvent être pratiqués légalement : conduite d\'entretiens consentis, observation non intrusive et évaluation rigoureuse de la fiabilité des sources.',
  'level' => 'intermediaire', 'hours' => 16, 'featured' => true,
  'description' => '<p>Le renseignement humain fascine autant qu\'il inquiète. Cette formation le ramène à ce qu\'il est dans la pratique professionnelle légitime : <strong>l\'art de recueillir, auprès de personnes consentantes, des informations fiables et utiles</strong>, puis de les évaluer avec méthode.</p><p>Les compétences enseignées sont directement transposables à l\'analyse de risques, à l\'enquête interne, au journalisme, à la recherche de terrain et à la diplomatie.</p>',
  'objectives' => "Expliquer les principes fondamentaux du renseignement humain\nIdentifier les limites légales et déontologiques de la collecte\nPréparer et conduire un entretien d'information consenti\nPratiquer une observation non intrusive d'un environnement public\nÉvaluer une source et une information selon une grille standard",
  'skills' => "Préparer un guide d'entretien\nPratiquer l'écoute active et le questionnement ouvert\nNoter et restituer fidèlement des propos\nCoter une source (fiabilité / crédibilité)\nIdentifier les signaux de manipulation",
  'prerequisites' => "Fondements du renseignement recommandés\nQualités relationnelles et sens de l'écoute",
  'chapters' => [
    [
      'title' => 'Principes fondamentaux du renseignement humain',
      'summary' => 'Relation, besoin, confiance et responsabilité.',
      'lessons' => [
        ['title' => 'Briefing vidéo : la dimension humaine', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les quatre principes qui guident toute collecte humaine légitime.</p>'],
        ['title' => 'Les principes du HUMINT', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Toute collecte humaine repose sur une <strong>relation</strong> entre un professionnel et une personne qui accepte de partager ce qu'elle sait. Quatre principes structurent cette relation.</p>
<h3>1. Le besoin d'en connaître</h3>
<p>On ne recueille que ce qui répond à une question précise. Interroger « au cas où » expose inutilement les personnes et noie l'analyse.</p>
<h3>2. Le consentement éclairé</h3>
<p>Dans un cadre privé ou académique, la personne sait à qui elle parle, pourquoi, et ce que deviendront ses propos. Elle peut refuser de répondre ou retirer son accord.</p>
<h3>3. La protection</h3>
<p>Le professionnel est responsable de la sécurité et de la confidentialité de ses interlocuteurs. Une source exposée par négligence est une faute professionnelle grave.</p>
<h3>4. Le scepticisme méthodique</h3>
<p>La sympathie pour un interlocuteur ne doit jamais remplacer l'évaluation. Chaque information est recoupée.</p>
<table><thead><tr><th>Profil d'interlocuteur</th><th>Apport typique</th><th>Vigilance</th></tr></thead><tbody>
<tr><td>Expert académique</td><td>Analyse de fond, contexte historique</td><td>Biais d'école de pensée</td></tr>
<tr><td>Acteur économique local</td><td>Réalités du terrain, prix, flux</td><td>Intérêts commerciaux</td></tr>
<tr><td>Responsable associatif</td><td>Perception des populations</td><td>Engagement militant</td></tr>
<tr><td>Fonctionnaire (propos publics)</td><td>Position officielle</td><td>Communication institutionnelle</td></tr>
</tbody></table>
HTML],
      ],
      'quiz' => [
        ['Que signifie le principe du « besoin d\'en connaître » ?', ['Tout savoir sur chaque interlocuteur', 'Ne recueillir que ce qui répond à une question précise', 'Partager toutes les informations avec l\'équipe', 'Garder les sources secrètes à vie'], 1, 'La collecte doit être ciblée sur un besoin défini.'],
        ['Dans un cadre privé, le consentement éclairé implique que l\'interlocuteur :', ['Ignore la finalité de l\'échange', 'Sache à qui il parle et ce que deviendront ses propos', 'Soit rémunéré', 'Signe un contrat de travail'], 1, 'Transparence sur l\'identité et la finalité : c\'est la base de l\'éthique.'],
        ['Quelle vigilance s\'impose face à un acteur économique local ?', ['Aucune, il est neutre', 'Tenir compte de ses intérêts commerciaux', 'Refuser tout échange', 'Exiger des preuves écrites'], 1, 'Chaque profil a ses biais : ici, les intérêts commerciaux.'],
      ],
    ],
    [
      'title' => 'Collecte éthique et légale d\'informations',
      'summary' => 'Le droit applicable et les lignes rouges.',
      'lessons' => [
        ['title' => 'Cadre juridique de la collecte', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>La légalité de la collecte dépend à la fois <strong>du statut de celui qui collecte</strong> (service de l'État, entreprise, chercheur, journaliste) et <strong>des moyens employés</strong>.</p>
<h3>Textes de référence (à adapter au pays d'exercice)</h3>
<ul>
<li>Constitutions nationales : droit à la vie privée, secret des correspondances.</li>
<li>Lois nationales sur la protection des données personnelles et autorités de contrôle associées.</li>
<li>Convention de l'Union africaine sur la cybersécurité et la protection des données à caractère personnel (Convention de Malabo, 2014).</li>
<li>Code pénal : corruption, atteinte au secret professionnel, usurpation d'identité, intrusion.</li>
<li>Droit du travail et secret des affaires.</li>
</ul>
<h3>Les lignes rouges absolues</h3>
<div class="warning"><ul class="mb-0"><li>Offrir un avantage pour obtenir une information confidentielle (corruption) ;</li><li>Menacer, faire pression, exploiter une vulnérabilité (chantage, abus de faiblesse) ;</li><li>Usurper une identité ou une qualité (se dire policier, journaliste, auditeur…) ;</li><li>Enregistrer une conversation privée à l'insu de l'interlocuteur lorsque la loi l'interdit ;</li><li>Inciter quelqu'un à violer son obligation de confidentialité.</li></ul></div>
<h3>Le test des quatre questions</h3>
<ol><li>Est-ce légal dans le pays concerné ?</li><li>Serais-je à l'aise si la méthode était rendue publique ?</li><li>L'interlocuteur aurait-il accepté s'il avait su ?</li><li>Le bénéfice attendu justifie-t-il le risque pour les personnes ?</li></ol>
<p>Une seule réponse négative suffit à abandonner la démarche.</p>
HTML],
      ],
      'quiz' => [
        ['Laquelle de ces pratiques constitue une ligne rouge absolue ?', ['Préparer un guide d\'entretien', 'Se présenter faussement comme policier', 'Prendre des notes avec l\'accord de l\'interlocuteur', 'Citer une source publique'], 1, 'L\'usurpation d\'identité ou de qualité est illégale et contraire à l\'éthique.'],
        ['Quel texte africain encadre la protection des données personnelles à l\'échelle continentale ?', ['La Convention de Malabo', 'Le Traité de Rome', 'La Charte de Kouroukan Fouga', 'Le Protocole de Kyoto'], 0, 'La Convention de l\'UA de 2014, dite de Malabo.'],
        ['Selon le test des quatre questions, que faire si une seule réponse est négative ?', ['Poursuivre avec prudence', 'Abandonner la démarche', 'Demander l\'avis de la source', 'Changer de pays'], 1, 'Une seule réponse négative suffit à renoncer.'],
      ],
    ],
    [
      'title' => 'Techniques d\'entretien et d\'observation non intrusive',
      'summary' => 'Conduire un entretien d\'information et observer un espace public.',
      'lessons' => [
        ['title' => 'L\'entretien d\'information', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>L'entretien d'information est une conversation <strong>préparée, transparente et structurée</strong>. Il s'inspire des méthodes du journalisme et des sciences sociales.</p>
<h3>Préparation</h3>
<ul><li>Objectif écrit en une phrase ;</li><li>Recherche préalable sur le sujet (pour poser des questions intelligentes) ;</li><li>Guide d'entretien : 5 à 8 questions ouvertes, du général au particulier ;</li><li>Logistique : lieu neutre et confortable, durée annoncée.</li></ul>
<h3>Conduite : la méthode de l'entonnoir</h3>
<ol><li><strong>Accueil</strong> : présentation, finalité, règles de confidentialité, accord sur la prise de notes.</li><li><strong>Questions ouvertes</strong> : « Comment décririez-vous… ? », « Que s'est-il passé ensuite ? »</li><li><strong>Approfondissement</strong> : reformulation, relances neutres, demandes d'exemples.</li><li><strong>Questions fermées</strong> de vérification : dates, chiffres, noms publics.</li><li><strong>Clôture</strong> : « Y a-t-il un point que je n'ai pas abordé ? », remerciements.</li></ol>
<h3>Écoute active</h3>
<p>Silence accueillant, reformulation, attention au non-verbal, absence de jugement. On évite les questions suggestives (« Vous êtes d'accord que… ? ») qui contaminent la réponse.</p>
<h3>Observation non intrusive</h3>
<p>Observer un <strong>espace public</strong> (marché, gare, salon professionnel) permet de comprendre des flux, des usages, une ambiance — sans jamais suivre, photographier ou ficher des individus. On note des tendances (affluence, horaires, types d'activités), pas des identités.</p>
<div class="note">Après l'entretien, rédigez votre compte rendu dans l'heure : la mémoire se dégrade très vite. Distinguez verbatim, paraphrase et impressions personnelles.</div>
HTML],
      ],
      'quiz' => [
        ['Dans la méthode de l\'entonnoir, quand pose-t-on les questions fermées ?', ['Au tout début', 'Après les questions ouvertes, pour vérifier des précisions', 'Jamais', 'Uniquement par écrit'], 1, 'On part du général (ouvert) pour aller vers la vérification (fermé).'],
        ['Laquelle de ces questions est suggestive ?', ['Comment décririez-vous la situation ?', 'Vous êtes d\'accord que la situation est grave ?', 'Que s\'est-il passé ensuite ?', 'Pouvez-vous donner un exemple ?'], 1, 'Elle oriente la réponse et contamine l\'information.'],
        ['L\'observation non intrusive d\'un espace public porte sur :', ['L\'identité des passants', 'Les tendances et usages collectifs', 'Le suivi d\'une personne précise', 'La photographie des visages'], 1, 'On observe des flux et des usages, jamais des individus.'],
      ],
    ],
    [
      'title' => 'Évaluation de la fiabilité des sources',
      'summary' => 'La cotation à deux dimensions et la détection des manipulations.',
      'lessons' => [
        ['title' => 'Coter une source et une information', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le système de cotation le plus répandu, souvent appelé <strong>code de l'Amirauté</strong> ou système OTAN, évalue séparément <strong>la source</strong> (lettre) et <strong>l'information</strong> (chiffre).</p>
<table><thead><tr><th>Fiabilité de la source</th><th>Crédibilité de l'information</th></tr></thead><tbody>
<tr><td>A — Totalement fiable</td><td>1 — Confirmée par d'autres sources</td></tr>
<tr><td>B — Habituellement fiable</td><td>2 — Probablement vraie</td></tr>
<tr><td>C — Assez fiable</td><td>3 — Possiblement vraie</td></tr>
<tr><td>D — Pas toujours fiable</td><td>4 — Douteuse</td></tr>
<tr><td>E — Non fiable</td><td>5 — Improbable</td></tr>
<tr><td>F — Fiabilité impossible à évaluer</td><td>6 — Véracité impossible à évaluer</td></tr>
</tbody></table>
<p>Une source habituellement fiable peut transmettre une information douteuse (B4), et une source inconnue une information confirmée (F1). C'est tout l'intérêt de la double cotation.</p>
<h3>Critères d'évaluation de la source</h3>
<ul><li><strong>Accès</strong> : était-elle en position de savoir ?</li><li><strong>Historique</strong> : ses informations passées se sont-elles vérifiées ?</li><li><strong>Motivation</strong> : quel intérêt a-t-elle à parler ?</li><li><strong>Cohérence</strong> interne du récit.</li></ul>
<h3>Signaux d'alerte</h3>
<ul><li>Information trop parfaite, qui répond exactement à ce que l'on espérait ;</li><li>Détails invérifiables abondants, précision factice ;</li><li>Pression pour une décision rapide ;</li><li>Sources multiples qui partagent en réalité une même origine (<em>rapport circulaire</em>).</li></ul>
HTML],
      ],
      'quiz' => [
        ['Que signifie la cotation « B2 » ?', ['Source non fiable, information confirmée', 'Source habituellement fiable, information probablement vraie', 'Source inconnue, information douteuse', 'Source totalement fiable, information improbable'], 1, 'La lettre cote la source, le chiffre cote l\'information.'],
        ['Pourquoi coter séparément la source et l\'information ?', ['Par tradition militaire uniquement', 'Car une bonne source peut transmettre une information erronée et inversement', 'Pour simplifier le travail', 'Pour protéger l\'identité de la source'], 1, 'Les deux dimensions sont indépendantes.'],
        ['Qu\'est-ce qu\'un « rapport circulaire » ?', ['Un rapport diffusé à tous les services', 'Plusieurs sources apparentes qui reposent sur une même origine', 'Un rapport sans conclusion', 'Une note de synthèse mensuelle'], 1, 'Il donne une fausse impression de confirmation.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Simulation d\'entretien et étude de cas sur la cotation.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Guide d\'entretien type, grille de cotation et test des quatre questions.</p>'],
        ['title' => 'Exercice : préparer un guide d\'entretien', 'type' => 'exercise', 'minutes' => 40, 'content' => <<<'HTML'
<p><strong>Situation fictive.</strong> Une ONG de développement souhaite comprendre pourquoi un marché agricole régional a perdu la moitié de sa fréquentation en un an. Vous allez rencontrer, avec leur accord, un responsable de coopérative.</p>
<h3>À produire</h3>
<ol><li>L'objectif de l'entretien en une phrase.</li><li>Un texte d'introduction (présentation, finalité, confidentialité).</li><li>Six questions ouvertes ordonnées en entonnoir.</li><li>Trois relances neutres.</li><li>Un modèle de compte rendu distinguant verbatim, paraphrase et impressions.</li></ol>
<div class="note">Critères d'évaluation : absence de questions suggestives, transparence sur la finalité, pertinence des relances, respect de la confidentialité.</div>
HTML],
        ['title' => 'Étude de cas : la source trop parfaite', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Un cabinet d'analyse reçoit d'un contact rencontré récemment des informations très détaillées annonçant la faillite imminente d'un concurrent de son client. Le contact insiste pour que l'information soit utilisée « dans les 48 heures ».</p>
<h3>Questions</h3>
<ol><li>Quelle cotation provisoire attribuez-vous à la source et à l'information ? Justifiez.</li><li>Listez les signaux d'alerte présents.</li><li>Quelles vérifications ouvertes et légales mèneriez-vous (registres de commerce, publications légales, résultats financiers publiés) ?</li><li>Quels risques juridiques le client encourt-il s'il agit sur cette base (diffusion de fausses informations, manipulation de marché) ?</li></ol>
<p><strong>Dénouement :</strong> l'information était une tentative de manipulation destinée à pousser le client à une communication publique préjudiciable. La prudence méthodique a protégé le client.</p>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-B201', 'category' => 'B', 'slug' => 'protection-des-sources-et-redaction',
  'title' => 'Protection des sources, analyse comportementale et rédaction',
  'subtitle' => 'Protéger ceux qui parlent, interpréter avec prudence, écrire pour décider.',
  'summary' => 'Trois compétences qui distinguent le professionnel : garantir la confidentialité de ses sources, faire un usage scientifiquement honnête de l\'analyse comportementale et rédiger des rapports clairs, sourcés et actionnables.',
  'level' => 'avance', 'hours' => 14, 'featured' => false,
  'description' => '<p>Cette formation avancée complète le parcours HUMINT. Elle aborde la <strong>protection des sources</strong> comme une obligation morale et juridique, déconstruit les mythes de la « détection du mensonge » à la lumière de la recherche scientifique, et entraîne à la rédaction de produits de renseignement de qualité professionnelle.</p>',
  'objectives' => "Mettre en place des mesures de protection et de confidentialité des sources\nDistinguer les apports réels et les limites scientifiques de l'analyse comportementale\nRédiger un rapport de renseignement structuré, sourcé et nuancé\nAdapter un produit écrit à son destinataire",
  'skills' => "Pseudonymiser et cloisonner des informations sensibles\nÉviter les conclusions abusives fondées sur le non-verbal\nAppliquer la structure BLUF (conclusion d'abord)\nUtiliser un vocabulaire de probabilité normalisé",
  'prerequisites' => "Renseignement humain : principes et éthique (recommandé)\nTrès bonne expression écrite",
  'chapters' => [
    [
      'title' => 'Protection des sources et confidentialité',
      'summary' => 'Pseudonymisation, cloisonnement et sécurité des échanges.',
      'lessons' => [
        ['title' => 'Briefing vidéo : protéger, interpréter, écrire', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les trois piliers de la formation en un coup d\'œil.</p>'],
        ['title' => 'Protéger ses sources', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>La protection des sources est un principe partagé par le renseignement, le journalisme (consacré par de nombreuses législations africaines sur la presse) et la recherche en sciences sociales. Elle repose sur trois mécanismes.</p>
<h3>1. La pseudonymisation</h3>
<p>Dans les documents de travail, la source est désignée par un code (ex. <code>S-014</code>). La table de correspondance est conservée séparément, avec un accès restreint.</p>
<h3>2. Le cloisonnement</h3>
<p>Seules les personnes qui en ont besoin connaissent l'identité d'une source. Les rapports diffusés décrivent l'<em>accès</em> de la source (« une personne ayant une connaissance directe du secteur ») sans permettre de l'identifier.</p>
<h3>3. L'hygiène numérique</h3>
<ul><li>Messageries chiffrées de bout en bout pour les échanges ;</li><li>Chiffrement des supports (ordinateurs, clés USB) ;</li><li>Attention aux métadonnées des fichiers et des photos ;</li><li>Politique de conservation : supprimer ce qui n'est plus nécessaire.</li></ul>
<div class="warning"><strong>Risque de recoupement.</strong> Même sans nom, un détail (fonction rare, lieu, date d'une réunion à trois participants) peut suffire à identifier quelqu'un. Relisez chaque rapport avec ce regard.</div>
HTML],
      ],
      'quiz' => [
        ['À quoi sert la pseudonymisation d\'une source ?', ['À la rémunérer discrètement', 'À la désigner par un code dans les documents de travail', 'À la rendre anonyme pour toujours', 'À vérifier sa fiabilité'], 1, 'Le code remplace l\'identité ; la correspondance est stockée à part.'],
        ['Pourquoi un rapport sans nom peut-il malgré tout exposer une source ?', ['À cause de la police de caractères', 'Parce que des détails contextuels peuvent permettre de l\'identifier', 'Parce que les rapports sont publics', 'Ce n\'est pas possible'], 1, 'Le risque de recoupement impose une relecture attentive.'],
        ['Quelle mesure relève de l\'hygiène numérique ?', ['Publier les rapports en ligne', 'Chiffrer les supports de stockage', 'Partager la table de correspondance', 'Conserver tous les fichiers indéfiniment'], 1, 'Le chiffrement protège en cas de perte ou de vol.'],
      ],
    ],
    [
      'title' => 'Analyse comportementale et limites scientifiques',
      'summary' => 'Ce que la science dit vraiment du mensonge et du non-verbal.',
      'lessons' => [
        ['title' => 'Mythes et réalités du comportement', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>Films et séries ont popularisé l'idée qu'un regard fuyant ou un geste de la main trahirait le mensonge. <strong>La recherche scientifique contredit largement ces croyances.</strong></p>
<h3>Ce que montrent les études</h3>
<ul><li>Les méta-analyses de la recherche en psychologie montrent que la capacité moyenne à détecter le mensonge à partir du comportement dépasse à peine le hasard (de l'ordre de 54 %).</li><li>Il n'existe <strong>aucun indice non verbal universel</strong> du mensonge, à la manière du « nez de Pinocchio ».</li><li>Le stress d'une personne honnête soupçonnée peut produire les mêmes signes que ceux attribués aux menteurs.</li><li>Les variations culturelles du contact visuel, de la gestuelle ou des silences sont considérables — un point crucial sur un continent aussi divers que l'Afrique.</li></ul>
<h3>Ce qui fonctionne mieux</h3>
<ul><li>Les approches centrées sur le <strong>contenu verbal</strong> : richesse en détails vérifiables, cohérence, plausibilité ;</li><li>La <strong>vérification factuelle</strong> des éléments avancés ;</li><li>Les entretiens non accusatoires, qui favorisent un récit libre et détaillé.</li></ul>
<div class="warning">Ne jamais fonder un jugement sur une personne (recrutement, soupçon, sanction) à partir d'une « lecture » du comportement. C'est scientifiquement infondé et potentiellement discriminatoire.</div>
HTML],
      ],
      'quiz' => [
        ['Que montrent les méta-analyses sur la détection du mensonge à partir du comportement ?', ['Une précision supérieure à 90 %', 'Une précision à peine supérieure au hasard', 'Une précision parfaite chez les experts', 'Aucune étude n\'existe'], 1, 'Autour de 54 % : guère mieux que le hasard.'],
        ['Existe-t-il un signe non verbal universel du mensonge ?', ['Oui, le regard fuyant', 'Oui, le toucher du nez', 'Non, aucun indice universel n\'a été établi', 'Oui, la transpiration'], 2, 'La recherche n\'a identifié aucun indice universel.'],
        ['Quelle approche est jugée plus fiable ?', ['L\'observation des micro-expressions seules', 'L\'analyse du contenu verbal et la vérification des faits', 'Le détecteur de mensonge artisanal', 'L\'intuition de l\'enquêteur'], 1, 'Le contenu vérifiable et les entretiens non accusatoires donnent de meilleurs résultats.'],
      ],
    ],
    [
      'title' => 'Rédaction de rapports de renseignement',
      'summary' => 'Structure BLUF, sourcing, nuances et lisibilité.',
      'lessons' => [
        ['title' => 'Écrire pour un décideur', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>Un décideur dispose de peu de temps. Le rapport doit lui permettre de comprendre <strong>l'essentiel en trente secondes</strong>, puis d'approfondir s'il le souhaite.</p>
<h3>La structure BLUF</h3>
<p><em>Bottom Line Up Front</em> : la conclusion d'abord.</p>
<ol><li><strong>Titre-jugement</strong> : une phrase qui dit l'essentiel (« La hausse des prix du transport devrait se poursuivre jusqu'en mars »).</li><li><strong>Résumé</strong> en trois à cinq lignes, avec niveau de confiance.</li><li><strong>Développement</strong> : faits, analyse, hypothèses alternatives.</li><li><strong>Lacunes et indicateurs</strong> à surveiller.</li><li><strong>Sources</strong> et cotations.</li></ol>
<h3>Règles d'écriture</h3>
<ul><li>Phrases courtes, voix active, un paragraphe = une idée ;</li><li>Vocabulaire de probabilité normalisé (presque certain, probable, possible…) ;</li><li>Distinction visible entre faits et jugements ;</li><li>Pas d'adjectifs dramatiques ni de jargon inutile.</li></ul>
<table><thead><tr><th>À éviter</th><th>À privilégier</th></tr></thead><tbody>
<tr><td>« La situation est explosive. »</td><td>« Trois incidents ont été signalés en une semaine contre un par mois en moyenne ; une escalade est possible (confiance faible). »</td></tr>
<tr><td>« Il semblerait que peut-être… »</td><td>« Il est probable que… »</td></tr>
</tbody></table>
HTML],
      ],
      'quiz' => [
        ['Que signifie BLUF ?', ['Brief, Long, Useful, Final', 'Bottom Line Up Front', 'Basic Level Understanding Format', 'Background Layout Under Focus'], 1, 'La conclusion est placée en tête du document.'],
        ['Quelle formulation est la plus professionnelle ?', ['La situation est explosive', 'Il semblerait que peut-être', 'Une escalade est possible (confiance faible), trois incidents en une semaine', 'Tout va mal'], 2, 'Faits chiffrés + jugement probabiliste + niveau de confiance.'],
        ['Que doit contenir la section « lacunes et indicateurs » ?', ['Les erreurs de frappe', 'Ce que l\'on ignore et ce qu\'il faut surveiller', 'La biographie de l\'analyste', 'Les remerciements'], 1, 'Elle prépare le suivi et l\'actualisation de l\'analyse.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Rédaction d\'un rapport complet et étude de cas sur une fuite.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Modèle de rapport BLUF, check-list de protection des sources et rappels scientifiques.</p>'],
        ['title' => 'Exercice : rédiger un rapport BLUF', 'type' => 'exercise', 'minutes' => 50, 'content' => <<<'HTML'
<p>À partir des éléments fictifs ci-dessous, rédigez un rapport d'une page.</p>
<ul><li>Fait 1 : le prix du carburant a augmenté de 18 % en deux mois dans la région (statistiques officielles).</li><li>Fait 2 : deux syndicats de transporteurs ont annoncé publiquement des consultations de leurs membres.</li><li>Fait 3 : un responsable de coopérative (source cotée B, information cotée 3) évoque un possible mouvement de grève.</li><li>Lacune : aucune date n'est connue.</li></ul>
<h3>Consignes</h3>
<ol><li>Un titre-jugement.</li><li>Un résumé de quatre lignes avec niveau de confiance.</li><li>Deux hypothèses alternatives.</li><li>Trois indicateurs à surveiller.</li><li>La source humaine décrite sans pouvoir être identifiée.</li></ol>
HTML],
        ['title' => 'Étude de cas : la fuite par recoupement', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Un rapport interne mentionne qu'« une source participant à la réunion restreinte du comité de direction du mardi » a signalé un projet de cession. Le rapport circule plus largement que prévu. En quelques heures, l'entreprise concernée identifie la source : seules quatre personnes assistaient à la réunion.</p>
<h3>Questions</h3>
<ol><li>Quelles erreurs de rédaction ont rendu l'identification possible ?</li><li>Réécrivez la mention de la source de manière protectrice.</li><li>Quelles mesures de diffusion auraient limité le risque ?</li><li>Quelles sont les responsabilités morales et juridiques de l'analyste ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 8, 'time' => 20],
],
];
