import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, ArrowUpRight, Check, Clock3, Search, Sparkles } from 'lucide-react';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/UI';
import { ServiceGlyph } from '../components/Graphics';
import { cn } from '../lib/cn';

export function Services() {
  usePageMeta(
    'Services — Websites, business systems, apps & security | Reagansoft Innovation',
    'Explore Reagansoft Innovation services: business websites, ecommerce, custom systems, mobile apps, billing automation, branding, maintenance and cybersecurity.',
  );

  const { services } = useSite();
  const [query, setQuery] = useState('');
  const [sort, setSort] = useState<'default' | 'low' | 'high'>('default');

  const filtered = useMemo(() => {
    const term = query.trim().toLowerCase();
    const rows = services.filter((service) =>
      !term || `${service.name} ${service.description} ${service.features.join(' ')}`.toLowerCase().includes(term),
    );
    if (sort === 'low') return [...rows].sort((a, b) => a.price_min - b.price_min);
    if (sort === 'high') return [...rows].sort((a, b) => b.price_min - a.price_min);
    return rows;
  }, [services, query, sort]);

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-20 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative">
          <span className="eyebrow text-brand-300">Services</span>
          <div className="mt-4 grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
            <div>
              <h1 className="h1 max-w-3xl text-white">Everything you need to run and grow digitally.</h1>
              <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
                Fourteen focused service lines, delivered by one accountable team. Start with a single website or bring us the whole
                operation — the standard does not change.
              </p>
            </div>
            <div className="rounded-2xl border border-white/12 bg-white/[0.05] p-5 backdrop-blur">
              <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-white/40">How pricing works</p>
              <p className="mt-2 text-[13px] leading-relaxed text-white/65">
                Ranges shown are real starting points. After a short discovery call we confirm scope, milestones and a fixed quote.
              </p>
              <Link to="/pricing" className="mt-4 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-300 hover:text-white">
                See pricing detail <ArrowUpRight className="h-3 w-3" />
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="py-12 sm:py-16">
        <div className="container-x">
          <div className="flex flex-col gap-3 rounded-2xl border border-brand-100 bg-white p-3 shadow-soft sm:flex-row sm:items-center">
            <div className="flex flex-1 items-center gap-2 rounded-xl bg-brand-50/60 px-3 py-2.5">
              <Search className="h-4 w-4 text-ink-faint" />
              <input
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                placeholder="Search services, e.g. ecommerce, school, branding…"
                className="w-full bg-transparent text-sm outline-none placeholder:text-ink-faint"
                aria-label="Search services"
              />
            </div>
            <div className="flex items-center gap-2">
              <span className="pl-2 text-[11px] font-semibold uppercase tracking-wider text-ink-faint">Sort</span>
              <select value={sort} onChange={(event) => setSort(event.target.value as typeof sort)} className="rounded-xl border border-brand-100 bg-white px-3 py-2.5 text-sm text-ink-soft outline-none">
                <option value="default">Recommended</option>
                <option value="low">Price: low to high</option>
                <option value="high">Price: high to low</option>
              </select>
            </div>
          </div>

          <div className="mt-6 flex items-center justify-between">
            <p className="text-[12px] text-ink-muted">
              Showing <span className="font-semibold text-navy-900">{filtered.length}</span> of {services.length} services
            </p>
            <span className="hidden items-center gap-1.5 text-[11px] text-ink-faint sm:inline-flex">
              <Sparkles className="h-3 w-3 text-brand-500" /> All prices in Ugandan Shillings
            </span>
          </div>

          <div className="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {filtered.map((service, index) => (
              <Reveal key={service.id} delay={Math.min(index, 6) * 50}>
                <div className="card card-hover group flex h-full flex-col p-6">
                  <div className="flex items-start justify-between gap-3">
                    <span className="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                      <ServiceGlyph name={service.icon} className="h-6 w-6" />
                    </span>
                    {service.delivery_days ? (
                      <span className="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-[10px] font-semibold text-brand-700">
                        <Clock3 className="h-3 w-3" /> {service.delivery_days} days
                      </span>
                    ) : (
                      <span className="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">Custom</span>
                    )}
                  </div>

                  <h2 className="mt-5 font-display text-lg font-semibold text-navy-900">{service.name}</h2>
                  <p className="mt-2 text-[13px] leading-relaxed text-ink-muted">{service.description}</p>

                  {service.features.length > 0 && (
                    <ul className="mt-4 space-y-1.5">
                      {service.features.slice(0, 3).map((feature) => (
                        <li key={feature} className="flex items-start gap-2 text-[12px] text-ink-soft">
                          <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" strokeWidth={2.5} />
                          {feature}
                        </li>
                      ))}
                    </ul>
                  )}

                  <div className="mt-auto flex items-end justify-between gap-3 border-t border-brand-50 pt-4">
                    <div>
                      <p className="font-mono text-[9px] uppercase tracking-wider text-ink-faint">From</p>
                      <p className="font-display text-[15px] font-semibold text-brand-700">{service.price_short}</p>
                    </div>
                    <Link to={`/services/${service.slug}`} className={cn('btn-ghost btn-sm', 'group-hover:border-brand-300')}>
                      Details <ArrowRight className="h-3.5 w-3.5" />
                    </Link>
                  </div>
                </div>
              </Reveal>
            ))}
          </div>

          {filtered.length === 0 && (
            <div className="mt-8 rounded-3xl border border-dashed border-brand-200 bg-brand-50/40 px-6 py-16 text-center">
              <p className="font-display text-lg font-semibold text-navy-900">No services match “{query}”.</p>
              <p className="mt-1 text-sm text-ink-muted">Try a broader term, or tell us what you need and we will scope it.</p>
              <div className="mt-5 flex justify-center gap-3">
                <button type="button" className="btn-ghost btn-sm" onClick={() => setQuery('')}>Clear search</button>
                <Link to="/contact" className="btn-primary btn-sm">Describe your project</Link>
              </div>
            </div>
          )}
        </div>
      </section>

      <section className="border-t border-brand-100 bg-brand-50/40 py-16">
        <div className="container-x grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
          <Reveal>
            <SectionHeading eyebrow="Not on the list?" title="Most of our best work started as a custom request." lead="If your problem does not fit a package, we will scope it from scratch — integrations, migrations, internal tools or something entirely new." />
            <Link to="/contact" className="btn-dark btn-sm mt-6">
              Describe the problem <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </Reveal>
          <Reveal delay={100}>
            <div className="grid gap-3 sm:grid-cols-2">
              {[
                ['Data migration', 'Move spreadsheets and legacy records into a clean, validated database.'],
                ['API integrations', 'Connect payments, SMS, accounting and third-party services.'],
                ['Training & handover', 'Practical sessions, written guides and recorded walkthroughs.'],
                ['Ongoing support', 'Monitoring, backups, fixes and a predictable monthly retainer.'],
              ].map(([title, text]) => (
                <div key={title} className="rounded-2xl border border-brand-100 bg-white p-4">
                  <p className="text-[12px] font-semibold text-navy-900">{title}</p>
                  <p className="mt-1 text-[11.5px] leading-relaxed text-ink-muted">{text}</p>
                </div>
              ))}
            </div>
          </Reveal>
        </div>
      </section>
    </>
  );
}
