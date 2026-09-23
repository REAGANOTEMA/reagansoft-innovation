<?php
require __DIR__ . '/config/config.php';

$services = db()->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();
$currency = settings('currency');

$requestUrl = is_logged_in() && user_role() === 'client'
    ? 'client/request.php?service=' . (int)($services[0]['id'] ?? 0)
    : 'register.php';

public_head([
    'title'     => 'Pricing | Reagan Soft Innovation Limited',
    'desc'      => 'Honest pricing in Jinja, Uganda: professional websites from UGX 500,000 to UGX 10,000,000 and custom business systems from UGX 20,000,000 to UGX 40,000,000. Final quotes approved before work begins.',
    'active'    => 'pricing',
    'canonical' => 'pricing.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Pricing</span>
    <h1>Honest ranges, agreed before we start.</h1>
    <p class="lead">We work in two clear pricing ranges. The final figure depends entirely on your requirements — and it is always written into a quotation that you approve before any development begins.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="price-bands">

      <article class="price-band" id="websites">
        <div class="pb-body">
          <span class="eyebrow">Web Development</span>
          <h2>Professional websites &amp; e-commerce</h2>
          <p>Company websites, online stores, portfolios and web platforms — designed, built and launched for real results.</p>
          <div class="pb-amount"><?= money(PRICE_WEBSITE_MIN, $currency) ?> <span class="pb-to">to</span> <?= money(PRICE_WEBSITE_MAX, $currency) ?></div>
          <a class="btn btn-primary" href="<?= app_url($requestUrl) ?>">Start a website project <?= icon('arrow') ?></a>
        </div>
        <div class="pb-factors">
          <b>The final website price depends on:</b>
          <ul class="feature-list">
            <li>Number of pages</li>
            <li>Design complexity</li>
            <li>Functionality</li>
            <li>Integrations</li>
            <li>E-commerce requirements</li>
            <li>Custom features</li>
            <li>Content requirements</li>
            <li>Hosting &amp; domain requirements</li>
            <li>Maintenance requirements</li>
          </ul>
        </div>
      </article>

      <article class="price-band alt" id="systems">
        <div class="pb-body">
          <span class="eyebrow">Custom Business Systems</span>
          <h2>Systems, software &amp; automation</h2>
          <p>Client portals, management systems, dashboards and workflow automation — built around your real business processes.</p>
          <div class="pb-amount"><?= money(PRICE_SYSTEM_MIN, $currency) ?> <span class="pb-to">to</span> <?= money(PRICE_SYSTEM_MAX, $currency) ?></div>
          <a class="btn btn-light" href="<?= app_url($requestUrl) ?>">Start a systems project <?= icon('arrow') ?></a>
        </div>
        <div class="pb-factors">
          <b>Business-system pricing depends on:</b>
          <ul class="feature-list">
            <li>Business requirements</li>
            <li>Number of modules</li>
            <li>User roles</li>
            <li>Workflow complexity</li>
            <li>Integrations</li>
            <li>Reporting</li>
            <li>Automation</li>
            <li>Security requirements</li>
            <li>Deployment requirements</li>
            <li>Support requirements</li>
          </ul>
        </div>
      </article>

    </div>

    <p class="center muted mt-2">We do not promise a fixed final price before reviewing your requirements. Every project receives a detailed written quotation for approval.</p>

    <div class="pricing-grid mt-3">
      <?php foreach ($services as $s): $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string)$s['features']))); ?>
        <div class="price-card">
          <div class="card-icon"><?= icon($s['icon']) ?></div>
          <h3><?= e($s['name']) ?></h3>
          <?php if ($s['price_note'] !== ''): ?><p class="price-note" style="margin:8px 0 0"><?= e($s['price_note']) ?></p><?php endif; ?>
          <div class="amount"><?= e(service_price_display($s, $currency)) ?></div>
          <?php if ($s['delivery_days']): ?>
            <div class="deliver"><?= icon('clock') ?><span><?= (int)$s['delivery_days'] ?> day<?= (int)$s['delivery_days'] === 1 ? '' : 's' ?>+ delivery estimate</span></div>
          <?php endif; ?>
          <?php if ($features): ?>
            <ul class="feature-list">
              <?php foreach (array_slice($features, 0, 5) as $f): ?><li><span class="fcheck"><?= icon('check') ?></span><?= e($f) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <a class="btn btn-ghost" style="margin-top:auto" href="<?= app_url(is_logged_in() && user_role() === 'client' ? 'client/request.php?service=' . (int)$s['id'] : 'register.php') ?>">Request this service</a>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cta" style="margin-top:60px">
      <div>
        <span class="eyebrow">How quotations work</span>
        <h2>What happens after you send a request?</h2>
        <p>The team reviews your requirements, prepares a detailed quotation with line items and agreed terms, and sends it to your client portal for approval.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('process.php') ?>">See our process</a>
    </div>
  </div>
</section>
<?php public_footer(); ?>