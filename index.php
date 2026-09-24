<?php
require __DIR__ . '/config/config.php';

$pdo = db();
try {
    $services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();
} catch (Throwable $e) {
    $services = [];
    log_error('home services query: ' . $e->getMessage());
}

$completed = 0;
try {
    $s = $pdo->query("SELECT COUNT(*) c FROM projects WHERE status='COMPLETED'")->fetch();
    $completed = (int)$s['c'];
} catch (Throwable $e) { /* not installed yet */ }

$bandService = [
    'websites' => 1,
    'receipts' => 9,
    'apps'     => 8,
    'systems'  => 3,
];

public_head([
    'title'     => 'Software Development & Digital Solutions in Jinja, Uganda',
    'desc'      => 'Reagan Soft Innovation Limited builds professional websites, business systems, ecommerce and custom software in Jinja, Uganda. Start your project through our secure client portal.',
    'active'    => 'home',
    'canonical' => 'index.php',
]);
?>
<section class="hero" id="home">
  <div class="hero-slider" id="heroSlider" role="region" aria-roledescription="carousel" aria-label="What we build for you">
    <div class="hero-slides">
      <?php $heroSlides = [
          ['assets/img/hero1.webp', 'Web Development', 'PROFESSIONAL WEBSITES', 'A website built around your business, designed to look right on every phone and computer, and kept working long after launch.', 1],
          ['assets/img/hero3.webp', 'Web &amp; Mobile Applications', 'APPS FOR YOUR OWNERSHIP', 'Business apps delivered as a website, Android and iOS app together, prepared and published to the App Store and Google Play.', 8],
          ['assets/img/hero4.webp', 'Receipt &amp; Billing Automation', 'AUTOMATIC RECEIPTS', 'Your shop, school or clinic prints and sends a professional receipt automatically every time a client pays you.', 9],
          ['assets/img/hero2.webp', 'Custom Business Systems', 'SYSTEMS BUILT AROUND YOUR PROCESSES', 'Client portals, management systems, dashboards and workflow automation, scoped by modules and approved by you first.', 3],
          ['assets/img/hero5.webp', 'Ecommerce', 'ONLINE STORES THAT SELL', 'Product catalogues, cart, checkout and mobile money payment architecture, built to turn visitors into paying customers.', 1],
          ['assets/img/hero6.webp', 'Software that keeps working', 'A ONE TIME DEPOSIT', 'Secure your project with a fixed one time deposit of <?= money($deposit, $currency) ?>, credited against your written quotation.', 0],
      ]; ?>
      <?php foreach ($heroSlides as $si => $hd): [$hImg, $hTag, $hKicker, $hLead, $hSvc] = $hd; ?>
        <div class="hero-slide<?= $si === 0 ? ' s-active' : '' ?>" data-slide>
          <img class="hero-bg" src="<?= app_url($hImg) ?>" width="1200" height="675"
               alt="<?= e($hKicker) ?> — Reagan Soft Innovation Limited, digital solutions in Jinja, Uganda"
               loading="<?= $si === 0 ? 'eager' : 'lazy' ?>" fetchpriority="<?= $si === 0 ? 'high' : 'auto' ?>" decoding="async">
          <div class="hero-overlay" aria-hidden="true"></div>
          <div class="hero-grain" aria-hidden="true"></div>
          <div class="container hero-inner">
            <span class="hero-tag"><span class="dot" aria-hidden="true"></span> <?= e($hTag) ?></span>
            <h1><?= icon('rocket') ?> Build the <span class="accent"><?= e($hKicker) ?></span></h1>
            <p class="lead"><?= e($hLead) ?></p>
            <div class="hero-actions">
              <a class="btn btn-primary" href="<?= app_url('checkout.php' . ($hSvc > 0 ? '?service=' . (int)$hSvc : '')) ?>"><?= icon('rocket') ?> Get Now</a>
              <a class="btn btn-ghost" href="<?= app_url('pricing.php') ?>">View services &amp; pricing</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <button class="hero-arrow hero-prev" type="button" data-prev aria-label="Previous slide"><?= icon('prev') ?></button>
    <button class="hero-arrow hero-next" type="button" data-next aria-label="Next slide"><?= icon('next') ?></button>
    <div class="hero-dots" data-dots aria-label="Choose slide"></div>
  </div>
  <a class="hero-scroll" href="#overview" aria-label="Scroll to our services and pricing">
    <span class="hero-scroll-mouse" aria-hidden="true"><span></span></span>
    <span class="hero-scroll-label">Scroll</span>
  </a>
</section>

<section class="hero-band" id="overview">
  <div class="container">
    <div class="hero-pricing">
      <div class="hero-price-item">
        <span class="hp-label">Web Development</span>
        <span class="hp-value"><?= e(settings('currency')) ?> <?= money_compact(PRICE_WEBSITE_MIN) ?> – <?= money_compact(PRICE_WEBSITE_MAX) ?></span>
        <span class="hp-sub">Professional websites &amp; online stores</span>
      </div>
      <div class="hero-price-item">
        <span class="hp-label">Business Systems</span>
        <span class="hp-value"><?= e(settings('currency')) ?> <?= money_compact(PRICE_SYSTEM_MIN) ?> – <?= money_compact(PRICE_SYSTEM_MAX) ?></span>
        <span class="hp-sub">Custom systems &amp; software</span>
      </div>
    </div>
    <div class="hero-trust">
      <span><span class="tick"><?= icon('check') ?></span> You can request work online</span>
      <span><span class="tick"><?= icon('check') ?></span> Tasks &amp; progress you can follow</span>
      <span><span class="tick"><?= icon('check') ?></span> Clear, upfront pricing</span>
      <span><span class="tick"><?= icon('check') ?></span> <b data-count="<?= max(1, $completed) ?>"><?= max(1, $completed) ?></b>+ projects delivered</span>
    </div>
    <div class="hero-byline">
      <img src="<?= app_url('assets/img/founder.webp') ?>" alt="Reagan Otema, founder" width="40" height="40" loading="eager" fetchpriority="high">
      <span>Built by <b>Reagan Otema</b>, founder in Jinja, Uganda</span>
    </div>
    <p class="hero-motto"><?= icon('rocket') ?> <?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></p>
  </div>
</section>

<section class="section" id="services-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">What we do</span><h2>Solutions for real businesses, not templates.</h2></div>
      <div>
        <p style="margin-bottom:10px">Pick a service, describe your requirements in the portal, and follow the work from request to delivery.</p>
        <a class="btn btn-ghost btn-sm" href="<?= app_url('services.php') ?>">View all services <?= icon('arrow') ?></a>
      </div>
    </div>
    <div class="cards svc-grid">
      <?php foreach (array_slice($services, 0, 6) as $i => $s): ?>
        <article class="card svc-card accent-<?= ($i % 4) + 1 ?>">
          <div class="svc-top">
            <span class="svc-icon"><?= icon($s['icon']) ?></span>
            <?php if ($s['delivery_days']): ?>
              <span class="svc-days"><?= icon('clock') ?><span><?= (int)$s['delivery_days'] ?> day<?= (int)$s['delivery_days'] === 1 ? '' : 's' ?></span></span>
            <?php endif; ?>
          </div>
          <h3><?= e($s['name']) ?></h3>
          <p><?= e($s['description']) ?></p>
          <div class="svc-price"><?php $srvPrice = service_price_display($s, settings('currency')); $srvNote = trim((string)$s['price_note']); if ($srvNote !== '' && mb_stripos($srvPrice, $srvNote) === false): ?><span class="svc-note"><?= e($srvNote) ?></span><?php endif; ?><strong><?= e($srvPrice) ?></strong></div>
          <a class="svc-btn" href="<?= app_url('checkout.php?service=' . (int)$s['id']) ?>"><span>Choose this program</span><?= icon('arrow') ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="split-banner">
      <div class="split-media"><img src="<?= app_url('assets/img/codes-banner.webp') ?>" alt="Clean code written by the Reagan Soft Innovation team"><span class="split-shine" aria-hidden="true"></span></div>
      <div class="split-body">
        <span class="eyebrow">Clean code, on purpose</span>
        <h2>Code that is built to be looked after, not just launched.</h2>
        <p class="muted">Anyone can put up a page. We write systems that stay fast, stay secure and stay easy for you to use, with the database, access control and roles set up properly from the start.</p>
        <ul class="feature-list">
          <li><span class="fcheck"><?= icon('check') ?></span> Secure logins &amp; role based access on every build</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Clean, documented code your next developer can read</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Backups, updates and support after launch</li>
        </ul>
        <a class="btn btn-primary" href="<?= app_url('process.php') ?>">See how a project runs <?= icon('arrow') ?></a>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Why people work with us</span><h2>More than a developer, a partner in the work.</h2></div>
    </div>
    <div class="value-grid">
      <div class="value-card">
        <div class="card-icon"><?= icon('shield') ?></div>
        <h3>No security shortcuts</h3>
        <p>Passwords, logins and client data are protected properly from day one, not bolted on as an afterthought.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('chat') ?></div>
        <h3>You keep tabs on everything</h3>
        <p>Every request, message, task and file lives in one place, so you always know what is happening next.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('pin') ?></div>
        <h3>A real team in your time zone</h3>
        <p>Based in Jinja, working with businesses across Uganda, and reachable by phone and WhatsApp when you need us.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('doc') ?></div>
        <h3>Records you can actually follow</h3>
        <p>Quotations, invoices and project history stay saved and searchable. Nothing disappears into a lost email chain.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('layers') ?></div>
        <h3>Systems that grow with you</h3>
        <p>We build in modular pieces, so your software expands as your business does, without any scrapping or starting over.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('clock') ?></div>
        <h3>Realistic timelines</h3>
        <p>Realistic estimates and early updates if anything changes. We dislike surprise "almost done" emails as much as you do.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft" id="process-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Simple process</span><h2>From request to delivery.</h2></div>
      <p>Four clear stages. You stay informed at every step.</p>
    </div>
    <div class="steps">
      <div class="step"><span class="step-num">STEP 01</span><h3>Create account</h3><p>Register your name, company, phone and email. It takes less than a minute.</p></div>
      <div class="step"><span class="step-num">STEP 02</span><h3>Choose program &amp; pay</h3><p>Pick the program for your project and secure it with a one time deposit.</p></div>
      <div class="step"><span class="step-num">STEP 03</span><h3>We review &amp; quote</h3><p>Our team reviews the work, agrees scope and sends a professional quotation.</p></div>
      <div class="step"><span class="step-num">STEP 04</span><h3>Track delivery</h3><p>Follow tasks, progress, messages and completion from your dashboard.</p></div>
    </div>
    <p class="center mt-3"><a class="btn btn-dark" href="<?= app_url('process.php') ?>">See the full process <?= icon('arrow') ?></a></p>
  </div>
</section>

<section class="section" id="pricing-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Transparent pricing</span><h2>Clear ranges. No surprises.</h2></div>
      <p>Projects are secured with a fixed one time deposit of <?= money(deposit_amount(), settings('currency')) ?>, credited against your final quotation. As a custom studio we never promise a fixed price before reviewing your requirements. A written quotation is always approved by you first.</p>
    </div>
    <div class="pricing-band">
      <div class="pband-card">
        <span class="card-icon"><?= icon('globe') ?></span>
        <h3>Professional Websites</h3>
        <p>Company websites, online stores and web platforms, scoped by pages, design, features and integrations.</p>
        <div class="pband-amount"><?= money(PRICE_WEBSITE_MIN, settings('currency')) ?><span>–</span><?= money(PRICE_WEBSITE_MAX, settings('currency')) ?></div>
        <a class="btn btn-primary" href="<?= app_url('checkout.php?service=' . (int)$bandService['websites']) ?>">Choose this program <?= icon('arrow') ?></a>
      </div>
      <div class="pband-card">
        <span class="card-icon"><?= icon('receipt') ?></span>
        <h3>Receipt &amp; Billing Automation</h3>
        <p>Automatic receipt and invoice generation, payment records and simple accounting for shops, schools and service businesses.</p>
        <div class="pband-amount"><?= money(PRICE_RECEIPT_MIN, settings('currency')) ?><span>–</span><?= money(PRICE_RECEIPT_MAX, settings('currency')) ?></div>
        <a class="btn btn-primary" href="<?= app_url('checkout.php?service=' . (int)$bandService['receipts']) ?>">Choose this program <?= icon('arrow') ?></a>
      </div>
      <div class="pband-card">
        <span class="card-icon"><?= icon('mobile') ?></span>
        <h3>Web &amp; Mobile Apps</h3>
        <p>Business apps for phone and browser, prepared and published for the Apple App Store and Google Play.</p>
        <div class="pband-amount">From <?= money(PRICE_APP_MIN, settings('currency')) ?></div>
        <a class="btn btn-primary" href="<?= app_url('checkout.php?service=' . (int)$bandService['apps']) ?>">Choose this program <?= icon('arrow') ?></a>
      </div>
      <div class="pband-card alt">
        <span class="card-icon"><?= icon('cpu') ?></span>
        <h3>Custom Business Systems</h3>
        <p>Portals, management systems, dashboards and automation for schools, hospitals, NGOs and companies, scoped by modules, roles and workflows.</p>
        <div class="pband-amount"><?= money(PRICE_SYSTEM_MIN, settings('currency')) ?><span>–</span><?= money(PRICE_SYSTEM_MAX, settings('currency')) ?></div>
        <a class="btn btn-light" href="<?= app_url('checkout.php?service=' . (int)$bandService['systems']) ?>">Choose this program <?= icon('arrow') ?></a>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft" id="about-home">
  <div class="container">
    <div class="about-grid">
      <div>
        <span class="eyebrow">About Reagan Soft Innovation</span>
        <h2>Built in Jinja. Designed for real businesses.</h2>
        <p class="lead mb-2"><?= e(settings('company_about', 'We help organisations turn ideas and everyday business processes into practical digital products.')) ?></p>
        <p class="muted">We keep things simple: you always see what was requested, what is being worked on, what has been completed and what comes next, all from your own portal.</p>
        <div class="hero-actions mt-3">
          <a class="btn btn-primary" href="<?= app_url('about.php') ?>">More about us</a>
          <a class="btn btn-ghost" href="<?= app_url('portfolio.php') ?>">Our work</a>
        </div>
      </div>
      <div class="founder-stack">
        <div class="founder-card">
          <div class="founder-photo">
            <img src="<?= app_url('assets/img/founder.webp') ?>" alt="Reagan Otema, founder of Reagan Soft Innovation Limited">
          </div>
          <div class="founder-info">
            <span class="founder-role">Founder &amp; Lead Developer</span>
            <h3>Reagan Otema</h3>
            <p>Reagan leads the design and development on every project himself, and makes sure each one is built to be looked after, not just launched.</p>
            <div class="founder-brand">
              <img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="Reagan Soft Innovation logo" width="52" height="52">
              <span class="brand-text">Reagan Soft <b>Innovation</b></span>
            </div>
            <div class="founder-contact">
              <span><?= icon('phone') ?><?= e(settings('company_phone', '+256730314979')) ?></span>
              <span><?= icon('pin') ?><?= e(settings('company_address', 'Jinja, Uganda')) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <span class="eyebrow">Let's build</span>
        <h2>Have a project in mind?</h2>
        <p>Create an account, choose your program and secure it with a one time deposit. We will review it and respond with a clear plan and quotation.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-light" href="<?= app_url('checkout.php') ?>">Start a project &amp; pay deposit</a>
        <a class="btn btn-outline" href="<?= app_url('contact.php') ?>">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>