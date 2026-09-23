<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$me = current_user();

$ALLOWED = [
    'company_name', 'company_tagline', 'company_founder', 'company_phone', 'company_email',
    'company_address', 'company_whatsapp', 'company_fb', 'company_x', 'company_linkedin',
    'company_about', 'currency', 'tax_percent', 'invoices_due_days',
    'payment_mtn_number', 'payment_airtel_number', 'payment_bank_details', 'payment_gateway_status',
    'registration_open',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save_settings') {
        $pdo->beginTransaction();
        $up = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($ALLOWED as $key) {
            $val = trim((string)($_POST[$key] ?? ''));
            if (in_array($key, ['tax_percent', 'invoices_due_days'], true)) {
                $val = in_array($key, ['invoices_due_days'], true) ? (string)max(1, (int)$val) : (string)max(0, (float)$val);
            }
            if ($key === 'registration_open') { $val = isset($_POST['registration_open']) ? '1' : '0'; }
            $up->execute([$key, $val]);
        }
        $pdo->commit();
        audit('settings_updated', 'settings', 0, 'Company settings updated by ' . $me['full_name']);
        flash('success', 'Settings saved.');
        redirect(app_url('admin/settings.php'));
    }
}

$rows = $pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
$cfg = [];
foreach ($rows as $r) { $cfg[$r['setting_key']] = $r['setting_value']; }
foreach ($ALLOWED as $key) { $cfg[$key] = $cfg[$key] ?? ''; }
$currency = $cfg['currency'] ?: 'UGX';

dashboard_head(['title' => 'Settings', 'active' => 'settings', 'crumb' => 'Settings']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Configuration</span>
    <h2>System settings</h2>
    <p class="sub">Brand, contact and payment details used across the website and invoices.</p>
  </div>
</div>

<?php render_alerts(); ?>

<form method="post" action="settings.php" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save_settings">

  <section class="panel">
    <div class="panel-head"><h3><?= icon('globe') ?> Company information</h3></div>
    <div class="form-row">
      <div class="field"><label for="company_name">Company name</label><input class="input" id="company_name" name="company_name" maxlength="190" value="<?= e($cfg['company_name']) ?>"></div>
      <div class="field"><label for="company_tagline">Tagline</label><input class="input" id="company_tagline" name="company_tagline" maxlength="190" value="<?= e($cfg['company_tagline']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_founder">Founder</label><input class="input" id="company_founder" name="company_founder" maxlength="120" value="<?= e($cfg['company_founder']) ?>"></div>
      <div class="field"><label for="company_phone">Phone</label><input class="input" id="company_phone" name="company_phone" maxlength="40" value="<?= e($cfg['company_phone']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_email">Email</label><input class="input" id="company_email" name="company_email" type="email" maxlength="190" value="<?= e($cfg['company_email']) ?>"></div>
      <div class="field"><label for="company_address">Address</label><input class="input" id="company_address" name="company_address" maxlength="255" value="<?= e($cfg['company_address']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_whatsapp">WhatsApp number</label><input class="input" id="company_whatsapp" name="company_whatsapp" maxlength="40" value="<?= e($cfg['company_whatsapp']) ?>" placeholder="+256… (leave empty to hide)"></div>
      <div class="field"><label for="company_fb">Facebook URL</label><input class="input" id="company_fb" name="company_fb" maxlength="255" value="<?= e($cfg['company_fb']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_x">X (Twitter) URL</label><input class="input" id="company_x" name="company_x" maxlength="255" value="<?= e($cfg['company_x']) ?>"></div>
      <div class="field"><label for="company_linkedin">LinkedIn URL</label><input class="input" id="company_linkedin" name="company_linkedin" maxlength="255" value="<?= e($cfg['company_linkedin']) ?>"></div>
    </div>
    <div class="field"><label for="company_about">About the company</label><textarea class="textarea" id="company_about" name="company_about" rows="4"><?= e($cfg['company_about']) ?></textarea></div>
  </section>

  <section class="panel">
    <div class="panel-head"><h3><?= icon('money') ?> Currency &amp; invoicing</h3></div>
    <div class="form-row">
      <div class="field"><label for="currency">Currency code</label><input class="input" id="currency" name="currency" maxlength="8" value="<?= e($cfg['currency']) ?>"></div>
      <div class="field"><label for="tax_percent">Default tax (%)</label><input class="input" id="tax_percent" name="tax_percent" type="number" min="0" max="100" step="0.1" value="<?= e($cfg['tax_percent']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="invoices_due_days">Invoice due (days)</label><input class="input" id="invoices_due_days" name="invoices_due_days" type="number" min="1" max="365" value="<?= e($cfg['invoices_due_days']) ?>"></div>
      <div class="field"><label class="check" style="display:flex;gap:8px;align-items:center;margin-top:28px">
        <input type="checkbox" name="registration_open" value="1" <?= $cfg['registration_open'] === '1' ? 'checked' : '' ?>><span class="small">Allow new client registrations</span>
      </label></div>
    </div>
    <div class="form-note">Tax and due days are defaults — quotations and invoices can override these per document.</div>
  </section>

  <section class="panel">
    <div class="panel-head"><h3><?= icon('settings') ?> Payment details</h3></div>
    <div class="form-row">
      <div class="field"><label for="payment_mtn_number">MTN MoMo number</label><input class="input" id="payment_mtn_number" name="payment_mtn_number" maxlength="40" value="<?= e($cfg['payment_mtn_number']) ?>"></div>
      <div class="field"><label for="payment_airtel_number">Airtel Money number</label><input class="input" id="payment_airtel_number" name="payment_airtel_number" maxlength="40" value="<?= e($cfg['payment_airtel_number']) ?>"></div>
    </div>
    <div class="field"><label for="payment_bank_details">Bank details</label><textarea class="textarea" id="payment_bank_details" name="payment_bank_details" rows="3" placeholder="Bank name, account name, account number…"><?= e($cfg['payment_bank_details']) ?></textarea></div>
    <div class="field"><label for="payment_gateway_status">Online payment gateway</label>
      <select class="select" id="payment_gateway_status" name="payment_gateway_status">
        <?php foreach (['not_configured' => 'Not configured (manual confirmation)', 'ready' => 'Configured (active)'] as $k => $v): ?><option value="<?= $k ?>" <?= $cfg['payment_gateway_status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
      <div class="form-note">Online payments are confirmed manually. Gateway credentials are stored in server configuration, never in the database.</div>
    </div>
  </section>

  <div style="display:flex;gap:10px;align-items:center">
    <button class="btn btn-primary" type="submit" style="padding:13px 22px;font-size:16px"><?= icon('check') ?> Save settings</button>
    <a class="btn btn-ghost" href="<?= app_url('admin/settings.php') ?>">Discard changes</a>
  </div>
</form>
<?php dashboard_footer(); ?>