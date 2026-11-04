import { useEffect, useState } from 'react';
import { Search, ShieldCheck } from 'lucide-react';
import { useApi, useDebounced } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { EmptyState, ErrorBlock, LoadingBlock, PageHeader, Pagination } from '../../components/UI';
import { formatDate, titleCase } from '../../lib/format';
import type { AuditLog } from '../../lib/types';

export function ConsoleAudit() {
  usePageMeta('Audit log — Reagansoft console');
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const q = useDebounced(search);
  const { data, meta, loading, error, reload } = useApi<AuditLog[]>('admin/audit', { page, q: q || undefined });

  useEffect(() => {
    setPage(1);
  }, [q]);

  const totalPages = Number(meta?.total_pages ?? 1);

  return (
    <div className="space-y-6">
      <PageHeader eyebrow="Security" title="Audit log" description="Every significant action across the platform, newest first." />

      <label className="relative block max-w-md">
        <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-faint" />
        <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search action, details or user…" className="input pl-9" />
      </label>

      {loading ? (
        <LoadingBlock label="Loading audit trail…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState icon={<ShieldCheck className="h-6 w-6" />} title="No entries" message="Actions will appear here as the team works." />
      ) : (
        <>
          <div className="table-shell overflow-x-auto">
            <table className="data-table min-w-[780px]">
              <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th><th>Entity</th><th>IP</th></tr></thead>
              <tbody>
                {data.map((row) => (
                  <tr key={row.id}>
                    <td className="whitespace-nowrap text-[10.5px] text-ink-faint">{formatDate(row.created_at, true)}</td>
                    <td className="text-[11px] font-medium text-navy-900">{row.full_name ?? 'System'}</td>
                    <td><span className="rounded-full bg-brand-50 px-2 py-0.5 font-mono text-[9.5px] font-semibold text-brand-700">{titleCase(row.action.replace(/_/g, ' '))}</span></td>
                    <td className="text-[11px] text-ink-muted">{row.details ?? '—'}</td>
                    <td className="font-mono text-[10px] text-ink-faint">{row.entity_type ? `${row.entity_type}${row.entity_id ? ` #${row.entity_id}` : ''}` : '—'}</td>
                    <td className="font-mono text-[10px] text-ink-faint">{row.ip_address ?? '—'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}
    </div>
  );
}
