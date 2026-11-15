import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { AuthProvider } from './lib/auth';
import { SiteProvider } from './lib/site';
import { ToastProvider } from './components/Toast';
import { APP_BASE } from './lib/env';
import './index.css';

const root = document.getElementById('root');

if (!root) throw new Error('Root element not found');

if (import.meta.env.PROD && 'serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    const base = APP_BASE.replace(/\/$/, '');
    navigator.serviceWorker
      .register(`${base}/sw.js`, { scope: `${base}/` })
      .catch(() => {
        /* Registration is optional; the site keeps working without it. */
      });
  });
}

createRoot(root).render(
  <StrictMode>
    <ToastProvider>
      <SiteProvider>
        <AuthProvider>
          <App />
        </AuthProvider>
      </SiteProvider>
    </ToastProvider>
  </StrictMode>,
);

/**
 * The brand splash is painted by the static HTML shell before React boots.
 * Hold it briefly so the opening feels intentional, then fade it away.
 */
const splash = document.getElementById('rsi-splash');
if (splash) {
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  window.setTimeout(() => {
    splash.classList.add('rsi-splash-out');
    window.setTimeout(() => splash.remove(), reduced ? 60 : 700);
  }, reduced ? 250 : 1400);
}
