param(
    [string]$Output = (Join-Path $env:TEMP 'opencode\reagansoft-all-access-card.png')
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing

$root = Split-Path -Parent $PSScriptRoot
$iconPath = Join-Path $root 'assets\img\reagansoftinnovation-logo-new-white.png'
if (-not (Test-Path -LiteralPath $iconPath)) { throw "Logo not found: $iconPath" }

$W = 1080
$H = 2280
$bmp = New-Object System.Drawing.Bitmap($W, $H, [System.Drawing.Imaging.PixelFormat]::Format32bppArgb)
$g = [System.Drawing.Graphics]::FromImage($bmp)
$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
$g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
$g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
$g.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

function C($hex, $alpha = 255) {
    $hex = $hex.TrimStart('#')
    [System.Drawing.Color]::FromArgb($alpha,
        [Convert]::ToInt32($hex.Substring(0,2),16),
        [Convert]::ToInt32($hex.Substring(2,2),16),
        [Convert]::ToInt32($hex.Substring(4,2),16))
}
function RoundPath($x, $y, $w, $h, $r) {
    $p = New-Object System.Drawing.Drawing2D.GraphicsPath
    $d = $r * 2
    $p.AddArc($x, $y, $d, $d, 180, 90)
    $p.AddArc($x + $w - $d, $y, $d, $d, 270, 90)
    $p.AddArc($x + $w - $d, $y + $h - $d, $d, $d, 0, 90)
    $p.AddArc($x, $y + $h - $d, $d, $d, 90, 90)
    $p.CloseFigure()
    return $p
}
function FillRound($x, $y, $w, $h, $r, $color) {
    $p = RoundPath $x $y $w $h $r
    $b = New-Object System.Drawing.SolidBrush($color)
    $g.FillPath($b, $p)
    $b.Dispose(); $p.Dispose()
}
function StrokeRound($x, $y, $w, $h, $r, $color, $width = 1) {
    $p = RoundPath $x $y $w $h $r
    $pen = New-Object System.Drawing.Pen($color, $width)
    $g.DrawPath($pen, $p)
    $pen.Dispose(); $p.Dispose()
}
function FitFont($text, $family, $size, $style, $maxWidth, $minSize = 10) {
    do {
        $font = New-Object System.Drawing.Font($family, [float]$size, $style, [System.Drawing.GraphicsUnit]::Pixel)
        $m = $g.MeasureString($text, $font)
        if ($m.Width -le $maxWidth -or $size -le $minSize) { return $font }
        $font.Dispose(); $size -= 0.5
    } while ($true)
}
function Text($text, $family, $size, $style, $color, $x, $y, $maxWidth = 0, $maxHeight = 0, $align = 'Near') {
    $font = if ($maxWidth -gt 0) { FitFont $text $family $size $style $maxWidth } else {
        New-Object System.Drawing.Font($family, [float]$size, $style, [System.Drawing.GraphicsUnit]::Pixel)
    }
    $brush = New-Object System.Drawing.SolidBrush($color)
    $sf = New-Object System.Drawing.StringFormat
    $sf.Alignment = [System.Drawing.StringAlignment]::$align
    $sf.LineAlignment = [System.Drawing.StringAlignment]::Near
    $sf.Trimming = [System.Drawing.StringTrimming]::EllipsisCharacter
    $sf.FormatFlags = [System.Drawing.StringFormatFlags]::NoWrap
    if ($maxWidth -le 0) { $maxWidth = 2000 }
    if ($maxHeight -le 0) { $maxHeight = 200 }
    $g.DrawString($text, $font, $brush, [System.Drawing.RectangleF]::new([float]$x, [float]$y, [float]$maxWidth, [float]$maxHeight), $sf)
    $sf.Dispose(); $brush.Dispose(); $font.Dispose()
}
function Line($x1, $y1, $x2, $y2, $color, $width = 1) {
    $pen = New-Object System.Drawing.Pen($color, $width)
    $g.DrawLine($pen, $x1, $y1, $x2, $y2)
    $pen.Dispose()
}

$navy = C '#062a4a'; $navy2 = C '#0a4a78'; $blue = C '#0a7bce'; $blueLight = C '#7fd0ff'
$ink = C '#13283c'; $muted = C '#5c7589'; $line = C '#dbe9f3'; $soft = C '#f5fafd'; $white = C '#ffffff'

$bgRect = [System.Drawing.Rectangle]::new(0, 0, $W, $H)
$bg = New-Object System.Drawing.Drawing2D.LinearGradientBrush($bgRect, $navy, $navy2, 90)
$g.FillRectangle($bg, $bgRect); $bg.Dispose()
for ($x = 0; $x -lt $W; $x += 60) { Line $x 0 $x $H (C '#ffffff' 10) }
for ($y = 0; $y -lt $H; $y += 60) { Line 0 $y $W $y (C '#ffffff' 10) }
$glow = New-Object System.Drawing.SolidBrush((C '#0a7bce' 28))
$g.FillEllipse($glow, -180, -220, 620, 620); $g.FillEllipse($glow, 760, 120, 460, 460); $glow.Dispose()

$icon = [System.Drawing.Image]::FromFile($iconPath)
$g.DrawImage($icon, 64, 60, 92, 91); $icon.Dispose()

Text 'REAGANSOFT' 'Segoe UI' 40 ([System.Drawing.FontStyle]::Bold) $white 178 66 500 60
Text 'I N N O V A T I O N   L I M I T E D' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Bold) $blueLight 180 116 500 30

FillRound 786 70 230 44 22 (C '#7f1d1d' 170)
StrokeRound 786 70 230 44 22 (C '#fca5a5' 150) 1
Text 'PRIVATE  •  OWNER ONLY' 'Segoe UI' 12 ([System.Drawing.FontStyle]::Bold) $white 786 82 230 20 ([System.Drawing.StringAlignment]::Center)

Text 'ALL ACCESS CARD' 'Segoe UI' 54 ([System.Drawing.FontStyle]::Bold) $white 64 205 900 70
Text 'Portals  •  team accounts  •  database access' 'Segoe UI' 24 ([System.Drawing.FontStyle]::Regular) (C '#cfe0ef') 66 282 900 40

FillRound 64 340 560 38 19 (C '#ffffff' 18)
Text 'Prepared for Reagan Otema' 'Segoe UI' 19 ([System.Drawing.FontStyle]::Bold) $white 82 349 530 26
Text 'Reagansoft Innovation Limited  •  Jinja, Uganda' 'Segoe UI' 16 ([System.Drawing.FontStyle]::Regular) (C '#9fc4df') 66 390 700 28

FillRound 64 430 952 1730 34 (C '#001a2e' 110)
FillRound 56 420 968 1730 34 $white
StrokeRound 56 420 968 1730 34 (C '#d7e7f2') 1

$left = 96
$contentW = 888

Text 'SIGN-IN DESTINATIONS' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Bold) $blue $left 450 400 24
$boxW = 424; $boxH = 92; $gap = 40; $y1 = 486; $y2 = 594
function PortalBox($x, $y, $label, $value) {
    FillRound $x $y $boxW $boxH 14 $soft
    StrokeRound $x $y $boxW $boxH 14 $line 1
    Text $label 'Segoe UI' 12 ([System.Drawing.FontStyle]::Bold) $blue ($x + 20) ($y + 14) ($boxW - 40) 20
    Text $value 'Consolas' 17 ([System.Drawing.FontStyle]::Regular) $ink ($x + 20) ($y + 42) ($boxW - 40) 30
}
PortalBox $left $y1 'WEBSITE' 'reagansoftinnovation.com'
PortalBox ($left + $boxW + $gap) $y1 'SIGN IN  •  WORKS NOW' 'reagansoftinnovation.com/login.php'
PortalBox $left $y2 'ADMIN PORTAL' 'reagansoftinnovation.com/admin/'
PortalBox ($left + $boxW + $gap) $y2 'CLIENT PORTAL' 'reagansoftinnovation.com/client/'
Text 'New app links /login, /console and /portal use the same accounts after the pending SPA upload.' 'Segoe UI' 14 ([System.Drawing.FontStyle]::Italic) $muted $left 700 $contentW 24

Line $left 744 ($left + $contentW) 744 $line 1

Text 'TEAM ACCOUNTS' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Bold) $blue $left 768 400 24
FillRound 858 765 112 28 14 (C '#dcfce7')
Text '8  ACTIVE' 'Segoe UI' 12 ([System.Drawing.FontStyle]::Bold) (C '#15803d') 858 772 112 18 ([System.Drawing.StringAlignment]::Center)

$accounts = @(
    @{ Role='ADMIN';  Name='Reagan Otema'; Email='admin@reagansoft.com' },
    @{ Role='STAFF';  Name='Sarah Namukasa'; Email='staff@reagansoft.com' },
    @{ Role='CLIENT'; Name='Amara Kaggwa'; Email='amara@kirekafarms.com' },
    @{ Role='CLIENT'; Name='Grace Ayebare'; Email='grace@pearlholdings.com' },
    @{ Role='CLIENT'; Name='David Mulumba'; Email='david@mulumbasons.com' },
    @{ Role='CLIENT'; Name='Agnes Nakato'; Email='agnesnakato@gmail.com' },
    @{ Role='CLIENT'; Name='Hotel Paradise on the Nile'; Email='info@hotelparadiseonthenile.co.ug' },
    @{ Role='CLIENT'; Name='Iganga School of Nursing and Midwifery'; Email='info@igangaschoolofnursingandmidwifery.ac.ug' }
)
$rowY = 808; $rowH = 78
for ($i = 0; $i -lt $accounts.Count; $i++) {
    $a = $accounts[$i]; $y = $rowY + ($i * $rowH)
    $fill = if ($i % 2 -eq 0) { $white } else { $soft }
    FillRound $left $y $contentW ($rowH - 6) 12 $fill
    StrokeRound $left $y $contentW ($rowH - 6) 12 (C '#e6f0f7') 1
    $badgeColor = switch ($a.Role) { 'ADMIN' { C '#0a7bce' } 'STAFF' { C '#0f766e' } default { C '#475569' } }
    $badgeW = if ($a.Role -eq 'CLIENT') { 76 } else { 70 }
    FillRound ($left + 16) ($y + 21) $badgeW 28 14 $badgeColor
    Text $a.Role 'Segoe UI' 11 ([System.Drawing.FontStyle]::Bold) $white ($left + 16) ($y + 28) $badgeW 18 ([System.Drawing.StringAlignment]::Center)
    Text $a.Name 'Segoe UI' 18 ([System.Drawing.FontStyle]::Bold) $ink ($left + 108) ($y + 11) 550 26
    Text $a.Email 'Consolas' 15 ([System.Drawing.FontStyle]::Regular) $muted ($left + 108) ($y + 40) 660 22
    $dotBrush = New-Object System.Drawing.SolidBrush((C '#22c55e'))
    $g.FillEllipse($dotBrush, ($left + $contentW - 40), ($y + 30), 10, 10); $dotBrush.Dispose()
    Text 'ACTIVE' 'Segoe UI' 10 ([System.Drawing.FontStyle]::Bold) (C '#15803d') ($left + $contentW - 96) ($y + 27) 52 18
}

Line $left 1452 ($left + $contentW) 1452 $line 1

Text 'DATABASE ACCESS' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Bold) $blue $left 1476 400 24
Text 'LOCAL / CURRENT CONFIG' 'Segoe UI' 12 ([System.Drawing.FontStyle]::Bold) $muted $left 1476 $contentW 20 ([System.Drawing.StringAlignment]::Far)

function DbBox($x, $y, $label, $hostValue, $dbValue, $passValue) {
    FillRound $x $y $contentW 108 14 (C '#f8fbfe')
    StrokeRound $x $y $contentW 108 14 $line 1
    Text $label 'Segoe UI' 12 ([System.Drawing.FontStyle]::Bold) $blue ($x + 20) ($y + 12) 500 20
    Text 'HOST' 'Segoe UI' 10 ([System.Drawing.FontStyle]::Bold) $muted ($x + 20) ($y + 40) 100 16
    Text $hostValue 'Consolas' 16 ([System.Drawing.FontStyle]::Regular) $ink ($x + 20) ($y + 59) 180 24
    Text 'DB / USER' 'Segoe UI' 10 ([System.Drawing.FontStyle]::Bold) $muted ($x + 214) ($y + 40) 160 16
    Text $dbValue 'Consolas' 16 ([System.Drawing.FontStyle]::Regular) $ink ($x + 214) ($y + 59) 310 24
    Text 'PASSWORD' 'Segoe UI' 10 ([System.Drawing.FontStyle]::Bold) $muted ($x + 548) ($y + 40) 160 16
    Text $passValue 'Consolas' 16 ([System.Drawing.FontStyle]::Bold) (C '#9a3412') ($x + 548) ($y + 59) 320 24
}

DbBox $left 1512 'APPLICATION DATABASE  •  reagansoft_system' 'localhost' 'reagansoft_system' 'f.o9Wv2dP5Tmv]?+'
DbBox $left 1632 'MIRROR DATABASE  •  reagansoft_website' 'localhost' 'reagansoft_website' 'dORp*y_e[2@&JjD}'

Text 'phpMyAdmin: http://localhost/phpmyadmin  •  production: cPanel > phpMyAdmin' 'Segoe UI' 13 ([System.Drawing.FontStyle]::Regular) $muted $left 1756 $contentW 22
Text 'Production database credentials may be set with RSI_DB_* environment variables.' 'Segoe UI' 13 ([System.Drawing.FontStyle]::Regular) $muted $left 1782 $contentW 22
Text 'Hosting/cPanel and SMTP credentials are not stored in this project. Keep them in a password manager.' 'Segoe UI' 13 ([System.Drawing.FontStyle]::Bold) (C '#9a3412') $left 1808 $contentW 22

$passY = 1846
FillRound $left $passY $contentW 138 18 $navy
StrokeRound $left $passY $contentW 138 18 (C '#0a5da0') 1
Text 'SHARED APP PASSWORD' 'Segoe UI' 13 ([System.Drawing.FontStyle]::Bold) $blueLight ($left + 28) ($passY + 20) 500 22
Text 'Reagan@2026' 'Consolas' 44 ([System.Drawing.FontStyle]::Bold) $white ($left + 28) ($passY + 48) 560 60
Text 'Change this password' 'Segoe UI' 14 ([System.Drawing.FontStyle]::Bold) (C '#cfe0ef') ($left + 640) ($passY + 46) 220 22
Text 'after first sign-in.' 'Segoe UI' 14 ([System.Drawing.FontStyle]::Regular) (C '#9fc4df') ($left + 640) ($passY + 68) 220 22

FillRound $left 2010 $contentW 74 14 (C '#fff7ed')
StrokeRound $left 2010 $contentW 74 14 (C '#fed7aa') 1
Text 'PRIVATE. Do not post publicly. This card contains database passwords.' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Bold) (C '#9a3412') ($left + 18) ($passY + 190) ($contentW - 36) 24
Text 'Store it in a password manager and rotate the database passwords if it is shared.' 'Segoe UI' 13 ([System.Drawing.FontStyle]::Regular) (C '#9a3412') ($left + 18) ($passY + 214) ($contentW - 36) 22

Text 'Prepared by Reagan Otema  •  +256 730 314 979' 'Segoe UI' 18 ([System.Drawing.FontStyle]::Bold) $white 64 2192 900 28
Text 'Reagansoft Innovation Limited  •  reagansoftinnovation.com' 'Segoe UI' 15 ([System.Drawing.FontStyle]::Regular) (C '#9fc4df') 64 2228 900 24

$dir = Split-Path -Parent $Output
if ($dir -and -not (Test-Path -LiteralPath $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
$bmp.Save($Output, [System.Drawing.Imaging.ImageFormat]::Png)
$g.Dispose(); $bmp.Dispose()
$size = [math]::Round((Get-Item -LiteralPath $Output).Length / 1KB, 1)
Write-Host "Created $Output (${W}x${H}, $size KB)"
