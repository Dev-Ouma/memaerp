import * as React from 'react';
import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '../lib/utils';
import { MemaLoaderInline } from './loader';

export const buttonVariants = cva(
  'inline-flex items-center justify-center whitespace-nowrap rounded-lg text-sm font-medium transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-mema-teal-700 disabled:pointer-events-none disabled:opacity-50 active:scale-[0.98]',
  {
    variants: {
      variant: {
        default:
          'bg-mema-teal-800 text-white shadow hover:bg-mema-teal-700 active:bg-mema-teal-900',
        secondary:
          'bg-mema-green-600 text-white shadow-sm hover:bg-mema-green-700 active:bg-mema-green-800',
        destructive:
          'bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800',
        outline:
          'border border-slate-200 bg-white text-slate-800 hover:bg-slate-50 hover:text-mema-teal-800 border-slate-300',
        ghost:
          'text-slate-700 hover:bg-slate-100 hover:text-mema-teal-900',
        link: 'text-mema-teal-800 underline-offset-4 hover:underline',
      },
      size: {
        default: 'h-10 px-4 py-2',
        sm: 'h-8 rounded-md px-3 text-xs',
        lg: 'h-12 rounded-lg px-6 text-base font-semibold',
        icon: 'h-10 w-10 p-0',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
  isLoading?: boolean;
}

export const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, isLoading, children, disabled, ...props }, ref) => {
    const Comp = asChild ? Slot : 'button';
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        disabled={disabled || isLoading}
        {...props}
      >
        {isLoading ? (
          <span className="flex items-center gap-2">
            <MemaLoaderInline size={20} />
            <span>Processing...</span>
          </span>
        ) : (
          children
        )}
      </Comp>
    );
  }
);
Button.displayName = 'Button';
