import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, Quote } from 'lucide-react';
import { asset } from '../lib/env';
import { frameFit, MEDIA, MEDIA_ALT, type MediaKey } from '../lib/media';
import { cn } from '../lib/cn';

const notes: { key: MediaKey; label: string; text: string; to: string }[] = [
  {
    key: 'ceoBike',
    label: 'Field note 01 · On the road',
    text: 'Our team is willing to reach our clients as far as they are, to install the systems, train the people and handle everything else that comes with it.',
    to: '/contact',
  },
  {
    key: 'founder',
    label: 'Field note 02 · Direct access',
    text: 'You talk to the person who builds the system. No account layer, no telephone game, no mystery about who is responsible.',
    to: '/about',
  },
  {
    key: 'team',
    label: 'Field note 03 · One studio',
    text: 'The people who design your product are the people who support it after launch. Context stays with the team, not in a handover document.',
    to: '/about',
  },
  {
    key: 'officeVisitors',
    label: 'Field note 04 · Open doors',
    text: 'Come and sit with us in Jinja. Ask the awkward questions, meet the developers and see the workspace before you decide anything.',
    to: '/contact',
  },
];

const DURATION = 5200;

/** Rotating written slides with real photography, shared by every page. */
export function FieldNotes() {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    if (paused || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const timer = window.setTimeout(() => setActive((current) => (current + 1) % notes.length), DURATION);
    return () => window.clearTimeout(timer);
  }, [active, paused]);

  const note = notes[active];

  return (
    <div
      className="relative mt-6 overflow-hidden rounded-[26px] border border-white/10 bg-white/[0.035] p-4 sm:p-5"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      aria-roledescription="carousel"
      aria-label="Notes from the studio"
    >
      <div className="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true" />
      <svg viewBox="0 0 600 160" className="pointer-events-none absolute inset-0 h-full w-full opacity-60" fill="none" aria-hidden="true">
        <g className="orbit-spin-slow" style={{ transformOrigin: '50% 50%' }}>
          <ellipse cx="500" cy="80" rx="120" ry="80" stroke="#7dd3fc" strokeOpacity="0.12" strokeDasharray="3 12" />
        </g>
      </svg>

      <div className="relative grid items-center gap-4 sm:grid-cols-[112px_1fr] sm:gap-6">
        <div className="media-glow relative hidden h-24 w-28 overflow-hidden rounded-2xl border border-white/10 sm:block">
          {notes.map((item, index) => (
            <img
              key={item.key}
              src={asset(MEDIA[item.key])}
              alt={index === active ? MEDIA_ALT[item.key] : ''}
              className={cn('absolute inset-0 h-full w-full transition-opacity duration-700', frameFit(MEDIA[item.key]), index === active ? 'opacity-100' : 'opacity-0')}
              loading="lazy"
            />
          ))}
          <span className="screen-sheen" aria-hidden="true" />
        </div>

        <div key={active} className="min-w-0">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-brand-300">{note.label}</p>
            <span className="font-mono text-[9px] uppercase tracking-[0.16em] text-white/30">
              {String(active + 1).padStart(2, '0')} / {String(notes.length).padStart(2, '0')}
            </span>
          </div>
          <p key={note.text} className="slide-rise mt-3 flex gap-2 font-display text-[14px] leading-relaxed text-white/85 sm:text-[16px]">
            <Quote className="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
            {note.text}
          </p>
          <div className="mt-4 flex items-center gap-3">
            <div className="flex flex-1 gap-1.5">
              {notes.map((item, index) => (
                <button
                  key={item.key}
                  type="button"
                  onClick={() => setActive(index)}
                  className="group relative h-1 flex-1 overflow-hidden rounded-full bg-white/15"
                  aria-label={`Show ${item.label}`}
                  aria-current={index === active}
                >
                  <span
                    key={`${index}-${active}-${paused}`}
                    className={cn(
                      'absolute inset-y-0 left-0 rounded-full bg-brand-300',
                      index === active ? (paused ? 'w-full' : 'tv-progress') : 'w-0 group-hover:w-full group-hover:bg-white/40',
                    )}
                    style={index === active && !paused ? { animationDuration: `${DURATION}ms` } : undefined}
                  />
                </button>
              ))}
            </div>
            <Link to={note.to} className="inline-flex shrink-0 items-center gap-1 text-[11px] font-semibold text-brand-200 transition hover:text-white">
              Read more <ArrowUpRight className="h-3 w-3" />
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
