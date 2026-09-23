<?php
require __DIR__ . '/config/config.php';

if (is_logged_in()) {
    audit('logout', 'users', (int)current_user()['id'], 'Signed out');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
session_start();
session_regenerate_id(true);

redirect(app_url('index.php'));