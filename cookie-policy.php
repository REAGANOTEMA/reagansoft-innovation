<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'Cookie Policy | Reagan Soft Innovation Limited',
    'desc'      => 'How Reagan Soft Innovation Limited uses cookies and similar technologies on its website and client portal.',
    'active'    => 'legal',
    'canonical' => 'cookie-policy.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Legal</span>
    <h1>Cookie Policy</h1>
    <p class="lead">A short, honest note on the small files our site uses to keep you signed in and secure.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <article class="legal">
      <p class="muted">Last updated: 23 September 2026</p>

      <h2>What is a cookie?</h2>
      <p>A cookie is a small text file a website stores on your device. It helps the site remember you, keep you signed in and keep your session secure.</p>

      <h2>The cookies we use</h2>
      <p>We keep this deliberately simple. Our website and client portal use one essential cookie: a session cookie that keeps you signed in after you log in and protects your portal against forged requests. Without it, the portal cannot work.</p>
      <p>We do not use advertising cookies, tracking cookies, fingerprinting or third party analytics on this installation.</p>

      <h2>How to control cookies</h2>
      <p>You can clear or block cookies in your browser settings. If you block the session cookie, the client portal will not be able to keep you signed in, and you may need to sign in again on each visit. The public pages of the site work fine without cookies.</p>

      <h2>Changes to this policy</h2>
      <p>If we ever start using additional cookies, we will update this page and, where the law requires, ask for your consent first.</p>

      <h2>Contact</h2>
      <p>Questions about our use of cookies? Email <?= e(settings('company_email', 'info@reagansoft.com')) ?>.</p>
    </article>
  </div>
</section>
<?php public_footer(); ?>