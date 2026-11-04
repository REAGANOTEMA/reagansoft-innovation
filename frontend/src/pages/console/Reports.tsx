import { ArrowDownRight, ArrowUpRight, Banknote, ChartColumn, ReceiptText, Wallet } from 'lucide-react';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { ErrorBlock, LoadingBlock, PageHeader, StatCard } from '../../components/UI';
import { compactMoney, money, titleCase } from '../../lib/format';
import { invoiceStatus } from '../../lib/status';
import { cn } from '../../lib/cn';

interface ReportData {
  revenue_by_month: { ym: string; total: number }[];
  expenses_by_month: { ym: string; total: number }[];
  projects_by_status: { status: string; c: number }[];
  top_clients: { full_name: string; company?: string | null; total: number }[];
  invoice_summary: { status: string; c: number; total: number; paid: number }[];
  expenses_by_category: { category: string; total: number }[];
  totals: { revenue: number; expenses: number; outstanding: number };
}

export function ConsoleReports() {
  usePageMeta('Reports — Reagansoft console');
  const { data, loading, error, reload } = useApi<ReportData>('admin/reports');

  if (loading) return <LoadingBlock label="Building reports…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load reports.'} onRetry={reload} />;

  const profit = Number(data.totals.revenue) - Number(data.totals.expenses);
  const months = Array.from(new Set([...data.revenue_by_month.map((row) => row.ym), ...data.expenses_by_month.map((row) => row.ym)])).sort();
  const maxMonth = Math.max(1, ...data.revenue_by_month.map((row) => Number(row.total)), ...data.expenses_by_month.map((row) => Number(row.total)));
  const projectTotal = Math.max(1, data.projects_by_status.reduce((sum, row) => sum + Number(row.c), 0));
  const maxCategory = Math.max(1, ...data.expenses_by_category.map((row) => Number(row.total)));

  function monthValue(rows: { ym: string; total: number }[], ym: string): number {
    return Number(rows.find((row) => row.ym === ym)?.total ?? 0);
  }

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Insight" title="Reports" description="Revenue, expenses, pipeline and client value at a glance." />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Revenue" value={money(data.totals.revenue)} icon={<Banknote className="h-5 w-5" />} tone="green" />
        <StatCard label="Expenses" value={money(data.totals.expenses)} icon={<Wallet className="h-5 w-5" />} tone="amber" />
        <StatCard label="Net position" value={money(profit)} sub={profit >= 0 ? 'In surplus' : 'In deficit'} icon={profit >= 0 ? <ArrowUpRight className="h-5 w-5" /> : <ArrowDownRight className="h-5 w-5" />} tone={profit >= 0 ? 'blue' : 'red'} />
        <StatCard label="Outstanding" value={money(data.totals.outstanding)} icon={<ReceiptText className="h-5 w-5" />} tone="violet" />
      </div>

      <section className="rounded-2xl border border-brand-100 bg-white p-5">
        <div className="flex items-center justify-between">
          <h2 className="font-display text-sm font-semibold text-navy-900">Revenue vs expenses</h2>
          <div className="flex items-center gap-3 text-[10px] text-ink-muted">
            <span className="inline-flex items-center gap-1"><span className="h-2 w-2 rounded-sm bg-brand-500" /> Revenue</span>
            <span className="inline-flex items-center gap-1"><span className="h-2 w-2 rounded-sm bg-amber-400" /> Expenses</span>
          </div>
        </div>
        <div className="mt-5 flex h-52 items-end gap-2">
          {months.length === 0 ? <p className="w-full text-center text-[11px] text-ink-faint">No financial activity yet.</p> : months.map((ym) => {
            const revenue = monthValue(data.revenue_by_month, ym);
            const expenses = monthValue(data.expenses_by_month, ym);
            return (
              <div key={ym} className="flex flex-1 flex-col items-center gap-1.5">
                <div className="flex h-40 w-full items-end justify-center gap-1">
                  <div className="group relative w-1/2 rounded-t bg-brand-500/90" style={{ height: `${Math.max(2, (revenue / maxMonth) * 100)}%` }} title={money(revenue)} />
                  <div className="w-1/2 rounded-t bg-amber-400/90" style={{ height: `${Math.max(2, (expenses / maxMonth) * 100)}%` }} title={money(expenses)} />
                </div>
                <span className="font-mono text-[8px] text-ink-faint">{ym.slice(2)}</span>
              </div>
            );
          })}
        </div>
      </section>

      <div className="grid gap-5 lg:grid-cols-2">
        <section className="rounded-2xl border border-brand-100 bg-white p-5">
          <div className="flex items-center gap-2"><ChartColumn className="h-4 w-4 text-brand-600" /><h2 className="font-display text-sm font-semibold text-navy-900">Projects by status</h2></div>
          <div className="mt-4 space-y-2.5">
            {data.projects_by_status.length === 0 ? <p className="text-[11px] text-ink-faint">No projects yet.</p> : data.projects_by_status.map((row) => (
              <div key={row.status}>
                <div className="flex items-center justify-between text-[11px]"><span className="font-medium text-navy-900">{titleCase(row.status)}</span><span className="font-mono text-ink-muted">{row.c}</span></div>
                <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-brand-50"><div className="h-full rounded-full bg-brand-500" style={{ width: `${(Number(row.c) / projectTotal) * 100}%` }} /></div>
              </div>
            ))}
          </div>
        </section>

        <section className="rounded-2xl border border-brand-100 bg-white p-5">
          <h2 className="font-display text-sm font-semibold text-navy-900">Expenses by category</h2>
          <div className="mt-4 space-y-2.5">
            {data.expenses_by_category.length === 0 ? <p className="text-[11px] text-ink-faint">No expenses recorded.</p> : data.expenses_by_category.map((row) => (
              <div key={row.category}>
                <div className="flex items-center justify-between text-[11px]"><span className="font-medium text-navy-900">{row.category}</span><span className="font-mono text-ink-muted">{money(row.total)}</span></div>
                <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-amber-50"><div className="h-full rounded-full bg-amber-400" style={{ width: `${(Number(row.total) / maxCategory) * 100}%` }} /></div>
              </div>
            ))}
          </div>
        </section>
      </div>

      <div className="grid gap-5 lg:grid-cols-2">
        <section className="rounded-2xl border border-brand-100 bg-white p-5">
          <h2 className="font-display text-sm font-semibold text-navy-900">Top clients by confirmed revenue</h2>
          <div className="mt-3 space-y-2">
            {data.top_clients.length === 0 ? <p className="text-[11px] text-ink-faint">No confirmed payments yet.</p> : data.top_clients.map((client, index) => (
              <div key={`${client.full_name}-${index}`} className="flex items-center justify-between gap-3 rounded-xl border border-brand-100 px-3 py-2.5">
                <div className="flex items-center gap-2.5">
                  <span className="grid h-6 w-6 place-items-center rounded-full bg-brand-50 font-mono text-[9px] font-bold text-brand-700">{index + 1}</span>
                  <div>
                    <p className="text-[11.5px] font-semibold text-navy-900">{client.full_name}</p>
                    {client.company && <p className="text-[9.5px] text-ink-faint">{client.company}</p>}
                  </div>
                </div>
                <span className="font-mono text-[11px] font-semibold text-emerald-700">{money(client.total)}</span>
              </div>
            ))}
          </div>
        </section>

        <section className="rounded-2xl border border-brand-100 bg-white p-5">
          <h2 className="font-display text-sm font-semibold text-navy-900">Invoice summary</h2>
          <div className="mt-3 overflow-x-auto">
            <table className="data-table min-w-[380px]">
              <thead><tr><th>Status</th><th className="text-right">Count</th><th className="text-right">Billed</th><th className="text-right">Paid</th></tr></thead>
              <tbody>
                {data.invoice_summary.length === 0 ? <tr><td colSpan={4} className="py-5 text-center text-[11px] text-ink-faint">No invoices yet.</td></tr> : data.invoice_summary.map((row) => (
                  <tr key={row.status}>
                    <td><span className={cn('badge', invoiceStatus(row.status).tone === 'green' ? 'bg-emerald-50 text-emerald-700' : 'bg-brand-50 text-brand-700')}>{invoiceStatus(row.status).label}</span></td>
                    <td className="text-right font-mono text-[11px]">{row.c}</td>
                    <td className="text-right font-mono text-[11px]">{money(row.total)}</td>
                    <td className="text-right font-mono text-[11px] text-emerald-700">{money(row.paid)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <p className="mt-3 text-[10.5px] text-ink-faint">Monthly figures use confirmed payments and recorded expense dates. Compact view: {compactMoney(data.totals.revenue)} revenue.</p>
        </section>
      </div>
    </div>
  );
}
