import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { api, getCsrf, setCsrf } from './api';
import type { User } from './types';

interface AuthState {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<User>;
  register: (payload: Record<string, unknown>) => Promise<User>;
  logout: () => Promise<void>;
  refresh: () => Promise<void>;
  setUser: (user: User | null) => void;
}

const AuthContext = createContext<AuthState | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUserState] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  const refresh = useCallback(async () => {
    try {
      const res = await api<{ user: User | null; csrf: string }>('auth/session');
      setCsrf(res.data.csrf);
      setUserState(res.data.user);
    } catch {
      setUserState(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  const login = useCallback(async (email: string, password: string) => {
    if (!getCsrf()) await refresh();
    const res = await api<{ user: User; csrf: string }>('auth/login', {
      method: 'POST',
      body: { email, password },
    });
    setCsrf(res.data.csrf);
    setUserState(res.data.user);
    return res.data.user;
  }, [refresh]);

  const register = useCallback(async (payload: Record<string, unknown>) => {
    if (!getCsrf()) await refresh();
    const res = await api<{ user: User; csrf: string }>('auth/register', {
      method: 'POST',
      body: payload,
    });
    setCsrf(res.data.csrf);
    setUserState(res.data.user);
    return res.data.user;
  }, [refresh]);

  const logout = useCallback(async () => {
    try {
      await api('auth/logout', { method: 'POST' });
    } catch {
      /* The local session is cleared even if the server call fails. */
    }
    setUserState(null);
  }, []);

  const value = useMemo<AuthState>(
    () => ({
      user,
      loading,
      login,
      register,
      logout,
      refresh,
      setUser: setUserState,
    }),
    [user, loading, login, register, logout, refresh],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthState {
  const context = useContext(AuthContext);
  if (!context) throw new Error('useAuth must be used inside AuthProvider');
  return context;
}
