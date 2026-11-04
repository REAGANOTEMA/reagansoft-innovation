import { useState } from 'react';
import { ReceiptText, Wallet } from 'lucide-react';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, Modal, PageHeader, StatCard } from '../../components/UI';
import { InvoiceView } from '../../components/InvoiceView';
import { PaymentModal } from '../../components/PaymentModal';
import { formatDate, money } from '../../lib/format';
import { invoiceStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Invoice } from '../../lib/types';

const filters = [
  { key: 'all', label: 'All invoices' },
  { key: 'open', label: 'Outstanding' },
  { key: 'paid', label: 'Paid' },
] as const;

type FilterKey = (typeof filters)[number]['key'];

export function PortalInvoices() {
  usePageMeta('Invoices — Reagansoft client portal');
  const { data, loading, error, reload } = useApi<Invoice[]>('client/invoices');
  const [filter, setFilter] = useState<FilterKey>('all');
  const [viewInvoice, setViewInvoice] = useState<Invoice | null>(null);
  const [payInvoice, setPayInvoice] = useState<Invoice | null>(null);

  const invoices = data ?? [];
  const totalBilled = invoices.reduce((sum, invoice) => sum + Number(invoice.total), 0);
  const totalPaid = invoices.reduce((sum, invoice) => sum + Number(invoice.amount_paid), 0);
  const outstanding = Math.max(0, totalBilled - totalPaid);

  const visible = invoices.filter((invoice) => {
    if (filter === 'paid') return invoice.status === 'paid';
    if (filter === 'open') return ['sent', 'partially_paid', 'overdue'].includes(invoice.status);
    return true;
  });

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Billing" title="Invoices & payments" description="Review what has been billed, what is outstanding, and submit a payment reference for confirmation." />

      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard label="Total billed" value={money(totalBilled)} icon={ReceiptText} tone="brand" />
        <StatCard label="Paid to date" value={money(totalPaid)} icon={Wallet} tone="green" />
        <StatCard label="Outstanding" value={money(outstanding)} icon={ReceiptText} tone={outstanding > 0 ? 'amber' : 'gray'} />
      </div>

      <div className="flex flex-wrap gap-1 rounded-xl border border-brand-100 bg-white p-1.5">
        {filters.map((item) => (
          <button
            key={item.key}
            type="button"
            onClick={() => setFilter(item.key)}
            className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', filter === item.key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50 hover:text-brand-700')}
          >
            {item.label}
          </button>
        ))}
      </div>

      {loading ? (
        <LoadingBlock label="Loading invoices…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : visible.length === 0 ? (
        <EmptyState icon={<ReceiptText className="h-6 w-6" />} title="No invoices here" message="Invoices appear once a project is approved and billing begins." />
      ) : (
        <div className="space-y-2.5">
          {visible.map((invoice) => {
            const status = invoiceStatus(invoice.status);
            const balance = Math.max(0, Number(invoice.total) - Number(invoice.amount_paid));
            return (
              <div key={invoice.id} className="rounded-2xl border border-brand-100 bg-white p-4 sm:p-5">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="font-mono text-[11.5px] font-semibold text-navy-900">{invoice.invoice_no}</p>
                      <Badge tone={status.tone}>{status.label}</Badge>
                      {invoice.is_deposit ? <Badge tone="brand">Deposit</Badge> : null}
                    </div>
                    <p className="mt-1 text-[12px] font-medium text-navy-900">{invoice.project ?? 'Project'}</p>
                    <p className="text-[10.5px] text-ink-faint">
                      {invoice.ref_no ? `${invoice.ref_no} · ` : ''}Issued {formatDate(invoice.created_at)} · due {formatDate(invoice.due_date)}
                    </p>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <p className="font-display text-base font-semibold text-navy-900">{money(invoice.total)}</p>
                      {balance > 0 ? <p className="text-[10.5px] font-medium text-amber-700">{money(balance)} due</p> : <p className="text-[10.5px] text-emerald-700">Settled</p>}
                    </div>
                    <div className="flex gap-2">
                      <button type="button" className="btn-ghost btn-sm" onClick={() => setViewInvoice(invoice)}>View</button>
                      {balance > 0 && invoice.status !== 'cancelled' && (
                        <button type="button" className="btn-primary btn-sm" onClick={() => setPayInvoice(invoice)}>Pay</button>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      <div className="rounded-2xl border border-brand-100 bg-brand-50/50 p-5 text-[11.5px] leading-relaxed text-ink-muted">
        Payments are confirmed manually by our finance team. Submit your Mobile Money or bank reference and the invoice will update once
        confirmed. Keep your reference until then.
      </div>

      <Modal open={Boolean(viewInvoice)} onClose={() => setViewInvoice(null)} title="Invoice detail" size="lg">
        {viewInvoice && <InvoiceView invoice={viewInvoice} />}
      </Modal>

      <PaymentModal invoice={payInvoice} open={Boolean(payInvoice)} onClose={() => setPayInvoice(null)} onSuccess={reload} />
    </div>
  );
}
