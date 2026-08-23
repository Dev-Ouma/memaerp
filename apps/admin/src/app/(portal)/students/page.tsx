'use client';

import React, { FormEvent, useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
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
  personDisplayName,
  programmeLabel,
} from '@mema/ui';
import { DataTable } from '@mema/tables';
import { api, ApiError } from '@mema/api-client';
import { Download, RefreshCw, Search, UserCheck } from 'lucide-react';
import type { MatriculationQueueItem, Student } from '@mema/types';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'The student records operation failed.';

function statusVariant(status: string) {
  if (status === 'ACTIVE') return 'success' as const;
  if (status === 'ON_LEAVE') return 'warning' as const;
  if (status === 'SUSPENDED' || status === 'WITHDRAWN') return 'destructive' as const;
  return 'outline' as const;
}

export default function AdminStudentsPage() {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedQueueId, setSelectedQueueId] = useState('');
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const dashboard = useQuery({ queryKey: ['students', 'dashboard'], queryFn: () => api.getStudentsDashboard() });
  const queue = useQuery({ queryKey: ['students', 'matriculation-queue'], queryFn: () => api.getMatriculationQueue() });
  const students = useQuery({
    queryKey: ['students', 'registry', searchTerm],
    queryFn: () => api.getStudents({ search: searchTerm || undefined }),
  });

  const canMatriculate = user.data?.permissions.includes('student.record.matriculate') ?? false;
  const selectedQueue =
    queue.data?.find((item) => item.id === selectedQueueId) ?? queue.data?.[0] ?? null;

  const stats = useMemo(
    () => [
      { label: 'Active students', value: dashboard.data?.active ?? 0 },
      { label: 'Matriculation queue', value: dashboard.data?.matriculation_queue ?? 0 },
      { label: 'On leave', value: dashboard.data?.on_leave ?? 0 },
      { label: 'Matriculated this month', value: dashboard.data?.matriculated_this_month ?? 0 },
    ],
    [dashboard.data],
  );

  async function refresh() {
    await Promise.all([dashboard.refetch(), queue.refetch(), students.refetch()]);
  }

  async function perform(action: () => Promise<unknown>, success: string) {
    setSaving(true);
    setError(null);
    setNotice(null);
    try {
      await action();
      setNotice(success);
      await refresh();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setSaving(false);
    }
  }

  async function matriculateSelected(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selectedQueue) return;
    const data = new FormData(event.currentTarget);
    await perform(
      () =>
        api.matriculateStudents({
          application_ids: [selectedQueue.id],
          pledge_signed: data.get('pledge_signed') === 'on',
          notes: String(data.get('notes') || '') || undefined,
        }),
      `Matriculated ${selectedQueue.person?.full_name ?? 'applicant'}.`,
    );
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Student Information System (SIS)
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Matriculation, master records, digital IDs, and lifecycle statuses (MOD-01-06)
          </p>
        </div>
        <div className="flex flex-wrap gap-2 self-start sm:self-auto">
          <Button variant="outline" className="gap-2" onClick={() => refresh()} disabled={saving}>
            <RefreshCw className="h-4 w-4" /> Refresh
          </Button>
          <Button variant="outline" className="gap-2" asChild>
            <a href={api.getStudentMasterReportUrl('csv')} target="_blank" rel="noreferrer">
              <Download className="h-4 w-4" /> Export Master CSV
            </a>
          </Button>
        </div>
      </div>

      {notice ? (
        <Alert>
          <AlertTitle>Success</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      ) : null}
      {error ? (
        <Alert variant="destructive">
          <AlertTitle>Action failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      ) : null}

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <Card key={stat.label}>
            <CardHeader className="pb-2">
              <CardDescription>{stat.label}</CardDescription>
              <CardTitle className="text-3xl">{stat.value}</CardTitle>
            </CardHeader>
          </Card>
        ))}
      </div>

      {canMatriculate ? (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <UserCheck className="h-5 w-5 text-mema-teal-800" /> Matriculation Queue
            </CardTitle>
            <CardDescription>
              Accepted applications with verified documents, ready for official student number allocation.
            </CardDescription>
          </CardHeader>
          <CardContent className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <DataTable<MatriculationQueueItem>
              isLoading={queue.isLoading}
              isError={queue.isError}
              errorMessage={queue.error instanceof Error ? queue.error.message : undefined}
              data={queue.data ?? []}
              getRowKey={(item) => item.id}
              emptyMessage="No applicants are waiting for matriculation."
              columns={[
                {
                  key: 'application',
                  header: 'Application',
                  cell: (item) => (
                    <span className="font-mono text-xs font-semibold">{item.application_number}</span>
                  ),
                },
                {
                  key: 'name',
                  header: 'Applicant',
                  cell: (item) => item.person?.full_name ?? '—',
                },
                {
                  key: 'programme',
                  header: 'Programme',
                  cell: (item) => item.programme?.code ?? '—',
                },
                {
                  key: 'intake',
                  header: 'Intake',
                  cell: (item) => item.intake?.code ?? '—',
                },
              ]}
            />

            {selectedQueue ? (
              <form onSubmit={matriculateSelected} className="space-y-4 rounded-xl border border-slate-200 p-4">
                <label className="block space-y-1 text-sm">
                  <span className="font-medium text-slate-700">Select applicant</span>
                  <select
                    className="h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm"
                    value={selectedQueue.id}
                    onChange={(event) => setSelectedQueueId(event.target.value)}
                  >
                    {(queue.data ?? []).map((item) => (
                      <option key={item.id} value={item.id}>
                        {item.application_number} — {item.person?.full_name}
                      </option>
                    ))}
                  </select>
                </label>
                <p className="text-xs text-slate-500">{selectedQueue.programme?.name}</p>
                <label className="flex items-center gap-2 text-sm text-slate-700">
                  <input type="checkbox" name="pledge_signed" className="rounded border-slate-300" />
                  Student pledge signed
                </label>
                <label className="block space-y-1 text-sm">
                  <span className="font-medium text-slate-700">Matriculation notes</span>
                  <textarea
                    name="notes"
                    rows={3}
                    className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                    placeholder="Original certificates verified at admissions desk."
                  />
                </label>
                <Button type="submit" disabled={saving} className="w-full gap-2">
                  <UserCheck className="h-4 w-4" /> Matriculate &amp; Issue Number
                </Button>
              </form>
            ) : null}
          </CardContent>
        </Card>
      ) : null}

      <div className="flex flex-col sm:flex-row items-center gap-4">
        <div className="flex-1 w-full">
          <Input
            placeholder="Search by student number or name..."
            value={searchTerm}
            onChange={(event) => setSearchTerm(event.target.value)}
            leftIcon={<Search className="h-4 w-4" />}
          />
        </div>
        <Button variant="outline" className="gap-2 shrink-0" asChild>
          <a href={api.getMatriculationReportUrl('pdf')} target="_blank" rel="noreferrer">
            <Download className="h-4 w-4" /> Matriculation Roll PDF
          </a>
        </Button>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Matriculated Student Registry</CardTitle>
          <CardDescription>
            Loaded from <code className="text-xs">GET /api/v1/students</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <DataTable<Student>
            isLoading={students.isLoading}
            isError={students.isError}
            errorMessage={students.error instanceof Error ? students.error.message : undefined}
            data={students.data ?? []}
            getRowKey={(student) => student.id}
            emptyMessage="No students found for the current search."
            columns={[
              {
                key: 'student_number',
                header: 'Student ID',
                cell: (student) => (
                  <span className="font-mono font-bold text-mema-teal-900">{student.student_number}</span>
                ),
              },
              {
                key: 'name',
                header: 'Full Name',
                cell: (student) => (
                  <span className="font-semibold text-slate-900">{personDisplayName(student.person)}</span>
                ),
              },
              {
                key: 'programme',
                header: 'Programme',
                cell: (student) => (
                  <span className="text-xs font-medium text-slate-800">
                    {student.programme ? programmeLabel(student.programme) : '—'}
                  </span>
                ),
              },
              {
                key: 'year',
                header: 'Year / Sem',
                className: 'text-center',
                cell: (student) => (
                  <span className="font-mono text-xs">
                    Yr {student.current_year_level} · Sem {student.current_term_sequence}
                  </span>
                ),
              },
              {
                key: 'status',
                header: 'Status',
                className: 'text-center',
                cell: (student) => (
                  <Badge variant={statusVariant(student.status)} dot>
                    {student.status}
                  </Badge>
                ),
              },
              {
                key: 'digital_id',
                header: 'Digital ID',
                className: 'text-center',
                cell: (student) => student.digital_id_status ?? '—',
              },
              {
                key: 'actions',
                header: 'Action',
                className: 'text-right',
                cell: (student) => (
                  <Button size="sm" variant="ghost" className="h-8 text-xs gap-1 text-mema-teal-800" asChild>
                    <a href={api.getStudentDigitalIdUrl(student.id)} target="_blank" rel="noreferrer">
                      <Download className="h-3.5 w-3.5" /> Digital ID
                    </a>
                  </Button>
                ),
              },
            ]}
          />
        </CardContent>
      </Card>
    </div>
  );
}
