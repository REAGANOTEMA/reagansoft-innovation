import { useState } from 'react';
import { Link } from 'react-router-dom';
import { Bell, CheckCheck, CreditCard, FolderKanban, MessageSquare, ReceiptText } from 'lucide-react';
import { api } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { EmptyState, ErrorBlock, LoadingBlock, PageHeader } from '../../components/UI';
import { timeAgo } from '../../lib/format';
import { cn } from '../../lib/cn';
import type { Notification } from '../../lib/types';

interface Payload {
  notifications: Notification[];
  unread: number;
}

function iconFor(type: string) {
  if (type === 'invoice') return ReceiptText;
  if (type === 'payment') return CreditCard;
  if (type === 'project') return FolderKanban;
  if (type === 'message') return MessageSquare;
  return Bell;
}

export function PortalNotifications() {
  usePageMeta('Notifications — Reagansoft client portal');
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Payload>('client/notifications');
  const [busy, setBusy] = useState(false);

  const notifications = data?.notifications ?? [];
  const unread = data?.unread ?? 0;

  async function markAll() {
    setBusy(true);
    try {
      await api('client/notifications', { method: 'POST', body: {} });
      push('success', 'All notifications marked as read.');
      reload();
    } catch {
      push('error', 'Could not update notifications.');
    } finally {
      setBusy(false);
    }
  }

  async function markOne(id: number) {
    try {
      await api('client/notifications', { method: 'POST', body: { id } });
      reload();
    } catch {
      push('error', 'Could not update that notification.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Notifications"
        title="Activity feed"
        description={unread > 0 ? `You have ${unread} unread update${unread === 1 ? '' : 's'}.` : 'You are all caught up.'}
        actions={unread > 0 ? <button type="button" onClick={markAll} disabled={busy} className="btn-ghost btn-sm"><CheckCheck className="h-3.5 w-3.5" /> Mark all read</button> : undefined}
      />

      {loading ? (
        <LoadingBlock label="Loading notifications…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : notifications.length === 0 ? (
        <EmptyState icon={<Bell className="h-6 w-6" />} title="No notifications yet" message="Project updates, invoices and messages will appear here." />
      ) : (
        <div className="space-y-2">
          {notifications.map((item) => {
            const Icon = iconFor(item.type);
            const unreadItem = !item.is_read;
            return (
              <div
                key={item.id}
                className={cn('flex items-start gap-3 rounded-2xl border p-4 transition', unreadItem ? 'border-brand-200 bg-brand-50/50' : 'border-brand-100 bg-white')}
              >
                <span className={cn('grid h-9 w-9 shrink-0 place-items-center rounded-xl', unreadItem ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-600')}>
                  <Icon className="h-4 w-4" />
                </span>
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <p className={cn('text-[12.5px]', unreadItem ? 'font-semibold text-navy-900' : 'font-medium text-navy-800')}>{item.title}</p>
                    <span className="text-[10px] text-ink-faint">{timeAgo(item.created_at)}</span>
                  </div>
                  {item.message && <p className="mt-0.5 text-[11.5px] leading-relaxed text-ink-muted">{item.message}</p>}
                  <div className="mt-1.5 flex items-center gap-3">
                    {item.related_project_id && (
                      <Link to={`/portal/projects/${item.related_project_id}`} className="text-[10.5px] font-semibold text-brand-700 hover:underline">
                        Open project
                      </Link>
                    )}
                    {unreadItem && (
                      <button type="button" onClick={() => markOne(item.id)} className="text-[10.5px] font-semibold text-ink-muted hover:text-brand-700">
                        Mark read
                      </button>
                    )}
                  </div>
                </div>
                {unreadItem && <span className="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-brand-500" aria-label="Unread" />}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
