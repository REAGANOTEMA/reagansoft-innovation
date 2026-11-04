import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, ArrowRight, ArrowUpRight, CalendarDays, Check, ChevronRight, ExternalLink, Layers3, UserRound } from 'lucide-react';
import { useApi } from '../lib/hooks';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { Badge, ErrorBlock, LoadingBlock } from '../components/UI';
import { ProjectCover } from '../components/Graphics';
import { formatDate } from '../lib/format';
import type { Project } from '../lib/types';

export function ProjectDetail() {
  const { id = '' } = useParams();
  const { portfolio } = useSite();
  const fallback = portfolio.find((item) => String(item.id) === id);
  const { data, loading, error, reload } = useApi<Project>(`public/portfolio/${encodeURIComponent(id)}`);
  const project = data ?? fallback;

  usePageMeta(
    project ? `${project.title} — Case study | Reagansoft Innovation` : 'Case study — Reagansoft Innovation',
    project?.description,
  );

  if (!project) {
    if (loading) return <div className="container-x py-20"><LoadingBlock label="Loading case study…" /></div>;
    return (
      <div className="container-x py-20">
        <ErrorBlock message={error || 'That case study could not be found.'} onRetry={reload} />
        <div className="mt-6 text-center">
          <Link to="/portfolio" className="btn-ghost btn-sm"><ArrowLeft className="h-3.5 w-3.5" /> Back to portfolio</Link>
        </div>
      </div>
    );
  }

  const more = portfolio.filter((item) => item.id !== project.id).slice(0, 3);

  return (
    <>
      <section className="border-b border-brand-100 bg-brand-50/40 pb-10 pt-8">
        <div className="container-x">
          <nav className="flex flex-wrap items-center gap-1.5 text-[11px] text-ink-muted" aria-label="Breadcrumb">
            <Link to="/" className="hover:text-brand-700">Home</Link>
            <ChevronRight className="h-3 w-3" />
            <Link to="/portfolio" className="hover:text-brand-700">Work</Link>
            <ChevronRight className="h-3 w-3" />
            <span className="line-clamp-1 font-semibold text-navy-900">{project.title}</span>
          </nav>

          <div className="mt-7 grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
            <div>
              <div className="flex flex-wrap items-center gap-2">
                <Badge tone="blue">{project.service ?? 'Custom build'}</Badge>
                <Badge tone="green">Completed</Badge>
                <span className="font-mono text-[10px] uppercase tracking-widest text-ink-faint">{project.ref_no}</span>
              </div>
              <h1 className="h1 mt-4 max-w-3xl text-navy-900">{project.title}</h1>
              <p className="mt-4 max-w-2xl text-[1.02rem] leading-relaxed text-ink-muted">{project.description}</p>

              <dl className="mt-7 grid grid-cols-2 gap-4 border-t border-brand-100 pt-5 sm:grid-cols-3">
                <div>
                  <dt className="font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">Service</dt>
                  <dd className="mt-1 text-[13px] font-semibold text-navy-900">{project.service ?? 'Custom'}</dd>
                </div>
                <div>
                  <dt className="font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">Completed</dt>
                  <dd className="mt-1 text-[13px] font-semibold text-navy-900">{formatDate(project.completed_at)}</dd>
                </div>
                <div>
                  <dt className="font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">Client</dt>
                  <dd className="mt-1 inline-flex items-center gap-1.5 text-[13px] font-semibold text-navy-900">
                    <UserRound className="h-3.5 w-3.5 text-brand-600" /> Private client
                  </dd>
                </div>
              </dl>

              <div className="mt-6 flex flex-wrap gap-3">
                {project.website_url && (
                  <a href={project.website_url} target="_blank" rel="noreferrer" className="btn-primary btn-sm">
                    Visit live site <ExternalLink className="h-3.5 w-3.5" />
                  </a>
                )}
                <Link to="/contact" className="btn-ghost btn-sm">
                  Request something similar <ArrowRight className="h-3.5 w-3.5" />
                </Link>
              </div>
            </div>

            <Reveal delay={100}>
              <ProjectCover cover={project.cover_art} className="aspect-[4/3] w-full shadow-lift" />
            </Reveal>
          </div>
        </div>
      </section>

      <section className="py-16">
        <div className="container-x grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
          <div className="space-y-6">
            <Reveal>
              <div className="rounded-3xl border border-brand-100 bg-white p-6">
                <h2 className="font-display text-lg font-semibold text-navy-900">The brief</h2>
                <p className="mt-3 whitespace-pre-line text-[13.5px] leading-relaxed text-ink-muted">
                  {project.requirements || project.description}
                </p>
              </div>
            </Reveal>

            <Reveal delay={60}>
              <div className="rounded-3xl border border-brand-100 bg-white p-6">
                <h2 className="font-display text-lg font-semibold text-navy-900">What we delivered</h2>
                <ul className="mt-4 grid gap-2.5 sm:grid-cols-2">
                  {(project.deliverables
                    ? project.deliverables.split(/[,;\n]+/).map((item) => item.trim()).filter(Boolean)
                    : ['Discovery and scope', 'Interface design', 'Development and testing', 'Deployment and training']
                  ).map((item) => (
                    <li key={item} className="flex items-start gap-2 text-[13px] text-ink-soft">
                      <span className="mt-0.5 grid h-4.5 w-4.5 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                        <Check className="h-3 w-3" strokeWidth={3} />
                      </span>
                      {item}
                    </li>
                  ))}
                </ul>
              </div>
            </Reveal>

            <Reveal delay={120}>
              <div className="rounded-3xl bg-navy-950 p-6 text-white">
                <h2 className="font-display text-lg font-semibold">Outcome</h2>
                <p className="mt-2 text-[13px] leading-relaxed text-white/60">
                  The platform is in active use by the client team, with structured data, role-based access and a documented support
                  path. It is maintained by Reagansoft under an ongoing agreement.
                </p>
                <div className="mt-5 grid grid-cols-3 gap-3">
                  {[
                    ['Status', 'Live'],
                    ['Progress', `${project.progress ?? 100}%`],
                    ['Support', 'Ongoing'],
                  ].map(([label, value]) => (
                    <div key={label} className="rounded-xl bg-white/[0.06] p-3">
                      <p className="font-mono text-[8px] uppercase tracking-widest text-white/35">{label}</p>
                      <p className="mt-1 text-[12px] font-semibold text-white">{value}</p>
                    </div>
                  ))}
                </div>
              </div>
            </Reveal>
          </div>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-brand-100 bg-brand-50/50 p-6">
              <h3 className="font-display text-base font-semibold text-navy-900">Project snapshot</h3>
              <ul className="mt-4 space-y-3 text-[12.5px]">
                <li className="flex items-center justify-between gap-3">
                  <span className="inline-flex items-center gap-2 text-ink-muted"><Layers3 className="h-3.5 w-3.5" /> Service</span>
                  <span className="font-semibold text-navy-900">{project.service ?? 'Custom'}</span>
                </li>
                <li className="flex items-center justify-between gap-3">
                  <span className="inline-flex items-center gap-2 text-ink-muted"><CalendarDays className="h-3.5 w-3.5" /> Completed</span>
                  <span className="font-semibold text-navy-900">{formatDate(project.completed_at)}</span>
                </li>
                <li className="flex items-center justify-between gap-3">
                  <span className="inline-flex items-center gap-2 text-ink-muted"><Check className="h-3.5 w-3.5" /> Progress</span>
                  <span className="font-semibold text-emerald-600">{project.progress ?? 100}%</span>
                </li>
              </ul>
              {project.website_url && (
                <a href={project.website_url} target="_blank" rel="noreferrer" className="btn-ghost btn-sm mt-5 w-full">
                  Open website <ArrowUpRight className="h-3.5 w-3.5" />
                </a>
              )}
            </div>

            <div className="rounded-3xl border border-brand-100 bg-white p-6">
              <h3 className="font-display text-base font-semibold text-navy-900">Have a similar brief?</h3>
              <p className="mt-2 text-[12.5px] leading-relaxed text-ink-muted">Send it over. We will reply with a scoped plan and a fixed quote.</p>
              <Link to="/contact" className="btn-primary btn-sm mt-4 w-full">
                Talk to us <ArrowRight className="h-3.5 w-3.5" />
              </Link>
            </div>
          </aside>
        </div>
      </section>

      {more.length > 0 && (
        <section className="border-t border-brand-100 bg-brand-50/40 py-16">
          <div className="container-x">
            <div className="flex items-end justify-between gap-4">
              <h2 className="font-display text-xl font-semibold text-navy-900">More work</h2>
              <Link to="/portfolio" className="text-[12px] font-semibold text-brand-700 hover:text-brand-800">All projects →</Link>
            </div>
            <div className="mt-6 grid gap-5 md:grid-cols-3">
              {more.map((item) => (
                <Link key={item.id} to={`/portfolio/${item.id}`} className="group overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-soft transition hover:-translate-y-1 hover:shadow-lift">
                  <ProjectCover cover={item.cover_art} className="aspect-[16/10] w-full" />
                  <div className="p-4">
                    <p className="text-[10px] font-semibold uppercase tracking-wider text-brand-600">{item.service ?? 'Custom'}</p>
                    <p className="mt-1 line-clamp-2 font-display text-[13.5px] font-semibold text-navy-900">{item.title}</p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
}
