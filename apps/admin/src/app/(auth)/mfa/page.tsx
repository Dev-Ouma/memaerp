'use client';

import React, { Suspense, useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { AuthLayout, useAuth } from '@mema/auth';
import { Alert, AlertDescription, AlertTitle, Button, Input, MemaLoaderScreen } from '@mema/ui';

function MfaForm() {
  const router = useRouter();
  const search = useSearchParams();
  const { pendingMfaChallenge, verifyMfa, isLoading } = useAuth();
  const [code, setCode] = useState('');
  const [error, setError] = useState<string | null>(null);

  const submit = async (event: React.FormEvent) => {
    event.preventDefault();
    setError(null);
    try {
      await verifyMfa(code.trim());
      router.replace(search.get('next') || '/');
    } catch (reason) {
      setError(reason instanceof Error ? reason.message : 'The authentication code is invalid.');
    }
  };

  return (
    <AuthLayout appName="ERP Administration">
      <div className="space-y-5">
        <div>
          <h1 className="font-heading text-2xl font-bold text-slate-900">Multi-factor authentication</h1>
          <p className="mt-2 text-sm text-slate-600">Enter the six-digit authenticator code or one unused recovery code.</p>
        </div>
        {!pendingMfaChallenge && <Alert variant="destructive"><AlertTitle>No active challenge</AlertTitle><AlertDescription>Return to sign in and verify your password first.</AlertDescription></Alert>}
        {error && <Alert variant="destructive" role="alert"><AlertTitle>Verification failed</AlertTitle><AlertDescription>{error}</AlertDescription></Alert>}
        <form className="space-y-4" onSubmit={submit}>
          <div className="space-y-2"><label htmlFor="mfa-code" className="text-sm font-medium text-slate-700">Authentication code</label><Input id="mfa-code" inputMode="numeric" autoComplete="one-time-code" value={code} onChange={(event) => setCode(event.target.value)} required /></div>
          <Button className="w-full" type="submit" disabled={!pendingMfaChallenge} isLoading={isLoading}>Verify and continue</Button>
        </form>
        <a href="/login" className="text-sm font-medium text-mema-teal-800 hover:underline">Back to sign in</a>
      </div>
    </AuthLayout>
  );
}

export default function MfaPage() {
  return <Suspense fallback={<MemaLoaderScreen label="Loading verification…" />}><MfaForm /></Suspense>;
}
