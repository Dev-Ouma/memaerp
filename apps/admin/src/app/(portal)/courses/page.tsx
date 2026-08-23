'use client';

import React, { FormEvent, useEffect, useMemo, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { api, ApiError } from '@mema/api-client';
import type { Course, CourseOffering } from '@mema/types';
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
  BookOpen,
  CheckCircle2,
  Download,
  Layers3,
  LockKeyhole,
  Plus,
  RefreshCw,
  Users,
  GraduationCap,
} from 'lucide-react';

const fieldClass =
  'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-mema-teal-700';
const labelClass = 'space-y-1 text-sm font-medium text-slate-700';
const stages = ['DEPARTMENT_BOARD', 'SCHOOL_BOARD'] as const;
const messageFrom = (reason: unknown) =>
  reason instanceof ApiError ? reason.message : 'The course operation failed.';

function statusVariant(status?: string) {
  if (status === 'ACTIVE' || status === 'OFFERED') return 'success' as const;
  if (status === 'UNDER_REVIEW') return 'warning' as const;
  if (status === 'CLOSED' || status === 'DISCONTINUED') return 'destructive' as const;
  return 'outline' as const;
}

export default function AdminCoursesPage() {
  const [selectedCourseId, setSelectedCourseId] = useState('');
  const [showCourseForm, setShowCourseForm] = useState(false);
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);

  const user = useQuery({ queryKey: ['auth', 'me'], queryFn: () => api.getCurrentUser() });
  const dashboard = useQuery({ queryKey: ['courses', 'dashboard'], queryFn: () => api.getCourseDashboard() });
  const courses = useQuery({ queryKey: ['courses', 'catalogue'], queryFn: () => api.getCourses() });
  const offerings = useQuery({ queryKey: ['courses', 'offerings'], queryFn: () => api.getOfferings() });
  const departments = useQuery({
    queryKey: ['institution', 'departments'],
    queryFn: () => api.getInstitutionDepartments(),
  });
  const campuses = useQuery({
    queryKey: ['institution', 'campuses'],
    queryFn: () => api.getInstitutionCampuses(),
  });
  const years = useQuery({
    queryKey: ['institution', 'academic-years'],
    queryFn: () => api.getInstitutionAcademicYears(),
  });
  const staff = useQuery({ queryKey: ['courses', 'lecturers'], queryFn: () => api.getTeachingStaff() });

  useEffect(() => {
    if (!selectedCourseId && courses.data?.[0]) setSelectedCourseId(courses.data[0].id);
  }, [courses.data, selectedCourseId]);

  const selectedCourse = courses.data?.find((item) => item.id === selectedCourseId);
  const canManage = user.data?.permissions.includes('course.catalogue.manage') ?? false;
  const canApprove = user.data?.permissions.includes('course.catalogue.approve') ?? false;
  const canOffer = user.data?.permissions.includes('course.offering.manage') ?? false;
  const canAssign = user.data?.permissions.includes('course.offering.assign-lecturer') ?? false;
  const terms = useMemo(
    () => years.data?.data.flatMap((year) => year.terms.map((term) => ({ ...term, yearCode: year.code }))) ?? [],
    [years.data],
  );
  const courseOfferings = offerings.data?.filter((item) => item.course_id === selectedCourseId) ?? [];
  const nextStep = selectedCourse?.reviews
    ?.filter((step) => step.status === 'PENDING')
    .sort((left, right) => left.sequence - right.sequence)[0];

  async function refresh() {
    await Promise.all([dashboard.refetch(), courses.refetch(), offerings.refetch()]);
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

  async function createCourse(event: FormEvent<HTMLFormElement>) {
    const { form, data } = submitted(event);
    await perform(
      async () => {
        const created = await api.createCourse({
          department_id: String(data.get('department_id')),
          code: String(data.get('code')).toUpperCase(),
          title: String(data.get('title')),
          credits: Number(data.get('credits')),
          lecture_hours: Number(data.get('lecture_hours') || 3),
          lab_hours: Number(data.get('lab_hours') || 0),
          tutorial_hours: Number(data.get('tutorial_hours') || 0),
          learning_outcomes: String(data.get('learning_outcomes') || '') || undefined,
          syllabus_outline: String(data.get('syllabus_outline') || '') || undefined,
        });
        setSelectedCourseId(created.id);
        setShowCourseForm(false);
      },
      'Course drafted for departmental review.',
      form,
    );
  }

  const isLoading = courses.isLoading || offerings.isLoading || departments.isLoading;

  return (
    <div className="space-y-8" data-testid="course-catalogue">
      <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div className="max-w-3xl">
          <div className="text-mema-teal-700 mb-2 flex items-center gap-2 text-xs font-semibold tracking-[0.18em] uppercase">
            <BookOpen className="h-4 w-4" />
            MOD-01-04 · Academic delivery
          </div>
          <h2 className="font-heading text-2xl font-bold text-slate-950 sm:text-3xl">
            Course catalogue & semester offerings
          </h2>
          <p className="mt-2 text-sm leading-6 text-slate-600">
            Maintain the master catalogue, map prerequisites, record department and school board
            approval, open campus sections, and allocate teaching staff.
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" asChild>
            <a href={api.getCourseCatalogueReportUrl('pdf')}>
              <Download className="mr-2 h-4 w-4" />
              Catalogue PDF
            </a>
          </Button>
          <Button variant="outline" asChild>
            <a href={api.getCourseOfferingReportUrl('csv')}>
              <Download className="mr-2 h-4 w-4" />
              Sections CSV
            </a>
          </Button>
          <Button variant="outline" onClick={() => void refresh()} isLoading={isLoading}>
            <RefreshCw className="mr-2 h-4 w-4" />
            Refresh
          </Button>
          {canManage && (
            <Button onClick={() => setShowCourseForm((value) => !value)}>
              <Plus className="mr-2 h-4 w-4" />
              New course
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
          <AlertTitle>Catalogue updated</AlertTitle>
          <AlertDescription>{notice}</AlertDescription>
        </Alert>
      )}

      <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <MetricCard icon={BookOpen} label="Active courses" value={dashboard.data?.active_courses ?? 0} />
        <MetricCard icon={Layers3} label="Open sections" value={dashboard.data?.open_sections ?? 0} />
        <MetricCard
          icon={Users}
          label="Capacity used"
          value={`${dashboard.data?.capacity_saturation_percent ?? 0}%`}
        />
        <MetricCard
          icon={GraduationCap}
          label="Teaching load"
          value={dashboard.data?.lecturer_workload_hours ?? 0}
        />
      </div>

      {showCourseForm && canManage && (
        <Card data-testid="new-course-form">
          <CardHeader>
            <CardTitle>Draft a master course</CardTitle>
            <CardDescription>
              New courses stay in draft until the department board and school board approve them.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={createCourse} className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
              <Field label="Department">
                <select name="department_id" aria-label="Course department" className={fieldClass} required>
                  <option value="">Select department</option>
                  {departments.data?.data.map((item) => (
                    <option key={item.id} value={item.id}>
                      {item.code} · {item.name}
                    </option>
                  ))}
                </select>
              </Field>
              <Field label="Course code">
                <Input name="code" aria-label="Course code" placeholder="CSC 310" required />
              </Field>
              <Field label="Course title">
                <Input name="title" aria-label="Course title" placeholder="Operating Systems" required />
              </Field>
              <Field label="Credits">
                <Input name="credits" aria-label="Course credits" type="number" min="1" max="12" defaultValue="3" required />
              </Field>
              <Field label="Lecture hours">
                <Input name="lecture_hours" aria-label="Lecture hours" type="number" min="0" defaultValue="3" />
              </Field>
              <Field label="Laboratory hours">
                <Input name="lab_hours" aria-label="Laboratory hours" type="number" min="0" defaultValue="0" />
              </Field>
              <Field label="Tutorial hours">
                <Input name="tutorial_hours" aria-label="Tutorial hours" type="number" min="0" defaultValue="0" />
              </Field>
              <Field label="Learning outcomes">
                <Input name="learning_outcomes" aria-label="Learning outcomes" placeholder="Students can…" />
              </Field>
              <div className="md:col-span-2 xl:col-span-3">
                <Field label="Syllabus outline">
                  <Input name="syllabus_outline" aria-label="Syllabus outline" placeholder="Weekly topics and assessment" />
                </Field>
              </div>
              <div className="flex items-end">
                <Button type="submit" className="w-full" isLoading={saving}>
                  Create draft
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      )}

      <div className="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
        <Card className="h-fit">
          <CardHeader>
            <CardTitle>Course registry</CardTitle>
            <CardDescription>{courses.data?.length ?? 0} catalogue records</CardDescription>
          </CardHeader>
          <CardContent className="space-y-2">
            {isLoading && <p className="py-8 text-center text-sm text-slate-500">Loading catalogue…</p>}
            {courses.data?.map((item) => (
              <button
                key={item.id}
                type="button"
                onClick={() => setSelectedCourseId(item.id)}
                className={`w-full rounded-xl border p-4 text-left transition ${item.id === selectedCourseId ? 'border-mema-teal-700 bg-mema-teal-50 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300'}`}
              >
                <div className="text-mema-teal-800 font-mono text-xs font-bold">{item.code}</div>
                <div className="mt-1 text-sm font-semibold text-slate-950">{item.title}</div>
                <div className="mt-2 flex items-center justify-between text-xs text-slate-500">
                  <span>{item.credits ?? item.credit_units} credits</span>
                  <Badge variant={statusVariant(item.status)}>{item.status ?? (item.is_active ? 'ACTIVE' : 'DRAFT')}</Badge>
                </div>
              </button>
            ))}
          </CardContent>
        </Card>

        <div className="min-w-0 space-y-5">
          {!selectedCourse ? (
            <Card>
              <CardContent className="py-16 text-center text-sm text-slate-500">
                Select a course to manage prerequisites, approval and offerings.
              </CardContent>
            </Card>
          ) : (
            <>
              <Card>
                <CardHeader>
                  <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                      <CardTitle>
                        {selectedCourse.code} · {selectedCourse.title}
                      </CardTitle>
                      <CardDescription>
                        {selectedCourse.department?.name ?? 'Unassigned department'} ·{' '}
                        {selectedCourse.credits ?? selectedCourse.credit_units} credits
                      </CardDescription>
                    </div>
                    <div className="flex gap-2">
                      <Badge variant={statusVariant(selectedCourse.status)} dot>
                        {selectedCourse.status ?? 'ACTIVE'}
                      </Badge>
                      <Button asChild size="sm" variant="outline">
                        <a href={api.getCourseSyllabusUrl(selectedCourse.id)}>Syllabus PDF</a>
                      </Button>
                    </div>
                  </div>
                </CardHeader>
              </Card>

              <ApprovalCard
                course={selectedCourse}
                canManage={canManage}
                canApprove={canApprove}
                nextStep={nextStep}
                saving={saving}
                onSubmit={() =>
                  void perform(() => api.submitCourse(selectedCourse.id), 'Course submitted for department board review.')
                }
                onApprove={(event) => {
                  const { form, data } = submitted(event);
                  if (!nextStep) return;
                  void perform(
                    () =>
                      api.approveCourse(selectedCourse.id, {
                        stage: nextStep.stage,
                        reference: String(data.get('reference')),
                        comments: String(data.get('comments')) || undefined,
                      }),
                    `${nextStep.stage.replaceAll('_', ' ')} approval recorded.`,
                    form,
                  );
                }}
              />

              <Card>
                <CardHeader>
                  <CardTitle>Prerequisite graph</CardTitle>
                  <CardDescription>Catalogue-level dependencies used by registration and curriculum mapping.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  {selectedCourse.prerequisites?.map((requirement) => (
                    <div key={requirement.id} className="flex items-center justify-between rounded-xl border p-3 text-sm">
                      <span>
                        <span className="font-mono font-bold">{selectedCourse.code}</span>
                        <span className="mx-2 text-slate-400">{requirement.requirement_type}</span>
                        <span className="font-mono font-bold">{requirement.prerequisite_course?.code}</span>
                      </span>
                      {canManage && selectedCourse.status !== 'DISCONTINUED' && (
                        <Button
                          size="sm"
                          variant="ghost"
                          aria-label="Remove course requirement"
                          onClick={() =>
                            void perform(
                              () => api.deleteCoursePrerequisite(selectedCourse.id, requirement.id),
                              'Course dependency removed.',
                            )
                          }
                        >
                          Remove
                        </Button>
                      )}
                    </div>
                  ))}
                  {canManage && (
                    <form
                      onSubmit={(event) => {
                        const { form, data } = submitted(event);
                        void perform(
                          () =>
                            api.addCoursePrerequisite(selectedCourse.id, {
                              required_course_id: String(data.get('required_course_id')),
                              requirement_type: String(data.get('requirement_type')) as 'PREREQUISITE',
                            }),
                          'Course dependency added to the validated graph.',
                          form,
                        );
                      }}
                      className="grid gap-3 sm:grid-cols-2"
                    >
                      <select name="required_course_id" aria-label="Required course" className={fieldClass} required>
                        <option value="">Required course</option>
                        {courses.data
                          ?.filter((item) => item.id !== selectedCourse.id)
                          .map((item) => (
                            <option key={item.id} value={item.id}>
                              {item.code}
                            </option>
                          ))}
                      </select>
                      <select name="requirement_type" aria-label="Requirement type" className={fieldClass}>
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

              <Card data-testid="course-offerings">
                <CardHeader>
                  <CardTitle>Semester sections</CardTitle>
                  <CardDescription>
                    Capacity gates, campus copies and lecturer allocations for the selected course.
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-5">
                  {canOffer && selectedCourse.status === 'ACTIVE' && (
                    <form
                      onSubmit={(event) => {
                        const { form, data } = submitted(event);
                        void perform(
                          () =>
                            api.createCourseOffering({
                              course_id: selectedCourse.id,
                              term_id: String(data.get('term_id')),
                              campus_id: String(data.get('campus_id')),
                              section_code: String(data.get('section_code')).toUpperCase(),
                              max_capacity: Number(data.get('max_capacity')),
                              delivery_mode: String(data.get('delivery_mode')) as 'IN_PERSON',
                              workload_credits: Number(data.get('workload_credits') || 0),
                            }),
                          'Semester offering opened.',
                          form,
                        );
                      }}
                      className="bg-mema-teal-50/70 grid gap-3 rounded-xl p-4 md:grid-cols-3 xl:grid-cols-6"
                    >
                      <select name="term_id" aria-label="Offering term" className={fieldClass} required>
                        <option value="">Term</option>
                        {terms.map((term) => (
                          <option key={term.id} value={term.id}>
                            {term.code}
                          </option>
                        ))}
                      </select>
                      <select name="campus_id" aria-label="Offering campus" className={fieldClass} required>
                        <option value="">Campus</option>
                        {campuses.data?.data.map((campus) => (
                          <option key={campus.id} value={campus.id}>
                            {campus.code}
                          </option>
                        ))}
                      </select>
                      <Input name="section_code" aria-label="Section code" placeholder="A" required />
                      <Input name="max_capacity" aria-label="Section capacity" type="number" min="1" defaultValue="60" required />
                      <select name="delivery_mode" aria-label="Delivery mode" className={fieldClass}>
                        <option>IN_PERSON</option>
                        <option>ONLINE</option>
                        <option>HYBRID</option>
                      </select>
                      <Button type="submit" isLoading={saving}>
                        Open section
                      </Button>
                    </form>
                  )}
                  <div className="overflow-x-auto">
                    <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Section</TableHead>
                          <TableHead>Campus</TableHead>
                          <TableHead>Term</TableHead>
                          <TableHead>Enrolled / Cap</TableHead>
                          <TableHead>Lecturer</TableHead>
                          <TableHead>Status</TableHead>
                          <TableHead />
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        {courseOfferings.map((offering) => (
                          <OfferingRow
                            key={offering.id}
                            offering={offering}
                            staff={staff.data ?? []}
                            canAssign={canAssign}
                            canOffer={canOffer}
                            saving={saving}
                            onAssign={(event) => {
                              const { form, data } = submitted(event);
                              void perform(
                                () =>
                                  api.assignOfferingLecturer(offering.id, {
                                    lecturer_id: String(data.get('lecturer_id')),
                                    role: 'PRIMARY',
                                    workload_credits: Number(data.get('workload_credits') || 0),
                                  }),
                                'Lecturer allocated to the section.',
                                form,
                              );
                            }}
                            onClose={() =>
                              void perform(() => api.closeCourseOffering(offering.id), 'Offering closed for enrollment.')
                            }
                          />
                        ))}
                      </TableBody>
                    </Table>
                  </div>
                </CardContent>
              </Card>
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

function ApprovalCard({
  course,
  canManage,
  canApprove,
  nextStep,
  saving,
  onSubmit,
  onApprove,
}: {
  course: Course;
  canManage: boolean;
  canApprove: boolean;
  nextStep?: { stage: (typeof stages)[number] };
  saving: boolean;
  onSubmit: () => void;
  onApprove: (event: FormEvent<HTMLFormElement>) => void;
}) {
  return (
    <Card data-testid="course-workflow">
      <CardHeader>
        <CardTitle>Catalogue approval chain</CardTitle>
        <CardDescription>Department board, then school board. School approval publishes the course.</CardDescription>
      </CardHeader>
      <CardContent className="space-y-5">
        <div className="grid gap-3 md:grid-cols-2">
          {stages.map((stage, index) => {
            const step = course.reviews?.find((item) => item.stage === stage);
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
                  <span className="text-sm font-bold">{stage.replaceAll('_', ' ')}</span>
                </div>
                <div className="mt-2 text-xs text-slate-500">{step?.reference ?? 'Pending'}</div>
              </div>
            );
          })}
        </div>
        {canManage && course.status === 'DRAFT' && (
          <Button onClick={onSubmit} isLoading={saving}>
            Submit for review
          </Button>
        )}
        {canApprove && course.status === 'UNDER_REVIEW' && nextStep && (
          <form onSubmit={onApprove} className="grid gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 md:grid-cols-[1fr_2fr_auto]">
            <Input name="reference" aria-label={`${nextStep.stage} approval reference`} required />
            <Input name="comments" aria-label="Approval comments" placeholder="Optional comments" />
            <Button type="submit" isLoading={saving}>
              Approve {nextStep.stage.replaceAll('_', ' ')}
            </Button>
          </form>
        )}
        {course.status === 'ACTIVE' && (
          <Alert>
            <LockKeyhole className="h-4 w-4" />
            <AlertTitle>Published catalogue course</AlertTitle>
            <AlertDescription>
              School board {course.school_board_ref ?? 'resolution'} locked the official record.
            </AlertDescription>
          </Alert>
        )}
      </CardContent>
    </Card>
  );
}

function OfferingRow({
  offering,
  staff,
  canAssign,
  canOffer,
  saving,
  onAssign,
  onClose,
}: {
  offering: CourseOffering;
  staff: Array<{ id: string; email: string; person?: { given_name?: string; family_name?: string } }>;
  canAssign: boolean;
  canOffer: boolean;
  saving: boolean;
  onAssign: (event: FormEvent<HTMLFormElement>) => void;
  onClose: () => void;
}) {
  const capacity = offering.capacity || offering.max_capacity || 0;
  const percent = capacity > 0 ? Math.round((offering.enrolled_count / capacity) * 100) : 0;
  return (
    <TableRow>
      <TableCell className="font-mono font-bold">{offering.section_code}</TableCell>
      <TableCell>{offering.campus?.code ?? '—'}</TableCell>
      <TableCell>{offering.term?.code ?? '—'}</TableCell>
      <TableCell>
        {offering.enrolled_count} / {capacity}{' '}
        <Badge variant={percent >= 90 ? 'destructive' : percent >= 75 ? 'warning' : 'success'}>{percent}%</Badge>
      </TableCell>
      <TableCell className="text-xs">{offering.lecturer?.email ?? 'Unassigned'}</TableCell>
      <TableCell>
        <Badge variant={statusVariant(offering.status)}>{offering.status ?? 'OFFERED'}</Badge>
      </TableCell>
      <TableCell className="min-w-[260px]">
        {canAssign && offering.status !== 'CLOSED' && (
          <form onSubmit={onAssign} className="flex gap-2">
            <select name="lecturer_id" aria-label={`Lecturer for section ${offering.section_code}`} className={fieldClass} required>
              <option value="">Lecturer</option>
              {staff.map((person) => (
                <option key={person.id} value={person.id}>
                  {person.person?.given_name ? `${person.person.given_name} ${person.person.family_name}` : person.email}
                </option>
              ))}
            </select>
            <Input name="workload_credits" aria-label="Workload credits" type="number" min="0" defaultValue="3" className="w-20" />
            <Button type="submit" size="sm" isLoading={saving}>
              Assign
            </Button>
          </form>
        )}
        {canOffer && offering.status === 'OFFERED' && (
          <Button className="mt-2" size="sm" variant="outline" onClick={onClose}>
            Close enrollment
          </Button>
        )}
      </TableCell>
    </TableRow>
  );
}
