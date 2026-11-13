// Tests : e-mail de bienvenue, e-mail de confirmation avec facture PDF, avoir, registre,
// configuration SMTP depuis l'administration (serveur SMTP local de test), contrôles d'accès.
const { request } = require('playwright');
const { execFileSync } = require('child_process');
const net = require('net');
const fs = require('fs');
const os = require('os');
const path = require('path');
const B = process.env.BASE_URL || 'http://127.0.0.1:8080';
const ROOT = path.join(__dirname, '..');
const MAILDIR = path.join(ROOT, 'storage/mail');
const ADMIN_EMAIL = process.env.ADMIN_EMAIL || 'admin@africaspy.test';
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD || 'Admin-Test-2026';
const sql = q => execFileSync('mysql', ['--default-character-set=utf8mb4', '-N', 'africaspy', '-e', q]).toString().trim();
let pass = 0, fail = 0;
const check = (n, c, x = '') => { c ? pass++ : fail++; console.log((c ? '  PASS ' : '  FAIL ') + n + (x ? ' — ' + x : '')); };
const token = h => (h.match(/name="_csrf" value="([^"]+)"/) || [])[1];
const PWD = 'Factura-Kq83vz';

async function register(ctx, email, first = 'Awa') {
  const t = token(await (await ctx.get('/register.php')).text());
  return ctx.post('/register.php', { form: { _csrf: t, first_name: first, last_name: 'Testfacture', email, password: PWD, password_confirm: PWD, charter: '1' } });
}
async function login(ctx, email, pwd) {
  const t = token(await (await ctx.get('/login.php')).text());
  return ctx.post('/login.php', { form: { _csrf: t, email, password: pwd } });
}
async function buy(ctx, slug) {
  const t = token(await (await ctx.get('/checkout.php?course=' + slug)).text());
  const r = await ctx.post('/checkout.php?course=' + slug, { form: { _csrf: t, method: 'sandbox', terms: '1' }, maxRedirects: 0 });
  const loc = r.headers().location || '';
  const ref = decodeURIComponent((loc.match(/ref=([^&]+)/) || [])[1] || '');
  const t2 = token(await (await ctx.get('/payment-sandbox.php?ref=' + encodeURIComponent(ref))).text());
  await ctx.post('/payment-sandbox.php?ref=' + encodeURIComponent(ref), { form: { _csrf: t2, outcome: 'success' } });
  return ref;
}
// Messages .eml du mode journal adressés à $to (et éventuellement d'un type donné)
function mails(to, tag) {
  return fs.readdirSync(MAILDIR).filter(f => f.endsWith('.eml')).map(f => fs.readFileSync(path.join(MAILDIR, f), 'latin1'))
    .filter(m => m.includes('To: ' + to) && (!tag || m.includes('X-AFSPY-Tag: ' + tag)));
}
function attachment(eml, re) {
  const parts = eml.split(/\r?\n--/);
  const p = parts.find(x => re.test(x) && /Content-Transfer-Encoding: base64/i.test(x));
  if (!p) return null;
  const body = p.split(/\r?\n\r?\n/).slice(1).join('\n').replace(/[^A-Za-z0-9+/=]/g, '');
  return Buffer.from(body, 'base64');
}
function pdfText(buf) {
  const f = path.join(os.tmpdir(), 'afspy-' + Date.now() + Math.random() + '.pdf');
  fs.writeFileSync(f, buf);
  try { return execFileSync('pdftotext', ['-layout', f, '-']).toString(); } finally { fs.unlinkSync(f); }
}
// Serveur SMTP minimal (127.0.0.1:2525) : enregistre les messages reçus
function smtpSink(port) {
  const received = [];
  const server = net.createServer(sock => {
    let data = false, buf = '', msg = { rcpt: [] };
    sock.write('220 sink ESMTP\r\n');
    sock.on('data', d => {
      buf += d.toString('latin1');
      let i;
      while ((i = buf.indexOf('\r\n')) >= 0) {
        if (data) {
          const end = buf.indexOf('\r\n.\r\n');
          if (end < 0) return;
          msg.data = buf.slice(0, end); buf = buf.slice(end + 5); data = false;
          received.push(msg); msg = { rcpt: [] }; sock.write('250 OK queued\r\n'); continue;
        }
        const line = buf.slice(0, i); buf = buf.slice(i + 2);
        const cmd = line.slice(0, 4).toUpperCase();
        if (cmd === 'EHLO') sock.write('250-sink\r\n250 8BITMIME\r\n');
        else if (cmd === 'HELO') sock.write('250 sink\r\n');
        else if (cmd === 'MAIL') { msg.from = line; sock.write('250 OK\r\n'); }
        else if (cmd === 'RCPT') { msg.rcpt.push(line); sock.write('250 OK\r\n'); }
        else if (cmd === 'DATA') { data = true; sock.write('354 go\r\n'); }
        else if (cmd === 'QUIT') { sock.end('221 bye\r\n'); }
        else sock.write('250 OK\r\n');
      }
    });
    sock.on('error', () => {});
  });
  return new Promise(res => server.listen(port, '127.0.0.1', () => res({ server, received })));
}

(async () => {
  sql("DELETE FROM rate_limits"); sql("DELETE FROM login_attempts");
  sql("DELETE FROM audit_logs WHERE action = 'auth.register' AND ip_address IN ('127.0.0.1','::1')");
  sql("UPDATE settings SET setting_value = '' WHERE setting_key IN ('mail_transport')"); sql("UPDATE settings SET setting_value = '0' WHERE setting_key = 'tax_rate'");
  sql("UPDATE settings SET setting_value = '1' WHERE setting_key = 'welcome_mail_enabled'");
  const slug = sql("SELECT slug FROM courses WHERE status = 'published' AND price_xof > 0 ORDER BY sort_order, id LIMIT 1");
  const stamp = Date.now();

  // ---- 1. E-mail de bienvenue
  const email = `facture${stamp}@example.org`;
  const ctx = await request.newContext({ baseURL: B });
  await register(ctx, email);
  const w = mails(email, 'welcome');
  check('Bienvenue : un e-mail est émis à l\'inscription', w.length === 1);
  check('Bienvenue : objet, version HTML et texte, logo intégré', w[0] && /Subject: =\?UTF-8\?/.test(w[0]) && /text\/html/.test(w[0]) && /text\/plain/.test(w[0]) && /Content-ID: <afspy-logo>/.test(w[0]));
  const html = w[0] ? (attachment(w[0], /Content-Type: text\/html/) || '').toString('utf8') : '';
  check('Bienvenue : contenu personnalisé et lien vers l\'espace', /Bonjour Awa/.test(html) && /learner\/dashboard\.php/.test(html) && !/<script/i.test(html));
  check('Bienvenue : aucun mot de passe dans le message', w[0] && !w[0].includes(PWD) && !html.includes(PWD));

  // ---- 2. Paiement → facture PDF jointe, numérotation continue
  const before = sql("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(invoice_no, '-', -1) AS UNSIGNED)),0) FROM invoices WHERE doc_type='invoice' AND invoice_no LIKE CONCAT('FAC-', YEAR(UTC_DATE()), '-%')");
  const ref = await buy(ctx, slug);
  const inv = sql(`SELECT i.invoice_no, i.amount_ttc_xof, i.tax_xof FROM invoices i JOIN payments p ON p.id = i.payment_id WHERE p.reference = '${ref}' AND i.doc_type = 'invoice'`).split('\t');
  const year = new Date().getUTCFullYear();
  check('Facture : émise automatiquement à la confirmation du paiement', !!inv[0], inv[0]);
  check('Facture : numéro séquentiel FAC-AAAA-NNNNNN', inv[0] === `FAC-${year}-${String(+before + 1).padStart(6, '0')}`, inv[0]);
  check('Facture : montant TTC = montant encaissé (170 000 FCFA)', inv[1] === '170000', inv[1]);
  const pm = mails(email, 'payment');
  check('Confirmation : un e-mail de paiement est envoyé', pm.length === 1);
  const pdf = pm[0] && attachment(pm[0], /application\/pdf/);
  check('Confirmation : la facture PDF est jointe', !!pdf && pdf.slice(0, 5).toString() === '%PDF-' && pm[0].includes(`Facture-${inv[0]}.pdf`));
  const txt = pdf ? pdfText(pdf) : '';
  check('PDF : numéro, client, montants et mention acquittée', txt.includes(inv[0]) && /Testfacture/.test(txt) && /170\s?000/.test(txt) && /900\s?000/.test(txt) && /ACQUITT/i.test(txt));
  check('PDF : n\'est pas présenté comme un document officiel de l\'État', !/diplôme d.État|ministère/i.test(txt));
  // idempotence : une nouvelle confirmation ne crée pas de seconde facture
  sql(`UPDATE payments SET status = 'paid' WHERE reference = '${ref}'`);
  execFileSync('php', ['-r', `require '${ROOT}/includes/bootstrap.php'; require '${ROOT}/includes/invoices.php'; issue_invoice((int) q_val('SELECT id FROM payments WHERE reference = ?', ['${ref}']));`]);
  check('Facture : émission idempotente (une seule facture par paiement)', sql(`SELECT COUNT(*) FROM invoices i JOIN payments p ON p.id = i.payment_id WHERE p.reference = '${ref}' AND doc_type = 'invoice'`) === '1');

  // ---- 3. Téléchargement et contrôles d'accès
  let r = await ctx.get('/learner/payments.php');
  check('Espace apprenant : bouton « Facture PDF »', (await r.text()).includes('learner/invoice.php?no=' + inv[0]));
  r = await ctx.get('/learner/invoice.php?no=' + inv[0]);
  check('Téléchargement : le titulaire obtient son PDF', r.status() === 200 && r.headers()['content-type'] === 'application/pdf');
  const other = await request.newContext({ baseURL: B });
  const email2 = `autre${stamp}@example.org`;
  await register(other, email2, 'Kofi');
  r = await other.get('/learner/invoice.php?no=' + inv[0]);
  check('IDOR : un autre apprenant reçoit 404', r.status() === 404);
  const anon = await request.newContext({ baseURL: B });
  r = await anon.get('/learner/invoice.php?no=' + inv[0], { maxRedirects: 0 });
  check('Anonyme : redirigé vers la connexion', [302, 303].includes(r.status()) && /login/.test(r.headers().location || ''));
  r = await other.get('/admin/invoices.php', { maxRedirects: 0 });
  check('Registre des factures interdit aux apprenants', [302, 303, 403].includes(r.status()));

  // ---- 4. Administration : registre, export CSV, avoir
  const adm = await request.newContext({ baseURL: B });
  await login(adm, ADMIN_EMAIL, ADMIN_PASSWORD);
  r = await adm.get('/admin/invoices.php?q=' + inv[0]);
  check('Registre : la facture est listée', r.status() === 200 && (await r.text()).includes(inv[0]));
  r = await adm.get('/admin/invoices.php?export=csv&q=' + inv[0]);
  const csv = await r.text();
  check('Export CSV : en-tête, séparateur « ; », ligne de la facture', /text\/csv/.test(r.headers()['content-type']) && csv.includes('Numéro;Type;Date') && csv.includes(inv[0] + ';Facture;'));
  // injection de formule neutralisée
  const origLabel = sql(`SELECT label FROM invoices WHERE invoice_no = '${inv[0]}'`);
  sql(`UPDATE invoices SET label = '=HYPERLINK("http://x")' WHERE invoice_no = '${inv[0]}'`);
  const csv2 = await (await adm.get('/admin/invoices.php?export=csv&q=' + inv[0])).text();
  check('Export CSV : formule neutralisée (injection CSV)', csv2.includes(`"'=HYPERLINK(`) && !/;"?=HYPERLINK/.test(csv2));
  sql(`UPDATE invoices SET label = '${origLabel.replace(/'/g, "''")}' WHERE invoice_no = '${inv[0]}'`);
  const pid = sql(`SELECT id FROM payments WHERE reference = '${ref}'`);
  const tp = token(await (await adm.get('/admin/payments.php')).text());
  await adm.post('/admin/payments.php', { form: { _csrf: tp, action: 'refund', id: pid, note: 'Test remboursement' } });
  const cn = sql(`SELECT i.invoice_no, i.amount_ttc_xof, r.invoice_no FROM invoices i JOIN invoices r ON r.id = i.related_id WHERE i.payment_id = ${pid} AND i.doc_type = 'credit_note'`).split('\t');
  check('Remboursement : avoir émis, montant négatif, lié à la facture', /^AV-\d{4}-\d{6}$/.test(cn[0]) && cn[1] === '-170000' && cn[2] === inv[0], cn.join(' '));
  const cm = mails(email, 'credit-note');
  check('Remboursement : avoir PDF envoyé par e-mail', cm.length === 1 && cm[0].includes(`Avoir-${cn[0]}.pdf`));
  check('Facture d\'origine conservée (jamais modifiée)', sql(`SELECT amount_ttc_xof FROM invoices WHERE invoice_no = '${inv[0]}'`) === '170000');

  // ---- 5. Paramètres : taxe, protection CSRF, SMTP réel
  let ts = token(await (await adm.get('/admin/settings.php')).text());
  r = await adm.post('/admin/settings.php', { form: { action: 'billing', tax_rate: '18' }, maxRedirects: 0 });
  check('Paramètres : refus sans jeton CSRF', r.status() >= 400 && sql("SELECT setting_value FROM settings WHERE setting_key='tax_rate'") === '0');
  await adm.post('/admin/settings.php', { form: { _csrf: ts, action: 'billing', seller_legal_name: "AFRICA'SPY Academy SARL", seller_address: 'Abidjan', seller_tax_id: 'NCC-TEST', seller_registration: 'RCCM-TEST', seller_email: '', tax_rate: '18', tax_label: 'TVA', tax_exempt_mention: '', invoice_footer: '' } });
  check('Facturation : taux enregistré', sql("SELECT setting_value FROM settings WHERE setting_key='tax_rate'") === '18');
  const ref2 = await buy(other, slug);
  const inv2 = sql(`SELECT i.invoice_no, i.amount_ht_xof, i.tax_xof, i.amount_ttc_xof FROM invoices i JOIN payments p ON p.id = i.payment_id WHERE p.reference = '${ref2}' AND i.doc_type='invoice'`).split('\t');
  check('TVA 18 % : HT 144 068 + TVA 25 932 = TTC 170 000', inv2[1] === '144068' && inv2[2] === '25932' && inv2[3] === '170000', inv2.join(' '));
  const t2 = pdfText(attachment(mails(email2, 'payment')[0], /application\/pdf/));
  check('PDF : identifiants fiscaux du vendeur et TVA', /NCC-TEST/.test(t2) && /RCCM-TEST/.test(t2) && /TVA/.test(t2) && /25\s?932/.test(t2));
  ts = token(await (await adm.get('/admin/settings.php')).text());
  await adm.post('/admin/settings.php', { form: { _csrf: ts, action: 'billing', seller_legal_name: '', seller_address: '', seller_tax_id: '', seller_registration: '', seller_email: '', tax_rate: '0', tax_label: 'TVA', tax_exempt_mention: '', invoice_footer: '' } });

  const sink = await smtpSink(2525);
  ts = token(await (await adm.get('/admin/settings.php')).text());
  r = await adm.post('/admin/settings.php', { form: { _csrf: ts, action: 'mail', mail_transport: 'smtp', mail_from: 'no-reply@africaspy.test', mail_from_name: "AFRICA'SPY", mail_reply_to: '', smtp_host: '127.0.0.1', smtp_port: '2525', smtp_secure: 'none', smtp_user: '', smtp_pass: 'Secret-Smtp-994', welcome_mail_enabled: '1' } });
  const enc = sql("SELECT setting_value FROM settings WHERE setting_key='smtp_pass_enc'");
  check('SMTP : mot de passe stocké chiffré', enc.length > 30 && !enc.includes('Secret-Smtp-994'));
  const page = await (await adm.get('/admin/settings.php')).text();
  check('SMTP : mot de passe jamais réaffiché', !page.includes('Secret-Smtp-994') && !page.includes(enc));
  const dec = execFileSync('php', ['-r', `require '${ROOT}/includes/bootstrap.php'; echo mail_config()['smtp_pass'];`]).toString();
  check('SMTP : déchiffrement correct côté serveur', dec === 'Secret-Smtp-994');
  ts = token(page);
  await adm.post('/admin/settings.php', { form: { _csrf: ts, action: 'mail_test', test_to: 'controle@example.org' } });
  check('SMTP : e-mail de test reçu par le serveur SMTP', sink.received.some(m => m.rcpt.some(x => x.includes('controle@example.org')) && m.data.includes('X-AFSPY-Tag: test')));
  const email3 = `smtp${stamp}@example.org`;
  await register(await request.newContext({ baseURL: B }), email3, 'Fatou');
  check('SMTP : l\'e-mail de bienvenue part réellement en SMTP', sink.received.some(m => m.rcpt.some(x => x.includes(email3)) && m.data.includes('X-AFSPY-Tag: welcome')));
  check('SMTP : expéditeur configuré utilisé', sink.received.every(m => m.from.includes('no-reply@africaspy.test')));
  check('Audit : modification et test de la messagerie tracés', +sql("SELECT COUNT(*) FROM audit_logs WHERE action IN ('settings.mail','settings.mail_test') AND created_at > UTC_TIMESTAMP() - INTERVAL 5 MINUTE") >= 2);

  // Désactivation de l'e-mail de bienvenue
  ts = token(await (await adm.get('/admin/settings.php')).text());
  await adm.post('/admin/settings.php', { form: { _csrf: ts, action: 'mail', mail_transport: 'smtp', mail_from: 'no-reply@africaspy.test', mail_from_name: "AFRICA'SPY", smtp_host: '127.0.0.1', smtp_port: '2525', smtp_secure: 'none', smtp_user: '', smtp_pass: '' } });
  check('SMTP : mot de passe conservé si le champ est laissé vide', sql("SELECT setting_value FROM settings WHERE setting_key='smtp_pass_enc'") === enc);
  const n = sink.received.length;
  await register(await request.newContext({ baseURL: B }), `nowelcome${stamp}@example.org`, 'Moussa');
  check('Option : bienvenue désactivée → aucun envoi', sink.received.length === n);

  // Remise en état (mode journal)
  sql("UPDATE settings SET setting_value = '' WHERE setting_key IN ('mail_transport','smtp_host','smtp_pass_enc','mail_from','smtp_user')");
  sql("UPDATE settings SET setting_value = '1' WHERE setting_key = 'welcome_mail_enabled'");
  sql("UPDATE settings SET setting_value = '587' WHERE setting_key = 'smtp_port'"); sql("UPDATE settings SET setting_value = 'tls' WHERE setting_key = 'smtp_secure'");
  sink.server.close();
  console.log(`\n${pass} réussis, ${fail} échoués`);
  process.exit(fail ? 1 : 0);
})().catch(e => { console.error(e); process.exit(1); });
