import { Link } from 'react-router-dom';
import { ArrowRight, Check, ClipboardCheck, Code2, Compass, FileText, Handshake, LifeBuoy, MessageSquare, Rocket, ShieldCheck, Users } from 'lucide-react';
import { usePageMeta } from '../lib/seo';
import { Reveal } from '../components/Reveal';
import { SectionHeading, Badge } from '../components/UI';

const steps = [
  {
    icon: Compass,
    step: '01',
    title: 'Discover',
    duration: 'Week 1',
    text: 'We meet your team, review the current process and agree what success looks like. You leave with a written brief and a clear scope.',
    deliverables: ['Discovery notes', 'Feature list', 'Success criteria', 'Indicative budget'],
  },
  {
    icon: FileText,
    step: '02',
    title: 'Design',
    duration: 'Weeks 1–2',
    text: 'Information architecture, wireframes and a visual direction. We review together before a single line of production code is written.',
    deliverables: ['Sitemap / flow', 'Wireframes', 'UI direction', 'Acceptance checklist'],
  },
  {
    icon: Code2,
    step: '03',
    title: 'Build',
    duration: 'Weeks 2–8',
    text: 'Weekly increments on a shared board, with demo links you can click. Feedback is collected in one place and prioritised openly.',
    deliverables: ['Working increments', 'Weekly demo', 'Issue log', 'Staging environment'],
  },
  {
    icon: ClipboardCheck,
    step: '04',
    title: 'Test & launch',
    duration: 'Final week',
    text: 'Functional, responsive and permission testing. We migrate data, train your team and supervise go-live with a rollback plan ready.',
    deliverables: ['Test report', 'Data migration', 'Training session', 'Launch checklist'],
  },
  {
    icon: LifeBuoy,
    step: '05',
    title: 'Support',
    duration: 'Ongoing',
    text: 'A stabilisation window after launch, then an optional maintenance plan for updates, monitoring and improvements.',
    deliverables: ['Handover pack', 'Support channel', 'Backup routine', 'Roadmap review'],
  },
];

const cadence = [
  { icon: MessageSquare, title: 'One channel', text: 'A shared WhatsApp or email thread for decisions, so nothing important lives in someone’s memory.' },
  { icon: Users, title: 'One owner', text: 'A named lead on our side who knows your project and answers directly.' },
  { icon: ShieldCheck, title: 'One source of truth', text: 'A live board for tasks, bugs and change requests — visible to both teams.' },
  { icon: Handshake, title: 'No silent changes', text: 'Anything outside the agreed scope is quoted and approved before it is built.' },
];

export function Process() {
  usePageMeta(
    'Our process — How Reagansoft delivers software projects',
    'A transparent five-stage delivery process: discovery, design, build, launch and support. See what happens at each stage.',
  );

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-20 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative">
          <span className="eyebrow text-brand-300">Process</span>
          <h1 className="h1 mt-4 max-w-3xl text-white">Visible progress, from first call to long after launch.</h1>
          <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
            Good software is rarely a straight line. Our process keeps the important decisions explicit, the feedback early and the
            surprises out of the invoice.
          </p>
          <div className="mt-7 flex flex-wrap gap-2">
            <Badge tone="blue">Weekly demos</Badge>
            <Badge tone="green">Written scope</Badge>
            <Badge tone="violet">Documented handover</Badge>
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="container-x">
          <div className="space-y-4">
            {steps.map((step, index) => (
              <Reveal key={step.step} delay={index * 50}>
                <div className="grid gap-6 rounded-3xl border border-brand-100 bg-white p-6 shadow-soft sm:p-7 lg:grid-cols-[auto_1fr_260px] lg:items-start">
                  <div className="flex items-center gap-4 lg:block">
                    <span className="grid h-12 w-12 place-items-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                      <step.icon className="h-5 w-5" />
                    </span>
                    <div className="lg:mt-3">
                      <p className="font-mono text-[10px] font-bold tracking-widest text-brand-600">{step.step}</p>
                      <p className="mt-0.5 font-mono text-[9px] uppercase tracking-wider text-ink-faint">{step.duration}</p>
                    </div>
                  </div>

                  <div>
                    <h2 className="font-display text-lg font-semibold text-navy-900">{step.title}</h2>
                    <p className="mt-2 max-w-2xl text-[13px] leading-relaxed text-ink-muted">{step.text}</p>
                    <div className="mt-4 flex flex-wrap gap-x-4 gap-y-1.5">
                      {step.deliverables.map((item) => (
                        <span key={item} className="inline-flex items-center gap-1.5 text-[11.5px] text-ink-soft">
                          <Check className="h-3.5 w-3.5 text-emerald-500" strokeWidth={2.5} /> {item}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div className="hidden rounded-2xl bg-brand-50/60 p-4 lg:block">
                    <p className="font-mono text-[8px] uppercase tracking-[0.18em] text-ink-faint">You receive</p>
                    <p className="mt-2 text-[12px] font-semibold leading-relaxed text-navy-900">{step.deliverables[0]}</p>
                    <p className="mt-1 text-[11px] leading-relaxed text-ink-muted">plus the rest of the stage deliverables, shared in your project folder.</p>
                  </div>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="border-y border-brand-100 bg-brand-50/40 py-16">
        <div className="container-x grid gap-12 lg:grid-cols-[0.9fr_1.1fr]">
          <Reveal>
            <SectionHeading eyebrow="Working together" title="Four rules that keep projects calm." lead="Most delivery problems are communication problems. These rules remove the usual gaps." />
            <Link to="/contact" className="btn-dark btn-sm mt-6">Start a project <ArrowRight className="h-3.5 w-3.5" /></Link>
          </Reveal>
          <div className="grid gap-3 sm:grid-cols-2">
            {cadence.map((item, index) => (
              <Reveal key={item.title} delay={index * 60}>
                <div className="card h-full p-5">
                  <item.icon className="h-5 w-5 text-brand-600" />
                  <p className="mt-3 text-[12.5px] font-semibold text-navy-900">{item.title}</p>
                  <p className="mt-1 text-[11.5px] leading-relaxed text-ink-muted">{item.text}</p>
                </div>
              </Reveal>
            ))}
          </div>
        </div>
      </section>

      <section className="py-16 sm:py-20">
        <div className="container-x grid gap-12 lg:grid-cols-2">
          <Reveal>
            <SectionHeading eyebrow="Quality gates" title="What we check before anything ships." lead="A release is not done because it looks finished. These checks are mandatory." />
            <div className="mt-6 space-y-3">
              {[
                'Every role can only see and do what it should',
                'Forms validate on both client and server',
                'Key flows tested on mobile, tablet and desktop',
                'Backups and restore steps documented',
                'No secrets in source control or client-side code',
                'Performance and page weight reviewed',
              ].map((item) => (
                <div key={item} className="flex items-start gap-3 text-[12.5px] text-ink-soft">
                  <span className="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-600">
                    <Check className="h-3 w-3" strokeWidth={3} />
                  </span>
                  {item}
                </div>
              ))}
            </div>
          </Reveal>

          <Reveal delay={100}>
            <div className="rounded-3xl bg-navy-950 p-7 text-white">
              <span className="grid h-11 w-11 place-items-center rounded-2xl bg-white/10 text-brand-300">
                <Rocket className="h-5 w-5" />
              </span>
              <h2 className="mt-4 font-display text-xl font-semibold">What we need from you</h2>
              <ul className="mt-4 space-y-3 text-[12.5px] text-white/65">
                {[
                  'A decision-maker available for weekly reviews',
                  'Access to current data, content and brand assets',
                  'A single point of contact for feedback',
                  'Realistic timelines for approvals',
                ].map((item) => (
                  <li key={item} className="flex gap-2">
                    <Check className="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-300" /> {item}
                  </li>
                ))}
              </ul>
              <div className="mt-6 rounded-2xl border border-white/10 bg-white/[0.04] p-4 text-[11.5px] leading-relaxed text-white/50">
                Projects move fastest when feedback is consolidated. We will help you set that rhythm in week one.
              </div>
            </div>
          </Reveal>
        </div>
      </section>

      <section className="border-t border-brand-100 py-16">
        <div className="container-x">
          <div className="flex flex-col items-center gap-4 rounded-3xl bg-gradient-to-br from-brand-600 to-navy-900 px-6 py-10 text-center text-white sm:px-10">
            <h2 className="h2 max-w-2xl text-white">Ready for stage one?</h2>
            <p className="max-w-xl text-sm text-white/65">A discovery call costs nothing and usually clarifies more than a long email thread.</p>
            <div className="flex flex-wrap justify-center gap-3">
              <Link to="/contact" className="btn bg-white text-navy-900 hover:bg-brand-50">Book a call <ArrowRight className="h-4 w-4" /></Link>
              <Link to="/pricing" className="btn border border-white/25 bg-white/5 text-white hover:bg-white/10">See pricing</Link>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
