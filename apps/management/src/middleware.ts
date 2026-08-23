import { createAuthMiddleware, authMiddlewareConfig } from '@mema/auth/middleware';

export const middleware = createAuthMiddleware();
export const config = authMiddlewareConfig;
