function meta(name: string): string {
  const el = document.querySelector(`meta[name="${name}"]`);
  return el?.getAttribute('content') ?? '';
}

/** Install path, e.g. "/reagansoft-innovation" (empty at a domain root). */
export const APP_BASE = meta('rsi-base').replace(/\/$/, '');

/** Absolute API base, injected by the PHP shell. */
export const API_BASE = meta('rsi-api-base') || `${APP_BASE}/api/v1`;

/** Absolute public image base. */
export const ASSET_BASE = meta('rsi-asset-base') || `${APP_BASE}/assets/img`;

/** Current brand mark (header, sign-in, app icon), transparent background. */
export const LOGO_NEW = `${ASSET_BASE}/reagansoftinnovation-logo-new.png`;

/** White version of the current mark for dark surfaces. */
export const LOGO_NEW_WHITE = `${ASSET_BASE}/reagansoftinnovation-logo-new-white.png`;

/** Real company mark for footers and brand panels, transparent background. */
export const LOGO_REAL = `${ASSET_BASE}/real-reagansoft-logo.png`;

/** White version of the real mark for dark surfaces. */
export const LOGO_REAL_WHITE = `${ASSET_BASE}/real-reagansoft-logo-white.png`;

export const LOGO = LOGO_NEW;
export const FAVICON = `${ASSET_BASE}/reagansoftinnovation-logo-favicon.png`;
export const APPLE_ICON = `${ASSET_BASE}/apple-180.png`;
export const APP_ICON_192 = `${ASSET_BASE}/icon-192.png`;
export const APP_ICON_512 = `${ASSET_BASE}/icon-512.png`;
export const MANIFEST = `${ASSET_BASE}/site.webmanifest`;

export function asset(name: string): string {
  const clean = name.replace(/^\//, '');
  const path = clean
    .split('/')
    .map((segment) => encodeURIComponent(segment).replace(/%2F/gi, '/'))
    .join('/');
  return `${ASSET_BASE}/${path}`;
}

export function appUrl(path: string): string {
  return `${APP_BASE}${path.startsWith('/') ? path : `/${path}`}`;
}
