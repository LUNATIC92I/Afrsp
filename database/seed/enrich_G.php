<?php
/** Enrichissements pédagogiques : module G (renseignement militaire). */
return [
'AFS-G101' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : le dialogue entre le chef et l\'officier renseignement', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La qualité du renseignement dépend d'abord de la qualité du <strong>dialogue</strong> entre le chef et son officier renseignement. Un besoin mal exprimé produit un renseignement inutile.</p>
<h3>Formuler un bon besoin</h3>
<table><thead><tr><th>Besoin mal formulé</th><th>Besoin bien formulé</th></tr></thead><tbody>
<tr><td>« Dites-moi tout sur la région. »</td><td>« D'ici le 15, quels axes resteront praticables pour le ravitaillement des villages isolés pendant les pluies ? »</td></tr>
<tr><td>« Y a-t-il une menace ? »</td><td>« Le marché de jeudi risque-t-il d'être pris pour cible ? Quels signes nous alerteraient ? »</td></tr>
</tbody></table>
<h3>La question « et alors ? »</h3>
<p>Chaque fait présenté au chef doit répondre à la question « et alors ? » : quelle conséquence pour sa décision ? Un briefing qui empile des faits sans conséquence fait perdre du temps et masque l'essentiel.</p>
<h3>Le droit de dire « je ne sais pas »</h3>
<p>L'officier renseignement doit pouvoir dire qu'il ignore, et proposer comment combler la lacune. Un chef qui sanctionne l'incertitude obtiendra des certitudes fausses.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un besoin précis, lié à une décision et à une date.</li><li>Chaque fait doit répondre à « et alors ? ».</li><li>Reconnaître une lacune est un gage de sérieux.</li></ul></div>
HTML]],
      'quiz' => [
        ['Un bon besoin en renseignement est :', ['Le plus large possible', 'Précis, lié à une décision et à une échéance', 'Secret pour l\'officier renseignement', 'Formulé par la presse'], 1, 'La précision oriente utilement la recherche.'],
        ['La question « et alors ? » sert à :', ['Contester le chef', 'Relier chaque fait à sa conséquence pour la décision', 'Allonger le briefing', 'Éviter les conclusions'], 1, 'Un fait sans conséquence pour la décision n\'a pas sa place dans le briefing.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : la grille ASCOPE pour la dimension civile', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>La grille <strong>ASCOPE</strong> complète PMESII pour décrire la dimension civile d'une zone, au plus près du terrain.</p>
<table><thead><tr><th>Élément</th><th>Exemples (zone fictive)</th></tr></thead><tbody>
<tr><td>Zones</td><td>Terres de pâturage, zones agricoles, quartiers, lieux sacrés</td></tr>
<tr><td>Structures</td><td>Écoles, centres de santé, ponts, forages, marchés</td></tr>
<tr><td>Capacités</td><td>Services publics disponibles : eau, électricité, santé, justice</td></tr>
<tr><td>Organisations</td><td>Autorités coutumières, associations, ONG, coopératives</td></tr>
<tr><td>Populations</td><td>Groupes, déplacés, leaders d'opinion, besoins prioritaires</td></tr>
<tr><td>Événements</td><td>Fêtes, marchés, élections, saisons agricoles, anniversaires</td></tr>
</tbody></table>
<p>En croisant PMESII (le « quoi ») et ASCOPE (le « où, qui, quand »), on obtient une image fine de la zone, utile autant pour la protection des populations que pour la sécurité des forces.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>ASCOPE décrit la dimension civile au niveau local.</li><li>Les événements et les structures sont souvent les points les plus sensibles.</li><li>L'analyse sert d'abord à protéger.</li></ul></div>
HTML]],
      'quiz' => [
        ['Dans la grille ASCOPE, un marché hebdomadaire relève notamment des :', ['Capacités militaires', 'Structures et événements', 'Organisations politiques uniquement', 'Équipements'], 1, 'C\'est une structure et un événement récurrent, souvent sensible.'],
        ['Croiser PMESII et ASCOPE permet :', ['De remplacer le terrain', 'D\'obtenir une image fine de la zone et de sa dimension civile', 'D\'éviter toute analyse', 'De classer les militaires'], 1, 'Les deux grilles se complètent.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : construire une liste d\'indicateurs d\'alerte', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Un <strong>indicateur</strong> est un fait observable qui rend une hypothèse plus ou moins probable. Une bonne liste d'indicateurs transforme une inquiétude vague en surveillance méthodique.</p>
<h3>Méthode</h3>
<ol><li>Partir de l'hypothèse à surveiller (par exemple : escalade des violences intercommunautaires dans une vallée fictive).</li><li>Se demander : si cette hypothèse se réalisait, que verrait-on avant ?</li><li>Retenir des indicateurs observables légalement, précis et datables.</li><li>Prévoir aussi des indicateurs contraires, qui signalent un apaisement.</li><li>Fixer un rythme de revue et un seuil d'alerte.</li></ol>
<h3>Exemple fictif</h3>
<table><thead><tr><th>Indicateur d'escalade</th><th>Indicateur d'apaisement</th></tr></thead><tbody>
<tr><td>Discours de haine diffusés sur les radios locales</td><td>Rencontre publique entre autorités coutumières</td></tr>
<tr><td>Départ de familles et de commerçants</td><td>Réouverture d'un marché commun</td></tr>
<tr><td>Hausse brutale du prix du carburant et des vivres</td><td>Accord sur les couloirs de transhumance</td></tr>
</tbody></table>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un indicateur est observable, précis et légal.</li><li>Suivre aussi les signes d'apaisement.</li><li>Revoir la liste à intervalles réguliers.</li></ul></div>
HTML]],
      'quiz' => [
        ['Un indicateur est :', ['Une certitude absolue', 'Un fait observable qui rend une hypothèse plus ou moins probable', 'Un nom de code', 'Une opinion du chef'], 1, 'Il fait évoluer la probabilité d\'une hypothèse.'],
        ['Pourquoi suivre aussi des indicateurs d\'apaisement ?', ['Pour rassurer à tout prix', 'Pour éviter de ne voir que ce qui confirme la menace', 'Ils sont inutiles', 'Pour remplacer les autres'], 1, 'Cela limite le biais de confirmation.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : réseaux sociaux et sécurité des opérations', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les téléphones des personnels sont devenus la principale vulnérabilité des forces armées. En 2018, la publication d'une carte mondiale de l'activité d'une application sportive a ainsi révélé, par les trajets de joggeurs, l'emplacement et l'organisation de sites militaires dans plusieurs pays.</p>
<h3>Les fuites les plus fréquentes</h3>
<ul><li>Photos géolocalisées ou montrant des installations, des véhicules, des écrans ;</li><li>Publications annonçant un départ, une date de retour, un lieu de déploiement ;</li><li>Applications de sport ou de rencontre qui partagent la position ;</li><li>Groupes de messagerie non sécurisés pour des échanges de service.</li></ul>
<h3>Mesures simples</h3>
<ol><li>Désactiver la géolocalisation et vérifier les métadonnées avant toute publication ;</li><li>Fixer des règles claires d'usage des téléphones en zone sensible ;</li><li>Utiliser uniquement les moyens de communication autorisés pour le service ;</li><li>Sensibiliser aussi les familles, souvent sources involontaires d'information.</li></ol>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un selfie peut révéler une position.</li><li>Les règles doivent être simples et expliquées.</li><li>La sensibilisation concerne aussi l'entourage.</li></ul></div>
HTML]],
      'quiz' => [
        ['Quelle est aujourd\'hui une source majeure de fuites d\'information militaire ?', ['Les archives papier', 'Les téléphones personnels et les réseaux sociaux', 'Les cartes routières', 'Les uniformes'], 1, 'Photos, géolocalisation et applications sont des vulnérabilités majeures.'],
        ['Avant de publier une photo prise en mission, il faut notamment :', ['Ajouter la date et le lieu', 'Désactiver la géolocalisation et vérifier ce que montre l\'image', 'La partager dans tous les groupes', 'L\'envoyer à la presse'], 1, 'Métadonnées et arrière-plans révèlent beaucoup.'],
      ],
    ],
  ],
],
'AFS-G201' => [
  'chapters' => [
    0 => [
      'lessons' => [['title' => 'Approfondissement : les structures intégrées d\'une mission', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Les missions de l'ONU se sont dotées de structures qui rassemblent les informations de toutes les composantes.</p>
<table><thead><tr><th>Structure</th><th>Rôle</th></tr></thead><tbody>
<tr><td>Centre d'analyse conjoint de la mission (JMAC)</td><td>Analyse intégrée à moyen et long terme pour la direction de la mission : tendances, acteurs, scénarios</td></tr>
<tr><td>Centre d'opérations conjoint (JOC)</td><td>Suivi de la situation au quotidien, rapports d'incidents, coordination des réponses</td></tr>
<tr><td>Composante militaire (cellule renseignement)</td><td>Appréciation de la situation sécuritaire au profit de la force</td></tr>
<tr><td>Police, droits humains, affaires civiles</td><td>Informations sur la criminalité, les violations, les dynamiques locales</td></tr>
</tbody></table>
<p>L'enjeu est la <strong>fusion</strong> : une analyse militaire qui ignore les rapports des droits humains ou des affaires civiles passe à côté de signaux essentiels, et inversement.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Le JMAC analyse, le JOC suit la situation au quotidien.</li><li>Toutes les composantes contribuent.</li><li>La fusion des sources améliore la protection.</li></ul></div>
HTML]],
      'quiz' => [
        ['Le centre d\'analyse conjoint d\'une mission (JMAC) produit principalement :', ['Les ordres d\'opération', 'Une analyse intégrée à moyen et long terme pour la direction', 'Les fiches de paie', 'Les communiqués de presse'], 1, 'Il éclaire la direction de la mission sur les tendances et les scénarios.'],
        ['Pourquoi fusionner les informations de toutes les composantes ?', ['Pour réduire les effectifs', 'Parce que chaque composante détient des signaux que les autres n\'ont pas', 'Par obligation comptable', 'Ce n\'est pas souhaitable'], 1, 'La fusion produit une image plus complète.'],
      ],
    ],
    1 => [
      'lessons' => [['title' => 'Approfondissement : les mécanismes d\'alerte communautaire', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Des missions de paix ont mis en place des <strong>réseaux d'alerte communautaire</strong> : des habitants désignés par leur communauté peuvent signaler une menace à un point de contact, par téléphone ou par radio, afin qu'une réponse soit organisée.</p>
<h3>Bénéfices</h3>
<ul><li>Alerte plus rapide, au plus près des populations ;</li><li>Meilleure compréhension des dynamiques locales ;</li><li>Renforcement du lien de confiance avec la mission.</li></ul>
<h3>Risques à maîtriser</h3>
<ul><li>Exposition des personnes qui alertent : discrétion, protection des identités, plan de protection ;</li><li>Fausses alertes ou manipulations : vérification et recoupement ;</li><li>Attentes déçues : si aucune réponse ne suit, la confiance disparaît.</li></ul>
<div class="keypoints"><strong>Points clés</strong><ul><li>Un réseau d'alerte n'a de sens que si une réponse est possible.</li><li>La protection des personnes est la priorité.</li><li>Toute alerte est vérifiée avant action.</li></ul></div>
HTML]],
      'quiz' => [
        ['Un réseau d\'alerte communautaire perd sa raison d\'être si :', ['Il est rapide', 'Aucune réponse ne suit les alertes', 'Il implique les habitants', 'Il utilise la radio'], 1, 'Sans réponse, la confiance et l\'utilité disparaissent.'],
        ['Face à une alerte communautaire, il faut :', ['Agir sans vérifier', 'La vérifier et la recouper avant d\'agir', 'L\'ignorer', 'La publier'], 1, 'Les fausses alertes et manipulations existent.'],
      ],
    ],
    2 => [
      'lessons' => [['title' => 'Approfondissement : marquer et diffuser dans une coalition', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Dans une coalition, chaque document doit indiquer clairement <strong>qui peut le lire</strong> et <strong>ce que l'on peut en faire</strong>.</p>
<h3>Les mentions indispensables</h3>
<table><thead><tr><th>Mention</th><th>Rôle</th></tr></thead><tbody>
<tr><td>Niveau de classification</td><td>Sensibilité de l'information selon les règles de l'émetteur</td></tr>
<tr><td>Destinataires autorisés</td><td>Liste des partenaires ou des structures qui peuvent la recevoir</td></tr>
<tr><td>Restrictions de rediffusion</td><td>Interdiction de transmettre sans accord de l'émetteur</td></tr>
<tr><td>Finalité autorisée</td><td>Usage limité (protection des civils, sûreté du personnel, etc.)</td></tr>
<tr><td>Date et durée de validité</td><td>Une information périmée ne doit pas circuler comme actuelle</td></tr>
</tbody></table>
<p>Une version <strong>assainie</strong>, de niveau plus bas, peut être préparée pour une diffusion plus large.</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Marquer clairement qui peut lire et ce que l'on peut faire.</li><li>Prévoir des versions assainies.</li><li>Dater toute information partagée.</li></ul></div>
HTML]],
      'quiz' => [
        ['Pourquoi dater une information partagée ?', ['Pour l\'archivage seulement', 'Pour éviter qu\'une information périmée circule comme actuelle', 'C\'est facultatif', 'Pour la traduire'], 1, 'La validité dans le temps fait partie de la qualité.'],
        ['Une version assainie d\'un document permet :', ['De cacher l\'information à tous', 'Une diffusion plus large sans révéler sources ni méthodes', 'De supprimer la classification', 'D\'éviter tout partage'], 1, 'Elle concilie partage et protection.'],
      ],
    ],
    3 => [
      'lessons' => [['title' => 'Approfondissement : les lignes rouges du renseignement militaire', 'type' => 'text', 'minutes' => 15, 'content' => <<<'HTML'
<p>Certaines limites ne souffrent <strong>aucune exception</strong>, quelles que soient l'urgence, la pression ou la menace.</p>
<ul><li>Aucune torture ni aucun traitement cruel, inhumain ou dégradant ;</li><li>Aucune surveillance ou collecte hors du cadre légal et des autorisations ;</li><li>Aucune transmission d'information à un partenaire s'il existe un risque sérieux de violations graves ;</li><li>Aucun ciblage de personnes sur la base de leur appartenance ethnique, religieuse ou politique ;</li><li>Aucune manipulation des populations civiles ni mise en danger des sources.</li></ul>
<h3>Que faire face à un ordre manifestement illégal ?</h3>
<p>Le droit international et la plupart des droits nationaux reconnaissent qu'un ordre manifestement illégal ne doit pas être exécuté. Le professionnel signale sa préoccupation à sa hiérarchie, consigne les faits et utilise les voies de recours prévues (inspection, conseiller juridique).</p>
<div class="keypoints"><strong>Points clés</strong><ul><li>Les lignes rouges sont absolues.</li><li>Un ordre manifestement illégal ne s'exécute pas.</li><li>Consigner et signaler par les voies prévues.</li></ul></div>
HTML]],
      'quiz' => [
        ['Face à un ordre manifestement illégal, le professionnel doit :', ['L\'exécuter sans discuter', 'Ne pas l\'exécuter, signaler et consigner les faits', 'En parler sur les réseaux sociaux', 'Démissionner immédiatement sans rien dire'], 1, 'Les voies de recours prévues doivent être utilisées.'],
        ['Cibler des personnes en raison de leur appartenance ethnique est :', ['Une méthode efficace', 'Une ligne rouge absolue', 'Autorisé en cas d\'urgence', 'Une pratique recommandée'], 1, 'C\'est discriminatoire, illégal et contre-productif.'],
      ],
    ],
  ],
],
];
