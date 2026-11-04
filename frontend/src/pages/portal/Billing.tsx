import { useEffect, useState, type FormEvent } from 'react';
import { AlertCircle, CheckCircle2, Info, ShieldCheck } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useAuth } from '../../lib/auth';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { ErrorBlock, LoadingBlock, PageHeader } from '../../components/UI';
import { cn } from '../../lib/cn';
import type { User } from '../../lib/types';

interface Requirement {
  key: string;
  group: string;
  type: 'text' | 'email' | 'tel' | 'choice' | 'select' | 'textarea' | string;
  label: string;
  value: string;
  required: boolean;
  placeholder?: string;
  why?: string;
  hint?: string;
  options?: string[];
}

interface MissingItem {
  key: string;
  label: string;
  why?: string;
}

interface BillingPayload {
  requirements: Requirement[];
  missing: MissingItem[];
  terms_accepted: boolean;
  countries: string[];
  schema_ready: boolean;
  user: User;
}

const groupTitles: Record<string, string> = {
  contact: 'Contact details',
  identity: 'Who is paying',
  location: 'Where you are',
};

export function PortalBilling() {
  usePageMeta('Billing details — Reagansoft client portal');
  const { push } = useToast();
  const { setUser } = useAuth();
  const { data, loading, error, reload } = useApi<BillingPayload>('client/billing');
  const [values, setValues] = useState<Record<string, string>>({});
  const [terms, setTerms] = useState(false);
  const [busy, setBusy] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (!data) return;
    const initial: Record<string, string> = {};
    data.requirements.forEach((item) => {
      initial[item.key] = item.value ?? '';
    });
    setValues(initial);
    setTerms(data.terms_accepted);
  }, [data]);

  if (loading) return <LoadingBlock label="Loading billing details…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load billing details.'} onRetry={reload} />;

  const isBusiness = (values.payer_type || 'business') !== 'individual';
  const groups = ['contact', 'identity', 'location'];
  const missing = data.missing ?? [];

  function update(key: string, value: string) {
    setValues((current) => ({ ...current, [key]: value }));
    setFieldErrors((current) => {
      if (!current[key]) return current;
      const next = { ...current };
      delete next[key];
      return next;
    });
  }

  async function submit(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setFieldErrors({});
    try {
      const res = await api<{ user: User }>('auth/profile', {
        method: 'PATCH',
        body: {
          ...values,
          billing_terms_accepted: terms,
        },
      });
      setUser(res.data.user);
      push('success', 'Billing details saved.');
      reload();
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setFieldErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not save your billing details.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Billing"
        title="Billing details"
        description="These details appear on your invoices and are required before a payment can be submitted."
      />

      {!data.schema_ready && (
        <div className="flex items-start gap-2 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-[12px] text-amber-800">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
          Billing fields are not fully configured on this installation. Please contact support before submitting a payment.
        </div>
      )}

      <div className="grid gap-5 lg:grid-cols-[1.4fr_0.6fr]">
        <form onSubmit={submit} className="space-y-5">
          {groups.map((group) => {
            const items = data.requirements.filter((item) => item.group === group);
            if (items.length === 0) return null;
            return (
              <section key={group} className="rounded-2xl border border-brand-100 bg-white p-5">
                <h2 className="font-display text-sm font-semibold text-navy-900">{groupTitles[group] ?? group}</h2>
                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                  {items.map((item) => {
                    const businessOnly = item.key === 'company' || item.key === 'tax_id';
                    if (businessOnly && !isBusiness) return null;
                    const error = fieldErrors[item.key];
                    const label = item.required && !(businessOnly && !isBusiness) ? `${item.label} *` : item.label;

                    if (item.key === 'payer_type') {
                      return (
                        <label key={item.key} className="block sm:col-span-2">
                          <span className="label">{label}</span>
                          <div className="mt-1 grid gap-2 sm:grid-cols-2">
                            {[
                              { value: 'business', title: 'A business or organisation', hint: 'Invoices need a company name and TIN.' },
                              { value: 'individual', title: 'An individual', hint: 'Personal billing, no TIN required.' },
                            ].map((option) => {
                              const active = (values.payer_type || 'business') === option.value;
                              return (
                                <button
                                  key={option.value}
                                  type="button"
                                  onClick={() => update('payer_type', option.value)}
                                  className={cn('rounded-xl border px-3.5 py-3 text-left transition', active ? 'border-brand-400 bg-brand-50 ring-2 ring-brand-100' : 'border-brand-100 bg-white hover:border-brand-200')}
                                >
                                  <span className="flex items-center justify-between gap-2">
                                    <span className="text-[12px] font-semibold text-navy-900">{option.title}</span>
                                    <span className={cn('grid h-4 w-4 place-items-center rounded-full border', active ? 'border-brand-600 bg-brand-600 text-white' : 'border-brand-200')}>
                                      {active && <CheckCircle2 className="h-2.5 w-2.5" />}
                                    </span>
                                  </span>
                                  <span className="mt-0.5 block text-[10.5px] text-ink-muted">{option.hint}</span>
                                </button>
                              );
                            })}
                          </div>
                        </label>
                      );
                    }

                    return (
                      <label key={item.key} className={cn('block', (item.key === 'address' || item.key === 'email') && 'sm:col-span-2')}>
                        <span className="label">{label}</span>
                        {item.type === 'select' ? (
                          <select className="input" value={values[item.key] ?? ''} onChange={(event) => update(item.key, event.target.value)} required={item.required}>
                            <option value="">Select…</option>
                            {(item.options ?? data.countries).map((option) => <option key={option} value={option}>{option}</option>)}
                          </select>
                        ) : (
                          <input
                            className={cn('input', error && 'border-rose-300')}
                            type={item.type === 'tel' ? 'tel' : item.type === 'email' ? 'email' : 'text'}
                            value={values[item.key] ?? ''}
                            onChange={(event) => update(item.key, event.target.value)}
                            placeholder={item.placeholder}
                            required={item.required && !businessOnly}
                            autoComplete={item.key === 'full_name' ? 'name' : item.key === 'email' ? 'email' : item.key === 'phone' ? 'tel' : item.key === 'address' ? 'street-address' : 'off'}
                          />
                        )}
                        {error && <span className="mt-1 block text-[10.5px] text-rose-600">{error}</span>}
                        {!error && item.hint && <span className="mt-1 block text-[10.5px] text-ink-faint">{item.hint}</span>}
                      </label>
                    );
                  })}
                </div>
              </section>
            );
          })}

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <label className="flex items-start gap-3">
              <input
                type="checkbox"
                checked={terms}
                onChange={(event) => setTerms(event.target.checked)}
                disabled={data.terms_accepted}
                className="mt-0.5 h-4 w-4 rounded border-brand-300 text-brand-600"
              />
              <span className="text-[11.5px] leading-relaxed text-ink-muted">
                I confirm these billing details are accurate and I accept the payment and invoicing terms.
                {data.terms_accepted && <span className="mt-0.5 block font-semibold text-emerald-700">Accepted earlier — no action needed.</span>}
              </span>
            </label>
            {fieldErrors.billing_terms_accepted && <p className="mt-1 text-[10.5px] text-rose-600">{fieldErrors.billing_terms_accepted}</p>}
          </section>

          <div className="flex justify-end">
            <button type="submit" disabled={busy} className="btn-primary">
              {busy ? 'Saving…' : 'Save billing details'}
            </button>
          </div>
        </form>

        <aside className="space-y-4">
          <div className={cn('rounded-2xl border p-5', missing.length === 0 ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60')}>
            <div className="flex items-center gap-2">
              {missing.length === 0 ? <ShieldCheck className="h-4 w-4 text-emerald-600" /> : <AlertCircle className="h-4 w-4 text-amber-600" />}
              <h3 className="font-display text-sm font-semibold text-navy-900">
                {missing.length === 0 ? 'Ready to pay' : `${missing.length} item${missing.length === 1 ? '' : 's'} missing`}
              </h3>
            </div>
            {missing.length === 0 ? (
              <p className="mt-1.5 text-[11.5px] leading-relaxed text-ink-muted">Your billing profile is complete. You can submit payment references from any invoice.</p>
            ) : (
              <ul className="mt-2.5 space-y-2">
                {missing.map((item) => (
                  <li key={item.key} className="text-[11.5px] leading-relaxed">
                    <p className="font-semibold text-amber-900">{item.label}</p>
                    {item.why && <p className="text-[10.5px] text-amber-800/80">{item.why}</p>}
                  </li>
                ))}
              </ul>
            )}
          </div>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/40 p-5">
            <Info className="h-4 w-4 text-brand-600" />
            <p className="mt-2 text-[11.5px] font-semibold text-navy-900">Why we ask</p>
            <p className="mt-1 text-[11px] leading-relaxed text-ink-muted">
              Invoices must carry the correct legal name and tax details. Accurate contact details let us confirm payments without delays.
            </p>
          </div>
        </aside>
      </div>
    </div>
  );
}
