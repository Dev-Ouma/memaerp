'use client';

import React from 'react';
import {
  LayoutDashboard,
  BookOpen,
  Calendar,
  CreditCard,
  Award,
  CheckCircle2,
} from 'lucide-react';
import { PortalShell } from '@mema/auth';
import type { NavItem } from '@mema/ui';

const studentNavItems: NavItem[] = [
  {
    title: 'Overview',
    href: '/',
    icon: <LayoutDashboard className="h-5 w-5" />,
  },
  {
    title: 'Course Registration',
    href: '/registration',
    icon: <BookOpen className="h-5 w-5" />,
    badge: 'Open',
  },
  {
    title: 'Timetable & Classes',
    href: '/timetable',
    icon: <Calendar className="h-5 w-5" />,
  },
  {
    title: 'Fees & Payments',
    href: '/finance',
    icon: <CreditCard className="h-5 w-5" />,
  },
  {
    title: 'Results & Transcripts',
    href: '/results',
    icon: <Award className="h-5 w-5" />,
  },
  {
    title: 'Clearance & ID',
    href: '/clearance',
    icon: <CheckCircle2 className="h-5 w-5" />,
  },
];

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Student Portal"
      appSubtitle="Official student information and lifecycle services"
      navItems={studentNavItems}
    >
      {children}
    </PortalShell>
  );
}
