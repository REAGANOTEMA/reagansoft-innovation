<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$currency = settings('currency');
$year = min(date('Y'), max(2020, (int)($_GET['year'] ?? date('Y'))));
$years = $pdo->query('SELECT DISTINCT YEAR(created_at) y FROM payments UNION SELECT DISTINCT YEAR(submitted_at) FROM projects ORDER BY y DESC')->fetchAll();
$years = array_map(fn($r) => (int)$r['y'], $years);
if (!$years) { $years = [(int)date('Y')]; }

$stats = [
    'projects'    => (int)$pdo->query("SELECT COUNT(*) c FROM projects")->fetch()['c'],
    'active'      => (int)$pdo->query("SELECT COUNT(*) c FROM projects WHERE status NOT IN ('COMPLETED','CANCELLED')")->fetch()['c'],
    'clients'     => (int)$pdo->query("SELECT COUNT(*) c FROM users WHERE role = 'client'")->fetch()['c'],
    'revenue'     => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status = 'confirmed'")->fetch()['s'],
    'pendingPay'  => (float)$pdo->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE status = 'pending'")->fetch()['s'],
    'outstanding' => (float)$pdo->query("SELECT COALESCE(SUM(total - amount_paid),0) s FROM invoices WHERE status IN ('sent','partially_paid','overdue')")->fetch()['s'],
];

$byStatus = $pdo->query("SELECT status, COUNT(*) c FROM projects GROUP BY status ORDER BY c DESC")->fetchAll();

$monthly = $pdo->prepare(
    "SELECT DATE_FORMAT(p.created_at, '%Y-%m') ym, DATE_FORMAT(p.created_at, '%b') mon, COALESCE(SUM(p.amount),0) amount, COUNT(*) n
     FROM payments p WHERE p.status = 'confirmed' AND YEAR(p.created_at) = ? GROUP BY ym, mon ORDER BY ym"
);
$monthly->execute([$year]);
$monthly = $monthly->fetchAll();

$byService = $pdo->query(
    "SELECT COALESCE(s.name, 'General') name, COUNT(*) c, COALESCE(AVG(p.budget),0) avg_budget
     FROM projects p LEFT JOIN services s ON s.id = p.service_id
     GROUP BY COALESCE(s.name,'General') ORDER BY c DESC, name"
)->fetchAll();

$topClients = $pdo->query(
    "SELECT u.full_name, u.company, COUNT(p.id) projects,
            COALESCE(SUM(i.total),0) invoiced, COALESCE(SUM(CASE WHEN pay.status='confirmed' THEN pay.amount ELSE 0 END),0) paid
     FROM users u
     JOIN projects p ON p.client_id = u.id
     LEFT JOIN invoices i ON i.project_id = p.id
     LEFT JOIN payments pay ON pay.invoice_id = i.id
     WHERE u.role = 'client'
     GROUP BY u.id ORDER BY paid DESC, invoiced DESC LIMIT 8"
)->fetchAll();

dashboard_head(['title' => 'Reports', 'active' => 'reports', 'crumb' => 'Reports']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Analytics</span>
    <h2>Business reports</h2>
    <p class="sub">A live view of projects, revenue and client activity.</p>
  </div>
  <form method="get" action="reports.php" class="toolbar" style="gap:8px">
    <div class="field" style="margin:0"><label class="visually-hidden" for="year">Year</label>
      <select class="select" id="year" name="year" onchange="this.form.submit()">
        <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php render_alerts(); ?>

<div class="stat-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:16px;margin-bottom:22px">
  <div class="stat"><span class="stat-icon"><?= icon('folder') ?></span><div><small>Total projects</small><strong><?= number_format($stats['projects']) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('clock') ?></span><div><small>Active projects</small><strong><?= number_format($stats['active']) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('users') ?></span><div><small>Clients</small><strong><?= number_format($stats['clients']) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('check') ?></span><div><small>Confirmed revenue</small><strong><?= money($stats['revenue'], $currency) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('inbox') ?></span><div><small>Pending payments</small><strong><?= money($stats['pendingPay'], $currency) ?></strong></div></div>
  <div class="stat"><span class="stat-icon"><?= icon('money') ?></span><div><small>Outstanding</small><strong><?= money($stats['outstanding'], $currency) ?></strong></div></div>
</div>

<div class="report-grid">
  <section class="panel">
    <div class="panel-head"><h3>Confirmed revenue — <?= $year ?></h3><a class="small" href="<?= app_url('admin/invoices.php') ?>">View invoices</a></div>
    <?php if ($monthly): ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Month</th><th>Payments</th><th>Amount</th></tr></thead>
          <tbody>
            <?php foreach ($monthly as $m): ?>
              <tr><td class="small"><?= e($m['mon']) ?></td><td><?= (int)$m['n'] ?></td><td><b><?= money($m['amount'], $currency) ?></b></td></tr>
            <?php endforeach; ?>
            <tr><td><b>Total</b></td><td><?= array_sum(array_column($monthly, 'n')) ?></td><td><b><?= money(array_sum(array_column($monthly, 'amount')), $currency) ?></b></td></tr>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty"><?= icon('chart') ?><h4>No confirmed payments in <?= $year ?></h4></div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h3>Projects by status</h3></div>
    <?php if ($byStatus): ?>
      <?php foreach ($byStatus as $bs): $pct = max(5, round(($bs['c'] / max(1, $stats['projects'])) * 100)); ?>
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;gap:10px" class="small"><span><?= status_badge($bs['status']) ?></span><b><?= (int)$bs['c'] ?></b></div>
          <div class="progress" style="margin-top:6px"><span style="width:<?= min(100, $pct) ?>%"></span></div>
        </div>
      <?php endforeach; ?>
    <?php else: ?><div class="empty"><h4>No projects yet</h4></div><?php endif; ?>
  </section>
</div>

<section class="panel">
  <div class="panel-head"><h3>Top clients by payments</h3></div>
  <?php if ($topClients): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Client</th><th>Projects</th><th>Invoiced</th><th>Paid</th><th>Outstanding</th></tr></thead>
        <tbody>
          <?php foreach ($topClients as $tc): ?>
            <tr>
              <td><span class="row-title"><?= e($tc['full_name']) ?></span><div class="row-sub"><?= e($tc['company'] ?? '—') ?></div></td>
              <td><?= (int)$tc['projects'] ?></td>
              <td><?= money($tc['invoiced'], $currency) ?></td>
              <td><b><?= money($tc['paid'], $currency) ?></b></td>
              <td class="small"><?= money(max(0, (float)$tc['invoiced'] - (float)$tc['paid']), $currency) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?><div class="empty"><?= icon('users') ?><h4>No client data yet</h4></div><?php endif; ?>
</section>

<section class="panel">
  <div class="panel-head"><h3>Work delivered by service</h3></div>
  <?php if ($byService): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Service</th><th>Projects</th><th>Average budget</th></tr></thead>
        <tbody>
          <?php foreach ($byService as $bs): ?>
            <tr><td class="small"><?= e($bs['name']) ?></td><td><?= (int)$bs['c'] ?></td><td><?= money($bs['avg_budget'], $currency) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?><div class="empty"><h4>No data yet</h4></div><?php endif; ?>
</section>
<?php dashboard_footer(); ?>