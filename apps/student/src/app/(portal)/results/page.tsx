'use client';

import React, { useMemo } from 'react';
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
} from '@mema/ui';
import { useAuth } from '@mema/auth';
import { api } from '@mema/api-client';
import { Download, CheckCircle2 } from 'lucide-react';
import type { TermGpa } from '@mema/types';

function formatStanding(standing: TermGpa['standing']): string {
  return standing.replaceAll('_', ' ').replace(/\b\w/g, (char) => char.toUpperCase());
}

export default function StudentResultsPage() {
  const { user } = useAuth();
  const { data, isLoading, isError, error } = useQuery({
    queryKey: ['exams', 'term-gpas', user?.person?.id],
    queryFn: () => api.getTermGpas(),
    enabled: Boolean(user),
  });

  const termGpas = useMemo(() => {
    if (!data || !user?.person?.id) return [];
    return data.filter(
      (row) =>
        row.student?.person_id === user.person?.id || row.student?.person?.id === user.person?.id
    );
  }, [data, user]);

  const latest = termGpas[0];

  return (
    <div className="space-y-8">
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900 font-heading">
            Academic Performance & Transcripts
          </h2>
          <p className="text-sm text-slate-500 mt-1">
            Senate-approved term GPA records from{' '}
            <code className="text-xs">GET /api/v1/exams/term-gpas</code>
          </p>
        </div>
        <Button className="gap-2 self-start sm:self-auto bg-mema-teal-800 hover:bg-mema-teal-700 text-white">
          <Download className="h-4 w-4" /> Download Official PDF Transcript
        </Button>
      </div>

      {isError && (
        <Alert variant="destructive">
          <AlertTitle>Unable to load results</AlertTitle>
          <AlertDescription>
            {error instanceof Error ? error.message : 'An unexpected error occurred.'}
          </AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-1 sm:grid-cols-4 gap-5">
        <Card className="p-5 bg-white border-l-4 border-l-mema-teal-800">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Cumulative GPA (CGPA)
          </p>
          <h3 className="text-3xl font-bold text-mema-teal-900 mt-2 font-heading">
            {latest ? Number(latest.cumulative_gpa).toFixed(2) : isLoading ? '…' : '—'}
          </h3>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-mema-green-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Credits Earned
          </p>
          <h3 className="text-3xl font-bold text-mema-green-700 mt-2 font-heading">
            {latest ? latest.cumulative_credits_earned : isLoading ? '…' : '—'}
          </h3>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-blue-600">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Academic Standing
          </p>
          <h3 className="text-2xl font-bold text-blue-900 mt-2 font-heading">
            {latest ? formatStanding(latest.standing) : isLoading ? 'Loading…' : '—'}
          </h3>
        </Card>

        <Card className="p-5 bg-white border-l-4 border-l-amber-500">
          <p className="text-xs font-semibold text-slate-500 uppercase tracking-wider">
            Term Records
          </p>
          <p className="text-3xl font-bold text-amber-700 mt-2 font-heading">{termGpas.length}</p>
          <p className="text-[11px] text-emerald-600 font-semibold mt-1 flex items-center gap-1">
            <CheckCircle2 className="h-3.5 w-3.5" /> Published GPA summaries
          </p>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Term GPA History</CardTitle>
          <CardDescription>
            Per-semester GPA progression for your student record. Detailed per-course marks require a
            student-scoped marks API (pending Codex).
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading ? (
            <div className="py-12 text-center text-sm text-slate-500">Loading term GPA records...</div>
          ) : termGpas.length === 0 ? (
            <div className="py-12 text-center text-sm text-slate-500">
              No published term GPA records found for your account.
            </div>
          ) : (
            <div className="space-y-4">
              {termGpas.map((row) => (
                <div
                  key={row.id}
                  className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4"
                >
                  <div>
                    <p className="font-semibold text-slate-900">
                      {row.term?.name ?? `Term ${row.term_id.slice(0, 8)}`}
                    </p>
                    <p className="text-xs text-slate-500 mt-1">
                      Term GPA {Number(row.term_gpa).toFixed(2)} · Cumulative{' '}
                      {Number(row.cumulative_gpa).toFixed(2)}
                    </p>
                  </div>
                  <Badge variant="success">{formatStanding(row.standing)}</Badge>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
