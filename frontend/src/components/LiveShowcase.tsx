import { useCallback, useEffect, useMemo, useRef, useState, type PointerEvent as ReactPointerEvent } from 'react';
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
import { frameFit, HERO_SLIDES, isFullFrame, MEDIA, MEDIA_ALT } from '../lib/media';
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
  note: string;
  alt: string;
}

const slides: Slide[] = [
  {
    image: MEDIA[HERO_SLIDES[0]],
    channel: 'Web development',
    eyebrow: 'Channel 01 · Websites',
    title: 'Websites that ',
    accent: 'earn their keep',
    lead: 'A fast, secure website built around your business, designed to look right on every screen and still working properly years after launch.',
    to: '/services/business-website',
    cta: 'Explore website development',
    note: 'From the first sketch to the last button, built by the same senior team.',
    alt: MEDIA_ALT.remoteWork,
  },
  {
    image: MEDIA[HERO_SLIDES[1]],
    channel: 'Enterprise systems',
    eyebrow: 'Channel 02 · Systems',
    title: 'Systems built around ',
    accent: 'how you work',
    lead: 'Client portals, management dashboards and workflow automation, scoped module by module and approved by you before we write a line of code.',
    to: '/services/business-systems',
    cta: 'See business systems',
    note: 'We sit with your team, learn the real workflow, then build around it.',
    alt: MEDIA_ALT.hireDevelopers,
  },
  {
    image: MEDIA[HERO_SLIDES[2]],
    channel: 'Web & mobile apps',
    eyebrow: 'Channel 03 · Applications',
    title: 'One app, ',
    accent: 'every screen',
    lead: 'Business apps delivered for the browser, Android and iOS together, prepared, tested and published with your team.',
    to: '/services/web-mobile-apps',
    cta: 'View app development',
    note: 'One product that feels at home on a laptop, a tablet and a phone.',
    alt: MEDIA_ALT.workspace,
  },
  {
    image: MEDIA[HERO_SLIDES[3]],
    channel: 'Billing automation',
    eyebrow: 'Channel 04 · Finance',
    title: 'Receipts that ',
    accent: 'issue themselves',
    lead: 'Your shop, school or clinic sends a professional receipt the moment a client pays, with every payment recorded and easy to find again.',
    to: '/services/receipt-billing',
    cta: 'See billing automation',
    note: 'Less paperwork, cleaner records and receipts your clients can trust.',
    alt: MEDIA_ALT.codingHome,
  },
  {
    image: MEDIA[HERO_SLIDES[4]],
    channel: 'Ecommerce',
    eyebrow: 'Channel 05 · Commerce',
    title: 'Online stores that ',
    accent: 'actually sell',
    lead: 'Product catalogues, cart, checkout and mobile money flows designed to turn visitors into paying customers.',
    to: '/services/ecommerce',
    cta: 'Explore ecommerce',
    note: 'A storefront designed around how your customers actually pay.',
    alt: MEDIA_ALT.everything,
  },
  {
    image: MEDIA[HERO_SLIDES[5]],
    channel: 'How we start',
    eyebrow: 'Channel 06 · Process',
    title: 'A clear ',
    accent: '75% first payment',
    lead: 'Your first payment is 75% of the written quote, credited in full. The remaining 25% is due at handover, with no hidden charges.',
    to: '/process',
    cta: 'How a project runs',
    note: 'Clear terms from day one, so you always know where you stand.',
    alt: MEDIA_ALT.homepageHero,
  },
  {
    image: MEDIA[HERO_SLIDES[6]],
    channel: 'On-site support',
    eyebrow: 'Channel 07 · Field work',
    title: 'We come ',
    accent: 'to your doorstep',
    lead: 'From Jinja to your shop, school or office, we travel to see how the work really happens, then install, train and support the system where it is used.',
    to: '/contact',
    cta: 'Bring us to your team',
    note: 'Real visits, real training and support after the launch.',
    alt: MEDIA_ALT.ceoBike,
  },
];

const DURATION = 4500;

const tickerItems = [
  'Websites that earn their keep',
  'Management systems',
  'Ecommerce & mobile money',
  'Web and mobile apps',
  'Billing automation',
  'Integrations & APIs',
  'Support after launch',
  'Jinja · Uganda',
];

export function LiveShowcase({ services, stats }: { services: Service[]; stats: { projects: number; clients: number; services: number } }) {
  const [active, setActive] = useState(0);
  const [paused, setPaused] = useState(false);
  const [clock, setClock] = useState(() => new Date());
  const timer = useRef<number | null>(null);
  const sectionRef = useRef<HTMLElement | null>(null);

  const handlePointerMove = (event: ReactPointerEvent<HTMLElement>) => {
    if (event.pointerType !== 'mouse') return;
    const node = sectionRef.current;
    if (!node) return;
    const rect = node.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    node.style.setProperty('--spot-x', `${(((event.clientX - rect.left) / rect.width) * 100).toFixed(1)}%`);
    node.style.setProperty('--spot-y', `${(((event.clientY - rect.top) / rect.height) * 100).toFixed(1)}%`);
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    node.style.setProperty('--tilt-x', `${(x * 9).toFixed(2)}deg`);
    node.style.setProperty('--tilt-y', `${(-y * 7).toFixed(2)}deg`);
  };

  const resetPointer = () => {
    const node = sectionRef.current;
    if (!node) return;
    node.style.setProperty('--tilt-x', '0deg');
    node.style.setProperty('--tilt-y', '0deg');
  };

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
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
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
      ref={sectionRef}
      className="group relative flex min-h-[560px] flex-col overflow-hidden bg-navy-950 text-white sm:min-h-[700px] lg:min-h-[760px]"
      aria-roledescription="carousel"
      aria-label="Reagansoft live showcase"
      aria-live="polite"
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onPointerMove={handlePointerMove}
      onPointerLeave={resetPointer}
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
            className={cn('h-full w-full contrast-[1.06] saturate-[1.08]', frameFit(item.image), isFullFrame(item.image) && 'object-right', index === active && !isFullFrame(item.image) && 'kenburns')}
            loading={index === 0 ? 'eager' : 'lazy'}
            fetchPriority={index === 0 ? 'high' : 'auto'}
          />
          <div className="absolute inset-0 bg-gradient-to-r from-navy-950 via-navy-950/70 to-navy-950/20" />
          <div className="absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/10 to-navy-950/45" />
        </div>
      ))}

      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-20" />
      <div className="cine-grain pointer-events-none absolute inset-0 z-[3]" />
      <div className="cine-bars pointer-events-none absolute inset-0 z-[6]" />
      <div className="screen-scan absolute inset-0" />
      <div className="screen-sheen" />
      <div className="aurora opacity-70" />
      <div className="lens-flare pointer-events-none absolute -right-20 top-[18%] h-72 w-72 rounded-full" aria-hidden="true" />
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 z-[2]"
        style={{ background: 'radial-gradient(460px circle at var(--spot-x, 50%) var(--spot-y, 38%), rgba(79,182,255,0.16), transparent 66%)' }}
      />
      <div aria-hidden="true" className="pointer-events-none absolute -left-24 top-1/3 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl glow-orb" />
      <div aria-hidden="true" className="pointer-events-none absolute -right-24 bottom-10 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl glow-orb" />

      {/* Broadcast bar */}
      <div className="relative z-20 border-b border-white/10 bg-navy-950/35 backdrop-blur">
        <div className="container-x flex h-11 items-center justify-between gap-4 text-[10px] uppercase tracking-[0.2em] text-white/45">
          <div className="flex items-center gap-3">
            <span className="inline-flex items-center gap-1.5 rounded-sm bg-rose-500 px-2 py-1 font-bold tracking-[0.18em] text-white">
              <span className="live-eq text-white" aria-hidden="true"><span /><span /><span /><span /><span /></span> Live
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

      <div className="ticker relative z-20 overflow-hidden border-b border-white/10 bg-navy-950/25">
        <div className="ticker-track gap-8 py-1.5 font-mono text-[9px] uppercase tracking-[0.24em] text-white/40">
          {[0, 1].map((dup) => (
            <span key={dup} className="flex items-center gap-8" aria-hidden={dup === 1}>
              {tickerItems.map((item) => (
                <span key={item} className="inline-flex items-center gap-2 whitespace-nowrap">
                  <span className="h-1 w-1 rounded-full bg-brand-400/80" />
                  {item}
                </span>
              ))}
            </span>
          ))}
        </div>
      </div>

      <div className="container-x relative z-10 flex flex-1 flex-col justify-center py-10 sm:py-14 lg:py-20">
        <div className="grid items-center gap-12 lg:grid-cols-[1.08fr_0.92fr]">
          <div key={active} className="max-w-2xl">
            <span className="slide-rise inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-200 backdrop-blur">
              <span className="relative flex h-2 w-2">
                <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60" />
                <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-400" />
              </span>
              {slide.eyebrow}
            </span>

            <h1 className="slide-rise slide-delay-1 h1 mt-5 text-white sm:mt-6">
              {slide.title}
              <span className="text-gradient">{slide.accent}</span>
            </h1>

            <p className="slide-rise slide-delay-2 mt-5 max-w-xl text-[1rem] leading-relaxed text-white/75 sm:mt-6 sm:text-[1.1rem]">{slide.lead}</p>
            <p className="slide-rise slide-delay-3 mt-4 flex items-start gap-2 border-l-2 border-brand-400/70 pl-3 font-mono text-[10px] uppercase leading-relaxed tracking-[0.12em] text-brand-200/80 sm:text-[11px]">
              <span className="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-300 orbit-pulse" />
              <span className="caption-type">{slide.note}</span>
            </p>

            <div className="slide-rise slide-delay-3 mt-7 flex flex-col gap-3 min-[420px]:flex-row min-[420px]:flex-wrap min-[420px]:items-center">
              <Link to={slide.to} className="btn-primary justify-center">
                {slide.cta} <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/portfolio" className="btn inline-flex justify-center border border-white/20 bg-white/5 text-white backdrop-blur hover:bg-white/10">
                Watch our work <ArrowUpRight className="h-4 w-4" />
              </Link>
            </div>

            <Link
              to={slide.to}
              className="slide-rise slide-delay-4 group mt-7 flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.05] p-2.5 backdrop-blur transition hover:border-brand-400/50 hover:bg-white/[0.08] lg:hidden"
            >
                <span className="media-glow reel-frame relative h-16 w-20 shrink-0 overflow-hidden rounded-xl">
                  <img key={slide.image} src={asset(slide.image)} alt="" className={cn('h-full w-full', frameFit(slide.image))} />
                  <span className="reel-sheen absolute inset-0" />
                </span>
              <div className="min-w-0 flex-1">
                <p className="font-mono text-[8px] uppercase tracking-[0.2em] text-brand-300">Now showing</p>
                <p className="mt-1 truncate font-display text-[13px] font-semibold text-white">{slide.channel}</p>
                <p className="mt-0.5 truncate text-[10px] text-white/45">Jinja, Uganda · engineering studio</p>
              </div>
              <span className="shrink-0 rounded-full bg-emerald-400/10 px-2 py-1 font-mono text-[8px] uppercase tracking-widest text-emerald-300">on air</span>
            </Link>

            <div className="slide-rise slide-delay-5 mt-7 grid max-w-lg grid-cols-3 gap-2.5 border-t border-white/10 pt-5 sm:mt-10 sm:gap-4 sm:pt-6">
              {[
                { label: 'Projects delivered', value: `${stats.projects || 7}+`, to: '/portfolio' },
                { label: 'Organisations served', value: `${stats.clients || 6}+`, to: '/about' },
                { label: 'Service lines', value: `${stats.services || services.length || 14}`, to: '/services' },
              ].map((item) => (
                <Link key={item.label} to={item.to} className="group">
                  <p className="font-mono text-[8px] uppercase leading-relaxed tracking-[0.14em] text-white/35 transition group-hover:text-brand-200 sm:text-[9px] sm:tracking-[0.18em]">{item.label}</p>
                  <p className="mt-1 font-display text-xl font-semibold text-white transition group-hover:text-brand-300 sm:text-2xl">{item.value}</p>
                </Link>
              ))}
            </div>
          </div>

          <div className="relative hidden lg:block">
            <div className="hero-parallax relative ml-auto w-full max-w-[420px]">
              <svg viewBox="0 0 420 460" className="pointer-events-none absolute -inset-10 h-[calc(100%+80px)] w-[calc(100%+80px)]" fill="none" aria-hidden="true">
                <g className="orbit-spin" style={{ transformOrigin: '50% 50%' }}>
                  <circle cx="210" cy="230" r="184" stroke="#7dd3fc" strokeOpacity="0.18" strokeDasharray="3 12" />
                  <circle cx="210" cy="46" r="5" fill="#a5e4ff" fillOpacity="0.9" />
                  <circle cx="394" cy="230" r="3" fill="#4fb6ff" fillOpacity="0.7" />
                </g>
                <g className="orbit-reverse" style={{ transformOrigin: '50% 50%' }}>
                  <circle cx="210" cy="230" r="150" stroke="#4fb6ff" strokeOpacity="0.14" strokeDasharray="18 26" />
                  <circle cx="60" cy="230" r="3.5" fill="#7dd3fc" fillOpacity="0.8" />
                </g>
              </svg>
              <div className="hero-depth rounded-2xl border border-white/15 bg-navy-950/55 p-3 shadow-[0_50px_120px_-40px_rgba(3,26,46,.95)] backdrop-blur-xl">
                <div className="flex items-center justify-between border-b border-white/10 pb-2.5">
                  <span className="font-mono text-[9px] uppercase tracking-[0.2em] text-white/40">Now showing</span>
                  <span className="inline-flex items-center gap-1.5 font-mono text-[9px] uppercase tracking-[0.16em] text-emerald-300">
                    <span className="live-eq text-emerald-300" aria-hidden="true"><span /><span /><span /><span /><span /></span> on air
                  </span>
                </div>

                <Link to={slide.to} className="media-glow reel-frame group relative mt-3 block overflow-hidden rounded-xl border border-white/10">
                  <img key={slide.image} src={asset(slide.image)} alt="" className={cn('h-44 w-full', frameFit(slide.image))} />
                  <span className="reel-sheen absolute inset-0 z-[7]" />
                  <span className="frame-corner frame-corner-tl" aria-hidden="true" />
                  <span className="frame-corner frame-corner-tr" aria-hidden="true" />
                  <span className="frame-corner frame-corner-bl" aria-hidden="true" />
                  <span className="frame-corner frame-corner-br" aria-hidden="true" />
                  <span className="absolute inset-0 z-[6] grid place-items-center bg-navy-950/25 opacity-0 transition group-hover:opacity-100">
                    <span className="grid h-11 w-11 place-items-center rounded-full border border-white/40 bg-white/15 text-white backdrop-blur">
                      <ArrowUpRight className="h-5 w-5" />
                    </span>
                  </span>
                </Link>

                <div className="mt-3 flex items-center justify-between gap-3">
                  <div>
                    <Link to={slide.to} className="font-display text-sm font-semibold text-white transition hover:text-brand-300">{slide.channel}</Link>
                    <p className="mt-0.5 text-[10px] text-white/40">Jinja, Uganda · engineering studio</p>
                  </div>
                  <Link to={slide.to} aria-label={`Open ${slide.channel}`} className="grid h-9 w-9 place-items-center rounded-full border border-white/15 bg-white/5 text-brand-300 transition hover:border-brand-400/60 hover:text-white">
                    <ShieldCheck className="h-4 w-4" />
                  </Link>
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
                  <Link
                    to="/services"
                    className="mt-1 flex items-center justify-center gap-1 rounded-lg border border-brand-400/30 bg-brand-500/10 px-3 py-2 text-[10.5px] font-semibold text-brand-200 transition hover:border-brand-400/60 hover:bg-brand-500/20 hover:text-white"
                  >
                    View all {services.length || 14} services <ArrowRight className="h-3 w-3" />
                  </Link>
                </div>
              </div>

              <div className="float-soft absolute -bottom-4 -left-5 rounded-xl border border-white/15 bg-navy-900/90 px-4 py-3 shadow-soft backdrop-blur">
                <p className="font-mono text-[8px] uppercase tracking-[0.2em] text-white/35">Uptime</p>
                <p className="mt-1 font-display text-lg font-semibold text-emerald-300">99.98%</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Editorial chapter rail, visible on large cinema screens */}
      <div className="absolute right-1.5 top-1/2 z-20 hidden -translate-y-1/2 flex-col gap-2 2xl:flex" aria-label="Showcase chapters">
        {slides.map((item, index) => (
          <button
            key={item.channel}
            type="button"
            onClick={() => go(index)}
            className={cn(
              'hero-chapter group flex items-center gap-2 rounded-full border border-white/10 bg-navy-950/70 px-2 py-1.5 text-left backdrop-blur transition',
              index === active ? 'hero-chapter-active' : 'text-white/35 hover:text-white/70',
            )}
            aria-label={`Go to ${item.channel}`}
            aria-current={index === active}
          >
            <span className="font-mono text-[8px]">{String(index + 1).padStart(2, '0')}</span>
            <span className="max-w-0 overflow-hidden whitespace-nowrap text-[8px] uppercase tracking-[0.16em] transition-all group-hover:max-w-[120px]">
              {item.channel}
            </span>
          </button>
        ))}
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
