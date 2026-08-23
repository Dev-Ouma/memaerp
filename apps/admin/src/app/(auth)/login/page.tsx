'use client';

import React, { Suspense } from 'react';
import { AuthLayout, LoginForm } from '@mema/auth';

function LoginContent() {
  return (
    <AuthLayout appName="ERP Administration">
      <LoginForm
        title="Administration sign in"
        subtitle="Sign in with your institutional account to access ERP administration."
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
