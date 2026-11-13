import { useRef, useState, type CSSProperties, type PointerEvent, type ReactNode } from 'react';
import { cn } from '../lib/cn';

interface TiltProps {
  children: ReactNode;
  className?: string;
  /** Maximum rotation in degrees. Kept low for a calm, architectural feel. */
  max?: number;
  style?: CSSProperties;
}

/**
 * A restrained pointer-driven 3D tilt. It only applies a few degrees of
 * rotation, never follows touch scrolling, and is completely disabled for
 * visitors who prefer reduced motion.
 */
export function Tilt({ children, className, max = 5, style }: TiltProps) {
  const ref = useRef<HTMLDivElement>(null);
  const [transform, setTransform] = useState<string>('');

  function handleMove(event: PointerEvent<HTMLDivElement>) {
    if (event.pointerType !== 'mouse') return;
    if (typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const node = ref.current;
    if (!node) return;
    const rect = node.getBoundingClientRect();
    if (!rect.width || !rect.height) return;
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    setTransform(`perspective(1100px) rotateX(${(-y * max).toFixed(2)}deg) rotateY(${(x * max).toFixed(2)}deg)`);
  }

  function reset() {
    setTransform('');
  }

  return (
    <div
      ref={ref}
      onPointerMove={handleMove}
      onPointerLeave={reset}
      className={cn('transition-transform duration-300 ease-out [transform-style:preserve-3d] motion-reduce:!transform-none', className)}
      style={{ ...style, transform: transform || style?.transform }}
    >
      {children}
    </div>
  );
}
