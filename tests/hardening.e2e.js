// Tests du durcissement de sécurité : verrouillage ciblé, limitation de débit, 2FA TOTP,
// politique de mot de passe, Argon2id, réencodage d'images, liens, security.txt.
const { chromium, request } = require('playwright');
const { execFileSync } = require('child_process');
const crypto = require('crypto');
const fs = require('fs');
const path = require('path');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const ROOT = path.join(__dirname, '..');
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
let pass = 0, fail = 0;
const check = (n, c, x = '') => { c ? pass++ : fail++; console.log((c ? '  PASS ' : '  FAIL ') + n + (x ? ' : ' + x : '')); };
const token = h => (h.match(/name="_csrf" value="([^"]+)"/) || [])[1];
const AJAX = { 'X-Requested-With': 'XMLHttpRequest' };

function b32decode(s) {
  const a = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; let bits = '';
  for (const c of s.replace(/[^A-Z2-7]/gi, '').toUpperCase()) bits += a.indexOf(c).toString(2).padStart(5, '0');
  const out = []; for (let i = 0; i + 8 <= bits.length; i += 8) out.push(parseInt(bits.slice(i, i + 8), 2));
  return Buffer.from(out);
}
function totp(secret, offset = 0) {
  const step = Math.floor(Date.now() / 1000 / 30) + offset;
  const buf = Buffer.alloc(8); buf.writeUInt32BE(0, 0); buf.writeUInt32BE(step, 4);
  const h = crypto.createHmac('sha1', b32decode(secret)).update(buf).digest();
  const o = h[19] & 15;
  return String((((h[o] & 127) << 24) | (h[o + 1] << 16) | (h[o + 2] << 8) | h[o + 3]) % 1e6).padStart(6, '0');
}
async function register(ctx, email, pwd = 'Securite-Rx7q') {
  const t = token(await (await ctx.get('/register.php')).text());
  return ctx.post('/register.php', { form: { _csrf: t, first_name: 'Test', last_name: 'Durcissement', email, password: pwd, password_confirm: pwd, charter: '1' } });
}
async function login(ctx, email, pwd) {
  const t = token(await (await ctx.get('/login.php')).text());
  return ctx.post('/login.php', { form: { _csrf: t, email, password: pwd }, maxRedirects: 0 });
}

(async () => {
  sql("DELETE FROM login_attempts"); sql("DELETE FROM rate_limits");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");

  // ---- 1. Verrouillage ciblé : un attaquant ne peut plus bloquer un compte depuis une autre IP
  const victim = 'victime' + Date.now() + '@example.org';
  const v = await request.newContext({ baseURL: B }); await register(v, victim);
  for (let i = 0; i < 8; i++) sql(`INSERT INTO login_attempts (email, ip_address, success, attempted_at) VALUES ('${victim}', '203.0.113.9', 0, UTC_TIMESTAMP())`);
  let ctx = await request.newContext({ baseURL: B });
  let r = await login(ctx, victim, 'Securite-Rx7q');
  check('Verrouillage : 8 échecs depuis une IP tierce ne bloquent pas le titulaire', r.status() === 303 && /dashboard/.test(r.headers().location || ''));
  ctx = await request.newContext({ baseURL: B });
  let last;
  for (let i = 0; i < 6; i++) last = await login(ctx, victim, 'mauvais' + i);
  check('Verrouillage : 5 échecs depuis la même IP bloquent cette IP', /Trop de tentatives/.test(await last.text()));
  sql("DELETE FROM login_attempts");

  // ---- 2. Limitation de débit (réinitialisation de mot de passe par IP)
  ctx = await request.newContext({ baseURL: B });
  let codes = [];
  for (let i = 0; i < 11; i++) {
    const t = token(await (await ctx.get('/forgot-password.php')).text());
    codes.push((await ctx.post('/forgot-password.php', { form: { _csrf: t, email: 'inconnu' + i + '@example.org' } })).status());
  }
  check('Débit : 11e demande de réinitialisation -> 429', codes[10] === 429 && codes[9] === 200, codes.join(','));
  sql("DELETE FROM rate_limits");

  // ---- 3. Empoisonnement de l'en-tête Host
  fs.writeFileSync(path.join(ROOT, 'storage/logs/mail.log'), '');
  ctx = await request.newContext({ baseURL: B, extraHTTPHeaders: { Host: 'evil.example' } });
  let t = token(await (await ctx.get('/forgot-password.php')).text());
  await ctx.post('/forgot-password.php', { form: { _csrf: t, email: victim } });
  const mail = fs.readFileSync(path.join(ROOT, 'storage/logs/mail.log'), 'utf8');
  check('Host : le lien de réinitialisation ignore l\'en-tête Host forgé', /reset-password\.php/.test(mail) && !/evil\.example/.test(mail));
  const prod = execFileSync('php', ['-r', 'define("APP_ROOT","' + ROOT + '"); $GLOBALS["__config"]=["env"=>"production","base_url"=>""]; function config($k,$d=null){$v=$GLOBALS["__config"];foreach(explode(".",$k) as $s){if(!is_array($v)||!array_key_exists($s,$v))return $d;$v=$v[$s];}return $v;} $_SERVER["HTTP_HOST"]="evil.example"; require "' + ROOT + '/includes/functions.php"; try { echo absolute_url("x.php"); } catch (Throwable $e) { echo "REFUS"; }']).toString();
  check('Host : en production sans base_url, aucun lien n\'est construit depuis la requête', prod === 'REFUS', prod);

  // ---- 4. Politique de mot de passe et Argon2id
  ctx = await request.newContext({ baseURL: B });
  r = await register(ctx, 'faible' + Date.now() + '@example.org', 'password1234');
  check('Mot de passe courant refusé', /trop courant/.test(await r.text()));
  const em = 'jeanbaptiste' + Date.now() + '@example.org';
  r = await register(ctx, em, 'jeanbaptiste2026');
  check('Mot de passe contenant l\'e-mail refusé', /ne doit pas contenir/.test(await r.text()));
  const strong = 'argon' + Date.now() + '@example.org';
  ctx = await request.newContext({ baseURL: B }); await register(ctx, strong);
  check('Nouveaux comptes hachés en Argon2id', sql(`SELECT LEFT(password_hash,10) FROM users WHERE email='${strong}'`) === '$argon2id$');
  const adm = await request.newContext({ baseURL: B });
  r = await login(adm, ADMIN_EMAIL, ADMIN_PASSWORD);
  check('Ancien hachage bcrypt migré vers Argon2id à la connexion', sql(`SELECT LEFT(password_hash,10) FROM users WHERE email='${ADMIN_EMAIL}'`) === '$argon2id$');

  // ---- 5. Double authentification TOTP de bout en bout
  const b = await chromium.launch();
  const bc = await b.newContext(); await bc.addInitScript(() => sessionStorage.setItem('afspy-intro', '1'));
  const p = await bc.newPage(); p.on('dialog', d => d.accept());
  const mfaUser = 'mfa' + Date.now() + '@example.org';
  await p.goto(B + '/register.php');
  await p.fill('#first_name', 'Kwame'); await p.fill('#last_name', 'Asante'); await p.fill('#email', mfaUser);
  await p.fill('#password', 'Horizon-Nord-58'); await p.fill('#password_confirm', 'Horizon-Nord-58'); await p.check('input[name=charter]');
  await Promise.all([p.waitForNavigation(), p.click('form button[type=submit]')]);
  await p.goto(B + '/learner/security.php');
  await Promise.all([p.waitForNavigation(), p.click('form:has(input[value=start]) button')]);
  const secret = (await p.textContent('.mfa-steps code')).replace(/\s/g, '');
  check('2FA : QR Code et clé affichés', secret.length === 32 && await p.locator('.mfa-qr img').count() === 1);
  await p.fill('#code', '000000'); await p.fill('#password', 'Horizon-Nord-58');
  await Promise.all([p.waitForNavigation(), p.click('form:has(input[value=confirm]) button')]);
  check('2FA : code faux refusé à l\'activation', sql(`SELECT totp_enabled FROM users WHERE email='${mfaUser}'`) === '0');
  await p.fill('#code', totp(secret)); await p.fill('#password', 'Horizon-Nord-58');
  await Promise.all([p.waitForNavigation(), p.click('form:has(input[value=confirm]) button')]);
  const recovery = await p.$$eval('.recovery li', l => l.map(x => x.textContent.trim()));
  check('2FA : activée avec 8 codes de secours', sql(`SELECT totp_enabled FROM users WHERE email='${mfaUser}'`) === '1' && recovery.length === 8);
  const stored = sql(`SELECT totp_secret FROM users WHERE email='${mfaUser}'`);
  check('2FA : secret chiffré en base (pas en clair)', !stored.includes(secret) && stored.length > 40);
  // Connexion : mot de passe seul insuffisant
  ctx = await request.newContext({ baseURL: B });
  r = await login(ctx, mfaUser, 'Horizon-Nord-58');
  check('2FA : mot de passe seul -> étape de vérification', r.status() === 303 && /login-2fa/.test(r.headers().location || ''));
  r = await ctx.get('/learner/dashboard.php', { maxRedirects: 0 });
  check('2FA : espace inaccessible avant le second facteur', r.status() === 303 && /login\.php/.test(r.headers().location || ''));
  t = token(await (await ctx.get('/login-2fa.php')).text());
  r = await ctx.post('/login-2fa.php', { form: { _csrf: t, code: '123456' } });
  check('2FA : code invalide refusé', /Code invalide/.test(await r.text()));
  // Code valide (pas suivant, pour éviter le rejeu du code utilisé à l'activation)
  await new Promise(res => setTimeout(res, (30 - (Math.floor(Date.now() / 1000) % 30) + 1) * 1000));
  const good = totp(secret);
  t = token(await (await ctx.get('/login-2fa.php')).text());
  r = await ctx.post('/login-2fa.php', { form: { _csrf: t, code: good }, maxRedirects: 0 });
  check('2FA : code valide -> connexion', r.status() === 303 && /dashboard/.test(r.headers().location || ''));
  const ctx2 = await request.newContext({ baseURL: B });
  await login(ctx2, mfaUser, 'Horizon-Nord-58');
  t = token(await (await ctx2.get('/login-2fa.php')).text());
  r = await ctx2.post('/login-2fa.php', { form: { _csrf: t, code: good } });
  check('2FA : rejeu du même code refusé', /Code invalide/.test(await r.text()));
  t = token(await (await ctx2.get('/login-2fa.php')).text());
  r = await ctx2.post('/login-2fa.php', { form: { _csrf: t, code: recovery[0] }, maxRedirects: 0 });
  check('2FA : code de secours accepté une fois', r.status() === 303 && /dashboard/.test(r.headers().location || ''));
  const ctx3 = await request.newContext({ baseURL: B });
  await login(ctx3, mfaUser, 'Horizon-Nord-58');
  t = token(await (await ctx3.get('/login-2fa.php')).text());
  r = await ctx3.post('/login-2fa.php', { form: { _csrf: t, code: recovery[0] } });
  check('2FA : code de secours déjà utilisé refusé', /Code invalide/.test(await r.text()));

  // ---- 6. 2FA obligatoire pour le personnel
  sql("UPDATE settings SET setting_value='1' WHERE setting_key='mfa_required_staff'");
  r = await adm.get('/admin/users.php', { maxRedirects: 0 });
  check('Personnel sans 2FA redirigé vers « Sécurité du compte » quand elle est obligatoire', r.status() === 303 && /security\.php/.test(r.headers().location || ''));
  sql("UPDATE settings SET setting_value='0' WHERE setting_key='mfa_required_staff'");

  // ---- 7. Réencodage des images téléversées
  const evil = path.join(require('os').tmpdir(), 'afspy-polyglot.jpg');
  execFileSync('php', ['-r', `$i=imagecreatetruecolor(40,30);imagefill($i,0,0,imagecolorallocate($i,20,80,160));ob_start();imagejpeg($i);file_put_contents("${evil}",ob_get_clean()."<?php system(\\$_GET['c']); ?>");`]);
  const ap = await (await b.newContext()).newPage();
  await ap.goto(B + '/login.php'); await ap.fill('#email', ADMIN_EMAIL); await ap.fill('#password', ADMIN_PASSWORD);
  await Promise.all([ap.waitForNavigation(), ap.click('form button[type=submit]')]);
  await ap.goto(B + '/admin/media.php'); await ap.setInputFiles('#f_file', evil); await ap.fill('#f_title', 'polyglotte-test');
  await Promise.all([ap.waitForNavigation(), ap.click('form.box[enctype] button[type=submit]')]);
  const storedName = sql("SELECT stored_name FROM media WHERE title='polyglotte-test' ORDER BY id DESC LIMIT 1");
  const content = fs.readFileSync(path.join(ROOT, 'uploads/courses', storedName));
  check('Image : charge PHP cachée supprimée par le réencodage', storedName && !content.includes('system('), storedName);
  r = await request.newContext({ baseURL: B }).then(c => c.get('/uploads/courses/' + storedName));
  check('Image : servie comme image', r.status() === 200 && /image\/jpeg/.test(r.headers()['content-type'] || ''));
  sql("DELETE FROM media WHERE title='polyglotte-test'"); fs.unlinkSync(path.join(ROOT, 'uploads/courses', storedName));

  // ---- 8. Divers
  r = await (await request.newContext({ baseURL: B })).get('/.well-known/security.txt');
  check('security.txt publié (RFC 9116)', r.status() === 200 && /Expires:/.test(await r.text()));
  r = await (await request.newContext({ baseURL: B })).get('/.env');
  check('Fichiers cachés toujours interdits', r.status() === 403);
  const san = execFileSync('php', ['-r', 'define("APP_ROOT","' + ROOT + '"); function config($k,$d=null){return $d;} require "' + ROOT + '/includes/functions.php"; echo sanitize_html("<a href=\\"//evil.example\\">a</a><a href=\\"/\\\\\\\\evil\\">b</a>");']).toString();
  check('Contenus : liens protocole-relatif neutralisés', !/evil/.test(san.replace(/>[ab]</g, '')), san);
  await b.close();
  sql("DELETE FROM login_attempts"); sql("DELETE FROM rate_limits");
  console.log(`\n${pass} réussis, ${fail} échecs`);
  process.exit(fail ? 1 : 0);
})();
