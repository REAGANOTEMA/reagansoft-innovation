<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$me = current_user();
$roleF = $_GET['role'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '') ?: null;
        $role = $_POST['role'] ?? 'staff';
        $company = trim($_POST['company'] ?? '') ?: null;
        $password = $_POST['password'] ?? '';
        $errors = [];
        if ($fullName === '' || mb_strlen($fullName) > 150) $errors[] = 'Enter a full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (mb_strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        $dup = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $dup->execute([$email]);
        if ($dup->fetch()) $errors[] = 'A user with that email already exists.';
        if (!in_array($role, ['admin', 'staff', 'client'], true)) $role = 'staff';
        if (!$errors) {
            $pdo->prepare('INSERT INTO users (full_name, email, phone, password_hash, role, company, active) VALUES (?,?,?,?,?,?,1)')
                ->execute([$fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT), $role, $company]);
            $uid = (int)$pdo->lastInsertId();
            audit('user_created', 'users', $uid, 'Created ' . $role . ' account for ' . $fullName);
            flash('success', 'Account created for ' . $fullName . '.');
        } else {
            set_errors($errors);
        }
        redirect(app_url('admin/users.php'));
    }

    if ($action === 'toggle_active') {
        $uid = (int)($_POST['user_id'] ?? 0);
        $active = (int)($_POST['active'] ?? 1) === 1 ? 1 : 0;
        $row = $pdo->query('SELECT full_name FROM users WHERE id = ' . $uid)->fetch();
        if ($row && $uid !== (int)$me['id']) {
            $pdo->prepare('UPDATE users SET active = ? WHERE id = ?')->execute([$active, $uid]);
            audit('user_status_changed', 'users', $uid, ($active ? 'Activated' : 'Deactivated') . ' ' . $row['full_name']);
            flash('success', $row['full_name'] . ' ' . ($active ? 'reactivated' : 'deactivated') . '.');
        } else {
            set_errors(['You cannot suspend your own account.']);
        }
        redirect(app_url('admin/users.php'));
    }
}

$where = ['1=1'];
$params = [];
if (in_array($roleF, ['admin', 'staff', 'client'], true)) {
    $where[] = 'u.role = ?'; $params[] = $roleF;
}
$whereSql = implode(' AND ', $where);

$st = $pdo->prepare("SELECT u.id, u.full_name, u.email, u.phone, u.role, u.company, u.active, u.last_login_at, u.created_at,
    (SELECT COUNT(*) FROM projects p WHERE p.client_id = u.id) AS project_count
    FROM users u WHERE $whereSql ORDER BY u.created_at DESC LIMIT 200");
$st->execute($params);
$users = $st->fetchAll();

dashboard_head(['title' => 'Users', 'active' => 'users', 'crumb' => 'Users']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">User accounts</span>
    <h2>Staff &amp; accounts</h2>
    <p class="sub">Create staff accounts and manage who can access the system.</p>
  </div>
  <button class="btn btn-primary" type="button" data-modal-open="user-modal"><?= icon('plus') ?> Add user</button>
</div>

<?php render_alerts(); ?>

<div class="modal-backdrop" id="user-modal">
  <div class="modal">
    <div class="modal-head"><h3>Add a staff account</h3><button type="button" class="modal-close" data-modal-close="user-modal" aria-label="Close"><?= icon('close') ?></button></div>
    <form method="post" action="users.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create_user">
      <div class="form-row">
        <div class="field"><label for="full_name">Full name *</label><input class="input" id="full_name" name="full_name" required maxlength="150"></div>
        <div class="field"><label for="email">Email *</label><input class="input" id="email" name="email" type="email" required maxlength="190"></div>
      </div>
      <div class="form-row">
        <div class="field"><label for="phone">Phone</label><input class="input" id="phone" name="phone" maxlength="40"></div>
        <div class="field"><label for="role">Role</label>
          <select class="select" id="role" name="role">
            <option value="staff">Staff</option>
            <option value="admin">Administrator</option>
          </select>
        </div>
      </div>
      <div class="field"><label for="company">Company (optional)</label><input class="input" id="company" name="company" maxlength="190"></div>
      <div class="field"><label for="password">Temporary password *</label><input class="input" id="password" name="password" type="password" required minlength="8"></div>
      <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close="user-modal">Cancel</button><button class="btn btn-primary" type="submit"><?= icon('check') ?> Create account</button></div>
    </form>
  </div>
</div>

<form class="toolbar" method="get" action="users.php">
  <div class="field" style="margin:0"><label class="visually-hidden" for="role">Role</label>
    <select class="select" id="role" name="role">
      <option value="">All roles</option>
      <?php foreach (['admin', 'staff', 'client'] as $r): ?><option value="<?= $r ?>" <?= $roleF === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?></option><?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-ghost" type="submit"><?= icon('search') ?> Filter</button>
  <a class="btn btn-ghost" href="users.php">Reset</a>
</form>

<section class="panel">
  <?php if ($users): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>User</th><th>Role</th><th>Phone</th><th>Company</th><th>Projects</th><th>Last login</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><span class="row-title"><?= e($u['full_name']) ?><?= (int)$u['id'] === (int)$me['id'] ? ' <span class="badge blue">You</span>' : '' ?></span><div class="row-sub"><?= e($u['email']) ?></div></td>
              <td><?= $u['role'] === 'admin' ? '<span class="badge violet">Admin</span>' : ($u['role'] === 'staff' ? '<span class="badge sky">Staff</span>' : '<span class="badge gray">Client</span>') ?></td>
              <td class="small"><?php if (!empty($u['phone'])): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $u['phone'])) ?>"><?= e($u['phone']) ?></a><?php else: ?><span class="muted">—</span><?php endif; ?></td>
              <td class="small"><?= e($u['company'] ?? '—') ?></td>
              <td class="small"><?= $u['role'] === 'client' ? (int)$u['project_count'] : '—' ?></td>
              <td class="small"><?= $u['last_login_at'] ? time_ago($u['last_login_at']) : 'Never' ?></td>
              <td><?= $u['active'] ? '<span class="badge green">Active</span>' : '<span class="badge red">Suspended</span>' ?></td>
              <td>
                <?php if ((int)$u['id'] !== (int)$me['id']): ?>
                  <form method="post" data-confirm="<?= $u['active'] ? 'Deactivate this account?' : 'Activate this account?' ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                    <input type="hidden" name="active" value="<?= $u['active'] ? '0' : '1' ?>">
                    <button class="btn btn-ghost btn-sm" type="submit"><?= $u['active'] ? 'Deactivate' : 'Activate' ?></button>
                  </form>
                <?php else: ?><span class="muted small">—</span><?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty"><?= icon('users') ?><h4>No users found</h4><p>Accounts appear here as they are created.</p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>