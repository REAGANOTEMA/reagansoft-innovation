import { useEffect, useState, type FormEvent } from 'react';
import { Building2, CreditCard, Globe2, Save, ShieldCheck, SlidersHorizontal } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { useSite } from '../../lib/site';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { ErrorBlock, Field, LoadingBlock, PageHeader } from '../../components/UI';
import { cn } from '../../lib/cn';

type SettingsMap = Record<string, string>;

const sections = [
  { id: 'company', label: 'Company', icon: Building2 },
  { id: 'contact', label: 'Contact & social', icon: Globe2 },
  { id: 'operations', label: 'Operations', icon: SlidersHorizontal },
  { id: 'payments', label: 'Payments', icon: CreditCard },
  { id: 'site', label: 'Site & SEO', icon: ShieldCheck },
] as const;

export function ConsoleSettings() {
  usePageMeta('Settings — Reagansoft console');
  const { push } = useToast();
  const { refresh: refreshSite } = useSite();
  const { data, loading, error, reload } = useApi<SettingsMap>('admin/settings');
  const [form, setForm] = useState<SettingsMap>({});
  const [section, setSection] = useState<(typeof sections)[number]['id']>('company');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (data) setForm(data);
  }, [data]);

  function update(key: string, value: string) {
    setForm((current) => ({ ...current, [key]: value }));
  }

  async function save(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    try {
      await api('admin/settings', { method: 'PATCH', body: form });
      push('success', 'Settings saved.');
      await refreshSite();
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not save settings.');
    } finally {
      setBusy(false);
    }
  }

  if (loading) return <LoadingBlock label="Loading settings…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load settings.'} onRetry={reload} />;

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Configuration" title="Settings" description="Company identity, operations and payment details used across the site and documents." />

      <div className="grid gap-5 lg:grid-cols-[220px_1fr]">
        <nav className="flex gap-1 overflow-x-auto rounded-xl border border-brand-100 bg-white p-1.5 lg:flex-col">
          {sections.map((item) => {
            const Icon = item.icon;
            return (
              <button
                key={item.id}
                type="button"
                onClick={() => setSection(item.id)}
                className={cn('flex shrink-0 items-center gap-2 rounded-lg px-3 py-2.5 text-left text-[11.5px] font-semibold transition', section === item.id ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50')}
              >
                <Icon className="h-3.5 w-3.5" /> {item.label}
              </button>
            );
          })}
        </nav>

        <form onSubmit={save} className="rounded-2xl border border-brand-100 bg-white p-5">
          {section === 'company' && (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Company name" className="sm:col-span-2"><input className="input" value={form.company_name ?? ''} onChange={(event) => update('company_name', event.target.value)} /></Field>
              <Field label="Tagline" className="sm:col-span-2"><input className="input" value={form.company_tagline ?? ''} onChange={(event) => update('company_tagline', event.target.value)} /></Field>
              <Field label="Founder" ><input className="input" value={form.company_founder ?? ''} onChange={(event) => update('company_founder', event.target.value)} /></Field>
              <Field label="Address"><input className="input" value={form.company_address ?? ''} onChange={(event) => update('company_address', event.target.value)} /></Field>
              <Field label="About the company" className="sm:col-span-2"><textarea className="input min-h-[130px]" value={form.company_about ?? ''} onChange={(event) => update('company_about', event.target.value)} /></Field>
            </div>
          )}

          {section === 'contact' && (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Company email"><input className="input" type="email" value={form.company_email ?? ''} onChange={(event) => update('company_email', event.target.value)} /></Field>
              <Field label="Support email"><input className="input" type="email" value={form.support_email ?? ''} onChange={(event) => update('support_email', event.target.value)} /></Field>
              <Field label="Phone"><input className="input" value={form.company_phone ?? ''} onChange={(event) => update('company_phone', event.target.value)} /></Field>
              <Field label="WhatsApp"><input className="input" value={form.company_whatsapp ?? ''} onChange={(event) => update('company_whatsapp', event.target.value)} /></Field>
              <Field label="Working hours" className="sm:col-span-2"><input className="input" value={form.company_hours ?? ''} onChange={(event) => update('company_hours', event.target.value)} /></Field>
              <Field label="Google Maps embed URL" className="sm:col-span-2"><input className="input" value={form.company_map_url ?? ''} onChange={(event) => update('company_map_url', event.target.value)} /></Field>
              <Field label="Facebook"><input className="input" value={form.company_fb ?? ''} onChange={(event) => update('company_fb', event.target.value)} /></Field>
              <Field label="LinkedIn"><input className="input" value={form.company_linkedin ?? ''} onChange={(event) => update('company_linkedin', event.target.value)} /></Field>
              <Field label="X / Twitter"><input className="input" value={form.company_x ?? ''} onChange={(event) => update('company_x', event.target.value)} /></Field>
            </div>
          )}

          {section === 'operations' && (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Currency code"><input className="input" value={form.currency ?? 'UGX'} onChange={(event) => update('currency', event.target.value)} /></Field>
              <Field label="Project deposit"><input className="input" type="number" min="0" value={form.project_deposit ?? ''} onChange={(event) => update('project_deposit', event.target.value)} /></Field>
              <Field label="Invoice due days"><input className="input" type="number" min="0" value={form.invoices_due_days ?? ''} onChange={(event) => update('invoices_due_days', event.target.value)} /></Field>
              <Field label="Tax percent"><input className="input" type="number" min="0" step="0.01" value={form.tax_percent ?? ''} onChange={(event) => update('tax_percent', event.target.value)} /></Field>
              <Field label="Registration"><select className="input" value={form.registration_open ?? '1'} onChange={(event) => update('registration_open', event.target.value)}><option value="1">Open</option><option value="0">Closed</option></select></Field>
              <Field label="Newsletter"><select className="input" value={form.newsletter_enabled ?? '1'} onChange={(event) => update('newsletter_enabled', event.target.value)}><option value="1">Enabled</option><option value="0">Disabled</option></select></Field>
            </div>
          )}

          {section === 'payments' && (
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="MTN Mobile Money number"><input className="input" value={form.payment_mtn_number ?? ''} onChange={(event) => update('payment_mtn_number', event.target.value)} /></Field>
              <Field label="Airtel Money number"><input className="input" value={form.payment_airtel_number ?? ''} onChange={(event) => update('payment_airtel_number', event.target.value)} /></Field>
              <Field label="Bank details" className="sm:col-span-2"><textarea className="input min-h-[100px]" value={form.payment_bank_details ?? ''} onChange={(event) => update('payment_bank_details', event.target.value)} placeholder="Bank name, account name, account number, branch…" /></Field>
              <Field label="Gateway status" className="sm:col-span-2" hint="Displayed to clients on the payment form.">
                <select className="input" value={form.payment_gateway_status ?? 'not_configured'} onChange={(event) => update('payment_gateway_status', event.target.value)}>
                  <option value="not_configured">Not configured</option>
                  <option value="sandbox">Sandbox</option>
                  <option value="live">Live</option>
                </select>
              </Field>
            </div>
          )}

          {section === 'site' && (
            <div className="grid gap-4">
              <Field label="Site announcement" hint="Leave blank to hide the announcement bar."><textarea className="input min-h-[80px]" value={form.site_announcement ?? ''} onChange={(event) => update('site_announcement', event.target.value)} /></Field>
              <Field label="SEO keywords"><textarea className="input min-h-[80px]" value={form.seo_keywords ?? ''} onChange={(event) => update('seo_keywords', event.target.value)} /></Field>
              <div className="rounded-xl border border-brand-100 bg-brand-50/40 p-3 text-[11px] leading-relaxed text-ink-muted">
                <ShieldCheck className="mb-1 h-3.5 w-3.5 text-brand-600" />
                Payment gateway credentials are managed in server configuration. This screen only controls the status shown to clients.
              </div>
            </div>
          )}

          <div className="mt-5 flex items-center justify-between border-t border-brand-50 pt-4">
            <p className="text-[10.5px] text-ink-faint">Changes apply across the public site, portal and documents.</p>
            <button type="submit" disabled={busy} className="btn-primary btn-sm">{busy ? 'Saving…' : 'Save settings'} <Save className="h-3.5 w-3.5" /></button>
          </div>
        </form>
      </div>
    </div>
  );
}
