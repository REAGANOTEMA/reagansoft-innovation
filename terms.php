<?php
require __DIR__ . '/config/config.php';

public_head([
    'title'     => 'Terms & Conditions | Reagan Soft Innovation Limited',
    'desc'      => 'The terms and conditions that apply when you register an account, request a project, accept a quotation or use the services of Reagan Soft Innovation Limited.',
    'active'    => 'legal',
    'canonical' => 'terms.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Legal</span>
    <h1>Terms &amp; Conditions</h1>
    <p class="lead">The agreed rules of the road between you and us, written so everyone can understand them.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <article class="legal">
      <p class="muted">Last updated: 23 September 2026</p>

      <h2>1. Agreement to these terms</h2>
      <p>By creating a client account, sending a request, accepting a quotation or paying an invoice, you agree to these terms and conditions. If you do not agree with any part of them, please do not use our services. These terms are enforced under the laws of the Republic of Uganda.</p>

      <h2>2. About us</h2>
      <p>Reagan Soft Innovation Limited is a software company based in <?= e(settings('company_address', 'Jinja, Uganda')) ?>. We design and build websites, business systems, ecommerce stores, custom software and related digital services.</p>

      <h2>3. Client account</h2>
      <p>You are responsible for keeping your account details accurate and your password safe. Anything done from your account is treated as done by you. One person or business may maintain one account unless we agree otherwise in writing.</p>

      <h2>4. Requests and quotations</h2>
      <ul>
        <li>A request describes the work you want done. We review it and may ask clarifying questions.</li>
        <li>Every project is confirmed by a written quotation showing line items, prices, terms and an expiry date.</li>
        <li>Work only begins after you approve the quotation in your portal. Scope, budget and deadline are the ones in that approved quotation.</li>
        <li>If the scope changes later, we prepare a revised quotation before extra work begins.</li>
      </ul>

      <h2>5. Payments and invoices</h2>
      <p>We issue an invoice with clear items and a due date. Unless a different arrangement is stated in your approved quotation, a deposit is required before work begins and the balance is due on delivery. Payments are recorded against your invoice, and accepted payment methods are shown on the invoice.</p>

      <h2>6. Refunds</h2>
      <p>Refunds are handled separately in our <a href="<?= app_url('refund-policy.php') ?>">Refund Policy</a>. In short, work not yet begun may be refunded, work in progress is charged for what has been completed, and delivered work is not refunded.</p>

      <h2>7. Your responsibilities</h2>
      <ul>
        <li>Provide complete and accurate requirements and feedback within agreed review periods.</li>
        <li>Supply the content, images, logos and brand assets we need from you on time.</li>
        <li>Give us access to systems or accounts that are genuinely required to complete the work.</li>
        <li>Lawfully own or licence any third party material you provide for use in your project.</li>
      </ul>
      <p>Deadlines assume these inputs arrive when expected. Delays you cause may move the delivery date fairly.</p>

      <h2>8. Ownership</h2>
      <p>Once the final balance on a project is paid in full, the finished work we created for you belongs to you, including the source code, design files and documentation. We keep the right to show completed work in our portfolio unless you ask us not to.</p>

      <h2>9. Third party tools and services</h2>
      <p>Where your project uses third party services, such as hosting, domains, payment gateways or mobile money accounts, those services are supplied under their own terms. We help you set them up, but their operation and fees are the responsibility of the provider and, where applicable, you.</p>

      <h2>10. Warranty and support</h2>
      <p>We deliver work that matches the approved specification. New projects include a reasonable period of support after delivery to fix defects that are clearly ours. Ongoing maintenance, new features and third party issues are billed separately under a maintenance plan or a new quotation.</p>

      <h2>11. Limitation of liability</h2>
      <p>We are not liable for indirect or consequential losses, such as loss of profit, revenue or data, except where the law does not allow us to limit liability. Our total liability for any claim is limited to the amount you have paid us for the project in question.</p>

      <h2>12. Confidentiality</h2>
      <p>We keep your business information private and only use it to deliver your project. Our obligations under this clause survive the end of a project.</p>

      <h2>13. Acceptable use</h2>
      <p>You agree not to use our site, portal or services for anything unlawful, to misuse another person's data, or to attempt to break the security of the platform. We may suspend an account for a serious or repeated breach.</p>

      <h2>14. Ending the relationship</h2>
      <p>You may cancel a project at any time. You remain responsible for work completed up to the cancellation date, and any refund is handled under our Refund Policy. We may stop serving an account that breaches these terms.</p>

      <h2>15. Changes to these terms</h2>
      <p>We may update these terms from time to time. Significant changes will be announced through your portal or by email. Continued use of the service after a change means you accept the updated terms.</p>

      <h2>16. Contact</h2>
      <p>Questions about these terms? Email <?= e(settings('company_email', 'info@reagansoft.com')) ?> or call <?= e(settings('company_phone', '+256730314979')) ?>.</p>
    </article>
  </div>
</section>
<?php public_footer(); ?>