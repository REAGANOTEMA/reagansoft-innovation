<?php
require __DIR__ . '/config/config.php';

$services = db()->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();

public_head([
    'title'     => 'Services & Pricing | Reagan Soft Innovation Limited',
    'desc'      => 'Web development, business systems, e-commerce, software development, branding and website maintenance in Jinja and across Uganda. Clear services and starting prices.',
    'active'    => 'services',
    'canonical' => 'services.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Services</span>
    <h1>Everything your business needs to go digital.</h1>
    <p class="lead">Services are scoped individually after a short conversation. Prices below are the actual ranges in <?= e(settings('currency')) ?> — professional websites and e-commerce run from <?= money(PRICE_WEBSITE_MIN, settings('currency')) ?> to <?= money(PRICE_WEBSITE_MAX, settings('currency')) ?>, automatic receipt and billing systems from <?= money(PRICE_RECEIPT_MIN, settings('currency')) ?> to <?= money(PRICE_RECEIPT_MAX, settings('currency')) ?>, apps from <?= money(PRICE_APP_MIN, settings('currency')) ?>, and custom business systems and software for schools, hospitals, NGOs and companies from <?= money(PRICE_SYSTEM_MIN, settings('currency')) ?> to <?= money(PRICE_SYSTEM_MAX, settings('currency')) ?>.</p>
  </div>
</section>

<?php foreach ($services as $i => $s): $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string)$s['features']))); ?>
<section class="section <?= $i % 2 === 1 ? 'section-soft' : '' ?>" id="<?= e($s['slug']) ?>">
  <div class="container">
    <div class="service-block">
      <div>
        <div class="card-icon"><?= icon($s['icon']) ?></div>
        <h2><?= e($s['name']) ?></h2>
        <p class="lead" style="font-size:16px"><?= nl2br(e($s['description'])) ?></p>
        <div class="hero-actions" style="margin-top:20px">
          <?php if (is_logged_in() && user_role() === 'client'): ?>
            <a class="btn btn-primary" href="<?= app_url('client/request.php?service=' . (int)$s['id']) ?>">Request this service <?= icon('arrow') ?></a>
          <?php else: ?>
            <a class="btn btn-primary" href="<?= app_url('register.php') ?>">Request this service <?= icon('arrow') ?></a>
          <?php endif; ?>
        </div>
      </div>
      <div class="price-card svc-card" style="<?= $i % 2 === 0 ? '' : 'border-color:var(--line);box-shadow:var(--shadow-sm)' ?>">
        <?php $srvPrice = service_price_display($s, settings('currency')); $srvNote = trim((string)$s['price_note']); ?>
        <?php if ($srvNote !== '' && mb_stripos($srvPrice, $srvNote) === false): ?><span class="price-note"><?= e($srvNote) ?></span><?php endif; ?>
        <div class="amount"><?= e($srvPrice) ?></div>
        <?php if ($s['delivery_days']): ?>
          <div class="deliver"><?= icon('clock') ?><span>Estimated delivery: <?= (int)$s['delivery_days'] ?> day<?= (int)$s['delivery_days'] === 1 ? '' : 's' ?>+</span></div>
        <?php endif; ?>
        <?php if ($features): ?>
          <ul class="feature-list">
            <?php foreach ($features as $f): ?><li><span class="fcheck"><?= icon('check') ?></span><?= e($f) ?></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <a class="btn btn-dark" href="<?= app_url('contact.php?subject=' . urlencode($s['name'])) ?>">Get a quote for this service</a>
      </div>
    </div>
  </div>
</section>
<?php endforeach; ?>

<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <span class="eyebrow">Not sure where to start?</span>
        <h2>Tell us about your project.</h2>
        <p>We will review your requirements and recommend the right service — or a simpler, cheaper alternative if one exists.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('contact.php') ?>">Talk to us <?= icon('arrow') ?></a>
    </div>
  </div>
</section>
<?php public_footer(); ?>