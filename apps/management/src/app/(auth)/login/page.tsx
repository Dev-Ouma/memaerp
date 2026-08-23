'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';

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
    <Suspense fallback={<div className="p-8 text-sm text-slate-600">Loading sign in...</div>}>
      <LoginContent />
    </Suspense>
  );
}
