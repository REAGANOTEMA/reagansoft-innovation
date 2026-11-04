import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { ArrowRight, CheckCircle2, Clock3, Mail, MapPin, MessageCircle, Phone, Send, ShieldCheck } from 'lucide-react';
import { api, ApiError } from '../lib/api';
import { usePageMeta } from '../lib/seo';
import { useSite } from '../lib/site';
import { useToast } from '../components/Toast';
import { Field } from '../components/UI';
import { ServiceGlyph } from '../components/Graphics';

export function Contact() {
  usePageMeta(
    'Contact Reagansoft Innovation — Start a project in Uganda',
    'Tell us about your project. Reagansoft Innovation is based in Jinja, Uganda and works with organisations across the country.',
  );

  const { settings, services } = useSite();
  const { push } = useToast();
  const [params] = useSearchParams();
  const [sent, setSent] = useState(false);
  const [sending, setSending] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [form, setForm] = useState({
    full_name: '',
    email: '',
    phone: '',
    company: '',
    service: '',
    budget: '',
    message: '',
    company_website: '',
  });

  useEffect(() => {
    const service = params.get('service') ?? '';
    const tier = params.get('tier') ?? '';
    if (service || tier) {
      setForm((current) => ({
        ...current,
        service,
        message: current.message || (service ? `I would like to discuss ${service}.` : `I am interested in the ${tier} package.`),
      }));
    }
  }, [params]);

  function update(key: keyof typeof form, value: string) {
    setForm((current) => ({ ...current, [key]: value }));
    setErrors((current) => {
      if (!current[key]) return current;
      const next = { ...current };
      delete next[key];
      return next;
    });
  }

  async function submit(event: React.FormEvent) {
    event.preventDefault();
    setSending(true);
    setErrors({});
    try {
      const subject = [form.service, form.budget ? `Budget: ${form.budget}` : ''].filter(Boolean).join(' · ');
      await api('public/contact', {
        method: 'POST',
        body: {
          full_name: form.full_name,
          email: form.email,
          phone: form.phone,
          company: form.company,
          subject,
          message: form.message,
          company_website: form.company_website,
        },
      });
      setSent(true);
      push('success', 'Message sent. We will get back to you shortly.');
    } catch (error) {
      if (error instanceof ApiError) {
        const mapped: Record<string, string> = {};
        Object.entries(error.errors).forEach(([key, value]) => {
          mapped[key] = Array.isArray(value) ? value[0] : value;
        });
        setErrors(mapped);
        push('error', error.message);
      } else {
        push('error', 'Could not send your message. Please try again.');
      }
    } finally {
      setSending(false);
    }
  }

  return (
    <>
      <section className="relative overflow-hidden bg-navy-950 pb-16 pt-16 text-white sm:pt-20">
        <div className="aurora" />
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-35" />
        <div className="container-x relative grid gap-8 lg:grid-cols-[1.1fr_0.9fr] lg:items-end">
          <div>
            <span className="eyebrow text-brand-300">Contact</span>
            <h1 className="h1 mt-4 max-w-3xl text-white">Tell us what is slowing you down.</h1>
            <p className="mt-5 max-w-2xl text-[1.02rem] leading-relaxed text-white/65">
              Share the problem, the current process and any deadline. We will reply with next steps — usually within one working day.
            </p>
          </div>
          <div className="space-y-2.5">
            <a href={`mailto:${settings.company_email}`} className="flex items-center gap-3 rounded-xl border border-white/12 bg-white/[0.05] px-4 py-3 text-[12.5px] text-white/70 backdrop-blur transition hover:bg-white/10 hover:text-white">
              <Mail className="h-4 w-4 text-brand-300" /> {settings.company_email}
            </a>
            <a href={`tel:${settings.company_phone.replace(/\s/g, '')}`} className="flex items-center gap-3 rounded-xl border border-white/12 bg-white/[0.05] px-4 py-3 text-[12.5px] text-white/70 backdrop-blur transition hover:bg-white/10 hover:text-white">
              <Phone className="h-4 w-4 text-brand-300" /> {settings.company_phone}
            </a>
            <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="flex items-center gap-3 rounded-xl border border-white/12 bg-white/[0.05] px-4 py-3 text-[12.5px] text-white/70 backdrop-blur transition hover:bg-white/10 hover:text-white">
              <MessageCircle className="h-4 w-4 text-brand-300" /> WhatsApp {settings.company_whatsapp}
            </a>
          </div>
        </div>
      </section>

      <section className="py-14 sm:py-20">
        <div className="container-x grid gap-10 lg:grid-cols-[1.25fr_0.75fr]">
          <div className="rounded-3xl border border-brand-100 bg-white p-6 shadow-soft sm:p-8">
            {sent ? (
              <div className="flex min-h-[420px] flex-col items-center justify-center text-center">
                <span className="grid h-16 w-16 place-items-center rounded-3xl bg-emerald-50 text-emerald-600">
                  <CheckCircle2 className="h-8 w-8" />
                </span>
                <h2 className="mt-5 font-display text-2xl font-semibold text-navy-900">Message received.</h2>
                <p className="mt-2 max-w-md text-sm leading-relaxed text-ink-muted">
                  Thank you, {form.full_name.split(' ')[0] || 'there'}. We have your enquiry and will respond to {form.email} within one
                  working day. If it is urgent, WhatsApp is the fastest route.
                </p>
                <div className="mt-6 flex flex-wrap justify-center gap-3">
                  <Link to="/portfolio" className="btn-ghost btn-sm">Browse our work</Link>
                  <button
                    type="button"
                    className="btn-primary btn-sm"
                    onClick={() => {
                      setSent(false);
                      setForm({ full_name: '', email: '', phone: '', company: '', service: '', budget: '', message: '', company_website: '' });
                    }}
                  >
                    Send another message
                  </button>
                </div>
              </div>
            ) : (
              <form onSubmit={submit} className="space-y-4">
                <div>
                  <h2 className="font-display text-xl font-semibold text-navy-900">Project enquiry</h2>
                  <p className="mt-1 text-[12px] text-ink-muted">Fields marked * are required.</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                  <Field label="Full name *" error={errors.full_name}>
                    <input className="input" value={form.full_name} onChange={(event) => update('full_name', event.target.value)} required autoComplete="name" placeholder="Your name" />
                  </Field>
                  <Field label="Email address *" error={errors.email}>
                    <input className="input" type="email" value={form.email} onChange={(event) => update('email', event.target.value)} required autoComplete="email" placeholder="you@company.com" />
                  </Field>
                  <Field label="Phone number *" error={errors.phone}>
                    <input className="input" value={form.phone} onChange={(event) => update('phone', event.target.value)} required autoComplete="tel" placeholder="+256 7xx xxx xxx" />
                  </Field>
                  <Field label="Company / organisation" error={errors.company}>
                    <input className="input" value={form.company} onChange={(event) => update('company', event.target.value)} autoComplete="organization" placeholder="Optional" />
                  </Field>
                  <Field label="Service of interest">
                    <select className="input" value={form.service} onChange={(event) => update('service', event.target.value)}>
                      <option value="">Not sure yet</option>
                      {services.map((service) => (
                        <option key={service.id} value={service.name}>{service.name}</option>
                      ))}
                    </select>
                  </Field>
                  <Field label="Budget range">
                    <select className="input" value={form.budget} onChange={(event) => update('budget', event.target.value)}>
                      <option value="">Prefer to discuss</option>
                      <option value="Under UGX 1M">Under UGX 1M</option>
                      <option value="UGX 1M – 5M">UGX 1M – 5M</option>
                      <option value="UGX 5M – 15M">UGX 5M – 15M</option>
                      <option value="UGX 15M – 40M">UGX 15M – 40M</option>
                      <option value="Above UGX 40M">Above UGX 40M</option>
                    </select>
                  </Field>
                </div>

                <Field label="Tell us about the project *" error={errors.message} hint="What should it do, who will use it, and what is the deadline?">
                  <textarea
                    className="input min-h-[140px] resize-y"
                    value={form.message}
                    onChange={(event) => update('message', event.target.value)}
                    required
                    placeholder="We currently track stock in a notebook and need…"
                  />
                </Field>

                <input
                  type="text"
                  tabIndex={-1}
                  autoComplete="off"
                  value={form.company_website}
                  onChange={(event) => update('company_website', event.target.value)}
                  className="hidden"
                  aria-hidden="true"
                />

                <div className="flex flex-col gap-3 border-t border-brand-50 pt-4 sm:flex-row sm:items-center sm:justify-between">
                  <p className="inline-flex items-center gap-1.5 text-[11px] text-ink-faint">
                    <ShieldCheck className="h-3.5 w-3.5 text-emerald-500" /> Your details stay private. No mailing list unless you ask.
                  </p>
                  <button type="submit" disabled={sending} className="btn-primary">
                    {sending ? 'Sending…' : 'Send enquiry'} <Send className="h-4 w-4" />
                  </button>
                </div>
              </form>
            )}
          </div>

          <aside className="space-y-4">
            <div className="rounded-3xl border border-brand-100 bg-brand-50/50 p-6">
              <h3 className="font-display text-base font-semibold text-navy-900">Studio details</h3>
              <ul className="mt-4 space-y-3 text-[12.5px] text-ink-soft">
                <li className="flex gap-2.5"><MapPin className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" /> {settings.company_address}</li>
                <li className="flex gap-2.5"><Clock3 className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" /> {settings.company_hours}</li>
                <li className="flex gap-2.5"><Mail className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" /> {settings.company_email}</li>
                <li className="flex gap-2.5"><Phone className="mt-0.5 h-4 w-4 shrink-0 text-brand-600" /> {settings.company_phone}</li>
              </ul>
              {settings.company_map_url && (
                <a href={settings.company_map_url} target="_blank" rel="noreferrer" className="btn-ghost btn-sm mt-4 w-full">
                  Open in Google Maps <ArrowRight className="h-3.5 w-3.5" />
                </a>
              )}
            </div>

            <div className="rounded-3xl border border-brand-100 bg-white p-6">
              <h3 className="font-display text-base font-semibold text-navy-900">What happens next</h3>
              <ol className="mt-4 space-y-3">
                {[
                  'We read your enquiry and check fit.',
                  'You get a reply with questions or a call link.',
                  'A 30-minute discovery call clarifies scope.',
                  'You receive a written estimate and timeline.',
                ].map((item, index) => (
                  <li key={item} className="flex gap-2.5 text-[12px] text-ink-soft">
                    <span className="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-brand-50 font-mono text-[9px] font-bold text-brand-700">{index + 1}</span>
                    {item}
                  </li>
                ))}
              </ol>
            </div>

            <div className="rounded-3xl bg-navy-950 p-6 text-white">
              <div className="flex items-center gap-2">
                <ServiceGlyph name="chat" className="h-4 w-4 text-brand-300" />
                <p className="text-[12px] font-semibold">Prefer WhatsApp?</p>
              </div>
              <p className="mt-2 text-[11.5px] leading-relaxed text-white/55">Send a voice note or a short message. We reply during working hours.</p>
              <a href={`https://wa.me/${settings.company_whatsapp.replace(/[^\d]/g, '')}`} target="_blank" rel="noreferrer" className="btn bg-white text-navy-900 btn-sm mt-4 w-full">
                Message {settings.company_whatsapp}
              </a>
            </div>
          </aside>
        </div>
      </section>
    </>
  );
}
