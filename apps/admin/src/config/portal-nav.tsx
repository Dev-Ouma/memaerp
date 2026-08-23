import React from 'react';
import {
  LayoutDashboard,
  GraduationCap,
  BookOpen,
  UserCheck,
  Users,
  CircleDollarSign,
  Shield,
  ShieldCheck,
  Building2,
  Cloud,
  ClipboardList,
  UserRound,
} from 'lucide-react';
import type { NavItem } from '@mema/ui';

export const adminNavItems: NavItem[] = [
  {
    title: 'ERP Dashboard',
    href: '/',
    icon: <LayoutDashboard className="h-5 w-5" />,
    anyPermission: ['analytics.dashboard.view', 'student.record.view', 'curriculum.programme.view'],
  },
  {
    title: 'Institution & Calendar',
    href: '/institution',
    icon: <Building2 className="h-5 w-5" />,
    anyPermission: ['institution.structure.view', 'institution.calendar.view'],
  },
  {
    title: 'Programmes & Curricula',
    href: '/programmes',
    icon: <GraduationCap className="h-5 w-5" />,
    permission: 'curriculum.programme.view',
  },
  {
    title: 'Courses & Capacities',
    href: '/courses',
    icon: <BookOpen className="h-5 w-5" />,
    anyPermission: ['course.catalogue.view', 'course.offering.view'],
  },
  {
    title: 'Admissions & Applicants',
    href: '/admissions',
    icon: <UserCheck className="h-5 w-5" />,
    permission: 'admission.application.view',
    badge: '18 New',
  },
  {
    title: 'Student Directory',
    href: '/students',
    icon: <Users className="h-5 w-5" />,
    permission: 'student.record.view',
  },
  {
    title: 'Academic Advising',
    href: '/advising',
    icon: <UserRound className="h-5 w-5" />,
    permission: 'advising.assignment.manage',
  },
  {
    title: 'LMS Sync',
    href: '/lms',
    icon: <Cloud className="h-5 w-5" />,
    permission: 'lms.sync.view',
  },
  {
    title: 'Attendance Reports',
    href: '/attendance',
    icon: <ClipboardList className="h-5 w-5" />,
    permission: 'attendance.report.view',
  },
  {
    title: 'Finance & Invoicing',
    href: '/finance',
    icon: <CircleDollarSign className="h-5 w-5" />,
    anyPermission: ['finance.invoice.view', 'finance.payment.view'],
  },
  {
    title: 'IAM, Roles & Audit',
    href: '/security',
    icon: <Shield className="h-5 w-5" />,
    anyPermission: ['iam.role.view', 'audit.log.view'],
  },
  {
    title: 'My Security',
    href: '/account-security',
    icon: <ShieldCheck className="h-5 w-5" />,
  },
];

/** Nav entries surfaced as dashboard quick-access tiles (excludes home + account). */
export const adminModuleLinks = adminNavItems.filter(
  (item) => item.href !== '/' && item.href !== '/account-security',
);
