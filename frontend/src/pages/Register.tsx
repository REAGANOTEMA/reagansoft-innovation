import { useState, type FormEvent } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { ArrowLeft, ArrowRight, Check, Eye, EyeOff, Lock, Mail, Phone, ShieldCheck, UserRound } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { usePageMeta } from '../lib/seo';
import { useSite } from '../lib/site';
import { appUrl } from '../lib/env';
import { ApiError } from '../lib/api';
import { Logo } from '../components/Logo';
import { Field, Spinner } from '../components/UI';

export function Register() {
  usePageMeta('Create a client account — Reagansoft Innovation', 'Create a Reagansoft client account to submit project requests and track delivery.');
  const { user, register, loading } = useAuth();
  const { settings } = useSite();
  const navigate = useNavigate();
  const [form, setForm] = useState({
    full_name: '',
    company: '',
    email: '',
    phone: '',
    password: '',
    confirm: '',
    company_website: '',
    accept: false,
  });
  const [show, setShow] = useState(false);
  const [error, setError] = useState('');
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [busy, setBusy] = useState(false);

  if (!loading && user) {
    return <Navigate to={user.role === 'client' ? '/portal' : '/console'} replace />;
  }

  const registrationOpen = settings.registration_open !== '0';

  function update<K extends keyof typeof form>(key: K, value: (typeof form)[K]) {
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
    if (!form.accept) {
      setError('Please accept the terms and privacy notice to continue.');
      return;
    }
    if (form.password !== form.confirm) {
      setErrors({ confirm: 'Passwords do not match.' });
      return;
    }
    setBusy(true);
    setError('');
    setErrors({});
    try {
      await register({
        full_name: form.full_name,
        company: form.company,
        email: form.email,
        phone: form.phone,
        password: form.password,
        confirm: form.confirm,
        company_website: form.company_website,
      });
      navigate('/portal', { replace: true });
    } catch (err) {
      if (err instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(err.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        setError(err.message);
      } else {
        setError('Could not create your account. Please try again.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="grid min-h-screen bg-white lg:grid-cols-[0.95fr_1.05fr]">
      <div className="relative hidden overflow-hidden bg-navy-950 p-10 text-white lg:flex lg:flex-col">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
        <div className="relative flex items-center justify-between">
          <Logo dark />
          <Link to="/" className="inline-flex items-center gap-1.5 text-[11px] text-white/50 transition hover:text-white">
            <ArrowLeft className="h-3 w-3" /> Back to website
          </Link>
        </div>

        <div className="relative mt-20 max-w-md">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-white/55">
            <ShieldCheck className="h-3 w-3" /> Client accounts
          </span>
          <h1 className="h2 mt-5 text-white">A clearer way to run your project with us.</h1>
          <p className="mt-3 text-sm leading-relaxed text-white/60">
            Create an account once, then use it for every request, quote, invoice and update.
          </p>

          <div className="mt-8 space-y-3">
            {[
              'Submit and track project requests',
              'See milestones, files and messages',
              'Download invoices and submit payments',
              'Keep billing details ready for checkout',
            ].map((item) => (
              <div key={item} className="flex items-center gap-2.5 text-[12.5px] text-white/65">
                <span className="grid h-5 w-5 place-items-center rounded-full bg-emerald-400/15 text-emerald-300">
                  <Check className="h-3 w-3" strokeWidth={3} />
                </span>
                {item}
              </div>
            ))}
          </div>
        </div>

        <div className="relative mt-auto rounded-2xl border border-white/10 bg-white/[0.04] p-5">
          <p className="font-mono text-[9px] uppercase tracking-[0.18em] text-white/35">A note on data</p>
          <p className="mt-2 text-[11.5px] leading-relaxed text-white/50">
            We only ask for what we need to serve you. Your details are never sold, and billing information is used solely to process
            invoices and payments.
          </p>
        </div>
      </div>

      <div className="flex flex-col">
        <div className="flex items-center justify-between px-6 py-5 lg:hidden">
          <Logo />
          <Link to="/" className="text-[11px] text-ink-muted">Website</Link>
        </div>

        <div className="flex flex-1 items-center justify-center px-6 py-10">
          <div className="w-full max-w-md">
            <span className="eyebrow">Get started</span>
            <h2 className="mt-2 font-display text-2xl font-semibold tracking-tight text-navy-900">Create your client account</h2>
            <p className="mt-1.5 text-[12.5px] text-ink-muted">
              Already registered?{' '}
              <Link to="/login" className="font-semibold text-brand-700 hover:text-brand-800">Sign in</Link>
            </p>

            {!registrationOpen && (
              <div className="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-[12.5px] font-medium text-amber-800">
                Registration is currently closed. Please contact us to set up an account.
              </div>
            )}

            {error && (
              <div className="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[12.5px] font-medium text-rose-700" role="alert">
                {error}
              </div>
            )}

            <form onSubmit={submit} className="mt-6 space-y-4">
              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Full name *" error={errors.full_name}>
                  <div className="relative">
                    <UserRound className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                    <input className="input pl-10" value={form.full_name} onChange={(event) => update('full_name', event.target.value)} required autoComplete="name" placeholder="Your name" />
                  </div>
                </Field>
                <Field label="Company" error={errors.company}>
                  <input className="input" value={form.company} onChange={(event) => update('company', event.target.value)} autoComplete="organization" placeholder="Optional" />
                </Field>
                <Field label="Email address *" error={errors.email}>
                  <div className="relative">
                    <Mail className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                    <input className="input pl-10" type="email" value={form.email} onChange={(event) => update('email', event.target.value)} required autoComplete="email" placeholder="you@company.com" />
                  </div>
                </Field>
                <Field label="Phone number *" error={errors.phone}>
                  <div className="relative">
                    <Phone className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                    <input className="input pl-10" value={form.phone} onChange={(event) => update('phone', event.target.value)} required autoComplete="tel" placeholder="+256 7xx xxx xxx" />
                  </div>
                </Field>
                <Field label="Password *" error={errors.password} hint="At least 8 characters.">
                  <div className="relative">
                    <Lock className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                    <input
                      className="input px-10"
                      type={show ? 'text' : 'password'}
                      value={form.password}
                      onChange={(event) => update('password', event.target.value)}
                      required
                      minLength={8}
                      autoComplete="new-password"
                      placeholder="Create a password"
                    />
                    <button type="button" onClick={() => setShow((value) => !value)} className="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-ink-faint hover:text-navy-900" aria-label={show ? 'Hide password' : 'Show password'}>
                      {show ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                    </button>
                  </div>
                </Field>
                <Field label="Confirm password *" error={errors.confirm}>
                  <input
                    className="input"
                    type={show ? 'text' : 'password'}
                    value={form.confirm}
                    onChange={(event) => update('confirm', event.target.value)}
                    required
                    minLength={8}
                    autoComplete="new-password"
                    placeholder="Repeat password"
                  />
                </Field>
              </div>

              <input type="text" tabIndex={-1} autoComplete="off" value={form.company_website} onChange={(event) => update('company_website', event.target.value)} className="hidden" aria-hidden="true" />

              <label className="flex items-start gap-2.5 text-[11.5px] leading-relaxed text-ink-muted">
                <input
                  type="checkbox"
                  checked={form.accept}
                  onChange={(event) => update('accept', event.target.checked)}
                  className="mt-0.5 h-4 w-4 rounded border-brand-200 text-brand-600 focus:ring-brand-300"
                />
                <span>
                  I agree to the <a href={appUrl('/terms.php')} className="font-medium text-brand-700">terms of service</a> and{' '}
                  <a href={appUrl('/privacy.php')} className="font-medium text-brand-700">privacy notice</a>.
                </span>
              </label>

              <button type="submit" disabled={busy || !registrationOpen} className="btn-primary w-full">
                {busy ? <><Spinner className="h-4 w-4 text-white" /> Creating account…</> : <>Create account <ArrowRight className="h-4 w-4" /></>}
              </button>
            </form>

            <p className="mt-5 text-center text-[11px] text-ink-faint">
              Prefer to talk first?{' '}
              <Link to="/contact" className="font-medium text-brand-700 hover:text-brand-800">Send an enquiry</Link>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
