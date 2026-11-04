import { Link, useParams } from 'react-router-dom';
import { ArrowLeft, ArrowRight, BadgeCheck, Check, ChevronRight, Clock3, FileCheck2, LifeBuoy, ShieldCheck, Sparkles, Wallet } from 'lucide-react';
import { useApi } from '../lib/hooks';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { Badge, ErrorBlock, LoadingBlock, SectionHeading } from '../components/UI';
import { ServiceGlyph } from '../components/Graphics';
import { money } from '../lib/format';
import type { Service } from '../lib/types';

const included = [
  { icon: FileCheck2, title: 'Discovery & scope', text: 'A written brief, sitemap or feature list and an agreed acceptance checklist.' },
  { icon: Sparkles, title: 'Design direction', text: 'Wireframes and a polished visual system aligned to your brand.' },
  { icon: BadgeCheck, title: 'Build & QA', text: 'Development in visible increments with responsive and functional testing.' },
  { icon: ShieldCheck, title: 'Launch support', text: 'Deployment, data checks, training and a post-launch stabilisation window.' },
  { icon: LifeBuoy, title: 'Handover pack', text: 'Credentials, documentation and guidance for ongoing maintenance.' },
  { icon: Wallet, title: 'Clear payment plan', text: 'Deposit to start, milestone payments as work is accepted, final balance at handover.' },
];

export function ServiceDetail() {
  const { slug = '' } = useParams();
  const { services, settings } = useSite();
  const fallback = services.find((item) => item.slug === slug);
  const { data, loading, error, reload } = useApi<{ service: Service; related: Service[] }>(`public/services/${encodeURIComponent(slug)}`);

  const service = data?.service ?? fallback;
  const related = data?.related ?? services.filter((item) => item.slug !== slug).slice(0, 3);

  usePageMeta(
    service ? `${service.name} — Reagansoft Innovation` : 'Service — Reagansoft Innovation',
    service?.description,
  );

  if (!service) {
    if (loading) return <div className="container-x py-20"><LoadingBlock label="Loading service…" /></div>;
    return (
      <div className="container-x py-20">
        <ErrorBlock message={error || 'We could not find that service.'} onRetry={reload} />
        <div className="mt-6 text-center">
          <Link to="/services" className="btn-ghost btn-sm"><ArrowLeft className="h-3.5 w-3.5" /> All services</Link>
        </div>
      </div>
    );
  }

  return (
    <>
      <section className="relative overflow-hidden border-b border-brand-100 bg-brand-50/40 pb-14 pt-8">
        <div className="pointer-events-none absolute inset-0 bg-grid opacity-50" />
        <div className="container-x relative">
          <nav className="flex flex-wrap items-center gap-1.5 text-[11px] text-ink-muted" aria-label="Breadcrumb">
            <Link to="/" className="hover:text-brand-700">Home</Link>
            <ChevronRight className="h-3 w-3" />
            <Link to="/services" className="hover:text-brand-700">Services</Link>
            <ChevronRight className="h-3 w-3" />
            <span className="font-semibold text-navy-900">{service.name}</span>
          </nav>

          <div className="mt-8 grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
            <div>
              <span className="grid h-14 w-14 place-items-center rounded-2xl bg-white text-brand-600 shadow-soft ring-1 ring-brand-100">
                <ServiceGlyph name={service.icon} className="h-7 w-7" />
              </span>
              <h1 className="h1 mt-5 max-w-3xl text-navy-900">{service.name}</h1>
              <p className="mt-4 max-w-2xl text-[1.02rem] leading-relaxed text-ink-muted">{service.description}</p>
              <div className="mt-6 flex flex-wrap items-center gap-2">
                <Badge tone="blue">{service.price_short}</Badge>
                {service.delivery_days && <Badge tone="cyan"><Clock3 className="h-3 w-3" /> About {service.delivery_days} days</Badge>}
                <Badge tone="green"><ShieldCheck className="h-3 w-3" /> Fixed scope before work</Badge>
              </div>
            </div>

            <Reveal delay={100}>
              <div className="rounded-3xl border border-brand-100 bg-white p-6 shadow-soft">
                <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-faint">Investment</p>
                <p className="mt-2 font-display text-2xl font-semibold text-navy-900">{service.price_short}</p>
                {service.price_note && <p className="mt-1 text-[12px] text-ink-muted">{service.price_note}</p>}

                <div className="mt-4 space-y-2 rounded-2xl bg-brand-50/60 p-4 text-[12px] text-ink-soft">
                  <div className="flex items-center justify-between gap-3">
                    <span className="text-ink-muted">Pricing model</span>
                    <span className="font-semibold capitalize">{service.pricing_type.replace(/_/g, ' ')}</span>
                  </div>
                  {service.price_max && (
                    <div className="flex items-center justify-between gap-3">
                      <span className="text-ink-muted">Typical ceiling</span>
                      <span className="font-semibold">{money(service.price_max, service.currency)}</span>
                    </div>
                  )}
                  <div className="flex items-center justify-between gap-3">
                    <span className="text-ink-muted">Deposit</span>
                    <span className="font-semibold">{money(settings.project_deposit ?? 200000, settings.currency)}</span>
                  </div>
                </div>

                <Link to={`/contact?service=${encodeURIComponent(service.name)}`} className="btn-primary mt-5 w-full">
                  Request a quote <ArrowRight className="h-4 w-4" />
                </Link>
                <Link to="/pricing" className="btn-ghost mt-2 w-full">
                  How pricing works
                </Link>
                <p className="mt-4 text-center text-[10.5px] leading-relaxed text-ink-faint">
                  Every quote is itemised. No hidden fees, no surprise change requests.
                </p>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="container-x grid gap-12 lg:grid-cols-[1.15fr_0.85fr]">
          <div>
            <Reveal>
              <SectionHeading eyebrow="What's included" title="A complete delivery, not just a handoff." />
              <div className="mt-7 grid gap-3 sm:grid-cols-2">
                {included.map((item) => (
                  <div key={item.title} className="flex gap-3 rounded-2xl border border-brand-100 bg-white p-4">
                    <span className="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
                      <item.icon className="h-4 w-4" />
                    </span>
                    <div>
                      <p className="text-[12.5px] font-semibold text-navy-900">{item.title}</p>
                      <p className="mt-0.5 text-[11.5px] leading-relaxed text-ink-muted">{item.text}</p>
                    </div>
                  </div>
                ))}
              </div>
            </Reveal>

            {service.features.length > 0 && (
              <Reveal delay={80}>
                <div className="mt-10 rounded-3xl border border-brand-100 bg-white p-6">
                  <h2 className="font-display text-lg font-semibold text-navy-900">Capabilities in this service</h2>
                  <ul className="mt-4 grid gap-2.5 sm:grid-cols-2">
                    {service.features.map((feature) => (
                      <li key={feature} className="flex items-start gap-2 text-[13px] text-ink-soft">
                        <span className="mt-0.5 grid h-4 w-4 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                          <Check className="h-3 w-3" strokeWidth={3} />
                        </span>
                        {feature}
                      </li>
                    ))}
                  </ul>
                </div>
              </Reveal>
            )}

            <Reveal delay={120}>
              <div className="mt-10 rounded-3xl bg-navy-950 p-6 text-white">
                <h2 className="font-display text-lg font-semibold">How this engagement runs</h2>
                <div className="mt-5 grid gap-4 sm:grid-cols-4">
                  {[
                    ['01', 'Discover', 'Goals, users and constraints'],
                    ['02', 'Design', 'Scope and visual direction'],
                    ['03', 'Build', 'Weekly visible increments'],
                    ['04', 'Launch', 'Testing, training, support'],
                  ].map(([step, title, text]) => (
                    <div key={step}>
                      <p className="font-mono text-[10px] font-bold tracking-widest text-brand-300">{step}</p>
                      <p className="mt-1.5 text-[12.5px] font-semibold text-white">{title}</p>
                      <p className="mt-0.5 text-[10.5px] leading-relaxed text-white/45">{text}</p>
                    </div>
                  ))}
                </div>
                <Link to="/process" className="mt-6 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-300 hover:text-white">
                  Full process detail <ArrowRight className="h-3 w-3" />
                </Link>
              </div>
            </Reveal>
          </div>

          <aside className="space-y-4">
            <Reveal delay={60}>
              <div className="rounded-3xl border border-brand-100 bg-brand-50/50 p-6">
                <h3 className="font-display text-base font-semibold text-navy-900">Good fit if you need…</h3>
                <ul className="mt-3 space-y-2 text-[12.5px] text-ink-soft">
                  {[
                    'A dependable partner, not a one-off freelancer',
                    'Clear scope and a realistic delivery date',
                    'Documentation and training for your team',
                    'Support after launch, not just a handover',
                  ].map((item) => (
                    <li key={item} className="flex gap-2">
                      <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-600" /> {item}
                    </li>
                  ))}
                </ul>
              </div>
            </Reveal>

            <Reveal delay={120}>
              <div className="rounded-3xl border border-brand-100 bg-white p-6">
                <h3 className="font-display text-base font-semibold text-navy-900">Talk it through</h3>
                <p className="mt-2 text-[12.5px] leading-relaxed text-ink-muted">
                  A 30-minute call is usually enough to tell whether this is the right starting point.
                </p>
                <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="mt-4 block rounded-xl bg-brand-50 px-4 py-3 text-[12px] font-semibold text-brand-800">
                  {settings.company_phone}
                </a>
                <a href={`mailto:${settings.company_email}`} className="mt-2 block rounded-xl bg-brand-50 px-4 py-3 text-[12px] font-semibold text-brand-800">
                  {settings.company_email}
                </a>
              </div>
            </Reveal>
          </aside>
        </div>
      </section>

      {related.length > 0 && (
        <section className="border-t border-brand-100 bg-brand-50/40 py-16">
          <div className="container-x">
            <SectionHeading eyebrow="Related" title="Often paired with" />
            <div className="mt-8 grid gap-4 md:grid-cols-3">
              {related.map((item) => (
                <Link key={item.id} to={`/services/${item.slug}`} className="card card-hover group flex items-center gap-4 p-5">
                  <span className="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                    <ServiceGlyph name={item.icon} className="h-5 w-5" />
                  </span>
                  <div className="min-w-0">
                    <p className="truncate font-display text-[13.5px] font-semibold text-navy-900">{item.name}</p>
                    <p className="mt-0.5 truncate text-[11px] text-ink-muted">{item.price_short}</p>
                  </div>
                  <ArrowRight className="ml-auto h-4 w-4 shrink-0 text-ink-faint transition group-hover:text-brand-600" />
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      <section className="py-16">
        <div className="container-x">
          <div className="flex flex-col items-center gap-4 rounded-3xl bg-gradient-to-br from-brand-600 to-navy-900 px-6 py-10 text-center text-white sm:px-10">
            <h2 className="h2 max-w-2xl text-white">Ready to scope {service.name.toLowerCase()}?</h2>
            <p className="max-w-xl text-sm text-white/65">Send a short brief and we will reply with next steps, a timeline and an itemised quote.</p>
            <div className="flex flex-wrap justify-center gap-3">
              <Link to={`/contact?service=${encodeURIComponent(service.name)}`} className="btn bg-white text-navy-900 hover:bg-brand-50">
                Start the conversation <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/portfolio" className="btn border border-white/25 bg-white/5 text-white hover:bg-white/10">
                See related work
              </Link>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
