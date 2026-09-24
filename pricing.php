<?php
require __DIR__ . '/config/config.php';

$services = db()->query("SELECT * FROM services WHERE status='active' ORDER BY sort_order, id")->fetchAll();
$currency = settings('currency');
$deposit  = deposit_amount();

$bandService = [
    'websites' => 1,
    'receipts' => 9,
    'apps'     => 8,
    'systems'  => 3,
];

public_head([
    'title'     => 'Pricing | Reagan Soft Innovation Limited',
    'desc'      => 'Honest pricing in Jinja, Uganda: professional websites from UGX 500,000 to UGX 10,000,000, automatic receipt systems from UGX 3,000,000 to UGX 10,000,000, web and mobile apps from UGX 10,000,000 and custom business systems from UGX 20,000,000 to UGX 40,000,000. Final quotes approved before work begins.',
    'active'    => 'pricing',
    'canonical' => 'pricing.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Pricing</span>
    <h1>Honest ranges, agreed before we start.</h1>
    <p class="lead">We work in clear pricing ranges: professional websites and ecommerce, automatic receipt and billing systems, apps for both web and mobile app stores, and larger custom business systems. Every project is secured with a fixed one time deposit of <strong style="color:var(--navy)"><?= money($deposit, $currency) ?></strong> (credited against your quotation). The final figure depends entirely on your requirements, and it is always written into a quotation that you approve before any development begins.</p>
  </div>
</section>

<section class="deposit-ribbon" role="note">
  <div class="container">
    <span class="dr-icon"><?= icon('shield') ?></span>
    <div>
      <b>How it works: choose a program → create your account → pay the <?= money($deposit, $currency) ?> deposit.</b>
      <p>Your deposit secures your slot and is credited to your project. You only pay the balance after approving your written quotation, never before.</p>
    </div>
    <a class="btn btn-ghost btn-sm" href="<?= app_url('checkout.php') ?>">Start now <?= icon('arrow') ?></a>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="price-bands">

      <article class="price-band" id="websites">
        <div class="pb-body">
          <span class="eyebrow">Web Development</span>
          <h2>Professional websites &amp; ecommerce</h2>
          <p>Company websites, online stores, portfolios and web platforms, designed, built and launched for real results.</p>
          <div class="pb-amount"><?= money(PRICE_WEBSITE_MIN, $currency) ?> <span class="pb-to">to</span> <?= money(PRICE_WEBSITE_MAX, $currency) ?></div>
          <a class="btn btn-primary" href="<?= app_url('checkout.php?service=' . (int)$bandService['websites']) ?>">Start a website project <?= icon('arrow') ?></a>
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

      <article class="price-band" id="receipts">
        <div class="pb-body">
          <span class="eyebrow">Receipt &amp; Billing Automation</span>
          <h2>Automatic receipts, invoices &amp; payment records</h2>
          <p>Whenever you make a sale, the system prints or sends a professional receipt automatically. It is perfect for shops, schools, clinics and service businesses.</p>
          <div class="pb-amount"><?= money(PRICE_RECEIPT_MIN, $currency) ?> <span class="pb-to">to</span> <?= money(PRICE_RECEIPT_MAX, $currency) ?></div>
          <a class="btn btn-primary" href="<?= app_url('checkout.php?service=' . (int)$bandService['receipts']) ?>">Start a receipt system project <?= icon('arrow') ?></a>
        </div>
        <div class="pb-factors">
          <b>Receipt system pricing depends on:</b>
          <ul class="feature-list">
            <li>Automatic receipt printing and sending</li>
            <li>Invoice generation and numbering</li>
            <li>Payment records and cash totals</li>
            <li>Mobile money reference capture</li>
            <li>School or hospital billing needs</li>
            <li>Daily and monthly report formats</li>
            <li>Shop and product catalogue size</li>
            <li>Staff roles and permissions</li>
            <li>Receipt branding and layout</li>
            <li>Training and handover</li>
          </ul>
        </div>
      </article>

      <article class="price-band alt" id="apps">
        <div class="pb-body">
          <span class="eyebrow">Web &amp; Mobile Applications</span>
          <h2>Apps for the App Store and Google Play</h2>
          <p>Business applications built to work as a website, an Android app and an iOS app, prepared, tested and published to the Apple App Store and Google Play.</p>
          <div class="pb-amount"><span class="pb-to" style="margin-right:6px">from</span><?= money(PRICE_APP_MIN, $currency) ?></div>
          <a class="btn btn-light" href="<?= app_url('checkout.php?service=' . (int)$bandService['apps']) ?>">Start an app project <?= icon('arrow') ?></a>
        </div>
        <div class="pb-factors">
          <b>App pricing depends on:</b>
          <ul class="feature-list">
            <li>Platforms: web, Android, iOS, or all</li>
            <li>App Store and Google Play publication</li>
            <li>Number of screens and features</li>
            <li>Dashboards and admin panels</li>
            <li>Push and SMS notifications</li>
            <li>Payment and mobile money integration</li>
            <li>Data, database and reporting needs</li>
            <li>Security and user accounts</li>
            <li>Testing across devices</li>
            <li>Support and updates</li>
          </ul>
        </div>
      </article>

      <article class="price-band" id="systems">
        <div class="pb-body">
          <span class="eyebrow">Custom Business Systems</span>
          <h2>Systems, software &amp; automation</h2>
          <p>Client portals, management systems, dashboards and workflow automation for schools, hospitals, NGOs, government offices and companies, built around your real business processes.</p>
          <div class="pb-amount"><?= money(PRICE_SYSTEM_MIN, $currency) ?> <span class="pb-to">to</span> <?= money(PRICE_SYSTEM_MAX, $currency) ?></div>
          <a class="btn btn-light" href="<?= app_url('checkout.php?service=' . (int)$bandService['systems']) ?>">Start a systems project <?= icon('arrow') ?></a>
        </div>
        <div class="pb-factors">
          <b>Business system pricing depends on:</b>
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
          <?php $srvPrice = service_price_display($s, $currency); $srvNote = trim((string)$s['price_note']); ?>
          <?php if ($srvNote !== '' && mb_stripos($srvPrice, $srvNote) === false): ?><p class="price-note" style="margin:8px 0 0"><?= e($srvNote) ?></p><?php endif; ?>
          <div class="amount"><?= e($srvPrice) ?></div>
          <?php if ($s['delivery_days']): ?>
            <div class="deliver"><?= icon('clock') ?><span><?= (int)$s['delivery_days'] ?> day<?= (int)$s['delivery_days'] === 1 ? '' : 's' ?>+ delivery estimate</span></div>
          <?php endif; ?>
          <?php if ($features): ?>
            <ul class="feature-list">
              <?php foreach (array_slice($features, 0, 5) as $f): ?><li><span class="fcheck"><?= icon('check') ?></span><?= e($f) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
          <a class="btn btn-primary" style="margin-top:auto" href="<?= app_url('checkout.php?service=' . (int)$s['id']) ?>"><?= icon('lock') ?> Choose &amp; pay deposit</a>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cta" style="margin-top:60px">
      <div>
        <span class="eyebrow">How quotations work</span>
        <h2>What happens after you pay your deposit?</h2>
        <p>The team reviews your requirements, prepares a detailed quotation with line items and agreed terms, and sends it to your client portal for approval.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-light" href="<?= app_url('checkout.php') ?>">Start a project</a>
        <a class="btn btn-outline" href="<?= app_url('process.php') ?>">See our process</a>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>