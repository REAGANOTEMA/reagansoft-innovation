<?php
/**
 * Reagan Soft Innovation Limited — one-click database installer.
 *
 * Creates the database, imports database/schema.sql, then database/seed.sql.
 * Reads its connection settings from config/config.php (edit those, or set the
 * RSI_DB_* environment variables) — so the same file works on XAMPP and on a
 * hosted server.
 *
 * DELETE THIS FILE once installation is complete.
 */

require __DIR__ . '/config/config.php';

/* ------------------------------------------------------------------ */
/* Helpers                                                             */
/* ------------------------------------------------------------------ */

function rsi_connect(string $dbName = ''): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4' . ($dbName !== '' ? ';dbname=' . $dbName : '');
    return new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
    ]);
}

function rsi_is_installed(): bool
{
    try {
        $pdo = rsi_connect(DB_NAME);
        $n = (int)$pdo->query(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ' . $pdo->quote(DB_NAME)
        )->fetchColumn();
        return $n > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Split a .sql file into individual statements.
 * Handles quoted strings (incl. semicolons inside them), -- and # line
 * comments, and /* block comments. Skips CREATE DATABASE / USE so the
 * installer always owns the target database.
 */
function rsi_sql_statements(string $sql): array
{
    $stmts = [];
    $buf   = '';
    $len   = strlen($sql);
    $quote = null;
    $i     = 0;

    while ($i < $len) {
        $ch = $sql[$i];

        if ($quote !== null) {
            $buf .= $ch;
            if ($quote !== '`' && $ch === '\\') {
                $i++;
                if ($i < $len) { $buf .= $sql[$i]; }
            } elseif ($ch === $quote) {
                $quote = null;
            }
            $i++;
            continue;
        }

        if ($ch === "'" || $ch === '"' || $ch === '`') {
            $quote = $ch;
            $buf  .= $ch;
            $i++;
            continue;
        }

        if ($ch === '-' && ($sql[$i + 1] ?? '') === '-') {
            while ($i < $len && $sql[$i] !== "\n") { $i++; }
            $buf .= "\n";
            $i++;
            continue;
        }

        if ($ch === '#') {
            while ($i < $len && $sql[$i] !== "\n") { $i++; }
            $buf .= "\n";
            $i++;
            continue;
        }

        if ($ch === '/' && ($sql[$i + 1] ?? '') === '*') {
            $i += 2;
            while ($i < $len && !($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) { $i++; }
            $i += 2;
            $buf .= "\n";
            continue;
        }

        if ($ch === ';') {
            $stmts[] = trim($buf);
            $buf = '';
            $i++;
            continue;
        }

        $buf .= $ch;
        $i++;
    }

    if (trim($buf) !== '') { $stmts[] = trim($buf); }

    $out = [];
    foreach ($stmts as $s) {
        if ($s === '' || $s === null) { continue; }
        if (preg_match('/^\s*(CREATE\s+DATABASE|USE\s)/i', $s)) { continue; }
        $out[] = $s;
    }
    return $out;
}

function rsi_run_file(PDO $pdo, string $file, array &$log, array &$errors): int
{
    $sql = @file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        $errors[] = 'Could not read ' . basename($file) . ' — make sure the database/ folder came with the project.';
        return 0;
    }
    $count = 0;
    foreach (rsi_sql_statements($sql) as $stmt) {
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (PDOException $e) {
            $errors[] = basename($file) . ': ' . substr($e->getMessage(), 0, 220)
                . ' — ' . substr(preg_replace('/\s+/', ' ', $stmt), 0, 120);
        }
    }
    $log[] = basename($file) . ' imported (' . $count . ' statements).';
    return $count;
}

/* ------------------------------------------------------------------ */
/* State                                                               */
/* ------------------------------------------------------------------ */

$installed = rsi_is_installed();
$log       = [];
$errors    = [];
$done      = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $do = $_POST['do'] ?? 'install';

    if ($do === 'reinstall' && trim((string)($_POST['confirm_db'] ?? '')) !== DB_NAME) {
        $errors[] = 'Reinstall cancelled: type the exact database name to confirm (it wipes all current data).';
    }

    if (!$errors) {
        try {
            $ident = preg_replace('/[^A-Za-z0-9_]/', '', DB_NAME);
            if ($ident === '') { throw new RuntimeException('Invalid database name in config.'); }

            // 1. Make sure the database exists (may be skipped on restricted hosts).
            try {
                rsi_connect('')->exec(
                    'CREATE DATABASE IF NOT EXISTS `' . $ident . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
                );
                $log[] = 'Database `' . DB_NAME . '` is ready.';
            } catch (PDOException $e) {
                $log[] = 'No CREATE DATABASE privilege — using the existing `' . DB_NAME . '`.';
            }

            $pdo = rsi_connect(DB_NAME);

            // 2. Tables.
            rsi_run_file($pdo, __DIR__ . '/database/schema.sql', $log, $errors);

            // 3. Seed data (services, settings, demo accounts, samples).
            rsi_run_file($pdo, __DIR__ . '/database/seed.sql', $log, $errors);

            $installed = rsi_is_installed();
            $done      = $installed && !$errors;
            if ($done) {
                $log[] = 'Installation complete.';
            }
        } catch (Throwable $e) {
            log_error('install.php: ' . $e->getMessage());
            $errors[] = $e->getMessage();
        }
    }
}

/* ------------------------------------------------------------------ */
/* Output                                                              */
/* ------------------------------------------------------------------ */

$demoAccounts = [
    'admin@reagansoft.com'    => 'Administrator',
    'staff@reagansoft.com'    => 'Staff',
    'amara@kirekafarms.com'   => 'Client demo',
    'grace@pearlholdings.com' => 'Client demo',
    'david@mulumbasons.com'   => 'Client demo',
    'agnesnakato@gmail.com'   => 'Client demo',
];

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Install · Reagan Soft Innovation Limited</title>
<link rel="icon" type="image/png" href="assets/img/reagansoftinnovation-logo-favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Space+Grotesk:wght@500;600;700&family=JetBrains+Mono:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/tech.css">
<style>
  .install-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 48px 16px;
    background:
      radial-gradient(760px 420px at 88% 0%, rgba(30,158,234,.16), transparent 70%),
      linear-gradient(180deg, var(--sky-50), #fff);
    position: relative; overflow: hidden;
  }
  .install-wrap::before {
    content: ''; position: absolute; inset: 0;
    background:
      linear-gradient(rgba(10,123,206,.06) 1px, transparent 1px),
      linear-gradient(90deg, rgba(10,123,206,.06) 1px, transparent 1px);
    background-size: 42px 42px;
    -webkit-mask-image: radial-gradient(760px 520px at 50% 46%, #000, transparent 80%);
    mask-image: radial-gradient(760px 520px at 50% 46%, #000, transparent 80%);
  }
  .install-card {
    position: relative; z-index: 1;
    width: min(680px, 100%); background: #fff;
    border: 1px solid rgba(10,123,206,.18);
    border-radius: var(--radius-xl); padding: 38px;
    box-shadow: 0 30px 70px rgba(8,59,102,.16);
  }
  .install-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--blue), var(--cyan), var(--blue));
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
  }
  .install-card h1 { font-family: var(--font-disp); font-size: clamp(26px, 4vw, 34px); margin-bottom: 8px; }
  .sysbar {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    font-family: var(--font-mono); font-size: 10px; letter-spacing: .14em;
    text-transform: uppercase; color: var(--faint); margin-bottom: 18px;
  }
  .sysbar .led { display: inline-flex; align-items: center; gap: 7px; color: var(--blue); font-weight: 600; }
  .sysbar .led::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--green); box-shadow: 0 0 8px rgba(21,127,90,.6); }
  .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin: 18px 0 22px; }
  .meta-item { background: var(--sky-50); border: 1px solid var(--line-2); border-radius: var(--radius); padding: 12px 14px; }
  .meta-item small { display: block; font-family: var(--font-mono); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
  .meta-item b { font-family: var(--font-mono); font-size: 13.5px; color: var(--navy); word-break: break-all; }
  .install-log { margin: 4px 0 0; padding-left: 20px; color: var(--ink-2); font-size: 14.5px; }
  .install-log li { margin-bottom: 5px; }
  .install-log li::marker { color: var(--blue); }
  .ok-box { background: var(--green-bg); border: 1px solid rgba(21,127,90,.25); color: var(--green); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 18px; font-weight: 600; }
  .err-box { background: var(--red-bg); border: 1px solid rgba(179,64,58,.22); color: var(--red); border-radius: var(--radius); padding: 14px 16px; margin-bottom: 18px; font-size: 14.5px; }
  .err-box ul { margin: 8px 0 0; padding-left: 18px; }
  .accounts { width: 100%; border-collapse: collapse; font-size: 13.5px; margin: 6px 0 4px; }
  .accounts th, .accounts td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line-2); }
  .accounts th { font-family: var(--font-mono); font-size: 10.5px; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
  .accounts td:first-child { font-family: var(--font-mono); color: var(--navy); }
  .danger-zone { margin-top: 24px; padding-top: 18px; border-top: 1px dashed var(--line); }
  .danger-zone .field { margin-bottom: 10px; }
  .steps { counter-reset: step; padding-left: 0; list-style: none; margin: 0; }
  .steps li { counter-increment: step; position: relative; padding: 6px 0 6px 40px; color: var(--ink-2); font-size: 15px; }
  .steps li::before {
    content: counter(step, decimal-leading-zero);
    position: absolute; left: 0; top: 4px;
    font-family: var(--font-mono); font-weight: 700; font-size: 13px; color: var(--blue);
    background: var(--sky-100); border: 1px solid var(--line); border-radius: 7px;
    width: 30px; height: 26px; display: grid; place-items: center;
  }
  @media (max-width: 640px) { .install-card { padding: 26px 20px; } }
</style>
</head>
<body>
<div class="install-wrap">
  <div class="install-card">
    <div class="sysbar">
      <span class="led">RSI&nbsp;//&nbsp;INSTALL&nbsp;AGENT</span>
      <span><?= e(DB_NAME) ?></span>
    </div>

    <h1>Set up your database</h1>
    <p class="lead" style="font-size:15.5px">This installer creates one database, <b><?= e(DB_NAME) ?></b>, imports the schema and loads the services, settings and demo accounts.</p>

    <div class="meta-grid">
      <div class="meta-item"><small>Database</small><b><?= e(DB_NAME) ?></b></div>
      <div class="meta-item"><small>Host</small><b><?= e(DB_HOST) ?></b></div>
      <div class="meta-item"><small>User</small><b><?= e(DB_USER) ?></b></div>
      <div class="meta-item"><small>Status</small><b><?= $installed ? 'INSTALLED' : 'NOT INSTALLED' ?></b></div>
    </div>

    <?php if ($errors): ?>
      <div class="err-box">
        <b><?= $done ? 'Installed with warnings.' : 'The installation hit a problem:' ?></b>
        <ul>
          <?php foreach (array_slice($errors, 0, 8) as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
        </ul>
        <?php if (count($errors) > 8): ?><div class="small muted mt-1">…and <?= count($errors) - 8 ?> more in storage/logs/app.log</div><?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($done): ?>
      <div class="ok-box">✔ Database installed. You are ready to go.</div>
    <?php endif; ?>

    <?php if ($log): ?>
      <ul class="install-log">
        <?php foreach ($log as $l): ?><li><?= e($l) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if ($done || ($installed && !$log)): ?>
      <h3 class="mt-3">Sign in with a demo account</h3>
      <table class="accounts">
        <thead><tr><th>Email</th><th>Role</th></tr></thead>
        <tbody>
          <?php foreach ($demoAccounts as $email => $role): ?>
            <tr><td><?= e($email) ?></td><td><?= e($role) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="small muted">Password for every account above: <b>Reagan@2026</b></p>

      <ul class="steps mt-2">
        <li>Open the website and pick a program, or sign in to the client portal.</li>
        <li>Admins sign in at <b>admin/</b> · clients at <b>client/</b> (login page redirects automatically).</li>
        <li>When you have your real host credentials, set them in <b>config/config.php</b> (or the RSI_DB_* environment variables).</li>
        <li><b>Delete install.php from the server.</b></li>
      </ul>

      <div class="hero-actions mt-3" style="gap:10px">
        <a class="btn btn-primary" href="index.php">Open the website</a>
        <a class="btn btn-ghost" href="login.php">Go to sign in</a>
        <a class="btn btn-outline" href="admin/index.php">Admin portal</a>
      </div>

      <div class="danger-zone">
        <h4 style="color:var(--red)">Danger zone</h4>
        <p class="small muted">Reinstalling drops every table and reloads the demo data. Type the database name to confirm.</p>
        <form method="post" onsubmit="return confirm('This wipes all current data in <?= e(DB_NAME) ?>. Continue?');">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="reinstall">
          <div class="field">
            <label for="confirm_db">Database name</label>
            <input class="input" id="confirm_db" name="confirm_db" placeholder="<?= e(DB_NAME) ?>" autocomplete="off">
          </div>
          <button class="btn btn-danger" type="submit">Reinstall database</button>
        </form>
      </div>

    <?php else: ?>
      <form method="post" class="mt-2">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="install">
        <button class="btn btn-primary btn-block" type="submit">Create database &amp; import data</button>
      </form>
      <p class="small muted center mt-2 mb-0">Uses the settings from <b>config/config.php</b>. Change them first if your host provides different credentials.</p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
