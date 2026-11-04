import { useEffect, useState, type ChangeEvent, type FormEvent } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  ArrowLeft,
  ArrowRight,
  CalendarDays,
  Check,
  Download,
  FileText,
  FolderKanban,
  Paperclip,
  Plus,
  ReceiptText,
  Save,
  Send,
  Trash2,
  Upload,
  UserRound,
} from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, ProgressBar } from '../../components/UI';
import { formatDate, money, timeAgo } from '../../lib/format';
import { invoiceStatus, priorityTone, projectStatus, quotationStatus, taskStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Invoice, Message, Project, ProjectFile, Quotation, Task } from '../../lib/types';

interface Detail {
  project: Project;
  tasks: Task[];
  files: ProjectFile[];
  messages: Message[];
  invoices: Invoice[];
  quotations: Quotation[];
  milestones: string[];
  milestone_index: number;
}

interface StaffRow {
  id: number;
  full_name: string;
}

const statuses = ['NEW', 'REVIEWING', 'QUOTATION', 'APPROVED', 'IN_PROGRESS', 'WAITING_FOR_CLIENT', 'TESTING', 'COMPLETED', 'CANCELLED'];

export function ConsoleProjectDetail() {
  const { id = '' } = useParams();
  const { services, settings } = useSite();
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Detail>(`admin/projects/${encodeURIComponent(id)}`);
  const staff = useApi<StaffRow[]>('admin/staff');
  const [editOpen, setEditOpen] = useState(false);
  const [taskOpen, setTaskOpen] = useState(false);
  const [message, setMessage] = useState('');
  const [sending, setSending] = useState(false);
  const [uploading, setUploading] = useState(false);

  usePageMeta(data?.project ? `${data.project.title} — Reagansoft console` : 'Project — Reagansoft console');

  if (loading) return <LoadingBlock label="Loading project…" />;
  if (error || !data) return <ErrorBlock message={error || 'Project not found.'} onRetry={reload} />;

  const { project, tasks, files, messages, invoices, quotations, milestones, milestone_index: milestoneIndex } = data;
  const status = projectStatus(project.status);
  const completedTasks = tasks.filter((task) => task.status === 'COMPLETED').length;
  const invoiced = invoices.reduce((sum, invoice) => sum + Number(invoice.total), 0);

  async function sendMessage(event: FormEvent) {
    event.preventDefault();
    if (!message.trim()) return;
    setSending(true);
    try {
      await api(`admin/projects/${project.id}/messages`, { method: 'POST', body: { message } });
      setMessage('');
      push('success', 'Message sent to the client.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not send the message.');
    } finally {
      setSending(false);
    }
  }

  async function uploadFile(event: ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0];
    if (!file) return;
    const form = new FormData();
    form.append('file', file);
    setUploading(true);
    try {
      await api(`admin/projects/${project.id}/files`, { method: 'POST', body: form });
      push('success', 'File uploaded.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not upload the file.');
    } finally {
      setUploading(false);
      event.target.value = '';
    }
  }

  async function updateTask(task: Task, patch: Record<string, unknown>) {
    try {
      await api(`admin/tasks/${task.id}`, { method: 'PATCH', body: patch });
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the task.');
    }
  }

  async function deleteTask(task: Task) {
    if (!window.confirm(`Delete “${task.title}”?`)) return;
    try {
      await api(`admin/tasks/${task.id}`, { method: 'DELETE' });
      push('success', 'Task deleted.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not delete the task.');
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Link to="/console/projects" className="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-ink-muted transition hover:text-brand-700">
          <ArrowLeft className="h-3.5 w-3.5" /> All projects
        </Link>
        <span className="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-faint">{project.ref_no}</span>
      </div>

      <PageHeader
        eyebrow={project.service ?? 'Custom project'}
        title={project.title}
        description={`${project.client_name}${project.company ? ` · ${project.company}` : ''} · submitted ${formatDate(project.submitted_at)}`}
        actions={
          <>
            <Link to={`/console/clients/${project.client_id}`} className="btn-ghost btn-sm"><UserRound className="h-3.5 w-3.5" /> Client</Link>
            <button type="button" className="btn-primary btn-sm" onClick={() => setEditOpen(true)}><Save className="h-3.5 w-3.5" /> Manage</button>
          </>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div className="rounded-2xl border border-brand-100 bg-white p-4">
          <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Status</p>
          <div className="mt-1.5 flex items-center gap-2"><Badge tone={status.tone}>{status.label}</Badge><Badge tone={priorityTone(project.priority)}>{project.priority}</Badge></div>
        </div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4">
          <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Progress</p>
          <div className="mt-2 flex items-center gap-2"><ProgressBar value={project.progress} className="flex-1" /><span className="font-mono text-[10px] font-semibold text-brand-700">{project.progress}%</span></div>
        </div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4">
          <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Tasks</p>
          <p className="mt-1.5 text-[13px] font-semibold text-navy-900">{completedTasks} / {tasks.length} complete</p>
        </div>
        <div className="rounded-2xl border border-brand-100 bg-white p-4">
          <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Invoiced</p>
          <p className="mt-1.5 text-[13px] font-semibold text-navy-900">{money(invoiced)}</p>
        </div>
      </div>

      <div className="rounded-2xl border border-brand-100 bg-white p-5">
        <div className="flex items-center justify-between">
          <h2 className="font-display text-sm font-semibold text-navy-900">Delivery milestones</h2>
          <span className="text-[10.5px] text-ink-faint">{project.deadline ? `Target ${formatDate(project.deadline)}` : 'No deadline set'}</span>
        </div>
        <div className="no-scrollbar mt-4 flex gap-2 overflow-x-auto pb-1">
          {milestones.map((label, index) => {
            const done = project.status !== 'CANCELLED' && index < milestoneIndex;
            const current = project.status !== 'CANCELLED' && index === milestoneIndex;
            return (
              <div key={label} className={cn('min-w-[118px] flex-1 rounded-xl border px-3 py-2.5', done ? 'border-emerald-100 bg-emerald-50/70' : current ? 'border-brand-200 bg-brand-50' : 'border-brand-50 bg-white')}>
                <div className="flex items-center gap-1.5">
                  <span className={cn('grid h-4 w-4 place-items-center rounded-full text-[8px] font-bold', done ? 'bg-emerald-500 text-white' : current ? 'bg-brand-600 text-white' : 'bg-brand-100 text-brand-500')}>
                    {done ? <Check className="h-2.5 w-2.5" strokeWidth={3} /> : index + 1}
                  </span>
                  <span className={cn('text-[9.5px] font-semibold leading-tight', done ? 'text-emerald-800' : current ? 'text-brand-800' : 'text-ink-faint')}>{label}</span>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      <div className="grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
        <div className="space-y-5">
          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Tasks</h2>
              <button type="button" className="btn-ghost btn-sm" onClick={() => setTaskOpen(true)}><Plus className="h-3.5 w-3.5" /> Add task</button>
            </div>
            <div className="mt-3 space-y-2">
              {tasks.length === 0 ? (
                <p className="rounded-xl bg-brand-50/50 px-3 py-5 text-center text-[11px] text-ink-faint">No tasks yet. Add the first work item.</p>
              ) : (
                tasks.map((task) => {
                  const meta = taskStatus(task.status);
                  return (
                    <div key={task.id} className="rounded-xl border border-brand-100 p-3">
                      <div className="flex flex-wrap items-start justify-between gap-2">
                        <div>
                          <p className="text-[12px] font-semibold text-navy-900">{task.title}</p>
                          <p className="text-[10px] text-ink-faint">{task.assignee ?? 'Unassigned'}{task.due_date ? ` · due ${formatDate(task.due_date)}` : ''}</p>
                        </div>
                        <div className="flex items-center gap-2">
                          <select
                            value={task.status}
                            onChange={(event) => updateTask(task, { status: event.target.value })}
                            className="rounded-lg border border-brand-100 bg-white px-2 py-1 text-[10.5px] outline-none"
                          >
                            {['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'].map((item) => <option key={item} value={item}>{taskStatus(item).label}</option>)}
                          </select>
                          <button type="button" onClick={() => deleteTask(task)} className="rounded-lg p-1.5 text-ink-faint transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete task"><Trash2 className="h-3.5 w-3.5" /></button>
                        </div>
                      </div>
                      <div className="mt-2 flex items-center gap-2">
                        <input
                          type="range"
                          min="0"
                          max="100"
                          step="5"
                          value={task.progress}
                          onChange={(event) => updateTask(task, { progress: Number(event.target.value) })}
                          className="h-1.5 flex-1 accent-brand-600"
                          aria-label={`Progress for ${task.title}`}
                        />
                        <Badge tone={meta.tone}>{task.progress}%</Badge>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <h2 className="font-display text-sm font-semibold text-navy-900">Project conversation</h2>
              <label className="btn-ghost btn-sm cursor-pointer">
                <Paperclip className="h-3.5 w-3.5" /> {uploading ? 'Uploading…' : 'Share file'}
                <input type="file" className="hidden" onChange={uploadFile} disabled={uploading} />
              </label>
            </div>
            <div className="mt-4 max-h-[360px] space-y-3 overflow-y-auto pr-1">
              {messages.length === 0 ? (
                <p className="rounded-xl bg-brand-50/50 px-3 py-5 text-center text-[11px] text-ink-faint">No messages yet.</p>
              ) : (
                messages.map((item) => {
                  const mine = item.sender_role !== 'client';
                  return (
                    <div key={item.id} className={cn('flex', mine ? 'justify-end' : 'justify-start')}>
                      <div className={cn('max-w-[82%] rounded-2xl px-3.5 py-2.5', mine ? 'bg-brand-600 text-white' : 'bg-brand-50 text-ink')}>
                        <p className={cn('text-[9.5px] font-semibold uppercase tracking-wider', mine ? 'text-white/60' : 'text-brand-700')}>{item.sender ?? (mine ? 'Team' : 'Client')}</p>
                        <p className="mt-1 whitespace-pre-line text-[12px] leading-relaxed">{item.message}</p>
                        <p className={cn('mt-1 text-[9px]', mine ? 'text-white/50' : 'text-ink-faint')}>{formatDate(item.created_at, true)}</p>
                      </div>
                    </div>
                  );
                })
              )}
            </div>
            <form onSubmit={sendMessage} className="mt-4 border-t border-brand-50 pt-4">
              <textarea className="input min-h-[80px]" value={message} onChange={(event) => setMessage(event.target.value)} placeholder="Reply to the client…" />
              <div className="mt-2 flex justify-end">
                <button type="submit" disabled={sending || !message.trim()} className="btn-primary btn-sm">{sending ? 'Sending…' : 'Send message'} <Send className="h-3.5 w-3.5" /></button>
              </div>
            </form>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Brief & requirements</h2>
            <p className="mt-2 whitespace-pre-line text-[12px] leading-relaxed text-ink-muted">{project.description}</p>
            {project.requirements && <p className="mt-3 whitespace-pre-line border-t border-brand-50 pt-3 text-[12px] leading-relaxed text-ink-muted">{project.requirements}</p>}
          </section>
        </div>

        <div className="space-y-5">
          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Client</h2>
            <p className="mt-2 text-[12.5px] font-semibold text-navy-900">{project.client_name}</p>
            <p className="text-[11px] text-ink-muted">{project.company ?? 'Individual'}</p>
            <p className="mt-1 text-[11px] text-ink-muted">{project.client_email}</p>
            <p className="text-[11px] text-ink-muted">{project.client_phone ?? '—'}</p>
            <Link to={`/console/clients/${project.client_id}`} className="btn-ghost btn-sm mt-3 w-full">Open client <ArrowRight className="h-3.5 w-3.5" /></Link>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Billing</h2>
            <div className="mt-3 space-y-2">
              {invoices.length === 0 && quotations.length === 0 && <p className="text-[11px] text-ink-faint">Nothing raised yet.</p>}
              {invoices.map((invoice) => {
                const meta = invoiceStatus(invoice.status);
                return (
                  <div key={invoice.id} className="flex items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2">
                    <div>
                      <p className="font-mono text-[10.5px] font-semibold text-navy-900">{invoice.invoice_no}</p>
                      <p className="text-[9.5px] text-ink-faint">{money(invoice.total)} · due {formatDate(invoice.due_date)}</p>
                    </div>
                    <Badge tone={meta.tone}>{meta.label}</Badge>
                  </div>
                );
              })}
              {quotations.map((quote) => {
                const meta = quotationStatus(quote.status);
                return (
                  <div key={quote.id} className="flex items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2">
                    <div>
                      <p className="font-mono text-[10.5px] font-semibold text-navy-900">{quote.quotation_no}</p>
                      <p className="text-[9.5px] text-ink-faint">{money(quote.total)} · issued {formatDate(quote.issued_on)}</p>
                    </div>
                    <Badge tone={meta.tone}>{meta.label}</Badge>
                  </div>
                );
              })}
            </div>
            <div className="mt-3 grid gap-2">
              <Link to={`/console/invoices?project=${project.id}`} className="btn-ghost btn-sm"><ReceiptText className="h-3.5 w-3.5" /> Create invoice</Link>
              <Link to={`/console/quotations?project=${project.id}`} className="btn-ghost btn-sm"><FileText className="h-3.5 w-3.5" /> Create quotation</Link>
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Files</h2>
              <label className="cursor-pointer text-[10.5px] font-semibold text-brand-700">
                <Upload className="mr-1 inline h-3 w-3" />{uploading ? 'Uploading…' : 'Upload'}
                <input type="file" className="hidden" onChange={uploadFile} disabled={uploading} />
              </label>
            </div>
            <div className="mt-3 space-y-2">
              {files.length === 0 ? (
                <p className="text-[11px] text-ink-faint">No files shared.</p>
              ) : (
                files.map((file) => (
                  <div key={file.id} className="flex items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2">
                    <div className="min-w-0">
                      <p className="truncate text-[10.5px] font-semibold text-navy-900">{file.original_name}</p>
                      <p className="text-[9px] text-ink-faint">{file.uploader ?? 'Team'} · {formatDate(file.created_at)}</p>
                    </div>
                    <a href={file.url} target="_blank" rel="noreferrer" className="rounded-lg p-1.5 text-brand-700 hover:bg-brand-50" aria-label="Download"><Download className="h-3.5 w-3.5" /></a>
                  </div>
                ))
              )}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5 text-[11px] text-ink-muted">
            <p className="flex items-center gap-1.5"><CalendarDays className="h-3.5 w-3.5 text-brand-500" /> Updated {timeAgo(project.updated_at)}</p>
            <p className="mt-1.5 flex items-center gap-1.5"><FolderKanban className="h-3.5 w-3.5 text-brand-500" /> Budget {project.budget ? money(project.budget) : 'not set'}</p>
            <p className="mt-1.5 flex items-center gap-1.5"><UserRound className="h-3.5 w-3.5 text-brand-500" /> Owner {project.assignee ?? 'unassigned'}</p>
          </section>
        </div>
      </div>

      <EditProjectModal
        open={editOpen}
        onClose={() => setEditOpen(false)}
        project={project}
        staff={staff.data ?? []}
        services={services}
        currency={settings.currency}
        onSaved={() => {
          setEditOpen(false);
          reload();
          push('success', 'Project updated.');
        }}
      />

      <AddTaskModal
        open={taskOpen}
        onClose={() => setTaskOpen(false)}
        projectId={project.id}
        staff={staff.data ?? []}
        onCreated={() => {
          setTaskOpen(false);
          reload();
          push('success', 'Task added.');
        }}
      />
    </div>
  );
}

function EditProjectModal({
  open,
  onClose,
  project,
  staff,
  services,
  currency,
  onSaved,
}: {
  open: boolean;
  onClose: () => void;
  project: Project;
  staff: StaffRow[];
  services: { id: number; name: string }[];
  currency: string;
  onSaved: () => void;
}) {
  const { push } = useToast();
  const [form, setForm] = useState({
    title: project.title,
    status: project.status,
    priority: project.priority,
    progress: String(project.progress),
    assigned_to: project.assigned_to ? String(project.assigned_to) : '',
    service_id: project.service_id ? String(project.service_id) : '',
    budget: project.budget ? String(project.budget) : '',
    deadline: project.deadline ? project.deadline.slice(0, 10) : '',
    website_url: project.website_url ?? '',
    deliverables: project.deliverables ?? '',
    requirements: project.requirements ?? '',
  });
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!open) return;
    setForm({
      title: project.title,
      status: project.status,
      priority: project.priority,
      progress: String(project.progress),
      assigned_to: project.assigned_to ? String(project.assigned_to) : '',
      service_id: project.service_id ? String(project.service_id) : '',
      budget: project.budget ? String(project.budget) : '',
      deadline: project.deadline ? project.deadline.slice(0, 10) : '',
      website_url: project.website_url ?? '',
      deliverables: project.deliverables ?? '',
      requirements: project.requirements ?? '',
    });
  }, [open, project]);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api(`admin/projects/${project.id}`, {
        method: 'PATCH',
        body: {
          ...form,
          assigned_to: form.assigned_to || 0,
          service_id: form.service_id || 0,
        },
      });
      onSaved();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the project.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="Manage project" description={project.ref_no} size="lg"
      footer={
        <>
          <button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button>
          <button type="submit" form="edit-project" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Save changes'}</button>
        </>
      }
    >
      <form id="edit-project" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Title" className="sm:col-span-2"><input className="input" value={form.title} onChange={(event) => setForm({ ...form, title: event.target.value })} required /></Field>
        <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}>{statuses.map((item) => <option key={item} value={item}>{projectStatus(item).label}</option>)}</select></Field>
        <Field label="Priority"><select className="input" value={form.priority} onChange={(event) => setForm({ ...form, priority: event.target.value as Project['priority'] })}>{['low', 'normal', 'high', 'urgent'].map((item) => <option key={item} value={item}>{item}</option>)}</select></Field>
        <Field label="Progress (%)"><input className="input" type="number" min="0" max="100" value={form.progress} onChange={(event) => setForm({ ...form, progress: event.target.value })} /></Field>
        <Field label="Assigned to"><select className="input" value={form.assigned_to} onChange={(event) => setForm({ ...form, assigned_to: event.target.value })}><option value="">Unassigned</option>{staff.map((member) => <option key={member.id} value={member.id}>{member.full_name}</option>)}</select></Field>
        <Field label="Service"><select className="input" value={form.service_id} onChange={(event) => setForm({ ...form, service_id: event.target.value })}><option value="">Custom</option>{services.map((service) => <option key={service.id} value={service.id}>{service.name}</option>)}</select></Field>
        <Field label={`Budget (${currency})`}><input className="input" type="number" min="0" step="1000" value={form.budget} onChange={(event) => setForm({ ...form, budget: event.target.value })} /></Field>
        <Field label="Deadline"><input className="input" type="date" value={form.deadline} onChange={(event) => setForm({ ...form, deadline: event.target.value })} /></Field>
        <Field label="Website URL"><input className="input" value={form.website_url} onChange={(event) => setForm({ ...form, website_url: event.target.value })} placeholder="https://…" /></Field>
        <Field label="Deliverables" className="sm:col-span-2"><input className="input" value={form.deliverables} onChange={(event) => setForm({ ...form, deliverables: event.target.value })} /></Field>
        <Field label="Requirements" className="sm:col-span-2"><textarea className="input min-h-[80px]" value={form.requirements} onChange={(event) => setForm({ ...form, requirements: event.target.value })} /></Field>
      </form>
    </Modal>
  );
}

function AddTaskModal({
  open,
  onClose,
  projectId,
  staff,
  onCreated,
}: {
  open: boolean;
  onClose: () => void;
  projectId: number;
  staff: StaffRow[];
  onCreated: () => void;
}) {
  const { push } = useToast();
  const [form, setForm] = useState({ title: '', description: '', assigned_to: '', priority: 'medium', due_date: '', progress: '0', status: 'TODO' });
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api('admin/tasks', { method: 'POST', body: { ...form, project_id: projectId, assigned_to: form.assigned_to || 0 } });
      setForm({ title: '', description: '', assigned_to: '', priority: 'medium', due_date: '', progress: '0', status: 'TODO' });
      onCreated();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not add the task.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="Add task" size="md"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="add-task" disabled={busy} className="btn-primary btn-sm">{busy ? 'Adding…' : 'Add task'}</button></>}
    >
      <form id="add-task" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Task title *" className="sm:col-span-2"><input className="input" value={form.title} onChange={(event) => setForm({ ...form, title: event.target.value })} required /></Field>
        <Field label="Description" className="sm:col-span-2"><textarea className="input min-h-[70px]" value={form.description} onChange={(event) => setForm({ ...form, description: event.target.value })} /></Field>
        <Field label="Assign to"><select className="input" value={form.assigned_to} onChange={(event) => setForm({ ...form, assigned_to: event.target.value })}><option value="">Unassigned</option>{staff.map((member) => <option key={member.id} value={member.id}>{member.full_name}</option>)}</select></Field>
        <Field label="Priority"><select className="input" value={form.priority} onChange={(event) => setForm({ ...form, priority: event.target.value })}>{['low', 'medium', 'high'].map((item) => <option key={item} value={item}>{item}</option>)}</select></Field>
        <Field label="Due date"><input className="input" type="date" value={form.due_date} onChange={(event) => setForm({ ...form, due_date: event.target.value })} /></Field>
        <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}>{['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'].map((item) => <option key={item} value={item}>{taskStatus(item).label}</option>)}</select></Field>
      </form>
    </Modal>
  );
}
