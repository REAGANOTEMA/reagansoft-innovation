<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$uid = (int)current_user()['id'];
$isAdmin = user_role() === 'admin';

$stats = [];
$stats['clients']        = (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role='client' AND active=1")->fetch()['c'];
$stats['new_requests']   = (int)$pdo->query("SELECT COUNT(*) c FROM projects WHERE status IN ('NEW','REVIEWING')")->fetch()['c'];
$stats['active']         = (int)$pdo->query("SELECT COUNT(*) c FROM projects WHERE status NOT IN ('COMPLETED','CANCELLED')")->fetch()['c'];
$stats['completed']      = (int)$pdo->query("SELECT COUNT(*) c FROM projects WHERE status='COMPLETED'")->fetch()['c'];
$stats['pending_quotes'] = (int)$pdo->query("SELECT COUNT(*) c FROM quotations WHERE status='sent'")->fetch()['c'];
$stats['outstanding']    = (array)$pdo->query("SELECT COALESCE(SUM(total - amount_paid),0) s, COUNT(*) c FROM invoices WHERE status IN ('sent','partially_paid','overdue')")->fetch();
$stats['revenue']        = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status='confirmed'")->fetch()['s'];
$stats['messages_unread']= (int)$pdo->query("SELECT COUNT(*) c FROM contact_messages WHERE is_read=0")->fetch()['c'];

$recentRequests = $pdo->query(
    "SELECT p.*, u.full_name AS client_name, u.company, s.name AS service
     FROM projects p JOIN users u ON u.id = p.client_id LEFT JOIN services s ON s.id = p.service_id
     ORDER BY p.created_at DESC LIMIT 8"
)->fetchAll();

$recentLogs = $pdo->query(
    "SELECT l.*, u.full_name FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id ORDER BY l.created_at DESC LIMIT 8"
)->fetchAll();

$pendingInvoices = $pdo->query(
    "SELECT i.invoice_no, i.status, i.total, i.amount_paid, i.due_date, u.full_name, p.title
     FROM invoices i JOIN users u ON u.id = i.client_id JOIN projects p ON p.id = i.project_id
     WHERE i.status IN ('sent','partially_paid','overdue') ORDER BY i.due_date ASC LIMIT 6"
)->fetchAll();

dashboard_head([
    'title'  => 'Operations Dashboard',
    'active' => 'dashboard',
    'crumb'  => 'Dashboard' . ($isAdmin ? '' : ''),
]);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Operations</span>
    <h2>Welcome, <?= e(explode(' ', current_user()['full_name'])[0]) ?>.</h2>
    <p class="sub">Live overview of clients, requests, projects and revenue.</p>
  </div>
  <form method="get" action="<?= app_url('admin/requests.php') ?>" class="actions no-print">
    <button class="btn btn-primary" type="submit"><?= icon('inbox') ?> Review requests (<?= $stats['new_requests'] ?>)</button>
  </form>
</div>

<?php render_alerts(); ?>

<div class="stats">
  <div class="stat"><span class="stat-icon"><?= icon('users') ?></span><div><small>Clients</small><strong><?= $stats['clients'] ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('inbox') ?></span><div><small>New &amp; reviewing requests</small><strong><?= $stats['new_requests'] ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('folder') ?></span><div><small>Active projects</small><strong><?= $stats['active'] ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('check') ?></span><div><small>Completed projects</small><strong><?= $stats['completed'] ?></strong></div></div>
</div>
<div class="stats">
  <div class="stat"><span class="stat-icon"><?= icon('doc') ?></span><div><small>Quotations awaiting reply</small><strong><?= $stats['pending_quotes'] ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('money') ?></span><div><small>Outstanding invoice value</small><strong><?= money($stats['outstanding']['s'], settings('currency')) ?><small> · <?= (int)$stats['outstanding']['c'] ?> invoice<?= (int)$stats['outstanding']['c'] === 1 ? '' : 's' ?></small></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('layers') ?></span><div><small>Confirmed revenue</small><strong><?= money($stats['revenue'], settings('currency')) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('mail') ?></span><div><small>Contact messages</small><strong><?= $stats['messages_unread'] ?><?= $stats['messages_unread'] > 0 ? ' unread' : '' ?></strong><a href="<?= app_url('admin/messages.php') ?>">Open</a></div></div>
</div>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head">
      <h3>Recent project requests</h3>
      <a class="small" href="<?= app_url('admin/projects.php') ?>">View all <?= icon('arrow') ?></a>
    </div>
    <?php if ($recentRequests): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Client</th><th>Request</th><th>Status</th><th>Received</th></tr></thead>
          <tbody>
            <?php foreach ($recentRequests as $r): ?>
              <tr>
                <td><b><?= e($r['client_name']) ?></b><div class="row-sub"><?= e($r['company'] ?? '—') ?></div></td>
                <td><a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$r['id']) ?>"><?= e($r['title']) ?></a><div class="row-sub"><?= e($r['ref_no']) ?> · <?= e($r['service'] ?? 'General') ?></div></td>
                <td><?= status_badge($r['status']) ?></td>
                <td class="muted small"><?= time_ago($r['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('inbox') ?><h4>No requests yet</h4><p>New client requests will appear here.</p></div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head">
      <h3>Invoices needing attention</h3>
      <a class="small" href="<?= app_url('admin/invoices.php') ?>">View all <?= icon('arrow') ?></a>
    </div>
    <?php if ($pendingInvoices): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Invoice</th><th>Client</th><th>Balance</th><th>Due</th></tr></thead>
          <tbody>
            <?php foreach ($pendingInvoices as $inv):
              $bal = (float)$inv['total'] - (float)$inv['amount_paid']; ?>
              <tr>
                <td><a class="row-title" href="<?= app_url('admin/invoices.php') ?>"><?= e($inv['invoice_no']) ?></a></td>
                <td class="small"><?= e($inv['full_name']) ?></td>
                <td><?= money($bal, settings('currency')) ?></td>
                <td><?= status_badge($inv['status']) ?> <span class="small muted"><?= fmt_date($inv['due_date']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('money') ?><h4>No pending invoices</h4><p>All invoices are settled or in draft.</p></div>
    <?php endif; ?>
  </section>
</div>

<section class="panel">
  <div class="panel-head">
    <h3>Recent activity</h3>
    <?php if ($isAdmin): ?><a class="small" href="<?= app_url('admin/audit.php') ?>">View audit log <?= icon('arrow') ?></a><?php endif; ?>
  </div>
  <?php if ($recentLogs): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>User</th><th>Action</th><th>Details</th><th>Time</th></tr></thead>
        <tbody>
          <?php foreach ($recentLogs as $l): ?>
            <tr>
              <td class="small"><?= e($l['full_name'] ?? 'Guest') ?></td>
              <td><span class="badge gray"><?= e(str_replace('_', ' ', $l['action'])) ?></span></td>
              <td class="small"><?= e(truncate($l['details'] ?? '', 90)) ?></td>
              <td class="muted small"><?= time_ago($l['created_at']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty"><?= icon('clock') ?><h4>No activity yet</h4><p>Administrative actions will be logged here.</p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>