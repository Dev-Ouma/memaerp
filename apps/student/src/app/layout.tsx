'use client';

import React from 'react';
import './globals.css';
import { AuthProvider } from '@mema/auth';
import { AppShell, NavItem } from '@mema/ui';
import {
  LayoutDashboard,
  BookOpen,
  Calendar,
  CreditCard,
  Award,
  CheckCircle2,
} from 'lucide-react';

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

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en">
      <head>
        <title>MEMA Student Portal — University Information System</title>
        <meta
          name="description"
          content="Official Mema University Student Information & Lifecycle Portal"
        />
      </head>
      <body>
        <AuthProvider>
          <AppShell
            appName="Student Portal"
            appSubtitle="BSc in Computer Science (Year 3 · Sem 1)"
            userName="Ian Wabwire"
            userRole="Undergraduate Student"
            userIdentifier="CT201/0042/23"
            navItems={studentNavItems}
          >
            {children}
          </AppShell>
        </AuthProvider>
      </body>
    </html>
  );
}
