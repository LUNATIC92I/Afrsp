// Tests de sécurité et parcours administrateur
const { chromium, request } = require('playwright');
const { execFileSync } = require('child_process');
const fs = require('fs');
const OUT = process.env.OUT_DIR || require('os').tmpdir();
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['-N', 'africaspy', '-e', q]).toString().trim();
let pass = 0, fail = 0;
const check = (name, cond, extra = '') => { cond ? pass++ : fail++; console.log((cond ? '  PASS ' : '  FAIL ') + name + (extra ? ' — ' + extra : '')); };
const token = html => (html.match(/name="_csrf" value="([^"]+)"/) || [])[1];

(async () => {
  // Base de développement : réinitialise les compteurs anti-abus liés à 127.0.0.1
  sql("DELETE FROM login_attempts WHERE ip_address IN ('127.0.0.1','::1')");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  // ---------- 1. CSRF : POST sans jeton refusé
  const anon = await request.newContext({ baseURL: B });
  let r = await anon.post('/login.php', { form: { email: 'admin@africaspy.test', password: 'x' }, maxRedirects: 0 });
  check('CSRF: login sans jeton -> 419', r.status() === 419, String(r.status()));
  r = await anon.post('/contact.php', { form: { full_name: 'x' }, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  check('CSRF: AJAX sans jeton -> 419', r.status() === 419);

  // ---------- 2. Force brute : verrouillage après 5 échecs
  const bf = await request.newContext({ baseURL: B });
  const victim = 'bruteforce' + Date.now() + '@example.org';
  let last = '';
  for (let i = 0; i < 6; i++) {
    const t = token(await (await bf.get('/login.php')).text());
    last = await (await bf.post('/login.php', { form: { _csrf: t, email: victim, password: 'wrong' + i } })).text();
  }
  check('Anti force brute: verrouillage après 5 échecs', /Trop de tentatives/.test(last));

  // ---------- 3. Contrôle d'accès
  r = await anon.get('/admin/dashboard.php', { maxRedirects: 0 });
  check('Admin anonyme -> redirection connexion', r.status() === 303 && /login\.php/.test(r.headers()['location'] || ''));
  r = await anon.get('/download.php?media=1', { maxRedirects: 0 });
  check('Téléchargement anonyme refusé', r.status() === 303);
  r = await anon.get('/learner/certificate.php?no=AFSPY-0000-XXXX', { maxRedirects: 0 });
  check('Certificat anonyme refusé', r.status() === 303);
  r = await anon.get('/logout.php', { maxRedirects: 0 });
  check('Déconnexion par GET ignorée', r.status() === 303);

  // Apprenant connecté -> admin interdit (403)
  const learner = await request.newContext({ baseURL: B });
  let t = token(await (await learner.get('/register.php')).text());
  const lem = 'learner' + Date.now() + '@example.org';
  await learner.post('/register.php', { form: { _csrf: t, first_name: 'Awa', last_name: 'Diop', email: lem, password: 'Securite2026', password_confirm: 'Securite2026', charter: '1' } });
  r = await learner.get('/admin/users.php');
  check('Apprenant -> /admin = 403', r.status() === 403);
  r = await learner.get('/download.php?media=1');
  check('Apprenant non inscrit -> média 403', r.status() === 403);
  r = await learner.get('/learner/exams.php?attempt=1');
  check('Tentative d\'autrui -> 404 (IDOR)', r.status() === 404);
  const otherCert = sql("SELECT certificate_no FROM certificates LIMIT 1");
  r = await learner.get('/learner/certificate.php?no=' + otherCert);
  check('Certificat d\'autrui -> 404 (IDOR)', r.status() === 404);

  // ---------- 4. Injection SQL (requêtes préparées)
  r = await anon.get('/ajax/courses.php?q=' + encodeURIComponent("' OR 1=1 -- ") + '&domaine=' + encodeURIComponent("x' UNION SELECT 1--"));
  const j = await r.json();
  check('Injection SQL neutralisée (catalogue)', r.status() === 200 && j.count === 0, 'count=' + j.count);
  r = await anon.get('/verify-certificate.php?code=' + encodeURIComponent("' OR '1'='1"));
  check('Injection SQL neutralisée (vérification)', /AUCUN CERTIFICAT VALIDE/.test(await r.text()));

  // ---------- 5. XSS : échappement + nettoyage HTML
  t = token(await (await learner.get('/learner/profile.php')).text());
  await learner.post('/learner/profile.php', { form: { _csrf: t, action: 'profile', first_name: 'Awa', last_name: 'Diop', bio: '<script>alert(1)</script><img src=x onerror=alert(2)>' } });
  const prof = await (await learner.get('/learner/profile.php')).text();
  check('XSS: bio échappée', !prof.includes('<script>alert(1)') && prof.includes('&lt;script&gt;'));
  const san = execFileSync('php', ['-r', 'define("APP_ROOT","' + __dirname + '/.."); function config($k,$d=null){return $d;} require "' + __dirname + '/../includes/functions.php"; echo sanitize_html("<p onclick=\\"x()\\">ok<script>bad()</script><a href=\\"javascript:alert(1)\\">l</a><iframe src=//e></iframe></p>");']).toString();
  check('XSS: sanitize_html retire script/onclick/javascript:/iframe', !/script|onclick|javascript:|iframe/i.test(san), san);

  // ---------- 6. En-têtes de sécurité & cookie de session
  const fresh = await request.newContext({ baseURL: B });
  r = await fresh.get('/index.php');
  const h = r.headers();
  check('CSP présente', /default-src 'self'/.test(h['content-security-policy'] || ''));
  check('X-Frame-Options', h['x-frame-options'] === 'SAMEORIGIN');
  check('nosniff', h['x-content-type-options'] === 'nosniff');
  check('Cookie de session HttpOnly + SameSite', /HttpOnly/i.test(h['set-cookie'] || '') && /SameSite=Lax/i.test(h['set-cookie'] || ''));

  // ---------- 7. Parcours administrateur (navigateur)
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  await ctx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const p = await ctx.newPage();
  const errs = [];
  p.on('pageerror', e => errs.push(e.message));
  p.on('dialog', d => d.accept());
  await p.goto(B + '/login.php');
  await p.fill('#email', ADMIN_EMAIL); await p.fill('#password', ADMIN_PASSWORD);
  await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]);
  check('Admin: connexion -> console', p.url().includes('/admin/dashboard.php'), p.url());
  await p.waitForTimeout(1500);
  await p.screenshot({ path: OUT + '/admin-dashboard.png', fullPage: true });

  // Création d'une formation
  await p.goto(B + '/admin/courses.php?action=new');
  const code = 'AFS-T' + String(Date.now()).slice(-4);
  await p.fill('#f_code', code);
  await p.fill('#f_title', 'Formation de test automatisé');
  await p.fill('#f_summary', 'Résumé de la formation de test créée par le scénario automatisé de validation.');
  await p.selectOption('#f_status', 'published');
  await p.setInputFiles('#f_cover', __dirname + '/../resources/tools/test-cover.png');
  await Promise.all([p.waitForNavigation(), p.click('form.box button[type=submit]')]);
  const newId = sql("SELECT id FROM courses WHERE code='" + code + "'");
  check('Admin: formation créée + examen auto', !!newId && sql('SELECT COUNT(*) FROM exams WHERE course_id=' + newId) === '1');
  check('Admin: couverture téléversée (MIME vérifié)', sql('SELECT cover_media_id IS NOT NULL FROM courses WHERE id=' + newId) === '1');
  // Chapitre
  await p.goto(B + '/admin/chapters.php?course=' + newId);
  await p.fill('#f_title', 'Chapitre de test'); await p.fill('#f_summary', 'Résumé');
  await Promise.all([p.waitForNavigation(), p.click('form.box button[type=submit]')]);
  const chId = sql('SELECT id FROM chapters WHERE course_id=' + newId);
  check('Admin: chapitre créé', !!chId);
  // Leçon
  await p.goto(B + '/admin/lessons.php?chapter=' + chId + '&edit=new');
  await p.fill('#f_title', 'Leçon de test');
  await p.fill('#f_content', '<h3>Titre</h3><p>Contenu <strong>riche</strong></p><script>alert(1)</script>');
  await Promise.all([p.waitForNavigation(), p.click('form.box button[type=submit]')]);
  const lc = sql("SELECT content FROM lessons WHERE chapter_id=" + chId);
  check('Admin: leçon créée et HTML nettoyé', lc.includes('<strong>riche</strong>') && !lc.includes('script'), lc);
  // Question
  await p.goto(B + '/admin/exams.php?course=' + newId);
  await p.fill('#f_prompt', 'Question de test : quelle est la bonne réponse ?');
  const inputs = await p.$$('#answers-list input.input');
  await inputs[0].fill('Mauvaise A'); await inputs[1].fill('Bonne B'); await inputs[2].fill('Mauvaise C');
  await p.check('#answers-list .answer-row:nth-child(2) input[type=radio]');
  await Promise.all([p.waitForNavigation(), p.click('form.box:nth-of-type(2) button[type=submit]')]);
  check('Admin: question QCM créée (bonne réponse = B)', sql("SELECT a.label FROM answers a JOIN questions q ON q.id=a.question_id JOIN exams e ON e.id=q.exam_id WHERE e.course_id=" + newId + " AND a.is_correct=1") === 'Bonne B');
  await p.screenshot({ path: OUT + '/admin-exams.png', fullPage: true });
  // Upload frauduleux : PHP déguisé en image
  fs.writeFileSync('' + OUT + '/evil.jpg', '<?php system($_GET["c"]); ?>');
  await p.goto(B + '/admin/media.php');
  await p.setInputFiles('#f_file', '' + OUT + '/evil.jpg');
  await Promise.all([p.waitForNavigation(), p.click('form.box[enctype] button[type=submit]')]);
  const toast = await p.textContent('.toasts');
  check('Upload: PHP déguisé en .jpg rejeté', /Format non autorisé/.test(toast), toast.trim().slice(0, 90));
  // Annonce
  await p.goto(B + '/admin/announcements.php');
  await p.fill('#f_title', 'Annonce de test'); await p.fill('#f_body', 'Corps de l\'annonce de test.');
  await Promise.all([p.waitForNavigation(), p.click('form.box button[type=submit]')]);
  check('Admin: annonce publiée', sql("SELECT COUNT(*) FROM announcements WHERE title='Annonce de test'") !== '0');
  // Révocation de certificat
  const certId = sql("SELECT id FROM certificates WHERE certificate_no='" + otherCert + "'");
  await p.goto(B + '/admin/certificates.php');
  await p.fill('form[data-confirm] input[name=reason]', 'Test de révocation');
  await Promise.all([p.waitForNavigation(), p.click('form[data-confirm] button.btn--danger')]);
  r = await anon.get('/verify-certificate.php?code=' + otherCert);
  check('Certificat révoqué -> vérification publique "RÉVOQUÉ"', /CERTIFICAT RÉVOQUÉ/.test(await r.text()));
  await p.goto(B + '/admin/certificates.php');
  await Promise.all([p.waitForNavigation(), p.click('form:has(input[value=restore]) button')]);
  r = await anon.get('/verify-certificate.php?code=' + otherCert);
  check('Certificat rétabli -> AUTHENTIQUE', /CERTIFICAT AUTHENTIQUE/.test(await r.text()));
  // Falsification en base : la signature HMAC ne correspond plus
  sql("UPDATE certificates SET holder_name='Faussaire' WHERE id=" + certId);
  sql("UPDATE certificates SET score=99 WHERE id=" + certId);
  sql("UPDATE certificates SET issued_at = issued_at - INTERVAL 1 DAY WHERE id=" + certId);
  r = await anon.get('/verify-certificate.php?code=' + otherCert);
  check('Certificat altéré en base (date) -> non valide', /AUCUN CERTIFICAT VALIDE/.test(await r.text()));
  sql("UPDATE certificates SET issued_at = issued_at + INTERVAL 1 DAY, holder_name='Kofi MENSAH', score=100 WHERE id=" + certId);
  // Audit
  check('Journal d\'audit alimenté', Number(sql("SELECT COUNT(*) FROM audit_logs WHERE action LIKE 'course.%' OR action LIKE 'certificate.%'")) >= 3);
  await p.goto(B + '/admin/audit.php');
  await p.screenshot({ path: OUT + '/admin-audit.png' });
  // Nettoyage formation de test
  sql('DELETE FROM courses WHERE id=' + newId);
  check('Aucune erreur JS côté admin', errs.length === 0, errs.join(' | '));
  await b.close();
  console.log(`\n${pass} réussis, ${fail} échecs`);
  process.exit(fail ? 1 : 0);
})();
