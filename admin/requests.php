<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();

$requests = $pdo->query(
    "SELECT p.*, u.full_name AS client_name, u.company, u.email, u.phone, s.name AS service
     FROM projects p
     JOIN users u ON u.id = p.client_id
     LEFT JOIN services s ON s.id = p.service_id
     WHERE p.status IN ('NEW','REVIEWING')
     ORDER BY p.submitted_at ASC"
)->fetchAll();

dashboard_head(['title' => 'Requests Queue', 'active' => 'requests', 'crumb' => 'Requests']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Requests queue</span>
    <h2>New &amp; reviewing requests</h2>
    <p class="sub">Requests waiting for review. Open each one to update status, assign staff and create a quotation.</p>
  </div>
</div>

<?php render_alerts(); ?>

<?php if ($requests): ?>
  <div class="grid-3">
    <?php foreach ($requests as $r): ?>
      <article class="folio-card" style="display:flex;flex-direction:column">
        <div class="folio-body">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:10px">
            <?= status_badge($r['status']) ?>
            <span class="small muted"><?= time_ago($r['created_at']) ?></span>
          </div>
          <h3 style="font-size:17px;margin:0 0 4px"><?= e($r['title']) ?></h3>
          <div class="row-sub" style="margin-bottom:8px"><?= e($r['ref_no']) ?> · <?= e($r['service'] ?? 'General') ?></div>
          <p class="muted small" style="flex-grow:1"><?= e(truncate($r['description'], 180)) ?></p>
          <div class="kv" style="padding:6px 0"><span>Client</span><span><?= e($r['client_name']) ?> <?= e($r['company'] ?? '') ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Contact</span><span><?= e($r['phone']) ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Budget</span><span><?= $r['budget'] !== null ? money($r['budget'], settings('currency')) : '—' ?></span></div>
          <div class="kv" style="padding:6px 0"><span>Priority</span><span><?= priority_badge($r['priority']) ?></span></div>
          <a class="btn btn-primary btn-block mt-2" href="<?= app_url('admin/project.php?id=' . (int)$r['id']) ?>">Review &amp; respond <?= icon('arrow') ?></a>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <section class="panel"><div class="empty"><?= icon('inbox') ?><h4>No waiting requests</h4><p>Every request has been reviewed. New ones will appear here as clients submit them.</p></div></section>
<?php endif; ?>
<?php dashboard_footer(); ?>