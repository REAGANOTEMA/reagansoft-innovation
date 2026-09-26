<?php
require __DIR__ . '/config/config.php';

$projects = [];
try {
    $projects = db()->query(
        "SELECT p.ref_no, p.title, p.description, p.requirements, p.completed_at,
                s.name AS service, s.slug AS service_slug
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
<section class="page-hero pi-grid">
  <div class="container">
    <div class="pi-copy">
      <span class="eyebrow">Our work</span>
      <h1>Real projects, delivered for real businesses.</h1>
      <p class="lead">Every build below was scoped, paid for and handed over in full. We publish a project only once its client has approved it for public display.</p>
      <?php if ($projects): ?>
        <div class="folio-count">
          <span class="tick"><?= icon('check') ?></span>
          <span><b><?= count($projects) ?></b> <?= count($projects) === 1 ? 'project' : 'projects' ?> delivered and approved for display</span>
        </div>
      <?php endif; ?>
    </div>
    <?= page_image('assets/img/hero4.webp', 4, 'Delivered work, published with client approval'); ?>
  </div>
</section>

<?php if ($projects): ?>
<section class="section">
  <div class="container">
    <div class="folio-grid">
      <?php foreach ($projects as $i => $p): ?>
        <article class="folio-card<?= $i === 0 ? ' is-featured' : '' ?>">
          <?= folio_cover($p, $i + 1, $i === 0) ?>
          <div class="folio-body">
            <span class="tag"><?= e($p['service'] ?? 'Custom project') ?></span>
            <h3><?= e($p['title']) ?></h3>
            <p class="muted"><?= e(truncate($p['description'], 190)) ?></p>
            <?= folio_scope($p['requirements'] ?? null) ?>
            <div class="folio-foot">
              <span class="folio-foot-done"><?= icon('check') ?><span>Delivered &amp; completed</span></span>
              <?php if (!empty($p['completed_at'])): ?>
                <span class="folio-foot-when">Handed over <?= e(fmt_date((string)$p['completed_at'])) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <?php if (!$projects): ?>
      <div class="empty mb-3">
        <?= icon('folder') ?>
        <h4>Completed projects will be shown here</h4>
        <p>As soon as the first client project is completed and approved for showcase, it will appear in this gallery. In the meantime, here is the type of work we deliver:</p>
      </div>
    <?php else: ?>
      <div class="section-head">
        <div>
          <span class="eyebrow">Beyond the gallery</span>
          <h2>Most of what we build never appears here.</h2>
          <p class="section-sub">Internal systems, client portals and business tools are built under confidentiality and stay private. These are the disciplines behind them &mdash; if you need one of these, we have almost certainly built it before.</p>
        </div>
      </div>
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
        <p>Tell us what is getting in the way. We will come back with an approach, a scope and a fixed price &mdash; before you commit to anything.</p>
      </div>
      <a class="btn btn-light" href="<?= app_url('checkout.php') ?>">Start a project</a>
    </div>
  </div>
</section>
<?php public_footer(); ?>
