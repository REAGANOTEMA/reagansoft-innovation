<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'Our Process | How We Deliver Software in Jinja, Uganda',
    'desc'      => 'From project request to delivery: see how Reagan Soft Innovation Limited plans, quotes, builds, tests and hands over your website or business system.',
    'active'    => 'process',
    'canonical' => 'process.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Our process</span>
    <h1>A clear path from idea to delivery.</h1>
    <p class="lead">Every project follows the same structured lifecycle, so you always know exactly where things stand.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="steps" style="grid-template-columns:repeat(2,1fr)">
      <div class="step"><span class="step-num">STEP 01</span><h3>Create your client account</h3><p>Register your details free of charge. Your secure portal is created instantly.</p></div>
      <div class="step"><span class="step-num">STEP 02</span><h3>Send a project request</h3><p>Add a title, description, requirements, budget, priority and any supporting files.</p></div>
      <div class="step"><span class="step-num">STEP 03</span><h3>Requirement review</h3><p>Our team reviews the request, asks any clarifying questions and confirms the scope.</p></div>
      <div class="step"><span class="step-num">STEP 04</span><h3>Quotation &amp; approval</h3><p>You receive a written quotation with line items, pricing and terms — approve it in your portal.</p></div>
      <div class="step"><span class="step-num">STEP 05</span><h3>Development</h3><p>Work begins. Tasks are created, assigned and tracked with visible progress.</p></div>
      <div class="step"><span class="step-num">STEP 06</span><h3>Testing &amp; review</h3><p>Your project is tested, and you review the result with the team.</p></div>
      <div class="step"><span class="step-num">STEP 07</span><h3>Delivery &amp; handover</h3><p>Final files, credentials and training are handed over — status moves to Completed.</p></div>
      <div class="step"><span class="step-num">STEP 08</span><h3>Support &amp; maintenance</h3><p>Optional maintenance plans keep your system secure, backed up and up to date.</p></div>
    </div>
  </div>
</section>

<section class="section section-soft">
  <div class="container">
    <div class="section-head"><div><span class="eyebrow">Project lifecycle</span><h2>Real statuses, transparently tracked.</h2></div></div>
    <div class="panel">
      <div class="timeline">
        <div class="tl-item done"><b>Request Submitted</b><span>Your request is received and a reference number is generated.</span></div>
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
        <h2>Start your first request today.</h2>
        <p>The whole process takes less than five minutes to begin.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('register.php') ?>">Create client account</a>
    </div>
  </div>
</section>
<?php public_footer(); ?>