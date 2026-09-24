<?php
require __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect(app_url(user_role() === 'client' ? 'client/index.php' : 'admin/index.php'));
}

$error = '';
$email = trim($_POST['email'] ?? '');

$postedRedirect = trim((string)($_POST['redirect'] ?? ''));
$redirect = $postedRedirect !== '' ? $postedRedirect
    : (string)(($_GET['redirect'] ?? '') !== '' ? $_GET['redirect'] : (string)($_SESSION['intended'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $st = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $u = $st->fetch();

        if (!$u || !password_verify($password, $u['password_hash'])) {
            // Brute-force mitigation: lock after repeated failures.
            if ($u) {
                if (!$u['locked_until'] || strtotime($u['locked_until']) < time()) {
                    $attempts = (int)$u['failed_logins'] + 1;
                    if ($attempts >= LOGIN_MAX_ATTEMPTS) {
                        $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCK_MINUTES * 60);
                        db()->prepare("UPDATE users SET failed_logins = ?, locked_until = ? WHERE id = ?")
                            ->execute([0, $lockUntil, (int)$u['id']]);
                        $error = 'Too many failed attempts. Please try again later.';
                    } else {
                        db()->prepare("UPDATE users SET failed_logins = ? WHERE id = ?")
                            ->execute([$attempts, (int)$u['id']]);
                        $error = 'Email or password is incorrect.';
                    }
                } else {
                    $error = 'Account temporarily locked. Please try again later.';
                }
            } else {
                $error = 'Email or password is incorrect.';
            }
        } elseif ($u['active'] != 1) {
            $error = 'This account has been deactivated. Please contact support.';
        } elseif ($u['locked_until'] && strtotime($u['locked_until']) > time()) {
            $error = 'Account temporarily locked. Please try again later.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$u['id'];
            $_SESSION['role'] = $u['role'];
            $_SESSION['name'] = $u['full_name'];
            db()->prepare('UPDATE users SET last_login_at = NOW(), failed_logins = 0, locked_until = NULL WHERE id = ?')
                ->execute([(int)$u['id']]);
            audit('login', 'users', (int)$u['id'], 'Signed in as ' . $u['role']);
            notify((int)$u['id'], 'Welcome back', 'You have signed in to your portal.', 'info');

            $intended = $_SESSION['intended'] ?? '';
            unset($_SESSION['intended']);
            $fallback = $u['role'] === 'client' ? app_url('client/index.php') : app_url('admin/index.php');
            if ($redirect && str_starts_with($redirect, '/') && !str_starts_with($redirect, '//')) {
                redirect($redirect);
            }
            redirect($fallback);
        }
    }
}

public_head([
    'title'  => 'Client Login | Reagan Soft Innovation Limited',
    'desc'   => 'Sign in to your Reagan Soft Innovation client portal to track projects, requests, tasks, files and messages.',
    'robots' => false,
]);
?>
<section class="auth-page" style="border-top:1px solid var(--line)">
  <div class="auth-card">
    <a class="brand" href="<?= app_url('index.php') ?>"><img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="Reagan Soft Innovation logo"><span class="brand-text">Reagan Soft <b>Innovation</b><small class="brand-tagline"><?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></small></span></a>
    <h2>Welcome back</h2>
    <p class="auth-sub">Sign in to track your projects and requests.</p>
    <?php if ($error): ?><div class="alert" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
      <div class="field"><label for="email">Email</label><input class="input" id="email" type="email" name="email" required value="<?= e($email) ?>" autocomplete="email"></div>
      <div class="field"><label for="password">Password</label><input class="input" id="password" type="password" name="password" required autocomplete="current-password"></div>
      <button class="btn btn-primary btn-block" type="submit"><?= icon('lock') ?> Sign in</button>
    </form>
    <p class="auth-foot">New client? <a href="<?= app_url('register.php' . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : '')) ?>">Create an account</a></p>
    <p class="auth-back"><a href="<?= app_url('index.php') ?>"><?= icon('arrow-l') ?> Back to website</a></p>
  </div>
</section>
<?php public_footer(); ?>