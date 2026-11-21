import { useEffect, type ReactNode } from 'react';
import { AlertTriangle, ChevronLeft, ChevronRight, Inbox, Loader2, X } from 'lucide-react';
import { cn } from '../lib/cn';
import { LOGO_REAL } from '../lib/env';
import { toneClasses, type Tone } from '../lib/format';

export function SectionHeading({
  eyebrow,
  title,
  lead,
  align = 'left',
  dark = false,
  className,
}: {
  eyebrow?: string;
  title: ReactNode;
  lead?: ReactNode;
  align?: 'left' | 'center';
  dark?: boolean;
  className?: string;
}) {
  return (
    <div className={cn('max-w-2xl', align === 'center' && 'mx-auto text-center', className)}>
      {eyebrow && <span className={cn('eyebrow', dark && 'text-brand-300')}>{eyebrow}</span>}
      <h2 className={cn('h2 mt-3 text-balance', dark ? 'text-white' : 'text-navy-900')}>{title}</h2>
      {lead && <p className={cn('lead mt-4 text-pretty', dark ? 'text-white/65' : 'text-ink-muted')}>{lead}</p>}
    </div>
  );
}

export function Badge({ children, tone = 'gray', className }: { children: ReactNode; tone?: Tone; className?: string }) {
  return <span className={cn('badge', toneClasses[tone], className)}>{children}</span>;
}

export function Spinner({ className }: { className?: string }) {
  return <Loader2 className={cn('h-5 w-5 animate-spin text-brand-600', className)} aria-hidden="true" />;
}

/** The real company logo arriving piece by piece, then locking into place. */
export function LogoAssembly({ width = 116, className }: { width?: number; className?: string }) {
  return (
    <span
      className={cn('logo-assembly', className)}
      style={{ width, height: Math.round(width * 0.69) }}
      aria-hidden="true"
    >
      <span className="logo-assembly-halo" />
      <img className="logo-assembly-watermark" src={LOGO_REAL} alt="" />
      {[1, 2, 3, 4].map((piece) => (
        <img key={piece} className={`logo-assembly-piece logo-assembly-piece-${piece}`} src={LOGO_REAL} alt="" />
      ))}
      <img className="logo-assembly-complete" src={LOGO_REAL} alt="" />
      <span className="logo-assembly-scan" />
    </span>
  );
}

export function LoadingBlock({ label = 'Loading…', className }: { label?: string; className?: string }) {
  return (
    <div className={cn('relative flex min-h-[240px] flex-col items-center justify-center gap-5 overflow-hidden rounded-3xl border border-brand-100 bg-gradient-to-b from-white via-brand-50/50 to-white px-6 text-ink-muted shadow-soft', className)}>
      <span className="pointer-events-none absolute inset-0 bg-grid opacity-30" aria-hidden="true" />
      <span className="lens-flare pointer-events-none absolute -top-16 left-1/2 h-48 w-48 -translate-x-1/2 rounded-full" aria-hidden="true" />
      <LogoAssembly width={128} />
      <div className="relative text-center">
        <p className="text-sm font-semibold text-navy-900">{label}</p>
        <p className="mt-1 font-mono text-[9px] uppercase tracking-[0.22em] text-brand-500">Assembling your workspace</p>
      </div>
      <span className="relative h-1 w-36 overflow-hidden rounded-full bg-brand-100" aria-hidden="true">
        <span className="block h-full w-1/2 rounded-full bg-gradient-to-r from-brand-400 to-cyan-300 [animation:loadingSweep_1.4s_ease-in-out_infinite]" />
      </span>
    </div>
  );
}

export function ErrorBlock({ message, onRetry }: { message: string; onRetry?: () => void }) {
  return (
    <div className="flex min-h-[220px] flex-col items-center justify-center gap-4 rounded-3xl border border-rose-100 bg-rose-50/60 px-6 text-center">
      <span className="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-rose-600">
        <AlertTriangle className="h-6 w-6" />
      </span>
      <div>
        <h3 className="font-display text-lg font-semibold text-navy-900">Something went wrong</h3>
        <p className="mt-1 text-sm text-ink-muted">{message}</p>
      </div>
      {onRetry && (
        <button type="button" className="btn-ghost btn-sm" onClick={onRetry}>
          Try again
        </button>
      )}
    </div>
  );
}

export function EmptyState({
  title,
  message,
  action,
  icon,
}: {
  title: string;
  message?: string;
  action?: ReactNode;
  icon?: ReactNode;
}) {
  return (
    <div className="flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-14 text-center">
      <span className="grid h-12 w-12 place-items-center rounded-2xl bg-white text-brand-500 shadow-soft">
        {icon ?? <Inbox className="h-6 w-6" />}
      </span>
      <div>
        <h3 className="font-display text-base font-semibold text-navy-900">{title}</h3>
        {message && <p className="mx-auto mt-1 max-w-sm text-sm text-ink-muted">{message}</p>}
      </div>
      {action}
    </div>
  );
}

export function Modal({
  open,
  onClose,
  title,
  description,
  children,
  footer,
  size = 'md',
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  description?: string;
  children: ReactNode;
  footer?: ReactNode;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}) {
  useEffect(() => {
    if (!open) return;
    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose();
    };
    document.addEventListener('keydown', onKey);
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = '';
    };
  }, [open, onClose]);

  if (!open) return null;
  const width = { sm: 'max-w-md', md: 'max-w-2xl', lg: 'max-w-4xl', xl: 'max-w-6xl' }[size];

  return (
    <div className="fixed inset-0 z-[90] flex items-end justify-center overflow-y-auto bg-navy-950/55 p-0 backdrop-blur-sm sm:items-center sm:p-6" role="dialog" aria-modal="true">
      <button type="button" aria-label="Close dialog" className="absolute inset-0 cursor-default" onClick={onClose} />
      <div className={cn('relative z-10 w-full rounded-t-3xl bg-white shadow-lift sm:rounded-3xl', width)}>
        <div className="flex items-start justify-between gap-4 border-b border-brand-50 px-5 py-4 sm:px-6">
          <div>
            <h2 className="font-display text-lg font-semibold text-navy-900">{title}</h2>
            {description && <p className="mt-1 text-sm text-ink-muted">{description}</p>}
          </div>
          <button type="button" onClick={onClose} className="rounded-full p-2 text-ink-muted transition hover:bg-brand-50 hover:text-navy-900" aria-label="Close">
            <X className="h-5 w-5" />
          </button>
        </div>
        <div className="max-h-[70vh] overflow-y-auto px-5 py-5 sm:px-6">{children}</div>
        {footer && <div className="flex flex-wrap items-center justify-end gap-3 border-t border-brand-50 px-5 py-4 sm:px-6">{footer}</div>}
      </div>
    </div>
  );
}

export function Field({ label, error, hint, children, className }: { label: string; error?: string; hint?: string; children: ReactNode; className?: string }) {
  return (
    <label className={cn('block', className)}>
      <span className="label">{label}</span>
      {children}
      {hint && !error && <span className="mt-1 block text-xs text-ink-faint">{hint}</span>}
      {error && <span className="field-error">{error}</span>}
    </label>
  );
}

export function StatCard({
  label,
  value,
  sub,
  icon,
  tone = 'blue',
}: {
  label: string;
  value: ReactNode;
  sub?: ReactNode;
  icon?: ReactNode;
  tone?: Tone;
}) {
  return (
    <div className="stat-card">
      <div className="flex items-start justify-between gap-3">
        <div>
          <p className="text-[11px] font-bold uppercase tracking-[0.14em] text-ink-faint">{label}</p>
          <p className="mt-2 font-display text-2xl font-semibold text-navy-900">{value}</p>
          {sub && <p className="mt-1 text-xs text-ink-muted">{sub}</p>}
        </div>
        {icon && <span className={cn('grid h-11 w-11 place-items-center rounded-2xl', toneClasses[tone])}>{icon}</span>}
      </div>
      <span className="pointer-events-none absolute -right-6 -top-8 h-24 w-24 rounded-full bg-brand-50/80 blur-2xl" />
    </div>
  );
}

export function ProgressBar({ value, className }: { value: number; className?: string }) {
  const safe = Math.max(0, Math.min(100, Number(value) || 0));
  return (
    <div className={cn('h-2 w-full overflow-hidden rounded-full bg-brand-100', className)}>
      <div className="h-full rounded-full bg-gradient-to-r from-brand-500 to-cyan-400 transition-all duration-500" style={{ width: `${safe}%` }} />
    </div>
  );
}

export function Pagination({ page, totalPages, onPage }: { page: number; totalPages: number; onPage: (page: number) => void }) {
  if (totalPages <= 1) return null;
  return (
    <div className="flex items-center justify-between gap-3 px-1 py-3 text-sm text-ink-muted">
      <span>
        Page {page} of {totalPages}
      </span>
      <div className="flex items-center gap-2">
        <button type="button" className="btn-ghost btn-sm" disabled={page <= 1} onClick={() => onPage(page - 1)}>
          <ChevronLeft className="h-4 w-4" /> Prev
        </button>
        <button type="button" className="btn-ghost btn-sm" disabled={page >= totalPages} onClick={() => onPage(page + 1)}>
          Next <ChevronRight className="h-4 w-4" />
        </button>
      </div>
    </div>
  );
}

export function PageHeader({
  eyebrow,
  title,
  description,
  actions,
}: {
  eyebrow?: string;
  title: ReactNode;
  description?: ReactNode;
  actions?: ReactNode;
}) {
  return (
    <div className="flex flex-col gap-4 border-b border-brand-100 pb-6 sm:flex-row sm:items-end sm:justify-between">
      <div>
        {eyebrow && <span className="eyebrow">{eyebrow}</span>}
        <h1 className="mt-2 font-display text-2xl font-semibold tracking-tight text-navy-900 sm:text-3xl">{title}</h1>
        {description && <p className="mt-2 max-w-2xl text-sm text-ink-muted">{description}</p>}
      </div>
      {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
    </div>
  );
}
