'use client';

import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError } from '@mema/api-client';
import type { CurriculumCourse, Programme } from '@mema/types';
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
} from '@mema/ui';
import {
  ArrowRight,
  BookOpen,
  CheckCircle2,
  Download,
  GitBranch,
  GraduationCap,
  Layers3,
  LockKeyhole,
  Plus,
  RefreshCw,
  ShieldCheck,
  Trash2,
  TriangleAlert,
} from 'lucide-react';

const fieldClass =
  'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-mema-teal-700';
const labelClass = 'space-y-1 text-sm font-medium text-slate-700';
const stages = ['HOD', 'DEAN', 'ACADEMIC_BOARD', 'SENATE'] as const;
const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'The curriculum operation failed.';

function statusVariant(status?: string) {
  if (status === 'APPROVED') return 'success' as const;
  if (status === 'UNDER_REVIEW') return 'warning' as const;
  return 'outline' as const;
}

export default function AdminProgrammesPage() {
  const [selectedProgrammeId, setSelectedProgrammeId] = useState('');
  const [selectedVersionId, setSelectedVersionId] = useState('');
  const [showProgrammeForm, setShowProgrammeForm] = useState(false);
  const [courseType, setCourseType] = useState<'CORE' | 'ELECTIVE' | 'REQUIRED_AUDIT'>('CORE');
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const programmes = useQuery({
    queryKey: ['curriculum', 'programmes'],
    queryFn: () => api.getProgrammes(),
  });
  const departments = useQuery({
    queryKey: ['institution', 'departments'],
    queryFn: () => api.getInstitutionDepartments(),
  });
  const years = useQuery({
    queryKey: ['institution', 'academic-years'],
    queryFn: () => api.getInstitutionAcademicYears(),
  });
  const catalogue = useQuery({
    queryKey: ['curriculum', 'course-catalogue'],
    queryFn: () => api.getCurriculumCourses(),
  });
  const versions = useQuery({
    queryKey: ['curriculum', 'versions', selectedProgrammeId],
    queryFn: () => api.getCurriculumVersions(selectedProgrammeId),
    enabled: selectedProgrammeId !== '',
  });

  useEffect(() => {
    if (!selectedProgrammeId && programmes.data?.[0]) setSelectedProgrammeId(programmes.data[0].id);
  }, [programmes.data, selectedProgrammeId]);

  useEffect(() => {
    if (!versions.data?.some((item) => item.id === selectedVersionId))
      setSelectedVersionId(versions.data?.[0]?.id ?? '');
  }, [selectedVersionId, versions.data]);

  const selectedProgramme = programmes.data?.find((item) => item.id === selectedProgrammeId);
  const version = versions.data?.find((item) => item.id === selectedVersionId);
  const canManage = user.data?.permissions.includes('curriculum.programme.manage') ?? false;
  const canApprove = user.data?.permissions.includes('curriculum.programme.approve') ?? false;
  const locked = version?.status === 'APPROVED' || version?.status === 'SUPERSEDED';
  const mappedIds = new Set(version?.curriculum_courses?.map((item) => item.course_id) ?? []);
  const availableCourses = catalogue.data?.filter((course) => !mappedIds.has(course.id)) ?? [];
  const coreCredits =
    version?.curriculum_courses
      ?.filter((item) => item.course_type === 'CORE')
      .reduce((sum, item) => sum + (item.course?.credits ?? 0), 0) ?? 0;
  const electiveMinimum =
    version?.elective_groups?.reduce((sum, group) => sum + group.minimum_credits, 0) ?? 0;
  const nextStep = version?.review_steps
    ?.filter((step) => step.status === 'PENDING')
    .sort((left, right) => left.sequence - right.sequence)[0];

  const grid = useMemo(() => {
    const groups = new Map<string, CurriculumCourse[]>();
    for (const item of version?.curriculum_courses ?? []) {
      const key = `Year ${item.year_level} · Semester ${item.semester ?? 1}`;
      groups.set(key, [...(groups.get(key) ?? []), item]);
    }
    return [...groups.entries()];
  }, [version]);

  async function refresh() {
    await Promise.all([programmes.refetch(), versions.refetch(), catalogue.refetch()]);
  }

  async function perform(action: () => Promise<unknown>, success: string, form?: HTMLFormElement) {
    setSaving(true);
    setError(null);
    setNotice(null);
    try {
      await action();
      form?.reset();
      setNotice(success);
      await refresh();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setSaving(false);
    }
  }

  function submitted(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    return { form: event.currentTarget, data: new FormData(event.currentTarget) };
  }

  async function createProgramme(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    setSaving(true);
    setError(null);
    setNotice(null);
    try {
      const created = await api.createProgramme({
        department_id: String(data.get('department_id')),
        code: String(data.get('code')).toUpperCase(),
        name: String(data.get('name')),
        award_level: String(data.get('award_level')) as 'BACHELORS',
        duration_years: Number(data.get('duration_years')),
        total_credits_required: Number(data.get('total_credits_required')),
        minimum_residency_credits: Number(data.get('minimum_residency_credits')),
        qualification_framework_code: String(data.get('qualification_framework_code')) || undefined,
        accreditation_body: String(data.get('accreditation_body')) || undefined,
        accreditation_reference: String(data.get('accreditation_reference')) || undefined,
        accreditation_expires_on: String(data.get('accreditation_expires_on')) || undefined,
      });
      form.reset();
      setShowProgrammeForm(false);
      setSelectedProgrammeId(created.id);
      setNotice(`${created.code} created in the programme registry.`);
      await programmes.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setSaving(false);
    }
  }

  async function createVersion(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    if (!selectedProgramme) return;
    setSaving(true);
    setError(null);
    setNotice(null);
    try {
      const created = await api.createCurriculumVersion({
        programme_id: selectedProgramme.id,
        effective_year_id: String(data.get('effective_year_id')),
        version_code: String(data.get('version_code')),
        graduation_credits_required: Number(data.get('graduation_credits_required')),
        minimum_elective_credits: Number(data.get('minimum_elective_credits') || 0),
      });
      form.reset();
      setSelectedVersionId(created.id);
      setNotice(`${created.version_code} created as an editable draft.`);
      await versions.refetch();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setSaving(false);
    }
  }

  async function addCourse(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    if (!version) return;
    await perform(
      () =>
        api.addCurriculumCourse(version.id, {
          course_id: String(data.get('course_id')),
          year_level: Number(data.get('year_level')),
          semester: Number(data.get('semester')),
          course_type: courseType,
          elective_group_id:
            courseType === 'ELECTIVE' ? String(data.get('elective_group_id')) : null,
        }),
      'Course added to the semester grid.',
      form,
    );
  }

  async function addElectiveGroup(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    if (!version) return;
    await perform(
      () =>
        api.createCurriculumElectiveGroup(version.id, {
          code: String(data.get('code')).toUpperCase(),
          name: String(data.get('name')),
          minimum_courses: Number(data.get('minimum_courses')),
          minimum_credits: Number(data.get('minimum_credits')),
        }),
      'Elective cluster and minimum selection rule created.',
      form,
    );
  }

  async function addRequirement(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    if (!version) return;
    await perform(
      () =>
        api.addCurriculumRequirement(version.id, {
          course_id: String(data.get('course_id')),
          required_course_id: String(data.get('required_course_id')),
          requirement_type: String(data.get('requirement_type')) as 'PREREQUISITE',
        }),
      'Course dependency added to the validated graph.',
      form,
    );
  }

  const isLoading =
    programmes.isLoading || departments.isLoading || years.isLoading || catalogue.isLoading;

  return (
    <div className="space-y-8" data-testid="curriculum-engine">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div className="max-w-3xl">
          <div className="text-mema-teal-700 mb-2 flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase">
            <GraduationCap className="h-4 w-4" />
            MOD-01-03 · Academic governance
          </div>
          <h2 className="font-heading text-2xl font-bold text-slate-950 sm:text-3xl">
            Programme & curriculum engine
          </h2>
          <p className="mt-2 text-sm leading-6 text-slate-600">
            Build semester curricula, validate dependency graphs, record the HOD-to-Senate review
            chain, lock approved versions and assign them to student cohorts.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" onClick={() => void refresh()} isLoading={isLoading}>
            <RefreshCw className="mr-2 h-4 w-4" />
            Refresh
          </Button>
          {canManage && (
            <Button onClick={() => setShowProgrammeForm((value) => !value)}>
              <Plus className="mr-2 h-4 w-4" />
              New programme
            </Button>
          )}
        </div>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertTitle>Operation failed</AlertTitle>
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}
      {notice && (
        <Alert variant="success">
          <AlertTitle>Curriculum updated</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}

      {showProgrammeForm && canManage && (
        <Card data-testid="new-programme-form">
          <CardHeader>
            <CardTitle>Create programme registry record</CardTitle>
            <CardDescription>
              Accreditation and national qualification references remain visible throughout the
              programme lifecycle.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={createProgramme} className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <Field label="Department">
                <select
                  name="department_id"
                  aria-label="Programme department"
                  className={fieldClass}
                  required
                >
                  <option value="">Select department</option>
                  {departments.data?.data.map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.code} · {item.name}
                    </option>
                  ))}
                </select>
              </Field>
              <Field label="Programme code">
                <Input name="code" aria-label="Programme code" placeholder="BSC-SE" required />
              </Field>
              <Field label="Programme name">
                <Input
                  name="name"
                  aria-label="Programme name"
                  placeholder="Bachelor of Science in Software Engineering"
                  required
                />
              </Field>
              <Field label="Award">
                <select name="award_level" aria-label="Award level" className={fieldClass}>
                  <option>BACHELORS</option>
                  <option>MASTERS</option>
                  <option>DOCTORATE</option>
                  <option>DIPLOMA</option>
                  <option>CERTIFICATE</option>
                </select>
              </Field>
              <Field label="Duration (years)">
                <Input
                  name="duration_years"
                  aria-label="Programme duration"
                  type="number"
                  min="1"
                  max="10"
                  defaultValue="4"
                  required
                />
              </Field>
              <Field label="Graduation credits">
                <Input
                  name="total_credits_required"
                  aria-label="Programme graduation credits"
                  type="number"
                  min="1"
                  defaultValue="120"
                  required
                />
              </Field>
              <Field label="Residency credits">
                <Input
                  name="minimum_residency_credits"
                  aria-label="Minimum residency credits"
                  type="number"
                  min="0"
                  defaultValue="30"
                  required
                />
              </Field>
              <Field label="Qualification framework">
                <Input
                  name="qualification_framework_code"
                  aria-label="Qualification framework code"
                  placeholder="KNQF-7"
                />
              </Field>
              <Field label="Accreditation body">
                <Input
                  name="accreditation_body"
                  aria-label="Accreditation body"
                  placeholder="CUE"
                />
              </Field>
              <Field label="Accreditation reference">
                <Input
                  name="accreditation_reference"
                  aria-label="Accreditation reference"
                  placeholder="CUE/PROG/2026/014"
                />
              </Field>
              <Field label="Accreditation expiry">
                <Input
                  name="accreditation_expires_on"
                  aria-label="Accreditation expiry"
                  type="date"
                />
              </Field>
              <div className="flex items-end">
                <Button type="submit" className="w-full" isLoading={saving}>
                  Create programme
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      <div className="grid gap-5 xl:grid-cols-[340px_minmax(0,1fr)]">
        <Card className="h-fit">
          <CardHeader>
            <CardTitle>Programme registry</CardTitle>
            <CardDescription>
              {programmes.data?.length ?? 0} active and historical records
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-2">
            {isLoading && (
              <p className="py-8 text-center text-sm text-slate-500">Loading academic registry…</p>
            )}
            {programmes.data?.map((item) => (
              <ProgrammeButton
                key={item.id}
                programme={item}
                active={item.id === selectedProgrammeId}
                onSelect={() => setSelectedProgrammeId(item.id)}
              />
            ))}
          </CardContent>
        </Card>

        <div className="min-w-0 space-y-5">
          {!selectedProgramme ? (
            <Card>
              <CardContent className="py-16 text-center text-sm text-slate-500">
                Select a programme to manage its curricula.
              </CardContent>
            </Card>
          ) : (
            <>
              <Card>
                <CardHeader>
                  <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <CardTitle>
                        {selectedProgramme.code} ·{' '}
                        {selectedProgramme.name ?? selectedProgramme.title}
                      </CardTitle>
                      <CardDescription>
                        {selectedProgramme.department?.name ?? 'Unassigned department'} ·{' '}
                        {selectedProgramme.award_level} · {selectedProgramme.duration_years} years
                      </CardDescription>
                    </div>
                    <Badge
                      variant={selectedProgramme.accreditation_warning ? 'warning' : 'success'}
                      dot
                    >
                      {selectedProgramme.accreditation_warning
                        ? 'Accreditation renewal due'
                        : 'Accreditation current'}
                    </Badge>
                  </div>
                </CardHeader>
                <CardContent className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                  <Metric
                    label="Required credits"
                    value={selectedProgramme.total_credits_required ?? 0}
                  />
                  <Metric
                    label="Residency minimum"
                    value={selectedProgramme.minimum_residency_credits ?? 0}
                  />
                  <Metric
                    label="Framework"
                    value={selectedProgramme.qualification_framework_code ?? 'Not recorded'}
                  />
                  <Metric
                    label="Accreditation"
                    value={selectedProgramme.accreditation_body ?? 'Not recorded'}
                  />
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Curriculum versions</CardTitle>
                  <CardDescription>
                    Approved versions are immutable. Revisions are always created as a new draft.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex flex-wrap gap-2">
                    {versions.data?.map((item) => (
                      <Button
                        key={item.id}
                        variant={item.id === selectedVersionId ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setSelectedVersionId(item.id)}
                      >
                        {item.version_code}
                        <Badge className="ml-2" variant={statusVariant(item.status)}>
                          {item.status ?? (item.is_approved ? 'APPROVED' : 'DRAFT')}
                        </Badge>
                      </Button>
                    ))}
                  </div>
                  {canManage && (
                    <form
                      onSubmit={createVersion}
                      className="grid gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 md:grid-cols-4"
                    >
                      <select
                        name="effective_year_id"
                        aria-label="Effective academic year"
                        className={fieldClass}
                        required
                      >
                        <option value="">Effective year</option>
                        {years.data?.data.map((item) => (
                          <option key={item.id} value={item.id}>
                            {item.code}
                          </option>
                        ))}
                      </select>
                      <Input
                        name="version_code"
                        aria-label="Curriculum version code"
                        placeholder="2027-V1"
                        required
                      />
                      <Input
                        name="graduation_credits_required"
                        aria-label="Version graduation credits"
                        type="number"
                        min="1"
                        defaultValue={selectedProgramme.total_credits_required ?? 120}
                        required
                      />
                      <div className="flex gap-2">
                        <Input
                          name="minimum_elective_credits"
                          aria-label="Minimum elective credits"
                          type="number"
                          min="0"
                          defaultValue="0"
                        />
                        <Button type="submit" isLoading={saving}>
                          Create draft
                        </Button>
                      </div>
                    </form>
                  )}
                </CardContent>
              </Card>

              {version && (
                <>
                  <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <MetricCard
                      icon={BookOpen}
                      label="Mapped courses"
                      value={version.curriculum_courses?.length ?? 0}
                    />
                    <MetricCard icon={Layers3} label="Core credits" value={coreCredits} />
                    <MetricCard icon={GitBranch} label="Elective minimum" value={electiveMinimum} />
                    <MetricCard
                      icon={ShieldCheck}
                      label="Graduation target"
                      value={version.graduation_credits_required ?? 0}
                    />
                  </div>
                  {locked && (
                    <Alert>
                      <LockKeyhole className="h-4 w-4" />
                      <AlertTitle>Approved version locked</AlertTitle>
                      <AlertDescription>
                        Structure hash:{' '}
                        <code className="text-xs break-all">{version.structure_hash}</code>. Create
                        a new version for any revision.
                      </AlertDescription>
                    </Alert>
                  )}

                  <Card data-testid="curriculum-grid">
                    <CardHeader>
                      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                          <CardTitle>Semester curriculum grid</CardTitle>
                          <CardDescription>
                            Core, elective and required-audit courses grouped by delivery year and
                            semester.
                          </CardDescription>
                        </div>
                        <div className="flex gap-2">
                          <Button asChild size="sm" variant="outline">
                            <a href={api.getCurriculumReportUrl(version.id, 'pdf')}>
                              <Download className="mr-2 h-4 w-4" />
                              PDF
                            </a>
                          </Button>
                          <Button asChild size="sm" variant="outline">
                            <a href={api.getCurriculumReportUrl(version.id, 'csv')}>
                              <Download className="mr-2 h-4 w-4" />
                              CSV
                            </a>
                          </Button>
                        </div>
                      </div>
                    </CardHeader>
                    <CardContent className="space-y-5">
                      {canManage && !locked && (
                        <form
                          onSubmit={addCourse}
                          className="bg-mema-teal-50/70 grid gap-3 rounded-xl p-4 md:grid-cols-5"
                        >
                          <select
                            name="course_id"
                            aria-label="Curriculum course"
                            className={fieldClass}
                            required
                          >
                            <option value="">Select catalogue course</option>
                            {availableCourses.map((course) => (
                              <option key={course.id} value={course.id}>
                                {course.code} · {course.title} ({course.credits})
                              </option>
                            ))}
                          </select>
                          <Input
                            name="year_level"
                            aria-label="Course year level"
                            type="number"
                            min="1"
                            max="10"
                            defaultValue="1"
                            required
                          />
                          <Input
                            name="semester"
                            aria-label="Course semester"
                            type="number"
                            min="1"
                            max="4"
                            defaultValue="1"
                            required
                          />
                          <select
                            name="course_type"
                            aria-label="Curriculum course type"
                            className={fieldClass}
                            value={courseType}
                            onChange={(event) =>
                              setCourseType(event.target.value as typeof courseType)
                            }
                          >
                            <option>CORE</option>
                            <option>ELECTIVE</option>
                            <option>REQUIRED_AUDIT</option>
                          </select>
                          <div className="flex gap-2">
                            {courseType === 'ELECTIVE' && (
                              <select
                                name="elective_group_id"
                                aria-label="Elective group"
                                className={fieldClass}
                                required
                              >
                                <option value="">Cluster</option>
                                {version.elective_groups?.map((group) => (
                                  <option key={group.id} value={group.id}>
                                    {group.code}
                                  </option>
                                ))}
                              </select>
                            )}
                            <Button type="submit" isLoading={saving}>
                              <Plus className="mr-1 h-4 w-4" />
                              Add
                            </Button>
                          </div>
                        </form>
                      )}
                      {grid.length === 0 ? (
                        <div className="rounded-xl border border-dashed p-10 text-center text-sm text-slate-500">
                          No courses mapped yet. Add the first catalogue course above.
                        </div>
                      ) : (
                        grid.map(([label, items]) => (
                          <div
                            key={label}
                            className="overflow-hidden rounded-xl border border-slate-200"
                          >
                            <div className="border-b bg-slate-50 px-4 py-2 text-sm font-bold text-slate-800">
                              {label}
                            </div>
                            <div className="overflow-x-auto">
                              <Table>
                                <TableHeader>
                                  <TableRow>
                                    <TableHead>Course</TableHead>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Credits</TableHead>
                                    <TableHead>Classification</TableHead>
                                    {canManage && !locked && (
                                      <TableHead className="text-right">Action</TableHead>
                                    )}
                                  </TableRow>
                                </TableHeader>
                                <TableBody>
                                  {items.map((item) => (
                                    <TableRow key={item.id}>
                                      <TableCell className="text-mema-teal-900 font-mono font-semibold">
                                        {item.course?.code}
                                      </TableCell>
                                      <TableCell>{item.course?.title}</TableCell>
                                      <TableCell>{item.course?.credits}</TableCell>
                                      <TableCell>
                                        <Badge variant="outline">{item.course_type}</Badge>
                                        {item.elective_group && (
                                          <span className="ml-2 text-xs text-slate-500">
                                            {item.elective_group.code}
                                          </span>
                                        )}
                                      </TableCell>
                                      {canManage && !locked && (
                                        <TableCell className="text-right">
                                          <Button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            aria-label={`Remove ${item.course?.code}`}
                                            onClick={() =>
                                              void perform(
                                                () =>
                                                  api.deleteCurriculumCourse(version.id, item.id),
                                                'Course removed from the draft grid.',
                                              )
                                            }
                                          >
                                            <Trash2 className="h-4 w-4 text-rose-600" />
                                          </Button>
                                        </TableCell>
                                      )}
                                    </TableRow>
                                  ))}
                                </TableBody>
                              </Table>
                            </div>
                          </div>
                        ))
                      )}
                    </CardContent>
                  </Card>

                  <div className="grid gap-5 lg:grid-cols-2">
                    <Card>
                      <CardHeader>
                        <CardTitle>Elective clusters</CardTitle>
                        <CardDescription>
                          Define how many courses and credits a student must select from each
                          cluster.
                        </CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        {version.elective_groups?.map((group) => (
                          <div
                            key={group.id}
                            className="flex items-center justify-between rounded-xl border p-3"
                          >
                            <div>
                              <div className="font-semibold text-slate-900">
                                {group.code} · {group.name}
                              </div>
                              <div className="text-xs text-slate-500">
                                Choose ≥ {group.minimum_courses} course(s) and ≥{' '}
                                {group.minimum_credits} credits
                              </div>
                            </div>
                            {canManage && !locked && (
                              <Button
                                size="sm"
                                variant="ghost"
                                aria-label={`Remove elective cluster ${group.code}`}
                                onClick={() =>
                                  void perform(
                                    () => api.deleteCurriculumElectiveGroup(version.id, group.id),
                                    'Elective cluster removed.',
                                  )
                                }
                              >
                                <Trash2 className="h-4 w-4 text-rose-600" />
                              </Button>
                            )}
                          </div>
                        ))}
                        {canManage && !locked && (
                          <form onSubmit={addElectiveGroup} className="grid gap-3 sm:grid-cols-2">
                            <Input
                              name="code"
                              aria-label="Elective cluster code"
                              placeholder="TECH"
                              required
                            />
                            <Input
                              name="name"
                              aria-label="Elective cluster name"
                              placeholder="Technical electives"
                              required
                            />
                            <Input
                              name="minimum_courses"
                              aria-label="Minimum elective courses"
                              type="number"
                              min="1"
                              defaultValue="1"
                              required
                            />
                            <Input
                              name="minimum_credits"
                              aria-label="Minimum elective credits"
                              type="number"
                              min="1"
                              defaultValue="3"
                              required
                            />
                            <Button type="submit" className="sm:col-span-2" isLoading={saving}>
                              Add cluster
                            </Button>
                          </form>
                        )}
                      </CardContent>
                    </Card>
                    <Card>
                      <CardHeader>
                        <CardTitle>Dependency graph</CardTitle>
                        <CardDescription>
                          Prerequisite cycles are rejected before persistence; co- and
                          anti-requisites remain explicit.
                        </CardDescription>
                      </CardHeader>
                      <CardContent className="space-y-4">
                        {version.requirements?.map((requirement) => (
                          <div
                            key={requirement.id}
                            className="flex items-center gap-2 rounded-xl border p-3 text-sm"
                          >
                            <span className="font-mono font-bold">{requirement.course?.code}</span>
                            <ArrowRight className="text-mema-teal-700 h-4 w-4" />
                            <Badge variant="outline">{requirement.requirement_type}</Badge>
                            <ArrowRight className="text-mema-teal-700 h-4 w-4" />
                            <span className="font-mono font-bold">
                              {requirement.prerequisite_course?.code}
                            </span>
                            {canManage && !locked && (
                              <Button
                                className="ml-auto"
                                size="sm"
                                variant="ghost"
                                aria-label="Remove course requirement"
                                onClick={() =>
                                  void perform(
                                    () =>
                                      api.deleteCurriculumRequirement(version.id, requirement.id),
                                    'Course dependency removed.',
                                  )
                                }
                              >
                                <Trash2 className="h-4 w-4 text-rose-600" />
                              </Button>
                            )}
                          </div>
                        ))}
                        {canManage && !locked && (
                          <form onSubmit={addRequirement} className="grid gap-3 sm:grid-cols-2">
                            <select
                              name="course_id"
                              aria-label="Dependent course"
                              className={fieldClass}
                              required
                            >
                              <option value="">Dependent course</option>
                              {version.curriculum_courses?.map((item) => (
                                <option key={item.id} value={item.course_id}>
                                  {item.course?.code}
                                </option>
                              ))}
                            </select>
                            <select
                              name="required_course_id"
                              aria-label="Required course"
                              className={fieldClass}
                              required
                            >
                              <option value="">Required course</option>
                              {version.curriculum_courses?.map((item) => (
                                <option key={item.id} value={item.course_id}>
                                  {item.course?.code}
                                </option>
                              ))}
                            </select>
                            <select
                              name="requirement_type"
                              aria-label="Requirement type"
                              className={fieldClass}
                            >
                              <option>PREREQUISITE</option>
                              <option>COREQUISITE</option>
                              <option>ANTIREQUISITE</option>
                            </select>
                            <Button type="submit" className="sm:col-span-2" isLoading={saving}>
                              Validate & add edge
                            </Button>
                          </form>
                        )}
                      </CardContent>
                    </Card>
                  </div>

                  <Card data-testid="curriculum-workflow">
                    <CardHeader>
                      <CardTitle>Academic approval chain</CardTitle>
                      <CardDescription>
                        Every approval records its reviewer, reference and timestamp. Senate
                        approval writes the cryptographic ledger and locks the version.
                      </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-5">
                      <div className="grid gap-3 md:grid-cols-4">
                        {stages.map((stage, index) => {
                          const step = version.review_steps?.find((item) => item.stage === stage);
                          const approved = step?.status === 'APPROVED';
                          return (
                            <div
                              key={stage}
                              className={`rounded-xl border p-4 ${approved ? 'border-emerald-200 bg-emerald-50' : nextStep?.stage === stage ? 'border-amber-300 bg-amber-50' : 'border-slate-200'}`}
                            >
                              <div className="flex items-center gap-2">
                                {approved ? (
                                  <CheckCircle2 className="h-5 w-5 text-emerald-600" />
                                ) : (
                                  <span className="flex h-5 w-5 items-center justify-center rounded-full bg-slate-200 text-xs font-bold">
                                    {index + 1}
                                  </span>
                                )}
                                <span className="text-sm font-bold">
                                  {stage.replaceAll('_', ' ')}
                                </span>
                              </div>
                              <div className="mt-2 text-xs text-slate-500">
                                {step?.reference ??
                                  (nextStep?.stage === stage ? 'Awaiting decision' : 'Pending')}
                              </div>
                            </div>
                          );
                        })}
                      </div>
                      {canManage && version.status === 'DRAFT' && (
                        <Button
                          onClick={() =>
                            void perform(
                              () => api.submitCurriculumVersion(version.id),
                              'Curriculum submitted for HOD review.',
                            )
                          }
                          isLoading={saving}
                        >
                          <ShieldCheck className="mr-2 h-4 w-4" />
                          Submit for review
                        </Button>
                      )}
                      {canApprove && version.status === 'UNDER_REVIEW' && nextStep && (
                        <form
                          onSubmit={(event) => {
                            const { form, data } = submitted(event);
                            void perform(
                              () =>
                                api.approveCurriculumVersion(version.id, {
                                  stage: nextStep.stage,
                                  reference: String(data.get('reference')),
                                  comments: String(data.get('comments')) || undefined,
                                }),
                              `${nextStep.stage.replaceAll('_', ' ')} approval recorded.`,
                              form,
                            );
                          }}
                          className="grid gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 md:grid-cols-[1fr_2fr_auto]"
                        >
                          <Input
                            name="reference"
                            aria-label={`${nextStep.stage} approval reference`}
                            placeholder={`${nextStep.stage}/2026/001`}
                            required
                          />
                          <Input
                            name="comments"
                            aria-label="Approval comments"
                            placeholder="Review comments (optional)"
                          />
                          <Button type="submit" isLoading={saving}>
                            Approve {nextStep.stage.replaceAll('_', ' ')}
                          </Button>
                        </form>
                      )}
                      {version.status === 'APPROVED' && (
                        <div className="grid gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 md:grid-cols-[1fr_auto]">
                          <div>
                            <div className="font-semibold text-emerald-950">
                              Senate resolution {version.senate_approval_ref}
                            </div>
                            <div className="text-xs text-emerald-800">
                              Approved{' '}
                              {version.approved_at
                                ? new Date(version.approved_at).toLocaleString()
                                : ''}
                            </div>
                          </div>
                          {canManage && (
                            <form
                              onSubmit={(event) => {
                                const { form, data } = submitted(event);
                                void perform(
                                  () =>
                                    api.assignCurriculumCohort(
                                      version.id,
                                      String(data.get('admission_year_id')),
                                    ),
                                  'Cohort assignment completed.',
                                  form,
                                );
                              }}
                              className="flex gap-2"
                            >
                              <select
                                name="admission_year_id"
                                aria-label="Admission cohort year"
                                className={fieldClass}
                                required
                              >
                                <option value="">Admission cohort</option>
                                {years.data?.data.map((item) => (
                                  <option key={item.id} value={item.id}>
                                    {item.code}
                                  </option>
                                ))}
                              </select>
                              <Button type="submit" isLoading={saving}>
                                Assign cohort
                              </Button>
                            </form>
                          )}
                        </div>
                      )}
                    </CardContent>
                  </Card>
                </>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}

function Field({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <label className={labelClass}>
      <span>{label}</span>
      {children}
    </label>
  );
}
function ProgrammeButton({
  programme,
  active,
  onSelect,
}: {
  programme: Programme;
  active: boolean;
  onSelect: () => void;
}) {
  return (
    <button
      type="button"
      onClick={onSelect}
      className={`w-full rounded-xl border p-4 text-left transition ${active ? 'border-mema-teal-700 bg-mema-teal-50 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'}`}
    >
      <div className="flex items-start justify-between gap-3">
        <div>
          <div className="text-mema-teal-800 font-mono text-xs font-bold">{programme.code}</div>
          <div className="mt-1 text-sm font-semibold text-slate-950">
            {programme.name ?? programme.title}
          </div>
        </div>
        {programme.accreditation_warning && (
          <TriangleAlert className="h-4 w-4 shrink-0 text-amber-600" />
        )}
      </div>
      <div className="mt-2 text-xs text-slate-500">
        {programme.award_level} · {programme.duration_years} years
      </div>
    </button>
  );
}
function Metric({ label, value }: { label: string; value: React.ReactNode }) {
  return (
    <div className="rounded-xl bg-slate-50 p-4">
      <div className="text-xs font-medium tracking-wide text-slate-500 uppercase">{label}</div>
      <div className="mt-1 font-semibold text-slate-950">{value}</div>
    </div>
  );
}
function MetricCard({
  icon: Icon,
  label,
  value,
}: {
  icon: React.ComponentType<{ className?: string }>;
  label: string;
  value: React.ReactNode;
}) {
  return (
    <Card>
      <CardContent className="flex items-center gap-3 p-5">
        <div className="bg-mema-teal-50 rounded-xl p-2">
          <Icon className="text-mema-teal-800 h-5 w-5" />
        </div>
        <div>
          <div className="text-xl font-bold text-slate-950">{value}</div>
          <div className="text-xs text-slate-500">{label}</div>
        </div>
      </CardContent>
    </Card>
  );
}
