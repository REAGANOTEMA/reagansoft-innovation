<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$me = current_user();
$tab = $_GET['tab'] ?? 'projects';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'reply_message') {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $internal = isset($_POST['is_internal']);
        if ($projectId && $message !== '') {
            $pdo->prepare('INSERT INTO project_messages (project_id, sender_id, sender_role, message, is_internal) VALUES (?,?,?,?,?)')
                ->execute([$projectId, (int)$me['id'], $me['role'], $message, $internal ? 1 : 0]);
            $msgId = (int)$pdo->lastInsertId();
            audit('message_sent', 'project_messages', $msgId, 'Sent ' . ($internal ? 'internal note' : 'message') . ' on project #' . $projectId);
            if (!$internal) {
                $pr = $pdo->query('SELECT client_id, ref_no FROM projects WHERE id = ' . $projectId)->fetch();
                if ($pr) notify((int)$pr['client_id'], 'New message', 'A new message was posted on ' . $pr['ref_no'] . '.', 'project', $projectId);
            }
            flash('success', 'Message sent.');
        } else {
            set_errors(['Message cannot be empty.']);
        }
        redirect(app_url('admin/messages.php?tab=projects'));
    }

    if ($action === 'contact_read') {
        $cid = (int)($_POST['contact_id'] ?? 0);
        $mark = (int)($_POST['read'] ?? 1) === 1 ? 1 : 0;
        $pdo->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?')->execute([$mark, $cid]);
        flash('success', 'Contact message updated.');
        redirect(app_url('admin/messages.php?tab=contact'));
    }

    if ($action === 'contact_delete') {
        $cid = (int)($_POST['contact_id'] ?? 0);
        $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$cid]);
        audit('contact_message_deleted', 'contact_messages', $cid, 'Deleted a contact submission');
        flash('success', 'Contact message deleted.');
        redirect(app_url('admin/messages.php?tab=contact'));
    }
}

/* project conversations */
$convs = $pdo->query(
    "SELECT m.*, u.full_name AS sender_name, p.title AS project_title, p.ref_no, p.client_id,
            (SELECT COUNT(*) FROM project_messages m2 WHERE m2.project_id = m.project_id AND m2.is_internal = 0 AND m2.sender_role = 'client') AS client_msgs
     FROM project_messages m
     JOIN users u ON u.id = m.sender_id
     JOIN projects p ON p.id = m.project_id
     WHERE m.created_at = (SELECT MAX(created_at) FROM project_messages m3 WHERE m3.project_id = m.project_id)
     ORDER BY m.created_at DESC"
)->fetchAll();

/* contact inbox */
$contactUnread = (int)$pdo->query("SELECT COUNT(*) c FROM contact_messages WHERE is_read = 0")->fetch()['c'];
$contacts = $pdo->query("SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC LIMIT 60")->fetchAll();

dashboard_head(['title' => 'Messages', 'active' => 'messages', 'crumb' => 'Messages']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Communication</span>
    <h2>Messages &amp; contact inbox</h2>
    <p class="sub">Project conversations with clients (internal notes stay private) and messages from the public contact form.</p>
  </div>
</div>

<?php render_alerts(); ?>

<nav class="tabs" aria-label="Message sections">
  <a class="<?= $tab === 'projects' ? 'active' : '' ?>" href="?tab=projects">Project messages</a>
  <a class="<?= $tab === 'contact' ? 'active' : '' ?>" href="?tab=contact">Contact inbox <?php if ($contactUnread): ?><span class="badge blue"><?= $contactUnread ?></span><?php endif; ?></a>
</nav>

<?php if ($tab === 'projects'): ?>
  <?php if ($convs): ?>
    <div class="panel">
      <?php foreach ($convs as $m): ?>
        <div class="convo-row" style="padding:18px 0;border-bottom:1px solid var(--line);display:flex;gap:16px;justify-content:space-between;align-items:center;flex-wrap:wrap">
          <div style="flex-grow:1;min-width:240px">
            <a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$m['project_id'] . '&tab=messages') ?>"><?= e($m['project_title']) ?></a>
            <div class="row-sub"><?= e($m['ref_no']) ?> · <?= $m['sender_role'] === 'client' ? '<b class="badge green">Client</b>' : '<span class="badge blue">Staff</span>' ?> <?= e($m['sender_name']) ?> · <?= time_ago($m['created_at']) ?></div>
            <p class="small mt-1" style="margin-bottom:0"><?= e(truncate($m['message'], 200)) ?></p>
            <p class="small muted mt-1" style="margin-bottom:0"><?= (int)$m['client_msgs'] ?> client message<?= (int)$m['client_msgs'] === 1 ? '' : 's' ?> in this thread</p>
          </div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <a class="btn btn-primary btn-sm" href="<?= app_url('admin/project.php?id=' . (int)$m['project_id'] . '&tab=messages') ?>">Open thread <?= icon('arrow') ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <section class="panel">
      <div class="panel-head"><h3>Send a message to a project</h3></div>
      <form method="post" action="messages.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="reply_message">
        <div class="field"><label for="project_id">Project *</label>
          <select class="select" id="project_id" name="project_id" required>
            <option value="">Select project…</option>
            <?php $projects = $pdo->query("SELECT p.id, p.ref_no, p.title, u.full_name AS client_name FROM projects p JOIN users u ON u.id = p.client_id WHERE p.status NOT IN ('CANCELLED') ORDER BY p.submitted_at DESC")->fetchAll(); ?>
            <?php foreach ($projects as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['ref_no']) ?> — <?= e(truncate($p['title'], 50)) ?> (<?= e($p['client_name']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="message">Message *</label><textarea class="textarea" id="message" name="message" required rows="4" placeholder="Write your reply to the client…"></textarea></div>
        <label class="check" style="display:flex;gap:8px;align-items:center;margin:10px 0">
          <input type="checkbox" name="is_internal" value="1"><span class="small">Internal note — visible only to staff (the client will not see it)</span>
        </label>
        <button class="btn btn-primary" type="submit"><?= icon('send') ?> Send message</button>
      </form>
    </section>
  <?php else: ?>
    <section class="panel"><div class="empty"><?= icon('chat') ?><h4>No project messages yet</h4><p>Messages inside projects will appear here.</p></div></section>
  <?php endif; ?>

<?php else: ?>
  <?php if ($contacts): ?>
    <?php foreach ($contacts as $c): ?>
      <section class="panel <?= $c['is_read'] ? '' : 'is-unread' ?>" style="<?= $c['is_read'] ? '' : 'border-left:4px solid var(--blue)' ?>">
        <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:flex-start">
          <div>
            <h3 style="margin:0 0 2px"><?= e($c['full_name']) ?><?= $c['is_read'] ? '' : ' <span class="badge blue">New</span>' ?></h3>
            <p class="row-sub" style="margin:0">
              <?= e($c['company'] ?: 'Individual') ?> · <?= e($c['email']) ?><?= $c['phone'] ? ' · ' . e($c['phone']) : '' ?>
            </p>
            <p class="row-sub"><?= $c['subject'] ? '<b>' . e($c['subject']) . '</b> · ' : '' ?>Received <?= time_ago($c['created_at']) ?></p>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a class="btn btn-primary btn-sm" href="mailto:<?= e($c['email']) ?>?subject=Re:<?= e($c['subject'] ?? 'Your message') ?>"><?= icon('mail') ?> Reply by email</a>
            <form method="post">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="contact_read">
              <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
              <input type="hidden" name="read" value="<?= $c['is_read'] ? '0' : '1' ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= $c['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
            </form>
            <form method="post" data-confirm="Delete this contact message permanently?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="contact_delete">
              <input type="hidden" name="contact_id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= icon('trash') ?> Delete</button>
            </form>
          </div>
        </div>
        <div class="panel-quote mt-2" style="background:var(--sky-50);border-radius:var(--radius);padding:14px 16px;color:var(--ink-2)"><?= nl2br(e($c['message'])) ?></div>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
    <section class="panel"><div class="empty"><?= icon('mail') ?><h4>Contact inbox is empty</h4><p>Messages submitted through the public contact form appear here.</p></div></section>
  <?php endif; ?>
<?php endif; ?>
<?php dashboard_footer(); ?>