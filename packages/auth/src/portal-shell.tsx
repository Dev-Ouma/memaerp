'use client';

import React, { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import type { AuthUserProfile } from '@mema/types';
import { AppShell, MemaLoaderScreen, type NavItem } from '@mema/ui';
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

  useEffect(() => {
    if (!isLoading && !user) {
      router.replace('/login');
    }
  }, [isLoading, router, user]);

  if (isLoading) {
    return <MemaLoaderScreen label="Loading your session…" />;
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
