import type { Tone } from './format';

export function projectStatus(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    NEW: { label: 'New request', tone: 'blue' },
    REVIEWING: { label: 'Reviewing', tone: 'cyan' },
    QUOTATION: { label: 'Quotation', tone: 'violet' },
    APPROVED: { label: 'Approved', tone: 'cyan' },
    IN_PROGRESS: { label: 'In progress', tone: 'blue' },
    WAITING_FOR_CLIENT: { label: 'Waiting on you', tone: 'amber' },
    TESTING: { label: 'Testing', tone: 'violet' },
    COMPLETED: { label: 'Completed', tone: 'green' },
    CANCELLED: { label: 'Cancelled', tone: 'red' },
  };
  return map[status] ?? { label: status.replace(/_/g, ' ').toLowerCase(), tone: 'gray' };
}

export function invoiceStatus(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    draft: { label: 'Draft', tone: 'gray' },
    sent: { label: 'Sent', tone: 'blue' },
    partially_paid: { label: 'Part paid', tone: 'amber' },
    paid: { label: 'Paid', tone: 'green' },
    overdue: { label: 'Overdue', tone: 'red' },
    cancelled: { label: 'Cancelled', tone: 'red' },
  };
  return map[status] ?? { label: status, tone: 'gray' };
}

export function quotationStatus(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    draft: { label: 'Draft', tone: 'gray' },
    sent: { label: 'Awaiting response', tone: 'blue' },
    accepted: { label: 'Accepted', tone: 'green' },
    rejected: { label: 'Declined', tone: 'red' },
    expired: { label: 'Expired', tone: 'amber' },
    cancelled: { label: 'Cancelled', tone: 'red' },
  };
  return map[status] ?? { label: status, tone: 'gray' };
}

export function paymentStatus(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    pending: { label: 'Awaiting confirmation', tone: 'amber' },
    confirmed: { label: 'Confirmed', tone: 'green' },
    rejected: { label: 'Not confirmed', tone: 'red' },
  };
  return map[status] ?? { label: status, tone: 'gray' };
}

export function taskStatus(status: string): { label: string; tone: Tone } {
  const map: Record<string, { label: string; tone: Tone }> = {
    TODO: { label: 'To do', tone: 'gray' },
    IN_PROGRESS: { label: 'In progress', tone: 'blue' },
    REVIEW: { label: 'Review', tone: 'violet' },
    COMPLETED: { label: 'Completed', tone: 'green' },
  };
  return map[status] ?? { label: status, tone: 'gray' };
}

export function priorityTone(priority: string): Tone {
  if (priority === 'urgent') return 'red';
  if (priority === 'high') return 'amber';
  if (priority === 'low') return 'gray';
  return 'blue';
}

export function paymentMethod(method: string): string {
  const map: Record<string, string> = {
    mtn_momo: 'MTN Mobile Money',
    airtel_money: 'Airtel Money',
    bank: 'Bank transfer',
    cash: 'Cash',
    other: 'Other',
  };
  return map[method] ?? method;
}
