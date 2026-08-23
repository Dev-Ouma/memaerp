'use client';

import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams } from 'next/navigation';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError } from '@mema/api-client';
import type { StudentMark } from '@mema/types';
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
  MemaLoader,
  MemaLoaderInline,
} from '@mema/ui';
import { Save, Send } from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Marks operation failed.';

type DraftScores = Record<string, { cat: string; exam: string }>;

function rowKey(mark: StudentMark): string {
  return mark.course_enrollment_id ?? mark.id;
}

export default function LecturerMarksPage() {
  const searchParams = useSearchParams();
  const initialOffering = searchParams.get('offering') ?? '';

  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });

  const myOfferings = useMemo(() => {
    const uid = user.data?.id;
    return (offerings.data ?? []).filter((o) => !uid || o.lecturer_id === uid);
  }, [offerings.data, user.data?.id]);

  const [selectedOfferingId, setSelectedOfferingId] = useState(initialOffering);
  const [drafts, setDrafts] = useState<DraftScores>({});
  const [busyId, setBusyId] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!selectedOfferingId && myOfferings[0]) setSelectedOfferingId(myOfferings[0].id);
  }, [myOfferings, selectedOfferingId]);

  const marksSheet = useQuery({
    queryKey: ['exams', 'marks-sheet', selectedOfferingId],
    queryFn: () => api.getMarksSheet(selectedOfferingId),
    enabled: Boolean(selectedOfferingId),
  });

  useEffect(() => {
    if (!marksSheet.data) return;
    const next: DraftScores = {};
    for (const mark of marksSheet.data) {
      const key = rowKey(mark);
      next[key] = {
        cat: mark.cat_score != null ? String(mark.cat_score) : '',
        exam: mark.exam_score != null ? String(mark.exam_score) : '',
      };
    }
    setDrafts(next);
  }, [marksSheet.data]);

  const selectedOffering = myOfferings.find((o) => o.id === selectedOfferingId);

  const saveRow = useCallback(
    async (mark: StudentMark) => {
      const key = rowKey(mark);
      const draft = drafts[key];
      if (!draft || !selectedOfferingId) return;
      setBusyId(key);
      setError(null);
      try {
        await api.saveMarks(selectedOfferingId, {
          enrollment_id: mark.course_enrollment_id,
          cat_score: Number(draft.cat || 0),
          exam_score: Number(draft.exam || 0),
        });
        setNotice('Marks saved as draft.');
        await marksSheet.refetch();
      } catch (reason) {
        setError(messageFrom(reason));
      } finally {
        setBusyId(null);
      }
    },
    [drafts, selectedOfferingId, marksSheet],
  );

  const submitRow = useCallback(
    async (mark: StudentMark) => {
      const key = rowKey(mark);
      if (!selectedOfferingId) return;
      setBusyId(key);
      setError(null);
      try {
        await api.submitMarks(selectedOfferingId, mark.course_enrollment_id);
        setNotice('Marks submitted for moderation.');
        await marksSheet.refetch();
      } catch (reason) {
        setError(messageFrom(reason));
      } finally {
        setBusyId(null);
      }
    },
    [selectedOfferingId, marksSheet],
  );

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">Marks Entry</h2>
        <p className="text-sm text-slate-500 mt-1">
          CAT (0–40) + Exam (0–60) · draft save and submit per student
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
          <CardTitle>Select offering</CardTitle>
          <CardDescription>
            {selectedOffering
              ? `${selectedOffering.course?.code} · ${selectedOffering.section_code}`
              : 'Choose a section to load the marks sheet'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <select
            className="h-10 w-full max-w-md rounded-lg border border-slate-300 bg-white px-3 text-sm"
            value={selectedOfferingId}
            onChange={(event) => setSelectedOfferingId(event.target.value)}
          >
            {myOfferings.map((offering) => (
              <option key={offering.id} value={offering.id}>
                {offering.course?.code} · {offering.section_code}
              </option>
            ))}
          </select>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Marks sheet</CardTitle>
          <CardDescription>Enrolled students for the selected offering</CardDescription>
        </CardHeader>
        <CardContent>
          {marksSheet.isLoading ? (
            <div className="flex justify-center py-12">
              <MemaLoader size={72} label="Loading marks…" />
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Student</TableHead>
                  <TableHead>CAT /40</TableHead>
                  <TableHead>Exam /60</TableHead>
                  <TableHead>Total</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(marksSheet.data ?? []).map((mark) => {
                  const key = rowKey(mark);
                  const student = mark.course_enrollment?.student;
                  const person = student?.person;
                  const name = person
                    ? `${person.first_name ?? ''} ${person.last_name ?? ''}`.trim()
                    : student?.student_number ?? '—';
                  const draft = drafts[key] ?? { cat: '', exam: '' };
                  const total =
                    (Number(draft.cat) || 0) + (Number(draft.exam) || 0);
                  const status = (mark as StudentMark & { approval_status?: string }).approval_status ?? 'DRAFT';

                  return (
                    <TableRow key={mark.id}>
                      <TableCell>
                        <div className="font-medium">{name}</div>
                        <div className="text-xs text-slate-500">{student?.student_number}</div>
                      </TableCell>
                      <TableCell>
                        <Input
                          type="number"
                          min={0}
                          max={40}
                          className="w-20 h-8"
                          value={draft.cat}
                          disabled={status !== 'DRAFT'}
                          onChange={(e) =>
                            setDrafts((prev) => ({
                              ...prev,
                              [key]: { ...draft, cat: e.target.value },
                            }))
                          }
                        />
                      </TableCell>
                      <TableCell>
                        <Input
                          type="number"
                          min={0}
                          max={60}
                          className="w-20 h-8"
                          value={draft.exam}
                          disabled={status !== 'DRAFT'}
                          onChange={(e) =>
                            setDrafts((prev) => ({
                              ...prev,
                              [key]: { ...draft, exam: e.target.value },
                            }))
                          }
                        />
                      </TableCell>
                      <TableCell className="font-mono font-semibold">{total || '—'}</TableCell>
                      <TableCell>
                        <Badge variant={status === 'DRAFT' ? 'outline' : 'success'}>{status}</Badge>
                      </TableCell>
                      <TableCell className="text-right space-x-2">
                        <Button
                          size="sm"
                          variant="outline"
                          disabled={busyId === key || status !== 'DRAFT'}
                          onClick={() => saveRow(mark)}
                          className="gap-1"
                        >
                          {busyId === key ? (
                            <MemaLoaderInline size={36} />
                          ) : (
                            <Save className="h-3 w-3" />
                          )}
                          Save
                        </Button>
                        <Button
                          size="sm"
                          disabled={busyId === key || status !== 'DRAFT'}
                          onClick={() => submitRow(mark)}
                          className="gap-1"
                        >
                          <Send className="h-3 w-3" /> Submit
                        </Button>
                      </TableCell>
                    </TableRow>
                  );
                })}
                {(marksSheet.data ?? []).length === 0 && (
                  <TableRow>
                    <TableCell colSpan={6} className="text-center text-slate-500 py-8">
                      No enrolled students or marks records for this offering.
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
