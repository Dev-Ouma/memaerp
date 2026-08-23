'use client';

import React, { useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError } from '@mema/api-client';
import {
  Alert,
  AlertDescription,
  AlertTitle,
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
  MemaLoaderInline,
} from '@mema/ui';
import {
  Activity,
  CloudUpload,
  Download,
  GraduationCap,
  RefreshCw,
  Server,
  Users,
} from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'LMS sync operation failed.';

function statusVariant(status?: string) {
  if (status === 'SYNCED') return 'success' as const;
  if (status === 'FAILED') return 'destructive' as const;
  if (status === 'SYNCING' || status === 'PENDING') return 'warning' as const;
  return 'outline' as const;
}

export default function AdminLmsPage() {
  const [busyOfferingId, setBusyOfferingId] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const status = useQuery({
    queryKey: ['lms', 'status'],
    queryFn: () => api.getLmsSyncStatus(),
    refetchInterval: 60_000,
  });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const enrollments = useQuery({
    queryKey: ['enrollment', 'course-enrollments'],
    queryFn: () => api.getCourseEnrollments(),
  });

  const recent = (status.data?.recent as Array<Record<string, unknown>> | undefined) ?? [];
  const offered = useMemo(
    () => offerings.data?.filter((item) => item.status === 'OFFERED' || (item.status as string) === 'ACTIVE') ?? [],
    [offerings.data],
  );

  async function refreshAll() {
    await Promise.all([status.refetch(), offerings.refetch()]);
  }

  async function syncCourse(offeringId: string) {
    setBusyOfferingId(offeringId);
    setError(null);
    setNotice(null);
    try {
      await api.syncLmsCourse(offeringId);
      setNotice('Course offering synced to Moodle.');
      await status.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusyOfferingId(null);
    }
  }

  async function pullGrades(offeringId: string) {
    setBusyOfferingId(offeringId);
    setError(null);
    setNotice(null);
    try {
      const result = await api.pullLmsGrades(offeringId);
      setNotice(`Imported ${String(result.grades_imported ?? 0)} grade rows from Moodle.`);
      await status.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusyOfferingId(null);
    }
  }

  async function syncEnrollments(offeringId: string) {
    setBusyOfferingId(offeringId);
    setError(null);
    setNotice(null);
    try {
      const rows = (enrollments.data ?? []).filter(
        (row) => row.course_offering_id === offeringId && row.status === 'ENROLLED',
      );
      let synced = 0;
      for (const row of rows) {
        await api.syncLmsEnrollment(row.id);
        synced += 1;
      }
      setNotice(`Synced ${synced} enrollment(s) to Moodle.`);
      await status.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusyOfferingId(null);
    }
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">LMS Sync Dashboard</h2>
          <p className="text-sm text-slate-500 mt-1">
            Moodle course provisioning, enrollment sync, and grade pull-back (MOD-02-01).
          </p>
        </div>
        <Button variant="outline" onClick={refreshAll} className="gap-2 self-start sm:self-auto">
          <RefreshCw className="h-4 w-4" /> Refresh
        </Button>
      </div>

      {notice && (
        <Alert>
          <AlertTitle>Sync updated</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Sync failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Integration</CardDescription>
            <CardTitle className="text-lg flex items-center gap-2">
              <Server className="h-5 w-5 text-mema-teal-700" />
              {status.data?.enabled ? 'Enabled' : 'Stub mode'}
            </CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-slate-600">
            Moodle client runs in stub mode when <code>MOODLE_BASE_URL</code> is unset.
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Queue depth</CardDescription>
            <CardTitle className="text-3xl">{String(status.data?.queue_depth ?? 0)}</CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-slate-600">Pending sync jobs</CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Course mappings</CardDescription>
            <CardTitle className="text-3xl flex items-center gap-2">
              <GraduationCap className="h-6 w-6 text-mema-teal-700" />
              {String(status.data?.course_mappings ?? 0)}
            </CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-slate-600">ERP offerings linked to Moodle</CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Enrollment mappings</CardDescription>
            <CardTitle className="text-3xl flex items-center gap-2">
              <Users className="h-6 w-6 text-mema-teal-700" />
              {String(status.data?.enrollment_mappings ?? 0)}
            </CardTitle>
          </CardHeader>
          <CardContent className="text-sm text-slate-600">
            Failed: {String(status.data?.failed_count ?? 0)}
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <CloudUpload className="h-5 w-5 text-mema-teal-700" />
            Offerings
          </CardTitle>
          <CardDescription>Push offerings to Moodle or pull grades back into ERP.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Course</TableHead>
                <TableHead>Section</TableHead>
                <TableHead>Enrolled</TableHead>
                <TableHead className="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {offered.map((offering) => (
                <TableRow key={offering.id}>
                  <TableCell>
                    <div className="font-medium">{offering.course?.code ?? '—'}</div>
                    <div className="text-xs text-slate-500">{offering.course?.title}</div>
                  </TableCell>
                  <TableCell>{offering.section_code}</TableCell>
                  <TableCell>
                    {offering.enrolled_count}/{offering.max_capacity}
                  </TableCell>
                  <TableCell className="text-right space-x-2">
                    <Button
                      size="sm"
                      variant="outline"
                      disabled={busyOfferingId === offering.id}
                      onClick={() => syncCourse(offering.id)}
                      className="gap-1"
                    >
                      {busyOfferingId === offering.id ? (
                        <MemaLoaderInline size={36} />
                      ) : (
                        <CloudUpload className="h-3 w-3" />
                      )}
                      Sync
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      disabled={busyOfferingId === offering.id}
                      onClick={() => syncEnrollments(offering.id)}
                      className="gap-1"
                    >
                      <Users className="h-3 w-3" /> Enroll
                    </Button>
                    <Button
                      size="sm"
                      variant="secondary"
                      disabled={busyOfferingId === offering.id}
                      onClick={() => pullGrades(offering.id)}
                      className="gap-1"
                    >
                      <Download className="h-3 w-3" /> Grades
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
              {offered.length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-slate-500">
                    No active offerings.
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
            <Activity className="h-5 w-5 text-mema-teal-700" />
            Recent Sync Log
          </CardTitle>
          <CardDescription>Last twenty Moodle sync operations for this institution.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Type</TableHead>
                <TableHead>Direction</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>When</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {recent.map((entry) => (
                <TableRow key={String(entry.id)}>
                  <TableCell>{String(entry.sync_type ?? '—')}</TableCell>
                  <TableCell>{String(entry.direction ?? '—')}</TableCell>
                  <TableCell>
                    <Badge variant={statusVariant(String(entry.status))}>{String(entry.status ?? '—')}</Badge>
                  </TableCell>
                  <TableCell>
                    {entry.created_at ? new Date(String(entry.created_at)).toLocaleString() : '—'}
                  </TableCell>
                </TableRow>
              ))}
              {recent.length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-slate-500">
                    No sync activity yet.
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  );
}
