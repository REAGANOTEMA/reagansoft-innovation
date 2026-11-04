import { Link } from 'react-router-dom';
import { ArrowRight, Check, CircleDollarSign, Clock3, FileText, HelpCircle, Layers3, ShieldCheck, Wallet } from 'lucide-react';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { Badge, SectionHeading } from '../components/UI';
import { money } from '../lib/format';
import { cn } from '../lib/cn';

const tiers = [
  {
    name: 'Starter',
    tagline: 'Get online properly',
    from: 500000,
    unit: 'project',
    description: 'A focused website or brand presence for a small team that needs to look credible and be found.',
    features: ['Up to 6 pages', 'Mobile-first design', 'Contact & enquiry forms', 'Basic SEO setup', 'Training walkthrough', '30-day launch support'],
    cta: 'Start with a website',
    tone: 'light' as const,
  },
  {
    name: 'Growth',
    tagline: 'Run the operation',
    from: 10000000,
    unit: 'project',
    description: 'An ecommerce store, booking engine or internal system that replaces manual work and centralises data.',
    features: ['Discovery & process mapping', 'Custom dashboards', 'Roles & permissions', 'Payments or SMS integration', 'Data migration', '90-day support window'],
    cta: 'Scope a business system',
    tone: 'dark' as const,
  },
  {
    name: 'Enterprise',
    tagline: 'Build the platform',
    from: 20000000,
    unit: 'project',
    description: 'Multi-module software, mobile apps and integrations for organisations with complex or regulated workflows.',
    features: ['Architecture & security review', 'Multi-role portals', 'Mobile or partner APIs', 'Automated reporting', 'Deployment & monitoring', 'Ongoing retainer option'],
    cta: 'Discuss a platform',
    tone: 'light' as const,
  },
];

const factors = [
  { icon: Layers3, title: 'Scope & modules', text: 'The number of workflows, user roles and screens is the biggest driver of effort.' },
  { icon: CircleDollarSign, title: 'Integrations', text: 'Payments, SMS, accounting, biometrics or third-party APIs add design and testing time.' },
  { icon: FileText, title: 'Content & data', text: 'Migration, cleanup and content preparation can be handled by us or by your team.' },
  { icon: Clock3, title: 'Timeline', text: 'Compressed delivery may require parallel workstreams; realistic timelines cost less.' },
];

export function Pricing() {
  usePageMeta(
    'Pricing — Transparent software project costs | Reagansoft Innovation',
    'Understand Reagansoft Innovation pricing: website packages, business systems, custom software, deposits, payment milestones and maintenance.',
  );

  const { services, settings } = useSite();
  const deposit = money(settings.project_deposit ?? 200000, settings.currency);

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-20 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative">
          <span className="eyebrow text-brand-300">Pricing</span>
          <h1 className="h1 mt-4 max-w-3xl text-white">Honest numbers, before the work starts.</h1>
          <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
            Software is not a commodity, so we do not pretend one price fits every brief. These are the real starting points — and
            exactly how we get from them to a fixed, itemised quote.
          </p>
          <div className="mt-7 flex flex-wrap gap-2">
            <Badge tone="blue">Deposit from {deposit}</Badge>
            <Badge tone="green">Fixed scope before build</Badge>
            <Badge tone="violet">Milestone payments</Badge>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="container-x">
          <div className="grid gap-4 lg:grid-cols-3">
            {tiers.map((tier, index) => (
              <Reveal key={tier.name} delay={index * 80}>
                <div
                  className={cn(
                    'relative flex h-full flex-col rounded-3xl border p-6 sm:p-7',
                    tier.tone === 'dark'
                      ? 'border-navy-800 bg-navy-950 text-white shadow-lift'
                      : 'border-brand-100 bg-white shadow-soft',
                  )}
                >
                  {tier.name === 'Growth' && (
                    <span className="absolute -top-3 left-6 rounded-full bg-gold-500 px-3 py-1 font-mono text-[9px] font-bold uppercase tracking-[0.16em] text-navy-950">
                      Most requested
                    </span>
                  )}
                  <p className={cn('font-mono text-[9px] uppercase tracking-[0.2em]', tier.tone === 'dark' ? 'text-brand-300' : 'text-brand-600')}>
                    {tier.tagline}
                  </p>
                  <h2 className={cn('mt-2 font-display text-xl font-semibold', tier.tone === 'dark' ? 'text-white' : 'text-navy-900')}>{tier.name}</h2>
                  <div className="mt-4">
                    <span className={cn('font-mono text-[10px] uppercase tracking-wider', tier.tone === 'dark' ? 'text-white/40' : 'text-ink-faint')}>From</span>
                    <p className={cn('mt-1 font-display text-3xl font-semibold', tier.tone === 'dark' ? 'text-white' : 'text-navy-900')}>
                      {money(tier.from, settings.currency)}
                    </p>
                  </div>
                  <p className={cn('mt-3 text-[12.5px] leading-relaxed', tier.tone === 'dark' ? 'text-white/55' : 'text-ink-muted')}>{tier.description}</p>
                  <ul className="mt-5 flex-1 space-y-2.5">
                    {tier.features.map((feature) => (
                      <li key={feature} className={cn('flex items-start gap-2 text-[12.5px]', tier.tone === 'dark' ? 'text-white/70' : 'text-ink-soft')}>
                        <Check className={cn('mt-0.5 h-3.5 w-3.5 shrink-0', tier.tone === 'dark' ? 'text-brand-300' : 'text-emerald-500')} strokeWidth={2.5} />
                        {feature}
                      </li>
                    ))}
                  </ul>
                  <Link
                    to={`/contact?tier=${encodeURIComponent(tier.name)}`}
                    className={cn('mt-6 w-full', tier.tone === 'dark' ? 'btn bg-white text-navy-900 hover:bg-brand-50' : 'btn-primary')}
                  >
                    {tier.cta} <ArrowRight className="h-4 w-4" />
                  </Link>
                </div>
              </Reveal>
            ))}
          </div>
          <p className="mt-5 text-center text-[11px] text-ink-faint">
            Ranges are indicative and exclude third-party costs such as domain names, hosting, SMS credits and payment gateway fees.
          </p>
        </div>
      </section>

      <section className="border-y border-brand-100 bg-brand-50/40 py-16">
        <div className="container-x">
          <div className="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <SectionHeading eyebrow="Service rates" title="Published starting points." lead="Every active service line with its minimum investment. Final quotes depend on scope." />
            <Link to="/services" className="btn-ghost btn-sm shrink-0">
              Compare services <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </div>

          <div className="mt-8 table-shell overflow-x-auto">
            <table className="data-table min-w-[680px]">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Starting from</th>
                  <th>Pricing model</th>
                  <th>Typical delivery</th>
                  <th className="text-right">Action</th>
                </tr>
              </thead>
              <tbody>
                {services.map((service) => (
                  <tr key={service.id}>
                    <td>
                      <p className="font-semibold text-navy-900">{service.name}</p>
                      <p className="mt-0.5 line-clamp-1 max-w-md text-[11.5px] text-ink-muted">{service.description}</p>
                    </td>
                    <td className="whitespace-nowrap font-mono text-[12px] font-semibold text-brand-700">{service.price_short}</td>
                    <td className="whitespace-nowrap text-[12px] capitalize text-ink-muted">{service.pricing_type.replace(/_/g, ' ')}</td>
                    <td className="whitespace-nowrap text-[12px] text-ink-muted">{service.delivery_days ? `${service.delivery_days} days` : 'Scoped'}</td>
                    <td className="text-right">
                      <Link to={`/services/${service.slug}`} className="text-[11px] font-semibold text-brand-700 hover:text-brand-800">
                        Details →
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="container-x grid gap-12 lg:grid-cols-2">
          <Reveal>
            <SectionHeading eyebrow="What changes the price" title="Four variables, no mystery." lead="We will always show you which of these is driving the cost — and what can be deferred to a later phase." />
            <div className="mt-7 grid gap-3 sm:grid-cols-2">
              {factors.map((factor) => (
                <div key={factor.title} className="rounded-2xl border border-brand-100 bg-white p-4">
                  <span className="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-600">
                    <factor.icon className="h-4 w-4" />
                  </span>
                  <p className="mt-3 text-[12.5px] font-semibold text-navy-900">{factor.title}</p>
                  <p className="mt-1 text-[11.5px] leading-relaxed text-ink-muted">{factor.text}</p>
                </div>
              ))}
            </div>
          </Reveal>

          <Reveal delay={100}>
            <div className="rounded-3xl border border-brand-100 bg-white p-6 shadow-soft">
              <div className="flex items-center gap-3">
                <span className="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-50 text-emerald-600">
                  <Wallet className="h-5 w-5" />
                </span>
                <div>
                  <h2 className="font-display text-lg font-semibold text-navy-900">How payments work</h2>
                  <p className="text-[11px] text-ink-muted">Simple, staged and documented.</p>
                </div>
              </div>
              <ol className="mt-5 space-y-4">
                {[
                  ['Deposit', `${deposit} to reserve the slot and begin discovery.`, 'Due on acceptance'],
                  ['Milestone 1', 'Design or first working module accepted.', 'Typically 30–40%'],
                  ['Milestone 2', 'Core build complete and tested.', 'Typically 30%'],
                  ['Handover', 'Training, documentation and go-live.', 'Balance on launch'],
                ].map(([title, text, meta], index) => (
                  <li key={title} className="flex gap-3">
                    <span className="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-brand-50 font-mono text-[10px] font-bold text-brand-700">{index + 1}</span>
                    <div className="flex-1">
                      <div className="flex flex-wrap items-baseline justify-between gap-2">
                        <p className="text-[12.5px] font-semibold text-navy-900">{title}</p>
                        <span className="font-mono text-[9px] uppercase tracking-wider text-ink-faint">{meta}</span>
                      </div>
                      <p className="mt-0.5 text-[11.5px] text-ink-muted">{text}</p>
                    </div>
                  </li>
                ))}
              </ol>
              <div className="mt-5 rounded-2xl bg-brand-50/70 p-4 text-[11.5px] leading-relaxed text-ink-muted">
                Invoices are due within 14 days unless agreed otherwise. We accept bank transfer, MTN MoMo and Airtel Money. Official
                receipts are issued for every confirmed payment.
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="border-t border-brand-100 bg-navy-950 py-16 text-white">
        <div className="container-x grid gap-8 lg:grid-cols-[1fr_1fr] lg:items-center">
          <div>
            <span className="eyebrow text-brand-300">Maintenance</span>
            <h2 className="h2 mt-3 text-white">After launch, keep it healthy.</h2>
            <p className="mt-3 max-w-xl text-sm leading-relaxed text-white/60">
              Maintenance plans start from UGX 150,000 per month and cover updates, backups, monitoring and a monthly allowance for
              small changes. Bigger enhancements are quoted separately.
            </p>
          </div>
          <div className="grid gap-3 sm:grid-cols-2">
            {[
              { icon: ShieldCheck, title: 'Security updates', text: 'Dependency and platform patches applied on a schedule.' },
              { icon: Clock3, title: 'Response window', text: 'Priority support with a clear response target.' },
              { icon: Wallet, title: 'Predictable cost', text: 'One monthly figure instead of ad-hoc invoices.' },
              { icon: HelpCircle, title: 'Advice included', text: 'Practical guidance on hosting, tools and next steps.' },
            ].map((item) => (
              <div key={item.title} className="rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                <item.icon className="h-4 w-4 text-brand-300" />
                <p className="mt-2 text-[12.5px] font-semibold text-white">{item.title}</p>
                <p className="mt-1 text-[11px] leading-relaxed text-white/45">{item.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16">
        <div className="container-x">
          <div className="flex flex-col items-center gap-4 rounded-3xl border border-brand-100 bg-brand-50/50 px-6 py-10 text-center">
            <h2 className="h2 max-w-2xl text-navy-900">Want a number for your specific brief?</h2>
            <p className="max-w-xl text-sm text-ink-muted">Share the essentials and we will respond with a written estimate — usually within one working day.</p>
            <div className="flex flex-wrap justify-center gap-3">
              <Link to="/contact" className="btn-primary">Request a quote <ArrowRight className="h-4 w-4" /></Link>
              <Link to="/faq" className="btn-ghost">Read the FAQ</Link>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
