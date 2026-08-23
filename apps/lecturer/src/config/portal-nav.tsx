import React from 'react';
import { LayoutDashboard, BookOpen, ClipboardList, QrCode, Users } from 'lucide-react';
import type { NavItem } from '@mema/ui';

export const lecturerNavItems: NavItem[] = [
  { title: 'Teaching Dashboard', href: '/', icon: <LayoutDashboard className="h-5 w-5" /> },
  {
    title: 'My Offerings',
    href: '/offerings',
    icon: <BookOpen className="h-5 w-5" />,
    permission: 'course.offering.view',
  },
  {
    title: 'Marks Entry',
    href: '/marks',
    icon: <ClipboardList className="h-5 w-5" />,
    permission: 'examination.marks.enter',
  },
  {
    title: 'Attendance',
    href: '/attendance',
    icon: <QrCode className="h-5 w-5" />,
    permission: 'attendance.session.manage',
  },
  {
    title: 'My Advisees',
    href: '/advisees',
    icon: <Users className="h-5 w-5" />,
    permission: 'advising.advisee.view',
  },
];

export const lecturerModuleLinks = lecturerNavItems.filter((item) => item.href !== '/');
