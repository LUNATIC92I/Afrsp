<?php
/** Enrichissements pédagogiques : modules C et D. */
return [
'AFS-C101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : les ingérences économiques étrangères', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>L'<strong>ingérence économique</strong> désigne les actions d'acteurs étrangers visant à obtenir un avantage indu sur des entreprises ou des secteurs nationaux, par des moyens déloyaux ou illégaux.</p>
<h3>Formes observées dans le monde</h3>
<ul><li>Prises de participation visant à capter une technologie ;</li><li>Recrutement ciblé de chercheurs ou d'ingénieurs clés ;</li><li>Audits ou « due diligences » détournés de leur finalité ;</li><li>Procédures juridiques extraterritoriales utilisées comme levier ;</li><li>Campagnes de dénigrement pour faire échouer un appel d'offres.</li></ul>
<h3>Secteurs sensibles en Afrique</h3>
<p>Mines et minerais critiques, énergie, télécommunications, paiement mobile, agro-industrie (semences, transformation), infrastructures portuaires.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Toute relation économique n'est pas une ingérence : c'est le caractère déloyal qui compte.</li><li>Les moments de transaction (levée de fonds, partenariat) sont les plus exposés.</li><li>La réponse passe par la vigilance, le droit et le signalement aux autorités.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qu\'est-ce qui caractérise une ingérence économique ?', ['Toute présence d\'un investisseur étranger', 'L\'usage de moyens déloyaux ou illégaux pour obtenir un avantage indu', 'Une simple concurrence commerciale', 'Une exportation'], 1, 'Le caractère déloyal distingue l\'ingérence de la concurrence normale.'],
        ['Quel moment expose particulièrement une entreprise ?', ['Les congés annuels', 'Une levée de fonds ou un partenariat', 'L\'inventaire de stock', 'Le paiement des salaires'], 1, 'Les échanges d\'informations y sont intenses.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : les signaux de vigilance sans surveillance abusive', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La recherche sur la menace interne montre que les incidents sont souvent précédés de <strong>signaux observables</strong> dans le cadre normal du travail. Les repérer ne nécessite pas de surveiller la vie privée des employés.</p>
<h3>Signaux liés au travail (à traiter avec discernement)</h3>
<ul><li>Demandes d'accès sans lien avec la mission ;</li><li>Téléchargements massifs avant un départ ;</li><li>Contournement répété des procédures de sécurité ;</li><li>Conflit professionnel grave non résolu.</li></ul>
<h3>La bonne réponse</h3>
<p>Un signal appelle d'abord un <strong>échange managérial</strong> et, si nécessaire, une vérification encadrée par les procédures internes et le droit du travail, jamais une enquête clandestine ni une stigmatisation.</p>
<div class="warning">Les critères liés à l'origine, à la religion, à l'opinion ou à la vie privée ne sont pas des indicateurs de risque : les utiliser serait discriminatoire et illégal.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les signaux pertinents sont professionnels et objectifs.</li><li>La première réponse est managériale.</li><li>Toute vérification suit une procédure écrite et proportionnée.</li></ul></div>
HTML]],
      'quiz' => [
        ['Lequel est un signal pertinent de menace interne ?', ['La religion d\'un employé', 'Des téléchargements massifs avant un départ', 'Son pays d\'origine', 'Ses opinions politiques'], 1, 'Seuls des signaux professionnels et objectifs sont légitimes.'],
        ['Quelle est la première réponse appropriée à un signal ?', ['Une enquête clandestine', 'Un échange managérial', 'Un licenciement immédiat', 'La publication du nom de l\'employé'], 1, 'La réponse doit rester proportionnée et encadrée.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : le marquage TLP pour partager l\'information', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>Traffic Light Protocol (TLP)</strong>, normalisé par le FIRST (Forum of Incident Response and Security Teams, version 2.0 en 2022), est un code simple indiquant jusqu'où une information peut être partagée.</p>
<table><thead><tr><th>Marquage</th><th>Diffusion autorisée</th></tr></thead><tbody>
<tr><td>TLP:RED</td><td>Uniquement les participants nommés de la réunion ou de l'échange</td></tr>
<tr><td>TLP:AMBER+STRICT</td><td>Uniquement l'organisation du destinataire</td></tr>
<tr><td>TLP:AMBER</td><td>L'organisation du destinataire et ses clients, sur la base du besoin d'en connaître</td></tr>
<tr><td>TLP:GREEN</td><td>La communauté (secteur, réseau de confiance), pas publiquement</td></tr>
<tr><td>TLP:CLEAR</td><td>Diffusion publique sans restriction</td></tr>
</tbody></table>
<p>Le TLP complète la classification interne : il régit le <em>partage</em> entre organisations, notamment entre équipes de réponse aux incidents.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le marquage s'indique en tête du document.</li><li>En cas de doute, demander à l'émetteur.</li><li>TLP:CLEAR remplace l'ancien TLP:WHITE.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que permet TLP:GREEN ?', ['La diffusion publique', 'Le partage dans la communauté de confiance, pas publiquement', 'Uniquement les participants nommés', 'Aucun partage'], 1, 'GREEN = communauté, pas de publication.'],
        ['Quel marquage a remplacé TLP:WHITE dans la version 2.0 ?', ['TLP:BLUE', 'TLP:CLEAR', 'TLP:OPEN', 'TLP:GREY'], 1, 'TLP 2.0 (2022) utilise TLP:CLEAR.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : concevoir une campagne de sensibilisation', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Une sensibilisation efficace change des comportements, pas seulement des connaissances.</p>
<h3>Les ingrédients</h3>
<ul><li><strong>Exemples locaux</strong> : fraudes réellement observées dans le pays ou le secteur (anonymisées) ;</li><li><strong>Formats courts</strong> et répétés plutôt qu'une longue session annuelle ;</li><li><strong>Exercices de simulation</strong> d'hameçonnage annoncés dans leur principe, suivis d'une explication bienveillante ;</li><li><strong>Un canal de signalement simple</strong> (adresse ou bouton unique) ;</li><li><strong>Des indicateurs</strong> : taux de signalement, délai de signalement, plutôt que le seul taux de clic.</li></ul>
<div class="note">Le meilleur indicateur d'une culture de sécurité est l'augmentation des signalements : elle montre que les équipes reconnaissent les tentatives et ont confiance dans le dispositif.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Court, fréquent, concret.</li><li>Valoriser les signalements, ne jamais humilier.</li><li>Mesurer les progrès dans le temps.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel indicateur reflète le mieux une bonne culture de sécurité ?', ['Le nombre de sanctions', 'L\'augmentation des signalements', 'La durée des formations', 'Le nombre de mots de passe changés'], 1, 'Signaler montre que la menace est reconnue et que le dispositif inspire confiance.'],
        ['Quel format de sensibilisation est le plus efficace ?', ['Une longue session annuelle', 'Des formats courts et répétés avec exemples locaux', 'Un document de 100 pages', 'Aucun format'], 1, 'La répétition et la proximité favorisent le changement de comportement.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-C201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : la sécurité des voyageurs d\'affaires', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les déplacements concentrent des risques informationnels : appareils perdus ou inspectés, réseaux non fiables, conversations entendues.</p>
<h3>Check-list numérique</h3>
<ul><li>Emporter un ordinateur et un téléphone dédiés, ne contenant que le nécessaire ;</li><li>Chiffrement intégral activé et mises à jour faites avant le départ ;</li><li>Accès aux ressources de l'organisation via un VPN d'entreprise ;</li><li>Ne pas brancher de clés USB reçues ; recharger sur prise électrique plutôt que sur des ports USB publics ;</li><li>Au retour, faire vérifier les appareils par le service informatique.</li></ul>
<h3>Check-list comportementale</h3>
<ul><li>Discrétion dans les lieux publics (taxi, restaurant, salon d'aéroport) ;</li><li>Documents sensibles toujours sous contrôle ;</li><li>Signalement de toute sollicitation inhabituelle.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Moins on emporte de données, moins on en expose.</li><li>Le retour de mission compte autant que le départ.</li><li>Respecter les lois du pays visité, y compris lors des contrôles aux frontières.</li></ul></div>
HTML]],
      'quiz' => [
        ['Pourquoi utiliser un ordinateur dédié en déplacement ?', ['Pour aller plus vite', 'Pour limiter les données exposées en cas de perte ou d\'inspection', 'Par superstition', 'Pour éviter les mises à jour'], 1, 'Minimiser les données emportées réduit l\'impact d\'un incident.'],
        ['Que faire des appareils au retour d\'une mission sensible ?', ['Rien', 'Les faire vérifier par le service informatique', 'Les vendre', 'Les prêter à un collègue'], 1, 'Une vérification permet de détecter une éventuelle compromission.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : la méthode du nœud papillon', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>nœud papillon</strong> (bow-tie) représente un événement redouté au centre, ses <em>causes</em> à gauche et ses <em>conséquences</em> à droite. Les mesures de prévention s'insèrent entre causes et événement ; les mesures de protection entre événement et conséquences.</p>
<pre><code>Causes -> [Prévention] -> ÉVÉNEMENT -> [Protection] -> Conséquences
Hameçonnage   MFA, formation    Compromission    Sauvegardes,      Arrêt d'activité
Mot de passe                    de messagerie    cellule de crise  Fuite de données</code></pre>
<p>Cette visualisation, utilisée dans l'industrie et la sûreté, aide les décideurs à voir où investir et à vérifier qu'aucune cause majeure n'est sans barrière.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Prévention à gauche, protection à droite.</li><li>Chaque barrière a un responsable et un test.</li><li>Outil de communication efficace avec la direction.</li></ul></div>
HTML]],
      'quiz' => [
        ['Dans un nœud papillon, où se placent les mesures de prévention ?', ['Après l\'événement', 'Entre les causes et l\'événement', 'Dans les conséquences', 'Nulle part'], 1, 'Elles empêchent l\'événement de survenir.'],
        ['Les sauvegardes testées sont, dans le nœud papillon :', ['Une cause', 'Une mesure de protection limitant les conséquences', 'Un événement redouté', 'Une mesure de prévention'], 1, 'Elles réduisent l\'impact après l\'événement.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : continuité d\'activité et gestion de crise', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La <strong>continuité d'activité</strong> organise le maintien des fonctions essentielles lorsqu'un incident survient. La norme ISO 22301 en fournit le cadre de référence.</p>
<h3>Deux notions clés</h3>
<ul><li><strong>RTO</strong> (Recovery Time Objective) : durée maximale d'interruption acceptable ;</li><li><strong>RPO</strong> (Recovery Point Objective) : perte de données maximale acceptable, exprimée en temps (par exemple 24 h de données).</li></ul>
<h3>La cellule de crise</h3>
<p>Elle réunit direction, technique, juridique et communication, avec un journal de bord horodaté. Ses premiers réflexes : qualifier l'incident, protéger les personnes et les preuves, informer les autorités et partenaires concernés, communiquer de façon factuelle.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>RTO = temps ; RPO = données perdues.</li><li>Un plan non testé n'est pas un plan.</li><li>La communication de crise doit être factuelle et coordonnée.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que désigne le RPO ?', ['La durée maximale d\'interruption', 'La perte de données maximale acceptable', 'Le budget de crise', 'Le responsable de la crise'], 1, 'Recovery Point Objective.'],
        ['Quelle norme encadre la continuité d\'activité ?', ['ISO 9001', 'ISO 22301', 'ISO 14001', 'ISO 3166'], 1, 'ISO 22301 porte sur les systèmes de management de la continuité d\'activité.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : le retour d\'expérience (RETEX)', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>retour d'expérience</strong> transforme un exercice ou un incident en améliorations concrètes.</p>
<h3>Trame en quatre questions</h3>
<ol><li>Qu'était-il prévu ?</li><li>Que s'est-il réellement passé ?</li><li>Pourquoi des écarts ?</li><li>Que changeons-nous, qui en est responsable, pour quelle date ?</li></ol>
<p>Le RETEX est <strong>sans recherche de coupable</strong> (« blameless ») : il analyse les systèmes et les procédures, pas les personnes. Cette approche, issue de l'aéronautique et de la santé, favorise la transparence.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Chaque action corrective a un responsable et une échéance.</li><li>On suit la mise en œuvre lors de l'exercice suivant.</li><li>L'absence de recherche de coupable libère la parole.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que signifie un RETEX « sans recherche de coupable » ?', ['On ne tire aucune leçon', 'On analyse les systèmes et procédures plutôt que les personnes', 'On sanctionne en privé', 'On ne documente rien'], 1, 'Cela favorise des retours honnêtes.'],
        ['Que doit comporter chaque action corrective ?', ['Un slogan', 'Un responsable et une échéance', 'Un coupable', 'Un budget illimité'], 1, 'Sans responsable ni date, l\'action reste lettre morte.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-D101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : le paysage cyber africain', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La transformation numérique rapide du continent s'accompagne d'une croissance des menaces. Les rapports publics d'organisations comme INTERPOL (évaluations des cybermenaces en Afrique) soulignent notamment :</p>
<ul><li>les escroqueries en ligne et la compromission de messageries professionnelles ;</li><li>les rançongiciels visant entreprises et administrations ;</li><li>les fraudes liées au paiement mobile ;</li><li>les campagnes d'hameçonnage ciblant les clients bancaires.</li></ul>
<h3>Les réponses institutionnelles</h3>
<ul><li>Création d'équipes nationales de réponse aux incidents (CERT/CSIRT) dans de nombreux pays ;</li><li>Adoption de lois sur la cybercriminalité et la protection des données ;</li><li>La Convention de Malabo (Union africaine, 2014) est entrée en vigueur en 2023 après avoir atteint le nombre requis de ratifications.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>La fraude financière domine les menaces signalées.</li><li>Les CERT nationaux sont des interlocuteurs clés.</li><li>Le cadre juridique continental se consolide.</li></ul></div>
HTML]],
      'quiz' => [
        ['En quelle année la Convention de Malabo est-elle entrée en vigueur ?', ['2014', '2018', '2023', 'Elle n\'est jamais entrée en vigueur'], 2, 'Adoptée en 2014, elle est entrée en vigueur en 2023.'],
        ['Quel type de structure coordonne la réponse aux incidents au niveau national ?', ['Un CERT / CSIRT', 'Une banque centrale', 'Un office de brevets', 'Une chambre de commerce'], 0, 'Les CERT/CSIRT nationaux coordonnent la réponse aux incidents.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : documenter une enquête ouverte', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>Protocole de Berkeley</strong> sur les enquêtes numériques en sources ouvertes (publié en 2020 par le Human Rights Center de l'Université de Californie à Berkeley et le Haut-Commissariat des Nations unies aux droits de l'homme) fixe des standards professionnels pour la collecte, la préservation et la vérification d'informations en ligne.</p>
<h3>Principes retenus</h3>
<ul><li><strong>Exactitude</strong> et <strong>impartialité</strong> ;</li><li><strong>Préservation</strong> des contenus avec métadonnées et empreintes ;</li><li><strong>Sécurité</strong> de l'enquêteur et des personnes concernées ;</li><li><strong>Légalité</strong> et respect de la vie privée ;</li><li><strong>Documentation</strong> complète de la méthodologie.</li></ul>
<h3>Journal d'enquête type</h3>
<pre><code>Date/heure UTC | Requête / URL | Résultat | Capture (fichier, SHA-256) | Commentaire</code></pre>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un standard international existe : s'y référer.</li><li>Tout ce qui n'est pas documenté n'existe pas.</li><li>La sécurité de l'enquêteur fait partie de la méthode.</li></ul></div>
HTML]],
      'quiz' => [
        ['Le Protocole de Berkeley porte sur :', ['Le chiffrement des réseaux', 'Les standards des enquêtes numériques en sources ouvertes', 'La fabrication de logiciels', 'La géolocalisation des personnes'], 1, 'Il fixe des standards de collecte, préservation et vérification.'],
        ['Que doit contenir une entrée de journal d\'enquête ?', ['Seulement l\'URL', 'Date UTC, requête, résultat, capture avec empreinte, commentaire', 'Un résumé oral', 'Rien, si l\'on se souvient'], 1, 'La traçabilité exige un enregistrement complet.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : réduire l\'empreinte de son organisation', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Après l'audit vient la <strong>remédiation</strong>. Les mesures les plus efficaces sont souvent simples.</p>
<table><thead><tr><th>Exposition constatée</th><th>Mesure</th></tr></thead><tbody>
<tr><td>Sous-domaine de test accessible</td><td>Désactiver ou protéger par authentification</td></tr>
<tr><td>Métadonnées dans les PDF publiés</td><td>Nettoyage systématique avant publication</td></tr>
<tr><td>Organigramme détaillé en ligne</td><td>Publier des fonctions génériques et des adresses de service</td></tr>
<tr><td>Identifiants dans des fuites</td><td>Réinitialisation, authentification multifacteur</td></tr>
<tr><td>Technologies détaillées dans les offres d'emploi</td><td>Formulations plus générales</td></tr>
</tbody></table>
<p>Ajoutez un fichier <code>security.txt</code> (norme RFC 9116) sur votre site pour indiquer comment signaler une vulnérabilité.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Prioriser les expositions les plus exploitables.</li><li>Automatiser le nettoyage des documents.</li><li>Répéter l'audit périodiquement.</li></ul></div>
HTML]],
      'quiz' => [
        ['À quoi sert le fichier security.txt (RFC 9116) ?', ['À bloquer les robots', 'À indiquer comment signaler une vulnérabilité', 'À stocker les mots de passe', 'À chiffrer le site'], 1, 'Il facilite la divulgation responsable.'],
        ['Quelle mesure répond à un sous-domaine de test exposé ?', ['Le promouvoir', 'Le désactiver ou le protéger par authentification', 'Le traduire', 'L\'ignorer'], 1, 'Les environnements de test sont des cibles fréquentes.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : nettoyer les métadonnées avant publication', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le réflexe défensif le plus rentable : <strong>supprimer les métadonnées</strong> des fichiers que l'on diffuse.</p>
<h3>Procédure recommandée</h3>
<ol><li>Exporter le document final en PDF depuis le logiciel de traitement de texte, en désactivant l'inclusion des propriétés personnelles ;</li><li>Vérifier les propriétés du PDF (auteur, créateur, logiciel) ;</li><li>Pour les photos, supprimer les données EXIF (notamment la géolocalisation) ou désactiver la géolocalisation de l'appareil ;</li><li>Contrôler un échantillon avant chaque campagne de publication.</li></ol>
<p>Des outils libres comme ExifTool permettent d'afficher et de supprimer les métadonnées de nombreux formats.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les photos de smartphones contiennent souvent des coordonnées GPS.</li><li>Les documents bureautiques peuvent conserver des commentaires et révisions.</li><li>Intégrer le nettoyage à la procédure de publication.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle métadonnée d\'une photo est la plus sensible pour la vie privée ?', ['La résolution', 'La géolocalisation GPS', 'Le format', 'La taille du fichier'], 1, 'Elle peut révéler un domicile ou un lieu sensible.'],
        ['Quel outil libre permet d\'afficher et supprimer des métadonnées ?', ['ExifTool', 'Wireshark', 'Shodan', 'Maltego'], 0, 'ExifTool gère de nombreux formats.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-D201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : formats et partage de la CTI', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Pour être exploitable par des machines, la CTI s'échange dans des formats normalisés :</p>
<ul><li><strong>STIX 2.1</strong> (Structured Threat Information eXpression), standard OASIS décrivant objets et relations : acteurs, campagnes, indicateurs, techniques ;</li><li><strong>TAXII</strong>, protocole de transport des données STIX ;</li><li>Des plateformes libres comme <strong>MISP</strong> permettent de partager des indicateurs au sein de communautés de confiance.</li></ul>
<h3>Qualité d'un indicateur</h3>
<p>Un bon IoC est <strong>contextualisé</strong> (campagne, date d'observation, confiance), <strong>daté</strong> (les infrastructures changent vite) et accompagné d'un marquage TLP.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>STIX décrit, TAXII transporte.</li><li>Un indicateur sans contexte génère des faux positifs.</li><li>Le partage se fait dans des communautés de confiance.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel est le rôle de TAXII ?', ['Décrire les menaces', 'Transporter des données STIX', 'Chiffrer les disques', 'Analyser la mémoire'], 1, 'STIX décrit, TAXII transporte.'],
        ['Qu\'est-ce qui rend un indicateur de compromission de qualité ?', ['Sa longueur', 'Son contexte, sa date et son niveau de confiance', 'Sa couleur', 'Sa popularité'], 1, 'Sans contexte, un IoC est difficilement exploitable.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : typosquattage et domaines trompeurs', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>typosquattage</strong> consiste à enregistrer des noms de domaine proches d'une marque (lettre inversée, tiret ajouté, extension différente, caractères visuellement similaires) pour tromper les utilisateurs.</p>
<h3>Exemples de variantes (fictives)</h3>
<pre><code>banque-exemple.com   ->  banque-exernple.com  (rn au lieu de m)
                     ->  banqueexemple-ci.com
                     ->  banque-exemple.co</code></pre>
<h3>Défense</h3>
<ul><li>Surveiller les nouveaux enregistrements de domaines proches de sa marque (services de veille, journaux de transparence des certificats) ;</li><li>Enregistrer préventivement les variantes les plus évidentes ;</li><li>Signaler les domaines frauduleux au registrar et au CERT national ;</li><li>Mettre en place SPF, DKIM et DMARC pour limiter l'usurpation de ses adresses e-mail.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les domaines proches sont un vecteur majeur d'hameçonnage.</li><li>La surveillance est passive et légale.</li><li>DMARC protège la réputation de sa messagerie.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qu\'est-ce que le typosquattage ?', ['Une erreur de frappe sans conséquence', 'L\'enregistrement de domaines proches d\'une marque pour tromper', 'Un protocole réseau', 'Une technique d\'entretien'], 1, 'Il vise à piéger les utilisateurs inattentifs.'],
        ['Quels mécanismes limitent l\'usurpation d\'adresses e-mail d\'un domaine ?', ['HTML et CSS', 'SPF, DKIM et DMARC', 'FTP et SSH', 'PDF et EXIF'], 1, 'Ils authentifient l\'expéditeur.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : l\'authentification multifacteur', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>L'<strong>authentification multifacteur (MFA)</strong> combine au moins deux éléments : ce que l'on sait (mot de passe), ce que l'on possède (téléphone, clé), ce que l'on est (biométrie).</p>
<table><thead><tr><th>Méthode</th><th>Robustesse</th><th>Commentaire</th></tr></thead><tbody>
<tr><td>Code par SMS</td><td>Moyenne</td><td>Exposée aux détournements de carte SIM</td></tr>
<tr><td>Application d'authentification (codes temporaires)</td><td>Bonne</td><td>Fonctionne hors connexion</td></tr>
<tr><td>Notification « approuver »</td><td>Bonne si correspondance de nombre</td><td>Attention à la fatigue des notifications</td></tr>
<tr><td>Clé de sécurité FIDO2 / passkeys</td><td>Très élevée</td><td>Résistante à l'hameçonnage</td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>Même un SMS vaut mieux que rien.</li><li>Privilégier FIDO2 pour les comptes à privilèges.</li><li>Ne jamais approuver une notification non sollicitée.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle méthode MFA résiste le mieux à l\'hameçonnage ?', ['Le code SMS', 'La clé de sécurité FIDO2 / passkey', 'La question secrète', 'Le mot de passe seul'], 1, 'FIDO2 lie l\'authentification au site légitime.'],
        ['Quel risque concerne le code par SMS ?', ['Le détournement de carte SIM', 'La surchauffe du téléphone', 'Aucun risque', 'La perte de batterie'], 0, 'Le SIM swapping permet de recevoir les SMS de la victime.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : écrire une règle de détection', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les équipes de détection formalisent leurs hypothèses en <strong>règles</strong>. Le format ouvert <strong>Sigma</strong> permet de décrire une détection indépendamment de l'outil (SIEM) utilisé.</p>
<h3>Exemple pédagogique simplifié</h3>
<pre><code>title: Création de règle de transfert de messagerie vers l'extérieur
logsource: { product: messagerie, service: audit }
detection:
  selection:
    action: create_forward_rule
    destination|endswith: externe
  condition: selection
level: high</code></pre>
<h3>Cycle de vie d'une règle</h3>
<ol><li>Hypothèse (tirée de la CTI) ;</li><li>Écriture et test sur données historiques ;</li><li>Mesure des faux positifs ;</li><li>Déploiement, puis revue périodique.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Une règle part d'une hypothèse documentée.</li><li>Tester avant de déployer.</li><li>Sigma facilite le partage entre organisations.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel est l\'intérêt du format Sigma ?', ['Chiffrer les journaux', 'Décrire une détection indépendamment de l\'outil', 'Remplacer les antivirus', 'Accélérer Internet'], 1, 'Une règle Sigma peut être convertie pour différents SIEM.'],
        ['Que faire avant de déployer une règle de détection ?', ['La publier sur les réseaux sociaux', 'La tester sur des données historiques et mesurer les faux positifs', 'Rien', 'Supprimer les journaux'], 1, 'Une règle non testée peut noyer les analystes.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
];
