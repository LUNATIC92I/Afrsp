<?php
/** Administration : messages reçus via le formulaire de contact. */
declare(strict_types=1);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';
require_permission('messages.view');

if (is_post()) {
    verify_csrf();
    $id = input_int('id');
    if (input('action') === 'read') q('UPDATE contact_messages SET is_read = 1 - is_read WHERE id = ?', [$id]);
    if (input('action') === 'delete') { q('DELETE FROM contact_messages WHERE id = ?', [$id]); audit('message.delete', 'contact', $id); flash('success', 'Message supprimé.'); }
    back();
}
$pg = paginate((int) q_val('SELECT COUNT(*) FROM contact_messages'), 20);
$rows = q_all("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT {$pg['per']} OFFSET {$pg['offset']}");
$pageTitle = 'Messages';
$appArea = 'admin';
$activeApp = 'messages';
require APP_ROOT . '/includes/app-header.php'; ?>
<?php if (!$rows): ?><div class="empty"><strong>Aucun message</strong>Les messages du formulaire de contact apparaîtront ici.</div><?php endif; ?>
<?php foreach ($rows as $r): ?>
<article class="box reveal" style="<?= $r['is_read'] ? '' : 'border-color:rgba(0,200,255,.45)' ?>">
  <div class="box__head"><h2 style="letter-spacing:.06em;text-transform:none"><?= e($r['subject']) ?></h2><span class="mono"><?= e(fmt_datetime($r['created_at'])) ?></span></div>
  <div class="box__body">
    <p class="mono dim" style="font-size:.78rem"><?= e($r['full_name']) ?> | <a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></p>
    <p style="white-space:pre-line"><?= e($r['message']) ?></p>
    <div class="flex"><a class="btn btn--sm btn--primary" href="mailto:<?= e($r['email']) ?>?subject=<?= rawurlencode('Re: ' . $r['subject']) ?>">Répondre</a><?= action_btn('read', ['id' => $r['id']], $r['is_read'] ? 'Marquer non lu' : 'Marquer lu') ?><?= action_btn('delete', ['id' => $r['id']], 'Supprimer', 'btn--sm btn--danger', 'Supprimer ce message ?') ?></div>
  </div>
</article>
<?php endforeach; ?>
<?= pagination_links($pg) ?>
<?php require APP_ROOT . '/includes/app-footer.php';
