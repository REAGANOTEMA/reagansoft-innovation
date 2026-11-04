import {
  BookOpen,
  Boxes,
  BrainCircuit,
  Building2,
  Cloud,
  Code2,
  Cpu,
  Gamepad2,
  Globe2,
  Layers3,
  Link2,
  Lock,
  MessageSquare,
  Palette,
  ReceiptText,
  Server,
  ShieldCheck,
  ShoppingCart,
  Smartphone,
  Sparkles,
  Stethoscope,
  GraduationCap,
  Hotel,
  Sprout,
  Truck,
  type LucideIcon,
} from 'lucide-react';
import { cn } from '../lib/cn';

const iconMap: Record<string, LucideIcon> = {
  globe: Globe2,
  cart: ShoppingCart,
  cpu: Cpu,
  'code-s': Code2,
  mobile: Smartphone,
  receipt: ReceiptText,
  layers: Layers3,
  palette: Palette,
  shield: ShieldCheck,
  gamepad: Gamepad2,
  book: BookOpen,
  chat: MessageSquare,
  link: Link2,
  lock: Lock,
  cloud: Cloud,
  server: Server,
  ai: BrainCircuit,
  boxes: Boxes,
  sparkles: Sparkles,
};

export function ServiceGlyph({ name, className }: { name: string; className?: string }) {
  const Icon = iconMap[name] ?? Code2;
  return <Icon className={cn('h-5 w-5', className)} strokeWidth={1.7} aria-hidden="true" />;
}

export function HeroVisual() {
  return (
    <div className="relative mx-auto w-full max-w-[560px] select-none" aria-hidden="true">
      <div className="absolute -left-8 top-10 h-40 w-40 rounded-full bg-brand-400/25 blur-3xl" />
      <div className="absolute -right-6 bottom-8 h-48 w-48 rounded-full bg-cyan-300/20 blur-3xl" />

      <div className="relative overflow-hidden rounded-[28px] border border-white/12 bg-navy-900/90 p-3 shadow-[0_50px_120px_-40px_rgba(3,26,46,.9)] backdrop-blur">
        <div className="overflow-hidden rounded-[20px] border border-white/10 bg-[#071f36]">
          <div className="flex items-center gap-2 border-b border-white/10 bg-white/[0.04] px-4 py-2.5">
            <span className="h-2 w-2 rounded-full bg-rose-400/80" />
            <span className="h-2 w-2 rounded-full bg-amber-300/80" />
            <span className="h-2 w-2 rounded-full bg-emerald-400/80" />
            <span className="ml-3 rounded-md bg-white/5 px-2 py-1 font-mono text-[9px] tracking-wide text-white/45">
              console.reagansoft.ug / operations
            </span>
            <span className="ml-auto flex items-center gap-1 rounded-full bg-emerald-400/10 px-2 py-1 font-mono text-[8px] uppercase tracking-widest text-emerald-300">
              <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" /> live
            </span>
          </div>

          <div className="grid grid-cols-[52px_1fr] gap-0">
            <div className="flex flex-col items-center gap-3 border-r border-white/10 bg-white/[0.02] py-4">
              {[Layers3, Boxes, Cpu, ShieldCheck, Cloud].map((Icon, index) => (
                <span
                  key={index}
                  className={cn(
                    'grid h-7 w-7 place-items-center rounded-lg',
                    index === 0 ? 'bg-brand-500 text-white' : 'text-white/30',
                  )}
                >
                  <Icon className="h-3.5 w-3.5" />
                </span>
              ))}
            </div>

            <div className="p-3.5">
              <div className="flex items-end justify-between">
                <div>
                  <p className="font-mono text-[8px] uppercase tracking-[0.2em] text-brand-300">Executive overview</p>
                  <p className="mt-1 font-display text-sm font-semibold text-white">Delivery performance</p>
                </div>
                <div className="rounded-md bg-white/5 px-2 py-1 font-mono text-[8px] text-white/40">Q4 · 2026</div>
              </div>

              <div className="mt-3 grid grid-cols-3 gap-2">
                {[
                  { label: 'Active', value: '24', tone: 'text-brand-300' },
                  { label: 'Revenue', value: 'UGX 86M', tone: 'text-emerald-300' },
                  { label: 'Uptime', value: '99.98%', tone: 'text-cyan-300' },
                ].map((item) => (
                  <div key={item.label} className="rounded-lg border border-white/8 bg-white/[0.035] p-2">
                    <p className="font-mono text-[7px] uppercase tracking-widest text-white/35">{item.label}</p>
                    <p className={cn('mt-1 font-display text-[11px] font-semibold', item.tone)}>{item.value}</p>
                  </div>
                ))}
              </div>

              <div className="mt-3 rounded-xl border border-white/8 bg-white/[0.03] p-3">
                <div className="flex items-center justify-between">
                  <p className="font-mono text-[8px] uppercase tracking-widest text-white/40">Sprint velocity</p>
                  <span className="font-mono text-[8px] text-emerald-300">+18.4%</span>
                </div>
                <div className="mt-2 flex h-20 items-end gap-1.5">
                  {[38, 52, 44, 66, 58, 78, 70, 88, 76, 94, 84, 100].map((height, index) => (
                    <span
                      key={index}
                      className="flex-1 rounded-t-[3px] bg-gradient-to-t from-brand-600/40 to-brand-400"
                      style={{ height: `${height}%`, opacity: 0.45 + index * 0.045 }}
                    />
                  ))}
                </div>
              </div>

              <div className="mt-3 space-y-1.5">
                {[
                  { name: 'School management platform', pct: 92, color: 'bg-emerald-400' },
                  { name: 'Hospital records system', pct: 74, color: 'bg-brand-400' },
                  { name: 'Hotel booking engine', pct: 58, color: 'bg-cyan-400' },
                ].map((row) => (
                  <div key={row.name} className="flex items-center gap-2 rounded-lg bg-white/[0.03] px-2 py-1.5">
                    <span className={cn('h-1.5 w-1.5 rounded-full', row.color)} />
                    <span className="flex-1 truncate font-mono text-[8px] text-white/55">{row.name}</span>
                    <div className="h-1 w-14 overflow-hidden rounded-full bg-white/10">
                      <span className={cn('block h-full rounded-full', row.color)} style={{ width: `${row.pct}%` }} />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Floating status card */}
      <div className="absolute -left-4 top-[22%] hidden w-[172px] rounded-2xl border border-white/15 bg-white/95 p-3 shadow-lift backdrop-blur sm:block animate-float-slow">
        <div className="flex items-center gap-2">
          <span className="grid h-7 w-7 place-items-center rounded-lg bg-emerald-100 text-emerald-600">
            <ShieldCheck className="h-4 w-4" />
          </span>
          <div>
            <p className="text-[10px] font-bold text-navy-900">Deployment secure</p>
            <p className="font-mono text-[8px] text-ink-faint">CI · passed in 42s</p>
          </div>
        </div>
        <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-emerald-100">
          <div className="h-full w-[96%] rounded-full bg-emerald-500" />
        </div>
      </div>

      {/* Phone mockup */}
      <div className="absolute -bottom-8 -right-3 hidden w-[126px] rounded-[22px] border-[5px] border-navy-950 bg-navy-950 shadow-[0_40px_80px_-30px_rgba(3,26,46,.9)] sm:block animate-float">
        <div className="overflow-hidden rounded-[16px] bg-[#071f36]">
          <div className="mx-auto mt-1.5 h-1 w-8 rounded-full bg-white/20" />
          <div className="p-2.5">
            <p className="font-mono text-[7px] uppercase tracking-widest text-brand-300">Client portal</p>
            <p className="mt-1 font-display text-[10px] font-semibold text-white">Project progress</p>
            <div className="mt-2 rounded-lg bg-white/5 p-2">
              <div className="flex items-center justify-between">
                <span className="font-mono text-[7px] text-white/45">RSI-2026-00007</span>
                <span className="rounded-full bg-emerald-400/15 px-1.5 py-0.5 font-mono text-[6px] uppercase text-emerald-300">done</span>
              </div>
              <p className="mt-1 text-[8px] font-medium text-white/75">School platform</p>
              <div className="mt-1.5 h-1 overflow-hidden rounded-full bg-white/10">
                <div className="h-full w-full rounded-full bg-emerald-400" />
              </div>
            </div>
            <div className="mt-2 grid grid-cols-2 gap-1.5">
              {[1, 2, 3, 4].map((item) => (
                <div key={item} className="h-8 rounded-lg bg-white/[0.06]" />
              ))}
            </div>
            <div className="mt-2 h-6 rounded-lg bg-brand-500" />
          </div>
        </div>
      </div>

      <div className="absolute -right-5 top-4 hidden items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 backdrop-blur md:flex animate-float">
        <BrainCircuit className="h-3.5 w-3.5 text-brand-300" />
        <span className="font-mono text-[8px] uppercase tracking-widest text-white/70">AI-assisted</span>
      </div>
    </div>
  );
}

export function ProjectCover({ cover, className }: { cover?: string | null; className?: string }) {
  const key = (cover ?? 'systems').toLowerCase();
  const palette: Record<string, { from: string; to: string; accent: string }> = {
    education: { from: '#0b3b63', to: '#0a6fb5', accent: '#7dd3fc' },
    hospitality: { from: '#3b2a12', to: '#b45309', accent: '#fcd34d' },
    agriculture: { from: '#12351f', to: '#15803d', accent: '#86efac' },
    commerce: { from: '#2e1065', to: '#7c3aed', accent: '#c4b5fd' },
    health: { from: '#083344', to: '#0e7490', accent: '#67e8f9' },
    systems: { from: '#062a4a', to: '#0a7bce', accent: '#93c5fd' },
  };
  const colors = palette[key] ?? palette.systems;
  const Icon = key === 'education' ? GraduationCap : key === 'hospitality' ? Hotel : key === 'agriculture' ? Sprout : key === 'health' ? Stethoscope : key === 'commerce' ? ShoppingCart : Boxes;

  return (
    <div
      className={cn('relative overflow-hidden rounded-2xl', className)}
      style={{ background: `linear-gradient(135deg, ${colors.from}, ${colors.to})` }}
      aria-hidden="true"
    >
      <div className="absolute inset-0 bg-grid-dark opacity-60" />
      <div className="absolute -right-8 -top-8 h-32 w-32 rounded-full blur-2xl" style={{ background: `${colors.accent}33` }} />
      <svg viewBox="0 0 400 240" className="relative h-full w-full" role="presentation">
        <defs>
          <linearGradient id={`glass-${key}`} x1="0" x2="1" y1="0" y2="1">
            <stop offset="0" stopColor="#ffffff" stopOpacity="0.2" />
            <stop offset="1" stopColor="#ffffff" stopOpacity="0.05" />
          </linearGradient>
        </defs>
        <g transform="translate(40 34)">
          <rect width="250" height="160" rx="14" fill={`url(#glass-${key})`} stroke="#ffffff" strokeOpacity="0.25" />
          <rect x="0" y="0" width="250" height="24" rx="14" fill="#ffffff" fillOpacity="0.12" />
          <circle cx="14" cy="12" r="3" fill="#ffffff" fillOpacity="0.5" />
          <circle cx="25" cy="12" r="3" fill="#ffffff" fillOpacity="0.35" />
          <circle cx="36" cy="12" r="3" fill="#ffffff" fillOpacity="0.25" />
          <rect x="16" y="38" width="92" height="10" rx="5" fill="#ffffff" fillOpacity="0.5" />
          <rect x="16" y="56" width="150" height="6" rx="3" fill="#ffffff" fillOpacity="0.2" />
          <rect x="16" y="68" width="120" height="6" rx="3" fill="#ffffff" fillOpacity="0.15" />
          <rect x="16" y="90" width="64" height="46" rx="8" fill="#ffffff" fillOpacity="0.14" />
          <rect x="88" y="90" width="64" height="46" rx="8" fill="#ffffff" fillOpacity="0.1" />
          <rect x="160" y="90" width="74" height="46" rx="8" fill={colors.accent} fillOpacity="0.3" />
          <rect x="16" y="146" width="218" height="1" fill="#ffffff" fillOpacity="0.15" />
        </g>
        <g transform="translate(268 96)">
          <rect width="86" height="128" rx="16" fill="#0b1c2e" stroke="#ffffff" strokeOpacity="0.3" />
          <rect x="8" y="10" width="70" height="108" rx="10" fill="#ffffff" fillOpacity="0.08" />
          <rect x="16" y="22" width="38" height="6" rx="3" fill="#ffffff" fillOpacity="0.5" />
          <rect x="16" y="34" width="54" height="4" rx="2" fill="#ffffff" fillOpacity="0.2" />
          <rect x="16" y="46" width="54" height="26" rx="6" fill={colors.accent} fillOpacity="0.35" />
          <rect x="16" y="78" width="54" height="8" rx="4" fill="#ffffff" fillOpacity="0.16" />
          <rect x="16" y="92" width="40" height="8" rx="4" fill="#ffffff" fillOpacity="0.12" />
        </g>
        <g opacity="0.85" transform="translate(332 36)">
          <circle cx="22" cy="22" r="22" fill="#ffffff" fillOpacity="0.12" />
          <Icon x="11" y="11" width="22" height="22" color={colors.accent} />
        </g>
      </svg>
      <div className="absolute bottom-3 left-4 font-mono text-[9px] uppercase tracking-[0.2em] text-white/60">
        {key} · delivered
      </div>
    </div>
  );
}

export function CloudInfraArt({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 420 300" className={cn('h-auto w-full', className)} role="img" aria-label="Cloud infrastructure illustration">
      <defs>
        <linearGradient id="cloudGrad" x1="0" x2="1" y1="0" y2="1">
          <stop offset="0" stopColor="#0a7bce" />
          <stop offset="1" stopColor="#1e9ee8" />
        </linearGradient>
        <linearGradient id="panelGrad" x1="0" x2="0" y1="0" y2="1">
          <stop offset="0" stopColor="#0b3556" />
          <stop offset="1" stopColor="#062a4a" />
        </linearGradient>
      </defs>
      <rect width="420" height="300" rx="24" fill="#f2f9fe" />
      <g stroke="#0a7bce" strokeOpacity="0.12">
        {Array.from({ length: 9 }).map((_, i) => (
          <line key={`v${i}`} x1={i * 48} y1="0" x2={i * 48} y2="300" />
        ))}
        {Array.from({ length: 7 }).map((_, i) => (
          <line key={`h${i}`} x1="0" y1={i * 48} x2="420" y2={i * 48} />
        ))}
      </g>
      <g transform="translate(130 34)">
        <rect width="160" height="64" rx="18" fill="url(#panelGrad)" />
        <path d="M45 46c-9 0-15-6-15-14s7-14 15-13c2-9 10-15 20-15 12 0 21 8 22 20 7 1 12 7 12 14 0 8-6 14-15 14H45z" fill="#4fb6ff" fillOpacity="0.9" />
        <circle cx="122" cy="30" r="4" fill="#7dd3fc" />
        <circle cx="136" cy="30" r="4" fill="#7dd3fc" fillOpacity="0.5" />
      </g>
      <g stroke="#1e9ee8" strokeWidth="1.6" strokeDasharray="4 5" fill="none">
        <path d="M210 98v34" />
        <path d="M210 132H100v34" />
        <path d="M210 132h110v34" />
        <path d="M100 166v34" />
        <path d="M210 166v34" />
        <path d="M320 166v34" />
      </g>
      {[
        { x: 50, y: 200, label: 'API', w: 100 },
        { x: 160, y: 200, label: 'DB', w: 100 },
        { x: 270, y: 200, label: 'CDN', w: 100 },
      ].map((node) => (
        <g key={node.label} transform={`translate(${node.x} ${node.y})`}>
          <rect width={node.w} height="56" rx="14" fill="#ffffff" stroke="#cfe7f8" />
          <rect x="14" y="16" width="24" height="24" rx="7" fill="url(#cloudGrad)" fillOpacity="0.14" />
          <path d="M26 24v8M22 28h8" stroke="#0a7bce" strokeWidth="1.6" />
          <text x="48" y="26" fontFamily="Inter, sans-serif" fontSize="11" fontWeight="700" fill="#083b66">{node.label}</text>
          <text x="48" y="40" fontFamily="JetBrains Mono, monospace" fontSize="8" fill="#8ca3b5">healthy</text>
          <circle cx={node.w - 16} cy="16" r="3" fill="#22c55e" />
        </g>
      ))}
    </svg>
  );
}

export function SecurityArt({ className }: { className?: string }) {
  return (
    <svg viewBox="0 0 360 300" className={cn('h-auto w-full', className)} role="img" aria-label="Cybersecurity illustration">
      <defs>
        <linearGradient id="secGrad" x1="0" x2="1" y1="0" y2="1">
          <stop offset="0" stopColor="#0a7bce" />
          <stop offset="1" stopColor="#22d3ee" />
        </linearGradient>
      </defs>
      <rect width="360" height="300" rx="24" fill="#062a4a" />
      <g stroke="#7dd3fc" strokeOpacity="0.12">
        {Array.from({ length: 8 }).map((_, i) => (
          <line key={`v${i}`} x1={i * 46} y1="0" x2={i * 46} y2="300" />
        ))}
        {Array.from({ length: 7 }).map((_, i) => (
          <line key={`h${i}`} x1="0" y1={i * 46} x2="360" y2={i * 46} />
        ))}
      </g>
      <circle cx="180" cy="140" r="72" fill="url(#secGrad)" fillOpacity="0.1" stroke="#38bdf8" strokeOpacity="0.3" />
      <circle cx="180" cy="140" r="52" fill="url(#secGrad)" fillOpacity="0.14" stroke="#38bdf8" strokeOpacity="0.35" />
      <path d="M180 96l34 14v26c0 24-15 40-34 48-19-8-34-24-34-48v-26z" fill="#062a4a" stroke="#7dd3fc" strokeWidth="2" />
      <path d="M166 140l10 10 20-22" fill="none" stroke="#34d399" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round" />
      {[0, 60, 120, 180, 240, 300].map((angle) => {
        const rad = (angle * Math.PI) / 180;
        const x = 180 + Math.cos(rad) * 100;
        const y = 140 + Math.sin(rad) * 100;
        return <circle key={angle} cx={x} cy={y} r="5" fill="#38bdf8" fillOpacity="0.8" />;
      })}
      <g transform="translate(26 232)">
        <rect width="140" height="42" rx="10" fill="#ffffff" fillOpacity="0.08" stroke="#ffffff" strokeOpacity="0.15" />
        <circle cx="18" cy="21" r="6" fill="#34d399" />
        <text x="34" y="18" fontFamily="Inter, sans-serif" fontSize="9" fontWeight="700" fill="#e0f2fe">Threat monitor</text>
        <text x="34" y="30" fontFamily="JetBrains Mono, monospace" fontSize="7" fill="#7dd3fc">0 critical findings</text>
      </g>
      <g transform="translate(196 232)">
        <rect width="138" height="42" rx="10" fill="#ffffff" fillOpacity="0.08" stroke="#ffffff" strokeOpacity="0.15" />
        <circle cx="18" cy="21" r="6" fill="#38bdf8" />
        <text x="34" y="18" fontFamily="Inter, sans-serif" fontSize="9" fontWeight="700" fill="#e0f2fe">Encrypted</text>
        <text x="34" y="30" fontFamily="JetBrains Mono, monospace" fontSize="7" fill="#7dd3fc">TLS 1.3 · AES-256</text>
      </g>
    </svg>
  );
}

export function AiArt({ className }: { className?: string }) {
  const nodes = [
    [60, 80], [120, 50], [120, 120], [180, 85], [180, 150], [250, 60], [250, 120], [310, 90],
  ];
  const edges = [[0, 1], [0, 2], [1, 3], [2, 3], [2, 4], [3, 5], [3, 6], [4, 6], [5, 7], [6, 7]];
  return (
    <svg viewBox="0 0 360 220" className={cn('h-auto w-full', className)} role="img" aria-label="Artificial intelligence neural network illustration">
      <rect width="360" height="220" rx="24" fill="#f2f9fe" />
      <g stroke="#0a7bce" strokeOpacity="0.18">
        {edges.map(([a, b], index) => (
          <line key={index} x1={nodes[a][0]} y1={nodes[a][1]} x2={nodes[b][0]} y2={nodes[b][1]} />
        ))}
      </g>
      {nodes.map(([x, y], index) => (
        <g key={index}>
          <circle cx={x} cy={y} r={index === 3 ? 14 : 9} fill={index === 3 ? '#0a7bce' : '#ffffff'} stroke="#0a7bce" strokeWidth="2" />
          {index === 3 && <circle cx={x} cy={y} r="20" fill="none" stroke="#0a7bce" strokeOpacity="0.3" />}
        </g>
      ))}
      <text x="24" y="196" fontFamily="JetBrains Mono, monospace" fontSize="8" fill="#5c7589">MODEL · TRAINED · DEPLOYED</text>
      <g transform="translate(262 150)">
        <rect width="74" height="48" rx="12" fill="#062a4a" />
        <text x="12" y="20" fontFamily="Inter, sans-serif" fontSize="9" fontWeight="700" fill="#e0f2fe">Insight</text>
        <text x="12" y="34" fontFamily="JetBrains Mono, monospace" fontSize="7" fill="#7dd3fc">+24% accuracy</text>
      </g>
    </svg>
  );
}

export function IndustryGlyph({ kind }: { kind: string }) {
  const map: Record<string, LucideIcon> = {
    education: GraduationCap,
    health: Stethoscope,
    hospitality: Hotel,
    commerce: ShoppingCart,
    agriculture: Sprout,
    logistics: Truck,
    government: Building2,
    ngos: Sparkles,
  };
  const Icon = map[kind] ?? Building2;
  return <Icon className="h-5 w-5" strokeWidth={1.7} />;
}
