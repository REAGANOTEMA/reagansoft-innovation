<?php
require __DIR__ . '/config/config.php';

$deposit = deposit_amount();

public_head([
    'title'     => 'Our Process | How We Deliver Software in Jinja, Uganda',
    'desc'      => 'From choosing a program to delivery: create your account, secure your project with a one time deposit, then follow your website or business system from request to handover with Reagan Soft Innovation Limited.',
    'active'    => 'process',
    'canonical' => 'process.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Our process</span>
    <h1>A clear path from idea to delivery.</h1>
    <p class="lead">Every project follows the same structured lifecycle: choose your program, create your account, secure it with a <?= money($deposit, settings('currency')) ?> deposit, and track everything until handover.</p>
    <div class="hero-actions" style="margin-top:24px">
      <a class="btn btn-primary" href="<?= app_url('checkout.php') ?>"><?= icon('rocket') ?> Start a project now</a>
      <a class="btn btn-ghost" href="<?= app_url('pricing.php') ?>">See programs &amp; pricing</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="process-flow">
      <div class="pf-step">
        <span class="pf-num">01</span>
        <div class="pf-body">
          <h3>Choose your program</h3>
          <p>Browse the programs on the services and pricing pages: websites, ecommerce, receipt systems, apps, business systems and more. Pick the one that fits your project.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">02</span>
        <div class="pf-body">
          <h3>Create your account or sign in</h3>
          <p>You must have a client account to start a project. Registration is free and takes under a minute, and your secure portal is created instantly.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">03</span>
        <div class="pf-body">
          <h3>Pay your project deposit</h3>
          <p>A fixed one time deposit of <?= money($deposit, settings('currency')) ?> secures your project and is <strong>credited against your final quotation</strong>. Pay by MTN MoMo, Airtel Money, bank transfer or cash, and we confirm it within the day.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">04</span>
        <div class="pf-body">
          <h3>Send your project details</h3>
          <p>Add a title, description, requirements, budget and any supporting files. Everything is saved in your portal.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">05</span>
        <div class="pf-body">
          <h3>Requirement review</h3>
          <p>Our team reviews the request, asks any clarifying questions and confirms the scope.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">06</span>
        <div class="pf-body">
          <h3>Quotation &amp; approval</h3>
          <p>You receive a written quotation with line items, pricing (minus your deposit) and terms, which you approve in your portal.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">07</span>
        <div class="pf-body">
          <h3>Development</h3>
          <p>Work begins. Tasks are created, assigned and tracked with visible progress, and you can message the team any time.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">08</span>
        <div class="pf-body">
          <h3>Testing &amp; review</h3>
          <p>Your project is tested, and you review the result with the team.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">09</span>
        <div class="pf-body">
          <h3>Delivery &amp; handover</h3>
          <p>Final files, credentials and training are handed over, and the status moves to Completed.</p>
        </div>
      </div>
      <div class="pf-step">
        <span class="pf-num">10</span>
        <div class="pf-body">
          <h3>Support &amp; maintenance</h3>
          <p>Optional maintenance plans keep your system secure, backed up and up to date.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">Project lifecycle</span><h2>Real statuses, transparently tracked.</h2></div></div>
    <div class="panel">
      <div class="timeline">
        <div class="tl-item done"><b>Deposit Received</b><span>Your deposit is confirmed and your project is activated.</span></div>
        <div class="tl-item"><b>Requirement Review</b><span>Scope and requirements are confirmed.</span></div>
        <div class="tl-item"><b>Quotation</b><span>A written quotation is prepared for your approval.</span></div>
        <div class="tl-item"><b>Approval</b><span>You approve the quotation and development begins.</span></div>
        <div class="tl-item"><b>Development</b><span>Tasks are built and tracked with visible progress.</span></div>
        <div class="tl-item"><b>Testing</b><span>Quality checks and your review.</span></div>
        <div class="tl-item"><b>Delivery</b><span>Handover of files, access and documentation.</span></div>
        <div class="tl-item"><b>Completed</b><span>Your project is finished and archived.</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <span class="eyebrow" style="color:#7fd0ff">Ready when you are</span>
        <h2>Start your project today.</h2>
        <p>Choose a program, create your account and pay your <?= money($deposit, settings('currency')) ?> deposit. It takes less than five minutes to begin.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('checkout.php') ?>">Start a project <?= icon('arrow') ?></a>
    </div>
  </div>
</section>
<?php public_footer(); ?>