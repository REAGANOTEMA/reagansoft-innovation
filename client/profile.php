<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$user = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'profile_update') {
        $name    = trim($_POST['full_name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $email   = strtolower(trim($_POST['email'] ?? ''));
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || mb_strlen($name) > 150)                      $errors[] = 'Please enter your full name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))                  $errors[] = 'Please enter a valid email address.';
        if ($phone === '' || mb_strlen($phone) > 40)                     $errors[] = 'Please enter your phone number.';
        if (mb_strlen($company) > 190 || mb_strlen($address) > 255)      $errors[] = 'One of the fields is too long.';
        if (!$errors) {
            $st = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $st->execute([$email, (int)$user['id']]);
            if ($st->fetch()) $errors[] = 'That email address is already in use by another account.';
        }

        $avatarPath = $user['avatar'];
        if (empty($errors) && !empty($_FILES['avatar']['name'])) {
            $res = upload_file($_FILES['avatar']);
            if (!$res['ok']) {
                $errors[] = 'Avatar: ' . $res['error'];
            } else {
                $avatarPath = $res['path'];
            }
        }

        if (!$errors) {
            $pdo->prepare('UPDATE users SET full_name = ?, company = ?, email = ?, phone = ?, address = ?, avatar = ? WHERE id = ?')
                ->execute([$name, $company ?: null, $email, $phone, $address ?: null, $avatarPath, (int)$user['id']]);
            $_SESSION['name'] = $name;
            audit('profile_updated', 'users', (int)$user['id'], 'Client updated profile');
            flash('success', 'Your profile has been updated.');
            redirect(app_url('client/profile.php'));
        }
        set_errors($errors);
    }

    if ($action === 'password_update') {
        $current = (string)($_POST['current_password'] ?? '');
        $new     = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if (!password_verify($current, $user['password_hash'])) {
            $errors[] = 'Your current password is incorrect.';
        }
        if (strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }
        if ($new !== $confirm) {
            $errors[] = 'The new passwords do not match.';
        }
        if ($current !== '' && $new !== '' && $current === $new) {
            $errors[] = 'New password must be different from your current password.';
        }
        if (!$errors) {
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                ->execute([password_hash($new, PASSWORD_DEFAULT), (int)$user['id']]);
            audit('password_changed', 'users', (int)$user['id'], 'Client changed password');
            notify((int)$user['id'], 'Password changed', 'Your account password was changed successfully.', 'info');
            flash('success', 'Your password has been updated.');
            redirect(app_url('client/profile.php'));
        }
        set_errors($errors);
    }

    if (in_array($action, ['profile_update', 'password_update'], true)) {
        redirect(app_url('client/profile.php'));
    }
}

dashboard_head(['title' => 'My Profile', 'active' => 'profile', 'crumb' => 'Profile']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Profile</span>
    <h2>My account settings</h2>
    <p class="sub">Update your contact details and password. Your role cannot be changed from here.</p>
  </div>
</div>

<?php render_alerts(); ?>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head"><h3>Personal details</h3></div>
    <form method="post" enctype="multipart/form-data" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="profile_update">
      <div class="field"><label for="full_name">Full name <span class="req">*</span></label><input class="input" id="full_name" name="full_name" required maxlength="150" value="<?= e($user['full_name']) ?>"></div>
      <div class="field"><label for="company">Company / business name</label><input class="input" id="company" name="company" maxlength="190" value="<?= e($user['company'] ?? '') ?>"></div>
      <div class="form-row">
        <div class="field"><label for="email">Email <span class="req">*</span></label><input class="input" id="email" type="email" name="email" required maxlength="190" value="<?= e($user['email']) ?>"></div>
        <div class="field"><label for="phone">Phone <span class="req">*</span></label><input class="input" id="phone" name="phone" required maxlength="40" value="<?= e($user['phone'] ?? '') ?>"></div>
      </div>
      <div class="field"><label for="address">Address</label><input class="input" id="address" name="address" maxlength="255" value="<?= e($user['address'] ?? '') ?>"></div>
      <div class="field">
        <label for="avatar">Profile photo (optional)</label>
        <input class="input" id="avatar" type="file" name="avatar" accept=".png,.jpg,.jpeg,.gif,.webp">
        <div class="form-note">PNG, JPG, GIF or WebP — max 10 MB.</div>
      </div>
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Save changes</button>
    </form>
  </section>

  <section class="panel">
    <div class="panel-head"><h3>Change password</h3></div>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="password_update">
      <div class="field"><label for="current_password">Current password <span class="req">*</span></label><input class="input" id="current_password" type="password" name="current_password" required autocomplete="current-password"></div>
      <div class="form-row">
        <div class="field"><label for="new_password">New password <span class="req">*</span></label><input class="input" id="new_password" type="password" name="new_password" required minlength="8" autocomplete="new-password"></div>
        <div class="field"><label for="confirm_password">Confirm new password <span class="req">*</span></label><input class="input" id="confirm_password" type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></div>
      </div>
      <div class="form-note mb-2">Minimum 8 characters.</div>
      <button class="btn btn-dark" type="submit"><?= icon('lock') ?> Update password</button>
    </form>

    <div class="panel-head" style="margin-top:26px"><h3>Account information</h3></div>
    <div class="kv"><span>Role</span><span><?= e(ucfirst($user['role'])) ?></span></div>
    <div class="kv"><span>Member since</span><span><?= fmt_date($user['created_at'], 'd M Y') ?></span></div>
    <div class="kv"><span>Last login</span><span><?= fmt_date($user['last_login_at'], 'd M Y H:i') ?></span></div>
  </section>
</div>
<?php dashboard_footer(); ?>