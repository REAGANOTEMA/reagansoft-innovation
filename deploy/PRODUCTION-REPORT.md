# Reagansoft Innovation Limited - Production Repair Report

Date: 2026-11-14
Local project: `C:\xampp\htdocs\reagansoft-innovation`
Production host: `https://reagansoftinnovation.com`
Production stack observed: LiteSpeed, PHP 8.1.34, MySQL API reachable

## 1. Executive summary

The production site is not showing the corrected build. The live root still serves the legacy PHP site, the React application at `/app/` is an older build, and the root `.htaccess`/`spa.php` routing is not active. The corrected code, rebuilt SPA, transparent logos, PWA files, and database repairs exist locally and pass local checks.

This is primarily a deployment and server-routing failure, not a styling failure. Production cannot be fixed by uploading only the new CSS or logo. The root `.htaccess`, `spa.php`, the new `app/` build, `sw.js`, and the rebuilt assets must all be uploaded together.

Production has not been modified by this work. No server credentials were available.

## 2. Production audit evidence

Observed with live HTTP requests before changes:

| Request | Result | Meaning |
|---|---|---|
| `GET /` | `200`, legacy PHP HTML | Root is still served by the old `index.php` |
| `GET /about` | `404` | SPA deep-link routing is not active |
| `GET /services` | `404` | SPA deep-link routing is not active |
| `GET /portal` | `404` | Client portal route is not routed |
| `GET /app/` | `200`, old build | An older React build is deployed |
| `GET /sw.js` | `404` | Service worker is missing |
| `GET /assets/img/site.webmanifest` | `200`, `application/octet-stream` | Wrong MIME type |
| `GET /assets/img/reagansoftinnovation-logo-new.png` | `404` | New logo not deployed |
| `GET /assets/img/real-reagansoft-logo.png` | `404` | Footer logo not deployed |
| `GET /assets/img/og-image.jpg` | `404` | Social image not deployed |
| `GET /api/v1/public/site` | `200`, JSON | Backend API is alive |
| `GET /api/v1/public/services` | `200`, JSON | Services API is alive |

The live `/app/` page references old hashed assets such as `index-CgLxH4cZ.js`, `icons-CdJlgP23.js`, and `index-DkLarQMZ.css`. It does not contain the `rsi-base`, `rsi-api-base`, or `rsi-asset-base` meta tags injected by `spa.php`, so images and API calls resolve against the wrong base path.

## 3. Root cause

1. The production document root does not read the local `.htaccess`, so `/about`, `/services`, `/portal`, and `/console` never reach `spa.php`.
2. The deployed `app/` folder is stale and was built for a different base path.
3. The live root still serves `index.php`, so visitors see the legacy PHP design.
4. `sw.js`, the transparent logo files, the OG image, and the manifest MIME rule were never uploaded.
5. The live manifest is served as `application/octet-stream`, which prevents reliable PWA installation.

Local Apache has `mod_rewrite` and the `.htaccess` rules active, which is why the same routes return `200` locally.

## 4. Routing and SPA fixes

- Root `.htaccess` routes real files and directories directly, and sends all other requests to `spa.php`.
- `spa.php` serves `app/index.html` and injects:
  - `<base href="/app/">` (or the subfolder equivalent)
  - `rsi-base`, `rsi-api-base`, and `rsi-asset-base` meta tags
  - favicon links, manifest link, Apple PWA meta, and social preview tags
- API paths remain under `/api/v1`; `/api` and `/assets` are excluded from SPA routing.
- `spa.php` now emits absolute `og:url`, `og:image`, and `twitter:image` URLs using the request host and HTTPS/proxy headers.
- `spa.php` sends `no-cache` for the shell so new asset hashes are picked up after deployment.
- Service worker version bumped from `rsi-v3` to `rsi-v4` to force an update after upload.

Local verification:

- `/`, `/about`, `/services`, `/services/business-website`, `/portfolio`, `/pricing`, `/process`, `/faq`, `/contact`, `/login`, `/register`, `/portal`, and `/console` all return `200`.
- `/robots.txt`, `/sitemap.xml`, `/sw.js`, `/assets/img/site.webmanifest`, and the logo/OG assets all return `200`.
- `/install.php` and `/create_admin.php` return `403` by design.

## 5. Logo and visual asset fixes

Header rule:

- Use `assets/img/reagansoftinnovation-logo-new.png`.
- Transparent PNG, no white circle, no rounded card, no ring, no box shadow.
- Brand text remains beside the mark.
- App/sidebar uses `reagansoftinnovation-logo-new-white.png` on dark backgrounds.

Footer/founder rule:

- Use `assets/img/real-reagansoft-logo.png` on light surfaces.
- Use `assets/img/real-reagansoft-logo-white.png` on dark surfaces.
- Footer mark is larger (up to `104px` high) but no longer wrapped in a circular container.

Generated assets:

| Asset | Size |
|---|---|
| `reagansoftinnovation-logo-new.png` | `160x158`, about `47 KB` |
| `reagansoftinnovation-logo-new-white.png` | `160x158`, about `3 KB` |
| `real-reagansoft-logo.png` | `300x207`, about `81 KB` |
| `real-reagansoft-logo-white.png` | `300x207`, about `26 KB` |
| `og-image.jpg` | `1200x630`, about `62 KB` |
| `favicon-16.png` / `favicon-32.png` | correct sizes |
| `icon-192.png` / `icon-512.png` | PWA icons |
| `icon-maskable-192.png` / `icon-maskable-512.png` | maskable PWA icons |

The large original transparent PNGs were reduced for web delivery. Full-size originals are backed up at:

`C:\Users\REAGAN\AppData\Local\Temp\rsi-logo-originals`

Important: `real-reagansoft-logo.png` has a transparent centre (centre alpha `0`). This was verified by pixel inspection. It may be intentional because the badge artwork is open, but it has not been visually approved on a real display. If the centre should be solid, regenerate it from the original image with a closed white fill or a different background-removal tolerance.

## 6. PHP and text cleanup

- Removed UTF-8 BOMs from PHP files.
- Fixed double-encoded characters such as `Â·`, `Â©`, and install-page mojibake.
- Preserved intentional middle dots and ellipsis characters.
- Removed em/en dashes from user-facing text; only `sql/normalize-text.sql` retains the old search strings on purpose.
- Brand spelling normalised to `Reagansoft Innovation Limited` and `Reagansoft`.
- PHP lint checks: 59 files, 0 failures.

## 7. Database and SQL repair

Original import failures were caused by key definitions that could produce:

- `#1068 Multiple primary key defined`
- `#1826 Duplicate foreign key`

The SQL dumps were rebuilt so they are safe to re-run:

- `CREATE DATABASE IF NOT EXISTS` and `USE`
- `SET NAMES utf8mb4`
- `CREATE TABLE IF NOT EXISTS`
- primary keys, unique keys, indexes, and `AUTO_INCREMENT` defined inline
- `INSERT IGNORE` for seed rows
- foreign keys declared inline with checks disabled
- no `DROP TABLE` and no `DELETE`

`activity_logs` repair:

- `id` is `int unsigned NOT NULL AUTO_INCREMENT`
- primary key on `id`
- user foreign key present
- indexes on entity, user, created date, and action
- existing rows preserved

Current local verification:

- `reagansoft_system`: 17 tables, 8 users
- `reagansoft_website`: 17 tables, 8 users
- `tools/db-check.php`: 48 ok, 0 warnings, 0 failures
- `tools/repair-databases.php --dry-run`: no changes needed for either database
- Company settings, payment numbers, WhatsApp number, and currency verified

Text normalisation is repeatable with:

```powershell
& 'C:\xampp\mysql\bin\mysql.exe' --default-character-set=utf8mb4 -u root < sql\normalize-text.sql
```

Use `--default-character-set=utf8mb4`; without it, branded en-dash replacements can silently fail.

## 8. Authentication and API tests

Local API authentication flow was tested end-to-end with a temporary client account, then the account and its audit rows were deleted:

- `GET /api/v1/auth/session` -> `200`, CSRF token issued
- `POST /api/v1/auth/register` -> `201`, client created
- Duplicate registration -> `409`, correct error
- `POST /api/v1/auth/login` -> `200`, session established
- `GET /api/v1/auth/session` -> `200`, correct `client` role and email
- `POST /api/v1/auth/logout` -> `200`
- Cleanup restored the user count to 8 / 8

Public API checks:

- `/api/v1/public/site` -> `200`
- `/api/v1/public/services` -> `200`

Production public API also returns `200` for those endpoints, so the live database/API layer is reachable. Production login was not exercised after deployment because no credentials were supplied.

## 9. PWA, SEO, and security

- `sw.js` caches the shell/assets, handles navigation fallback, and skips API/admin/install requests.
- Manifest uses `start_url: "/"`, `scope: "/"`, standalone display, theme colour, and 192/512/maskable icons.
- `.htaccess` adds `application/manifest+json` for `.webmanifest`.
- `.htaccess` adds HSTS only when HTTPS is active.
- `spa.php` adds `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and `Permissions-Policy`.
- `.htaccess` denies `install.php`, `create_admin.php`, SQL/MD/log files, and sensitive directories.
- `robots.txt` and `sitemap.xml` are present.
- Social preview uses the absolute `og-image.jpg` URL after deployment.

The installer denial is intentional. To run `install.php` or `create_admin.php`, temporarily remove the matching `<FilesMatch>` block, run the installer, then restore the block and delete the installer.

## 10. Frontend build and performance

Latest build completed successfully:

- 1,648 modules transformed
- `app/index.html` 1.69 KB
- `app/assets/index-CQw-RLI_.css` 69.60 KB (12.09 KB gzip)
- `app/assets/index-CVKqI6Yx.js` 411.26 KB (87.47 KB gzip)
- `app/assets/vendor-DTqo3bJO.js` 165.85 KB (54.21 KB gzip)
- `app/assets/icons-CwBBLo5V.js` 46.93 KB (9.56 KB gzip)

The logo optimisation reduced the header logo from about `563 KB` to `47 KB` and the real footer logo from about `506 KB` to `81 KB`.

The visual upgrade uses the existing lightweight SVG/CSS system (`Graphics.tsx`, `CinemaBand.tsx`, `LiveShowcase.tsx`, `Tilt.tsx`) with cinematic image slides, slow zoom, crossfades, and reduced-motion handling. No Three.js/React Three Fiber dependency was added, and no video files exist in `assets/`. True WebGL or video should be added only with licensed assets and a performance budget.

## 11. Responsive and functional test status

Validated:

- TypeScript/Vite production build
- PHP syntax across 59 files
- SPA routes at HTTP level
- API public endpoints
- Registration, duplicate registration, login, session, and logout
- Database health and mirror consistency
- Manifest, service worker, favicons, logos, OG image, and CSS availability

Not yet validated:

- Visual layout on real phones/tablets
- Production after upload
- Production PWA install prompt
- Real payment/email integrations
- True 3D/WebGL or video playback

Do not interpret HTTP `200` checks as a substitute for device testing.

## 12. Deployment package and upload order

A repeatable packaging script is available at `tools/package-production.ps1`.

It:

- builds the frontend unless `-SkipBuild` is used
- stages `app/`, `assets/`, `api/`, `inc/`, `admin/`, `client/`, root `.php` files, `.htaccess`, `spa.php`, `sw.js`, `robots.txt`, `sitemap.xml`, `README.md`, `sql/normalize-text.sql`, and deployment examples
- excludes `config/`, `storage/`, `uploads/`, `frontend/`, `node_modules`, and local database dumps
- writes a ZIP to the temp folder and includes an `UPLOAD-README.txt`

Run:

```powershell
powershell -ExecutionPolicy Bypass -File tools\package-production.ps1
```

Upload order:

1. Back up the live site and both databases.
2. Upload `app/`, `assets/`, `api/`, `inc/`, `admin/`, `client/`, and the root `.php` files.
3. Upload `.htaccess`, `spa.php`, `sw.js`, `robots.txt`, and `sitemap.xml`.
4. Confirm `config/`, `storage/`, and `uploads/` on production are preserved.
5. Clear LiteSpeed, Cloudflare, and browser caches.
6. Open `/`, `/about`, `/services`, `/login`, `/portal`, and `/console` directly.
7. Check `/sw.js`, `/assets/img/site.webmanifest`, and the logo/OG URLs.
8. Install the PWA over HTTPS and confirm the prompt and offline shell.

Do not upload:

- `config/` with local database credentials
- `storage/` with local sessions/logs
- `uploads/` with local client files
- `frontend/` source or `node_modules`
- local SQL dumps unless deliberately restoring a database
- `install.php`/`create_admin.php` while the installer block is active

## 13. Remaining risks and blockers

- No production server access: deployment must be performed by the account owner.
- Root `.htaccess` must be honoured. If LiteSpeed ignores it, enable `AllowOverride All` and confirm `mod_rewrite`.
- The live `app/` folder must be replaced completely. Uploading new hashed assets beside old files is not enough if the old `index.html` is still served.
- The live manifest MIME must be checked after upload; if LiteSpeed ignores the `.htaccess` `AddType`, set it in cPanel's MIME Types.
- The real logo's transparent centre needs visual approval.
- Offline/PWA caching must be retested on the live HTTPS origin.
- Real device, payment, and email tests are still outstanding.
- True 3D/video remains a design choice requiring licensed assets; the current site uses SVG/CSS cinematic visuals instead.

## 14. Artifacts

- `tools/package-production.ps1` - rebuilds and packages the upload set
- `tools/db-check.php` - database/schema/settings verification
- `tools/repair-databases.php` - safe activity_logs repair
- `sql/normalize-text.sql` - repeatable brand/text normalisation
- `deploy/nginx.conf.example` - Nginx equivalent
- `deploy/web.config` - IIS equivalent
- `README.md` - setup, hosting, PWA, and deployment notes
- `C:\Users\REAGAN\AppData\Local\Temp\rsi-logo-originals` - full-size logo backups
- `C:\Users\REAGAN\AppData\Local\Temp\opencode\process_logos2.ps1` - logo generation script
