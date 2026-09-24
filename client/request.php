<?php
require __DIR__ . '/../config/config.php';
require_client();

$pdo = db();
$services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY name")->fetchAll();
$preselect = isset($_GET['service']) ? (int)$_GET['service'] : 0;
$errors = [];
$values = ['title' => '', 'service_id' => '0', 'description' => '', 'requirements' => '', 'budget' => '', 'deadline' => '', 'priority' => 'normal', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values = [
        'title'        => trim($_POST['title'] ?? ''),
        'service_id'   => (string)(int)($_POST['service_id'] ?? 0),
        'description'  => trim($_POST['description'] ?? ''),
        'requirements' => trim($_POST['requirements'] ?? ''),
        'budget'       => trim($_POST['budget'] ?? ''),
        'deadline'     => trim($_POST['deadline'] ?? ''),
        'priority'     => in_array($_POST['priority'] ?? '', ['low', 'normal', 'high', 'urgent'], true) ? $_POST['priority'] : 'normal',
        'notes'        => trim($_POST['notes'] ?? ''),
    ];

    if ($values['title'] === '' || mb_strlen($values['title']) > 190)                $errors[] = 'Please enter a project title.';
    if ($values['description'] === '' || mb_strlen($values['description']) > 10000)  $errors[] = 'Please describe the work you need (max 10,000 characters).';
    if ($values['service_id'] !== '0' && !in_array($values['service_id'], array_map(fn($s) => (string)$s['id'], $services), true)) {
        $errors[] = 'Please choose a valid service.';
    }
    if ($values['budget'] !== '' && ((float)$values['budget'] <= 0 || (float)$values['budget'] > 999999999999)) {
        $errors[] = 'Please enter a valid budget.';
    }
    if ($values['deadline'] !== '' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['deadline']) || strtotime($values['deadline']) === false)) {
        $errors[] = 'Please enter a valid deadline date.';
    }

    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $res = upload_file($_FILES['attachment']);
        if (!$res['ok']) {
            $errors[] = $res['error'];
        } else {
            $attachment = $res;
        }
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            $ref = next_project_ref($pdo);
            $budget = $values['budget'] !== '' ? (float)$values['budget'] : null;
            $ins = $pdo->prepare(
                "INSERT INTO projects (ref_no, client_id, service_id, title, description, requirements, budget, priority, deadline, status)
                 VALUES (?,?,?,?,?,?,?,?,?,'NEW')"
            );
            $ins->execute([
                $ref, (int)current_user()['id'],
                $values['service_id'] !== '0' ? (int)$values['service_id'] : null,
                $values['title'], $values['description'], $values['requirements'] ?: null,
                $budget, $values['priority'], $values['deadline'] !== '' ? $values['deadline'] : null,
            ]);
            $projectId = (int)$pdo->lastInsertId();

            if ($attachment) {
                $pdo->prepare(
                    'INSERT INTO project_files (project_id, uploader_id, original_name, stored_name, file_path, mime_type, size_bytes) VALUES (?,?,?,?,?,?,?)'
                )->execute([
                    $projectId, (int)current_user()['id'], $attachment['name'], $attachment['stored'],
                    $attachment['path'], $attachment['mime'], $attachment['size'],
                ]);
            }

            $deposit = deposit_amount();
            if ($deposit > 0) {
                $label = '';
                foreach ($services as $sv) {
                    if ((int)$sv['id'] === (int)$values['service_id']) { $label = (string)$sv['name']; break; }
                }
                $depositInv = issue_deposit_invoice($pdo, $projectId, (int)current_user()['id'], $deposit, $label);
                notify((int)current_user()['id'], 'Project deposit', 'Your project deposit of ' . money($deposit, settings('currency')) . ' is due. Pay it from the Invoice tab to activate your project.', 'invoice', $projectId);
            }

            audit('request_submitted', 'project', $projectId, 'Request ' . $ref . ' submitted');
            notify((int)current_user()['id'], 'Request received', 'Your request ' . $ref . ' has been submitted. Complete your project deposit to activate it.', 'project', $projectId);
            notify_staff('New project request', $values['title'] . ' (' . $ref . ') received from ' . current_user()['full_name'] . '.', 'project', $projectId);
            $pdo->commit();
            flash('success', 'Your request has been submitted. Reference number: ' . $ref . '. Complete your project deposit to get it activated.');
            clear_old();
            redirect(app_url('client/project.php?id=' . $projectId . '&tab=invoice'));
        } catch (Throwable $e) {
            $pdo->rollBack();
            log_error('request submission: ' . $e->getMessage());
            $errors[] = 'We could not submit your request right now. Please try again shortly.';
        }
    }
    keep_old(array_keys($values));
    set_errors($errors);
}

dashboard_head(['title' => 'New Request', 'active' => 'request', 'crumb' => 'New Request']);
?>
<div class="dash-head">
  <div>
    <span class="eyebrow">New work request</span>
    <h2>Tell us what you need</h2>
    <p class="sub">Provide enough detail for our team to understand the work and prepare the right response.</p>
  </div>
</div>

<?php render_alerts(); ?>

<section class="panel" style="max-width:900px">
  <form method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="field"><label for="title">Project title <span class="req">*</span></label><input class="input" id="title" name="title" maxlength="190" required placeholder="e.g. Company website redesign" value="<?= old('title', $values['title']) ?>"></div>

    <div class="field">
      <label for="service_id">Service</label>
      <select class="select" id="service_id" name="service_id">
        <option value="0">Select a service (optional)</option>
        <?php foreach ($services as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $values['service_id'] === (string)$s['id'] || $preselect === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> — <?= e(service_price_short($s, settings('currency'))) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field"><label for="description">Describe the work <span class="req">*</span></label><textarea class="textarea" id="description" name="description" required maxlength="10000" placeholder="Goals, features, number of pages, references, special requirements…"><?= old('description', $values['description']) ?></textarea></div>

    <div class="field"><label for="requirements">Detailed requirements (optional)</label><textarea class="textarea" id="requirements" name="requirements" maxlength="10000" style="min-height:90px" placeholder="Anything specific: platforms, integrations, pages, content, designs you already have…"><?= old('requirements', $values['requirements']) ?></textarea></div>

    <div class="form-row">
      <div class="field"><label for="budget">Estimated budget (<?= e(settings('currency')) ?>)</label><input class="input" id="budget" type="number" min="0" step="10000" name="budget" placeholder="e.g. 500000" value="<?= old('budget', $values['budget']) ?>"></div>
      <div class="field"><label for="deadline">Preferred completion date</label><input class="input" id="deadline" type="date" name="deadline" value="<?= old('deadline', $values['deadline']) ?>"></div>
    </div>

    <div class="form-row">
      <div class="field">
        <label for="priority">Priority</label>
        <select class="select" id="priority" name="priority">
          <?php foreach (project_priority_labels() as $k => $v): ?>
            <option value="<?= e($k) ?>" <?= $values['priority'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label for="attachment">Supporting file</label>
        <input class="input" id="attachment" type="file" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.png,.jpg,.jpeg,.gif,.webp,.zip">
        <div class="form-note">PDF, Word, Excel, PowerPoint, TXT, CSV, images or ZIP — max 10 MB.</div>
      </div>
    </div>

    <div class="field"><label for="notes">Additional notes (optional)</label><textarea class="textarea" id="notes" name="notes" maxlength="5000" style="min-height:70px" placeholder="Anything else we should know…"><?= old('notes', $values['notes']) ?></textarea></div>

    <button class="btn btn-primary" type="submit"><?= icon('send') ?> Submit request</button>
    <a class="btn btn-ghost" href="<?= app_url('client/index.php') ?>">Cancel</a>
  </form>
</section>
<?php dashboard_footer(); ?>