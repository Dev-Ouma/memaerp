'use client';

import React from 'react';
import { LayoutDashboard, BookOpen, ClipboardList } from 'lucide-react';
import { PortalShell } from '@mema/auth';
import type { NavItem } from '@mema/ui';

const navItems: NavItem[] = [
  { title: 'Teaching Dashboard', href: '/', icon: <LayoutDashboard className="h-5 w-5" /> },
  { title: 'My Offerings', href: '/offerings', icon: <BookOpen className="h-5 w-5" /> },
  { title: 'Marks Entry', href: '/marks', icon: <ClipboardList className="h-5 w-5" /> },
];

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Lecturer Portal"
      appSubtitle="Teaching, assessment and class management"
      navItems={navItems}
    >
      {children}
    </PortalShell>
  );
}
