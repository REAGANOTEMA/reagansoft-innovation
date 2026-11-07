import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  ArrowRight,
  ArrowUpRight,
  ChevronLeft,
  ChevronRight,
  Clock3,
  Pause,
  Play,
  Radio,
  ShieldCheck,
  Sparkles,
} from 'lucide-react';
import { asset } from '../lib/env';
import { cn } from '../lib/cn';
import type { Service } from '../lib/types';

interface Slide {
  image: string;
  channel: string;
  eyebrow: string;
  title: string;
  accent: string;
  lead: string;
  to: string;
  cta: string;
  alt: string;
}

const slides: Slide[] = [
  {
    image: 'hero1.webp',
    channel: 'Web development',
    eyebrow: 'Channel 01 · Websites',
    title: 'Websites that ',
    accent: 'earn their keep',
    lead: 'A fast, secure website built around your business — designed to look right on every screen and still working properly years after launch.',
    to: '/services/business-website',
    cta: 'Explore website development',
    alt: 'Modern website development at Reagansoft Innovation',
  },
  {
    image: 'hero2.webp',
    channel: 'Enterprise systems',
    eyebrow: 'Channel 02 · Systems',
    title: 'Systems built around ',
    accent: 'how you work',
    lead: 'Client portals, management dashboards and workflow automation, scoped module by module and approved by you before we write a line of code.',
    to: '/services/business-systems',
    cta: 'See business systems',
    alt: 'Enterprise management system dashboards',
  },
  {
    image: 'hero3.webp',
    channel: 'Web & mobile apps',
    eyebrow: 'Channel 03 · Applications',
    title: 'One app, ',
    accent: 'every screen',
    lead: 'Business apps delivered for the browser, Android and iOS together — prepared, tested and published with your team.',
    to: '/services/web-mobile-apps',
    cta: 'View app development',
    alt: 'Web and mobile application engineering',
  },
  {
    image: 'hero4.webp',
    channel: 'Billing automation',
    eyebrow: 'Channel 04 · Finance',
    title: 'Receipts that ',
    accent: 'issue themselves',
    lead: 'Your shop, school or clinic sends a professional receipt the moment a client pays, with every payment recorded and easy to find again.',
    to: '/services/receipt-billing',
    cta: 'See billing automation',
    alt: 'Receipt and billing automation software',
  },
  {
    image: 'hero5.webp',
    channel: 'Ecommerce',
    eyebrow: 'Channel 05 · Commerce',
    title: 'Online stores that ',
    accent: 'actually sell',
    lead: 'Product catalogues, cart, checkout and mobile money flows designed to turn visitors into paying customers.',
    to: '/services/ecommerce',
    cta: 'Explore ecommerce',
    alt: 'Ecommerce storefront development',
  },
  {
    image: 'hero6.webp',
    channel: 'How we start',
    eyebrow: 'Channel 06 · Process',
    title: 'A fixed, ',
    accent: 'one-time deposit',
    lead: 'Your deposit is credited in full against a written quotation. Scope is agreed before work begins, with no hidden charges.',
    to: '/process',
    cta: 'How a project runs',
    alt: 'Reagansoft project process and fixed deposit',
  },
];

const DURATION = 7000;

export function LiveShowcase({ services, stats }: { services: Service[]; stats: { projects: number; clients: number; services: number } }) {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);
  const [clock, setClock] = useState(() => new Date());
  const timer = useRef<number | null>(null);

  const go = useCallback((next: number) => {
    setActive((current) => (next + slides.length) % slides.length);
  }, []);

  const next = useCallback(() => go(active + 1), [active, go]);
  const prev = useCallback(() => go(active - 1), [active, go]);

  useEffect(() => {
    const id = window.setInterval(() => setClock(new Date()), 1000);
    return () => window.clearInterval(id);
  }, []);

  useEffect(() => {
    if (paused) return;
    timer.current = window.setTimeout(() => setActive((current) => (current + 1) % slides.length), DURATION);
    return () => {
      if (timer.current) window.clearTimeout(timer.current);
    };
  }, [active, paused]);

  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'ArrowRight') setActive((current) => (current + 1) % slides.length);
      if (event.key === 'ArrowLeft') setActive((current) => (current - 1 + slides.length) % slides.length);
      if (event.key === ' ') {
        event.preventDefault();
        setPaused((value) => !value);
      }
    };
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, []);

  const slide = slides[active];
  const featured = useMemo(() => services.slice(0, 3), [services]);
  const time = clock.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

  return (
    <section
      className="relative flex min-h-[640px] flex-col overflow-hidden bg-navy-950 text-white sm:min-h-[700px] lg:min-h-[760px]"
      aria-roledescription="carousel"
      aria-label="Reagansoft live showcase"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
    >
      {slides.map((item, index) => (
        <div
          key={item.channel}
          className={cn(
            'absolute inset-0 transition-opacity duration-1000',
            index === active ? 'opacity-100' : 'pointer-events-none opacity-0',
          )}
          aria-hidden={index !== active}
        >
          <img
            src={asset(item.image)}
            alt={item.alt}
            className={cn('h-full w-full object-cover', index === active && 'kenburns')}
            loading={index === 0 ? 'eager' : 'lazy'}
            fetchPriority={index === 0 ? 'high' : 'auto'}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-navy-950 via-navy-950/85 to-navy-950/30" />
          <div className="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/20 to-navy-950/55" />
        </div>
      ))}

      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-20" />
      <div className="aurora opacity-70" />

      {/* Broadcast bar */}
      <div className="relative z-20 border-b border-white/10 bg-navy-950/35 backdrop-blur">
        <div className="container-x flex h-11 items-center justify-between gap-4 text-[10px] uppercase tracking-[0.2em] text-white/45">
          <div className="flex items-center gap-3">
            <span className="inline-flex items-center gap-1.5 rounded-sm bg-rose-500 px-2 py-1 font-bold tracking-[0.18em] text-white">
              <span className="h-1.5 w-1.5 animate-pulse rounded-full bg-white" /> Live
            </span>
            <span className="hidden font-semibold text-white/75 sm:inline">Reagansoft TV</span>
            <span className="hidden sm:inline">/</span>
            <span className="hidden sm:inline">{slide.channel}</span>
          </div>
          <div className="hidden items-center gap-4 md:flex">
            <span className="inline-flex items-center gap-1.5"><Radio className="h-3 w-3" /> Channel {String(active + 1).padStart(2, '0')}</span>
            <span className="inline-flex items-center gap-1.5"><Clock3 className="h-3 w-3" /> {time} EAT</span>
            <span className="rounded-sm border border-white/20 px-1.5 py-0.5 font-semibold text-white/70">HD</span>
          </div>
          <div className="flex items-center gap-3">
            <span className="font-mono text-[10px] text-white/40">{String(active + 1).padStart(2, '0')} / {String(slides.length).padStart(2, '0')}</span>
          </div>
        </div>
      </div>

      <div className="container-x relative z-10 flex flex-1 flex-col justify-center py-14 lg:py-20">
        <div className="grid items-center gap-12 lg:grid-cols-[1.08fr_0.92fr]">
          <div key={active} className="max-w-2xl">
            <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-200 backdrop-blur">
              <span className="relative flex h-2 w-2">
                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60" />
                <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-400" />
              </span>
              {slide.eyebrow}
            </span>

            <h1 className="h1 mt-6 text-white">
              {slide.title}
              <span className="bg-gradient-to-r from-brand-300 via-cyan-300 to-brand-400 bg-clip-text text-transparent">{slide.accent}</span>
            </h1>

            <p className="mt-6 max-w-xl text-[1.02rem] leading-relaxed text-white/65 sm:text-[1.1rem]">{slide.lead}</p>

            <div className="mt-8 flex flex-wrap items-center gap-3">
              <Link to={slide.to} className="btn-primary">
                {slide.cta} <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/portfolio" className="btn inline-flex border border-white/20 bg-white/5 text-white backdrop-blur hover:bg-white/10">
                Watch our work <ArrowUpRight className="h-4 w-4" />
              </Link>
            </div>

            <dl className="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
              {[
                { label: 'Projects delivered', value: `${stats.projects || 7}+` },
                { label: 'Organisations served', value: `${stats.clients || 6}+` },
                { label: 'Service lines', value: `${stats.services || services.length || 14}` },
              ].map((item) => (
                <div key={item.label}>
                  <dt className="font-mono text-[9px] uppercase tracking-[0.18em] text-white/35">{item.label}</dt>
                  <dd className="mt-1 font-display text-2xl font-semibold text-white">{item.value}</dd>
                </div>
              ))}
            </dl>
          </div>

          <div className="relative hidden lg:block">
            <div className="relative ml-auto w-full max-w-[420px]">
              <div className="rounded-2xl border border-white/15 bg-navy-950/55 p-3 shadow-[0_50px_120px_-40px_rgba(3,26,46,.95)] backdrop-blur-xl">
                <div className="flex items-center justify-between border-b border-white/10 pb-2.5">
                  <span className="font-mono text-[9px] uppercase tracking-[0.2em] text-white/40">Now showing</span>
                  <span className="inline-flex items-center gap-1.5 font-mono text-[9px] uppercase tracking-[0.16em] text-emerald-300">
                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" /> on air
                  </span>
                </div>

                <div className="mt-3 overflow-hidden rounded-xl border border-white/10">
                  <img key={slide.image} src={asset(slide.image)} alt="" className="h-44 w-full object-cover" />
                </div>

                <div className="mt-3 flex items-center justify-between gap-3">
                  <div>
                    <p className="font-display text-sm font-semibold text-white">{slide.channel}</p>
                    <p className="mt-0.5 text-[10px] text-white/40">Jinja, Uganda · engineering studio</p>
                  </div>
                  <span className="grid h-9 w-9 place-items-center rounded-full border border-white/15 bg-white/5 text-brand-300">
                    <ShieldCheck className="h-4 w-4" />
                  </span>
                </div>

                <div className="mt-4 space-y-2">
                  {featured.map((service, index) => (
                    <Link
                      key={service.id}
                      to={`/services/${service.slug}`}
                      className="group flex items-center justify-between gap-3 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2 transition hover:bg-white/[0.07]"
                    >
                      <span className="flex items-center gap-2 text-[11px] text-white/60">
                        <span className="font-mono text-[9px] text-white/30">0{index + 1}</span>
                        {service.name}
                      </span>
                      <ArrowUpRight className="h-3 w-3 text-white/30 transition group-hover:text-brand-300" />
                    </Link>
                  ))}
                </div>
              </div>

              <div className="absolute -bottom-4 -left-5 rounded-xl border border-white/15 bg-navy-900/90 px-4 py-3 shadow-soft backdrop-blur">
                <p className="font-mono text-[8px] uppercase tracking-[0.2em] text-white/35">Uptime</p>
                <p className="mt-1 font-display text-lg font-semibold text-emerald-300">99.98%</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Controls */}
      <div className="relative z-20 border-t border-white/10 bg-navy-950/55 backdrop-blur">
        <div className="container-x flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-3">
            <button type="button" onClick={prev} className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/70 transition hover:border-white/35 hover:text-white" aria-label="Previous slide">
              <ChevronLeft className="h-4 w-4" />
            </button>
            <button type="button" onClick={() => setPaused((value) => !value)} className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/70 transition hover:border-white/35 hover:text-white" aria-label={paused ? 'Play slideshow' : 'Pause slideshow'}>
              {paused ? <Play className="h-4 w-4" /> : <Pause className="h-4 w-4" />}
            </button>
            <button type="button" onClick={next} className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/70 transition hover:border-white/35 hover:text-white" aria-label="Next slide">
              <ChevronRight className="h-4 w-4" />
            </button>
            <span className="ml-1 font-mono text-[10px] uppercase tracking-[0.18em] text-white/35">{paused ? 'Paused' : 'Auto-play'}</span>
          </div>

          <div className="flex flex-1 items-center gap-2 sm:max-w-md">
            {slides.map((item, index) => (
              <button
                key={item.channel}
                type="button"
                onClick={() => go(index)}
                className="group relative h-1 flex-1 overflow-hidden rounded-full bg-white/15"
                aria-label={`Show ${item.channel}`}
                aria-current={index === active}
              >
                <span
                  key={`${index}-${active}-${paused}`}
                  className={cn(
                    'absolute inset-y-0 left-0 rounded-full bg-brand-400',
                    index === active ? (paused ? 'w-full' : 'tv-progress') : 'w-0 group-hover:w-full group-hover:bg-white/40',
                  )}
                  style={index === active && !paused ? { animationDuration: `${DURATION}ms` } : undefined}
                />
              </button>
            ))}
          </div>

          <div className="hidden items-center gap-2 font-mono text-[10px] uppercase tracking-[0.16em] text-white/35 lg:flex">
            <Sparkles className="h-3 w-3" /> Innovating today for a smarter tomorrow
          </div>
        </div>
      </div>
    </section>
  );
}
