import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { App } from './App';
import { AuthProvider } from './lib/auth';
import { SiteProvider } from './lib/site';
import { ToastProvider } from './components/Toast';
import './index.css';

const root = document.getElementById('root');

if (!root) throw new Error('Root element not found');

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
