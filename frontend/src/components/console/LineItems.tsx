import { Plus, Trash2 } from 'lucide-react';
import { money } from '../../lib/format';

export interface LineItem {
  description: string;
  quantity: string;
  unit_price: string;
}

export function emptyLine(): LineItem {
  return { description: '', quantity: '1', unit_price: '' };
}

export function lineTotal(item: LineItem): number {
  return Math.max(0, Number(item.quantity) || 0) * Math.max(0, Number(item.unit_price) || 0);
}

export function LineItems({
  items,
  onChange,
  currency = 'UGX',
}: {
  items: LineItem[];
  onChange: (items: LineItem[]) => void;
  currency?: string;
}) {
  function update(index: number, key: keyof LineItem, value: string) {
    onChange(items.map((item, i) => (i === index ? { ...item, [key]: value } : item)));
  }

  return (
    <div className="space-y-2">
      <div className="hidden grid-cols-[1fr_72px_120px_100px_32px] gap-2 px-1 font-mono text-[8px] uppercase tracking-[0.14em] text-ink-faint sm:grid">
        <span>Description</span>
        <span>Qty</span>
        <span>Unit price</span>
        <span className="text-right">Amount</span>
        <span />
      </div>
      {items.map((item, index) => (
        <div key={index} className="grid grid-cols-[1fr_32px] gap-2 sm:grid-cols-[1fr_72px_120px_100px_32px]">
          <input
            className="input col-span-1"
            value={item.description}
            onChange={(event) => update(index, 'description', event.target.value)}
            placeholder="Item or service description"
          />
          <input
            className="input"
            type="number"
            min="0"
            step="0.01"
            value={item.quantity}
            onChange={(event) => update(index, 'quantity', event.target.value)}
            placeholder="Qty"
          />
          <input
            className="input"
            type="number"
            min="0"
            step="0.01"
            value={item.unit_price}
            onChange={(event) => update(index, 'unit_price', event.target.value)}
            placeholder="0"
          />
          <div className="hidden items-center justify-end font-mono text-[11px] font-semibold text-navy-900 sm:flex">
            {money(lineTotal(item), currency)}
          </div>
          <button
            type="button"
            onClick={() => onChange(items.length === 1 ? [emptyLine()] : items.filter((_, i) => i !== index))}
            className="grid h-10 w-8 place-items-center rounded-lg text-ink-faint transition hover:bg-rose-50 hover:text-rose-600"
            aria-label="Remove line"
          >
            <Trash2 className="h-3.5 w-3.5" />
          </button>
        </div>
      ))}
      <button type="button" onClick={() => onChange([...items, emptyLine()])} className="btn-ghost btn-sm">
        <Plus className="h-3.5 w-3.5" /> Add line
      </button>
    </div>
  );
}
