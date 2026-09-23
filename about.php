<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'About Us | Software Development Company in Jinja, Uganda',
    'desc'      => 'About Reagan Soft Innovation Limited — a software and web development company in Jinja, Uganda founded by Reagan Otema. We build websites, business systems and custom software.',
    'active'    => 'about',
    'canonical' => 'about.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">About us</span>
    <h1>A software company built on clarity and delivery.</h1>
    <p class="lead">Reagan Soft Innovation Limited helps organisations in Uganda and beyond turn business processes into practical, reliable digital products.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div>
        <span class="eyebrow">Who we are</span>
        <h2>Founded in Jinja. Focused on results.</h2>
        <p><?= e(settings('company_about', 'We help organisations turn ideas and everyday business processes into practical digital products.')) ?></p>
        <p class="muted">The company is led by founder Reagan Otema. Our team treats every project as a partnership: we understand the problem before we write a line of code, and we keep you informed through a dedicated client portal from request to completion.</p>
        <div class="info-list" style="display:grid;gap:12px;margin-top:22px;max-width:480px">
          <div class="info-line" style="color:var(--ink-2)"><?= icon('flag') ?><span><b>Founded by</b> <?= e(settings('company_founder', 'Reagan Otema')) ?></span></div>
          <div class="info-line" style="color:var(--ink-2)"><?= icon('pin') ?><span><b>Based in</b> <?= e(settings('company_address', 'Jinja, Uganda')) ?></span></div>
          <div class="info-line" style="color:var(--ink-2)"><?= icon('layers') ?><span><b>Focus</b> Websites · Business systems · E-commerce · Software</span></div>
        </div>
      </div>
      <div class="info-card">
        <h3>What we believe</h3>
        <div class="info-list">
          <div class="info-line"><?= icon('check') ?><span>Software should solve real problems, not look impressive on paper.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Clients deserve to see progress — honest status, real files, clear next steps.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Security and maintainability are not optional extras.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Good pricing is transparent pricing.</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">How we work</span><h2>Our promise to every client.</h2></div></div>
    <div class="value-grid">
      <div class="value-card"><div class="card-icon"><?= icon('search') ?></div><h3>We listen first</h3><p>Scope and requirements are agreed before any development begins.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('doc') ?></div><h3>Written quotations</h3><p>You approve a clear quotation with items, pricing and terms before work starts.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('eye') ?></div><h3>Visible progress</h3><p>Track tasks and completion in your portal — no black box.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('shield') ?></div><h3>Secure by default</h3><p>Protected logins, safe file handling and privacy respected.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('send') ?></div><h3>Timely delivery</h3><p>Realistic estimates with proactive updates if anything changes.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('chat') ?></div><h3>Support after launch</h3><p>Maintenance plans and support keep your system healthy.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="info-card">
        <span class="eyebrow" style="color:#7fd0ff">Get started</span>
        <h3>Create a client account</h3>
        <p>Sign up free, send a project request and get a written response from our team.</p>
        <a class="btn btn-light mt-2" href="<?= app_url('register.php') ?>">Start a project <?= icon('arrow') ?></a>
      </div>
      <div>
        <h2>Talk to us before you buy.</h2>
        <p class="lead">Not sure what you need? Send a message and we will advise honestly — including when a simpler solution will do.</p>
        <p class="muted">Phone: <strong><?= e(settings('company_phone', '+256730314979')) ?></strong><br>
        Email: <strong><?= e(settings('company_email', 'info@reagansoft.com')) ?></strong></p>
        <a class="btn btn-primary" href="<?= app_url('contact.php') ?>">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>