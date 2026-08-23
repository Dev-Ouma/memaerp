'use client';

import React, { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api } from '@mema/api-client';
import {
  Badge,
  Button,
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@mema/ui';
import { AlertTriangle, ClipboardList, RefreshCw } from 'lucide-react';

export default function AdminAttendancePage() {
  const [selectedOfferingId, setSelectedOfferingId] = useState('');

  const atRisk = useQuery({ queryKey: ['attendance', 'at-risk'], queryFn: () => api.getAttendanceAtRisk() });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const report = useQuery({
    queryKey: ['attendance', 'report', selectedOfferingId],
    queryFn: () => api.getCourseAttendanceReport(selectedOfferingId),
    enabled: Boolean(selectedOfferingId),
  });

  const offered = useMemo(
    () => offerings.data?.filter((item) => item.status === 'OFFERED' || (item.status as string) === 'ACTIVE') ?? [],
    [offerings.data],
  );

  React.useEffect(() => {
    if (!selectedOfferingId && offered[0]) setSelectedOfferingId(offered[0].id);
  }, [offered, selectedOfferingId]);

  const students = (report.data?.students as Array<Record<string, unknown>> | undefined) ?? [];
  const sessions = (report.data?.sessions as Array<Record<string, unknown>> | undefined) ?? [];

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">Attendance Reports</h2>
          <p className="text-sm text-slate-500 mt-1">
            Departmental registers and students below the attendance threshold (MOD-02-02).
          </p>
        </div>
        <Button
          variant="outline"
          className="gap-2 self-start sm:self-auto"
          onClick={() => {
            atRisk.refetch();
            if (selectedOfferingId) report.refetch();
          }}
        >
          <RefreshCw className="h-4 w-4" /> Refresh
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-amber-600" />
            At-Risk Students
          </CardTitle>
          <CardDescription>Students below 75% attendance on any enrolled offering.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student</TableHead>
                <TableHead>Course</TableHead>
                <TableHead>Attendance</TableHead>
                <TableHead>Flagged</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(atRisk.data ?? []).map((alert) => {
                const student = alert.student as { student_number?: string; person?: { first_name?: string; last_name?: string } };
                const offering = alert.course_offering as { course?: { code?: string }; section_code?: string };
                const name = `${student?.person?.first_name ?? ''} ${student?.person?.last_name ?? ''}`.trim();
                return (
                  <TableRow key={String(alert.id)}>
                    <TableCell>
                      <div className="font-medium">{name || '—'}</div>
                      <div className="text-xs text-slate-500">{student?.student_number}</div>
                    </TableCell>
                    <TableCell>
                      {offering?.course?.code ?? '—'} · {offering?.section_code ?? '—'}
                    </TableCell>
                    <TableCell>
                      <Badge variant="destructive">{String(alert.attendance_percentage ?? 0)}%</Badge>
                    </TableCell>
                    <TableCell>
                      {alert.flagged_at ? new Date(String(alert.flagged_at)).toLocaleDateString() : '—'}
                    </TableCell>
                  </TableRow>
                );
              })}
              {(atRisk.data ?? []).length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-slate-500">
                    No at-risk alerts.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <ClipboardList className="h-5 w-5 text-mema-teal-700" />
            Course Register
          </CardTitle>
          <CardDescription>Session history and per-student attendance for an offering.</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <label className="block space-y-1 text-sm font-medium text-slate-700 max-w-md">
            Offering
            <select
              className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
              value={selectedOfferingId}
              onChange={(event) => setSelectedOfferingId(event.target.value)}
            >
              {offered.map((offering) => (
                <option key={offering.id} value={offering.id}>
                  {offering.course?.code ?? 'Course'} · {offering.section_code}
                </option>
              ))}
            </select>
          </label>

          <div className="grid gap-6 lg:grid-cols-2">
            <div>
              <h3 className="text-sm font-semibold text-slate-800 mb-2">Students</h3>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Attended</TableHead>
                    <TableHead>%</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {students.map((row) => (
                    <TableRow key={String(row.student_id)}>
                      <TableCell>{String(row.name ?? row.student_number ?? '—')}</TableCell>
                      <TableCell>{String(row.sessions_attended ?? 0)}</TableCell>
                      <TableCell>
                        {row.at_risk ? (
                          <Badge variant="destructive">{String(row.attendance_percentage ?? 0)}%</Badge>
                        ) : (
                          <Badge variant="success">{String(row.attendance_percentage ?? 0)}%</Badge>
                        )}
                      </TableCell>
                    </TableRow>
                  ))}
                  {students.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center text-slate-500">
                        {report.isLoading ? 'Loading…' : 'No enrollments.'}
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </div>
            <div>
              <h3 className="text-sm font-semibold text-slate-800 mb-2">Sessions</h3>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Date</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Opened</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {sessions.map((session) => (
                    <TableRow key={String(session.id)}>
                      <TableCell>{String(session.session_date ?? '—')}</TableCell>
                      <TableCell>{String(session.status ?? '—')}</TableCell>
                      <TableCell>
                        {session.opened_at ? new Date(String(session.opened_at)).toLocaleString() : '—'}
                      </TableCell>
                    </TableRow>
                  ))}
                  {sessions.length === 0 && (
                    <TableRow>
                      <TableCell colSpan={3} className="text-center text-slate-500">
                        No sessions recorded.
                      </TableCell>
                    </TableRow>
                  )}
                </TableBody>
              </Table>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
