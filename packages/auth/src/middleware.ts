import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const DEFAULT_PUBLIC_PATHS = ['/login', '/mfa', '/reset-password'];
const SESSION_COOKIES = [
  'laravel_session',
  'mema-erp-session',
  '__Host-mema_session',
  '__Host-ERPSESSION',
  'mema_session',
];

export interface AuthMiddlewareOptions {
  publicPaths?: string[];
}

export function createAuthMiddleware(options: AuthMiddlewareOptions = {}) {
  const publicPaths = [...DEFAULT_PUBLIC_PATHS, ...(options.publicPaths ?? [])];

  return function middleware(request: NextRequest) {
    const { pathname } = request.nextUrl;
    const isPublic = publicPaths.some(
      (path) => pathname === path || pathname.startsWith(`${path}/`)
    );
    const hasSessionCookie = SESSION_COOKIES.some((name) => request.cookies.has(name));

    if (!isPublic && !hasSessionCookie) {
      const loginUrl = new URL('/login', request.url);
      if (pathname !== '/') {
        loginUrl.searchParams.set('next', pathname);
      }
      return NextResponse.redirect(loginUrl);
    }

    return NextResponse.next();
  };
}

export const authMiddlewareConfig = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
