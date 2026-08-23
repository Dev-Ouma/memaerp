'use client';

import React, { FormEvent, useState } from 'react';
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
  Input,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  MemaLoaderInline,
} from '@mema/ui';
import { CheckCircle2, ScanLine } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Check-in failed.';

export default function StudentAttendancePage() {
  const [token, setToken] = useState('');
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const record = useQuery({
    queryKey: ['attendance', 'my-record'],
    queryFn: () => api.getMyAttendanceRecord(),
  });

  async function handleCheckIn(event: FormEvent) {
    event.preventDefault();
    if (!token.trim()) return;
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      const result = await api.checkInAttendance(token.trim());
      setNotice(`Checked in successfully (${String(result.status ?? 'PRESENT')}).`);
      setToken('');
      await record.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  const courses = (record.data?.courses as Array<Record<string, unknown>> | undefined) ?? [];

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Class Check-In</h2>
        <p className="text-sm text-slate-500 mt-1">
          Scan the lecturer&apos;s QR code or paste the session token below.
        </p>
      </div>

      {notice && (
        <Alert>
          <CheckCircle2 className="h-4 w-4" />
          <AlertTitle>Checked in</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Check-in failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <ScanLine className="h-5 w-5 text-mema-teal-700" />
            QR Check-In
          </CardTitle>
          <CardDescription>Token expires five minutes after the lecturer opens the session.</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleCheckIn} className="flex flex-col sm:flex-row gap-3">
            <Input
              value={token}
              onChange={(event) => setToken(event.target.value)}
              placeholder="Paste attendance token"
              className="font-mono text-sm"
            />
            <Button type="submit" disabled={busy || !token.trim()} className="gap-2 shrink-0">
              {busy ? <MemaLoaderInline size={40} /> : <CheckCircle2 className="h-4 w-4" />}
              Check In
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>My Attendance</CardTitle>
          <CardDescription>Course attendance percentages against the 75% threshold.</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Course</TableHead>
                <TableHead>Attendance</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {courses.map((course) => (
                <TableRow key={String(course.offering_id)}>
                  <TableCell>
                    <div className="font-medium">{String(course.course_code ?? '—')}</div>
                    <div className="text-xs text-slate-500">{String(course.course_title ?? '')}</div>
                  </TableCell>
                  <TableCell>{String(course.attendance_percentage ?? 0)}%</TableCell>
                  <TableCell>
                    {course.at_risk ? (
                      <Badge variant="destructive">Below threshold</Badge>
                    ) : (
                      <Badge variant="success">On track</Badge>
                    )}
                  </TableCell>
                </TableRow>
              ))}
              {courses.length === 0 && (
                <TableRow>
                  <TableCell colSpan={3} className="text-center text-slate-500">
                    {record.isLoading ? 'Loading…' : 'No enrolled courses yet.'}
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
