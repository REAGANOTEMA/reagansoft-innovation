export function money(amount: number | string | null | undefined, currency = 'UGX'): string {
  const value = Number(amount ?? 0);
  return `${currency} ${new Intl.NumberFormat('en-UG', { maximumFractionDigits: 0 }).format(Number.isFinite(value) ? value : 0)}`;
}

export function compactMoney(amount: number | string | null | undefined): string {
  const value = Number(amount ?? 0);
  if (Math.abs(value) >= 1_000_000) return `${(value / 1_000_000).toFixed(value % 1_000_000 === 0 ? 0 : 1)}M`;
  if (Math.abs(value) >= 1_000) return `${(value / 1_000).toFixed(value % 1_000 === 0 ? 0 : 1)}K`;
  return String(Math.round(value));
}

export function formatDate(value: string | null | undefined, withTime = false): string {
  if (!value) return '—';
  const date = new Date(value.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {}),
  });
}

export function timeAgo(value: string | null | undefined): string {
  if (!value) return '—';
  const date = new Date(value.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return value;
  const diff = Math.floor((Date.now() - date.getTime()) / 1000);
  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)} hr ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)} day${Math.floor(diff / 86400) === 1 ? '' : 's'} ago`;
  return formatDate(value);
}

export function titleCase(value: string): string {
  return value
    .replace(/[_-]+/g, ' ')
    .toLowerCase()
    .replace(/\b\w/g, (c) => c.toUpperCase());
}

export function initials(name: string): string {
  return name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('');
}

export type Tone = 'green' | 'amber' | 'red' | 'blue' | 'violet' | 'gray' | 'cyan';

export function statusTone(status: string): Tone {
  const key = status.toLowerCase();
  if (['completed', 'paid', 'accepted', 'active', 'confirmed', 'success'].includes(key)) return 'green';
  if (['in_progress', 'sent', 'partially_paid', 'reviewing', 'testing', 'quotation', 'pending', 'todo'].includes(key)) return 'blue';
  if (['waiting_for_client', 'overdue', 'expired', 'draft', 'rejected', 'cancelled', 'inactive'].includes(key)) return 'amber';
  if (['cancelled', 'rejected'].includes(key)) return 'red';
  if (['approved', 'review'].includes(key)) return 'violet';
  return 'gray';
}

export const toneClasses: Record<Tone, string> = {
  green: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
  amber: 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
  red: 'bg-rose-50 text-rose-700 ring-1 ring-rose-100',
  blue: 'bg-brand-50 text-brand-700 ring-1 ring-brand-100',
  violet: 'bg-violet-50 text-violet-700 ring-1 ring-violet-100',
  gray: 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
  cyan: 'bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100',
};

export function fileSize(bytes: number): string {
  if (bytes >= 1048576) return `${(bytes / 1048576).toFixed(1)} MB`;
  if (bytes >= 1024) return `${Math.round(bytes / 1024)} KB`;
  return `${bytes} B`;
}
