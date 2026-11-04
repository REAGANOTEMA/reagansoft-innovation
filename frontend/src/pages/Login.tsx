import { useState, type FormEvent } from 'react';
import { Link, Navigate, useLocation, useNavigate } from 'react-router-dom';
import { ArrowLeft, ArrowRight, Eye, EyeOff, Lock, Mail, ShieldCheck, Sparkles } from 'lucide-react';
import { useAuth } from '../lib/auth';
import { usePageMeta } from '../lib/seo';
import { useSite } from '../lib/site';
import { ApiError } from '../lib/api';
import { Logo } from '../components/Logo';
import { Field, Spinner } from '../components/UI';
import { HeroVisual } from '../components/Graphics';

export function Login() {
  usePageMeta('Sign in — Reagansoft client portal', 'Sign in to track projects, invoices and messages with Reagansoft Innovation.');
  const { user, login, loading } = useAuth();
  const { settings } = useSite();
  const navigate = useNavigate();
  const location = useLocation();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [show, setShow] = useState(false);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const from = (location.state as { from?: string } | null)?.from;

  if (!loading && user) {
    return <Navigate to={from ?? (user.role === 'client' ? '/portal' : '/console')} replace />;
  }

  async function submit(event: FormEvent) {
    event.preventDefault();
    setError('');
    setBusy(true);
    try {
      const account = await login(email.trim(), password);
      const destination = from ?? (account.role === 'client' ? '/portal' : '/console');
      navigate(destination, { replace: true });
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Could not sign in. Please try again.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="grid min-h-screen bg-white lg:grid-cols-[1.05fr_0.95fr]">
      <div className="relative hidden overflow-hidden bg-navy-950 p-10 text-white lg:flex lg:flex-col">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
        <div className="relative flex items-center justify-between">
          <Logo dark />
          <Link to="/" className="inline-flex items-center gap-1.5 text-[11px] text-white/50 transition hover:text-white">
            <ArrowLeft className="h-3 w-3" /> Back to website
          </Link>
        </div>

        <div className="relative mt-16 max-w-lg">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-white/55">
            <ShieldCheck className="h-3 w-3" /> Secure client portal
          </span>
          <h1 className="h2 mt-5 text-white">Your projects, invoices and updates in one place.</h1>
          <p className="mt-3 text-sm leading-relaxed text-white/60">
            Sign in to follow progress, download files, message the team and settle invoices without chasing anyone.
          </p>

          <div className="mt-8 space-y-3">
            {[
              'Live project status and milestones',
              'Invoices, receipts and payment submission',
              'Direct messages and file sharing',
            ].map((item) => (
              <div key={item} className="flex items-center gap-2.5 text-[12.5px] text-white/65">
                <span className="grid h-5 w-5 place-items-center rounded-full bg-brand-500/20 text-brand-300">
                  <Sparkles className="h-3 w-3" />
                </span>
                {item}
              </div>
            ))}
          </div>
        </div>

        <div className="relative mt-auto pt-12">
          <div className="scale-[0.82] origin-left">
            <HeroVisual />
          </div>
        </div>
      </div>

      <div className="flex flex-col">
        <div className="flex items-center justify-between px-6 py-5 lg:hidden">
          <Logo />
          <Link to="/" className="text-[11px] text-ink-muted">Website</Link>
        </div>

        <div className="flex flex-1 items-center justify-center px-6 py-10">
          <div className="w-full max-w-sm">
            <span className="eyebrow">Welcome back</span>
            <h2 className="mt-2 font-display text-2xl font-semibold tracking-tight text-navy-900">Sign in to your account</h2>
            <p className="mt-1.5 text-[12.5px] text-ink-muted">
              New here?{' '}
              <Link to="/register" className="font-semibold text-brand-700 hover:text-brand-800">Create a client account</Link>
            </p>

            {error && (
              <div className="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-[12.5px] font-medium text-rose-700" role="alert">
                {error}
              </div>
            )}

            <form onSubmit={submit} className="mt-6 space-y-4">
              <Field label="Email address">
                <div className="relative">
                  <Mail className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                  <input
                    className="input pl-10"
                    type="email"
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    required
                    autoComplete="email"
                    placeholder="you@company.com"
                  />
                </div>
              </Field>

              <Field label="Password">
                <div className="relative">
                  <Lock className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
                  <input
                    className="input px-10"
                    type={show ? 'text' : 'password'}
                    value={password}
                    onChange={(event) => setPassword(event.target.value)}
                    required
                    autoComplete="current-password"
                    placeholder="Your password"
                  />
                  <button
                    type="button"
                    onClick={() => setShow((value) => !value)}
                    className="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-ink-faint transition hover:text-navy-900"
                    aria-label={show ? 'Hide password' : 'Show password'}
                  >
                    {show ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                  </button>
                </div>
              </Field>

              <button type="submit" disabled={busy} className="btn-primary w-full">
                {busy ? <><Spinner className="h-4 w-4 text-white" /> Signing in…</> : <>Sign in <ArrowRight className="h-4 w-4" /></>}
              </button>
            </form>

            <div className="mt-6 rounded-2xl border border-brand-100 bg-brand-50/50 p-4">
              <p className="text-[11px] font-semibold text-navy-900">Staff and administrators</p>
              <p className="mt-0.5 text-[11px] leading-relaxed text-ink-muted">
                Use the same sign-in. You will be routed to the console automatically.
              </p>
            </div>

            <p className="mt-6 text-center text-[11px] text-ink-faint">
              Trouble signing in?{' '}
              <a href={`mailto:${settings.support_email || settings.company_email}`} className="font-medium text-brand-700 hover:text-brand-800">
                Contact support
              </a>
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
