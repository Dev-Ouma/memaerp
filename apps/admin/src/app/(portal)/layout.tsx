'use client';

import React from 'react';
import {
  LayoutDashboard,
  GraduationCap,
  BookOpen,
  UserCheck,
  Users,
  CircleDollarSign,
  Shield,
} from 'lucide-react';
import { PortalShell } from '@mema/auth';
import type { NavItem } from '@mema/ui';

const adminNavItems: NavItem[] = [
  {
    title: 'ERP Dashboard',
    href: '/',
    icon: <LayoutDashboard className="h-5 w-5" />,
  },
  {
    title: 'Programmes & Curricula',
    href: '/programmes',
    icon: <GraduationCap className="h-5 w-5" />,
  },
  {
    title: 'Courses & Capacities',
    href: '/courses',
    icon: <BookOpen className="h-5 w-5" />,
  },
  {
    title: 'Admissions & Applicants',
    href: '/admissions',
    icon: <UserCheck className="h-5 w-5" />,
    badge: '18 New',
  },
  {
    title: 'Student Directory',
    href: '/students',
    icon: <Users className="h-5 w-5" />,
  },
  {
    title: 'Finance & Invoicing',
    href: '/finance',
    icon: <CircleDollarSign className="h-5 w-5" />,
  },
  {
    title: 'IAM, Roles & Audit',
    href: '/security',
    icon: <Shield className="h-5 w-5" />,
  },
];

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="ERP Administration"
      appSubtitle="Institutional Master Control · Registrar & Finance"
      navItems={adminNavItems}
    >
      {children}
    </PortalShell>
  );
}
