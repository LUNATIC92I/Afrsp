# AFRICA'SPY — Intelligence Academy

Plateforme web complète de formation aux métiers du renseignement, du contre-espionnage et de l'intelligence stratégique : site institutionnel immersif, catalogue dynamique, espace apprenant, moteur d'examens, certificats PDF vérifiables par QR Code et console d'administration.

> **Positionnement éthique.** Tous les contenus enseignent des pratiques **légales et défensives**. Aucune technique d'intrusion, d'interception ou de surveillance clandestine n'est enseignée ; les exercices reposent sur des données synthétiques et des scénarios fictifs. AFRICA'SPY ne revendique aucune accréditation officielle : ses certificats attestent un parcours de formation privé et ne sont pas des diplômes d'État.

---

## 1. Fonctionnalités

| Domaine | Contenu |
|---|---|
| **Site public** | Accueil cinématographique (séquence d'ouverture, particules Canvas, carte holographique de l'Afrique, centre de commandement interactif, radar, cartes holographiques, chiffres clés réels), Institution, Académie (filtres + recherche AJAX), fiches formations, galerie des outils (module E), vérification des certificats, contact (AJAX), mentions légales |
| **Catalogue initial** | 6 domaines (modules A à F), **11 formations**, 64 chapitres, **183 leçons** rédigées (leçons, approfondissements avec points clés, briefings vidéo, supports PDF, simulations guidées notées, exercices, études de cas, fiches méthode, glossaires avec fiches de révision interactives, bibliographies), **232 questions QCM** avec explications ; examens finaux de 15 questions en 35 minutes |
| **Espace apprenant** | Inscription, connexion, réinitialisation du mot de passe, profil, tableau de bord « terminal » (statistiques, activité, prochaines étapes, notifications), inscription aux formations, lecteur de cours immersif, progression, QCM de chapitre corrigés, téléchargements protégés, examens chronométrés, historique, certificats |
| **Certification** | Banque de questions, tirage aléatoire, correction automatique, seuil et nombre de tentatives configurables, résultat immédiat, certificat PDF (logo, nom, formation, date, numéro unique, QR Code, signature institutionnelle **fictive explicitement signalée**), vérification publique par numéro ou QR Code, signature HMAC anti-falsification, révocation |
| **Travaux & notes** | Dépôt des réponses aux simulations, exercices et études de cas (versions successives), correction par un formateur (note /20, commentaire ou demande de reprise, notification e-mail), page « Mes travaux » avec moyenne ; notes personnelles par leçon avec sauvegarde automatique |
| **Administration** | Tableau de bord (statistiques d'inscription et de réussite), utilisateurs & rôles, formations, catégories, chapitres, leçons & médias, examens & QCM, résultats (réouverture de tentatives), certificats (révocation, régénération), médiathèque, annonces, messages, paramètres institutionnels, journal d'audit |
| **Paiement** | Accès payant par formation (prix de référence **900 000 FCFA**, prix promotionnel **170 000 FCFA**, modifiables par formation), caisse en 3 étapes, Mobile Money / carte via **CinetPay** (statut toujours revérifié auprès de l'API), virement / dépôt validé par l'administration, reçus PDF, historique « Mes paiements », console *Paiements* (chiffre d'affaires, validation, annulation, remboursement avec retrait d'accès), compte à rebours optionnel de fin d'offre |
| **Rôles** | `admin` (tout), `instructor` / Responsable pédagogique (contenus, examens, médias, annonces, statistiques), `learner` — permissions fines en base (`roles`, `permissions`, `role_permissions`) |

## 2. Technologies

- **PHP 8.1+** (testé en 8.3), **MySQL 8 / MariaDB 10.4+** (testé MariaDB 10.11), PDO + requêtes préparées
- **HTML5, CSS3, JavaScript natif** (Canvas API, IntersectionObserver, requestAnimationFrame), **AJAX** (fetch)
- Bootstrap n'est **pas** utilisé : design system entièrement original (`assets/css`)
- Dépendances Composer (incluses dans `vendor/`, aucune commande nécessaire sur l'hébergement) :
  - `dompdf/dompdf` — génération des certificats et supports PDF
  - `chillerlan/php-qrcode` — QR Codes de vérification
  - `phpmailer/phpmailer` — envoi d'e-mails SMTP (optionnel)
- Polices auto-hébergées (Orbitron, Rajdhani, Inter, Share Tech Mono — licence SIL OFL)

## 3. Arborescence

```
africaspy/
├── index.php, about.php, academy.php, course.php, tools.php, contact.php
├── verify-certificate.php, login.php, register.php, logout.php
├── forgot-password.php, reset-password.php, enroll.php, download.php, legal.php
├── config/          app.php, database.php, config.local.php (généré, hors dépôt)   [accès HTTP interdit]
├── includes/        bootstrap, header/footer/navigation, auth, functions, catalog,
│                    learning (examens/certificats), pdf, logo, content, admin…      [accès HTTP interdit]
├── assets/          css/ (style, animations, dashboard) · js/ (boot, main, interactions, dashboard)
│                    images/ (visuels originaux, couvertures, équipements) · fonts/
├── learner/         dashboard, courses, learning, exams, certificates, certificate, profile
├── admin/           dashboard, users, courses, categories, chapters, lessons, exams, results,
│                    certificates, media, announcements, messages, settings, audit
├── ajax/            courses (catalogue), progress, quiz, announcement
├── certificates/generated/   PDF des certificats                                   [accès HTTP interdit]
├── uploads/         courses/ (images publiques) · documents/ · videos/ (servis via download.php)
├── database/        schema.sql · africaspy.sql (complet) · build-sql.php · seed/ (contenus)
├── install/         assistant web d'installation + cli.php
├── resources/       polices PDF, outils de génération des visuels, routeur de dev        [accès HTTP interdit]
├── storage/         logs, cache, polices compilées                                        [accès HTTP interdit]
├── tests/           tests E2E Playwright (parcours apprenant, sécurité + administration)
└── vendor/          dépendances Composer                                                  [accès HTTP interdit]
```

## 4. Installation

### Prérequis
PHP ≥ 8.1 avec les extensions `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `dom`, `intl` ; une base MySQL/MariaDB vide en `utf8mb4_unicode_ci` ; droits d'écriture sur `config/`, `storage/*`, `certificates/generated/`, `uploads/*`.

### Option A — Assistant web (hébergement mutualisé)
1. Téléversez tout le dossier à la racine web (ou dans un sous-dossier : il est détecté automatiquement).
2. Créez la base de données et son utilisateur depuis le panneau de l'hébergeur.
3. Ouvrez `https://votre-domaine/install/` : l'assistant vérifie les prérequis, importe `database/africaspy.sql`, puis vous demande de **définir vous-même l'identifiant et le mot de passe administrateur** (10 caractères minimum, lettres + chiffres).
4. L'assistant écrit `config/config.local.php` (clé applicative aléatoire de 256 bits) et se **verrouille définitivement** (`install/install.lock`).

### Option B — Ligne de commande
```bash
mysql -e "CREATE DATABASE africaspy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php install/cli.php --db-host=127.0.0.1 --db-name=africaspy --db-user=USER --db-pass='***' \
    --base-url=https://academy.example.org --admin-email=admin@example.org \
    --admin-first=Prénom --admin-last=Nom
# Le mot de passe administrateur est saisi de façon masquée
# (ou fourni via la variable d'environnement AFRICASPY_ADMIN_PASSWORD).
```

### Identifiants initiaux de l'administrateur
**Aucun identifiant n'est livré dans le code ni dans la base.** Le compte administrateur est créé exclusivement lors de l'installation, avec l'e-mail et le mot de passe que vous choisissez ; le mot de passe est stocké uniquement sous forme de hachage (`password_hash`). Pour créer d'autres comptes (responsables pédagogiques, apprenants) : *Administration › Utilisateurs*.

### Importer la base manuellement
`database/africaspy.sql` crée toutes les tables (clés primaires/étrangères, index, contraintes) et le catalogue. Il peut être importé via phpMyAdmin ; créez ensuite l'administrateur avec l'assistant ou `install/cli.php`.

## 5. Configuration

`config/config.local.php` (généré, jamais versionné, accès HTTP bloqué) — exemple :

```php
<?php return [
  'env'      => 'production',             // 'development' affiche les erreurs
  'base_url' => 'https://academy.example.org',
  'app_key'  => '…64 caractères hexadécimaux…',  // signe les certificats : ne jamais changer après mise en service
  'db'   => ['host' => '127.0.0.1', 'port' => 3306, 'name' => 'africaspy', 'user' => '…', 'pass' => '…'],
  'mail' => ['transport' => 'smtp', 'from' => 'no-reply@example.org', 'smtp_host' => 'smtp.example.org',
             'smtp_port' => 587, 'smtp_user' => '…', 'smtp_pass' => '…', 'smtp_secure' => 'tls'],
];
```

- **Paiement** : ajoutez dans `config/config.local.php` :
  ```php
  'payments' => [
      'cinetpay_enabled' => true,
      'cinetpay_apikey'  => '…',      // tableau de bord CinetPay › Intégrations
      'cinetpay_site_id' => '…',
      'cinetpay_secret'  => '…',      // facultatif : vérifie la signature x-token des notifications
      'sandbox'          => false,    // true uniquement en développement (ignoré si env = production)
  ],
  ```
  L'URL de notification à déclarer chez CinetPay est `https://votre-domaine/payment-notify.php`. Sans clés CinetPay, seul le virement / dépôt est proposé (instructions et coordonnées à saisir dans *Administration › Paramètres*). Le prix de chaque formation se règle dans *Administration › Formations*.
- **Enrichissement des contenus sur une base existante** : `php database/enrich-apply.php` ajoute les leçons d'approfondissement, le chapitre « Mise en situation » de chaque formation, les questions QCM supplémentaires, les glossaires et les bibliographies, et porte les examens à 15 questions / 35 min s'ils ont gardé leurs réglages d'origine (idempotent : relançable sans doublon) et recalcule la progression des apprenants.
- **Travaux et notes (installation existante)** : `mysql africaspy < database/migrations/2026_11_13_submissions_notes.sql` (tables `submissions`, `lesson_notes`, permission `submissions.review` accordée aux administrateurs et responsables pédagogiques).
- **Sécurité (installation existante)** : `mysql africaspy < database/migrations/2026_11_14_security.sql` (limitation de débit, colonnes 2FA). Derrière un proxy/CDN, ajoutez `'trusted_proxies' => ['IP du proxy']` dans `config/config.local.php`.
- **Mise à jour d'une installation existante** : `mysql africaspy < database/migrations/2026_11_13_payments.sql` (ajoute les prix, la table `payments` et la permission `payments.manage` ; les inscriptions existantes conservent leur accès).
- **E-mails** : par défaut (`transport = log`), les e-mails (liens de réinitialisation) sont écrits dans `storage/logs/mail.log`. Configurez `smtp` ou `mail` en production.
- Les variables d'environnement `AFRICASPY_DB_HOST`, `AFRICASPY_DB_NAME`, `AFRICASPY_DB_USER`, `AFRICASPY_DB_PASS`, `AFRICASPY_APP_KEY`, `AFRICASPY_BASE_URL` peuvent remplacer le fichier.
- Paramètres institutionnels (coordonnées, signataire des certificats, ouverture des inscriptions) : *Administration › Paramètres*.

## 6. Déploiement

### Apache (mutualisé ou dédié)
Les fichiers `.htaccess` fournis bloquent l'accès direct à `config/`, `includes/`, `database/`, `vendor/`, `storage/`, `resources/`, `certificates/`, `uploads/documents/`, `uploads/videos/` et désactivent toute exécution de script dans `uploads/`. Nécessite `AllowOverride All` et `mod_rewrite`. Activez HTTPS puis décommentez la redirection forcée dans `.htaccess`.

### Nginx + PHP-FPM
```nginx
server {
    listen 443 ssl http2;
    server_name academy.example.org;
    root /var/www/africaspy;
    index index.php;
    client_max_body_size 210M;

    location ~ /\.                                   { deny all; }
    location ~ ^/(config|includes|database|vendor|storage|resources|certificates)/ { deny all; }
    location ~ ^/uploads/(documents|videos)/         { deny all; }
    location ~ ^/install/(installer\.php|cli\.php|install\.lock)$ { deny all; }
    location ~ ^/uploads/.*\.(php|phtml|phar)$       { deny all; }
    location ~ ^/(composer\.(json|lock)|README\.md)$ { deny all; }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~* \.(css|js|woff2|webp|svg|png|jpg)$ { expires 30d; }
}
```

### Checklist de mise en production
- [ ] HTTPS actif (le cookie de session reçoit alors l'attribut `Secure` et HSTS est envoyé)
- [ ] `env` = `production`, `base_url` renseignée (utilisée dans les QR Codes)
- [ ] Transport e-mail SMTP configuré
- [ ] `upload_max_filesize` / `post_max_size` ≥ 200M si vous hébergez des vidéos
- [ ] Sauvegardes régulières de la base **et** de `uploads/` et `certificates/generated/`
- [ ] Coordonnées et signataire mis à jour dans *Administration › Paramètres*

## 7. Sécurité mise en œuvre

> Rapport d'audit complet, constats corrigés et recommandations d'exploitation : **[SECURITY.md](SECURITY.md)**.
> Nouveautés : double authentification TOTP (obligatoire au choix pour le personnel), Argon2id, verrouillage ciblé compte + IP, limitation de débit généralisée, réencodage des images, `security.txt`.

- **Injections SQL** : PDO, requêtes préparées natives (`ATTR_EMULATE_PREPARES = false`) partout ; noms de colonnes jamais issus de l'entrée utilisateur
- **XSS** : échappement systématique (`e()`), nettoyage HTML par liste blanche (`sanitize_html`) pour les contenus de cours, CSP stricte sans script inline (`script-src 'self'`)
- **CSRF** : jeton par session sur tous les formulaires et requêtes AJAX (`X-CSRF-Token`) ; déconnexion en POST uniquement
- **Mots de passe** : `password_hash` / `password_verify`, rehachage automatique, politique minimale, vérification à temps constant même pour un compte inexistant
- **Sessions** : cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS, mode strict, régénération à la connexion et toutes les 15 min, expiration après 2 h d'inactivité, invalidation de toutes les sessions au changement de mot de passe
- **Force brute** : 5 échecs par compte / 20 par IP sur 15 min ; limitation des demandes de réinitialisation, des inscriptions et des messages de contact ; jetons de réinitialisation à usage unique (hachés, 60 min)
- **Contrôle d'accès** : RBAC par permissions, vérification de propriété sur chaque ressource (tentatives, certificats, médias — anti-IDOR), protection du dernier administrateur
- **Téléversements** : vérification du type MIME réel (`finfo`), extension imposée par le type détecté, taille limitée, nom aléatoire, documents et vidéos hors de portée HTTP (servis par `download.php` après contrôle d'inscription, avec prise en charge des requêtes partielles)
- **Certificats** : signature HMAC-SHA256 (clé applicative) recalculée à chaque vérification — toute altération en base est détectée
- **Examens** : questions tirées côté serveur, bonnes réponses jamais envoyées au navigateur, verrou transactionnel contre la double soumission, délai de grâce contrôlé côté serveur
- **Journalisation** : `audit_logs` (actions administratives, connexions, examens, accès refusés)
- En-têtes : `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, HSTS

## 8. Tests

Deux suites de bout en bout (Playwright) sont fournies dans `tests/` :

```bash
# Serveur local (le routeur reproduit les règles .htaccess)
php -S 127.0.0.1:8080 resources/dev-router.php

npm i -D playwright && npx playwright install chromium   # une fois
node tests/learner.e2e.js     # inscription → formation → leçons → QCM → examen → certificat PDF → vérification
ADMIN_EMAIL=… ADMIN_PASSWORD=… node tests/security.e2e.js
ADMIN_EMAIL=… ADMIN_PASSWORD=… node tests/hardening.e2e.js     # 26 contrôles : verrouillage, débit, Host, Argon2id, 2FA, images
ADMIN_EMAIL=… ADMIN_PASSWORD=… node tests/submissions.e2e.js   # 19 contrôles : notes, dépôt, reprise, correction, sécurité
# 44 contrôles (dont 9 sur le paiement) : CSRF, force brute, contrôle d'accès, IDOR, injection SQL, XSS, en-têtes,
# cookie de session, téléversement frauduleux, parcours admin complet, révocation et
# falsification de certificat, journal d'audit
```
Les tests interrogent la base locale avec le client `mysql` (base `africaspy`) ; ils sont destinés à un environnement de développement, pas à la production.

### Captures d'écran
26 captures haute résolution sont disponibles dans `docs/screenshots/` (accueil, offre, centre de commandement, carte, catalogue, fiche formation, caisse, accès accordé, lecteur de cours, QCM, examen, résultat, tableaux de bord, certificat, reçu, administration, mobile). Pour les régénérer : `node tests/screenshots.js` (serveur local lancé).

## 9. Contenus, visuels et régénération

- **Contenus pédagogiques** : `database/seed/courses_*.php` (leçons de base), `enrich_*.php` (approfondissements, QCM supplémentaires, `enrich_scenarios.php` pour les mises en situation) et `glossaries.php` (glossaires, bibliographies), au format PHP lisible. Après modification : `php database/build-sql.php` régénère `database/africaspy.sql` et les supports PDF `uploads/documents/seed-*.pdf`. En production, éditez plutôt les contenus depuis l'administration.
- **Visuels** : toutes les images (salle de commandement, salle d'analyse, table géopolitique, laboratoire numérique, couvertures A–F, illustrations d'équipements, emblème et favicon) sont des **créations originales** rendues de manière procédurale à partir des tracés cartographiques Natural Earth (domaine public) — aucune licence tierce à attribuer. Régénération : `python3 resources/tools/generate_visuals.py` (Pillow, numpy).
- **Vidéos** : briefings pédagogiques (MP4, sans narration) générés par `python3 resources/tools/generate_videos.py <catalog.json>` (Pillow + ffmpeg).
- Pour utiliser des **photographies réelles**, téléversez-les depuis *Administration › Médiathèque* ou dans la fiche d'une formation, en renseignant le champ « Crédit / licence » qui conserve l'attribution.
- Les noms d'outils logiciels (Maltego, Shodan, Wireshark…) sont des marques de leurs détenteurs, cités à titre pédagogique sans affiliation ; aucun logo tiers n'est reproduit.

## 10. Accessibilité et performances

- Animations : séquence d'ouverture, particules, carte holographique, radar, cartes holographiques, transitions de pages, texte « déchiffré », boutons magnétiques, satellites en orbite, bandeau défilant, tracé SVG progressif, bordure conique animée de l'offre, barre de progression de lecture, projecteur suivant le pointeur, effet « Access granted » après paiement
- Respect de `prefers-reduced-motion` (séquence d'ouverture, parallaxe, particules, transitions désactivées), navigation clavier (onglets ARIA du centre de commandement, modales avec focus piégé, zones de carte activables au clavier), lien d'évitement, contrastes élevés, curseur d'appoint qui ne remplace jamais le curseur natif
- Animations limitées à `transform` / `opacity`, Canvas suspendu hors écran et onglet masqué, particules réduites sur mobile, images WebP en chargement différé, polices `woff2` préchargées, aucune bibliothèque front-end
