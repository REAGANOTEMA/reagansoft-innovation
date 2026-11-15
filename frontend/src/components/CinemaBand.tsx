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
      </div>

      <div className="container-x relative flex min-h-[450px] flex-col justify-between gap-6 py-7 sm:min-h-[470px] sm:py-9">
        <div className="flex flex-wrap items-center justify-between gap-3 text-[10px] uppercase tracking-[0.2em] text-white/45">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3 py-1.5 font-mono font-semibold text-brand-200 backdrop-blur">
            <Radio className="h-3 w-3" /> {eyebrow}
          </span>
          <span className="font-mono text-white/35">
            {String(active + 1).padStart(2, '0')} / {String(slides.length).padStart(2, '0')}
          </span>
        </div>

        <div className="grid gap-6 pb-2 sm:gap-8 sm:pb-3 lg:grid-cols-[1fr_auto] lg:items-end">
          <div key={active} className="max-w-2xl">
            <span className="inline-flex rounded-full bg-brand-500/15 px-3 py-1 font-mono text-[9.5px] font-bold uppercase tracking-[0.18em] text-brand-200 ring-1 ring-brand-400/30">
              {slide.label}
            </span>
            <h2 className="mt-4 font-display text-[clamp(1.7rem,3.4vw,2.9rem)] font-semibold leading-[1.05] text-white">{title}</h2>
            {lead && <p className="mt-3 max-w-xl text-[13px] leading-relaxed text-white/60 sm:text-sm">{lead}</p>}
            <p className="mt-4 flex items-center gap-2 text-[11.5px] text-white/50">
              <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-400" />
              {slide.caption}
            </p>
            {to && cta && (
              <Link to={to} className="btn mt-5 w-full justify-center border border-white/20 bg-white/5 text-white backdrop-blur hover:bg-white/10 min-[420px]:w-auto">
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
                  className={cn('h-1 rounded-full transition-all', index === active ? 'w-6 bg-brand-300' : 'w-2.5 bg-white/25 hover:bg-white/45')}
                  aria-label={`Show ${item.label}`}
                  aria-current={index === active}
                />
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
