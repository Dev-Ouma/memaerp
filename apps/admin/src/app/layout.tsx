'use client';

import React from 'react';
import './globals.css';
import { AuthProvider } from '@mema/auth';
import { AppShell, NavItem } from '@mema/ui';
import {
  LayoutDashboard,
  GraduationCap,
  BookOpen,
  UserCheck,
  Users,
  CircleDollarSign,
  Shield,
} from 'lucide-react';

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

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <head>
        <title>MEMA ERP — Enterprise Administration</title>
        <meta
          name="description"
          content="Mema University Enterprise Resource Planning & Administration"
        />
      </head>
      <body>
        <AuthProvider>
          <AppShell
            appName="ERP Administration"
            appSubtitle="Institutional Master Control · Registrar & Finance"
            userName="Dr. Arthur Mutua"
            userRole="System Administrator & Registrar"
            userIdentifier="EMP-00109"
            navItems={adminNavItems}
          >
            {children}
          </AppShell>
        </AuthProvider>
      </body>
    </html>
  );
}
