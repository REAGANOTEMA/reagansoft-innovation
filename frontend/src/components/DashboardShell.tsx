import { useEffect, useState, type FormEvent } from 'react';
import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom';
import { Bell, ChevronDown, ExternalLink, LogOut, Menu, Search, X, type LucideIcon } from 'lucide-react';
import { Logo } from './Logo';
import { useAuth } from '../lib/auth';
import { api } from '../lib/api';
import { initials, titleCase } from '../lib/format';
import { cn } from '../lib/cn';
import { useToast } from './Toast';

export interface NavItem {
  to: string;
  label: string;
  icon: LucideIcon;
  end?: boolean;
  adminOnly?: boolean;
}

interface Props {
  variant: 'portal' | 'console';
  items: NavItem[];
  footerItems?: NavItem[];
}

export function DashboardShell({ variant, items, footerItems = [] }: Props) {
  const { user, logout } = useAuth();
  const { push } = useToast();
  const navigate = useNavigate();
  const [open, setOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [unread, setUnread] = useState(0);
  const [search, setSearch] = useState('');

  const isAdmin = user?.role === 'admin';

  useEffect(() => {
    let alive = true;
    const load = () => {
      api<{ unread: number }>(variant === 'portal' ? 'client/notifications' : 'admin/notifications')
        .then((res) => {
          if (alive) setUnread(Number(res.data.unread) || 0);
        })
        .catch(() => undefined);
    };
    load();
    const timer = window.setInterval(load, 60_000);
    return () => {
      alive = false;
      window.clearInterval(timer);
    };
  }, [variant]);

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [open]);

  async function handleLogout() {
    await logout();
    push('info', 'You have signed out.');
    navigate('/');
  }

  function submitSearch(event: FormEvent) {
    event.preventDefault();
    const query = search.trim();
    if (!query) return;
    navigate(`${variant === 'portal' ? '/portal/projects' : '/console/projects'}?q=${encodeURIComponent(query)}`);
  }

  const visibleItems = items.filter((item) => !item.adminOnly || isAdmin);
  const visibleFooter = footerItems.filter((item) => !item.adminOnly || isAdmin);

  return (
    <div className="min-h-screen bg-[#f5f9fd]">
      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 flex w-[264px] flex-col bg-navy-950 text-white transition-transform duration-300 lg:translate-x-0',
          open ? 'translate-x-0' : '-translate-x-full',
        )}
      >
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-25" />
        <div className="relative flex h-[72px] items-center justify-between border-b border-white/10 px-5">
          <Logo dark />
          <button type="button" className="rounded-lg p-1.5 text-white/60 lg:hidden" onClick={() => setOpen(false)} aria-label="Close menu">
            <X className="h-5 w-5" />
          </button>
        </div>

        <div className="relative px-4 py-4">
          <span className="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-white/55">
            <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
            {variant === 'portal' ? 'Client portal' : isAdmin ? 'Administrator' : 'Staff workspace'}
          </span>
        </div>

        <nav className="relative flex-1 space-y-0.5 overflow-y-auto px-3 pb-4" aria-label="Workspace navigation">
          {visibleItems.map((item) => (
            <NavLink
              key={item.to}
              to={item.to}
              end={item.end}
              onClick={() => setOpen(false)}
              className={({ isActive }) => cn('side-link', isActive && 'side-link-active')}
            >
              <item.icon className="h-[18px] w-[18px] shrink-0 opacity-80" strokeWidth={1.8} />
              <span className="flex-1">{item.label}</span>
            </NavLink>
          ))}

          {visibleFooter.length > 0 && (
            <>
              <div className="my-3 border-t border-white/10" />
              {visibleFooter.map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.end}
                  onClick={() => setOpen(false)}
                  className={({ isActive }) => cn('side-link', isActive && 'side-link-active')}
                >
                  <item.icon className="h-[18px] w-[18px] shrink-0 opacity-80" strokeWidth={1.8} />
                  <span className="flex-1">{item.label}</span>
                </NavLink>
              ))}
            </>
          )}
        </nav>

        <div className="relative border-t border-white/10 p-3">
          <div className="rounded-2xl bg-white/[0.06] p-3">
            <div className="flex items-center gap-2.5">
              <span className="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-xs font-bold text-white">
                {initials(user?.full_name ?? 'RSI')}
              </span>
              <div className="min-w-0 flex-1">
                <p className="truncate text-[13px] font-semibold text-white">{user?.full_name}</p>
                <p className="truncate text-[10px] uppercase tracking-wider text-white/40">{titleCase(user?.role ?? '')}</p>
              </div>
            </div>
            <div className="mt-3 flex gap-1.5">
              <Link to="/" className="flex flex-1 items-center justify-center gap-1 rounded-lg bg-white/8 py-1.5 text-[10px] font-medium text-white/60 transition hover:bg-white/15 hover:text-white">
                <ExternalLink className="h-3 w-3" /> Website
              </Link>
              <button
                type="button"
                onClick={handleLogout}
                className="flex flex-1 items-center justify-center gap-1 rounded-lg bg-white/8 py-1.5 text-[10px] font-medium text-white/60 transition hover:bg-rose-500/20 hover:text-rose-200"
              >
                <LogOut className="h-3 w-3" /> Sign out
              </button>
            </div>
          </div>
        </div>
      </aside>

      {open && <div className="fixed inset-0 z-40 bg-navy-950/60 backdrop-blur-sm lg:hidden" onClick={() => setOpen(false)} />}

      <div className="lg:pl-[264px]">
        <header className="sticky top-0 z-30 flex h-[72px] items-center gap-3 border-b border-brand-100 bg-white/85 px-4 backdrop-blur-xl sm:px-6">
          <button type="button" className="grid h-9 w-9 place-items-center rounded-xl border border-brand-100 text-navy-900 lg:hidden" onClick={() => setOpen(true)} aria-label="Open menu">
            <Menu className="h-5 w-5" />
          </button>

          <form onSubmit={submitSearch} className="hidden max-w-sm flex-1 items-center gap-2 rounded-xl border border-brand-100 bg-white px-3 py-2 text-sm text-ink-muted focus-within:border-brand-300 sm:flex">
            <Search className="h-4 w-4 shrink-0" />
            <input
              value={search}
              onChange={(event) => setSearch(event.target.value)}
              placeholder={variant === 'portal' ? 'Search your projects…' : 'Search projects…'}
              className="w-full bg-transparent outline-none placeholder:text-ink-faint"
              aria-label="Search"
            />
          </form>

          <div className="ml-auto flex items-center gap-2">
            <Link
              to={variant === 'portal' ? '/portal/notifications' : '/console/notifications'}
              className="relative grid h-9 w-9 place-items-center rounded-xl border border-brand-100 text-ink-muted transition hover:bg-brand-50 hover:text-brand-700"
              aria-label="Notifications"
            >
              <Bell className="h-4 w-4" />
              {unread > 0 && (
                <span className="absolute -right-1 -top-1 grid h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[9px] font-bold text-white">
                  {unread > 9 ? '9+' : unread}
                </span>
              )}
            </Link>

            <div className="relative">
              <button
                type="button"
                onClick={() => setMenuOpen((value) => !value)}
                className="flex items-center gap-2 rounded-xl border border-brand-100 py-1.5 pl-1.5 pr-2.5 text-left transition hover:bg-brand-50"
              >
                <span className="grid h-7 w-7 place-items-center rounded-lg bg-navy-900 text-[10px] font-bold text-white">{initials(user?.full_name ?? 'RSI')}</span>
                <span className="hidden sm:block">
                  <span className="block max-w-[140px] truncate text-[12px] font-semibold leading-tight text-navy-900">{user?.full_name}</span>
                  <span className="block text-[9px] uppercase tracking-wider text-ink-faint">{titleCase(user?.role ?? '')}</span>
                </span>
                <ChevronDown className={cn('h-3.5 w-3.5 text-ink-faint transition', menuOpen && 'rotate-180')} />
              </button>

              {menuOpen && (
                <>
                  <button type="button" className="fixed inset-0 z-10 cursor-default" onClick={() => setMenuOpen(false)} aria-label="Close menu" />
                  <div className="absolute right-0 z-20 mt-2 w-56 overflow-hidden rounded-2xl border border-brand-100 bg-white p-1.5 shadow-lift">
                    <div className="border-b border-brand-50 px-3 py-2.5">
                      <p className="truncate text-sm font-semibold text-navy-900">{user?.full_name}</p>
                      <p className="truncate text-[11px] text-ink-muted">{user?.email}</p>
                    </div>
                    <Link
                      to={variant === 'portal' ? '/portal/profile' : '/console/profile'}
                      onClick={() => setMenuOpen(false)}
                      className="mt-1 block rounded-xl px-3 py-2 text-sm text-ink-soft transition hover:bg-brand-50"
                    >
                      Account settings
                    </Link>
                    <Link to="/" onClick={() => setMenuOpen(false)} className="block rounded-xl px-3 py-2 text-sm text-ink-soft transition hover:bg-brand-50">
                      Public website
                    </Link>
                    <button
                      type="button"
                      onClick={handleLogout}
                      className="mt-1 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                    >
                      <LogOut className="h-4 w-4" /> Sign out
                    </button>
                  </div>
                </>
              )}
            </div>
          </div>
        </header>

        <main className="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
