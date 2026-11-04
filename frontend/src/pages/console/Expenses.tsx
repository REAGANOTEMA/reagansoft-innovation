import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Trash2, Wallet } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination } from '../../components/UI';
import { formatDate, money } from '../../lib/format';
import type { Expense, Project } from '../../lib/types';

const categories = ['Software', 'Hosting', 'Hardware', 'Transport', 'Marketing', 'Salaries', 'Utilities', 'Office', 'Other'];

export function ConsoleExpenses() {
  usePageMeta('Expenses — Reagansoft console');
  const { push } = useToast();
  const [category, setCategory] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const { data, meta, loading, error, reload } = useApi<Expense[]>('admin/expenses', { page, category: category || undefined });
  const projects = useApi<Project[]>('admin/projects', { page: 1 });

  useEffect(() => {
    setPage(1);
  }, [category]);

  const totalPages = Number(meta?.total_pages ?? 1);
  const total = Number(meta?.total ?? 0);

  async function remove(expense: Expense) {
    if (!window.confirm(`Delete “${expense.description}”?`)) return;
    try {
      await api(`admin/expenses/${expense.id}`, { method: 'DELETE' });
      push('success', 'Expense deleted.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not delete the expense.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Finance"
        title="Expenses"
        description="Track operating costs and project spend."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> Record expense</button>}
      />

      <div className="flex flex-wrap items-center justify-between gap-3">
        <select value={category} onChange={(event) => setCategory(event.target.value)} className="rounded-xl border border-brand-100 bg-white px-3 py-2.5 text-sm outline-none">
          <option value="">All categories</option>
          {categories.map((item) => <option key={item} value={item}>{item}</option>)}
        </select>
        <p className="text-[11.5px] text-ink-muted">Filtered total: <span className="font-semibold text-navy-900">{money(total)}</span></p>
      </div>

      {loading ? (
        <LoadingBlock label="Loading expenses…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<Wallet className="h-6 w-6" />} title="No expenses" message="Record the first expense to start tracking spend." />
      ) : (
        <>
          <div className="table-shell overflow-x-auto">
            <table className="data-table min-w-[760px]">
              <thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Project</th><th>Reference</th><th>Recorded by</th><th className="text-right">Amount</th><th /></tr></thead>
              <tbody>
                {data.map((expense) => (
                  <tr key={expense.id}>
                    <td className="text-[10.5px] text-ink-faint">{formatDate(expense.expense_date)}</td>
                    <td className="text-[11.5px] font-medium text-navy-900">{expense.description}</td>
                    <td className="text-[11px] text-ink-muted">{expense.category}</td>
                    <td className="text-[11px] text-ink-muted">{expense.project ?? '—'}</td>
                    <td className="font-mono text-[10px] text-ink-faint">{expense.reference ?? '—'}</td>
                    <td className="text-[10.5px] text-ink-muted">{expense.recorder ?? '—'}</td>
                    <td className="text-right font-mono text-[11px] font-semibold text-navy-900">{money(expense.amount, expense.currency)}</td>
                    <td><button type="button" onClick={() => remove(expense)} className="rounded-lg p-1.5 text-ink-faint hover:bg-rose-50 hover:text-rose-600" aria-label="Delete expense"><Trash2 className="h-3.5 w-3.5" /></button></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}

      <CreateExpenseModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        projects={projects.data ?? []}
        onCreated={() => {
          setCreateOpen(false);
          reload();
          push('success', 'Expense recorded.');
        }}
      />
    </div>
  );
}

function CreateExpenseModal({ open, onClose, projects, onCreated }: { open: boolean; onClose: () => void; projects: Project[]; onCreated: () => void }) {
  const { push } = useToast();
  const [form, setForm] = useState({ category: 'Software', description: '', amount: '', expense_date: new Date().toISOString().slice(0, 10), project_id: '', reference: '', notes: '' });
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api('admin/expenses', { method: 'POST', body: { ...form, project_id: form.project_id || 0 } });
      setForm({ category: 'Software', description: '', amount: '', expense_date: new Date().toISOString().slice(0, 10), project_id: '', reference: '', notes: '' });
      onCreated();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not record the expense.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="Record expense" size="md"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="expense-form" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Record expense'}</button></>}
    >
      <form id="expense-form" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Description *"><input className="input" value={form.description} onChange={(event) => setForm({ ...form, description: event.target.value })} required /></Field>
        <Field label="Category"><select className="input" value={form.category} onChange={(event) => setForm({ ...form, category: event.target.value })}>{categories.map((item) => <option key={item} value={item}>{item}</option>)}</select></Field>
        <Field label="Amount *"><input className="input" type="number" min="1" step="0.01" value={form.amount} onChange={(event) => setForm({ ...form, amount: event.target.value })} required /></Field>
        <Field label="Date"><input className="input" type="date" value={form.expense_date} onChange={(event) => setForm({ ...form, expense_date: event.target.value })} /></Field>
        <Field label="Project"><select className="input" value={form.project_id} onChange={(event) => setForm({ ...form, project_id: event.target.value })}><option value="">Not project-specific</option>{projects.map((project) => <option key={project.id} value={project.id}>{project.ref_no} — {project.title}</option>)}</select></Field>
        <Field label="Reference"><input className="input" value={form.reference} onChange={(event) => setForm({ ...form, reference: event.target.value })} /></Field>
        <Field label="Notes" className="sm:col-span-2"><textarea className="input min-h-[70px]" value={form.notes} onChange={(event) => setForm({ ...form, notes: event.target.value })} /></Field>
      </form>
    </Modal>
  );
}
