<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'About Us | Software Development Company in Jinja, Uganda',
    'desc'      => 'About Reagan Soft Innovation Limited, a software and web development company in Jinja, Uganda founded by Reagan Otema. We build websites, business systems and custom software.',
    'active'    => 'about',
    'canonical' => 'about.php',
]);
?>
<section class="page-hero pi-grid">
  <div class="container">
    <div class="pi-copy">
      <span class="eyebrow">About us</span>
      <h1>A software company built on clarity and delivery.</h1>
      <p class="lead">Reagan Soft Innovation Limited helps organisations in Uganda and beyond turn everyday business processes into practical, reliable digital products.</p>
    </div>
    <?= page_image('assets/img/hero1.png', 1, 'Software that is built properly and kept working'); ?>
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
          <div class="info-line" style="color:var(--ink-2)"><?= icon('layers') ?><span><b>Focus</b> Websites · Business systems · Ecommerce · Software</span></div>
        </div>
      </div>
      <div class="info-card">
        <h3>What we believe</h3>
        <div class="info-list">
          <div class="info-line"><?= icon('check') ?><span>Software should solve a real problem, not just look good on a demo.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Clients deserve visible progress: real status, real files, clear next steps.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Security and maintainability are not optional extras.</span></div>
          <div class="info-line"><?= icon('check') ?><span>If the price is not clear upfront, neither are we.</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Meet the founder</span><h2>Reagan Otema, the hands behind the code.</h2></div>
      <p>Reagan started Reagan Soft Innovation to give Uganda's businesses software that is secure, well built and actually maintained.</p>
    </div>
    <div class="about-grid">
      <div class="founder-card">
        <div class="founder-photo">
          <img src="<?= app_url('assets/img/founder.webp') ?>" alt="Reagan Otema, founder and lead developer">
        </div>
        <div class="founder-info">
          <span class="founder-role">Founder &amp; Lead Developer</span>
          <h3>Reagan Otema</h3>
          <p>Reagan founded the company around a simple principle: software that is built properly, priced clearly and kept working long after launch, instead of being built once and abandoned.</p>
          <div class="founder-brand">
            <img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="Reagan Soft Innovation logo" width="52" height="52">
            <span class="brand-text">Reagan Soft <b>Innovation</b></span>
          </div>
          <div class="founder-contact">
            <span><?= icon('phone') ?><?= e(settings('company_phone', '+256730314979')) ?></span>
            <span><?= icon('mail') ?><?= e(settings('company_email', 'info@reagansoft.com')) ?></span>
            <span><?= icon('pin') ?><?= e(settings('company_address', 'Jinja, Uganda')) ?></span>
          </div>
        </div>
      </div>
      <div style="display:grid;align-content:start;gap:16px">
        <div class="split-media" style="height:100%;min-height:320px">
          <img src="<?= app_url('assets/img/computer-portrait.webp') ?>" alt="Reagan Soft Innovation development workstation in the Jinja office">
          <span class="split-shine" aria-hidden="true"></span>
        </div>
        <div class="panel" style="margin:0">
          <h4><?= icon('cpu') ?> Development &amp; design under one roof</h4>
          <p class="small muted" style="margin:0">
            From requirements and database design to the final launch, every build is handled in-house: design, code, testing and handover. There is never a finger-pointing game between separate vendors.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">How we work</span><h2>Our promise to every client.</h2></div></div>
    <div class="value-grid">
      <div class="value-card"><div class="card-icon"><?= icon('search') ?></div><h3>We listen first</h3><p>Scope and requirements are agreed before any development begins.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('doc') ?></div><h3>Written quotations</h3><p>You approve a clear quotation with items, pricing and terms before work starts.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('eye') ?></div><h3>Visible progress</h3><p>Track tasks and completion in your portal, with no black box.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('shield') ?></div><h3>Secure by default</h3><p>Protected logins, safe file handling and privacy respected.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('send') ?></div><h3>Timely delivery</h3><p>Realistic estimates with proactive updates if anything changes.</p></div>
      <div class="value-card"><div class="card-icon"><?= icon('chat') ?></div><h3>Support after launch</h3><p>Maintenance plans and support keep your system healthy.</p></div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="about-grid">
      <div class="info-card">
        <span class="eyebrow">Get started</span>
        <h3>Create a client account</h3>
        <p>Sign up free, send a project request and get a written response from our team.</p>
        <a class="btn btn-light mt-2" href="<?= app_url('checkout.php') ?>">Start a project <?= icon('arrow') ?></a>
      </div>
      <div>
        <h2>Talk to us before you buy.</h2>
        <p class="lead">Not sure what you need? Send a message and we will give you a straight answer, including when a simpler solution will do.</p>
        <p class="muted">Phone: <strong><?= e(settings('company_phone', '+256730314979')) ?></strong><br>
        Email: <strong><?= e(settings('company_email', 'info@reagansoft.com')) ?></strong></p>
        <a class="btn btn-primary" href="<?= app_url('contact.php') ?>">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>