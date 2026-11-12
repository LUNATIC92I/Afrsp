<?php
/** Déconnexion (POST + CSRF uniquement, pour éviter les déconnexions forcées). */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
if (!is_post()) {
    redirect('index.php');
}
verify_csrf();
if (current_user()) {
    audit('auth.logout');
}
logout_user();
start_secure_session();
flash('info', 'Vous êtes déconnecté. À bientôt.');
redirect('login.php');
