<?php
/**
 * Authentication and account endpoints.
 */

declare(strict_types=1);

function api_user_public(array $u): array
{
    $allowed = [
        'id', 'full_name', 'email', 'phone', 'role', 'company', 'address',
        'avatar', 'payer_type', 'tax_id', 'national_id', 'country', 'city',
        'billing_terms_at', 'active', 'last_login_at', 'created_at',
    ];
    $out = [];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $u)) {
            $out[$key] = $u[$key];
        }
    }
    $out['id'] = (int)$out['id'];
    $out['active'] = (int)$out['active'];
    return $out;
}

function handle_auth(string $method, array $seg): bool
{
    $what = $seg[1] ?? '';

    if ($what === 'session' && $method === 'GET') {
        api_ok([
            'user' => api_user() ? api_user_public(api_user()) : null,
            'csrf' => api_csrf_token(),
        ]);
    }
    if ($what === 'login' && $method === 'POST') {
        auth_login();
    }
    if ($what === 'register' && $method === 'POST') {
        auth_register();
    }
    if ($what === 'logout' && $method === 'POST') {
        auth_logout();
    }
    if ($what === 'profile' && in_array($method, ['PATCH', 'PUT', 'POST'], true)) {
        auth_update_profile();
    }
    if ($what === 'password' && in_array($method, ['PATCH', 'PUT', 'POST'], true)) {
        auth_change_password();
    }
    return false;
}

function auth_login(): never
{
    api_require_csrf();
    $data = api_body();
    $email = strtolower(trim((string)($data['email'] ?? '')));
    $password = (string)($data['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        throw new ApiException('Enter your email address and password.', 422);
    }

    api_rate_limit_login($email);

    $st = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    $u = $st->fetch();

    if (!$u || !password_verify($password, (string)$u['password_hash'])) {
        api_apply_login_audit('login_failed', 'Failed sign-in for ' . $email);
        if ($u) {
            if (!$u['locked_until'] || strtotime((string)$u['locked_until']) < time()) {
                $attempts = (int)$u['failed_logins'] + 1;
                if ($attempts >= LOGIN_MAX_ATTEMPTS) {
                    db()->prepare('UPDATE users SET failed_logins = 0, locked_until = ? WHERE id = ?')
                        ->execute([date('Y-m-d H:i:s', time() + LOGIN_LOCK_MINUTES * 60), (int)$u['id']]);
                    throw new ApiException('Too many failed attempts. Please try again in ' . LOGIN_LOCK_MINUTES . ' minutes.', 429);
                }
                db()->prepare('UPDATE users SET failed_logins = ? WHERE id = ?')
                    ->execute([$attempts, (int)$u['id']]);
            } else {
                throw new ApiException('Account temporarily locked. Please try again later.', 429);
            }
        }
        throw new ApiException('Email or password is incorrect.', 401);
    }
    if ((int)$u['active'] !== 1) {
        throw new ApiException('This account has been deactivated. Please contact support.', 403);
    }
    if ($u['locked_until'] && strtotime((string)$u['locked_until']) > time()) {
        throw new ApiException('Account temporarily locked. Please try again later.', 429);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$u['id'];
    $_SESSION['role'] = $u['role'];
    $_SESSION['name'] = $u['full_name'];

    $lastLogin = $u['last_login_at'] ? strtotime((string)$u['last_login_at']) : 0;
    db()->prepare('UPDATE users SET last_login_at = NOW(), failed_logins = 0, locked_until = NULL WHERE id = ?')
        ->execute([(int)$u['id']]);
    audit('login', 'users', (int)$u['id'], 'Signed in as ' . $u['role']);
    if ($lastLogin < strtotime('-12 hours')) {
        notify((int)$u['id'], 'Welcome back', 'You have signed in to your portal.', 'info');
    }

    $u['last_login_at'] = date('Y-m-d H:i:s');
    api_ok([
        'user' => api_user_public($u),
        'csrf' => api_csrf_token(),
        'redirect' => $u['role'] === 'client' ? '/portal' : '/console',
    ]);
}

function auth_register(): never
{
    api_require_csrf();
    if (settings('registration_open', '1') !== '1') {
        throw new ApiException('New client registration is currently closed. Please contact us.', 403);
    }
    $data = api_body();
    if (!empty($data['company_website'])) {
        throw new ApiException('Registration could not be completed.', 422);
    }

    $fullName = api_string($data, 'full_name', 'Full name', 150, true);
    $company = api_string($data, 'company', 'Company', 190);
    $email = strtolower(api_string($data, 'email', 'Email', 190, true));
    $phone = api_string($data, 'phone', 'Phone', 40, true);
    $password = (string)($data['password'] ?? '');
    $confirm = (string)($data['confirm'] ?? $data['password_confirm'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new ApiException('Please enter a valid email address.', 422, ['email' => 'Invalid email address.']);
    }
    if (strlen($password) < 8) {
        throw new ApiException('Password must be at least 8 characters.', 422, ['password' => 'Minimum 8 characters.']);
    }
    if ($confirm !== '' && $password !== $confirm) {
        throw new ApiException('Passwords do not match.', 422, ['confirm' => 'Passwords do not match.']);
    }

    $st = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $st->execute([$email]);
    if ($st->fetch()) {
        throw new ApiException('An account with this email already exists. Please sign in instead.', 409, ['email' => 'Email already registered.']);
    }

    try {
        db()->prepare(
            'INSERT INTO users (full_name, company, email, phone, password_hash, role, active) VALUES (?,?,?,?,?,\'client\',1)'
        )->execute([$fullName, $company ?: null, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $e) {
        log_error('API registration: ' . $e->getMessage());
        if ($e->getCode() === '23000') {
            throw new ApiException('An account with this email already exists.', 409);
        }
        throw new ApiException('We could not create your account right now.', 503);
    }

    $uid = (int)db()->lastInsertId();
    audit('client_registered', 'users', $uid, 'New client account created via website');
    notify($uid, 'Welcome to Reagansoft Innovation', 'Your client account is ready. Send your first project request to get started.', 'success');

    session_regenerate_id(true);
    $_SESSION['user_id'] = $uid;
    $_SESSION['role'] = 'client';
    $_SESSION['name'] = $fullName;

    $st = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([$uid]);
    api_ok([
        'user' => api_user_public($st->fetch()),
        'csrf' => api_csrf_token(),
        'redirect' => '/portal',
    ], null, 201);
}

function auth_logout(): never
{
    api_require_csrf();
    $uid = (int)(api_user()['id'] ?? 0);
    if ($uid) {
        audit('logout', 'users', $uid, 'Signed out');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    api_ok(['logged_out' => true]);
}

function auth_update_profile(): never
{
    $user = api_require_login();
    api_require_csrf();
    $data = api_body();

    $fullName = api_string($data, 'full_name', 'Full name', 150, true);
    $phone = api_string($data, 'phone', 'Phone', 40);
    $company = api_string($data, 'company', 'Company', 190);
    $address = api_string($data, 'address', 'Address', 255);
    $city = api_string($data, 'city', 'City', 90);
    $country = api_string($data, 'country', 'Country', 80) ?: 'Uganda';
    $payerType = api_in_enum(api_string($data, 'payer_type', 'Payer type', 20) ?: 'business', ['business', 'individual'], 'payer type');
    $taxId = api_string($data, 'tax_id', 'TIN', 60);
    $nationalId = api_string($data, 'national_id', 'National ID', 60);

    db()->prepare(
        'UPDATE users SET full_name = ?, phone = ?, company = ?, address = ?, city = ?, country = ?,
                          payer_type = ?, tax_id = ?, national_id = ? WHERE id = ?'
    )->execute([
        $fullName, $phone ?: null, $company ?: null, $address ?: null, $city ?: null,
        $country, $payerType, $taxId ?: null, $nationalId ?: null, (int)$user['id'],
    ]);

    if (api_bool($data, 'billing_terms_accepted')) {
        db()->prepare('UPDATE users SET billing_terms_at = NOW() WHERE id = ? AND billing_terms_at IS NULL')
            ->execute([(int)$user['id']]);
    }

    audit('profile_updated', 'users', (int)$user['id'], 'Updated account details');
    $st = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $st->execute([(int)$user['id']]);
    api_ok(['user' => api_user_public($st->fetch())]);
}

function auth_change_password(): never
{
    $user = api_require_login();
    api_require_csrf();
    $data = api_body();
    $current = (string)($data['current_password'] ?? '');
    $password = (string)($data['password'] ?? '');
    $confirm = (string)($data['confirm'] ?? '');

    if (!password_verify($current, (string)$user['password_hash'])) {
        throw new ApiException('Your current password is incorrect.', 422, ['current_password' => 'Incorrect password.']);
    }
    if (strlen($password) < 8) {
        throw new ApiException('The new password must be at least 8 characters.', 422, ['password' => 'Minimum 8 characters.']);
    }
    if ($password !== $confirm) {
        throw new ApiException('The new passwords do not match.', 422, ['confirm' => 'Passwords do not match.']);
    }
    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), (int)$user['id']]);
    audit('password_changed', 'users', (int)$user['id'], 'Changed account password');
    api_ok(['updated' => true]);
}
