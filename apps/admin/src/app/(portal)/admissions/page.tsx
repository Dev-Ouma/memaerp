'use client';

import React, { FormEvent, useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError, getApiRootUrl } from '@mema/api-client';
import type { ApplicationStatus } from '@mema/types';
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
  formatDate,
} from '@mema/ui';
import { Download, FileText, RefreshCw, UserCheck } from 'lucide-react';

const fieldClass =
  'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-mema-teal-700';
const labelClass = 'space-y-1 text-sm font-medium text-slate-700';

function statusVariant(status: ApplicationStatus) {
  if (status === 'ACCEPTED' || status === 'MATRICULATED') return 'success' as const;
  if (status === 'ADMITTED' || status === 'SHORTLISTED') return 'warning' as const;
  if (status === 'REJECTED' || status === 'EXPIRED') return 'destructive' as const;
  return 'outline' as const;
}

const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'The admissions operation failed.';

export default function AdminAdmissionsPage() {
  const [selectedId, setSelectedId] = useState('');
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const dashboard = useQuery({ queryKey: ['admissions', 'dashboard'], queryFn: () => api.getAdmissionsDashboard() });
  const applications = useQuery({
    queryKey: ['admissions', 'applications', search, statusFilter],
    queryFn: () => api.getApplications({ search: search || undefined, status: statusFilter || undefined }),
  });

  const selected = applications.data?.find((item) => item.id === selectedId) ?? applications.data?.[0];
  const canReview = user.data?.permissions.includes('admission.application.review') ?? false;
  const canDecide = user.data?.permissions.includes('admission.application.decide') ?? false;
  const canImport = user.data?.permissions.includes('admission.kuccps.import') ?? false;

  const funnel = useMemo(
    () => [
      { label: 'Submitted', value: dashboard.data?.submitted ?? 0 },
      { label: 'Under review', value: dashboard.data?.under_review ?? 0 },
      { label: 'Shortlisted', value: dashboard.data?.shortlisted ?? 0 },
      { label: 'Admitted', value: dashboard.data?.admitted ?? 0 },
      { label: 'Accepted', value: dashboard.data?.accepted ?? 0 },
    ],
    [dashboard.data],
  );

  async function refresh() {
    await Promise.all([dashboard.refetch(), applications.refetch()]);
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

  async function verifyApplication(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selected) return;
    const data = new FormData(event.currentTarget);
    await perform(
      () => api.verifyApplication(selected.id, String(data.get('notes') || '') || undefined),
      'Document screening recorded.',
    );
  }

  async function decideApplication(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!selected) return;
    const data = new FormData(event.currentTarget);
    await perform(
      () =>
        api.decideApplication(selected.id, {
          decision: String(data.get('decision')) as 'ADMIT' | 'REJECT',
          reference: String(data.get('reference')),
          notes: String(data.get('notes') || '') || undefined,
        }),
      String(data.get('decision')) === 'ADMIT' ? 'Admission offer issued.' : 'Application rejected.',
    );
  }

  async function importKuccps(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const data = new FormData(event.currentTarget);
    await perform(
      () =>
        api.importKuccps({
          rows: [
            {
              kuccps_index: String(data.get('kuccps_index')),
              applicant_name: String(data.get('applicant_name')),
              programme_code: String(data.get('programme_code')),
              mean_grade: String(data.get('mean_grade') || '') || undefined,
            },
          ],
        }),
      'KUCCPS placement imported.',
    );
    event.currentTarget.reset();
  }

  const reportBase = `${getApiRootUrl()}/api/v1/admissions/report`;
  const feeReportBase = `${getApiRootUrl()}/api/v1/admissions/fee-report`;

  return (
    <div className="space-y-8" data-testid="admissions-console">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div className="max-w-3xl">
          <div className="text-mema-teal-700 mb-2 flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase">
            <UserCheck className="h-4 w-4" />
            MOD-01-05 · Admissions
          </div>
          <h1 className="font-heading text-3xl font-bold text-slate-900">Admissions & applicant review</h1>
          <p className="mt-2 text-sm text-slate-600">
            Direct and KUCCPS applications, document verification, committee decisions, offer letters and intake exports.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" className="gap-2" onClick={() => void refresh()} disabled={saving}>
            <RefreshCw className="h-4 w-4" /> Refresh
          </Button>
          <Button variant="outline" className="gap-2" asChild>
            <a href={`${reportBase}?format=pdf`} data-testid="admissions-report-pdf">
              <Download className="h-4 w-4" /> Intake roll PDF
            </a>
          </Button>
          <Button variant="outline" className="gap-2" asChild>
            <a href={`${feeReportBase}?format=csv`} data-testid="admissions-fee-report-csv">
              <Download className="h-4 w-4" /> Fee revenue CSV
            </a>
          </Button>
        </div>
      </div>

      {notice ? (
        <Alert>
          <AlertTitle>Updated</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      ) : null}
      {error ? (
        <Alert variant="destructive">
          <AlertTitle>Action failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      ) : null}

      <div className="grid gap-4 md:grid-cols-5">
        {funnel.map((item) => (
          <Card key={item.label}>
            <CardHeader className="pb-2">
              <CardDescription>{item.label}</CardDescription>
              <CardTitle className="text-2xl">{item.value}</CardTitle>
            </CardHeader>
          </Card>
        ))}
      </div>

      <div className="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <Card>
          <CardHeader>
            <CardTitle>Applicant queue</CardTitle>
            <CardDescription>Search, filter and open an application for screening or committee action.</CardDescription>
            <div className="grid gap-3 pt-4 md:grid-cols-[1fr_180px]">
              <Input
                placeholder="Search name, app no, email, ID…"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                aria-label="Search applications"
              />
              <select
                className={fieldClass}
                value={statusFilter}
                onChange={(event) => setStatusFilter(event.target.value)}
                aria-label="Filter by status"
              >
                <option value="">All statuses</option>
                {[
                  'DRAFT',
                  'SUBMITTED',
                  'UNDER_REVIEW',
                  'SHORTLISTED',
                  'ADMITTED',
                  'ACCEPTED',
                  'REJECTED',
                ].map((status) => (
                  <option key={status} value={status}>
                    {status}
                  </option>
                ))}
              </select>
            </div>
          </CardHeader>
          <CardContent className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Application</TableHead>
                  <TableHead>Applicant</TableHead>
                  <TableHead>Programme</TableHead>
                  <TableHead>Grade</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {(applications.data ?? []).map((application) => (
                  <TableRow
                    key={application.id}
                    data-testid={`application-row-${application.application_number}`}
                    className={selected?.id === application.id ? 'bg-mema-teal-50/60' : 'cursor-pointer'}
                    onClick={() => setSelectedId(application.id)}
                  >
                    <TableCell className="font-mono text-xs">{application.application_number}</TableCell>
                    <TableCell>
                      <div className="font-medium text-slate-900">
                        {application.person?.first_name} {application.person?.last_name}
                      </div>
                      <div className="text-xs text-slate-500">{application.person?.personal_email}</div>
                    </TableCell>
                    <TableCell>{application.programme?.code}</TableCell>
                    <TableCell>{application.mean_grade}</TableCell>
                    <TableCell>
                      <Badge variant={statusVariant(application.status)}>{application.status}</Badge>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>

        <div className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>Application detail</CardTitle>
              <CardDescription>
                {selected
                  ? `${selected.application_number} · ${selected.person?.personal_email ?? 'No email'}`
                  : 'Select an application from the queue.'}
              </CardDescription>
            </CardHeader>
            {selected ? (
              <CardContent className="space-y-4 text-sm">
                <div className="grid grid-cols-2 gap-3">
                  <div>
                    <div className="text-slate-500">Programme</div>
                    <div className="font-medium">{selected.programme?.name}</div>
                  </div>
                  <div>
                    <div className="text-slate-500">Campus</div>
                    <div className="font-medium">{selected.campus?.name}</div>
                  </div>
                  <div>
                    <div className="text-slate-500">School</div>
                    <div className="font-medium">{selected.secondary_school_name}</div>
                  </div>
                  <div>
                    <div className="text-slate-500">Score</div>
                    <div className="font-medium">{selected.qualification_score ?? '—'}</div>
                  </div>
                  <div>
                    <div className="text-slate-500">Fee</div>
                    <div className="font-medium">{selected.is_fee_paid ? 'Paid' : 'Unpaid'}</div>
                  </div>
                  <div>
                    <div className="text-slate-500">Submitted</div>
                    <div className="font-medium">
                      {selected.submitted_at ? formatDate(selected.submitted_at) : 'Draft'}
                    </div>
                  </div>
                </div>
                {selected.documents?.length ? (
                  <div className="rounded-lg border border-slate-200 p-3">
                    <div className="mb-2 flex items-center gap-2 font-medium text-slate-800">
                      <FileText className="h-4 w-4" /> Uploaded documents
                    </div>
                    <ul className="space-y-1 text-xs text-slate-600">
                      {selected.documents.map((document) => (
                        <li key={document.id}>
                          {document.document_type}: {document.original_name} ({document.verification_status})
                        </li>
                      ))}
                    </ul>
                  </div>
                ) : null}
                {['ADMITTED', 'ACCEPTED'].includes(selected.status) ? (
                  <Button variant="outline" className="w-full gap-2" asChild>
                    <a href={api.getAdmissionOfferLetterUrl(selected.id)} target="_blank" rel="noreferrer">
                      <Download className="h-4 w-4" /> Download offer letter
                    </a>
                  </Button>
                ) : null}
              </CardContent>
            ) : null}
          </Card>

          {canReview ? (
            <Card>
              <CardHeader>
                <CardTitle>Document screening</CardTitle>
                <CardDescription>Verify certificates and auto-score against programme cut-off.</CardDescription>
              </CardHeader>
              <CardContent>
                <form className="space-y-3" onSubmit={verifyApplication}>
                  <label className={labelClass}>
                    Notes
                    <textarea
                      name="notes"
                      className={`${fieldClass} min-h-[88px] py-2`}
                      placeholder="Verification notes for the admissions file"
                    />
                  </label>
                  <Button type="submit" disabled={!selected || saving} data-testid="verify-application">
                    Verify documents
                  </Button>
                </form>
              </CardContent>
            </Card>
          ) : null}

          {canDecide ? (
            <Card>
              <CardHeader>
                <CardTitle>Committee decision</CardTitle>
                <CardDescription>Issue or decline an admission offer for shortlisted files.</CardDescription>
              </CardHeader>
              <CardContent>
                <form className="space-y-3" onSubmit={decideApplication}>
                  <label className={labelClass}>
                    Decision
                    <select name="decision" className={fieldClass} defaultValue="ADMIT">
                      <option value="ADMIT">Admit</option>
                      <option value="REJECT">Reject</option>
                    </select>
                  </label>
                  <label className={labelClass}>
                    Committee reference
                    <Input name="reference" placeholder="ADM/COMM/2026/014" required />
                  </label>
                  <label className={labelClass}>
                    Notes
                    <textarea name="notes" className={`${fieldClass} min-h-[72px] py-2`} />
                  </label>
                  <Button type="submit" disabled={!selected || saving} data-testid="decide-application">
                    Record committee decision
                  </Button>
                </form>
              </CardContent>
            </Card>
          ) : null}

          {canImport ? (
            <Card>
              <CardHeader>
                <CardTitle>KUCCPS import</CardTitle>
                <CardDescription>Ingest a placement row into the admissions database.</CardDescription>
              </CardHeader>
              <CardContent>
                <form className="space-y-3" onSubmit={importKuccps}>
                  <label className={labelClass}>
                    KUCCPS index
                    <Input name="kuccps_index" placeholder="99887766001/2025" required />
                  </label>
                  <label className={labelClass}>
                    Applicant name
                    <Input name="applicant_name" placeholder="Peter Kamau" required />
                  </label>
                  <label className={labelClass}>
                    Programme code
                    <Input name="programme_code" defaultValue="BSC-CS" required />
                  </label>
                  <label className={labelClass}>
                    Mean grade
                    <Input name="mean_grade" placeholder="B+" />
                  </label>
                  <Button type="submit" disabled={saving} data-testid="import-kuccps">
                    Import placement
                  </Button>
                </form>
              </CardContent>
            </Card>
          ) : null}
        </div>
      </div>
    </div>
  );
}
