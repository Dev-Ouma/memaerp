import axios, { AxiosError, AxiosInstance, AxiosRequestConfig } from 'axios';
import type {
  AdmissionProspect,
  AdmissionsDashboard,
  Application,
  ApplicationDocument,
  ApplicationPayment,
  AuthUserProfile,
  Campus,
  Course,
  CourseDashboard,
  CourseEnrollment,
  CourseOffering,
  CoursePrerequisite,
  CourseReview,
  CurriculumCourse,
  CurriculumVersion,
  ElectiveGroup,
  Invoice,
  Payment,
  Programme,
  MatriculationQueueItem,
  Student,
  StudentDashboard,
  StudentMark,
  TermGpa,
  TermRegistration,
} from '@mema/types';

export const API_BASE_URL =
  (typeof process !== 'undefined' && process.env?.NEXT_PUBLIC_API_URL) ||
  'http://localhost:8000/api/v1';

export function getApiRootUrl(baseUrl: string = API_BASE_URL): string {
  return baseUrl.replace(/\/api\/v1\/?$/, '');
}

export class ApiError extends Error {
  constructor(
    message: string,
    public status?: number,
    public code?: string,
    public fields?: Record<string, string[]>,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

export interface IamRoleSummary {
  id: string;
  code: string;
  name: string;
  family: string;
  is_system: boolean;
  is_mfa_mandatory: boolean;
  default_scope_type: 'institution' | 'campus' | 'faculty' | 'department' | 'self';
  permissions_count: number;
  assignments_count: number;
}

export interface IamUserSummary {
  id: string;
  email: string;
  username: string | null;
  name: string | null;
  status: 'PENDING' | 'ACTIVE' | 'LOCKED' | 'SUSPENDED' | 'DEACTIVATED';
  is_active: boolean;
  mfa_enabled: boolean;
  last_login_at: string | null;
  roles: Array<{
    id: string;
    code: string;
    name: string;
    scope_type: string;
    scope_id: string | null;
  }>;
}

export interface IamSession {
  id: string;
  device_name: string;
  ip_address: string;
  user_agent: string | null;
  mfa_verified: boolean;
  last_activity_at: string;
  idle_expires_at: string;
  absolute_expires_at: string;
  revoked_at: string | null;
  revoked_reason: string | null;
}

export type InstitutionRecordStatus = 'DRAFT' | 'PENDING_APPROVAL' | 'ACTIVE' | 'ARCHIVED';

export interface InstitutionCampus {
  id: string;
  code: string;
  name: string;
  town: string | null;
  is_main_campus: boolean;
  is_active: boolean;
  status: InstitutionRecordStatus;
  resolution_reference: string | null;
  faculties_count?: number;
}

export interface InstitutionFaculty {
  id: string;
  campus_id: string;
  code: string;
  name: string;
  is_active: boolean;
  status: InstitutionRecordStatus;
  resolution_reference: string | null;
  campus?: InstitutionCampus;
  departments_count?: number;
}

export interface InstitutionDepartment {
  id: string;
  faculty_id: string;
  code: string;
  name: string;
  cost_centre: string | null;
  is_active: boolean;
  status: InstitutionRecordStatus;
  resolution_reference: string | null;
  faculty?: InstitutionFaculty;
}

export interface InstitutionUnit {
  id: string;
  department_id: string;
  code: string;
  name: string;
  type: string;
  head_of_unit_id: string | null;
  is_active: boolean;
  status: InstitutionRecordStatus;
  resolution_reference: string | null;
  department?: InstitutionDepartment;
}

export interface InstitutionStudyMode {
  id: string;
  code: string;
  name: string;
  description: string | null;
  is_active: boolean;
}

export interface InstitutionIntake {
  id: string;
  academic_year_id: string;
  code: string;
  name: string;
  opens_on: string;
  closes_on: string;
  reporting_on: string | null;
  status: 'DRAFT' | 'ACTIVE' | 'ARCHIVED';
  academic_year?: InstitutionAcademicYear;
}

export interface InstitutionCalendarEvent {
  id: string;
  academic_year_id: string | null;
  term_id: string | null;
  event_type: 'HOLIDAY' | 'REGISTRATION' | 'EXAM' | 'DEADLINE' | 'LECTURE' | 'CEREMONY' | 'OTHER';
  title: string;
  description: string | null;
  starts_at: string;
  ends_at: string | null;
  is_critical: boolean;
  is_holiday: boolean;
}

export interface InstitutionTerm {
  id: string;
  academic_year_id: string;
  code: string;
  name: string;
  sequence: number;
  study_mode_code: string;
  term_type: 'SEMESTER' | 'TRIMESTER' | 'TERM' | 'SESSION';
  starts_on: string;
  ends_on: string;
  status: InstitutionRecordStatus;
  is_current: boolean;
  registration_opens_at: string | null;
  registration_closes_at: string | null;
}

export interface InstitutionAcademicYear {
  id: string;
  code: string;
  name: string;
  starts_on: string;
  ends_on: string;
  status: InstitutionRecordStatus;
  is_current: boolean;
  senate_resolution_reference: string | null;
  terms: InstitutionTerm[];
}

export interface InstitutionLookup {
  id: string;
  type: string;
  code: string;
  name: string;
  metadata: Record<string, unknown>;
  display_order: number;
  is_active: boolean;
}

export interface InstitutionOverview {
  campuses: number;
  faculties: number;
  departments: number;
  units: number;
  intakes: number;
  current_academic_year: InstitutionAcademicYear | null;
}

export interface PageMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

function normalizeProgramme(programme: Programme): Programme {
  return {
    ...programme,
    title: programme.title ?? programme.name ?? programme.code,
    credit_units_required: programme.credit_units_required ?? programme.total_credits_required ?? 0,
  };
}

function normalizeCourse(course: Course): Course {
  return {
    ...course,
    credit_units: course.credit_units ?? course.credits ?? 0,
    practical_hours: course.practical_hours ?? course.lab_hours ?? 0,
  };
}

function normalizeOffering(offering: CourseOffering): CourseOffering {
  return {
    ...offering,
    capacity: offering.capacity ?? offering.max_capacity ?? 0,
    max_capacity: offering.max_capacity ?? offering.capacity,
  };
}

function extractError(error: unknown): ApiError {
  if (error instanceof ApiError) {
    return error;
  }

  if (axios.isAxiosError(error)) {
    const axiosError = error as AxiosError<{
      message?: string;
      code?: string;
      errors?: Record<string, string[]>;
      error?: { message?: string; code?: string; fields?: Record<string, string[]> };
    }>;
    const data = axiosError.response?.data;
    const fields = data?.error?.fields ?? data?.errors;
    const message =
      (fields?.login?.[0] ?? fields?.email?.[0]) ||
      data?.error?.message ||
      data?.message ||
      axiosError.message ||
      'Request failed';

    return new ApiError(
      message,
      axiosError.response?.status,
      data?.error?.code ?? data?.code,
      fields,
    );
  }

  return new ApiError(error instanceof Error ? error.message : 'Request failed');
}

export class MemaApiClient {
  private client: AxiosInstance;

  constructor(baseURL: string = API_BASE_URL) {
    this.client = axios.create({
      baseURL,
      withCredentials: true,
      // First-party portals run on separate localhost ports/subdomains. Axios otherwise omits
      // the X-XSRF-TOKEN header for these credentialed cross-origin requests.
      withXSRFToken: true,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        const normalized = extractError(error);
        const publicAuthPath =
          typeof window !== 'undefined' &&
          ['/login', '/mfa', '/reset-password'].some(
            (path) =>
              window.location.pathname === path || window.location.pathname.startsWith(`${path}/`),
          );
        if (normalized.status === 401 && typeof window !== 'undefined' && !publicAuthPath) {
          window.location.assign('/login');
        }
        return Promise.reject(normalized);
      },
    );
  }

  async getCsrfCookie(): Promise<void> {
    const rootUrl = getApiRootUrl();
    await axios.get(`${rootUrl}/sanctum/csrf-cookie`, {
      withCredentials: true,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
  }

  async login(credentials: {
    login: string;
    password: string;
    remember?: boolean;
  }): Promise<{ mfa_required: boolean; challenge_token?: string }> {
    await this.getCsrfCookie().catch(() => undefined);
    const response = await this.client.post<{ mfa_required?: boolean; challenge_token?: string }>(
      '/auth/login',
      credentials,
    );
    return {
      mfa_required: Boolean(response.data.mfa_required),
      challenge_token: response.data.challenge_token,
    };
  }

  async verifyMfa(challengeToken: string, code: string): Promise<void> {
    await this.client.post('/auth/mfa/verify', { challenge_token: challengeToken, code });
  }

  async forgotPassword(email: string): Promise<{ message: string; debug_token?: string }> {
    const response = await this.client.post<{ message: string; debug_token?: string }>(
      '/auth/password/forgot',
      { email },
    );
    return response.data;
  }

  async resetPassword(payload: {
    email: string;
    token: string;
    password: string;
    password_confirmation: string;
  }): Promise<void> {
    await this.client.post('/auth/password/reset', payload);
  }

  async getInstitutionOverview(): Promise<InstitutionOverview> {
    const response = await this.client.get<{ data: InstitutionOverview }>('/institution/overview');
    return response.data.data;
  }

  async getInstitutionCampuses(params?: {
    search?: string;
    active?: boolean;
  }): Promise<{ data: InstitutionCampus[]; meta: PageMeta }> {
    const response = await this.client.get('/institution/campuses', { params });
    return response.data;
  }

  async createInstitutionCampus(payload: {
    code: string;
    name: string;
    town?: string;
    status: InstitutionRecordStatus;
    resolution_reference?: string;
  }): Promise<InstitutionCampus> {
    const response = await this.client.post<{ data: InstitutionCampus }>(
      '/institution/campuses',
      payload,
    );
    return response.data.data;
  }

  async updateInstitutionCampus(
    id: string,
    payload: Partial<
      Pick<
        InstitutionCampus,
        'name' | 'town' | 'status' | 'resolution_reference' | 'is_main_campus'
      >
    >,
  ): Promise<InstitutionCampus> {
    const response = await this.client.patch<{ data: InstitutionCampus }>(
      `/institution/campuses/${id}`,
      payload,
    );
    return response.data.data;
  }

  async getInstitutionFaculties(): Promise<{ data: InstitutionFaculty[]; meta: PageMeta }> {
    const response = await this.client.get('/institution/faculties');
    return response.data;
  }

  async createInstitutionFaculty(payload: {
    campus_id: string;
    code: string;
    name: string;
    status: InstitutionRecordStatus;
    resolution_reference?: string;
  }): Promise<InstitutionFaculty> {
    const response = await this.client.post<{ data: InstitutionFaculty }>(
      '/institution/faculties',
      payload,
    );
    return response.data.data;
  }

  async updateInstitutionFaculty(
    id: string,
    payload: Partial<
      Pick<InstitutionFaculty, 'name' | 'status' | 'resolution_reference' | 'campus_id'>
    >,
  ): Promise<InstitutionFaculty> {
    const response = await this.client.patch<{ data: InstitutionFaculty }>(
      `/institution/faculties/${id}`,
      payload,
    );
    return response.data.data;
  }

  async getInstitutionDepartments(): Promise<{ data: InstitutionDepartment[]; meta: PageMeta }> {
    const response = await this.client.get('/institution/departments');
    return response.data;
  }

  async createInstitutionDepartment(payload: {
    faculty_id: string;
    code: string;
    name: string;
    cost_centre?: string;
    status: InstitutionRecordStatus;
    resolution_reference?: string;
  }): Promise<InstitutionDepartment> {
    const response = await this.client.post<{ data: InstitutionDepartment }>(
      '/institution/departments',
      payload,
    );
    return response.data.data;
  }

  async updateInstitutionDepartment(
    id: string,
    payload: Partial<
      Pick<
        InstitutionDepartment,
        'name' | 'status' | 'resolution_reference' | 'faculty_id' | 'cost_centre'
      >
    >,
  ): Promise<InstitutionDepartment> {
    const response = await this.client.patch<{ data: InstitutionDepartment }>(
      `/institution/departments/${id}`,
      payload,
    );
    return response.data.data;
  }

  async getInstitutionUnits(): Promise<{ data: InstitutionUnit[]; meta: PageMeta }> {
    const response = await this.client.get('/institution/units');
    return response.data;
  }

  async createInstitutionUnit(payload: {
    department_id: string;
    code: string;
    name: string;
    type?: string;
    status: InstitutionRecordStatus;
    resolution_reference?: string;
  }): Promise<InstitutionUnit> {
    const response = await this.client.post<{ data: InstitutionUnit }>(
      '/institution/units',
      payload,
    );
    return response.data.data;
  }

  async updateInstitutionUnit(
    id: string,
    payload: Partial<
      Pick<InstitutionUnit, 'name' | 'status' | 'resolution_reference' | 'department_id' | 'type'>
    >,
  ): Promise<InstitutionUnit> {
    const response = await this.client.patch<{ data: InstitutionUnit }>(
      `/institution/units/${id}`,
      payload,
    );
    return response.data.data;
  }

  async getInstitutionAcademicYears(): Promise<{
    data: InstitutionAcademicYear[];
    meta: PageMeta;
  }> {
    const response = await this.client.get('/institution/academic-years');
    return response.data;
  }

  async createInstitutionAcademicYear(payload: {
    code: string;
    name: string;
    starts_on: string;
    ends_on: string;
  }): Promise<InstitutionAcademicYear> {
    const response = await this.client.post<{ data: InstitutionAcademicYear }>(
      '/institution/academic-years',
      payload,
    );
    return response.data.data;
  }

  async createInstitutionTerm(payload: {
    academic_year_id: string;
    study_mode_code: string;
    code: string;
    name: string;
    sequence: number;
    term_type: InstitutionTerm['term_type'];
    starts_on: string;
    ends_on: string;
    registration_opens_at?: string;
    registration_closes_at?: string;
  }): Promise<InstitutionTerm> {
    const response = await this.client.post<{ data: InstitutionTerm }>(
      '/institution/terms',
      payload,
    );
    return response.data.data;
  }

  async activateInstitutionAcademicYear(
    id: string,
    senateResolutionReference: string,
  ): Promise<InstitutionAcademicYear> {
    const response = await this.client.post<{ data: InstitutionAcademicYear }>(
      `/institution/academic-years/${id}/activate`,
      {
        senate_resolution_reference: senateResolutionReference,
      },
    );
    return response.data.data;
  }

  async activateInstitutionTerm(id: string): Promise<InstitutionTerm> {
    const response = await this.client.post<{ data: InstitutionTerm }>(
      `/institution/terms/${id}/activate`,
    );
    return response.data.data;
  }

  async getInstitutionStudyModes(): Promise<{ data: InstitutionStudyMode[] }> {
    const response = await this.client.get('/institution/study-modes');
    return response.data;
  }

  async createInstitutionStudyMode(payload: {
    code: string;
    name: string;
    description?: string;
  }): Promise<InstitutionStudyMode> {
    const response = await this.client.post<{ data: InstitutionStudyMode }>(
      '/institution/study-modes',
      payload,
    );
    return response.data.data;
  }

  async getInstitutionIntakes(): Promise<{ data: InstitutionIntake[]; meta: PageMeta }> {
    const response = await this.client.get('/institution/intakes');
    return response.data;
  }

  async createInstitutionIntake(
    payload: Omit<InstitutionIntake, 'id' | 'academic_year' | 'reporting_on'> & {
      reporting_on?: string;
    },
  ): Promise<InstitutionIntake> {
    const response = await this.client.post<{ data: InstitutionIntake }>(
      '/institution/intakes',
      payload,
    );
    return response.data.data;
  }

  async getInstitutionCalendarEvents(
    academicYearId?: string,
  ): Promise<{ data: InstitutionCalendarEvent[] }> {
    const response = await this.client.get('/institution/calendar-events', {
      params: { academic_year_id: academicYearId },
    });
    return response.data;
  }

  async createInstitutionCalendarEvent(
    payload: Omit<InstitutionCalendarEvent, 'id' | 'description' | 'ends_at' | 'term_id'> & {
      description?: string;
      ends_at?: string;
      term_id?: string;
    },
  ): Promise<InstitutionCalendarEvent> {
    const response = await this.client.post<{ data: InstitutionCalendarEvent }>(
      '/institution/calendar-events',
      payload,
    );
    return response.data.data;
  }

  async getInstitutionLookups(
    type: string,
  ): Promise<{ data: InstitutionLookup[]; meta: { elapsed_ms: number } }> {
    const response = await this.client.get(`/institution/lookups/${encodeURIComponent(type)}`);
    return response.data;
  }

  async createInstitutionLookup(
    type: string,
    payload: { code: string; name: string; display_order?: number },
  ): Promise<InstitutionLookup> {
    const response = await this.client.post<{ data: InstitutionLookup }>(
      `/institution/lookups/${encodeURIComponent(type)}`,
      payload,
    );
    return response.data.data;
  }

  async updateInstitutionLookup(
    type: string,
    id: string,
    payload: Partial<Pick<InstitutionLookup, 'name' | 'metadata' | 'display_order' | 'is_active'>>,
  ): Promise<InstitutionLookup> {
    const response = await this.client.patch<{ data: InstitutionLookup }>(
      `/institution/lookups/${encodeURIComponent(type)}/${id}`,
      payload,
    );
    return response.data.data;
  }

  getInstitutionReportUrl(
    report: 'directory' | 'calendar',
    params?: Record<string, string>,
  ): string {
    const query = params ? `?${new URLSearchParams(params).toString()}` : '';
    return `${API_BASE_URL}/institution/reports/${report}${query}`;
  }

  async getIamUsers(): Promise<{ data: IamUserSummary[]; meta: { total: number } }> {
    const response = await this.client.get('/iam/users');
    return response.data;
  }

  async getIamRoles(): Promise<{ data: IamRoleSummary[] }> {
    const response = await this.client.get('/iam/roles');
    return response.data;
  }

  async createIamUser(payload: {
    given_name: string;
    family_name: string;
    email: string;
    username: string;
    identity_type: 'APPLICANT' | 'STUDENT' | 'EMPLOYEE' | 'ALUMNI';
    identifier: string;
    password: string;
  }): Promise<{ message: string; data: { id: string } }> {
    const response = await this.client.post('/iam/users', payload);
    return response.data;
  }

  async updateIamUserStatus(
    userId: string,
    status: IamUserSummary['status'],
    reason: string,
  ): Promise<void> {
    await this.client.patch(`/iam/users/${userId}/status`, { status, reason });
  }

  async assignIamRole(
    userId: string,
    payload: {
      role_id: string;
      scope_type: 'institution' | 'campus' | 'faculty' | 'department' | 'self';
      scope_id?: string;
      starts_at?: string;
      ends_at?: string;
      reason: string;
    },
  ): Promise<void> {
    await this.client.post(`/iam/users/${userId}/roles`, payload);
  }

  async resetIamUserMfa(userId: string, reason: string): Promise<void> {
    await this.client.post(`/iam/users/${userId}/mfa-reset`, { reason });
  }

  async changePassword(payload: {
    current_password: string;
    password: string;
    password_confirmation: string;
  }): Promise<void> {
    await this.client.post('/auth/password/change', payload);
  }

  async logoutAll(): Promise<void> {
    await this.client.post('/auth/logout-all');
  }

  async setupMfa(): Promise<{ secret: string; provisioning_uri: string }> {
    const response = await this.client.post('/auth/mfa/setup');
    return response.data;
  }

  async confirmMfa(code: string): Promise<{ message: string; recovery_codes: string[] }> {
    const response = await this.client.post('/auth/mfa/confirm', { code });
    return response.data;
  }

  async disableMfa(password: string): Promise<void> {
    await this.client.delete('/auth/mfa', { data: { password } });
  }

  async getSessions(): Promise<{ data: IamSession[] }> {
    const response = await this.client.get('/auth/sessions');
    return response.data;
  }

  async revokeSession(sessionId: string): Promise<void> {
    await this.client.delete(`/auth/sessions/${sessionId}`);
  }

  async logout(): Promise<void> {
    await this.client.post('/auth/logout');
  }

  async getCurrentUser(): Promise<AuthUserProfile> {
    const response = await this.client.get<{ user: AuthUserProfile }>('/auth/me');
    return response.data.user;
  }

  async getProgrammes(): Promise<Programme[]> {
    const res = await this.client.get<{ data: Programme[] }>('/curriculum/programmes');
    return res.data.data.map(normalizeProgramme);
  }

  async getProgramme(id: string): Promise<Programme> {
    const res = await this.client.get<{ data: Programme }>(`/curriculum/programmes/${id}`);
    return normalizeProgramme(res.data.data);
  }

  async createProgramme(payload: {
    department_id: string;
    code: string;
    name: string;
    award_level: 'CERTIFICATE' | 'DIPLOMA' | 'BACHELORS' | 'MASTERS' | 'DOCTORATE';
    duration_years: number;
    total_credits_required: number;
    minimum_residency_credits?: number;
    qualification_framework_code?: string;
    accreditation_body?: string;
    accreditation_reference?: string;
    accreditation_expires_on?: string;
  }): Promise<Programme> {
    const response = await this.client.post<{ data: Programme }>('/curriculum/programmes', payload);
    return normalizeProgramme(response.data.data);
  }

  async updateProgramme(id: string, payload: Partial<Programme>): Promise<Programme> {
    const response = await this.client.patch<{ data: Programme }>(
      `/curriculum/programmes/${id}`,
      payload,
    );
    return normalizeProgramme(response.data.data);
  }

  async getCurriculumVersions(programmeId: string): Promise<CurriculumVersion[]> {
    const response = await this.client.get<{ data: CurriculumVersion[] }>(
      `/curriculum/programmes/${programmeId}/curricula`,
    );
    return response.data.data;
  }

  async createCurriculumVersion(payload: {
    programme_id: string;
    effective_year_id: string;
    version_code: string;
    graduation_credits_required: number;
    minimum_elective_credits?: number;
  }): Promise<CurriculumVersion> {
    const response = await this.client.post<{ data: CurriculumVersion }>(
      '/curriculum/versions',
      payload,
    );
    return response.data.data;
  }

  async getCurriculumCourses(): Promise<Course[]> {
    const response = await this.client.get<{ data: Course[] }>('/curriculum/courses');
    return response.data.data.map(normalizeCourse);
  }

  async createCurriculumElectiveGroup(
    versionId: string,
    payload: {
      code: string;
      name: string;
      minimum_courses: number;
      minimum_credits: number;
    },
  ): Promise<ElectiveGroup> {
    const response = await this.client.post<{ data: ElectiveGroup }>(
      `/curriculum/versions/${versionId}/elective-groups`,
      payload,
    );
    return response.data.data;
  }

  async updateCurriculumElectiveGroup(
    versionId: string,
    groupId: string,
    payload: Partial<ElectiveGroup>,
  ): Promise<ElectiveGroup> {
    const response = await this.client.patch<{ data: ElectiveGroup }>(
      `/curriculum/versions/${versionId}/elective-groups/${groupId}`,
      payload,
    );
    return response.data.data;
  }

  async deleteCurriculumElectiveGroup(versionId: string, groupId: string): Promise<void> {
    await this.client.delete(`/curriculum/versions/${versionId}/elective-groups/${groupId}`);
  }

  async addCurriculumCourse(
    versionId: string,
    payload: {
      course_id: string;
      year_level: number;
      semester: number;
      course_type: 'CORE' | 'ELECTIVE' | 'REQUIRED_AUDIT';
      elective_group_id?: string | null;
    },
  ): Promise<CurriculumCourse> {
    const response = await this.client.post<{ data: CurriculumCourse }>(
      `/curriculum/versions/${versionId}/courses`,
      payload,
    );
    return response.data.data;
  }

  async updateCurriculumCourse(
    versionId: string,
    itemId: string,
    payload: Partial<
      Pick<CurriculumCourse, 'year_level' | 'semester' | 'course_type' | 'elective_group_id'>
    >,
  ): Promise<CurriculumCourse> {
    const response = await this.client.patch<{ data: CurriculumCourse }>(
      `/curriculum/versions/${versionId}/courses/${itemId}`,
      payload,
    );
    return response.data.data;
  }

  async deleteCurriculumCourse(versionId: string, itemId: string): Promise<void> {
    await this.client.delete(`/curriculum/versions/${versionId}/courses/${itemId}`);
  }

  async addCurriculumRequirement(
    versionId: string,
    payload: {
      course_id: string;
      required_course_id: string;
      requirement_type: 'PREREQUISITE' | 'COREQUISITE' | 'ANTIREQUISITE';
    },
  ): Promise<CoursePrerequisite> {
    const response = await this.client.post<{ data: CoursePrerequisite }>(
      `/curriculum/versions/${versionId}/requirements`,
      payload,
    );
    return response.data.data;
  }

  async deleteCurriculumRequirement(versionId: string, requirementId: string): Promise<void> {
    await this.client.delete(`/curriculum/versions/${versionId}/requirements/${requirementId}`);
  }

  async submitCurriculumVersion(versionId: string): Promise<CurriculumVersion> {
    const response = await this.client.post<{ data: CurriculumVersion }>(
      `/curriculum/versions/${versionId}/submit`,
    );
    return response.data.data;
  }

  async approveCurriculumVersion(
    versionId: string,
    payload: {
      stage: 'HOD' | 'DEAN' | 'ACADEMIC_BOARD' | 'SENATE';
      reference: string;
      comments?: string;
    },
  ): Promise<CurriculumVersion> {
    const response = await this.client.post<{ data: CurriculumVersion }>(
      `/curriculum/versions/${versionId}/approve`,
      payload,
    );
    return response.data.data;
  }

  async assignCurriculumCohort(versionId: string, admissionYearId: string): Promise<number> {
    const response = await this.client.post<{ data: { assigned_count: number } }>(
      `/curriculum/versions/${versionId}/assign-cohort`,
      {
        admission_year_id: admissionYearId,
      },
    );
    return response.data.data.assigned_count;
  }

  getCurriculumReportUrl(versionId: string, format: 'pdf' | 'csv'): string {
    return `${API_BASE_URL}/curriculum/versions/${encodeURIComponent(versionId)}/report?format=${format}`;
  }

  async getAdmissionsCatalogue(): Promise<{
    institution: { id: string; name: string; code: string };
    programmes: Programme[];
    campuses: Campus[];
    intakes: InstitutionIntake[];
    study_modes: InstitutionStudyMode[];
    application_fee: { amount: number; currency: string };
  }> {
    const res = await this.client.get<{
      data: {
        institution: { id: string; name: string; code: string };
        programmes: Programme[];
        campuses: Campus[];
        intakes: InstitutionIntake[];
        study_modes: InstitutionStudyMode[];
        application_fee: { amount: number; currency: string };
      };
    }>('/admissions/catalogue');
    return res.data.data;
  }

  async registerApplicant(payload: {
    given_name: string;
    family_name: string;
    middle_name?: string;
    email: string;
    phone: string;
    national_id: string;
    gender?: string;
    nationality?: string;
    password: string;
  }): Promise<{ token: string; user: { id: string; email: string; person_id: string } }> {
    const res = await this.client.post<{
      data: { token: string; user: { id: string; email: string; person_id: string } };
    }>('/admissions/register', payload);
    return res.data.data;
  }

  async getAdmissionsDashboard(): Promise<AdmissionsDashboard> {
    const res = await this.client.get<{ data: AdmissionsDashboard }>('/admissions/dashboard');
    return res.data.data;
  }

  async getApplications(params?: {
    search?: string;
    status?: string;
    programme_id?: string;
    campus_id?: string;
    intake_id?: string;
    fee_paid?: boolean;
  }): Promise<Application[]> {
    const res = await this.client.get<{ data: Application[] }>('/admissions/applications', { params });
    return res.data.data;
  }

  async getApplication(id: string): Promise<Application> {
    const res = await this.client.get<{ data: Application }>(`/admissions/applications/${id}`);
    return res.data.data;
  }

  async createApplication(payload: {
    programme_id: string;
    campus_id: string;
    intake_id?: string;
    study_mode_id?: string;
    secondary_school_name: string;
    mean_grade: string;
    kcse_index_number: string;
    entry_path?: string;
    person_id?: string;
  }): Promise<Application> {
    const res = await this.client.post<{ data: Application }>('/admissions/applications', payload);
    return res.data.data;
  }

  async uploadApplicationDocument(
    id: string,
    file: File | Blob,
    documentType: string,
  ): Promise<ApplicationDocument> {
    const form = new FormData();
    form.append('document_type', documentType);
    form.append('file', file);
    const res = await this.client.post<{ data: ApplicationDocument }>(
      `/admissions/applications/${id}/documents`,
      form,
      { headers: { 'Content-Type': 'multipart/form-data' } },
    );
    return res.data.data;
  }

  async payApplicationFee(
    id: string,
    payload: { channel: 'MPESA' | 'CARD' | 'BANK'; phone: string; transaction_reference?: string },
  ): Promise<{ payment: ApplicationPayment; application: Application }> {
    const res = await this.client.post<{ data: { payment: ApplicationPayment; application: Application } }>(
      `/admissions/applications/${id}/pay`,
      payload,
    );
    return res.data.data;
  }

  async submitApplication(id: string): Promise<Application> {
    const res = await this.client.post<{ data: Application }>(`/admissions/applications/${id}/submit`);
    return res.data.data;
  }

  async beginApplicationReview(id: string): Promise<Application> {
    const res = await this.client.post<{ data: Application }>(`/admissions/applications/${id}/begin-review`);
    return res.data.data;
  }

  async verifyApplication(id: string, notes?: string): Promise<Application> {
    const res = await this.client.post<{ data: Application }>(`/admissions/applications/${id}/verify`, {
      notes,
    });
    return res.data.data;
  }

  async decideApplication(
    id: string,
    payload: { decision: 'ADMIT' | 'REJECT'; reference: string; notes?: string },
  ): Promise<Application> {
    const res = await this.client.post<{ data: Application }>(`/admissions/applications/${id}/decide`, payload);
    return res.data.data;
  }

  async acceptAdmissionOffer(id: string): Promise<Application> {
    const res = await this.client.post<{ data: Application }>(
      `/admissions/applications/${id}/accept-offer`,
    );
    return res.data.data;
  }

  getAdmissionOfferLetterUrl(id: string): string {
    return `${API_BASE_URL}/admissions/applications/${encodeURIComponent(id)}/offer-letter`;
  }

  getAdmissionsReportUrl(format: 'pdf' | 'csv'): string {
    return `${API_BASE_URL}/admissions/report?format=${format}`;
  }

  getApplicationFeeReportUrl(format: 'pdf' | 'csv'): string {
    return `${API_BASE_URL}/admissions/fee-report?format=${format}`;
  }

  async importKuccps(payload: {
    intake_id?: string;
    rows: Array<{
      kuccps_index: string;
      applicant_name: string;
      programme_code: string;
      mean_grade?: string;
      aggregate_points?: number;
    }>;
  }): Promise<{ imported: number; applications_created: number; batch: string }> {
    const res = await this.client.post<{
      data: { imported: number; applications_created: number; batch: string };
    }>('/admissions/kuccps/import', payload);
    return res.data.data;
  }

  async getAdmissionProspects(): Promise<AdmissionProspect[]> {
    const res = await this.client.get<{ data: AdmissionProspect[] }>('/admissions/prospects');
    return res.data.data;
  }

  async createAdmissionProspect(payload: {
    full_name: string;
    email: string;
    phone?: string;
    source?: string;
    campaign_code?: string;
    programme_interest_id?: string;
    notes?: string;
  }): Promise<AdmissionProspect> {
    const res = await this.client.post<{ data: AdmissionProspect }>('/admissions/prospects', payload);
    return res.data.data;
  }

  async getCourses(params?: {
    search?: string;
    department_id?: string;
    status?: string;
    active?: boolean;
  }): Promise<Course[]> {
    const res = await this.client.get<{ data: Course[] }>('/courses/', { params });
    return res.data.data.map(normalizeCourse);
  }

  async getCourse(id: string): Promise<Course> {
    const res = await this.client.get<{ data: Course }>(`/courses/${id}`);
    return normalizeCourse(res.data.data);
  }

  async createCourse(payload: {
    department_id: string;
    code: string;
    title: string;
    credits: number;
    lecture_hours?: number;
    lab_hours?: number;
    tutorial_hours?: number;
    description?: string;
    learning_outcomes?: string;
    syllabus_outline?: string;
  }): Promise<Course> {
    const response = await this.client.post<{ data: Course }>('/courses/', payload);
    return normalizeCourse(response.data.data);
  }

  async updateCourse(id: string, payload: Partial<Course>): Promise<Course> {
    const response = await this.client.patch<{ data: Course }>(`/courses/${id}`, payload);
    return normalizeCourse(response.data.data);
  }

  async submitCourse(id: string): Promise<Course> {
    const response = await this.client.post<{ data: Course }>(`/courses/${id}/submit`);
    return normalizeCourse(response.data.data);
  }

  async approveCourse(
    id: string,
    payload: {
      stage: CourseReview['stage'];
      reference: string;
      comments?: string;
    },
  ): Promise<Course> {
    const response = await this.client.post<{ data: Course }>(`/courses/${id}/approve`, payload);
    return normalizeCourse(response.data.data);
  }

  async addCoursePrerequisite(
    courseId: string,
    payload: {
      required_course_id: string;
      requirement_type: 'PREREQUISITE' | 'COREQUISITE' | 'ANTIREQUISITE';
    },
  ): Promise<CoursePrerequisite> {
    const response = await this.client.post<{ data: CoursePrerequisite }>(
      `/courses/${courseId}/prerequisites`,
      payload,
    );
    return response.data.data;
  }

  async deleteCoursePrerequisite(courseId: string, requirementId: string): Promise<void> {
    await this.client.delete(`/courses/${courseId}/prerequisites/${requirementId}`);
  }

  async getCourseDashboard(): Promise<CourseDashboard> {
    const response = await this.client.get<{ data: CourseDashboard }>('/courses/dashboard');
    return response.data.data;
  }

  async getTeachingStaff(): Promise<Array<{ id: string; email: string; person?: { given_name?: string; family_name?: string } }>> {
    const response = await this.client.get<{
      data: Array<{ id: string; email: string; person?: { given_name?: string; family_name?: string } }>;
    }>('/courses/lecturers');
    return response.data.data;
  }

  async getOfferings(params?: {
    term_id?: string;
    campus_id?: string;
    department_id?: string;
    active_only?: boolean;
  }): Promise<CourseOffering[]> {
    const res = await this.client.get<{ data: CourseOffering[] }>('/courses/offerings', {
      params,
    });
    return res.data.data.map(normalizeOffering);
  }

  async createCourseOffering(payload: {
    course_id: string;
    term_id: string;
    campus_id: string;
    section_code: string;
    max_capacity: number;
    delivery_mode?: 'IN_PERSON' | 'ONLINE' | 'HYBRID';
    workload_credits?: number;
  }): Promise<CourseOffering> {
    const response = await this.client.post<{ data: CourseOffering }>('/courses/offerings', payload);
    return normalizeOffering(response.data.data);
  }

  async assignOfferingLecturer(
    offeringId: string,
    payload: {
      lecturer_id: string;
      role?: 'PRIMARY' | 'ASSISTANT';
      workload_credits?: number;
    },
  ): Promise<CourseOffering> {
    const response = await this.client.post<{ data: CourseOffering }>(
      `/courses/offerings/${offeringId}/assign-lecturer`,
      payload,
    );
    return normalizeOffering(response.data.data);
  }

  async closeCourseOffering(offeringId: string): Promise<CourseOffering> {
    const response = await this.client.post<{ data: CourseOffering }>(
      `/courses/offerings/${offeringId}/close`,
    );
    return normalizeOffering(response.data.data);
  }

  getCourseCatalogueReportUrl(format: 'pdf' | 'csv'): string {
    return `${API_BASE_URL}/courses/report?format=${format}`;
  }

  getCourseOfferingReportUrl(format: 'pdf' | 'csv'): string {
    return `${API_BASE_URL}/courses/offerings/report?format=${format}`;
  }

  getCourseSyllabusUrl(courseId: string): string {
    return `${API_BASE_URL}/courses/${encodeURIComponent(courseId)}/syllabus`;
  }

  async getStudents(params?: { search?: string; status?: string }): Promise<Student[]> {
    const res = await this.client.get<{ data: Student[] }>('/students', {
      params: {
        'filter[search]': params?.search,
        'filter[status]': params?.status,
        per_page: 100,
      },
    });
    return res.data.data.map((row) => this.normalizeStudent(row));
  }

  async getStudentById(studentId: string): Promise<Student> {
    const res = await this.client.get<{ data: Student }>(`/students/${studentId}`);
    return this.normalizeStudent(res.data.data);
  }

  async getStudentsDashboard(): Promise<StudentDashboard> {
    const res = await this.client.get<{ data: StudentDashboard }>('/students/dashboard');
    return res.data.data;
  }

  async getMatriculationQueue(): Promise<MatriculationQueueItem[]> {
    const res = await this.client.get<{ data: MatriculationQueueItem[] }>('/students/matriculation-queue');
    return res.data.data;
  }

  async matriculateStudents(payload: {
    application_ids: string[];
    pledge_signed?: boolean;
    notes?: string;
  }): Promise<Student[]> {
    const res = await this.client.post<{ data: Student[] }>('/students/matriculate', payload);
    return res.data.data.map((row) => this.normalizeStudent(row));
  }

  async updateStudentStatus(
    studentId: string,
    payload: { status: string; reason: string },
  ): Promise<Student> {
    const res = await this.client.patch<{ data: Student }>(`/students/${studentId}/status`, payload);
    return this.normalizeStudent(res.data.data);
  }

  getStudentMasterReportUrl(format: 'csv' | 'pdf' = 'csv'): string {
    return `${API_BASE_URL}/students/report?type=directory&format=${format}`;
  }

  getMatriculationReportUrl(format: 'pdf' | 'csv' = 'pdf'): string {
    return `${API_BASE_URL}/students/report?type=matriculation&format=${format}`;
  }

  getStudentDigitalIdUrl(studentId: string): string {
    return `${API_BASE_URL}/students/${encodeURIComponent(studentId)}/digital-id`;
  }

  private normalizeStudent(row: Student & { current_semester?: number; admission_year?: { id: string } }): Student {
    return {
      ...row,
      current_term_sequence: row.current_term_sequence ?? row.current_semester ?? 1,
      admission_term_id: row.admission_term_id ?? row.admission_year?.id ?? row.id,
      cumulative_gpa: row.cumulative_gpa ?? 0,
      cumulative_credits_earned: row.cumulative_credits_earned ?? 0,
    };
  }

  async getStudentProfile(studentId?: string): Promise<Student> {
    if (studentId) {
      return this.getStudentById(studentId);
    }
    const res = await this.client.get<{ data: Student[] }>('/students', { params: { per_page: 1 } });
    const first = res.data.data[0];
    if (!first) {
      throw new ApiError('No student profile found for the current user.', 404);
    }
    return this.normalizeStudent(first);
  }

  async getTermRegistrations(): Promise<TermRegistration[]> {
    const res = await this.client.get<{ data: TermRegistration[] }>('/enrollment/registrations');
    return res.data.data;
  }

  async getTermGpas(): Promise<TermGpa[]> {
    const res = await this.client.get<{ data: TermGpa[] }>('/exams/term-gpas');
    return res.data.data;
  }

  async getMarksSheet(offeringId: string): Promise<StudentMark[]> {
    const res = await this.client.get<{ data: StudentMark[] }>(`/exams/marks-sheet/${offeringId}`);
    return res.data.data;
  }

  async saveMarks(
    offeringId: string,
    payload: { enrollment_id: string; cat_score: number; exam_score: number },
  ): Promise<StudentMark> {
    const res = await this.client.post<{ data: StudentMark }>(
      `/exams/marks-sheet/${offeringId}/save`,
      payload,
    );
    return res.data.data;
  }

  async submitMarks(offeringId: string, enrollmentId: string): Promise<StudentMark> {
    const res = await this.client.post<{ data: StudentMark }>(
      `/exams/marks-sheet/${offeringId}/submit`,
      { enrollment_id: enrollmentId },
    );
    return res.data.data;
  }

  getResultSlipUrl(termId: string): string {
    return `${API_BASE_URL}/progression/result-slip/${encodeURIComponent(termId)}`;
  }

  /** Finance (MOD-01-09) */
  async getFinanceStatement(): Promise<{ invoices: Invoice[]; payments: Payment[]; clearance: Record<string, unknown> }> {
    const res = await this.client.get<{ data: { invoices: Invoice[]; payments: Payment[]; clearance: Record<string, unknown> } }>(
      '/finance/statement',
    );
    return res.data.data;
  }

  async getFinanceClearance(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/finance/clearance-status');
    return res.data.data;
  }

  async getInvoices(): Promise<Invoice[]> {
    const statement = await this.getFinanceStatement();
    return statement.invoices;
  }

  async getPayments(): Promise<Payment[]> {
    const statement = await this.getFinanceStatement();
    return statement.payments;
  }

  async initiateMpesaPayment(payload: {
    invoice_id: string;
    phone_number: string;
    amount: number;
  }): Promise<{ status: string; checkout_request_id: string; message: string; payment_id?: string; receipt_number?: string }> {
    const res = await this.client.post<{ data: { status: string; checkout_request_id: string; message: string; payment_id?: string; receipt_number?: string } }>(
      '/finance/mpesa/stk-push',
      {
        invoice_id: payload.invoice_id,
        phone_number: payload.phone_number,
        amount: payload.amount,
      },
    );
    return res.data.data;
  }

  /** Enrollment (MOD-01-07) */
  async registerTerm(termId: string): Promise<TermRegistration> {
    const res = await this.client.post<{ data: TermRegistration }>('/enrollment/register-term', { term_id: termId });
    return res.data.data;
  }

  async getAvailableCourses(termId: string): Promise<CourseOffering[]> {
    const res = await this.client.get<{ data: CourseOffering[] }>('/enrollment/available-courses', {
      params: { term_id: termId },
    });
    return res.data.data.map((row) => normalizeOffering(row));
  }

  async registerCourses(payload: { term_id: string; offering_ids: string[] }): Promise<CourseEnrollment[]> {
    const res = await this.client.post<{ data: CourseEnrollment[] }>('/enrollment/courses/enroll', {
      term_id: payload.term_id,
      offering_ids: payload.offering_ids,
    });
    return res.data.data;
  }

  async getMyCourses(termId?: string): Promise<CourseEnrollment[]> {
    const res = await this.client.get<{ data: CourseEnrollment[] }>('/enrollment/my-courses', {
      params: { term_id: termId },
    });
    return res.data.data;
  }

  async getCourseEnrollments(): Promise<CourseEnrollment[]> {
    const res = await this.client.get<{ data: CourseEnrollment[] }>('/enrollment/course-enrollments');
    return res.data.data;
  }

  /** Portal (MOD-01-13) */
  async getPortalDashboard(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/portal/student/dashboard');
    return res.data.data;
  }

  /** LMS (MOD-02-01) */
  async getLmsSyncStatus(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/lms/sync/status');
    return res.data.data;
  }

  async syncLmsCourse(offeringId: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/lms/sync/courses', {
      offering_id: offeringId,
    });
    return res.data.data;
  }

  async getLmsLaunchUrl(path = '/'): Promise<string> {
    const res = await this.client.get<{ data: { url: string } }>('/lms/launch', { params: { path } });
    return res.data.data.url;
  }

  async syncLmsEnrollment(enrollmentId: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/lms/sync/enrollments', {
      enrollment_id: enrollmentId,
    });
    return res.data.data;
  }

  async pullLmsGrades(offeringId: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/lms/sync/grades/pull', {
      offering_id: offeringId,
    });
    return res.data.data;
  }

  /** Attendance (MOD-02-02) */
  async openAttendanceSession(offeringId: string, teachingSlotId?: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/attendance/sessions/open', {
      offering_id: offeringId,
      teaching_slot_id: teachingSlotId,
    });
    return res.data.data;
  }

  async closeAttendanceSession(sessionId: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>(
      `/attendance/sessions/${sessionId}/close`,
    );
    return res.data.data;
  }

  async getActiveAttendanceSessions(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/attendance/sessions/active');
    return res.data.data;
  }

  async checkInAttendance(token: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/attendance/check-in', { token });
    return res.data.data;
  }

  async getMyAttendanceRecord(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/attendance/my-record');
    return res.data.data;
  }

  async getCourseAttendanceReport(offeringId: string): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>(
      `/attendance/course/${offeringId}/report`,
    );
    return res.data.data;
  }

  async getAttendanceAtRisk(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/attendance/at-risk');
    return res.data.data;
  }

  async getPortalDocuments(): Promise<Array<{ title: string; url: string; type: string }>> {
    const res = await this.client.get<{ data: Array<{ title: string; url: string; type: string }> }>(
      '/portal/student/documents',
    );
    return res.data.data;
  }

  async getMyResults(): Promise<TermGpa[]> {
    const res = await this.client.get<{ data: TermGpa[] }>('/progression/my-results');
    return res.data.data;
  }

  async getTimetableSchedule(termId?: string): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/timetable/my-schedule', {
      params: { term_id: termId },
    });
    return res.data.data;
  }

  getTimetableExportUrl(termId?: string): string {
    const query = termId ? `?term_id=${encodeURIComponent(termId)}` : '';
    return `${API_BASE_URL}/timetable/export.ics${query}`;
  }

  /** Graduation & clearance (MOD-01-12 / MOD-02-09) */
  async applyGraduation(): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/graduation/apply');
    return res.data.data;
  }

  async getGraduationClearanceStatus(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/graduation/clearance-status');
    return res.data.data;
  }

  async getGraduationClearanceQueue(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/graduation/clearance-queue');
    return res.data.data;
  }

  async clearGraduationCheckpoint(checkpointId: string, notes?: string): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>(
      `/graduation/checkpoints/${checkpointId}/clear`,
      { notes },
    );
    return res.data.data;
  }

  getGraduationTranscriptUrl(): string {
    return `${API_BASE_URL}/graduation/transcript`;
  }

  getExamCardUrl(): string {
    return `${API_BASE_URL}/exams/my-card`;
  }

  /** Advising (MOD-02-03) */
  async getMyAdvisees(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/advising/my-advisees');
    return res.data.data;
  }

  async getAdvisingAssignments(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/advising/assignments');
    return res.data.data;
  }

  async assignAdvisor(payload: {
    student_id: string;
    advisor_user_id: string;
    assignment_reason?: string;
  }): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/advising/assignments', payload);
    return res.data.data;
  }

  async getMyDegreeProgress(): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>('/advising/my-progress');
    return res.data.data;
  }

  async getStudentDegreeAudit(studentId: string): Promise<Record<string, unknown>> {
    const res = await this.client.get<{ data: Record<string, unknown> }>(
      `/advising/student/${studentId}/degree-audit`,
    );
    return res.data.data;
  }

  async getStudentAdvisoryNotes(studentId: string): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>(
      `/advising/student/${studentId}/notes`,
    );
    return res.data.data;
  }

  async createAdvisoryNote(payload: {
    student_id: string;
    note_text: string;
    note_type?: string;
    visible_to_student?: boolean;
    is_confidential?: boolean;
  }): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>('/advising/notes', payload);
    return res.data.data;
  }

  async requestAdvisingSession(payload: {
    scheduled_at: string;
    mode?: string;
    topic?: string;
  }): Promise<Record<string, unknown>> {
    const res = await this.client.post<{ data: Record<string, unknown> }>(
      '/advising/sessions/request',
      payload,
    );
    return res.data.data;
  }

  async getAdvisingSessions(): Promise<Record<string, unknown>[]> {
    const res = await this.client.get<{ data: Record<string, unknown>[] }>('/advising/sessions');
    return res.data.data;
  }

  async updateAdvisingSession(
    sessionId: string,
    payload: { status: string; outcome?: string },
  ): Promise<Record<string, unknown>> {
    const res = await this.client.patch<{ data: Record<string, unknown> }>(
      `/advising/sessions/${sessionId}`,
      payload,
    );
    return res.data.data;
  }

  /** Legacy read endpoints */
  async getStudentMarks(_studentId?: string): Promise<StudentMark[]> {
    throw new ApiError('Use lecturer marks sheet API for detailed marks.', 501, 'NOT_IMPLEMENTED');
  }

  async request<T = unknown>(config: AxiosRequestConfig): Promise<T> {
    const res = await this.client.request<T>(config);
    return res.data;
  }
}

export const api = new MemaApiClient();

export * from './mock-data';
