<?php
/** MODULE C — CONTRE-ESPIONNAGE */
return [
[
  'code' => 'AFS-C101', 'category' => 'C', 'slug' => 'contre-espionnage-menaces-protection',
  'title' => 'Contre-espionnage : menaces et protection de l\'information',
  'subtitle' => 'Identifier les menaces d\'ingérence et protéger ce qui compte vraiment.',
  'summary' => 'Comprendre les logiques d\'ingérence et d\'espionnage visant les organisations africaines, détecter les menaces internes et l\'ingénierie sociale, et mettre en place une protection proportionnée des informations sensibles.',
  'level' => 'intermediaire', 'hours' => 16, 'featured' => true,
  'description' => '<p>Le contre-espionnage n\'est pas l\'apanage des États. Entreprises, administrations, universités, ONG et startups africaines détiennent des informations convoitées : appels d\'offres, données clients, savoir-faire, positions de négociation, résultats de recherche.</p><p>Cette formation adopte une approche <strong>strictement défensive</strong> : comprendre les modes opératoires pour mieux s\'en protéger, sans jamais basculer dans la surveillance abusive des personnels.</p>',
  'objectives' => "Définir le contre-espionnage et ses finalités défensives\nIdentifier les principaux vecteurs d'ingérence visant les organisations\nComprendre la menace interne sans stigmatiser les personnels\nClassifier et protéger les informations sensibles\nReconnaître et contrer les tentatives d'ingénierie sociale",
  'skills' => "Cartographier les actifs informationnels sensibles\nMettre en place une politique de classification simple\nSensibiliser des équipes aux manipulations\nRédiger une procédure de signalement",
  'prerequisites' => "Fondements du renseignement recommandés\nExpérience professionnelle en organisation appréciée",
  'chapters' => [
    [
      'title' => 'Introduction au contre-espionnage',
      'summary' => 'Définitions, acteurs et modes opératoires.',
      'lessons' => [
        ['title' => 'Briefing vidéo : penser comme un défenseur', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Les principes de la posture défensive en contre-espionnage.</p>'],
        ['title' => 'Comprendre l\'ingérence', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Le contre-espionnage regroupe l'ensemble des mesures visant à <strong>détecter, prévenir et neutraliser</strong> les activités de renseignement hostiles dirigées contre une organisation ou un État. Pour une organisation privée, il s'agit essentiellement de <strong>protection</strong> et de <strong>vigilance</strong> ; les enquêtes et sanctions relèvent des autorités judiciaires.</p>
<h3>Qui cherche à obtenir vos informations ?</h3>
<ul><li>Des <strong>concurrents</strong> peu scrupuleux (intelligence économique offensive illégale) ;</li><li>Des <strong>acteurs étatiques</strong> étrangers intéressés par des secteurs stratégiques (mines, énergie, télécoms, agriculture) ;</li><li>Des <strong>groupes criminels</strong> (fraude, rançongiciels, extorsion) ;</li><li>Des <strong>individus</strong> motivés par la vengeance, l'idéologie ou l'argent.</li></ul>
<h3>Les modes opératoires classiques</h3>
<table><thead><tr><th>Vecteur</th><th>Exemple</th></tr></thead><tbody>
<tr><td>Approche humaine</td><td>Faux recruteur, consultant trop curieux, stagiaire sans encadrement</td></tr>
<tr><td>Numérique</td><td>Hameçonnage ciblé, compromission de messagerie</td></tr>
<tr><td>Physique</td><td>Vol d'ordinateur en déplacement, documents laissés en salle de réunion</td></tr>
<tr><td>Légal mais déloyal</td><td>Questions insistantes lors d'audits, de salons, de visites de site</td></tr>
</tbody></table>
<p>La plupart des pertes d'informations ne résultent pas d'opérations sophistiquées mais d'<strong>imprudences ordinaires</strong> : conversations dans un avion, publications sur les réseaux sociaux, mots de passe partagés.</p>
HTML],
      ],
      'quiz' => [
        ['Pour une organisation privée, le contre-espionnage consiste principalement à :', ['Mener des enquêtes judiciaires', 'Protéger ses informations et rester vigilante', 'Surveiller la vie privée des salariés', 'Espionner ses concurrents'], 1, 'L\'approche est défensive ; les enquêtes relèvent des autorités.'],
        ['Quelle est la cause la plus fréquente des fuites d\'informations ?', ['Les opérations d\'espionnage sophistiquées', 'Les imprudences ordinaires', 'Les satellites', 'Les catastrophes naturelles'], 1, 'Conversations, réseaux sociaux, négligences : le facteur humain domine.'],
        ['Un consultant posant des questions insistantes hors de sa mission relève de :', ['L\'approche humaine', 'L\'imagerie satellitaire', 'La cryptographie', 'La comptabilité'], 0, 'C\'est un vecteur humain classique.'],
      ],
    ],
    [
      'title' => 'Menaces internes et risques d\'infiltration',
      'summary' => 'Prévenir sans stigmatiser.',
      'lessons' => [
        ['title' => 'La menace interne', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>La menace interne désigne le risque qu'une personne disposant d'un <strong>accès légitime</strong> (salarié, prestataire, partenaire) cause un préjudice, volontairement ou non.</p>
<h3>Trois profils</h3>
<ul><li><strong>Négligent</strong> : le plus fréquent — erreur, méconnaissance des règles ;</li><li><strong>Compromis</strong> : ses identifiants ou son poste sont utilisés par un tiers ;</li><li><strong>Malveillant</strong> : agit intentionnellement, souvent après une frustration non traitée.</li></ul>
<h3>Facteurs de risque organisationnels</h3>
<ul><li>Droits d'accès excessifs et jamais révisés ;</li><li>Départs mal gérés (comptes actifs après la fin du contrat) ;</li><li>Climat social dégradé, absence de canal d'écoute ;</li><li>Absence de séparation des tâches sensibles.</li></ul>
<h3>Mesures proportionnées</h3>
<ol><li><strong>Moindre privilège</strong> : chacun n'accède qu'à ce dont il a besoin ;</li><li><strong>Revue périodique</strong> des accès ;</li><li><strong>Procédures d'arrivée et de départ</strong> formalisées ;</li><li><strong>Culture de confiance</strong> : un canal de signalement protégé et un management attentif réduisent davantage le risque que la surveillance.</li></ol>
<div class="warning">La surveillance des salariés est strictement encadrée par le droit du travail et la protection des données : information préalable, proportionnalité, finalité légitime. Une surveillance généralisée détruit la confiance et peut être illégale.</div>
HTML],
      ],
      'quiz' => [
        ['Quel est le profil de menace interne le plus fréquent ?', ['Le malveillant', 'Le négligent', 'L\'agent étranger', 'Le pirate externe'], 1, 'L\'erreur et la méconnaissance des règles dominent.'],
        ['Le principe du moindre privilège consiste à :', ['Donner tous les accès aux cadres', 'N\'accorder que les accès nécessaires à la mission', 'Supprimer tous les accès', 'Payer moins les nouveaux salariés'], 1, 'Chacun n\'accède qu\'à ce dont il a besoin.'],
        ['Quelle mesure réduit durablement le risque interne ?', ['La surveillance généralisée des messageries', 'Une culture de confiance et un canal de signalement protégé', 'L\'interdiction des réseaux sociaux', 'Le licenciement préventif'], 1, 'Le climat organisationnel est un facteur clé.'],
      ],
    ],
    [
      'title' => 'Protection des informations sensibles',
      'summary' => 'Identifier, classifier et protéger.',
      'lessons' => [
        ['title' => 'Classifier et protéger', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>On ne peut pas tout protéger avec la même intensité. La démarche commence par l'identification du <strong>patrimoine informationnel critique</strong> : ce dont la perte, l'altération ou la divulgation causerait un préjudice grave.</p>
<h3>Étape 1 : inventaire</h3>
<p>Pour chaque information sensible : qui la détient, où est-elle stockée, qui y accède, quelle serait la conséquence d'une fuite ?</p>
<h3>Étape 2 : une classification simple</h3>
<table><thead><tr><th>Niveau</th><th>Exemples</th><th>Règles</th></tr></thead><tbody>
<tr><td>Public</td><td>Plaquette, site web</td><td>Diffusion libre</td></tr>
<tr><td>Interne</td><td>Notes de service, organigramme</td><td>Personnels uniquement</td></tr>
<tr><td>Confidentiel</td><td>Offres commerciales, données RH</td><td>Liste nominative, chiffrement</td></tr>
<tr><td>Secret organisationnel</td><td>Stratégie, négociations en cours</td><td>Accès très restreint, traçabilité</td></tr>
</tbody></table>
<h3>Étape 3 : mesures</h3>
<ul><li>Marquage visible des documents ;</li><li>Chiffrement des fichiers et des supports ;</li><li>Bureau et écran propres ; destruction sécurisée des papiers ;</li><li>Règles de discussion en lieux publics et en déplacement ;</li><li>Clauses de confidentialité avec les partenaires.</li></ul>
<div class="callout">Une classification trop complexe n'est pas appliquée. Quatre niveaux suffisent dans la plupart des organisations.</div>
HTML],
      ],
      'quiz' => [
        ['Par quoi commence une démarche de protection de l\'information ?', ['L\'achat d\'un logiciel', 'L\'inventaire du patrimoine informationnel critique', 'La classification de tout en secret', 'L\'installation de caméras'], 1, 'On identifie d\'abord ce qui doit être protégé.'],
        ['Pourquoi limiter le nombre de niveaux de classification ?', ['Par économie de papier', 'Parce qu\'un système trop complexe n\'est pas appliqué', 'Parce que la loi l\'impose', 'Pour faciliter les fuites'], 1, 'La simplicité favorise l\'adoption.'],
        ['Laquelle est une mesure de protection physique ?', ['Le chiffrement des courriels', 'La destruction sécurisée des documents papier', 'La revue des droits d\'accès', 'La classification'], 1, 'Broyage et politique du bureau propre relèvent de la sécurité physique.'],
      ],
    ],
    [
      'title' => 'Détection des tentatives d\'ingénierie sociale',
      'summary' => 'Les leviers psychologiques de la manipulation et les parades.',
      'lessons' => [
        ['title' => 'Reconnaître la manipulation', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>L'ingénierie sociale exploite des réflexes humains normaux — serviabilité, respect de l'autorité, peur — pour obtenir une information ou une action. Elle précède la majorité des cyberattaques réussies.</p>
<h3>Les leviers utilisés</h3>
<table><thead><tr><th>Levier</th><th>Formulation typique</th></tr></thead><tbody>
<tr><td>Autorité</td><td>« Le directeur général exige ce virement aujourd'hui. »</td></tr>
<tr><td>Urgence</td><td>« Votre compte sera bloqué dans une heure. »</td></tr>
<tr><td>Sympathie</td><td>« On s'est croisés au séminaire de Dakar, tu te souviens ? »</td></tr>
<tr><td>Réciprocité</td><td>Petit cadeau ou service rendu, puis demande</td></tr>
<tr><td>Preuve sociale</td><td>« Tous vos collègues ont déjà validé. »</td></tr>
</tbody></table>
<h3>Scénarios fréquents en Afrique</h3>
<ul><li>Fraude au président par messagerie instantanée ou appel vocal ;</li><li>Faux changement de coordonnées bancaires d'un fournisseur ;</li><li>Fausses offres d'emploi ou de bourses visant les jeunes diplômés ;</li><li>Faux techniciens d'opérateurs télécoms ou de services de paiement mobile.</li></ul>
<h3>Parades</h3>
<ol><li><strong>Vérifier par un autre canal</strong> (rappeler un numéro connu, jamais celui fourni) ;</li><li>Ne jamais céder à l'urgence artificielle : <em>« ralentir, c'est se protéger »</em> ;</li><li>Procédures de double validation pour les paiements ;</li><li>Signaler sans crainte : celui qui signale une tentative ne doit jamais être blâmé.</li></ol>
HTML],
      ],
      'quiz' => [
        ['« Votre compte sera bloqué dans une heure » exploite principalement le levier :', ['De la réciprocité', 'De l\'urgence', 'De la preuve sociale', 'De la sympathie'], 1, 'L\'urgence empêche la réflexion.'],
        ['Quelle est la meilleure parade face à une demande inhabituelle de virement ?', ['Exécuter rapidement', 'Vérifier par un autre canal connu', 'Répondre au même message', 'Demander à un collègue de valider sans vérifier'], 1, 'Le contre-appel par un canal indépendant déjoue la fraude au président.'],
        ['Un salarié qui signale une tentative d\'hameçonnage après avoir cliqué doit être :', ['Sanctionné', 'Remercié et accompagné', 'Ignoré', 'Licencié'], 1, 'Blâmer décourage les signalements, pourtant essentiels.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Cartographie des actifs et étude de cas de fraude.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Grille de classification, check-list anti-ingénierie sociale et procédure de signalement type.</p>'],
        ['title' => 'Exercice : cartographier les actifs sensibles', 'type' => 'exercise', 'minutes' => 45, 'content' => <<<'HTML'
<p><strong>Organisation fictive :</strong> « AgroSahel Coop », coopérative de 40 salariés qui exporte des produits transformés et prépare une levée de fonds.</p>
<h3>Consignes</h3>
<ol><li>Identifiez huit actifs informationnels (recettes, fichier clients, négociation financière, données RH…).</li><li>Classez-les selon la grille à quatre niveaux.</li><li>Pour les trois plus critiques, indiquez qui y accède aujourd'hui et qui <em>devrait</em> y accéder.</li><li>Proposez cinq mesures prioritaires, réalistes pour une PME.</li></ol>
HTML],
        ['title' => 'Étude de cas : la fraude au fournisseur', 'type' => 'case_study', 'minutes' => 30, 'content' => <<<'HTML'
<p><strong>Scénario pédagogique (fictif).</strong> Une société de BTP reçoit un courriel apparemment envoyé par son principal fournisseur annonçant un changement de banque. Le comptable met à jour les coordonnées. Deux virements importants partent vers le compte frauduleux avant que le vrai fournisseur ne réclame son paiement.</p>
<h3>Questions</h3>
<ol><li>Quels indices auraient dû alerter (domaine de l'adresse, ton, timing) ?</li><li>Quelle procédure de contrôle aurait empêché la fraude ?</li><li>Qui prévenir immédiatement après la découverte (banque, autorités, plateforme de signalement nationale) ?</li><li>Comment communiquer en interne sans désigner de coupable ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
[
  'code' => 'AFS-C201', 'category' => 'C', 'slug' => 'securite-organisationnelle-contre-mesures',
  'title' => 'Sécurité organisationnelle et contre-mesures défensives',
  'subtitle' => 'Sécurité du personnel, analyse des menaces et exercices de simulation.',
  'summary' => 'Construire une posture de sécurité organisationnelle complète : protection des personnes, analyse structurée des menaces, contre-mesures défensives et entraînement par la simulation.',
  'level' => 'avance', 'hours' => 18, 'featured' => false,
  'description' => '<p>Ce second volet du module C s\'adresse aux responsables sécurité, risk managers et cadres dirigeants. Il relie les dimensions humaine, physique, numérique et organisationnelle de la sécurité dans une démarche cohérente fondée sur l\'analyse de risques.</p>',
  'objectives' => "Organiser la sécurité des personnels, y compris en déplacement\nConduire une analyse de menaces structurée\nSélectionner des contre-mesures proportionnées\nConcevoir et animer un exercice de simulation de crise",
  'skills' => "Réaliser une matrice de risques\nRédiger un plan de sécurité des déplacements\nPrioriser des contre-mesures selon leur coût et leur efficacité\nAnimer un exercice sur table",
  'prerequisites' => "Contre-espionnage : menaces et protection de l'information\nResponsabilités managériales ou en sécurité appréciées",
  'chapters' => [
    [
      'title' => 'Sécurité du personnel et des organisations',
      'summary' => 'Protéger les personnes, en poste et en mission.',
      'lessons' => [
        ['title' => 'Briefing vidéo : la sécurité comme système', 'type' => 'video', 'minutes' => 4, 'content' => '<p>Vue d\'ensemble des couches de sécurité d\'une organisation.</p>'],
        ['title' => 'Protéger les personnes', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Les personnes sont à la fois la première ressource et la première cible. La sécurité du personnel couvre le cycle de vie professionnel et les situations de déplacement.</p>
<h3>Le cycle de vie</h3>
<ul><li><strong>Recrutement</strong> : vérifications proportionnées et légales (diplômes, références), sans enquête intrusive sur la vie privée ;</li><li><strong>Intégration</strong> : sensibilisation, charte, signature des engagements de confidentialité ;</li><li><strong>En poste</strong> : formation continue, entretiens réguliers ;</li><li><strong>Départ</strong> : restitution des équipements, clôture des accès, rappel des obligations.</li></ul>
<h3>Sécurité en déplacement</h3>
<table><thead><tr><th>Avant</th><th>Pendant</th><th>Après</th></tr></thead><tbody>
<tr><td>Analyse du pays (avis officiels), itinéraire, contacts d'urgence, équipement informatique dédié</td><td>Discrétion, points de contact réguliers, vigilance sur les réseaux Wi-Fi publics</td><td>Débriefing, signalement d'incidents, contrôle des appareils</td></tr>
</tbody></table>
<div class="callout">Le devoir de protection de l'employeur (<em>duty of care</em>) impose d'évaluer les risques avant d'envoyer un collaborateur en mission et de prévoir les moyens d'assistance.</div>
HTML],
      ],
      'quiz' => [
        ['Quelle vérification est appropriée lors d\'un recrutement ?', ['Une enquête sur la vie familiale', 'La vérification proportionnée des diplômes et références', 'La consultation de ses messages privés', 'Le suivi de ses déplacements personnels'], 1, 'Les vérifications doivent rester légales et proportionnées.'],
        ['Que doit inclure la procédure de départ d\'un salarié ?', ['Rien de particulier', 'La clôture des accès et la restitution des équipements', 'La surveillance de son nouvel emploi', 'La suppression de son contrat'], 1, 'Des comptes actifs après départ sont un risque majeur.'],
        ['Le « duty of care » désigne :', ['Le devoir de protection de l\'employeur envers ses collaborateurs', 'Une assurance automobile', 'Une technique d\'entretien', 'Un niveau de classification'], 0, 'Il impose d\'évaluer et de couvrir les risques des missions.'],
      ],
    ],
    [
      'title' => 'Analyse des menaces',
      'summary' => 'Menace, vulnérabilité, impact : la matrice de risques.',
      'lessons' => [
        ['title' => 'Conduire une analyse de menaces', 'type' => 'text', 'minutes' => 30, 'content' => <<<'HTML'
<p>Le risque résulte de la combinaison d'une <strong>menace</strong> (un acteur avec une intention et une capacité), d'une <strong>vulnérabilité</strong> (une faiblesse exploitable) et d'un <strong>impact</strong> (la gravité des conséquences).</p>
<h3>Évaluer une menace</h3>
<ul><li><strong>Intention</strong> : l'acteur veut-il nous nuire ? Pourquoi ?</li><li><strong>Capacité</strong> : en a-t-il les moyens ?</li><li><strong>Opportunité</strong> : nos vulnérabilités lui offrent-elles une ouverture ?</li></ul>
<h3>La matrice probabilité × impact</h3>
<table><thead><tr><th></th><th>Impact faible</th><th>Impact modéré</th><th>Impact grave</th></tr></thead><tbody>
<tr><th>Probable</th><td>Moyen</td><td>Élevé</td><td><strong>Critique</strong></td></tr>
<tr><th>Possible</th><td>Faible</td><td>Moyen</td><td>Élevé</td></tr>
<tr><th>Peu probable</th><td>Faible</td><td>Faible</td><td>Moyen</td></tr>
</tbody></table>
<h3>Sources d'information sur les menaces</h3>
<p>Rapports publics d'autorités nationales de cybersécurité, bulletins des équipes CERT/CSIRT africaines, alertes sectorielles, retours d'expérience des pairs, incidents internes. L'analyse doit être <strong>actualisée régulièrement</strong> : une matrice figée devient trompeuse.</p>
HTML],
      ],
      'quiz' => [
        ['Le risque résulte de la combinaison de :', ['Menace, vulnérabilité et impact', 'Budget, personnel et locaux', 'Pays, langue et monnaie', 'Intention seule'], 0, 'Les trois composantes doivent être évaluées.'],
        ['Les trois critères d\'évaluation d\'une menace sont :', ['Prix, qualité, délai', 'Intention, capacité, opportunité', 'Taille, poids, couleur', 'Source, date, auteur'], 1, 'Intention + capacité + opportunité.'],
        ['Pourquoi actualiser régulièrement une matrice de risques ?', ['Pour justifier un budget', 'Parce que les menaces et vulnérabilités évoluent', 'Par obligation fiscale', 'Ce n\'est pas nécessaire'], 1, 'Une analyse figée devient trompeuse.'],
      ],
    ],
    [
      'title' => 'Contre-mesures défensives',
      'summary' => 'Prévenir, détecter, réagir, se rétablir.',
      'lessons' => [
        ['title' => 'Choisir des contre-mesures', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>Une contre-mesure réduit la probabilité ou l'impact d'un risque. La défense efficace est <strong>en profondeur</strong> : plusieurs couches complémentaires, de sorte qu'une défaillance isolée ne suffise pas.</p>
<h3>Les quatre fonctions</h3>
<ol><li><strong>Prévenir</strong> : contrôle d'accès, chiffrement, formation, procédures ;</li><li><strong>Détecter</strong> : journalisation, alertes, signalements des personnels ;</li><li><strong>Réagir</strong> : plan de réponse, cellule de crise, contacts juridiques et autorités ;</li><li><strong>Se rétablir</strong> : sauvegardes testées, plan de continuité, retour d'expérience.</li></ol>
<h3>Prioriser</h3>
<p>Chaque mesure est évaluée selon son coût, sa facilité de mise en œuvre et sa réduction du risque. Les mesures « à fort effet et faible coût » viennent en premier : double authentification, sauvegardes hors ligne, procédure de contre-appel pour les paiements, sensibilisation.</p>
<div class="note"><strong>Contre-mesures et droit :</strong> les mesures défensives restent dans le périmètre de l'organisation. « Riposter » contre un attaquant (intrusion en retour, représailles) est illégal et dangereux : on signale aux autorités compétentes.</div>
HTML],
      ],
      'quiz' => [
        ['Que signifie la « défense en profondeur » ?', ['Placer les serveurs en sous-sol', 'Superposer plusieurs couches de protection complémentaires', 'Recruter des plongeurs', 'Réagir uniquement après une attaque'], 1, 'Plusieurs couches évitent qu\'une seule défaillance soit fatale.'],
        ['Les sauvegardes testées relèvent de quelle fonction ?', ['Prévenir', 'Détecter', 'Se rétablir', 'Aucune'], 2, 'Elles permettent le rétablissement après incident.'],
        ['Face à une attaque, une organisation privée doit :', ['Riposter en attaquant l\'agresseur', 'Se défendre dans son périmètre et signaler aux autorités', 'Payer immédiatement toute rançon', 'Ne rien faire'], 1, 'La riposte offensive est illégale ; on signale.'],
      ],
    ],
    [
      'title' => 'Études de cas et simulations pédagogiques',
      'summary' => 'S\'entraîner par l\'exercice sur table.',
      'lessons' => [
        ['title' => 'Concevoir un exercice sur table', 'type' => 'text', 'minutes' => 25, 'content' => <<<'HTML'
<p>L'exercice sur table (<em>tabletop exercise</em>) réunit les décideurs autour d'un scénario fictif qui se déroule par étapes (« injects »). Il teste les procédures, la coordination et la communication sans aucun risque réel.</p>
<h3>Conception en cinq étapes</h3>
<ol><li>Définir 2 ou 3 objectifs (ex. tester la chaîne d'alerte) ;</li><li>Écrire un scénario plausible et <strong>entièrement fictif</strong> ;</li><li>Préparer 5 à 8 injects successifs ;</li><li>Désigner un animateur et un observateur ;</li><li>Prévoir un débriefing structuré : ce qui a fonctionné, ce qui doit changer, qui fait quoi et quand.</li></ol>
<div class="callout">Un exercice réussi n'est pas un exercice sans erreurs : c'est un exercice qui révèle les failles avant qu'une vraie crise ne le fasse.</div>
HTML],
      ],
      'quiz' => [
        ['Qu\'est-ce qu\'un « inject » dans un exercice sur table ?', ['Un logiciel malveillant', 'Un nouvel élément de scénario introduit en cours d\'exercice', 'Une injection SQL', 'Un participant extérieur'], 1, 'Les injects font évoluer le scénario.'],
        ['Le scénario d\'un exercice doit être :', ['Réel et confidentiel', 'Plausible et entièrement fictif', 'Copié d\'un incident récent avec les vrais noms', 'Improvisé'], 1, 'Le fictif évite toute mise en cause réelle.'],
        ['Qu\'est-ce qu\'un exercice réussi ?', ['Un exercice sans aucune erreur', 'Un exercice qui révèle les failles avant une vraie crise', 'Un exercice très court', 'Un exercice sans débriefing'], 1, 'L\'objectif est d\'apprendre.'],
      ],
    ],
    [
      'title' => 'Atelier pratique',
      'summary' => 'Matrice de risques et simulation complète.',
      'lessons' => [
        ['title' => 'Fiche de synthèse (PDF)', 'type' => 'document', 'minutes' => 10, 'content' => '<p>Modèles de matrice de risques, de plan de déplacement et de fiche d\'exercice.</p>'],
        ['title' => 'Exercice : matrice de risques', 'type' => 'exercise', 'minutes' => 45, 'content' => <<<'HTML'
<p><strong>Organisation fictive :</strong> une université régionale qui héberge un laboratoire de recherche sur les semences résistantes à la sécheresse.</p>
<ol><li>Identifiez six risques (fuite de résultats, rançongiciel, vol de matériel, ingérence via partenariats…).</li><li>Évaluez chacun : intention, capacité, vulnérabilité, impact.</li><li>Placez-les dans la matrice.</li><li>Proposez deux contre-mesures par risque critique, classées par coût.</li></ol>
HTML],
        ['title' => 'Étude de cas : exercice « Harmattan »', 'type' => 'case_study', 'minutes' => 40, 'content' => <<<'HTML'
<p><strong>Simulation pédagogique (fictive).</strong> Un groupe logistique découvre un lundi matin que ses serveurs sont chiffrés par un rançongiciel, en pleine période de dédouanement.</p>
<ul><li><strong>Inject 1 (08 h 10)</strong> : les agents ne peuvent plus imprimer les déclarations.</li><li><strong>Inject 2 (09 h 00)</strong> : une demande de rançon apparaît ; les attaquants menacent de publier des données clients.</li><li><strong>Inject 3 (10 h 30)</strong> : un journaliste appelle le service communication.</li><li><strong>Inject 4 (12 h 00)</strong> : on découvre que la dernière sauvegarde hors ligne date de trois semaines.</li></ul>
<h3>Questions de débriefing</h3>
<ol><li>Qui aurait dû être alerté, dans quel ordre ?</li><li>Quelles obligations de notification (autorité de protection des données, clients) s'appliquent ?</li><li>Pourquoi est-il déconseillé de payer la rançon ?</li><li>Quelles trois mesures auraient le plus réduit l'impact ?</li></ol>
HTML],
      ],
      'quiz' => [],
    ],
  ],
  'exam' => ['pass' => 70, 'attempts' => 3, 'count' => 10, 'time' => 25],
],
];
