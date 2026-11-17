import { ArrowUpRight, BadgeCheck, Building2, Clock3, GraduationCap, ShieldCheck } from 'lucide-react';
import { Link } from 'react-router-dom';
import { Reveal } from './Reveal';
import { Tilt } from './Tilt';
import { SectionHeading } from './UI';
import { media, mediaAlt, type MediaKey } from '../lib/media';
import { cn } from '../lib/cn';

type ArtVariant = 'degree' | 'studio';

/** Isometric gradient art that gives each proof card its own 3D signature. */
function DepthArt({ variant, className }: { variant: ArtVariant; className?: string }) {
  const id = `proof-${variant}`;

  return (
    <svg viewBox="0 0 200 150" className={cn('h-auto w-full', className)} fill="none" aria-hidden="true">
      <defs>
        <linearGradient id={`${id}-top`} x1="0" y1="0" x2="1" y2="1">
          <stop stopColor="#e0f2fe" />
          <stop offset="0.55" stopColor="#7dd3fc" />
          <stop offset="1" stopColor="#2563eb" />
        </linearGradient>
        <linearGradient id={`${id}-left`} x1="0" y1="0" x2="0" y2="1">
          <stop stopColor="#1e6fae" />
          <stop offset="1" stopColor="#0a3457" />
        </linearGradient>
        <linearGradient id={`${id}-right`} x1="0" y1="0" x2="1" y2="1">
          <stop stopColor="#0f81c6" />
          <stop offset="1" stopColor="#08304f" />
        </linearGradient>
        <filter id={`${id}-blur`} x="-60%" y="-80%" width="220%" height="260%">
          <feGaussianBlur stdDeviation="8" />
        </filter>
      </defs>

      <ellipse cx="100" cy="124" rx="58" ry="13" fill="#38bdf8" opacity="0.3" filter={`url(#${id}-blur)`} />

      {variant === 'degree' ? (
        <g>
          <path d="M100 104 158 72 100 40 42 72Z" fill={`url(#${id}-left)`} stroke="#7dd3fc" strokeOpacity="0.5" />
          <path d="M42 72 100 104 100 116 42 84Z" fill={`url(#${id}-right)`} />
          <path d="M158 72 100 104 100 116 158 84Z" fill="#062944" />
          <path d="M100 84 140 61 100 38 60 61Z" fill={`url(#${id}-top)`} stroke="#e0f2fe" strokeOpacity="0.55" />
          <path d="M60 61 100 84 100 94 60 71Z" fill={`url(#${id}-left)`} />
          <path d="M140 61 100 84 100 94 140 71Z" fill={`url(#${id}-right)`} />
          <path d="M100 12 122 24 122 44 100 56 78 44 78 24Z" fill={`url(#${id}-top)`} stroke="#e0f2fe" strokeOpacity="0.65" />
          <path d="M100 22 113 29 100 36 87 29Z" fill="#ffffff" fillOpacity="0.32" />
          <path d="M122 44c9 7 9 15 3 21" stroke="#f5cf83" strokeWidth="2" strokeLinecap="round" />
          <circle cx="125" cy="66" r="3" fill="#f5cf83" />
        </g>
      ) : (
        <g>
          <path d="M100 122 166 84 100 46 34 84Z" fill="#0a3557" stroke="#7dd3fc" strokeOpacity="0.4" />
          <path d="M67 65 133 103M134 65 68 103M100 46 100 122" stroke="#7dd3fc" strokeOpacity="0.18" />
          <path d="M50 75 50 91M150 75 150 91" stroke="#7dd3fc" strokeOpacity="0.4" strokeDasharray="3 4" />
          <path d="M58 78 72 70 86 78 72 86Z" fill={`url(#${id}-top)`} stroke="#e0f2fe" strokeOpacity="0.5" />
          <path d="M114 78 128 70 142 78 128 86Z" fill={`url(#${id}-top)`} stroke="#e0f2fe" strokeOpacity="0.5" />
          <path d="M72 86 72 95M128 86 128 95" stroke="#7dd3fc" strokeOpacity="0.5" />
          <path d="M100 52 122 64 100 76 78 64Z" fill={`url(#${id}-top)`} stroke="#e0f2fe" strokeOpacity="0.65" />
          <path d="M78 64 100 76 100 98 78 86Z" fill={`url(#${id}-left)`} />
          <path d="M122 64 100 76 100 98 122 86Z" fill={`url(#${id}-right)`} />
          <path d="M86 64 114 64M100 52 100 40" stroke="#e0f2fe" strokeOpacity="0.45" />
          <circle cx="100" cy="34" r="4" fill="#7dd3fc" />
        </g>
      )}
    </svg>
  );
}

interface ProofCard {
  image: MediaKey;
  portrait?: boolean;
  overline: string;
  caption: string;
  title: string;
  body: string;
  proof: string;
  to: string;
  link: string;
  icon: typeof GraduationCap;
  art: ArtVariant;
}

const proofCards: ProofCard[] = [
  {
    image: 'founderGraduation',
    portrait: true,
    overline: 'Engineer-led · BYU–Idaho',
    caption: 'Founder & lead developer',
    title: 'Built by an engineer, not a sales desk.',
    body:
      'Our founder holds a BSc in Software Development from BYU–Idaho. That training shows up in the work: clear architecture, honest timelines and code your team can truly own — with no middle layer translating every conversation.',
    proof: 'Qualified leadership · Hands-on delivery',
    to: '/about',
    link: 'Meet the founder',
    icon: GraduationCap,
    art: 'degree',
  },
  {
    image: 'officeVisitors',
    overline: 'Jinja studio · In person',
    caption: 'Visiting partners at the main office',
    title: 'Meet the people who build your system.',
    body:
      'Partners who visit our Jinja office sit with the same designers, developers and project leads working on their build. You see the studio, ask direct questions and hear from the people responsible — before, during and after launch.',
    proof: 'One accountable team · No hand-offs',
    to: '/contact',
    link: 'Arrange a visit',
    icon: Building2,
    art: 'studio',
  },
];

const assurances = [
  {
    icon: ShieldCheck,
    title: 'You own the work',
    text: 'Full rights to your code, data and accounts. No lock-in, ever.',
  },
  {
    icon: BadgeCheck,
    title: 'Scope before build',
    text: 'One clear quote and agreed milestones before a line of code is written.',
  },
  {
    icon: Clock3,
    title: 'Replies that land',
    text: 'Direct access to the team and a reply within one working day.',
  },
];

/** Premium proof band shown on every public page. */
export function PremiumProof() {
  return (
    <section className="relative isolate overflow-hidden bg-navy-950 py-20 sm:py-24">
      <div className="aurora" aria-hidden="true" />
      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" aria-hidden="true" />
      <div className="glow-orb pointer-events-none absolute -left-24 top-10 h-72 w-72 rounded-full bg-brand-500/25 blur-3xl" aria-hidden="true" />
      <div className="glow-orb pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl" aria-hidden="true" />

      <div className="container-x relative">
        <Reveal>
          <SectionHeading
            dark
            align="center"
            eyebrow="Proof behind the promise"
            title="Real people. Real engineering. Real accountability."
            lead="Anyone can promise a good website. We would rather show you the team, the qualifications and the studio that will actually deliver yours."
          />
        </Reveal>

        <div className="mt-12 grid gap-6 lg:grid-cols-2">
          {proofCards.map((card, index) => {
            const Icon = card.icon;
            return (
              <Reveal key={card.image} delay={index * 110}>
                <Tilt className="h-full" max={4}>
                  <article className="depth-card relative flex h-full flex-col overflow-hidden rounded-[28px] border border-white/10 p-2.5">
                    <div className="group relative overflow-hidden rounded-[21px] border border-white/10 bg-navy-900">
                      {card.portrait && (
                        <img
                          src={media(card.image)}
                          alt=""
                          aria-hidden="true"
                          className="pointer-events-none absolute inset-0 h-full w-full scale-125 object-cover opacity-45 blur-2xl"
                          loading="lazy"
                          decoding="async"
                        />
                      )}
                      <img
                        src={media(card.image)}
                        alt={mediaAlt(card.image)}
                        className={cn(
                          'relative block w-full transition duration-700 ease-out group-hover:scale-[1.04]',
                          card.portrait ? 'h-[300px] object-contain object-center sm:h-[340px]' : 'h-[300px] object-cover object-center sm:h-[340px]',
                        )}
                        loading="lazy"
                        decoding="async"
                      />
                      <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-950 via-navy-950/20 to-transparent" />
                      <div className="frame-corner frame-corner-tl" aria-hidden="true" />
                      <div className="frame-corner frame-corner-tr" aria-hidden="true" />
                      <div className="frame-corner frame-corner-bl" aria-hidden="true" />
                      <div className="frame-corner frame-corner-br" aria-hidden="true" />

                      <div className="absolute left-4 top-4 flex items-center gap-2 rounded-full border border-white/15 bg-navy-950/65 px-3 py-1.5 backdrop-blur-xl">
                        <Icon className="h-3.5 w-3.5 text-brand-300" aria-hidden="true" />
                        <span className="font-mono text-[9px] font-semibold uppercase tracking-[0.16em] text-white/85">
                          {card.overline}
                        </span>
                      </div>

                      <div className="absolute inset-x-4 bottom-4 flex items-end justify-between gap-3">
                        <p className="font-display text-base font-semibold text-white sm:text-lg">{card.caption}</p>
                        <div className="relative hidden h-[70px] w-[92px] shrink-0 sm:block">
                          <div className="depth-floor absolute inset-2" aria-hidden="true" />
                          <div className="depth-ring absolute inset-3 rounded-full" aria-hidden="true" />
                          <div className="float-soft absolute inset-0">
                            <DepthArt variant={card.art} />
                          </div>
                        </div>
                      </div>
                    </div>

                    <div className="flex flex-1 flex-col px-4 pb-4 pt-6 sm:px-6 sm:pb-5">
                      <h3 className="font-display text-xl font-semibold text-white sm:text-2xl">{card.title}</h3>
                      <p className="mt-3 text-[13.5px] leading-relaxed text-white/65">{card.body}</p>
                      <div className="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-4">
                        <span className="font-mono text-[9px] uppercase tracking-[0.16em] text-brand-300/90">
                          {card.proof}
                        </span>
                        <Link
                          to={card.to}
                          className="group/link inline-flex items-center gap-1.5 text-[12px] font-semibold text-white transition hover:text-brand-300"
                        >
                          {card.link}
                          <ArrowUpRight className="h-3.5 w-3.5 transition-transform group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5" />
                        </Link>
                      </div>
                    </div>
                  </article>
                </Tilt>
              </Reveal>
            );
          })}
        </div>

        <div className="mt-6 grid gap-3 sm:grid-cols-3">
          {assurances.map((item, index) => {
            const Icon = item.icon;
            return (
              <Reveal key={item.title} delay={index * 70}>
                <Tilt className="h-full" max={3}>
                  <div className="depth-card relative flex h-full items-start gap-3 overflow-hidden rounded-2xl border border-white/10 px-5 py-4">
                    <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-brand-300/25 bg-brand-400/10">
                      <Icon className="h-4 w-4 text-brand-300" aria-hidden="true" />
                    </span>
                    <div>
                      <p className="text-[13px] font-semibold text-white">{item.title}</p>
                      <p className="mt-1 text-[11.5px] leading-relaxed text-white/55">{item.text}</p>
                    </div>
                  </div>
                </Tilt>
              </Reveal>
            );
          })}
        </div>
      </div>
    </section>
  );
}
