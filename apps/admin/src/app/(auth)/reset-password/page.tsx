'use client';

import React, { Suspense, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import { AuthLayout } from '@mema/auth';
import { api, ApiError } from '@mema/api-client';
import { Alert, AlertDescription, AlertTitle, Button, Input, MemaLoaderScreen } from '@mema/ui';

function ResetPasswordForm() {
  const search = useSearchParams();
  const initialToken = search.get('token') || '';
  const [email, setEmail] = useState(search.get('email') || '');
  const [token, setToken] = useState(initialToken);
  const [password, setPassword] = useState('');
  const [confirmation, setConfirmation] = useState('');
  const [message, setMessage] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const resetMode = Boolean(initialToken || token);

  const submit = async (event: React.FormEvent) => {
    event.preventDefault(); setLoading(true); setError(null); setMessage(null);
    try {
      if (resetMode) {
        await api.resetPassword({ email, token, password, password_confirmation: confirmation });
        setMessage('Your password has been reset. You can now sign in.');
      } else {
        const result = await api.forgotPassword(email);
        setMessage(result.message);
        if (result.debug_token) setToken(result.debug_token);
      }
    } catch (reason) {
      setError(reason instanceof ApiError ? reason.message : 'The request could not be completed.');
    } finally { setLoading(false); }
  };

  return <AuthLayout appName="ERP Administration"><div className="space-y-5">
    <div><h1 className="font-heading text-2xl font-bold text-slate-900">{resetMode ? 'Choose a new password' : 'Reset password'}</h1><p className="mt-2 text-sm text-slate-600">{resetMode ? 'Use 12–128 characters with uppercase, lowercase, a number, and a symbol.' : 'Enter your institutional email. The response never reveals whether an account exists.'}</p></div>
    {message && <Alert variant="success" role="status"><AlertTitle>Request completed</AlertTitle><AlertDescription>{message}</AlertDescription></Alert>}
    {error && <Alert variant="destructive" role="alert"><AlertTitle>Unable to reset password</AlertTitle><AlertDescription>{error}</AlertDescription></Alert>}
    <form className="space-y-4" onSubmit={submit}>
      <div className="space-y-2"><label htmlFor="email" className="text-sm font-medium">Institutional email</label><Input id="email" type="email" autoComplete="email" value={email} onChange={(event) => setEmail(event.target.value)} required /></div>
      {resetMode && <><div className="space-y-2"><label htmlFor="token" className="text-sm font-medium">Reset token</label><Input id="token" value={token} onChange={(event) => setToken(event.target.value)} required /></div><div className="space-y-2"><label htmlFor="new-password" className="text-sm font-medium">New password</label><Input id="new-password" type="password" autoComplete="new-password" value={password} onChange={(event) => setPassword(event.target.value)} required /></div><div className="space-y-2"><label htmlFor="confirm-password" className="text-sm font-medium">Confirm password</label><Input id="confirm-password" type="password" autoComplete="new-password" value={confirmation} onChange={(event) => setConfirmation(event.target.value)} required /></div></>}
      <Button className="w-full" type="submit" isLoading={loading}>{resetMode ? 'Reset password' : 'Send reset instructions'}</Button>
    </form><a href="/login" className="text-sm font-medium text-mema-teal-800 hover:underline">Back to sign in</a>
  </div></AuthLayout>;
}

export default function ResetPasswordPage() { return <Suspense fallback={<MemaLoaderScreen label="Loading…" />}><ResetPasswordForm /></Suspense>; }
