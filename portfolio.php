<?php
/**
 * Public "Our Work" gallery.
 *
 * Only projects the client has signed off are published here, which is
 * why the query filters on status = 'COMPLETED' rather than on a
 * separate "public" flag: completion and client approval are the same
 * event in this business, and a project nobody has approved should
 * never reach a public page.
 */

require __DIR__ . '/config/config.php';

/* ------------------------------------------------------------------
 * The showcase columns
 * ------------------------------------------------------------------
 * website_url, deliverables and cover_art were added to the projects
 * table after the first release. An install that has not run
 * database/upgrade.sql yet does not have them, and asking for a column
 * the table lacks would throw and leave the gallery empty. So the
 * query is built from what is actually there: an un-migrated site
 * still shows its delivered projects, just without the live links,
 * the badges and the drawn scenes.
 * ------------------------------------------------------------------ */
$cols     = 'p.ref_no, p.title, p.description, p.requirements, p.completed_at';
$showcase = [];
foreach (['website_url', 'deliverables', 'cover_art'] as $col) {
    if (db_has_column('projects', $col)) {
        $cols     .= ', p.' . $col;
        $showcase[] = $col;
    }
}

$projects = [];
try {
    $projects = db()->query(
        "SELECT $cols,
                s.name AS service, s.slug AS service_slug,
                u.company AS client_company
         FROM projects p
         LEFT JOIN services s ON s.id = p.service_id
         LEFT JOIN users u ON u.id = p.client_id
         WHERE p.status = 'COMPLETED'
         ORDER BY p.completed_at DESC, p.id DESC
         LIMIT 12"
    )->fetchAll();
} catch (Throwable $e) {
    log_error('portfolio query: ' . $e->getMessage());
}

/* ------------------------------------------------------------------
 * What the gallery adds up to
 * ------------------------------------------------------------------
 * Counted from the projects themselves rather than typed in, so the
 * strip can never claim a website was delivered when the row says it
 * was not. Each project is credited once per kind of thing it carried.
 * ------------------------------------------------------------------ */
$totals = ['website' => 0, 'system' => 0, 'apps' => 0];
$live   = 0;
foreach ($projects as $p) {
    foreach (array_keys(folio_kit_list($p['deliverables'] ?? null)) as $kind) {
        if (isset($totals[$kind])) {
            $totals[$kind]++;
        }
    }
    if (folio_live($p['website_url'] ?? null) !== '') {
        $live++;
    }
}

$filters = [
    ''        => ['All delivered work', 'grid'],
    'website' => ['Websites', 'globe'],
    'system'  => ['Business systems', 'cpu'],
    'apps'    => ['Mobile apps', 'mobile'],
];

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
    'desc'      => 'Delivered websites, business systems and mobile apps by Reagan Soft Innovation Limited in Jinja, Uganda — with live links you can open and the full scope of each build.',
    'active'    => 'portfolio',
    'canonical' => 'portfolio.php',
]);
?>
<section class="page-hero pi-grid">
  <div class="container">
    <div class="pi-copy">
      <span class="eyebrow">Our work</span>
      <h1>Real projects, delivered for real businesses.</h1>
      <p class="lead">Every build below was scoped, paid for and handed over in full. We publish a project only once its client has approved it for public display &mdash; and we leave the live link on the card so you can judge the work yourself, not take our word for it.</p>
      <?php if ($projects): ?>
        <div class="folio-count">
          <span class="tick"><?= icon('check') ?></span>
          <span><b><?= count($projects) ?></b> <?= count($projects) === 1 ? 'project' : 'projects' ?> delivered<?= $live > 0 ? ', ' . $live . ' ' . ($live === 1 ? 'live site' : 'live sites') . ' you can open' : '' ?></span>
        </div>
      <?php endif; ?>
    </div>
    <?= page_image('assets/img/hero4.webp', 4, 'Delivered work, published with client approval'); ?>
  </div>
</section>

<?php if ($projects): ?>
<section class="section">
  <div class="container">

    <?php if (array_sum($totals) > 0 || $live > 0): ?>
      <div class="folio-stats">
        <div class="fs-item">
          <span class="fs-ico"><?= icon('globe') ?></span>
          <span class="fs-txt"><b data-count="<?= $totals['website'] ?>"><?= $totals['website'] ?></b><small>Websites designed, built and launched</small></span>
        </div>
        <div class="fs-item">
          <span class="fs-ico"><?= icon('cpu') ?></span>
          <span class="fs-txt"><b data-count="<?= $totals['system'] ?>"><?= $totals['system'] ?></b><small>Business systems and client portals in daily use</small></span>
        </div>
        <div class="fs-item">
          <span class="fs-ico"><?= icon('mobile') ?></span>
          <span class="fs-txt"><b data-count="<?= $totals['apps'] ?>"><?= $totals['apps'] ?></b><small>Mobile apps shipped to the stores and their users</small></span>
        </div>
        <div class="fs-item">
          <span class="fs-ico"><?= icon('link') ?></span>
          <span class="fs-txt"><b data-count="<?= $live ?>"><?= $live ?></b><small>Live sites open to anyone, linked from this page</small></span>
        </div>
      </div>
    <?php endif; ?>

    <?php if (count($filters) > 1): ?>
      <div class="folio-filter" data-folio-filter role="group" aria-label="Filter delivered work">
        <div class="ff-bar">
          <?php foreach ($filters as $key => $f): ?>
            <button type="button" class="ff-chip<?= $key === '' ? ' is-active' : '' ?>" data-filter="<?= e($key) ?>" aria-pressed="<?= $key === '' ? 'true' : 'false' ?>">
              <?= icon($f[1]) ?><span><?= e($f[0]) ?></span>
            </button>
          <?php endforeach; ?>
        </div>
        <p class="ff-count" data-filter-count aria-live="polite">
          <?= count($projects) ?> <?= count($projects) === 1 ? 'project' : 'projects' ?> shown
        </p>
      </div>
    <?php endif; ?>

    <div class="folio-grid" data-folio-grid>
      <?php foreach ($projects as $i => $p): ?>
        <?php
        $kit      = folio_kit_list($p['deliverables'] ?? null);
        $liveHtml = folio_live($p['website_url'] ?? null);
        $client   = trim((string)($p['client_company'] ?? ''));
        ?>
        <article class="folio-card<?= $i === 0 ? ' is-featured' : '' ?>" data-kit="<?= e(implode(' ', array_keys($kit))) ?>">
          <?= folio_cover($p, $i + 1, $i === 0) ?>
          <div class="folio-body">
            <div class="folio-top">
              <span class="tag"><?= e($p['service'] ?? 'Custom project') ?></span>
              <?php if ($client !== ''): ?>
                <span class="folio-client"><?= e($client) ?></span>
              <?php endif; ?>
            </div>
            <h3><?= e($p['title']) ?></h3>
            <p class="folio-desc"><?= e($p['description']) ?></p>
            <?= folio_deliverables($p['deliverables'] ?? null) ?>
            <?= folio_scope($p['requirements'] ?? null) ?>
            <?= $liveHtml ?>
            <div class="folio-foot">
              <span class="folio-foot-done"><?= icon('check') ?><span>Delivered &amp; completed</span></span>
              <?php if (!empty($p['completed_at'])): ?>
                <span class="folio-foot-when"><?= icon('calendar') ?>Handed over <?= e(fmt_date((string)$p['completed_at'])) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <p class="folio-none" data-filter-empty hidden>
      <?= icon('search') ?>
      <span>Nothing in that category yet. <button type="button" class="folio-reset" data-filter-reset>Show everything</button></span>
    </p>

    <?php if (count($showcase) < 3): ?>
      <p class="folio-upgrade">
        <?= icon('settings') ?>
        <span>Live links, deliverable badges and the drawn cover scenes need three columns this site has not got yet. Run <b>database/upgrade.sql</b> once and reload &mdash; it adds them, loads the June 2026 projects and changes nothing else.</span>
      </p>
    <?php endif; ?>
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
