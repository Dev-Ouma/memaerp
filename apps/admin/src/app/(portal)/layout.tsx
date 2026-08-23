'use client';

import React from 'react';
import { PortalShell } from '@mema/auth';
import { adminNavItems } from '@/config/portal-nav';

export default function PortalLayout({ children }: { children: React.ReactNode }) {
  return (
    <PortalShell
      appName="ERP Administration"
      appSubtitle="Institutional Master Control · Registrar & Finance"
      navItems={adminNavItems}
    >
      {children}
    </PortalShell>
  );
}
