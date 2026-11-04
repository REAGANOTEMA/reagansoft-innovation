import { useEffect, useState, type FormEvent } from 'react';
import { KeyRound, Mail, Save, ShieldCheck, UserRound } from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useAuth } from '../../lib/auth';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Field, PageHeader } from '../../components/UI';
import { formatDate, initials } from '../../lib/format';
import type { User } from '../../lib/types';

export function PortalProfile({ scope = 'portal' }: { scope?: 'portal' | 'console' }) {
  usePageMeta(scope === 'console' ? 'Profile — Reagansoft console' : 'Profile — Reagansoft client portal');
  const { user, setUser } = useAuth();
  const { push } = useToast();

  const [profile, setProfile] = useState({
    full_name: '',
    phone: '',
    company: '',
    address: '',
    city: '',
    country: 'Uganda',
    payer_type: 'business',
    tax_id: '',
    national_id: '',
  });
  const [profileErrors, setProfileErrors] = useState<Record<string, string>>({});
  const [savingProfile, setSavingProfile] = useState(false);

  const [passwords, setPasswords] = useState({ current_password: '', password: '', confirm: '' });
  const [passwordErrors, setPasswordErrors] = useState<Record<string, string>>({});
  const [savingPassword, setSavingPassword] = useState(false);

  useEffect(() => {
    if (!user) return;
    setProfile({
      full_name: user.full_name ?? '',
      phone: user.phone ?? '',
      company: user.company ?? '',
      address: user.address ?? '',
      city: user.city ?? '',
      country: user.country ?? 'Uganda',
      payer_type: user.payer_type ?? 'business',
      tax_id: user.tax_id ?? '',
      national_id: user.national_id ?? '',
    });
  }, [user]);

  if (!user) return null;

  function update(key: keyof typeof profile, value: string) {
    setProfile((current) => ({ ...current, [key]: value }));
    setProfileErrors((current) => {
      if (!current[key]) return current;
      const next = { ...current };
      delete next[key];
      return next;
    });
  }

  async function saveProfile(event: FormEvent) {
    event.preventDefault();
    setSavingProfile(true);
    setProfileErrors({});
    try {
      const res = await api<{ user: User }>('auth/profile', { method: 'PATCH', body: profile });
      setUser(res.data.user);
      push('success', 'Profile updated.');
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setProfileErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not update your profile.');
      }
    } finally {
      setSavingProfile(false);
    }
  }

  async function savePassword(event: FormEvent) {
    event.preventDefault();
    setSavingPassword(true);
    setPasswordErrors({});
    try {
      await api('auth/password', { method: 'POST', body: passwords });
      setPasswords({ current_password: '', password: '', confirm: '' });
      push('success', 'Password changed.');
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setPasswordErrors(mapped);
        push('error', err.message);
      } else {
        push('error', 'Could not change your password.');
      }
    } finally {
      setSavingPassword(false);
    }
  }

  const isBusiness = profile.payer_type !== 'individual';

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Account" title="Your profile" description="Keep your contact and business details current. Billing details live on their own page." />

      <div className="grid gap-5 lg:grid-cols-[0.7fr_1.3fr]">
        <div className="space-y-4">
          <div className="rounded-2xl border border-brand-100 bg-white p-5 text-center">
            <div className="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-brand-600 font-display text-xl font-semibold text-white">
              {initials(user.full_name)}
            </div>
            <p className="mt-3 font-display text-sm font-semibold text-navy-900">{user.full_name}</p>
            <p className="mt-0.5 inline-flex items-center gap-1 text-[11px] text-ink-muted"><Mail className="h-3 w-3" /> {user.email}</p>
            <div className="mt-3 flex items-center justify-center gap-2">
              <span className="rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-semibold capitalize text-brand-700">{user.role}</span>
              {user.company && <span className="rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-semibold text-brand-700">{user.company}</span>}
            </div>
            <dl className="mt-4 space-y-2 border-t border-brand-50 pt-3 text-left text-[11px]">
              <div className="flex justify-between gap-3"><dt className="text-ink-muted">Member since</dt><dd className="font-medium text-navy-900">{formatDate(user.created_at)}</dd></div>
              <div className="flex justify-between gap-3"><dt className="text-ink-muted">Last sign-in</dt><dd className="font-medium text-navy-900">{formatDate(user.last_login_at, true)}</dd></div>
            </dl>
          </div>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/40 p-5">
            <ShieldCheck className="h-4 w-4 text-brand-600" />
            <p className="mt-2 text-[11.5px] font-semibold text-navy-900">Account security</p>
            <p className="mt-1 text-[11px] leading-relaxed text-ink-muted">Use a unique password. We never ask for it by phone, email or WhatsApp.</p>
          </div>
        </div>

        <div className="space-y-5">
          <form onSubmit={saveProfile} className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center gap-2">
              <UserRound className="h-4 w-4 text-brand-600" />
              <h2 className="font-display text-sm font-semibold text-navy-900">Personal & business details</h2>
            </div>
            <div className="mt-4 grid gap-4 sm:grid-cols-2">
              <Field label="Full name *" error={profileErrors.full_name} className="sm:col-span-2">
                <input className="input" value={profile.full_name} onChange={(event) => update('full_name', event.target.value)} required />
              </Field>
              <Field label="Email address" hint="Contact support to change your sign-in email.">
                <input className="input bg-brand-50/50" value={user.email} readOnly />
              </Field>
              <Field label="Phone" error={profileErrors.phone}>
                <input className="input" type="tel" value={profile.phone} onChange={(event) => update('phone', event.target.value)} placeholder="e.g. 0772 123 456" />
              </Field>
              <Field label="Paying as" error={profileErrors.payer_type}>
                <select className="input" value={profile.payer_type} onChange={(event) => update('payer_type', event.target.value)}>
                  <option value="business">Business / organisation</option>
                  <option value="individual">Individual</option>
                </select>
              </Field>
              <Field label="Company" error={profileErrors.company}>
                <input className="input" value={profile.company} onChange={(event) => update('company', event.target.value)} disabled={!isBusiness} placeholder="Registered business name" />
              </Field>
              {isBusiness && (
                <Field label="TIN" error={profileErrors.tax_id}>
                  <input className="input" value={profile.tax_id} onChange={(event) => update('tax_id', event.target.value)} placeholder="URA TIN" />
                </Field>
              )}
              <Field label="National ID / passport" error={profileErrors.national_id}>
                <input className="input" value={profile.national_id} onChange={(event) => update('national_id', event.target.value)} placeholder="Optional" />
              </Field>
              <Field label="Address" error={profileErrors.address} className="sm:col-span-2">
                <input className="input" value={profile.address} onChange={(event) => update('address', event.target.value)} placeholder="Street, plot or village" />
              </Field>
              <Field label="City / district" error={profileErrors.city}>
                <input className="input" value={profile.city} onChange={(event) => update('city', event.target.value)} />
              </Field>
              <Field label="Country" error={profileErrors.country}>
                <input className="input" value={profile.country} onChange={(event) => update('country', event.target.value)} />
              </Field>
            </div>
            <div className="mt-4 flex justify-end">
              <button type="submit" disabled={savingProfile} className="btn-primary btn-sm">
                {savingProfile ? 'Saving…' : 'Save profile'} <Save className="h-3.5 w-3.5" />
              </button>
            </div>
          </form>

          <form onSubmit={savePassword} className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center gap-2">
              <KeyRound className="h-4 w-4 text-brand-600" />
              <h2 className="font-display text-sm font-semibold text-navy-900">Change password</h2>
            </div>
            <div className="mt-4 grid gap-4 sm:grid-cols-2">
              <Field label="Current password *" error={passwordErrors.current_password} className="sm:col-span-2">
                <input className="input" type="password" value={passwords.current_password} onChange={(event) => setPasswords((current) => ({ ...current, current_password: event.target.value }))} required autoComplete="current-password" />
              </Field>
              <Field label="New password *" error={passwordErrors.password} hint="At least 8 characters.">
                <input className="input" type="password" value={passwords.password} onChange={(event) => setPasswords((current) => ({ ...current, password: event.target.value }))} required autoComplete="new-password" />
              </Field>
              <Field label="Confirm new password *" error={passwordErrors.confirm}>
                <input className="input" type="password" value={passwords.confirm} onChange={(event) => setPasswords((current) => ({ ...current, confirm: event.target.value }))} required autoComplete="new-password" />
              </Field>
            </div>
            <div className="mt-4 flex justify-end">
              <button type="submit" disabled={savingPassword} className="btn-primary btn-sm">
                {savingPassword ? 'Updating…' : 'Update password'} <KeyRound className="h-3.5 w-3.5" />
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
