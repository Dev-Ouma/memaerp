'use client';

import React from 'react';
import { LayoutDashboard } from 'lucide-react';
import { PortalShell } from '@mema/auth';
import type { NavItem } from '@mema/ui';

const navItems: NavItem[] = [
  { title: 'Executive Dashboard', href: '/', icon: <LayoutDashboard className="h-5 w-5" /> },
];

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Management Dashboard"
      appSubtitle="Institutional analytics and executive reporting"
      navItems={navItems}
    >
      {children}
    </PortalShell>
  );
}
