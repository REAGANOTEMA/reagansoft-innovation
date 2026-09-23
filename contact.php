<?php
require __DIR__ . '/config/config.php';

$errors = [];
$submitted = false;
$subject = isset($_GET['subject']) ? trim((string)$_GET['subject']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (!empty($_POST['company_website'])) {
        // Honeypot: bots fill hidden fields.
        flash('success', 'Thank you. Your message has been received.');
        redirect(app_url('contact.php'));
    }
    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || mb_strlen($name) > 150) $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($phone === '' || mb_strlen($phone) > 40) $errors[] = 'Please enter a phone number so we can reach you.';
    if ($message === '' || mb_strlen($message) > 5000) $errors[] = 'Please enter your message (max 5000 characters).';

    if (!$errors) {
        try {
            db()->prepare(
                'INSERT INTO contact_messages (full_name, email, phone, company, subject, message, ip_address) VALUES (?,?,?,?,?,?,?)'
            )->execute([$name, $email, $phone, $company ?: null, $subject ?: null, $message, $_SERVER['REMOTE_ADDR'] ?? null]);
            audit('contact_message_sent', 'contact_messages', (int)db()->lastInsertId(), 'Message from ' . $name);
            $submitted = true;
        } catch (Throwable $e) {
            log_error('contact insert: ' . $e->getMessage());
            $errors[] = 'We could not save your message right now. Please try again shortly.';
        }
    }
    if ($errors) {
        keep_old(['full_name', 'phone', 'email', 'company', 'subject', 'message']);
    }
}

$phone = settings('company_phone', '+256730314979');

public_head([
    'title'     => 'Contact Us | Reagan Soft Innovation Limited, Jinja, Uganda',
    'desc'      => 'Contact Reagan Soft Innovation Limited in Jinja, Uganda. Phone +256 730 314 979. Send us a message about your website, software or business system project.',
    'active'    => 'contact',
    'canonical' => 'contact.php',
]);
?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">Contact</span>
    <h1>Let's talk about your project.</h1>
    <p class="lead">Send us a message and we will respond with an honest, practical answer.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div>
        <h2>Reach us directly</h2>
        <p class="muted">Prefer to talk? Our team is based in Jinja and works with clients across Uganda.</p>
        <div class="contact-list">
          <div class="contact-item">
            <span class="ci-icon"><?= icon('phone') ?></span>
            <div><b>Phone</b><span><a href="tel:+256<?= preg_replace('/\D/', '', $phone) ?>"><?= e($phone) ?></a></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('chat') ?></span>
            <div><b>WhatsApp</b><span><?= whatsapp_link('Chat with us on WhatsApp', 'Hello Reagan Soft Innovation, I would like to enquire about a project.', '', '') ?></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('pin') ?></span>
            <div><b>Location</b><span><?= e(settings('company_address', 'Jinja, Uganda')) ?></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('mail') ?></span>
            <div><b>Email</b><span><?= e(settings('company_email', 'info@reagansoft.com')) ?></span></div>
          </div>
          <div class="contact-item">
            <span class="ci-icon"><?= icon('user') ?></span>
            <div><b>Founded by</b><span><?= e(settings('company_founder', 'Reagan Otema')) ?></span></div>
          </div>
        </div>
      </div>
      <div class="panel" style="margin:0">
        <?php if ($submitted): ?>
          <div class="alert success">Thank you, your message has been received. We will get back to you at <strong><?= e($email ?? '') ?></strong>.</div>
          <p class="muted">Your message has been stored safely in our system. If email delivery is enabled for this installation, you will also receive a copy.</p>
        <?php else: ?>
          <h3>Send us a message</h3>
          <?php if ($errors): foreach ($errors as $err): ?><div class="alert"><?= e($err) ?></div><?php endforeach; endif; ?>
          <form method="post" novalidate>
            <?= csrf_field() ?>
            <div class="field visually-hidden" aria-hidden="true">
              <label for="company_website">Company website</label>
              <input class="input" id="company_website" name="company_website" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-row">
              <div class="field"><label for="full_name">Full name *</label><input class="input" id="full_name" name="full_name" required maxlength="150" value="<?= old('full_name') ?>"></div>
              <div class="field"><label for="phone">Phone *</label><input class="input" id="phone" name="phone" required maxlength="40" value="<?= old('phone') ?>"></div>
            </div>
            <div class="form-row">
              <div class="field"><label for="email">Email *</label><input class="input" id="email" type="email" name="email" required maxlength="190" value="<?= old('email') ?>"></div>
              <div class="field"><label for="company">Company</label><input class="input" id="company" name="company" maxlength="190" value="<?= old('company') ?>"></div>
            </div>
            <div class="field"><label for="subject">Subject</label><input class="input" id="subject" name="subject" maxlength="190" value="<?= old('subject', '') !== '' ? old('subject') : e($subject) ?>"></div>
            <div class="field"><label for="message">Message *</label><textarea class="textarea" id="message" name="message" required maxlength="5000"><?= old('message') ?></textarea></div>
            <button class="btn btn-primary" type="submit"><?= icon('send') ?> Send message</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php public_footer(); ?>