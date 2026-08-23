import React from 'react';
import {
  LayoutDashboard,
  BookOpen,
  Calendar,
  CreditCard,
  Award,
  CheckCircle2,
  ScanLine,
  MonitorPlay,
  GraduationCap,
} from 'lucide-react';
import type { NavItem } from '@mema/ui';

export const studentNavItems: NavItem[] = [
  {
    title: 'Overview',
    href: '/',
    icon: <LayoutDashboard className="h-5 w-5" />,
  },
  {
    title: 'Course Registration',
    href: '/registration',
    icon: <BookOpen className="h-5 w-5" />,
    permission: 'enrollment.registration.register',
    badge: 'Open',
  },
  {
    title: 'Timetable & Classes',
    href: '/timetable',
    icon: <Calendar className="h-5 w-5" />,
    permission: 'enrollment.registration.view',
  },
  {
    title: 'Class Check-In',
    href: '/attendance',
    icon: <ScanLine className="h-5 w-5" />,
    permission: 'attendance.checkin.self',
  },
  {
    title: 'Degree Progress',
    href: '/advising',
    icon: <GraduationCap className="h-5 w-5" />,
    permission: 'advising.progress.view-self',
  },
  {
    title: 'E-Learning (Moodle)',
    href: '/lms',
    icon: <MonitorPlay className="h-5 w-5" />,
    permission: 'lms.launch.view',
  },
  {
    title: 'Fees & Payments',
    href: '/finance',
    icon: <CreditCard className="h-5 w-5" />,
    anyPermission: ['finance.invoice.view', 'finance.payment.view'],
  },
  {
    title: 'Results & Transcripts',
    href: '/results',
    icon: <Award className="h-5 w-5" />,
    permission: 'examination.marks.view',
  },
  {
    title: 'Clearance & ID',
    href: '/clearance',
    icon: <CheckCircle2 className="h-5 w-5" />,
    permission: 'graduation.clearance.view',
  },
];

export const studentModuleLinks = studentNavItems.filter((item) => item.href !== '/');
