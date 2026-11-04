import { Link } from 'react-router-dom';
import { ArrowLeft, Compass, Home } from 'lucide-react';
import { usePageMeta } from '../lib/seo';
import { Logo } from '../components/Logo';

export function NotFound() {
  usePageMeta('Page not found — Reagansoft Innovation');
  return (
    <div className="relative flex min-h-[70vh] items-center justify-center overflow-hidden bg-navy-950 px-6 py-20 text-white">
      <div className="aurora" />
      <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-30" />
      <div className="relative w-full max-w-lg text-center">
        <div className="flex justify-center"><Logo dark /></div>
        <p className="mt-10 font-mono text-[11px] uppercase tracking-[0.24em] text-brand-300">Error 404</p>
        <h1 className="h1 mt-3 text-white">This page took a wrong turn.</h1>
        <p className="mx-auto mt-4 max-w-md text-sm leading-relaxed text-white/60">
          The link may be outdated, or the page has moved. Use one of the routes below to get back on track.
        </p>
        <div className="mt-8 flex flex-wrap justify-center gap-3">
          <Link to="/" className="btn-primary"><Home className="h-4 w-4" /> Back home</Link>
          <Link to="/services" className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10"><Compass className="h-4 w-4" /> Browse services</Link>
          <Link to="/contact" className="btn border border-white/20 bg-white/5 text-white hover:bg-white/10"><ArrowLeft className="h-4 w-4" /> Contact us</Link>
        </div>
      </div>
    </div>
  );
}
