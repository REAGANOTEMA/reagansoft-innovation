<?php
/**
 * Reagan Soft Innovation Limited — layout renderers.
 * Loaded from inc/functions.php.
 */

function page_title(array $page, string $default = ''): string {
    $base = settings('company_name', APP_NAME);
    $title = $page['title'] ?? $default;
    return $title !== '' ? $title . ' | ' . $base : $base;
}

function render_alerts(): void {
    $msg = flash('success');
    if ($msg) echo '<div class="alert success" role="alert">' . e($msg) . '</div>';
    $msg = flash('error');
    if ($msg) echo '<div class="alert" role="alert">' . e($msg) . '</div>';
    foreach (flash_errors() as $err) {
        echo '<div class="alert" role="alert">' . e($err) . '</div>';
    }
}

function public_head(array $page = []): void {
    $base = settings('company_name', APP_NAME);
    $desc = $page['desc'] ?? 'Software development company in Jinja, Uganda. Websites, business systems, ecommerce, branding and custom software solutions.';
    $title = page_title($page);
    $active = $page['active'] ?? '';
    $canon = app_url($page['canonical'] ?? '');
    $ogImage = app_url('assets/img/reagansoftinnovation-logo.jpeg');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#083b66">
<link rel="icon" type="image/png" href="<?= app_url('assets/img/reagansoftinnovation-logo-favicon.png') ?>">
<link rel="apple-touch-icon" href="<?= app_url('assets/img/reagansoftinnovation-logo-apple.png') ?>">
<?php if ($canon): ?><link rel="canonical" href="<?= e($canon) ?>"><?php endif; ?>
<?php if ($page['robots'] ?? true): ?><meta name="robots" content="index, follow"><?php else: ?><meta name="robots" content="noindex, nofollow"><?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e($base) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e($ogImage) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= app_url('assets/css/tech.css') ?>">
<script defer src="<?= app_url('assets/js/app.js') ?>"></script>
</head>
<body class="page-<?= e($page['active'] !== '' ? $page['active'] : 'default') ?>">
<a class="skip-link" href="#main">Skip to main content</a>
<header class="topbar" id="topbar">
  <div class="container nav">
    <a class="brand" href="<?= app_url('index.php') ?>" aria-label="Reagan Soft Innovation Limited, Home">
      <img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="" width="40" height="40">
      <span class="brand-text">Reagan Soft <b>Innovation</b><small class="brand-tagline"><?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></small></span>
    </a>
    <button class="menu" id="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="primary-nav">
      <span class="menu-icon"></span>
    </button>
    <nav class="links" id="primary-nav" aria-label="Primary">
      <a href="<?= app_url('index.php') ?>" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a>
      <a href="<?= app_url('about.php') ?>" class="<?= $active === 'about' ? 'active' : '' ?>">About</a>
      <a href="<?= app_url('services.php') ?>" class="<?= $active === 'services' ? 'active' : '' ?>">Services</a>
      <a href="<?= app_url('pricing.php') ?>" class="<?= $active === 'pricing' ? 'active' : '' ?>">Pricing</a>
      <a href="<?= app_url('process.php') ?>" class="<?= $active === 'process' ? 'active' : '' ?>">Process</a>
      <a href="<?= app_url('portfolio.php') ?>" class="<?= $active === 'portfolio' ? 'active' : '' ?>">Work</a>
      <a href="<?= app_url('faq.php') ?>" class="<?= $active === 'faq' ? 'active' : '' ?>">FAQ</a>
      <a href="<?= app_url('contact.php') ?>" class="<?= $active === 'contact' ? 'active' : '' ?>">Contact</a>
      <a class="nav-start" href="<?= app_url('checkout.php') ?>"><?= icon('rocket') ?> Start a Project</a>
      <a class="nav-login" href="<?= app_url('login.php') ?>"><?= is_logged_in() && current_user()['role'] !== 'admin' ? 'Portal' : 'Client Login' ?></a>
    </nav>
  </div>
</header>
<main id="main">
<?php
}

/* ------------------------------------------------------------------
 * Tidy framed image used inside page headers (HUD corners + caption).
 * Inner pages carry their visual as a designed in-page image, never
 * as a full-bleed hero — the home page owns the hero treatment.
 * ------------------------------------------------------------------ */
function page_image(string $src, int $idx, string $cap = '', string $alt = '', string $loading = 'lazy'): string
{
    if ($alt === '') {
        $alt = 'Reagan Soft Innovation Limited — ' . ($cap !== '' ? $cap : 'Jinja, Uganda');
    }
    $n = min(max($idx, 1), 6);
    return '<figure class="page-img">'
        . '<div class="page-img-frame frame-v' . $n . '">'
        . '<img src="' . e(app_url($src)) . '" alt="' . e($alt) . '" width="1200" height="675" loading="' . e($loading) . '" decoding="async">'
        . '<span class="pi-c c1"></span><span class="pi-c c2"></span><span class="pi-c c3"></span><span class="pi-c c4"></span>'
        . '</div>'
        . '<figcaption class="page-img-cap"><span class="pi-led"></span><span>' . e($cap !== '' ? $cap : 'Reagan Soft Innovation · Jinja, Uganda') . '</span></figcaption>'
        . '</figure>';
}

function public_footer(): void {
    $phone = settings('company_phone', '+256730314979');
    $email = settings('company_email', '');
    $address = settings('company_address', 'Jinja, Uganda');
    $fb = settings('company_fb', '');
    $x = settings('company_x', '');
    $li = settings('company_linkedin', '');
    ?>
</main>
<footer class="site-footer">
  <div class="footer-flag" aria-hidden="true">
    <div class="flag-bird fb-l">
      <span class="fb-bob">
        <svg class="crane" viewBox="0 0 72 40" width="72" height="40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g class="legs" stroke="#c9dcec" stroke-width="1.7" stroke-linecap="round">
            <path d="M24 30 L17 39"/>
            <path d="M30 30 L26 39"/>
          </g>
          <path class="tail" d="M12 21 l-9 -4 l3 7 z" fill="#101c28"/>
          <ellipse class="body" cx="30" cy="21" rx="14" ry="8" fill="#ffffff" opacity=".96"/>
          <g class="wing">
            <path class="wing-main" d="M30 16 C38 7 48 5 57 8 C50 13 42 15 34 18 C32 18.6 30 18 30 16 Z" fill="#d7e6f2"/>
            <path class="wing-tip" d="M57 8 C60 9.2 62 11 63 13.2 C58 12 53 13 48 14.2 C50.5 11.4 53.6 9.4 57 8 Z" fill="#0f2233"/>
          </g>
          <path class="neck" d="M38 19 C45 14 49 12.5 53 13 C50 10 46 7.6 41 8" stroke="#ffffff" stroke-width="4.6" stroke-linecap="round" opacity=".96"/>
          <circle class="head" cx="55" cy="11" r="3.6" fill="#ffffff"/>
          <g class="crest" stroke="#ffd23f" stroke-width="1.7" stroke-linecap="round">
            <path d="M55 7.4 L55 3.4"/>
            <path d="M57 7.6 L59 4"/>
            <path d="M53 7.6 L51 4"/>
          </g>
          <path class="beak" d="M58.6 10.6 L64 11.4 L58.6 12.6 Z" fill="#c0392b"/>
        </svg>
      </span>
    </div>
    <div class="flag-bird fb-r">
      <span class="fb-bob">
        <svg class="crane" viewBox="0 0 72 40" width="72" height="40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g class="legs" stroke="#c9dcec" stroke-width="1.7" stroke-linecap="round">
            <path d="M24 30 L17 39"/>
            <path d="M30 30 L26 39"/>
          </g>
          <path class="tail" d="M12 21 l-9 -4 l3 7 z" fill="#101c28"/>
          <ellipse class="body" cx="30" cy="21" rx="14" ry="8" fill="#ffffff" opacity=".96"/>
          <g class="wing">
            <path class="wing-main" d="M30 16 C38 7 48 5 57 8 C50 13 42 15 34 18 C32 18.6 30 18 30 16 Z" fill="#d7e6f2"/>
            <path class="wing-tip" d="M57 8 C60 9.2 62 11 63 13.2 C58 12 53 13 48 14.2 C50.5 11.4 53.6 9.4 57 8 Z" fill="#0f2233"/>
          </g>
          <path class="neck" d="M38 19 C45 14 49 12.5 53 13 C50 10 46 7.6 41 8" stroke="#ffffff" stroke-width="4.6" stroke-linecap="round" opacity=".96"/>
          <circle class="head" cx="55" cy="11" r="3.6" fill="#ffffff"/>
          <g class="crest" stroke="#ffd23f" stroke-width="1.7" stroke-linecap="round">
            <path d="M55 7.4 L55 3.4"/>
            <path d="M57 7.6 L59 4"/>
            <path d="M53 7.6 L51 4"/>
          </g>
          <path class="beak" d="M58.6 10.6 L64 11.4 L58.6 12.6 Z" fill="#c0392b"/>
        </svg>
      </span>
    </div>
  </div>
  <div class="container footer-grid">
    <div class="footer-brand">
      <a class="brand" href="<?= app_url('index.php') ?>">
        <img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="Reagan Soft Innovation logo" width="96" height="96">
        <span class="brand-text">Reagan Soft <b>Innovation</b></span>
      </a>
      <p class="footer-tagline"><?= icon('rocket') ?> <?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></p>
      <p><?= e(settings('company_about', 'Software development company based in Jinja, Uganda.')) ?></p>
      <p class="footer-founder">Founded by <strong><?= e(settings('company_founder', APP_FOUNDER)) ?></strong> · <?= e($address) ?></p>
    </div>
    <nav class="footer-col" aria-label="Company">
      <b>Company</b>
      <a href="<?= app_url('about.php') ?>">About us</a>
      <a href="<?= app_url('process.php') ?>">Our process</a>
      <a href="<?= app_url('portfolio.php') ?>">Our work</a>
      <a href="<?= app_url('faq.php') ?>">FAQ</a>
    </nav>
    <nav class="footer-col" aria-label="Services">
      <b>Services</b>
      <a href="<?= app_url('services.php') ?>">All services</a>
      <a href="<?= app_url('pricing.php') ?>">Pricing</a>
      <a href="<?= app_url('contact.php') ?>">Get a quote</a>
      <a href="<?= app_url('register.php') ?>">Client portal</a>
    </nav>
    <nav class="footer-col" aria-label="Legal">
      <b>Legal</b>
      <a href="<?= app_url('privacy.php') ?>">Privacy Policy</a>
      <a href="<?= app_url('terms.php') ?>">Terms &amp; Conditions</a>
      <a href="<?= app_url('refund-policy.php') ?>">Refund Policy</a>
      <a href="<?= app_url('cookie-policy.php') ?>">Cookie Policy</a>
    </nav>
    <div class="footer-col">
      <b>Contact</b>
      <p class="footer-contact"><span><?= icon('pin') ?></span> <?= e($address) ?></p>
      <p class="footer-contact"><span><?= icon('phone') ?></span> <a href="tel:+256<?= preg_replace('/\D/', '', $phone) ?>"><?= e($phone) ?></a></p>
      <p class="footer-contact"><span><?= icon('mail') ?></span> <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></p>
      <?php if (whatsapp_url() !== ''): ?>
        <p class="footer-contact"><span><?= icon('chat') ?></span> <a href="<?= e(whatsapp_url('Hello Reagan Soft Innovation, I would like to enquire about a project.')) ?>" target="_blank" rel="noopener">WhatsApp</a></p>
      <?php endif; ?>
      <div class="footer-social">
        <?php if ($fb): ?><a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><?= icon_social('fb') ?></a><?php endif; ?>
        <?php if ($x): ?><a href="<?= e($x) ?>" target="_blank" rel="noopener" aria-label="X (Twitter)"><?= icon_social('x-social') ?></a><?php endif; ?>
        <?php if ($li): ?><a href="<?= e($li) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><?= icon_social('linkedin') ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container">
      <span>© <?= date('Y') ?> <?= e(settings('company_name', APP_NAME)) ?>. All rights reserved.</span>
      <span><a href="<?= app_url('login.php') ?>">Client Login</a> · <a href="<?= app_url('register.php') ?>">Create Account</a></span>
    </div>
  </div>
</footer>
<?php $wa = whatsapp_url('Hello Reagan Soft Innovation, I would like to enquire about a project.'); ?>
<?php if ($wa !== ''): ?>
<a class="wa-float" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
  <?= icon_social('whatsapp') ?><span>Chat with us</span>
</a>
<?php endif; ?>
</body>
</html>
<?php
}

/* ------------------------------------------------------------------
 * Authenticated dashboards
 * ------------------------------------------------------------------ */
function dashboard_nav_items(): array {
    $role = user_role();
    $client = [
        ['dashboard', 'Dashboard', app_url('client/index.php'), 'dashboard'],
        ['projects', 'My Projects', app_url('client/projects.php'), 'folder'],
        ['request', 'New Request', app_url('client/request.php'), 'plus'],
        ['messages', 'Messages', app_url('client/messages.php'), 'chat'],
        ['files', 'Files', app_url('client/files.php'), 'file'],
        ['notifications', 'Notifications', app_url('client/notifications.php'), 'bell'],
        ['profile', 'Profile', app_url('client/profile.php'), 'user'],
    ];
    $staff = [
        ['dashboard', 'Dashboard', app_url('admin/index.php'), 'dashboard'],
        ['projects', 'Projects', app_url('admin/projects.php'), 'folder'],
        ['requests', 'Requests', app_url('admin/requests.php'), 'inbox'],
        ['clients', 'Clients', app_url('admin/clients.php'), 'users'],
        ['tasks', 'Tasks', app_url('admin/tasks.php'), 'list'],
        ['quotations', 'Quotations', app_url('admin/quotations.php'), 'doc'],
        ['invoices', 'Invoices', app_url('admin/invoices.php'), 'money'],
        ['messages', 'Messages', app_url('admin/messages.php'), 'chat'],
        ['files', 'Files', app_url('admin/files.php'), 'file'],
    ];
    if ($role === 'admin') {
        $staff = array_merge($staff, [
            ['services', 'Services', app_url('admin/services.php'), 'globe'],
            ['users', 'Users', app_url('admin/users.php'), 'users'],
            ['reports', 'Reports', app_url('admin/reports.php'), 'chart'],
            ['settings', 'Settings', app_url('admin/settings.php'), 'settings'],
            ['audit', 'Audit Logs', app_url('admin/audit.php'), 'search'],
        ]);
    }
    return $role === 'client' ? $client : $staff;
}

function dashboard_head(array $page = []): void {
    $user = current_user();
    $role = user_role();
    $items = dashboard_nav_items();
    $active = $page['active'] ?? '';
    $unread = unread_notifications();
    $title = page_title($page, 'Portal');
    $loginUrl = $role === 'client' ? app_url('client/index.php') : app_url('admin/index.php');
    ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="Client and operations portal for <?= e(settings('company_name', APP_NAME)) ?>.">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#083b66">
<link rel="icon" type="image/png" href="<?= app_url('assets/img/reagansoftinnovation-logo-favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="<?= app_url('assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= app_url('assets/css/tech.css') ?>">
<script defer src="<?= app_url('assets/js/app.js') ?>"></script>
</head>
<body class="dash-body">
<a class="skip-link" href="#main">Skip to main content</a>
<div class="dash-layout">
  <aside class="sidebar" id="sidebar" aria-label="Portal navigation">
    <div class="sidebar-brand">
      <a class="brand" href="<?= $loginUrl ?>">
        <img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="" width="38" height="38">
        <span class="brand-text">Reagan Soft <b>Innovation</b></span>
      </a>
    </div>
    <nav class="side-nav">
      <?php foreach ($items as $it): ?>
        <a href="<?= e($it[2]) ?>" class="<?= $active === $it[0] ? 'active' : '' ?>">
          <?= icon($it[3]) ?><span><?= e($it[1]) ?></span>
          <?php if ($it[0] === 'notifications' && $unread > 0): ?><span class="side-badge"><?= $unread ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="side-bottom">
      <a href="<?= app_url($role !== 'client' ? 'index.php' : 'index.php') ?>"><?= icon('globe') ?><span>Public website</span></a>
      <a href="<?= app_url('logout.php') ?>"><?= icon('logout') ?><span>Sign out</span></a>
    </div>
  </aside>
  <div class="dash-shell">
    <header class="dash-topbar">
      <button class="menu menu-dark" id="sidebar-toggle" aria-label="Toggle navigation" aria-expanded="false">
        <span class="menu-icon"></span>
      </button>
      <div class="dash-topbar-title">
        <span class="dash-crumb"><?= e($page['crumb'] ?? 'Portal') ?></span>
      </div>
      <div class="dash-topbar-right">
        <?php if ($role === 'client'): ?>
          <a class="icon-btn" href="<?= app_url('client/notifications.php') ?>" aria-label="Notifications">
            <?= icon('bell') ?>
            <?php if ($unread > 0): ?><span class="icon-badge"><?= $unread ?></span><?php endif; ?>
          </a>
        <?php endif; ?>
        <div class="user-chip">
          <span class="user-avatar"><?= e(mb_strtoupper(mb_substr($user['full_name'] ?? '?', 0, 1))) ?></span>
          <div class="user-meta">
            <strong><?= e($user['full_name'] ?? '') ?></strong>
            <small><?= e(mb_strtoupper($role)) ?></small>
          </div>
        </div>
      </div>
    </header>
    <main class="dash-main" id="main">
<?php
}

function dashboard_footer(): void {
    ?>
    </main>
  </div>
</div>
</body>
</html>
<?php
}

/* ------------------------------------------------------------------
 * Extra icons used by the footer only (social).
 * ------------------------------------------------------------------ */
function icon_social(string $name): string {
    $svg = [
        'whatsapp' => '<path d="M12.05 2.6c-5.2 0-9.4 4.2-9.4 9.4 0 1.7.4 3.3 1.2 4.7L2.6 21.4l4.8-1.2c1.4.8 3 1.2 4.7 1.2 5.2 0 9.4-4.2 9.4-9.4s-4.2-9.4-9.4-9.4zm3.4 13.4c-.2.5-1 1-1.5 1-.4 0-1 .2-3.1-.7-2.6-1.1-4.2-3.9-4.3-4.1-.1-.2-1-1.4-1-2.6s.7-1.8.9-2c.2-.2.4-.3.6-.3s.3 0 .4.1c.1.1.3.7.6 1.2.1.2.1.3.1.3v.3c-.2.4-.4.6-.4.7.2.6.7 1.4 1.4 2 .9.9 1.8 1.2 2.2 1.3.2.1.4.1.5-.1l.7-.8c.2-.2.4-.2.6-.1l1.8.9c.2.1.4.2.4.4.1.1.1.6-.1 1z"/>',
        'fb' => '<path d="M14 8h3V5h-3c-2.2 0-4 1.8-4 4v2H7v3h3v6h3v-6h3l1-3h-4V9c0-.6.4-1 1-1z"/>',
        'x-social' => '<path d="M4 4l7.2 9.3L4.4 20h2.2l5.5-5.5L16.8 20H20l-7.5-9.7L18.9 4h-2.2l-5 5.2L8 4z"/>',
        'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M7 10v7M7 7.2v.01M11 17v-4a2 2 0 0 1 4 0v4v-7"/><path d="M11 10v7"/>',
    ];
    $path = $svg[$name] ?? $svg['fb'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/* ------------------------------------------------------------------
 * Payment labels + client-facing payment instructions.
 * ------------------------------------------------------------------ */
function payment_method_label(string $method): string {
    return [
        'mtn_momo'     => 'MTN Mobile Money',
        'airtel_money' => 'Airtel Money',
        'bank'         => 'Bank transfer',
        'cash'         => 'Cash',
        'other'        => 'Other',
    ][$method] ?? ucwords(str_replace('_', ' ', $method));
}

function payment_instructions_html(): string {
    $mtn = settings('payment_mtn_number', '');
    $airtel = settings('payment_airtel_number', '');
    $bank = settings('payment_bank_details', '');
    if ($mtn === '' && $airtel === '' && $bank === '') {
        return '';
    }
    ob_start();
    echo '<div class="pay-details">';
    if ($mtn !== '') {
        echo '<div class="pay-box"><span class="pbx-icon">' . icon('phone') . '</span><div><b>MTN Mobile Money</b><span class="pbx-num">' . e($mtn) . '</span><p>Send the amount to this number, then submit the payment below with the MoMo transaction reference so we can confirm it quickly.</p></div></div>';
    }
    if ($airtel !== '') {
        echo '<div class="pay-box"><span class="pbx-icon">' . icon('phone') . '</span><div><b>Airtel Money</b><span class="pbx-num">' . e($airtel) . '</span><p>Send the amount to this number, then submit the payment below with the transaction reference.</p></div></div>';
    }
    if (trim($bank) !== '') {
        echo '<div class="pay-box"><span class="pbx-icon">' . icon('money') . '</span><div><b>Bank transfer</b>' . nl2br(e(trim($bank))) . '</div></div>';
    }
    echo '</div>';
    return (string)ob_get_clean();
}