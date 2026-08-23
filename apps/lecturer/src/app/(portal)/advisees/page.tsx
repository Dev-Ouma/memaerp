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
import { NotebookPen, Users } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Advising action failed.';

export default function LecturerAdviseesPage() {
  const [selectedStudentId, setSelectedStudentId] = useState('');
  const [noteText, setNoteText] = useState('');
  const [visibleToStudent, setVisibleToStudent] = useState(true);
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const advisees = useQuery({ queryKey: ['advising', 'my-advisees'], queryFn: () => api.getMyAdvisees() });
  const audit = useQuery({
    queryKey: ['advising', 'audit', selectedStudentId],
    queryFn: () => api.getStudentDegreeAudit(selectedStudentId),
    enabled: Boolean(selectedStudentId),
  });
  const notes = useQuery({
    queryKey: ['advising', 'notes', selectedStudentId],
    queryFn: () => api.getStudentAdvisoryNotes(selectedStudentId),
    enabled: Boolean(selectedStudentId),
  });
  const sessions = useQuery({
    queryKey: ['advising', 'sessions'],
    queryFn: () => api.getAdvisingSessions(),
  });

  useEffect(() => {
    const first = advisees.data?.[0] as { student?: { id?: string } } | undefined;
    if (!selectedStudentId && first?.student?.id) setSelectedStudentId(first.student.id);
  }, [advisees.data, selectedStudentId]);

  async function saveNote(event: FormEvent) {
    event.preventDefault();
    if (!selectedStudentId || !noteText.trim()) return;
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      await api.createAdvisoryNote({
        student_id: selectedStudentId,
        note_text: noteText.trim(),
        note_type: 'RECOMMENDATION',
        visible_to_student: visibleToStudent,
      });
      setNoteText('');
      setNotice('Advisory note saved.');
      await notes.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  async function confirmSession(sessionId: string) {
    setBusy(true);
    setError(null);
    try {
      await api.updateAdvisingSession(sessionId, { status: 'CONFIRMED' });
      setNotice('Session confirmed.');
      await sessions.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  const remaining = (audit.data?.remaining as Array<Record<string, unknown>> | undefined) ?? [];
  const recommendations = (audit.data?.recommendations as Array<Record<string, unknown>> | undefined) ?? [];

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">My Advisees</h2>
        <p className="text-sm text-slate-500 mt-1">
          Degree audits, recommendations and advisory notes (MOD-02-03)
        </p>
      </div>

      {notice && (
        <Alert>
          <AlertTitle>Saved</AlertTitle>
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
          <CardTitle className="flex items-center gap-2">
            <Users className="h-5 w-5 text-mema-teal-700" />
            Advisee portfolio
          </CardTitle>
          <CardDescription>{advisees.data?.length ?? 0} assigned student(s)</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Student</TableHead>
                <TableHead>Programme</TableHead>
                <TableHead>CGPA</TableHead>
                <TableHead>Progress</TableHead>
                <TableHead>Risk</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {(advisees.data ?? []).map((row) => {
                const student = row.student as {
                  id?: string;
                  student_number?: string;
                  full_name?: string;
                  programme?: string;
                  cgpa?: number;
                };
                return (
                  <TableRow
                    key={String(row.assignment_id)}
                    className={selectedStudentId === student?.id ? 'bg-mema-teal-50/40' : 'cursor-pointer'}
                    onClick={() => student?.id && setSelectedStudentId(student.id)}
                  >
                    <TableCell>
                      <div className="font-medium">{student?.full_name}</div>
                      <div className="text-xs text-slate-500">{student?.student_number}</div>
                    </TableCell>
                    <TableCell className="text-sm">{student?.programme ?? '—'}</TableCell>
                    <TableCell className="font-mono">{Number(student?.cgpa ?? 0).toFixed(2)}</TableCell>
                    <TableCell>{String(row.completion_percent ?? 0)}%</TableCell>
                    <TableCell>
                      <Badge variant={row.at_risk ? 'destructive' : 'success'}>
                        {row.at_risk ? 'At risk' : 'On track'}
                      </Badge>
                    </TableCell>
                  </TableRow>
                );
              })}
              {(advisees.data ?? []).length === 0 && (
                <TableRow>
                  <TableCell colSpan={5} className="text-center text-slate-500 py-8">
                    {advisees.isLoading ? 'Loading advisees…' : 'No advisees assigned to you yet.'}
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {selectedStudentId && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Degree audit</CardTitle>
              <CardDescription>
                {String(audit.data?.credits_earned ?? 0)} / {String(audit.data?.credits_required ?? 0)} credits ·{' '}
                {remaining.length} remaining
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <div>
                <h4 className="text-sm font-semibold mb-2">Recommended next</h4>
                {recommendations.slice(0, 5).map((row) => (
                  <div key={String(row.course_id)} className="text-sm flex justify-between border-b border-slate-100 py-1">
                    <span>{String(row.course_code)} · {String(row.course_title)}</span>
                    <span className="text-xs text-slate-500">{String(row.credits)} cr</span>
                  </div>
                ))}
                {recommendations.length === 0 && (
                  <p className="text-sm text-slate-500">{audit.isLoading ? 'Loading…' : 'No recommendations.'}</p>
                )}
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <NotebookPen className="h-5 w-5 text-mema-teal-700" />
                Advisory note
              </CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={saveNote} className="space-y-3">
                <textarea
                  className="w-full min-h-24 rounded-lg border border-slate-300 p-3 text-sm"
                  value={noteText}
                  onChange={(e) => setNoteText(e.target.value)}
                  placeholder="Session notes / recommendations…"
                  required
                />
                <label className="flex items-center gap-2 text-sm text-slate-700">
                  <input
                    type="checkbox"
                    checked={visibleToStudent}
                    onChange={(e) => setVisibleToStudent(e.target.checked)}
                  />
                  Visible to student
                </label>
                <Button type="submit" disabled={busy} className="gap-2">
                  {busy ? <MemaLoaderInline size={40} /> : <NotebookPen className="h-4 w-4" />}
                  Save note
                </Button>
              </form>
              <div className="mt-4 space-y-2">
                {(notes.data ?? []).slice(0, 5).map((note) => (
                  <div key={String(note.id)} className="text-xs border-t border-slate-100 pt-2">
                    <div className="text-slate-400 mb-1">
                      {note.created_at ? new Date(String(note.created_at)).toLocaleString() : '—'}
                    </div>
                    <p className="text-sm text-slate-700">{String(note.note_text)}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>
      )}

      <Card>
        <CardHeader>
          <CardTitle>Session requests</CardTitle>
        </CardHeader>
        <CardContent className="space-y-3">
          {(sessions.data ?? []).map((session) => {
            const student = session.student as { person?: { given_name?: string; family_name?: string }; student_number?: string };
            const name = `${student?.person?.given_name ?? ''} ${student?.person?.family_name ?? ''}`.trim();
            return (
              <div key={String(session.id)} className="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                <div>
                  <div className="font-medium">{name || student?.student_number}</div>
                  <div className="text-xs text-slate-500">
                    {session.scheduled_at ? new Date(String(session.scheduled_at)).toLocaleString() : '—'} ·{' '}
                    {String(session.topic ?? 'General')}
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <Badge variant="outline">{String(session.status)}</Badge>
                  {session.status === 'REQUESTED' && (
                    <Button size="sm" disabled={busy} onClick={() => confirmSession(String(session.id))}>
                      Confirm
                    </Button>
                  )}
                </div>
              </div>
            );
          })}
          {(sessions.data ?? []).length === 0 && (
            <p className="text-sm text-slate-500">No session requests.</p>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
