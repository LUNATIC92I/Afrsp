<?php
/** Espace apprenant — profil personnel et sécurité du compte. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';

$u = require_login();
$errors = [];
if (is_post()) {
    verify_csrf();
    if (input('action') === 'profile') {
        $d = ['first_name' => input('first_name'), 'last_name' => input('last_name'), 'country' => input('country'), 'organization' => input('organization'), 'bio' => input('bio')];
        $errors = validate($d, ['first_name' => 'required|name|max:80', 'last_name' => 'required|name|max:80', 'country' => 'max:80', 'organization' => 'max:150', 'bio' => 'max:600'],
            ['first_name' => 'Prénom', 'last_name' => 'Nom', 'country' => 'Pays', 'organization' => 'Organisation', 'bio' => 'Présentation']);
        if (!$errors) {
            q('UPDATE users SET first_name = ?, last_name = ?, country = ?, organization = ?, bio = ? WHERE id = ?',
                [$d['first_name'], $d['last_name'], $d['country'] ?: null, $d['organization'] ?: null, $d['bio'] ?: null, $u['id']]);
            audit('profile.update', 'user', (int) $u['id']);
            flash('success', 'Profil mis à jour.');
            redirect('learner/profile.php');
        }
        flash('error', 'Le profil contient des erreurs.');
    } elseif (input('action') === 'password') {
        $cur = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['password'] ?? '');
        $hash = (string) q_val('SELECT password_hash FROM users WHERE id = ?', [$u['id']]);
        if (!password_verify($cur, $hash)) {
            $errors['current_password'] = 'Mot de passe actuel incorrect.';
        } elseif ($new !== (string) ($_POST['password_confirm'] ?? '')) {
            $errors['password'] = 'La confirmation ne correspond pas.';
        } elseif ($p = password_policy_error_for($new, [$u['email'], $u['first_name'], $u['last_name']])) {
            $errors['password'] = $p;
        } else {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [hash_password($new), $u['id']]);
            login_user((int) $u['id']); // nouvelle empreinte : les autres sessions sont invalidées
            audit('profile.password', 'user', (int) $u['id']);
            flash('success', 'Mot de passe modifié. Vos autres sessions ont été fermées.');
            redirect('learner/profile.php');
        }
        flash('error', reset($errors));
    }
}
$stats = q_one('SELECT (SELECT COUNT(*) FROM course_enrollments WHERE user_id = ?) enr, (SELECT COUNT(*) FROM certificates WHERE user_id = ? AND status = \'valid\') certs', [$u['id'], $u['id']]);
$pageTitle = 'Profil';
$appArea = 'learner';
$activeApp = 'profile';
require APP_ROOT . '/includes/app-header.php';
$err = static fn ($k) => isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
?>
<section class="box reveal"><div class="box__body flex" style="gap:22px">
  <div class="avatar-xl"><?= e(initials($u['first_name'], $u['last_name'])) ?></div>
  <div>
    <h2 style="margin:0;font-size:1.4rem"><?= e($u['first_name'] . ' ' . $u['last_name']) ?></h2>
    <p class="mono dim" style="margin:4px 0 0;font-size:.75rem"><?= e($u['email']) ?> · <?= e($u['role_name']) ?> · membre depuis <?= e(fmt_date($u['created_at'])) ?></p>
    <p class="mono" style="margin:6px 0 0;font-size:.75rem;color:var(--turq)"><?= (int) $stats['enr'] ?> formation(s) · <?= (int) $stats['certs'] ?> certificat(s) · dernière connexion <?= e(fmt_datetime($u['last_login_at'])) ?></p>
  </div>
</div></section>
<div class="grid-2 grid-2--even">
  <section class="box reveal">
    <div class="box__head"><h2>Informations personnelles</h2></div>
    <form class="box__body" method="post" novalidate>
      <?= csrf_field() ?><input type="hidden" name="action" value="profile">
      <div class="form-row">
        <div class="field"><label for="first_name">Prénom</label><input class="input" id="first_name" name="first_name" value="<?= e(old('first_name', $u['first_name'])) ?>" required maxlength="80"><?= $err('first_name') ?></div>
        <div class="field"><label for="last_name">Nom</label><input class="input" id="last_name" name="last_name" value="<?= e(old('last_name', $u['last_name'])) ?>" required maxlength="80"><?= $err('last_name') ?></div>
      </div>
      <p class="hint dim" style="margin-top:-8px;font-size:.8rem">Ces nom et prénom figureront sur vos futurs certificats.</p>
      <div class="field"><label for="email_ro">E-mail</label><input class="input" id="email_ro" value="<?= e($u['email']) ?>" disabled><span class="hint">Pour modifier votre e-mail, contactez l'administration.</span></div>
      <div class="form-row">
        <div class="field"><label for="country">Pays</label><input class="input" id="country" name="country" value="<?= e($u['country'] ?? '') ?>" maxlength="80"></div>
        <div class="field"><label for="organization">Organisation</label><input class="input" id="organization" name="organization" value="<?= e($u['organization'] ?? '') ?>" maxlength="150"></div>
      </div>
      <div class="field"><label for="bio">Présentation</label><textarea class="textarea" id="bio" name="bio" maxlength="600"><?= e($u['bio'] ?? '') ?></textarea><?= $err('bio') ?></div>
      <button class="btn btn--primary" type="submit">Enregistrer</button>
    </form>
  </section>
  <section class="box reveal">
    <div class="box__head"><h2>Sécurité du compte</h2></div>
    <form class="box__body" method="post" novalidate>
      <?= csrf_field() ?><input type="hidden" name="action" value="password">
      <div class="field"><label for="current_password">Mot de passe actuel</label><input class="input" type="password" id="current_password" name="current_password" required autocomplete="current-password"><?= $err('current_password') ?></div>
      <div class="field"><label for="password">Nouveau mot de passe</label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"><div class="pw-meter" data-pw-meter="password"><span></span></div><?= $err('password') ?></div>
      <div class="field"><label for="password_confirm">Confirmation</label><input class="input" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"></div>
      <button class="btn btn--gold" type="submit">Modifier le mot de passe</button>
      <p class="disclaimer mt-2">La modification ferme automatiquement toutes vos autres sessions ouvertes.</p>
    </form>
  </section>
</div>
<?php require APP_ROOT . '/includes/app-footer.php'; ?>
