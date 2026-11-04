import { useEffect, useState, type FormEvent } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Ban, Eye, FileText, Plus } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination } from '../../components/UI';
import { LineItems, emptyLine, lineTotal, type LineItem } from '../../components/console/LineItems';
import { formatDate, money } from '../../lib/format';
import { quotationStatus } from '../../lib/status';
import type { Project, Quotation } from '../../lib/types';

const statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'];

export function ConsoleQuotations() {
  usePageMeta('Quotations — Reagansoft console');
  const { settings } = useSite();
  const { push } = useToast();
  const [params] = useSearchParams();
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const [viewQuote, setViewQuote] = useState<Quotation | null>(null);
  const { data, meta, loading, error, reload } = useApi<Quotation[]>('admin/quotations', { page, status: status || undefined });
  const projects = useApi<Project[]>('admin/projects', { page: 1 });

  useEffect(() => {
    if (params.get('project')) setCreateOpen(true);
  }, [params]);

  useEffect(() => {
    setPage(1);
  }, [status]);

  const totalPages = Number(meta?.total_pages ?? 1);

  async function updateStatus(quote: Quotation, next: string) {
    try {
      await api(`admin/quotations/${quote.id}`, { method: 'PATCH', body: { status: next } });
      push('success', 'Quotation updated.');
      setViewQuote(null);
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the quotation.');
    }
  }

  async function cancel(quote: Quotation) {
    if (!window.confirm(`Cancel ${quote.quotation_no}?`)) return;
    try {
      await api(`admin/quotations/${quote.id}`, { method: 'DELETE' });
      push('success', 'Quotation cancelled.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not cancel the quotation.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Sales"
        title="Quotations"
        description="Proposals sent to clients, with line items and validity."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> New quotation</button>}
      />

      <div className="flex flex-wrap gap-1 rounded-xl border border-brand-100 bg-white p-1.5">
        <button type="button" onClick={() => setStatus('')} className={status === '' ? 'rounded-lg bg-brand-600 px-3.5 py-2 text-[12px] font-semibold text-white' : 'rounded-lg px-3.5 py-2 text-[12px] font-semibold text-ink-muted hover:bg-brand-50'}>All</button>
        {statuses.map((item) => (
          <button key={item} type="button" onClick={() => setStatus(item)} className={status === item ? 'rounded-lg bg-brand-600 px-3.5 py-2 text-[12px] font-semibold text-white' : 'rounded-lg px-3.5 py-2 text-[12px] font-semibold text-ink-muted hover:bg-brand-50'}>{quotationStatus(item).label}</button>
        ))}
      </div>

      {loading ? (
        <LoadingBlock label="Loading quotations…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-14 text-center text-[12px] text-ink-muted">No quotations match the filters.</div>
      ) : (
        <>
          <div className="space-y-2.5">
            {data.map((quote) => {
              const metaStatus = quotationStatus(quote.status);
              return (
                <div key={quote.id} className="rounded-2xl border border-brand-100 bg-white p-4 sm:p-5">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <div className="flex flex-wrap items-center gap-2">
                        <p className="font-mono text-[11.5px] font-semibold text-navy-900">{quote.quotation_no}</p>
                        <Badge tone={metaStatus.tone}>{metaStatus.label}</Badge>
                      </div>
                      <p className="mt-1 text-[12px] font-medium text-navy-900">{quote.project}</p>
                      <p className="text-[10.5px] text-ink-faint">{quote.client_name} · issued {formatDate(quote.issued_on)} · expires {formatDate(quote.expiry_date)}</p>
                    </div>
                    <div className="flex items-center gap-3">
                      <p className="font-display text-base font-semibold text-navy-900">{money(quote.total)}</p>
                      <button type="button" className="btn-ghost btn-sm" onClick={() => setViewQuote(quote)}><Eye className="h-3.5 w-3.5" /> View</button>
                      {quote.status !== 'cancelled' && <button type="button" onClick={() => cancel(quote)} className="rounded-lg p-2 text-ink-faint hover:bg-rose-50 hover:text-rose-600" aria-label="Cancel quotation"><Ban className="h-3.5 w-3.5" /></button>}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}

      <CreateQuotationModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        projects={projects.data ?? []}
        defaultProject={params.get('project') ?? ''}
        currency={settings.currency}
        taxDefault={settings.tax_percent ?? '0'}
        onCreated={() => {
          setCreateOpen(false);
          reload();
          push('success', 'Quotation created.');
        }}
      />

      <Modal open={Boolean(viewQuote)} onClose={() => setViewQuote(null)} title="Quotation detail" size="lg">
        {viewQuote && (
          <div className="space-y-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
              <div>
                <p className="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-faint">Quotation</p>
                <p className="mt-1 font-display text-xl font-semibold text-navy-900">{viewQuote.quotation_no}</p>
                <p className="text-[12px] text-ink-muted">{viewQuote.project} · {viewQuote.client_name}</p>
              </div>
              <div className="text-right">
                <Badge tone={quotationStatus(viewQuote.status).tone}>{quotationStatus(viewQuote.status).label}</Badge>
                <p className="mt-2 font-display text-2xl font-semibold text-navy-900">{money(viewQuote.total)}</p>
              </div>
            </div>
            <div className="table-shell">
              <table className="data-table">
                <thead><tr><th>Description</th><th className="text-right">Qty</th><th className="text-right">Unit</th><th className="text-right">Amount</th></tr></thead>
                <tbody>
                  {(viewQuote.items ?? []).map((item) => (
                    <tr key={item.id}><td>{item.description}</td><td className="text-right">{Number(item.quantity)}</td><td className="text-right">{money(item.unit_price)}</td><td className="text-right font-semibold">{money(Number(item.quantity) * Number(item.unit_price))}</td></tr>
                  ))}
                </tbody>
              </table>
            </div>
            {viewQuote.notes && <div className="rounded-xl bg-brand-50/50 p-3 text-[11.5px] text-ink-muted">{viewQuote.notes}</div>}
            {viewQuote.terms && <div className="rounded-xl border border-brand-100 p-3 text-[11px] text-ink-muted"><span className="font-semibold text-navy-900">Terms: </span>{viewQuote.terms}</div>}
            <div className="flex flex-wrap items-end gap-2 border-t border-brand-50 pt-4">
              <Field label="Set status" className="flex-1">
                <select className="input" value={viewQuote.status} onChange={(event) => updateStatus(viewQuote, event.target.value)}>
                  {statuses.map((item) => <option key={item} value={item}>{quotationStatus(item).label}</option>)}
                </select>
              </Field>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}

function CreateQuotationModal({
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
  const [form, setForm] = useState({ project_id: defaultProject, status: 'draft', discount: '', tax_percent: taxDefault, notes: '', terms: '' });
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
      await api('admin/quotations', {
        method: 'POST',
        body: {
          project_id: form.project_id,
          status: form.status,
          discount: form.discount || 0,
          tax_percent: form.tax_percent || 0,
          notes: form.notes,
          terms: form.terms,
          items: items.map((item) => ({ description: item.description, quantity: Number(item.quantity) || 0, unit_price: Number(item.unit_price) || 0 })),
        },
      });
      setItems([emptyLine()]);
      setForm({ project_id: defaultProject, status: 'draft', discount: '', tax_percent: taxDefault, notes: '', terms: '' });
      onCreated();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not create the quotation.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="New quotation" size="lg"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="create-quote" disabled={busy} className="btn-primary btn-sm">{busy ? 'Creating…' : 'Create quotation'}</button></>}
    >
      <form id="create-quote" onSubmit={submit} className="space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <Field label="Project *" className="sm:col-span-2">
            <select className="input" value={form.project_id} onChange={(event) => setForm({ ...form, project_id: event.target.value })} required>
              <option value="">Select a project…</option>
              {projects.map((project) => <option key={project.id} value={project.id}>{project.ref_no} — {project.title} ({project.client_name})</option>)}
            </select>
          </Field>
          <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}>{statuses.map((item) => <option key={item} value={item}>{quotationStatus(item).label}</option>)}</select></Field>
          <Field label="Tax %"><input className="input" type="number" min="0" step="0.01" value={form.tax_percent} onChange={(event) => setForm({ ...form, tax_percent: event.target.value })} /></Field>
        </div>
        <div className="rounded-xl border border-brand-100 p-3"><LineItems items={items} onChange={setItems} currency={currency} /></div>
        <div className="grid gap-4 sm:grid-cols-2">
          <Field label="Discount"><input className="input" type="number" min="0" step="0.01" value={form.discount} onChange={(event) => setForm({ ...form, discount: event.target.value })} /></Field>
          <div className="rounded-xl bg-brand-50/60 p-3 text-[12px]">
            <div className="flex justify-between"><span className="text-ink-muted">Subtotal</span><span className="font-medium">{money(subtotal, currency)}</span></div>
            <div className="flex justify-between"><span className="text-ink-muted">Tax</span><span className="font-medium">{money(total - taxable, currency)}</span></div>
            <div className="mt-1 flex justify-between border-t border-brand-100 pt-1 font-semibold text-navy-900"><span>Total</span><span>{money(total, currency)}</span></div>
          </div>
        </div>
        <Field label="Notes"><textarea className="input min-h-[70px]" value={form.notes} onChange={(event) => setForm({ ...form, notes: event.target.value })} /></Field>
        <Field label="Terms"><textarea className="input min-h-[70px]" value={form.terms} onChange={(event) => setForm({ ...form, terms: event.target.value })} placeholder="Validity, payment terms, assumptions…" /></Field>
      </form>
    </Modal>
  );
}
