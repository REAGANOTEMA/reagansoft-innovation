import { useEffect, useState, type FormEvent } from 'react';
import { ClipboardList, Plus, Trash2 } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader, Pagination } from '../../components/UI';
import { formatDate } from '../../lib/format';
import { priorityTone, taskStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Project, Task } from '../../lib/types';

const statuses = ['TODO', 'IN_PROGRESS', 'REVIEW', 'COMPLETED'];

interface StaffRow {
  id: number;
  full_name: string;
}

export function ConsoleTasks() {
  usePageMeta('Tasks — Reagansoft console');
  const { push } = useToast();
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [createOpen, setCreateOpen] = useState(false);
  const { data, meta, loading, error, reload } = useApi<Task[]>('admin/tasks', { page, status: status || undefined });
  const projects = useApi<Project[]>('admin/projects', { page: 1 });
  const staff = useApi<StaffRow[]>('admin/staff');

  useEffect(() => {
    setPage(1);
  }, [status]);

  const totalPages = Number(meta?.total_pages ?? 1);

  async function update(task: Task, patch: Record<string, unknown>) {
    try {
      await api(`admin/tasks/${task.id}`, { method: 'PATCH', body: patch });
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not update the task.');
    }
  }

  async function remove(task: Task) {
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
      <PageHeader
        eyebrow="Delivery"
        title="Tasks"
        description="Work items across every active project."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> New task</button>}
      />

      <div className="flex flex-wrap gap-1 rounded-xl border border-brand-100 bg-white p-1.5">
        <button type="button" onClick={() => setStatus('')} className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', status === '' ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50')}>All</button>
        {statuses.map((item) => (
          <button key={item} type="button" onClick={() => setStatus(item)} className={cn('rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', status === item ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50')}>{taskStatus(item).label}</button>
        ))}
      </div>

      {loading ? (
        <LoadingBlock label="Loading tasks…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<ClipboardList className="h-6 w-6" />} title="No tasks" message="Add tasks from a project or use the button above." />
      ) : (
        <>
          <div className="space-y-2">
            {data.map((task) => (
              <div key={task.id} className="rounded-2xl border border-brand-100 bg-white p-4">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                      <p className="text-[12.5px] font-semibold text-navy-900">{task.title}</p>
                      <Badge tone={priorityTone(task.priority)}>{task.priority}</Badge>
                    </div>
                    <p className="mt-0.5 text-[10.5px] text-ink-muted">{task.project}{task.ref_no ? ` · ${task.ref_no}` : ''} · {task.assignee ?? 'Unassigned'}</p>
                    {task.description && <p className="mt-1 line-clamp-2 text-[11px] text-ink-muted">{task.description}</p>}
                  </div>
                  <div className="flex items-center gap-2">
                    <select value={task.status} onChange={(event) => update(task, { status: event.target.value, ...(event.target.value === 'COMPLETED' ? { progress: 100 } : {}) })} className="rounded-lg border border-brand-100 bg-white px-2 py-1.5 text-[10.5px] outline-none">
                      {statuses.map((item) => <option key={item} value={item}>{taskStatus(item).label}</option>)}
                    </select>
                    <span className="font-mono text-[10px] text-brand-700">{task.progress}%</span>
                    <button type="button" onClick={() => remove(task)} className="rounded-lg p-1.5 text-ink-faint hover:bg-rose-50 hover:text-rose-600" aria-label="Delete task"><Trash2 className="h-3.5 w-3.5" /></button>
                  </div>
                </div>
                <div className="mt-2 flex flex-wrap items-center gap-3 text-[10px] text-ink-faint">
                  <span>{task.due_date ? `Due ${formatDate(task.due_date)}` : 'No due date'}</span>
                  <input type="range" min="0" max="100" step="5" value={task.progress} onChange={(event) => update(task, { progress: Number(event.target.value) })} className="h-1.5 w-32 accent-brand-600" aria-label={`Progress for ${task.title}`} />
                </div>
              </div>
            ))}
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}

      <CreateTaskModal
        open={createOpen}
        onClose={() => setCreateOpen(false)}
        projects={projects.data ?? []}
        staff={staff.data ?? []}
        onCreated={() => {
          setCreateOpen(false);
          reload();
          push('success', 'Task created.');
        }}
      />
    </div>
  );
}

function CreateTaskModal({ open, onClose, projects, staff, onCreated }: { open: boolean; onClose: () => void; projects: Project[]; staff: StaffRow[]; onCreated: () => void }) {
  const { push } = useToast();
  const [form, setForm] = useState({ project_id: '', title: '', description: '', assigned_to: '', priority: 'medium', due_date: '', status: 'TODO', progress: '0' });
  const [busy, setBusy] = useState(false);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api('admin/tasks', { method: 'POST', body: { ...form, assigned_to: form.assigned_to || 0 } });
      setForm({ project_id: '', title: '', description: '', assigned_to: '', priority: 'medium', due_date: '', status: 'TODO', progress: '0' });
      onCreated();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not create the task.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title="New task" size="md"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="new-task" disabled={busy} className="btn-primary btn-sm">{busy ? 'Creating…' : 'Create task'}</button></>}
    >
      <form id="new-task" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Project *" className="sm:col-span-2">
          <select className="input" value={form.project_id} onChange={(event) => setForm({ ...form, project_id: event.target.value })} required>
            <option value="">Select a project…</option>
            {projects.map((project) => <option key={project.id} value={project.id}>{project.ref_no} — {project.title}</option>)}
          </select>
        </Field>
        <Field label="Task title *" className="sm:col-span-2"><input className="input" value={form.title} onChange={(event) => setForm({ ...form, title: event.target.value })} required /></Field>
        <Field label="Description" className="sm:col-span-2"><textarea className="input min-h-[70px]" value={form.description} onChange={(event) => setForm({ ...form, description: event.target.value })} /></Field>
        <Field label="Assign to"><select className="input" value={form.assigned_to} onChange={(event) => setForm({ ...form, assigned_to: event.target.value })}><option value="">Unassigned</option>{staff.map((member) => <option key={member.id} value={member.id}>{member.full_name}</option>)}</select></Field>
        <Field label="Priority"><select className="input" value={form.priority} onChange={(event) => setForm({ ...form, priority: event.target.value })}>{['low', 'medium', 'high'].map((item) => <option key={item} value={item}>{item}</option>)}</select></Field>
        <Field label="Due date"><input className="input" type="date" value={form.due_date} onChange={(event) => setForm({ ...form, due_date: event.target.value })} /></Field>
        <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}>{statuses.map((item) => <option key={item} value={item}>{taskStatus(item).label}</option>)}</select></Field>
      </form>
    </Modal>
  );
}
