import clsx from 'clsx';

export function cn(...values: Array<string | false | null | undefined>): string {
  return clsx(values);
}
