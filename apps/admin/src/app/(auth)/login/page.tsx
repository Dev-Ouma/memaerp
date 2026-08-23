'use client';

import React, { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAuth } from '@mema/auth';
import { ApiError } from '@mema/api-client';
import { Input, Button, Alert, AlertTitle, AlertDescription } from '@mema/ui';
import { 
  GraduationCap, User, Lock, Eye, EyeOff, ShieldCheck, 
  ArrowRight, ShieldAlert, Cpu, Activity, UserCheck
} from 'lucide-react';

const demoRoles = [
  { label: 'System Admin', email: 'admin@mema.ac.ke', desc: 'Full ERP & IAM settings' },
  { label: 'Registrar', email: 'registrar@mema.ac.ke', desc: 'Admissions & programmes' },
  { label: 'Finance Officer', email: 'finance@mema.ac.ke', desc: 'Ledgers & fee statements' },
  { label: 'Dean', email: 'dean@mema.ac.ke', desc: 'Faculty, student records' },
];

function AdminLoginContent() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { login, isLoading, user } = useAuth();
  
  const [loginValue, setLoginValue] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const nextPath = searchParams.get('next') || '/';

  useEffect(() => {
    if (user) {
      router.replace(nextPath);
    }
  }, [user, router, nextPath]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      const result = await login(loginValue.trim(), password);
      if (result.mfaRequired) {
        router.replace(`/mfa?next=${encodeURIComponent(nextPath)}`);
      } else {
        router.replace(nextPath);
      }
    } catch (err) {
      const message =
        err instanceof ApiError
          ? err.message
          : 'Invalid credentials. Please contact the ICT Helpdesk.';
      setError(message);
    }
  };

  const handleRoleSelect = (email: string) => {
    setLoginValue(email);
    setPassword('password123');
  };

  return (
    <div className="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-slate-50 font-sans">
      
      {/* ── LEFT PANEL: SECURE ERP INFRA (5 Cols) ── */}
      <div className="hidden lg:flex lg:col-span-5 relative bg-[#072d3a] overflow-hidden flex-col justify-between p-12 border-r border-slate-800">
        
        {/* Logo and Name */}
        <div className="z-10 flex items-center gap-3">
          <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-accent to-red-600 flex items-center justify-center text-white shadow-md">
            <GraduationCap className="w-6 h-6" />
          </div>
          <div>
            <span className="font-extrabold text-base tracking-widest text-white block">MEMA UNIVERSITY</span>
            <span className="text-[10px] text-red-500 font-bold tracking-widest uppercase block">ERP Administration Console</span>
          </div>
        </div>

        {/* Console Security Info */}
        <div className="z-10 space-y-6">
          <h2 className="text-3xl font-extrabold text-white leading-tight font-heading">
            Enterprise Resource Planning
          </h2>
          <p className="text-slate-400 text-sm leading-relaxed max-w-sm">
            Access secure back-office controllers for academic schedules, student tracking, invoicing, and IAM directory configurations.
          </p>
          
          <div className="grid grid-cols-1 gap-4 pt-4">
            {[
              { icon: ShieldCheck, title: 'Role-Based Access Control', desc: 'Granular token-based authorization scopes.' },
              { icon: Cpu, title: 'Centralized Lifecycle Logs', desc: 'Continuous audit logs recording administrative updates.' },
              { icon: Activity, title: 'Operational Node Health', desc: 'Multi-region failover configuration ready.' },
            ].map((feat, i) => (
              <div key={i} className="flex gap-3.5 items-start p-3.5 rounded-xl bg-white/5 border border-white/5 backdrop-blur-xs">
                <div className="p-2 bg-white/5 rounded-lg text-red-500">
                  <feat.icon className="w-4 h-4" />
                </div>
                <div>
                  <h4 className="font-bold text-white text-xs uppercase tracking-wider">{feat.title}</h4>
                  <p className="text-slate-400 text-xs mt-0.5">{feat.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Footer info */}
        <div className="z-10 text-2xs text-slate-500 flex items-center gap-2">
          <span className="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
          <span>All Administration Systems Operational</span>
        </div>
      </div>

      {/* ── RIGHT PANEL: SECURE FORM (7 Cols) ── */}
      <div className="lg:col-span-7 flex items-center justify-center p-6 sm:p-12 md:p-20">
        <div className="w-full max-w-md space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-sm">
          
          {/* Header */}
          <div className="space-y-2 text-center lg:text-left">
            <h1 className="text-2xl font-black text-brand-primary font-heading tracking-tight">ERP Administrator Login</h1>
            <p className="text-sm text-slate-500">Sign in with your administrative account to access the control panel.</p>
          </div>

          {/* Warning banner */}
          <div className="p-3 bg-red-50 border border-red-100 rounded-xl flex items-start gap-2 text-red-800">
            <ShieldAlert className="w-4 h-4 text-red-600 flex-shrink-0 mt-0.5" />
            <p className="text-[10px] leading-normal font-semibold">
              Warning: Unauthorized access is strictly prohibited and subject to system-wide auditing and legal prosecution.
            </p>
          </div>

          {error && (
            <Alert variant="destructive" className="rounded-xl border-red-200 bg-red-50 text-red-800">
              <AlertTitle className="font-bold">Access Denied</AlertTitle>
              <AlertDescription className="text-xs">{error}</AlertDescription>
            </Alert>
          )}

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-5">
            
            <div className="space-y-1.5">
              <label htmlFor="login" className="text-xs font-bold text-slate-700 block uppercase tracking-wider">
                Institutional Email
              </label>
              <Input
                id="login"
                name="login"
                autoComplete="username"
                required
                value={loginValue}
                onChange={(e) => setLoginValue(e.target.value)}
                leftIcon={<User className="h-4 w-4 text-slate-400" />}
                placeholder="e.g. admin@mema.ac.ke"
                className="h-11 rounded-lg border-slate-300 focus:ring-brand-primary"
              />
            </div>

            <div className="space-y-1.5">
              <div className="flex items-center justify-between">
                <label htmlFor="password" className="text-xs font-bold text-slate-700 uppercase tracking-wider">
                  ERP Security Key
                </label>
              </div>
              <div className="relative">
                <Input
                  id="password"
                  name="password"
                  type={showPassword ? 'text' : 'password'}
                  autoComplete="current-password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  leftIcon={<Lock className="h-4 w-4 text-slate-400" />}
                  placeholder="Enter your security password"
                  className="h-11 rounded-lg border-slate-300 focus:ring-brand-primary pr-10"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3 top-3.5 text-slate-400 hover:text-slate-600"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <Button
              type="submit"
              className="w-full bg-[#0A3E50] hover:bg-[#072d3a] text-white font-bold h-11 rounded-lg shadow-sm gap-2 transition-all"
              isLoading={isLoading}
            >
              Sign In to ERP <ArrowRight className="w-4 h-4" />
            </Button>

          </form>

          {/* Quick-fill Roles Selector */}
          <div className="space-y-2">
            <span className="font-bold text-xs text-brand-primary block uppercase tracking-wider">
              🔑 One-Click Demo Role Access
            </span>
            <div className="grid grid-cols-2 gap-2">
              {demoRoles.map((role, idx) => (
                <button
                  key={idx}
                  onClick={() => handleRoleSelect(role.email)}
                  className="p-2 border border-slate-200 hover:border-brand-primary hover:bg-slate-50 text-left rounded-lg transition-all"
                >
                  <div className="font-bold text-2xs text-brand-primary flex items-center gap-1">
                    <UserCheck className="w-3 h-3 text-brand-accent" />
                    {role.label}
                  </div>
                  <span className="text-[9px] text-slate-500 block truncate">{role.desc}</span>
                </button>
              ))}
            </div>
          </div>

          <div className="flex justify-center gap-1.5 text-3xs text-slate-400 items-center">
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
            <span>MEMA ERP SecurSession active · TLS 1.3 encrypted</span>
          </div>

        </div>
      </div>

    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<div className="p-8 text-sm text-slate-600">Loading sign in...</div>}>
      <AdminLoginContent />
    </Suspense>
  );
}
