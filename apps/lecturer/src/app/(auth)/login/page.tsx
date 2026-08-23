'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';
import { MemaLoaderScreen } from '@mema/ui';

function LoginContent() {
  return (
    <AuthLayout appName="Lecturer Portal">
      <LoginForm title="Lecturer sign in" subtitle="Sign in to manage teaching and assessment." />
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
