import React from 'react';
import { LayoutDashboard, Inbox } from 'lucide-react';
import type { NavItem } from '@mema/ui';

export const staffNavItems: NavItem[] = [
  { title: 'Staff Dashboard', href: '/', icon: <LayoutDashboard className="h-5 w-5" /> },
  {
    title: 'Clearance Queue',
    href: '/requests',
    icon: <Inbox className="h-5 w-5" />,
    permission: 'graduation.clearance.clear',
  },
];

export const staffModuleLinks = staffNavItems.filter((item) => item.href !== '/');
