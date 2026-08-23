'use client';

import * as React from 'react';
import { cn } from '../lib/utils';
import {
  Menu,
  X,
  Bell,
  LogOut,
  GraduationCap,
} from 'lucide-react';

export interface NavItem {
  title: string;
  href: string;
  icon: React.ReactNode;
  badge?: string | number;
}

export interface AppShellProps {
  appName: string;
  appSubtitle?: string;
  navItems: NavItem[];
  userName?: string;
  userRole?: string;
  userIdentifier?: string;
  avatarUrl?: string;
  currentPath?: string;
  onLogout?: () => void;
  children: React.ReactNode;
}

export function AppShell({
  appName,
  appSubtitle,
  navItems,
  userName = 'Ian Wabwire',
  userRole = 'Student',
  userIdentifier = 'CT201/0042/23',
  currentPath,
  onLogout,
  children,
}: AppShellProps) {
  const [pathname, setPathname] = React.useState(currentPath || '/');
  const [sidebarOpen, setSidebarOpen] = React.useState(false);

  React.useEffect(() => {
    if (typeof window !== 'undefined') {
      setPathname(window.location.pathname);
    }
  }, [currentPath]);

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 flex flex-col antialiased">
      {/* Mobile backdrop */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 w-72 bg-mema-teal-900 text-white flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 shadow-xl border-r border-mema-teal-950',
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        )}
      >
        {/* Logo and Brand */}
        <div className="h-18 px-6 flex items-center justify-between border-b border-mema-teal-800/80 bg-mema-teal-950/40">
          <div className="flex items-center gap-3">
            <div className="h-10 w-10 rounded-xl bg-gradient-to-br from-mema-teal-500 to-mema-green-600 flex items-center justify-center shadow-lg text-white font-bold">
              <GraduationCap className="h-6 w-6" />
            </div>
            <div>
              <span className="font-heading text-lg font-bold tracking-tight text-white block">
                MEMA ERP
              </span>
              <span className="text-xs text-mema-teal-200 block font-medium">
                {appName}
              </span>
            </div>
          </div>
          <button
            onClick={() => setSidebarOpen(false)}
            className="lg:hidden p-1.5 rounded-lg text-mema-teal-200 hover:bg-mema-teal-800 hover:text-white"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Navigation Items */}
        <div className="flex-1 overflow-y-auto px-4 py-6 space-y-1.5">
          <div className="px-3 pb-2 text-[11px] font-semibold tracking-wider text-mema-teal-300 uppercase">
            Menu Navigation
          </div>
          {navItems.map((item) => {
            const isActive =
              pathname === item.href ||
              (item.href !== '/' && pathname.startsWith(item.href));
            return (
              <a
                key={item.href}
                href={item.href}
                onClick={() => setSidebarOpen(false)}
                className={cn(
                  'flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group',
                  isActive
                    ? 'bg-mema-teal-800 text-white font-semibold shadow-inner border border-mema-teal-700'
                    : 'text-mema-teal-100 hover:bg-mema-teal-800/60 hover:text-white'
                )}
              >
                <div className="flex items-center gap-3">
                  <span
                    className={cn(
                      'transition-colors',
                      isActive ? 'text-mema-green-400' : 'text-mema-teal-300 group-hover:text-white'
                    )}
                  >
                    {item.icon}
                  </span>
                  <span>{item.title}</span>
                </div>
                {item.badge && (
                  <span className="px-2 py-0.5 text-xs rounded-full bg-mema-green-600/90 text-white font-bold shadow-sm">
                    {item.badge}
                  </span>
                )}
              </a>
            );
          })}
        </div>

        {/* User Card & Logout in Sidebar */}
        <div className="p-4 border-t border-mema-teal-800/80 bg-mema-teal-950/50">
          <div className="flex items-center gap-3 p-2 rounded-xl bg-mema-teal-800/40">
            <div className="h-10 w-10 rounded-full bg-mema-teal-700 text-white flex items-center justify-center font-bold text-sm ring-2 ring-mema-green-500/40">
              {userName
                .split(' ')
                .map((n) => n[0])
                .join('')}
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-semibold text-white truncate">{userName}</p>
              <p className="text-xs text-mema-teal-300 truncate">{userIdentifier}</p>
            </div>
            {onLogout && (
              <button
                onClick={onLogout}
                title="Sign out"
                className="p-2 rounded-lg text-mema-teal-300 hover:text-rose-300 hover:bg-rose-950/40 transition-colors"
              >
                <LogOut className="h-4 w-4" />
              </button>
            )}
          </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="lg:pl-72 flex flex-col flex-1">
        {/* Top Navbar */}
        <header className="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <button
              onClick={() => setSidebarOpen(true)}
              className="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900"
            >
              <Menu className="h-5 w-5" />
            </button>
            <div>
              <h1 className="text-base sm:text-lg font-bold text-slate-900 font-heading">
                {appName}
              </h1>
              {appSubtitle && (
                <p className="text-xs text-slate-500 hidden sm:block">
                  {appSubtitle}
                </p>
              )}
            </div>
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
              <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
              <span>Academic Year 2026/2027</span>
            </div>

            <button
              className="relative p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors"
              title="Notifications"
            >
              <Bell className="h-5 w-5" />
              <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white" />
            </button>

            <div className="h-8 w-px bg-slate-200 hidden sm:block" />

            <div className="flex items-center gap-2">
              <div className="text-right hidden sm:block">
                <span className="text-xs font-semibold text-slate-900 block leading-tight">
                  {userName}
                </span>
                <span className="text-[11px] text-slate-500 font-medium">{userRole}</span>
              </div>
            </div>
          </div>
        </header>

        {/* Page Body */}
        <main className="flex-1 p-4 sm:p-8 max-w-7xl w-full mx-auto">
          {children}
        </main>
      </div>
    </div>
  );
}
