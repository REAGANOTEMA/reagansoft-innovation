<?php
require __DIR__ . '/config/config.php';

$pdo = db();
try {
    $services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();
} catch (Throwable $e) {
    $services = [];
    log_error('home services query: ' . $e->getMessage());
}

$featured = array_slice($services, 0, 3);
$completed = 0;
try {
    $s = $pdo->query("SELECT COUNT(*) c FROM projects WHERE status='COMPLETED'")->fetch();
    $completed = (int)$s['c'];
} catch (Throwable $e) { /* not installed yet */ }

public_head([
    'title'     => 'Software Development & Digital Solutions in Jinja, Uganda',
    'desc'      => 'Reagan Soft Innovation Limited builds professional websites, business systems, e-commerce and custom software in Jinja, Uganda. Start your project through our secure client portal.',
    'active'    => 'home',
    'canonical' => 'index.php',
]);
?>
<section class="hero">
  <div class="container hero-inner">
    <div>
      <span class="hero-tag"><span class="dot" aria-hidden="true"></span> Software · Websites · Business Systems — Jinja, Uganda</span>
      <h1>Building digital solutions that <span class="accent">move your business forward.</span></h1>
      <p class="lead">Reagan Soft Innovation Limited provides websites, business systems, e-commerce platforms, software development and branding — with a client portal that keeps every request, task, file and update in one place.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= app_url('register.php') ?>"><?= icon('arrow') ?> Start a Project</a>
        <a class="btn btn-ghost" href="<?= app_url('services.php') ?>">View Services</a>
      </div>
      <div class="hero-trust">
        <span><span class="tick"><?= icon('check') ?></span> Request work online</span>
        <span><span class="tick"><?= icon('check') ?></span> Track tasks &amp; progress</span>
        <span><span class="tick"><?= icon('check') ?></span> Clear, upfront pricing</span>
      </div>
    </div>
    <div class="hero-visual">
      <div class="portal-card">
        <div class="portal-card-head"><span class="live-dot"></span> Client portal — what you get</div>
        <div class="portal-rows">
          <div class="portal-row"><span class="ck"><?= icon('check') ?></span><div><b>Project requests</b><small>Submit work and attach files</small></div></div>
          <div class="portal-row"><span class="ck"><?= icon('check') ?></span><div><b>Status &amp; progress</b><small>Watch your project move forward</small></div></div>
          <div class="portal-row"><span class="ck"><?= icon('check') ?></span><div><b>Tasks &amp; deadlines</b><small>See exactly what is being worked on</small></div></div>
          <div class="portal-row"><span class="ck"><?= icon('check') ?></span><div><b>Messages &amp; files</b><small>Communicate about your project securely</small></div></div>
        </div>
        <a class="btn btn-dark btn-block" href="<?= app_url('register.php') ?>">Create your free client account <?= icon('arrow') ?></a>
      </div>
    </div>
  </div>
</section>

<section class="section" id="services-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">What we do</span><h2>Solutions designed for real businesses.</h2></div>
      <p>Choose a service, submit your requirements through the portal and follow the delivery from request to completion.</p>
    </div>
    <div class="cards">
      <?php foreach ($services as $s): ?>
        <article class="card">
          <div class="card-icon"><?= icon($s['icon']) ?></div>
          <h3><?= e($s['name']) ?></h3>
          <p><?= e(truncate($s['description'], 130)) ?></p>
          <?php if ($s['delivery_days']): ?>
            <div class="deliver" style="margin-top:12px"><?= icon('clock') ?><span>Delivery from <?= (int)$s['delivery_days'] ?> days</span></div>
          <?php endif; ?>
          <div class="price"><?= e($s['price_note']) ?> <strong><?= money($s['price'], settings('currency')) ?></strong></div>
          <a class="btn btn-ghost btn-sm mt-2" href="<?= app_url('services.php#' . $s['slug']) ?>">Request service <?= icon('arrow') ?></a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Why choose us</span><h2>A development partner, not just a developer.</h2></div>
    </div>
    <div class="value-grid">
      <div class="value-card">
        <div class="card-icon"><?= icon('shield') ?></div>
        <h3>Security by default</h3>
        <p>Every system is built with secure authentication, protected data, and tested code — not shortcuts.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('chat') ?></div>
        <h3>Clear communication</h3>
        <p>You always know what was requested, what is being worked on and what comes next — from your own client portal.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('pin') ?></div>
        <h3>Built in Jinja, Uganda</h3>
        <p>A local team that understands local businesses, payments and market realities — real support when you need it.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('doc') ?></div>
        <h3>Everything documented</h3>
        <p>Quotations, invoices, files and project history are kept in one organised, searchable workspace.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('layers') ?></div>
        <h3>Built to grow</h3>
        <p>Modular systems that scale with your business rather than locked one-off code that cannot evolve.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('clock') ?></div>
        <h3>Respect for your time</h3>
        <p>Clear delivery estimates, honest timelines and proactive updates so you are never left guessing.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="process-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Simple process</span><h2>From request to delivery.</h2></div>
      <p>Four clear stages. You stay informed at every step.</p>
    </div>
    <div class="steps">
      <div class="step"><span class="step-num">STEP 01</span><h3>Create account</h3><p>Register your name, company, phone and email — takes less than a minute.</p></div>
      <div class="step"><span class="step-num">STEP 02</span><h3>Send a request</h3><p>Describe what you need, add your budget and attach any supporting files.</p></div>
      <div class="step"><span class="step-num">STEP 03</span><h3>We review &amp; quote</h3><p>Our team reviews the work, agrees scope and sends a professional quotation.</p></div>
      <div class="step"><span class="step-num">STEP 04</span><h3>Track delivery</h3><p>Follow tasks, progress, messages and completion from your dashboard.</p></div>
    </div>
    <p class="center mt-3"><a class="btn btn-dark" href="<?= app_url('process.php') ?>">See the full process <?= icon('arrow') ?></a></p>
  </div>
</section>

<?php if ($featured): ?>
<section class="section section-soft" id="pricing-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">Transparent pricing</span><h2>Starting prices, clearly stated.</h2></div>
      <p>Every project is scoped individually — websites build up to <?= money(10000000, settings('currency')) ?> and business systems up to <?= money(20000000, settings('currency')) ?>.</p>
    </div>
    <div class="pricing-grid">
      <?php foreach ($featured as $i => $s): ?>
        <div class="price-card <?= $i === 1 ? 'featured' : '' ?>">
          <div class="card-icon"><?= icon($s['icon']) ?></div>
          <h3><?= e($s['name']) ?></h3>
          <p class="price-note"><?= e($s['price_note']) ?> <?= money($s['price'], settings('currency')) ?></p>
          <div class="amount"><?= (float)$s['price_max'] > (float)$s['price'] ? 'up to ' . money($s['price_max'], settings('currency')) : money($s['price'], settings('currency')) ?></div>
          <div class="deliver"><?= icon('clock') ?><span><?= (int)$s['delivery_days'] ?> day<?= ((int)$s['delivery_days']) === 1 ? '' : 's' ?>+ delivery estimate</span></div>
          <a class="btn <?= $i === 1 ? 'btn-primary' : 'btn-ghost' ?>" href="<?= app_url('services.php#' . $s['slug']) ?>">Request this service <?= icon('arrow') ?></a>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="center mt-3"><a href="<?= app_url('pricing.php') ?>">View all services &amp; pricing <?= icon('arrow') ?></a></p>
  </div>
</section>
<?php endif; ?>

<section class="section" id="about-home">
  <div class="container">
    <div class="about-grid">
      <div>
        <span class="eyebrow">About Reagan Soft Innovation</span>
        <h2>Built in Jinja. Designed for real businesses.</h2>
        <p class="lead mb-2"><?= e(settings('company_about', 'We help organisations turn ideas and everyday business processes into practical digital products.')) ?></p>
        <p class="muted">Our platform is built around clarity: you always know what was requested, what is being worked on, what has been completed and what comes next.</p>
        <div class="hero-actions mt-3">
          <a class="btn btn-primary" href="<?= app_url('about.php') ?>">More about us</a>
          <a class="btn btn-ghost" href="<?= app_url('portfolio.php') ?>">Our work</a>
        </div>
      </div>
      <div class="info-card">
        <a class="brand" href="<?= app_url('index.php') ?>"><img src="<?= app_url('assets/img/reagansoftinnovation-logo.jpeg') ?>" alt="" width="44" height="44"><span class="brand-text">Reagan Soft <b>Innovation</b></span></a>
        <h3>Work with a team, not a template.</h3>
        <p>From a founder at Jinja, our services are delivered by a focused team of developers and designers.</p>
        <div class="info-list">
          <div class="info-line"><?= icon('phone') ?><span><?= e(settings('company_phone', '+256730314979')) ?></span></div>
          <div class="info-line"><?= icon('pin') ?><span><?= e(settings('company_address', 'Jinja, Uganda')) ?></span></div>
          <div class="info-line"><?= icon('mail') ?><span><?= e(settings('company_email', 'info@reagansoft.com')) ?></span></div>
          <div class="info-line"><?= icon('flag') ?><span>Founded by <?= e(settings('company_founder', 'Reagan Otema')) ?></span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <span class="eyebrow" style="color:#7fd0ff">Let's build</span>
        <h2>Have a project in mind?</h2>
        <p>Register and send your first request through the secure client portal. We will review it and respond with a clear plan.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-light" href="<?= app_url('register.php') ?>">Create client account</a>
        <a class="btn btn-outline" style="border-color:#7fd0ff;color:#fff" href="<?= app_url('contact.php') ?>">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>