<?php
require __DIR__ . '/../config/config.php';
require_admin();

$pdo = db();
$me = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_service') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $priceMax = ($_POST['price_max'] ?? '') !== '' ? (float)$_POST['price_max'] : null;
        $priceNote = trim($_POST['price_note'] ?? 'From') ?: 'From';
        $features = trim($_POST['features'] ?? '');
        $delivery = (int)($_POST['delivery_days'] ?? 0) ?: null;
        $icon = trim($_POST['icon'] ?? 'code-s');
        $status = $_POST['status'] ?? 'active';
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($slug === '') { $slug = $slugify = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-'); }
        if ($name === '' || mb_strlen($name) > 150) $errors[] = 'Enter a service name.';
        if ($slug === '' || mb_strlen($slug) > 160) $errors[] = 'A valid slug is required.';
        if ($price < 0) $errors[] = 'Starting price cannot be negative.';
        if ($priceMax !== null && $priceMax < $price) $errors[] = 'The upper range must be at least the starting price.';
        if ($status !== 'active' && $status !== 'inactive') $status = 'active';

        if ($errors) { set_errors($errors); redirect(app_url('admin/services.php')); }

        if ($id > 0) {
            $pdo->prepare('UPDATE services SET name=?, slug=?, description=?, price=?, price_max=?, price_note=?, features=?, delivery_days=?, icon=?, status=?, sort_order=? WHERE id=?')
                ->execute([$name, $slug, $description, $price, $priceMax, $priceNote, $features, $delivery, $icon, $status, $sortOrder, $id]);
            audit('service_updated', 'services', $id, 'Updated "' . $name . '"');
            flash('success', 'Service updated.');
        } else {
            $pdo->prepare('INSERT INTO services (name, slug, description, price, price_max, price_note, features, delivery_days, icon, status, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$name, $slug, $description, $price, $priceMax, $priceNote, $features, $delivery, $icon, $status, $sortOrder]);
            audit('service_created', 'services', (int)$pdo->lastInsertId(), 'Created "' . $name . '"');
            flash('success', 'Service created.');
        }
        redirect(app_url('admin/services.php'));
    }

    if ($action === 'toggle_service') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] === 'active' ? 'inactive' : 'active';
        $pdo->prepare('UPDATE services SET status = ? WHERE id = ?')->execute([$status, $id]);
        audit('service_status_changed', 'services', $id, 'Service set to ' . $status);
        flash('success', 'Service updated.');
        redirect(app_url('admin/services.php'));
    }
}

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order, id")->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $st = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$iconOptions = ['globe', 'cpu', 'cart', 'code-s', 'palette', 'shield', 'layers', 'doc', 'users', 'chart', 'settings'];
$currency = settings('currency');

dashboard_head(['title' => 'Services', 'active' => 'services', 'crumb' => 'Services']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">Catalogue</span>
    <h2>Services &amp; pricing</h2>
    <p class="sub">Everything shown on the public services and pricing pages is managed from here.</p>
  </div>
  <button class="btn btn-primary" type="button" data-modal-open="service-modal"><?= icon('plus') ?> Add service</button>
</div>

<?php render_alerts(); ?>

<?php $formTarget = $edit ? (int)$edit['id'] : 0; ?>
<div class="modal-backdrop <?= $edit ? 'open' : '' ?>" id="service-modal">
  <div class="modal">
    <div class="modal-head"><h3><?= $edit ? 'Edit service' : 'Add service' ?></h3><button type="button" class="modal-close" data-modal-close="service-modal" aria-label="Close"><?= icon('close') ?></button></div>
    <form method="post" action="services.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_service">
      <input type="hidden" name="id" value="<?= $formTarget ?>">
      <div class="form-row">
        <div class="field"><label for="name">Service name *</label><input class="input" id="name" name="name" required maxlength="150" value="<?= e($edit['name'] ?? '') ?>" placeholder="e.g. Business Website Development"></div>
        <div class="field"><label for="slug">Slug (for page anchors)</label><input class="input" id="slug" name="slug" maxlength="160" value="<?= e($edit['slug'] ?? '') ?>" placeholder="auto-generated"></div>
      </div>
      <div class="field"><label for="description">Description *</label><textarea class="textarea" id="description" name="description" required rows="3"><?= e($edit['description'] ?? '') ?></textarea></div>
      <div class="form-row">
        <div class="field"><label for="price">Starting price (<?= e($currency) ?>) *</label><input class="input" id="price" name="price" type="number" min="0" step="100" required value="<?= e($edit ? (string)$edit['price'] : '') ?>"></div>
        <div class="field"><label for="price_max">Upper range (<?= e($currency) ?>)</label><input class="input" id="price_max" name="price_max" type="number" min="0" step="100" value="<?= e($edit && $edit['price_max'] !== null ? (string)$edit['price_max'] : '') ?>" placeholder="Optional — up to figure"></div>
      </div>
      <div class="form-row">
        <div class="field"><label for="price_note">Price label</label><input class="input" id="price_note" name="price_note" maxlength="120" value="<?= e($edit['price_note'] ?? 'From') ?>"></div>
        <div class="field"><label for="delivery_days">Delivery estimate (days)</label><input class="input" id="delivery_days" name="delivery_days" type="number" min="0" value="<?= e($edit && $edit['delivery_days'] !== null ? (string)$edit['delivery_days'] : '') ?>"></div>
      </div>
      <div class="form-row">
        <div class="field"><label for="icon">Icon</label>
          <select class="select" id="icon" name="icon">
            <?php foreach ($iconOptions as $io): ?><option value="<?= $io ?>" <?= ($edit['icon'] ?? 'code-s') === $io ? 'selected' : '' ?>><?= e(ucwords(str_replace('-', ' ', $io))) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="field"><label for="sort_order">Sort order</label><input class="input" id="sort_order" name="sort_order" type="number" min="0" value="<?= e($edit['sort_order'] ?? '0') ?>"></div>
      </div>
      <div class="field"><label for="features">Features (one per line)</label><textarea class="textarea" id="features" name="features" rows="5" placeholder="Custom responsive design&#10;Contact &amp; enquiry form"><?= e($edit['features'] ?? '') ?></textarea></div>
      <div class="field"><label for="status-<?= $formTarget ?>">Status</label>
        <select class="select" id="status-svc" name="status">
          <option value="active" <?= ($edit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active (visible on website)</option>
          <option value="inactive" <?= ($edit['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive (hidden)</option>
        </select>
      </div>
      <div class="modal-foot"><button type="button" class="btn btn-ghost" data-modal-close="service-modal">Cancel</button><button class="btn btn-primary" type="submit"><?= icon('check') ?> Save service</button></div>
    </form>
  </div>
</div>

<section class="panel">
  <?php if ($services): ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Service</th><th>Range</th><th>Delivery</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($services as $s): ?>
            <tr>
              <td><span class="row-title"><?= e($s['name']) ?></span><div class="row-sub"><?= e($s['slug']) ?></div></td>
              <td><b><?= money($s['price'], $currency) ?></b><?= $s['price_max'] !== null && (float)$s['price_max'] > (float)$s['price'] ? ' <span class="muted small">up to ' . money($s['price_max'], $currency) . '</span>' : '' ?></td>
              <td class="small"><?= $s['delivery_days'] ? (int)$s['delivery_days'] . ' days+' : '—' ?></td>
              <td class="small"><?= (int)$s['sort_order'] ?></td>
              <td><?= status_badge($s['status']) ?></td>
              <td style="min-width:170px">
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                  <a class="btn btn-ghost btn-sm" href="services.php?edit=<?= (int)$s['id'] ?>#service-modal" data-modal-open="service-modal"><?= icon('edit') ?> Edit</a>
                  <form method="post" data-confirm="<?= $s['status'] === 'active' ? 'Hide this service from the website?' : 'Show this service on the website?' ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_service">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <input type="hidden" name="status" value="<?= $s['status'] ?>">
                    <button class="btn btn-ghost btn-sm" type="submit"><?= $s['status'] === 'active' ? 'Hide' : 'Show' ?></button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="empty"><?= icon('globe') ?><h4>No services yet</h4><p>Add services to populate the public website and pricing page.</p></div>
  <?php endif; ?>
</section>
<?php dashboard_footer(); ?>