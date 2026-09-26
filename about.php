<?php
require __DIR__ . '/config/config.php';

$phone  = settings('company_phone', '+256730314979');
$email  = settings('company_email', 'info@reagansoftinnovation.com');
$waText = 'Hello Reagan Soft Innovation, I would like to enquire about a project.';

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
    <?= page_image('assets/img/hero1.webp', 1, 'Software that is built properly and kept working') ?>
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
        <ul class="info-lines">
          <li><span class="il-icon"><?= icon('flag') ?></span><span><b>Founded by</b> <?= e(settings('company_founder', 'Reagan Otema')) ?></span></li>
          <li><span class="il-icon"><?= icon('pin') ?></span><span><b>Based in</b> <?= e(settings('company_address', 'Jinja, Uganda')) ?></span></li>
          <li><span class="il-icon"><?= icon('layers') ?></span><span><b>Focus</b> Websites &middot; Business systems &middot; Ecommerce &middot; Software</span></li>
        </ul>
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
    <div class="studio">
      <figure class="studio-media">
        <div class="studio-frame">
          <img src="<?= app_url('assets/img/computer-portrait.webp') ?>" width="900" height="1200"
               alt="The Reagan Soft Innovation development workstation in the Jinja office"
               loading="lazy" decoding="async">
          <span class="studio-glow" aria-hidden="true"></span>
        </div>
        <figcaption><span class="pi-led"></span>Reagan Soft Innovation development workstation in the Jinja office</figcaption>
      </figure>
      <div class="studio-body">
        <span class="eyebrow">Our studio</span>
        <h2>Development &amp; design under one roof.</h2>
        <p class="lead">From requirements and database design to the final launch, every build is handled in-house: design, code, testing and handover. There is never a finger-pointing game between separate vendors.</p>
        <ul class="feature-list">
          <li><span class="fcheck"><?= icon('check') ?></span> Requirements gathered and signed off before we build</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Database and access design done by the people who write the code</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Interface design and front-end build kept in the same conversation</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Testing, security review and launch handled by the same team</li>
          <li><span class="fcheck"><?= icon('check') ?></span> Handover, documentation and after-launch support included</li>
        </ul>
        <div class="studio-actions">
          <a class="btn btn-primary" href="<?= app_url('portfolio.php') ?>">See our work <?= icon('arrow') ?></a>
          <a class="btn btn-ghost" href="<?= app_url('process.php') ?>">How a project runs</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
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
            <span><?= icon('phone') ?><a href="<?= e(phone_tel($phone)) ?>"><?= e(phone_display($phone, $phone)) ?></a></span>
            <span><?= icon('mail') ?><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span>
            <span><?= icon('pin') ?><?= e(settings('company_address', 'Jinja, Uganda')) ?></span>
          </div>
        </div>
      </div>
      <div class="info-card">
        <h3>The principle Reagan builds by</h3>
        <p>Every project is judged by one question: will this still be easy to run, easy to hand over and easy to maintain a year from now?</p>
        <div class="info-list">
          <div class="info-line"><?= icon('shield') ?><span>Secure logins and role based access, not passwords in a spreadsheet.</span></div>
          <div class="info-line"><?= icon('doc') ?><span>Written scope, so what you approve is what gets built.</span></div>
          <div class="info-line"><?= icon('clock') ?><span>Realistic timelines, and news early if anything changes.</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head">
      <div><span class="eyebrow">How we work</span><h2>Our promise to every client.</h2></div>
      <p>Six commitments that apply to every project, from a one page website to a full management system.</p>
    </div>
    <div class="steps six">
      <div class="step"><span class="step-num">STEP 01</span><h3>We listen first</h3><p>Scope and requirements are agreed before any development begins.</p></div>
      <div class="step"><span class="step-num">STEP 02</span><h3>Written quotations</h3><p>You approve a clear quotation with items, pricing and terms before work starts.</p></div>
      <div class="step"><span class="step-num">STEP 03</span><h3>Visible progress</h3><p>Track tasks and completion in your portal, with no black box.</p></div>
      <div class="step"><span class="step-num">STEP 04</span><h3>Secure by default</h3><p>Protected logins, safe file handling and privacy respected.</p></div>
      <div class="step"><span class="step-num">STEP 05</span><h3>Timely delivery</h3><p>Realistic estimates with proactive updates if anything changes.</p></div>
      <div class="step"><span class="step-num">STEP 06</span><h3>Support after launch</h3><p>Maintenance plans and support keep your system healthy.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="info-card">
        <span class="eyebrow">Get started</span>
        <h3>Create a client account</h3>
        <p>Sign up free, send a project request and get a written response from our team.</p>
        <div class="info-list">
          <div class="info-line"><?= icon('check') ?><span>Free to register, no card needed to send a request.</span></div>
          <div class="info-line"><?= icon('check') ?><span>Your quotations, invoices and files stay in one portal.</span></div>
        </div>
        <a class="btn btn-light mt-2" href="<?= app_url('checkout.php') ?>">Start a project <?= icon('arrow') ?></a>
      </div>
      <div>
        <h2>Talk to us before you buy.</h2>
        <p class="lead">Not sure what you need? Send a message and we will give you a straight answer, including when a simpler solution will do.</p>
        <div class="contact-list three">
          <div class="contact-item">
            <span class="ci-icon"><?= icon('phone') ?></span>
            <div><b>Phone</b><span><a href="<?= e(phone_tel($phone)) ?>"><?= e(phone_display($phone, $phone)) ?></a></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('chat') ?></span>
            <div><b>WhatsApp</b><span><?= whatsapp_link(whatsapp_number_display(), $waText) ?></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('mail') ?></span>
            <div><b>Email</b><span><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span></div>
          </div>
        </div>
        <div class="hero-actions mt-3">
          <a class="btn btn-primary" href="<?= app_url('contact.php') ?>">Contact us <?= icon('arrow') ?></a>
          <a class="btn btn-ghost" href="<?= app_url('register.php') ?>">Create an account</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>
