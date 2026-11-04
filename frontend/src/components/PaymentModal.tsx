import { useState } from 'react';
import { Link } from 'react-router-dom';
import { AlertTriangle, ArrowRight, Wallet } from 'lucide-react';
import { api, ApiError } from '../lib/api';
import { money } from '../lib/format';
import { paymentMethod } from '../lib/status';
import { Modal } from './UI';
import { useToast } from './Toast';
import type { Invoice } from '../lib/types';

const methods = ['mtn_momo', 'airtel_money', 'bank', 'cash', 'other'];

export function PaymentModal({
  invoice,
  open,
  onClose,
  onSuccess,
}: {
  invoice: Invoice | null;
  open: boolean;
  onClose: () => void;
  onSuccess: () => void;
}) {
  const { push } = useToast();
  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState('mtn_momo');
  const [reference, setReference] = useState('');
  const [notes, setNotes] = useState('');
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [missing, setMissing] = useState<string[]>([]);

  if (!invoice) return null;
  const balance = Math.max(0, Number(invoice.total) - Number(invoice.amount_paid));

  async function submit(event: React.FormEvent) {
    event.preventDefault();
    setBusy(true);
    setError('');
    setMissing([]);
    try {
      await api('client/payments', {
        method: 'POST',
        body: {
          invoice_id: invoice!.id,
          amount: amount || balance,
          method,
          reference,
          notes,
        },
      });
      push('success', 'Payment submitted. We will confirm it shortly.');
      setAmount('');
      setReference('');
      setNotes('');
      onSuccess();
      onClose();
    } catch (err) {
      if (err instanceof ApiError) {
        setError(err.message);
        const billing = err.errors.billing;
        if (billing) setMissing(Array.isArray(billing) ? billing : [billing]);
      } else {
        setError('Could not submit the payment. Please try again.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal
      open={open}
      onClose={onClose}
      title={`Pay ${invoice.invoice_no}`}
      description={`Outstanding balance: ${money(balance)}`}
      size="md"
      footer={
        <>
          <button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button>
          <button type="submit" form="payment-form" disabled={busy || balance <= 0} className="btn-primary btn-sm">
            {busy ? 'Submitting…' : 'Submit payment'} <ArrowRight className="h-3.5 w-3.5" />
          </button>
        </>
      }
    >
      {error && (
        <div className="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[12.5px] text-rose-700">
          <p className="font-semibold">{error}</p>
          {missing.length > 0 && (
            <div className="mt-2">
              <p className="text-[11.5px]">Complete these billing details first:</p>
              <ul className="mt-1 list-inside list-disc text-[11.5px]">
                {missing.map((item) => <li key={item}>{item}</li>)}
              </ul>
              <Link to="/portal/billing" className="mt-2 inline-flex items-center gap-1 font-semibold underline">
                Go to billing details <ArrowRight className="h-3 w-3" />
              </Link>
            </div>
          )}
        </div>
      )}

      <form id="payment-form" onSubmit={submit} className="space-y-4">
        <div className="rounded-2xl bg-brand-50/70 p-4 text-[12px] text-ink-soft">
          <div className="flex items-center justify-between">
            <span className="text-ink-muted">Invoice total</span>
            <span className="font-semibold">{money(invoice.total)}</span>
          </div>
          <div className="mt-1 flex items-center justify-between">
            <span className="text-ink-muted">Already paid</span>
            <span className="font-semibold">{money(invoice.amount_paid)}</span>
          </div>
          <div className="mt-2 flex items-center justify-between border-t border-brand-100 pt-2">
            <span className="font-semibold text-navy-900">Balance</span>
            <span className="font-display text-base font-semibold text-brand-700">{money(balance)}</span>
          </div>
        </div>

        <label className="block">
          <span className="label">Amount paid</span>
          <input className="input" type="number" min="1" max={balance} step="0.01" value={amount} onChange={(event) => setAmount(event.target.value)} placeholder={String(balance)} />
          <span className="mt-1 block text-[11px] text-ink-faint">Leave blank to pay the full balance.</span>
        </label>

        <label className="block">
          <span className="label">Payment method</span>
          <select className="input" value={method} onChange={(event) => setMethod(event.target.value)}>
            {methods.map((item) => <option key={item} value={item}>{paymentMethod(item)}</option>)}
          </select>
        </label>

        <label className="block">
          <span className="label">Transaction reference {method !== 'cash' ? '*' : ''}</span>
          <input className="input" value={reference} onChange={(event) => setReference(event.target.value)} placeholder="e.g. MP260114.1234.A56789" required={method !== 'cash'} />
        </label>

        <label className="block">
          <span className="label">Notes (optional)</span>
          <textarea className="input min-h-[80px]" value={notes} onChange={(event) => setNotes(event.target.value)} placeholder="Anything we should know about this payment" />
        </label>

        <div className="flex items-start gap-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2.5 text-[11px] text-amber-800">
          <AlertTriangle className="mt-0.5 h-3.5 w-3.5 shrink-0" />
          Payments are confirmed manually by our team. Keep your transaction reference until the invoice shows as paid.
        </div>
      </form>

      <div className="mt-3 flex items-center gap-2 text-[10.5px] text-ink-faint">
        <Wallet className="h-3.5 w-3.5" /> Pay to {invoice.invoice_no}
      </div>
    </Modal>
  );
}
