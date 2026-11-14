<?php
/**
 * Chapitre « Mise en situation » ajouté à chaque formation (avant l'atelier final) :
 * simulation guidée notée, étude de cas, fiche méthode réutilisable, QCM.
 * Tous les pays, organisations et personnes cités sont FICTIFS.
 */
$bareme = static fn (array $rows) => '<h3>Barème indicatif (auto-évaluation sur 20)</h3><table><thead><tr><th>Critère</th><th>Points</th></tr></thead><tbody>'
    . implode('', array_map(static fn ($r) => '<tr><td>' . $r[0] . '</td><td>' . $r[1] . '</td></tr>', $rows)) . '</tbody></table>';
$fictif = '<div class="note"><strong>Scénario entièrement fictif.</strong> Pays, organisations, personnes et données sont imaginaires et ne décrivent aucune situation réelle.</div>';

return [
'AFS-A101' => [
  'title' => 'Mise en situation : cellule d\'analyse « Delta »',
  'summary' => 'Appliquer l\'ensemble du cycle à un dossier complet, de la commande à la note finale.',
  'lessons' => [
    ['title' => 'Simulation guidée : de la commande à la note', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p><strong>Commande.</strong> La directrice d'une compagnie aérienne régionale fictive, « Air Sahelia », vous demande : <em>« Devons-nous maintenir nos vols vers Port-Saba, capitale de la République de Kanaga, pendant les élections dans six semaines ? »</em></p>
<h3>Étape 1 : Orientation (15 min)</h3>
<p>Formulez trois besoins en renseignement et, pour chacun, deux éléments d'information observables.</p>
<h3>Étape 2 : Plan de collecte (10 min)</h3>
<p>Associez à chaque élément au moins une source ouverte légale et une source de recoupement.</p>
<h3>Étape 3 : Analyse (20 min)</h3>
<p>À partir des éléments fictifs suivants, construisez une matrice ACH à trois hypothèses (vols normaux / perturbations ponctuelles / suspension nécessaire) :</p>
<ul><li>L'autorité de l'aviation civile de Kanaga a publié un avis de fonctionnement normal de l'aéroport.</li><li>Deux manifestations ont eu lieu près du centre-ville, sans incident à l'aéroport.</li><li>Un syndicat de contrôleurs aériens évoque une grève « possible ».</li><li>Les ambassades n'ont pas modifié leurs conseils aux voyageurs.</li></ul>
<h3>Étape 4 : Livrable (15 min)</h3>
<p>Rédigez une note BLUF d'une demi-page avec niveau de confiance, trois indicateurs à surveiller et une recommandation.</p>
HTML . $bareme([['Besoins précis et vérifiables', '4'], ['Plan de collecte légal et recoupé', '3'], ['Matrice ACH cohérente', '5'], ['Note BLUF claire, sourcée, avec confiance', '5'], ['Indicateurs pertinents', '3']])],
    ['title' => 'Étude de cas : le rapport qui arrivait trop tard', 'type' => 'case_study', 'minutes' => 30, 'content' => $fictif . <<<'HTML'
<p>Une ONG humanitaire fictive dispose d'une analyste remarquable, dont les notes sont longues, exhaustives et parfaitement sourcées. Lors d'une crise d'approvisionnement en eau dans une région du nord de Kanaga, sa note de 22 pages arrive quatre jours après la décision de redéployer les équipes, prise sans elle.</p>
<h3>Questions</h3>
<ol><li>Quelle(s) phase(s) du cycle ont échoué ?</li><li>Comment concilier rigueur et délai ? Proposez un format en deux temps (alerte courte puis analyse).</li><li>Quel rôle aurait dû jouer le décideur dans l'orientation ?</li><li>Rédigez l'alerte de cinq lignes qui aurait dû être envoyée le premier jour.</li></ol>
<blockquote>Leçon : la qualité d'un produit inclut sa pertinence temporelle.</blockquote>
HTML],
    ['title' => 'Fiche méthode : modèle de note d\'analyse', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<p>Modèle réutilisable, à copier dans votre traitement de texte.</p>
<pre><code>CLASSIFICATION : [Public / Interne / Confidentiel]       DATE : [JJ/MM/AAAA]
RÉFÉRENCE : [N°]     DESTINATAIRE : [Fonction]     AUTEUR : [Fonction]

TITRE-JUGEMENT : [Une phrase qui dit l'essentiel]

RÉSUMÉ (5 lignes max.)
- Constat principal
- Cause probable
- Conséquence pour le destinataire
- Niveau de confiance : [élevé / modéré / faible], avec justification
- Recommandation ou indicateur à suivre

FAITS ÉTABLIS          (sources cotées, ex. B2)
ANALYSE                (hypothèses concurrentes, raisonnement)
LACUNES                (ce que l'on ignore)
INDICATEURS À SURVEILLER
SOURCES                (références, dates de consultation)</code></pre>
<div class="keypoints"><strong>Points clés</strong><ul><li>Faits, jugements et lacunes toujours séparés.</li><li>Le titre est un jugement, pas un thème.</li><li>Une note = un destinataire et une question.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Dans une situation urgente, quel format concilie rigueur et délai ?', ['Attendre l\'analyse complète', 'Une alerte courte immédiate puis une analyse détaillée', 'Ne rien envoyer', 'Un rapport de 50 pages'], 1, 'Le format en deux temps sert la décision sans sacrifier la rigueur.'],
    ['Le titre d\'une note d\'analyse doit être :', ['Un thème général', 'Un jugement qui dit l\'essentiel', 'Le nom de l\'auteur', 'Une question'], 1, 'Le décideur doit comprendre la conclusion dès le titre.'],
  ],
],
'AFS-A201' => [
  'title' => 'Mise en situation : enquête ouverte sur un incendie de marché',
  'summary' => 'Croiser OSINT, imagerie et témoignages publics pour établir les faits.',
  'lessons' => [
    ['title' => 'Simulation guidée : établir la chronologie d\'un événement', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p><strong>Situation.</strong> Un grand marché de Port-Saba (Kanaga) a été ravagé par un incendie. Sur les réseaux sociaux, trois versions circulent : accident électrique, acte criminel, incendie lié à un conflit entre commerçants. Une assurance vous mandate pour établir les faits publics, sans enquêter sur des personnes.</p>
<h3>Matériel fictif fourni</h3>
<ul><li>4 vidéos publiques publiées entre 02 h 10 et 03 h 40 ;</li><li>2 communiqués (pompiers, mairie) ;</li><li>une image satellitaire « avant » et une « après » ;</li><li>des articles de deux journaux locaux.</li></ul>
<h3>Consignes</h3>
<ol><li>Vérifiez chaque vidéo (antériorité, lieu, heure par les ombres et l'éclairage).</li><li>Établissez une chronologie horodatée en indiquant la source de chaque élément.</li><li>Mesurez sur l'imagerie l'étendue de la zone touchée.</li><li>Pour chacune des trois versions, indiquez les éléments qui la soutiennent ou la contredisent.</li><li>Concluez en séparant faits établis, hypothèses et lacunes.</li></ol>
<div class="warning">Ne cherchez ni à identifier ni à nommer des personnes filmées : ce n'est ni le mandat ni une pratique éthique.</div>
HTML . $bareme([['Vérification méthodique des vidéos', '5'], ['Chronologie sourcée', '5'], ['Exploitation de l\'imagerie', '3'], ['Évaluation équilibrée des versions', '4'], ['Respect du périmètre éthique', '3']])],
    ['title' => 'Étude de cas : l\'image satellite mal interprétée', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Un analyste junior signale « une concentration de véhicules militaires » sur une image satellitaire d'une zone frontalière. Après vérification, il s'agissait de camions de collecte de coton stationnés devant une usine d'égrenage, comme chaque année à la même période.</p>
<ol><li>Quels critères de photo-interprétation auraient évité l'erreur (association, motif, saisonnalité) ?</li><li>Pourquoi une comparaison avec la même date l'année précédente est-elle décisive ?</li><li>Quelles sources ouvertes (calendrier agricole, presse économique) auraient confirmé l'activité ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : journal de vérification', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>N°</th><th>Contenu (URL, archive)</th><th>Première publication</th><th>Lieu vérifié</th><th>Heure estimée</th><th>Méthode</th><th>Confiance</th></tr></thead><tbody>
<tr><td>1</td><td>...</td><td>...</td><td>...</td><td>...</td><td>...</td><td>...</td></tr></tbody></table>
<h3>Réflexes</h3>
<ul><li>Archiver avant d'analyser (le contenu peut disparaître) ;</li><li>Noter la date UTC de chaque consultation ;</li><li>Indiquer la méthode utilisée, pas seulement le résultat.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un journal permet à un tiers de refaire la vérification.</li><li>La confiance s'exprime pour chaque élément.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Pour savoir si une activité observée sur imagerie est inhabituelle, on compare de préférence avec :', ['Une image d\'un autre pays', 'La même zone à la même période l\'année précédente', 'Une carte routière', 'Une photo de presse'], 1, 'La comparaison saisonnière évite de confondre routine et anomalie.'],
    ['Pourquoi archiver un contenu avant de l\'analyser ?', ['Pour le republier', 'Parce qu\'il peut être supprimé ou modifié', 'Pour gagner de la place', 'Par obligation fiscale'], 1, 'La preuve doit être conservée.'],
  ],
],
'AFS-B101' => [
  'title' => 'Mise en situation : mission d\'étude de terrain',
  'summary' => 'Préparer, conduire et évaluer une série d\'entretiens consentis.',
  'lessons' => [
    ['title' => 'Simulation guidée : plan d\'entretiens pour une étude de marché', 'type' => 'exercise', 'minutes' => 50, 'content' => $fictif . <<<'HTML'
<p><strong>Mandat.</strong> Une coopérative fictive de transformation de poisson souhaite comprendre pourquoi ses ventes baissent dans trois villes côtières de Kanaga. Vous disposez de deux jours sur place.</p>
<h3>Consignes</h3>
<ol><li>Identifiez cinq profils d'interlocuteurs pertinents (vendeuses, transporteurs, restaurateurs, association de consommateurs, service public des pêches) et justifiez chaque choix.</li><li>Rédigez le texte de présentation (identité, finalité, confidentialité, droit de refus).</li><li>Préparez un guide de six questions ouvertes adapté à deux de ces profils.</li><li>Prévoyez une grille de cotation des informations recueillies.</li><li>Indiquez trois situations où vous interromprez un entretien (détresse, demande de contrepartie, sujet hors mandat).</li></ol>
HTML . $bareme([['Pertinence des profils', '4'], ['Transparence de la présentation', '4'], ['Qualité des questions ouvertes', '5'], ['Grille de cotation', '4'], ['Limites éthiques anticipées', '3']])],
    ['title' => 'Étude de cas : l\'informateur qui voulait être payé', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Lors d'une étude pour un client industriel, un employé d'un fournisseur concurrent propose de « fournir les prix d'achat internes » contre rémunération. Le chargé d'études hésite : l'information serait très utile au client.</p>
<ol><li>Identifiez les infractions potentielles (corruption, violation du secret des affaires, complicité).</li><li>Quels risques pour le chargé d'études, son cabinet et le client ?</li><li>Rédigez la réponse à apporter à l'employé.</li><li>Quelles sources légales permettraient d'estimer ces prix (statistiques douanières, cours publics des matières premières, entretiens avec des experts) ?</li></ol>
<blockquote>Règle : une information obtenue illégalement contamine toute l'analyse qui en dépend.</blockquote>
HTML],
    ['title' => 'Fiche méthode : compte rendu d\'entretien', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<pre><code>CR N° : [..]      Date / durée : [..]      Lieu : [type de lieu, non nominatif]
Interlocuteur : [code pseudonyme, ex. S-021]   Accès : [nature de sa connaissance]
Consentement : [oui, finalité annoncée, prise de notes acceptée]

1. Verbatim marquants        (entre guillemets)
2. Faits rapportés           (cotation de chaque fait, ex. C2)
3. Opinions de l'interlocuteur
4. Impressions de l'enquêteur (séparées et signalées comme telles)
5. Points à vérifier          (avec sources de recoupement prévues)
6. Engagements pris           (restitution, confidentialité)</code></pre>
<div class="keypoints"><strong>Points clés</strong><ul><li>Rédiger dans l'heure qui suit.</li><li>Ne jamais mêler faits, opinions et impressions.</li><li>Conserver la table de correspondance des pseudonymes à part.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Un interlocuteur propose des données internes de son employeur contre paiement. Que faire ?', ['Accepter si le client paie', 'Refuser et rechercher des sources légales', 'Négocier le prix', 'Accepter sans le dire au client'], 1, 'L\'opération serait illégale et contaminerait l\'analyse.'],
    ['Dans un compte rendu d\'entretien, les impressions de l\'enquêteur doivent être :', ['Mêlées aux faits', 'Séparées et signalées comme telles', 'Supprimées', 'Présentées comme des faits'], 1, 'La séparation garantit l\'honnêteté du document.'],
  ],
],
'AFS-B201' => [
  'title' => 'Mise en situation : le dossier sensible',
  'summary' => 'Protéger une source, interpréter avec prudence et rédiger sous contrainte de temps.',
  'lessons' => [
    ['title' => 'Simulation guidée : rapport protégé en 45 minutes', 'type' => 'exercise', 'minutes' => 45, 'content' => $fictif . <<<'HTML'
<p><strong>Situation.</strong> Un cadre d'une entreprise publique fictive de Kanaga vous a confié, de manière volontaire et consentie, que des retards de paiement aux fournisseurs risquent de provoquer l'arrêt d'un chantier routier stratégique. Il craint pour sa carrière. Votre client (un bailleur de fonds) attend une note avant ce soir.</p>
<h3>Consignes</h3>
<ol><li>Listez les détails du récit qui pourraient permettre d'identifier la source et décidez comment les neutraliser.</li><li>Recherchez au moins deux éléments publics corroborant ou nuançant l'information (presse, rapports d'exécution budgétaire).</li><li>Rédigez une note BLUF d'une page avec description protectrice de la source.</li><li>Définissez la liste de diffusion et le marquage.</li></ol>
HTML . $bareme([['Neutralisation des détails identifiants', '6'], ['Corroboration publique', '4'], ['Qualité de la note BLUF', '6'], ['Diffusion maîtrisée', '4']])],
    ['title' => 'Étude de cas : le recruteur « intuitif »', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Le responsable sécurité d'une société de transport fictive écarte systématiquement les candidats qui « évitent le regard » lors des entretiens, persuadé de repérer ainsi les personnes malhonnêtes. Plusieurs candidats issus de régions où baisser les yeux devant un aîné est une marque de respect sont éliminés.</p>
<ol><li>Quelles erreurs scientifiques et quels biais sont en cause ?</li><li>Quels risques juridiques (discrimination) l'entreprise court-elle ?</li><li>Proposez une procédure de recrutement fondée sur des critères vérifiables.</li></ol>
HTML],
    ['title' => 'Fiche méthode : check-list de protection d\'une source', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<ul class="checklist">
<li>Pseudonyme attribué et table séparée</li>
<li>Accès décrit sans détail identifiant</li>
<li>Dates et lieux de rencontre généralisés</li>
<li>Liste de diffusion restreinte et nominative</li>
<li>Marquage du document</li>
<li>Échanges sur messagerie chiffrée</li>
<li>Métadonnées du fichier nettoyées</li>
<li>Durée de conservation définie</li>
</ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Relire chaque rapport « comme un adversaire » cherchant la source.</li><li>Moins de détails, plus de sécurité.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Écarter des candidats parce qu\'ils évitent le regard est :', ['Une méthode fiable', 'Scientifiquement infondé et potentiellement discriminatoire', 'Une obligation légale', 'Une pratique recommandée'], 1, 'Le contact visuel varie selon les cultures et ne prédit pas l\'honnêteté.'],
    ['Comment relire un rapport pour protéger une source ?', ['Rapidement', 'Comme un adversaire cherchant à l\'identifier', 'Uniquement pour l\'orthographe', 'Ce n\'est pas nécessaire'], 1, 'Cette relecture révèle les détails identifiants.'],
  ],
],
'AFS-C101' => [
  'title' => 'Mise en situation : audit de vigilance d\'une PME exportatrice',
  'summary' => 'Diagnostiquer les vulnérabilités informationnelles et proposer un plan d\'action.',
  'lessons' => [
    ['title' => 'Simulation guidée : diagnostic en une journée', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p><strong>Organisation fictive :</strong> « Karité d'Or », 35 salariés, exporte des cosmétiques naturels et négocie un contrat de distribution avec un groupe étranger.</p>
<h3>Constats de visite (fictifs)</h3>
<ul><li>La formule des produits est stockée sur un ordinateur partagé sans mot de passe ;</li><li>Les visiteurs circulent librement dans l'atelier de production ;</li><li>Le commercial publie ses déplacements sur ses réseaux sociaux ;</li><li>La comptable a reçu un courriel d'un « nouveau transitaire » demandant les factures du client principal ;</li><li>Aucun accord de confidentialité n'a été signé avec le futur distributeur.</li></ul>
<h3>Consignes</h3>
<ol><li>Classez les constats par gravité (matrice probabilité × impact).</li><li>Proposez une mesure par constat, avec coût estimé (faible / moyen / élevé).</li><li>Rédigez un plan d'action sur 90 jours en trois priorités.</li><li>Préparez un message de sensibilisation de dix lignes pour les salariés.</li></ol>
HTML . $bareme([['Hiérarchisation justifiée', '5'], ['Mesures proportionnées', '5'], ['Plan 90 jours réaliste', '5'], ['Message de sensibilisation clair et bienveillant', '5']])],
    ['title' => 'Étude de cas : la délégation trop curieuse', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Une délégation d'« investisseurs » visite une usine de semences améliorées. Pendant la visite, deux membres photographient les installations, posent des questions détaillées sur les procédés de sélection et demandent à échanger seuls avec les techniciens. Aucune lettre d'intention n'a été signée.</p>
<ol><li>Quels signaux auraient dû alerter ?</li><li>Quelles règles de visite mettre en place (parcours défini, photos interdites, accompagnement, accord de confidentialité préalable) ?</li><li>Comment poursuivre une relation commerciale légitime tout en protégeant le savoir-faire ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : protocole d\'accueil des visiteurs', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<ol><li><strong>Avant</strong> : liste nominative des visiteurs, objet de la visite, accord de confidentialité si des informations non publiques seront présentées.</li><li><strong>À l'arrivée</strong> : badge visiteur, rappel des règles (photos, zones interdites).</li><li><strong>Pendant</strong> : accompagnement permanent, parcours défini, documents sensibles rangés.</li><li><strong>Après</strong> : compte rendu, signalement de tout comportement inhabituel.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Accueillir n'est pas tout montrer.</li><li>Les règles s'annoncent avant la visite.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Lors d\'une visite d\'usine, quelle règle est la plus protectrice ?', ['Laisser les visiteurs circuler librement', 'Un parcours défini avec accompagnement permanent', 'Autoriser toutes les photos', 'Les laisser seuls avec les techniciens'], 1, 'Le parcours accompagné limite les fuites involontaires.'],
    ['Une formule de produit stockée sans protection sur un poste partagé relève d\'une vulnérabilité :', ['Faible', 'Critique, car elle touche le cœur du savoir-faire', 'Inexistante', 'Purement juridique'], 1, 'C\'est un actif essentiel exposé.'],
  ],
],
'AFS-C201' => [
  'title' => 'Mise en situation : plan de sécurité d\'une mission régionale',
  'summary' => 'Préparer une équipe en déplacement et réagir à un incident.',
  'lessons' => [
    ['title' => 'Simulation guidée : préparer la mission « Sentinelle »', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p><strong>Mission fictive.</strong> Quatre consultants d'un cabinet d'audit doivent passer dix jours dans trois villes de Kanaga pour évaluer des centres de santé. L'une des villes connaît des tensions intercommunautaires épisodiques.</p>
<h3>Consignes</h3>
<ol><li>Réalisez l'analyse de risques (personnes, informations, matériel, réputation).</li><li>Rédigez le plan de déplacement : itinéraires, horaires, points de contact, protocole de « check-in » quotidien.</li><li>Définissez l'équipement numérique et les règles d'usage.</li><li>Écrivez la procédure en cas de perte de contact pendant plus de quatre heures.</li><li>Prévoyez le débriefing de retour.</li></ol>
HTML . $bareme([['Analyse de risques complète', '5'], ['Plan de déplacement opérationnel', '5'], ['Règles numériques', '3'], ['Procédure de perte de contact', '4'], ['Débriefing prévu', '3']])],
    ['title' => 'Étude de cas : la clé USB du séminaire', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>À l'issue d'un séminaire, chaque participant reçoit une clé USB « contenant les présentations ». Un responsable financier la branche sur son ordinateur professionnel. Deux semaines plus tard, des courriels frauduleux très crédibles sont envoyés à ses fournisseurs depuis sa messagerie.</p>
<ol><li>Reconstituez la chaîne d'attaque probable.</li><li>Placez sur un nœud papillon les barrières qui ont manqué.</li><li>Quelle politique adopter vis-à-vis des supports amovibles reçus ?</li><li>Quelles actions immédiates après la découverte ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : fiche de mission sécurité', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<pre><code>MISSION : [nom]        DATES : [..]        CHEF DE MISSION : [..]
PARTICIPANTS / CONTACTS D'URGENCE : [..]
ANALYSE PAYS : [sources officielles consultées, date]
ITINÉRAIRE ET HÉBERGEMENTS : [..]
CHECK-IN : [heure quotidienne, canal, destinataire]
ÉQUIPEMENT : [poste dédié, chiffrement, VPN, téléphone de mission]
PROCÉDURE INCIDENT : [qui appeler, dans quel ordre]
PERTE DE CONTACT > 4 h : [actions graduées]
RETOUR : [débriefing, contrôle des appareils]</code></pre>
<div class="keypoints"><strong>Points clés</strong><ul><li>Une fiche par mission, validée avant le départ.</li><li>Le check-in quotidien est non négociable.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Que faire d\'une clé USB reçue lors d\'un séminaire ?', ['La brancher immédiatement', 'Ne pas la brancher sur un poste professionnel et la confier au service informatique', 'La donner à un collègue', 'La formater sur son ordinateur'], 1, 'Les supports amovibles inconnus sont un vecteur d\'attaque classique.'],
    ['À quoi sert le check-in quotidien en mission ?', ['À contrôler les dépenses', 'À détecter rapidement une perte de contact', 'À planifier les repas', 'Rien de particulier'], 1, 'Il déclenche la procédure d\'urgence si nécessaire.'],
  ],
],
'AFS-D101' => [
  'title' => 'Mise en situation : rapport d\'exposition pour un client',
  'summary' => 'Conduire un audit d\'exposition mandaté sur données synthétiques et le restituer.',
  'lessons' => [
    ['title' => 'Simulation guidée : audit « Kilimanjaro Logistics » complet', 'type' => 'exercise', 'minutes' => 75, 'content' => $fictif . <<<'HTML'
<p>Vous disposez d'un <strong>mandat écrit</strong> (fictif) de la société « Kilimanjaro Logistics » pour évaluer son exposition publique. Les données suivantes sont synthétiques :</p>
<pre><code>Sous-domaines : www, mail, vpn, test-erp, old-portal, dev
Certificats récents : erp-preprod.kilimanjaro-logistics.example
Document public : Rapport_annuel.pdf  (Auteur: j.mwangi ; Producteur: Office 2013)
Offre d'emploi : « administrateur système Windows Server 2012, VPN FortiGate »
Profils publics : 14 employés, format d'adresse prenom.nom@...</code></pre>
<h3>Consignes</h3>
<ol><li>Classez les expositions en critique / importante / mineure en justifiant.</li><li>Expliquez, pour les trois plus graves, comment un attaquant pourrait les exploiter (sans détail technique offensif).</li><li>Proposez une remédiation par exposition, avec responsable et délai.</li><li>Rédigez un résumé exécutif d'une demi-page pour la direction, sans jargon.</li></ol>
HTML . $bareme([['Classement argumenté', '5'], ['Compréhension des risques', '4'], ['Remédiations concrètes', '6'], ['Résumé exécutif accessible', '5']])],
    ['title' => 'Étude de cas : la photo du bureau', 'type' => 'case_study', 'minutes' => 20, 'content' => $fictif . <<<'HTML'
<p>Pour célébrer un contrat, le service communication d'une agence publique publie une photo de l'équipe dans son bureau. En arrière-plan, un tableau blanc affiche des noms de serveurs et un post-it porte un mot de passe Wi-Fi.</p>
<ol><li>Quelles informations un tiers peut-il tirer de la photo ?</li><li>Quelles actions immédiates après la publication ?</li><li>Quelle règle intégrer à la procédure de publication des contenus visuels ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : structure d\'un rapport d\'exposition', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<ol><li><strong>Mandat et périmètre</strong> (référence du mandat écrit, exclusions)</li><li><strong>Résumé exécutif</strong> (risques principaux, actions prioritaires)</li><li><strong>Méthodologie</strong> (sources ouvertes consultées, dates)</li><li><strong>Constats</strong> (tableau : exposition, gravité, preuve archivée)</li><li><strong>Recommandations</strong> (responsable, délai, coût)</li><li><strong>Annexes</strong> (journal d'enquête, empreintes des fichiers)</li><li><strong>Destruction des données</strong> collectées à la clôture</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le rapport lui-même est confidentiel : marquage et diffusion restreinte.</li><li>Aucune donnée personnelle superflue.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Un rapport d\'exposition doit commencer par :', ['Les annexes techniques', 'Le mandat, le périmètre et le résumé exécutif', 'La liste des employés', 'Les outils utilisés'], 1, 'Le cadre et l\'essentiel d\'abord.'],
    ['Une photo publique d\'un bureau peut révéler :', ['Rien d\'utile', 'Des informations techniques visibles en arrière-plan', 'Uniquement l\'humeur de l\'équipe', 'La météo'], 1, 'Tableaux, écrans et post-it sont des fuites fréquentes.'],
  ],
],
'AFS-D201' => [
  'title' => 'Mise en situation : cellule CTI sectorielle',
  'summary' => 'Produire et partager une analyse de menace pour un secteur entier.',
  'lessons' => [
    ['title' => 'Simulation guidée : bulletin CTI hebdomadaire du secteur bancaire', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p>Vous animez la cellule CTI (fictive) d'une association de banques de la zone « UMOK ». Cette semaine, vous recevez :</p>
<ul><li>de la banque A : 3 domaines d'hameçonnage imitant son application mobile ;</li><li>de la banque B : une hausse de tentatives de connexion depuis des adresses déjà signalées ;</li><li>du CERT national : un avis sur une campagne de faux SMS de livraison ;</li><li>d'un éditeur de sécurité : un rapport public sur un groupe ciblant les banques africaines.</li></ul>
<h3>Consignes</h3>
<ol><li>Rédigez un bulletin en trois niveaux : stratégique (5 lignes pour les directions générales), opérationnel (campagnes et TTP), tactique (indicateurs).</li><li>Attribuez un marquage TLP à chaque partie et justifiez.</li><li>Formulez trois recommandations mesurables.</li><li>Proposez un indicateur de performance du dispositif de partage.</li></ol>
HTML . $bareme([['Trois niveaux bien distingués', '6'], ['Marquages TLP justifiés', '4'], ['Recommandations mesurables', '5'], ['Indicateur de performance pertinent', '5']])],
    ['title' => 'Étude de cas : le faux positif qui coûtait cher', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Une banque bloque automatiquement une liste d'adresses IP reçue d'un partenaire. Parmi elles figure l'adresse d'un fournisseur de services de paiement partagée par de nombreux clients : les paiements en ligne sont interrompus pendant six heures.</p>
<ol><li>Quelles informations manquaient à l'indicateur (contexte, date, confiance) ?</li><li>Quelle procédure de qualification aurait évité l'incident ?</li><li>Comment la pyramide de la douleur éclaire-t-elle le choix de détecter des comportements plutôt que des adresses ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : modèle de bulletin CTI', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<pre><code>BULLETIN CTI N° [..]   PÉRIODE : [..]   MARQUAGE GLOBAL : TLP:[..]
1. SYNTHÈSE STRATÉGIQUE (direction)         : 5 lignes, impact métier
2. CAMPAGNES OBSERVÉES (sécurité)           : description, TTP (ATT&CK), cibles
3. INDICATEURS (SOC)                        : type | valeur | 1re/dernière observation | confiance
4. RECOMMANDATIONS                          : action | responsable | échéance
5. SOURCES                                  : émetteur | date | fiabilité</code></pre>
<div class="keypoints"><strong>Points clés</strong><ul><li>Chaque indicateur porte une date et un niveau de confiance.</li><li>La synthèse stratégique ne contient pas de jargon.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Pourquoi bloquer automatiquement une liste d\'IP sans qualification est-il risqué ?', ['Ce n\'est pas risqué', 'Une IP partagée peut être légitime et le blocage interrompre un service', 'Les IP sont toujours malveillantes', 'Le blocage est illégal'], 1, 'Les infrastructures partagées génèrent des faux positifs.'],
    ['Un bulletin CTI destiné aux directions générales doit surtout présenter :', ['Les adresses IP', 'L\'impact métier en quelques lignes', 'Le code source des logiciels malveillants', 'Les journaux bruts'], 1, 'Chaque niveau a son lecteur.'],
  ],
],
'AFS-E101' => [
  'title' => 'Mise en situation : équiper une cellule de veille',
  'summary' => 'Choisir, justifier et encadrer les outils d\'une nouvelle cellule.',
  'lessons' => [
    ['title' => 'Simulation guidée : cahier des charges outillage', 'type' => 'exercise', 'minutes' => 50, 'content' => $fictif . <<<'HTML'
<p>Une chambre de commerce fictive crée une cellule de veille de trois personnes chargée de suivre les marchés régionaux, de protéger l'image de ses membres et de vérifier les informations circulant en ligne. Budget limité.</p>
<h3>Consignes</h3>
<ol><li>Listez les missions et, pour chacune, l'outil logiciel ou matériel adapté (privilégier libre ou gratuit lorsque c'est suffisant).</li><li>Rédigez pour chaque outil une ligne de cadrage : finalité, données traitées, durée de conservation.</li><li>Définissez la configuration du poste de travail (comptes séparés, chiffrement, sauvegardes).</li><li>Proposez un plan de formation de cinq jours.</li></ol>
HTML . $bareme([['Adéquation outils / missions', '6'], ['Lignes de cadrage', '5'], ['Sécurité du poste', '4'], ['Plan de formation', '5']])],
    ['title' => 'Étude de cas : l\'outil gratuit trop bavard', 'type' => 'case_study', 'minutes' => 20, 'content' => $fictif . <<<'HTML'
<p>Un analyste utilise un service en ligne gratuit pour « enrichir » automatiquement une liste de 2 000 adresses e-mail de clients de son entreprise. Il découvre ensuite que les conditions d'utilisation autorisent le service à conserver et réutiliser les données soumises.</p>
<ol><li>Quelles obligations de protection des données ont été méconnues ?</li><li>Que faut-il vérifier avant d'utiliser un service en ligne (conditions, localisation, conservation) ?</li><li>Quelles actions correctives engager ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : évaluer un outil avant adoption', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>Critère</th><th>Questions</th></tr></thead><tbody>
<tr><td>Finalité</td><td>Répond-il à une mission définie ?</td></tr>
<tr><td>Légalité</td><td>Son usage est-il licite dans les pays concernés ?</td></tr>
<tr><td>Données</td><td>Quelles données transmet-il, à qui, où sont-elles stockées ?</td></tr>
<tr><td>Licence</td><td>Usage commercial autorisé ? Coût réel ?</td></tr>
<tr><td>Fiabilité</td><td>Maintenu ? Documentation ? Communauté ?</td></tr>
<tr><td>Sécurité</td><td>Mises à jour, authentification, journalisation ?</td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>« Gratuit » peut signifier que vos données sont le prix.</li><li>Lire les conditions d'utilisation avant tout envoi de données.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Avant de soumettre des données à un service en ligne gratuit, il faut :', ['Rien vérifier', 'Lire les conditions d\'utilisation et vérifier la conservation des données', 'Le partager avec ses collègues', 'Désactiver l\'antivirus'], 1, 'Les données soumises peuvent être réutilisées par le service.'],
    ['Quel critère d\'évaluation d\'un outil porte sur la licéité de son usage ?', ['Fiabilité', 'Légalité', 'Licence', 'Ergonomie'], 1, 'La légalité de l\'usage dépend du droit applicable.'],
  ],
],
'AFS-F101' => [
  'title' => 'Mise en situation : stratégie d\'export dans la ZLECAf',
  'summary' => 'Mobiliser veille, protection et influence pour un projet d\'export.',
  'lessons' => [
    ['title' => 'Simulation guidée : dossier d\'aide à la décision', 'type' => 'exercise', 'minutes' => 60, 'content' => $fictif . <<<'HTML'
<p>La PME fictive « Soleil Mango » produit des jus de fruits et hésite entre deux marchés d'export : le pays côtier de Kanaga et le pays enclavé de Tarenda. Le conseil d'administration décide dans un mois.</p>
<h3>Consignes</h3>
<ol><li>Définissez les facteurs critiques de succès et trois questions de veille.</li><li>Réalisez une analyse PESTEL comparative des deux marchés (données fictives à inventer de façon cohérente et signalées comme telles).</li><li>Identifiez les actifs à protéger (recettes, marque, fichiers clients) et les mesures associées (dépôt de marque régional, accords de confidentialité).</li><li>Construisez quatre scénarios avec deux incertitudes clés.</li><li>Rédigez la note stratégique de deux pages avec recommandation.</li></ol>
HTML . $bareme([['Facteurs critiques et questions de veille', '3'], ['PESTEL comparatif', '5'], ['Plan de protection', '4'], ['Scénarios cohérents', '4'], ['Note stratégique argumentée', '4']])],
    ['title' => 'Étude de cas : la marque déposée par un autre', 'type' => 'case_study', 'minutes' => 25, 'content' => $fictif . <<<'HTML'
<p>Au moment d'exporter vers un pays voisin, une entreprise découvre qu'un distributeur local a déposé sa marque à son propre nom deux ans plus tôt. Il propose de la « rétrocéder » contre une forte somme.</p>
<ol><li>Quelle erreur de veille et de protection a été commise ?</li><li>Quelles voies de recours explorer (opposition, action en nullité pour dépôt de mauvaise foi, selon le droit applicable) ?</li><li>Quel dispositif de surveillance des dépôts de marques mettre en place ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : tableau de bord de veille', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>Axe</th><th>Question clé</th><th>Sources</th><th>Fréquence</th><th>Responsable</th><th>Dernier signal</th></tr></thead><tbody>
<tr><td>Concurrence</td><td>...</td><td>...</td><td>Hebdo</td><td>...</td><td>...</td></tr>
<tr><td>Réglementation</td><td>...</td><td>...</td><td>Mensuel</td><td>...</td><td>...</td></tr>
<tr><td>Marques et brevets</td><td>Nouveaux dépôts proches de nos marques ?</td><td>Bulletins officiels de propriété intellectuelle</td><td>Mensuel</td><td>...</td><td>...</td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>Chaque axe a un responsable.</li><li>Surveiller les dépôts de marques avant d'entrer sur un marché.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Quelle mesure aurait évité le dépôt de la marque par un distributeur ?', ['Aucune', 'Déposer la marque dans les pays cibles avant d\'y exporter et surveiller les dépôts', 'Changer de logo', 'Ne pas exporter'], 1, 'La protection doit précéder l\'entrée sur un marché.'],
    ['Dans un tableau de bord de veille, chaque axe doit avoir :', ['Un slogan', 'Une question clé, des sources, une fréquence et un responsable', 'Un budget illimité', 'Un logo'], 1, 'C\'est ce qui rend la veille opérationnelle.'],
  ],
],
'AFS-F201' => [
  'title' => 'Mise en situation : comité d\'investissement',
  'summary' => 'Évaluer un projet dans un contexte instable et défendre une recommandation.',
  'lessons' => [
    ['title' => 'Simulation guidée : avis de risque pour un projet solaire', 'type' => 'exercise', 'minutes' => 75, 'content' => $fictif . <<<'HTML'
<p>Un fonds d'investissement fictif étudie une centrale solaire dans une région frontalière de Tarenda (pays imaginaire). Éléments fictifs : élections dans un an, tensions foncières anciennes, forte demande en électricité, financement d'une banque régionale de développement, présence de communautés pastorales sur une partie du site.</p>
<h3>Consignes</h3>
<ol><li>Réalisez le profil de la région et la cartographie des acteurs (y compris les connecteurs).</li><li>Notez les six dimensions du risque pays/projet et pondérez-les.</li><li>Identifiez les exigences ESG applicables (consultation des communautés, accès à l'eau, couloirs de transhumance).</li><li>Construisez trois scénarios à trois ans avec indicateurs.</li><li>Rédigez un avis : investir / investir sous conditions / ne pas investir, avec conditions précises.</li></ol>
HTML . $bareme([['Analyse d\'acteurs nuancée', '4'], ['Notation argumentée', '4'], ['Exigences ESG identifiées', '4'], ['Scénarios et indicateurs', '4'], ['Avis clair et conditionné', '4']])],
    ['title' => 'Étude de cas : la carte qui fâchait', 'type' => 'case_study', 'minutes' => 20, 'content' => $fictif . <<<'HTML'
<p>Un rapport de risque diffusé à des investisseurs colorie en rouge une province entière « à éviter », sur la base d'incidents concentrés dans deux districts. Les autorités locales et les entreprises de la province protestent : les investissements s'effondrent, y compris dans les zones calmes.</p>
<ol><li>Quelles erreurs méthodologiques (échelle, données, vocabulaire) ?</li><li>Comment représenter le risque de façon plus fine et plus juste ?</li><li>Quelle responsabilité l'analyste porte-t-il dans les effets de ses publications ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : grille de notation du risque pays', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>Dimension</th><th>Note (1 à 5)</th><th>Tendance (hausse, stable, baisse)</th><th>Poids projet</th><th>Justification et sources</th></tr></thead><tbody>
<tr><td>Politique</td><td></td><td></td><td></td><td></td></tr><tr><td>Sécuritaire</td><td></td><td></td><td></td><td></td></tr>
<tr><td>Économique</td><td></td><td></td><td></td><td></td></tr><tr><td>Juridique</td><td></td><td></td><td></td><td></td></tr>
<tr><td>Opérationnelle</td><td></td><td></td><td></td><td></td></tr><tr><td>Environnementale et sociale</td><td></td><td></td><td></td><td></td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>Toujours préciser l'échelle géographique.</li><li>La tendance compte autant que la note.</li><li>Une note sans justification n'a pas de valeur.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Colorier toute une province en rouge à partir d\'incidents localisés est une erreur de :', ['Typographie', 'Échelle géographique', 'Traduction', 'Mise en page'], 1, 'Le risque doit être représenté à la bonne échelle.'],
    ['Dans une grille de risque pays, la tendance indique :', ['La couleur de la carte', 'L\'évolution probable de la situation', 'Le nom de l\'analyste', 'Le coût du projet'], 1, 'Une même note peut cacher une amélioration ou une dégradation.'],
  ],
],
'AFS-G101' => [
  'title' => 'Mise en situation : appui renseignement à la sécurisation d\'un scrutin',
  'summary' => 'Analyser une zone, définir les besoins prioritaires et présenter un briefing au chef.',
  'lessons' => [
    ['title' => 'Simulation guidée : briefing avant une période électorale', 'type' => 'exercise', 'minutes' => 75, 'content' => $fictif . <<<'HTML'
<p>Les forces armées de la République de Kassanié (pays imaginaire) appuient la police pour sécuriser un scrutin local dans la région du Haut-Sorou. Éléments fictifs : deux candidats issus de communautés rivales ; un pont endommagé isole trois bureaux de vote ; des messages appelant au boycott circulent ; la saison des pluies commence ; des déplacés vivent dans un camp près du chef-lieu.</p>
<h3>Consignes</h3>
<ol><li>Réalisez l'analyse de la zone avec les grilles PMESII et ASCOPE.</li><li>Formulez trois besoins prioritaires en renseignement liés aux décisions du chef.</li><li>Présentez l'hypothèse la plus probable et la plus dangereuse, avec cinq indicateurs chacune.</li><li>Identifiez les mesures de sécurité des opérations à imposer aux unités.</li><li>Rédigez un briefing de cinq minutes en précisant ce qui relève des autorités civiles et le respect de la neutralité politique des forces.</li></ol>
HTML . $bareme([['Analyse de zone complète et nuancée', '4'], ['Besoins prioritaires précis et datés', '4'], ['Hypothèses et indicateurs pertinents', '4'], ['Sécurité des opérations et neutralité', '4'], ['Briefing clair et utile au chef', '4']])],
    ['title' => 'Étude de cas : la photo de la base', 'type' => 'case_study', 'minutes' => 20, 'content' => $fictif . <<<'HTML'
<p>Un soldat publie sur un réseau social une photo souriante devant le portail d'une base avancée, avec la mention « bientôt la relève ! ». La photo, géolocalisée, montre l'entrée, des véhicules et un planning affiché au mur. Elle est partagée des centaines de fois.</p>
<ol><li>Quelles informations critiques ont été exposées ?</li><li>Quels risques pour l'unité et pour la population voisine ?</li><li>Quelles mesures immédiates et quelles règles durables proposez-vous ?</li><li>Comment sensibiliser sans humilier le soldat concerné ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : le briefing renseignement en cinq minutes', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>Temps</th><th>Contenu</th></tr></thead><tbody>
<tr><td>30 secondes</td><td>Le message essentiel : la conclusion d'abord</td></tr>
<tr><td>1 minute</td><td>La situation : faits évalués, sources et confiance</td></tr>
<tr><td>2 minutes</td><td>Les hypothèses : la plus probable, la plus dangereuse, et leurs indicateurs</td></tr>
<tr><td>1 minute</td><td>Les conséquences pour la décision du chef (« et alors ? »)</td></tr>
<tr><td>30 secondes</td><td>Les lacunes et les besoins de recherche proposés</td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>La conclusion d'abord.</li><li>Une carte simple vaut mieux qu'un long texte.</li><li>Toujours dire ce que l'on ignore.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Dans un briefing renseignement, on présente en premier :', ['L\'historique complet de la région', 'Le message essentiel, la conclusion', 'La liste des sources', 'Les questions du public'], 1, 'Le chef doit saisir l\'essentiel en quelques secondes.'],
    ['Lors d\'une élection, les forces armées qui appuient la sécurité doivent :', ['Soutenir un candidat', 'Respecter une stricte neutralité politique', 'Organiser le vote', 'Diffuser des consignes de vote'], 1, 'La neutralité est une condition de légitimité et de sécurité.'],
  ],
],
'AFS-G201' => [
  'title' => 'Mise en situation : cellule d\'analyse d\'une mission régionale',
  'summary' => 'Fusionner des rapports, protéger les civils et partager l\'information avec les partenaires.',
  'lessons' => [
    ['title' => 'Simulation guidée : avant le marché transfrontalier', 'type' => 'exercise', 'minutes' => 75, 'content' => $fictif . <<<'HTML'
<p>Vous dirigez la cellule d'analyse d'une mission régionale fictive dans la province de Lowani. Vous recevez trois rapports : un contingent signale des hommes armés près d'une piste ; la section des droits humains rapporte des menaces contre des déplacés ; un partenaire étatique transmet une information classifiée, avec interdiction de rediffusion, annonçant une attaque « dans les prochains jours ». Le grand marché transfrontalier a lieu dans quatre jours.</p>
<h3>Consignes</h3>
<ol><li>Évaluez et recoupez les trois rapports.</li><li>Construisez la matrice menaces, vulnérabilités et capacités de protection pour le jour du marché.</li><li>Proposez des mesures de protection hiérarchisées et leurs responsables.</li><li>Déterminez ce que vous pouvez partager avec les autorités locales et les humanitaires, sous quelle forme, en respectant le contrôle de l'émetteur.</li><li>Identifiez les risques juridiques et éthiques et les garanties à prévoir.</li></ol>
HTML . $bareme([['Évaluation et recoupement des rapports', '4'], ['Matrice de protection complète', '4'], ['Mesures réalistes et hiérarchisées', '4'], ['Partage conforme aux règles', '4'], ['Garanties juridiques et éthiques', '4']])],
    ['title' => 'Étude de cas : le rapport qui n\'a pas circulé', 'type' => 'case_study', 'minutes' => 20, 'content' => $fictif . <<<'HTML'
<p>Une alerte sur l'imminence de violences contre un village est restée bloquée dans la composante qui l'avait reçue, faute de procédure de partage et par crainte de compromettre la source. Les violences ont eu lieu. Une autre composante disposait pourtant de moyens pour renforcer la présence dans le village.</p>
<ol><li>Quels dysfonctionnements identifiez-vous ?</li><li>Comment concilier protection de la source et diffusion de l'alerte ?</li><li>Quelles procédures proposer pour l'avenir ?</li></ol>
HTML],
    ['title' => 'Fiche méthode : matrice de protection des civils', 'type' => 'text', 'minutes' => 10, 'content' => <<<'HTML'
<table><thead><tr><th>Lieu ou période</th><th>Menace</th><th>Vulnérabilités</th><th>Capacités de protection</th><th>Mesure proposée</th><th>Responsable</th></tr></thead><tbody>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>Une ligne par lieu ou période sensible.</li><li>Chaque mesure a un responsable identifié.</li><li>Revoir la matrice à chaque nouvelle information.</li></ul></div>
HTML],
  ],
  'quiz' => [
    ['Une information reçue avec interdiction de rediffusion peut être utilisée pour protéger des civils :', ['En la transmettant telle quelle à tous', 'En obtenant l\'accord de l\'émetteur ou en partageant une alerte assainie', 'Jamais, en aucun cas', 'En la publiant'], 1, 'Le contrôle de l\'émetteur se concilie avec la protection par l\'accord ou l\'assainissement.'],
    ['Une alerte bloquée dans une seule composante révèle surtout :', ['Un excès de partage', 'Une absence de procédure de fusion et de diffusion', 'Un manque de matériel', 'Un problème de langue uniquement'], 1, 'Les procédures de partage interne sont aussi importantes que la collecte.'],
  ],
],
];
