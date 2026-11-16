import { useEffect, useRef, useState } from 'react';
import { Volume2, X } from 'lucide-react';
import { cn } from '../lib/cn';

const STORAGE_KEY = 'rsi_welcome_voice_v1';

export function WelcomeVoice() {
  const [visible, setVisible] = useState(false);
  const [speaking, setSpeaking] = useState(false);
  const utteranceRef = useRef<SpeechSynthesisUtterance | null>(null);

  useEffect(() => {
    if (typeof window === 'undefined' || !('speechSynthesis' in window)) return;
    try {
      if (window.localStorage.getItem(STORAGE_KEY) === 'done') return;
    } catch {
      // ignore storage errors
    }
    const timer = window.setTimeout(() => setVisible(true), 1200);
    return () => window.clearTimeout(timer);
  }, []);

  function pickVoice() {
    const voices = window.speechSynthesis.getVoices();
    const preferred = voices.find((v) => /en(-|_)?(GB|US|NG|KE|ZA)?/i.test(v.lang) && /female|zira|samantha|aria|google uk english female|google us english/i.test(`${v.name} ${v.voiceURI}`));
    return preferred || voices.find((v) => /^en/i.test(v.lang)) || voices[0] || null;
  }

  function markDone() {
    try {
      window.localStorage.setItem(STORAGE_KEY, 'done');
    } catch {
      // ignore
    }
  }

  function dismiss() {
    window.speechSynthesis.cancel();
    setSpeaking(false);
    markDone();
    setVisible(false);
  }

  function play() {
    if (!('speechSynthesis' in window)) return;
    window.speechSynthesis.cancel();
    const text = 'Welcome to Reagansoft Innovation Limited. We craft premium software, websites, and digital systems tailored for your growth. How can we help you today?';
    const utterance = new SpeechSynthesisUtterance(text);
    const voice = pickVoice();
    if (voice) utterance.voice = voice;
    utterance.rate = 0.96;
    utterance.pitch = 1.02;
    utterance.volume = 1;
    utterance.onend = () => {
      setSpeaking(false);
      markDone();
      setVisible(false);
    };
    utterance.onerror = () => {
      setSpeaking(false);
    };
    utteranceRef.current = utterance;
    setSpeaking(true);
    window.speechSynthesis.speak(utterance);
  }

  if (!visible) return null;

  return (
    <div className="fixed bottom-5 right-5 z-[70] max-w-[320px] animate-fade-up">
      <div className="relative overflow-hidden rounded-2xl border border-brand-200/70 bg-white/95 p-4 shadow-[0_20px_50px_-20px_rgba(6,42,74,.45)] backdrop-blur-xl">
        <div className="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-50/80 via-transparent to-cyan-50/60" />
        <button
          type="button"
          onClick={dismiss}
          className="absolute right-2 top-2 rounded-lg p-1 text-ink-faint transition hover:bg-brand-50 hover:text-navy-900"
          aria-label="Dismiss welcome message"
        >
          <X className="h-4 w-4" />
        </button>
        <p className="relative font-mono text-[10px] uppercase tracking-[0.18em] text-brand-700">Welcome</p>
        <p className="relative mt-1 pr-6 text-sm font-semibold text-navy-900">A premium greeting from Reagansoft</p>
        <p className="relative mt-1 text-xs leading-relaxed text-ink-muted">Tap to hear a short welcome message.</p>
        <div className="relative mt-3 flex items-center gap-2">
          <button
            type="button"
            onClick={play}
            className={cn(
              'inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition',
              speaking ? 'bg-brand-700' : 'bg-navy-900 hover:bg-brand-700',
            )}
          >
            <Volume2 className="h-4 w-4" />
            {speaking ? 'Playing…' : 'Play welcome voice'}
          </button>
          <button type="button" onClick={dismiss} className="text-xs font-medium text-ink-muted hover:text-navy-900">
            Not now
          </button>
        </div>
      </div>
    </div>
  );
}
