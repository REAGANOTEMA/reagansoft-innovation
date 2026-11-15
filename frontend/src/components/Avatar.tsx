import { useEffect, useState } from 'react';
import { cn } from '../lib/cn';
import { initials } from '../lib/format';

interface AvatarProps {
  name?: string | null;
  src?: string | null;
  size?: number;
  rounded?: 'xl' | 'full';
  className?: string;
}

export function Avatar({ name, src, size = 40, rounded = 'xl', className }: AvatarProps) {
  const [broken, setBroken] = useState(false);
  useEffect(() => {
    setBroken(false);
  }, [src]);
  const radius = rounded === 'full' ? 'rounded-full' : 'rounded-xl';
  const label = name?.trim() || 'Reagansoft';
  const text = name?.trim() ? initials(name) : 'RSI';

  if (src && !broken) {
    return (
      <img
        src={src}
        alt={`${label} profile photo`}
        width={size}
        height={size}
        loading="lazy"
        onError={() => setBroken(true)}
        style={{ width: size, height: size }}
        className={cn('shrink-0 bg-brand-50 object-cover ring-1 ring-brand-100', radius, className)}
      />
    );
  }

  return (
    <span
      aria-label={`${label} avatar`}
      style={{ width: size, height: size, fontSize: Math.max(10, Math.round(size * 0.36)) }}
      className={cn('grid shrink-0 place-items-center bg-brand-600 font-semibold text-white', radius, className)}
    >
      {text}
    </span>
  );
}
