'use client';

import { AppProviders } from '@mema/auth';

export function ApplicantProviders({ children }: { children: React.ReactNode }) {
  return <AppProviders>{children}</AppProviders>;
}
