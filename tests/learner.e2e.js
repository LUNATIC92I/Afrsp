const { chromium } = require('playwright');
const { execFileSync } = require('child_process');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const SLUG = process.env.COURSE_SLUG || 'fondements-du-renseignement'; // formation testée (paramétrable)
const OUT = process.env.OUT_DIR || require('os').tmpdir();
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
(async () => {
  // Base de développement : réinitialise les compteurs anti-abus liés à 127.0.0.1
  sql("DELETE FROM login_attempts WHERE ip_address IN ('127.0.0.1','::1')");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  sql("DELETE FROM rate_limits");
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1366, height: 860 } });
  await ctx.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const p = await ctx.newPage();
  const errs = [];
  p.on('pageerror', e => errs.push('PAGEERR ' + e.message));
  p.on('console', m => { if (m.type() === 'error') errs.push('CONSOLE ' + m.text()); });
  const email = 'test' + Date.now() + '@example.org';
  await p.goto(B + '/register.php');
  await p.fill('#first_name', 'Kofi'); await p.fill('#last_name', 'Mensah'); await p.fill('#email', email);
  await p.fill('#password', 'Analyste2026'); await p.fill('#password_confirm', 'Analyste2026');
  await p.check('input[name=charter]');
  await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]);
  console.log('after register:', p.url());
  const cidPre = sql(`SELECT id FROM courses WHERE slug='${SLUG}'`);
  // Avant paiement : aucun accès au cours
  await p.goto(B + '/learner/learning.php?course=' + cidPre);
  console.log('learning before payment ->', p.url().replace(B, ''));
  await p.goto(B + '/course.php?slug=' + SLUG);
  console.log('price shown:', (await p.textContent('.course-aside .price__now')).trim(), '| old:', (await p.textContent('.course-aside .price__old')).trim());
  await Promise.all([p.waitForNavigation(), p.click('.course-aside .btn--pay')]);
  console.log('checkout:', p.url().replace(B, ''), '| total:', (await p.textContent('.order__total span:last-child')).trim());
  await p.screenshot({ path: OUT + '/checkout.png', fullPage: true });
  await p.check('input[name=method][value=sandbox]');
  await p.check('input[name=terms]');
  await Promise.all([p.waitForNavigation(), p.click('.btn--pay')]);
  console.log('gateway:', p.url().replace(B, ''));
  await p.screenshot({ path: OUT + '/sandbox.png' });
  await Promise.all([p.waitForNavigation(), p.click('button[value=success]')]);
  await p.waitForTimeout(1200);
  console.log('return:', (await p.textContent('.granted__title')).trim());
  await p.screenshot({ path: OUT + '/granted.png' });
  console.log('payment row:', sql("SELECT CONCAT(p.status,' ',p.amount_xof,' ',p.list_price_xof) FROM payments p JOIN users u ON u.id=p.user_id WHERE u.email='" + email + "'"));
  const rc = await p.request.get(B + '/learner/receipt.php?ref=' + sql("SELECT reference FROM payments p JOIN users u ON u.id=p.user_id WHERE u.email='" + email + "'"));
  console.log('receipt:', rc.status(), rc.headers()['content-type']);
  require('fs').writeFileSync(OUT + '/receipt.pdf', await rc.body());
  await Promise.all([p.waitForNavigation(), p.click('.granted a.btn--primary')]);
  console.log('after enroll:', p.url());
  await p.waitForTimeout(800);
  await p.screenshot({ path: OUT + '/learning.png' });
  await p.click('[data-complete]'); await p.waitForTimeout(900);
  await p.screenshot({ path: OUT + '/learning-done.png' });
  const cid = sql(`SELECT id FROM courses WHERE slug='${SLUG}'`);
  const ids = sql('SELECT l.id FROM lessons l JOIN chapters c ON c.id=l.chapter_id WHERE c.course_id=' + cid).split('\n');
  for (const id of ids) {
    const r = await p.evaluate(async (id) => (await AFSPY.request(AFSPY.url('ajax/progress.php'), { body: { lesson_id: id } })), id);
    if (!r.ok) console.log('progress fail', r);
  }
  console.log('progress =', sql("SELECT progress FROM course_enrollments ce JOIN users u ON u.id=ce.user_id WHERE u.email='" + email + "'"));
  const ch = sql('SELECT id FROM chapters WHERE course_id=' + cid + ' ORDER BY sort_order LIMIT 1');
  await p.goto(B + '/learner/learning.php?course=' + cid + '&quiz=' + ch);
  for (const fs of await p.$$('fieldset.q')) { const r = await fs.$('input'); await r.check(); }
  await p.click('form[data-quiz] button[type=submit]'); await p.waitForTimeout(900);
  console.log('quiz:', await p.textContent('[data-quiz-result]'));
  await p.screenshot({ path: OUT + '/quiz.png', fullPage: true });
  await p.goto(B + '/learner/exams.php?course=' + cid);
  p.once('dialog', d => d.accept());
  await Promise.all([p.waitForNavigation(), p.click('form[data-confirm] button')]);
  console.log('exam url:', p.url());
  const correct = Object.fromEntries(sql('SELECT question_id, id FROM answers WHERE is_correct=1').split('\n').map(l => l.split('\t')));
  for (const fs of await p.$$('fieldset.q')) {
    const name = await (await fs.$('input')).getAttribute('name'); const qid = name.match(/\d+/)[0];
    await fs.$eval('input[value="' + correct[qid] + '"]', el => el.click());
  }
  await p.waitForTimeout(300);
  await p.screenshot({ path: OUT + '/exam.png' });
  p.once('dialog', d => d.accept());
  await Promise.all([p.waitForNavigation(), p.click('.exam-bar button[type=submit]')]);
  await p.waitForTimeout(1800);
  console.log('result url:', p.url(), await p.textContent('.result-hero h2'));
  await p.screenshot({ path: OUT + '/result.png' });
  const no = sql("SELECT certificate_no FROM certificates ct JOIN users u ON u.id=ct.user_id WHERE u.email='" + email + "'");
  console.log('cert:', no);
  const res = await p.request.get(B + '/learner/certificate.php?no=' + no + '&dl=1');
  console.log('pdf:', res.status(), res.headers()['content-type'], (await res.body()).length);
  require('fs').writeFileSync(OUT + '/cert.pdf', await res.body());
  const vmid = sql(`SELECT l.media_id FROM lessons l JOIN chapters ch ON ch.id = l.chapter_id WHERE ch.course_id = ${cid} AND l.type = 'video' LIMIT 1`);
  const vid = await p.request.get(B + '/download.php?media=' + vmid, { headers: { Range: 'bytes=0-99' } });
  console.log('video range:', vid.status(), vid.headers()['content-range']);
  await p.goto(B + '/verify-certificate.php?code=' + no);
  console.log('verify:', (await p.textContent('.verify-result__status')).trim());
  await p.goto(B + '/learner/dashboard.php'); await p.waitForTimeout(1800);
  await p.screenshot({ path: OUT + '/dashboard.png', fullPage: true });
  console.log(errs.length ? errs.join('\n') : 'no JS errors');
  await b.close();
})();
