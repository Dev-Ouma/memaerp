'use client';

import React, { useEffect, useRef, useState } from 'react';

type Direction = 'up' | 'left' | 'right' | 'scale' | 'fade';

type RevealProps = {
  children: React.ReactNode;
  /** Milliseconds to wait after the element enters the viewport. Use for stagger. */
  delay?: number;
  direction?: Direction;
  className?: string;
  as?: 'div' | 'section' | 'article' | 'li' | 'span';
};

/**
 * Reveals its children once, when they first scroll into view.
 *
 * Two deliberate choices:
 *
 * 1. It reveals ONCE and then disconnects. Animations that replay every time you scroll past
 *    are nauseating on a long page and make the site feel unstable rather than polished.
 *
 * 2. The initial hidden state is applied by the client, not the server. If it were in the
 *    server-rendered HTML, then a visitor whose JavaScript fails — a flaky mobile connection,
 *    a corporate proxy — would get a permanently blank page. A public university site must
 *    render its content without JavaScript; the motion is decoration layered on afterwards.
 *
 * Reduced motion is handled in CSS rather than here, so it also covers the CSS-only animations
 * (the ticker, the marquee) that this component knows nothing about.
 */
export function Reveal({
  children,
  delay = 0,
  direction = 'up',
  className,
  as: Tag = 'div',
}: RevealProps) {
  const ref = useRef<HTMLElement>(null);
  const [armed, setArmed] = useState(false);
  const [shown, setShown] = useState(false);

  useEffect(() => {
    // Arming in an effect means the server HTML is the visible state (see note 2 above).
    setArmed(true);

    const node = ref.current;
    if (!node) return;

    if (typeof IntersectionObserver === 'undefined') {
      setShown(true);
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          setShown(true);
          observer.disconnect();
        }
      },
      // A negative bottom margin holds the reveal until the element is properly on screen
      // rather than one pixel into it, which reads as a glitch on fast scrolls.
      { threshold: 0.05, rootMargin: '0px 0px -60px 0px' },
    );

    observer.observe(node);
    return () => observer.disconnect();
  }, []);

  return (
    <Tag
      ref={ref as any}
      className={['reveal', armed && !shown ? 'reveal-armed' : '', shown ? 'reveal-shown' : '', className]
        .filter(Boolean)
        .join(' ')}
      data-reveal={direction}
      style={delay ? ({ '--reveal-delay': `${delay}ms` } as React.CSSProperties) : undefined}
    >
      {children}
    </Tag>
  );
}

/**
 * Counts up to a target when scrolled into view.
 *
 * Driven by requestAnimationFrame against wall-clock time rather than a fixed-step
 * setInterval: a 16ms interval drifts on a busy main thread and lands the number late, and on
 * a 120Hz display it wastes frames. Easing is cubic ease-out so the number decelerates into
 * its final value instead of stopping dead.
 */
export function Counter({ target, suffix = '' }: { target: number; suffix?: string }) {
  const ref = useRef<HTMLSpanElement>(null);
  const [value, setValue] = useState(0);

  useEffect(() => {
    const node = ref.current;
    if (!node) return;

    const reduced =
      typeof matchMedia === 'function' && matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || typeof IntersectionObserver === 'undefined') {
      setValue(target);
      return;
    }

    let frame = 0;
    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries) {
          if (!entry.isIntersecting) continue;
          observer.disconnect();

          const duration = 1800;
          const startedAt = performance.now();

          const tick = (now: number) => {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            setValue(Math.round(target * eased));
            if (progress < 1) frame = requestAnimationFrame(tick);
          };

          frame = requestAnimationFrame(tick);
        }
      },
      { threshold: 0.4 },
    );

    observer.observe(node);
    return () => {
      observer.disconnect();
      cancelAnimationFrame(frame);
    };
  }, [target]);

  return (
    <span ref={ref}>
      {value.toLocaleString('en-KE')}
      {suffix}
    </span>
  );
}
