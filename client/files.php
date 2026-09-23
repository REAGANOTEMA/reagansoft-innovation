<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$uid = (int)current_user()['id'];

$files = $pdo->prepare(
    "SELECT f.*, p.title AS project_title, p.ref_no FROM project_files f
     JOIN projects p ON p.id = f.project_id
     WHERE p.client_id = ? ORDER BY f.created_at DESC"
);
$files->execute([$uid]);
$files = $files->fetchAll();

dashboard_head(['title' => 'Files', 'active' => 'files', 'crumb' => 'Files']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Files</span>
    <h2>Your project files</h2>
    <p class="sub">Supporting documents and deliverables shared across your projects. Files are private and access-controlled.</p>
  </div>
</div>

<?php render_alerts(); ?>

<section class="panel">
  <?php if ($files): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>File</th><th>Project</th><th>Size</th><th>Uploaded</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($files as $f): ?>
            <tr>
              <td><div class="row-title"><?= e($f['original_name']) ?></div><div class="row-sub"><?= e($f['mime_type']) ?></div></td>
              <td><a class="small" href="<?= app_url('client/project.php?id=' . (int)$f['project_id'] . '&tab=files') ?>"><?= e($f['project_title']) ?></a></td>
              <td class="muted small"><?= pretty_size((int)$f['size_bytes']) ?></td>
              <td class="muted small"><?= fmt_date($f['created_at']) ?></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= app_url('download.php?id=' . (int)$f['id']) ?>"><?= icon('download') ?> Download</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty">
      <?= icon('file') ?>
      <h4>No files yet</h4>
      <p>Files attached to your requests and shared as project deliverables will appear here.</p>
    </div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>