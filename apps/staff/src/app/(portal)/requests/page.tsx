'use client';

import React, { useState } from 'react';
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
import { CheckCircle2, RefreshCw } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Clearance action failed.';

export default function StaffRequestsPage() {
  const [busyId, setBusyId] = useState<string | null>(null);
  const [notes, setNotes] = useState<Record<string, string>>({});
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const queue = useQuery({
    queryKey: ['graduation', 'clearance-queue'],
    queryFn: () => api.getGraduationClearanceQueue(),
  });

  async function clearCheckpoint(id: string) {
    setBusyId(id);
    setError(null);
    setNotice(null);
    try {
      await api.clearGraduationCheckpoint(id, notes[id] || undefined);
      setNotice('Checkpoint cleared successfully.');
      await queue.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusyId(null);
    }
  }

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">Clearance Queue</h2>
          <p className="text-sm text-slate-500 mt-1">
            Departmental sign-offs for graduation applications (MOD-02-09)
          </p>
        </div>
        <Button variant="outline" onClick={() => queue.refetch()} className="gap-2 self-start sm:self-auto">
          <RefreshCw className="h-4 w-4" /> Refresh
        </Button>
      </div>

      {notice && (
        <Alert>
          <CheckCircle2 className="h-4 w-4" />
          <AlertTitle>Cleared</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Action failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Pending checkpoints</CardTitle>
          <CardDescription>
            {queue.data?.length ?? 0} item(s) awaiting clearance · requires{' '}
            <code className="text-xs">graduation.clearance.clear</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student</TableHead>
                <TableHead>Department</TableHead>
                <TableHead>Notes</TableHead>
                <TableHead className="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(queue.data ?? []).map((item) => {
                const app = item.application as {
                  student?: {
                    student_number?: string;
                    person?: { given_name?: string; family_name?: string };
                    programme?: { name?: string };
                  };
                };
                const student = app?.student;
                const name = student?.person
                  ? `${student.person.given_name ?? ''} ${student.person.family_name ?? ''}`.trim()
                  : '—';
                const id = String(item.id);

                return (
                  <TableRow key={id}>
                    <TableCell>
                      <div className="font-medium">{name}</div>
                      <div className="text-xs text-slate-500">
                        {student?.student_number} · {student?.programme?.name}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge variant="outline">{String(item.department_name ?? item.department_code)}</Badge>
                    </TableCell>
                    <TableCell>
                      <Input
                        placeholder="Optional clearance notes"
                        className="h-8 text-xs"
                        value={notes[id] ?? ''}
                        onChange={(e) => setNotes((prev) => ({ ...prev, [id]: e.target.value }))}
                      />
                    </TableCell>
                    <TableCell className="text-right">
                      <Button
                        size="sm"
                        disabled={busyId === id}
                        onClick={() => clearCheckpoint(id)}
                        className="gap-1"
                      >
                        {busyId === id ? (
                          <MemaLoaderInline size={36} />
                        ) : (
                          <CheckCircle2 className="h-3 w-3" />
                        )}
                        Clear
                      </Button>
                    </TableCell>
                  </TableRow>
                );
              })}
              {(queue.data ?? []).length === 0 && (
                <TableRow>
                  <TableCell colSpan={4} className="text-center text-slate-500 py-8">
                    {queue.isLoading ? 'Loading clearance queue…' : 'Queue is empty.'}
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
