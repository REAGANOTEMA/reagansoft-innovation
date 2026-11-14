/* Reagansoft Innovation Limited - service worker
 *
 * Keeps the application shell available offline, caches fingerprinted
 * assets, and never caches API responses or authenticated POST requests.
 * The file is registered from frontend/src/main.tsx with a scope that
 * matches the install path (domain root or a subfolder).
 */

const VERSION = 'rsi-v4';
const CACHE = `rsi-static-${VERSION}`;
const scope = self.registration.scope;
const SHELL_URL = new URL('index.html', `${scope}app/`).href;
const ROOT_URL = new URL('./', scope).href;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(CACHE)
      .then((cache) => Promise.allSettled([cache.add(ROOT_URL), cache.add(SHELL_URL)]))
      .then(() => self.skipWaiting()),
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;
  if (url.pathname.endsWith('/sw.js')) return;
  if (url.pathname.includes('/api/')) return; // API calls always go to the network

  if (request.mode === 'navigate') {
    event.respondWith(networkFirst(request));
    return;
  }

  if (/\.(css|js|mjs|woff2?|ttf|otf|png|jpe?g|webp|avif|svg|ico|webmanifest)$/i.test(url.pathname)) {
    event.respondWith(cacheFirst(request));
  }
});

async function networkFirst(request) {
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      const copy = response.clone();
      caches.open(CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
    }
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) return cached;
    const shell = await caches.match(SHELL_URL);
    if (shell) return shell;
    return new Response(
      '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline | Reagansoft Innovation</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#062a4a;color:#eef8ff;font:16px/1.6 system-ui,sans-serif;padding:32px;text-align:center}a{color:#88d0ff}</style></head><body><main><h1 style="font-size:24px;margin:0 0 8px">You are offline</h1><p style="margin:0 0 20px;color:#b8e2ff">The Reagansoft site could not be reached. Reconnect and try again.</p><a href="' + ROOT_URL + '">Reload the site</a></main></body></html>',
      { headers: { 'Content-Type': 'text/html; charset=UTF-8' } },
    );
  }
}

async function cacheFirst(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    const response = await fetch(request);
    if (response && response.ok) {
      const copy = response.clone();
      caches.open(CACHE).then((cache) => cache.put(request, copy)).catch(() => {});
    }
    return response;
  } catch (error) {
    return new Response('', { status: 504, statusText: 'Offline' });
  }
}
