<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'Refund & Cancellation Policy | Reagan Soft Innovation Limited',
    'desc'      => 'How refunds and cancellations work at Reagan Soft Innovation Limited, including deposits, work in progress and delivered projects.',
    'active'    => 'legal',
    'canonical' => 'refund-policy.php',
]);
?>
<section class="page-hero pi-grid">
  <div class="container">
    <div class="pi-copy">
      <span class="eyebrow">Legal</span>
      <h1>Refund &amp; Cancellation Policy</h1>
      <p class="lead">A fair, written understanding of what happens to money when a project is cancelled.</p>
    </div>
    <?= page_image('assets/img/hero3.webp', 3, 'Fair, written protection for every payment'); ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <article class="legal">
      <p class="muted">Last updated: 23 September 2026</p>

      <h2>The short version</h2>
      <p>If we have not started your work, you can cancel and get your deposit back. If work is in progress, you are charged fairly for what has been completed. Once work is delivered and accepted, it is not refunded.</p>

      <h2>Deposits</h2>
      <p>Most projects start with a deposit, usually 50% of the quoted total, unless a different figure is written in your approved quotation. The deposit reserves our team's time and secures your slot in the delivery schedule.</p>

      <h2>No work started</h2>
      <p>If you cancel before we begin production, your deposit is refunded in full, minus any costs we have already paid on your behalf, such as domain registration or third party licences.</p>

      <h2>Work in progress</h2>
      <p>If you cancel while work is underway, you receive a refund of any amount above the fair value of completed work. Completed work includes design done, development done, testing and any third party costs already incurred. We agree this amount openly with you before any money changes hands.</p>

      <h2>Delivered work</h2>
      <p>Once a project is delivered, handed over and accepted, it is not refunded, because the value of completed software is the finished product and its source. Defects that are clearly our fault are fixed under the support terms in our <a href="<?= app_url('terms.php') ?>">Terms &amp; Conditions</a>.</p>

      <h2>How to cancel</h2>
      <p>Send your cancellation request by email to <?= e(settings('company_email', 'info@reagansoftinnovation.com')) ?> with the subject "Cancellation" and your project reference number. We confirm receipt and settle the position within a reasonable time.</p>

      <h2>Processing a refund</h2>
      <p>Approved refunds are paid back through the same channel you used to pay (mobile money, bank transfer or another agreed method) within the timeframe agreed at the time of approval, usually no more than two weeks.</p>

      <h2>Disputes</h2>
      <p>If you are not happy with how a refund was calculated, tell us what you disagree with and we will review it fairly. We are based in <?= e(settings('company_address', 'Jinja, Uganda')) ?> and we aim to resolve every dispute directly with you.</p>
    </article>
  </div>
</section>
<?php public_footer(); ?>