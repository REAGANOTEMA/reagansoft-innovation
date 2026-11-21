import { ArrowUpRight, MapPin, Navigation, Route } from 'lucide-react';
import { Link } from 'react-router-dom';
import { cn } from '../lib/cn';
import { media, mediaAlt } from '../lib/media';
import { useSite } from '../lib/site';
import { Reveal } from './Reveal';
import { Tilt } from './Tilt';

/** A quiet route-map illustration that gives the field story its own graphic signature. */
export function FieldRouteArt({ className, idPrefix = 'field-route' }: { className?: string; idPrefix?: string }) {
  const line = `${idPrefix}-line`;
  const glow = `${idPrefix}-glow`;

  return (
    <svg viewBox="0 0 320 190" className={cn('h-auto w-full', className)} fill="none" aria-hidden="true">
      <defs>
        <linearGradient id={line} x1="0" y1="0" x2="1" y2="1">
          <stop stopColor="#7dd3fc" />
          <stop offset="1" stopColor="#4fb6ff" />
        </linearGradient>
        <filter id={glow} x="-50%" y="-50%" width="200%" height="200%">
          <feGaussianBlur stdDeviation="5" />
        </filter>
      </defs>

      <g stroke="#7dd3fc" strokeOpacity="0.1">
        {Array.from({ length: 9 }).map((_, index) => (
          <line key={`v${index}`} x1={index * 40} y1="0" x2={index * 40} y2="190" />
        ))}
        {Array.from({ length: 6 }).map((_, index) => (
          <line key={`h${index}`} x1="0" y1={index * 38} x2="320" y2={index * 38} />
        ))}
      </g>

      <path
        d="M26 158C70 158 80 116 120 116s58-46 98-46 54-26 76-26"
        stroke={`url(#${line})`}
        strokeWidth="7"
        strokeLinecap="round"
        opacity="0.35"
        filter={`url(#${glow})`}
      />
      <path
        d="M26 158C70 158 80 116 120 116s58-46 98-46 54-26 76-26"
        stroke={`url(#${line})`}
        strokeWidth="1.8"
        strokeDasharray="6 7"
        strokeLinecap="round"
        opacity="0.9"
      />

      {[
        { x: 26, y: 158, label: 'STUDIO' },
        { x: 120, y: 116, label: 'SITE' },
        { x: 218, y: 70, label: 'TRAINING' },
        { x: 294, y: 44, label: 'CLIENT' },
      ].map((point, index) => (
        <g key={point.label}>
          <circle cx={point.x} cy={point.y} r={index === 3 ? 9 : 7} fill="#062944" stroke="#7dd3fc" strokeOpacity="0.5" />
          <circle cx={point.x} cy={point.y} r={index === 3 ? 3.5 : 2.5} fill={index === 3 ? '#a5e4ff' : '#4fb6ff'} />
          <text
            x={point.x}
            y={point.y + 21}
            textAnchor="middle"
            fontFamily="JetBrains Mono, monospace"
            fontSize="7"
            letterSpacing="1.2"
            fill="#7dd3fc"
            fillOpacity="0.75"
          >
            {point.label}
          </text>
        </g>
      ))}

      <g transform="translate(268 8)" opacity="0.75">
        <circle cx="12" cy="12" r="11" stroke="#7dd3fc" strokeOpacity="0.35" />
        <path d="M12 3v18M3 12h18" stroke="#7dd3fc" strokeOpacity="0.4" strokeWidth="0.7" />
        <path d="M12 4l2.4 5.6L12 12 9.6 9.6z" fill="#a5e4ff" />
        <text x="12" y="1" textAnchor="middle" fontFamily="JetBrains Mono, monospace" fontSize="5" fill="#7dd3fc">N</text>
      </g>
    </svg>
  );
}

/**
 * A cinematic field story built around the company Yamaha. The photography
 * sits in a lit, bracketed frame and the words underneath stay human: this is
 * about showing up in person, not about selling a motorcycle.
 */
export function FieldStory() {
  const { settings } = useSite();
  const ceo = settings.company_founder || 'Reagan Otema';

  return (
    <section className="relative isolate overflow-hidden bg-navy-950 py-20 text-white sm:py-24">
      <div className="aurora" aria-hidden="true" />
      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" aria-hidden="true" />
      <div className="glow-orb pointer-events-none absolute -right-28 top-1/4 h-80 w-80 rounded-full bg-brand-500/20 blur-3xl" aria-hidden="true" />
      <div className="glow-orb pointer-events-none absolute -left-24 bottom-0 h-72 w-72 rounded-full bg-cyan-400/10 blur-3xl" aria-hidden="true" />

      <div className="container-x relative grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16">
        <Reveal>
          <Tilt max={4}>
            <div className="relative">
              <div className="pointer-events-none absolute -right-6 -top-6 h-28 w-28 rounded-full border border-brand-300/25 depth-ring sm:h-36 sm:w-36" aria-hidden="true" />
              <figure className="depth-card relative overflow-hidden rounded-[30px] border border-white/10 p-2.5">
              <div className="group relative overflow-hidden rounded-[23px] border border-white/10 bg-navy-900">
                <img
                  src={media('ceoBike')}
                  alt={mediaAlt('ceoBike')}
                  className="block aspect-square w-full object-contain object-center"
                  loading="lazy"
                  decoding="async"
                />
                <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/10 to-transparent" />
                <div className="screen-sheen" aria-hidden="true" />
                <div className="frame-corner frame-corner-tl" aria-hidden="true" />
                <div className="frame-corner frame-corner-tr" aria-hidden="true" />
                <div className="frame-corner frame-corner-bl" aria-hidden="true" />
                <div className="frame-corner frame-corner-br" aria-hidden="true" />

                <div className="absolute left-5 top-5 flex items-center gap-2 rounded-full border border-white/15 bg-navy-950/65 px-3 py-1.5 backdrop-blur-xl">
                  <span className="relative flex h-1.5 w-1.5">
                    <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-300 opacity-60 motion-reduce:animate-none" />
                    <span className="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand-300" />
                  </span>
                  <span className="font-mono text-[9px] font-semibold uppercase tracking-[0.16em] text-white/85">
                    Field log · 001 / Jinja
                  </span>
                </div>

                <div className="absolute inset-x-5 bottom-5">
                  <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-brand-300">On the road with {ceo}</p>
                  <p className="mt-1 max-w-md font-display text-lg font-semibold leading-snug text-white sm:text-xl">
                    A working bike for a working studio.
                  </p>
                </div>
              </div>
              <figcaption className="px-4 pb-3 pt-4">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <span className="font-mono text-[9px] uppercase tracking-[0.18em] text-white/45">
                    {ceo} &amp; the company Yamaha
                  </span>
                  <span className="font-mono text-[9px] uppercase tracking-[0.18em] text-brand-300/80">
                    Install · Train · Support
                  </span>
                </div>
                <p className="mt-3 border-t border-white/10 pt-3 text-[12px] leading-relaxed text-white/65">
                  Our team is willing to reach our clients as far as they are. We will come to install the system,
                  train the people who will use it, and stay close for whatever comes next.
                </p>
              </figcaption>
              </figure>
              <div className="float-soft absolute -bottom-5 -left-3 hidden rounded-2xl border border-white/15 bg-navy-900/90 px-4 py-3 shadow-lift backdrop-blur sm:block">
                <p className="font-mono text-[8px] uppercase tracking-[0.2em] text-brand-300">Field note</p>
                <p className="mt-1 text-[11px] font-semibold text-white">We come to you</p>
              </div>
              <div className="float-soft absolute -right-3 top-1/3 hidden rounded-2xl border border-white/15 bg-navy-900/90 px-3 py-2 font-mono text-[9px] uppercase tracking-[0.16em] text-white/70 shadow-lift backdrop-blur sm:block" style={{ animationDelay: '-3s' }}>
                Install · Train · Support
              </div>
            </div>
          </Tilt>
        </Reveal>

        <Reveal delay={120}>
          <span className="eyebrow text-brand-300">Out of the studio</span>
          <h2 className="h2 mt-3 text-balance text-white">
            The CEO, the Yamaha and the road to your door.
          </h2>
          <p className="mt-5 text-[14px] leading-relaxed text-white/70">
            Most of the useful conversations in this business do not happen around a boardroom table. They happen at a school gate,
            in a shop doorway, or in a hotel lobby while someone explains the notebook they still use to keep everything straight.
            {ceo.split(' ')[0]} goes to those places himself.
          </p>
          <p className="mt-4 text-[14px] leading-relaxed text-white/70">
            The company Yamaha is painted in Reagansoft blue, which is partly practical and partly a promise. Our teams are willing to
            reach our clients wherever they are, to install the systems, train the people who use them, and handle all the other little
            things that make a launch work. When the bike pulls up, it means the person who shaped your system is close enough to see it
            working with your own team.
          </p>

          <div className="mt-6 flex flex-wrap gap-2">
            <span className="badge border border-white/15 bg-white/5 text-white/75">
              <MapPin className="mr-1.5 h-3.5 w-3.5 text-brand-300" /> Site visits
            </span>
            <span className="badge border border-white/15 bg-white/5 text-white/75">
              <Route className="mr-1.5 h-3.5 w-3.5 text-brand-300" /> Training on site
            </span>
            <span className="badge border border-white/15 bg-white/5 text-white/75">
              <Navigation className="mr-1.5 h-3.5 w-3.5 text-brand-300" /> As far as you are
            </span>
          </div>

          <div className="relative mt-7 overflow-hidden rounded-2xl border border-white/10 bg-white/5 p-5 sm:p-6">
            <FieldRouteArt idPrefix="field-story-route" className="pointer-events-none absolute -bottom-6 -right-6 w-[240px] opacity-60 sm:w-[280px]" />
            <div className="relative">
              <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-brand-300">A note from the road</p>
              <blockquote className="mt-3 max-w-md font-display text-[17px] font-medium leading-snug text-white sm:text-lg">
                “Software makes more sense when you have stood where the work actually happens.”
              </blockquote>
              <p className="mt-3 text-[11.5px] text-white/50">
                — {ceo}, Founder &amp; CEO
              </p>
            </div>
          </div>

          <Link
            to="/contact"
            className="group/link mt-7 inline-flex items-center gap-2 text-[13px] font-semibold text-white transition hover:text-brand-200"
          >
            Arrange a visit or a site survey
            <ArrowUpRight className="h-4 w-4 transition-transform group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5" />
          </Link>
        </Reveal>
      </div>
    </section>
  );
}
