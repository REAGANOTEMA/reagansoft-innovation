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
      <h1>We build digital tools that <span class="accent">move your business forward.</span></h1>
      <p class="lead">Reagan Soft Innovation is the small team in Jinja that builds websites, business systems and custom software for businesses like yours — and keeps you in the loop through a simple client portal. No jargon, no guessing.</p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?= app_url('register.php') ?>"><?= icon('arrow') ?> Start a Project</a>
        <a class="btn btn-ghost" href="<?= app_url('services.php') ?>">See What We Build</a>
      </div>
      <div class="hero-trust">
        <span><span class="tick"><?= icon('check') ?></span> You can request work online</span>
        <span><span class="tick"><?= icon('check') ?></span> Tasks &amp; progress you can follow</span>
        <span><span class="tick"><?= icon('check') ?></span> Honest, upfront pricing</span>
      </div>
      <div class="hero-byline">
        <img src="<?= app_url('assets/img/founder.webp') ?>" alt="Reagan Otema, founder" width="40" height="40">
        <span>Built by <b>Reagan Otema</b>, founder — Jinja, Uganda</span>
      </div>
      <p class="hero-motto"><?= icon('rocket') ?> <?= e(settings('company_tagline', 'Innovating today for a smarter tomorrow.')) ?></p>
    </div>
    <div class="hero-visual">
      <div class="hero-stage">
        <img class="hero-shot" src="<?= app_url('assets/img/computer-setup1.webp') ?>" alt="Reagan Soft Innovation at work on a client build in Jinja">
        <span class="hero-wash" aria-hidden="true"></span>
        <span class="hero-chip chip-top"><span class="live-dot"></span> Online — Jinja, Uganda</span>
        <span class="hero-chip chip-mid"><?= icon('folder') ?><b><?= max(1, $completed) ?>+</b> projects delivered</span>
        <div class="code-card" aria-hidden="true">
          <div class="code-head"><span class="dots"><i></i><i></i><i></i></span><span class="code-name">RSI-2026-00002 · project.php</span></div>
          <pre class="code"><code><span class="t-kw">&lt;?php</span>
<span class="t-kw">if</span> (<span class="t-fn">review_scope</span>(<span class="t-var">$client</span>)) {
    <span class="t-fn">send_quotation</span>(<span class="t-var">$project</span>);
    <span class="t-var">$project</span>-&gt;<span class="t-var">status</span> = <span class="t-str">'APPROVED'</span>;
}
<span class="t-cmt">// scope it, quote it, deliver it</span></code></pre>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section" id="services-home">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">What we do</span><h2>Solutions for real businesses, not templates.</h2></div>
      <p>Pick a service, describe your requirements in the portal, and follow the work from request to delivery.</p>
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
    <div class="split-banner">
      <div class="split-media"><img src="<?= app_url('assets/img/codes-banner.webp') ?>" alt="Clean code written by the Reagan Soft Innovation team"><span class="split-shine" aria-hidden="true"></span></div>
      <div class="split-body">
        <span class="eyebrow">Clean code, on purpose</span>
        <h2>Code that is built to be looked after, not just launched.</h2>
        <p class="muted">Anyone can put up a page. We write systems that stay fast, stay secure and stay easy for you to use — with the database, access control and roles set up properly from the start.</p>
        <ul class="feature-list">
          <li><span class="fcheck"><?= icon('check') ?></span> Secure logins &amp; role-based access on every build</li>
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
      <div><span class="eyebrow">Why people work with us</span><h2>More than a developer — a partner in the work.</h2></div>
    </div>
    <div class="value-grid">
      <div class="value-card">
        <div class="card-icon"><?= icon('shield') ?></div>
        <h3>No security shortcuts</h3>
        <p>Passwords, logins and client data are protected properly from day one — not bolted on as an afterthought.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('chat') ?></div>
        <h3>You keep tabs on everything</h3>
        <p>Every request, message, task and file lives in one place, so you always know what is happening next.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('pin') ?></div>
        <h3>A real team in your time zone</h3>
        <p>Based in Jinja, working with businesses across Uganda — reachable by phone and WhatsApp when you need us.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('doc') ?></div>
        <h3>Write-ups you can actually follow</h3>
        <p>Quotations, invoices and project history stay saved and searchable. Nothing disappears into a lost email chain.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('layers') ?></div>
        <h3>Systems that grow with you</h3>
        <p>We build in modular pieces, so your software expands as your business does — no scrapping and starting over.</p>
      </div>
      <div class="value-card">
        <div class="card-icon"><?= icon('clock') ?></div>
        <h3>Honest timelines</h3>
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
      <div class="step"><span class="step-num">STEP 01</span><h3>Create account</h3><p>Register your name, company, phone and email — takes less than a minute.</p></div>
      <div class="step"><span class="step-num">STEP 02</span><h3>Send a request</h3><p>Describe what you need, add your budget and attach any supporting files.</p></div>
      <div class="step"><span class="step-num">STEP 03</span><h3>We review &amp; quote</h3><p>Our team reviews the work, agrees scope and sends a professional quotation.</p></div>
      <div class="step"><span class="step-num">STEP 04</span><h3>Track delivery</h3><p>Follow tasks, progress, messages and completion from your dashboard.</p></div>
    </div>
    <p class="center mt-3"><a class="btn btn-dark" href="<?= app_url('process.php') ?>">See the full process <?= icon('arrow') ?></a></p>
  </div>
</section>

<?php if ($featured): ?>
<section class="section" id="pricing-home">
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

<section class="section section-soft" id="about-home">
  <div class="container">
    <div class="about-grid">
      <div>
        <span class="eyebrow">About Reagan Soft Innovation</span>
        <h2>Built in Jinja. Designed for real businesses.</h2>
        <p class="lead mb-2"><?= e(settings('company_about', 'We help organisations turn ideas and everyday business processes into practical digital products.')) ?></p>
        <p class="muted">We keep things simple: you always see what was requested, what is being worked on, what has been completed and what comes next — all from your own portal.</p>
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
            <p>“We build software the way we would want it done for our own business — clear scope, honest pricing and work you can actually see.”</p>
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
        <p>Register and send your first request through the secure client portal. We will review it and respond with a clear plan.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-light" href="<?= app_url('register.php') ?>">Create client account</a>
        <a class="btn btn-outline" href="<?= app_url('contact.php') ?>">Contact us</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>