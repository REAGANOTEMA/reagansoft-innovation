import {
  Bell,
  BriefcaseBusiness,
  ChartColumn,
  ClipboardList,
  CreditCard,
  FileText,
  FolderKanban,
  Gauge,
  Inbox,
  ReceiptText,
  ScrollText,
  Settings,
  ShieldCheck,
  Users,
  Wallet,
} from 'lucide-react';
import { DashboardShell, type NavItem } from '../../components/DashboardShell';

const items: NavItem[] = [
  { to: '/console', label: 'Overview', icon: Gauge, end: true },
  { to: '/console/projects', label: 'Projects', icon: FolderKanban },
  { to: '/console/clients', label: 'Clients', icon: Users },
  { to: '/console/enquiries', label: 'Enquiries', icon: Inbox },
  { to: '/console/services', label: 'Services', icon: BriefcaseBusiness },
  { to: '/console/invoices', label: 'Invoices', icon: ReceiptText },
  { to: '/console/quotations', label: 'Quotations', icon: FileText },
  { to: '/console/payments', label: 'Payments', icon: CreditCard },
  { to: '/console/tasks', label: 'Tasks', icon: ClipboardList },
  { to: '/console/expenses', label: 'Expenses', icon: Wallet },
  { to: '/console/reports', label: 'Reports', icon: ChartColumn },
  { to: '/console/settings', label: 'Settings', icon: Settings, adminOnly: true },
  { to: '/console/audit', label: 'Audit log', icon: ScrollText, adminOnly: true },
  { to: '/console/users', label: 'Team & users', icon: ShieldCheck, adminOnly: true },
];

const footerItems: NavItem[] = [
  { to: '/console/notifications', label: 'Notifications', icon: Bell },
  { to: '/console/profile', label: 'My account', icon: ShieldCheck },
];

export function ConsoleLayout() {
  return <DashboardShell variant="console" items={items} footerItems={footerItems} />;
}
