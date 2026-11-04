import { Link } from 'react-router-dom';
import { LOGO } from '../lib/env';

export function Logo({ dark = false, compact = false }: { dark?: boolean; compact?: boolean }) {
  return (
    <Link to="/" className="group inline-flex items-center gap-3" aria-label="Reagansoft Innovation Limited — Home">
      <span className="relative grid h-11 w-11 shrink-0 place-items-center overflow-hidden rounded-2xl bg-white shadow-[0_10px_24px_-10px_rgba(8,59,102,.45)] ring-1 ring-brand-100">
        <img src={LOGO} alt="" width={44} height={44} className="h-full w-full object-cover" />
      </span>
      {!compact && (
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
