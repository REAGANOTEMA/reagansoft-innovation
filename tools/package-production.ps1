param(
    [string]$Output = (Join-Path $env:TEMP 'reagansoft-production-upload.zip'),
    [switch]$SkipBuild
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$stage = Join-Path $env:TEMP ('rsi-deploy-' + [guid]::NewGuid().ToString('N'))

if (-not $SkipBuild) {
    Write-Host 'Building React frontend...'
    Push-Location (Join-Path $root 'frontend')
    try {
        npm run build
        if ($LASTEXITCODE -ne 0) { throw 'Frontend build failed.' }
    }
    finally {
        Pop-Location
    }
}

if (-not (Test-Path -LiteralPath (Join-Path $root 'app\index.html'))) {
    throw 'app/index.html is missing. Run the build first.'
}

New-Item -ItemType Directory -Path $stage -Force | Out-Null

$directories = @('app', 'assets', 'api', 'inc', 'admin', 'client')
$files = @(
    '.htaccess',
    'spa.php',
    'sw.js',
    'robots.txt',
    'sitemap.xml',
    'README.md',
    'README.txt'
)

foreach ($dir in $directories) {
    $src = Join-Path $root $dir
    if (Test-Path -LiteralPath $src) {
        Copy-Item -LiteralPath $src -Destination $stage -Recurse -Force
    }
}

foreach ($file in $files) {
    $src = Join-Path $root $file
    if (Test-Path -LiteralPath $src) {
        Copy-Item -LiteralPath $src -Destination $stage -Force
    }
}

Get-ChildItem -LiteralPath $root -Filter '*.php' -File | ForEach-Object {
    Copy-Item -LiteralPath $_.FullName -Destination $stage -Force
}

$sqlDir = Join-Path $stage 'sql'
New-Item -ItemType Directory -Path $sqlDir -Force | Out-Null
$normalize = Join-Path $root 'sql\normalize-text.sql'
if (Test-Path -LiteralPath $normalize) {
    Copy-Item -LiteralPath $normalize -Destination $sqlDir -Force
}

$deployDir = Join-Path $stage 'deploy'
New-Item -ItemType Directory -Path $deployDir -Force | Out-Null
foreach ($file in @('nginx.conf.example', 'web.config', 'PRODUCTION-REPORT.md')) {
    $src = Join-Path $root ('deploy\' + $file)
    if (Test-Path -LiteralPath $src) {
        Copy-Item -LiteralPath $src -Destination $deployDir -Force
    }
}

$readme = @'
Reagansoft Innovation Limited - production upload package

Upload the CONTENTS of this folder to the website document root. Do not upload
the folder itself as a subdirectory. The document root currently serves the
legacy site and must also serve spa.php and the new app/ build.

Do NOT overwrite these production-only paths from this package:
- config/ (database credentials)
- storage/ (sessions, logs, private files)
- uploads/ (client files and images)

Required server settings
- PHP 8.1 or newer with pdo_mysql, mbstring, openssl and fileinfo.
- Apache/LiteSpeed with mod_rewrite and AllowOverride All, or use the
  equivalent Nginx/IIS configuration in deploy/.
- HTTPS enabled.

After upload
1. Keep a backup of the current live site and both databases.
2. Upload this package's contents.
3. Clear LiteSpeed/Cloudflare/browser caches.
4. Open / and confirm the React shell loads (not index.php).
5. Open /about, /services, /login, /portal and /console directly.
6. Confirm /sw.js is 200, /assets/img/site.webmanifest is
   application/manifest+json, and the install prompt appears over HTTPS.
7. Installers are blocked by .htaccess. Run install.php or create_admin.php
   from the server console only if you genuinely need them.

If deep links still show the legacy site, .htaccess is not being read. Enable
AllowOverride All for the document root and confirm mod_rewrite is active.
'@
Set-Content -LiteralPath (Join-Path $stage 'UPLOAD-README.txt') -Value $readme -Encoding ASCII

if (Test-Path -LiteralPath $Output) { Remove-Item -LiteralPath $Output -Force }
Compress-Archive -Path (Join-Path $stage '*') -DestinationPath $Output -CompressionLevel Optimal
Remove-Item -LiteralPath $stage -Recurse -Force

$size = [math]::Round((Get-Item -LiteralPath $Output).Length / 1MB, 2)
Write-Host "Created $Output ($size MB)"
