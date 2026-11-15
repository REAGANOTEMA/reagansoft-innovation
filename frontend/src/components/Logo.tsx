import { Link } from 'react-router-dom';
import { LOGO_NEW, LOGO_NEW_WHITE, LOGO_REAL, LOGO_REAL_WHITE } from '../lib/env';

type LogoVariant = 'new' | 'real';

export function Logo({
  dark = false,
  compact = false,
  variant = 'new',
}: {
  dark?: boolean;
  compact?: boolean;
  variant?: LogoVariant;
}) {
  const isReal = variant === 'real';
  const src = isReal ? (dark ? LOGO_REAL_WHITE : LOGO_REAL) : dark ? LOGO_NEW_WHITE : LOGO_NEW;

  return (
    <Link to="/" className="group inline-flex items-center gap-3" aria-label="Reagansoft Innovation Limited, Home">
      <img
        src={src}
        alt={compact ? 'Reagansoft Innovation Limited' : ''}
        width={isReal ? 210 : 48}
        height={isReal ? 144 : 48}
        className={
          isReal
            ? 'h-20 w-auto max-w-[210px] shrink-0 object-contain drop-shadow-[0_2px_18px_rgba(30,158,232,.22)] transition duration-500 group-hover:drop-shadow-[0_2px_26px_rgba(30,158,232,.5)]'
            : 'h-12 w-12 shrink-0 object-contain drop-shadow-[0_2px_14px_rgba(30,158,232,.22)] transition duration-500 group-hover:scale-105 group-hover:drop-shadow-[0_2px_22px_rgba(30,158,232,.55)]'
        }
      />
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
