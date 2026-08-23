'use client';

import Lottie from 'lottie-react';
import { cn } from '../lib/utils';
import animationData from '../assets/mema-dot-loader.json';

/** Inspired by Loader Animation by Waqas Awan (IconScout #4573598), recolored with MEMA brand tokens. */

export interface MemaLoaderProps {
  /** Visual width of the loader in pixels */
  size?: number;
  /** Optional status text shown below the animation */
  label?: string;
  className?: string;
  /** Hide the accessible status label visually while keeping it for screen readers */
  srOnlyLabel?: string;
}

const LOADER_ASPECT = 1;

export function MemaLoader({
  size = 72,
  label,
  className,
  srOnlyLabel = 'Loading',
}: MemaLoaderProps) {
  return (
    <div
      className={cn('inline-flex flex-col items-center justify-center gap-3', className)}
      role="status"
      aria-live="polite"
      aria-busy="true"
    >
      <Lottie
        animationData={animationData}
        loop
        aria-hidden
        style={{
          width: size,
          height: Math.round(size * LOADER_ASPECT),
        }}
      />
      {label ? (
        <span className="text-sm font-medium text-slate-600">{label}</span>
      ) : (
        <span className="sr-only">{srOnlyLabel}</span>
      )}
    </div>
  );
}

export interface MemaLoaderInlineProps {
  size?: number;
  className?: string;
}

/** Compact loader for buttons and inline actions */
export function MemaLoaderInline({ size = 24, className }: MemaLoaderInlineProps) {
  return (
    <Lottie
      animationData={animationData}
      loop
      aria-hidden
      className={className}
      style={{
        width: size,
        height: Math.round(size * LOADER_ASPECT),
      }}
    />
  );
}

export interface MemaLoaderScreenProps {
  label?: string;
  className?: string;
}

/** Full-viewport centered loader for auth bootstrap and route transitions */
export function MemaLoaderScreen({
  label = 'Loading…',
  className,
}: MemaLoaderScreenProps) {
  return (
    <div
      className={cn(
        'flex min-h-screen items-center justify-center bg-slate-50 px-6',
        className
      )}
    >
      <div className="rounded-2xl border border-slate-200 bg-white px-8 py-7 shadow-sm">
        <MemaLoader size={88} label={label} />
      </div>
    </div>
  );
}
