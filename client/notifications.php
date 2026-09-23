<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$uid = (int)current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'read_all') {
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$uid]);
        flash('success', 'All notifications marked as read.');
    } elseif ($action === 'read_one') {
        $id = (int)($_POST['notification_id'] ?? 0);
        $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?')->execute([$id, $uid]);
    }
    redirect(app_url('client/notifications.php'));
}

$perPage = 15;
$page = max(1, (int)($_GET['page'] ?? 1));
$count = $pdo->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id = ?');
$count->execute([$uid]);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$list = $pdo->prepare('SELECT n.*, p.title AS project_title FROM notifications n LEFT JOIN projects p ON p.id = n.related_project_id WHERE n.user_id = ? ORDER BY n.created_at DESC LIMIT ' . $perPage . ' OFFSET ' . $pg['offset']);
$list->execute([$uid]);
$notifs = $list->fetchAll();

dashboard_head(['title' => 'Notifications', 'active' => 'notifications', 'crumb' => 'Notifications']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Notifications</span>
    <h2>Updates &amp; alerts</h2>
    <p class="sub">Status changes, messages, quotations and other updates regarding your account.</p>
  </div>
  <?php if ($notifs): ?>
    <form method="post" class="actions no-print">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="read_all">
      <button class="btn btn-ghost btn-sm" type="submit"><?= icon('check') ?> Mark all as read</button>
    </form>
  <?php endif; ?>
</div>

<?php render_alerts(); ?>

<section class="panel">
  <?php if ($notifs): ?>
    <?php foreach ($notifs as $n): ?>
      <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
        <span class="notif-icon"><?= icon('bell') ?></span>
        <div style="flex-grow:1;min-width:0">
          <b><?= e($n['title']) ?></b>
          <p class="small muted" style="margin:2px 0 4px"><?= e($n['message']) ?></p>
          <span class="small muted">
            <?= time_ago($n['created_at']) ?>
            <?php if ($n['project_title']): ?> · <a href="<?= app_url('client/project.php?id=' . (int)$n['related_project_id']) ?>"><?= e($n['project_title']) ?></a><?php endif; ?>
          </span>
        </div>
        <?php if (!$n['is_read']): ?>
          <form method="post" class="no-print">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="read_one">
            <input type="hidden" name="notification_id" value="<?= (int)$n['id'] ?>">
            <button class="btn btn-ghost btn-sm" type="submit">Mark read</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php pagination_links($pg['totalPages'], $pg['page'], 'notifications.php'); ?>
  <?php else: ?>
    <div class="empty">
      <?= icon('bell') ?>
      <h4>No notifications</h4>
      <p>When something changes on your projects, you will be notified here.</p>
    </div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>