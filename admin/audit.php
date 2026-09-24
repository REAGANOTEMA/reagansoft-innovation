<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$q = trim($_GET['q'] ?? '');
$entity = $_GET['entity'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(l.action LIKE ? OR l.details LIKE ? OR u.full_name LIKE ? OR l.ip_address LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if (preg_match('/^[a-z_]+$/', $entity)) {
    $where[] = 'l.entity_type = ?'; $params[] = $entity;
}
$whereSql = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) c FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT l.*, u.full_name AS user_name, u.role AS user_role
        FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id
        WHERE $whereSql
        ORDER BY l.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$logs = $stm->fetchAll();

$entities = $pdo->query("SELECT entity_type, COUNT(*) c FROM activity_logs WHERE entity_type IS NOT NULL GROUP BY entity_type ORDER BY c DESC")->fetchAll();

$base = 'audit.php?q=' . urlencode($q) . '&entity=' . urlencode($entity);

dashboard_head(['title' => 'Audit Logs', 'active' => 'audit', 'crumb' => 'Audit Logs']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Audit trail</span>
    <h2>Activity logs</h2>
    <p class="sub">Every meaningful action is recorded here — a complete trail for accountability.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="audit.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search logs</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search action, user, IP…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="entity">Entity</label>
    <select class="select" id="entity" name="entity">
      <option value="">All entities</option>
      <?php foreach ($entities as $e): ?><option value="<?= e($e['entity_type']) ?>" <?= $entity === $e['entity_type'] ? 'selected' : '' ?>><?= e(str_replace('_', ' ', $e['entity_type'])) ?> (<?= (int)$e['c'] ?>)</option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="audit.php">Reset</a>
</form>

<section class="panel">
  <?php if ($logs): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td class="small"><?= fmt_date($l['created_at'], 'd M Y H:i') ?></td>
              <td class="small"><?= e($l['user_name'] ?? 'Guest') ?><?= $l['user_role'] ? ' <span class="badge gray">' . e($l['user_role']) . '</span>' : '' ?></td>
              <td class="small"><?= e(str_replace('_', ' ', $l['action'])) ?><div class="row-sub"><?= e(str_replace('_', ' ', truncate($l['details'] ?? '', 90))) ?></div></td>
              <td class="small"><?= e(str_replace('_', ' ', $l['entity_type'] ?? '—')) ?><?= $l['entity_id'] ? ' #' . (int)$l['entity_id'] : '' ?></td>
              <td class="small"><?= e($l['ip_address'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty"><?= icon('search') ?><h4>No activity yet</h4><p>Actions take place, they are recorded here.</p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>