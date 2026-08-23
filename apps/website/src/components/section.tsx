import React from 'react';
import Link from 'next/link';
import { ArrowRight } from 'lucide-react';
import { Reveal } from './reveal';

/**
 * The page rhythm used everywhere on this site: a small eyebrow label, one heading, at most
 * one supporting paragraph. Having it in one place is what keeps eleven sections on the
 * homepage from drifting into eleven slightly different typographic treatments.
 *
 * `highlight` renders the trailing words of the heading in the accent colour. It is a separate
 * prop rather than embedded markup so that page authors cannot put arbitrary elements inside
 * an <h2>.
 */
export function SectionHeading({
  eyebrow,
  title,
  highlight,
  body,
  align = 'left',
  tone = 'light',
  as: Heading = 'h2',
}: {
  eyebrow?: string;
  title: string;
  highlight?: string;
  body?: string;
  align?: 'left' | 'center';
  tone?: 'light' | 'dark';
  as?: 'h1' | 'h2';
}) {
  return (
    <div className={`section-heading section-heading-${align} section-heading-${tone}`}>
      {eyebrow ? (
        <Reveal>
          <span className="eyebrow">{eyebrow}</span>
        </Reveal>
      ) : null}

      <Reveal delay={70}>
        <Heading className="section-title">
          {title}
          {highlight ? <span className="section-title-accent"> {highlight}</span> : null}
        </Heading>
      </Reveal>

      {body ? (
        <Reveal delay={140}>
          <p className="section-body">{body}</p>
        </Reveal>
      ) : null}
    </div>
  );
}

/** A consistently padded, width-constrained section shell. */
export function Section({
  id,
  children,
  tone = 'light',
  className,
}: {
  id?: string;
  children: React.ReactNode;
  tone?: 'light' | 'muted' | 'dark' | 'deep';
  className?: string;
}) {
  return (
    <section id={id} className={['section', `section-${tone}`, className].filter(Boolean).join(' ')}>
      <div className="container">{children}</div>
    </section>
  );
}

/**
 * The site's only two link buttons. Anything that navigates within the site uses next/link so
 * that the client router handles it; anything that leaves the site (a portal on another origin)
 * is a plain anchor, because next/link cannot prefetch across origins and pretending otherwise
 * just costs a wasted request.
 */
export function ActionLink({
  href,
  children,
  variant = 'solid',
  external,
  className,
}: {
  href: string;
  children: React.ReactNode;
  variant?: 'solid' | 'outline' | 'ghost' | 'quiet';
  external?: boolean;
  className?: string;
}) {
  const classes = ['action', `action-${variant}`, className].filter(Boolean).join(' ');
  const isExternal = external ?? /^https?:\/\//.test(href);

  if (isExternal) {
    return (
      <a href={href} className={classes} rel="noopener">
        {children}
      </a>
    );
  }

  return (
    <Link href={href} className={classes}>
      {children}
    </Link>
  );
}

/** The "eyebrow + heading on the left, view-all link on the right" header used by list sections. */
export function SectionHeader({
  eyebrow,
  title,
  highlight,
  actionLabel,
  actionHref,
}: {
  eyebrow: string;
  title: string;
  highlight?: string;
  actionLabel: string;
  actionHref: string;
}) {
  return (
    <div className="section-header-row">
      <SectionHeading eyebrow={eyebrow} title={title} highlight={highlight} />
      <Reveal delay={120}>
        <ActionLink href={actionHref} variant="quiet">
          {actionLabel} <ArrowRight className="icon-inline" aria-hidden />
        </ActionLink>
      </Reveal>
    </div>
  );
}

/** The banded page header every inner page opens with. */
export function PageHero({
  eyebrow,
  title,
  body,
}: {
  eyebrow: string;
  title: string;
  body: string;
}) {
  return (
    <section className="page-hero">
      <div className="page-hero-glow" aria-hidden />
      <div className="container">
        <SectionHeading eyebrow={eyebrow} title={title} body={body} tone="dark" as="h1" />
      </div>
    </section>
  );
}
