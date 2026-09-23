<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$me = current_user();
$q = trim($_GET['q'] ?? '');
$folder = $_GET['folder'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_file' && user_role() === 'admin') {
        $fid = (int)($_POST['file_id'] ?? 0);
        $f = $pdo->query('SELECT * FROM project_files WHERE id = ' . $fid)->fetch();
        if ($f) {
            $full = UPLOAD_DIR . '/' . $f['file_path'];
            if (is_file($full)) { @unlink($full); }
            $pdo->prepare('DELETE FROM project_files WHERE id = ?')->execute([$fid]);
            audit('file_deleted', 'project_files', $fid, 'Deleted ' . $f['original_name']);
            flash('success', 'File deleted.');
        }
        redirect(app_url('admin/files.php'));
    }
    redirect(app_url('admin/files.php'));
}

$where = ['1=1'];
$params = [];
if ($q !== '') {
    $where[] = '(f.original_name LIKE ? OR p.title LIKE ? OR p.ref_no LIKE ? OR u.full_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($folder !== '' && is_numeric($folder)) {
    $where[] = 'f.project_id = ?'; $params[] = (int)$folder;
}
$whereSql = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) c FROM project_files f JOIN projects p ON p.id = f.project_id JOIN users u ON u.id = f.uploader_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT f.*, u.full_name AS uploader_name, u.role AS uploader_role, p.title AS project_title, p.ref_no, p.id AS project_id
        FROM project_files f
        JOIN projects p ON p.id = f.project_id
        JOIN users u ON u.id = f.uploader_id
        WHERE $whereSql
        ORDER BY f.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$files = $stm->fetchAll();

$projects = $pdo->query("SELECT id, ref_no, title FROM projects ORDER BY submitted_at DESC LIMIT 200")->fetchAll();

$base = 'files.php?q=' . urlencode($q) . '&folder=' . urlencode($folder);
$totalBytes = (float)$pdo->query("SELECT COALESCE(SUM(size_bytes),0) s FROM project_files")->fetch()['s'];

function human_bytes(float $bytes): string {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 1) . ' KB';
    return (int)$bytes . ' B';
}

dashboard_head(['title' => 'Files', 'active' => 'files', 'crumb' => 'Files']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">File library</span>
    <h2>All project files</h2>
    <p class="sub">Every uploaded file across projects. Files are private and served only to authorised users.</p>
  </div>
  <div class="stat"><small>Total storage</small><strong><?= human_bytes($totalBytes) ?></strong></div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="files.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search files</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search filename or project…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="folder">Project</label>
    <select class="select" id="folder" name="folder">
      <option value="">All projects</option>
      <?php foreach ($projects as $p): ?><option value="<?= (int)$p['id'] ?>" <?= $folder === (string)$p['id'] ? 'selected' : '' ?>><?= e($p['ref_no']) ?> — <?= e(truncate($p['title'], 40)) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="files.php">Reset</a>
</form>

<section class="panel">
  <?php if ($files): ?>
    <div class="grid-3">
      <?php foreach ($files as $f): $ext = strtolower(pathinfo($f['original_name'], PATHINFO_EXTENSION)); ?>
        <article class="file-card stack">
          <div style="display:flex;gap:12px;align-items:center">
            <span class="fc-icon"><?= icon(in_array($ext, ['pdf'], true) ? 'doc' : 'file') ?></span>
            <div style="min-width:0">
              <b class="small"><?= e($f['original_name']) ?></b>
              <div class="row-sub"><?= human_bytes((float)$f['size_bytes']) ?> · <?= strtoupper((string)$ext) ?></div>
            </div>
          </div>
          <div class="kv" style="padding:5px 0"><span>Project</span><span><a href="<?= app_url('admin/project.php?id=' . (int)$f['project_id'] . '&tab=files') ?>"><?= e($f['ref_no']) ?></a></span></div>
          <div class="kv" style="padding:5px 0"><span>Uploaded by</span><span><?= e($f['uploader_name']) ?></span></div>
          <div class="kv" style="padding:5px 0"><span>Uploaded</span><span><?= time_ago($f['created_at']) ?></span></div>
          <div style="display:flex;gap:8px;margin-top:auto;padding-top:10px;flex-wrap:wrap">
            <a class="btn btn-primary btn-sm" href="<?= app_url('download.php?id=' . (int)$f['id']) ?>"><?= icon('download') ?> Download</a>
            <?php if (user_role() === 'admin'): ?>
              <form method="post" data-confirm="Delete this file permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_file">
                <input type="hidden" name="file_id" value="<?= (int)$f['id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Delete</button>
              </form>
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php pagination_links($pg['totalPages'], $pg['page'], $base); ?>
  <?php else: ?>
    <div class="empty"><?= icon('file') ?><h4>No files found</h4><p><?= $q ? 'No files match your search.' : 'Files uploaded in projects will appear here.' ?></p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>