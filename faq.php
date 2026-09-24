<?php
require __DIR__ . '/config/config.php';

$faqs = [
    ['How do I start a project with Reagan Soft Innovation?',
     'Create a free client account, then submit a New Request from your dashboard. Include a description, your budget, any requirements and supporting files. We review it and respond with a plan and a written quotation.'],
    ['What does a quotation include?',
     'A quotation lists every line item with quantities and unit prices, plus the total, any discount or tax, and our terms and expiry date. You approve it in your client portal before development begins.'],
    ['How are prices determined?',
     'Starting prices are published for each service. The final figure depends on scope, pages/features, integrations and timeline. You always approve the exact figure in writing before we start.'],
    ['Can I see progress while my project is being built?',
     'Yes. Your client portal shows the project status, overall progress, individual tasks, deadlines, files and messages, updated by the team as work happens.'],
    ['How do payments work?',
     'We issue an invoice with clear items and due date. Once payment is confirmed, the payment is recorded against your invoice. Mobile money and bank payment details are provided on your invoice.'],
    ['What file types can I attach to a request?',
     'PDF, Word, Excel, PowerPoint, TXT, CSV, images (PNG/JPG/GIF/WebP) and ZIP files up to 10 MB. Files are stored privately and are only visible to your project participants.'],
    ['Do you offer website maintenance?',
     'Yes. We offer maintenance plans covering security updates, backups, content changes and technical support on a recurring basis.'],
    ['Where is Reagan Soft Innovation based?',
     'We are based in Jinja, Uganda, and work with clients across the country and beyond.'],
];

public_head([
    'title'     => 'FAQ | Reagan Soft Innovation Limited',
    'desc'      => 'Frequently asked questions about working with Reagan Soft Innovation Limited: requests, quotations, progress, payments, maintenance and more.',
    'active'    => 'faq',
    'canonical' => 'faq.php',
]);
?>
<section class="page-hero pi-grid">
  <div class="container">
    <div class="pi-copy">
      <span class="eyebrow">FAQ</span>
      <h1>Frequently asked questions.</h1>
      <p class="lead">Straight answers about how we work, pay and deliver.</p>
    </div>
    <?= page_image('assets/img/hero6.webp', 6, 'Straight answers about how we work, pay and deliver'); ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="faq-list">
      <?php foreach ($faqs as $i => $faq): ?>
        <details class="faq-item" <?= $i === 0 ? 'open' : '' ?>>
          <summary><?= e($faq[0]) ?></summary>
          <div class="faq-body"><?= e($faq[1]) ?></div>
        </details>
      <?php endforeach; ?>
    </div>
    <div class="cta" style="margin-top:60px">
      <div>
        <span class="eyebrow">Still have questions?</span>
        <h2>Ask us directly.</h2>
        <p>We respond quickly with practical answers, even if the answer is that you do not need our services.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('contact.php') ?>">Contact us</a>
    </div>
  </div>
</section>
<?php public_footer(); ?>