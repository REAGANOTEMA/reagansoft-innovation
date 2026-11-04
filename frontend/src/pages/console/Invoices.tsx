import { useEffect, useState, type FormEvent } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Ban, Eye, Plus, ReceiptText, Search } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi, useDebounced } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination, StatCard } from '../../components/UI';
import { InvoiceView } from '../../components/InvoiceView';
import { LineItems, emptyLine, lineTotal, type LineItem } from '../../components/console/LineItems';
import { formatDate, money } from '../../lib/format';
import { invoiceStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Invoice, Project } from '../../lib/types';

const statuses = ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'];

export function ConsoleInvoices() {
  usePageMeta('Invoices — Reagansoft console');
  const { settings } = useSite();
  const { push } = useToast();
  const [params] = useSearchParams();
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const [viewId, setViewId] = useState<number | null>(null);
  const debounced = useDebounced(query);

  const { data, meta, loading, error, reload } = useApi<Invoice[]>('admin/invoices', { page, status: status || undefined, q: debounced || undefined });
  const projects = useApi<Project[]>('admin/projects', { page: 1 });

  useEffect(() => {
    if (params.get('project')) setCreateOpen(true);
  }, [params]);

  useEffect(() => {
    setPage(1);
  }, [status, debounced]);

  const totalPages = Number(meta?.total_pages ?? 1);
  const summary = meta?.summary as { outstanding: number; paid_total: number; total_count: number } | undefined;

  async function cancel(invoice: Invoice) {
    if (!window.confirm(`Cancel ${invoice.invoice_no}?`)) return;
    try {
      await api(`admin/invoices/${invoice.id}`, { method: 'DELETE' });
      push('success', 'Invoice cancelled.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not cancel the invoice.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Finance"
        title="Invoices"
        description="Issue invoices, track balances and keep billing accurate."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> New invoice</button>}
      />

      <div className="grid gap-4 sm:grid-cols-3">
        <StatCard label="Outstanding" value={money(summary?.outstanding ?? 0)} icon={<ReceiptText className="h-5 w-5" />} tone="amber" />
        <StatCard label="Paid total" value={money(summary?.paid_total ?? 0)} icon={<ReceiptText className="h-5 w-5" />} tone="green" />
        <StatCard label="Invoices" value={summary?.total_count ?? 0} icon={<ReceiptText className="h-5 w-5" />} tone="blue" />
      </div>

      <div className="flex flex-col gap-3 rounded-2xl border border-brand-100 bg-white p-3 sm:flex-row">
        <div className="flex flex-1 items-center gap-2 rounded-xl bg-brand-50/60 px-3 py-2.5">
          <Search className="h-4 w-4 text-ink-faint" />
          <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search invoice, client or project…" className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint" aria-label="Search invoices" />
        </div>
        <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-brand-100 bg-white px-3 py-2.5 text-sm outline-none">
          <option value="">All statuses</option>
          {statuses.map((item) => <option key={item} value={item}>{invoiceStatus(item).label}</option>)}
        </select>
      </div>

      {loading ? (
        <LoadingBlock label="Loading invoices…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-14 text-center text-[12px] text-ink-muted">No invoices match the filters.</div>
      ) : (
        <>
          <div className="table-shell overflow-x-auto">
            <table className="data-table min-w-[860px]">
              <thead><tr><th>Invoice</th><th>Client</th><th>Project</th><th>Issued</th><th>Due</th><th className="text-right">Total</th><th className="text-right">Balance</th><th>Status</th><th /></tr></thead>
              <tbody>
                {data.map((invoice) => {
                  const metaStatus = invoiceStatus(invoice.status);
                  const balance = Math.max(0, Number(invoice.total) - Number(invoice.amount_paid));
                  return (
                    <tr key={invoice.id}>
                      <td><button type="button" onClick={() => setViewId(invoice.id)} className="font-mono text-[11px] font-semibold text-brand-700 hover:underline">{invoice.invoice_no}</button></td>
                      <td className="text-[11px] text-ink-muted">{invoice.client_name}</td>
                      <td className="text-[11px] text-ink-muted">{invoice.project}</td>
                      <td className="text-[10.5px] text-ink-faint">{formatDate(invoice.created_at)}</td>
                      <td className="text-[10.5px] text-ink-faint">{formatDate(invoice.due_date)}</td>
                      <td className="text-right font-mono text-[11px] font-semibold text-navy-900">{money(invoice.total)}</td>
                      <td className={cn('text-right font-mono text-[11px]', balance > 0 ? 'text-amber-700' : 'text-emerald-700')}>{money(balance)}</td>
                      <td><Badge tone={metaStatus.tone}>{metaStatus.label}</Badge></td>
                      <td>
                        <div className="flex items-center gap-1">
                          <button type="button" onClick={() => setViewId(invoice.id)} className="rounded-lg p-1.5 text-brand-700 hover:bg-brand-50" aria-label="View invoice"><Eye className="h-3.5 w-3.5" /></button>
                          {invoice.status !== 'cancelled' && <button type="button" onClick={() => cancel(invoice)} className="rounded-lg p-1.5 text-ink-faint hover:bg-rose-50 hover:text-rose-600" aria-label="Cancel invoice"><Ban className="h-3.5 w-3.5" /></button>}
                        </div>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}

      <CreateInvoiceModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        projects={projects.data ?? []}
        defaultProject={params.get('project') ?? ''}
        currency={settings.currency}
        taxDefault={settings.tax_percent ?? '0'}
        onCreated={() => {
          setCreateOpen(false);
          reload();
          push('success', 'Invoice created.');
        }}
      />

      <InvoiceDetailModal id={viewId} onClose={() => setViewId(null)} onChanged={reload} />
    </div>
  );
}

function CreateInvoiceModal({
  open,
  onClose,
  projects,
  defaultProject,
  currency,
  taxDefault,
  onCreated,
}: {
  open: boolean;
  onClose: () => void;
  projects: Project[];
  defaultProject: string;
  currency: string;
  taxDefault: string;
  onCreated: () => void;
}) {
  const { push } = useToast();
  const [form, setForm] = useState({ project_id: defaultProject, status: 'sent', discount: '', tax_percent: taxDefault, notes: '' });
  const [items, setItems] = useState<LineItem[]>([emptyLine()]);
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (open) setForm((current) => ({ ...current, project_id: defaultProject }));
  }, [open, defaultProject]);

  const subtotal = items.reduce((sum, item) => sum + lineTotal(item), 0);
  const taxable = Math.max(0, subtotal - (Number(form.discount) || 0));
  const total = taxable + (taxable * (Number(form.tax_percent) || 0)) / 100;

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api('admin/invoices', {
        method: 'POST',
        body: {
          project_id: form.project_id,
          status: form.status,
          discount: form.discount || 0,
          tax_percent: form.tax_percent || 0,
          notes: form.notes,
          items: items.map((item) => ({ description: item.description, quantity: Number(item.quantity) || 0, unit_price: Number(item.unit_price) || 0 })),
        },
      });
      setItems([emptyLine()]);
      setForm({ project_id: defaultProject, status: 'sent', discount: '', tax_percent: taxDefault, notes: '' });
      onCreated();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not create the invoice.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="New invoice" size="lg"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="create-invoice" disabled={busy} className="btn-primary btn-sm">{busy ? 'Creating…' : 'Create invoice'}</button></>}
    >
      <form id="create-invoice" onSubmit={submit} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <Field label="Project *" className="sm:col-span-2">
            <select className="input" value={form.project_id} onChange={(event) => setForm({ ...form, project_id: event.target.value })} required>
              <option value="">Select a project…</option>
              {projects.map((project) => <option key={project.id} value={project.id}>{project.ref_no} — {project.title} ({project.client_name})</option>)}
            </select>
          </Field>
          <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}><option value="sent">Send immediately</option><option value="draft">Save as draft</option></select></Field>
          <Field label="Tax %"><input className="input" type="number" min="0" step="0.01" value={form.tax_percent} onChange={(event) => setForm({ ...form, tax_percent: event.target.value })} /></Field>
        </div>

        <div className="rounded-xl border border-brand-100 p-3">
          <LineItems items={items} onChange={setItems} currency={currency} />
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          <Field label="Discount"><input className="input" type="number" min="0" step="0.01" value={form.discount} onChange={(event) => setForm({ ...form, discount: event.target.value })} /></Field>
          <div className="rounded-xl bg-brand-50/60 p-3 text-[12px]">
            <div className="flex justify-between"><span className="text-ink-muted">Subtotal</span><span className="font-medium">{money(subtotal, currency)}</span></div>
            <div className="flex justify-between"><span className="text-ink-muted">Tax</span><span className="font-medium">{money(total - taxable, currency)}</span></div>
            <div className="mt-1 flex justify-between border-t border-brand-100 pt-1 font-semibold text-navy-900"><span>Total</span><span>{money(total, currency)}</span></div>
          </div>
        </div>

        <Field label="Notes"><textarea className="input min-h-[70px]" value={form.notes} onChange={(event) => setForm({ ...form, notes: event.target.value })} /></Field>
      </form>
    </Modal>
  );
}

function InvoiceDetailModal({ id, onClose, onChanged }: { id: number | null; onClose: () => void; onChanged: () => void }) {
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Invoice>(id ? `admin/invoices/${id}` : null);
  const [status, setStatus] = useState('');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (data) setStatus(data.status);
  }, [data]);

  async function saveStatus() {
    if (!data) return;
    setBusy(true);
    try {
      await api(`admin/invoices/${data.id}`, { method: 'PATCH', body: { status } });
      push('success', 'Invoice status updated.');
      reload();
      onChanged();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the status.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={Boolean(id)} onClose={onClose} title="Invoice detail" size="lg">
      {loading ? <LoadingBlock label="Loading invoice…" /> : error || !data ? <ErrorBlock message={error || 'Invoice not found.'} /> : (
        <div className="space-y-4">
          <InvoiceView invoice={data} />
          <div className="flex flex-wrap items-end gap-3 border-t border-brand-50 pt-4">
            <Field label="Update status" className="flex-1">
              <select className="input" value={status} onChange={(event) => setStatus(event.target.value)}>
                {statuses.map((item) => <option key={item} value={item}>{invoiceStatus(item).label}</option>)}
              </select>
            </Field>
            <button type="button" onClick={saveStatus} disabled={busy || status === data.status} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Save status'}</button>
          </div>
        </div>
      )}
    </Modal>
  );
}
