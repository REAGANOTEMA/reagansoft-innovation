import type { ReactNode } from 'react';
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { APP_BASE } from './lib/env';
import { useAuth } from './lib/auth';
import { SiteLayout } from './components/SiteLayout';
import { ProtectedRoute } from './components/ProtectedRoute';
import { Home } from './pages/Home';
import { About } from './pages/About';
import { Services } from './pages/Services';
import { ServiceDetail } from './pages/ServiceDetail';
import { Portfolio } from './pages/Portfolio';
import { ProjectDetail } from './pages/ProjectDetail';
import { Pricing } from './pages/Pricing';
import { Process } from './pages/Process';
import { Faq } from './pages/Faq';
import { Contact } from './pages/Contact';
import { Login } from './pages/Login';
import { Register } from './pages/Register';
import { NotFound } from './pages/NotFound';
import { PortalLayout } from './pages/portal/PortalLayout';
import { PortalOverview } from './pages/portal/Overview';
import { PortalProjects } from './pages/portal/Projects';
import { PortalProjectDetail } from './pages/portal/ProjectDetail';
import { NewRequest } from './pages/portal/NewRequest';
import { PortalInvoices } from './pages/portal/Invoices';
import { PortalNotifications } from './pages/portal/Notifications';
import { PortalBilling } from './pages/portal/Billing';
import { PortalProfile } from './pages/portal/Profile';
import { ConsoleLayout } from './pages/console/ConsoleLayout';
import { ConsoleOverview } from './pages/console/Overview';
import { ConsoleProjects } from './pages/console/Projects';
import { ConsoleProjectDetail } from './pages/console/ProjectDetail';
import { ConsoleClients } from './pages/console/Clients';
import { ConsoleClientDetail } from './pages/console/ClientDetail';
import { ConsoleEnquiries } from './pages/console/Enquiries';
import { ConsoleServices } from './pages/console/Services';
import { ConsoleInvoices } from './pages/console/Invoices';
import { ConsoleQuotations } from './pages/console/Quotations';
import { ConsolePayments } from './pages/console/Payments';
import { ConsoleTasks } from './pages/console/Tasks';
import { ConsoleExpenses } from './pages/console/Expenses';
import { ConsoleReports } from './pages/console/Reports';
import { ConsoleSettings } from './pages/console/Settings';
import { ConsoleAudit } from './pages/console/Audit';
import { ConsoleUsers } from './pages/console/Users';
import { ConsoleNotifications } from './pages/console/Notifications';
import { ConsoleProfile } from './pages/console/Profile';

function AdminOnly({ children }: { children: ReactNode }) {
  const { user } = useAuth();
  if (user?.role !== 'admin') return <Navigate to="/console" replace />;
  return <>{children}</>;
}

export function App() {
  return (
    <BrowserRouter basename={APP_BASE || '/'}>
      <Routes>
        <Route element={<SiteLayout />}>
          <Route index element={<Home />} />
          <Route path="about" element={<About />} />
          <Route path="services" element={<Services />} />
          <Route path="services/:slug" element={<ServiceDetail />} />
          <Route path="portfolio" element={<Portfolio />} />
          <Route path="portfolio/:id" element={<ProjectDetail />} />
          <Route path="pricing" element={<Pricing />} />
          <Route path="process" element={<Process />} />
          <Route path="faq" element={<Faq />} />
          <Route path="contact" element={<Contact />} />
          <Route path="*" element={<NotFound />} />
        </Route>

        <Route path="login" element={<Login />} />
        <Route path="register" element={<Register />} />

        <Route
          path="portal"
          element={
            <ProtectedRoute roles={['client']}>
              <PortalLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<PortalOverview />} />
          <Route path="projects" element={<PortalProjects />} />
          <Route path="projects/:id" element={<PortalProjectDetail />} />
          <Route path="request" element={<NewRequest />} />
          <Route path="invoices" element={<PortalInvoices />} />
          <Route path="notifications" element={<PortalNotifications />} />
          <Route path="billing" element={<PortalBilling />} />
          <Route path="profile" element={<PortalProfile />} />
        </Route>

        <Route
          path="console"
          element={
            <ProtectedRoute roles={['admin', 'staff']}>
              <ConsoleLayout />
            </ProtectedRoute>
          }
        >
          <Route index element={<ConsoleOverview />} />
          <Route path="projects" element={<ConsoleProjects />} />
          <Route path="projects/:id" element={<ConsoleProjectDetail />} />
          <Route path="clients" element={<ConsoleClients />} />
          <Route path="clients/:id" element={<ConsoleClientDetail />} />
          <Route path="enquiries" element={<ConsoleEnquiries />} />
          <Route path="services" element={<ConsoleServices />} />
          <Route path="invoices" element={<ConsoleInvoices />} />
          <Route path="quotations" element={<ConsoleQuotations />} />
          <Route path="payments" element={<ConsolePayments />} />
          <Route path="tasks" element={<ConsoleTasks />} />
          <Route path="expenses" element={<ConsoleExpenses />} />
          <Route path="reports" element={<ConsoleReports />} />
          <Route path="settings" element={<AdminOnly><ConsoleSettings /></AdminOnly>} />
          <Route path="audit" element={<AdminOnly><ConsoleAudit /></AdminOnly>} />
          <Route path="users" element={<AdminOnly><ConsoleUsers /></AdminOnly>} />
          <Route path="notifications" element={<ConsoleNotifications />} />
          <Route path="profile" element={<ConsoleProfile />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
