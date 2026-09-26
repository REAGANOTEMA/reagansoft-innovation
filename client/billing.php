<?php
/**
 * ============================================================
 * PAYMENT DETAILS  (client/billing.php)
 * ============================================================
 * The one page a client is sent to when they try to pay before we
 * know who they are, and the page they use to change those details
 * later. It is deliberately a normal portal page rather than a modal:
 * the client may be filling it in on a phone, half way through a
 * Mobile Money payment, and a modal that traps them is worse than a
 * page they can leave and come back to.
 *
 * WHERE THEY ARRIVE FROM
 *   ?next=<path>   the page they were trying to pay from. It is
 *                  validated by billing_safe_next() before it is used,
 *                  so it can only ever be a path on this site — see
 *                  the note on that function for why '//host' is
 *                  rejected as well as 'https://host'.
 *
 * WHAT IT DOES
 * Shows what is still outstanding, takes the details, validates them
 * through the same billing_validate() the gate uses, and returns the
 * client to where they were so the payment is never more than one
 * click away.
 * ============================================================
 */
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$user = current_user();

$fallback = app_url('client/index.php');
$next = billing_safe_next((string)($_POST['next'] ?? ($_GET['next'] ?? '')), '');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    [$errors, $clean] = billing_process_post($pdo, $user);

    if (!$errors) {
        // Re-read rather than patch the old array: the saved values are
        // now the truth, and the summary above them has to agree.
        $user = current_user() ?: $user;
        flash('success', 'Payment details saved. You can pay whenever you are ready.');
        redirect($next !== '' ? $next : $fallback);
    }

    // The payer type the client just chose decides which fields are
    // required, so redraw the form against their choice rather than
    // the one on file, or the business/individual branches flicker.
    $posted = billing_post_fields($_POST);
    foreach (['payer_type', 'company', 'tax_id', 'national_id', 'country', 'city'] as $key) {
        if (array_key_exists($key, $posted)) {
            $user[$key] = $posted[$key];
        }
    }
} else {
    // Errors carried over from the gate, which set them before
    // redirecting here.
    $errors = flash_errors();
}

$missing = billing_missing($user);
$progress = billing_progress($user);
$wasBlocked = $missing !== [];
?>
<?php dashboard_head(['title' => 'Payment Details', 'active' => 'billing', 'crumb' => 'Payment Details']); ?>

<div class="dash-head">
  <div>
    <span class="eyebrow">Payment details</span>
    <h2>Confirm who is paying</h2>
    <p class="sub">We ask for these once, before your first payment, so your invoice, receipt and contract all carry the same name. Change them any time and every future invoice uses the new details.</p>
  </div>
</div>

<?php render_alerts(); ?>

<?php if ($wasBlocked): ?>
  <div class="alert billing-blocked-note">
    <b><?= icon('lock') ?> One step left before you can pay.</b>
    <p>Fill in the <?= count($missing) ?> detail<?= count($missing) === 1 ? '' : 's' ?> marked below and you will be taken straight back to your payment. It takes about a minute, and you only do it once.</p>
  </div>
<?php elseif (billing_schema_ready()): ?>
  <div class="alert success billing-ok-note">
    <b><?= icon('check') ?> All set — you can pay.</b>
    <p>Your invoices will be raised to the details below. Keep them accurate, and update them here if anything changes.</p>
  </div>
<?php endif; ?>

<div class="grid-2 billing-layout">
  <section class="panel billing-panel">
    <div class="panel-head"><h3><?= icon('receipt') ?> Your details</h3></div>
    <?php
    billing_form_html(
        $user,
        $errors,
        $wasBlocked ? 'Save and continue to payment' : 'Save my payment details',
        $next,
        ''
    );
    ?>
  </section>

  <aside class="billing-aside">
    <section class="panel">
      <div class="panel-head"><h3><?= icon('list') ?> What is still needed</h3></div>
      <?php if ($wasBlocked): ?>
        <p class="small muted">Tick these off as you type. The bar at the top of the form keeps count.</p>
        <ul class="bl-list" data-billing-checklist>
          <?php foreach ($missing as $item): ?>
            <li data-billing-check="<?= e($item['key']) ?>"><span class="bl-tick"><?= icon('next') ?></span><span><b><?= e($item['label']) ?></b><small><?= e($item['why']) ?></small></span></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <ul class="bl-list all-done">
          <li><span class="bl-tick ok"><?= icon('check') ?></span><span><b>Everything is complete</b><small>You can submit a payment whenever you are ready.</small></span></li>
        </ul>
      <?php endif; ?>
    </section>

    <section class="panel">
      <div class="panel-head"><h3><?= icon('shield') ?> Why we ask</h3></div>
      <ul class="bl-why">
        <li><b>A correct invoice</b> Your name, business name and TIN are printed on the invoice. A wrong TIN means a tax claim you cannot make.</li>
        <li><b>Fast payment confirmation</b> We match your payment against the name and number on it. Wrong details is the most common reason a payment sits unconfirmed.</li>
        <li><b>Delivery and paperwork</b> Contracts, files and any hardware go to the address you give us.</li>
        <li><b>Reach you about this project only</b> We store these on your account. We never sell them or share them with anyone else.</li>
      </ul>
    </section>

    <?php if (!$wasBlocked): ?>
      <section class="panel">
        <div class="panel-head"><h3><?= icon('money') ?> Where to pay</h3></div>
        <?= payment_instructions_html() ?>
        <p class="small muted mb-0">Open any of your invoices or start a new project to submit a payment against it.</p>
        <a class="btn btn-outline btn-block mt-2" href="<?= e(app_url('client/projects.php')) ?>"><?= icon('folder') ?> Go to my projects</a>
      </section>
    <?php endif; ?>
  </aside>
</div>

<?php dashboard_footer(); ?>
