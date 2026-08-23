'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';
import { MemaLoaderScreen } from '@mema/ui';

function LoginContent() {
  return (
    <AuthLayout appName="Staff Portal">
      <LoginForm title="Staff sign in" subtitle="Sign in to access internal staff services." />
    </AuthLayout>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<MemaLoaderScreen label="Loading sign in…" />}>
      <LoginContent />
    </Suspense>
  );
}
