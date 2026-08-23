'use client';

import React from 'react';
import { LayoutDashboard, Inbox } from 'lucide-react';
import { PortalShell } from '@mema/auth';
import type { NavItem } from '@mema/ui';

const navItems: NavItem[] = [
  { title: 'Staff Dashboard', href: '/', icon: <LayoutDashboard className="h-5 w-5" /> },
  { title: 'Requests & Clearance', href: '/requests', icon: <Inbox className="h-5 w-5" /> },
];

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Staff Portal"
      appSubtitle="Administrative services and internal workflows"
      navItems={navItems}
    >
      {children}
    </PortalShell>
  );
}
