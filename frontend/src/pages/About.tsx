import { Link } from 'react-router-dom';
import { ArrowRight, ArrowUpRight, Check, Compass, HeartHandshake, MapPin, ShieldCheck, Sparkles, Target, Users, Wrench } from 'lucide-react';
import { useSite } from '../lib/site';
import { usePageMeta } from '../lib/seo';
import { asset } from '../lib/env';
import { Reveal } from '../components/Reveal';
import { SectionHeading, Badge } from '../components/UI';
import { CloudInfraArt, IndustryGlyph, SecurityArt } from '../components/Graphics';

const principles = [
  { icon: Target, title: 'Useful over impressive', text: 'Every feature must earn its place by solving a real operational problem.' },
  { icon: ShieldCheck, title: 'Reliability is a feature', text: 'Backups, access control and monitoring are designed in from day one.' },
  { icon: HeartHandshake, title: 'Relationships over transactions', text: 'Most of our work comes from referrals and returning clients. We protect that trust.' },
  { icon: Wrench, title: 'Built to be handed over', text: 'Readable code, documentation and training so your team is never held hostage.' },
];

const milestones = [
  { year: 'The start', title: 'A studio in Jinja', text: 'Reagansoft began with a simple observation: local organisations were being sold websites they could not maintain or grow.' },
  { year: 'The shift', title: 'From pages to platforms', text: 'Client demand moved from brochures to systems — stock, billing, school records, bookings and payments.' },
  { year: 'Today', title: 'A full delivery partner', text: 'We now cover strategy, design, engineering, deployment, training and long-term support across Uganda.' },
  { year: 'Next', title: 'Deeper integration', text: 'Our roadmap focuses on data, automation and secure integrations that connect the tools businesses already use.' },
];

export function About() {
  usePageMeta(
    'About Reagansoft Innovation — Software studio in Jinja, Uganda',
    'Meet the team behind Reagansoft Innovation Limited: a Ugandan software studio building websites, business systems and mobile apps with long-term support.',
  );

  const { settings, stats } = useSite();

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-20 pt-16 text-white sm:pt-24">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative grid items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
          <div>
            <span className="eyebrow text-brand-300">About the studio</span>
            <h1 className="h1 mt-4 max-w-2xl text-white">
              A Ugandan team building
              <br />
              software that <span className="bg-gradient-to-r from-brand-300 to-cyan-300 bg-clip-text text-transparent">lasts.</span>
            </h1>
            <p className="mt-6 max-w-xl text-[1.02rem] leading-relaxed text-white/65">
              Reagansoft Innovation Limited is a software company based in {settings.company_address}. We work with schools, hotels,
              farms, traders, clinics and growing businesses to replace manual processes with systems their teams can trust.
            </p>
            <div className="mt-8 flex flex-wrap gap-3">
              <Link to="/contact" className="btn-primary">
                Work with us <ArrowRight className="h-4 w-4" />
              </Link>
              <Link to="/portfolio" className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10">
                See our work <ArrowUpRight className="h-4 w-4" />
              </Link>
            </div>
          </div>

          <Reveal delay={120}>
            <div className="relative">
              <div className="overflow-hidden rounded-[28px] border border-white/15 bg-white/5 p-2 backdrop-blur">
                <img src={asset('founder.webp')} alt={`${settings.company_founder}, founder of Reagansoft Innovation`} className="h-[380px] w-full rounded-[22px] object-cover object-top" />
              </div>
              <div className="absolute -bottom-5 left-5 right-5 rounded-2xl border border-white/15 bg-white/95 p-4 text-navy-900 shadow-lift">
                <p className="font-display text-sm font-semibold">{settings.company_founder}</p>
                <p className="mt-0.5 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">Founder · Reagansoft Innovation</p>
                <p className="mt-2 text-[12px] leading-relaxed text-ink-muted">“Technology should make the work lighter, not add another layer of confusion.”</p>
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="border-b border-brand-100 bg-white py-10">
        <div className="container-x grid grid-cols-2 gap-6 sm:grid-cols-4">
          {[
            { label: 'Projects delivered', value: stats.projects },
            { label: 'Completed builds', value: stats.completed },
            { label: 'Active clients', value: stats.clients },
            { label: 'Service lines', value: stats.services },
          ].map((item) => (
            <div key={item.label} className="text-center sm:text-left">
              <p className="font-display text-3xl font-semibold text-navy-900">{item.value}</p>
              <p className="mt-1 font-mono text-[9px] uppercase tracking-[0.18em] text-ink-faint">{item.label}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="py-20 sm:py-24">
        <div className="container-x grid gap-14 lg:grid-cols-[0.95fr_1.05fr]">
          <Reveal>
            <SectionHeading
              eyebrow="Our story"
              title="Started locally. Built for real constraints."
              lead={settings.company_about}
            />
            <div className="mt-6 space-y-4 text-[14px] leading-relaxed text-ink-muted">
              <p>
                Most of the organisations we serve do not need another generic template. They need a system that reflects how they
                take orders, track stock, register students, manage bookings or follow up with clients.
              </p>
              <p>
                That is why we spend time on discovery before writing code. We look at the spreadsheets, the notebooks, the WhatsApp
                groups and the workarounds — then design something simpler and more dependable.
              </p>
              <p>
                We are deliberately small and senior. The people who scope your project are the same people who build it, test it and
                support it after launch.
              </p>
            </div>
            <div className="mt-7 flex flex-wrap gap-2">
              <Badge tone="blue"><MapPin className="h-3 w-3" /> {settings.company_address}</Badge>
              <Badge tone="green"><Sparkles className="h-3 w-3" /> Founded by {settings.company_founder}</Badge>
              <Badge tone="violet"><Users className="h-3 w-3" /> Local team, national reach</Badge>
            </div>
          </Reveal>

          <div className="grid gap-4 sm:grid-cols-2">
            <Reveal delay={80}>
              <div className="card p-6">
                <span className="grid h-11 w-11 place-items-center rounded-2xl bg-brand-50 text-brand-600">
                  <Compass className="h-5 w-5" />
                </span>
                <h3 className="mt-4 font-display text-base font-semibold text-navy-900">Mission</h3>
                <p className="mt-2 text-[13px] leading-relaxed text-ink-muted">
                  To give Ugandan organisations dependable digital tools that reduce manual work, improve decisions and create room to grow.
                </p>
              </div>
            </Reveal>
            <Reveal delay={140}>
              <div className="card p-6">
                <span className="grid h-11 w-11 place-items-center rounded-2xl bg-navy-900 text-brand-300">
                  <Target className="h-5 w-5" />
                </span>
                <h3 className="mt-4 font-display text-base font-semibold text-navy-900">Vision</h3>
                <p className="mt-2 text-[13px] leading-relaxed text-ink-muted">
                  A market where local businesses can access the same quality of software and support as organisations anywhere else.
                </p>
              </div>
            </Reveal>
            <Reveal delay={200} className="sm:col-span-2">
              <div className="relative overflow-hidden rounded-3xl bg-brand-50 p-6">
                <div className="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-brand-200/60 blur-2xl" />
                <h3 className="relative font-display text-base font-semibold text-navy-900">How we measure success</h3>
                <div className="relative mt-4 grid gap-3 sm:grid-cols-3">
                  {[
                    ['Time saved', 'Hours returned to your team every week'],
                    ['Clarity', 'One source of truth instead of scattered records'],
                    ['Confidence', 'Systems that stay up and data you can trust'],
                  ].map(([title, text]) => (
                    <div key={title} className="rounded-2xl bg-white/80 p-4">
                      <p className="text-[12px] font-semibold text-brand-700">{title}</p>
                      <p className="mt-1 text-[11.5px] leading-relaxed text-ink-muted">{text}</p>
                    </div>
                  ))}
                </div>
              </div>
            </Reveal>
          </div>
        </div>
      </section>

      <section className="relative overflow-hidden bg-brand-50/50 py-20 sm:py-24">
        <div className="container-x">
          <Reveal>
            <SectionHeading eyebrow="What we believe" title="Principles that shape the work." align="center" />
          </Reveal>
          <div className="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {principles.map((item, index) => (
              <Reveal key={item.title} delay={index * 70}>
                <div className="card h-full p-6">
                  <span className="grid h-11 w-11 place-items-center rounded-2xl bg-white text-brand-600 ring-1 ring-brand-100">
                    <item.icon className="h-5 w-5" />
                  </span>
                  <h3 className="mt-4 font-display text-[15px] font-semibold text-navy-900">{item.title}</h3>
                  <p className="mt-2 text-[12.5px] leading-relaxed text-ink-muted">{item.text}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20 sm:py-24">
        <div className="container-x grid gap-14 lg:grid-cols-2">
          <Reveal>
            <SectionHeading eyebrow="Journey" title="A steady build, not a sudden leap." lead="The studio grew by solving one operational problem at a time." />
            <div className="mt-8 space-y-0">
              {milestones.map((item, index) => (
                <div key={item.title} className="relative flex gap-5 pb-7 last:pb-0">
                  {index < milestones.length - 1 && <span className="absolute left-[15px] top-8 h-full w-px bg-brand-100" />}
                  <span className="relative z-10 mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full border border-brand-200 bg-white font-mono text-[9px] font-bold text-brand-700">
                    {String(index + 1).padStart(2, '0')}
                  </span>
                  <div>
                    <p className="font-mono text-[9px] uppercase tracking-[0.18em] text-brand-600">{item.year}</p>
                    <h3 className="mt-1 font-display text-[15px] font-semibold text-navy-900">{item.title}</h3>
                    <p className="mt-1 text-[12.5px] leading-relaxed text-ink-muted">{item.text}</p>
                  </div>
                </div>
              ))}
            </div>
          </Reveal>

          <div className="space-y-4">
            <Reveal delay={80}>
              <CloudInfraArt />
            </Reveal>
            <Reveal delay={160}>
              <SecurityArt />
            </Reveal>
          </div>
        </div>
      </section>

      <section className="border-t border-brand-100 bg-white py-16">
        <div className="container-x">
          <Reveal>
            <SectionHeading eyebrow="Sectors" title="Where our work shows up." align="center" lead="Different industries, same discipline: understand the workflow, simplify it, then make it reliable." />
          </Reveal>
          <div className="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            {['education', 'hospitality', 'agriculture', 'commerce', 'health', 'logistics', 'government', 'ngos'].map((kind, index) => (
              <Reveal key={kind} delay={index * 40}>
                <div className="flex flex-col items-center gap-2 rounded-2xl border border-brand-100 bg-white p-4 text-center transition hover:border-brand-200 hover:shadow-soft">
                  <span className="grid h-9 w-9 place-items-center rounded-xl bg-brand-50 text-brand-600">
                    <IndustryGlyph kind={kind} />
                  </span>
                  <span className="text-[11px] font-semibold capitalize text-ink-soft">{kind === 'ngos' ? 'NGOs' : kind}</span>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="py-20">
        <div className="container-x">
          <Reveal>
            <div className="relative overflow-hidden rounded-[32px] bg-navy-950 px-6 py-12 text-white sm:px-12">
              <div className="aurora opacity-70" />
              <div className="relative grid items-center gap-8 lg:grid-cols-[1.4fr_0.6fr]">
                <div>
                  <h2 className="h2 text-white">Have a process that needs fixing?</h2>
                  <p className="mt-3 max-w-xl text-sm leading-relaxed text-white/60">
                    Bring the messy version. We will help you see what can be simplified, what should be automated and what can wait.
                  </p>
                </div>
                <div className="flex flex-wrap gap-3 lg:justify-end">
                  <Link to="/contact" className="btn-primary">
                    Talk to us <ArrowRight className="h-4 w-4" />
                  </Link>
                  <Link to="/services" className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10">
                    Browse services
                  </Link>
                </div>
              </div>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  );
}
