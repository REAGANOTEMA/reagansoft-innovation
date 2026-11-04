import { useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, Plus, Search, Users } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi, useDebounced } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination } from '../../components/UI';
import { formatDate, money, timeAgo } from '../../lib/format';

interface ClientRow {
  id: number;
  full_name: string;
  email: string;
  phone?: string | null;
  company?: string | null;
  city?: string | null;
  country?: string | null;
  active: number;
  created_at: string;
  last_login_at?: string | null;
  project_count: number;
  outstanding: number;
}

export function ConsoleClients() {
  usePageMeta('Clients — Reagansoft console');
  const [query, setQuery] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const [temp, setTemp] = useState<{ email: string; password: string } | null>(null);
  const debounced = useDebounced(query);
  const { data, meta, loading, error, reload } = useApi<ClientRow[]>('admin/clients', { page, q: debounced || undefined });

  useEffect(() => {
    setPage(1);
  }, [debounced]);

  const totalPages = Number(meta?.total_pages ?? 1);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Relationships"
        title="Clients"
        description="Every client account, their projects and outstanding balance."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> Add client</button>}
      />

      <div className="flex items-center gap-2 rounded-2xl border border-brand-100 bg-white px-3 py-2.5">
        <Search className="h-4 w-4 text-ink-faint" />
        <input value={query} onChange={(event) => setQuery(event.target.value)} placeholder="Search by name, email, company or phone…" className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint" aria-label="Search clients" />
      </div>

      {loading ? (
        <LoadingBlock label="Loading clients…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<Users className="h-6 w-6" />} title="No clients found" message={query ? 'Try a different search.' : 'Add your first client account.'} />
      ) : (
        <>
          <div className="table-shell overflow-x-auto">
            <table className="data-table min-w-[840px]">
              <thead>
                <tr><th>Client</th><th>Contact</th><th>Location</th><th className="text-right">Projects</th><th className="text-right">Outstanding</th><th>Status</th><th>Last seen</th><th /></tr>
              </thead>
              <tbody>
                {data.map((client) => (
                  <tr key={client.id}>
                    <td>
                      <Link to={`/console/clients/${client.id}`} className="block">
                        <span className="block text-[12.5px] font-semibold text-navy-900">{client.full_name}</span>
                        <span className="text-[10px] text-ink-faint">{client.company ?? 'Individual'} · joined {formatDate(client.created_at)}</span>
                      </Link>
                    </td>
                    <td>
                      <span className="block text-[11px] text-ink-muted">{client.email}</span>
                      <span className="text-[10px] text-ink-faint">{client.phone ?? '—'}</span>
                    </td>
                    <td className="text-[11px] text-ink-muted">{[client.city, client.country].filter(Boolean).join(', ') || '—'}</td>
                    <td className="text-right font-mono text-[11px] text-navy-900">{client.project_count}</td>
                    <td className="text-right font-mono text-[11px] font-semibold text-navy-900">{Number(client.outstanding) > 0 ? money(client.outstanding) : '—'}</td>
                    <td><Badge tone={client.active ? 'green' : 'gray'}>{client.active ? 'Active' : 'Inactive'}</Badge></td>
                    <td className="text-[10.5px] text-ink-faint">{client.last_login_at ? timeAgo(client.last_login_at) : 'Never'}</td>
                    <td><Link to={`/console/clients/${client.id}`} className="inline-flex items-center gap-1 text-[10.5px] font-semibold text-brand-700">Open <ArrowUpRight className="h-3 w-3" /></Link></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}

      <CreateClientModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        onCreated={(info) => {
          setCreateOpen(false);
          reload();
          if (info?.temporary_password) setTemp({ email: info.email, password: info.temporary_password });
        }}
      />

      <Modal open={Boolean(temp)} onClose={() => setTemp(null)} title="Client account created" description="Share these credentials securely. The password is shown once." size="sm"
        footer={<button type="button" className="btn-primary btn-sm" onClick={() => setTemp(null)}>Done</button>}
      >
        {temp && (
          <div className="space-y-3 text-[12px]">
            <div className="rounded-xl bg-brand-50 p-3"><p className="text-[9px] font-bold uppercase tracking-wider text-ink-faint">Email</p><p className="mt-0.5 font-mono text-[12px] text-navy-900">{temp.email}</p></div>
            <div className="rounded-xl bg-brand-50 p-3"><p className="text-[9px] font-bold uppercase tracking-wider text-ink-faint">Temporary password</p><p className="mt-0.5 font-mono text-[12px] text-navy-900">{temp.password}</p></div>
            <p className="text-[11px] text-ink-muted">Ask the client to change this password after signing in.</p>
          </div>
        )}
      </Modal>
    </div>
  );
}

function CreateClientModal({
  open,
  onClose,
  onCreated,
}: {
  open: boolean;
  onClose: () => void;
  onCreated: (info?: { email: string; temporary_password?: string | null }) => void;
}) {
  const { push } = useToast();
  const [form, setForm] = useState({ full_name: '', email: '', phone: '', company: '', password: '' });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setErrors({});
    try {
      const res = await api<{ id: number; temporary_password: string | null }>('admin/clients', { method: 'POST', body: form });
      push('success', 'Client account created.');
      setForm({ full_name: '', email: '', phone: '', company: '', password: '' });
      onCreated({ email: form.email, temporary_password: res.data.temporary_password });
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not create the client.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="Add client" description="Create a client account. Leave the password blank to generate one." size="md"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="create-client" disabled={busy} className="btn-primary btn-sm">{busy ? 'Creating…' : 'Create client'}</button></>}
    >
      <form id="create-client" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Full name *" error={errors.full_name}><input className="input" value={form.full_name} onChange={(event) => setForm({ ...form, full_name: event.target.value })} required /></Field>
        <Field label="Email *" error={errors.email}><input className="input" type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} required /></Field>
        <Field label="Phone" error={errors.phone}><input className="input" type="tel" value={form.phone} onChange={(event) => setForm({ ...form, phone: event.target.value })} /></Field>
        <Field label="Company" error={errors.company}><input className="input" value={form.company} onChange={(event) => setForm({ ...form, company: event.target.value })} /></Field>
        <Field label="Temporary password" hint="Minimum 8 characters. Leave blank to auto-generate." className="sm:col-span-2"><input className="input" type="text" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} /></Field>
      </form>
    </Modal>
  );
}
