'use client';

import React, { createContext, useContext, useEffect, useState, useMemo } from 'react';
import type { User, Student } from '@mema/types';
import { api, mockCurrentUser, mockCurrentStudent } from '@mema/api-client';

export interface AuthContextType {
  user: User | null;
  student: Student | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (identifier: string, pass: string) => Promise<void>;
  logout: () => Promise<void>;
  can: (permission: string) => boolean;
  hasRole: (roleName: string) => boolean;
  setUser: React.Dispatch<React.SetStateAction<User | null>>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export interface AuthProviderProps {
  children: React.ReactNode;
  initialUser?: User | null;
  initialStudent?: Student | null;
}

export function AuthProvider({
  children,
  initialUser = null,
  initialStudent = null,
}: AuthProviderProps) {
  const [user, setUser] = useState<User | null>(initialUser || mockCurrentUser);
  const [student, setStudent] = useState<Student | null>(initialStudent || mockCurrentStudent);
  const [isLoading, setIsLoading] = useState<boolean>(false);

  useEffect(() => {
    // Optionally verify active session against backend
    const checkSession = async () => {
      try {
        const u = await api.getCurrentUser();
        setUser(u);
        if (u.user_type === 'STUDENT') {
          const s = await api.getStudentProfile();
          setStudent(s);
        }
      } catch {
        // Fallback to mock session if in dev/offline
        if (typeof process !== 'undefined' && process.env?.NODE_ENV === 'development' && !user) {
          setUser(mockCurrentUser);
          setStudent(mockCurrentStudent);
        }
      }
    };
    checkSession();
  }, [user]);

  const login = async (identifier: string, pass: string) => {
    setIsLoading(true);
    try {
      await api.login({ identifier, password: pass });
      const u = await api.getCurrentUser();
      setUser(u);
      if (u.user_type === 'STUDENT') {
        const s = await api.getStudentProfile();
        setStudent(s);
      }
    } catch (err) {
      // For local prototype simulation
      setUser(mockCurrentUser);
      setStudent(mockCurrentStudent);
    } finally {
      setIsLoading(false);
    }
  };

  const logout = async () => {
    setIsLoading(true);
    try {
      await api.logout();
    } catch {
      // ignore
    } finally {
      setUser(null);
      setStudent(null);
      setIsLoading(false);
    }
  };

  const can = useMemo(() => {
    return (permission: string) => {
      if (!user) return false;
      if (user.roles?.some((r) => r.name === 'SUPERADMIN' || r.name === 'ADMIN')) return true;
      return user.permissions?.includes(permission) ?? false;
    };
  }, [user]);

  const hasRole = useMemo(() => {
    return (roleName: string) => {
      if (!user) return false;
      return user.roles?.some((r) => r.name === roleName) ?? false;
    };
  }, [user]);

  const value = useMemo(
    () => ({
      user,
      student,
      isLoading,
      isAuthenticated: Boolean(user),
      login,
      logout,
      can,
      hasRole,
      setUser,
    }),
    [user, student, isLoading, can, hasRole]
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
