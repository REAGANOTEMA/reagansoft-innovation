<?php
require __DIR__ . '/config/config.php';

if (is_logged_in()) {
    redirect(app_url(user_role() === 'client' ? 'client/index.php' : 'admin/index.php'));
}

if (settings('registration_open', '1') !== '1' && APP_DEBUG === false) {
    redirect(app_url('login.php'));
}

$errors = [];
$values = ['full_name' => '', 'company' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!empty($_POST['company_website'])) {
        flash('success', 'Your account has been created. Please sign in.');
        redirect(app_url('login.php'));
    }
    $values = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'company'   => trim($_POST['company'] ?? ''),
        'email'     => strtolower(trim($_POST['email'] ?? '')),
        'phone'     => trim($_POST['phone'] ?? ''),
    ];
    $password = (string)($_POST['password'] ?? '');
    $confirm  = (string)($_POST['confirm'] ?? '');

    if ($values['full_name'] === '' || mb_strlen($values['full_name']) > 150) {
        $errors[] = 'Please enter your full name.';
    }
    if ($values['company'] !== '' && mb_strlen($values['company']) > 190) {
        $errors[] = 'Company name is too long.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($values['phone'] === '' || mb_strlen($values['phone']) > 40) {
        $errors[] = 'Please enter a phone number so our team can reach you.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $st = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $st->execute([$values['email']]);
        if ($st->fetch()) {
            $errors[] = 'An account with this email already exists. Please sign in instead.';
        }
    }

    if (!$errors) {
        try {
            db()->prepare(
                'INSERT INTO users (full_name, company, email, phone, password_hash, role, active) VALUES (?,?,?,?,?,\'client\',1)'
            )->execute([
                $values['full_name'],
                $values['company'] ?: null,
                $values['email'],
                $values['phone'],
                password_hash($password, PASSWORD_DEFAULT),
            ]);
            $uid = (int)db()->lastInsertId();
            audit('client_registered', 'users', $uid, 'New client account created');
            notify($uid, 'Welcome to Reagan Soft Innovation', 'Your client account is ready. You can now send your first project request.', 'success');
            flash('success', 'Your account has been created. Please sign in to continue.');
            clear_old();
            redirect(app_url('login.php'));
        } catch (Throwable $e) {
            log_error('registration: ' . $e->getMessage());
            if ($e instanceof PDOException && $e->getCode() === '23000') {
                $errors[] = 'An account with this email already exists. Please sign in instead.';
            } else {
                $errors[] = 'We could not create your account right now. Please try again shortly.';
            }
        }
    }
    keep_old(array_keys($values));
    set_errors($errors);
}

public_head([
    'title'  => 'Client Registration | Reagan Soft Innovation Limited',
    'desc'   => 'Create a free client account with Reagan Soft Innovation Limited to submit project requests, track progress and manage files.',
    'robots' => false,
]);
?>
<section class="auth-page" style="border-top:1px solid var(--line)">
  <div class="auth-card">
    <a class="brand" href="<?= app_url('index.php') ?>"><img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="Reagan Soft Innovation logo"><span class="brand-text">Reagan Soft <b>Innovation</b><small class="brand-tagline"><?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></small></span></a>
    <h2>Create your client account</h2>
    <p class="auth-sub">Use accurate contact details so our team can reach you.</p>
    <?php if ($errors): foreach ($errors as $err): ?><div class="alert" role="alert"><?= e($err) ?></div><?php endforeach; endif; ?>
    <form method="post" novalidate>
      <?= csrf_field() ?>
      <div class="field visually-hidden" aria-hidden="true">
        <label for="company_website">Company website</label>
        <input class="input" id="company_website" name="company_website" tabindex="-1" autocomplete="off">
      </div>
      <div class="field"><label for="full_name">Full name <span class="req">*</span></label><input class="input" id="full_name" name="full_name" required maxlength="150" value="<?= old('full_name') ?>" autocomplete="name"></div>
      <div class="field"><label for="company">Company / business name</label><input class="input" id="company" name="company" maxlength="190" value="<?= old('company') ?>" autocomplete="organization"></div>
      <div class="field"><label for="email">Email <span class="req">*</span></label><input class="input" id="email" type="email" name="email" required maxlength="190" value="<?= old('email') ?>" autocomplete="email"></div>
      <div class="field"><label for="phone">Phone <span class="req">*</span></label><input class="input" id="phone" name="phone" required maxlength="40" value="<?= old('phone') ?>" autocomplete="tel"></div>
      <div class="field"><label for="password">Password <span class="req">*</span></label><input class="input" id="password" type="password" name="password" required minlength="8" autocomplete="new-password"><div class="form-note">Minimum 8 characters.</div></div>
      <div class="field"><label for="confirm">Confirm password <span class="req">*</span></label><input class="input" id="confirm" type="password" name="confirm" required minlength="8" autocomplete="new-password"></div>
      <button class="btn btn-primary btn-block" type="submit"><?= icon('user') ?> Create account</button>
    </form>
    <p class="auth-foot">Already registered? <a href="<?= app_url('login.php') ?>">Sign in</a></p>
    <p class="auth-back"><a href="<?= app_url('index.php') ?>"><?= icon('arrow-l') ?> Back to website</a></p>
  </div>
</section>
<?php public_footer(); ?>