<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle_active') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $active = (int)($_POST['active'] ?? 1) === 1 ? 1 : 0;
        $row = $pdo->query('SELECT full_name FROM users WHERE id = ' . $uid)->fetch();
        if ($row) {
            $pdo->prepare('UPDATE users SET active = ? WHERE id = ?')->execute([$active, $uid]);
            audit('user_status_changed', 'users', $uid, ($active ? 'Activated' : 'Suspended') . ' client account ' . $row['full_name']);
            flash('success', $row['full_name'] . ' ' . ($active ? 'reactivated' : 'suspended') . '.');
        }
    }
    redirect(app_url('admin/clients.php'));
}

$where = "u.role = 'client'";
$params = [];
if ($q !== '') {
    $where .= ' AND (u.full_name LIKE ? OR u.company LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}

$count = $pdo->prepare("SELECT COUNT(*) c FROM users u WHERE $where");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT u.id, u.full_name, u.company, u.email, u.phone, u.active, u.created_at,
          (SELECT COUNT(*) FROM projects p WHERE p.client_id = u.id) AS project_count,
          (SELECT COUNT(*) FROM projects p WHERE p.client_id = u.id AND p.status NOT IN ('CANCELLED','COMPLETED')) AS active_count,
          (SELECT COALESCE(SUM(i.total),0) FROM invoices i JOIN projects p ON p.id = i.project_id WHERE p.client_id = u.id AND i.status NOT IN ('cancelled')) AS invoiced,
          (SELECT COALESCE(SUM(pay.amount),0) FROM payments pay JOIN invoices i ON i.id = pay.invoice_id JOIN projects pr ON pr.id = i.project_id WHERE pr.client_id = u.id AND pay.status = 'confirmed') AS paid
        FROM users u
        WHERE $where
        ORDER BY u.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$clients = $stm->fetchAll();

$base = 'clients.php?q=' . urlencode($q);
$currency = settings('currency');

dashboard_head(['title' => 'Clients', 'active' => 'clients', 'crumb' => 'Clients']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Client accounts</span>
    <h2>Your clients</h2>
    <p class="sub">Every client who has registered in the portal, with their invoicing totals.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="clients.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search clients</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search name, company, email, phone…" value="<?= e($q) ?>">
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="clients.php">Reset</a>
</form>

<?php if ($clients): ?>
  <div class="grid-3">
    <?php foreach ($clients as $c): $outstanding = (float)$c['invoiced'] - (float)$c['paid']; ?>
      <article class="folio-card" style="display:flex;flex-direction:column">
        <div class="folio-body">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:10px">
            <h3 style="font-size:16px;margin:0"><?= e($c['full_name']) ?></h3>
            <span class="badge <?= $c['active'] ? 'green' : 'red' ?>"><?= $c['active'] ? 'Active' : 'Suspended' ?></span>
          </div>
          <div class="row-sub" style="margin-bottom:8px"><?= e($c['company'] ?: 'Individual') ?> · since <?= fmt_date($c['created_at'], 'M Y') ?></div>
          <div class="kv" style="padding:6px 0"><span>Email</span><span><?= e($c['email']) ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Phone</span><span><?= e($c['phone'] ?: '—') ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Projects</span><span><?= (int)$c['project_count'] ?> (<?= (int)$c['active_count'] ?> active)</span></div>
          <div class="kv" style="padding:6px 0"><span>Invoiced</span><span><?= money($c['invoiced'], $currency) ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Paid</span><span><?= money($c['paid'], $currency) ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Outstanding</span><span><?= money($outstanding, $currency) ?></span></div>
          <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap">
            <a class="btn btn-primary btn-sm" href="<?= app_url('admin/projects.php?client=' . (int)$c['id']) ?>">View projects <?= icon('arrow') ?></a>
            <form method="post" data-confirm="<?= $c['active'] ? 'Suspend this client account?' : 'Reactivate this client account?' ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_active">
              <input type="hidden" name="user_id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="active" value="<?= $c['active'] ? '0' : '1' ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= $c['active'] ? 'Suspend' : 'Reactivate' ?></button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
<?php else: ?>
  <section class="panel"><div class="empty"><?= icon('users') ?><h4>No clients found</h4><p><?= $q ? 'No client matches your search.' : 'Clients who register through the public site will appear here.' ?></p></div></section>
<?php endif; ?>
<?php dashboard_footer(); ?>