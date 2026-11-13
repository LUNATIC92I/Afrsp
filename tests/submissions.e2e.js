// Dépôt de travaux corrigés et notes personnelles (parcours + sécurité)
const { chromium, request } = require('playwright');
const { execFileSync } = require('child_process');
const path = require('path');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const OUT = process.env.OUT_DIR || require('os').tmpdir();
const SHOTS = path.join(__dirname, '..', 'docs', 'screenshots');
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
let pass = 0, fail = 0;
const check = (n, c, x = '') => { c ? pass++ : fail++; console.log((c ? '  PASS ' : '  FAIL ') + n + (x ? ' — ' + x : '')); };
const token = h => (h.match(/name="_csrf" value="([^"]+)"/) || [])[1];

(async () => {
  sql("DELETE FROM login_attempts WHERE ip_address IN ('127.0.0.1','::1')");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
  await ctx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('dialog', d => d.accept());
  const prep = async () => { await p.addStyleTag({ content: '.cursor-ring{display:none!important}' }); await p.evaluate(() => document.querySelectorAll('.reveal').forEach(e => e.classList.add('is-visible'))); };

  // Apprenant : inscription + paiement (mode test) de la formation B101
  const email = 'fatou.ndiaye.' + Date.now() + '@example.org';
  await p.goto(B + '/register.php?formation=renseignement-humain-principes-ethique');
  await p.fill('#first_name', 'Fatou'); await p.fill('#last_name', 'Ndiaye'); await p.fill('#email', email);
  await p.fill('#password', 'Analyste2026'); await p.fill('#password_confirm', 'Analyste2026'); await p.check('input[name=charter]');
  await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]);
  await p.check('input[name=method][value=sandbox]'); await p.check('input[name=terms]');
  await Promise.all([p.waitForNavigation(), p.click('.btn--pay')]);
  await Promise.all([p.waitForNavigation(), p.click('button[value=success]')]);
  const cid = sql("SELECT id FROM courses WHERE code='AFS-B101'");
  const lid = sql("SELECT l.id FROM lessons l JOIN chapters c ON c.id=l.chapter_id WHERE c.course_id=" + cid + " AND l.title LIKE 'Simulation guidée%'");
  const uid = sql("SELECT id FROM users WHERE email='" + email + "'");

  // Notes personnelles : sauvegarde automatique puis persistance
  await p.goto(B + '/learner/learning.php?course=' + cid + '&lesson=' + lid);
  await p.click('.notes summary');
  await p.fill('#lesson-notes', 'Penser à la grille de cotation : séparer source et information. <script>alert(1)</script>');
  await p.waitForTimeout(1800);
  check('Notes : sauvegarde automatique', sql('SELECT COUNT(*) FROM lesson_notes WHERE user_id=' + uid + ' AND lesson_id=' + lid) === '1', await p.textContent('[data-note-state]'));
  await p.reload();
  check('Notes : restaurées et échappées', (await p.inputValue('#lesson-notes')).includes('grille de cotation') && !(await p.content()).includes('<script>alert(1)</script>'));

  // Dépôt trop court refusé
  await p.fill('#work-content', 'Trop court.');
  await p.click('form[data-work] button[type=submit]'); await p.waitForTimeout(900);
  check('Dépôt : travail trop court refusé', sql('SELECT COUNT(*) FROM submissions WHERE user_id=' + uid) === '0');
  // Dépôt valide
  const work = '1. Profils retenus : vendeuses de poisson (connaissance directe des volumes), transporteurs (coûts et délais), restaurateurs (demande), association de consommateurs (perception des prix), service public des pêches (statistiques officielles).\n2. Présentation : je me présente comme chargé d\'études pour la coopérative, j\'explique la finalité, la confidentialité et le droit de refuser.\n3. Questions ouvertes : comment ont évolué vos ventes depuis un an ? Qu\'est-ce qui a changé chez vos clients ? <b>test</b>';
  await p.fill('#work-content', work);
  await prep();
  await p.locator('.work').scrollIntoViewIfNeeded(); await p.evaluate(() => window.scrollBy(0, -120));
  await p.screenshot({ path: path.join(SHOTS, '32-depot-de-travail.jpg'), type: 'jpeg', quality: 86 });
  await p.click('form[data-work] button[type=submit]'); await p.waitForTimeout(2200);
  check('Dépôt : enregistré (version 1, à corriger)', sql("SELECT CONCAT(status,'/',version) FROM submissions WHERE user_id=" + uid) === 'submitted/1');
  check('Dépôt : la leçon est validée', sql('SELECT COUNT(*) FROM lesson_progress WHERE user_id=' + uid + ' AND lesson_id=' + lid) === '1');
  check('Dépôt : statut affiché après rechargement', /Déposé/.test(await p.textContent('.work__head')));

  // Sécurité
  const other = await request.newContext({ baseURL: B });
  let t = token(await (await other.get('/register.php')).text());
  const oem = 'other' + Date.now() + '@example.org';
  await other.post('/register.php', { form: { _csrf: t, first_name: 'Ali', last_name: 'Sow', email: oem, password: 'Securite2026', password_confirm: 'Securite2026', charter: '1' } });
  t = token(await (await other.get('/learner/dashboard.php')).text());
  let r = await other.post('/ajax/submission.php', { form: { _csrf: t, lesson_id: lid, content: 'x'.repeat(300) }, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  check('Sécurité : dépôt sans inscription (non payé) refusé', r.status() === 403);
  r = await other.post('/ajax/note.php', { form: { _csrf: t, lesson_id: lid, content: 'intrus' }, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  check('Sécurité : note sur une leçon non achetée refusée', r.status() === 403);
  r = await other.get('/admin/submissions.php');
  check('Sécurité : apprenant -> console de correction 403', r.status() === 403);
  r = await other.post('/ajax/submission.php', { form: { lesson_id: lid, content: 'x'.repeat(300) }, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
  check('Sécurité : dépôt sans jeton CSRF refusé', r.status() === 419);

  // Formateur : reprise demandée puis correction
  const actx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
  await actx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const a = await actx.newPage(); a.on('dialog', d => d.accept());
  await a.goto(B + '/login.php'); await a.fill('#email', ADMIN_EMAIL); await a.fill('#password', ADMIN_PASSWORD);
  await Promise.all([a.waitForNavigation(), a.click('form button[type=submit]')]);
  const sid = sql('SELECT id FROM submissions WHERE user_id=' + uid);
  await a.goto(B + '/admin/submissions.php');
  check('Formateur : travail listé « à corriger »', (await a.content()).includes('Fatou Ndiaye'));
  await a.goto(B + '/admin/submissions.php?id=' + sid);
  check('Formateur : contenu du travail échappé', !(await a.content()).includes('<b>test</b>'));
  await a.fill('#f_feedback', 'Bonne sélection des profils. Il manque la grille de cotation et les situations d\'interruption : complétez les étapes 4 et 5.');
  await Promise.all([a.waitForNavigation(), a.click('button[value=revision]')]);
  check('Formateur : reprise demandée', sql('SELECT status FROM submissions WHERE id=' + sid) === 'revision');
  // L'apprenant reprend
  await p.goto(B + '/learner/learning.php?course=' + cid + '&lesson=' + lid);
  await p.fill('#work-content', work + '\n4. Grille : fiabilité A-F et crédibilité 1-6 pour chaque information.\n5. J\'interromps si la personne est en détresse, demande une contrepartie ou aborde un sujet hors mandat.');
  await p.click('form[data-work] button[type=submit]'); await p.waitForTimeout(2200);
  check('Apprenant : nouvelle version déposée (v2)', sql("SELECT CONCAT(status,'/',version) FROM submissions WHERE id=" + sid) === 'submitted/2');
  // Correction définitive
  await a.goto(B + '/admin/submissions.php?id=' + sid);
  await a.fill('#f_grade', '16');
  await a.fill('#f_feedback', 'Travail complet et éthique. Les profils sont justifiés, la présentation est transparente et la grille de cotation est correcte. Pour aller plus loin : ajoutez des relances neutres.');
  await a.evaluate(() => document.querySelectorAll('.reveal').forEach(e => e.classList.add('is-visible')));
  await a.addStyleTag({ content: '.cursor-ring{display:none!important}' });
  await a.waitForTimeout(1200);
  await a.screenshot({ path: path.join(SHOTS, '33-correction-formateur.jpg'), type: 'jpeg', quality: 86 });
  await Promise.all([a.waitForNavigation(), a.click('button[value=reviewed]')]);
  check('Formateur : correction 16/20 enregistrée', sql("SELECT CONCAT(status,'/',grade) FROM submissions WHERE id=" + sid) === 'reviewed/16');
  // L'apprenant voit la note et ne peut plus modifier
  await p.goto(B + '/learner/learning.php?course=' + cid + '&lesson=' + lid); await prep();
  check('Apprenant : note affichée', (await p.textContent('.work__grade')).includes('16'));
  await p.locator('.work').scrollIntoViewIfNeeded(); await p.evaluate(() => window.scrollBy(0, -120)); await p.waitForTimeout(400);
  await p.screenshot({ path: path.join(SHOTS, '34-travail-corrige.jpg'), type: 'jpeg', quality: 86 });
  t = await p.evaluate(() => document.querySelector('meta[name="csrf-token"]').content);
  r = await p.request.post(B + '/ajax/submission.php', { form: { _csrf: t, lesson_id: lid, content: 'y'.repeat(300) }, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': t } });
  check('Apprenant : travail corrigé non modifiable (409)', r.status() === 409);
  await p.goto(B + '/learner/submissions.php');
  check('Apprenant : page « Mes travaux » avec moyenne', (await p.content()).includes('16'));
  check('Aucune erreur JS', errs.length === 0, errs.join(' | '));
  await b.close();
  console.log(`\n${pass} réussis, ${fail} échecs`);
  process.exit(fail ? 1 : 0);
})();
