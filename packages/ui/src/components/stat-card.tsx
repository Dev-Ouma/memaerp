import * as React from 'react';
import { Card } from './card';
import { cn } from '../lib/utils';
import { TrendingUp, TrendingDown, Minus } from 'lucide-react';

export interface StatCardProps {
  title: string;
  value: string | number;
  description?: string;
  icon?: React.ReactNode;
  trend?: {
    value: string | number;
    isPositive?: boolean;
    isNeutral?: boolean;
    label?: string;
  };
  className?: string;
}

export function StatCard({
  title,
  value,
  description,
  icon,
  trend,
  className,
}: StatCardProps) {
  return (
    <Card className={cn('p-6 overflow-hidden relative', className)}>
      <div className="flex items-center justify-between">
        <p className="text-sm font-medium text-slate-500">{title}</p>
        {icon && (
          <div className="h-10 w-10 rounded-lg bg-mema-teal-50 text-mema-teal-800 flex items-center justify-center">
            {icon}
          </div>
        )}
      </div>

      <div className="mt-4 flex items-baseline gap-2">
        <h2 className="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 font-heading">
          {value}
        </h2>
      </div>

      {(trend || description) && (
        <div className="mt-3 flex items-center gap-2 text-xs">
          {trend && (
            <span
              className={cn(
                'inline-flex items-center gap-1 font-semibold rounded px-1.5 py-0.5',
                trend.isNeutral
                  ? 'bg-slate-100 text-slate-700'
                  : trend.isPositive
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-rose-100 text-rose-700'
              )}
            >
              {trend.isNeutral ? (
                <Minus className="h-3 w-3" />
              ) : trend.isPositive ? (
                <TrendingUp className="h-3 w-3" />
              ) : (
                <TrendingDown className="h-3 w-3" />
              )}
              {trend.value}
            </span>
          )}
          {trend?.label && <span className="text-slate-500">{trend.label}</span>}
          {description && !trend && <span className="text-slate-500">{description}</span>}
        </div>
      )}
    </Card>
  );
}
