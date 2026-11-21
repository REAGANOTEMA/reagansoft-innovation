import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, ChevronLeft, ChevronRight, Pause, Play, Radio } from 'lucide-react';
import { asset } from '../lib/env';
import { cn } from '../lib/cn';

export interface CinemaSlide {
  image: string;
  alt: string;
  label: string;
  caption: string;
}

interface CinemaBandProps {
  eyebrow?: string;
  title: string;
  lead?: string;
  slides: CinemaSlide[];
  to?: string;
  cta?: string;
}

const DURATION = 4200;

export function CinemaBand({ eyebrow = 'Studio reel', title, lead, slides, to, cta }: CinemaBandProps) {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);

  const go = useCallback(
    (next: number) => setActive((current) => (next + slides.length) % slides.length),
    [slides.length],
  );

  useEffect(() => {
    if (paused || slides.length < 2) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const timer = window.setTimeout(() => setActive((current) => (current + 1) % slides.length), DURATION);
    return () => window.clearTimeout(timer);
  }, [active, paused, slides.length]);

  useEffect(() => {
    if (active > slides.length - 1) setActive(0);
  }, [active, slides.length]);

  const slide = slides[active];

  return (
    <section
      className="relative isolate overflow-hidden bg-navy-950 text-white"
      aria-roledescription="carousel"
      aria-label={title}
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      <div className="absolute inset-0">
        {slides.map((item, index) => (
          <div
            key={item.image}
            className={cn('absolute inset-0 transition-opacity duration-1000', index === active ? 'opacity-100' : 'opacity-0')}
            aria-hidden={index !== active}
          >
            <img
              src={asset(item.image)}
              alt={item.alt}
              className={cn('h-full w-full object-cover', index === active && !paused && 'kenburns')}
              loading="lazy"
            />
          </div>
        ))}
        <div className="absolute inset-0 bg-gradient-to-r from-navy-950 via-navy-950/85 to-navy-950/35" />
        <div className="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/25 to-navy-950/50" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-20" />
        <div className="cine-grain pointer-events-none absolute inset-0" />
        <div className="cine-bars pointer-events-none absolute inset-0" />
        <div className="lens-flare pointer-events-none absolute -right-14 top-10 h-56 w-56 rounded-full" aria-hidden="true" />
        <div className="screen-scan absolute inset-0" />
        <div className="screen-sheen" />
        <div aria-hidden="true" className="pointer-events-none absolute -right-16 top-1/4 h-64 w-64 rounded-full bg-brand-500/20 blur-3xl glow-orb" />
      </div>

      <span className="frame-corner frame-corner-tl" aria-hidden="true" />
      <span className="frame-corner frame-corner-tr" aria-hidden="true" />
      <span className="frame-corner frame-corner-bl" aria-hidden="true" />
      <span className="frame-corner frame-corner-br" aria-hidden="true" />

      <div className="container-x relative flex min-h-[450px] flex-col justify-between gap-6 py-7 sm:min-h-[470px] sm:py-9">
        <div className="flex flex-wrap items-center justify-between gap-3 text-[10px] uppercase tracking-[0.2em] text-white/45">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1.5 font-mono font-semibold text-brand-200 backdrop-blur">
            <Radio className="h-3 w-3" /> {eyebrow}
            <span className="live-eq text-emerald-300" aria-hidden="true"><span /><span /><span /><span /><span /></span>
          </span>
          <span className="font-mono text-white/35">
            {String(active + 1).padStart(2, '0')} / {String(slides.length).padStart(2, '0')}
          </span>
        </div>

        <div className="grid gap-6 pb-2 sm:gap-8 sm:pb-3 lg:grid-cols-[1fr_auto] lg:items-end">
          <div key={active} className="max-w-2xl">
            <span className="slide-rise inline-flex rounded-full bg-brand-500/15 px-3 py-1 font-mono text-[9.5px] font-bold uppercase tracking-[0.18em] text-brand-200 ring-1 ring-brand-400/30">
              {slide.label}
            </span>
            <h2 className="slide-rise slide-delay-1 mt-4 font-display text-[clamp(1.7rem,3.4vw,2.9rem)] font-semibold leading-[1.05] text-white">{title}</h2>
            {lead && <p className="slide-rise slide-delay-2 mt-3 max-w-xl text-[13px] leading-relaxed text-white/60 sm:text-sm">{lead}</p>}
            <p className="slide-rise slide-delay-3 mt-4 flex items-center gap-2 text-[11.5px] text-white/50">
              <span className="live-eq shrink-0 text-emerald-300" aria-hidden="true"><span /><span /><span /><span /><span /></span>
              {slide.caption}
            </p>
            {to && cta && (
              <Link to={to} className="slide-rise slide-delay-4 btn mt-5 w-full justify-center border border-white/20 bg-white/5 text-white backdrop-blur hover:bg-white/10 min-[420px]:w-auto">
                {cta} <ArrowRight className="h-4 w-4" />
              </Link>
            )}
          </div>

          <div className="flex flex-wrap items-center gap-2 lg:flex-col lg:items-end">
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => go(active - 1)}
                className="grid h-9 w-9 place-items-center rounded-full border border-white/20 text-white/70 transition hover:border-white/40 hover:text-white"
                aria-label="Previous slide"
              >
                <ChevronLeft className="h-4 w-4" />
              </button>
              <button
                type="button"
                onClick={() => setPaused((value) => !value)}
                className="grid h-9 w-9 place-items-center rounded-full border border-white/20 text-white/70 transition hover:border-white/40 hover:text-white"
                aria-label={paused ? 'Play reel' : 'Pause reel'}
              >
                {paused ? <Play className="h-4 w-4" /> : <Pause className="h-4 w-4" />}
              </button>
              <button
                type="button"
                onClick={() => go(active + 1)}
                className="grid h-9 w-9 place-items-center rounded-full border border-white/20 text-white/70 transition hover:border-white/40 hover:text-white"
                aria-label="Next slide"
              >
                <ChevronRight className="h-4 w-4" />
              </button>
            </div>
            <div className="flex items-center gap-1.5 lg:mt-1">
              {slides.map((item, index) => (
                <button
                  key={item.image}
                  type="button"
                  onClick={() => go(index)}
                  className={cn('relative h-1 overflow-hidden rounded-full bg-white/20 transition-all', index === active ? 'w-8' : 'w-2.5 hover:bg-white/40')}
                  aria-label={`Show ${item.label}`}
                  aria-current={index === active}
                >
                  <span
                    key={`${index}-${active}-${paused}`}
                    className={cn(
                      'absolute inset-y-0 left-0 rounded-full bg-brand-300',
                      index === active ? (paused ? 'w-full' : 'tv-progress') : 'w-0',
                    )}
                    style={index === active && !paused ? { animationDuration: `${DURATION}ms` } : undefined}
                  />
                </button>
              ))}
            </div>
          </div>
        </div>

        <div className="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
          <span className="mr-1 shrink-0 font-mono text-[9px] uppercase tracking-[0.2em] text-white/35">Reel</span>
          {slides.map((item, index) => (
            <button
              key={item.image}
              type="button"
              onClick={() => go(index)}
              aria-label={`Show ${item.label}`}
              aria-current={index === active}
                className={cn(
                  'reel-frame group relative h-11 w-16 shrink-0 overflow-hidden rounded-lg border transition duration-300 sm:h-12 sm:w-20',
                index === active
                  ? 'border-brand-300 shadow-[0_0_0_2px_rgba(79,182,255,.35)]'
                  : 'border-white/15 opacity-55 hover:opacity-100',
              )}
            >
               <img
                 src={asset(item.image)}
                 alt=""
                 className="h-full w-full object-cover transition duration-500 group-hover:scale-110"
                 loading="lazy"
               />
               <span className="reel-sheen absolute inset-0" />
               <span className="absolute inset-0 bg-gradient-to-t from-navy-950/75 via-transparent to-transparent" />
              <span className="absolute bottom-1 left-1.5 font-mono text-[7.5px] uppercase tracking-[0.14em] text-white/80">
                {String(index + 1).padStart(2, '0')}
              </span>
              {index === active && <span className="absolute inset-x-0 top-0 h-0.5 bg-brand-300" />}
            </button>
          ))}
        </div>
      </div>
    </section>
  );
}
