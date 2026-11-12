<?php
/**
 * Coque applicative (espace apprenant / administration).
 * Variables : $appArea ('learner'|'admin'), $pageTitle, $activeApp, $crumb
 */
declare(strict_types=1);
require_once APP_ROOT . '/includes/logo.php';
$me = current_user();
$appArea = $appArea ?? 'learner';
$activeApp = $activeApp ?? '';
$ico = static function (string $n): string {
    $p = [
        'home' => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'book' => '<path d="M4 4h7a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H4zM20 4h-5a3 3 0 0 0-3 3"/><path d="M20 4v14h-6"/>',
        'play' => '<circle cx="12" cy="12" r="9"/><path d="M10 8l6 4-6 4z"/>',
        'exam' => '<path d="M9 5h10M9 12h10M9 19h10"/><path d="M4 5l1 1 2-2M4 12l1 1 2-2M4 19l1 1 2-2"/>',
        'cert' => '<circle cx="12" cy="9" r="5"/><path d="M9 13l-2 8 5-3 5 3-2-8"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 5-7 8-7s7 2 8 7"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c.8-4 4-6 7-6s6.2 2 7 6"/><path d="M16 4.5a3.5 3.5 0 0 1 0 7M18 14c2 .8 3.4 2.6 4 6"/>',
        'layers' => '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8" r="1.5"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/>',
        'bell' => '<path d="M6 9a6 6 0 0 1 12 0c0 6 3 7 3 7H3s3-1 3-7"/><path d="M10 20a2 2 0 0 0 4 0"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'cog' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/>',
        'log' => '<path d="M6 3h9l4 4v14H6z"/><path d="M9 11h7M9 15h7M9 7h3"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
        'check' => '<path d="M5 12l5 5 9-10"/>',
    ][$n] ?? '';
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
};
if ($appArea === 'admin') {
    $unreadMsgs = can('messages.view') ? (int) q_val('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0') : 0;
    $nav = [
        ['Pilotage', [
            ['dashboard', 'admin/dashboard.php', 'Tableau de bord', 'chart', 'stats.view'],
            ['audit', 'admin/audit.php', 'Journal d\'audit', 'log', 'audit.view'],
        ]],
        ['Pédagogie', [
            ['courses', 'admin/courses.php', 'Formations', 'book', 'courses.manage'],
            ['categories', 'admin/categories.php', 'Catégories', 'tag', 'courses.manage'],
            ['chapters', 'admin/chapters.php', 'Chapitres', 'layers', 'courses.manage'],
            ['lessons', 'admin/lessons.php', 'Leçons & médias', 'play', 'courses.manage'],
            ['exams', 'admin/exams.php', 'Examens & QCM', 'exam', 'exams.manage'],
            ['media', 'admin/media.php', 'Médiathèque', 'image', 'media.manage'],
        ]],
        ['Apprenants', [
            ['users', 'admin/users.php', 'Utilisateurs', 'users', 'users.manage'],
            ['results', 'admin/results.php', 'Résultats', 'check', 'results.view'],
            ['certificates', 'admin/certificates.php', 'Certificats', 'cert', 'certificates.manage'],
        ]],
        ['Institution', [
            ['announcements', 'admin/announcements.php', 'Annonces', 'bell', 'announcements.manage'],
            ['messages', 'admin/messages.php', 'Messages', 'mail', 'messages.view'],
            ['settings', 'admin/settings.php', 'Paramètres', 'cog', 'settings.manage'],
        ]],
    ];
    $notifs = [];
} else {
    $nav = [
        ['Terminal', [
            ['dashboard', 'learner/dashboard.php', 'Tableau de bord', 'home', null],
            ['courses', 'learner/courses.php', 'Mes formations', 'book', null],
            ['exams', 'learner/exams.php', 'Examens & résultats', 'exam', null],
            ['certificates', 'learner/certificates.php', 'Certificats', 'cert', null],
            ['profile', 'learner/profile.php', 'Profil', 'user', null],
        ]],
        ['Académie', [
            ['catalog', 'academy.php', 'Catalogue', 'globe', null],
            ['tools', 'tools.php', 'Outils', 'layers', null],
        ]],
    ];
    $notifs = q_all("SELECT a.*, (ar.user_id IS NOT NULL) AS is_read FROM announcements a
                       LEFT JOIN announcement_reads ar ON ar.announcement_id = a.id AND ar.user_id = ?
                      WHERE a.is_published = 1 AND a.audience IN ('all','learners') AND a.published_at <= UTC_TIMESTAMP()
                      ORDER BY a.published_at DESC LIMIT 12", [$me['id']]);
}
$unread = count(array_filter($notifs, static fn ($n) => !$n['is_read']));
$extraCss = ['css/dashboard.css'];
$extraJs = ['js/dashboard.js'];
$bodyClass = 'app';
$hideChrome = true;
require APP_ROOT . '/includes/header.php';
?>
<div class="app-shell">
  <aside class="app-side" id="app-side" aria-label="Navigation de l'espace">
    <a href="<?= e(url('index.php')) ?>" aria-label="Site public AFRICA'SPY"><?= logo_lockup('holo') ?></a>
    <div class="app-side__role"><?= $appArea === 'admin' ? 'Console d\'administration' : 'Terminal apprenant' ?><br>Statut : <b><i class="dot"></i> <?= e($me['role_name']) ?></b></div>
    <?php foreach ($nav as [$group, $items]): $visible = array_filter($items, static fn ($i) => $i[4] === null || can($i[4])); if (!$visible) continue; ?>
    <p class="app-nav__label"><?= e($group) ?></p>
    <ul class="app-nav">
      <?php foreach ($visible as [$key, $href, $label, $icon]): ?>
      <li><a href="<?= e(url($href)) ?>"<?= $activeApp === $key ? ' aria-current="page"' : '' ?>><?= $ico($icon) ?><?= e($label) ?><?php if ($key === 'messages' && !empty($unreadMsgs)): ?><span class="badge-count"><?= (int) $unreadMsgs ?></span><?php endif; ?></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endforeach; ?>
    <?php if ($appArea === 'learner' && is_staff()): ?><p class="app-nav__label">Staff</p><ul class="app-nav"><li><a href="<?= e(url('admin/dashboard.php')) ?>"><?= $ico('cog') ?>Administration</a></li></ul><?php endif; ?>
    <?php if ($appArea === 'admin'): ?><p class="app-nav__label">Navigation</p><ul class="app-nav"><li><a href="<?= e(url('learner/dashboard.php')) ?>"><?= $ico('home') ?>Vue apprenant</a></li><li><a href="<?= e(url('index.php')) ?>"><?= $ico('globe') ?>Site public</a></li></ul><?php endif; ?>
    <div class="app-side__foot">
      <form method="post" action="<?= e(url('logout.php')) ?>"><?= csrf_field() ?><button class="btn btn--sm btn--block" type="submit">Déconnexion</button></form>
      <p class="mt-1" data-utc-clock>UTC --:--:--</p>
    </div>
  </aside>
  <div class="app-main">
    <header class="app-top">
      <button class="side-toggle" type="button" aria-controls="app-side" aria-expanded="false" aria-label="Menu"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
      <div>
        <p class="app-top__crumb"><?= e($crumb ?? ($appArea === 'admin' ? 'Administration' : 'Espace apprenant')) ?></p>
        <h1 class="app-top__title"><?= e($pageTitle) ?></h1>
      </div>
      <div class="app-top__right">
        <?php if ($appArea === 'learner'): ?>
        <div class="notif" data-notif>
          <button class="notif__btn" type="button" aria-expanded="false" aria-haspopup="true" aria-label="Annonces (<?= $unread ?> non lues)" data-badge="<?= $unread ?>"><?= $ico('bell') ?></button>
          <div class="notif__panel" hidden>
            <div class="notif__head"><span>Annonces</span><span><?= $unread ?> non lue<?= $unread > 1 ? 's' : '' ?></span></div>
            <?php if (!$notifs): ?><p class="dim" style="padding:16px;margin:0">Aucune annonce.</p><?php endif; ?>
            <?php foreach ($notifs as $n): ?>
            <button class="notif__item<?= $n['is_read'] ? '' : ' is-unread' ?>" type="button" data-ann="<?= (int) $n['id'] ?>">
              <strong><?= $n['level'] === 'alert' ? '⚠ ' : '' ?><?= e($n['title']) ?></strong><p><?= e($n['body']) ?></p><small><?= e(fmt_datetime($n['published_at'])) ?></small>
            </button>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
        <a class="user-chip" href="<?= e(url('learner/profile.php')) ?>"><span class="user-chip__av"><?= e(initials($me['first_name'], $me['last_name'])) ?></span><span><?= e($me['first_name']) ?></span></a>
      </div>
    </header>
    <main class="app-content" id="main">
