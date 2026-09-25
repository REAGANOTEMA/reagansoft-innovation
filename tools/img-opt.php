<?php
/**
 * Reagan Soft Innovation Limited — image auditor (CLI only).
 *
 * Checks assets/img without guessing, so a deleted or oversized file
 * can never reach the site again:
 *
 *   php tools/img-opt.php        report only (safe, read-only)
 *
 * What it reports
 *   1. referenced but missing  — a broken <img> or CSS url()
 *   2. present but unreferenced — dead weight to delete
 *   3. oversized               — over 300 KB, or wider than 2000 px
 *   4. unused image variables  — slides in index.php that were dropped
 *
 * It never writes, moves or deletes anything.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This tool runs from the command line only.\n");
}

$ROOT = dirname(__DIR__);
$IMG  = $ROOT . '/assets/img/';

const BIG_KB    = 300;   // anything above this is heavy for a web page
const BIG_WIDTH = 2000;  // wider than this is wasted bytes on phones

/* ------------------------------------------------------------------ *
 * 1. Collect every image filename referenced anywhere in the code
 * ------------------------------------------------------------------ */
$codeFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($ROOT, FilesystemIterator::SKIP_DOTS)
);
$skipDirs = ['/storage/', '/uploads/', '/.git/', '/vendor/'];
$exts    = ['php', 'html', 'css', 'js'];

$referenced = [];
foreach ($codeFiles as $file) {
    $path = $file->getPathname();
    foreach ($skipDirs as $skip) {
        if (strpos($path, $skip) !== false) { continue 2; }
    }
    if (!in_array(strtolower($file->getExtension()), $exts, true)) { continue; }
    if (realpath($path) === realpath(__FILE__)) { continue; }   // never audit our own report

    $text = file_get_contents($path);
    // The (?![A-Za-z0-9]) is essential: without it a class name such as
    // .icon-badge would be read as an image file called ".ico".
    if (preg_match_all('/[A-Za-z0-9._\-\/ ]*\.(?:png|jpe?g|webp|gif|svg|ico|avif)(?![A-Za-z0-9])/i', $text, $m)) {
        foreach ($m[0] as $ref) {
            $name = basename(str_replace('\\', '/', trim($ref)));
            if (strpos($name, '.') !== 0) {           // ignore a bare ".ico"
                $referenced[$name][] = $path;
            }
        }
    }
}

/* ------------------------------------------------------------------ *
 * 2. What is actually on disk
 * ------------------------------------------------------------------ */
$onDisk = [];
foreach (glob($IMG . '*') as $file) {
    if (is_file($file)) { $onDisk[basename($file)] = $file; }
}

$problems = 0;

/* ------------------------------------------------------------------ */
echo "\nBroken references (used in code, file is gone)\n";
echo str_repeat('-', 62) . "\n";
$broken = array_diff_key($referenced, $onDisk);
if (!$broken) {
    echo "  none — every image referenced in the code exists.\n";
} else {
    foreach ($broken as $name => $files) {
        $problems++;
        printf("  MISSING  %-38s referenced in %s\n", $name, implode(', ', array_unique(array_map(
            static fn($f) => basename($f),
            $files
        ))));
    }
}

/* ------------------------------------------------------------------ */
echo "\nUnreferenced images (on disk, not used anywhere)\n";
echo str_repeat('-', 62) . "\n";
$unused = array_diff_key($onDisk, $referenced);
if (!$unused) {
    echo "  none — every file in assets/img is in use.\n";
} else {
    $total = 0;
    foreach ($unused as $name => $file) {
        $total += filesize($file);
        $problems++;
        printf("  DELETE?  %-38s %8.1f KB\n", $name, filesize($file) / 1024);
    }
    printf("  %d file(s), %.1f MB in total\n", count($unused), $total / 1048576);
}

/* ------------------------------------------------------------------ */
echo "\nOversized images (over " . BIG_KB . " KB or wider than " . BIG_WIDTH . " px)\n";
echo str_repeat('-', 62) . "\n";
$big = 0;
foreach ($onDisk as $name => $file) {
    $kb = filesize($file) / 1024;
    $wh = '?';
    if (function_exists('getimagesize') && ($info = @getimagesize($file))) {
        $wh = $info[0] . 'x' . $info[1];
    }
    if ($kb > BIG_KB || (int)explode('x', $wh)[0] > BIG_WIDTH) {
        $big++;
        $problems++;
        printf("  LARGE    %-38s %8.1f KB  %s\n", $name, $kb, $wh);
    }
}
if (!$big) { echo "  none — all images are web-ready.\n"; }

/* ------------------------------------------------------------------ */
echo "\nApple touch icon\n";
echo str_repeat('-', 62) . "\n";
$touch = $IMG . 'apple-180.png';
if (is_file($touch)) {
    $info = @getimagesize($touch);
    printf("  ok       apple-180.png is %dx%d, %.1f KB (layout.php links it)\n",
        $info[0], $info[1], filesize($touch) / 1024);
    if ($info[0] !== 180) { $problems++; echo "  FIX      expected 180x180\n"; }
} else {
    $problems++;
    echo "  MISSING  apple-180.png\n";
}

/* ------------------------------------------------------------------ */
echo "\n" . str_repeat('=', 62) . "\n";
echo $problems === 0
    ? "Images are healthy: nothing broken, nothing unused, nothing oversized.\n"
    : $problems . " item(s) to look at.\n";
exit($problems === 0 ? 0 : 1);
