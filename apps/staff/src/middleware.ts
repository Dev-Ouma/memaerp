import { createAuthMiddleware } from '@mema/auth/middleware';

export const middleware = createAuthMiddleware();

export const config = {
  matcher: ['/((?!_next/static|_next/image|favicon.ico).*)'],
};
