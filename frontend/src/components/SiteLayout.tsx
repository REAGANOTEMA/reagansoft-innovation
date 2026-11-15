import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom';
import {
  ArrowRight,
  ArrowUp,
  ArrowUpRight,
  ChevronDown,
  Clock3,
  Facebook,
  Linkedin,
  Mail,
  MapPin,
  Menu,
  MessageCircle,
  Phone,
  Send,
  ShieldCheck,
  Sparkles,
  X,
} from 'lucide-react';
import { Logo } from './Logo';
import { useSite } from '../lib/site';
import { useAuth } from '../lib/auth';
import { api } from '../lib/api';
import { appUrl } from '../lib/env';
import { useToast } from './Toast';
import { ServiceGlyph } from './Graphics';
import { InstallApp } from './InstallApp';
import { cn } from '../lib/cn';

const navLinks = [
  { to: '/', label: 'Home', end: true },
  { to: '/about', label: 'About' },
  { to: '/services', label: 'Services' },
  { to: '/portfolio', label: 'Work' },
  { to: '/process', label: 'Process' },
  { to: '/pricing', label: 'Pricing' },
];

function ScrollToTop() {
  const { pathname } = useLocation();
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior });
  }, [pathname]);
  return null;
}

/** A thin gradient progress rail pinned above the header. */
function ScrollProgress() {
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    const onScroll = () => {
      const height = document.documentElement.scrollHeight - window.innerHeight;
      setProgress(height > 0 ? Math.min(1, window.scrollY / height) : 0);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    return () => {
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onScroll);
    };
  }, []);

  return (
    <div className="pointer-events-none fixed inset-x-0 top-0 z-[70] h-[3px] bg-transparent" aria-hidden="true">
      <div
        className="h-full origin-left bg-gradient-to-r from-brand-400 via-cyan-300 to-brand-500 shadow-[0_0_16px_rgba(30,158,232,.75)] transition-[width] duration-150 ease-out"
        style={{ width: `${progress * 100}%` }}
      />
    </div>
  );
}

/** Persistent quick-contact + back-to-top stack, available on every page. */
function FloatingActions() {
  const { settings } = useSite();
  const waNumber = settings.company_whatsapp.replace(/\D/g, '') || settings.company_phone.replace(/\D/g, '');
  const wa = `https://wa.me/${waNumber}`;

  return (
    <div className="no-print fixed bottom-4 left-4 z-[65] flex flex-col items-start gap-2 sm:bottom-6 sm:left-6">
      <button
        type="button"
        onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
        aria-label="Back to top"
        className="grid h-10 w-10 place-items-center rounded-full border border-brand-100 bg-white/95 text-navy-800 shadow-soft backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-300 hover:text-brand-700"
      >
        <ArrowUp className="h-4 w-4" />
      </button>
      <a
        href={`tel:${settings.company_phone.replace(/\s/g, '')}`}
        aria-label="Call Reagansoft Innovation"
        className="grid h-10 w-10 place-items-center rounded-full border border-brand-100 bg-white/95 text-navy-800 shadow-soft backdrop-blur transition hover:-translate-y-0.5 hover:border-brand-300 hover:text-brand-700"
      >
        <Phone className="h-4 w-4" />
      </a>
      <a
        href={wa}
        target="_blank"
        rel="noreferrer"
        aria-label="Chat on WhatsApp"
        className="group relative grid h-12 w-12 place-items-center rounded-full bg-emerald-500 text-white shadow-[0_16px_36px_-14px_rgba(16,185,129,.8)] transition hover:-translate-y-0.5 hover:bg-emerald-400"
      >
        <span className="absolute inset-0 animate-ping rounded-full bg-emerald-400/40 motion-reduce:animate-none" aria-hidden="true" />
        <MessageCircle className="relative h-5 w-5" />
      </a>
    </div>
  );
}

export function SiteLayout() {
  const [scrolled, setScrolled] = useState(false);
  const { settings, services } = useSite();
  const { user } = useAuth();
  const location = useLocation();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const isPortal = user?.role === 'client';
  const isStaff = user?.role === 'admin' || user?.role === 'staff';

  return (
    <div className="flex min-h-screen flex-col bg-white">
      <ScrollToTop />
      <ScrollProgress />

      <div className="hidden border-b border-white/10 bg-navy-950 text-white/60 lg:block">
        <div className="container-x flex h-9 items-center justify-between text-[11px]">
          <div className="flex items-center gap-5">
            <a href={`mailto:${settings.company_email}`} className="flex items-center gap-1.5 transition hover:text-white">
              <Mail className="h-3 w-3" /> {settings.company_email}
            </a>
            <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="flex items-center gap-1.5 transition hover:text-white">
              <Phone className="h-3 w-3" /> {settings.company_phone}
            </a>
            {settings.company_phone_alt && (
              <a href={`tel:${settings.company_phone_alt.replace(/\s/g, '')}`} className="flex items-center gap-1.5 transition hover:text-white">
                <Phone className="h-3 w-3" /> {settings.company_phone_alt}
              </a>
            )}
            <span className="flex items-center gap-1.5">
              <MapPin className="h-3 w-3" /> {settings.company_address}
            </span>
          </div>
          <div className="flex items-center gap-5">
            <span className="font-mono text-[10px] uppercase tracking-[0.16em] text-white/35">Mon - Fri · 8:00 - 18:00 EAT</span>
            {isStaff ? (
              <Link to="/console" className="font-semibold text-brand-300 transition hover:text-white">
                Staff console
              </Link>
            ) : isPortal ? (
              <Link to="/portal" className="font-semibold text-brand-300 transition hover:text-white">
                Client portal
              </Link>
            ) : (
              <>
                <Link to="/login" className="transition hover:text-white">
                  Client login
                </Link>
                <Link to="/register" className="font-semibold text-brand-300 transition hover:text-white">
                  Create account
                </Link>
              </>
            )}
          </div>
        </div>
      </div>

      <Header scrolled={scrolled} />

      {settings.site_announcement && (
        <div className="border-b border-brand-100 bg-brand-50">
          <div className="container-x flex items-center justify-center gap-2 py-2 text-center text-xs font-medium text-brand-800">
            <span className="h-1.5 w-1.5 rounded-full bg-brand-500" />
            {settings.site_announcement}
          </div>
        </div>
      )}

      <main key={location.pathname} className="page-enter flex-1">
        <Outlet />
      </main>

      <Footer />
      <InstallApp />
      <FloatingActions />
    </div>
  );
}

function Header({ scrolled }: { scrolled: boolean }) {
  const { settings, services } = useSite();
  const { user } = useAuth();
  const [open, setOpen] = useState(false);
  const [servicesOpen, setServicesOpen] = useState(false);
  const [mobileServices, setMobileServices] = useState(false);
  const location = useLocation();
  const closeTimer = useRef<number | null>(null);

  useEffect(() => {
    setOpen(false);
    setServicesOpen(false);
  }, [location.pathname]);

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [open]);

  const isServices = location.pathname.startsWith('/services');
  const featured = services.slice(0, 6);

  return (
    <header
      className={cn(
        'sticky top-0 z-50 border-b transition-all duration-300',
        scrolled ? 'border-brand-100/80 bg-white/90 shadow-[0_10px_30px_-24px_rgba(8,59,102,.4)] backdrop-blur-xl' : 'border-transparent bg-white',
      )}
    >
      <div className="container-x flex h-[72px] items-center justify-between gap-4">
        <Logo />

        <nav className="hidden items-center gap-1 lg:flex" aria-label="Main navigation">
          {navLinks.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.end}
              className={({ isActive }) =>
                cn(
                  'relative rounded-full px-3.5 py-2 text-[13.5px] font-medium text-ink-soft transition hover:text-brand-700',
                  (isActive || (link.to === '/services' && isServices)) && 'text-brand-700',
                )
              }
            >
              {link.label}
            </NavLink>
          ))}

          <div
            className="relative"
            onMouseEnter={() => {
              if (closeTimer.current) window.clearTimeout(closeTimer.current);
              setServicesOpen(true);
            }}
            onMouseLeave={() => {
              closeTimer.current = window.setTimeout(() => setServicesOpen(false), 140);
            }}
          >
            <button
              type="button"
              className={cn(
                'flex items-center gap-1 rounded-full px-3.5 py-2 text-[13.5px] font-medium text-ink-soft transition hover:text-brand-700',
                servicesOpen && 'text-brand-700',
              )}
              aria-expanded={servicesOpen}
              aria-haspopup="true"
              onClick={() => setServicesOpen((value) => !value)}
            >
              Solutions <ChevronDown className={cn('h-3.5 w-3.5 transition', servicesOpen && 'rotate-180')} />
            </button>

            {servicesOpen && (
              <div className="absolute left-1/2 top-full w-[620px] -translate-x-1/2 pt-3">
                <div className="overflow-hidden rounded-3xl border border-brand-100 bg-white p-3 shadow-lift">
                  <div className="grid grid-cols-2 gap-1">
                    {featured.map((service) => (
                      <Link
                        key={service.id}
                        to={`/services/${service.slug}`}
                        className="group flex items-start gap-3 rounded-2xl p-3 transition hover:bg-brand-50"
                      >
                        <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                          <ServiceGlyph name={service.icon} className="h-4 w-4" />
                        </span>
                        <span>
                          <span className="block text-[13px] font-semibold text-navy-900">{service.name}</span>
                          <span className="mt-0.5 block text-[11px] leading-snug text-ink-muted">{service.price_short}</span>
                        </span>
                      </Link>
                    ))}
                  </div>
                  <div className="mt-2 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-navy-900 px-4 py-3">
                    <div>
                      <p className="text-[12px] font-semibold text-white">Not sure where to start?</p>
                      <p className="text-[11px] text-white/55">Book a free 30-minute discovery call.</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <Link to="/services" className="inline-flex items-center gap-1 rounded-full border border-white/20 px-3 py-1.5 text-[11px] font-semibold text-white/80 transition hover:border-white/40 hover:text-white">
                        All services
                      </Link>
                      <Link to="/contact" className="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1.5 text-[11px] font-semibold text-navy-900">
                        Talk to us <ArrowUpRight className="h-3 w-3" />
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            )}
          </div>

          <NavLink
            to="/faq"
            className={({ isActive }) =>
              cn('rounded-full px-3.5 py-2 text-[13.5px] font-medium text-ink-soft transition hover:text-brand-700', isActive && 'text-brand-700')
            }
          >
            FAQ
          </NavLink>
        </nav>

        <div className="hidden items-center gap-2.5 lg:flex">
          {user ? (
            <Link to={user.role === 'client' ? '/portal' : '/console'} className="btn-ghost btn-sm">
              {user.role === 'client' ? 'Client portal' : 'Console'} <ArrowUpRight className="h-3.5 w-3.5" />
            </Link>
          ) : (
            <Link to="/login" className="text-[13px] font-semibold text-navy-800 transition hover:text-brand-700">
              Sign in
            </Link>
          )}
          <Link to="/contact" className="btn-primary btn-sm">
            Start a project <ArrowRight className="h-3.5 w-3.5" />
          </Link>
        </div>

        <button
          type="button"
          className="grid h-10 w-10 place-items-center rounded-xl border border-brand-100 text-navy-900 lg:hidden"
          onClick={() => setOpen(true)}
          aria-label="Open menu"
        >
          <Menu className="h-5 w-5" />
        </button>
      </div>

      {open && (
        <div className="fixed inset-0 z-[80] lg:hidden" role="dialog" aria-modal="true" aria-label="Site menu">
          <div className="absolute inset-0 bg-navy-950/65 backdrop-blur-sm" onClick={() => setOpen(false)} />
          <div className="absolute right-0 top-0 flex h-full w-[min(94vw,420px)] flex-col overflow-hidden bg-white shadow-lift">
            <div className="relative shrink-0 overflow-hidden bg-navy-950 px-5 pb-5 pt-[calc(1rem+env(safe-area-inset-top))] text-white">
              <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
              <div className="pointer-events-none absolute -right-12 -top-16 h-48 w-48 rounded-full bg-brand-500/25 blur-3xl" />
              <div className="relative flex items-center justify-between gap-3">
                <Logo dark />
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  className="grid h-11 w-11 place-items-center rounded-full border border-white/15 bg-white/5 text-white/70 backdrop-blur transition hover:bg-white/10 hover:text-white"
                  aria-label="Close menu"
                >
                  <X className="h-5 w-5" />
                </button>
              </div>

              <div className="relative mt-5 grid grid-cols-2 gap-2">
                <a
                  href={`tel:${settings.company_phone.replace(/\s/g, '')}`}
                  className="flex min-h-[46px] items-center gap-2 rounded-xl border border-white/12 bg-white/[0.06] px-3 py-2.5 text-[11.5px] font-semibold text-white/80 backdrop-blur transition hover:bg-white/10"
                >
                  <Phone className="h-3.5 w-3.5 shrink-0 text-brand-300" /> Call us
                </a>
                <a
                  href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`}
                  target="_blank"
                  rel="noreferrer"
                  className="flex min-h-[46px] items-center gap-2 rounded-xl border border-white/12 bg-white/[0.06] px-3 py-2.5 text-[11.5px] font-semibold text-white/80 backdrop-blur transition hover:bg-white/10"
                >
                  <MessageCircle className="h-3.5 w-3.5 shrink-0 text-emerald-300" /> WhatsApp
                </a>
              </div>
              {!user && (
                <Link
                  to="/login"
                  className="relative mt-2 flex min-h-[44px] items-center justify-between gap-2 rounded-xl bg-brand-500/15 px-3 py-2 text-[11.5px] font-semibold text-brand-200 ring-1 ring-brand-400/30"
                >
                  <span className="inline-flex items-center gap-2"><ShieldCheck className="h-3.5 w-3.5" /> Client portal sign in</span>
                  <ArrowUpRight className="h-3.5 w-3.5" />
                </Link>
              )}
            </div>

            <div className="flex-1 overflow-y-auto overscroll-contain px-4 py-4">
              <nav className="flex flex-col gap-0.5" aria-label="Mobile navigation">
                {navLinks.map((link) => (
                  <NavLink
                    key={link.to}
                    to={link.to}
                    end={link.end}
                    className={({ isActive }) =>
                      cn(
                        'flex min-h-[48px] items-center justify-between rounded-xl px-4 py-3 text-[15px] font-semibold text-ink-soft transition hover:bg-brand-50',
                        isActive && 'bg-brand-50 text-brand-700',
                      )
                    }
                  >
                    {({ isActive }) => (
                      <>
                        <span>{link.label}</span>
                        {isActive && <span className="h-1.5 w-1.5 rounded-full bg-brand-500" />}
                      </>
                    )}
                  </NavLink>
                ))}
                <button
                  type="button"
                  onClick={() => setMobileServices((value) => !value)}
                  className="flex min-h-[48px] items-center justify-between rounded-xl px-4 py-3 text-[15px] font-semibold text-ink-soft transition hover:bg-brand-50"
                  aria-expanded={mobileServices}
                >
                  Solutions <ChevronDown className={cn('h-4 w-4 transition', mobileServices && 'rotate-180 text-brand-600')} />
                </button>
                {mobileServices && (
                  <div className="mb-2 mt-1 space-y-1 rounded-2xl border border-brand-100 bg-brand-50/60 p-2">
                    {services.map((service) => (
                      <Link
                        key={service.id}
                        to={`/services/${service.slug}`}
                        className="flex min-h-[44px] items-center gap-2.5 rounded-xl bg-white px-3 py-2.5 text-[13px] font-medium text-navy-800 shadow-[0_1px_0_rgba(8,59,102,.04)] transition hover:text-brand-700"
                      >
                        <span className="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600">
                          <ServiceGlyph name={service.icon} className="h-3.5 w-3.5" />
                        </span>
                        <span className="min-w-0 flex-1 leading-snug">{service.name}</span>
                        <ArrowUpRight className="h-3.5 w-3.5 shrink-0 text-ink-faint" />
                      </Link>
                    ))}
                  </div>
                )}
                <NavLink to="/faq" className="flex min-h-[48px] items-center rounded-xl px-4 py-3 text-[15px] font-semibold text-ink-soft transition hover:bg-brand-50">
                  FAQ
                </NavLink>
                <NavLink to="/contact" className="flex min-h-[48px] items-center rounded-xl px-4 py-3 text-[15px] font-semibold text-ink-soft transition hover:bg-brand-50">
                  Contact
                </NavLink>
              </nav>

              <div className="mt-4 rounded-2xl border border-brand-100 bg-brand-50/50 p-4">
                <div className="flex items-start gap-2.5 text-[11.5px] leading-relaxed text-ink-muted">
                  <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                  <span>{settings.company_address}</span>
                </div>
                <div className="mt-2 flex items-start gap-2.5 text-[11.5px] leading-relaxed text-ink-muted">
                  <Clock3 className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                  <span>{settings.company_hours}</span>
                </div>
                <div className="mt-2 flex items-start gap-2.5 text-[11.5px] leading-relaxed text-ink-muted">
                  <Mail className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                  <span className="break-anywhere">{settings.company_email}</span>
                </div>
              </div>
            </div>

            <div className="space-y-2 border-t border-brand-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
              <Link to="/contact" className="btn-primary w-full">
                Start a project <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to={user ? (user.role === 'client' ? '/portal' : '/console') : '/register'} className="btn-ghost w-full">
                {user ? (user.role === 'client' ? 'Open client portal' : 'Open staff console') : 'Create an account'}
              </Link>
            </div>
          </div>
        </div>
      )}
    </header>
  );
}

function Footer() {
  const { settings, services } = useSite();
  const { push } = useToast();
  const [email, setEmail] = useState('');
  const [sending, setSending] = useState(false);

  async function subscribe(event: FormEvent) {
    event.preventDefault();
    if (!email.trim()) return;
    setSending(true);
    try {
      await api('public/newsletter', { method: 'POST', body: { email } });
      push('success', 'You are subscribed. Look out for our monthly insights.');
      setEmail('');
    } catch (error) {
      push('error', error instanceof Error ? error.message : 'Could not subscribe right now.');
    } finally {
      setSending(false);
    }
  }

  return (
    <footer className="relative overflow-hidden bg-navy-950 text-white">
      <div className="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-400/70 to-transparent" />
      <div className="aurora opacity-50" />
      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-25" />
      <div aria-hidden="true" className="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-brand-600/15 blur-3xl" />
      <div aria-hidden="true" className="pointer-events-none absolute -right-20 -top-20 h-80 w-80 text-brand-400/20">
        <svg viewBox="0 0 320 320" fill="none" className="h-full w-full">
          <circle cx="160" cy="160" r="150" stroke="currentColor" strokeOpacity="0.22" />
          <circle cx="160" cy="160" r="112" stroke="currentColor" strokeOpacity="0.18" strokeDasharray="3 7" />
          <circle cx="160" cy="160" r="74" stroke="currentColor" strokeOpacity="0.25" />
          <circle cx="160" cy="10" r="3" fill="currentColor" />
          <circle cx="304" cy="160" r="2.5" fill="currentColor" fillOpacity="0.7" />
          <circle cx="52" cy="246" r="2" fill="currentColor" fillOpacity="0.5" />
        </svg>
      </div>
      <p aria-hidden="true" className="pointer-events-none absolute -top-3 right-2 select-none font-display text-[64px] font-bold uppercase leading-none tracking-tight text-white/[0.025] sm:right-8 sm:text-[110px] lg:text-[140px]">
        Reagansoft
      </p>

      <div className="container-x relative pb-4 pt-10 sm:pt-14">
        <div className="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-brand-600/25 via-white/[0.05] to-transparent p-5 shadow-[0_30px_80px_-40px_rgba(0,0,0,.8)] sm:p-8">
          <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-20" />
          <div className="pointer-events-none absolute -right-10 -top-16 h-48 w-48 rounded-full bg-brand-400/20 blur-3xl" />
          <div className="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div className="max-w-2xl">
              <span className="inline-flex items-center gap-2 font-mono text-[10px] font-semibold uppercase tracking-[0.22em] text-brand-200">
                <Sparkles className="h-3.5 w-3.5" /> Let's build
              </span>
              <h2 className="mt-2 font-display text-[clamp(1.4rem,5vw,2rem)] font-semibold leading-tight text-white">
                Your next system starts with one conversation.
              </h2>
              <p className="mt-2 text-[13px] leading-relaxed text-white/55">
                Tell us what is slowing your team down. We will reply with a clear plan, a realistic timeline and an itemised quote.
              </p>
            </div>
            <div className="flex flex-col gap-2 min-[420px]:flex-row lg:shrink-0">
              <Link to="/contact" className="btn bg-white text-navy-950 hover:bg-brand-50">
                Start a project <ArrowRight className="h-4 w-4" />
              </Link>
              <a
                href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`}
                target="_blank"
                rel="noreferrer"
                className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10"
              >
                <MessageCircle className="h-4 w-4" /> WhatsApp us
              </a>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-x-6 gap-y-10 py-12 sm:gap-x-10 lg:grid-cols-[1.45fr_0.65fr_0.9fr_1.1fr] lg:gap-12 lg:py-14">
          <div className="col-span-2 lg:col-span-1">
            <Logo dark variant="real" compact />
            <p className="mt-4 max-w-sm text-sm leading-relaxed text-white/60">{settings.company_about}</p>
            <p className="mt-4 font-mono text-[10px] uppercase tracking-[0.22em] text-brand-300">{settings.company_tagline}</p>
            <div className="mt-4 flex flex-wrap items-center gap-2 text-[10.5px] text-white/40">
              <span className="rounded-full border border-white/15 px-2.5 py-1 font-mono uppercase tracking-[0.12em]">
                URSB Reg. No. {settings.company_registration_no ?? '80048847119307'}
              </span>
              <span>Registered in {settings.company_registration_country ?? 'Uganda'}</span>
            </div>
            <div className="mt-5 flex items-center gap-2">
              {settings.company_fb && (
                <a href={settings.company_fb} target="_blank" rel="noreferrer" className="grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="Facebook">
                  <Facebook className="h-4 w-4" />
                </a>
              )}
              {settings.company_linkedin && (
                <a href={settings.company_linkedin} target="_blank" rel="noreferrer" className="grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="LinkedIn">
                  <Linkedin className="h-4 w-4" />
                </a>
              )}
              <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="WhatsApp">
                <MessageCircle className="h-4 w-4" />
              </a>
              <a href={`mailto:${settings.company_email}`} className="grid h-10 w-10 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="Email">
                <Mail className="h-4 w-4" />
              </a>
            </div>
          </div>

          <div>
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Company</h3>
            <ul className="mt-4 space-y-3 text-[13px] sm:text-sm">
              {[
                ['About us', '/about'],
                ['Our work', '/portfolio'],
                ['Process', '/process'],
                ['Pricing', '/pricing'],
                ['FAQ', '/faq'],
                ['Contact', '/contact'],
              ].map(([label, href]) => (
                <li key={href}>
                  <Link to={href} className="inline-flex min-h-[32px] items-center text-white/60 transition hover:text-white">
                    {label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Solutions</h3>
            <ul className="mt-4 space-y-3 text-[13px] sm:text-sm">
              {services.slice(0, 6).map((service) => (
                <li key={service.id}>
                  <Link to={`/services/${service.slug}`} className="inline-flex min-h-[32px] items-start text-white/60 transition hover:text-white">
                    <span className="break-anywhere">{service.name}</span>
                  </Link>
                </li>
              ))}
              <li>
                <Link to="/services" className="inline-flex min-h-[32px] items-center gap-1 font-semibold text-brand-300 transition hover:text-white">
                  All services <ArrowRight className="h-3 w-3" />
                </Link>
              </li>
            </ul>
          </div>

          <div className="col-span-2 lg:col-span-1">
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Get in touch</h3>
            <ul className="mt-4 space-y-2.5 text-[13px] text-white/60 sm:text-sm">
              <li>
                <a href={settings.company_map_url || 'https://maps.google.com/?q=Jinja,Uganda'} target="_blank" rel="noreferrer" className="flex gap-2.5 rounded-xl border border-white/10 bg-white/[0.03] p-2.5 transition hover:border-white/20 hover:text-white">
                  <span className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/15 text-brand-300"><MapPin className="h-4 w-4" /></span>
                  <span className="break-anywhere pt-0.5">{settings.company_address}</span>
                </a>
              </li>
              <li>
                <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="flex gap-2.5 rounded-xl border border-white/10 bg-white/[0.03] p-2.5 transition hover:border-white/20 hover:text-white">
                  <span className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/15 text-brand-300"><Phone className="h-4 w-4" /></span>
                  <span className="break-anywhere pt-0.5">{settings.company_phone}{settings.company_phone_alt ? ` · ${settings.company_phone_alt}` : ''}</span>
                </a>
              </li>
              <li>
                <a href={`mailto:${settings.company_email}`} className="flex gap-2.5 rounded-xl border border-white/10 bg-white/[0.03] p-2.5 transition hover:border-white/20 hover:text-white">
                  <span className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/15 text-brand-300"><Mail className="h-4 w-4" /></span>
                  <span className="break-anywhere pt-0.5">{settings.company_email}</span>
                </a>
              </li>
              <li className="flex gap-2.5 rounded-xl border border-white/10 bg-white/[0.03] p-2.5">
                <span className="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-brand-500/15 text-brand-300"><Clock3 className="h-4 w-4" /></span>
                <span className="break-anywhere pt-0.5">{settings.company_hours}</span>
              </li>
            </ul>

            {settings.newsletter_enabled !== '0' && (
              <form onSubmit={subscribe} className="mt-5">
                <label className="mb-2 block text-[11px] font-semibold uppercase tracking-[0.14em] text-white/40" htmlFor="newsletter">
                  Monthly insights
                </label>
                <div className="flex gap-2">
                  <input
                    id="newsletter"
                    type="email"
                    required
                    value={email}
                    onChange={(event) => setEmail(event.target.value)}
                    placeholder="you@company.com"
                    className="min-w-0 flex-1 rounded-xl border border-white/15 bg-white/5 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-white/30 focus:border-brand-400"
                  />
                  <button type="submit" disabled={sending} className="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-500 disabled:opacity-60" aria-label="Subscribe">
                    <Send className="h-4 w-4" />
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>

        <div className="grid grid-cols-2 gap-2 rounded-2xl border border-white/10 bg-white/[0.03] p-2 sm:grid-cols-4">
          {[
            { icon: ShieldCheck, label: 'Secure client portal' },
            { icon: Sparkles, label: 'Fixed scope before build' },
            { icon: MessageCircle, label: 'WhatsApp support' },
            { icon: MapPin, label: 'Jinja · Uganda' },
          ].map((item) => (
            <div key={item.label} className="flex items-center gap-2 rounded-xl px-3 py-2.5 text-[10.5px] font-medium text-white/55">
              <item.icon className="h-3.5 w-3.5 shrink-0 text-brand-300" />
              <span>{item.label}</span>
            </div>
          ))}
        </div>

        <div className="mt-6 flex flex-col gap-4 border-t border-white/10 py-6 text-[11px] text-white/40 sm:flex-row sm:items-center sm:justify-between">
          <div className="text-center sm:text-left">
            <p>© {new Date().getFullYear()} {settings.company_name}. All rights reserved.</p>
            <p className="mt-1 font-mono text-[9.5px] uppercase tracking-[0.14em] text-white/30">
              Registered as {settings.company_legal_name ?? 'ReaganSof Innovation'} · URSB No. {settings.company_registration_no ?? '80048847119307'}
            </p>
          </div>
          <div className="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 sm:justify-end">
            <a href={appUrl('/privacy.php')} className="inline-flex min-h-[32px] items-center transition hover:text-white">Privacy</a>
            <a href={appUrl('/terms.php')} className="inline-flex min-h-[32px] items-center transition hover:text-white">Terms</a>
            <a href={appUrl('/refund-policy.php')} className="inline-flex min-h-[32px] items-center transition hover:text-white">Refunds</a>
            <a href={appUrl('/cookie-policy.php')} className="inline-flex min-h-[32px] items-center transition hover:text-white">Cookies</a>
            <Link to="/login" className="inline-flex min-h-[32px] items-center transition hover:text-white">Client login</Link>
            <button
              type="button"
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              className="inline-flex min-h-[32px] items-center gap-1.5 rounded-full border border-white/15 px-3 py-1 text-[10.5px] font-semibold text-white/60 transition hover:border-brand-400 hover:text-white"
            >
              Back to top <ArrowUp className="h-3 w-3" />
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
}
