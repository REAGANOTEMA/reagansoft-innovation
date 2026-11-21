import { Link } from 'react-router-dom';
import { MessageSquare } from 'lucide-react';
import { useApi } from '../../lib/hooks';
import { usePageMeta } from '../../lib/seo';
import { formatDate, timeAgo } from '../../lib/format';
import { Badge, EmptyState, ErrorBlock, LoadingBlock, PageHeader } from '../../components/UI';

interface ClientMessage {
  id: number;
  project_id: number;
  ref_no: string;
  project_title: string;
  sender: string | null;
  sender_role: string;
  message: string;
  created_at: string;
}

export function PortalMessages() {
  usePageMeta('Messages, Reagansoft client portal');
  const { data, loading, error, reload } = useApi<{ messages: ClientMessage[] }>('client/messages');

  if (loading) return <LoadingBlock label="Loading conversations…" />;
  if (error || !data) return <ErrorBlock message={error || 'Could not load messages.'} onRetry={reload} />;

  return (
    <div className="space-y-6">
      <PageHeader
        eyebrow="Communication"
        title="Messages"
        description="Every conversation with the team, across all your projects, in one place."
      />
      {data.messages.length === 0 ? (
        <EmptyState
          icon={<MessageSquare className="h-6 w-6" />}
          title="No messages yet"
          message="Open a project and send a message to start a conversation with the team."
        />
      ) : (
        <div className="space-y-3">
          {data.messages.map((message) => (
            <article key={message.id} className="rounded-2xl border border-brand-100 bg-white p-5">
              <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap items-center gap-2">
                  <Link
                    to={`/portal/projects/${message.project_id}`}
                    className="font-mono text-[11px] font-semibold text-brand-700 hover:underline"
                  >
                    {message.ref_no}
                  </Link>
                  <Badge tone={message.sender_role === 'client' ? 'blue' : 'green'}>
                    {message.sender || message.sender_role}
                  </Badge>
                </div>
                <span className="text-[10px] text-ink-faint" title={formatDate(message.created_at, true)}>
                  {timeAgo(message.created_at)}
                </span>
              </div>
              <p className="mt-1 text-[11px] font-semibold text-navy-900">{message.project_title}</p>
              <p className="mt-2 whitespace-pre-line text-[12.5px] leading-relaxed text-ink-soft">{message.message}</p>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
