import { Link } from 'react-router-dom';
import {
  ArrowRight,
  ArrowUpRight,
  BadgeCheck,
  Check,
  CircleDollarSign,
  Clock3,
  Compass,
  HeartHandshake,
  Layers3,
  LifeBuoy,
  MapPin,
  Quote,
  Rocket,
  ShieldCheck,
  Sparkles,
  Star,
  Workflow,
} from 'lucide-react';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { HeroVisual, ProjectCover, ServiceGlyph, IndustryGlyph } from '../components/Graphics';
import { Badge, SectionHeading } from '../components/UI';
import { money } from '../lib/format';

const industries = [
  { key: 'education', label: 'Education' },
  { key: 'hospitality', label: 'Hospitality' },
  { key: 'agriculture', label: 'Agriculture' },
  { key: 'commerce', label: 'Commerce' },
  { key: 'health', label: 'Health' },
  { key: 'logistics', label: 'Logistics' },
  { key: 'government', label: 'Public sector' },
  { key: 'ngos', label: 'NGOs' },
];

const values = [
  {
    icon: Compass,
    title: 'Built around your operation',
    text: 'We start with how your organisation actually works — then design the system, not the other way round.',
  },
  {
    icon: Workflow,
    title: 'One team, end to end',
    text: 'Strategy, design, engineering, deployment and training are handled by the same people who answer your calls.',
  },
  {
    icon: ShieldCheck,
    title: 'Secure by default',
    text: 'Role-based access, audited actions, encrypted sessions and disciplined release checks on every build.',
  },
  {
    icon: LifeBuoy,
    title: 'We stay after launch',
    text: 'Monitoring, maintenance and improvements are part of the relationship — not an afterthought.',
  },
];

const process = [
  { step: '01', title: 'Discover', text: 'A structured conversation about goals, users, constraints and budget.' },
  { step: '02', title: 'Design', text: 'Architecture, wireframes and a visual direction you can react to early.' },
  { step: '03', title: 'Build', text: 'Weekly increments, demo links and a shared board so progress is visible.' },
  { step: '04', title: 'Launch', text: 'Testing, data migration, training and a calm, supervised go-live.' },
  { step: '05', title: 'Evolve', text: 'Support, monitoring and roadmap work that keeps the product relevant.' },
];

const stack = ['React', 'TypeScript', 'PHP 8', 'MySQL', 'Tailwind CSS', 'Flutter', 'Node.js', 'REST APIs', 'Linux', 'Payments', 'Cloud', 'Analytics'];

export function Home() {
  usePageMeta(
    'Reagansoft Innovation Limited — Software studio in Jinja, Uganda',
    'Reagansoft Innovation designs and builds websites, business systems, ecommerce, mobile apps and custom software for organisations across Uganda.',
  );

  const { settings, services, portfolio, stats } = useSite();
  const featured = services.slice(0, 6);
  const work = portfolio.slice(0, 3);

  return (
    <>
      {/* Hero */}
      <section className="relative overflow-hidden bg-navy-950 pb-24 pt-16 text-white sm:pt-20 lg:pb-32 lg:pt-24">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-40" />
        <div className="pointer-events-none absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-navy-950 to-transparent" />

        <div className="container-x relative grid items-center gap-16 lg:grid-cols-[1.05fr_0.95fr]">
          <div>
            <Reveal>
              <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-3.5 py-1.5 text-[11px] font-medium text-white/70 backdrop-blur">
                <span className="relative flex h-2 w-2">
                  <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60" />
                  <span className="relative inline-flex h-2 w-2 rounded-full bg-emerald-400" />
                </span>
                Software studio · {settings.company_address}
              </span>
            </Reveal>

            <Reveal delay={80}>
              <h1 className="h1 mt-6 max-w-2xl text-white">
                Software that moves
                <br />
                your business <span className="bg-gradient-to-r from-brand-300 via-cyan-300 to-brand-400 bg-clip-text text-transparent">forward.</span>
              </h1>
            </Reveal>

            <Reveal delay={140}>
              <p className="mt-6 max-w-xl text-[1.05rem] leading-relaxed text-white/65">
                We design and build websites, business systems, ecommerce stores and mobile apps for organisations across Uganda —
                then stay around to keep them fast, secure and useful.
              </p>
            </Reveal>

            <Reveal delay={200}>
              <div className="mt-8 flex flex-wrap items-center gap-3">
                <Link to="/contact" className="btn-primary">
                  Start a project <ArrowRight className="h-4 w-4" />
                </Link>
                <Link to="/portfolio" className="btn inline-flex border border-white/20 bg-white/5 text-white backdrop-blur hover:bg-white/10">
                  Explore our work <ArrowUpRight className="h-4 w-4" />
                </Link>
              </div>
            </Reveal>

            <Reveal delay={260}>
              <dl className="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-white/10 pt-6">
                {[
                  { label: 'Projects delivered', value: `${stats.projects || 7}+` },
                  { label: 'Organisations served', value: `${stats.clients || 6}+` },
                  { label: 'Service lines', value: `${stats.services || 14}` },
                ].map((item) => (
                  <div key={item.label}>
                    <dt className="font-mono text-[9px] uppercase tracking-[0.18em] text-white/35">{item.label}</dt>
                    <dd className="mt-1 font-display text-2xl font-semibold text-white">{item.value}</dd>
                  </div>
                ))}
              </dl>
            </Reveal>
          </div>

          <Reveal delay={200} className="relative">
            <HeroVisual />
          </Reveal>
        </div>

        <div className="container-x relative mt-16 lg:mt-24">
          <div className="flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] px-5 py-4 backdrop-blur">
            <span className="font-mono text-[9px] uppercase tracking-[0.2em] text-white/35">Sectors</span>
            <div className="flex flex-1 flex-wrap items-center gap-x-6 gap-y-2">
              {industries.map((industry) => (
                <span key={industry.key} className="inline-flex items-center gap-2 text-[12px] font-medium text-white/55">
                  <IndustryGlyph kind={industry.key} /> {industry.label}
                </span>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Services */}
      <section className="relative py-20 sm:py-24">
        <div className="container-x">
          <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <Reveal>
              <SectionHeading
                eyebrow="What we do"
                title={
                  <>
                    Practical technology,
                    <br className="hidden sm:block" /> built for real operations.
                  </>
                }
                lead="From a first website to a full management platform, every engagement is scoped, designed and delivered with the same discipline."
              />
            </Reveal>
            <Reveal delay={100}>
              <Link to="/services" className="btn-ghost btn-sm">
                All {services.length || 14} services <ArrowRight className="h-3.5 w-3.5" />
              </Link>
            </Reveal>
          </div>

          <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {(featured.length ? featured : []).map((service, index) => (
              <Reveal key={service.id} delay={index * 60}>
                <Link to={`/services/${service.slug}`} className="card card-hover group flex h-full flex-col p-6">
                  <div className="flex items-start justify-between">
                    <span className="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition duration-300 group-hover:bg-brand-600 group-hover:text-white">
                      <ServiceGlyph name={service.icon} className="h-6 w-6" />
                    </span>
                    <ArrowUpRight className="h-4 w-4 text-ink-faint transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 group-hover:text-brand-600" />
                  </div>
                  <h3 className="mt-5 font-display text-lg font-semibold leading-snug text-navy-900">{service.name}</h3>
                  <p className="mt-2 flex-1 text-sm leading-relaxed text-ink-muted">{service.description}</p>
                  <div className="mt-5 flex items-center justify-between border-t border-brand-50 pt-4">
                    <span className="font-mono text-[11px] font-semibold text-brand-700">{service.price_short}</span>
                    {service.delivery_days ? (
                      <span className="inline-flex items-center gap-1 text-[11px] text-ink-faint">
                        <Clock3 className="h-3 w-3" /> {service.delivery_days} days
                      </span>
                    ) : (
                      <span className="text-[11px] text-ink-faint">Scoped per project</span>
                    )}
                  </div>
                </Link>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* Why us */}
      <section className="relative overflow-hidden bg-brand-50/50 py-20 sm:py-24">
        <div className="pointer-events-none absolute -right-24 top-10 h-72 w-72 rounded-full bg-brand-200/40 blur-3xl" />
        <div className="container-x relative grid gap-14 lg:grid-cols-[0.9fr_1.1fr]">
          <Reveal>
            <SectionHeading
              eyebrow="Why Reagansoft"
              title="A partner who understands the whole picture."
              lead="We are a Ugandan studio with an engineering culture. That means honest scoping, clear communication and software that is documented, tested and easy to hand over."
            />
            <div className="mt-8 space-y-3">
              {[
                'Fixed scope, transparent milestones, no surprise invoices',
                'You own your source code, data and hosting accounts',
                'Training and documentation included with every delivery',
              ].map((item) => (
                <div key={item} className="flex items-start gap-3 text-sm text-ink-soft">
                  <span className="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                    <Check className="h-3 w-3" strokeWidth={3} />
                  </span>
                  {item}
                </div>
              ))}
            </div>
            <Link to="/about" className="btn-dark btn-sm mt-8">
              More about the studio <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </Reveal>

          <div className="grid gap-4 sm:grid-cols-2">
            {values.map((value, index) => (
              <Reveal key={value.title} delay={index * 70}>
                <div className="card h-full p-6">
                  <span className="grid h-11 w-11 place-items-center rounded-2xl bg-navy-900 text-brand-300">
                    <value.icon className="h-5 w-5" />
                  </span>
                  <h3 className="mt-4 font-display text-[15px] font-semibold text-navy-900">{value.title}</h3>
                  <p className="mt-2 text-[13px] leading-relaxed text-ink-muted">{value.text}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* Work */}
      <section className="py-20 sm:py-24">
        <div className="container-x">
          <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <Reveal>
              <SectionHeading
                eyebrow="Selected work"
                title="Systems in production, not just concepts."
                lead="A snapshot of platforms we have designed, built and handed over to teams who use them every day."
              />
            </Reveal>
            <Reveal delay={100}>
              <Link to="/portfolio" className="btn-ghost btn-sm">
                View portfolio <ArrowRight className="h-3.5 w-3.5" />
              </Link>
            </Reveal>
          </div>

          <div className="mt-12 grid gap-5 md:grid-cols-3">
            {(work.length ? work : portfolio.slice(0, 3)).map((project, index) => (
              <Reveal key={project.id} delay={index * 80}>
                <Link to={`/portfolio/${project.id}`} className="group block overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
                  <ProjectCover cover={project.cover_art} className="aspect-[4/3] w-full" />
                  <div className="p-5">
                    <div className="flex items-center gap-2">
                      <Badge tone="blue">{project.service ?? 'Custom build'}</Badge>
                      <span className="font-mono text-[9px] uppercase tracking-widest text-ink-faint">{project.ref_no}</span>
                    </div>
                    <h3 className="mt-3 line-clamp-2 font-display text-[15px] font-semibold leading-snug text-navy-900 transition group-hover:text-brand-700">
                      {project.title}
                    </h3>
                    <p className="mt-2 line-clamp-2 text-[12.5px] leading-relaxed text-ink-muted">{project.description}</p>
                    <span className="mt-4 inline-flex items-center gap-1 text-[11px] font-semibold text-brand-700">
                      Read case study <ArrowUpRight className="h-3 w-3" />
                    </span>
                  </div>
                </Link>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* Process */}
      <section className="relative overflow-hidden bg-navy-950 py-20 text-white sm:py-24">
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
        <div className="pointer-events-none absolute left-1/3 top-0 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl" />
        <div className="container-x relative">
          <Reveal>
            <SectionHeading
              eyebrow="How we work"
              title="A calm, visible path from idea to launch."
              lead="No black boxes. You see the plan, the progress and the decisions at every stage."
              dark
            />
          </Reveal>

          <div className="mt-14 grid gap-4 md:grid-cols-5">
            {process.map((item, index) => (
              <Reveal key={item.step} delay={index * 70}>
                <div className="group relative h-full rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition hover:border-brand-400/40 hover:bg-white/[0.07]">
                  <div className="flex items-center justify-between">
                    <span className="font-mono text-[11px] font-semibold tracking-widest text-brand-300">{item.step}</span>
                    <span className="h-1.5 w-1.5 rounded-full bg-brand-400/50 transition group-hover:bg-brand-300" />
                  </div>
                  <h3 className="mt-4 font-display text-base font-semibold text-white">{item.title}</h3>
                  <p className="mt-2 text-[12px] leading-relaxed text-white/50">{item.text}</p>
                </div>
              </Reveal>
            ))}
          </div>

          <Reveal delay={200}>
            <div className="mt-10 flex flex-wrap items-center gap-3">
              <Link to="/process" className="btn-primary btn-sm">
                See the full process <ArrowRight className="h-3.5 w-3.5" />
              </Link>
              <span className="text-[12px] text-white/40">Typical first release: 3–10 weeks depending on scope.</span>
            </div>
          </Reveal>
        </div>
      </section>

      {/* Commitments + stack */}
      <section className="py-20 sm:py-24">
        <div className="container-x grid gap-12 lg:grid-cols-2">
          <Reveal>
            <SectionHeading
              eyebrow="Our commitments"
              title="Clear terms. Real accountability."
              lead="The things clients tell us matter most — written down and treated as part of the product."
            />
            <div className="mt-8 space-y-3">
              {[
                { icon: CircleDollarSign, title: 'Transparent pricing', text: `Project deposits from ${money(settings.project_deposit ?? 200000, settings.currency)}, with a written scope before work starts.` },
                { icon: BadgeCheck, title: 'Quality gates', text: 'Peer review, responsive checks and a launch checklist on every release.' },
                { icon: HeartHandshake, title: 'Local support', text: 'A team in Jinja you can call, meet or WhatsApp during working hours.' },
              ].map((item) => (
                <div key={item.title} className="flex gap-4 rounded-2xl border border-brand-100 bg-white p-4">
                  <span className="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-600">
                    <item.icon className="h-5 w-5" />
                  </span>
                  <div>
                    <h3 className="text-sm font-semibold text-navy-900">{item.title}</h3>
                    <p className="mt-0.5 text-[12.5px] leading-relaxed text-ink-muted">{item.text}</p>
                  </div>
                </div>
              ))}
            </div>
          </Reveal>

          <Reveal delay={120}>
            <div className="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-navy-900 p-7 text-white shadow-lift">
              <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-40" />
              <div className="relative">
                <span className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.18em] text-white/70">
                  <Sparkles className="h-3 w-3" /> Engineering stack
                </span>
                <h3 className="mt-5 font-display text-2xl font-semibold">Modern tools, boring reliability.</h3>
                <p className="mt-2 text-sm leading-relaxed text-white/65">
                  We choose proven technology that is easy to hire for, easy to host and easy to maintain after we hand over.
                </p>
                <div className="mt-6 flex flex-wrap gap-2">
                  {stack.map((item) => (
                    <span key={item} className="rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-[11px] font-medium text-white/75">
                      {item}
                    </span>
                  ))}
                </div>
                <div className="mt-8 grid grid-cols-2 gap-3">
                  <div className="rounded-2xl bg-white/10 p-4">
                    <p className="font-display text-2xl font-semibold">99.9%</p>
                    <p className="mt-0.5 text-[10px] uppercase tracking-widest text-white/45">Target uptime</p>
                  </div>
                  <div className="rounded-2xl bg-white/10 p-4">
                    <p className="font-display text-2xl font-semibold">&lt; 24h</p>
                    <p className="mt-0.5 text-[10px] uppercase tracking-widest text-white/45">Support response</p>
                  </div>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      {/* Mini FAQ */}
      <section className="border-y border-brand-100 bg-brand-50/40 py-20 sm:py-24">
        <div className="container-x grid gap-10 lg:grid-cols-[0.8fr_1.2fr]">
          <Reveal>
            <SectionHeading eyebrow="Before you ask" title="Straight answers." lead="A few of the questions we hear most often. The rest live on the FAQ page." />
            <Link to="/faq" className="btn-ghost btn-sm mt-6">
              All questions <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </Reveal>
          <div className="space-y-3">
            {[
              { q: 'How much does a project cost?', a: 'It depends on scope. Websites typically start at UGX 500,000, while business systems and custom software are quoted after discovery. Every proposal is itemised.' },
              { q: 'How long will it take?', a: 'A focused website can be ready in 2–4 weeks. Management systems usually take 6–12 weeks, delivered in usable increments rather than one big reveal.' },
              { q: 'Do you support the system after launch?', a: 'Yes. Maintenance plans cover updates, backups, monitoring and small improvements, with a clear response window.' },
            ].map((item, index) => (
              <Reveal key={item.q} delay={index * 60}>
                <details className="group rounded-2xl border border-brand-100 bg-white p-5 open:shadow-soft">
                  <summary className="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-semibold text-navy-900">
                    {item.q}
                    <span className="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-600 transition group-open:rotate-45">+</span>
                  </summary>
                  <p className="mt-3 text-[13px] leading-relaxed text-ink-muted">{item.a}</p>
                </details>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-20 sm:py-24">
        <div className="container-x">
          <Reveal>
            <div className="relative overflow-hidden rounded-[32px] bg-navy-950 px-6 py-14 text-center text-white sm:px-12">
              <div className="aurora opacity-70" />
              <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
              <div className="relative mx-auto max-w-2xl">
                <span className="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 font-mono text-[9px] uppercase tracking-[0.2em] text-white/55">
                  <MapPin className="h-3 w-3" /> {settings.company_address}
                </span>
                <h2 className="h2 mt-5 text-white">Let's build something your team will actually use.</h2>
                <p className="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-white/60">
                  Tell us what is slowing you down. We will come back with a clear, honest plan — and a realistic budget.
                </p>
                <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
                  <Link to="/contact" className="btn-primary">
                    Book a discovery call <ArrowRight className="h-4 w-4" />
                  </Link>
                  <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10">
                    WhatsApp us
                  </a>
                </div>
                <div className="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[11px] text-white/40">
                  <span className="inline-flex items-center gap-1.5"><Clock3 className="h-3 w-3" /> {settings.company_hours}</span>
                  <span className="inline-flex items-center gap-1.5"><Star className="h-3 w-3 text-gold-400" /> Fixed scope before work starts</span>
                  <span className="inline-flex items-center gap-1.5"><Rocket className="h-3 w-3" /> {settings.company_founder}</span>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  );
}
