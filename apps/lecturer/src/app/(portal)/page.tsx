'use client';

import React, { useMemo } from 'react';
import Link from 'next/link';
import { useQuery } from '@tanstack/react-query';
import {
  StatCard,
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Button,
  Badge,
} from '@mema/ui';
import { BookOpen, ClipboardList, QrCode, ArrowRight, Users } from 'lucide-react';
import { api } from '@mema/api-client';
import { lecturerModuleLinks } from '@/config/portal-nav';
import { ModuleHub } from '@/components/module-hub';

export default function LecturerDashboardPage() {
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const activeSessions = useQuery({
    queryKey: ['attendance', 'active'],
    queryFn: () => api.getActiveAttendanceSessions(),
    refetchInterval: 30_000,
  });
  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });

  const myOfferings = useMemo(() => {
    const uid = user.data?.id;
    if (!uid) return offerings.data ?? [];
    return (offerings.data ?? []).filter((o) => o.lecturer_id === uid);
  }, [offerings.data, user.data?.id]);

  const totalEnrolled = myOfferings.reduce((sum, o) => sum + (o.enrolled_count ?? 0), 0);
  const openSessions = activeSessions.data?.length ?? 0;

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Teaching Dashboard</h2>
        <p className="text-sm text-slate-500 mt-1">
          Offerings, marks entry, and QR attendance — all modules in one place.
        </p>
      </div>

      <ModuleHub items={lecturerModuleLinks} />

      <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <StatCard
          title="My Offerings"
          value={String(myOfferings.length)}
          description="Assigned sections this term"
          icon={<BookOpen className="h-5 w-5" />}
        />
        <StatCard
          title="Students Taught"
          value={String(totalEnrolled)}
          description="Total enrolled across offerings"
          icon={<Users className="h-5 w-5" />}
        />
        <StatCard
          title="Open QR Sessions"
          value={String(openSessions)}
          description="Active attendance clocks"
          icon={<QrCode className="h-5 w-5" />}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle>My Offerings</CardTitle>
              <CardDescription>Sections assigned to you</CardDescription>
            </div>
            <Link href="/offerings">
              <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                View all <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-3">
            {myOfferings.slice(0, 5).map((offering) => (
              <div key={offering.id} className="flex items-center justify-between text-sm border-b border-slate-100 pb-2 last:border-0">
                <div>
                  <div className="font-semibold text-slate-900">{offering.course?.code}</div>
                  <div className="text-xs text-slate-500">{offering.section_code}</div>
                </div>
                <Badge variant="outline">
                  {offering.enrolled_count}/{offering.max_capacity}
                </Badge>
              </div>
            ))}
            {myOfferings.length === 0 && (
              <p className="text-sm text-slate-500">
                {offerings.isLoading ? 'Loading offerings…' : 'No offerings assigned yet.'}
              </p>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle className="flex items-center gap-2">
                <QrCode className="h-5 w-5 text-mema-teal-700" />
                Attendance
              </CardTitle>
              <CardDescription>Open a QR session for in-class check-in</CardDescription>
            </div>
            <Link href="/attendance">
              <Button size="sm" className="gap-2">
                Open session <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-3">
            {(activeSessions.data ?? []).map((session) => {
              const offering = session.course_offering as { course?: { code?: string }; section_code?: string };
              return (
                <div key={String(session.id)} className="flex items-center justify-between text-sm">
                  <span>
                    {offering?.course?.code ?? 'Course'} · {offering?.section_code ?? '—'}
                  </span>
                  <Badge variant="success">OPEN</Badge>
                </div>
              );
            })}
            {openSessions === 0 && (
              <p className="text-sm text-slate-500">No open sessions. Start one from Attendance.</p>
            )}
            <Link href="/marks">
              <Button variant="outline" size="sm" className="gap-2 mt-2">
                <ClipboardList className="h-4 w-4" /> Marks entry
              </Button>
            </Link>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
