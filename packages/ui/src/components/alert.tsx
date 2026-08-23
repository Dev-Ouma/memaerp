import * as React from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '../lib/utils';
import { AlertCircle, CheckCircle2, Info, AlertTriangle } from 'lucide-react';

const alertVariants = cva(
  'relative w-full rounded-xl border p-4 [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg+div]:translate-y-[-3px] [&:has(svg)]:pl-11',
  {
    variants: {
      variant: {
        default: 'bg-slate-50 text-slate-900 border-slate-200 [&>svg]:text-slate-600',
        info: 'bg-blue-50/80 text-blue-900 border-blue-200 [&>svg]:text-blue-600',
        success: 'bg-emerald-50/80 text-emerald-900 border-emerald-200 [&>svg]:text-emerald-600',
        warning: 'bg-amber-50/80 text-amber-900 border-amber-200 [&>svg]:text-amber-600',
        destructive: 'bg-red-50/80 text-red-900 border-red-200 [&>svg]:text-red-600',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  }
);

export interface AlertProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof alertVariants> {
  icon?: React.ReactNode;
}

export function Alert({ className, variant = 'default', icon, children, ...props }: AlertProps) {
  const defaultIcon = {
    default: <Info className="h-5 w-5" />,
    info: <Info className="h-5 w-5" />,
    success: <CheckCircle2 className="h-5 w-5" />,
    warning: <AlertTriangle className="h-5 w-5" />,
    destructive: <AlertCircle className="h-5 w-5" />,
  }[variant || 'default'];

  return (
    <div
      role="alert"
      className={cn(alertVariants({ variant }), className)}
      {...props}
    >
      {icon ?? defaultIcon}
      <div>{children}</div>
    </div>
  );
}

export function AlertTitle({ className, ...props }: React.HTMLAttributes<HTMLHeadingElement>) {
  return (
    <h5
      className={cn('mb-1 font-semibold leading-none tracking-tight text-current', className)}
      {...props}
    />
  );
}

export function AlertDescription({ className, ...props }: React.HTMLAttributes<HTMLParagraphElement>) {
  return <div className={cn('text-sm opacity-90 leading-relaxed', className)} {...props} />;
}
