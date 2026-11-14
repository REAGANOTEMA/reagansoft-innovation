import { useEffect, useState } from 'react';
import { Download, Share, X } from 'lucide-react';

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

const DISMISS_KEY = 'rsi_install_dismissed';

function isStandalone(): boolean {
  if (typeof window === 'undefined') return false;
  const media = window.matchMedia('(display-mode: standalone)').matches;
  const ios = (window.navigator as Navigator & { standalone?: boolean }).standalone === true;
  return media || ios;
}

function isAppleMobile(): boolean {
  if (typeof navigator === 'undefined') return false;
  return /iphone|ipad|ipod/i.test(navigator.userAgent);
}

export function InstallApp() {
  const [prompt, setPrompt] = useState<BeforeInstallPromptEvent | null>(null);
  const [visible, setVisible] = useState(false);
  const [iosHelp, setIosHelp] = useState(false);

  useEffect(() => {
    if (isStandalone() || localStorage.getItem(DISMISS_KEY) === '1') return;

    const onPrompt = (event: Event) => {
      event.preventDefault();
      setPrompt(event as BeforeInstallPromptEvent);
      setVisible(true);
    };

    const onInstalled = () => {
      setVisible(false);
      setPrompt(null);
    };

    window.addEventListener('beforeinstallprompt', onPrompt);
    window.addEventListener('appinstalled', onInstalled);

    if (isAppleMobile()) {
      const timer = window.setTimeout(() => setVisible(true), 4500);
      return () => {
        window.clearTimeout(timer);
        window.removeEventListener('beforeinstallprompt', onPrompt);
        window.removeEventListener('appinstalled', onInstalled);
      };
    }

    return () => {
      window.removeEventListener('beforeinstallprompt', onPrompt);
      window.removeEventListener('appinstalled', onInstalled);
    };
  }, []);

  if (!visible) return null;

  const dismiss = () => {
    localStorage.setItem(DISMISS_KEY, '1');
    setVisible(false);
    setIosHelp(false);
  };

  const install = async () => {
    if (prompt) {
      await prompt.prompt();
      const choice = await prompt.userChoice;
      if (choice.outcome === 'accepted') dismiss();
      return;
    }
    if (isAppleMobile()) setIosHelp(true);
  };

  return (
    <div className="no-print fixed inset-x-3 bottom-3 z-[70] sm:inset-x-auto sm:right-5 sm:bottom-5 sm:w-[330px]">
      <div className="relative overflow-hidden rounded-2xl border border-brand-200 bg-white p-4 shadow-[0_24px_60px_-20px_rgba(8,59,102,.45)]">
        <button
          type="button"
          onClick={dismiss}
          aria-label="Dismiss install prompt"
          className="absolute right-2 top-2 grid h-7 w-7 place-items-center rounded-full text-ink-faint transition hover:bg-brand-50 hover:text-ink"
        >
          <X className="h-4 w-4" />
        </button>
        <div className="flex items-start gap-3">
          <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-navy-900 text-white">
            <Download className="h-5 w-5" />
          </span>
          <div className="min-w-0">
            <p className="pr-5 text-[13px] font-semibold text-navy-900">Install the Reagansoft app</p>
            <p className="mt-1 text-[12px] leading-relaxed text-ink-muted">
              {iosHelp
                ? 'Tap the Share button in Safari, then choose Add to Home Screen.'
                : 'Open the portal like a normal app and keep your projects a tap away.'}
            </p>
            <div className="mt-3 flex items-center gap-2">
              {iosHelp ? (
                <span className="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1.5 text-[11px] font-semibold text-brand-700">
                  <Share className="h-3.5 w-3.5" /> Share, then Add to Home Screen
                </span>
              ) : (
                <button type="button" onClick={install} className="btn-primary btn-sm">
                  Install
                </button>
              )}
              <button
                type="button"
                onClick={dismiss}
                className="text-[11px] font-semibold text-ink-muted transition hover:text-ink"
              >
                Not now
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
