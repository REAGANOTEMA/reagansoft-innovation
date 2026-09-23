<?php
require __DIR__ . '/../config/config.php';
require_staff();

$pdo = db();
$me = current_user();

$st = $_GET['status'] ?? '';
$q  = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add_task') {
        $projectId = (int)($_POST['project_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $assignee = (int)($_POST['assigned_to'] ?? 0) ?: null;
        $priority = $_POST['priority'] ?? 'medium';
        if (!in_array($priority, ['low', 'medium', 'high'], true)) $priority = 'medium';
        $due = trim($_POST['due_date'] ?? '');
        $due = ($due && preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) ? $due : null;
        $errors = [];
        if (!$projectId) $errors[] = 'Select a project for this task.';
        if ($title === '' || mb_strlen($title) > 190) $errors[] = 'Enter a task title (max 190 characters).';
        if ($errors) { set_errors($errors); redirect(app_url('admin/tasks.php?tab=create')); }
        $pdo->prepare('INSERT INTO project_tasks (project_id, assigned_to, title, priority, due_date, created_by, status) VALUES (?,?,?,?,?,?,?)')
            ->execute([$projectId, $assignee, $title, $priority, $due, (int)$me['id'], 'TODO']);
        $taskId = (int)$pdo->lastInsertId();
        audit('task_created', 'project_tasks', $taskId, 'Created task "' . $title . '"');
        $pr = $pdo->query('SELECT client_id, title FROM projects WHERE id = ' . $projectId)->fetch();
        if ($pr) {
            notify((int)$pr['client_id'], 'New task added', 'A new task was added to your project "' . $pr['title'] . '".', 'task', $projectId);
        }
        flash('success', 'Task created.');
        redirect(app_url('admin/tasks.php'));
    }

    if ($action === 'task_status') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], true)) {
            $t = $pdo->query('SELECT project_id, title FROM project_tasks WHERE id = ' . $taskId)->fetch();
            if ($t) {
                $pdo->prepare('UPDATE project_tasks SET status = ? WHERE id = ?')->execute([$status, $taskId]);
                if ($status === 'COMPLETED') {
                    $pdo->prepare('UPDATE project_tasks SET progress = 100 WHERE id = ?')->execute([$taskId]);
                    notify($t['project_id'] > 0 ? (int)$pdo->query('SELECT client_id FROM projects WHERE id = ' . $t['project_id'])->fetch()['client_id'] : 0, 'Task completed', 'Task "' . $t['title'] . '" was completed.', 'task', (int)$t['project_id']);
                }
                audit('task_status_changed', 'project_tasks', $taskId, 'Set task to ' . $status);
                flash('success', 'Task updated.');
            }
        }
        redirect(app_url('admin/tasks.php'));
    }
}

$where = ['1=1'];
$params = [];
if ($st !== '' && in_array(strtoupper($st), ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'], true)) {
    $where[] = 't.status = ?'; $params[] = strtoupper($st);
}
if ($q !== '') {
    $where[] = '(t.title LIKE ? OR p.title LIKE ? OR p.ref_no LIKE ? OR a.full_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = implode(' AND ', $where);

$count = $pdo->prepare("SELECT COUNT(*) c FROM project_tasks t JOIN projects p ON p.id = t.project_id WHERE $whereSql");
$count->execute($params);
$pg = paginate((int)$count->fetch()['c'], $perPage, $page);

$sql = "SELECT t.*, p.title AS project_title, p.ref_no, p.status AS project_status, p.client_id, a.full_name AS assignee_name
        FROM project_tasks t
        JOIN projects p ON p.id = t.project_id
        LEFT JOIN users a ON a.id = t.assigned_to
        WHERE $whereSql
        ORDER BY (t.status = 'COMPLETED') ASC, t.due_date IS NULL ASC, t.due_date ASC, t.created_at DESC
        LIMIT $perPage OFFSET {$pg['offset']}";
$stm = $pdo->prepare($sql); $stm->execute($params);
$tasks = $stm->fetchAll();

$projects = $pdo->query("SELECT id, ref_no, title FROM projects WHERE status NOT IN ('CANCELLED','COMPLETED') ORDER BY submitted_at DESC")->fetchAll();
$team = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('admin','staff') AND active = 1 ORDER BY full_name")->fetchAll();

$base = 'tasks.php?status=' . urlencode($st) . '&q=' . urlencode($q);

dashboard_head(['title' => 'Tasks', 'active' => 'tasks', 'crumb' => 'Tasks']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Task board</span>
    <h2>All project tasks</h2>
    <p class="sub">Every task across every project — see what is due and what needs attention.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form class="toolbar" method="get" action="tasks.php">
  <div class="field" style="margin:0;flex-grow:1;max-width:360px">
    <label class="visually-hidden" for="q">Search tasks</label>
    <input class="input" id="q" name="q" type="search" placeholder="Search task or project…" value="<?= e($q) ?>">
  </div>
  <div class="field" style="margin:0"><label class="visually-hidden" for="status">Status</label>
    <select class="select" id="status" name="status">
      <option value="">All statuses</option>
      <?php foreach (['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'] as $s): ?><option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= e(ucwords(strtolower($s))) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="tasks.php">Reset</a>
  <button class="btn btn-primary" type="button" data-modal-open="add-task-modal" style="margin-left:auto"><?= icon('plus') ?> Add task</button>
</form>

<div class="modal-backdrop" id="add-task-modal">
  <div class="modal">
    <div class="modal-head"><h3>Add task</h3><button type="button" class="modal-close" data-modal-close="add-task-modal" aria-label="Close"><?= icon('close') ?></button></div>
    <form method="post" action="tasks.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_task">
      <div class="field"><label for="project_id">Project *</label>
        <select class="select" id="project_id" name="project_id" required>
          <option value="">Select project…</option>
          <?php foreach ($projects as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['ref_no']) ?> — <?= e(truncate($p['title'], 60)) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label for="title">Task title *</label><input class="input" id="title" name="title" required maxlength="190" placeholder="e.g. Prepare home page design"></div>
      <div class="form-row">
        <div class="field"><label for="assigned_to">Assign to</label>
          <select class="select" id="assigned_to" name="assigned_to">
            <option value="0">Unassigned</option>
            <?php foreach ($team as $t): ?><option value="<?= (int)$t['id'] ?>"><?= e($t['full_name']) ?> (<?= e($t['role']) ?>)</option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="priority">Priority</label>
          <select class="select" id="priority" name="priority">
            <?php foreach ([['low', 'Low'], ['medium', 'Medium'], ['high', 'High']] as $opt): ?><option value="<?= $opt[0] ?>" <?= $opt[0] === 'medium' ? 'selected' : '' ?>><?= $opt[1] ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="field"><label for="due_date">Due date</label><input class="input" id="due_date" name="due_date" type="date"></div>
      <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close="add-task-modal">Cancel</button><button class="btn btn-primary" type="submit"><?= icon('plus') ?> Create task</button></div>
    </form>
  </div>
</div>

<section class="panel">
  <?php if ($tasks): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Task</th><th>Project</th><th>Assignee</th><th>Priority</th><th>Due</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($tasks as $t): ?>
            <tr>
              <td><span class="row-title"><?= e($t['title']) ?></span><div class="row-sub"><?= status_badge($t['project_status']) ?></div></td>
              <td><a class="row-title" href="<?= app_url('admin/project.php?id=' . (int)$t['project_id'] . '&tab=tasks') ?>"><?= e(truncate($t['project_title'], 42)) ?></a><div class="row-sub"><?= e($t['ref_no']) ?></div></td>
              <td class="small"><?= e($t['assignee_name'] ?? 'Unassigned') ?></td>
              <td><span class="tag"><?= e(ucfirst($t['priority'])) ?></span></td>
              <td class="small"><?= $t['due_date'] ? fmt_date($t['due_date']) : '—' ?></td>
              <td><?= status_badge($t['status']) ?></td>
              <td style="min-width:150px">
                <form method="post" style="display:flex;gap:6px;align-items:center">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="task_status">
                  <input type="hidden" name="task_id" value="<?= (int)$t['id'] ?>">
                  <select class="select" name="status" onchange="this.form.submit()" style="padding:7px 30px 7px 10px;font-size:13px">
                    <?php foreach (['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'] as $s): ?><option value="<?= $s ?>" <?= $t['status'] === $s ? 'selected' : '' ?>><?= e(ucwords(strtolower($s))) ?></option><?php endforeach; ?>
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
    <div class="empty"><?= icon('list') ?><h4>No tasks found</h4><p><?= ($q || $st) ? 'No tasks match your filters.' : 'No tasks yet. Create one above or inside a project.' ?></p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>