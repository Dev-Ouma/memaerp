import React from 'react';
import { GraduationCap } from 'lucide-react';

export interface AuthLayoutProps {
  appName: string;
  children: React.ReactNode;
}

export function AuthLayout({ appName, children }: AuthLayoutProps) {
  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <header className="border-b border-slate-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex h-16 max-w-6xl items-center gap-3 px-4 sm:px-6">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-mema-teal-500 to-mema-green-600 text-white shadow">
            <GraduationCap className="h-5 w-5" />
          </div>
          <div>
            <p className="text-sm font-bold text-slate-900">MEMA ERP</p>
            <p className="text-xs text-slate-500">{appName}</p>
          </div>
        </div>
      </header>

      <main className="flex flex-1 items-center justify-center px-4 py-10 sm:px-6">
        <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-card-subtle sm:p-8">
          {children}
        </div>
      </main>
    </div>
  );
}
