import { createContext, useCallback, useContext, useMemo, useState, type ReactNode } from 'react';
import { CheckCircle2, Info, X, XCircle } from 'lucide-react';

type ToastType = 'success' | 'error' | 'info';
interface ToastItem {
  id: number;
  type: ToastType;
  message: string;
}

interface ToastContextValue {
  push: (type: ToastType, message: string) => void;
}

const ToastContext = createContext<ToastContextValue | null>(null);

export function ToastProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<ToastItem[]>([]);

  const push = useCallback((type: ToastType, message: string) => {
    const id = Date.now() + Math.random();
    setItems((current) => [...current, { id, type, message }]);
    window.setTimeout(() => {
      setItems((current) => current.filter((item) => item.id !== id));
    }, 5200);
  }, []);

  const value = useMemo(() => ({ push }), [push]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className="pointer-events-none fixed inset-x-4 top-4 z-[100] flex flex-col items-end gap-2 sm:left-auto sm:right-6 sm:top-6">
        {items.map((item) => (
          <div
            key={item.id}
            className={`pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border p-4 shadow-lift backdrop-blur ${
              item.type === 'success'
                ? 'border-emerald-200 bg-emerald-50/95 text-emerald-900'
                : item.type === 'error'
                  ? 'border-rose-200 bg-rose-50/95 text-rose-900'
                  : 'border-brand-200 bg-white/95 text-navy-900'
            }`}
            role="status"
          >
            {item.type === 'success' ? (
              <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
            ) : item.type === 'error' ? (
              <XCircle className="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
            ) : (
              <Info className="mt-0.5 h-5 w-5 shrink-0 text-brand-600" />
            )}
            <p className="flex-1 text-sm font-medium leading-snug">{item.message}</p>
            <button
              type="button"
              aria-label="Dismiss notification"
              onClick={() => setItems((current) => current.filter((entry) => entry.id !== item.id))}
              className="rounded-full p-1 opacity-60 transition hover:bg-black/5 hover:opacity-100"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export function useToast(): ToastContextValue {
  const context = useContext(ToastContext);
  if (!context) throw new Error('useToast must be used inside ToastProvider');
  return context;
}
