<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$uid = (int)current_user()['id'];

$threads = $pdo->prepare(
    "SELECT p.id AS project_id, p.title, p.ref_no,
            (SELECT COUNT(*) FROM project_messages m WHERE m.project_id = p.id AND m.sender_id != ?) AS others,
            (SELECT MAX(m.created_at) FROM project_messages m WHERE m.project_id = p.id) AS last_at
     FROM projects p WHERE p.client_id = ? ORDER BY last_at DESC, p.submitted_at DESC"
);
$threads->execute([$uid, $uid]);
$threads = $threads->fetchAll();

dashboard_head(['title' => 'Messages', 'active' => 'messages', 'crumb' => 'Messages']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Messages</span>
    <h2>Project conversations</h2>
    <p class="sub">Messages are organised per project. Open a project and go to the Messages tab to reply.</p>
  </div>
</div>

<?php render_alerts(); ?>

<section class="panel">
  <?php if ($threads): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Project</th><th>New replies</th><th>Last activity</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($threads as $t): ?>
            <tr>
              <td>
                <a class="row-title" href="<?= app_url('client/project.php?id=' . (int)$t['project_id'] . '&tab=messages') ?>"><?= e($t['title']) ?></a>
                <div class="row-sub"><?= e($t['ref_no']) ?></div>
              </td>
              <td><?= (int)$t['others'] > 0 ? '<span class="badge blue">' . (int)$t['others'] . ' from the team</span>' : '<span class="muted small">None</span>' ?></td>
              <td class="muted small"><?= $t['last_at'] ? time_ago($t['last_at']) : '—' ?></td>
              <td><a class="btn btn-ghost btn-sm" href="<?= app_url('client/project.php?id=' . (int)$t['project_id'] . '&tab=messages') ?>">Open <?= icon('arrow') ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty">
      <?= icon('chat') ?>
      <h4>No conversations yet</h4>
      <p>Once you submit a project, you can exchange messages with the team about it.</p>
      <a class="btn btn-primary btn-sm" href="<?= app_url('client/request.php') ?>"><?= icon('plus') ?> New Request</a>
    </div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>