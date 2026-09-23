<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();

$q    = trim($_GET['q'] ?? '');
$st   = $_GET['status'] ?? '';
$pr   = $_GET['priority'] ?? '';
$cli  = (int)($_GET['client'] ?? 0);
$perPage = 12;
$page = max(1, (int)($_GET['page'] ?? 1));

$where = ['1=1'];
$params = [];

if ($q !== '') {
    $where[] = '(p.title LIKE ? OR p.ref_no LIKE ? OR p.description LIKE ? OR u.full_name LIKE ? OR u.company LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}
if ($st !== '' && in_array(strtoupper($st), project_statuses(), true)) {
    $where[] = 'p.status = ?'; $params[] = strtoupper($st);
}
if ($pr !== '' && array_key_exists($pr, project_priority_labels())) {
    $where[] = 'p.priority = ?'; $params[] = $pr;
}
if ($cli > 0) {
    $where[] = 'p.client_id = ?'; $params[] = $cli;
}

$whereSql = implode(' AND ', $where);
$count = $pdo->prepare("SELECT COUNT(*) c FROM projects p JOIN users u ON u.id = p.client_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT p.*, u.full_name AS client_name, u.company, s.name AS service, a.full_name AS assigned_name
        FROM projects p
        JOIN users u ON u.id = p.client_id
        LEFT JOIN services s ON s.id = p.service_id
        LEFT JOIN users a ON a.id = p.assigned_to
        WHERE $whereSql
        ORDER BY p.submitted_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$projects = $stm->fetchAll();

$clientOptions = $pdo->query("SELECT id, full_name, company FROM users WHERE role='client' ORDER BY full_name")->fetchAll();
$base = 'projects.php?q=' . urlencode($q) . '&status=' . urlencode($st) . '&priority=' . urlencode($pr) . '&client=' . $cli;

dashboard_head(['title' => 'Projects', 'active' => 'projects', 'crumb' => 'Projects']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Project management</span>
    <h2>All projects &amp; requests</h2>
    <p class="sub">Search, filter and open projects to manage status, tasks, files and communication.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="projects.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search projects</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search title, reference, client…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="status">Status</label>
    <select class="select" id="status" name="status">
      <option value="">All statuses</option>
      <?php foreach (project_statuses() as $s): ?><option value="<?= e($s) ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(ucwords(strtolower($s))) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="priority">Priority</label>
    <select class="select" id="priority" name="priority">
      <option value="">All priorities</option>
      <?php foreach (project_priority_labels() as $k => $v): ?><option value="<?= e($k) ?>" <?= $pr === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="client">Client</label>
    <select class="select" id="client" name="client">
      <option value="0">All clients</option>
      <?php foreach ($clientOptions as $c): ?><option value="<?= (int)$c['id'] ?>" <?= $cli === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['full_name']) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="projects.php">Reset</a>
</form>

<section class="panel">
  <?php if ($projects): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Project</th><th>Client</th><th>Status</th><th>Priority</th><th>Progress</th><th>Assigned</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($projects as $p): ?>
            <tr>
              <td>
                <a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$p['id']) ?>"><?= e($p['title']) ?></a>
                <div class="row-sub"><?= e($p['ref_no']) ?> · <?= e($p['service'] ?? 'General') ?></div>
              </td>
              <td><b class="small"><?= e($p['client_name']) ?></b><div class="row-sub"><?= e($p['company'] ?? '—') ?></div></td>
              <td><?= status_badge($p['status']) ?></td>
              <td><?= priority_badge($p['priority']) ?></td>
              <td style="min-width:120px"><div class="progress"><span style="width:<?= max(0, min(100, (int)$p['progress'])) ?>%"></span></div><small class="muted"><?= (int)$p['progress'] ?>%</small></td>
              <td class="small"><?= e($p['assigned_name'] ?? 'Unassigned') ?></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= app_url('admin/project.php?id=' . (int)$p['id']) ?>">Manage <?= icon('arrow') ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty"><?= icon('folder') ?><h4>No projects found</h4><p><?= ($q || $st || $pr || $cli) ? 'No projects match your filters.' : 'There are no projects yet. Client requests will appear here as they are submitted.' ?></p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>