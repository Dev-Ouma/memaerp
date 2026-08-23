'use client';

import * as React from 'react';
import { cn } from '../lib/utils';
import {
  Menu,
  X,
  Bell,
  LogOut,
  GraduationCap,
  PanelLeftClose,
  PanelLeftOpen,
  PanelLeft,
} from 'lucide-react';

export interface NavItem {
  title: string;
  href: string;
  icon: React.ReactNode;
  badge?: string | number;
  /** Hide this item unless the user holds this permission */
  permission?: string;
  /** Hide unless the user holds any of these permissions */
  anyPermission?: string[];
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
  const [mobileOpen, setMobileOpen] = React.useState(false);
  const [isCollapsed, setIsCollapsed] = React.useState(false);

  // Sync pathname and load persisted collapse preference
  React.useEffect(() => {
    if (typeof window !== 'undefined') {
      setPathname(window.location.pathname);
      const saved = localStorage.getItem('mema_sidebar_collapsed');
      if (saved !== null) {
        setIsCollapsed(saved === 'true');
      }
    }
  }, [currentPath]);

  // Keyboard shortcut: ⌘[ or Ctrl+[ or ⌘B / Ctrl+B
  React.useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && (e.key === '[' || e.key === 'b')) {
        e.preventDefault();
        toggleCollapse();
      }
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isCollapsed]);

  const toggleCollapse = () => {
    setIsCollapsed((prev) => {
      const next = !prev;
      if (typeof window !== 'undefined') {
        localStorage.setItem('mema_sidebar_collapsed', String(next));
      }
      return next;
    });
  };

  return (
    <div className="min-h-screen bg-slate-50 text-slate-900 flex flex-col antialiased">
      {/* Mobile backdrop */}
      {mobileOpen && (
        <div
          className="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden transition-opacity"
          onClick={() => setMobileOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-50 bg-mema-teal-900 text-white flex flex-col transition-all duration-300 ease-in-out shadow-xl border-r border-mema-teal-950',
          // Mobile visibility
          mobileOpen ? 'translate-x-0 w-72' : '-translate-x-full lg:translate-x-0',
          // Desktop collapsed vs expanded
          isCollapsed ? 'lg:w-20' : 'lg:w-72'
        )}
      >
        {/* Logo and Brand Header */}
        <div className={cn(
          'h-18 px-4 flex items-center border-b border-mema-teal-800/80 bg-mema-teal-950/40 transition-all duration-300',
          isCollapsed ? 'justify-center' : 'justify-between px-5'
        )}>
          <div className="flex items-center gap-3 overflow-hidden">
            <div className="h-10 w-10 min-w-[40px] rounded-xl bg-gradient-to-br from-mema-teal-500 to-mema-green-600 flex items-center justify-center shadow-lg text-white font-bold flex-shrink-0">
              <GraduationCap className="h-6 w-6" />
            </div>
            {!isCollapsed && (
              <div className="min-w-0 transition-opacity duration-200">
                <span className="font-heading text-base font-bold tracking-tight text-white block truncate leading-tight">
                  MEMA ERP
                </span>
                <span className="text-[11px] text-mema-teal-200 block font-medium truncate">
                  {appName}
                </span>
              </div>
            )}
          </div>

          {/* Desktop Collapse Toggle (ChatGPT style) */}
          <button
            onClick={toggleCollapse}
            title={isCollapsed ? 'Expand sidebar (⌘[)' : 'Collapse sidebar (⌘[)'}
            className={cn(
              'hidden lg:flex p-1.5 rounded-lg text-mema-teal-300 hover:bg-mema-teal-800 hover:text-white transition-all',
              isCollapsed && 'absolute -right-3.5 top-6 z-10 bg-mema-teal-900 border border-mema-teal-700 shadow-md rounded-full p-1 text-white hover:bg-mema-teal-700'
            )}
          >
            {isCollapsed ? (
              <PanelLeftOpen className="h-4 w-4" />
            ) : (
              <PanelLeftClose className="h-4 w-4" />
            )}
          </button>

          {/* Mobile close button */}
          <button
            onClick={() => setMobileOpen(false)}
            className="lg:hidden p-1.5 rounded-lg text-mema-teal-200 hover:bg-mema-teal-800 hover:text-white"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Navigation Items */}
        <div className="flex-1 overflow-y-auto px-3 py-5 space-y-1.5 overflow-x-hidden">
          {!isCollapsed && (
            <div className="px-3 pb-2 text-[10px] font-bold tracking-wider text-mema-teal-300/80 uppercase">
              Menu Navigation
            </div>
          )}

          {navItems.map((item) => {
            const isActive =
              pathname === item.href ||
              (item.href !== '/' && pathname.startsWith(item.href));

            return (
              <div key={item.href} className="relative group">
                <a
                  href={item.href}
                  onClick={() => setMobileOpen(false)}
                  className={cn(
                    'flex items-center rounded-xl text-sm font-medium transition-all duration-150 relative',
                    isCollapsed
                      ? 'justify-center h-11 w-11 mx-auto'
                      : 'justify-between px-3.5 py-2.5',
                    isActive
                      ? 'bg-mema-teal-800 text-white font-semibold shadow-inner border border-mema-teal-700'
                      : 'text-mema-teal-100 hover:bg-mema-teal-800/60 hover:text-white'
                  )}
                  title={isCollapsed ? item.title : undefined}
                >
                  <div className={cn('flex items-center', !isCollapsed && 'gap-3 min-w-0')}>
                    <span
                      className={cn(
                        'transition-colors flex-shrink-0',
                        isActive ? 'text-mema-green-400' : 'text-mema-teal-300 group-hover:text-white'
                      )}
                    >
                      {item.icon}
                    </span>
                    {!isCollapsed && <span className="truncate">{item.title}</span>}
                  </div>

                  {!isCollapsed && item.badge && (
                    <span className="px-2 py-0.5 text-xs rounded-full bg-mema-green-600/90 text-white font-bold shadow-sm flex-shrink-0">
                      {item.badge}
                    </span>
                  )}

                  {/* Dot badge when collapsed */}
                  {isCollapsed && item.badge && (
                    <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-mema-green-400 ring-2 ring-mema-teal-900" />
                  )}
                </a>

                {/* Floating Tooltip flyout on hover when collapsed */}
                {isCollapsed && (
                  <div className="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg shadow-xl border border-slate-700 whitespace-nowrap opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity z-50 flex items-center gap-2">
                    <span>{item.title}</span>
                    {item.badge && (
                      <span className="px-1.5 py-0.2 bg-mema-green-600 text-[10px] rounded-full font-bold">
                        {item.badge}
                      </span>
                    )}
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {/* User Profile & Logout in Sidebar */}
        <div className="p-3 border-t border-mema-teal-800/80 bg-mema-teal-950/50">
          <div className={cn(
            'flex items-center rounded-xl bg-mema-teal-800/40 transition-all',
            isCollapsed ? 'justify-center p-1.5' : 'gap-3 p-2'
          )}>
            <div className="h-9 w-9 min-w-[36px] rounded-full bg-mema-teal-700 text-white flex items-center justify-center font-bold text-xs ring-2 ring-mema-green-500/40 flex-shrink-0">
              {userName
                .split(' ')
                .map((n) => n[0])
                .slice(0, 2)
                .join('')}
            </div>

            {!isCollapsed && (
              <div className="flex-1 min-w-0">
                <p className="text-xs font-semibold text-white truncate leading-tight">{userName}</p>
                <p className="text-[11px] text-mema-teal-300 truncate">{userIdentifier}</p>
              </div>
            )}

            {onLogout && (
              <button
                onClick={onLogout}
                title="Sign out"
                className="p-1.5 rounded-lg text-mema-teal-300 hover:text-rose-300 hover:bg-rose-950/40 transition-colors flex-shrink-0"
              >
                <LogOut className="h-4 w-4" />
              </button>
            )}
          </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <div
        className={cn(
          'flex flex-col flex-1 transition-all duration-300 ease-in-out',
          isCollapsed ? 'lg:pl-20' : 'lg:pl-72'
        )}
      >
        {/* Top Navbar */}
        <header className="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-8 flex items-center justify-between">
          <div className="flex items-center gap-3">
            {/* Mobile menu trigger */}
            <button
              onClick={() => setMobileOpen(true)}
              className="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900"
            >
              <Menu className="h-5 w-5" />
            </button>

            {/* Desktop Quick Collapse/Expand button in top bar */}
            <button
              onClick={toggleCollapse}
              title={isCollapsed ? 'Expand sidebar (⌘[)' : 'Collapse sidebar (⌘[)'}
              className="hidden lg:flex p-2 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors"
            >
              <PanelLeft className="h-5 w-5" />
            </button>

            <div>
              <h1 className="text-base sm:text-lg font-bold text-slate-900 font-heading leading-tight">
                {appName}
              </h1>
              {appSubtitle && (
                <p className="text-xs text-slate-500 hidden sm:block">
                  {appSubtitle}
                </p>
              )}
            </div>
          </div>

          <div className="flex items-center gap-2 sm:gap-3">
            {/* Universal Portal Quick Switcher Bar */}
            <div className="hidden md:flex items-center gap-1 bg-slate-100/90 p-1 rounded-xl border border-slate-200 text-xs font-semibold">
              <a
                href="http://localhost:3000"
                className="px-2.5 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-white hover:shadow-xs transition-all"
                title="University Main Website"
              >
                Home
              </a>
              <a
                href="http://localhost:3001"
                className="px-2.5 py-1.5 rounded-lg text-slate-700 hover:text-slate-900 hover:bg-white hover:shadow-xs transition-all"
                title="Online Applications"
              >
                Apply Now
              </a>
              <a
                href="http://localhost:3002"
                className={cn(
                  'px-2.5 py-1.5 rounded-lg transition-all',
                  appName.toLowerCase().includes('student')
                    ? 'bg-mema-teal-800 text-white font-bold shadow-xs'
                    : 'text-slate-700 hover:text-slate-900 hover:bg-white'
                )}
                title="Student Portal"
              >
                Student Portal
              </a>
              <a
                href="http://localhost:3005"
                className={cn(
                  'px-2.5 py-1.5 rounded-lg transition-all',
                  appName.toLowerCase().includes('admin') || appName.toLowerCase().includes('erp')
                    ? 'bg-mema-teal-800 text-white font-bold shadow-xs'
                    : 'text-slate-700 hover:text-slate-900 hover:bg-white'
                )}
                title="Staff & ERP Administration Console"
              >
                Staff Portal
              </a>
            </div>

            <div className="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
              <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
              <span>2026/2027</span>
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

