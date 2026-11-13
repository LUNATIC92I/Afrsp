<?php
/**
 * Glossaires et bibliographies par formation.
 * 'g' : [terme, définition] · 'b' : [référence, intérêt pédagogique]
 */
$common = [
    ['Nations unies, Conseil des droits de l\'homme, <em>Compilation de bonnes pratiques relatives aux cadres juridiques et institutionnels des services de renseignement</em>, A/HRC/14/46, 2010.', 'Référence internationale sur le contrôle démocratique des services.'],
    ['Union africaine, <em>Convention sur la cybersécurité et la protection des données à caractère personnel</em> (Convention de Malabo), 2014, en vigueur depuis 2023.', 'Cadre continental de la protection des données.'],
];
return [
'AFS-A101' => [
  'g' => [['Renseignement', 'Information évaluée, recoupée et interprétée pour éclairer une décision.'], ['Cycle du renseignement', 'Orientation, collecte, exploitation, analyse, diffusion, puis retour d\'expérience.'], ['Besoin en renseignement', 'Question précise et vérifiable dérivée d\'une préoccupation du décideur.'], ['Toutes sources (all-source)', 'Analyse croisant plusieurs disciplines de collecte.'], ['ACH', 'Analyse des hypothèses concurrentes : méthode privilégiant la réfutation.'], ['Biais de confirmation', 'Tendance à retenir ce qui confirme une idée préexistante.'], ['Effet miroir', 'Projection de sa propre logique sur un autre acteur.'], ['Indicateur', 'Fait observable qui rend un scénario plus ou moins probable.'], ['Niveau de confiance', 'Appréciation de la solidité d\'un jugement analytique.']],
  'b' => array_merge([
    ['Heuer, Richards J., <em>Psychology of Intelligence Analysis</em>, CIA Center for the Study of Intelligence, 1999 (accès libre en ligne).', 'Classique sur les biais cognitifs et l\'ACH.'],
    ['Lowenthal, Mark M., <em>Intelligence: From Secrets to Policy</em>, CQ Press (éditions successives).', 'Manuel de référence sur le cycle et les disciplines.'],
    ['Kent, Sherman, <em>Strategic Intelligence for American World Policy</em>, Princeton University Press, 1949.', 'Texte fondateur de l\'analyse du renseignement.'],
    ['Kahneman, Daniel, <em>Système 1 / Système 2 : les deux vitesses de la pensée</em>, Flammarion, 2012.', 'Comprendre les mécanismes des biais de jugement.'],
  ], [$common[0]]),
],
'AFS-A201' => [
  'g' => [['HUMINT', 'Renseignement d\'origine humaine.'], ['OSINT', 'Renseignement d\'origine sources ouvertes.'], ['SIGINT', 'Renseignement d\'origine électromagnétique (COMINT, ELINT).'], ['GEOINT', 'Renseignement géospatial : imagerie et données géoréférencées.'], ['Géolocalisation', 'Détermination du lieu d\'une image ou d\'une vidéo.'], ['Chronolocalisation', 'Détermination du moment d\'une prise de vue.'], ['Résolution spatiale', 'Taille au sol représentée par un pixel.'], ['NDVI', 'Indice de végétation calculé à partir du rouge et du proche infrarouge.'], ['IXP', 'Point d\'échange Internet où les réseaux s\'interconnectent localement.']],
  'b' => array_merge([
    ['Silverman, Craig (dir.), <em>Verification Handbook</em>, European Journalism Centre, 2014 (accès libre).', 'Méthodes de vérification de contenus en ligne.'],
    ['Higgins, Eliot, <em>We Are Bellingcat</em>, Bloomsbury, 2021.', 'Récit et méthodes de l\'enquête en sources ouvertes.'],
    ['Programme européen Copernicus — données Sentinel (accès libre).', 'Imagerie satellitaire gratuite pour l\'entraînement.'],
    ['Lowenthal, Mark M., <em>Intelligence: From Secrets to Policy</em>, CQ Press.', 'Chapitres consacrés aux disciplines de collecte.'],
  ], $common),
],
'AFS-B101' => [
  'g' => [['Consentement éclairé', 'Accord donné en connaissant l\'identité de l\'interlocuteur et la finalité de l\'échange.'], ['Besoin d\'en connaître', 'Principe limitant la collecte et le partage au strict nécessaire.'], ['Entretien cognitif', 'Méthode d\'entretien améliorant la qualité des témoignages.'], ['Écoute active', 'Attention, reformulation et relances neutres.'], ['Question suggestive', 'Question qui oriente la réponse ; à proscrire.'], ['Code de l\'Amirauté', 'Cotation séparée de la source (A–F) et de l\'information (1–6).'], ['Rapport circulaire', 'Plusieurs sources apparentes issues d\'une même origine.'], ['Désinformation', 'Information fausse diffusée intentionnellement.'], ['Mésinformation', 'Information fausse diffusée de bonne foi.']],
  'b' => array_merge([
    ['Fisher, Ronald P. et Geiselman, R. Edward, <em>Memory-Enhancing Techniques for Investigative Interviewing: The Cognitive Interview</em>, Charles C. Thomas, 1992.', 'Ouvrage de référence sur l\'entretien cognitif.'],
    ['Vrij, Aldert, <em>Detecting Lies and Deceit</em>, Wiley, 2e éd. 2008.', 'Synthèse de la recherche sur la détection du mensonge.'],
    ['Silverman, Craig (dir.), <em>Verification Handbook for Disinformation and Media Manipulation</em>, European Journalism Centre, 2020.', 'Désinformation et manipulation des sources.'],
  ], $common),
],
'AFS-B201' => [
  'g' => [['Pseudonymisation', 'Remplacement de l\'identité par un code, avec table de correspondance séparée.'], ['Cloisonnement', 'Limitation de la connaissance d\'une information aux seules personnes concernées.'], ['Recoupement d\'identification', 'Identification indirecte d\'une personne par des détails contextuels.'], ['Effet de halo', 'Une impression globale qui colore tout le jugement.'], ['BLUF', 'Bottom Line Up Front : la conclusion en tête.'], ['Résumé exécutif', 'Synthèse lisible seule, destinée au décideur.'], ['Vocabulaire de probabilité', 'Échelle normalisée (presque certain, probable, possible…).']],
  'b' => array_merge([
    ['Bond, Charles F. et DePaulo, Bella M., « Accuracy of Deception Judgments », <em>Personality and Social Psychology Review</em>, 10(3), 2006.', 'Méta-analyse montrant une précision proche du hasard.'],
    ['Commission africaine des droits de l\'homme et des peuples, <em>Déclaration de principes sur la liberté d\'expression et l\'accès à l\'information en Afrique</em>, 2019.', 'Protection des sources journalistiques.'],
    ['Heuer, Richards J. et Pherson, Randolph H., <em>Structured Analytic Techniques for Intelligence Analysis</em>, CQ Press, 2010.', 'Techniques de rédaction et d\'analyse structurée.'],
  ], [$common[1]]),
],
'AFS-C101' => [
  'g' => [['Contre-espionnage', 'Mesures visant à détecter et neutraliser les activités de renseignement hostiles.'], ['Ingérence économique', 'Recherche d\'un avantage indu par des moyens déloyaux.'], ['Menace interne', 'Risque causé par une personne disposant d\'un accès légitime.'], ['Moindre privilège', 'Chacun n\'accède qu\'à ce dont il a besoin.'], ['Patrimoine informationnel', 'Ensemble des informations ayant de la valeur pour l\'organisation.'], ['Ingénierie sociale', 'Manipulation psychologique pour obtenir une information ou une action.'], ['Fraude au président', 'Usurpation d\'un dirigeant pour obtenir un virement.'], ['TLP', 'Traffic Light Protocol : code de partage de l\'information.']],
  'b' => array_merge([
    ['Mitnick, Kevin, <em>The Art of Deception</em>, Wiley, 2002.', 'Exemples classiques d\'ingénierie sociale.'],
    ['Hadnagy, Christopher, <em>Social Engineering: The Science of Human Hacking</em>, Wiley, 2018.', 'Leviers psychologiques et défenses.'],
    ['FIRST, <em>Traffic Light Protocol (TLP) version 2.0</em>, 2022 (en ligne).', 'Norme de marquage du partage.'],
    ['ISO/IEC 27001, Systèmes de management de la sécurité de l\'information.', 'Cadre de référence de la protection de l\'information.'],
  ], [$common[1]]),
],
'AFS-C201' => [
  'g' => [['Duty of care', 'Devoir de protection de l\'employeur envers ses collaborateurs.'], ['Matrice de risques', 'Croisement probabilité × impact pour prioriser.'], ['Nœud papillon', 'Représentation causes – événement – conséquences avec barrières.'], ['Défense en profondeur', 'Superposition de couches de protection.'], ['RTO', 'Durée maximale d\'interruption acceptable.'], ['RPO', 'Perte de données maximale acceptable.'], ['Exercice sur table', 'Simulation de crise par étapes (injects).'], ['RETEX', 'Retour d\'expérience transformé en actions correctives.']],
  'b' => array_merge([
    ['ISO 22301, Sécurité et résilience — Systèmes de management de la continuité d\'activité.', 'Référence de la continuité d\'activité.'],
    ['ISO 31000, Management du risque — Lignes directrices.', 'Principes généraux de l\'analyse de risques.'],
    ['ANSSI (France), <em>Guide d\'hygiène informatique</em> (accès libre).', 'Mesures défensives prioritaires et concrètes.'],
  ], [$common[1]]),
],
'AFS-D101' => [
  'g' => [['APT', 'Menace persistante avancée : groupe sophistiqué et durable.'], ['Chaîne d\'attaque', 'Étapes successives d\'une intrusion, de la reconnaissance à l\'exfiltration.'], ['Pivot', 'Passage d\'un indicateur à d\'autres indicateurs liés.'], ['Empreinte numérique', 'Ensemble des traces publiquement observables.'], ['Métadonnées', 'Données décrivant un fichier (auteur, date, appareil, GPS…).'], ['EXIF', 'Format de métadonnées des photographies.'], ['Empreinte cryptographique', 'Valeur (ex. SHA-256) garantissant l\'intégrité d\'un fichier.'], ['Chaîne de traçabilité', 'Documentation de toutes les manipulations d\'une preuve.'], ['security.txt', 'Fichier normalisé (RFC 9116) indiquant comment signaler une vulnérabilité.']],
  'b' => array_merge([
    ['Haut-Commissariat des Nations unies aux droits de l\'homme et Human Rights Center (UC Berkeley), <em>Protocole de Berkeley sur les enquêtes numériques en sources ouvertes</em>, 2022 (accès libre).', 'Standards professionnels de l\'OSINT.'],
    ['MITRE ATT&amp;CK — base de connaissances des tactiques et techniques (attack.mitre.org).', 'Langage commun de la défense.'],
    ['Hutchins, Eric M., Cloppert, Michael J. et Amin, Rohan M., « Intelligence-Driven Computer Network Defense Informed by Analysis of Adversary Campaigns and Intrusion Kill Chains », Lockheed Martin, 2011.', 'Article fondateur de la chaîne d\'attaque.'],
    ['IETF, RFC 9116, <em>A File Format to Aid in Security Vulnerability Disclosure</em>, 2022.', 'Norme security.txt.'],
  ], [$common[1]]),
],
'AFS-D201' => [
  'g' => [['CTI', 'Cyber Threat Intelligence : renseignement sur les menaces cyber.'], ['IoC', 'Indicateur de compromission (adresse, domaine, empreinte…).'], ['TTP', 'Tactiques, techniques et procédures d\'un adversaire.'], ['Modèle en diamant', 'Adversaire, capacité, infrastructure, victime.'], ['STIX / TAXII', 'Format et protocole d\'échange de la CTI.'], ['Typosquattage', 'Enregistrement de domaines proches d\'une marque pour tromper.'], ['DMARC', 'Politique d\'authentification des e-mails d\'un domaine.'], ['MFA', 'Authentification multifacteur.'], ['Sigma', 'Format ouvert de règles de détection.']],
  'b' => array_merge([
    ['Caltagirone, Sergio, Pendergast, Andrew et Betz, Christopher, <em>The Diamond Model of Intrusion Analysis</em>, 2013.', 'Modèle d\'analyse des intrusions.'],
    ['Bianco, David J., « The Pyramid of Pain », 2013.', 'Hiérarchie des indicateurs selon le coût pour l\'adversaire.'],
    ['OASIS, <em>STIX Version 2.1</em>, 2021.', 'Standard d\'échange de la CTI.'],
    ['FIRST, <em>Traffic Light Protocol 2.0</em>, 2022.', 'Règles de partage.'],
  ], [$common[1]]),
],
'AFS-E101' => [
  'g' => [['Note de cadrage', 'Document fixant finalité, mandat, périmètre, base légale, proportionnalité et conservation.'], ['Graphe de liens', 'Représentation des relations entre entités.'], ['Capture réseau', 'Enregistrement du trafic d\'un réseau dont on est responsable.'], ['Forensique', 'Investigation numérique visant à établir des faits.'], ['Copie bit à bit', 'Duplication exacte d\'un support, vérifiée par empreinte.'], ['Analyse mémoire', 'Examen du contenu de la mémoire vive d\'un système.'], ['Laboratoire isolé', 'Environnement d\'entraînement sans connexion aux réseaux réels.'], ['Proportionnalité', 'Choix du moyen le moins intrusif suffisant.']],
  'b' => array_merge([
    ['Carrier, Brian, <em>File System Forensic Analysis</em>, Addison-Wesley, 2005.', 'Fondements de l\'investigation de supports (auteur de The Sleuth Kit / Autopsy).'],
    ['Ligh, Michael H., Case, Andrew, Levy, Jamie et Walters, Aaron, <em>The Art of Memory Forensics</em>, Wiley, 2014.', 'Référence de l\'analyse mémoire (Volatility).'],
    ['Documentation officielle de Wireshark (Wireshark User\'s Guide, en ligne).', 'Prise en main de l\'analyse de protocoles.'],
    ['Citizen Lab, Université de Toronto — rapports publics sur la surveillance numérique.', 'Études de cas sur les abus de logiciels espions.'],
  ], $common),
],
'AFS-F101' => [
  'g' => [['Intelligence économique', 'Maîtrise et protection de l\'information stratégique par des moyens légaux.'], ['Veille', 'Surveillance organisée de l\'environnement.'], ['Signal faible', 'Information fragmentaire annonçant un changement.'], ['Facteur critique de succès', 'Élément déterminant pour atteindre un objectif.'], ['Secret des affaires', 'Protection juridique des informations confidentielles à valeur économique.'], ['OAPI / ARIPO', 'Offices régionaux africains de propriété intellectuelle.'], ['PESTEL', 'Grille d\'analyse : politique, économique, social, technologique, environnemental, légal.'], ['Scénario', 'Description cohérente d\'un futur possible, non une prévision.']],
  'b' => [
    ['Martre, Henri (dir.), <em>Intelligence économique et stratégie des entreprises</em>, La Documentation française, 1994.', 'Rapport fondateur en langue française.'],
    ['Porter, Michael E., <em>Competitive Strategy</em>, Free Press, 1980.', 'Analyse concurrentielle et forces du marché.'],
    ['Godet, Michel, <em>Manuel de prospective stratégique</em>, Dunod (éditions successives).', 'Méthodes de prospective et de scénarios.'],
    ['Schwartz, Peter, <em>The Art of the Long View</em>, Doubleday, 1991.', 'La méthode des scénarios inspirée de l\'expérience Shell.'],
    ['Secrétariat de la ZLECAf — textes de l\'Accord et documentation officielle (en ligne).', 'Règles du marché continental.'],
  ],
],
'AFS-F201' => [
  'g' => [['APSA', 'Architecture africaine de paix et de sécurité.'], ['CPS', 'Conseil de paix et de sécurité de l\'Union africaine.'], ['Alerte précoce', 'Identification anticipée des risques de crise.'], ['Subsidiarité', 'Traitement d\'un problème au niveau le plus proche pertinent.'], ['Connecteur', 'Acteur ou facteur favorisant la paix.'], ['Risque pays', 'Probabilité qu\'un contexte national affecte un projet.'], ['ESG', 'Critères environnementaux, sociaux et de gouvernance.'], ['ITIE', 'Initiative pour la transparence dans les industries extractives.']],
  'b' => [
    ['Union africaine, <em>Protocole relatif à la création du Conseil de paix et de sécurité</em>, 2002.', 'Fondement juridique de l\'APSA.'],
    ['Union africaine, <em>Agenda 2063 : l\'Afrique que nous voulons</em>, 2015.', 'Vision stratégique continentale.'],
    ['ACLED et UCDP — bases de données ouvertes sur les conflits (en ligne).', 'Données quantitatives documentées.'],
    ['Institute for Security Studies (ISS Africa) et International Crisis Group — rapports publics.', 'Analyses régionales de référence.'],
    ['Nations unies, <em>Principes directeurs relatifs aux entreprises et aux droits de l\'homme</em>, 2011.', 'Cadre de responsabilité des entreprises.'],
  ],
],
];
