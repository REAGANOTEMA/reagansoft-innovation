import { useEffect, useState } from 'react';
import { Check, CreditCard, X } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, PageHeader, Pagination } from '../../components/UI';
import { formatDate, money } from '../../lib/format';
import { paymentMethod, paymentStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Payment } from '../../lib/types';

const statuses = ['pending', 'confirmed', 'rejected'];

export function ConsolePayments() {
  usePageMeta('Payments — Reagansoft console');
  const { push } = useToast();
  const [status, setStatus] = useState('pending');
  const [page, setPage] = useState(1);
  const { data, meta, loading, error, reload } = useApi<Payment[]>('admin/payments', { page, status: status || undefined });

  useEffect(() => {
    setPage(1);
  }, [status]);

  const totalPages = Number(meta?.total_pages ?? 1);

  async function act(payment: Payment, action: 'confirm' | 'reject') {
    const verb = action === 'confirm' ? 'Confirm' : 'Reject';
    if (!window.confirm(`${verb} ${money(payment.amount)} from ${payment.client_name} for ${payment.invoice_no}?`)) return;
    try {
      await api(`admin/payments/${payment.id}`, { method: 'PATCH', body: { action } });
      push('success', action === 'confirm' ? 'Payment confirmed and invoice updated.' : 'Payment rejected.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not process the payment.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Finance" title="Payments" description="Confirm or reject client payment references. Confirming updates the invoice balance automatically." />

      <div className="flex flex-wrap gap-1 rounded-xl border border-brand-100 bg-white p-1.5">
        <button type="button" onClick={() => setStatus('')} className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', status === '' ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50')}>All</button>
        {statuses.map((item) => (
          <button key={item} type="button" onClick={() => setStatus(item)} className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold capitalize transition', status === item ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50')}>{paymentStatus(item).label}</button>
        ))}
      </div>

      {loading ? (
        <LoadingBlock label="Loading payments…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<CreditCard className="h-6 w-6" />} title="No payments" message={status === 'pending' ? 'No payments are waiting for confirmation.' : 'Nothing matches this filter.'} />
      ) : (
        <>
          <div className="space-y-2.5">
            {data.map((payment) => {
              const metaStatus = paymentStatus(payment.status);
              return (
                <div key={payment.id} className="rounded-2xl border border-brand-100 bg-white p-4 sm:p-5">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="flex flex-wrap items-center gap-2">
                        <p className="font-display text-base font-semibold text-navy-900">{money(payment.amount)}</p>
                        <Badge tone={metaStatus.tone}>{metaStatus.label}</Badge>
                      </div>
                      <p className="mt-1 text-[11.5px] text-ink-muted">{payment.client_name} · {payment.invoice_no} · {payment.project}</p>
                      <p className="text-[10.5px] text-ink-faint">
                        {paymentMethod(payment.method)} · ref {payment.reference ?? '—'} · submitted {formatDate(payment.created_at, true)}
                      </p>
                      {payment.notes && <p className="mt-1 text-[10.5px] text-ink-muted">{payment.notes}</p>}
                    </div>
                    <div className="flex items-center gap-2">
                      {payment.status === 'pending' ? (
                        <>
                          <button type="button" onClick={() => act(payment, 'confirm')} className="btn-primary btn-sm"><Check className="h-3.5 w-3.5" /> Confirm</button>
                          <button type="button" onClick={() => act(payment, 'reject')} className="btn-ghost btn-sm text-rose-600"><X className="h-3.5 w-3.5" /> Reject</button>
                        </>
                      ) : (
                        <span className="text-[10.5px] text-ink-faint">Processed {formatDate(payment.confirmed_at)}</span>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}
    </div>
  );
}
