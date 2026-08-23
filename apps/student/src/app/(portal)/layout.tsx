'use client';

import React from 'react';
import { PortalShell } from '@mema/auth';
import { studentNavItems } from '@/config/portal-nav';

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
