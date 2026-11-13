// Tests de la double authentification : code par e-mail, appareils de confiance, politique
// d'obligation, alertes de sécurité, désactivation et réinitialisation.
const { request } = require('playwright');
const { execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const MAILDIR = path.join(__dirname, '..', 'storage/mail');
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
let pass = 0, fail = 0;
const check = (n, c, x = '') => { c ? pass++ : fail++; console.log((c ? '  PASS ' : '  FAIL ') + n + (x ? ' : ' + x : '')); };
const token = h => (h.match(/name="_csrf" value="([^"]+)"/) || [])[1];
const PWD = 'Vigie-Sahel-73';

const seen = new Set(fs.readdirSync(MAILDIR));
/** Nouveaux e-mails (mode journal) depuis le dernier appel, filtrés par destinataire et type. */
function newMails(to, tag) {
  const out = [];
  for (const f of fs.readdirSync(MAILDIR).filter(f => f.endsWith('.eml') && !seen.has(f)).sort()) {
    seen.add(f);
    const m = fs.readFileSync(path.join(MAILDIR, f), 'latin1');
    if (m.includes('To: ' + to) && (!tag || m.includes('X-AFSPY-Tag: ' + tag))) out.push(m);
  }
  return out;
}
function textPart(eml) {
  const p = eml.split(/\r?\n--/).find(x => /Content-Type: text\/plain/.test(x));
  return p ? Buffer.from(p.split(/\r?\n\r?\n/).slice(1).join('').replace(/[^A-Za-z0-9+/=]/g, ''), 'base64').toString('utf8') : '';
}
const codeOf = eml => (textPart(eml).match(/: (\d{6})\b/) || [])[1];

async function form(ctx, page, data, opts = {}) {
  const t = token(await (await ctx.get(page)).text());
  return ctx.post(page, { form: { _csrf: t, ...data }, ...opts });
}
async function register(ctx, email) {
  return form(ctx, '/register.php', { first_name: 'Mariam', last_name: 'Diallo', email, password: PWD, password_confirm: PWD, charter: '1' });
}
const login = (ctx, email, pwd = PWD) => form(ctx, '/login.php', { email, password: pwd }, { maxRedirects: 0 });
const verify = (ctx, code, extra = {}) => form(ctx, '/login-2fa.php', { code, ...extra }, { maxRedirects: 0 });
const loc = r => r.headers().location || '';

(async () => {
  sql("DELETE FROM rate_limits"); sql("DELETE FROM login_attempts");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  sql("UPDATE settings SET setting_value = '' WHERE setting_key = 'mail_transport'");
  sql("UPDATE settings SET setting_value = 'optional' WHERE setting_key = 'mfa_policy'");
  sql("UPDATE settings SET setting_value = '30' WHERE setting_key = 'mfa_trust_days'");
  sql("UPDATE settings SET setting_value = '1' WHERE setting_key IN ('mfa_email_enabled','mfa_notify')");
  const stamp = Date.now();
  const email = `mfa-mail${stamp}@example.org`;

  // ---- 1. Activation de la méthode « code par e-mail »
  const a = await request.newContext({ baseURL: B });
  await register(a, email); newMails(email);
  let h = await (await a.get('/learner/security.php')).text();
  check('Choix de la méthode proposé (application ou e-mail)', /Configurer l'application/.test(h) && /Recevoir un code de confirmation/.test(h));
  await form(a, '/learner/security.php', { action: 'start_email' });
  let m = newMails(email, 'mfa-code');
  const setupCode = m[0] && codeOf(m[0]);
  check('Activation : code de confirmation envoyé par e-mail', /^\d{6}$/.test(setupCode || ''));
  check('Code stocké haché, jamais en clair', !sql(`SELECT code_hash FROM mfa_email_codes c JOIN users u ON u.id = c.user_id WHERE u.email = '${email}'`).includes(setupCode));
  const wrong = setupCode === '000000' ? '111111' : '000000';
  await form(a, '/learner/security.php', { action: 'confirm_email', code: wrong, password: PWD });
  check('Activation : code faux refusé', sql(`SELECT totp_enabled FROM users WHERE email = '${email}'`) === '0');
  await form(a, '/learner/security.php', { action: 'confirm_email', code: setupCode, password: 'mauvais-mdp-1' });
  check('Activation : mot de passe exigé', sql(`SELECT totp_enabled FROM users WHERE email = '${email}'`) === '0');
  let r = await form(a, '/learner/security.php', { action: 'confirm_email', code: setupCode, password: PWD });
  h = await r.text();
  const recovery = [...h.matchAll(/<li>([A-Z0-9]{5}-[A-Z0-9]{5})<\/li>/g)].map(x => x[1]);
  check('Activation : méthode e-mail enregistrée, 8 codes de secours', sql(`SELECT CONCAT(totp_enabled, mfa_method) FROM users WHERE email = '${email}'`) === '1email' && recovery.length === 8);
  check('Alerte e-mail « double authentification activée »', newMails(email, 'mfa-enabled').length === 1);

  // ---- 2. Connexion avec code par e-mail
  let c = await request.newContext({ baseURL: B });
  r = await login(c, email);
  check('Connexion : mot de passe seul renvoie vers la vérification', /login-2fa/.test(loc(r)));
  m = newMails(email, 'mfa-code');
  let code = m[0] && codeOf(m[0]);
  check('Connexion : code envoyé automatiquement par e-mail', /^\d{6}$/.test(code || ''));
  h = await (await c.get('/login-2fa.php')).text();
  check('Page de vérification : adresse masquée et option « appareil de confiance »', /m\*+@example\.org/.test(h) && /Faire confiance à cet appareil pendant 30 jours/.test(h));
  r = await c.get('/learner/dashboard.php', { maxRedirects: 0 });
  check('Espace inaccessible avant le second facteur', /login\.php/.test(loc(r)));
  r = await verify(c, code === '000000' ? '111111' : '000000');
  check('Code faux refusé', /Code invalide ou expiré/.test(await r.text()));
  // Renvoi : l'ancien code n'est plus valable
  await form(c, '/login-2fa.php', { action: 'resend' });
  const code2 = codeOf(newMails(email, 'mfa-code')[0] || '');
  r = await verify(c, code);
  check('Renvoi : seul le dernier code est valable', code2 && code2 !== code ? /Code invalide/.test(await r.text()) : true);
  r = await verify(c, code2);
  check('Code valide : connexion établie', r.status() === 303 && /dashboard/.test(loc(r)));
  check('Journal : connexion tracée avec la méthode', /Code par e-mail/.test(sql(`SELECT details FROM audit_logs WHERE action = 'auth.login' AND user_id = (SELECT id FROM users WHERE email = '${email}') ORDER BY id DESC LIMIT 1`)));
  // Code déjà utilisé
  c = await request.newContext({ baseURL: B });
  await login(c, email); newMails(email);
  r = await verify(c, code2);
  check('Code déjà utilisé refusé', /Code invalide/.test(await r.text()));
  // Expiration
  await form(c, '/login-2fa.php', { action: 'resend' });
  const code3 = codeOf(newMails(email, 'mfa-code')[0] || '');
  sql(`UPDATE mfa_email_codes SET expires_at = UTC_TIMESTAMP() - INTERVAL 1 MINUTE WHERE user_id = (SELECT id FROM users WHERE email = '${email}')`);
  r = await verify(c, code3);
  check('Code expiré (10 min) refusé', /Code invalide ou expiré/.test(await r.text()));
  // Épuisement des essais : 5 erreurs annulent le code
  c = await request.newContext({ baseURL: B });
  await login(c, email);
  const code4 = codeOf(newMails(email, 'mfa-code')[0] || '');
  sql(`UPDATE mfa_email_codes SET tries = 5 WHERE user_id = (SELECT id FROM users WHERE email = '${email}')`);
  r = await verify(c, code4);
  check('Code annulé après 5 erreurs, même juste', /Code invalide/.test(await r.text()));

  // ---- 3. Appareil de confiance
  sql("DELETE FROM rate_limits");
  const d = await request.newContext({ baseURL: B });
  await login(d, email);
  r = await verify(d, codeOf(newMails(email, 'mfa-code')[0] || ''), { trust: '1' });
  const ck = (await d.storageState()).cookies.find(x => /AFSPY_TD$/.test(x.name));
  check('Confiance : cookie posé (HttpOnly, SameSite=Lax)', !!ck && ck.httpOnly && ck.sameSite === 'Lax' && /^[a-f0-9]{24}\.[a-f0-9]{64}$/.test(ck.value));
  check('Confiance : validateur stocké haché', !sql(`SELECT token_hash FROM mfa_trusted_devices WHERE selector = '${ck && ck.value.split('.')[0]}'`).includes(ck && ck.value.split('.')[1]));
  check('Alerte e-mail « nouvel appareil de confiance »', newMails(email, 'mfa-device_trusted').length === 1);
  await d.get('/logout.php');
  await form(d, '/logout.php', {});
  const d2 = await request.newContext({ baseURL: B, storageState: { cookies: [ck], origins: [] } });
  r = await login(d2, email);
  check('Confiance : plus de code demandé sur cet appareil', /dashboard/.test(loc(r)) && newMails(email, 'mfa-code').length === 0);
  const forged = { ...ck, value: ck.value.split('.')[0] + '.' + 'f'.repeat(64) };
  const d3 = await request.newContext({ baseURL: B, storageState: { cookies: [forged], origins: [] } });
  r = await login(d3, email);
  check('Confiance : cookie falsifié ignoré', /login-2fa/.test(loc(r)));
  // Le cookie d'un compte ne vaut pas pour un autre
  const other = `mfa-other${stamp}@example.org`;
  const o = await request.newContext({ baseURL: B });
  await register(o, other); newMails(other);
  await form(o, '/learner/security.php', { action: 'start_email' });
  await form(o, '/learner/security.php', { action: 'confirm_email', code: codeOf(newMails(other, 'mfa-code')[0] || ''), password: PWD });
  const d4 = await request.newContext({ baseURL: B, storageState: { cookies: [ck], origins: [] } });
  r = await login(d4, other);
  check('Confiance : cookie d\'un autre compte sans effet', /login-2fa/.test(loc(r)));
  // Liste et retrait
  h = await (await d2.get('/learner/security.php')).text();
  check('Sécurité du compte : appareil listé (« Cet appareil »)', /Appareils de confiance/.test(h) && /Cet appareil/.test(h));
  await form(d2, '/learner/security.php', { action: 'forget_all' });
  const d5 = await request.newContext({ baseURL: B, storageState: { cookies: [ck], origins: [] } });
  r = await login(d5, email);
  check('Retrait des appareils : le code est de nouveau demandé', /login-2fa/.test(loc(r)));

  // ---- 4. Code de secours : alerte
  newMails(email);
  r = await verify(d5, recovery[0]);
  check('Code de secours accepté', /dashboard/.test(loc(r)));
  const rm = newMails(email, 'mfa-recovery_used');
  check('Alerte e-mail « code de secours utilisé » (codes restants)', rm.length === 1 && /Il vous en reste 7/.test(textPart(rm[0])));

  // ---- 5. Politique d'obligation
  const adm = await request.newContext({ baseURL: B });
  await login(adm, ADMIN_EMAIL, ADMIN_PASSWORD);
  r = await adm.post('/admin/settings.php', { form: { action: 'mfa', mfa_policy: 'all', mfa_trust_days: '30' }, maxRedirects: 0 });
  check('Paramètres 2FA : refus sans jeton CSRF', r.status() >= 400 && sql("SELECT setting_value FROM settings WHERE setting_key='mfa_policy'") === 'optional');
  const adminHas2fa = sql(`SELECT totp_enabled FROM users WHERE email = '${ADMIN_EMAIL}'`) === '1';
  r = await form(adm, '/admin/settings.php', { action: 'mfa', mfa_policy: 'all', mfa_trust_days: '30', mfa_email_enabled: '1', mfa_notify: '1' });
  if (!adminHas2fa) {
    check('Paramètres 2FA : imposer sans l\'avoir soi-même activée est refusé', sql("SELECT setting_value FROM settings WHERE setting_key='mfa_policy'") === 'optional');
    sql("UPDATE settings SET setting_value='all' WHERE setting_key='mfa_policy'");
  }
  const l = await request.newContext({ baseURL: B });
  const plain = `mfa-none${stamp}@example.org`;
  await register(l, plain);
  r = await l.get('/learner/dashboard.php', { maxRedirects: 0 });
  check('Obligation « tous » : apprenant sans 2FA redirigé vers la sécurité', /learner\/security\.php/.test(loc(r)));
  r = await l.get('/learner/courses.php', { maxRedirects: 0 });
  check('Obligation : toutes les pages bloquées', /learner\/security\.php/.test(loc(r)));
  const lt = token(await (await l.get('/learner/security.php')).text());
  r = await l.post('/ajax/note.php', { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': lt }, form: { lesson_id: '1', body: 'x' } });
  check('Obligation : requêtes AJAX refusées (403)', r.status() === 403);
  h = await (await l.get('/learner/security.php')).text();
  check('Obligation : page de sécurité accessible avec message', /Action requise/.test(h));
  h = await (await a.get('/learner/security.php')).text();
  check('Obligation : désactivation impossible, changement de méthode possible', !/value="disable"/.test(h) && /Changer de méthode/.test(h));
  sql("UPDATE settings SET setting_value='optional' WHERE setting_key='mfa_policy'");
  r = await l.get('/learner/dashboard.php', { maxRedirects: 0 });
  check('Politique facultative : accès normal', r.status() === 200);

  // ---- 6. Désactivation (mot de passe + code e-mail)
  sql("DELETE FROM rate_limits");
  newMails(email);
  await form(a, '/learner/security.php', { action: 'send_code' });
  const dc = codeOf(newMails(email, 'mfa-code')[0] || '');
  await form(a, '/learner/security.php', { action: 'disable', code: dc, password: 'mauvais-mdp-2' });
  check('Désactivation : mot de passe exigé', sql(`SELECT totp_enabled FROM users WHERE email = '${email}'`) === '1');
  await form(a, '/learner/security.php', { action: 'disable', code: dc, password: PWD });
  check('Désactivation : 2FA, codes et appareils effacés', sql(`SELECT CONCAT(totp_enabled, COALESCE(mfa_method,'-'), COALESCE(totp_recovery,'-')) FROM users WHERE email = '${email}'`) === '0--'
    && sql(`SELECT COUNT(*) FROM mfa_trusted_devices WHERE user_id = (SELECT id FROM users WHERE email = '${email}')`) === '0');
  check('Alerte e-mail « double authentification désactivée »', newMails(email, 'mfa-disabled').length === 1);

  // ---- 7. Réinitialisation par un administrateur
  const oid = sql(`SELECT id FROM users WHERE email = '${other}'`);
  await form(adm, '/admin/users.php?edit=' + oid, { action: 'reset_mfa', id: oid });
  check('Administration : réinitialisation de la 2FA', sql(`SELECT totp_enabled FROM users WHERE id = ${oid}`) === '0');
  check('Alerte e-mail à l\'utilisateur après réinitialisation', newMails(other, 'mfa-admin_reset').length === 1);

  // ---- 8. Désactivation des appareils de confiance par l'administration
  sql("UPDATE settings SET setting_value='0' WHERE setting_key='mfa_trust_days'");
  const t1 = await request.newContext({ baseURL: B });
  await login(t1, other); // 2FA réinitialisée : connexion directe
  await form(t1, '/learner/security.php', { action: 'start_email' });
  await form(t1, '/learner/security.php', { action: 'confirm_email', code: codeOf(newMails(other, 'mfa-code')[0] || ''), password: PWD });
  const t2 = await request.newContext({ baseURL: B });
  await login(t2, other);
  h = await (await t2.get('/login-2fa.php')).text();
  check('Durée de confiance 0 : option masquée', !/Faire confiance/.test(h));
  sql("UPDATE settings SET setting_value='30' WHERE setting_key='mfa_trust_days'");

  console.log(`\n${pass} réussis, ${fail} échoués`);
  process.exit(fail ? 1 : 0);
})().catch(e => { console.error(e); process.exit(1); });
