import { Link } from 'react-router-dom';
import { LOGO_NEW, LOGO_NEW_WHITE, LOGO_REAL, LOGO_REAL_WHITE } from '../lib/env';
import { useSite } from '../lib/site';

type LogoVariant = 'new' | 'real';

const PUZZLE_PIECES = [
  'inset(0 50% 50% 0)',
  'inset(0 0 50% 50%)',
  'inset(50% 50% 0 0)',
  'inset(50% 0 0 50%)',
];

export function Logo({
  dark = false,
  compact = false,
  variant = 'new',
}: {
  dark?: boolean;
  compact?: boolean;
  variant?: LogoVariant;
}) {
  const { settings } = useSite();
  const isReal = variant === 'real';
  const configured = isReal
    ? dark
      ? settings.logo_real_dark_url
      : settings.logo_real_url
    : dark
      ? settings.logo_mark_dark_url
      : settings.logo_mark_url;
  const fallback = isReal ? (dark ? LOGO_REAL_WHITE : LOGO_REAL) : dark ? LOGO_NEW_WHITE : LOGO_NEW;
  const src = configured || fallback;

  return (
    <Link to="/" className="group inline-flex items-center gap-3" aria-label="Reagansoft Innovation Limited, Home">
      {isReal ? (
        <img
          src={src}
          alt={compact ? 'Reagansoft Innovation Limited' : ''}
          width={210}
          height={144}
          className="h-20 w-auto max-w-[210px] shrink-0 object-contain drop-shadow-[0_2px_18px_rgba(30,158,232,.22)] transition duration-500 group-hover:drop-shadow-[0_2px_26px_rgba(30,158,232,.5)]"
        />
      ) : (
        <span className="logo-puzzle relative inline-block h-12 w-12 shrink-0">
          <img
            src={src}
            alt={compact ? 'Reagansoft Innovation Limited' : ''}
            width={48}
            height={48}
            className="h-12 w-12 object-contain drop-shadow-[0_2px_14px_rgba(30,158,232,.22)] transition duration-500 group-hover:scale-105 group-hover:drop-shadow-[0_2px_22px_rgba(30,158,232,.55)]"
          />
          {PUZZLE_PIECES.map((clip, index) => (
            <img
              key={clip}
              src={src}
              alt=""
              aria-hidden="true"
              className="logo-puzzle-piece absolute inset-0 h-12 w-12 object-contain"
              style={{ clipPath: clip, animationDelay: `${index * 90}ms` }}
            />
          ))}
        </span>
      )}
      {!compact && !isReal && (
        <span className="leading-tight">
          <span className={`block font-display text-[15px] font-semibold tracking-tight ${dark ? 'text-white' : 'text-navy-900'}`}>
            Reagansoft <span className="text-brand-500">Innovation</span>
          </span>
          <span className={`mt-0.5 block font-mono text-[9px] uppercase tracking-[0.2em] ${dark ? 'text-white/50' : 'text-ink-faint'}`}>
            Limited · Jinja, Uganda
          </span>
        </span>
      )}
    </Link>
  );
}
