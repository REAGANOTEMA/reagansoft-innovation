import { useEffect, useState } from 'react';
import { Check, Inbox, Mail, Phone, Trash2 } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, PageHeader, Pagination } from '../../components/UI';
import { formatDate, timeAgo } from '../../lib/format';
import { cn } from '../../lib/cn';
import type { Enquiry } from '../../lib/types';

const filters = [
  { key: '', label: 'All' },
  { key: 'unread', label: 'Unread' },
  { key: 'read', label: 'Read' },
] as const;

export function ConsoleEnquiries() {
  usePageMeta('Enquiries — Reagansoft console');
  const { push } = useToast();
  const [filter, setFilter] = useState('');
  const [page, setPage] = useState(1);
  const { data, meta, loading, error, reload } = useApi<Enquiry[]>('admin/enquiries', { page, filter: filter || undefined });

  useEffect(() => {
    setPage(1);
  }, [filter]);

  const totalPages = Number(meta?.total_pages ?? 1);

  async function markRead(enquiry: Enquiry, read = true) {
    try {
      await api(`admin/enquiries/${enquiry.id}`, { method: 'PATCH', body: { is_read: read } });
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the enquiry.');
    }
  }

  async function remove(enquiry: Enquiry) {
    if (!window.confirm(`Delete the enquiry from ${enquiry.full_name}? This cannot be undone.`)) return;
    try {
      await api(`admin/enquiries/${enquiry.id}`, { method: 'DELETE' });
      push('success', 'Enquiry deleted.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not delete the enquiry.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Inbox" title="Enquiries" description="Messages from the public contact form. Reply by email or phone." />

      <div className="flex flex-wrap gap-1 rounded-xl border border-brand-100 bg-white p-1.5">
        {filters.map((item) => (
          <button key={item.key || 'all'} type="button" onClick={() => setFilter(item.key)} className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', filter === item.key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50 hover:text-brand-700')}>
            {item.label}
          </button>
        ))}
      </div>

      {loading ? (
        <LoadingBlock label="Loading enquiries…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<Inbox className="h-6 w-6" />} title="No enquiries" message="New messages from the contact form will appear here." />
      ) : (
        <>
          <div className="space-y-2.5">
            {data.map((enquiry) => (
              <div key={enquiry.id} className={cn('rounded-2xl border p-4 sm:p-5', enquiry.is_read ? 'border-brand-100 bg-white' : 'border-brand-200 bg-brand-50/40')}>
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="text-[12.5px] font-semibold text-navy-900">{enquiry.full_name}</p>
                      {!enquiry.is_read && <Badge tone="blue">Unread</Badge>}
                      {enquiry.company && <span className="text-[10.5px] text-ink-muted">{enquiry.company}</span>}
                    </div>
                    <p className="mt-0.5 text-[10.5px] text-ink-faint">{formatDate(enquiry.created_at, true)} · {timeAgo(enquiry.created_at)}</p>
                  </div>
                  <div className="flex items-center gap-1.5">
                    {!enquiry.is_read && <button type="button" onClick={() => markRead(enquiry)} className="btn-ghost btn-sm"><Check className="h-3.5 w-3.5" /> Mark read</button>}
                    <button type="button" onClick={() => remove(enquiry)} className="rounded-lg p-2 text-ink-faint transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete enquiry"><Trash2 className="h-3.5 w-3.5" /></button>
                  </div>
                </div>
                {enquiry.subject && <p className="mt-2 text-[12px] font-semibold text-navy-900">{enquiry.subject}</p>}
                <p className="mt-1 whitespace-pre-line text-[12px] leading-relaxed text-ink-muted">{enquiry.message}</p>
                <div className="mt-3 flex flex-wrap gap-3 border-t border-brand-100/70 pt-2.5 text-[10.5px]">
                  <a href={`mailto:${enquiry.email}`} className="inline-flex items-center gap-1 font-semibold text-brand-700"><Mail className="h-3 w-3" /> {enquiry.email}</a>
                  {enquiry.phone && <a href={`tel:${enquiry.phone}`} className="inline-flex items-center gap-1 font-semibold text-brand-700"><Phone className="h-3 w-3" /> {enquiry.phone}</a>}
                </div>
              </div>
            ))}
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}
    </div>
  );
}
