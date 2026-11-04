import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { ArrowRight, ArrowUpRight, FolderKanban, PlusCircle, Search } from 'lucide-react';
import { useApi, useDebounced } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { formatDate, timeAgo } from '../../lib/format';
import { projectStatus } from '../../lib/status';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, PageHeader, Pagination, ProgressBar } from '../../components/UI';
import type { Project } from '../../lib/types';

const statuses = ['', 'NEW', 'REVIEWING', 'QUOTATION', 'APPROVED', 'IN_PROGRESS', 'WAITING_FOR_CLIENT', 'TESTING', 'COMPLETED', 'CANCELLED'];

export function PortalProjects() {
  usePageMeta('My projects — Reagansoft client portal');
  const [params, setParams] = useSearchParams();
  const [query, setQuery] = useState(params.get('q') ?? '');
  const [status, setStatus] = useState(params.get('status') ?? '');
  const [page, setPage] = useState(1);
  const debounced = useDebounced(query);
  const { data, meta, loading, error, reload } = useApi<Project[]>('client/projects', {
    page,
    status: status || undefined,
    q: debounced || undefined,
  });

  useEffect(() => {
    setPage(1);
  }, [status, debounced]);

  useEffect(() => {
    const next = new URLSearchParams();
    if (debounced) next.set('q', debounced);
    if (status) next.set('status', status);
    setParams(next, { replace: true });
  }, [debounced, status, setParams]);

  const totalPages = Number(meta?.total_pages ?? 1);

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Projects"
        title="Your project history"
        description="Track every request from submission through to delivery. Select a project to see tasks, files, messages and invoices."
        actions={<Link to="/portal/request" className="btn-primary btn-sm"><PlusCircle className="h-3.5 w-3.5" /> New request</Link>}
      />

      <div className="flex flex-col gap-3 rounded-2xl border border-brand-100 bg-white p-3 sm:flex-row sm:items-center">
        <div className="flex flex-1 items-center gap-2 rounded-xl bg-brand-50/60 px-3 py-2.5">
          <Search className="h-4 w-4 text-ink-faint" />
          <input
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Search by title or reference…"
            className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint"
            aria-label="Search projects"
          />
        </div>
        <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-brand-100 bg-white px-3 py-2.5 text-sm text-ink-soft outline-none">
          {statuses.map((item) => (
            <option key={item || 'all'} value={item}>{item ? projectStatus(item).label : 'All statuses'}</option>
          ))}
        </select>
      </div>

      {loading ? (
        <LoadingBlock label="Loading projects…" />
      ) : error ? (
        <ErrorBlock message={error} onRetry={reload} />
      ) : !data || data.length === 0 ? (
        <EmptyState
          icon={<FolderKanban className="h-6 w-6" />}
          title="No projects found"
          message={query || status ? 'Try clearing the filters, or send a new request.' : 'Send your first request and we will start the conversation.'}
          action={<Link to="/portal/request" className="btn-primary btn-sm">Start a request <ArrowRight className="h-3.5 w-3.5" /></Link>}
        />
      ) : (
        <>
          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {data.map((project) => {
              const metaStatus = projectStatus(project.status);
              return (
                <Link key={project.id} to={`/portal/projects/${project.id}`} className="group flex h-full flex-col rounded-2xl border border-brand-100 bg-white p-5 transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-lift">
                  <div className="flex items-start justify-between gap-2">
                    <span className="font-mono text-[9px] uppercase tracking-widest text-ink-faint">{project.ref_no}</span>
                    <Badge tone={metaStatus.tone}>{metaStatus.label}</Badge>
                  </div>
                  <h2 className="mt-2 line-clamp-2 font-display text-[14.5px] font-semibold leading-snug text-navy-900 group-hover:text-brand-700">{project.title}</h2>
                  <p className="mt-1 text-[11px] text-ink-muted">{project.service ?? 'Custom project'}</p>
                  <p className="mt-2 line-clamp-2 flex-1 text-[11.5px] leading-relaxed text-ink-muted">{project.description}</p>
                  <div className="mt-4 flex items-center gap-2">
                    <ProgressBar value={project.progress} className="flex-1" />
                    <span className="font-mono text-[9.5px] font-semibold text-brand-700">{project.progress}%</span>
                  </div>
                  <div className="mt-3 flex items-center justify-between border-t border-brand-50 pt-3 text-[10px] text-ink-faint">
                    <span>Updated {timeAgo(project.updated_at)}</span>
                    <span className="inline-flex items-center gap-1 font-semibold text-brand-700">
                      Open <ArrowUpRight className="h-3 w-3" />
                    </span>
                  </div>
                  {project.deadline && <p className="mt-1 text-[10px] text-ink-faint">Target date {formatDate(project.deadline)}</p>}
                </Link>
              );
            })}
          </div>
          <Pagination page={page} totalPages={totalPages} onPage={setPage} />
        </>
      )}
    </div>
  );
}
