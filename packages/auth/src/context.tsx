'use client';

import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react';
import type { AuthUserProfile } from '@mema/types';
import { api } from '@mema/api-client';

export interface AuthContextType {
  user: AuthUserProfile | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (login: string, password: string, remember?: boolean) => Promise<void>;
  logout: () => Promise<void>;
  refreshSession: () => Promise<AuthUserProfile | null>;
  can: (permission: string) => boolean;
  hasRole: (roleCode: string) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const isDemoMode =
  typeof process !== 'undefined' && process.env.NEXT_PUBLIC_AUTH_DEMO === 'true';

export interface AuthProviderProps {
  children: React.ReactNode;
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [user, setUser] = useState<AuthUserProfile | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const refreshSession = useCallback(async (): Promise<AuthUserProfile | null> => {
    try {
      const profile = await api.getCurrentUser();
      setUser(profile);
      return profile;
    } catch {
      setUser(null);
      return null;
    }
  }, []);

  useEffect(() => {
    let cancelled = false;

    const bootstrap = async () => {
      setIsLoading(true);
      try {
        await refreshSession();
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    };

    bootstrap();

    return () => {
      cancelled = true;
    };
  }, [refreshSession]);

  const login = async (loginValue: string, password: string, remember = false) => {
    setIsLoading(true);
    try {
      await api.login({ login: loginValue, password, remember });
      await refreshSession();
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    setIsLoading(true);
    try {
      await api.logout();
    } catch {
      // Session may already be invalid — still clear local state.
    } finally {
      setUser(null);
      setIsLoading(false);
    }
  };

  const can = useMemo(() => {
    return (permission: string) => {
      if (!user) return false;
      if (isDemoMode) return true;
      return user.permissions.includes(permission);
    };
  }, [user]);

  const hasRole = useMemo(() => {
    return (roleCode: string) => {
      if (!user) return false;
      return user.roles.some((role) => role.role_code === roleCode);
    };
  }, [user]);

  const value = useMemo(
    () => ({
      user,
      isLoading,
      isAuthenticated: Boolean(user),
      login,
      logout,
      refreshSession,
      can,
      hasRole,
    }),
    [user, isLoading, refreshSession, can, hasRole]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextType {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
