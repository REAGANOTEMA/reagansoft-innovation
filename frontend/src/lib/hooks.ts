import { useCallback, useEffect, useRef, useState } from 'react';
import { apiGet, ApiError } from './api';

interface ApiState<T> {
  data: T | null;
  meta: Record<string, unknown> | undefined;
  loading: boolean;
  error: string;
  reload: () => void;
}

export function useApi<T>(path: string | null, query?: Record<string, string | number | undefined | null>): ApiState<T> {
  const [data, setData] = useState<T | null>(null);
  const [meta, setMeta] = useState<Record<string, unknown> | undefined>(undefined);
  const [loading, setLoading] = useState(Boolean(path));
  const [error, setError] = useState('');
  const [tick, setTick] = useState(0);
  const queryKey = JSON.stringify(query ?? {});
  const controllerRef = useRef<AbortController | null>(null);

  useEffect(() => {
    if (!path) {
      setLoading(false);
      return;
    }
    controllerRef.current?.abort();
    const controller = new AbortController();
    controllerRef.current = controller;
    setLoading(true);
    setError('');

    apiGet<T>(path, query, controller.signal)
      .then((res) => {
        if (controller.signal.aborted) return;
        setData(res.data);
        setMeta(res.meta);
      })
      .catch((err: unknown) => {
        if (controller.signal.aborted) return;
        setError(err instanceof ApiError ? err.message : 'Something went wrong.');
      })
      .finally(() => {
        if (!controller.signal.aborted) setLoading(false);
      });

    return () => controller.abort();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [path, queryKey, tick]);

  const reload = useCallback(() => setTick((value) => value + 1), []);

  return { data, meta, loading, error, reload };
}

export function useDebounced<T>(value: T, delay = 350): T {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const timer = window.setTimeout(() => setDebounced(value), delay);
    return () => window.clearTimeout(timer);
  }, [value, delay]);
  return debounced;
}
