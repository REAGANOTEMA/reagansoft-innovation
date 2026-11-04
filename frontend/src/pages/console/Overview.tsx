import { Link } from 'react-router-dom';
import {
  ArrowRight,
  ArrowUpRight,
  Banknote,
  CheckCircle2,
  ClipboardList,
  CreditCard,
  FolderKanban,
  Inbox,
  PlusCircle,
  ReceiptText,
  TrendingUp,
  Users,
} from 'lucide-react';
import { useApi } from '../../lib/hooks';
import { useAuth } from '../../lib/auth';
import { usePageMeta } from '../../lib/seo';
import { Badge, ErrorBlock, LoadingBlock, PageHeader, ProgressBar, StatCard } from '../../components/UI';
import { compactMoney, formatDate, money, timeAgo, titleCase } from '../../lib/format';
import { invoiceStatus, paymentStatus, projectStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { AuditLog, Payment, Project } from '../../lib/types';

interface OverviewData {
  stats: {
    clients: number;
    new_requests: number;
    active_projects: number;
    completed_projects: number;
    pending_quotes: number;
    pending_payments: number;
    unread_enquiries: number;
    outstanding_value: number;
    outstanding_invoices: number;
    revenue: number;
    expenses: number;
  };
  recent_projects: Project[];
  pending_invoices: { id: number; invoice_no: string; status: string; total: number; amount_paid: number; due_date: string; full_name: string }[];
  recent_activity: AuditLog[];
  recent_payments: Payment[];
  monthly_revenue: { ym: string; total: number }[];
}

export function ConsoleOverview() {
  usePageMeta('Console overview — Reagansoft Innovation');
  const { user } = useAuth();
  const { data, loading, error, reload } = useApi<OverviewData>('admin/overview');

  if (loading) return <LoadingBlock label="Loading workspace…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load the workspace.'} onRetry={reload} />;

  const { stats, recent_projects, pending_invoices, recent_activity, recent_payments, monthly_revenue } = data;
  const maxRevenue = Math.max(1, ...monthly_revenue.map((row) => Number(row.total)));
  const profit = Number(stats.revenue) - Number(stats.expenses);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Console"
        title={`Good to see you, ${user?.full_name?.split(' ')[0] ?? 'team'}`}
        description="A live view of delivery, billing and the work that needs attention today."
        actions={
          <>
            <Link to="/console/projects" className="btn-ghost btn-sm"><PlusCircle className="h-3.5 w-3.5" /> New project</Link>
            <Link to="/console/invoices" className="btn-primary btn-sm"><ReceiptText className="h-3.5 w-3.5" /> Create invoice</Link>
          </>
        }
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Confirmed revenue" value={money(stats.revenue)} sub={`${money(stats.expenses)} expenses`} icon={<Banknote className="h-5 w-5" />} tone="green" />
        <StatCard label="Outstanding" value={money(stats.outstanding_value)} sub={`${stats.outstanding_invoices} open invoices`} icon={<ReceiptText className="h-5 w-5" />} tone="amber" />
        <StatCard label="Active projects" value={stats.active_projects} sub={`${stats.completed_projects} completed`} icon={<FolderKanban className="h-5 w-5" />} tone="blue" />
        <StatCard label="New requests" value={stats.new_requests} sub="Awaiting review" icon={<Inbox className="h-5 w-5" />} tone="violet" />
      </div>

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {[
          { label: 'Clients', value: stats.clients, icon: Users, to: '/console/clients' },
          { label: 'Pending quotes', value: stats.pending_quotes, icon: ClipboardList, to: '/console/quotations' },
          { label: 'Payments to confirm', value: stats.pending_payments, icon: CreditCard, to: '/console/payments' },
          { label: 'Unread enquiries', value: stats.unread_enquiries, icon: Inbox, to: '/console/enquiries' },
        ].map((item) => (
          <Link key={item.label} to={item.to} className="group flex items-center justify-between rounded-2xl border border-brand-100 bg-white p-4 transition hover:border-brand-200 hover:shadow-soft">
            <div className="flex items-center gap-3">
              <span className="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-600"><item.icon className="h-4 w-4" /></span>
              <div>
                <p className="text-[10px] font-bold uppercase tracking-[0.12em] text-ink-faint">{item.label}</p>
                <p className="font-display text-lg font-semibold text-navy-900">{item.value}</p>
              </div>
            </div>
            <ArrowUpRight className="h-4 w-4 text-ink-faint transition group-hover:text-brand-600" />
          </Link>
        ))}
      </div>

      <div className="grid gap-5 xl:grid-cols-[1.35fr_0.65fr]">
        <div className="space-y-5">
          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="font-display text-sm font-semibold text-navy-900">Confirmed revenue</h2>
                <p className="mt-0.5 text-[11px] text-ink-muted">Last 12 months · net position {money(profit)}</p>
              </div>
              <TrendingUp className="h-4 w-4 text-emerald-500" />
            </div>
            <div className="mt-5 flex h-40 items-end gap-1.5">
              {monthly_revenue.length === 0 ? (
                <p className="w-full text-center text-[11px] text-ink-faint">No confirmed payments yet.</p>
              ) : (
                monthly_revenue.map((row) => {
                  const height = Math.max(4, (Number(row.total) / maxRevenue) * 100);
                  return (
                    <div key={row.ym} className="group flex flex-1 flex-col items-center gap-1.5">
                      <span className="font-mono text-[8px] font-semibold text-brand-700 opacity-0 transition group-hover:opacity-100">{compactMoney(row.total)}</span>
                      <div className="w-full rounded-t-md bg-gradient-to-t from-brand-600 to-cyan-400 transition group-hover:from-brand-700" style={{ height: `${height}%` }} title={money(row.total)} />
                      <span className="font-mono text-[8px] text-ink-faint">{row.ym.slice(5)}</span>
                    </div>
                  );
                })
              )}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Recent projects</h2>
              <Link to="/console/projects" className="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700">All projects <ArrowRight className="h-3 w-3" /></Link>
            </div>
            <div className="mt-3 overflow-x-auto">
              <table className="data-table min-w-[560px]">
                <thead>
                  <tr><th>Project</th><th>Client</th><th>Status</th><th className="w-28">Progress</th><th>Updated</th></tr>
                </thead>
                <tbody>
                  {recent_projects.map((project) => {
                    const meta = projectStatus(project.status);
                    return (
                      <tr key={project.id}>
                        <td>
                          <Link to={`/console/projects/${project.id}`} className="block">
                            <span className="block text-[12px] font-semibold text-navy-900">{project.title}</span>
                            <span className="font-mono text-[9px] text-ink-faint">{project.ref_no}</span>
                          </Link>
                        </td>
                        <td className="text-[11px] text-ink-muted">{project.client_name}{project.company ? ` · ${project.company}` : ''}</td>
                        <td><Badge tone={meta.tone}>{meta.label}</Badge></td>
                        <td>
                          <div className="flex items-center gap-2">
                            <ProgressBar value={project.progress} className="w-16" />
                            <span className="font-mono text-[9px] text-brand-700">{project.progress}%</span>
                          </div>
                        </td>
                        <td className="text-[10.5px] text-ink-faint">{timeAgo(project.updated_at)}</td>
                      </tr>
                    );
                  })}
                  {recent_projects.length === 0 && <tr><td colSpan={5} className="py-6 text-center text-[11px] text-ink-faint">No projects yet.</td></tr>}
                </tbody>
              </table>
            </div>
          </section>
        </div>

        <div className="space-y-5">
          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Invoices needing attention</h2>
              <Link to="/console/invoices" className="text-[11px] font-semibold text-brand-700">View all</Link>
            </div>
            <div className="mt-3 space-y-2">
              {pending_invoices.length === 0 ? (
                <p className="rounded-xl bg-brand-50/50 px-3 py-4 text-center text-[11px] text-ink-faint">Nothing outstanding. Nice.</p>
              ) : (
                pending_invoices.map((invoice) => {
                  const meta = invoiceStatus(invoice.status);
                  return (
                    <Link key={invoice.id} to="/console/invoices" className="block rounded-xl border border-brand-100 px-3 py-2.5 transition hover:border-brand-200">
                      <div className="flex items-center justify-between gap-2">
                        <span className="font-mono text-[10.5px] font-semibold text-navy-900">{invoice.invoice_no}</span>
                        <Badge tone={meta.tone}>{meta.label}</Badge>
                      </div>
                      <p className="mt-0.5 text-[10.5px] text-ink-muted">{invoice.full_name} · due {formatDate(invoice.due_date)}</p>
                      <div className="mt-1 flex items-center justify-between text-[10px]">
                        <span className="text-ink-faint">{money(invoice.total)}</span>
                        <span className="font-semibold text-amber-700">{money(Number(invoice.total) - Number(invoice.amount_paid))} due</span>
                      </div>
                    </Link>
                  );
                })
              )}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Latest payments</h2>
            <div className="mt-3 space-y-2">
              {recent_payments.length === 0 ? (
                <p className="rounded-xl bg-brand-50/50 px-3 py-4 text-center text-[11px] text-ink-faint">No payments yet.</p>
              ) : (
                recent_payments.map((payment) => {
                  const meta = paymentStatus(payment.status);
                  return (
                    <div key={payment.id} className="flex items-center justify-between gap-2 rounded-xl border border-brand-100 px-3 py-2.5">
                      <div>
                        <p className="text-[11px] font-semibold text-navy-900">{money(payment.amount)}</p>
                        <p className="text-[9.5px] text-ink-faint">{payment.client_name} · {payment.invoice_no}</p>
                      </div>
                      <Badge tone={meta.tone}>{meta.label}</Badge>
                    </div>
                  );
                })
              )}
            </div>
          </section>

          <section className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Recent activity</h2>
            <div className="mt-3 space-y-2.5">
              {recent_activity.length === 0 ? (
                <p className="text-[11px] text-ink-faint">No activity recorded.</p>
              ) : (
                recent_activity.map((log) => (
                  <div key={log.id} className="flex gap-2.5">
                    <span className={cn('mt-0.5 grid h-6 w-6 shrink-0 place-items-center rounded-full', log.action.includes('payment') ? 'bg-emerald-50 text-emerald-600' : 'bg-brand-50 text-brand-600')}>
                      {log.action.includes('payment') ? <CheckCircle2 className="h-3 w-3" /> : <ClipboardList className="h-3 w-3" />}
                    </span>
                    <div className="min-w-0">
                      <p className="truncate text-[11px] font-medium text-navy-900">{titleCase(log.action)}</p>
                      <p className="text-[9.5px] text-ink-faint">{log.full_name ?? 'System'} · {timeAgo(log.created_at)}</p>
                    </div>
                  </div>
                ))
              )}
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}
