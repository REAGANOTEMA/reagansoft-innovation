import { useEffect, useState, type FormEvent } from 'react';
import { Plus, Shield, UserRound, UserX } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader } from '../../components/UI';
import { formatDate, initials } from '../../lib/format';
import { cn } from '../../lib/cn';
import type { User } from '../../lib/types';

export function ConsoleUsers() {
  usePageMeta('Users — Reagansoft console');
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<User[]>('admin/users');
  const [createOpen, setCreateOpen] = useState(false);
  const [editing, setEditing] = useState<User | null>(null);

  async function deactivate(user: User) {
    if (!window.confirm(`Deactivate ${user.full_name}? They will no longer be able to sign in.`)) return;
    try {
      await api(`admin/users/${user.id}`, { method: 'DELETE' });
      push('success', 'User deactivated.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not deactivate the user.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Administration"
        title="Users"
        description="Staff and administrator accounts with access to the console."
        actions={<button type="button" className="btn-primary btn-sm" onClick={() => setCreateOpen(true)}><Plus className="h-3.5 w-3.5" /> New user</button>}
      />

      {loading ? (
        <LoadingBlock label="Loading users…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<UserRound className="h-6 w-6" />} title="No users" message="Create a staff account to get started." />
      ) : (
        <div className="space-y-2.5">
          {data.map((user) => (
            <div key={user.id} className={cn('flex flex-wrap items-center justify-between gap-3 rounded-2xl border bg-white p-4', user.active ? 'border-brand-100' : 'border-brand-100 opacity-60')}>
              <div className="flex items-center gap-3">
                <span className={cn('grid h-10 w-10 place-items-center rounded-xl font-display text-[12px] font-semibold', user.role === 'admin' ? 'bg-navy-900 text-white' : user.role === 'staff' ? 'bg-brand-600 text-white' : 'bg-brand-50 text-brand-700')}>{initials(user.full_name)}</span>
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <p className="text-[12.5px] font-semibold text-navy-900">{user.full_name}</p>
                    <Badge tone={user.role === 'admin' ? 'violet' : user.role === 'staff' ? 'blue' : 'gray'}>{user.role}</Badge>
                    {!user.active && <Badge tone="red">inactive</Badge>}
                  </div>
                  <p className="text-[10.5px] text-ink-muted">{user.email}{user.phone ? ` · ${user.phone}` : ''}</p>
                  <p className="text-[10px] text-ink-faint">Last sign-in {formatDate(user.last_login_at, true)}</p>
                </div>
              </div>
              <div className="flex items-center gap-2">
                <button type="button" className="btn-ghost btn-sm" onClick={() => setEditing(user)}><Shield className="h-3.5 w-3.5" /> Edit</button>
                {user.active ? <button type="button" onClick={() => deactivate(user)} className="rounded-lg p-2 text-ink-faint hover:bg-rose-50 hover:text-rose-600" aria-label="Deactivate user"><UserX className="h-3.5 w-3.5" /></button> : null}
              </div>
            </div>
          ))}
        </div>
      )}

      <UserModal open={createOpen} onClose={() => setCreateOpen(false)} onSaved={() => { setCreateOpen(false); reload(); push('success', 'User created.'); }} />
      <UserModal open={Boolean(editing)} user={editing ?? undefined} onClose={() => setEditing(null)} onSaved={() => { setEditing(null); reload(); push('success', 'User updated.'); }} />
    </div>
  );
}

function UserModal({ open, user, onClose, onSaved }: { open: boolean; user?: User; onClose: () => void; onSaved: () => void }) {
  const { push } = useToast();
  const [form, setForm] = useState({ full_name: '', email: '', phone: '', role: 'staff', password: '', active: true });
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!open) return;
    setForm({
      full_name: user?.full_name ?? '',
      email: user?.email ?? '',
      phone: user?.phone ?? '',
      role: user?.role ?? 'staff',
      password: '',
      active: Boolean(user?.active ?? 1),
    });
  }, [open, user]);

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      if (user) {
        const body: Record<string, unknown> = { full_name: form.full_name, phone: form.phone, role: form.role, active: form.active };
        if (form.password) body.password = form.password;
        await api(`admin/users/${user.id}`, { method: 'PATCH', body });
      } else {
        await api('admin/users', { method: 'POST', body: form });
      }
      onSaved();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not save the user.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <Modal open={open} onClose={onClose} title={user ? 'Edit user' : 'New user'} size="md"
      footer={<><button type="button" className="btn-ghost btn-sm" onClick={onClose}>Cancel</button><button type="submit" form="user-form" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : user ? 'Save changes' : 'Create user'}</button></>}
    >
      <form id="user-form" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
        <Field label="Full name *" className="sm:col-span-2"><input className="input" value={form.full_name} onChange={(event) => setForm({ ...form, full_name: event.target.value })} required /></Field>
        <Field label="Email *"><input className="input" type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} required disabled={Boolean(user)} /></Field>
        <Field label="Phone"><input className="input" value={form.phone} onChange={(event) => setForm({ ...form, phone: event.target.value })} /></Field>
        <Field label="Role"><select className="input" value={form.role} onChange={(event) => setForm({ ...form, role: event.target.value })}><option value="staff">Staff</option><option value="admin">Administrator</option><option value="client">Client</option></select></Field>
        <Field label={user ? 'Reset password' : 'Password *'} hint={user ? 'Leave blank to keep the current password.' : 'At least 8 characters.'}>
          <input className="input" type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} required={!user} autoComplete="new-password" />
        </Field>
        {user && <Field label="Status" className="sm:col-span-2"><select className="input" value={form.active ? '1' : '0'} onChange={(event) => setForm({ ...form, active: event.target.value === '1' })}><option value="1">Active</option><option value="0">Inactive</option></select></Field>}
      </form>
    </Modal>
  );
}
