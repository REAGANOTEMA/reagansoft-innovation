export interface User {
  id: number;
  full_name: string;
  email: string;
  phone?: string | null;
  role: 'admin' | 'staff' | 'client';
  company?: string | null;
  address?: string | null;
  avatar?: string | null;
  payer_type?: 'business' | 'individual';
  tax_id?: string | null;
  national_id?: string | null;
  country?: string | null;
  city?: string | null;
  billing_terms_at?: string | null;
  active?: number;
  last_login_at?: string | null;
  created_at?: string;
}

export interface Settings {
  company_name: string;
  company_tagline: string;
  company_about: string;
  company_email: string;
  company_phone: string;
  company_whatsapp: string;
  company_address: string;
  company_founder: string;
  company_hours?: string;
  company_map_url?: string;
  company_fb?: string;
  company_linkedin?: string;
  company_x?: string;
  currency: string;
  project_deposit?: string;
  registration_open?: string;
  seo_keywords?: string;
  site_announcement?: string;
  support_email?: string;
  newsletter_enabled?: string;
  tax_percent?: string;
  invoices_due_days?: string;
  payment_mtn_number?: string;
  payment_airtel_number?: string;
  payment_bank_details?: string;
  payment_gateway_status?: string;
}

export interface Service {
  id: number;
  name: string;
  slug: string;
  description: string;
  price_min: number;
  price_max: number | null;
  currency: string;
  pricing_type: 'range' | 'starting_from' | 'contact_for_quote';
  price_note: string;
  price_display: string;
  price_short: string;
  features: string[];
  delivery_days: number | null;
  icon: string;
  status?: 'active' | 'inactive';
  sort_order?: number;
}

export interface Project {
  id: number;
  ref_no: string;
  client_id: number;
  service_id: number | null;
  assigned_to: number | null;
  title: string;
  description: string;
  requirements?: string | null;
  website_url?: string | null;
  deliverables?: string | null;
  cover_art?: string | null;
  budget?: number | null;
  priority: 'low' | 'normal' | 'high' | 'urgent';
  status: string;
  progress: number;
  deadline?: string | null;
  submitted_at?: string;
  updated_at?: string;
  completed_at?: string | null;
  service?: string | null;
  service_slug?: string | null;
  client_name?: string;
  company?: string | null;
  client_email?: string;
  client_phone?: string;
  assignee?: string | null;
}

export interface SiteData {
  settings: Settings;
  services: Service[];
  portfolio: Project[];
  stats: { projects: number; completed: number; clients: number; services: number };
  csrf: string;
  user: User | null;
  server_time: string;
}

export interface Notification {
  id: number;
  user_id: number;
  title: string;
  message?: string;
  type: string;
  is_read: number;
  related_project_id?: number | null;
  created_at: string;
}

export interface Invoice {
  id: number;
  invoice_no: string;
  project_id: number;
  client_id: number;
  due_date?: string | null;
  subtotal: number;
  discount: number;
  tax_percent: number;
  total: number;
  amount_paid: number;
  status: string;
  is_deposit: number;
  notes?: string | null;
  created_at?: string;
  project?: string;
  ref_no?: string;
  client_name?: string;
  items?: InvoiceItem[];
  payments?: Payment[];
}

export interface InvoiceItem {
  id: number;
  description: string;
  quantity: number;
  unit_price: number;
}

export interface Payment {
  id: number;
  invoice_id: number;
  amount: number;
  method: string;
  reference?: string | null;
  status: string;
  notes?: string | null;
  created_at: string;
  confirmed_at?: string | null;
  invoice_no?: string;
  client_name?: string;
  project?: string;
  ref_no?: string;
}

export interface Task {
  id: number;
  project_id: number;
  assigned_to?: number | null;
  title: string;
  description?: string | null;
  status: 'TODO' | 'IN_PROGRESS' | 'REVIEW' | 'COMPLETED';
  progress: number;
  priority: 'low' | 'medium' | 'high';
  due_date?: string | null;
  project?: string;
  ref_no?: string;
  assignee?: string | null;
}

export interface ProjectFile {
  id: number;
  original_name: string;
  mime_type: string;
  size_bytes: number;
  created_at: string;
  uploader?: string;
  url: string;
}

export interface Message {
  id: number;
  sender_id: number;
  sender_role: string;
  message: string;
  is_internal: number;
  created_at: string;
  sender?: string;
}

export interface Quotation {
  id: number;
  quotation_no: string;
  project_id: number;
  client_id: number;
  issued_on: string;
  expiry_date?: string | null;
  subtotal: number;
  discount: number;
  tax_percent: number;
  total: number;
  status: string;
  notes?: string | null;
  terms?: string | null;
  client_name?: string;
  project?: string;
  ref_no?: string;
  items?: InvoiceItem[];
}

export interface Enquiry {
  id: number;
  full_name: string;
  email: string;
  phone?: string | null;
  company?: string | null;
  subject?: string | null;
  message: string;
  is_read: number;
  created_at: string;
}

export interface Expense {
  id: number;
  category: string;
  description: string;
  amount: number;
  currency: string;
  expense_date: string;
  project_id?: number | null;
  reference?: string | null;
  notes?: string | null;
  project?: string | null;
  recorder?: string | null;
  created_at: string;
}

export interface AuditLog {
  id: number;
  user_id?: number | null;
  action: string;
  entity_type?: string | null;
  entity_id?: number | null;
  details?: string | null;
  ip_address?: string | null;
  created_at: string;
  full_name?: string | null;
}

export interface PaginationMeta {
  total: number;
  page: number;
  per_page: number;
  total_pages: number;
  offset: number;
}
