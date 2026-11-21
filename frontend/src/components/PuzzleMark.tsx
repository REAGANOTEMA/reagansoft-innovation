import { LOGO_NEW } from '../lib/env';
import { cn } from '../lib/cn';

const pieces = [
  'inset(0 50% 50% 0)',
  'inset(0 0 50% 50%)',
  'inset(50% 50% 0 0)',
  'inset(50% 0 0 50%)',
];

/**
 * The brand mark assembled from four interlocking pieces. Used wherever the
 * interface is waiting on work, so the loading state still tells our story.
 */
export function PuzzleMark({ className, label }: { className?: string; label?: string }) {
  return (
    <span className={cn('inline-flex items-center gap-3', className)} role="status" aria-label={label ?? 'Assembling Reagansoft'}>
      <span className="puzzle-mark h-12 w-12 shrink-0">
        <span className="absolute inset-1 rounded-full border border-brand-300/30 orbit-pulse" aria-hidden="true" />
        {pieces.map((clip) => (
          <img
            key={clip}
            src={LOGO_NEW}
            alt=""
            className="puzzle-mark-piece"
            style={{ clipPath: clip }}
            aria-hidden="true"
          />
        ))}
      </span>
      {label && <span className="font-mono text-[10px] uppercase tracking-[0.2em] text-white/60">{label}</span>}
    </span>
  );
}
