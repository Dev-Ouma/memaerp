'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';
import { MemaLoaderScreen } from '@mema/ui';

function LoginContent() {
  return (
    <AuthLayout appName="Management Dashboard">
      <LoginForm
        title="Executive sign in"
        subtitle="Sign in to access institutional analytics and executive reporting."
      />
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
