'use client';

import React, { useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { Alert, AlertDescription, AlertTitle, Button, Input } from '@mema/ui';
import { Lock, User } from 'lucide-react';
import { ApiError } from '@mema/api-client';
import { useAuth } from '../context';

export interface LoginFormProps {
  title?: string;
  subtitle?: string;
  defaultNext?: string;
}

export function LoginForm({
  title = 'Sign in to MEMA ERP',
  subtitle = 'Use your institutional username or email address.',
  defaultNext = '/',
}: LoginFormProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { login, isLoading, user } = useAuth();
  const [loginValue, setLoginValue] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);

  const nextPath = searchParams.get('next') || defaultNext;

  React.useEffect(() => {
    if (user) {
      router.replace(nextPath);
    }
  }, [user, router, nextPath]);

  const handleSubmit = async (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError(null);

    try {
      await login(loginValue.trim(), password);
      router.replace(nextPath);
    } catch (err) {
      const message =
        err instanceof ApiError
          ? err.message
          : 'Unable to sign in. Check your credentials and try again.';
      setError(message);
    }
  };

  return (
    <div className="w-full max-w-md space-y-6">
      <div className="space-y-2 text-center sm:text-left">
        <h1 className="text-2xl font-bold text-slate-900 font-heading">{title}</h1>
        <p className="text-sm text-slate-600">{subtitle}</p>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Sign in failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="space-y-2">
          <label htmlFor="login" className="text-sm font-medium text-slate-700">
            Username or email
          </label>
          <Input
            id="login"
            name="login"
            autoComplete="username"
            required
            value={loginValue}
            onChange={(event) => setLoginValue(event.target.value)}
            leftIcon={<User className="h-4 w-4" />}
            placeholder="e.g. registrar@mema.ac.ke"
          />
        </div>

        <div className="space-y-2">
          <div className="flex items-center justify-between">
            <label htmlFor="password" className="text-sm font-medium text-slate-700">
              Password
            </label>
            <a
              href="/reset-password"
              className="text-xs font-medium text-mema-teal-800 hover:underline"
            >
              Forgot password?
            </a>
          </div>
          <Input
            id="password"
            name="password"
            type="password"
            autoComplete="current-password"
            required
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            leftIcon={<Lock className="h-4 w-4" />}
            placeholder="Enter your password"
          />
        </div>

        <Button type="submit" className="w-full" size="lg" isLoading={isLoading}>
          Sign in
        </Button>
      </form>
    </div>
  );
}
