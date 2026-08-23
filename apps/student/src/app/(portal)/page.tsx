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
  Alert,
  AlertTitle,
  AlertDescription,
  formatCurrency,
} from '@mema/ui';
import { BookOpen, Award, CreditCard, ArrowRight, Clock, ScanLine, AlertTriangle } from 'lucide-react';
import { api } from '@mema/api-client';
import { studentModuleLinks } from '@/config/portal-nav';
import { ModuleHub } from '@/components/module-hub';

export default function StudentDashboardPage() {
  const dashboard = useQuery({ queryKey: ['portal', 'dashboard'], queryFn: () => api.getPortalDashboard() });
  const attendance = useQuery({ queryKey: ['attendance', 'my-record'], queryFn: () => api.getMyAttendanceRecord() });

  const data = dashboard.data ?? {};
  const student = (data.student ?? {}) as Record<string, string>;
  const finance = (data.finance ?? {}) as Record<string, number | boolean>;
  const registration = (data.registration ?? {}) as Record<string, unknown>;
  const academics = (data.academics ?? {}) as Record<string, string | number>;
  const alerts = (data.alerts ?? []) as Array<{ level: string; message: string }>;

  const courses = (attendance.data?.courses as Array<Record<string, unknown>> | undefined) ?? [];
  const atRiskCount = useMemo(() => courses.filter((c) => c.at_risk).length, [courses]);
  const avgAttendance = useMemo(() => {
    if (courses.length === 0) return null;
    const sum = courses.reduce((acc, c) => acc + Number(c.attendance_percentage ?? 0), 0);
    return Math.round(sum / courses.length);
  }, [courses]);

  return (
    <div className="space-y-8">
      {alerts.map((alert) => (
        <Alert key={alert.message} variant={alert.level === 'warning' ? 'warning' : 'default'}>
          <AlertTitle>{alert.level === 'warning' ? 'Action required' : 'Notice'}</AlertTitle>
          <AlertDescription>{alert.message}</AlertDescription>
        </Alert>
      ))}

      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Student Overview</h2>
        <p className="text-sm text-slate-500 mt-1">
          Welcome back, {student.full_name ?? 'Student'} · {student.student_number}
        </p>
      </div>

      <ModuleHub items={studentModuleLinks} />

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <StatCard
          title="Cumulative GPA"
          value={String(academics.latest_cgpa ?? '—')}
          description="Latest published CGPA"
          icon={<Award className="h-5 w-5" />}
        />
        <StatCard
          title="Fee balance"
          value={formatCurrency(Number(finance.balance ?? 0))}
          description={`${Number(finance.payment_percentage ?? 0).toFixed(0)}% paid`}
          icon={<CreditCard className="h-5 w-5" />}
        />
        <StatCard
          title="Registration"
          value={registration.registered ? 'Registered' : 'Pending'}
          description={String((registration.term as { name?: string })?.name ?? 'Current term')}
          icon={<BookOpen className="h-5 w-5" />}
        />
        <StatCard
          title="Attendance"
          value={avgAttendance !== null ? `${avgAttendance}%` : '—'}
          description={atRiskCount > 0 ? `${atRiskCount} course(s) below threshold` : 'Across enrolled courses'}
          icon={
            atRiskCount > 0 ? (
              <AlertTriangle className="h-5 w-5 text-amber-600" />
            ) : (
              <ScanLine className="h-5 w-5" />
            )
          }
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Quick Actions</CardTitle>
            <CardDescription>Common tasks from your module hub</CardDescription>
          </CardHeader>
          <CardContent className="flex flex-wrap gap-3">
            <Link href="/registration">
              <Button className="gap-2">
                Register units <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
            <Link href="/attendance">
              <Button variant="outline" className="gap-2">
                <ScanLine className="h-4 w-4" /> Class check-in
              </Button>
            </Link>
            <Link href="/timetable">
              <Button variant="outline">Timetable</Button>
            </Link>
            <Link href="/finance">
              <Button variant="outline">View fees</Button>
            </Link>
            <Link href="/results">
              <Button variant="outline">View results</Button>
            </Link>
            {!finance.registration_cleared ? (
              <Badge variant="warning">Fee clearance required for registration</Badge>
            ) : null}
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle>Course Attendance</CardTitle>
              <CardDescription>75% threshold per enrolled unit</CardDescription>
            </div>
            <Link href="/attendance">
              <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                Check in <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-3">
            {courses.slice(0, 4).map((course) => (
              <div key={String(course.offering_id)} className="flex items-center justify-between text-sm">
                <span className="font-medium">{String(course.course_code ?? '—')}</span>
                <Badge variant={course.at_risk ? 'destructive' : 'success'}>
                  {String(course.attendance_percentage ?? 0)}%
                </Badge>
              </div>
            ))}
            {courses.length === 0 && (
              <p className="text-sm text-slate-500">
                {attendance.isLoading ? 'Loading attendance…' : 'No enrolled courses yet.'}
              </p>
            )}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Clock className="h-5 w-5 text-mema-teal-700" />
            This Term
          </CardTitle>
          <CardDescription>
            {String(registration.course_count ?? 0)} courses enrolled · Status {student.status}
          </CardDescription>
        </CardHeader>
        <CardContent className="text-sm text-slate-600">
          {student.programme} · Use the module hub above for registration, timetable, fees, results, and clearance.
        </CardContent>
      </Card>
    </div>
  );
}
