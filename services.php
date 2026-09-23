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
    <p class="lead">Services are scoped individually after a short conversation. Ranges below are honest starting points in <?= e(settings('currency')) ?> — websites and e-commerce move up to <?= money(10000000, settings('currency')) ?>, while business systems and software grow up to <?= money(20000000, settings('currency')) ?>.</p>
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
      <div class="price-card" style="<?= $i % 2 === 0 ? '' : 'border-color:var(--line);box-shadow:var(--shadow-sm)' ?>">
        <span class="price-note"><?= e($s['price_note']) ?> <?= money($s['price'], settings('currency')) ?></span>
        <div class="amount"><?= (float)$s['price_max'] > (float)$s['price'] ? 'up to ' . money($s['price_max'], settings('currency')) : money($s['price'], settings('currency')) ?></div>
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