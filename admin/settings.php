<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$me = current_user();

$ALLOWED = [
    'company_name', 'company_tagline', 'company_founder', 'company_phone', 'company_phone_alt', 'company_email',
    'company_address', 'company_whatsapp', 'company_fb', 'company_x', 'company_linkedin',
    'company_about', 'currency', 'tax_percent', 'invoices_due_days',
    'payment_mtn_number', 'payment_airtel_number', 'payment_bank_details', 'payment_gateway_status',
    'project_deposit', 'registration_open',
];

$LOGO_KEYS = ['logo_mark', 'logo_mark_dark', 'logo_real', 'logo_real_dark'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'save_settings') {
        $logoUpdates = [];
        $logoErrors = [];
        $newFiles = [];

        foreach ($LOGO_KEYS as $key) {
            if (!empty($_POST['remove_' . $key])) {
                $logoUpdates[$key] = '';
                continue;
            }
            if (empty($_FILES[$key]['name'])) {
                continue;
            }
            $res = upload_image($_FILES[$key], 'branding', 900, false);
            if (!$res['ok']) {
                $logoErrors[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $res['error'];
            } else {
                $logoUpdates[$key] = $res['path'];
                $newFiles[] = $res['path'];
            }
        }

        if ($logoErrors) {
            foreach ($newFiles as $rel) {
                delete_upload($rel);
            }
            set_errors($logoErrors);
            redirect(app_url('admin/settings.php'));
        }

        $pdo->beginTransaction();
        $up = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($ALLOWED as $key) {
            $val = trim((string)($_POST[$key] ?? ''));
            if ($key === 'invoices_due_days') {
                $val = (string)max(1, (int)$val);
            } elseif (in_array($key, ['tax_percent', 'project_deposit'], true)) {
                $val = (string)max(0, (float)$val);
            }
            if ($key === 'registration_open') { $val = isset($_POST['registration_open']) ? '1' : '0'; }
            $up->execute([$key, $val]);
        }
        foreach ($logoUpdates as $key => $rel) {
            $old = settings($key, '');
            if ($old !== $rel) {
                delete_upload($old);
            }
            $up->execute([$key, $rel]);
        }
        // WhatsApp is a communication number and is independent from the
        // MTN payment number, so the two may differ.
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
foreach ($LOGO_KEYS as $key) { $cfg[$key] = $cfg[$key] ?? ''; }
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

<form method="post" action="settings.php" enctype="multipart/form-data" novalidate>
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
      <div class="field"><label for="company_phone">Phone for calls (primary)</label><input class="input" id="company_phone" name="company_phone" maxlength="40" value="<?= e($cfg['company_phone']) ?>"><div class="form-note">Used for phone calls and WhatsApp. Never used as a payment number.</div></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_phone_alt">Phone for calls (second)</label><input class="input" id="company_phone_alt" name="company_phone_alt" maxlength="40" value="<?= e($cfg['company_phone_alt']) ?>"><div class="form-note">A second call line. Shown next to the primary number.</div></div>
      <div class="field">
        <label for="company_whatsapp">WhatsApp number</label>
        <input class="input" id="company_whatsapp" name="company_whatsapp" maxlength="40" value="<?= e($cfg['company_whatsapp']) ?>" placeholder="0772514889 or +256772514889">
        <div class="form-note">Independent from the MTN MoMo number. Links as <code>wa.me/<?= e(whatsapp_number_raw()) ?></code>.</div>
      </div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_email">Email</label><input class="input" id="company_email" name="company_email" type="email" maxlength="190" value="<?= e($cfg['company_email']) ?>"></div>
      <div class="field"><label for="company_address">Address</label><input class="input" id="company_address" name="company_address" maxlength="255" value="<?= e($cfg['company_address']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_fb">Facebook URL</label><input class="input" id="company_fb" name="company_fb" maxlength="255" value="<?= e($cfg['company_fb']) ?>"></div>
      <div class="field"><label for="company_x">X (Twitter) URL</label><input class="input" id="company_x" name="company_x" maxlength="255" value="<?= e($cfg['company_x']) ?>"></div>
    </div>
    <div class="form-row">
      <div class="field"><label for="company_linkedin">LinkedIn URL</label><input class="input" id="company_linkedin" name="company_linkedin" maxlength="255" value="<?= e($cfg['company_linkedin']) ?>"></div>
    </div>
    <div class="field"><label for="company_about">About the company</label><textarea class="textarea" id="company_about" name="company_about" rows="4"><?= e($cfg['company_about']) ?></textarea></div>
  </section>

  <section class="panel">
    <div class="panel-head"><h3><?= icon('globe') ?> Brand logos</h3></div>
    <p class="small muted">Upload replacements for the shipped logos. PNG, JPG, GIF or WebP up to 10 MB. Transparent PNG is recommended. Leave a field empty to keep the current logo.</p>
    <div class="grid-2">
      <?php foreach ([
          'logo_mark'      => 'Mark, light backgrounds',
          'logo_mark_dark' => 'Mark, dark backgrounds',
          'logo_real'      => 'Full logo, light backgrounds',
          'logo_real_dark' => 'Full logo, dark backgrounds',
      ] as $key => $label): ?>
        <div class="field">
          <label for="<?= e($key) ?>"><?= e($label) ?></label>
          <?php
            $variant = str_starts_with($key, 'logo_real') ? 'real' : 'new';
            $dark = str_ends_with($key, '_dark');
            $preview = $cfg[$key] !== '' ? avatar_url($cfg[$key]) : logo_url($variant, $dark);
          ?>
          <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;padding:10px;border:1px solid rgba(8,59,102,.12);border-radius:12px;background:#fff">
            <img src="<?= e($preview) ?>" alt="" style="max-height:52px;max-width:140px;object-fit:contain">
          </div>
          <input class="input" id="<?= e($key) ?>" type="file" name="<?= e($key) ?>" accept=".png,.jpg,.jpeg,.gif,.webp">
          <?php if ($cfg[$key] !== ''): ?>
            <label class="check" style="display:flex;gap:8px;align-items:center;margin-top:6px"><input type="checkbox" name="remove_<?= e($key) ?>" value="1"><span class="small">Reset to shipped logo</span></label>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
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
    <div class="form-note">Tax and due days are defaults, quotations and invoices can override these per document.</div>
  </section>

  <section class="panel">
    <div class="panel-head"><h3><?= icon('settings') ?> Payment details</h3></div>
    <div class="form-row">
      <div class="field"><label for="payment_mtn_number">MTN MoMo number</label><input class="input" id="payment_mtn_number" name="payment_mtn_number" maxlength="40" value="<?= e($cfg['payment_mtn_number']) ?>"><div class="form-note">Payment destination only. WhatsApp uses its own number above.</div></div>
      <div class="field"><label>Airtel number</label><div class="input" style="display:flex;align-items:center;background:var(--sky-50)"><?= e($cfg['company_phone'] ?: 'Set the company phone above') ?></div><div class="form-note">Phone calls only. Airtel is not a payment destination.</div></div>
    </div>
    <div class="field"><label for="payment_bank_details">Bank details</label><textarea class="textarea" id="payment_bank_details" name="payment_bank_details" rows="3" placeholder="Bank name, account name, account number…"><?= e($cfg['payment_bank_details']) ?></textarea></div>
    <div class="form-row">
      <div class="field"><label for="project_deposit">Project deposit (<?= e($cfg['currency']) ?>)</label><input class="input" id="project_deposit" name="project_deposit" type="number" min="0" step="5000" value="<?= e($cfg['project_deposit']) ?>"><div class="form-note">One-time fixed deposit every client pays to start a project. It is credited against the final quotation, not an extra fee. Set to 0 to disable.</div></div>
    </div>
    <div class="field"><label for="payment_gateway_status">Online payment gateway</label>
      <select class="select" id="payment_gateway_status" name="payment_gateway_status">
        <?php foreach (['not_configured' => 'Not configured (manual confirmation)', 'ready' => 'Configured (active)'] as $k => $v): ?><option value="<?= $k ?>" <?= $cfg['payment_gateway_status'] === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
      </select>
      <div class="form-note">Online payments are confirmed manually. Gateway credentials are stored in server configuration, never in the database.</div>
    </div>
  </section>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Save settings</button>
    <a class="btn btn-ghost" href="<?= app_url('admin/settings.php') ?>">Discard changes</a>
  </div>
</form>
<?php dashboard_footer(); ?>