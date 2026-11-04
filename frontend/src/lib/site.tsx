import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { api, setCsrf } from './api';
import type { Project, Service, Settings, SiteData } from './types';

const fallbackSettings: Settings = {
  company_name: 'Reagansoft Innovation Limited',
  company_tagline: 'Innovating today for a smarter tomorrow.',
  company_about:
    'Reagansoft Innovation Limited is a software company based in Jinja, Uganda. We design and build websites, business systems, ecommerce stores and custom software for organisations across the country — and we stay around to look after them after launch.',
  company_email: 'info@reagansoftinnovation.com',
  company_phone: '+256772514889',
  company_whatsapp: '+256730314979',
  company_address: 'Jinja, Uganda',
  company_founder: 'Reagan Otema',
  company_hours: 'Mon – Fri, 8:00am – 6:00pm EAT',
  company_map_url: 'https://www.google.com/maps/search/?api=1&query=Jinja%2C+Uganda',
  currency: 'UGX',
  registration_open: '1',
  newsletter_enabled: '1',
};

interface SiteContextValue {
  settings: Settings;
  services: Service[];
  portfolio: Project[];
  stats: SiteData['stats'];
  loading: boolean;
}

const SiteContext = createContext<SiteContextValue>({
  settings: fallbackSettings,
  services: [],
  portfolio: [],
  stats: { projects: 0, completed: 0, clients: 0, services: 0 },
  loading: true,
});

export function SiteProvider({ children }: { children: ReactNode }) {
  const [data, setData] = useState<SiteData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let alive = true;
    api<SiteData>('public/site')
      .then((res) => {
        if (!alive) return;
        setData(res.data);
        if (res.data.csrf) setCsrf(res.data.csrf);
      })
      .catch(() => {
        /* The fallback content keeps the marketing site usable offline. */
      })
      .finally(() => {
        if (alive) setLoading(false);
      });
    return () => {
      alive = false;
    };
  }, []);

  const value = useMemo<SiteContextValue>(
    () => ({
      settings: { ...fallbackSettings, ...(data?.settings ?? {}) },
      services: data?.services ?? [],
      portfolio: data?.portfolio ?? [],
      stats: data?.stats ?? { projects: 0, completed: 0, clients: 0, services: 0 },
      loading,
    }),
    [data, loading],
  );

  return <SiteContext.Provider value={value}>{children}</SiteContext.Provider>;
}

export function useSite(): SiteContextValue {
  return useContext(SiteContext);
}
