<?php
/**
 * Enrichissements pédagogiques — modules A et B.
 * Clé : code de formation. 'chapters' : index (0 = premier chapitre) => leçons et questions ajoutées.
 * 'atelier' : leçons ajoutées au dernier chapitre (glossaire, ressources).
 */
return [
'AFS-A101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : besoins en renseignement et produits', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Le cycle ne démarre vraiment qu'avec un <strong>besoin en renseignement</strong> bien formulé. Les services distinguent généralement trois niveaux de besoins, du plus large au plus précis.</p>
<table><thead><tr><th>Niveau</th><th>Formulation</th><th>Exemple</th></tr></thead><tbody>
<tr><td>Priorité</td><td>Grand thème fixé par le décideur</td><td>Stabilité des corridors logistiques régionaux</td></tr>
<tr><td>Besoin en renseignement</td><td>Question analytique</td><td>Quels facteurs pourraient perturber le corridor Abidjan–Ouagadougou dans les six mois ?</td></tr>
<tr><td>Élément d'information</td><td>Fait observable à collecter</td><td>Évolution des délais de passage aux postes frontières</td></tr>
</tbody></table>
<h3>Les principaux types de produits</h3>
<ul>
<li><strong>Note de situation</strong> : ce qui se passe maintenant, diffusée rapidement.</li>
<li><strong>Note d'analyse</strong> : pourquoi cela se passe, quelles conséquences.</li>
<li><strong>Évaluation prospective</strong> : scénarios à moyen terme, indicateurs à surveiller.</li>
<li><strong>Alerte</strong> : signal d'un changement imminent nécessitant une décision.</li>
</ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un besoin flou produit une collecte inutile.</li><li>Chaque produit répond à un horizon temporel et à un lecteur précis.</li><li>Le retour du décideur (feedback) améliore le cycle suivant.</li></ul></div>
<div class="note"><strong>Mini-cas.</strong> Une banque demande « tout sur le pays X ». L'analyste propose trois besoins : risque de change, calendrier électoral, sécurité des agences en zone frontalière. La collecte devient ciblée et le livrable utile.</div>
HTML]],
      'quiz' => [
        ['Quel niveau correspond à « l\'évolution des délais de passage aux frontières » ?', ['Une priorité', 'Un besoin en renseignement', 'Un élément d\'information', 'Un produit fini'], 2, 'C\'est un fait observable à collecter pour répondre au besoin.'],
        ['Quel produit signale un changement imminent appelant une décision ?', ['La note de situation', 'L\'alerte', 'L\'évaluation prospective', 'La bibliographie'], 1, 'L\'alerte est courte, urgente et orientée décision.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : contrôle démocratique des services', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>L'histoire montre que la question centrale n'est pas seulement l'efficacité des services, mais leur <strong>redevabilité</strong>. Un service sans contrôle tend à servir un pouvoir plutôt que l'État et les citoyens.</p>
<h3>Les quatre formes de contrôle</h3>
<ol>
<li><strong>Exécutif</strong> : directives ministérielles, inspections internes.</li>
<li><strong>Parlementaire</strong> : commissions spécialisées, contrôle budgétaire.</li>
<li><strong>Juridictionnel</strong> : autorisation et recours devant un juge.</li>
<li><strong>Indépendant</strong> : autorités administratives, médiateurs, autorités de protection des données.</li>
</ol>
<h3>Repères internationaux</h3>
<p>Les Nations unies ont publié en 2010 une compilation de <em>bonnes pratiques</em> sur le cadre juridique et institutionnel des services de renseignement (rapport du Rapporteur spécial sur les droits de l'homme et la lutte antiterroriste). Elle recommande notamment un mandat légal public, un contrôle indépendant et des voies de recours pour les personnes concernées.</p>
<h3>En Afrique</h3>
<p>Plusieurs constitutions africaines adoptées ou révisées depuis les années 1990 encadrent explicitement les services de sécurité (par exemple l'Afrique du Sud, avec un inspecteur général du renseignement et une commission parlementaire conjointe). La tendance générale est à l'inscription des services dans la loi et à la création d'organes de contrôle.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Légitimité et efficacité vont de pair.</li><li>Le contrôle combine plusieurs niveaux complémentaires.</li><li>La loi publique fixe le mandat ; le secret porte sur les opérations, pas sur les règles.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel pays africain dispose d\'un inspecteur général du renseignement prévu par ses textes ?', ['L\'Afrique du Sud', 'Aucun', 'Seulement les pays du Maghreb', 'Tous les pays de la CEDEAO'], 0, 'L\'Afrique du Sud a institué un Inspector-General of Intelligence.'],
        ['Selon les bonnes pratiques internationales, qu\'est-ce qui doit rester public ?', ['Les opérations en cours', 'Le mandat légal des services', 'L\'identité des sources', 'Les budgets détaillés par opération'], 1, 'Le secret porte sur les opérations, pas sur les règles qui encadrent les services.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : planifier une collecte multi-sources', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Le <strong>plan de collecte</strong> associe chaque élément d'information recherché à des sources possibles, en privilégiant les moins coûteuses et les moins intrusives.</p>
<table><thead><tr><th>Élément recherché</th><th>Sources ouvertes</th><th>Sources humaines (consenties)</th><th>Imagerie</th></tr></thead><tbody>
<tr><td>Activité d'un port</td><td>Statistiques portuaires, presse économique</td><td>Échanges avec des transitaires</td><td>Images satellitaires libres (navires à quai)</td></tr>
<tr><td>Prix des denrées</td><td>Systèmes d'information sur les marchés</td><td>Commerçants interrogés</td><td>—</td></tr>
<tr><td>Inondations</td><td>Communiqués officiels, ONG</td><td>Témoignages publics</td><td>Imagerie radar (traverse les nuages)</td></tr>
</tbody></table>
<h3>Principe de parcimonie</h3>
<p>On commence par les sources ouvertes ; on ne recourt aux moyens plus coûteux que si une lacune importante subsiste. Cette règle économise des ressources et limite l'intrusion dans la vie des personnes.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Une source par élément ne suffit pas : prévoir des sources de recoupement.</li><li>Documenter pourquoi une source est choisie.</li><li>Réviser le plan à chaque cycle.</li></ul></div>
HTML]],
      'quiz' => [
        ['Que prescrit le principe de parcimonie dans un plan de collecte ?', ['Utiliser d\'abord les moyens les plus puissants', 'Commencer par les sources ouvertes et les moins intrusives', 'Collecter le plus de données possible', 'Éviter les sources ouvertes'], 1, 'On escalade seulement si une lacune importante subsiste.'],
        ['Pourquoi l\'imagerie radar est-elle utile pour suivre des inondations ?', ['Elle est gratuite', 'Elle traverse la couverture nuageuse', 'Elle identifie les personnes', 'Elle remplace les communiqués'], 1, 'Le radar fonctionne de jour comme de nuit et à travers les nuages.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : indicateurs et alerte précoce', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>L'analyse prospective s'appuie sur des <strong>indicateurs</strong> : des faits observables dont l'apparition rend un scénario plus ou moins probable. Cette méthode est au cœur des systèmes d'alerte précoce.</p>
<h3>Construire une liste d'indicateurs</h3>
<ol><li>Décrire chaque scénario en une phrase.</li><li>Lister ce qui serait <em>visible</em> si ce scénario se réalisait.</li><li>Retenir les indicateurs observables légalement et régulièrement.</li><li>Fixer des seuils (« plus de trois incidents par semaine »).</li></ol>
<h3>Exemple (fictif)</h3>
<table><thead><tr><th>Scénario</th><th>Indicateurs</th></tr></thead><tbody>
<tr><td>Hausse des tensions foncières</td><td>Plaintes déposées, conflits signalés par la presse locale, déplacements de troupeaux anormaux</td></tr>
<tr><td>Apaisement</td><td>Accords communautaires publiés, médiations annoncées, baisse des incidents</td></tr>
</tbody></table>
<div class="warning">Un indicateur n'est pas une preuve : il modifie une probabilité. Évitez de conclure sur un indicateur isolé.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les indicateurs rendent l'analyse vérifiable et révisable.</li><li>Prévoir aussi des indicateurs du scénario favorable.</li><li>Revoir régulièrement la liste.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qu\'est-ce qu\'un indicateur en analyse prospective ?', ['Une preuve définitive', 'Un fait observable qui modifie la probabilité d\'un scénario', 'Une opinion d\'expert', 'Un logiciel'], 1, 'Il rend le scénario plus ou moins probable sans le prouver.'],
        ['Pourquoi définir aussi des indicateurs du scénario favorable ?', ['Pour rassurer le décideur', 'Pour éviter le biais de confirmation vers le pire', 'Ce n\'est pas utile', 'Par obligation légale'], 1, 'Surveiller tous les scénarios limite les biais.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-A201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : le débriefing de voyage', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Le <strong>débriefing de retour de mission</strong> est la forme de HUMINT la plus simple et la plus légitime pour une organisation : on recueille, auprès de ses propres collaborateurs volontaires, ce qu'ils ont observé lors d'un déplacement professionnel.</p>
<h3>Trame de débriefing (20 minutes)</h3>
<ol><li>Contexte : lieux, dates, interlocuteurs officiels rencontrés.</li><li>Faits marquants : changements depuis la dernière mission (prix, sécurité, logistique).</li><li>Perceptions : climat des affaires, préoccupations exprimées publiquement.</li><li>Incidents : sollicitations inhabituelles, pertes de matériel, contrôles.</li><li>Recommandations pour la prochaine mission.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le débriefing est volontaire et transparent.</li><li>On ne demande jamais à un collaborateur de collecter clandestinement.</li><li>Les incidents de sécurité alimentent aussi la protection de l'organisation.</li></ul></div>
HTML]],
      'quiz' => [
        ['Le débriefing de retour de mission doit être :', ['Imposé et secret', 'Volontaire et transparent', 'Réalisé par un service étranger', 'Enregistré à l\'insu du collaborateur'], 1, 'C\'est la base d\'une pratique légitime.'],
        ['Que recueille-t-on en priorité lors d\'un débriefing ?', ['La vie privée des interlocuteurs', 'Les faits observés et les changements constatés', 'Des documents confidentiels de partenaires', 'Des rumeurs non vérifiées'], 1, 'On se concentre sur l\'observé et le vérifiable.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : vérifier une information publique', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>La vérification est l'étape qui distingue l'OSINT professionnel de la simple recherche. On applique une grille systématique inspirée du journalisme de vérification.</p>
<h3>La grille en cinq questions</h3>
<table><thead><tr><th>Question</th><th>Technique</th></tr></thead><tbody>
<tr><td>Qui l'a publié en premier ?</td><td>Recherche d'antériorité, tri chronologique</td></tr>
<tr><td>Quand ?</td><td>Métadonnées, archives web, éléments datés visibles</td></tr>
<tr><td>Où ?</td><td>Géolocalisation sur imagerie</td></tr>
<tr><td>Pourquoi maintenant ?</td><td>Contexte, intérêt de la diffusion</td></tr>
<tr><td>Est-ce cohérent ?</td><td>Recoupement avec d'autres sources indépendantes</td></tr>
</tbody></table>
<p>Les archives web publiques (comme la Wayback Machine d'Internet Archive) permettent de retrouver une version antérieure d'une page modifiée ou supprimée.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>La première publication est souvent la plus informative.</li><li>Un contenu viral n'est pas un contenu vérifié.</li><li>Conserver la trace de chaque vérification.</li></ul></div>
HTML]],
      'quiz' => [
        ['À quoi sert une archive web publique en OSINT ?', ['À pirater un site', 'À retrouver une version antérieure d\'une page', 'À masquer son identité', 'À accélérer Internet'], 1, 'Elle conserve des copies datées de pages web.'],
        ['Pourquoi chercher qui a publié une information en premier ?', ['Pour le contacter discrètement', 'Car la source originelle éclaire la fiabilité et le contexte', 'Par curiosité', 'Ce n\'est pas utile'], 1, 'Remonter à l\'origine est un réflexe fondamental.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : souveraineté numérique et câbles sous-marins', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Comprendre le SIGINT, c'est aussi comprendre l'<strong>infrastructure</strong> par laquelle circulent les communications. Pour l'Afrique, les câbles sous-marins et les points d'échange Internet sont des enjeux de souveraineté.</p>
<h3>Quelques repères</h3>
<ul><li>L'essentiel du trafic Internet intercontinental passe par des câbles sous-marins ; plusieurs grands câbles (par exemple 2Africa, Equiano, WACS, SAT-3) desservent le continent.</li><li>Les <strong>points d'échange Internet (IXP)</strong> nationaux permettent que le trafic entre deux utilisateurs d'un même pays reste dans le pays, ce qui réduit coûts et délais.</li><li>Les coupures de câbles (comme celles survenues en Afrique de l'Ouest en 2024) montrent la dépendance des économies à ces infrastructures.</li></ul>
<h3>Enjeux pour l'analyste</h3>
<p>Ces sujets relèvent de l'analyse stratégique ouverte : résilience des réseaux, dépendances technologiques, protection juridique des données. Ils ne requièrent aucune technique d'interception.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>L'infrastructure numérique est un actif stratégique.</li><li>Les IXP nationaux renforcent la résilience et la souveraineté.</li><li>L'analyse de ces enjeux se fait en sources ouvertes.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel est l\'intérêt d\'un point d\'échange Internet (IXP) national ?', ['Intercepter le trafic', 'Garder localement le trafic entre utilisateurs du même pays', 'Bloquer les réseaux sociaux', 'Remplacer les câbles sous-marins'], 1, 'Le trafic local reste local : moins coûteux, plus rapide, plus résilient.'],
        ['L\'analyse des dépendances aux câbles sous-marins relève :', ['De l\'interception', 'De l\'analyse stratégique en sources ouvertes', 'Du piratage', 'De l\'espionnage industriel'], 1, 'Ces enjeux s\'étudient avec des informations publiques.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : indices de végétation et suivi environnemental', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>Les capteurs multispectraux enregistrent la lumière réfléchie dans plusieurs longueurs d'onde. En combinant le rouge et le proche infrarouge, on calcule le <strong>NDVI</strong> (indice de végétation par différence normalisée), qui mesure la vigueur de la végétation.</p>
<p><code>NDVI = (PIR − Rouge) / (PIR + Rouge)</code> — valeur comprise entre −1 et 1 ; plus elle est élevée, plus la végétation est dense et active.</p>
<h3>Usages légitimes</h3>
<ul><li>Anticiper les crises alimentaires (sécheresse, baisse des récoltes) ;</li><li>Suivre la déforestation et les feux de brousse ;</li><li>Repérer l'extension d'exploitations minières artisanales à proximité de cours d'eau.</li></ul>
<p>Les données Sentinel-2 (Copernicus) et Landsat sont librement accessibles et suffisent pour la plupart de ces analyses.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le NDVI combine rouge et proche infrarouge.</li><li>La comparaison entre années révèle les tendances.</li><li>Ces analyses servent la sécurité alimentaire et environnementale.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelles bandes combine le NDVI ?', ['Bleu et vert', 'Rouge et proche infrarouge', 'Radar et thermique', 'Ultraviolet et rouge'], 1, 'NDVI = (PIR − Rouge) / (PIR + Rouge).'],
        ['Un NDVI élevé indique :', ['Un sol nu', 'De l\'eau', 'Une végétation dense et active', 'Une zone urbaine'], 2, 'Plus la valeur est proche de 1, plus la végétation est vigoureuse.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-B101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : le cycle de la relation avec un interlocuteur', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Une relation d'information professionnelle suit un cycle qui doit rester transparent à chaque étape.</p>
<ol><li><strong>Identification</strong> : qui détient une expertise utile (publications, fonctions publiques) ?</li><li><strong>Prise de contact</strong> : présentation honnête, finalité annoncée.</li><li><strong>Échange</strong> : entretien consenti, dans les limites fixées par l'interlocuteur.</li><li><strong>Entretien de la relation</strong> : restitution, remerciements, respect des engagements.</li><li><strong>Clôture</strong> : fin explicite si le besoin disparaît.</li></ol>
<div class="note"><strong>Réciprocité légitime.</strong> Partager une synthèse publique, inviter à une conférence ou citer une contribution (avec accord) sont des contreparties acceptables ; offrir de l'argent ou des avantages pour obtenir des informations confidentielles ne l'est pas.</div>
<div class="keypoints"><strong>Points clés</strong><ul><li>Chaque étape repose sur l'honnêteté.</li><li>La relation se gère dans la durée et se clôt proprement.</li><li>La réciprocité doit rester transparente et proportionnée.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle contrepartie est acceptable dans une relation d\'information ?', ['Une somme d\'argent contre un document interne', 'Le partage d\'une synthèse publique', 'Une promesse d\'emploi', 'Un cadeau de valeur'], 1, 'La réciprocité doit être transparente et proportionnée.'],
        ['Que faire lorsque le besoin d\'information disparaît ?', ['Continuer à solliciter', 'Clore explicitement la relation', 'Transmettre le contact à un tiers sans accord', 'Ne rien dire'], 1, 'La clôture fait partie d\'une relation respectueuse.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : consentement et protection des données dans les entretiens', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les notes d'entretien contiennent souvent des <strong>données personnelles</strong>. Les lois de protection des données s'appliquent donc à leur traitement.</p>
<h3>Bonnes pratiques</h3>
<ul><li>Informer l'interlocuteur de la finalité et de la durée de conservation ;</li><li>Recueillir son accord pour tout enregistrement audio ;</li><li>Pseudonymiser les comptes rendus ;</li><li>Limiter l'accès aux personnes qui en ont besoin ;</li><li>Supprimer les données brutes à l'échéance prévue.</li></ul>
<h3>Le cas des personnes vulnérables</h3>
<p>Victimes, mineurs, personnes déplacées : l'entretien exige un cadre renforcé (consentement éclairé, présence d'un tiers de confiance si nécessaire, absence de toute pression). En cas de doute, on s'abstient.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les notes d'entretien sont des données personnelles.</li><li>L'enregistrement suppose un accord explicite.</li><li>La vulnérabilité impose une prudence accrue.</li></ul></div>
HTML]],
      'quiz' => [
        ['Un enregistrement audio d\'entretien suppose :', ['Aucune formalité', 'L\'accord explicite de l\'interlocuteur', 'L\'autorisation d\'un concurrent', 'Un logiciel spécial'], 1, 'L\'enregistrement à l\'insu de la personne est contraire à l\'éthique et souvent illégal.'],
        ['Face à une personne vulnérable, en cas de doute sur le consentement, il faut :', ['Insister poliment', 'S\'abstenir', 'Enregistrer discrètement', 'Promettre une aide'], 1, 'La protection de la personne prime sur le besoin d\'information.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : l\'entretien cognitif', 'type' => 'text', 'minutes' => 20, 'content' => <<<'HTML'
<p>L'<strong>entretien cognitif</strong>, développé par les psychologues Ronald Fisher et Edward Geiselman dans les années 1980, vise à obtenir des témoignages plus complets et plus exacts. Il est aujourd'hui utilisé par de nombreux services d'enquête et chercheurs.</p>
<h3>Ses quatre consignes de base</h3>
<ol><li><strong>Restituer le contexte</strong> : se replacer mentalement dans la situation (lieu, moment, ambiance).</li><li><strong>Tout rapporter</strong>, même les détails qui semblent sans importance.</li><li><strong>Changer l'ordre</strong> du récit (par exemple à rebours).</li><li><strong>Changer de perspective</strong> : décrire comme l'aurait vu une autre personne présente.</li></ol>
<p>L'interviewer parle peu, laisse des silences et évite toute question suggestive. Les études montrent une augmentation sensible des informations correctes sans hausse importante des erreurs.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Méthode validée scientifiquement, non coercitive.</li><li>L'interlocuteur garde la maîtrise de son récit.</li><li>Elle s'applique aux témoignages consentis.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qui a développé l\'entretien cognitif ?', ['Sun Tzu', 'Fisher et Geiselman', 'Sherman Kent', 'Richards Heuer'], 1, 'Ronald Fisher et Edward Geiselman, dans les années 1980.'],
        ['Laquelle est une consigne de l\'entretien cognitif ?', ['Interrompre souvent', 'Changer l\'ordre du récit', 'Poser des questions suggestives', 'Exiger des réponses courtes'], 1, 'Raconter à rebours aide à retrouver des détails.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : désinformation et manipulation des sources', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Une source peut être instrumentalisée pour diffuser une information fausse : c'est la <strong>désinformation</strong> (fausse et intentionnelle), à distinguer de la <strong>mésinformation</strong> (fausse mais diffusée de bonne foi).</p>
<h3>Techniques fréquentes</h3>
<ul><li>Mélange de faits vrais et d'un élément faux (le vrai crédibilise le faux) ;</li><li>Fuites « exclusives » sollicitant une réaction rapide ;</li><li>Faux documents imitant des en-têtes officiels ;</li><li>Réseaux de comptes coordonnés simulant un consensus.</li></ul>
<h3>Défenses</h3>
<ul><li>Coter séparément chaque élément d'un même récit ;</li><li>Vérifier l'authenticité matérielle des documents (format, signatures, cohérence) ;</li><li>Demander : à qui profite la diffusion ?</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Désinformation = intention de tromper.</li><li>Le vrai sert souvent d'emballage au faux.</li><li>L'urgence imposée est un signal d'alerte.</li></ul></div>
HTML]],
      'quiz' => [
        ['Qu\'est-ce qui distingue la désinformation de la mésinformation ?', ['La langue utilisée', 'L\'intention de tromper', 'Le support (papier ou numérique)', 'La longueur du message'], 1, 'La désinformation est intentionnelle.'],
        ['Pourquoi coter séparément les éléments d\'un même récit ?', ['Pour gagner du temps', 'Parce qu\'un élément faux peut être noyé parmi des faits vrais', 'Par tradition', 'Pour protéger la source'], 1, 'Le mélange vrai/faux est une technique classique.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
'AFS-B201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : la protection juridique des sources journalistiques en Afrique', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La protection des sources est reconnue comme un élément de la liberté d'expression. La <strong>Déclaration de principes sur la liberté d'expression et l'accès à l'information en Afrique</strong>, adoptée par la Commission africaine des droits de l'homme et des peuples (révisée en 2019), prévoit que les journalistes ne doivent pas être contraints de révéler leurs sources, sauf exceptions strictement encadrées par la loi.</p>
<h3>Ce que cela implique pour un analyste</h3>
<ul><li>Respecter les engagements de confidentialité pris ;</li><li>Ne jamais exercer de pression pour identifier la source d'un journaliste ;</li><li>Connaître les exceptions prévues par la loi nationale applicable.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>La protection des sources a un fondement juridique régional.</li><li>Les exceptions doivent être prévues par la loi et proportionnées.</li><li>La confiance des sources repose sur le respect des engagements.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle instance a adopté la Déclaration de principes sur la liberté d\'expression en Afrique ?', ['L\'OMC', 'La Commission africaine des droits de l\'homme et des peuples', 'La BAD', 'L\'OAPI'], 1, 'La Commission africaine, avec une révision en 2019.'],
        ['Selon ces principes, un journaliste peut être contraint de révéler sa source :', ['Toujours', 'Jamais, sans exception', 'Seulement dans des exceptions strictement prévues par la loi', 'Sur simple demande d\'une entreprise'], 2, 'Les exceptions doivent être encadrées et proportionnées.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : les biais de l\'observateur', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Au-delà du comportement de l'interlocuteur, c'est souvent l'<strong>observateur</strong> qui se trompe. Plusieurs biais affectent la perception :</p>
<ul><li><strong>Effet de halo</strong> : une qualité perçue (aisance, élégance) colore tout le jugement ;</li><li><strong>Biais culturel</strong> : interpréter un comportement selon ses propres normes (le contact visuel direct est valorisé dans certaines cultures et jugé irrespectueux dans d'autres) ;</li><li><strong>Biais de confirmation</strong> : remarquer surtout ce qui confirme un soupçon ;</li><li><strong>Excès de confiance</strong> : les personnes qui se disent expertes en détection du mensonge ne font en moyenne pas mieux que les autres.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le non-verbal renseigne sur l'état émotionnel, pas sur la véracité.</li><li>Les normes culturelles changent l'interprétation des gestes.</li><li>La vérification factuelle reste la référence.</li></ul></div>
HTML]],
      'quiz' => [
        ['L\'effet de halo désigne :', ['Un phénomène optique', 'Le fait qu\'une qualité perçue influence tout le jugement', 'Une technique d\'entretien', 'Un niveau de classification'], 1, 'Une impression positive ou négative contamine l\'ensemble de l\'évaluation.'],
        ['Les personnes se disant expertes en détection du mensonge :', ['Sont infaillibles', 'Ne font en moyenne pas mieux que les autres', 'Réussissent à 95 %', 'Utilisent toujours un détecteur'], 1, 'Les études montrent un excès de confiance sans gain de précision.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : visualiser et synthétiser', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Un bon rapport utilise des <strong>supports visuels</strong> sobres qui servent l'argument.</p>
<table><thead><tr><th>Besoin</th><th>Support adapté</th></tr></thead><tbody>
<tr><td>Montrer une évolution</td><td>Courbe chronologique</td></tr>
<tr><td>Comparer des options</td><td>Tableau avantages / risques / coûts</td></tr>
<tr><td>Situer des faits</td><td>Carte annotée</td></tr>
<tr><td>Montrer des relations</td><td>Schéma d'acteurs (sans données personnelles inutiles)</td></tr>
</tbody></table>
<h3>Le résumé exécutif en 5 lignes</h3>
<ol><li>Le constat principal ;</li><li>la cause probable ;</li><li>la conséquence pour le lecteur ;</li><li>le niveau de confiance ;</li><li>l'action recommandée ou l'indicateur à suivre.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un visuel = un message.</li><li>Toujours sourcer les données d'un graphique.</li><li>Le résumé exécutif doit pouvoir être lu seul.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quel support est le plus adapté pour comparer des options ?', ['Une carte', 'Un tableau avantages / risques / coûts', 'Une photographie', 'Un poème'], 1, 'Le tableau rend la comparaison immédiate.'],
        ['Que doit permettre un résumé exécutif ?', ['Être lu seul et suffire à l\'essentiel', 'Remplacer les sources', 'Détailler la méthodologie', 'Présenter l\'auteur'], 0, 'Le décideur doit pouvoir s\'arrêter au résumé.'],
      ],
    ],
  ],
  'atelier' => 'glossary_biblio',
],
];
