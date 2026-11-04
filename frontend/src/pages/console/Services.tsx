import { useState, type FormEvent } from 'react';
import { BriefcaseBusiness, Pencil, Plus, Power } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, Field, LoadingBlock, Modal, PageHeader } from '../../components/UI';
import { money } from '../../lib/format';
import { cn } from '../../lib/cn';
import type { Service } from '../../lib/types';

const icons = ['globe', 'cart', 'cpu', 'code-s', 'mobile', 'receipt', 'layers', 'palette', 'shield', 'gamepad', 'book', 'chat', 'link', 'lock'];

interface ServiceForm {
  name: string;
  slug: string;
  description: string;
  price_min: string;
  price_max: string;
  currency: string;
  pricing_type: string;
  price_note: string;
  features: string;
  delivery_days: string;
  icon: string;
  status: string;
  sort_order: string;
}

function blankForm(currency: string): ServiceForm {
  return {
    name: '', slug: '', description: '', price_min: '', price_max: '', currency,
    pricing_type: 'starting_from', price_note: '', features: '', delivery_days: '',
    icon: 'code-s', status: 'active', sort_order: '0',
  };
}

export function ConsoleServices() {
  usePageMeta('Services — Reagansoft console');
  const { settings } = useSite();
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Service[]>('admin/services');
  const [editing, setEditing] = useState<Service | null>(null);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState<ServiceForm>(blankForm(settings.currency));
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  function startCreate() {
    setEditing(null);
    setForm(blankForm(settings.currency));
    setErrors({});
    setOpen(true);
  }

  function startEdit(service: Service) {
    setEditing(service);
    setForm({
      name: service.name,
      slug: service.slug,
      description: service.description,
      price_min: String(service.price_min ?? ''),
      price_max: service.price_max != null ? String(service.price_max) : '',
      currency: service.currency ?? settings.currency,
      pricing_type: service.pricing_type,
      price_note: service.price_note ?? '',
      features: (service.features ?? []).join('\n'),
      delivery_days: service.delivery_days != null ? String(service.delivery_days) : '',
      icon: service.icon ?? 'code-s',
      status: service.status ?? 'active',
      sort_order: String(service.sort_order ?? 0),
    });
    setErrors({});
    setOpen(true);
  }

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setErrors({});
    try {
      if (editing) {
        await api(`admin/services/${editing.id}`, { method: 'PATCH', body: form });
        push('success', 'Service updated.');
      } else {
        await api('admin/services', { method: 'POST', body: form });
        push('success', 'Service created.');
      }
      setOpen(false);
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
        push('error', 'Could not save the service.');
      }
    } finally {
      setBusy(false);
    }
  }

  async function toggle(service: Service) {
    const next = service.status === 'inactive' ? 'active' : 'inactive';
    try {
      if (next === 'inactive') {
        await api(`admin/services/${service.id}`, { method: 'DELETE' });
      } else {
        await api(`admin/services/${service.id}`, { method: 'PATCH', body: { ...service, features: (service.features ?? []).join('\n'), status: 'active' } });
      }
      push('success', next === 'inactive' ? 'Service hidden from the website.' : 'Service published.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not change the service status.');
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Catalogue"
        title="Services"
        description="What the website sells, how it is priced, and which services are visible."
        actions={<button type="button" className="btn-primary btn-sm" onClick={startCreate}><Plus className="h-3.5 w-3.5" /> Add service</button>}
      />

      {loading ? (
        <LoadingBlock label="Loading services…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<BriefcaseBusiness className="h-6 w-6" />} title="No services yet" message="Add the first service to populate the website." />
      ) : (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          {data.map((service) => (
            <div key={service.id} className={cn('flex h-full flex-col rounded-2xl border bg-white p-5', service.status === 'inactive' ? 'border-dashed border-brand-200 opacity-75' : 'border-brand-100')}>
              <div className="flex items-start justify-between gap-2">
                <div>
                  <p className="font-mono text-[8.5px] uppercase tracking-[0.16em] text-ink-faint">{service.icon} · #{service.sort_order ?? 0}</p>
                  <h2 className="mt-1 font-display text-[14px] font-semibold text-navy-900">{service.name}</h2>
                </div>
                <Badge tone={service.status === 'inactive' ? 'gray' : 'green'}>{service.status === 'inactive' ? 'Hidden' : 'Live'}</Badge>
              </div>
              <p className="mt-1 font-mono text-[9.5px] text-brand-700">/{service.slug}</p>
              <p className="mt-2 line-clamp-3 flex-1 text-[11.5px] leading-relaxed text-ink-muted">{service.description}</p>
              <div className="mt-3 rounded-xl bg-brand-50/60 px-3 py-2 text-[11px]">
                <div className="flex items-center justify-between"><span className="text-ink-muted">{service.price_display || money(service.price_min)}</span><span className="font-semibold text-navy-900">{service.pricing_type.replace(/_/g, ' ')}</span></div>
                <div className="mt-0.5 flex items-center justify-between text-[10px] text-ink-faint"><span>{service.delivery_days ? `${service.delivery_days} days delivery` : 'Flexible timeline'}</span><span>{service.features?.length ?? 0} features</span></div>
              </div>
              <div className="mt-3 flex gap-2 border-t border-brand-50 pt-3">
                <button type="button" className="btn-ghost btn-sm flex-1" onClick={() => startEdit(service)}><Pencil className="h-3.5 w-3.5" /> Edit</button>
                <button type="button" className="btn-ghost btn-sm flex-1" onClick={() => toggle(service)}><Power className="h-3.5 w-3.5" /> {service.status === 'inactive' ? 'Publish' : 'Hide'}</button>
              </div>
            </div>
          ))}
        </div>
      )}

      <Modal open={open} onClose={() => setOpen(false)} title={editing ? `Edit ${editing.name}` : 'Add service'} size="lg"
        footer={<><button type="button" className="btn-ghost btn-sm" onClick={() => setOpen(false)}>Cancel</button><button type="submit" form="service-form" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Save service'}</button></>}
      >
        <form id="service-form" onSubmit={submit} className="grid gap-4 sm:grid-cols-2">
          <Field label="Name *" error={errors.name}><input className="input" value={form.name} onChange={(event) => setForm({ ...form, name: event.target.value })} required /></Field>
          <Field label="Slug" error={errors.slug} hint="Leave blank to generate from the name."><input className="input" value={form.slug} onChange={(event) => setForm({ ...form, slug: event.target.value })} /></Field>
          <Field label="Description *" error={errors.description} className="sm:col-span-2"><textarea className="input min-h-[90px]" value={form.description} onChange={(event) => setForm({ ...form, description: event.target.value })} required /></Field>
          <Field label="Pricing type" error={errors.pricing_type}><select className="input" value={form.pricing_type} onChange={(event) => setForm({ ...form, pricing_type: event.target.value })}><option value="range">Range</option><option value="starting_from">Starting from</option><option value="contact_for_quote">Contact for quote</option></select></Field>
          <Field label="Icon"><select className="input" value={form.icon} onChange={(event) => setForm({ ...form, icon: event.target.value })}>{icons.map((icon) => <option key={icon} value={icon}>{icon}</option>)}</select></Field>
          <Field label="Minimum price" error={errors.price_min}><input className="input" type="number" min="0" step="1000" value={form.price_min} onChange={(event) => setForm({ ...form, price_min: event.target.value })} /></Field>
          <Field label="Maximum price" error={errors.price_max} hint="Leave blank for no upper bound."><input className="input" type="number" min="0" step="1000" value={form.price_max} onChange={(event) => setForm({ ...form, price_max: event.target.value })} /></Field>
          <Field label="Currency"><input className="input" value={form.currency} onChange={(event) => setForm({ ...form, currency: event.target.value })} /></Field>
          <Field label="Delivery days"><input className="input" type="number" min="0" value={form.delivery_days} onChange={(event) => setForm({ ...form, delivery_days: event.target.value })} /></Field>
          <Field label="Price note" error={errors.price_note} className="sm:col-span-2"><input className="input" value={form.price_note} onChange={(event) => setForm({ ...form, price_note: event.target.value })} placeholder="e.g. Includes hosting for one year" /></Field>
          <Field label="Features" hint="One feature per line." className="sm:col-span-2"><textarea className="input min-h-[100px]" value={form.features} onChange={(event) => setForm({ ...form, features: event.target.value })} /></Field>
          <Field label="Status"><select className="input" value={form.status} onChange={(event) => setForm({ ...form, status: event.target.value })}><option value="active">Active</option><option value="inactive">Inactive</option></select></Field>
          <Field label="Sort order"><input className="input" type="number" value={form.sort_order} onChange={(event) => setForm({ ...form, sort_order: event.target.value })} /></Field>
        </form>
      </Modal>
    </div>
  );
}
