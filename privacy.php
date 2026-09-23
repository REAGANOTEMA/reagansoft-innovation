<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'Privacy Policy | Reagan Soft Innovation Limited',
    'desc'      => 'How Reagan Soft Innovation Limited collects, uses, protects and stores your personal data when you use our website, contact us or use our client portal.',
    'active'    => 'legal',
    'canonical' => 'privacy.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Legal</span>
    <h1>Privacy Policy</h1>
    <p class="lead">Your information stays yours. This policy explains, in plain words, what we collect and why.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <article class="legal">
      <p class="muted">Last updated: 23 September 2026</p>

      <h2>Who we are</h2>
      <p>Reagan Soft Innovation Limited is a software company based in Jinja, Uganda. We build websites, business systems, e-commerce stores and custom software for businesses in Uganda and beyond. When you see "we", "us" or "our" in this policy, it means Reagan Soft Innovation Limited.</p>

      <h2>Information we collect</h2>
      <p>We only collect what we genuinely need to serve you:</p>
      <ul>
        <li><strong>Contact details</strong> — your full name, phone number, email address and company name, given when you register, message us or request a service.</li>
        <li><strong>Project information</strong> — the descriptions, requirements and files you send when you request work.</li>
        <li><strong>Technical information</strong> — your IP address and the pages you visit, used to keep the site secure and to protect against abuse, such as repeated login attempts.</li>
      </ul>

      <h2>How we use your information</h2>
      <ul>
        <li>To set up and manage your client account and portal.</li>
        <li>To respond to your enquiries, prepare quotations and deliver your project.</li>
        <li>To record payments and issue invoices.</li>
        <li>To keep our site secure and investigate abuse.</li>
        <li>To contact you about your account or your project. We never add you to marketing lists without your consent.</li>
      </ul>

      <h2>We do not sell your data</h2>
      <p>We never sell, rent or trade your personal information. It is only shared with the people and services that need it to do the work, for example the hosting provider that keeps your project files, or a payment provider you asked us to use.</p>

      <h2>How long we keep it</h2>
      <p>We keep account and project records for as long as your account is active, and for a reasonable period afterwards so we can answer questions about past work, invoices and support. You can ask us to delete your data at any time, subject to records we are legally required to keep, such as invoices.</p>

      <h2>How we protect it</h2>
      <p>Passwords are stored as strong, one-way hashes. All forms are protected against forged submissions, traffic is encrypted when your browser supports it, and file uploads are checked for type and size. Access to your portal data is limited to the people who need it to deliver your project.</p>

      <h2>Your rights</h2>
      <p>You can ask us, at any time, to:</p>
      <ul>
        <li>show you the personal data we hold about you;</li>
        <li>correct anything that is wrong or out of date;</li>
        <li>delete your account and your personal data, where the law allows.</li>
      </ul>
      <p>To make a request, email <?= e(settings('company_email', 'info@reagansoft.com')) ?> or call <?= e(settings('company_phone', '+256730314979')) ?>. We respond within a reasonable time and never charge for a simple request.</p>

      <h2>Children</h2>
      <p>Our services are for businesses and adults. We do not knowingly collect information from children under 18.</p>

      <h2>Changes to this policy</h2>
      <p>If we change this policy, we will update the date above and, for significant changes, tell you through your portal or by email. Continuing to use the site after a change means you accept the updated policy.</p>

      <h2>Contact</h2>
      <p>Questions about this policy? Email <?= e(settings('company_email', 'info@reagansoft.com')) ?> or write to us at <?= e(settings('company_address', 'Jinja, Uganda')) ?>.</p>
    </article>
  </div>
</section>
<?php public_footer(); ?>