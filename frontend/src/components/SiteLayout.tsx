import { useEffect, useRef, useState, type FormEvent } from 'react';
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom';
import {
  ArrowRight,
  ArrowUpRight,
  ChevronDown,
  Facebook,
  Linkedin,
  Mail,
  MapPin,
  Menu,
  MessageCircle,
  Phone,
  Send,
  X,
} from 'lucide-react';
import { Logo } from './Logo';
import { useSite } from '../lib/site';
import { useAuth } from '../lib/auth';
import { api } from '../lib/api';
import { appUrl } from '../lib/env';
import { useToast } from './Toast';
import { ServiceGlyph } from './Graphics';
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

      <div className="hidden border-b border-white/10 bg-navy-950 text-white/60 lg:block">
        <div className="container-x flex h-9 items-center justify-between text-[11px]">
          <div className="flex items-center gap-5">
            <a href={`mailto:${settings.company_email}`} className="flex items-center gap-1.5 transition hover:text-white">
              <Mail className="h-3 w-3" /> {settings.company_email}
            </a>
            <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="flex items-center gap-1.5 transition hover:text-white">
              <Phone className="h-3 w-3" /> {settings.company_phone}
            </a>
            <span className="flex items-center gap-1.5">
              <MapPin className="h-3 w-3" /> {settings.company_address}
            </span>
          </div>
          <div className="flex items-center gap-5">
            <span className="font-mono text-[10px] uppercase tracking-[0.16em] text-white/35">Mon – Fri · 8:00 – 18:00 EAT</span>
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

      <main key={location.pathname} className="flex-1">
        <Outlet />
      </main>

      <Footer />
    </div>
  );
}

function Header({ scrolled }: { scrolled: boolean }) {
  const { services } = useSite();
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
                  <div className="mt-2 flex items-center justify-between rounded-2xl bg-navy-900 px-4 py-3">
                    <div>
                      <p className="text-[12px] font-semibold text-white">Not sure where to start?</p>
                      <p className="text-[11px] text-white/55">Book a free 30-minute discovery call.</p>
                    </div>
                    <Link to="/contact" className="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1.5 text-[11px] font-semibold text-navy-900">
                      Talk to us <ArrowUpRight className="h-3 w-3" />
                    </Link>
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
        <div className="fixed inset-0 z-[80] lg:hidden">
          <div className="absolute inset-0 bg-navy-950/60 backdrop-blur-sm" onClick={() => setOpen(false)} />
          <div className="absolute right-0 top-0 flex h-full w-[min(92vw,400px)] flex-col overflow-y-auto bg-white shadow-lift">
            <div className="flex items-center justify-between border-b border-brand-100 px-5 py-4">
              <Logo />
              <button type="button" onClick={() => setOpen(false)} className="rounded-full p-2 text-ink-muted" aria-label="Close menu">
                <X className="h-5 w-5" />
              </button>
            </div>
            <div className="flex-1 px-4 py-4">
              <nav className="flex flex-col" aria-label="Mobile navigation">
                {navLinks.map((link) => (
                  <NavLink
                    key={link.to}
                    to={link.to}
                    end={link.end}
                    className={({ isActive }) =>
                      cn(
                        'rounded-xl px-4 py-3 text-[15px] font-medium text-ink-soft transition hover:bg-brand-50',
                        isActive && 'bg-brand-50 text-brand-700',
                      )
                    }
                  >
                    {link.label}
                  </NavLink>
                ))}
                <button
                  type="button"
                  onClick={() => setMobileServices((value) => !value)}
                  className="flex items-center justify-between rounded-xl px-4 py-3 text-[15px] font-medium text-ink-soft"
                >
                  Solutions <ChevronDown className={cn('h-4 w-4 transition', mobileServices && 'rotate-180')} />
                </button>
                {mobileServices && (
                  <div className="mb-2 space-y-1 rounded-2xl bg-brand-50/60 p-2">
                    {services.map((service) => (
                      <Link key={service.id} to={`/services/${service.slug}`} className="flex items-center gap-2.5 rounded-xl bg-white px-3 py-2.5 text-[13px] font-medium text-navy-800">
                        <ServiceGlyph name={service.icon} className="h-4 w-4 text-brand-600" /> {service.name}
                      </Link>
                    ))}
                  </div>
                )}
                <NavLink to="/faq" className="rounded-xl px-4 py-3 text-[15px] font-medium text-ink-soft hover:bg-brand-50">
                  FAQ
                </NavLink>
                <NavLink to="/contact" className="rounded-xl px-4 py-3 text-[15px] font-medium text-ink-soft hover:bg-brand-50">
                  Contact
                </NavLink>
              </nav>
            </div>
            <div className="space-y-2 border-t border-brand-100 p-4">
              <Link to="/contact" className="btn-primary w-full">
                Start a project <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to={user ? (user.role === 'client' ? '/portal' : '/console') : '/login'} className="btn-ghost w-full">
                {user ? (user.role === 'client' ? 'Client portal' : 'Staff console') : 'Sign in'}
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
      <div className="aurora opacity-60" />
      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />

      <div className="container-x relative py-16">
        <div className="grid gap-12 lg:grid-cols-[1.5fr_1fr_1fr_1.3fr]">
          <div>
            <Logo dark />
            <p className="mt-5 max-w-sm text-sm leading-relaxed text-white/60">{settings.company_about}</p>
            <p className="mt-4 font-mono text-[10px] uppercase tracking-[0.22em] text-brand-300">{settings.company_tagline}</p>
            <div className="mt-6 flex items-center gap-2">
              {settings.company_fb && (
                <a href={settings.company_fb} target="_blank" rel="noreferrer" className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="Facebook">
                  <Facebook className="h-4 w-4" />
                </a>
              )}
              {settings.company_linkedin && (
                <a href={settings.company_linkedin} target="_blank" rel="noreferrer" className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="LinkedIn">
                  <Linkedin className="h-4 w-4" />
                </a>
              )}
              <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="WhatsApp">
                <MessageCircle className="h-4 w-4" />
              </a>
              <a href={`mailto:${settings.company_email}`} className="grid h-9 w-9 place-items-center rounded-full border border-white/15 text-white/60 transition hover:border-brand-400 hover:text-white" aria-label="Email">
                <Mail className="h-4 w-4" />
              </a>
            </div>
          </div>

          <div>
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Company</h3>
            <ul className="mt-4 space-y-2.5 text-sm">
              {[
                ['About us', '/about'],
                ['Our work', '/portfolio'],
                ['Process', '/process'],
                ['Pricing', '/pricing'],
                ['FAQ', '/faq'],
                ['Contact', '/contact'],
              ].map(([label, href]) => (
                <li key={href}>
                  <Link to={href} className="text-white/60 transition hover:text-white">
                    {label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Solutions</h3>
            <ul className="mt-4 space-y-2.5 text-sm">
              {services.slice(0, 6).map((service) => (
                <li key={service.id}>
                  <Link to={`/services/${service.slug}`} className="text-white/60 transition hover:text-white">
                    {service.name}
                  </Link>
                </li>
              ))}
              <li>
                <Link to="/services" className="font-semibold text-brand-300 transition hover:text-white">
                  All services →
                </Link>
              </li>
            </ul>
          </div>

          <div>
            <h3 className="font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-white/40">Get in touch</h3>
            <ul className="mt-4 space-y-3 text-sm text-white/60">
              <li className="flex gap-2.5">
                <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-brand-300" /> {settings.company_address}
              </li>
              <li>
                <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="flex gap-2.5 transition hover:text-white">
                  <Phone className="mt-0.5 h-4 w-4 shrink-0 text-brand-300" /> {settings.company_phone}
                </a>
              </li>
              <li>
                <a href={`mailto:${settings.company_email}`} className="flex gap-2.5 transition hover:text-white">
                  <Mail className="mt-0.5 h-4 w-4 shrink-0 text-brand-300" /> {settings.company_email}
                </a>
              </li>
            </ul>

            {settings.newsletter_enabled !== '0' && (
              <form onSubmit={subscribe} className="mt-6">
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
                  <button type="submit" disabled={sending} className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-500 disabled:opacity-60" aria-label="Subscribe">
                    <Send className="h-4 w-4" />
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>

        <div className="mt-14 flex flex-col gap-4 border-t border-white/10 pt-6 text-[11px] text-white/40 sm:flex-row sm:items-center sm:justify-between">
          <p>© {new Date().getFullYear()} {settings.company_name}. All rights reserved.</p>
          <div className="flex flex-wrap items-center gap-x-5 gap-y-2">
            <a href={appUrl('/privacy.php')} className="transition hover:text-white">Privacy</a>
            <a href={appUrl('/terms.php')} className="transition hover:text-white">Terms</a>
            <a href={appUrl('/refund.php')} className="transition hover:text-white">Refunds</a>
            <Link to="/login" className="transition hover:text-white">Client login</Link>
            <span className="font-mono text-[10px] uppercase tracking-[0.16em] text-white/25">Jinja · Uganda</span>
          </div>
        </div>
      </div>
    </footer>
  );
}
