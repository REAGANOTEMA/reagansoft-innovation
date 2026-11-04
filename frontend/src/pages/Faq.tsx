import { useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { ArrowRight, ChevronDown, HelpCircle, Mail, MessageCircle, Phone } from 'lucide-react';
import { usePageMeta } from '../lib/seo';
import { useSite } from '../lib/site';
import { Reveal } from '../components/Reveal';
import { SectionHeading } from '../components/UI';
import { cn } from '../lib/cn';

const faqs = [
  {
    category: 'Working with us',
    items: [
      { q: 'What kind of organisations do you work with?', a: 'Schools, hotels, farms, clinics, traders, NGOs and growing businesses. If you have a process that needs to be more reliable, we can help — the size of the organisation matters less than the clarity of the problem.' },
      { q: 'Do you work with clients outside Jinja?', a: 'Yes. Most collaboration happens online, and we travel for discovery, training or launch when it adds value. We have served clients across Uganda.' },
      { q: 'Who owns the code and data?', a: 'You do. On final payment we hand over the source code, database access, hosting credentials and documentation. There is no lock-in.' },
      { q: 'Can you take over a project someone else started?', a: 'Often, yes. We start with a technical review and a written assessment of what is salvageable, what needs rebuilding and what it will cost. We will not pretend a rescue is cheaper than it is.' },
    ],
  },
  {
    category: 'Pricing & payment',
    items: [
      { q: 'How much does a project cost?', a: 'Websites start at UGX 500,000; business systems and custom software typically start at UGX 20,000,000. Every quote is itemised after discovery. See the pricing page for current service rates.' },
      { q: 'What is the deposit?', a: 'Projects begin with a deposit from UGX 200,000, which reserves the delivery slot and covers discovery. The balance is staged across milestones and handover.' },
      { q: 'How do I pay?', a: 'Bank transfer, MTN Mobile Money or Airtel Money. Official receipts are issued for every confirmed payment, and invoices are due within 14 days unless agreed otherwise.' },
      { q: 'Do you charge for changes?', a: 'Changes inside the agreed scope are included. New requirements are quoted before work starts, so you always approve the cost first.' },
    ],
  },
  {
    category: 'Delivery',
    items: [
      { q: 'How long does a project take?', a: 'A focused website usually takes 2–4 weeks. Business systems and custom platforms typically run 6–12 weeks, delivered in usable increments rather than one final reveal.' },
      { q: 'How will I know progress is happening?', a: 'You get a shared board, weekly demo links and a named lead who answers directly. Nothing important is hidden until launch.' },
      { q: 'What happens at launch?', a: 'We test key flows, migrate data, train your team, deploy and stay close during a stabilisation window. A rollback plan is prepared before go-live.' },
      { q: 'Do you provide training?', a: 'Yes. Every delivery includes a practical walkthrough, written guidance and, where useful, recorded sessions for new staff.' },
    ],
  },
  {
    category: 'Support & security',
    items: [
      { q: 'Do you offer maintenance?', a: 'Yes. Plans start from UGX 150,000 per month and cover updates, backups, monitoring and a monthly allowance for small changes. Larger work is quoted separately.' },
      { q: 'How is my data protected?', a: 'Role-based access, hashed passwords, audited actions, encrypted sessions and disciplined deployment checks are standard. Backups and restore steps are documented.' },
      { q: 'What if something breaks?', a: 'Contact support with the details. We triage quickly, restore service first where possible, then investigate and document the fix.' },
      { q: 'Can you integrate mobile money or card payments?', a: 'Yes, subject to provider availability and merchant onboarding. Integration scope, fees and testing are agreed before build.' },
    ],
  },
];

export function Faq() {
  usePageMeta(
    'FAQ — Working with Reagansoft Innovation',
    'Answers about scope, pricing, delivery, support, ownership and security when working with Reagansoft Innovation in Uganda.',
  );

  const { settings } = useSite();
  const [category, setCategory] = useState('All');
  const [open, setOpen] = useState<string | null>(faqs[0].items[0].q);

  const categories = useMemo(() => ['All', ...faqs.map((group) => group.category)], []);
  const groups = category === 'All' ? faqs : faqs.filter((group) => group.category === category);

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-16 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative">
          <span className="eyebrow text-brand-300">FAQ</span>
          <h1 className="h1 mt-4 max-w-3xl text-white">Questions we hear before every project.</h1>
          <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
            Clear answers on scope, money, delivery and support. If yours is not here, ask directly — we answer honestly, even when
            the answer is “not yet”.
          </p>
        </div>
      </section>

      <section className="py-12 sm:py-16">
        <div className="container-x grid gap-10 lg:grid-cols-[240px_1fr]">
          <aside className="lg:sticky lg:top-24 lg:self-start">
            <p className="font-mono text-[9px] uppercase tracking-[0.2em] text-ink-faint">Categories</p>
            <div className="mt-3 flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible">
              {categories.map((item) => (
                <button
                  key={item}
                  type="button"
                  onClick={() => setCategory(item)}
                  className={cn(
                    'shrink-0 rounded-xl px-3.5 py-2.5 text-left text-[12.5px] font-medium transition',
                    category === item ? 'bg-brand-600 text-white' : 'bg-brand-50/60 text-ink-soft hover:bg-brand-50 hover:text-brand-700',
                  )}
                >
                  {item}
                </button>
              ))}
            </div>
            <div className="mt-6 hidden rounded-2xl border border-brand-100 bg-white p-4 lg:block">
              <HelpCircle className="h-5 w-5 text-brand-600" />
              <p className="mt-2 text-[12px] font-semibold text-navy-900">Still unsure?</p>
              <p className="mt-1 text-[11px] leading-relaxed text-ink-muted">Send a short question and get a straight answer.</p>
              <Link to="/contact" className="btn-primary btn-sm mt-3 w-full">Ask us</Link>
            </div>
          </aside>

          <div className="space-y-8">
            {groups.map((group) => (
              <div key={group.category}>
                <h2 className="font-display text-lg font-semibold text-navy-900">{group.category}</h2>
                <div className="mt-3 space-y-2">
                  {group.items.map((item) => {
                    const isOpen = open === item.q;
                    return (
                      <div key={item.q} className={cn('rounded-2xl border bg-white transition', isOpen ? 'border-brand-200 shadow-soft' : 'border-brand-100')}>
                        <button
                          type="button"
                          onClick={() => setOpen(isOpen ? null : item.q)}
                          className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                          aria-expanded={isOpen}
                        >
                          <span className="text-[13.5px] font-semibold text-navy-900">{item.q}</span>
                          <ChevronDown className={cn('h-4 w-4 shrink-0 text-ink-faint transition', isOpen && 'rotate-180 text-brand-600')} />
                        </button>
                        {isOpen && <p className="px-5 pb-5 text-[12.5px] leading-relaxed text-ink-muted">{item.a}</p>}
                      </div>
                    );
                  })}
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="border-t border-brand-100 bg-brand-50/40 py-16">
        <div className="container-x">
          <Reveal>
            <div className="grid gap-6 rounded-3xl border border-brand-100 bg-white p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
              <div>
                <SectionHeading eyebrow="Direct line" title="Ask a human instead." lead="No bots, no ticket maze. Reach the team that would actually do the work." />
                <div className="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-[12px] text-ink-soft">
                  <a href={`mailto:${settings.company_email}`} className="inline-flex items-center gap-2 hover:text-brand-700"><Mail className="h-3.5 w-3.5 text-brand-600" /> {settings.company_email}</a>
                  <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="inline-flex items-center gap-2 hover:text-brand-700"><Phone className="h-3.5 w-3.5 text-brand-600" /> {settings.company_phone}</a>
                  <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="inline-flex items-center gap-2 hover:text-brand-700"><MessageCircle className="h-3.5 w-3.5 text-brand-600" /> WhatsApp</a>
                </div>
              </div>
              <Link to="/contact" className="btn-primary shrink-0">Contact us <ArrowRight className="h-4 w-4" /></Link>
            </div>
          </Reveal>
        </div>
      </section>
    </>
  );
}
