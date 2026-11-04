import { Link } from 'react-router-dom';
import { ArrowRight, ArrowUpRight, Bell, CheckCircle2, Clock3, FolderKanban, PlusCircle, ReceiptText, Sparkles, Wallet } from 'lucide-react';
import { useApi } from '../../lib/hooks';
import { useAuth } from '../../lib/auth';
import { usePageMeta } from '../../lib/seo';
import { formatDate, money, timeAgo } from '../../lib/format';
import { invoiceStatus, projectStatus } from '../../lib/status';
import { Badge, ErrorBlock, LoadingBlock, PageHeader, ProgressBar, StatCard } from '../../components/UI';
import type { Invoice, Notification, Project } from '../../lib/types';

interface Overview {
  stats: {
    active_projects: number;
    pending_requests: number;
    completed_projects: number;
    outstanding_invoices: number;
    outstanding_value: number;
    unread_notifications: number;
  };
  projects: Project[];
  invoices: Invoice[];
  notifications: Notification[];
}

export function PortalOverview() {
  usePageMeta('Client portal — Reagansoft Innovation');
  const { user } = useAuth();
  const { data, loading, error, reload } = useApi<Overview>('client/overview');

  if (loading) return <LoadingBlock label="Loading your workspace…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load your overview.'} onRetry={reload} />;

  const { stats, projects, invoices, notifications } = data;
  const firstName = (user?.full_name ?? '').split(' ')[0];

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Client portal"
        title={`Welcome back${firstName ? `, ${firstName}` : ''}.`}
        description="Everything about your projects, invoices and updates with Reagansoft — in one place."
        actions={
          <>
            <Link to="/portal/request" className="btn-primary btn-sm"><PlusCircle className="h-3.5 w-3.5" /> New request</Link>
            <Link to="/portal/invoices" className="btn-ghost btn-sm"><ReceiptText className="h-3.5 w-3.5" /> Invoices</Link>
          </>
        }
      />

      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard label="Active projects" value={stats.active_projects} sub="In delivery right now" icon={<FolderKanban className="h-5 w-5" />} />
        <StatCard label="Pending requests" value={stats.pending_requests} sub="Awaiting review or quote" icon={<Clock3 className="h-5 w-5" />} tone="violet" />
        <StatCard label="Completed" value={stats.completed_projects} sub="Delivered projects" icon={<CheckCircle2 className="h-5 w-5" />} tone="green" />
        <StatCard
          label="Outstanding"
          value={money(stats.outstanding_value)}
          sub={`${stats.outstanding_invoices} invoice${stats.outstanding_invoices === 1 ? '' : 's'}`}
          icon={<Wallet className="h-5 w-5" />}
          tone={stats.outstanding_value > 0 ? 'amber' : 'green'}
        />
      </div>

      <div className="grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="font-display text-lg font-semibold text-navy-900">Your projects</h2>
            <Link to="/portal/projects" className="text-[11px] font-semibold text-brand-700 hover:text-brand-800">View all →</Link>
          </div>

          {projects.length === 0 ? (
            <div className="rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-12 text-center">
              <FolderKanban className="mx-auto h-7 w-7 text-brand-500" />
              <p className="mt-3 font-display text-base font-semibold text-navy-900">No projects yet</p>
              <p className="mt-1 text-[12px] text-ink-muted">Send your first request and we will take it from there.</p>
              <Link to="/portal/request" className="btn-primary btn-sm mt-4">Start a request <ArrowRight className="h-3.5 w-3.5" /></Link>
            </div>
          ) : (
            <div className="space-y-3">
              {projects.map((project) => {
                const meta = projectStatus(project.status);
                return (
                  <Link key={project.id} to={`/portal/projects/${project.id}`} className="group block rounded-2xl border border-brand-100 bg-white p-4 transition hover:border-brand-200 hover:shadow-soft">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                      <div className="min-w-0">
                        <div className="flex flex-wrap items-center gap-2">
                          <span className="font-mono text-[9px] uppercase tracking-widest text-ink-faint">{project.ref_no}</span>
                          <Badge tone={meta.tone}>{meta.label}</Badge>
                        </div>
                        <p className="mt-1.5 truncate font-display text-[14px] font-semibold text-navy-900 group-hover:text-brand-700">{project.title}</p>
                        <p className="mt-0.5 text-[11px] text-ink-muted">{project.service ?? 'Custom project'}</p>
                      </div>
                      <span className="grid h-8 w-8 place-items-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <ArrowUpRight className="h-4 w-4" />
                      </span>
                    </div>
                    <div className="mt-3 flex items-center gap-3">
                      <ProgressBar value={project.progress} className="flex-1" />
                      <span className="font-mono text-[10px] font-semibold text-brand-700">{project.progress}%</span>
                    </div>
                    <div className="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10.5px] text-ink-faint">
                      <span>Updated {timeAgo(project.updated_at)}</span>
                      {project.deadline && <span>Target {formatDate(project.deadline)}</span>}
                    </div>
                  </Link>
                );
              })}
            </div>
          )}

          {invoices.length > 0 && (
            <div className="rounded-2xl border border-amber-100 bg-amber-50/60 p-4">
              <div className="flex items-start gap-3">
                <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700"><Wallet className="h-4 w-4" /></span>
                <div className="flex-1">
                  <p className="text-[12.5px] font-semibold text-amber-900">You have {invoices.length} open invoice{invoices.length === 1 ? '' : 's'}.</p>
                  <p className="mt-0.5 text-[11.5px] text-amber-800/80">Settle them from the invoices page or submit a payment reference for confirmation.</p>
                  <div className="mt-2 flex flex-wrap gap-2">
                    {invoices.slice(0, 2).map((invoice) => {
                      const status = invoiceStatus(invoice.status);
                      return (
                        <Link key={invoice.id} to="/portal/invoices" className="inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1.5 text-[10.5px] font-semibold text-amber-900 shadow-sm">
                          {invoice.invoice_no} · {money(Number(invoice.total) - Number(invoice.amount_paid))} <Badge tone={status.tone}>{status.label}</Badge>
                        </Link>
                      );
                    })}
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>

        <div className="space-y-4">
          <div className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-base font-semibold text-navy-900">Recent updates</h2>
              <Link to="/portal/notifications" className="text-[10.5px] font-semibold text-brand-700">All</Link>
            </div>
            {notifications.length === 0 ? (
              <div className="py-6 text-center">
                <Bell className="mx-auto h-5 w-5 text-ink-faint" />
                <p className="mt-2 text-[11.5px] text-ink-muted">No notifications yet.</p>
              </div>
            ) : (
              <ul className="mt-3 space-y-2.5">
                {notifications.map((item) => (
                  <li key={item.id} className="flex gap-2.5">
                    <span className={`mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full ${item.is_read ? 'bg-brand-200' : 'bg-brand-500'}`} />
                    <div className="min-w-0">
                      <p className={`text-[12px] leading-snug ${item.is_read ? 'text-ink-muted' : 'font-semibold text-navy-900'}`}>{item.title}</p>
                      {item.message && <p className="mt-0.5 line-clamp-2 text-[10.5px] leading-relaxed text-ink-muted">{item.message}</p>}
                      <p className="mt-0.5 text-[9.5px] text-ink-faint">{timeAgo(item.created_at)}</p>
                    </div>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <div className="relative overflow-hidden rounded-2xl bg-navy-950 p-5 text-white">
            <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
            <div className="relative">
              <Sparkles className="h-5 w-5 text-brand-300" />
              <p className="mt-2 font-display text-sm font-semibold">Need a change or a new idea?</p>
              <p className="mt-1 text-[11px] leading-relaxed text-white/55">Send a request and we will scope it, quote it and schedule it.</p>
              <Link to="/portal/request" className="btn bg-white text-navy-900 btn-sm mt-3 w-full">New request <ArrowRight className="h-3.5 w-3.5" /></Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
