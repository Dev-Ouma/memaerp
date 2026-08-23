import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

export function formatCurrency(amount: number, currency: string = 'KES'): string {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(amount);
}

export function formatDate(dateString: string): string {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date);
}

export function programmeLabel(programme: {
  title?: string;
  name?: string;
  code: string;
}): string {
  return programme.title ?? programme.name ?? programme.code;
}

export function personDisplayName(person?: {
  full_name?: string;
  given_name?: string;
  family_name?: string;
  first_name?: string;
  last_name?: string;
} | null): string {
  if (!person) return '—';
  if (person.full_name) return person.full_name;
  const modern = `${person.first_name ?? ''} ${person.last_name ?? ''}`.trim();
  if (modern) return modern;
  return `${person.given_name ?? ''} ${person.family_name ?? ''}`.trim() || '—';
}

export function courseCredits(course: { credit_units?: number; credits?: number }): number {
  return course.credit_units ?? course.credits ?? 0;
}
