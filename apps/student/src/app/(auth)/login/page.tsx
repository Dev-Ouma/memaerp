'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';

function LoginContent() {
  return (
    <AuthLayout appName="Student Portal">
      <LoginForm
        title="Student portal sign in"
        subtitle="Sign in with your student number or institutional email address."
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
