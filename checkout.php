<?php
require __DIR__ . '/config/config.php';
require_client();

$pdo = db();
$user = current_user();
$currency = settings('currency');

$service = null;
$serviceId = (int)($_GET['service'] ?? 0);
if ($serviceId > 0) {
    $st = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'active' LIMIT 1");
    $st->execute([$serviceId]);
    $service = $st->fetch() ?: null;
}

$deposit = deposit_amount();
$errors = [];
$values = ['title' => '', 'description' => '', 'requirements' => '', 'method' => 'mtn_momo', 'reference' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values = [
        'title'        => trim($_POST['title'] ?? ''),
        'description'  => trim($_POST['description'] ?? ''),
        'requirements' => trim($_POST['requirements'] ?? ''),
        'method'       => $_POST['method'] ?? 'mtn_momo',
        'reference'    => trim($_POST['reference'] ?? ''),
    ];
    if (!in_array($values['method'], ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'], true)) {
        $values['method'] = 'mtn_momo';
    }

    if ($values['title'] === '' || mb_strlen($values['title']) > 190) {
        $errors[] = 'Please give your project a short title.';
    }
    if ($values['description'] === '' || mb_strlen($values['description']) > 10000) {
        $errors[] = 'Please briefly describe the work you need (max 10,000 characters).';
    }
    if ($serviceId > 0 && $service === null) {
        $errors[] = 'The chosen program is not available. Please pick another program.';
    }
    if (mb_strlen($values['reference']) > 120) {
        $errors[] = 'The transaction reference is too long.';
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $ref = next_project_ref($pdo);
            $label = $service ? (string)$service['name'] : 'General software project';
            $ins = $pdo->prepare(
                "INSERT INTO projects (ref_no, client_id, service_id, title, description, requirements, priority, status)
                 VALUES (?,?,?,?,?,?, 'normal', 'NEW')"
            );
            $ins->execute([
                $ref,
                (int)$user['id'],
                $serviceId > 0 ? $serviceId : null,
                $values['title'],
                $values['description'],
                $values['requirements'] !== '' ? $values['requirements'] : null,
            ]);
            $projectId = (int)$pdo->lastInsertId();

            $invoiceId = issue_deposit_invoice($pdo, $projectId, (int)$user['id'], $deposit, $label);
            $pdo->prepare(
                "INSERT INTO payments (invoice_id, amount, method, reference, status, notes)
                 VALUES (?,?,?,?, 'pending', 'Project deposit, initiated by client')"
            )->execute([$invoiceId, $deposit, $values['method'], $values['reference'] !== '' ? $values['reference'] : null]);

            audit('project_started', 'project', $projectId, 'Client started ' . $label . ' (' . $ref . ') with deposit payment submitted');
            audit('payment_submitted', 'invoice', $invoiceId, 'Client submitted deposit of ' . $deposit . ' ' . $currency . ' for ' . $ref);
            notify((int)$user['id'], 'Project started', 'Your project ' . $ref . ' has been created and your deposit payment is in review. We will confirm it shortly.', 'project', $projectId);
            notify_staff('New project + deposit', $user['full_name'] . ' started "' . $values['title'] . '" (' . $ref . ') and submitted a deposit of ' . money($deposit, $currency) . '.', 'project', $projectId);

            $pdo->commit();
            flash('success', 'Project ' . $ref . ' created. Your deposit payment has been submitted for confirmation, and we will start as soon as it is verified.');
            clear_old();
            redirect(app_url('client/project.php?id=' . $projectId . '&tab=invoice'));
        } catch (Throwable $e) {
            $pdo->rollBack();
            log_error('checkout: ' . $e->getMessage());
            $errors[] = 'We could not complete your checkout right now. Please try again shortly.';
        }
    }
    keep_old(array_keys($values));
    set_errors($errors);
}

public_head([
    'title'     => 'Start Your Project | Reagan Soft Innovation Limited',
    'desc'      => 'Choose a program, secure your project with a one time deposit and get your software built by Reagan Soft Innovation Limited in Jinja, Uganda.',
    'robots'    => false,
]);
?>
<section class="page-hero checkout-hero">
  <div class="container">
    <span class="eyebrow">Checkout · <?= e($service['name'] ?? 'Your project') ?></span>
    <h1>Secure your project with a one time deposit.</h1>
    <p class="lead">Every project starts with a fixed deposit of <strong style="color:var(--navy)"><?= money($deposit, $currency) ?></strong>, which holds your slot, gets your requirements reviewed and is credited against your final quotation. The remaining cost is agreed in writing before development begins.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="checkout-steps" aria-label="Checkout progress">
      <div class="cs-step done"><span class="cs-dot"><?= icon('check') ?></span><div><b>Account created</b><small>You are signed in</small></div></div>
      <div class="cs-line"></div>
      <div class="cs-step done"><span class="cs-dot"><?= icon('check') ?></span><div><b>Program chosen</b><small><?= e($service['name'] ?? 'Your project') ?></small></div></div>
      <div class="cs-line"></div>
      <div class="cs-step current"><span class="cs-dot"><?= icon('money') ?></span><div><b>Pay deposit</b><small><?= money($deposit, $currency) ?></small></div></div>
    </div>

    <?php render_alerts(); ?>

    <div class="checkout-grid">
      <div class="checkout-col">

        <?php if ($service): $features = array_filter(array_map('trim', preg_split('/\r?\n/', (string)$service['features']))); ?>
          <section class="program-card">
            <div class="pc-head">
              <span class="svc-icon"><?= icon($service['icon']) ?></span>
              <div>
                <h3><?= e($service['name']) ?></h3>
                <?php $srvNote = trim((string)$service['price_note']); ?>
                <?php if ($srvNote !== ''): ?><p class="pc-note"><?= e($srvNote) ?></p><?php endif; ?>
              </div>
            </div>
            <p class="pc-desc"><?= e(truncate($service['description'], 220)) ?></p>
            <div class="pc-meta">
              <span><?= icon('money') ?> <?= e(service_price_display($service, $currency)) ?></span>
              <?php if ($service['delivery_days']): ?>
                <span><?= icon('clock') ?> <?= (int)$service['delivery_days'] ?>+ day<?= (int)$service['delivery_days'] === 1 ? '' : 's' ?></span>
              <?php endif; ?>
            </div>
            <?php if ($features): ?>
              <ul class="feature-list pc-features">
                <?php foreach (array_slice($features, 0, 5) as $f): ?><li><span class="fcheck"><?= icon('check') ?></span><?= e($f) ?></li><?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>
        <?php else: ?>
          <section class="program-card">
            <div class="pc-head">
              <span class="svc-icon"><?= icon('code-s') ?></span>
              <div>
                <h3>Custom software project</h3>
                <p class="pc-note">Tell us what you want to build</p>
              </div>
            </div>
            <p class="pc-desc">Start a general software project. After your deposit you will be guided through the requirements so our team can prepare an accurate quotation.</p>
          </section>
        <?php endif; ?>

        <section class="deposit-box">
          <div class="db-amount">
            <span class="db-label">Project deposit <small>(one time, credited to your quote)</small></span>
            <strong data-deposit="<?= (float)$deposit ?>"><?= money($deposit, $currency) ?></strong>
          </div>
          <p class="db-note"><?= icon('lock') ?> Your deposit is not an extra cost. It is applied to your project. The balance is fixed in a written quotation you approve before development begins.</p>
        </section>

        <?php $payHtml = payment_instructions_html(); ?>
        <?php if ($payHtml !== ''): ?>
          <section class="panel pay-panel">
            <div class="panel-head"><h3><?= icon('money') ?> How to pay your deposit</h3></div>
            <?= $payHtml ?>
            <p class="small muted mb-0">After sending the amount, come back and complete the form with your transaction reference so our team can confirm it quickly.</p>
          </section>
        <?php endif; ?>

      </div>

      <div class="checkout-col">
        <section class="panel checkout-form">
          <div class="panel-head"><h3><?= icon('send') ?> Submit deposit &amp; start</h3></div>
          <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="field"><label for="cs_title">Project title <span class="req">*</span></label><input class="input" id="cs_title" name="title" maxlength="190" required placeholder="e.g. Company website, school system, online store" value="<?= old('title', $values['title']) ?>"></div>
            <div class="field"><label for="cs_description">Briefly describe the work <span class="req">*</span></label><textarea class="textarea" id="cs_description" name="description" required maxlength="10000" rows="5" placeholder="What do you need built? Who uses it? Which pages/modules matter most?"><?= old('description', $values['description']) ?></textarea></div>
            <div class="field"><label for="cs_requirements">Detailed requirements <span class="muted">(optional)</span></label><textarea class="textarea" id="cs_requirements" name="requirements" maxlength="10000" rows="3" style="min-height:80px" placeholder="Platforms, integrations, content you already have, references…"><?= old('requirements', $values['requirements']) ?></textarea></div>

            <div class="field">
              <label for="cs_method">Payment method <span class="req">*</span></label>
              <select class="select" id="cs_method" name="method" required>
                <?php foreach (['mtn_momo' => 'MTN Mobile Money', 'airtel_money' => 'Airtel Money', 'bank' => 'Bank transfer', 'cash' => 'Cash', 'other' => 'Other'] as $k => $v): ?>
                  <option value="<?= $k ?>" <?= old('method', $values['method']) === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field"><label for="cs_reference">Transaction reference <span class="req">*</span></label><input class="input" id="cs_reference" name="reference" maxlength="120" required placeholder="e.g. MoMo reference / sender name" value="<?= old('reference', $values['reference']) ?>"><div class="form-note">The number or reference you sent the deposit with.</div></div>

            <div class="checkout-total">
              <span>One time project deposit</span>
              <strong><?= money($deposit, $currency) ?></strong>
            </div>

            <button class="btn btn-primary btn-block" type="submit"><?= icon('lock') ?> Pay deposit &amp; start project</button>
            <p class="small muted center mt-2 mb-0">By starting you agree to our <a href="<?= app_url('terms.php') ?>" target="_blank" rel="noopener">Terms &amp; Conditions</a>.</p>
          </form>
        </section>
      </div>
    </div>

  </div>
</section>
<?php public_footer(); ?>