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
