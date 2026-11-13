# Sécurité : AFRICA'SPY Intelligence Academy

Audit réalisé le 13 novembre 2026 sur l'ensemble du code applicatif (PHP, JavaScript, configuration serveur, schéma SQL).

**Méthode**
- Relecture systématique des sorties HTML, des requêtes SQL, des redirections, des téléversements et des contrôles d'accès.
- Audit des dépendances avec `composer audit`.
- Tests d'attaque automatisés : 179 contrôles répartis en 6 suites Playwright (voir plus bas).

Pour signaler une vulnérabilité, consultez `/.well-known/security.txt` (RFC 9116) ou la section « Signalement de vulnérabilités » des mentions légales.

---

## 1. Constats de l'audit et corrections

| # | Constat | Gravité | Statut |
|---|---|---|---|
| 1 | **Verrouillage de compte abusif** : 5 échecs de connexion depuis *n'importe quelle* IP bloquaient le compte. Un attaquant pouvait donc bloquer un administrateur à volonté. | Élevée | Corrigé : blocage par couple compte + IP (5), par IP (20), par compte toutes IP confondues (50) |
| 2 | **Empoisonnement des liens envoyés par e-mail** : si `base_url` était vide, le lien de réinitialisation reprenait l'en-tête `Host` de la requête, et le jeton pouvait fuiter vers un domaine tiers. | Élevée (conditionnelle) | Corrigé : `base_url` obligatoire à l'installation ; en production, l'en-tête `Host` n'est jamais utilisé |
| 3 | **Absence de second facteur** pour les comptes du personnel | Moyenne | Corrigé : 2FA TOTP (RFC 6238), codes de secours, option « 2FA obligatoire pour le personnel » |
| 4 | **Limitation de débit incomplète** : réinitialisation de mot de passe par IP, vérification de certificats, AJAX, caisse | Moyenne | Corrigé : limiteur atomique en base appliqué à tous les points sensibles (réponse 429 + `Retry-After`) |
| 5 | Mots de passe hachés en bcrypt (coût 10), sans refus des mots de passe courants ni des données personnelles | Moyenne | Corrigé : Argon2id (64 Mo, 4 itérations) avec migration transparente à la connexion, liste noire, interdiction du nom et de l'e-mail |
| 6 | Images téléversées conservées telles quelles (métadonnées EXIF/GPS, fichiers « polyglottes ») | Moyenne | Corrigé : réencodage systématique avec GD, dimensions bornées |
| 7 | Derrière un proxy ou un CDN : adresse IP réelle et HTTPS non détectés (limitations faussées, cookie sans `Secure`) | Moyenne | Corrigé : liste `trusted_proxies` ; `X-Forwarded-For` et `X-Forwarded-Proto` lus uniquement depuis ces proxys |
| 8 | Liens protocole-relatif (`//domaine`) acceptés dans les contenus de cours | Faible | Corrigé par le filtre HTML |
| 9 | Pas de durée de vie absolue des sessions ; pas de `security.txt` (bloqué par la règle sur les fichiers cachés) | Faible | Corrigé : reconnexion après 12 h ; `security.txt` dynamique et exception `.well-known` |
| 10 | Objet du formulaire de contact non contrôlé | Faible | Corrigé : liste fermée |

**Points vérifiés sans anomalie**
- Aucune injection SQL : requêtes préparées natives partout.
- Aucune faille XSS : chaque sortie dynamique passe par `e()`, `sanitize_html()` ou un cast entier.
- Toutes les actions modifiant des données exigent un jeton CSRF.
- Aucun accès aux ressources d'autrui (IDOR) sur les tentatives d'examen, les certificats, les reçus, les médias et les travaux.
- Les dépendances Composer n'ont aucune vulnérabilité connue.
- Les répertoires sensibles et les fichiers cachés sont protégés.

---

## 2. Mesures de sécurité en place

**Authentification et sessions**
- Mots de passe hachés en Argon2id ; 10 caractères minimum, lettres et chiffres, hors liste noire et sans données personnelles.
- Vérification à temps constant, y compris pour un compte inexistant.
- Double authentification, au choix de l'utilisateur :
  - **application d'authentification** (TOTP, RFC 6238), recommandée ;
  - **code à 6 chiffres par e-mail** (autorisable ou non par l'administration) : haché (HMAC) en base, valable 10 minutes, à usage unique, annulé après 5 erreurs, 6 envois au plus par 15 minutes ;
  - **appareils de confiance** facultatifs (0 à 90 jours) : cookie `HttpOnly`, `SameSite=Lax`, `Secure` en HTTPS, sélecteur + validateur haché en base ; liste et retrait par l'utilisateur ; tous retirés à chaque changement de mot de passe ou désactivation ;
  - **politique d'obligation** : facultative, personnel ou tous les comptes ; un compte non équipé ne peut ouvrir que « Sécurité du compte » (AJAX refusé en 403) et ne peut pas désactiver la 2FA ;
  - **alertes e-mail** à chaque activation, désactivation, régénération ou utilisation d'un code de secours, nouvel appareil de confiance et réinitialisation par un administrateur.
- Application TOTP :
  - secret chiffré en base (libsodium `secretbox`, clé dérivée de la clé applicative) ;
  - anti-rejeu par mémorisation du dernier pas de temps utilisé ;
  - 8 codes de secours à usage unique, stockés sous forme de HMAC ;
  - au plus 5 essais par étape de connexion, 10 par 15 minutes ;
  - réinitialisation possible par un administrateur, tracée dans le journal d'audit.
- Cookie de session `HttpOnly` et `SameSite=Lax`. En HTTPS, il est en plus `Secure` et prend le préfixe `__Host-`.
- Sessions : mode strict, identifiant régénéré à la connexion et toutes les 15 minutes, expiration après 2 h d'inactivité et au plus tard 12 h après la connexion. Un changement de mot de passe ferme toutes les autres sessions.
- Jetons de réinitialisation à usage unique, valables 60 minutes, stockés hachés. La réponse est identique, que le compte existe ou non.

**Contrôle d'accès**
- Permissions fines par rôle (RBAC), vérifiées sur chaque page et chaque endpoint.
- Propriété de chaque ressource vérifiée côté serveur.
- Accès aux cours conditionné à un paiement confirmé par le serveur.
- Le dernier administrateur actif est protégé ; un administrateur ne peut pas modifier son propre rôle.

**Entrées et sorties**
- PDO avec requêtes préparées natives (`EMULATE_PREPARES = false`).
- Échappement HTML systématique et filtre HTML par liste blanche pour les contenus riches.
- CSP stricte, sans script inline (`script-src 'self'`, `object-src 'none'`, `frame-ancestors 'self'`, `upgrade-insecure-requests` en HTTPS).
- En-têtes `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, et HSTS en HTTPS.

**Fichiers**
- Type MIME réel vérifié avec `finfo` ; extension imposée par le type détecté ; nom de fichier aléatoire ; taille bornée.
- Images réencodées.
- PDF et vidéos hors de portée HTTP, servis par `download.php` après contrôle de l'inscription.
- Aucune exécution de script possible dans `uploads/`.

**Paiement**
- Le statut n'est jamais déduit du retour du navigateur : il est revérifié auprès de l'API CinetPay.
- Montant et devise contrôlés.
- Verrou transactionnel (`SELECT ... FOR UPDATE`) contre la double validation.
- Mode test désactivé d'office en production.

**Traçabilité**
- Journal d'audit : connexions, verrouillages, opérations 2FA, limitations de débit, accès refusés, actions administratives, paiements, examens, corrections.

---

## 3. Recommandations pour l'exploitation

1. **HTTPS obligatoire.** Décommentez la redirection dans `.htaccess` et renseignez `base_url` en `https://`.
2. **Activez la 2FA** sur votre compte, puis réglez l'obligation sur « personnel » au minimum (*Administration / Paramètres / Double authentification*). Pour les comptes du personnel, préférez l'application d'authentification au code par e-mail.
3. **SMTP.** Configurez un vrai transport (*Paramètres / Messagerie*) : le mode `log` écrit les e-mails, dont les liens de réinitialisation, dans `storage/mail/` et `storage/logs/mail.log`, ce qui ne convient qu'au développement. Le mot de passe SMTP est chiffré en base (libsodium) et jamais réaffiché.
4. **Proxy ou CDN** (Cloudflare, répartiteur de charge) : déclarez leurs adresses dans `trusted_proxies`.
5. **Utilisateur MySQL dédié**, avec uniquement les droits `SELECT, INSERT, UPDATE, DELETE` sur la base `africaspy` une fois l'installation terminée.
6. **PHP en production** : `expose_php = Off`, `display_errors = Off`, `session.cookie_secure = 1`, `upload_max_filesize` adapté.
7. **Sauvegardes chiffrées** régulières de la base, de `uploads/`, de `certificates/generated/` et de `storage/invoices/`, avec un test de restauration.
8. **Mises à jour** : `composer audit` et `composer update` chaque mois ; PHP et MariaDB/MySQL à jour.
9. **Surveillance** : consultez régulièrement le journal d'audit (événements `auth.locked`, `security.rate_limited`, `access.denied`).
10. Ne changez jamais `app_key` après la mise en service : cela invaliderait les signatures des certificats et les secrets 2FA.

## 4. Risques résiduels assumés

- **QCM de chapitre** : ils sont formatifs et révèlent donc la bonne réponse, alors qu'ils puisent dans la même banque que l'examen final. L'intégrité de l'examen repose sur le tirage aléatoire (15 questions parmi 20 à 22), le mélange des réponses, le chronomètre et le nombre limité de tentatives. Pour un niveau d'exigence plus élevé, ajoutez des questions réservées à l'examen (questions sans chapitre).
- **Énumération à l'inscription** : la page indique qu'un e-mail est déjà utilisé (choix d'ergonomie). L'abus est limité à 5 inscriptions par IP et par heure.
- **PDF téléversés** : ils proviennent uniquement du personnel habilité. Un PDF piégé déposé par un compte d'administration compromis resterait un risque, d'où l'intérêt de la 2FA obligatoire.

## 5. Tests de sécurité

```bash
php -S 127.0.0.1:8080 resources/dev-router.php      # reproduit les règles .htaccess
node tests/security.e2e.js      # 44 contrôles : CSRF, force brute, accès, IDOR, injections, XSS, en-têtes, paiement
node tests/hardening.e2e.js     # 26 contrôles : verrouillage ciblé, débit, Host, Argon2id, 2FA, réencodage, security.txt
node tests/submissions.e2e.js   # 19 contrôles : travaux et notes (accès, CSRF, échappement)
node tests/mfa.e2e.js           # 42 contrôles : code e-mail (hachage, expiration, essais, renvoi), appareils de confiance (cookie falsifié, autre compte), obligation, alertes
node tests/mail_invoice.e2e.js  # 36 contrôles : e-mails, factures, avoirs (IDOR, CSRF, injection CSV, secret SMTP)
node tests/learner.e2e.js       # parcours complet
```
Ces tests utilisent une base de **développement** : ils remettent à zéro les compteurs anti-abus liés à 127.0.0.1. Ne les lancez jamais contre la production.
