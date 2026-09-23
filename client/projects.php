<?php
require __DIR__ . '/../config/config.php';
require_client();

$uid = (int)current_user()['id'];
$pdo = db();

$q   = trim($_GET['q'] ?? '');
$st  = $_GET['status'] ?? '';
$pr  = $_GET['priority'] ?? '';
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ['p.client_id = ?'];
$params = [$uid];

if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.ref_no LIKE ? OR p.description LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
}
if ($st !== '' && in_array(strtoupper($st), project_statuses(), true)) {
    $where[] = 'p.status = ?';
    $params[] = strtoupper($st);
}
if ($pr !== '' && array_key_exists($pr, project_priority_labels())) {
    $where[] = 'p.priority = ?';
    $params[] = $pr;
}

$whereSql = implode(' AND ', $where);
$count = $pdo->prepare("SELECT COUNT(*) c FROM projects p WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT p.*, s.name AS service FROM projects p
        LEFT JOIN services s ON s.id = p.service_id
        WHERE $whereSql ORDER BY p.submitted_at DESC LIMIT " . $perPage . " OFFSET " . $pg['offset'];
$stm = $pdo->prepare($sql);
$stm->execute($params);
$projects = $stm->fetchAll();

$base = 'projects.php?q=' . urlencode($q) . '&status=' . urlencode($st) . '&priority=' . urlencode($pr);

dashboard_head(['title' => 'My Projects', 'active' => 'projects', 'crumb' => 'My Projects']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Projects &amp; requests</span>
    <h2>My projects</h2>
    <p class="sub">Every request and project you have submitted appears here.</p>
  </div>
  <div class="actions no-print">
    <a class="btn btn-primary" href="<?= app_url('client/request.php') ?>"><?= icon('plus') ?> New Request</a>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="projects.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:340px">
    <label class="visually-hidden" for="q">Search projects</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search by title, reference or description…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0">
    <label class="visually-hidden" for="status">Status</label>
    <select class="select" id="status" name="status">
      <option value="">All statuses</option>
      <?php foreach (project_statuses() as $s): ?>
        <option value="<?= e($s) ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(ucwords(strtolower($s))) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="field" style="margin:0">
    <label class="visually-hidden" for="priority">Priority</label>
    <select class="select" id="priority" name="priority">
      <option value="">All priorities</option>
      <?php foreach (project_priority_labels() as $k => $v): ?>
        <option value="<?= e($k) ?>" <?= $pr === $k ? 'selected' : '' ?>><?= e($v) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="projects.php">Reset</a>
</form>

<section class="panel">
  <?php if ($projects): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Project</th><th>Service</th><th>Status</th><th>Priority</th><th>Progress</th><th>Submitted</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($projects as $p): ?>
            <tr>
              <td>
                <a class="row-title" href="<?= app_url('client/project.php?id=' . (int)$p['id']) ?>"><?= e($p['title']) ?></a>
                <div class="row-sub"><?= e($p['ref_no']) ?></div>
              </td>
              <td><?= e($p['service'] ?? 'General') ?></td>
              <td><?= status_badge($p['status']) ?></td>
              <td><?= priority_badge($p['priority']) ?></td>
              <td style="min-width:130px">
                <div class="progress"><span style="width:<?= max(0, min(100, (int)$p['progress'])) ?>%"></span></div>
                <small class="muted"><?= (int)$p['progress'] ?>%</small>
              </td>
              <td class="muted small"><?= fmt_date($p['submitted_at']) ?></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= app_url('client/project.php?id=' . (int)$p['id']) ?>">View <?= icon('arrow') ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty">
      <?= icon('folder') ?>
      <h4>No projects found</h4>
      <p><?= $q || $st || $pr ? 'No projects match your filters.' : 'You have not submitted any projects yet. Send your first request to get started.' ?></p>
      <a class="btn btn-primary btn-sm" href="<?= app_url('client/request.php') ?>"><?= icon('plus') ?> New Request</a>
    </div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>