import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import {
  ArrowLeft,
  ArrowRight,
  CalendarDays,
  Check,
  CheckCircle2,
  Download,
  FileText,
  FolderKanban,
  MessageSquare,
  Paperclip,
  ReceiptText,
  Send,
  Upload,
  Wallet,
} from 'lucide-react';
import { api, ApiError } from '../../lib/api';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { useToast } from '../../components/Toast';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, Modal, ProgressBar } from '../../components/UI';
import { InvoiceView } from '../../components/InvoiceView';
import { PaymentModal } from '../../components/PaymentModal';
import { formatDate, money, timeAgo } from '../../lib/format';
import { invoiceStatus, priorityTone, projectStatus, quotationStatus, taskStatus } from '../../lib/status';
import { cn } from '../../lib/cn';
import type { Invoice, Message, Project, ProjectFile, Quotation, Task } from '../../lib/types';

interface Detail {
  project: Project;
  tasks: Task[];
  files: ProjectFile[];
  messages: Message[];
  invoices: Invoice[];
  quotations: Quotation[];
  milestones: string[];
  milestone_index: number;
}

const tabs = [
  { key: 'overview', label: 'Overview' },
  { key: 'tasks', label: 'Tasks' },
  { key: 'files', label: 'Files' },
  { key: 'messages', label: 'Messages' },
  { key: 'billing', label: 'Invoices & quotes' },
] as const;

type TabKey = (typeof tabs)[number]['key'];

export function PortalProjectDetail() {
  const { id = '' } = useParams();
  const { push } = useToast();
  const { data, loading, error, reload } = useApi<Detail>(`client/projects/${encodeURIComponent(id)}`);
  const [tab, setTab] = useState<TabKey>('overview');
  const [message, setMessage] = useState('');
  const [sending, setSending] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [viewInvoice, setViewInvoice] = useState<Invoice | null>(null);
  const [payInvoice, setPayInvoice] = useState<Invoice | null>(null);

  usePageMeta(data?.project ? `${data.project.title} — Reagansoft portal` : 'Project — Reagansoft portal');

  if (loading) return <LoadingBlock label="Loading project…" />;
  if (error || !data) return <ErrorBlock message={error || 'Project not found.'} onRetry={reload} />;

  const { project, tasks, files, messages, invoices, quotations, milestones, milestone_index: milestoneIndex } = data;
  const status = projectStatus(project.status);
  const balance = invoices.reduce((sum, invoice) => sum + Math.max(0, Number(invoice.total) - Number(invoice.amount_paid)), 0);
  const completedTasks = tasks.filter((task) => task.status === 'COMPLETED').length;

  async function sendMessage(event: React.FormEvent) {
    event.preventDefault();
    if (!message.trim()) return;
    setSending(true);
    try {
      await api(`client/projects/${project.id}/messages`, { method: 'POST', body: { message } });
      setMessage('');
      push('success', 'Message sent to the team.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not send the message.');
    } finally {
      setSending(false);
    }
  }

  async function uploadFile(event: React.ChangeEvent<HTMLInputElement>) {
    const file = event.target.files?.[0];
    if (!file) return;
    const form = new FormData();
    form.append('file', file);
    setUploading(true);
    try {
      await api(`client/projects/${project.id}/files`, { method: 'POST', body: form });
      push('success', 'File uploaded.');
      reload();
    } catch (err) {
      push('error', err instanceof ApiError ? err.message : 'Could not upload the file.');
    } finally {
      setUploading(false);
      event.target.value = '';
    }
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <Link to="/portal/projects" className="inline-flex items-center gap-1.5 text-[11.5px] font-semibold text-ink-muted transition hover:text-brand-700">
          <ArrowLeft className="h-3.5 w-3.5" /> All projects
        </Link>
        <span className="font-mono text-[10px] uppercase tracking-[0.18em] text-ink-faint">{project.ref_no}</span>
      </div>

      <div className="rounded-2xl border border-brand-100 bg-white p-5 sm:p-6">
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div className="min-w-0">
            <div className="flex flex-wrap items-center gap-2">
              <Badge tone={status.tone}>{status.label}</Badge>
              <Badge tone={priorityTone(project.priority)}>{project.priority} priority</Badge>
              <span className="text-[11px] text-ink-muted">{project.service ?? 'Custom project'}</span>
            </div>
            <h1 className="mt-2 font-display text-xl font-semibold tracking-tight text-navy-900 sm:text-2xl">{project.title}</h1>
            <p className="mt-1 text-[11.5px] text-ink-muted">Submitted {formatDate(project.submitted_at)} · updated {timeAgo(project.updated_at)}</p>
          </div>
          <div className="flex flex-wrap gap-2">
            <button type="button" onClick={() => setTab('messages')} className="btn-ghost btn-sm"><MessageSquare className="h-3.5 w-3.5" /> Message team</button>
            {balance > 0 && <button type="button" onClick={() => setTab('billing')} className="btn-primary btn-sm"><Wallet className="h-3.5 w-3.5" /> Pay {money(balance)}</button>}
          </div>
        </div>

        <div className="mt-5 grid gap-4 border-t border-brand-50 pt-4 sm:grid-cols-2 lg:grid-cols-4">
          <div>
            <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Progress</p>
            <div className="mt-1.5 flex items-center gap-2">
              <ProgressBar value={project.progress} className="flex-1" />
              <span className="font-mono text-[10px] font-semibold text-brand-700">{project.progress}%</span>
            </div>
          </div>
          <div>
            <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Target date</p>
            <p className="mt-1 inline-flex items-center gap-1.5 text-[12px] font-semibold text-navy-900"><CalendarDays className="h-3.5 w-3.5 text-brand-500" /> {formatDate(project.deadline)}</p>
          </div>
          <div>
            <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Tasks complete</p>
            <p className="mt-1 text-[12px] font-semibold text-navy-900">{completedTasks} / {tasks.length}</p>
          </div>
          <div>
            <p className="font-mono text-[8px] uppercase tracking-[0.16em] text-ink-faint">Outstanding</p>
            <p className="mt-1 text-[12px] font-semibold text-navy-900">{money(balance)}</p>
          </div>
        </div>
      </div>

      <div className="rounded-2xl border border-brand-100 bg-white p-5">
        <div className="flex items-center justify-between">
          <h2 className="font-display text-sm font-semibold text-navy-900">Delivery milestones</h2>
          {project.status === 'CANCELLED' && <Badge tone="red">Cancelled</Badge>}
        </div>
        <div className="no-scrollbar mt-4 flex gap-2 overflow-x-auto pb-1">
          {milestones.map((label, index) => {
            const done = project.status !== 'CANCELLED' && index < milestoneIndex;
            const current = project.status !== 'CANCELLED' && index === milestoneIndex;
            return (
              <div key={label} className={cn('min-w-[118px] flex-1 rounded-xl border px-3 py-2.5', done ? 'border-emerald-100 bg-emerald-50/70' : current ? 'border-brand-200 bg-brand-50' : 'border-brand-50 bg-white')}>
                <div className="flex items-center gap-1.5">
                  <span className={cn('grid h-4 w-4 place-items-center rounded-full text-[8px] font-bold', done ? 'bg-emerald-500 text-white' : current ? 'bg-brand-600 text-white' : 'bg-brand-100 text-brand-500')}>
                    {done ? <Check className="h-2.5 w-2.5" strokeWidth={3} /> : index + 1}
                  </span>
                  <span className={cn('text-[9.5px] font-semibold leading-tight', done ? 'text-emerald-800' : current ? 'text-brand-800' : 'text-ink-faint')}>{label}</span>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      <div className="no-scrollbar flex gap-1 overflow-x-auto rounded-xl border border-brand-100 bg-white p-1.5">
        {tabs.map((item) => (
          <button
            key={item.key}
            type="button"
            onClick={() => setTab(item.key)}
            className={cn('shrink-0 rounded-lg px-3.5 py-2 text-[12px] font-semibold transition', tab === item.key ? 'bg-brand-600 text-white' : 'text-ink-muted hover:bg-brand-50 hover:text-brand-700')}
          >
            {item.label}
          </button>
        ))}
      </div>

      {tab === 'overview' && (
        <div className="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
          <div className="space-y-4">
            <div className="rounded-2xl border border-brand-100 bg-white p-5">
              <h2 className="font-display text-sm font-semibold text-navy-900">Brief</h2>
              <p className="mt-2 whitespace-pre-line text-[12.5px] leading-relaxed text-ink-muted">{project.description}</p>
            </div>
            {project.requirements && (
              <div className="rounded-2xl border border-brand-100 bg-white p-5">
                <h2 className="font-display text-sm font-semibold text-navy-900">Requirements</h2>
                <p className="mt-2 whitespace-pre-line text-[12.5px] leading-relaxed text-ink-muted">{project.requirements}</p>
              </div>
            )}
            {project.deliverables && (
              <div className="rounded-2xl border border-brand-100 bg-white p-5">
                <h2 className="font-display text-sm font-semibold text-navy-900">Deliverables</h2>
                <ul className="mt-2 grid gap-1.5 sm:grid-cols-2">
                  {project.deliverables.split(/[,;\n]+/).map((item) => item.trim()).filter(Boolean).map((item) => (
                    <li key={item} className="flex items-start gap-2 text-[12px] text-ink-soft">
                      <CheckCircle2 className="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-500" /> {item}
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>

          <div className="space-y-4">
            <div className="rounded-2xl border border-brand-100 bg-white p-5">
              <h2 className="font-display text-sm font-semibold text-navy-900">Project details</h2>
              <dl className="mt-3 space-y-2.5 text-[12px]">
                {[
                  ['Reference', project.ref_no],
                  ['Service', project.service ?? 'Custom'],
                  ['Budget', project.budget ? money(project.budget) : 'To be confirmed'],
                  ['Priority', project.priority],
                  ['Started', formatDate(project.started_at)],
                  ['Completed', formatDate(project.completed_at)],
                ].map(([label, value]) => (
                  <div key={label} className="flex items-center justify-between gap-3">
                    <dt className="text-ink-muted">{label}</dt>
                    <dd className="text-right font-semibold capitalize text-navy-900">{value}</dd>
                  </div>
                ))}
              </dl>
              {project.website_url && (
                <a href={project.website_url} target="_blank" rel="noreferrer" className="btn-ghost btn-sm mt-4 w-full">
                  Visit website <ArrowRight className="h-3.5 w-3.5" />
                </a>
              )}
            </div>

            <div className="rounded-2xl border border-brand-100 bg-brand-50/50 p-5">
              <h2 className="font-display text-sm font-semibold text-navy-900">Need something changed?</h2>
              <p className="mt-1.5 text-[11.5px] leading-relaxed text-ink-muted">Send a message with the detail and we will confirm scope and timing.</p>
              <button type="button" onClick={() => setTab('messages')} className="btn-primary btn-sm mt-3 w-full">Message the team</button>
            </div>
          </div>
        </div>
      )}

      {tab === 'tasks' && (
        <div className="rounded-2xl border border-brand-100 bg-white p-5">
          <h2 className="font-display text-sm font-semibold text-navy-900">Work items</h2>
          {tasks.length === 0 ? (
            <EmptyState title="No tasks yet" message="Tasks appear once work is scheduled." />
          ) : (
            <div className="mt-4 space-y-2">
              {tasks.map((task) => {
                const meta = taskStatus(task.status);
                return (
                  <div key={task.id} className="rounded-xl border border-brand-100 p-3.5">
                    <div className="flex flex-wrap items-start justify-between gap-2">
                      <div>
                        <p className="text-[12.5px] font-semibold text-navy-900">{task.title}</p>
                        {task.description && <p className="mt-0.5 text-[11px] text-ink-muted">{task.description}</p>}
                      </div>
                      <Badge tone={meta.tone}>{meta.label}</Badge>
                    </div>
                    <div className="mt-2.5 flex items-center gap-3">
                      <ProgressBar value={task.progress} className="flex-1" />
                      <span className="font-mono text-[9.5px] text-brand-700">{task.progress}%</span>
                      {task.due_date && <span className="text-[10px] text-ink-faint">Due {formatDate(task.due_date)}</span>}
                    </div>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      )}

      {tab === 'files' && (
        <div className="rounded-2xl border border-brand-100 bg-white p-5">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h2 className="font-display text-sm font-semibold text-navy-900">Shared files</h2>
            <label className="btn-ghost btn-sm cursor-pointer">
              <Upload className="h-3.5 w-3.5" /> {uploading ? 'Uploading…' : 'Upload file'}
              <input type="file" className="hidden" onChange={uploadFile} disabled={uploading} />
            </label>
          </div>
          {files.length === 0 ? (
            <EmptyState icon={<Paperclip className="h-6 w-6" />} title="No files yet" message="Upload briefs, logos or documents here, or wait for the team to share deliverables." />
          ) : (
            <div className="mt-4 space-y-2">
              {files.map((file) => (
                <div key={file.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 px-3.5 py-3">
                  <div className="flex items-center gap-2.5">
                    <span className="grid h-8 w-8 place-items-center rounded-lg bg-brand-50 text-brand-600"><FileText className="h-4 w-4" /></span>
                    <div>
                      <p className="text-[12px] font-semibold text-navy-900">{file.original_name}</p>
                      <p className="text-[10px] text-ink-faint">{file.uploader ?? 'Team'} · {formatDate(file.created_at, true)}</p>
                    </div>
                  </div>
                  <a href={file.url} target="_blank" rel="noreferrer" className="btn-ghost btn-sm"><Download className="h-3.5 w-3.5" /> Download</a>
                </div>
              ))}
            </div>
          )}
        </div>
      )}

      {tab === 'messages' && (
        <div className="rounded-2xl border border-brand-100 bg-white p-5">
          <h2 className="font-display text-sm font-semibold text-navy-900">Project conversation</h2>
          <div className="mt-4 max-h-[420px] space-y-3 overflow-y-auto pr-1">
            {messages.length === 0 ? (
              <EmptyState icon={<MessageSquare className="h-6 w-6" />} title="No messages yet" message="Ask a question or share an update to start the conversation." />
            ) : (
              messages.map((item) => {
                const mine = item.sender_role === 'client';
                return (
                  <div key={item.id} className={cn('flex', mine ? 'justify-end' : 'justify-start')}>
                    <div className={cn('max-w-[82%] rounded-2xl px-3.5 py-2.5', mine ? 'bg-brand-600 text-white' : 'bg-brand-50 text-ink')}>
                      <p className={cn('text-[9.5px] font-semibold uppercase tracking-wider', mine ? 'text-white/60' : 'text-brand-700')}>{item.sender ?? (mine ? 'You' : 'Reagansoft')}</p>
                      <p className="mt-1 whitespace-pre-line text-[12px] leading-relaxed">{item.message}</p>
                      <p className={cn('mt-1 text-[9px]', mine ? 'text-white/50' : 'text-ink-faint')}>{formatDate(item.created_at, true)}</p>
                    </div>
                  </div>
                );
              })
            )}
          </div>
          <form onSubmit={sendMessage} className="mt-4 border-t border-brand-50 pt-4">
            <textarea className="input min-h-[80px]" value={message} onChange={(event) => setMessage(event.target.value)} placeholder="Write a message to the team…" />
            <div className="mt-2 flex justify-end">
              <button type="submit" disabled={sending || !message.trim()} className="btn-primary btn-sm">
                {sending ? 'Sending…' : 'Send message'} <Send className="h-3.5 w-3.5" />
              </button>
            </div>
          </form>
        </div>
      )}

      {tab === 'billing' && (
        <div className="space-y-4">
          <div className="rounded-2xl border border-brand-100 bg-white p-5">
            <div className="flex items-center justify-between">
              <h2 className="font-display text-sm font-semibold text-navy-900">Invoices</h2>
              <span className="text-[11px] font-semibold text-brand-700">{money(balance)} outstanding</span>
            </div>
            {invoices.length === 0 ? (
              <EmptyState icon={<ReceiptText className="h-6 w-6" />} title="No invoices yet" message="Invoices will appear here once work is approved." />
            ) : (
              <div className="mt-3 space-y-2">
                {invoices.map((invoice) => {
                  const meta = invoiceStatus(invoice.status);
                  const due = Math.max(0, Number(invoice.total) - Number(invoice.amount_paid));
                  return (
                    <div key={invoice.id} className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-100 px-3.5 py-3">
                      <div>
                        <div className="flex items-center gap-2">
                          <p className="font-mono text-[11px] font-semibold text-navy-900">{invoice.invoice_no}</p>
                          <Badge tone={meta.tone}>{meta.label}</Badge>
                        </div>
                        <p className="mt-0.5 text-[10.5px] text-ink-faint">Issued {formatDate(invoice.created_at)} · due {formatDate(invoice.due_date)}</p>
                      </div>
                      <div className="flex items-center gap-3">
                        <div className="text-right">
                          <p className="font-display text-[13px] font-semibold text-navy-900">{money(invoice.total)}</p>
                          {due > 0 && <p className="text-[10px] text-amber-700">{money(due)} due</p>}
                        </div>
                        <button type="button" className="btn-ghost btn-sm" onClick={() => setViewInvoice(invoice)}>View</button>
                        {due > 0 && invoice.status !== 'cancelled' && (
                          <button type="button" className="btn-primary btn-sm" onClick={() => setPayInvoice(invoice)}>Pay</button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          <div className="rounded-2xl border border-brand-100 bg-white p-5">
            <h2 className="font-display text-sm font-semibold text-navy-900">Quotations</h2>
            {quotations.length === 0 ? (
              <EmptyState icon={<FileText className="h-6 w-6" />} title="No quotations yet" message="A quotation will appear once your request has been reviewed." />
            ) : (
              <div className="mt-3 space-y-2">
                {quotations.map((quote) => {
                  const meta = quotationStatus(quote.status);
                  return (
                    <div key={quote.id} className="rounded-xl border border-brand-100 px-3.5 py-3">
                      <div className="flex flex-wrap items-center justify-between gap-2">
                        <div className="flex items-center gap-2">
                          <p className="font-mono text-[11px] font-semibold text-navy-900">{quote.quotation_no}</p>
                          <Badge tone={meta.tone}>{meta.label}</Badge>
                        </div>
                        <p className="font-display text-[13px] font-semibold text-navy-900">{money(quote.total)}</p>
                      </div>
                      <p className="mt-1 text-[10.5px] text-ink-faint">Issued {formatDate(quote.issued_on)} · expires {formatDate(quote.expiry_date)}</p>
                      {quote.items && quote.items.length > 0 && (
                        <ul className="mt-2 space-y-1 border-t border-brand-50 pt-2">
                          {quote.items.map((item) => (
                            <li key={item.id} className="flex justify-between gap-3 text-[11px] text-ink-soft">
                              <span>{item.description} × {Number(item.quantity)}</span>
                              <span className="font-medium">{money(Number(item.quantity) * Number(item.unit_price))}</span>
                            </li>
                          ))}
                        </ul>
                      )}
                      {quote.notes && <p className="mt-2 text-[10.5px] text-ink-muted">{quote.notes}</p>}
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          <div className="rounded-2xl border border-brand-100 bg-brand-50/50 p-5">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <p className="text-[12px] font-semibold text-navy-900">Ready to pay?</p>
                <p className="mt-0.5 text-[11px] text-ink-muted">Submit a payment reference and our team will confirm it.</p>
              </div>
              <Link to="/portal/billing" className="btn-ghost btn-sm">Billing details <ArrowRight className="h-3.5 w-3.5" /></Link>
            </div>
          </div>
        </div>
      )}

      <Modal open={Boolean(viewInvoice)} onClose={() => setViewInvoice(null)} title="Invoice detail" size="lg">
        {viewInvoice && <InvoiceView invoice={viewInvoice} showPayments={false} />}
      </Modal>

      <PaymentModal
        invoice={payInvoice}
        open={Boolean(payInvoice)}
        onClose={() => setPayInvoice(null)}
        onSuccess={reload}
      />
    </div>
  );
}
