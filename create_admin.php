<?php
/**
 * Reagan Soft Innovation Limited — secure admin bootstrap.
 *
 * SECURITY: this script must be DELETED after the admin account is created.
 * It refuses to run when the database already contains an active admin,
 * so it is safe to leave temporarily during installation.
 */

require __DIR__ . '/config/config.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $existing = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='admin'")->fetch()['c'];
    $blocked = (int)$existing > 0;
} else {
    verify_csrf();
    $existing = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='admin'")->fetch()['c'];
    $blocked = (int)$existing > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $errors = [];

    if ($fullName === '' || mb_strlen($fullName) > 150) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $ins = $pdo->prepare(
            "INSERT INTO users (full_name, email, phone, password_hash, role, active) VALUES (?,?,?,?,'admin',1)"
        );
        $phone = trim($_POST['phone'] ?? '') ?: null;
        $ins->execute([$fullName, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
        audit('admin_created', 'users', (int)$pdo->lastInsertId(), 'Initial administrator account created');
        $blocked = true;
        $done = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install · Reagan Soft Innovation</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
  <a class="brand" href="index.php"><img src="assets/img/reagansoftinnovation-logo.jpeg" alt="Reagan Soft Innovation logo"><span class="brand-text">Reagan Soft <b>Innovation</b></span></a>
  <h2>Create administrator</h2>
  <p class="auth-sub">One-time setup. This page is disabled once an admin exists.</p>

  <?php if (isset($done)): ?>
    <div class="alert success">Administrator account created. You can now <a href="login.php">sign in</a>.</div>
    <div class="alert info">Delete <code>create_admin.php</code> from the server before going live.</div>
  <?php else: ?>
    <?php if ($blocked): ?>
      <div class="alert info">An administrator account already exists. Delete this file and sign in from <a href="login.php">login.php</a>.</div>
    <?php else: ?>
      <?php if (!empty($errors)): foreach ($errors as $err): ?>
        <div class="alert"><?= e($err) ?></div>
      <?php endforeach; endif; ?>
      <form method="post">
        <?= csrf_field() ?>
        <div class="field"><label for="full_name">Full name *</label><input class="input" id="full_name" name="full_name" required maxlength="150"></div>
        <div class="form-row">
          <div class="field"><label for="email">Email *</label><input class="input" id="email" type="email" name="email" required maxlength="190"></div>
          <div class="field"><label for="phone">Phone</label><input class="input" id="phone" name="phone" maxlength="40"></div>
        </div>
        <div class="form-row">
          <div class="field"><label for="password">Password *</label><input class="input" id="password" type="password" name="password" minlength="8" required autocomplete="new-password"></div>
          <div class="field"><label for="confirm">Confirm password *</label><input class="input" id="confirm" type="password" name="confirm" minlength="8" required autocomplete="new-password"></div>
        </div>
        <div class="hint mb-2">Minimum 8 characters. Use a strong, unique password.</div>
        <button class="btn btn-primary btn-block">Create administrator</button>
      </form>
    <?php endif; ?>
  <?php endif; ?>
</div>
</body>
</html>