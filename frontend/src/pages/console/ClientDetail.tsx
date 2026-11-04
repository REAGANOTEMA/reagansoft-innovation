import { useEffect, useState, type FormEvent } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, ArrowUpRight, Ban, CheckCircle2, Save, UserRound } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, ErrorBlock, Field, LoadingBlock, PageHeader, ProgressBar } from '../../components/UI';
import { formatDate, money, timeAgo } from '../../lib/format';
import { invoiceStatus, projectStatus } from '../../lib/status';
import type { Invoice, Project, User } from '../../lib/types';

interface Detail {
  client: User & { created_at?: string; last_login_at?: string | null };
  projects: Project[];
  invoices: Invoice[];
}

export function ConsoleClientDetail() {
  const { id = '' } = useParams();
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Detail>(`admin/clients/${encodeURIComponent(id)}`);
  const [form, setForm] = useState({
    full_name: '',
    phone: '',
    company: '',
    address: '',
    city: '',
    country: '',
    active: true,
    password: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  usePageMeta(data?.client ? `${data.client.full_name} — Reagansoft console` : 'Client — Reagansoft console');

  useEffect(() => {
    if (!data) return;
    setForm({
      full_name: data.client.full_name ?? '',
      phone: data.client.phone ?? '',
      company: data.client.company ?? '',
      address: data.client.address ?? '',
      city: data.client.city ?? '',
      country: data.client.country ?? '',
      active: Boolean(data.client.active),
      password: '',
    });
  }, [data]);

  if (loading) return <LoadingBlock label="Loading client…" />;
  if (error || !data) return <ErrorBlock message={error || 'Client not found.'} onRetry={reload} />;

  const { client, projects, invoices } = data;
  const outstanding = invoices.reduce((sum, invoice) => sum + Math.max(0, Number(invoice.total) - Number(invoice.amount_paid)), 0);

  async function save(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setErrors({});
    try {
      const body: Record<string, unknown> = {
        full_name: form.full_name,
        phone: form.phone,
        company: form.company,
        address: form.address,
        city: form.city,
        country: form.country,
        active: form.active,
      };
      if (form.password) body.password = form.password;
      await api(`admin/clients/${client.id}`, { method: 'PATCH', body });
      push('success', 'Client updated.');
      setForm((current) => ({ ...current, password: '' }));
      reload();
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not update the client.');
      }
    } finally {
      setBusy(false);
    }
  }

  async function toggleActive() {
    const next = !client.active;
    if (!window.confirm(next ? 'Reactivate this client account?' : 'Deactivate this client account? They will not be able to sign in.')) return;
    try {
      if (next) {
        await api(`admin/clients/${client.id}`, { method: 'PATCH', body: { active: true } });
      } else {
        await api(`admin/clients/${client.id}`, { method: 'DELETE' });
      }
      push('success', next ? 'Client reactivated.' : 'Client deactivated.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not change the account status.');
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Link to="/console/clients" className="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-ink-muted transition hover:text-brand-700">
          <ArrowLeft className="h-3.5 w-3.5" /> All clients
        </Link>
        <button type="button" onClick={toggleActive} className={client.active ? 'btn-ghost btn-sm text-rose-600' : 'btn-ghost btn-sm text-emerald-700'}>
          {client.active ? <><Ban className="h-3.5 w-3.5" /> Deactivate</> : <><CheckCircle2 className="h-3.5 w-3.5" /> Reactivate</>}
        </button>
      </div>

      <PageHeader
        eyebrow="Client"
        title={client.full_name}
        description={`${client.company ?? 'Individual'} · ${client.email}${client.phone ? ` · ${client.phone}` : ''}`}
        actions={<Badge tone={client.active ? 'green' : 'gray'}>{client.active ? 'Active account' : 'Inactive'}</Badge>}
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="rounded-2xl border border-brand-100 bg-white p-4"><p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Projects</p><p className="mt-1.5 font-display text-lg font-semibold text-navy-900">{projects.length}</p></div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4"><p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Invoices</p><p className="mt-1.5 font-display text-lg font-semibold text-navy-900">{invoices.length}</p></div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4"><p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Outstanding</p><p className="mt-1.5 font-display text-lg font-semibold text-navy-900">{money(outstanding)}</p></div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4"><p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Last sign-in</p><p className="mt-1.5 text-[12px] font-semibold text-navy-900">{client.last_login_at ? timeAgo(client.last_login_at) : 'Never'}</p></div>
      </div>

      <div className="grid gap-5 xl:grid-cols-[1.15fr_0.85fr]">
        <form onSubmit={save} className="rounded-2xl border border-brand-100 bg-white p-5">
          <div className="flex items-center gap-2"><UserRound className="h-4 w-4 text-brand-600" /><h2 className="font-display text-sm font-semibold text-navy-900">Client details</h2></div>
          <div className="mt-4 grid gap-4 sm:grid-cols-2">
            <Field label="Full name" error={errors.full_name} className="sm:col-span-2"><input className="input" value={form.full_name} onChange={(event) => setForm({ ...form, full_name: event.target.value })} required /></Field>
            <Field label="Email" hint="Email changes must be handled by support."><input className="input bg-brand-50/50" value={client.email} readOnly /></Field>
            <Field label="Phone" error={errors.phone}><input className="input" type="tel" value={form.phone} onChange={(event) => setForm({ ...form, phone: event.target.value })} /></Field>
            <Field label="Company" error={errors.company}><input className="input" value={form.company} onChange={(event) => setForm({ ...form, company: event.target.value })} /></Field>
            <Field label="Country" error={errors.country}><input className="input" value={form.country} onChange={(event) => setForm({ ...form, country: event.target.value })} /></Field>
            <Field label="Address" error={errors.address} className="sm:col-span-2"><input className="input" value={form.address} onChange={(event) => setForm({ ...form, address: event.target.value })} /></Field>
            <Field label="City / district" error={errors.city}><input className="input" value={form.city} onChange={(event) => setForm({ ...form, city: event.target.value })} /></Field>
            <Field label="Set new password" error={errors.password} hint="Leave blank to keep the current password."><input className="input" type="text" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} /></Field>
          </div>
          <div className="mt-4 flex items-center justify-between gap-3 border-t border-brand-50 pt-4">
            <label className="flex items-center gap-2 text-[11.5px] text-ink-muted">
              <input type="checkbox" checked={form.active} onChange={(event) => setForm({ ...form, active: event.target.checked })} className="h-4 w-4 rounded border-brand-300 text-brand-600" />
              Account active
            </label>
            <button type="submit" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Save changes'} <Save className="h-3.5 w-3.5" /></button>
          </div>
        </form>

        <div className="space-y-5">
          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Projects</h2>
            <div className="mt-3 space-y-2">
              {projects.length === 0 ? <p className="text-[11px] text-ink-faint">No projects yet.</p> : projects.map((project) => {
                const meta = projectStatus(project.status);
                return (
                  <Link key={project.id} to={`/console/projects/${project.id}`} className="block rounded-xl border border-brand-100 px-3 py-2.5 transition hover:border-brand-200">
                    <div className="flex items-center justify-between gap-2">
                      <span className="text-[11.5px] font-semibold text-navy-900">{project.title}</span>
                      <Badge tone={meta.tone}>{meta.label}</Badge>
                    </div>
                    <div className="mt-1.5 flex items-center gap-2">
                      <ProgressBar value={project.progress} className="flex-1" />
                      <span className="font-mono text-[9px] text-brand-700">{project.progress}%</span>
                    </div>
                  </Link>
                );
              })}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Invoices</h2>
              <Link to="/console/invoices" className="inline-flex items-center gap-1 text-[10.5px] font-semibold text-brand-700">All <ArrowUpRight className="h-3 w-3" /></Link>
            </div>
            <div className="mt-3 space-y-2">
              {invoices.length === 0 ? <p className="text-[11px] text-ink-faint">No invoices yet.</p> : invoices.map((invoice) => {
                const meta = invoiceStatus(invoice.status);
                return (
                  <div key={invoice.id} className="flex items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2.5">
                    <div>
                      <p className="font-mono text-[10.5px] font-semibold text-navy-900">{invoice.invoice_no}</p>
                      <p className="text-[9.5px] text-ink-faint">{money(invoice.total)} · due {formatDate(invoice.due_date)}</p>
                    </div>
                    <Badge tone={meta.tone}>{meta.label}</Badge>
                  </div>
                );
              })}
            </div>
          </section>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/40 p-5 text-[11px] text-ink-muted">
            Joined {formatDate(client.created_at)} · account #{client.id}
          </div>
        </div>
      </div>
    </div>
  );
}
