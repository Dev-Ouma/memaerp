'use client';

import React, { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { useAuth } from '@mema/auth';
import { api, ApiError } from '@mema/api-client';
import type { Application } from '@mema/types';
import {
  Alert,
  AlertDescription,
  AlertTitle,
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Input,
  MemaLoaderInline,
} from '@mema/ui';
import { ArrowRight, FileSearch, LogIn } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Unable to sign in.';

function statusVariant(status?: string) {
  if (status === 'ADMITTED' || status === 'ACCEPTED' || status === 'MATRICULATED') return 'success' as const;
  if (status === 'REJECTED' || status === 'EXPIRED') return 'destructive' as const;
  if (status === 'UNDER_REVIEW' || status === 'SUBMITTED') return 'warning' as const;
  return 'outline' as const;
}

export default function ApplicantStatusPage() {
  const { login, user, isLoading: authLoading } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loginError, setLoginError] = useState<string | null>(null);
  const [signingIn, setSigningIn] = useState(false);

  const applications = useQuery({
    queryKey: ['admissions', 'my-applications'],
    queryFn: () => api.getApplications(),
    enabled: Boolean(user),
  });

  async function handleLogin(event: React.FormEvent) {
    event.preventDefault();
    setSigningIn(true);
    setLoginError(null);
    try {
      const result = await login(email.trim(), password);
      if (result.mfaRequired) {
        setLoginError('MFA is required for this account. Use the main applicant registration flow.');
      }
    } catch (reason) {
      setLoginError(messageFrom(reason));
    } finally {
      setSigningIn(false);
    }
  }

  return (
    <div className="max-w-3xl mx-auto space-y-8 py-4">
      <div className="text-center space-y-2">
        <h1 className="text-3xl font-black text-brand-primary font-heading">Track Your Application</h1>
        <p className="text-sm text-slate-500">
          Sign in with the email and password you used when applying to view status and updates.
        </p>
      </div>

      {!user && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <LogIn className="h-5 w-5 text-brand-primary" />
              Applicant sign in
            </CardTitle>
            <CardDescription>Use the credentials created during application submission.</CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleLogin} className="space-y-4">
              {loginError && (
                <Alert variant="destructive">
                  <AlertTitle>Sign in failed</AlertTitle>
                  <AlertDescription>{loginError}</AlertDescription>
                </Alert>
              )}
              <div>
                <label className="text-xs font-bold text-slate-700 block mb-1">Email</label>
                <Input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@example.com"
                  required
                />
              </div>
              <div>
                <label className="text-xs font-bold text-slate-700 block mb-1">Password</label>
                <Input
                  type="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>
              <Button type="submit" disabled={signingIn || authLoading} className="gap-2 w-full">
                {signingIn ? <MemaLoaderInline size={40} /> : <LogIn className="h-4 w-4" />}
                View my applications
              </Button>
            </form>
            <p className="text-xs text-slate-500 mt-4 text-center">
              New applicant?{' '}
              <a href="/" className="font-bold text-brand-primary hover:underline">
                Start a fresh application
              </a>
            </p>
          </CardContent>
        </Card>
      )}

      {user && (
        <>
          <Alert>
            <AlertTitle>Signed in as {user.email}</AlertTitle>
            <AlertDescription>Showing applications linked to your applicant account.</AlertDescription>
          </Alert>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileSearch className="h-5 w-5 text-brand-primary" />
                My applications
              </CardTitle>
              <CardDescription>Live status from the admissions module</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              {(applications.data ?? []).map((app: Application) => (
                <div
                  key={app.id}
                  className="rounded-xl border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                >
                  <div>
                    <div className="font-mono font-bold text-brand-primary">{app.application_number}</div>
                    <div className="text-sm text-slate-700">{app.programme?.name ?? app.programme?.title}</div>
                    <div className="text-xs text-slate-500 mt-1">
                      Submitted {app.submitted_at ? new Date(app.submitted_at).toLocaleDateString() : '—'} · Fee{' '}
                      {app.is_fee_paid ? 'paid' : 'pending'}
                    </div>
                  </div>
                  <Badge variant={statusVariant(app.status)}>{app.status.replaceAll('_', ' ')}</Badge>
                </div>
              ))}
              {(applications.data ?? []).length === 0 && (
                <p className="text-sm text-slate-500 text-center py-8">
                  {applications.isLoading ? 'Loading applications…' : 'No applications found for this account.'}
                </p>
              )}
            </CardContent>
          </Card>

          <div className="text-center">
            <a href="/" className="inline-flex items-center gap-2 text-sm font-bold text-brand-secondary hover:underline">
              Submit another application <ArrowRight className="h-4 w-4" />
            </a>
          </div>
        </>
      )}
    </div>
  );
}
