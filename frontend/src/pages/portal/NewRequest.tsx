import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ArrowLeft, ArrowRight, Info, Send, Sparkles } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Field, PageHeader } from '../../components/UI';
import { money } from '../../lib/format';
import type { Project } from '../../lib/types';

export function NewRequest() {
  usePageMeta('New project request — Reagansoft client portal');
  const { services, settings } = useSite();
  const { user } = useAuth();
  const { push } = useToast();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    title: '',
    service_id: '',
    priority: 'normal',
    budget: '',
    description: '',
    requirements: '',
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

  async function submit(event: React.FormEvent) {
    event.preventDefault();
    setBusy(true);
    setErrors({});
    try {
      const res = await api<Project>('client/projects', {
        method: 'POST',
        body: {
          title: form.title,
          service_id: form.service_id || null,
          priority: form.priority,
          budget: form.budget || 0,
          description: form.description,
          requirements: form.requirements,
        },
      });
      push('success', 'Request received. We will review it and get back to you.');
      navigate(`/portal/projects/${res.data.id}`);
    } catch (error) {
      if (error instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(error.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        push('error', error.message);
      } else {
        push('error', 'Could not submit your request. Please try again.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="New request"
        title="Tell us what you need"
        description="Give us enough detail to scope the work properly. You can refine everything on a call."
        actions={<Link to="/portal/projects" className="btn-ghost btn-sm"><ArrowLeft className="h-3.5 w-3.5" /> Back to projects</Link>}
      />

      <div className="grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
        <form onSubmit={submit} className="rounded-2xl border border-brand-100 bg-white p-5 sm:p-6">
          <div className="grid gap-4 sm:grid-cols-2">
            <Field label="Project title *" error={errors.title} className="sm:col-span-2">
              <input className="input" value={form.title} onChange={(event) => update('title', event.target.value)} required placeholder="e.g. School fees and records system" />
            </Field>

            <Field label="Service" error={errors.service_id} hint="Choose the closest match, or leave blank.">
              <select className="input" value={form.service_id} onChange={(event) => update('service_id', event.target.value)}>
                <option value="">Not sure / custom</option>
                {services.map((service) => (
                  <option key={service.id} value={service.id}>{service.name} — {service.price_short}</option>
                ))}
              </select>
            </Field>

            <Field label="Priority" error={errors.priority}>
              <select className="input" value={form.priority} onChange={(event) => update('priority', event.target.value)}>
                <option value="low">Low — exploring options</option>
                <option value="normal">Normal — planned</option>
                <option value="high">High — needed soon</option>
                <option value="urgent">Urgent — time critical</option>
              </select>
            </Field>

            <Field label={`Indicative budget (${settings.currency})`} error={errors.budget} hint="Optional. Helps us propose the right scope." className="sm:col-span-2">
              <input className="input" type="number" min="0" step="1000" value={form.budget} onChange={(event) => update('budget', event.target.value)} placeholder="e.g. 5000000" />
            </Field>

            <Field label="What should it do? *" error={errors.description} hint="Describe the problem, who will use it and the outcome you want." className="sm:col-span-2">
              <textarea className="input min-h-[140px]" value={form.description} onChange={(event) => update('description', event.target.value)} required placeholder="We currently… and we need…" />
            </Field>

            <Field label="Specific requirements" error={errors.requirements} hint="Pages, modules, integrations, deadlines, existing systems." className="sm:col-span-2">
              <textarea className="input min-h-[110px]" value={form.requirements} onChange={(event) => update('requirements', event.target.value)} placeholder="One item per line is fine." />
            </Field>
          </div>

          <div className="mt-5 flex flex-col gap-3 border-t border-brand-50 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p className="inline-flex items-center gap-1.5 text-[11px] text-ink-faint">
              <Info className="h-3.5 w-3.5 text-brand-500" /> No payment is taken at this stage.
            </p>
            <button type="submit" disabled={busy} className="btn-primary">
              {busy ? 'Submitting…' : 'Submit request'} <Send className="h-4 w-4" />
            </button>
          </div>
        </form>

        <aside className="space-y-4">
          <div className="rounded-2xl border border-brand-100 bg-brand-50/50 p-5">
            <h3 className="font-display text-sm font-semibold text-navy-900">What happens next</h3>
            <ol className="mt-3 space-y-3">
              {[
                'We acknowledge the request and review the detail.',
                'We may ask a few clarifying questions.',
                'You receive a quotation and proposed timeline.',
                'Work starts after approval and deposit.',
              ].map((item, index) => (
                <li key={item} className="flex gap-2.5 text-[11.5px] text-ink-soft">
                  <span className="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-white font-mono text-[9px] font-bold text-brand-700">{index + 1}</span>
                  {item}
                </li>
              ))}
            </ol>
          </div>

          <div className="rounded-2xl border border-brand-100 bg-white p-5">
            <Sparkles className="h-4 w-4 text-brand-600" />
            <p className="mt-2 text-[12px] font-semibold text-navy-900">Not sure what to budget?</p>
            <p className="mt-1 text-[11px] leading-relaxed text-ink-muted">
              Most projects begin with a deposit of {money(settings.project_deposit ?? 200000, settings.currency)}. We will give you a
              realistic range after a short call.
            </p>
            <Link to="/pricing" className="mt-3 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700">
              See pricing guidance <ArrowRight className="h-3 w-3" />
            </Link>
          </div>

          {user?.company && (
            <div className="rounded-2xl border border-brand-100 bg-white p-5 text-[11.5px] text-ink-muted">
              Submitting as <span className="font-semibold text-navy-900">{user.company}</span>. You can update billing details from the
              billing page.
            </div>
          )}
        </aside>
      </div>
    </div>
  );
}
