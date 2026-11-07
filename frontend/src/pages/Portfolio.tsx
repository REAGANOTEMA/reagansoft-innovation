import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, ArrowUpRight, Search, SlidersHorizontal } from 'lucide-react';
import { useApi } from '../lib/hooks';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { Badge, SectionHeading } from '../components/UI';
import { ProjectCover } from '../components/Graphics';
import { cn } from '../lib/cn';
import { formatDate } from '../lib/format';
import type { Project } from '../lib/types';

interface PortfolioPayload {
  projects: Project[];
  categories: Array<{ slug: string; name: string }>;
}

export function Portfolio() {
  usePageMeta(
    'Our work — Case studies & delivered systems | Reagansoft Innovation',
    'Explore completed Reagansoft Innovation projects across education, hospitality, agriculture and commerce.',
  );

  const { portfolio, stats } = useSite();
  const { data } = useApi<PortfolioPayload>('public/portfolio');
  const projects = data?.projects?.length ? data.projects : portfolio;
  const [category, setCategory] = useState('all');
  const [query, setQuery] = useState('');

  const categories = useMemo(() => {
    const map = new Map<string, string>();
    projects.forEach((project) => {
      const key = project.service_slug ?? project.cover_art ?? 'other';
      const label = project.service ?? 'Other';
      if (!map.has(key)) map.set(key, label);
    });
    return [{ slug: 'all', name: 'All work' }, ...Array.from(map, ([slug, name]) => ({ slug, name }))];
  }, [projects]);

  const filtered = useMemo(() => {
    const term = query.trim().toLowerCase();
    return projects.filter((project) => {
      const key = project.service_slug ?? project.cover_art ?? 'other';
      const matchesCategory = category === 'all' || key === category;
      const matchesQuery = !term || `${project.title} ${project.description} ${project.service ?? ''}`.toLowerCase().includes(term);
      return matchesCategory && matchesQuery;
    });
  }, [projects, category, query]);

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-20 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
          <div>
            <span className="eyebrow text-brand-300">Portfolio</span>
            <h1 className="h1 mt-4 max-w-3xl text-white">Work that is live, used and maintained.</h1>
            <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
              Case studies from schools, hotels, farms and trading businesses. Each project below was scoped, designed, built and
              handed over by our team.
            </p>
          </div>
          <div className="grid grid-cols-3 gap-3">
            {[
              ['Projects', stats.projects],
              ['Completed', stats.completed],
              ['Clients', stats.clients],
            ].map(([label, value]) => (
              <div key={label} className="rounded-2xl border border-white/10 bg-white/[0.05] p-4 text-center backdrop-blur">
                <p className="font-display text-2xl font-semibold text-white">{value}</p>
                <p className="mt-1 font-mono text-[8px] uppercase tracking-[0.18em] text-white/40">{label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-12 sm:py-16">
        <div className="container-x">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div className="no-scrollbar flex gap-2 overflow-x-auto pb-1">
              {categories.map((item) => (
                <button
                  key={item.slug}
                  type="button"
                  onClick={() => setCategory(item.slug)}
                  className={cn(
                    'shrink-0 rounded-full border px-4 py-2 text-[12px] font-semibold transition',
                    category === item.slug
                      ? 'border-brand-600 bg-brand-600 text-white'
                      : 'border-brand-100 bg-white text-ink-soft hover:border-brand-300 hover:text-brand-700',
                  )}
                >
                  {item.name}
                </button>
              ))}
            </div>
            <div className="flex items-center gap-2 rounded-xl border border-brand-100 bg-white px-3 py-2.5 sm:w-72">
              <Search className="h-4 w-4 shrink-0 text-ink-faint" />
              <input
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                placeholder="Search work…"
                className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint"
                aria-label="Search portfolio"
              />
            </div>
          </div>

          <div className="mt-6 flex items-center justify-between text-[12px] text-ink-muted">
            <span>
              <span className="font-semibold text-navy-900">{filtered.length}</span> {filtered.length === 1 ? 'project' : 'projects'}
            </span>
            <span className="inline-flex items-center gap-1.5 text-ink-faint">
              <SlidersHorizontal className="h-3.5 w-3.5" /> Filter by service
            </span>
          </div>

          <div className="mt-6 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            {filtered.map((project, index) => (
              <Reveal key={project.id} delay={Math.min(index, 6) * 50}>
                <Link to={`/portfolio/${project.id}`} className="group block overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                  <ProjectCover cover={project.cover_art} className="aspect-[4/3] w-full" />
                  <div className="p-5">
                    <div className="flex flex-wrap items-center gap-2">
                      <Badge tone="blue">{project.service ?? 'Custom build'}</Badge>
                      {project.completed_at && <span className="text-[10px] text-ink-faint">{formatDate(project.completed_at)}</span>}
                    </div>
                    <h2 className="mt-3 line-clamp-2 font-display text-[15px] font-semibold leading-snug text-navy-900 transition group-hover:text-brand-700">
                      {project.title}
                    </h2>
                    <p className="mt-2 line-clamp-3 text-[12.5px] leading-relaxed text-ink-muted">{project.description}</p>
                    <div className="mt-4 flex items-center justify-between border-t border-brand-50 pt-3">
                      <span className="font-mono text-[9px] uppercase tracking-widest text-ink-faint">{project.ref_no}</span>
                      <span className="inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700">
                        Case study <ArrowUpRight className="h-3 w-3" />
                      </span>
                    </div>
                  </div>
                </Link>
              </Reveal>
            ))}
          </div>

          {filtered.length === 0 && (
            <div className="mt-8 rounded-3xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-16 text-center">
              <p className="font-display text-lg font-semibold text-navy-900">No work matches that filter yet.</p>
              <p className="mt-1 text-sm text-ink-muted">Try another category, or tell us what you are looking for.</p>
              <div className="mt-5 flex justify-center gap-3">
                <button type="button" className="btn-ghost btn-sm" onClick={() => { setCategory('all'); setQuery(''); }}>Reset filters</button>
                <Link to="/contact" className="btn-primary btn-sm">Talk to us</Link>
              </div>
            </div>
          )}
        </div>
      </section>

      <section className="border-t border-brand-100 bg-brand-50/40 py-16">
        <div className="container-x">
          <Reveal>
            <div className="flex flex-col items-start justify-between gap-6 rounded-3xl border border-brand-100 bg-white p-7 sm:flex-row sm:items-center">
              <div>
                <SectionHeading eyebrow="Your project next" title="Want a case study with your name on it?" lead="Tell us the outcome you need. We will map the shortest credible route to it." />
              </div>
              <Link to="/contact" className="btn-primary shrink-0">
                Start a project <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  );
}
