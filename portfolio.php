<?php
require __DIR__ . '/config/config.php';

$projects = [];
try {
    $projects = db()->query(
        "SELECT p.title, p.description, s.name AS service
         FROM projects p
         LEFT JOIN services s ON s.id = p.service_id
         WHERE p.status = 'COMPLETED'
         ORDER BY p.completed_at DESC
         LIMIT 12"
    )->fetchAll();
} catch (Throwable $e) {
    log_error('portfolio query: ' . $e->getMessage());
}

$capabilities = [
    ['globe', 'Company websites', 'Responsive marketing and corporate websites that represent your brand and generate enquiries.'],
    ['cpu', 'Business systems', 'Client portals, inventories, bookings and internal management systems.'],
    ['cart', 'Ecommerce', 'Online stores with catalogues, orders and mobile money ready architecture.'],
    ['code-s', 'Custom software', 'Tailored web applications built around a specific business process.'],
    ['palette', 'Branding', 'Logos, guidelines and design assets that give your business a consistent identity.'],
    ['layers', 'Dashboards & tools', 'Reporting dashboards and practical tools that make daily decisions easier.'],
];

public_head([
    'title'     => 'Our Work | Projects by Reagan Soft Innovation Limited',
    'desc'      => 'Recent projects and development capabilities from Reagan Soft Innovation Limited: websites, business systems, ecommerce and custom software in Jinja, Uganda.',
    'active'    => 'portfolio',
    'canonical' => 'portfolio.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Our work</span>
    <h1>Real projects, delivered for real businesses.</h1>
    <p class="lead">A selection of finished work, added here as clients approve it for public display.</p>
  </div>
</section>

<?php if ($projects): ?>
<section class="section">
  <div class="container">
    <div class="folio-grid">
      <?php foreach ($projects as $p): ?>
        <article class="folio-card">
          <div class="folio-body">
            <span class="tag"><?= e($p['service'] ?? 'Custom project') ?></span>
            <h3><?= e($p['title']) ?></h3>
            <p class="muted"><?= e(truncate($p['description'], 160)) ?></p>
            <div class="deliver" style="margin-top:auto;padding-top:12px"><?= icon('check') ?><span>Delivered &amp; completed</span></div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section <?= $projects ? '' : '' ?>">
  <div class="container">
    <?php if (!$projects): ?>
      <div class="empty mb-3">
        <?= icon('folder') ?>
        <h4>Completed projects will be shown here</h4>
        <p>As soon as the first client project is completed and approved for showcase, it will appear in this gallery. In the meantime, here is the type of work we deliver:</p>
      </div>
    <?php else: ?>
      <div class="section-head"><div><span class="eyebrow">Beyond the gallery</span><h2>More of what we build.</h2></div></div>
    <?php endif; ?>
    <div class="value-grid">
      <?php foreach ($capabilities as $c): ?>
        <div class="value-card">
          <div class="card-icon"><?= icon($c[0]) ?></div>
          <h3><?= e($c[1]) ?></h3>
          <p><?= e($c[2]) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="cta">
      <div>
        <span class="eyebrow">Your project could be next</span>
        <h2>Spotted a problem our software can solve?</h2>
        <p>Send a request and see how we would approach it.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('checkout.php') ?>">Start a project</a>
    </div>
  </div>
</section>
<?php public_footer(); ?>