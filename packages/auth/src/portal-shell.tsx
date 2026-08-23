'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import type { AuthUserProfile } from '@mema/types';
import { AppShell, type NavItem } from '@mema/ui';
import { useAuth } from './context';
import { filterNavItems } from './nav';

export interface PortalShellProps {
  appName: string;
  appSubtitle?: string;
  navItems: NavItem[];
  children: React.ReactNode;
}

function resolveIdentifier(user: AuthUserProfile): string {
  const identity = user.person?.identities?.find((item) =>
    ['STUDENT_NUMBER', 'STAFF_NUMBER', 'EMPLOYEE_NUMBER'].includes(item.type)
  );
  return identity?.identifier ?? user.username;
}

export function PortalShell({
  appName,
  appSubtitle,
  navItems,
  children,
}: PortalShellProps) {
  const router = useRouter();
  const { user, logout, isLoading, can } = useAuth();

  if (isLoading) {
    return (
      <div className="flex min-h-screen items-center justify-center bg-slate-50">
        <div className="rounded-xl border border-slate-200 bg-white px-6 py-4 text-sm text-slate-600 shadow-sm">
          Loading your session...
        </div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  const displayName = user.person?.full_name ?? user.username;
  const roleLabel = user.roles[0]?.role_name ?? 'Signed in user';
  const visibleNavItems = filterNavItems(navItems, can);

  return (
    <AppShell
      appName={appName}
      appSubtitle={appSubtitle}
      userName={displayName}
      userRole={roleLabel}
      userIdentifier={resolveIdentifier(user)}
      navItems={visibleNavItems}
      onLogout={async () => {
        await logout();
        router.replace('/login');
      }}
    >
      {children}
    </AppShell>
  );
}
