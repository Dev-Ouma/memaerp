'use client';

import React from 'react';
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
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@mema/ui';
import {
  Users,
  CircleDollarSign,
  ShieldCheck,
  ArrowRight,
  UserCheck,
  Activity,
  Server,
  Cloud,
  ClipboardList,
  AlertTriangle,
} from 'lucide-react';
import { api } from '@mema/api-client';
import { adminModuleLinks } from '@/config/portal-nav';
import { ModuleHub } from '@/components/module-hub';

export default function AdminDashboardPage() {
  const students = useQuery({ queryKey: ['students', 'dashboard'], queryFn: () => api.getStudentsDashboard() });
  const admissions = useQuery({ queryKey: ['admissions', 'dashboard'], queryFn: () => api.getAdmissionsDashboard() });
  const courses = useQuery({ queryKey: ['courses', 'dashboard'], queryFn: () => api.getCourseDashboard() });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const programmes = useQuery({ queryKey: ['programmes'], queryFn: () => api.getProgrammes() });
  const lms = useQuery({ queryKey: ['lms', 'status'], queryFn: () => api.getLmsSyncStatus() });
  const atRisk = useQuery({ queryKey: ['attendance', 'at-risk'], queryFn: () => api.getAttendanceAtRisk() });

  const topOfferings = (offerings.data ?? []).slice(0, 5);
  const topProgrammes = (programmes.data ?? []).slice(0, 4);
  const atRiskCount = atRisk.data?.length ?? 0;

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Institutional Master Console
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Mema University ERP · Live module telemetry from the API monolith
          </p>
        </div>
        <Badge variant="success" className="px-3 py-1 text-xs self-start sm:self-auto" dot>
          Core Monolith Online
        </Badge>
      </div>

      <ModuleHub
        title="ERP Module Hub"
        description="Every built module — mirrored from the sidebar for quick navigation."
        items={adminModuleLinks}
      />

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <StatCard
          title="Active Students"
          value={String(students.data?.active ?? '—')}
          description={`${students.data?.total ?? 0} total · ${students.data?.matriculation_queue ?? 0} in matriculation queue`}
          icon={<Users className="h-5 w-5" />}
        />
        <StatCard
          title="Applications"
          value={String(admissions.data?.total ?? '—')}
          description={`${admissions.data?.under_review ?? 0} under review · ${admissions.data?.admitted ?? 0} admitted`}
          icon={<UserCheck className="h-5 w-5" />}
        />
        <StatCard
          title="Open Sections"
          value={String(courses.data?.open_sections ?? '—')}
          description={`${courses.data?.capacity_saturation_percent ?? 0}% avg capacity · ${courses.data?.saturated_sections ?? 0} saturated`}
          icon={<CircleDollarSign className="h-5 w-5" />}
        />
        <StatCard
          title="At-Risk Attendance"
          value={String(atRiskCount)}
          description="Students below 75% on any offering"
          icon={<AlertTriangle className="h-5 w-5 text-amber-600" />}
        />
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <div>
              <CardTitle className="text-base flex items-center gap-2">
                <Cloud className="h-4 w-4 text-mema-teal-800" />
                LMS Sync Health
              </CardTitle>
              <CardDescription>Moodle integration status (MOD-02-01)</CardDescription>
            </div>
            <Link href="/lms">
              <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                Open <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-slate-600">Integration</span>
              <Badge variant={lms.data?.enabled ? 'success' : 'outline'}>
                {lms.data?.enabled ? 'Live' : 'Stub mode'}
              </Badge>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Course mappings</span>
              <span className="font-mono font-semibold">{String(lms.data?.course_mappings ?? 0)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Failed syncs</span>
              <span className="font-mono font-semibold text-rose-600">{String(lms.data?.failed_count ?? 0)}</span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <div>
              <CardTitle className="text-base flex items-center gap-2">
                <ClipboardList className="h-4 w-4 text-mema-teal-800" />
                Attendance Oversight
              </CardTitle>
              <CardDescription>QR registers & at-risk flags (MOD-02-02)</CardDescription>
            </div>
            <Link href="/attendance">
              <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                Reports <ArrowRight className="h-4 w-4" />
              </Button>
            </Link>
          </CardHeader>
          <CardContent className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span className="text-slate-600">Open at-risk alerts</span>
              <Badge variant={atRiskCount > 0 ? 'destructive' : 'success'}>{atRiskCount}</Badge>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Threshold</span>
              <span className="font-mono font-semibold">75%</span>
            </div>
            <div className="flex justify-between">
              <span className="text-slate-600">Lecturer QR sessions</span>
              <span className="text-slate-500">Via lecturer portal</span>
            </div>
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Course Sections & Capacity</CardTitle>
                <CardDescription>Live offerings from the course module</CardDescription>
              </div>
              <Link href="/courses">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  Manage Sections <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Course</TableHead>
                    <TableHead>Section</TableHead>
                    <TableHead className="text-center">Enrolled</TableHead>
                    <TableHead className="text-center">Capacity</TableHead>
                    <TableHead className="text-right">Occupancy</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {topOfferings.map((offering) => {
                    const capacity = offering.max_capacity || 1;
                    const percentage = Math.round((offering.enrolled_count / capacity) * 100);
                    return (
                      <TableRow key={offering.id}>
                        <TableCell>
                          <div className="font-bold text-slate-900">{offering.course?.code}</div>
                          <div className="text-xs text-slate-500">{offering.course?.title}</div>
                        </TableCell>
                        <TableCell className="font-medium text-xs">{offering.section_code}</TableCell>
                        <TableCell className="text-center font-mono">{offering.enrolled_count}</TableCell>
                        <TableCell className="text-center font-mono text-slate-500">{capacity}</TableCell>
                        <TableCell className="text-right">
                          <span className="font-mono text-xs font-bold">{percentage}%</span>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                  {topOfferings.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={5} className="text-center text-slate-500">
                        {offerings.isLoading ? 'Loading offerings…' : 'No offerings yet.'}
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between pb-4">
              <div>
                <CardTitle>Accredited Programmes</CardTitle>
                <CardDescription>From curriculum module</CardDescription>
              </div>
              <Link href="/programmes">
                <Button variant="ghost" size="sm" className="gap-1 text-mema-teal-800">
                  All Programmes <ArrowRight className="h-4 w-4" />
                </Button>
              </Link>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {topProgrammes.map((prog) => (
                  <div
                    key={prog.id}
                    className="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-100/60 transition-colors space-y-2"
                  >
                    <div className="flex items-center justify-between">
                      <Badge variant="default" className="font-mono font-bold">
                        {prog.code}
                      </Badge>
                      <Badge variant="outline" className="text-[11px]">
                        {prog.duration_years} Years
                      </Badge>
                    </div>
                    <h4 className="font-bold text-slate-900 text-sm">{prog.title}</h4>
                    <p className="text-xs text-slate-500">{prog.department?.name}</p>
                  </div>
                ))}
                {topProgrammes.length === 0 && (
                  <p className="text-sm text-slate-500 col-span-2">
                    {programmes.isLoading ? 'Loading programmes…' : 'No programmes seeded.'}
                  </p>
                )}
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardHeader className="pb-3">
              <CardTitle className="text-base flex items-center gap-2">
                <Activity className="h-4 w-4 text-mema-teal-800" />
                Recent LMS Sync
              </CardTitle>
              <CardDescription>Last operations from Moodle bridge</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              {((lms.data?.recent as Array<Record<string, unknown>> | undefined) ?? [])
                .slice(0, 5)
                .map((entry) => (
                  <div key={String(entry.id)} className="text-xs space-y-1 border-b border-slate-100 pb-2 last:border-0">
                    <div className="flex justify-between">
                      <span className="font-semibold">{String(entry.sync_type ?? 'sync')}</span>
                      <Badge variant="outline">{String(entry.status ?? '—')}</Badge>
                    </div>
                    <p className="text-slate-500">{String(entry.direction ?? '')}</p>
                  </div>
                ))}
              {((lms.data?.recent as unknown[] | undefined) ?? []).length === 0 && (
                <p className="text-xs text-slate-500">No sync activity yet.</p>
              )}
              <Link href="/lms" className="block pt-2">
                <Button variant="outline" size="sm" className="w-full text-xs">
                  LMS Sync Dashboard
                </Button>
              </Link>
            </CardContent>
          </Card>

          <Card className="bg-gradient-to-br from-mema-teal-900 to-slate-900 text-white p-6 space-y-4 shadow-lg">
            <div className="flex items-center justify-between">
              <span className="text-xs font-bold uppercase tracking-wider text-mema-green-400 flex items-center gap-1.5">
                <Server className="h-4 w-4" /> Platform Health
              </span>
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
            </div>
            <div className="space-y-2 text-xs text-mema-teal-100">
              <div className="flex justify-between">
                <span>RBAC permissions</span>
                <span className="text-emerald-300 font-mono flex items-center gap-1">
                  <ShieldCheck className="h-3 w-3" /> Seeded
                </span>
              </div>
              <div className="flex justify-between">
                <span>Active courses</span>
                <span className="text-emerald-300 font-mono">{String(courses.data?.active_courses ?? '—')}</span>
              </div>
              <div className="flex justify-between">
                <span>Finance module</span>
                <Link href="/finance" className="text-emerald-300 hover:underline">
                  Open ledger →
                </Link>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </div>
  );
}
