'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';

function LoginContent() {
  return (
    <AuthLayout appName="Lecturer Portal">
      <LoginForm title="Lecturer sign in" subtitle="Sign in to manage teaching and assessment." />
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
