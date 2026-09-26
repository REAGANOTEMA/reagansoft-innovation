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
<link rel="apple-touch-icon" sizes="180x180" href="<?= app_url('assets/img/apple-180.png') ?>">
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
function page_image(string $src, int $idx, string $cap = '', string $alt = '', string $loading = 'eager'): string
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

/* ------------------------------------------------------------------
 * Portfolio cover.
 *
 * Completed projects are not screenshotted, so the cover is drawn, not
 * photographed, and nothing in it claims to be a picture of the
 * client's site. Three drawn layers stack up:
 *
 *   1. folio_scene()   a sector backdrop — the trade, not the software:
 *                      a school hill, a Nile sunset, a furrow field, a
 *                      clinic cross, a shop front, a delivery route.
 *   2. folio_devices() the delivery itself, and the part every project
 *                      shares: a browser window with a phone beside it,
 *                      so "website and an app" reads before a word is
 *                      read.
 *   3. the status pill, the service glyph, the project reference and
 *      the handover date.
 *
 * Layers 1 and 2 are drawn in currentColor at low opacity, so the same
 * artwork holds up on the pale covers and on the dark featured one
 * without a second set of files.
 * ------------------------------------------------------------------ */
function folio_glyph(?string $slug, string $service = ''): string {
    $bySlug = [
        'business-website'  => 'globe',
        'ecommerce'         => 'cart',
        'business-systems'  => 'cpu',
        'software-dev'      => 'code-s',
        'web-mobile-apps'   => 'mobile',
        'receipt-billing'   => 'receipt',
        'custom-solutions'  => 'grid',
        'branding'          => 'palette',
        'maintenance'       => 'shield',
        'video-games'       => 'gamepad',
        'digital-books'     => 'book',
        'chat-automation'   => 'bot',
        'system-integration'=> 'link',
        'cybersecurity'     => 'lock',
    ];
    if ($slug !== null && $slug !== '' && isset($bySlug[$slug])) {
        return $bySlug[$slug];
    }
    $name = strtolower($service);
    foreach (['website' => 'globe', 'ecommerce' => 'cart', 'system' => 'cpu', 'app' => 'mobile',
              'billing' => 'receipt', 'brand' => 'palette', 'game' => 'gamepad', 'book' => 'book',
              'chat' => 'bot', 'payment' => 'link', 'security' => 'lock', 'software' => 'code-s'] as $needle => $glyph) {
        if (str_contains($name, $needle)) {
            return $glyph;
        }
    }
    return 'grid';
}

/* ------------------------------------------------------------------
 * Which drawn scene a project gets.
 *
 * projects.cover_art names it directly. An empty column is not a
 * problem: the title, description and service name are searched for
 * the trade instead, so a project added by hand in the admin panel
 * still lands on a sensible picture rather than a blank panel.
 * ------------------------------------------------------------------ */
function folio_art_key(array $p): string {
    $known = ['education', 'hospitality', 'agriculture', 'health', 'commerce', 'logistics', 'systems'];
    $set   = strtolower(trim((string)($p['cover_art'] ?? '')));
    if (in_array($set, $known, true)) {
        return $set;
    }

    $text = strtolower(trim((string)($p['title'] ?? '')) . ' '
        . trim((string)($p['description'] ?? '')) . ' '
        . trim((string)($p['service'] ?? '')) . ' '
        . trim((string)($p['requirements'] ?? '')));

    $needles = [
        'education'    => ['school', 'nursery', 'nursing', 'midwifery', 'university', 'college', 'student', 'pupil', 'training', 'academy', 'campus', 'course'],
        'hospitality'  => ['hotel', 'guest', 'room', 'lodge', 'resort', 'restaurant', 'booking', 'tour', 'travel', 'cafe', 'tourism'],
        'agriculture'  => ['farm', 'agric', 'crop', 'seed', 'harvest', 'livestock', 'poultry', 'garden', 'plantation', 'irrigation'],
        'health'       => ['clinic', 'hospital', 'health', 'medical', 'patient', 'doctor', 'surgery', 'pharmacy', 'dental', 'nutrition'],
        'commerce'     => ['ecommerce', 'e-commerce', 'store', 'shop', 'market', 'trading', 'trade', 'supplies', 'retail', 'wholesale', 'catalogue', 'catalog'],
        'logistics'    => ['logistic', 'deliver', 'transport', 'fleet', 'courier', 'warehouse', 'supply chain', 'dispatch', 'haulage'],
    ];
    foreach ($needles as $key => $words) {
        foreach ($words as $word) {
            if (str_contains($text, $word)) {
                return $key;
            }
        }
    }
    return 'systems';
}

/* ------------------------------------------------------------------
 * Layer 1 — the sector backdrop.
 *
 * Every scene is one 360x180 band, anchored to the bottom edge so it
 * crops rather than floats when the cover is narrow. Only the sun or
 * emblem uses a real fill; everything else is a stroke or a low
 * opacity wash, which is what keeps six scenes sitting together
 * without any of them shouting.
 * ------------------------------------------------------------------ */
function folio_scene(string $key): string {
    $sun = '<circle class="fs-sun" cx="46" cy="34" r="21"/>';

    $scenes = [
        // A school on a hill: the sun over the crest, a medical cross
        // on the near slope where every applicant will see it first.
        'education' =>
            $sun
            . '<path class="fs-hill" d="M-10 152Q74 112 168 132T370 120V190H-10Z"/>'
            . '<path class="fs-hill fs-hill-2" d="M-10 172Q88 140 202 158T370 148V190H-10Z"/>'
            . '<g class="fs-mark" transform="translate(96 78)">'
            . '<rect x="-17" y="-17" width="34" height="34" rx="10"/>'
            . '<path d="M-4.5-10h9v5.5H10v9H4.5V10h-9V4.5h-5.5v-9H-4.5z" class="fs-mark-in"/>'
            . '</g>'
            . '<path class="fs-line" d="M232 150q10-8 20 0t20 0 20 0"/>'
            . '<path class="fs-line" d="M262 162q10-8 20 0t20 0 20 0"/>',

        // A hotel on the Nile: the sun sitting on the water, the far
        // bank as one long low line, four swells in front of it.
        'hospitality' =>
            $sun
            . '<path class="fs-hill" d="M-10 116q60-16 120 0t130 0 120 0V190H-10Z"/>'
            . '<g class="fs-line fs-wave">'
            . '<path d="M-10 132q18-9 36 0t36 0 36 0 36 0 36 0 36 0 36 0 36 0 36 0"/>'
            . '<path d="M-10 148q18-9 36 0t36 0 36 0 36 0 36 0 36 0 36 0 36 0 36 0"/>'
            . '<path d="M-10 164q18-9 36 0t36 0 36 0 36 0 36 0 36 0 36 0 36 0 36 0"/>'
            . '</g>'
            . '<g class="fs-mark" transform="translate(96 74)">'
            . '<path d="M0-16 15-6v20H-15V-6Z"/>'
            . '<path d="M-6 14V-1h12v15"/>'
            . '</g>',

        // A supply field: the sun over furrows that converge toward
        // the horizon, and a seedling in the near corner.
        'agriculture' =>
            $sun
            . '<path class="fs-hill" d="M-10 122q90-14 190 0 90 12 190 0v68H-10Z"/>'
            . '<g class="fs-line fs-furrow">'
            . '<path d="M-10 190q90-40 190-46 100-6 190 12"/>'
            . '<path d="M-10 190q90-24 190-28 100-4 190 12"/>'
            . '<path d="M14 190q80-14 176-16 96-2 176 12"/>'
            . '</g>'
            . '<g class="fs-mark" transform="translate(74 66)">'
            . '<path d="M0 18V0"/>'
            . '<path d="M0 2C-14 2-18-8-16-18-6-16 0-8 0 2Z"/>'
            . '<path d="M0 6C14 6 18-4 16-14 6-12 0-4 0 6Z"/>'
            . '</g>',

        // A clinic: the cross again, but a heartbeat instead of a
        // hillside, so it never reads as the school scene.
        'health' =>
            $sun
            . '<g class="fs-line fs-pulse">'
            . '<path d="M-10 150h96l14-26 16 48 16-70 18 48h220"/>'
            . '</g>'
            . '<g class="fs-mark" transform="translate(96 74)">'
            . '<rect x="-17" y="-17" width="34" height="34" rx="10"/>'
            . '<path d="M-5-11h10v6h6v10H5v6H-5V5h-6v-10h6z" class="fs-mark-in"/>'
            . '</g>'
            . '<path class="fs-line" d="M244 150q10-8 20 0t20 0 20 0"/>',

        // A trading floor: an awning over a shop front and a stack of
        // crates waiting to be picked.
        'commerce' =>
            $sun
            . '<path class="fs-hill" d="M-10 138q90-10 190 2 90 10 190-2v52H-10Z"/>'
            . '<g class="fs-mark" transform="translate(92 70)">'
            . '<path d="M-20-14h40l-4-10H-16Z"/>'
            . '<path d="M-18-14v34h36v-34"/>'
            . '<path d="M-7 20V4h14v16"/>'
            . '</g>'
            . '<g class="fs-line">'
            . '<rect x="150" y="128" width="34" height="24" rx="4"/>'
            . '<rect x="160" y="108" width="30" height="20" rx="4"/>'
            . '<rect x="196" y="136" width="30" height="16" rx="4"/>'
            . '</g>',

        // A delivery route: a road to the horizon with a van on it.
        'logistics' =>
            $sun
            . '<path class="fs-hill" d="M-10 120q120-12 370 4v66H-10Z"/>'
            . '<g class="fs-line fs-road">'
            . '<path d="M118 190 186 122M242 190 186 122"/>'
            . '<path d="M186 168v10M186 146v10"/>'
            . '</g>'
            . '<g class="fs-mark" transform="translate(96 66)">'
            . '<path d="M-20-6h22v14h-22zM2-6h10l8 8v6H2z"/>'
            . '<circle cx="-12" cy="12" r="5"/>'
            . '<circle cx="12" cy="12" r="5"/>'
            . '</g>',

        // The fallback: a reporting dashboard, for the internal tools
        // and client portals that are not tied to one sector.
        'systems' =>
            $sun
            . '<g class="fs-line">'
            . '<rect x="40" y="96" width="66" height="56" rx="8"/>'
            . '<rect x="118" y="112" width="66" height="40" rx="8"/>'
            . '<rect x="196" y="86" width="66" height="66" rx="8"/>'
            . '</g>'
            . '<g class="fs-line fs-bars">'
            . '<path d="M54 138v-12M66 138v-22M78 138v-8M90 138v-28"/>'
            . '<path d="M210 140v-16M222 140v-26M234 140v-34M246 140v-12"/>'
            . '</g>',
    ];

    $body = $scenes[$key] ?? $scenes['systems'];
    return '<svg class="folio-scene" viewBox="0 0 360 180" preserveAspectRatio="xMidYMax slice" aria-hidden="true">'
        . $body . '</svg>';
}

/* ------------------------------------------------------------------
 * Layer 2 — the delivery: a browser window and the app beside it.
 *
 * Drawn once, used by every project, because it is the part of each
 * build that is genuinely the same: a website the client opens in a
 * browser, and an app the client carries. Fills are faint so the
 * sector scene behind it still shows through.
 * ------------------------------------------------------------------ */
function folio_devices(): string {
    return '<svg class="folio-devices" viewBox="0 0 360 180" preserveAspectRatio="xMidYMax meet" aria-hidden="true">'
        /* browser window */
        . '<rect class="fd-frame" x="30" y="34" width="196" height="118" rx="13"/>'
        . '<path class="fd-bar" d="M30 47a13 13 0 0 1 13-13h170a13 13 0 0 1 13 13v7H30Z"/>'
        . '<circle class="fd-dot" cx="46" cy="44" r="2.7"/>'
        . '<circle class="fd-dot" cx="58" cy="44" r="2.7"/>'
        . '<circle class="fd-dot" cx="70" cy="44" r="2.7"/>'
        . '<rect class="fd-url" x="88" y="39" width="120" height="10" rx="5"/>'
        /* hero block + copy column + call to action */
        . '<rect class="fd-solid" x="42" y="64" width="86" height="46" rx="7"/>'
        . '<rect class="fd-line" x="138" y="68" width="74" height="7" rx="3.5"/>'
        . '<rect class="fd-line" x="138" y="81" width="60" height="7" rx="3.5"/>'
        . '<rect class="fd-line" x="138" y="94" width="68" height="7" rx="3.5"/>'
        . '<rect class="fd-cta" x="138" y="108" width="56" height="15" rx="7.5"/>'
        /* three cards along the bottom of the window */
        . '<rect class="fd-card" x="42" y="120" width="52" height="20" rx="6"/>'
        . '<rect class="fd-card" x="102" y="120" width="52" height="20" rx="6"/>'
        . '<rect class="fd-card" x="162" y="120" width="52" height="20" rx="6"/>'
        /* phone, overlapping the window so the two read as one build */
        . '<rect class="fd-phone" x="222" y="42" width="66" height="118" rx="15"/>'
        . '<rect class="fd-notch" x="245" y="47" width="20" height="4" rx="2"/>'
        . '<rect class="fd-solid" x="230" y="60" width="50" height="24" rx="7"/>'
        . '<rect class="fd-line" x="230" y="92" width="50" height="6" rx="3"/>'
        . '<rect class="fd-line" x="230" y="103" width="36" height="6" rx="3"/>'
        . '<rect class="fd-card" x="230" y="116" width="50" height="16" rx="5"/>'
        . '<rect class="fd-card" x="230" y="136" width="50" height="16" rx="5"/>'
        . '</svg>';
}

function folio_cover(array $p, int $idx, bool $featured = false): string {
    $tone    = (($idx - 1) % 4) + 1;
    $glyph   = folio_glyph($p['service_slug'] ?? null, (string)($p['service'] ?? ''));
    $ref     = trim((string)($p['ref_no'] ?? ''));
    $done    = !empty($p['completed_at']) ? fmt_date((string)$p['completed_at'], 'M Y') : '';
    $scene   = folio_art_key($p);

    $out  = '<div class="folio-cover tone-' . $tone . ($featured ? ' is-featured' : '') . '">';
    $out .= folio_scene($scene);
    $out .= '<span class="folio-gridlines" aria-hidden="true"></span>';
    $out .= folio_devices();
    $out .= '<span class="folio-status">' . icon('check') . '<span>Delivered</span></span>';
    $out .= '<span class="folio-glyph" aria-hidden="true">' . icon($glyph) . '</span>';
    $out .= '<span class="folio-meta">'
          . ($ref !== '' ? '<span class="folio-ref">' . e($ref) . '</span>' : '')
          . ($done !== '' ? '<span class="folio-when">Handed over ' . e($done) . '</span>' : '')
          . '</span>';
    $out .= '</div>';
    return $out;
}

/* ------------------------------------------------------------------
 * What was handed over.
 *
 * Stored on the project as a short comma-separated list, e.g.
 * "Website, Business system, Mobile apps". Each item becomes an icon
 * badge, and the same list is emitted as a data-kit attribute so the
 * filter bar above the grid can match on it.
 * ------------------------------------------------------------------ */
function folio_kit_list(?string $deliverables): array {
    $parts = array_values(array_filter(array_map(
        static fn(string $p): string => trim($p),
        preg_split('/[;,]|\s+\/\s+/u', trim((string)$deliverables)) ?: []
    ), static fn(string $p): bool => $p !== ''));

    $icons = [
        'website'        => ['globe',   'Website',              'website'],
        'web'            => ['globe',   'Website',              'website'],
        'system'         => ['cpu',     'Business system',      'system'],
        'portal'         => ['cpu',     'Client portal',        'system'],
        'app'            => ['mobile',  'Mobile apps',          'apps'],
        'android'        => ['mobile',  'Android app',          'apps'],
        'store'          => ['cart',    'Online store',         'store'],
        'ecommerce'      => ['cart',    'Online store',         'store'],
        'brand'          => ['palette', 'Branding',             'branding'],
        'support'        => ['shield',  'Support & maintenance','support'],
        'maintenance'    => ['shield',  'Support & maintenance','support'],
        'training'       => ['users',   'Staff training',       'training'],
        'sms'            => ['send',    'SMS alerts',           'alerts'],
        'email'          => ['mail',    'Email alerts',         'alerts'],
        'payment'        => ['link',    'Mobile money payments','payments'],
        'booking'        => ['calendar','Online booking',       'booking'],
        'dashboard'      => ['chart',   'Reporting dashboard',  'system'],
        'report'         => ['chart',   'Reporting dashboard',  'system'],
        'game'           => ['gamepad', 'Game',                 'apps'],
        'book'           => ['book',    'Publication',          'website'],
        'automation'     => ['bot',     'Smart automation',     'system'],
        'security'       => ['lock',    'Security hardening',   'support'],
        'pos'            => ['receipt', 'Receipts & billing',   'system'],
        'billing'        => ['receipt', 'Receipts & billing',   'system'],
    ];

    $out = [];
    foreach ($parts as $part) {
        $needle = strtolower(rtrim($part, '. '));
        if (isset($icons[$needle])) {
            $out[$icons[$needle][2]] = [$icons[$needle][0], $icons[$needle][1]];
            continue;
        }
        foreach ($icons as $key => $set) {
            if (str_contains($needle, $key)) {
                $out[$set[2]] = [$set[0], $set[1]];
                continue 2;
            }
        }
        $out['other'] = ['grid', ucfirst($needle)];
    }
    return $out;
}

function folio_deliverables(?string $deliverables): string {
    $kit = folio_kit_list($deliverables);
    if ($kit === []) {
        return '';
    }
    $out = '<ul class="folio-kit" data-kit="' . e(implode(' ', array_keys($kit))) . '">';
    foreach ($kit as $set) {
        $out .= '<li>' . icon($set[0]) . '<span>' . e($set[1]) . '</span></li>';
    }
    return $out . '</ul>';
}

/* ------------------------------------------------------------------
 * The live site.
 *
 * Only http and https are ever linked: a value typed into the admin
 * panel must not be able to turn the Work page into a javascript:
 * link. A bare host such as "hotelparadiseonthenile.info" gains an
 * https:// scheme, because that is what a visitor expects and what
 * keeps the card free of mixed content.
 * ------------------------------------------------------------------ */
function folio_live(?string $url): string {
    $raw = trim((string)$url);
    if ($raw === '') {
        return '';
    }
    if (!preg_match('~^[a-z][a-z0-9+.\-]*://~i', $raw)) {
        $raw = 'https://' . ltrim($raw, '/');
    }
    $parts = parse_url($raw);
    if (!is_array($parts) || empty($parts['host'])) {
        return '';
    }
    $scheme = strtolower((string)($parts['scheme'] ?? 'https'));
    if (!in_array($scheme, ['http', 'https'], true)) {
        return '';
    }
    $host = (string)$parts['host'];
    $label = preg_replace('~^www\.~i', '', $host) . (empty($parts['path']) || $parts['path'] === '/' ? '' : rtrim((string)$parts['path'], '/'));
    $href = $scheme . '://' . $host . (empty($parts['path']) ? '' : (string)$parts['path'])
          . (isset($parts['query']) ? '?' . $parts['query'] : '');

    return '<a class="folio-live" href="' . e($href) . '" target="_blank" rel="noopener noreferrer">'
        . '<span class="fl-ico" aria-hidden="true">' . icon('globe') . '</span>'
        . '<span class="fl-txt"><b>Visit live site</b><small>' . e((string)$label) . '</small></span>'
        . '<span class="fl-go" aria-hidden="true">' . icon('arrow') . '</span>'
        . '</a>';
}

/* ------------------------------------------------------------------
 * Scope of work, taken from the project's own requirements field. The
 * seeded data stores it as a comma-separated sentence, so it is split
 * into short chips. Anything past the limit is folded into a details
 * element rather than dropped, which keeps a nine-item scope readable
 * on a card and still complete one tap away on a phone.
 * ------------------------------------------------------------------ */
function folio_scope(?string $requirements, int $limit = 4): string {
    $text = trim((string)$requirements);
    if ($text === '') {
        return '';
    }
    $parts = array_values(array_filter(array_map(
        static fn(string $p): string => trim($p),
        preg_split('/[;,]|\s+\/\s+/u', $text) ?: []
    ), static fn(string $p): bool => $p !== ''));

    if ($parts === []) {
        return '';
    }
    $shown  = array_slice($parts, 0, $limit);
    $hidden = array_slice($parts, $limit);

    $out = '<ul class="folio-scope">';
    foreach ($shown as $p) {
        $out .= '<li>' . icon('check') . '<span>' . e(rtrim($p, '. ')) . '</span></li>';
    }
    $out .= '</ul>';

    if ($hidden !== []) {
        $out .= '<details class="folio-scope-all"><summary>' . icon('plus') . '<span>Everything else we built</span></summary><ul>';
        foreach ($hidden as $p) {
            $out .= '<li>' . icon('check') . '<span>' . e(rtrim($p, '. ')) . '</span></li>';
        }
        $out .= '</ul></details>';
    }
    return $out;
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
      <p class="footer-contact"><span><?= icon('phone') ?></span> <a href="<?= e(phone_tel($phone)) ?>"><?= e(phone_display($phone, $phone)) ?></a></p>
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
        ['billing', 'Payment Details', app_url('client/billing.php'), 'receipt'],
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
    // A client who has not finished their payment details cannot pay, so
    // the sidebar carries a count of what is outstanding. Silence here
    // would mean they only discover it at the moment they try to pay.
    $billingLeft = ($role === 'client' && $user && billing_schema_ready()) ? count(billing_missing($user)) : 0;
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
          <?php if ($it[0] === 'billing' && $billingLeft > 0): ?><span class="side-badge warn" title="<?= $billingLeft ?> payment detail<?= $billingLeft === 1 ? '' : 's' ?> still needed"><?= $billingLeft ?></span><?php endif; ?>
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