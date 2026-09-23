<?php
require __DIR__ . '/config/config.php';

$services = db()->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();
$currency = settings('currency');

$websiteMin = null; $websiteMax = null;
$systemMin  = null; $systemMax  = null;
foreach ($services as $s) {
    $max = (float)$s['price_max'] > 0 ? (float)$s['price_max'] : (float)$s['price'];
    $min = (float)$s['price'];
    if ($max <= 10000000 && $s['slug'] !== 'custom-solutions') {
        $websiteMin = $websiteMin === null ? $min : min($websiteMin, $min);
        $websiteMax = $websiteMax === null ? $max : max($websiteMax, $max);
    } else {
        $systemMin = $systemMin === null ? $min : min($systemMin, $min);
        $systemMax = $systemMax === null ? $max : max($systemMax, $max);
    }
}

public_head([
    'title'     => 'Pricing | Reagan Soft Innovation Limited',
    'desc'      => 'Transparent starting prices and ranges for websites (up to 10M UGX), business systems and software (up to 20M UGX), e-commerce and branding in Jinja, Uganda.',
    'active'    => 'pricing',
    'canonical' => 'pricing.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Pricing</span>
    <h1>Straightforward pricing, stated up front.</h1>
    <p class="lead">Every engagement begins at an honest starting point and scales to your scope. Websites move from under <?= money(1000000, $currency) ?> up to <?= money(10000000, $currency) ?> — business systems from a few million up to <?= money(20000000, $currency) ?>. The final figure is always approved by you before work begins.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="price-ranges">
      <div class="range-card">
        <div class="card-icon"><?= icon('globe') ?></div>
        <div>
          <h3>Websites & E-Commerce</h3>
          <p>Company websites, online stores, portfolios and web platforms — designed, built and launched for real results.</p>
        </div>
        <div class="range-amount">
          <span class="rp-from">From</span>
          <strong><?= money(($websiteMin ?? 800000), $currency) ?></strong>
          <span class="rp-to">up to <?= money(($websiteMax ?? 10000000), $currency) ?></span>
        </div>
      </div>
      <div class="range-card">
        <div class="card-icon"><?= icon('cpu') ?></div>
        <div>
          <h3>Business Systems & Software</h3>
          <p>Client portals, management systems, school systems, dashboards and automation built around your real processes.</p>
        </div>
        <div class="range-amount">
          <span class="rp-from">From</span>
          <strong><?= money(($systemMin ?? 1500000), $currency) ?></strong>
          <span class="rp-to">up to <?= money(($systemMax ?? 20000000), $currency) ?></span>
        </div>
      </div>
    </div>

    <div class="pricing-grid" style="margin-top:56px">
      <?php foreach ($services as $s): $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string)$s['features']))); ?>
        <div class="price-card">
          <div class="card-icon"><?= icon($s['icon']) ?></div>
          <h3><?= e($s['name']) ?></h3>
          <p class="price-note" style="margin:2px 0 0"><?= e($s['price_note']) ?> <?= money($s['price'], $currency) ?></p>
          <div class="amount small"><?= (float)$s['price_max'] > (float)$s['price'] ? 'up to ' . money($s['price_max'], $currency) : money($s['price'], $currency) ?></div>
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