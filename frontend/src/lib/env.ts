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

export const LOGO = `${ASSET_BASE}/reagansoftinnovation-logo.jpeg`;
export const FAVICON = `${ASSET_BASE}/reagansoftinnovation-logo-favicon.png`;

export function asset(name: string): string {
  return `${ASSET_BASE}/${name.replace(/^\//, '')}`;
}

export function appUrl(path: string): string {
  return `${APP_BASE}${path.startsWith('/') ? path : `/${path}`}`;
}
