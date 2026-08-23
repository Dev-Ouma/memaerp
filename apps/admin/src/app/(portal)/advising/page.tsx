'use client';

import React, { FormEvent, useEffect, useState } from 'react';
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
import { UserPlus } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Assignment failed.';

export default function AdminAdvisingPage() {
  const [studentId, setStudentId] = useState('');
  const [advisorId, setAdvisorId] = useState('');
  const [reason, setReason] = useState('Department advising allocation');
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const assignments = useQuery({
    queryKey: ['advising', 'assignments'],
    queryFn: () => api.getAdvisingAssignments(),
  });
  const students = useQuery({ queryKey: ['students'], queryFn: () => api.getStudents() });
  const staff = useQuery({ queryKey: ['courses', 'lecturers'], queryFn: () => api.getTeachingStaff() });

  useEffect(() => {
    if (!studentId && students.data?.[0]) setStudentId(students.data[0].id);
  }, [students.data, studentId]);

  useEffect(() => {
    if (!advisorId && staff.data?.[0]) setAdvisorId(staff.data[0].id);
  }, [staff.data, advisorId]);

  async function assign(event: FormEvent) {
    event.preventDefault();
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      await api.assignAdvisor({
        student_id: studentId,
        advisor_user_id: advisorId,
        assignment_reason: reason,
      });
      setNotice('Advisor assigned successfully.');
      await assignments.refetch();
    } catch (reasonErr) {
      setError(messageFrom(reasonErr));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Academic Advising</h2>
        <p className="text-sm text-slate-500 mt-1">
          Assign faculty advisors and monitor advisee coverage (MOD-02-03)
        </p>
      </div>

      {notice && (
        <Alert>
          <AlertTitle>Updated</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Assignment failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <UserPlus className="h-5 w-5 text-mema-teal-700" />
            Assign advisor
          </CardTitle>
          <CardDescription>One active advisor per student · reassignment deactivates the previous link</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={assign} className="grid gap-4 md:grid-cols-2 max-w-3xl">
            <label className="space-y-1 text-sm font-medium text-slate-700">
              Student
              <select
                className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
                value={studentId}
                onChange={(e) => setStudentId(e.target.value)}
              >
                {(students.data ?? []).map((student) => (
                  <option key={student.id} value={student.id}>
                    {student.student_number} · {student.person?.first_name} {student.person?.last_name}
                  </option>
                ))}
              </select>
            </label>
            <label className="space-y-1 text-sm font-medium text-slate-700">
              Advisor (faculty)
              <select
                className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
                value={advisorId}
                onChange={(e) => setAdvisorId(e.target.value)}
              >
                {(staff.data ?? []).map((member) => (
                  <option key={member.id} value={member.id}>
                    {member.person?.given_name} {member.person?.family_name} · {member.email}
                  </option>
                ))}
              </select>
            </label>
            <label className="space-y-1 text-sm font-medium text-slate-700 md:col-span-2">
              Reason
              <input
                className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
                value={reason}
                onChange={(e) => setReason(e.target.value)}
              />
            </label>
            <Button type="submit" disabled={busy || !studentId || !advisorId} className="gap-2">
              {busy ? <MemaLoaderInline size={40} /> : <UserPlus className="h-4 w-4" />}
              Assign advisor
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Active assignments</CardTitle>
          <CardDescription>{assignments.data?.length ?? 0} student–advisor link(s)</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student</TableHead>
                <TableHead>Advisor</TableHead>
                <TableHead>Assigned</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(assignments.data ?? []).map((row) => {
                const student = row.student as {
                  student_number?: string;
                  person?: { given_name?: string; family_name?: string };
                  programme?: { name?: string };
                };
                const advisor = row.advisor as { email?: string; person?: { given_name?: string; family_name?: string } };
                const studentName = `${student?.person?.given_name ?? ''} ${student?.person?.family_name ?? ''}`.trim();
                const advisorName = `${advisor?.person?.given_name ?? ''} ${advisor?.person?.family_name ?? ''}`.trim();
                return (
                  <TableRow key={String(row.id)}>
                    <TableCell>
                      <div className="font-medium">{studentName || '—'}</div>
                      <div className="text-xs text-slate-500">
                        {student?.student_number} · {student?.programme?.name}
                      </div>
                    </TableCell>
                    <TableCell>
                      <div className="font-medium">{advisorName || '—'}</div>
                      <div className="text-xs text-slate-500">{advisor?.email}</div>
                    </TableCell>
                    <TableCell>
                      {row.assigned_at ? new Date(String(row.assigned_at)).toLocaleDateString() : '—'}
                    </TableCell>
                    <TableCell>
                      <Badge variant="success">ACTIVE</Badge>
                    </TableCell>
                  </TableRow>
                );
              })}
              {(assignments.data ?? []).length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-slate-500 py-8">
                    {assignments.isLoading ? 'Loading…' : 'No active advisor assignments.'}
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
