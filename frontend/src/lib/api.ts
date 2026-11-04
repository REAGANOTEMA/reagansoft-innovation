import { API_BASE } from './env';

export interface ApiEnvelope<T> {
  ok: boolean;
  data: T;
  meta?: Record<string, unknown>;
  error?: string;
  errors?: Record<string, string | string[]>;
}

export class ApiError extends Error {
  status: number;
  errors: Record<string, string | string[]>;

  constructor(message: string, status = 0, errors: Record<string, string | string[]> = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

let csrfToken = '';

export function setCsrf(token: string): void {
  csrfToken = token;
}

export function getCsrf(): string {
  return csrfToken;
}

type Options = {
  method?: 'GET' | 'POST' | 'PATCH' | 'PUT' | 'DELETE';
  body?: unknown;
  query?: Record<string, string | number | undefined | null>;
  signal?: AbortSignal;
};

export async function api<T = unknown>(path: string, options: Options = {}): Promise<{ data: T; meta?: Record<string, unknown> }> {
  const method = options.method ?? 'GET';
  let url = `${API_BASE}/${path.replace(/^\//, '')}`;
  if (options.query) {
    const params = new URLSearchParams();
    Object.entries(options.query).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') params.set(key, String(value));
    });
    const qs = params.toString();
    if (qs) url += `?${qs}`;
  }

  const headers: Record<string, string> = { Accept: 'application/json' };
  if (csrfToken && method !== 'GET') headers['X-CSRF-Token'] = csrfToken;

  let body: BodyInit | undefined;
  if (options.body instanceof FormData) {
    body = options.body;
  } else if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(options.body);
  }

  let response: Response;
  try {
    response = await fetch(url, {
      method,
      headers,
      body,
      credentials: 'same-origin',
      signal: options.signal,
    });
  } catch (error) {
    if ((error as Error).name === 'AbortError') throw error;
    throw new ApiError('Network unavailable. Check your connection and try again.', 0);
  }

  let payload: ApiEnvelope<T> | null = null;
  const text = await response.text();
  try {
    payload = text ? (JSON.parse(text) as ApiEnvelope<T>) : null;
  } catch {
    throw new ApiError('The server returned an unexpected response.', response.status);
  }

  if (!response.ok || !payload || payload.ok === false) {
    const message = payload?.error || `Request failed (${response.status}).`;
    throw new ApiError(message, response.status, payload?.errors ?? {});
  }

  return { data: payload.data, meta: payload.meta };
}

export async function apiGet<T>(path: string, query?: Options['query'], signal?: AbortSignal): Promise<{ data: T; meta?: Record<string, unknown> }> {
  return api<T>(path, { method: 'GET', query, signal });
}
