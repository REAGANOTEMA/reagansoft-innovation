import { useEffect, useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import { ArrowUpRight, FolderKanban, Plus, Search } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi, useDebounced } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination, ProgressBar } from '../../components/UI';
import { formatDate } from '../../lib/format';
import { priorityTone, projectStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Project } from '../../lib/types';

interface ClientRow {
  id: number;
  full_name: string;
  email: string;
  company?: string | null;
}

interface StaffRow {
  id: number;
  full_name: string;
  role: string;
}

const statuses = ['NEW', 'REVIEWING', 'QUOTATION', 'APPROVED', 'IN_PROGRESS', 'WAITING_FOR_CLIENT', 'TESTING', 'COMPLETED', 'CANCELLED'];

export function ConsoleProjects() {
  usePageMeta('Projects — Reagansoft console');
  const { services, settings } = useSite();
  const { push } = useToast();
  const [query, setQuery] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const debounced = useDebounced(query);

  const { data, meta, loading, error, reload } = useApi<Project[]>('admin/projects', {
    page,
    status: status || undefined,
    q: debounced || undefined,
  });
  const clients = useApi<ClientRow[]>('admin/clients', { page: 1 });
  const staff = useApi<StaffRow[]>('admin/staff');

  useEffect(() => {
    setPage(1);
  }, [status, debounced]);

  const totalPages = Number(meta?.total_pages ?? 1);
  const counts = (meta?.status_counts as { status: string; c: number }[] | undefined) ?? [];

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Delivery"
        title="Projects"
        description="Every client request and engagement, from first review to delivery."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> New project</button>}
      />

      <div className="no-scrollbar flex gap-2 overflow-x-auto pb-1">
        <button
          type="button"
          onClick={() => setStatus('')}
          className={cn('shrink-0 rounded-full border px-3.5 py-1.5 text-[11px] font-semibold transition', status === '' ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-100 bg-white text-ink-muted hover:border-brand-200')}
        >
          All
        </button>
        {statuses.map((item) => {
          const metaStatus = projectStatus(item);
          const count = counts.find((row) => row.status === item)?.c ?? 0;
          return (
            <button
              key={item}
              type="button"
              onClick={() => setStatus(item)}
              className={cn('shrink-0 rounded-full border px-3.5 py-1.5 text-[11px] font-semibold transition', status === item ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-100 bg-white text-ink-muted hover:border-brand-200')}
            >
              {metaStatus.label} <span className="ml-1 opacity-60">{count}</span>
            </button>
          );
        })}
      </div>

      <div className="flex items-center gap-2 rounded-2xl border border-brand-100 bg-white px-3 py-2.5">
        <Search className="h-4 w-4 text-ink-faint" />
        <input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder="Search by title, reference, client or company…"
          className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint"
          aria-label="Search projects"
        />
      </div>

      {loading ? (
        <LoadingBlock label="Loading projects…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<FolderKanban className="h-6 w-6" />} title="No projects found" message={query || status ? 'Try clearing the filters.' : 'Create the first project to get started.'} />
      ) : (
        <>
          <div className="table-shell overflow-x-auto">
            <table className="data-table min-w-[900px]">
              <thead>
                <tr>
                  <th>Project</th>
                  <th>Client</th>
                  <th>Service</th>
                  <th>Status</th>
                  <th>Priority</th>
                  <th className="w-28">Progress</th>
                  <th>Deadline</th>
                  <th />
                </tr>
              </thead>
              <tbody>
                {data.map((project) => {
                  const metaStatus = projectStatus(project.status);
                  return (
                    <tr key={project.id}>
                      <td>
                        <Link to={`/console/projects/${project.id}`} className="block">
                          <span className="block text-[12.5px] font-semibold text-navy-900">{project.title}</span>
                          <span className="font-mono text-[9px] text-ink-faint">{project.ref_no}</span>
                        </Link>
                      </td>
                      <td>
                        <span className="block text-[11.5px] font-medium text-navy-900">{project.client_name}</span>
                        <span className="text-[10px] text-ink-faint">{project.company ?? project.client_email}</span>
                      </td>
                      <td className="text-[11px] text-ink-muted">{project.service ?? 'Custom'}</td>
                      <td><Badge tone={metaStatus.tone}>{metaStatus.label}</Badge></td>
                      <td><Badge tone={priorityTone(project.priority)}>{project.priority}</Badge></td>
                      <td>
                        <div className="flex items-center gap-2">
                          <ProgressBar value={project.progress} className="w-16" />
                          <span className="font-mono text-[9px] text-brand-700">{project.progress}%</span>
                        </div>
                      </td>
                      <td className="text-[10.5px] text-ink-muted">{project.deadline ? formatDate(project.deadline) : '—'}</td>
                      <td>
                        <Link to={`/console/projects/${project.id}`} className="inline-flex items-center gap-1 text-[10.5px] font-semibold text-brand-700">
                          Open <ArrowUpRight className="h-3 w-3" />
                        </Link>
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

      <CreateProjectModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        onCreated={() => {
          setCreateOpen(false);
          reload();
          push('success', 'Project created.');
        }}
        clients={clients.data ?? []}
        staff={staff.data ?? []}
        services={services}
        currency={settings.currency}
      />
    </div>
  );
}

function CreateProjectModal({
  open,
  onClose,
  onCreated,
  clients,
  staff,
  services,
  currency,
}: {
  open: boolean;
  onClose: () => void;
  onCreated: () => void;
  clients: ClientRow[];
  staff: StaffRow[];
  services: { id: number; name: string }[];
  currency: string;
}) {
  const { push } = useToast();
  const [form, setForm] = useState({
    client_id: '',
    title: '',
    description: '',
    requirements: '',
    service_id: '',
    assigned_to: '',
    priority: 'normal',
    status: 'NEW',
    budget: '',
    deadline: '',
  });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  function update(key: keyof typeof form, value: string) {
    setForm((current) => ({ ...current, [key]: value }));
    setErrors((current) => {
      if (!current[key]) return current;
      const next = { ...current };
      delete next[key];
      return next;
    });
  }

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setErrors({});
    try {
      await api('admin/projects', { method: 'POST', body: form });
      setForm({ client_id: '', title: '', description: '', requirements: '', service_id: '', assigned_to: '', priority: 'normal', status: 'NEW', budget: '', deadline: '' });
      onCreated();
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not create the project.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="New project" description="Create a project on behalf of a client." size="lg"
      footer={
        <>
          <button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button>
          <button type="submit" form="create-project" disabled={busy} className="btn-primary btn-sm">{busy ? 'Creating…' : 'Create project'}</button>
        </>
      }
    >
      <form id="create-project" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Client *" error={errors.client_id} className="sm:col-span-2">
          <select className="input" value={form.client_id} onChange={(event) => update('client_id', event.target.value)} required>
            <option value="">Select a client…</option>
            {clients.map((client) => <option key={client.id} value={client.id}>{client.full_name}{client.company ? ` — ${client.company}` : ''}</option>)}
          </select>
        </Field>
        <Field label="Title *" error={errors.title} className="sm:col-span-2">
          <input className="input" value={form.title} onChange={(event) => update('title', event.target.value)} required placeholder="e.g. Farm produce ordering platform" />
        </Field>
        <Field label="Description *" error={errors.description} className="sm:col-span-2">
          <textarea className="input min-h-[90px]" value={form.description} onChange={(event) => update('description', event.target.value)} required />
        </Field>
        <Field label="Requirements" error={errors.requirements} className="sm:col-span-2">
          <textarea className="input min-h-[70px]" value={form.requirements} onChange={(event) => update('requirements', event.target.value)} />
        </Field>
        <Field label="Service" error={errors.service_id}>
          <select className="input" value={form.service_id} onChange={(event) => update('service_id', event.target.value)}>
            <option value="">Custom</option>
            {services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}
          </select>
        </Field>
        <Field label="Assigned to" error={errors.assigned_to}>
          <select className="input" value={form.assigned_to} onChange={(event) => update('assigned_to', event.target.value)}>
            <option value="">Unassigned</option>
            {staff.map((member) => <option key={member.id} value={member.id}>{member.full_name}</option>)}
          </select>
        </Field>
        <Field label="Status" error={errors.status}>
          <select className="input" value={form.status} onChange={(event) => update('status', event.target.value)}>
            {statuses.map((item) => <option key={item} value={item}>{projectStatus(item).label}</option>)}
          </select>
        </Field>
        <Field label="Priority" error={errors.priority}>
          <select className="input" value={form.priority} onChange={(event) => update('priority', event.target.value)}>
            {['low', 'normal', 'high', 'urgent'].map((item) => <option key={item} value={item}>{item}</option>)}
          </select>
        </Field>
        <Field label={`Budget (${currency})`} error={errors.budget}>
          <input className="input" type="number" min="0" step="1000" value={form.budget} onChange={(event) => update('budget', event.target.value)} />
        </Field>
        <Field label="Deadline" error={errors.deadline}>
          <input className="input" type="date" value={form.deadline} onChange={(event) => update('deadline', event.target.value)} />
        </Field>
      </form>
    </Modal>
  );
}
