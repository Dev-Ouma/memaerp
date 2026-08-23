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
  MemaLoaderInline,
} from '@mema/ui';
import { CalendarPlus, CheckCircle2, GraduationCap, UserRound } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Advising request failed.';

type CourseRow = {
  course_id?: string;
  course_code?: string;
  course_title?: string;
  credits?: number;
  course_type?: string;
  year_level?: number;
};

function CourseList({ title, rows, empty }: { title: string; rows: CourseRow[]; empty: string }) {
  return (
    <Card>
      <CardHeader className="pb-2">
        <CardTitle className="text-base">{title}</CardTitle>
        <CardDescription>{rows.length} course(s)</CardDescription>
      </CardHeader>
      <CardContent className="space-y-2">
        {rows.map((row) => (
          <div key={String(row.course_id)} className="flex justify-between text-sm border-b border-slate-100 pb-2 last:border-0">
            <div>
              <div className="font-medium">{row.course_code}</div>
              <div className="text-xs text-slate-500">{row.course_title}</div>
            </div>
            <div className="text-right text-xs text-slate-500">
              <div>{row.credits ?? 0} cr</div>
              <div>{row.course_type}</div>
            </div>
          </div>
        ))}
        {rows.length === 0 && <p className="text-sm text-slate-500">{empty}</p>}
      </CardContent>
    </Card>
  );
}

export default function StudentDegreeProgressPage() {
  const [busy, setBusy] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [topic, setTopic] = useState('Course selection & degree progress');
  const [scheduledAt, setScheduledAt] = useState('');

  const progress = useQuery({
    queryKey: ['advising', 'my-progress'],
    queryFn: () => api.getMyDegreeProgress(),
  });

  const data = progress.data ?? {};
  const completed = (data.completed as CourseRow[] | undefined) ?? [];
  const inProgress = (data.in_progress as CourseRow[] | undefined) ?? [];
  const remaining = (data.remaining as CourseRow[] | undefined) ?? [];
  const recommendations = (data.recommendations as CourseRow[] | undefined) ?? [];
  const notes = (data.notes as Array<Record<string, unknown>> | undefined) ?? [];
  const advisor = data.advisor as { name?: string; email?: string } | null | undefined;

  async function requestSession(event: FormEvent) {
    event.preventDefault();
    if (!scheduledAt) return;
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      await api.requestAdvisingSession({
        scheduled_at: new Date(scheduledAt).toISOString(),
        mode: 'IN_PERSON',
        topic,
      });
      setNotice('Advisory session requested. Your advisor will confirm.');
      setScheduledAt('');
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Degree Progress</h2>
        <p className="text-sm text-slate-500 mt-1">
          Curriculum completion vs your transcript · academic advising (MOD-02-03)
        </p>
      </div>

      {notice && (
        <Alert>
          <CheckCircle2 className="h-4 w-4" />
          <AlertTitle>Request sent</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}
      {error && (
        <Alert variant="destructive">
          <AlertTitle>Action failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card className="p-5">
          <p className="text-xs uppercase text-slate-500 font-semibold">Completion</p>
          <p className="text-3xl font-bold text-mema-teal-900 mt-1">{String(data.completion_percent ?? 0)}%</p>
        </Card>
        <Card className="p-5">
          <p className="text-xs uppercase text-slate-500 font-semibold">Credits earned</p>
          <p className="text-3xl font-bold mt-1">
            {String(data.credits_earned ?? 0)} / {String(data.credits_required ?? 0)}
          </p>
        </Card>
        <Card className="p-5">
          <p className="text-xs uppercase text-slate-500 font-semibold">CGPA</p>
          <p className="text-3xl font-bold mt-1">{Number(data.cgpa ?? 0).toFixed(2)}</p>
        </Card>
        <Card className="p-5">
          <p className="text-xs uppercase text-slate-500 font-semibold">Status</p>
          <Badge className="mt-2" variant={data.at_risk ? 'destructive' : data.audit_passed ? 'success' : 'warning'}>
            {data.at_risk ? 'At risk' : data.audit_passed ? 'Audit passed' : 'In progress'}
          </Badge>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <UserRound className="h-5 w-5 text-mema-teal-700" />
              Academic advisor
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {advisor ? (
              <div className="text-sm">
                <div className="font-semibold">{advisor.name ?? 'Advisor'}</div>
                <div className="text-slate-500">{advisor.email}</div>
              </div>
            ) : (
              <p className="text-sm text-slate-500">No advisor assigned yet.</p>
            )}
            <form onSubmit={requestSession} className="space-y-3">
              <Input
                type="datetime-local"
                value={scheduledAt}
                onChange={(e) => setScheduledAt(e.target.value)}
                required
              />
              <Input value={topic} onChange={(e) => setTopic(e.target.value)} placeholder="Session topic" />
              <Button type="submit" disabled={busy || !advisor} className="gap-2">
                {busy ? <MemaLoaderInline size={40} /> : <CalendarPlus className="h-4 w-4" />}
                Request session
              </Button>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <GraduationCap className="h-5 w-5 text-mema-teal-700" />
              Recommended next courses
            </CardTitle>
            <CardDescription>Prerequisites met · from remaining curriculum</CardDescription>
          </CardHeader>
          <CardContent className="space-y-2">
            {recommendations.map((row) => (
              <div key={String(row.course_id)} className="text-sm flex justify-between border-b border-slate-100 pb-2">
                <span className="font-medium">{row.course_code} · {row.course_title}</span>
                <span className="text-xs text-slate-500">{row.credits} cr</span>
              </div>
            ))}
            {recommendations.length === 0 && (
              <p className="text-sm text-slate-500">
                {progress.isLoading ? 'Loading…' : 'No eligible recommendations right now.'}
              </p>
            )}
          </CardContent>
        </Card>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <CourseList title="Completed" rows={completed} empty="No ratified completed courses yet." />
        <CourseList title="In progress" rows={inProgress} empty="No current enrollments." />
        <CourseList title="Remaining" rows={remaining} empty="Curriculum requirements complete." />
      </div>

      {notes.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Advisor notes shared with you</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3">
            {notes.map((note) => (
              <div key={String(note.id)} className="text-sm border-b border-slate-100 pb-3 last:border-0">
                <div className="text-xs text-slate-500 mb-1">
                  {note.created_at ? new Date(String(note.created_at)).toLocaleString() : '—'} ·{' '}
                  {String(note.note_type ?? 'GENERAL')}
                </div>
                <p>{String(note.note_text)}</p>
              </div>
            ))}
          </CardContent>
        </Card>
      )}
    </div>
  );
}
