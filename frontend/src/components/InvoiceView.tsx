import { Badge, ProgressBar } from '../UI';
import { formatDate, money } from '../../lib/format';
import { invoiceStatus, paymentMethod, paymentStatus } from '../../lib/status';
import type { Invoice, Payment } from '../../lib/types';

export function InvoiceView({ invoice, showPayments = true }: { invoice: Invoice; showPayments?: boolean }) {
  const balance = Math.max(0, Number(invoice.total) - Number(invoice.amount_paid));
  const status = invoiceStatus(invoice.status);

  return (
    <div className="space-y-5">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-faint">Invoice</p>
          <p className="mt-1 font-display text-xl font-semibold text-navy-900">{invoice.invoice_no}</p>
          <p className="mt-0.5 text-[12px] text-ink-muted">{invoice.project ?? ''}{invoice.ref_no ? ` · ${invoice.ref_no}` : ''}</p>
        </div>
        <div className="text-right">
          <Badge tone={status.tone}>{status.label}</Badge>
          <p className="mt-2 font-display text-2xl font-semibold text-navy-900">{money(invoice.total)}</p>
          <p className="text-[11px] text-ink-muted">{balance > 0 ? `${money(balance)} outstanding` : 'Fully settled'}</p>
        </div>
      </div>

      <div className="grid gap-3 sm:grid-cols-3">
        <div className="rounded-xl bg-brand-50/70 p-3">
          <p className="font-mono text-[8px] uppercase tracking-widest text-ink-faint">Issued</p>
          <p className="mt-1 text-[12px] font-semibold text-navy-900">{formatDate(invoice.created_at)}</p>
        </div>
        <div className="rounded-xl bg-brand-50/70 p-3">
          <p className="font-mono text-[8px] uppercase tracking-widest text-ink-faint">Due</p>
          <p className="mt-1 text-[12px] font-semibold text-navy-900">{formatDate(invoice.due_date)}</p>
        </div>
        <div className="rounded-xl bg-brand-50/70 p-3">
          <p className="font-mono text-[8px] uppercase tracking-widest text-ink-faint">Paid to date</p>
          <p className="mt-1 text-[12px] font-semibold text-navy-900">{money(invoice.amount_paid)}</p>
        </div>
      </div>

      {invoice.items && invoice.items.length > 0 && (
        <div className="table-shell">
          <table className="data-table">
            <thead>
              <tr>
                <th>Description</th>
                <th className="text-right">Qty</th>
                <th className="text-right">Unit price</th>
                <th className="text-right">Amount</th>
              </tr>
            </thead>
            <tbody>
              {invoice.items.map((item) => (
                <tr key={item.id}>
                  <td className="font-medium text-navy-900">{item.description}</td>
                  <td className="text-right">{Number(item.quantity)}</td>
                  <td className="text-right">{money(item.unit_price)}</td>
                  <td className="text-right font-semibold">{money(Number(item.quantity) * Number(item.unit_price))}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <div className="ml-auto max-w-xs space-y-1.5 text-[12px]">
        <div className="flex justify-between"><span className="text-ink-muted">Subtotal</span><span className="font-medium">{money(invoice.subtotal)}</span></div>
        {Number(invoice.discount) > 0 && (
          <div className="flex justify-between"><span className="text-ink-muted">Discount</span><span className="font-medium">− {money(invoice.discount)}</span></div>
        )}
        {Number(invoice.tax_percent) > 0 && (
          <div className="flex justify-between"><span className="text-ink-muted">Tax ({invoice.tax_percent}%)</span><span className="font-medium">{money(Number(invoice.total) - (Number(invoice.subtotal) - Number(invoice.discount)))}</span></div>
        )}
        <div className="flex justify-between border-t border-brand-100 pt-1.5 text-sm font-semibold text-navy-900"><span>Total</span><span>{money(invoice.total)}</span></div>
        <div className="flex justify-between text-[11px] text-ink-muted"><span>Paid</span><span>{money(invoice.amount_paid)}</span></div>
        <div className="flex justify-between text-[11px] font-semibold text-brand-700"><span>Balance</span><span>{money(balance)}</span></div>
      </div>

      {invoice.notes && (
        <div className="rounded-xl border border-brand-100 bg-brand-50/40 p-3 text-[11.5px] leading-relaxed text-ink-muted">{invoice.notes}</div>
      )}

      {showPayments && invoice.payments && invoice.payments.length > 0 && (
        <div>
          <p className="mb-2 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">Payment history</p>
          <div className="space-y-2">
            {invoice.payments.map((payment: Payment) => {
              const meta = paymentStatus(payment.status);
              return (
                <div key={payment.id} className="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2.5 text-[12px]">
                  <div>
                    <p className="font-semibold text-navy-900">{money(payment.amount)} · {paymentMethod(payment.method)}</p>
                    <p className="text-[10.5px] text-ink-faint">{formatDate(payment.created_at, true)}{payment.reference ? ` · ${payment.reference}` : ''}</p>
                  </div>
                  <Badge tone={meta.tone}>{meta.label}</Badge>
                </div>
              );
            })}
          </div>
        </div>
      )}

      <ProgressBar value={Number(invoice.total) > 0 ? (Number(invoice.amount_paid) / Number(invoice.total)) * 100 : 0} />
    </div>
  );
}
