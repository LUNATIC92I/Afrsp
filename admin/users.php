<?php
/** Administration — utilisateurs, rôles et statuts. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
$me = require_permission('users.manage');

$roles = array_column(q_all('SELECT id, name FROM roles ORDER BY id'), 'name', 'id');
$adminRole = (int) q_val("SELECT id FROM roles WHERE slug = 'admin'");
if (is_post()) {
    verify_csrf();
    $id = input_int('id');
    $action = input('action');
    $isLastAdmin = static fn (int $uid) => (int) q_val('SELECT role_id FROM users WHERE id = ?', [$uid]) === $adminRole && (int) q_val("SELECT COUNT(*) FROM users WHERE role_id = ? AND status = 'active'", [$adminRole]) <= 1;
    if ($action === 'save') {
        $d = ['first_name' => input('first_name'), 'last_name' => input('last_name'), 'email' => mb_strtolower(input('email')), 'role_id' => input('role_id'), 'status' => input('status'), 'password' => (string) ($_POST['password'] ?? '')];
        $err = validate($d, ['first_name' => 'required|name|max:80', 'last_name' => 'required|name|max:80', 'email' => 'required|email|max:190', 'role_id' => 'required|int', 'status' => 'required|in:active,suspended'],
            ['first_name' => 'Prénom', 'last_name' => 'Nom', 'email' => 'E-mail', 'role_id' => 'Rôle', 'status' => 'Statut']);
        if (!isset($roles[(int) $d['role_id']])) $err['role_id'] = 'Rôle invalide.';
        if (q_val('SELECT 1 FROM users WHERE email = ? AND id <> ?', [$d['email'], $id])) $err['email'] = 'Adresse déjà utilisée.';
        if ((!$id || $d['password'] !== '') && ($p = password_policy_error_for($d['password'], [$d['email'], $d['first_name'], $d['last_name']]))) $err['password'] = $p;
        if ($id && $id === (int) $me['id'] && ((int) $d['role_id'] !== (int) $me['role_id'] || $d['status'] !== 'active')) $err[] = 'Vous ne pouvez pas modifier votre propre rôle ou statut.';
        if ($id && $isLastAdmin($id) && ((int) $d['role_id'] !== $adminRole || $d['status'] !== 'active')) $err[] = 'Impossible : c\'est le dernier administrateur actif.';
        if ($err) { flash('error', implode(' ', $err)); set_old($_POST); back(['edit' => $id ?: 'new']); }
        if ($id) {
            q('UPDATE users SET first_name=?, last_name=?, email=?, role_id=?, status=? WHERE id=?', [$d['first_name'], $d['last_name'], $d['email'], (int) $d['role_id'], $d['status'], $id]);
            if ($d['password'] !== '') q('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($d['password']), $id]);
            audit('user.update', 'user', $id, $d['email'] . ' · rôle=' . $roles[(int) $d['role_id']] . ' · ' . $d['status'] . ($d['password'] !== '' ? ' · mot de passe réinitialisé' : ''));
        } else {
            q('INSERT INTO users (first_name, last_name, email, role_id, status, password_hash) VALUES (?,?,?,?,?,?)', [$d['first_name'], $d['last_name'], $d['email'], (int) $d['role_id'], $d['status'], hash_password($d['password'])]);
            $id = (int) db()->lastInsertId();
            audit('user.create', 'user', $id, $d['email'] . ' · ' . $roles[(int) $d['role_id']]);
        }
        flash('success', 'Utilisateur enregistré.');
        back(['edit' => $id]);
    }
    if ($action === 'delete' && $id) {
        if ($id === (int) $me['id'] || $isLastAdmin($id)) {
            flash('error', 'Suppression refusée (votre propre compte ou dernier administrateur).');
        } else {
            $email = q_val('SELECT email FROM users WHERE id = ?', [$id]);
            q('DELETE FROM users WHERE id = ?', [$id]);
            audit('user.delete', 'user', $id, (string) $email);
            flash('success', 'Compte supprimé (inscriptions, résultats et certificats associés supprimés).');
        }
        back();
    }
    if ($action === 'reset_mfa' && $id) {
        q('UPDATE users SET totp_secret = NULL, totp_enabled = 0, totp_last_step = NULL, totp_recovery = NULL WHERE id = ?', [$id]);
        audit('security.mfa_reset_by_admin', 'user', $id);
        flash('success', 'Double authentification réinitialisée : l\'utilisateur devra la reconfigurer.');
        back(['edit' => $id]);
    }
    if ($action === 'unlock' && $id) {
        q('DELETE FROM login_attempts WHERE email = (SELECT email FROM users WHERE id = ?)', [$id]);
        q('DELETE FROM rate_limits WHERE bucket LIKE ?', ['mfa:user:' . hash('sha256', (string) $id)]);
        audit('user.unlock', 'user', $id);
        flash('success', 'Verrouillage anti force brute levé pour ce compte.');
        back(['edit' => $id]);
    }
    back();
}

$appArea = 'admin';
$activeApp = 'users';
$editId = input('edit');
if ($editId !== '') {
    $u = ctype_digit($editId) ? q_one('SELECT * FROM users WHERE id = ?', [(int) $editId]) : null;
    if ($editId !== 'new' && !$u) render_error_page(404, 'Utilisateur introuvable', 'Ce compte n\'existe pas.');
    $enr = $u ? q_all('SELECT ce.*, c.code, c.title FROM course_enrollments ce JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = ? ORDER BY ce.enrolled_at DESC', [$u['id']]) : [];
    $att = $u ? q_all('SELECT ea.*, c.code FROM exam_attempts ea JOIN exams e ON e.id = ea.exam_id JOIN courses c ON c.id = e.course_id WHERE ea.user_id = ? ORDER BY ea.started_at DESC LIMIT 20', [$u['id']]) : [];
    $fails = $u ? (int) q_val('SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND attempted_at > UTC_TIMESTAMP() - INTERVAL 15 MINUTE', [$u['email']]) : 0;
    $v = static fn ($k, $d = '') => old($k, (string) ($u[$k] ?? $d));
    $pageTitle = $u ? $u['first_name'] . ' ' . $u['last_name'] : 'Nouvel utilisateur';
    $crumb = 'Utilisateurs';
    require APP_ROOT . '/includes/app-header.php'; ?>
<div class="grid-2">
  <form class="box reveal" method="post" novalidate>
    <div class="box__head"><h2><?= $u ? 'Compte' : 'Créer un compte' ?></h2><?php if ($u): ?><span class="mono">Inscrit le <?= e(fmt_date($u['created_at'], false)) ?></span><?php endif; ?></div>
    <div class="box__body form-grid">
      <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($u['id'] ?? 0) ?>">
      <?= fld('first_name', 'Prénom', $v('first_name'), 'text', ['required' => true, 'maxlength' => 80]) ?>
      <?= fld('last_name', 'Nom', $v('last_name'), 'text', ['required' => true, 'maxlength' => 80]) ?>
      <?= fld('email', 'E-mail', $v('email'), 'email', ['required' => true, 'maxlength' => 190, 'data-full' => true]) ?>
      <?= sel('role_id', 'Rôle', $roles, $v('role_id', (string) q_val("SELECT id FROM roles WHERE slug='learner'"))) ?>
      <?= sel('status', 'Statut', ['active' => 'Actif', 'suspended' => 'Suspendu'], $v('status', 'active')) ?>
      <?= fld('password', $u ? 'Nouveau mot de passe (laisser vide pour conserver)' : 'Mot de passe initial', '', 'password', ['autocomplete' => 'new-password', 'data-full' => true], '10 caractères minimum, lettres et chiffres. Communiquez-le par un canal sûr.') ?>
      <div class="full flex"><button class="btn btn--primary" type="submit">Enregistrer</button><a class="btn" href="<?= e(url('admin/users.php')) ?>">Retour</a></div>
    </div>
  </form>
  <?php if ($u): ?>
  <section class="box reveal"><div class="box__head"><h2>Activité</h2></div><div class="box__body">
    <dl class="kv"><dt>Dernière connexion</dt><dd><?= e(fmt_datetime($u['last_login_at'])) ?></dd><dt>Pays</dt><dd><?= e($u['country'] ?: '—') ?></dd><dt>Organisation</dt><dd><?= e($u['organization'] ?: '—') ?></dd><dt>Double authentification</dt><dd><?= (int) $u['totp_enabled'] ? '<span class="status-pill status-pill--on">● Activée</span> ' . action_btn('reset_mfa', ['id' => $u['id']], 'Réinitialiser', 'btn--sm', 'Réinitialiser la 2FA de cet utilisateur (téléphone perdu) ? Vérifiez son identité avant de confirmer.') : '<span class="status-pill status-pill--off">● Non activée</span>' ?></dd><dt>Échecs récents</dt><dd><?= $fails ?><?php if ($fails): ?> <?= action_btn('unlock', ['id' => $u['id']], 'Déverrouiller') ?><?php endif; ?></dd></dl>
    <h3 class="mt-2" style="font-size:1rem">Formations (<?= count($enr) ?>)</h3>
    <ul class="feed"><?php foreach ($enr as $e): ?><li><span><?= e($e['code']) ?> · <?= e($e['title']) ?></span><time><?= (int) $e['progress'] ?> %<?= $e['status'] === 'completed' ? ' ✓' : '' ?></time></li><?php endforeach; ?></ul>
    <h3 class="mt-2" style="font-size:1rem">Tentatives d'examen</h3>
    <ul class="feed"><?php foreach ($att as $a): ?><li><span><?= e($a['code']) ?> · <?= e(fmt_datetime($a['started_at'])) ?></span><time><?= $a['submitted_at'] ? e(number_format((float) $a['score'], 0)) . ' %' : 'en cours' ?></time></li><?php endforeach; ?></ul>
    <div class="mt-2"><?= action_btn('delete', ['id' => $u['id']], 'Supprimer le compte', 'btn--sm btn--danger', 'Supprimer définitivement ce compte et toutes ses données (inscriptions, résultats, certificats) ?') ?></div>
  </div></section>
  <?php endif; ?>
</div>
<?php require APP_ROOT . '/includes/app-footer.php'; exit;
}

$q = input('q');
$role = input_int('role');
$where = '1=1';
$p = [];
if ($q !== '') { $where .= ' AND (u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)'; array_push($p, "%$q%", "%$q%", "%$q%"); }
if ($role) { $where .= ' AND u.role_id = ?'; $p[] = $role; }
$pg = paginate((int) q_val("SELECT COUNT(*) FROM users u WHERE $where", $p), 25);
$rows = q_all("SELECT u.*, r.name AS role_name, (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.user_id = u.id) enr, (SELECT COUNT(*) FROM certificates c WHERE c.user_id = u.id) certs
                 FROM users u JOIN roles r ON r.id = u.role_id WHERE $where ORDER BY u.created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}", $p);
$pageTitle = 'Utilisateurs';
require APP_ROOT . '/includes/app-header.php'; ?>
<div class="toolbar reveal">
  <form class="toolbar grow" method="get"><input class="input grow" name="q" value="<?= e($q) ?>" placeholder="Nom ou e-mail">
    <select class="select" name="role"><option value="">Tous les rôles</option><?php foreach ($roles as $rid => $rn): ?><option value="<?= $rid ?>" <?= $role === $rid ? 'selected' : '' ?>><?= e($rn) ?></option><?php endforeach; ?></select>
    <button class="btn btn--sm" type="submit">Filtrer</button></form>
  <a class="btn btn--primary btn--sm" href="<?= e(url('admin/users.php', ['edit' => 'new'])) ?>">+ Nouvel utilisateur</a>
</div>
<section class="box reveal"><div class="box__head"><h2>Comptes</h2><span class="mono"><?= $pg['total'] ?> résultat(s)</span></div><div class="table-wrap"><table class="table">
  <thead><tr><th>Utilisateur</th><th>Rôle</th><th class="num">Formations</th><th class="num">Certificats</th><th>Inscription</th><th>Statut</th><th></th></tr></thead>
  <tbody><?php foreach ($rows as $r): ?>
    <tr><td><strong><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong><br><small class="dim"><?= e($r['email']) ?></small></td><td><?= e($r['role_name']) ?></td><td class="num"><?= (int) $r['enr'] ?></td><td class="num"><?= (int) $r['certs'] ?></td><td><?= e(fmt_date($r['created_at'], false)) ?></td>
      <td><?= $r['status'] === 'active' ? '<span class="status-pill status-pill--on">● Actif</span>' : '<span class="status-pill status-pill--bad">● Suspendu</span>' ?></td>
      <td><div class="actions"><a class="btn btn--sm" href="<?= e(url('admin/users.php', ['edit' => $r['id']])) ?>">Gérer</a></div></td></tr>
  <?php endforeach; ?></tbody></table></div></section>
<?= pagination_links($pg, ['q' => $q, 'role' => $role ?: null]) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
