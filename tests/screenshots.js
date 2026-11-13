// Génère les captures d'écran de présentation dans docs/screenshots/ (×2, JPEG).
// Usage : php -S 127.0.0.1:8080 resources/dev-router.php  puis  node tests/screenshots.js
const { chromium, devices } = require('playwright');
const { execFileSync } = require('child_process');
const path = require('path');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const OUT = path.join(__dirname, '..', 'docs', 'screenshots');
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
require('fs').mkdirSync(OUT, { recursive: true });

async function settle(p, sel) {
  await p.addStyleTag({ content: '.cursor-ring{display:none!important}' });
  if (sel) await p.locator(sel).first().scrollIntoViewIfNeeded();
  // Déclenche les révélations au défilement puis laisse finir les animations
  await p.evaluate(() => document.querySelectorAll('.reveal,[data-stagger],.img-reveal,[data-count],[data-split],[data-decrypt],[data-draw],.scanner').forEach(e => e.classList.add('is-visible')));
  await p.waitForTimeout(2200);
}
async function shot(p, name, sel, opts = {}) {
  await settle(p, sel);
  if (sel && opts.element) await p.locator(sel).first().screenshot({ path: path.join(OUT, name + '.jpg'), type: 'jpeg', quality: 86 });
  else await p.screenshot({ path: path.join(OUT, name + '.jpg'), type: 'jpeg', quality: 86, fullPage: !!opts.full });
  console.log('✓', name);
}

(async () => {
  sql("DELETE FROM login_attempts WHERE ip_address IN ('127.0.0.1','::1')");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
  await ctx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const p = await ctx.newPage();
  p.on('dialog', d => d.accept());

  // ---- Séquence d'ouverture (contexte sans mémoire de session)
  const ictx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
  const ip = await ictx.newPage();
  await ip.goto(B + '/index.php'); await ip.waitForTimeout(2700);
  await ip.screenshot({ path: path.join(OUT, '00-sequence-ouverture.jpg'), type: 'jpeg', quality: 86 }); console.log('✓ 00-sequence-ouverture');
  await ictx.close();

  // ---- Site public
  await p.goto(B + '/index.php', { waitUntil: 'networkidle' });
  await p.mouse.move(1000, 420);
  await shot(p, '01-accueil-hero');
  await shot(p, '02-offre-promotionnelle', '#offre', { element: true });
  await p.locator('#expertise').scrollIntoViewIfNeeded();
  await p.click('#tab-counter'); await shot(p, '03-centre-de-commandement', '#expertise .command', { element: true });
  await p.$eval('.zone[data-zone="horn"]', z => z.dispatchEvent(new MouseEvent('click', { bubbles: true }))); await p.locator('#carte').hover();
  await shot(p, '04-afrique-holographique', '#carte', { element: true });
  await shot(p, '05-formations-a-la-une', '#formations', { element: true });
  await p.goto(B + '/academy.php'); await p.click('.chip[data-domain="contre-espionnage"]'); await p.waitForTimeout(900);
  await shot(p, '06-catalogue-filtre', '#catalog-results', {});
  await p.goto(B + '/course.php?slug=renseignement-humain-principes-ethique');
  await shot(p, '07-fiche-formation-prix');
  await p.goto(B + '/tools.php'); await shot(p, '08-galerie-outils', '.gear-grid', {});

  // ---- Parcours apprenant : inscription → paiement → cours → examen
  const email = 'awa.diallo.' + Date.now() + '@example.org';
  await p.goto(B + '/register.php?formation=renseignement-humain-principes-ethique');
  await p.fill('#first_name', 'Awa'); await p.fill('#last_name', 'Diallo'); await p.fill('#email', email);
  await p.fill('#password', 'Analyste2026'); await p.fill('#password_confirm', 'Analyste2026'); await p.check('input[name=charter]');
  await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]);
  await p.check('input[name=method][value=manual]');
  await shot(p, '09-paiement-caisse');
  await p.check('input[name=method][value=sandbox]'); await p.check('input[name=terms]');
  await Promise.all([p.waitForNavigation(), p.click('.btn--pay')]);
  await Promise.all([p.waitForNavigation(), p.click('button[value=success]')]);
  await p.waitForTimeout(400);
  await shot(p, '10-acces-accorde');
  const cid = sql("SELECT id FROM courses WHERE slug='renseignement-humain-principes-ethique'");
  const lessons = sql('SELECT l.id FROM lessons l JOIN chapters c ON c.id=l.chapter_id WHERE c.course_id=' + cid + ' ORDER BY c.sort_order, l.sort_order').split('\n');
  await p.goto(B + '/learner/learning.php?course=' + cid + '&lesson=' + lessons[3]);
  for (const id of lessons.slice(0, 3)) await p.evaluate(async id => AFSPY.request(AFSPY.url('ajax/progress.php'), { body: { lesson_id: id } }), id);
  await p.goto(B + '/learner/learning.php?course=' + cid + '&lesson=' + lessons[3]);
  await shot(p, '11-lecteur-de-cours');
  for (const id of lessons) await p.evaluate(async id => AFSPY.request(AFSPY.url('ajax/progress.php'), { body: { lesson_id: id } }), id);
  const ch = sql('SELECT id FROM chapters WHERE course_id=' + cid + ' ORDER BY sort_order LIMIT 1');
  await p.goto(B + '/learner/learning.php?course=' + cid + '&quiz=' + ch);
  const correct = Object.fromEntries(sql('SELECT question_id, id FROM answers WHERE is_correct=1').split('\n').map(l => l.split('\t')));
  for (const fs of await p.$$('fieldset.q')) { const n = await (await fs.$('input')).getAttribute('name'); await fs.$eval('input[value="' + correct[n.match(/\d+/)[0]] + '"]', e => e.click()); }
  await p.click('form[data-quiz] button[type=submit]'); await p.waitForTimeout(1200);
  await shot(p, '12-qcm-corrige', 'form[data-quiz]', {});
  await p.goto(B + '/learner/exams.php?course=' + cid);
  await Promise.all([p.waitForNavigation(), p.click('form[data-confirm] button')]);
  const qs = await p.$$('fieldset.q');
  for (const fs of qs.slice(0, Math.ceil(qs.length * 0.6))) { const n = await (await fs.$('input')).getAttribute('name'); await fs.$eval('input[value="' + correct[n.match(/\d+/)[0]] + '"]', e => e.click()); }
  await shot(p, '13-examen-chronometre');
  for (const fs of qs) { const n = await (await fs.$('input')).getAttribute('name'); await fs.$eval('input[value="' + correct[n.match(/\d+/)[0]] + '"]', e => e.click()); }
  await Promise.all([p.waitForNavigation(), p.click('.exam-bar button[type=submit]')]);
  await shot(p, '14-resultat-examen');
  await p.goto(B + '/learner/dashboard.php'); await shot(p, '15-tableau-de-bord-apprenant');
  await p.goto(B + '/learner/payments.php'); await shot(p, '16-mes-paiements');
  // Certificat PDF -> image
  const no = sql("SELECT certificate_no FROM certificates ct JOIN users u ON u.id=ct.user_id WHERE u.email='" + email + "'");
  const pdf = await p.request.get(B + '/learner/certificate.php?no=' + no + '&dl=1');
  require('fs').writeFileSync(path.join(OUT, 'certificat.pdf'), await pdf.body());
  execFileSync('pdftoppm', ['-r', '160', '-jpeg', '-jpegopt', 'quality=88', '-singlefile', path.join(OUT, 'certificat.pdf'), path.join(OUT, '17-certificat')]);
  require('fs').unlinkSync(path.join(OUT, 'certificat.pdf'));
  console.log('✓ 17-certificat');
  const ref = sql("SELECT reference FROM payments p JOIN users u ON u.id=p.user_id WHERE u.email='" + email + "' AND p.status='paid'");
  const rc = await p.request.get(B + '/learner/receipt.php?ref=' + ref);
  require('fs').writeFileSync(path.join(OUT, 'recu.pdf'), await rc.body());
  execFileSync('pdftoppm', ['-r', '110', '-jpeg', '-jpegopt', 'quality=88', '-singlefile', '-f', '1', '-l', '1', path.join(OUT, 'recu.pdf'), path.join(OUT, '18-recu-de-paiement')]);
  require('fs').unlinkSync(path.join(OUT, 'recu.pdf'));
  console.log('✓ 18-recu-de-paiement');
  await p.goto(B + '/verify-certificate.php?code=' + no); await shot(p, '19-verification-publique');

  // ---- Administration
  const actx = await b.newContext({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
  await actx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const a = await actx.newPage();
  a.on('dialog', d => d.accept());
  await a.goto(B + '/login.php'); await a.fill('#email', ADMIN_EMAIL); await a.fill('#password', ADMIN_PASSWORD);
  await Promise.all([a.waitForNavigation(), a.click('form button[type=submit]')]);
  await a.goto(B + '/admin/dashboard.php'); await shot(a, '20-admin-pilotage');
  await a.goto(B + '/admin/payments.php'); await shot(a, '21-admin-paiements');
  await a.goto(B + '/admin/courses.php?action=edit&id=' + cid); await shot(a, '22-admin-edition-formation-prix', 'input[name=price_xof]', {});

  // ---- Mobile
  const mctx = await b.newContext({ ...devices['iPhone 13'] });
  await mctx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const m = await mctx.newPage();
  await m.goto(B + '/index.php'); await shot(m, '23-mobile-accueil');
  await m.goto(B + '/index.php'); await shot(m, '24-mobile-offre', '#offre', { element: true });
  await m.goto(B + '/course.php?slug=intelligence-economique-veille-strategique'); await m.locator('.course-aside').scrollIntoViewIfNeeded(); await shot(m, '25-mobile-achat', '.course-aside', { element: true });
  await b.close();
})();
