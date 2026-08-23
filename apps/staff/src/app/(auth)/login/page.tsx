'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';

function LoginContent() {
  return (
    <AuthLayout appName="Staff Portal">
      <LoginForm title="Staff sign in" subtitle="Sign in to access internal staff services." />
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
