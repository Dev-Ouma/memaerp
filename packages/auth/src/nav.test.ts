import { describe, expect, it } from 'vitest';
import { filterNavItems } from './nav';
import type { NavItem } from '@mema/ui';

const items: NavItem[] = [
  { title: 'Public', href: '/', icon: null, permission: 'public.view' },
  { title: 'Finance', href: '/finance', icon: null, anyPermission: ['finance.invoice.view'] },
];

describe('filterNavItems', () => {
  it('keeps items when permission matches', () => {
    const result = filterNavItems(items, (permission) => permission === 'public.view');
    expect(result.map((item) => item.title)).toEqual(['Public']);
  });

  it('keeps items when anyPermission matches', () => {
    const result = filterNavItems(items, (permission) => permission === 'finance.invoice.view');
    expect(result.map((item) => item.title)).toEqual(['Finance']);
  });
});
