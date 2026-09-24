<?php
/* Reagan Soft Innovation — one-off image optimisation CLI.
 * Converts heavy PNG/JPG hero art into sized, quality-managed WEBP.
 * Run:  php tools/img-opt.php   then remove tools/ when happy.
 */
declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED);

$IMG = 'C:/xampp/htdocs/reagansoft-innovation/assets/img/';

/* [filename, max width, webp quality] */
$jobs = [
    ['hero1.png', 1200, 82],
    ['hero2.png', 1200, 82],
    ['hero3.png', 1200, 82],
    ['hero4.png', 1200, 82],
    ['hero5.png', 1200, 82],
    ['hero6.png', 1200, 82],
    ['cover.png', 1200, 82],
];

function loadimg(string $src) {
    $info = @getimagesize($src);
    if (!$info) { return [null, 0, 0]; }
    $t = $info[2];
    if ($t === IMAGETYPE_PNG)       { $im = imagecreatefrompng($src); }
    elseif ($t === IMAGETYPE_JPEG)  { $im = imagecreatefromjpeg($src); }
    else { return [null, 0, 0]; }
    return [$im, $info[0], $info[1]];
}

foreach ($jobs as $job) {
    [$name, $maxW, $q] = $job;
    $src = $IMG . $name;
    if (!is_file($src)) { echo "skip (missing): $name\n"; continue; }
    [$im, $w, $h] = loadimg($src);
    if (!$im) { echo "skip (decode fail): $name\n"; continue; }

    if ($w > $maxW) {
        $nw = $maxW;
        $nh = (int)round($h * ($maxW / $w));
        $out = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($out, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($im);
        $im = $out;
        $w = $nw; $h = $nh;
    }

    $base = pathinfo($name, PATHINFO_FILENAME);
    $dest = $IMG . $base . '.webp';
    $ok = imagewebp($im, $dest, $q);
    imagedestroy($im);
    if (!$ok) { echo "ERROR writing webp: $name\n"; continue; }

    printf("%-14s %5d x %-6d %8.1f KB -> %8.1f KB  (-%d%%)\n",
        $name, $w, $h,
        filesize($src) / 1024,
        filesize($dest) / 1024,
        (int)round((1 - filesize($dest) / filesize($src)) * 100)
    );
}
echo "done\n";
