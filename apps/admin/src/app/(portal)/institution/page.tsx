'use client';

import React, { FormEvent, useCallback, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  api,
  ApiError,
  type InstitutionAcademicYear,
  type InstitutionRecordStatus,
} from '@mema/api-client';
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
import { Archive, Building2, CalendarDays, Database, Download, Plus, RefreshCw } from 'lucide-react';

const fieldClass = 'h-10 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-mema-teal-700';
const labelClass = 'space-y-1 text-sm font-medium text-slate-700';
const messageFrom = (reason: unknown) => reason instanceof ApiError ? reason.message : 'The institutional master-data operation failed.';

export default function InstitutionMasterDataPage() {
  const [saving, setSaving] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [lookupType, setLookupType] = useState('NATIONALITY');
  const [termStudyMode, setTermStudyMode] = useState('FULL_TIME');

  const overview = useQuery({ queryKey: ['institution', 'overview'], queryFn: () => api.getInstitutionOverview() });
  const campuses = useQuery({ queryKey: ['institution', 'campuses'], queryFn: () => api.getInstitutionCampuses() });
  const faculties = useQuery({ queryKey: ['institution', 'faculties'], queryFn: () => api.getInstitutionFaculties() });
  const departments = useQuery({ queryKey: ['institution', 'departments'], queryFn: () => api.getInstitutionDepartments() });
  const units = useQuery({ queryKey: ['institution', 'units'], queryFn: () => api.getInstitutionUnits() });
  const years = useQuery({ queryKey: ['institution', 'academic-years'], queryFn: () => api.getInstitutionAcademicYears() });
  const studyModes = useQuery({ queryKey: ['institution', 'study-modes'], queryFn: () => api.getInstitutionStudyModes() });
  const intakes = useQuery({ queryKey: ['institution', 'intakes'], queryFn: () => api.getInstitutionIntakes() });
  const events = useQuery({ queryKey: ['institution', 'calendar-events'], queryFn: () => api.getInstitutionCalendarEvents() });
  const lookups = useQuery({ queryKey: ['institution', 'lookups', lookupType], queryFn: () => api.getInstitutionLookups(lookupType) });

  const refresh = useCallback(async () => {
    await Promise.all([
      overview.refetch(), campuses.refetch(), faculties.refetch(), departments.refetch(), units.refetch(), years.refetch(),
      studyModes.refetch(), intakes.refetch(), events.refetch(), lookups.refetch(),
    ]);
  }, [campuses, departments, events, faculties, intakes, lookups, overview, studyModes, units, years]);

  async function perform(action: () => Promise<unknown>, success: string, form?: HTMLFormElement) {
    setSaving(true); setError(null); setNotice(null);
    try {
      await action(); form?.reset(); setNotice(success); await refresh();
    } catch (reason) {
      setError(messageFrom(reason));
    } finally {
      setSaving(false);
    }
  }

  function formData(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    return { form: event.currentTarget, data: new FormData(event.currentTarget) };
  }

  async function createCampus(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    const status = String(data.get('status')) as InstitutionRecordStatus;
    await perform(() => api.createInstitutionCampus({
      code: String(data.get('code')), name: String(data.get('name')), town: String(data.get('town')),
      status, ...(status === 'ACTIVE' ? { resolution_reference: String(data.get('resolution_reference')) } : {}),
    }), 'Campus created and added to the institutional hierarchy.', form);
  }

  async function createFaculty(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    const status = String(data.get('status')) as InstitutionRecordStatus;
    await perform(() => api.createInstitutionFaculty({
      campus_id: String(data.get('campus_id')), code: String(data.get('code')), name: String(data.get('name')),
      status, ...(status === 'ACTIVE' ? { resolution_reference: String(data.get('resolution_reference')) } : {}),
    }), 'Faculty created and linked to its campus.', form);
  }

  async function createDepartment(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    const status = String(data.get('status')) as InstitutionRecordStatus;
    await perform(() => api.createInstitutionDepartment({
      faculty_id: String(data.get('faculty_id')), code: String(data.get('code')), name: String(data.get('name')),
      cost_centre: String(data.get('cost_centre')), status,
      ...(status === 'ACTIVE' ? { resolution_reference: String(data.get('resolution_reference')) } : {}),
    }), 'Department created and linked to its faculty.', form);
  }

  async function createUnit(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    const status = String(data.get('status')) as InstitutionRecordStatus;
    await perform(() => api.createInstitutionUnit({
      department_id: String(data.get('department_id')), code: String(data.get('code')),
      name: String(data.get('name')), type: String(data.get('type')), status,
      ...(status === 'ACTIVE' ? { resolution_reference: String(data.get('resolution_reference')) } : {}),
    }), 'Unit created and linked to its department.', form);
  }

  async function createYear(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    await perform(() => api.createInstitutionAcademicYear({
      code: String(data.get('code')), name: String(data.get('name')),
      starts_on: String(data.get('starts_on')), ends_on: String(data.get('ends_on')),
    }), 'Academic year created in draft.', form);
  }

  async function createTerm(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    await perform(() => api.createInstitutionTerm({
      academic_year_id: String(data.get('academic_year_id')), study_mode_code: String(data.get('study_mode_code')),
      code: String(data.get('code')), name: String(data.get('name')), sequence: Number(data.get('sequence')),
      term_type: String(data.get('term_type')) as 'SEMESTER', starts_on: String(data.get('starts_on')),
      ends_on: String(data.get('ends_on')), registration_opens_at: String(data.get('registration_opens_at')) || undefined,
      registration_closes_at: String(data.get('registration_closes_at')) || undefined,
    }), 'Academic term created in draft.', form);
  }

  async function createStudyMode(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    await perform(() => api.createInstitutionStudyMode({
      code: String(data.get('code')), name: String(data.get('name')),
      description: String(data.get('description')) || undefined,
    }), 'Study mode created.', form);
  }

  async function createIntake(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    await perform(() => api.createInstitutionIntake({
      academic_year_id: String(data.get('academic_year_id')), code: String(data.get('code')),
      name: String(data.get('name')), opens_on: String(data.get('opens_on')),
      closes_on: String(data.get('closes_on')), reporting_on: String(data.get('reporting_on')) || undefined,
      status: 'ACTIVE',
    }), 'Intake window created.', form);
  }

  async function createEvent(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    const eventType = String(data.get('event_type')) as 'HOLIDAY' | 'REGISTRATION' | 'EXAM' | 'DEADLINE' | 'LECTURE' | 'CEREMONY' | 'OTHER';
    await perform(() => api.createInstitutionCalendarEvent({
      academic_year_id: String(data.get('academic_year_id')) || null,
      event_type: eventType, title: String(data.get('title')), starts_at: String(data.get('starts_at')),
      is_critical: data.get('is_critical') === 'on', is_holiday: eventType === 'HOLIDAY',
    }), 'Calendar event published.', form);
  }

  async function activateYear(year: InstitutionAcademicYear, form: HTMLFormElement) {
    const data = new FormData(form);
    await perform(() => api.activateInstitutionAcademicYear(year.id, String(data.get('resolution'))), `${year.code} activated and published.`);
  }

  async function createLookup(event: FormEvent<HTMLFormElement>) {
    const { form, data } = formData(event);
    await perform(() => api.createInstitutionLookup(lookupType, { code: String(data.get('code')), name: String(data.get('name')) }), 'Master lookup value created and cache invalidated.', form);
  }

  const isLoading = [overview, campuses, faculties, departments, units, years, studyModes, intakes, events].some((query) => query.isLoading);
  const current = overview.data?.current_academic_year;

  return (
    <div className="space-y-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 className="font-heading text-2xl font-bold text-slate-900">Institution & Master Data</h2>
          <p className="mt-1 text-sm text-slate-500">Govern campuses, faculties, departments, academic calendars and universal lookup codes.</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button asChild variant="outline"><a href={api.getInstitutionReportUrl('directory', { format: 'pdf' })}><Download className="mr-2 h-4 w-4" />Directory PDF</a></Button>
          <Button asChild variant="outline"><a href={api.getInstitutionReportUrl('calendar')}><Download className="mr-2 h-4 w-4" />Calendar PDF</a></Button>
          <Button variant="outline" onClick={() => void refresh()} isLoading={isLoading}><RefreshCw className="mr-2 h-4 w-4" />Refresh</Button>
        </div>
      </div>

      {error && <Alert variant="destructive"><AlertTitle>Operation failed</AlertTitle><AlertDescription>{error}</AlertDescription></Alert>}
      {notice && <Alert variant="success"><AlertTitle>Operation completed</AlertTitle><AlertDescription>{notice}</AlertDescription></Alert>}

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        {[
          { label: 'Campuses', value: overview.data?.campuses ?? 0, Icon: Building2 },
          { label: 'Faculties', value: overview.data?.faculties ?? 0, Icon: Building2 },
          { label: 'Departments', value: overview.data?.departments ?? 0, Icon: Database },
          { label: 'Units', value: overview.data?.units ?? 0, Icon: Database },
          { label: 'Active intakes', value: overview.data?.intakes ?? 0, Icon: CalendarDays },
          { label: 'Current year', value: current?.code ?? 'Not active', Icon: CalendarDays },
        ].map(({ label, value, Icon }) => (
          <Card key={label}><CardContent className="flex items-center gap-3 p-5"><Icon className="h-6 w-6 text-mema-teal-700" /><div><div className="text-xl font-bold text-slate-900">{value}</div><div className="text-xs text-slate-500">{label}</div></div></CardContent></Card>
        ))}
      </div>

      <Card>
        <CardHeader><CardTitle>Organisational hierarchy</CardTitle><CardDescription>University → campuses → faculties/schools/centres → departments → units. Active nodes require an approval reference; historical nodes are archived.</CardDescription></CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-4 xl:grid-cols-3">
            <HierarchyForm title="Add campus" onSubmit={createCampus} saving={saving}>
              <Input name="code" aria-label="Campus code" placeholder="Code" required />
              <Input name="name" aria-label="Campus name" placeholder="Campus name" required />
              <Input name="town" aria-label="Campus town" placeholder="Town" />
            </HierarchyForm>
            <HierarchyForm title="Add faculty" onSubmit={createFaculty} saving={saving}>
              <select name="campus_id" aria-label="Faculty campus" className={fieldClass} required><option value="">Select campus</option>{campuses.data?.data.map((item) => <option key={item.id} value={item.id}>{item.code} · {item.name}</option>)}</select>
              <Input name="code" aria-label="Faculty code" placeholder="Code" required />
              <Input name="name" aria-label="Faculty name" placeholder="Faculty or school name" required />
            </HierarchyForm>
            <HierarchyForm title="Add department" onSubmit={createDepartment} saving={saving}>
              <select name="faculty_id" aria-label="Department faculty" className={fieldClass} required><option value="">Select faculty</option>{faculties.data?.data.map((item) => <option key={item.id} value={item.id}>{item.code} · {item.name}</option>)}</select>
              <Input name="code" aria-label="Department code" placeholder="Code" required />
              <Input name="name" aria-label="Department name" placeholder="Department name" required />
              <Input name="cost_centre" aria-label="Cost centre" placeholder="Cost centre" />
            </HierarchyForm>
          </div>
          <div className="grid gap-4 xl:grid-cols-[360px_1fr]">
            <HierarchyForm title="Add unit" onSubmit={createUnit} saving={saving}>
              <select name="department_id" aria-label="Unit department" className={fieldClass} required><option value="">Select department</option>{departments.data?.data.map((item) => <option key={item.id} value={item.id}>{item.code} · {item.name}</option>)}</select>
              <Input name="code" aria-label="Unit code" placeholder="Code" required />
              <Input name="name" aria-label="Unit name" placeholder="Unit or centre name" required />
              <select name="type" aria-label="Unit type" className={fieldClass} defaultValue="UNIT"><option>UNIT</option><option>CENTRE</option><option>INSTITUTE</option><option>LAB</option></select>
            </HierarchyForm>
            <div className="overflow-x-auto"><Table><TableHeader><TableRow><TableHead>Campus</TableHead><TableHead>Faculty</TableHead><TableHead>Department / Unit</TableHead><TableHead>Cost centre</TableHead><TableHead>Status</TableHead><TableHead className="text-right">Action</TableHead></TableRow></TableHeader><TableBody>{departments.data?.data.map((department) => <TableRow key={department.id}><TableCell>{department.faculty?.campus?.name ?? '—'}</TableCell><TableCell>{department.faculty?.name ?? '—'}</TableCell><TableCell><div className="font-medium">{department.name}</div><div className="font-mono text-xs text-slate-500">{department.code}</div></TableCell><TableCell>{department.cost_centre ?? '—'}</TableCell><TableCell><Badge variant={department.is_active ? 'success' : 'outline'}>{department.status}</Badge></TableCell><TableCell className="text-right">{department.status !== 'ARCHIVED' && <Button size="sm" variant="ghost" aria-label={`Archive department ${department.code}`} onClick={() => void perform(() => api.updateInstitutionDepartment(department.id, { status: 'ARCHIVED' }), `${department.code} archived.`)}><Archive className="h-4 w-4" /></Button>}</TableCell></TableRow>)}{units.data?.data.map((unit) => <TableRow key={unit.id}><TableCell>{unit.department?.faculty?.campus?.name ?? '—'}</TableCell><TableCell>{unit.department?.faculty?.name ?? '—'}</TableCell><TableCell><div className="font-medium">↳ {unit.name}</div><div className="font-mono text-xs text-slate-500">{unit.code} · {unit.type}</div></TableCell><TableCell>—</TableCell><TableCell><Badge variant={unit.is_active ? 'success' : 'outline'}>{unit.status}</Badge></TableCell><TableCell className="text-right">{unit.status !== 'ARCHIVED' && <Button size="sm" variant="ghost" aria-label={`Archive unit ${unit.code}`} onClick={() => void perform(() => api.updateInstitutionUnit(unit.id, { status: 'ARCHIVED' }), `${unit.code} archived.`)}><Archive className="h-4 w-4" /></Button>}</TableCell></TableRow>)}</TableBody></Table></div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle>Academic calendar & term engine</CardTitle><CardDescription>Senate-approved years and non-overlapping study-mode terms drive registration, finance and examinations.</CardDescription></CardHeader>
        <CardContent className="space-y-6">
          <div className="grid gap-5 xl:grid-cols-2">
            <form onSubmit={createYear} className="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-2">
              <h3 className="font-semibold sm:col-span-2">Create academic year</h3>
              <Input name="code" aria-label="Academic year code" placeholder="2028/2029" required />
              <Input name="name" aria-label="Academic year name" placeholder="Academic Year 2028/2029" required />
              <label className={labelClass}>Academic year starts on<Input name="starts_on" type="date" required /></label>
              <label className={labelClass}>Academic year ends on<Input name="ends_on" type="date" required /></label>
              <Button type="submit" isLoading={saving} className="sm:col-span-2"><Plus className="mr-2 h-4 w-4" />Create draft year</Button>
            </form>
            <form onSubmit={createTerm} className="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-2">
              <h3 className="font-semibold sm:col-span-2">Configure term</h3>
              <select name="academic_year_id" aria-label="Term academic year" className={fieldClass} required><option value="">Select academic year</option>{years.data?.data.map((year) => <option key={year.id} value={year.id}>{year.code}</option>)}</select>
              <select name="study_mode_code" aria-label="Study mode" className={fieldClass} value={termStudyMode} onChange={(event) => setTermStudyMode(event.target.value)}>{studyModes.data?.data.filter((mode) => mode.is_active).map((mode) => <option key={mode.id} value={mode.code}>{mode.name}</option>)}</select>
              <Input name="code" aria-label="Term code" placeholder="2028/2029-S1" required />
              <Input name="name" aria-label="Term name" placeholder="Semester 1" required />
              <Input name="sequence" aria-label="Term sequence" type="number" min="1" max="6" defaultValue="1" required />
              <select name="term_type" aria-label="Term type" className={fieldClass} defaultValue="SEMESTER"><option>SEMESTER</option><option>TRIMESTER</option><option>TERM</option><option>SESSION</option></select>
              <label className={labelClass}>Term starts on<Input name="starts_on" type="date" required /></label>
              <label className={labelClass}>Term ends on<Input name="ends_on" type="date" required /></label>
              <label className={labelClass}>Registration opens<Input name="registration_opens_at" type="datetime-local" /></label>
              <label className={labelClass}>Registration closes<Input name="registration_closes_at" type="datetime-local" /></label>
              <Button type="submit" isLoading={saving} className="sm:col-span-2"><Plus className="mr-2 h-4 w-4" />Create draft term</Button>
            </form>
          </div>
          <div className="grid gap-5 xl:grid-cols-3">
            <form onSubmit={createStudyMode} className="space-y-3 rounded-xl border border-slate-200 p-4">
              <h3 className="font-semibold">Add study mode</h3>
              <Input name="code" aria-label="Study mode code" placeholder="BLOCK_RELEASE" required />
              <Input name="name" aria-label="Study mode name" placeholder="Block Release" required />
              <Input name="description" aria-label="Study mode description" placeholder="Optional description" />
              <Button type="submit" isLoading={saving} className="w-full"><Plus className="mr-2 h-4 w-4" />Add study mode</Button>
            </form>
            <form onSubmit={createIntake} className="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-2 xl:grid-cols-1">
              <h3 className="font-semibold">Configure intake</h3>
              <select name="academic_year_id" aria-label="Intake academic year" className={fieldClass} required><option value="">Select academic year</option>{years.data?.data.map((year) => <option key={year.id} value={year.id}>{year.code}</option>)}</select>
              <Input name="code" aria-label="Intake code" placeholder="JAN-2028" required />
              <Input name="name" aria-label="Intake name" placeholder="January 2028 Intake" required />
              <label className={labelClass}>Applications open<Input name="opens_on" type="date" required /></label>
              <label className={labelClass}>Applications close<Input name="closes_on" type="date" required /></label>
              <label className={labelClass}>Reporting date<Input name="reporting_on" type="date" /></label>
              <Button type="submit" isLoading={saving}><Plus className="mr-2 h-4 w-4" />Create intake</Button>
            </form>
            <form onSubmit={createEvent} className="space-y-3 rounded-xl border border-slate-200 p-4">
              <h3 className="font-semibold">Publish calendar event</h3>
              <select name="academic_year_id" aria-label="Event academic year" className={fieldClass}><option value="">University-wide</option>{years.data?.data.map((year) => <option key={year.id} value={year.id}>{year.code}</option>)}</select>
              <select name="event_type" aria-label="Calendar event type" className={fieldClass} defaultValue="DEADLINE"><option>DEADLINE</option><option>HOLIDAY</option><option>REGISTRATION</option><option>EXAM</option><option>LECTURE</option><option>CEREMONY</option><option>OTHER</option></select>
              <Input name="title" aria-label="Calendar event title" placeholder="Event or deadline" required />
              <label className={labelClass}>Starts at<Input name="starts_at" type="datetime-local" required /></label>
              <label className="flex items-center gap-2 text-sm text-slate-700"><input name="is_critical" type="checkbox" />Critical deadline</label>
              <Button type="submit" isLoading={saving} className="w-full"><Plus className="mr-2 h-4 w-4" />Publish event</Button>
            </form>
          </div>
          <div className="grid gap-4 lg:grid-cols-2">
            <section aria-label="Admission intakes" className="rounded-xl bg-slate-50 p-4"><h3 className="font-semibold">Admission intakes</h3><div className="mt-3 space-y-2">{intakes.data?.data.map((intake) => <div key={intake.id} className="flex items-center justify-between gap-3 text-sm"><span><strong>{intake.name}</strong><span className="block text-xs text-slate-500">{intake.opens_on} → {intake.closes_on}</span></span><Badge variant={intake.status === 'ACTIVE' ? 'success' : 'outline'}>{intake.status}</Badge></div>)}</div></section>
            <section aria-label="Calendar events" className="rounded-xl bg-slate-50 p-4"><h3 className="font-semibold">Events & deadlines</h3><div className="mt-3 space-y-2">{events.data?.data.map((item) => <div key={item.id} className="flex items-center justify-between gap-3 text-sm"><span><strong>{item.title}</strong><span className="block text-xs text-slate-500">{new Date(item.starts_at).toLocaleString()}</span></span><Badge variant={item.is_critical ? 'destructive' : 'outline'}>{item.event_type}</Badge></div>)}</div></section>
          </div>
          <div className="space-y-3">{years.data?.data.map((year) => <section aria-label={`Academic year ${year.code}`} key={year.id} className="rounded-xl border border-slate-200 p-4"><div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><div className="flex items-center gap-2"><span className="font-semibold">{year.name}</span><Badge variant={year.is_current ? 'success' : 'outline'}>{year.status}</Badge></div><p className="text-xs text-slate-500">{year.starts_on} → {year.ends_on}</p></div>{year.status !== 'ACTIVE' && <form onSubmit={(event) => { event.preventDefault(); void activateYear(year, event.currentTarget); }} className="flex gap-2"><Input name="resolution" aria-label={`Senate resolution for ${year.code}`} placeholder="Senate resolution" required /><Button type="submit" isLoading={saving} aria-label={`Activate academic year ${year.code}`}>Activate year</Button></form>}</div><div className="mt-3 flex flex-wrap gap-2">{year.terms.map((term) => <div key={term.id} className="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs"><span className="font-medium">{term.name} · {term.study_mode_code}</span><Badge variant={term.is_current ? 'success' : 'outline'}>{term.status}</Badge>{!term.is_current && year.status === 'ACTIVE' && <Button size="sm" variant="outline" aria-label={`Activate term ${term.code}`} onClick={() => void perform(() => api.activateInstitutionTerm(term.id), `${term.code} activated.`)} isLoading={saving}>Activate term</Button>}</div>)}</div></section>)}</div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle>Universal master lookups</CardTitle><CardDescription>Redis-cached reference codes used consistently across every ERP form and integration.</CardDescription></CardHeader>
        <CardContent className="space-y-4">
          <div className="flex flex-wrap justify-end gap-2"><Button asChild size="sm" variant="outline"><a href={api.getInstitutionReportUrl('directory', { format: 'csv' })}><Download className="mr-2 h-4 w-4" />Export CSV</a></Button><Button asChild size="sm" variant="outline"><a href={api.getInstitutionReportUrl('directory', { format: 'json' })}><Download className="mr-2 h-4 w-4" />Export JSON</a></Button></div>
          <div className="grid gap-3 sm:grid-cols-[220px_1fr]">
            <select aria-label="Lookup type" className={fieldClass} value={lookupType} onChange={(event) => setLookupType(event.target.value)}><option>NATIONALITY</option><option>COUNTY</option><option>BANK</option><option>CURRENCY</option><option>PAYMENT_METHOD</option><option>STUDENT_CATEGORY</option></select>
            <form onSubmit={createLookup} className="flex flex-col gap-2 sm:flex-row"><Input name="code" aria-label="Lookup code" placeholder="Code" required /><Input name="name" aria-label="Lookup name" placeholder="Display name" required /><Button type="submit" isLoading={saving}>Add value</Button></form>
          </div>
          <div className="flex flex-wrap gap-2">{lookups.data?.data.map((lookup) => <span key={lookup.id} className="inline-flex items-center gap-1 rounded-full border border-slate-200 pl-3 text-xs"><span>{lookup.code} · {lookup.name}</span><Button size="sm" variant="ghost" className="h-7 px-2" aria-label={`Archive lookup ${lookup.code}`} onClick={() => void perform(() => api.updateInstitutionLookup(lookupType, lookup.id, { is_active: false }), `${lookup.code} archived and cache refreshed.`)}><Archive className="h-3.5 w-3.5" /></Button></span>)}</div>
          {lookups.data && <p className="text-xs text-slate-500">Retrieved in {lookups.data.meta.elapsed_ms} ms.</p>}
        </CardContent>
      </Card>
    </div>
  );
}

function HierarchyForm({ title, onSubmit, saving, children }: { title: string; onSubmit: (event: FormEvent<HTMLFormElement>) => void; saving: boolean; children: React.ReactNode }) {
  return <form onSubmit={onSubmit} className="space-y-3 rounded-xl border border-slate-200 p-4"><h3 className="font-semibold">{title}</h3>{children}<select name="status" aria-label={`${title} status`} className={fieldClass} defaultValue="ACTIVE"><option>DRAFT</option><option>PENDING_APPROVAL</option><option>ACTIVE</option></select><Input name="resolution_reference" aria-label={`${title} approval reference`} placeholder="Council/Senate resolution (required for Active)" /><Button type="submit" isLoading={saving} className="w-full"><Plus className="mr-2 h-4 w-4" />{title}</Button></form>;
}
