'use client';

import React from 'react';
import { PortalShell } from '@mema/auth';
import { lecturerNavItems } from '@/config/portal-nav';

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="Lecturer Portal"
      appSubtitle="Teaching, assessment and class management"
      navItems={lecturerNavItems}
    >
      {children}
    </PortalShell>
  );
}
