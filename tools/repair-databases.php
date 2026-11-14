<?php
/**
 * Reagansoft Innovation Limited - activity_logs schema repair (CLI only).
 *
 * Why this exists
 * ---------------
 * The two databases (reagansoft_system and reagansoft_website) each carry an
 * activity_logs table. Older phpMyAdmin dumps split the table into a CREATE
 * TABLE statement followed by separate ALTER TABLE statements for the primary
 * key, indexes, AUTO_INCREMENT and the users foreign key. When such an import
 * stops halfway (or when both dumps are imported into one database), the table
 * can be left without a primary key or AUTO_INCREMENT. The application then
 * fails on INSERT with "Field 'id' doesn't have a default value".
 *
 * This tool inspects both databases, compares activity_logs with the expected
 * structure and applies only the missing pieces. It never drops the table and
 * never deletes a row.
 *
 *   php tools/repair-databases.php
 *   php tools/repair-databases.php --dry-run
 *
 * Run it with an account that can ALTER TABLE. The normal application accounts
 * can do this on their own databases (tools/setup-databases.php grants full
 * access). If they cannot, set RSI_DB_USER / RSI_DB_PASS for the duration of
 * the command.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

require __DIR__ . '/../config/config.php';

$dryRun = in_array('--dry-run', $argv, true);

$targets = [
    ['label' => 'reagansoft_system', 'db' => DB_NAME, 'user' => DB_USER, 'pass' => DB_PASS],
];
if (DB_MIRROR !== '' && DB_MIRROR !== DB_NAME) {
    $targets[] = ['label' => DB_MIRROR, 'db' => DB_MIRROR, 'user' => DB_MIRROR_USER, 'pass' => DB_MIRROR_PASS];
}

function say(string $level, string $message): void
{
    $marks = ['ok' => '[ ok ]', 'fix' => '[fix ]', 'warn' => '[warn]', 'fail' => '[FAIL]', 'info' => '[info]'];
    echo str_pad($marks[$level] ?? '[info]', 7) . $message . "\n";
}

function connect(string $db, string $user, string $pass): PDO
{
    return new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . $db . ';charset=utf8mb4',
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}

function table_exists(PDO $pdo, string $db, string $table): bool
{
    $st = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $st->execute([$db, $table]);
    return (int)$st->fetchColumn() > 0;
}

function columns(PDO $pdo, string $table): array
{
    $out = [];
    foreach ($pdo->query('SHOW COLUMNS FROM `' . $table . '`') as $row) {
        $out[$row['Field']] = $row;
    }
    return $out;
}

function indexes(PDO $pdo, string $table): array
{
    $out = [];
    foreach ($pdo->query('SHOW INDEX FROM `' . $table . '`') as $row) {
        $out[$row['Key_name']] = true;
    }
    return $out;
}

function scalar(PDO $pdo, string $sql): int
{
    return (int)$pdo->query($sql)->fetchColumn();
}

$expectedColumns = [
    'id'          => '`id` int(10) UNSIGNED NOT NULL',
    'user_id'     => '`user_id` int(10) UNSIGNED DEFAULT NULL',
    'action'      => "`action` varchar(120) NOT NULL",
    'entity_type' => '`entity_type` varchar(60) DEFAULT NULL',
    'entity_id'   => '`entity_id` int(10) UNSIGNED DEFAULT NULL',
    'details'     => '`details` text DEFAULT NULL',
    'ip_address'  => '`ip_address` varchar(45) DEFAULT NULL',
    'created_at'  => '`created_at` timestamp NULL DEFAULT current_timestamp()',
];

$expectedIndexes = [
    'idx_logs_entity'  => '(`entity_type`,`entity_id`)',
    'idx_logs_user'    => '(`user_id`)',
    'idx_logs_created' => '(`created_at`)',
    'idx_logs_action'  => '(`action`)',
];

$report = [];

foreach ($targets as $target) {
    $label = $target['label'];
    $db = $target['db'];
    $report[$label] = [];
    echo "\nDatabase: {$label}\n" . str_repeat('-', 42) . "\n";

    try {
        $pdo = connect($db, $target['user'], $target['pass']);
    } catch (Throwable $e) {
        say('fail', 'Connection failed: ' . $e->getMessage());
        $report[$label][] = 'connection failed';
        continue;
    }

    try {
        $exists = table_exists($pdo, $db, 'activity_logs');

        if (!$exists) {
            say('fix', 'activity_logs is missing; creating it with the full structure.');
            if (!$dryRun) {
                $pdo->exec(
                    'CREATE TABLE `activity_logs` ('
                    . '`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,'
                    . '`user_id` int(10) UNSIGNED DEFAULT NULL,'
                    . '`action` varchar(120) NOT NULL,'
                    . '`entity_type` varchar(60) DEFAULT NULL,'
                    . '`entity_id` int(10) UNSIGNED DEFAULT NULL,'
                    . '`details` text DEFAULT NULL,'
                    . '`ip_address` varchar(45) DEFAULT NULL,'
                    . '`created_at` timestamp NULL DEFAULT current_timestamp(),'
                    . 'PRIMARY KEY (`id`),'
                    . 'KEY `idx_logs_entity` (`entity_type`,`entity_id`),'
                    . 'KEY `idx_logs_user` (`user_id`),'
                    . 'KEY `idx_logs_created` (`created_at`),'
                    . 'KEY `idx_logs_action` (`action`)'
                    . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                );
            }
            $report[$label][] = 'created table';
        } else {
            say('ok', 'activity_logs exists; checking columns.');
        }

        $cols = columns($pdo, 'activity_logs');
        foreach ($expectedColumns as $name => $definition) {
            if (!isset($cols[$name])) {
                say('fix', "Adding missing column {$name}.");
                if (!$dryRun) {
                    $pdo->exec('ALTER TABLE `activity_logs` ADD COLUMN ' . $definition);
                }
                $report[$label][] = "added column {$name}";
            }
        }
        $cols = columns($pdo, 'activity_logs');

        $idx = indexes($pdo, 'activity_logs');
        $hasPrimary = isset($idx['PRIMARY']);

        if (!$hasPrimary) {
            $nulls = scalar($pdo, 'SELECT COUNT(*) FROM `activity_logs` WHERE `id` IS NULL');
            $dupes = scalar($pdo, 'SELECT COUNT(*) FROM (SELECT `id` FROM `activity_logs` GROUP BY `id` HAVING COUNT(*) > 1) d');
            if ($nulls > 0 || $dupes > 0) {
                say('warn', "Cannot add the primary key safely: {$nulls} NULL id(s), {$dupes} duplicate id group(s).");
                $report[$label][] = 'primary key not added (data conflict)';
            } else {
                say('fix', 'Adding the missing PRIMARY KEY on id.');
                if (!$dryRun) {
                    $pdo->exec('ALTER TABLE `activity_logs` MODIFY `id` int(10) UNSIGNED NOT NULL');
                    $pdo->exec('ALTER TABLE `activity_logs` ADD PRIMARY KEY (`id`)');
                }
                $report[$label][] = 'added primary key';
                $hasPrimary = true;
            }
        } else {
            say('ok', 'PRIMARY KEY on id is present.');
        }

        $cols = columns($pdo, 'activity_logs');
        $auto = stripos((string)($cols['id']['Extra'] ?? ''), 'auto_increment') !== false;
        if (!$auto && $hasPrimary) {
            say('fix', 'Enabling AUTO_INCREMENT on id.');
            if (!$dryRun) {
                $pdo->exec('ALTER TABLE `activity_logs` MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT');
            }
            $report[$label][] = 'enabled AUTO_INCREMENT';
        } elseif (!$auto) {
            say('warn', 'AUTO_INCREMENT left unchanged because there is no primary key yet.');
            $report[$label][] = 'AUTO_INCREMENT not enabled';
        } else {
            say('ok', 'AUTO_INCREMENT is enabled.');
        }

        $idx = indexes($pdo, 'activity_logs');
        foreach ($expectedIndexes as $name => $definition) {
            if (!isset($idx[$name])) {
                say('fix', "Adding missing index {$name}.");
                if (!$dryRun) {
                    $pdo->exec('ALTER TABLE `activity_logs` ADD KEY `' . $name . '` ' . $definition);
                }
                $report[$label][] = "added index {$name}";
            }
        }

        $fk = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = 'activity_logs' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
        );
        $fk->execute([$db]);
        $hasFk = (int)$fk->fetchColumn() > 0;
        $usersExists = table_exists($pdo, $db, 'users');

        if (!$hasFk && $usersExists) {
            $orphans = scalar(
                $pdo,
                'SELECT COUNT(*) FROM `activity_logs` a LEFT JOIN `users` u ON u.id = a.user_id
                 WHERE a.user_id IS NOT NULL AND u.id IS NULL'
            );
            if ($orphans > 0) {
                say('warn', "Foreign key not added: {$orphans} activity row(s) point to missing users.");
                $report[$label][] = 'foreign key not added (orphan rows)';
            } else {
                say('fix', 'Adding the users foreign key.');
                if (!$dryRun) {
                    $pdo->exec(
                        'ALTER TABLE `activity_logs` ADD CONSTRAINT `activity_logs_ibfk_1`
                         FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL'
                    );
                }
                $report[$label][] = 'added users foreign key';
            }
        } elseif ($hasFk) {
            say('ok', 'Users foreign key is present.');
        } else {
            say('warn', 'users table is missing, so the foreign key was skipped.');
            $report[$label][] = 'foreign key skipped (users table missing)';
        }

        /* Live INSERT / SELECT test, rolled back so no test row is left behind. */
        if (!$dryRun) {
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare(
                    'INSERT INTO `activity_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`)
                     VALUES (NULL, ?, ?, ?, ?, ?)'
                );
                $ins->execute(['schema_repair_check', 'repair', 0, 'rolled back after verification', '127.0.0.1']);
                $id = (int)$pdo->lastInsertId();
                $sel = $pdo->prepare('SELECT `action` FROM `activity_logs` WHERE `id` = ?');
                $sel->execute([$id]);
                $ok = $sel->fetchColumn() === 'schema_repair_check';
                $pdo->rollBack();
                if ($ok && $id > 0) {
                    say('ok', "INSERT and SELECT verified (temporary id {$id}, rolled back).");
                    $report[$label][] = 'insert/select verified';
                } else {
                    say('fail', 'INSERT succeeded but the row could not be read back.');
                    $report[$label][] = 'insert/select verification failed';
                }
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                say('fail', 'INSERT/SELECT test failed: ' . $e->getMessage());
                $report[$label][] = 'insert/select test failed';
            }
        }
    } catch (Throwable $e) {
        say('fail', 'Repair failed: ' . $e->getMessage());
        $report[$label][] = 'repair failed: ' . $e->getMessage();
    }
}

echo "\nRepair summary\n" . str_repeat('=', 42) . "\n";
foreach ($report as $label => $items) {
    echo $label . ': ' . ($items ? implode('; ', $items) : 'no changes needed') . "\n";
}
if ($dryRun) {
    echo "\nDry run only. Re-run without --dry-run to apply the changes.\n";
}
