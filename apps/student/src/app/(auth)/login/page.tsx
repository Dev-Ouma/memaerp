'use client';

import React, { useState, useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { useAuth } from '@mema/auth';
import { ApiError } from '@mema/api-client';
import { Input, Button, Alert, AlertTitle, AlertDescription, MemaLoaderScreen } from '@mema/ui';
import { 
  GraduationCap, User, Lock, Eye, EyeOff, ShieldCheck, 
  ArrowRight, FileText, BookOpen, BarChart3, Clock 
} from 'lucide-react';
import Image from 'next/image';

function StudentLoginContent() {
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
          : 'Invalid student ID or password. Please verify and try again.';
      setError(message);
    }
  };

  const handleDemoFill = () => {
    setLoginValue('student@mema.ac.ke');
    setPassword('password123');
  };

  return (
    <div className="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-slate-50 font-sans">
      
      {/* ── LEFT PANEL: BRAND & FEATURES (5 Cols) ── */}
      <div className="hidden lg:flex lg:col-span-5 relative bg-brand-primary overflow-hidden flex-col justify-between p-12">
        {/* Background Image with Dark Brand Overlay */}
        <div className="absolute inset-0 z-0">
          <Image 
            src="/campus-life.jpg" 
            alt="Mema Atrium" 
            fill 
            style={{ objectFit: 'cover' }} 
            className="opacity-25 filter brightness-75"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-brand-primary-dark via-brand-primary/95 to-brand-primary/80" />
        </div>

        {/* Logo and Name */}
        <div className="relative z-10 flex items-center gap-3">
          <div className="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-secondary to-brand-accent flex items-center justify-center text-white shadow-md">
            <GraduationCap className="w-6 h-6" />
          </div>
          <div>
            <span className="font-extrabold text-base tracking-widest text-white block">MEMA UNIVERSITY</span>
            <span className="text-[10px] text-brand-accent font-bold tracking-widest uppercase block">Student Lifecycle Portal</span>
          </div>
        </div>

        {/* Core Modules Info */}
        <div className="relative z-10 space-y-6">
          <h2 className="text-3xl font-extrabold text-white leading-tight font-heading">
            Your Digital Campus Companion
          </h2>
          <p className="text-slate-300 text-sm leading-relaxed max-w-md">
            The MEMA Student Portal is your integrated link to all academic, financial, and university community services.
          </p>
          
          <div className="grid grid-cols-1 gap-4 pt-4">
            {[
              { icon: BookOpen, title: 'Course Registration', desc: 'Enroll for core and elective units with ease.' },
              { icon: FileText, title: 'Finance & Statements', desc: 'Track fee statements, balances, and make payments.' },
              { icon: BarChart3, title: 'Exam Transcripts', desc: 'Access your grades and progression transcripts instantly.' },
              { icon: Clock, title: 'Academic Timetable', desc: 'Get real-time updates on class lectures and exams.' },
            ].map((mod, i) => (
              <div key={i} className="flex gap-3.5 items-start p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs">
                <div className="p-2 bg-white/10 rounded-lg text-brand-accent">
                  <mod.icon className="w-4 h-4" />
                </div>
                <div>
                  <h4 className="font-bold text-white text-xs uppercase tracking-wider">{mod.title}</h4>
                  <p className="text-slate-300 text-xs mt-0.5">{mod.desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Footer info */}
        <div className="relative z-10 text-2xs text-slate-400">
          © {new Date().getFullYear()} Mema University. Commission for University Education Accredited.
        </div>
      </div>

      {/* ── RIGHT PANEL: LOGIN FORM (7 Cols) ── */}
      <div className="lg:col-span-7 flex items-center justify-center p-6 sm:p-12 md:p-20">
        <div className="w-full max-w-md space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-slate-200 shadow-sm">
          
          {/* Header */}
          <div className="space-y-2 text-center lg:text-left">
            <h1 className="text-2xl font-black text-brand-primary font-heading tracking-tight">Student Portal Sign In</h1>
            <p className="text-sm text-slate-500">Sign in with your student index number or institutional email address.</p>
          </div>

          {error && (
            <Alert variant="destructive" className="rounded-xl border-red-200 bg-red-50 text-red-800">
              <AlertTitle className="font-bold">Sign in failed</AlertTitle>
              <AlertDescription className="text-xs">{error}</AlertDescription>
            </Alert>
          )}

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-5">
            
            <div className="space-y-1.5">
              <label htmlFor="login" className="text-xs font-bold text-slate-700 block uppercase tracking-wider">
                Student ID or Email
              </label>
              <Input
                id="login"
                name="login"
                autoComplete="username"
                required
                value={loginValue}
                onChange={(e) => setLoginValue(e.target.value)}
                leftIcon={<User className="h-4 w-4 text-slate-400" />}
                placeholder="e.g. student@mema.ac.ke"
                className="h-11 rounded-lg border-slate-300 focus:ring-brand-primary"
              />
            </div>

            <div className="space-y-1.5">
              <div className="flex items-center justify-between">
                <label htmlFor="password" className="text-xs font-bold text-slate-700 uppercase tracking-wider">
                  Secret Password
                </label>
                <a href="/reset-password" className="text-xs font-semibold text-brand-accent hover:underline">
                  Forgot Password?
                </a>
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
                  placeholder="Enter your portal password"
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
              className="w-full bg-brand-primary hover:bg-brand-primary-dark text-white font-bold h-11 rounded-lg shadow-sm gap-2 transition-all"
              isLoading={isLoading}
            >
              Access My Portal <ArrowRight className="w-4 h-4" />
            </Button>

          </form>

          {/* Quick-fill Helper for Testers/Users */}
          <div className="p-4 bg-brand-primary/5 rounded-xl border border-brand-primary/10 space-y-2">
            <div className="flex items-center justify-between text-xs">
              <span className="font-bold text-brand-primary flex items-center gap-1">
                💡 Testing / Demo Access
              </span>
              <button 
                onClick={handleDemoFill}
                className="text-brand-accent hover:text-brand-accent-dark font-black hover:underline"
              >
                Quick Auto-fill
              </button>
            </div>
            <p className="text-3xs text-slate-500 leading-normal">
              Click Quick Auto-fill to enter standard testing student credentials: <code className="font-mono bg-white px-1 py-0.5 rounded border border-slate-200">student@mema.ac.ke / password123</code>.
            </p>
          </div>

          {/* External links */}
          <div className="flex items-center justify-between border-t border-slate-100 pt-5 text-xs text-slate-500">
            <span>New Student?</span>
            <a href="http://localhost:3001/" className="font-extrabold text-brand-secondary hover:underline flex items-center gap-1">
              Start Application <ArrowRight className="w-3 h-3" />
            </a>
          </div>

          <div className="flex justify-center gap-1.5 text-3xs text-slate-400 items-center">
            <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
            <span>Secured with MEMA IAM Policy · TLS 1.3 encrypted connection</span>
          </div>

        </div>
      </div>

    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<MemaLoaderScreen label="Loading student sign in…" />}>
      <StudentLoginContent />
    </Suspense>
  );
}
