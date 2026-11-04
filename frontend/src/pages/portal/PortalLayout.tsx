import { Link } from 'react-router-dom';
import { Bell, FolderKanban, LayoutDashboard, LifeBuoy, PlusCircle, ReceiptText, UserCog, Wallet } from 'lucide-react';
import { DashboardShell, type NavItem } from '../../components/DashboardShell';

const items: NavItem[] = [
  { to: '/portal', label: 'Overview', icon: LayoutDashboard, end: true },
  { to: '/portal/projects', label: 'My projects', icon: FolderKanban },
  { to: '/portal/request', label: 'New request', icon: PlusCircle },
  { to: '/portal/invoices', label: 'Invoices', icon: ReceiptText },
  { to: '/portal/notifications', label: 'Notifications', icon: Bell },
  { to: '/portal/billing', label: 'Billing details', icon: Wallet },
  { to: '/portal/profile', label: 'Account settings', icon: UserCog },
];

const footerItems: NavItem[] = [
  { to: '/contact', label: 'Get support', icon: LifeBuoy },
];

export function PortalLayout() {
  return <DashboardShell variant="portal" items={items} footerItems={footerItems} />;
}

export function PortalSupportLink() {
  return <Link to="/contact">Contact support</Link>;
}
