import type { ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../lib/auth';
import { LogoAssembly } from './UI';

export function ProtectedRoute({ roles, children }: { roles: Array<'admin' | 'staff' | 'client'>; children: ReactNode }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="relative grid min-h-screen place-items-center overflow-hidden bg-navy-950">
        <div className="pointer-events-none absolute inset-0 bg-grid-dark opacity-25" />
        <div className="lens-flare pointer-events-none absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 rounded-full" />
        <div className="relative flex flex-col items-center gap-5 text-center text-sm font-medium text-white/70">
          <LogoAssembly width={180} />
          <div>
            <p className="font-display text-base font-semibold text-white">Preparing your workspace</p>
            <p className="mt-1 font-mono text-[9px] uppercase tracking-[0.24em] text-brand-300">Reagansoft Innovation</p>
          </div>
        </div>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace state={{ from: location.pathname }} />;
  }

  if (!roles.includes(user.role)) {
    return <Navigate to={user.role === 'client' ? '/portal' : '/console'} replace />;
  }

  return <>{children}</>;
}
