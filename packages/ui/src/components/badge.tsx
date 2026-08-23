import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '../lib/utils';

export const badgeVariants = cva(
  'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors',
  {
    variants: {
      variant: {
        default: 'bg-mema-teal-100 text-mema-teal-900 border border-mema-teal-200',
        success: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
        warning: 'bg-amber-100 text-amber-800 border border-amber-200',
        destructive: 'bg-rose-100 text-rose-800 border border-rose-200',
        info: 'bg-blue-100 text-blue-800 border border-blue-200',
        outline: 'border border-slate-300 text-slate-700 bg-white',
        muted: 'bg-slate-100 text-slate-700',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

export interface BadgeProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof badgeVariants> {
  dot?: boolean;
}

export function Badge({ className, variant, dot, children, ...props }: BadgeProps) {
  return (
    <div className={cn(badgeVariants({ variant }), className)} {...props}>
      {dot && (
        <span
          className={cn(
            'h-1.5 w-1.5 rounded-full',
            variant === 'success' && 'bg-emerald-600',
            variant === 'warning' && 'bg-amber-600',
            variant === 'destructive' && 'bg-rose-600',
            variant === 'info' && 'bg-blue-600',
            (!variant || variant === 'default') && 'bg-mema-teal-800'
          )}
        />
      )}
      {children}
    </div>
  );
}
