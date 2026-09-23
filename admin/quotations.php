<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$st = $_GET['status'] ?? '';
$q  = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'quote_status') {
        $quoteId = (int)($_POST['quotation_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'], true)) {
            $qu = $pdo->query('SELECT project_id, quotation_no FROM quotations WHERE id = ' . $quoteId)->fetch();
            if ($qu) {
                $pdo->prepare('UPDATE quotations SET status = ? WHERE id = ?')->execute([$status, $quoteId]);
                if ($status === 'accepted') {
                    $pdo->prepare("UPDATE projects SET status = 'QUOTATION' WHERE id = ? AND status IN ('NEW','REVIEWING')")->execute([(int)$qu['project_id']]);
                }
                audit('quotation_updated', 'quotation', $quoteId, $qu['quotation_no'] . ' marked ' . $status);
                flash('success', 'Quotation updated.');
            }
        }
        redirect(app_url('admin/quotations.php'));
    }
}

$where = ['1=1'];
$params = [];
if (in_array($st, ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'], true)) {
    $where[] = 'q.status = ?'; $params[] = $st;
}
if ($q !== '') {
    $where[] = '(q.quotation_no LIKE ? OR u.full_name LIKE ? OR u.company LIKE ? OR p.title LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) c FROM quotations q JOIN projects p ON p.id = q.project_id JOIN users u ON u.id = q.client_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT q.*, u.full_name AS client_name, u.company, p.title AS project_title, p.ref_no AS project_ref, p.id AS project_id
        FROM quotations q
        JOIN projects p ON p.id = q.project_id
        JOIN users u ON u.id = q.client_id
        WHERE $whereSql
        ORDER BY q.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$quotes = $stm->fetchAll();

$base = 'quotations.php?status=' . urlencode($st) . '&q=' . urlencode($q);
$currency = settings('currency');

dashboard_head(['title' => 'Quotations', 'active' => 'quotations', 'crumb' => 'Quotations']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Quotations</span>
    <h2>All quotations</h2>
    <p class="sub">Every quotation sent to clients, with quick status control and a trail back to each project.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="quotations.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search quotations</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search quotation, client, project…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="status">Status</label>
    <select class="select" id="status" name="status">
      <option value="">All statuses</option>
      <?php foreach (['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="quotations.php">Reset</a>
</form>

<section class="panel">
  <?php if ($quotes): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Quotation</th><th>Client</th><th>Project</th><th>Total</th><th>Expires</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($quotes as $qu): ?>
            <tr>
              <td><a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$qu['project_id'] . '&tab=quotation') ?>"><?= e($qu['quotation_no']) ?></a><div class="row-sub">Issued <?= fmt_date($qu['issued_on']) ?></div></td>
              <td><b class="small"><?= e($qu['client_name']) ?></b><div class="row-sub"><?= e($qu['company'] ?? '—') ?></div></td>
              <td class="small"><?= e(truncate($qu['project_title'], 38)) ?><div class="row-sub"><?= e($qu['project_ref']) ?></div></td>
              <td><b><?= money($qu['total'], $currency) ?></b></td>
              <td class="small"><?= fmt_date($qu['expiry_date']) ?></td>
              <td><?= status_badge($qu['status']) ?></td>
              <td style="min-width:150px">
                <form method="post">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="quote_status">
                  <input type="hidden" name="quotation_id" value="<?= (int)$qu['id'] ?>">
                  <select class="select" name="status" onchange="this.form.submit()" style="padding:7px 30px 7px 10px;font-size:13px">
                    <?php foreach (['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'] as $s): ?><option value="<?= $s ?>" <?= $qu['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option><?php endforeach; ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty"><?= icon('doc') ?><h4>No quotations found</h4><p><?= ($q || $st) ? 'No quotations match your filters.' : 'Create quotations from inside a project when a request is ready.' ?></p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>