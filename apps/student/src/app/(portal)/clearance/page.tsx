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
  formatCurrency,
  MemaLoaderInline,
} from '@mema/ui';
import {
  BookOpen,
  Building,
  CheckCircle2,
  CreditCard,
  Download,
  GraduationCap,
  IdCard,
} from 'lucide-react';

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'Clearance request failed.';

function deptIcon(code?: string) {
  if (code === 'FIN') return <CreditCard className="h-5 w-5 text-amber-600" />;
  if (code === 'LIB') return <Building className="h-5 w-5 text-mema-teal-700" />;
  return <BookOpen className="h-5 w-5 text-mema-teal-700" />;
}

export default function StudentClearancePage() {
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const dashboard = useQuery({ queryKey: ['portal', 'dashboard'], queryFn: () => api.getPortalDashboard() });
  const clearance = useQuery({
    queryKey: ['graduation', 'clearance-status'],
    queryFn: () => api.getGraduationClearanceStatus(),
  });

  const audit = clearance.data?.audit as Record<string, unknown> | undefined;
  const application = clearance.data?.application as Record<string, unknown> | null | undefined;
  const finance = clearance.data?.finance_clearance as Record<string, unknown> | undefined;
  const checkpoints = (application?.checkpoints as Array<Record<string, unknown>> | undefined) ?? [];
  const student = dashboard.data?.student as { id?: string; student_number?: string; full_name?: string } | undefined;

  const clearedCount = checkpoints.filter((c) => c.status === 'CLEARED').length;
  const totalSteps = checkpoints.length;

  async function applyForClearance() {
    setBusy(true);
    setError(null);
    try {
      await api.applyGraduation();
      await clearance.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold text-slate-900 font-heading">
          Clearance & Digital ID
        </h2>
        <p className="text-sm text-slate-500 mt-1">
          Graduation clearance, exam card, and student credentials
        </p>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Unable to proceed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="grid grid-cols-1 md:grid-cols-3 gap-5">
        <Card className="p-5">
          <div className="flex items-center gap-3 mb-3">
            <IdCard className="h-5 w-5 text-mema-teal-700" />
            <h3 className="font-semibold text-slate-900">Digital ID</h3>
          </div>
          {student?.id ? (
            <Button asChild variant="outline" size="sm" className="gap-2">
              <a href={api.getStudentDigitalIdUrl(student.id)} download>
                <Download className="h-4 w-4" /> Download ID Card
              </a>
            </Button>
          ) : (
            <p className="text-xs text-slate-500">Student record loading…</p>
          )}
        </Card>
        <Card className="p-5">
          <div className="flex items-center gap-3 mb-3">
            <GraduationCap className="h-5 w-5 text-mema-teal-700" />
            <h3 className="font-semibold text-slate-900">Exam card</h3>
          </div>
          <Button asChild variant="outline" size="sm" className="gap-2">
            <a href={api.getExamCardUrl()} download>
              <Download className="h-4 w-4" /> Download exam card
            </a>
          </Button>
        </Card>
        <Card className="p-5">
          <div className="flex items-center gap-3 mb-3">
            <BookOpen className="h-5 w-5 text-mema-teal-700" />
            <h3 className="font-semibold text-slate-900">Transcript</h3>
          </div>
          <Button asChild variant="outline" size="sm" className="gap-2">
            <a href={api.getGraduationTranscriptUrl()} download>
              <Download className="h-4 w-4" /> Official transcript
            </a>
          </Button>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between flex-wrap gap-2">
            <span>Degree audit</span>
            <Badge variant={audit?.audit_passed ? 'success' : 'warning'}>
              {audit?.audit_passed ? 'Eligible' : 'Credits pending'}
            </Badge>
          </CardTitle>
          <CardDescription>
            {Number(audit?.credits_earned ?? 0)} / {Number(audit?.credits_required ?? 0)} credits earned toward graduation
          </CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap items-center gap-3">
          {!application && (
            <Button onClick={applyForClearance} disabled={busy || !audit?.audit_passed} className="gap-2">
              {busy ? <MemaLoaderInline size={40} /> : <CheckCircle2 className="h-4 w-4" />}
              Apply for graduation clearance
            </Button>
          )}
          {application && (
            <Badge variant="outline">Application {String(application.status ?? 'PENDING')}</Badge>
          )}
          {finance && (
            <span className="text-sm text-slate-600">
              Fee balance: {formatCurrency(Number(finance.balance ?? 0))} ·{' '}
              {finance.graduation_cleared ? 'Graduation fees cleared' : 'Outstanding balance'}
            </span>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle className="flex items-center justify-between">
            <span>Clearance progress</span>
            {totalSteps > 0 && (
              <Badge variant={clearedCount === totalSteps ? 'success' : 'warning'}>
                {clearedCount} of {totalSteps} completed
              </Badge>
            )}
          </CardTitle>
          <CardDescription>
            Multi-department sign-off from the graduation module
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {clearance.isLoading && (
            <p className="text-sm text-slate-500 py-6 text-center">Loading clearance status…</p>
          )}
          {!clearance.isLoading && !application && (
            <p className="text-sm text-slate-500 py-6 text-center">
              No clearance application yet. Apply once your degree audit passes.
            </p>
          )}
          {checkpoints.map((step) => {
            const isApproved = step.status === 'CLEARED';
            const code = String(step.department_code ?? '');
            return (
              <div
                key={String(step.id)}
                className={`p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-4 ${
                  isApproved ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40'
                }`}
              >
                <div className="flex items-start gap-3.5">
                  <div
                    className={`h-10 w-10 rounded-xl flex items-center justify-center shrink-0 ${
                      isApproved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                    }`}
                  >
                    {deptIcon(code)}
                  </div>
                  <div className="space-y-1">
                    <h4 className="font-semibold text-slate-900 text-sm">
                      {String(step.department_name ?? code)}
                    </h4>
                    {step.notes ? (
                      <p className="text-xs text-slate-600">{String(step.notes)}</p>
                    ) : null}
                    {step.cleared_at ? (
                      <p className="text-[11px] text-slate-400">
                        Cleared on {new Date(String(step.cleared_at)).toLocaleDateString()}
                      </p>
                    ) : null}
                  </div>
                </div>
                <Badge variant={isApproved ? 'success' : 'warning'} dot>
                  {String(step.status)}
                </Badge>
              </div>
            );
          })}
        </CardContent>
      </Card>
    </div>
  );
}
