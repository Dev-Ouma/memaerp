'use client';

import React from 'react';
import { PortalShell } from '@mema/auth';
import { staffNavItems } from '@/config/portal-nav';

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Staff Portal"
      appSubtitle="Administrative services and internal workflows"
      navItems={staffNavItems}
    >
      {children}
    </PortalShell>
  );
}
