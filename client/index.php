<?php
require __DIR__ . '/../config/config.php';
require_client();

$uid = (int)current_user()['id'];
$pdo = db();

$projects = $pdo->prepare(
    "SELECT p.*, s.name AS service FROM projects p
     LEFT JOIN services s ON s.id = p.service_id
     WHERE p.client_id = ? ORDER BY p.updated_at DESC, p.id DESC"
); $projects->execute([$uid]); $projects = $projects->fetchAll();

$active = array_values(array_filter($projects, fn($p) => !in_array($p['status'], ['COMPLETED', 'CANCELLED'], true)));
$pending = array_values(array_filter($projects, fn($p) => in_array($p['status'], ['NEW', 'REVIEWING', 'QUOTATION'], true)));
$completed = array_values(array_filter($projects, fn($p) => $p['status'] === 'COMPLETED'));

$invoices = $pdo->prepare("SELECT * FROM invoices WHERE client_id = ? AND status IN ('sent','partially_paid','overdue')");
$invoices->execute([$uid]); $outstanding = $invoices->fetchAll();

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]); $notifs = $notifs->fetchAll();

$deps = $pdo->prepare("SELECT i.id, i.invoice_no, i.total, i.amount_paid, p.id AS project_id, p.title
                       FROM invoices i JOIN projects p ON p.id = i.project_id
                       WHERE i.client_id = ? AND i.is_deposit = 1 AND i.status IN ('sent','partially_paid','overdue')");
$deps->execute([$uid]); $depositsDue = $deps->fetchAll();

$unread = unread_notifications($uid);
$user = current_user();

dashboard_head(['title' => 'Client Dashboard', 'active' => 'dashboard', 'crumb' => 'Dashboard']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Client dashboard</span>
    <h2>Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?>.</h2>
    <p class="sub">Here is an overview of your projects and activity across the RSI portal.</p>
  </div>
  <div class="actions no-print">
    <a class="btn btn-primary" href="<?= app_url('client/request.php') ?>"><?= icon('plus') ?> New Request</a>
  </div>
</div>

<?php render_alerts(); ?>

<div class="stats">
  <div class="stat">
    <span class="stat-icon"><?= icon('folder') ?></span>
    <div><small>Active projects</small><strong><?= count($active) ?></strong></div>
  </div>
  <div class="stat">
    <span class="stat-icon"><?= icon('clock') ?></span>
    <div><small>Pending requests</small><strong><?= count($pending) ?></strong></div>
  </div>
  <div class="stat">
    <span class="stat-icon"><?= icon('check') ?></span>
    <div><small>Completed projects</small><strong><?= count($completed) ?></strong></div>
  </div>
  <div class="stat">
    <span class="stat-icon"><?= icon('rocket') ?></span>
    <div><small>Deposits due</small><strong><?= count($depositsDue) ?></strong></div>
  </div>
  <div class="stat">
    <span class="stat-icon"><?= icon('money') ?></span>
    <div><small>Outstanding invoices</small><strong><?= count($outstanding) ?></strong></div>
  </div>
</div>

<?php if ($depositsDue): ?>
  <div class="deposit-callout" style="margin-bottom:22px">
    <span class="dc-icon"><?= icon('rocket') ?></span>
    <div class="dc-body">
      <b><?= count($depositsDue) === 1 ? 'You have 1 project deposit to complete' : 'You have ' . count($depositsDue) . ' project deposits to complete' ?>.</b>
      <p>Projects remain on hold until your deposit is paid and confirmed.</p>
    </div>
    <a class="btn btn-primary btn-sm" href="<?= app_url('client/project.php?id=' . (int)$depositsDue[0]['project_id'] . '&tab=invoice') ?>"><?= icon('money') ?> Pay first deposit</a>
  </div>
<?php endif; ?>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head">
      <h3>My projects &amp; requests</h3>
      <a class="small" href="<?= app_url('client/projects.php') ?>">View all <?= icon('arrow') ?></a>
    </div>
    <?php if ($projects): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Project</th><th>Status</th><th>Progress</th><th>Updated</th></tr></thead>
          <tbody>
            <?php foreach (array_slice($projects, 0, 6) as $p): ?>
              <tr>
                <td>
                  <a class="row-title" href="<?= app_url('client/project.php?id=' . (int)$p['id']) ?>"><?= e($p['title']) ?></a>
                  <div class="row-sub"><?= e($p['ref_no']) ?> · <?= e($p['service'] ?? 'General') ?></div>
                </td>
                <td><?= status_badge($p['status']) ?></td>
                <td style="min-width:130px">
                  <div class="progress"><span style="width:<?= max(0, min(100, (int)$p['progress'])) ?>%"></span></div>
                  <small class="muted"><?= (int)$p['progress'] ?>%</small>
                </td>
                <td class="muted small"><?= time_ago($p['updated_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty">
        <?= icon('folder') ?>
        <h4>No projects yet</h4>
        <p>Send your first project request and the RSI team will respond with a plan and quotation.</p>
        <a class="btn btn-primary btn-sm" href="<?= app_url('client/request.php') ?>"><?= icon('plus') ?> New Request</a>
      </div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head">
      <h3>Recent notifications</h3>
      <a class="small" href="<?= app_url('client/notifications.php') ?>">View all <?= icon('arrow') ?></a>
    </div>
    <?php if ($notifs): ?>
      <?php foreach ($notifs as $n): ?>
        <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
          <span class="notif-icon"><?= icon('bell') ?></span>
          <div>
            <b><?= e(str_replace('_', ' ', $n['title'])) ?></b>
            <p class="small muted mb-0" style="margin:2px 0 4px"><?= e(str_replace('_', ' ', truncate($n['message'], 120))) ?></p>
            <span class="small muted"><?= time_ago($n['created_at']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty">
        <?= icon('bell') ?>
        <h4>No notifications yet</h4>
        <p>Updates about your projects and requests will appear here.</p>
      </div>
    <?php endif; ?>
  </section>
</div>

<section class="panel">
  <div class="panel-head"><h3>Quick actions</h3></div>
  <div class="grid-3">
    <a class="quick-cta" href="<?= app_url('checkout.php') ?>"><span class="stat-icon"><?= icon('rocket') ?></span><div><b>Start a project</b><small>Choose a program &amp; pay deposit</small></div></a>
    <a class="quick-cta" href="<?= app_url('client/request.php') ?>"><span class="stat-icon"><?= icon('plus') ?></span><div><b>Send a request</b><small>Describe work for the team</small></div></a>
    <a class="quick-cta" href="<?= app_url('client/projects.php') ?>"><span class="stat-icon"><?= icon('folder') ?></span><div><b>My projects</b><small>Track status &amp; progress</small></div></a>
    <a class="quick-cta" href="<?= app_url('client/messages.php') ?>"><span class="stat-icon"><?= icon('chat') ?></span><div><b>Messages</b><small>Talk to the RSI team</small></div></a>
    <a class="quick-cta" href="<?= app_url('client/files.php') ?>"><span class="stat-icon"><?= icon('file') ?></span><div><b>Files</b><small>View project documents</small></div></a>
    <a class="quick-cta" href="<?= app_url('client/profile.php') ?>"><span class="stat-icon"><?= icon('user') ?></span><div><b>My profile</b><small>Update your details</small></div></a>
    <a class="quick-cta" href="<?= app_url('contact.php') ?>"><span class="stat-icon"><?= icon('phone') ?></span><div><b>Get help</b><small>Contact the team directly</small></div></a>
  </div>
</section>
<?php dashboard_footer(); ?>