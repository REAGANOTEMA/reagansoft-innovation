import type { ReactNode } from 'react';
import { Navigate, useLocation } from 'react-router-dom';
import { useAuth } from '../lib/auth';
import { Spinner } from './UI';

export function ProtectedRoute({ roles, children }: { roles: Array<'admin' | 'staff' | 'client'>; children: ReactNode }) {
  const { user, loading } = useAuth();
  const location = useLocation();

  if (loading) {
    return (
      <div className="grid min-h-screen place-items-center bg-brand-50/50">
        <div className="flex flex-col items-center gap-3 text-sm font-medium text-ink-muted">
          <Spinner className="h-7 w-7" />
          Preparing your workspace…
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
