<?php
/** Inscription d'un apprenant. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require_guest();

$open = setting('registration_open', '1') === '1';
$authError = null;
$errors = [];
if (is_post() && $open) {
    verify_csrf();
    $data = [
        'first_name' => input('first_name'), 'last_name' => input('last_name'), 'email' => mb_strtolower(input('email')),
        'password' => (string) ($_POST['password'] ?? ''), 'password_confirm' => (string) ($_POST['password_confirm'] ?? ''),
    ];
    $errors = validate($data, [
        'first_name' => 'required|name|max:80', 'last_name' => 'required|name|max:80', 'email' => 'required|email|max:190',
        'password' => 'required', 'password_confirm' => 'same:password',
    ], ['first_name' => 'Prénom', 'last_name' => 'Nom', 'email' => 'E-mail', 'password' => 'Mot de passe', 'password_confirm' => 'mot de passe']);
    if (!isset($errors['password']) && ($p = password_policy_error_for($data['password'], [$data['email'], $data['first_name'], $data['last_name']]))) {
        $errors['password'] = $p;
    }
    if (empty($_POST['charter'])) {
        $errors['charter'] = 'Vous devez accepter la charte éthique de l\'Académie.';
    }
    // Limitation : N créations de compte par IP et par heure (security.register_max_per_ip)
    if (!$errors && (int) q_val("SELECT COUNT(*) FROM audit_logs WHERE action = 'auth.register' AND ip_address = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)", [client_ip()]) >= (int) config('security.register_max_per_ip', 5)) {
        $authError = 'Trop d\'inscriptions depuis cette connexion. Réessayez plus tard.';
    }
    if (!$errors && !$authError) {
        if (q_val('SELECT 1 FROM users WHERE email = ?', [$data['email']])) {
            $errors['email'] = 'Un compte existe déjà avec cette adresse. Connectez-vous ou réinitialisez votre mot de passe.';
        } else {
            $roleId = (int) q_val("SELECT id FROM roles WHERE slug = 'learner'");
            q('INSERT INTO users (role_id, first_name, last_name, email, password_hash) VALUES (?,?,?,?,?)',
                [$roleId, $data['first_name'], $data['last_name'], $data['email'], hash_password($data['password'])]);
            $uid = (int) db()->lastInsertId();
            login_user($uid);
            audit('auth.register', 'user', $uid);
            // E-mail de bienvenue automatique (un échec d'envoi ne bloque jamais l'inscription)
            mail_welcome(['first_name' => $data['first_name'], 'email' => $data['email']]);
            flash('success', 'Compte créé. Bienvenue à l\'Académie AFRICA\'SPY.');
            $slug = input('formation');
            if ($slug !== '' && ($cid = q_val("SELECT id FROM courses WHERE slug = ? AND status = 'published'", [$slug]))) {
                redirect('checkout.php', ['course' => $slug]);
            }
            redirect('learner/dashboard.php');
        }
    }
    if ($errors && !$authError) {
        $authError = 'Le formulaire contient ' . count($errors) . ' erreur' . (count($errors) > 1 ? 's' : '') . '.';
    }
    set_old($data);
}
$pageTitle = 'Inscription';
$authTitle = 'Rejoindre l\'Académie';
$authIntro = $open ? 'Créez votre compte apprenant pour accéder aux formations, aux examens et à la certification.' : 'Les inscriptions sont momentanément fermées.';
$authTerminal = 'Nouvel accès | enregistrement';
$authBody = function () use ($errors, $open): void {
    if (!$open) { echo '<a class="btn btn--ghost" href="' . e(url('contact.php')) . '">Nous contacter</a>'; return; }
    $err = static fn ($k) => isset($errors[$k]) ? '<span class="err">' . e($errors[$k]) . '</span>' : '';
    $inv = static fn ($k) => isset($errors[$k]) ? ' aria-invalid="true"' : ''; ?>
  <form method="post" action="<?= e(url('register.php')) ?>" novalidate>
    <?= csrf_field() ?><input type="hidden" name="formation" value="<?= e(input('formation')) ?>">
    <div class="form-row">
      <div class="field"><label for="first_name">Prénom</label><input class="input" id="first_name" name="first_name" required maxlength="80" autocomplete="given-name" value="<?= e(old('first_name')) ?>"<?= $inv('first_name') ?>><?= $err('first_name') ?></div>
      <div class="field"><label for="last_name">Nom</label><input class="input" id="last_name" name="last_name" required maxlength="80" autocomplete="family-name" value="<?= e(old('last_name')) ?>"<?= $inv('last_name') ?>><?= $err('last_name') ?></div>
    </div>
    <div class="field"><label for="email">Adresse e-mail</label><input class="input" type="email" id="email" name="email" required maxlength="190" autocomplete="email" value="<?= e(old('email')) ?>"<?= $inv('email') ?>><?= $err('email') ?></div>
    <div class="field"><label for="password">Mot de passe</label><input class="input" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"<?= $inv('password') ?>><div class="pw-meter" data-pw-meter="password"><span></span></div><span class="hint">10 caractères minimum, lettres et chiffres.</span><?= $err('password') ?></div>
    <div class="field"><label for="password_confirm">Confirmation</label><input class="input" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"<?= $inv('password_confirm') ?>><?= $err('password_confirm') ?></div>
    <label class="check mb-2"><input type="checkbox" name="charter" value="1" required> <span>J'accepte la <a href="<?= e(url('about.php#ethique')) ?>" target="_blank" rel="noopener">charte éthique</a> et m'engage à n'utiliser les compétences acquises qu'à des fins légales et légitimes.</span></label><?= $err('charter') ?>
    <button class="btn btn--primary btn--block mt-1" type="submit">Créer mon compte</button>
  </form>
  <p class="auth__alt">Déjà inscrit ? <a href="<?= e(url('login.php')) ?>">Se connecter</a></p>
<?php };
require APP_ROOT . '/includes/auth-layout.php';
