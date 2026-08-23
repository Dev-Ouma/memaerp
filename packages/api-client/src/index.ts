import axios, { AxiosError, AxiosInstance, AxiosRequestConfig } from 'axios';
import type {
  AuthUserProfile,
  Course,
  CourseOffering,
  Invoice,
  Payment,
  Programme,
  Student,
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
    public fields?: Record<string, string[]>
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

function normalizeProgramme(programme: Programme): Programme {
  return {
    ...programme,
    title: programme.title ?? programme.name ?? programme.code,
    credit_units_required:
      programme.credit_units_required ?? programme.total_credits_required ?? 0,
  };
}

function normalizeCourse(course: Course): Course {
  return {
    ...course,
    credit_units: course.credit_units ?? course.credits ?? 0,
    practical_hours: course.practical_hours ?? course.lab_hours ?? 0,
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
    }>;
    const data = axiosError.response?.data;
    const fields = data?.errors;
    const message =
      (fields?.login?.[0] ?? fields?.email?.[0]) ||
      data?.message ||
      axiosError.message ||
      'Request failed';

    return new ApiError(message, axiosError.response?.status, data?.code, fields);
  }

  return new ApiError(error instanceof Error ? error.message : 'Request failed');
}

export class MemaApiClient {
  private client: AxiosInstance;

  constructor(baseURL: string = API_BASE_URL) {
    this.client = axios.create({
      baseURL,
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    this.client.interceptors.response.use(
      (response) => response,
      (error) => Promise.reject(extractError(error))
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
      '/auth/login', credentials
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
      '/auth/password/forgot', { email }
    );
    return response.data;
  }

  async resetPassword(payload: { email: string; token: string; password: string; password_confirmation: string }): Promise<void> {
    await this.client.post('/auth/password/reset', payload);
  }

  async getIamUsers(): Promise<{ data: Array<Record<string, unknown>>; meta: { total: number } }> {
    const response = await this.client.get('/iam/users');
    return response.data;
  }

  async getIamRoles(): Promise<{ data: Array<Record<string, unknown>> }> {
    const response = await this.client.get('/iam/roles');
    return response.data;
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

  async getCourses(): Promise<Course[]> {
    const res = await this.client.get<{ data: Course[] }>('/courses/');
    return res.data.data.map(normalizeCourse);
  }

  async getOfferings(params?: {
    term_id?: string;
    campus_id?: string;
  }): Promise<CourseOffering[]> {
    const res = await this.client.get<{ data: CourseOffering[] }>(
      '/courses/offerings/active',
      { params }
    );
    return res.data.data;
  }

  async getStudents(): Promise<Student[]> {
    const res = await this.client.get<{ data: Student[] }>('/enrollment/students');
    return res.data.data;
  }

  async getStudentProfile(studentId?: string): Promise<Student> {
    const url = studentId ? `/enrollment/students/${studentId}` : '/enrollment/students';
    const res = await this.client.get<{ data: Student | Student[] }>(url);
    const payload = res.data.data;
    if (Array.isArray(payload)) {
      const first = payload[0];
      if (!first) {
        throw new ApiError('No student profile found for the current user.', 404);
      }
      return first;
    }
    return payload;
  }

  async getTermRegistrations(): Promise<TermRegistration[]> {
    const res = await this.client.get<{ data: TermRegistration[] }>(
      '/enrollment/registrations'
    );
    return res.data.data;
  }

  async getTermGpas(): Promise<TermGpa[]> {
    const res = await this.client.get<{ data: TermGpa[] }>('/exams/term-gpas');
    return res.data.data;
  }

  /** Not yet implemented on the backend — reserved for finance module */
  async getInvoices(_studentId?: string): Promise<Invoice[]> {
    throw new ApiError('Finance invoice API is not available yet.', 501, 'NOT_IMPLEMENTED');
  }

  /** Not yet implemented on the backend — reserved for finance module */
  async getPayments(_studentId?: string): Promise<Payment[]> {
    throw new ApiError('Finance payment API is not available yet.', 501, 'NOT_IMPLEMENTED');
  }

  /** Not yet implemented on the backend — reserved for registration module */
  async registerCourses(_payload: {
    term_id: string;
    offering_ids: string[];
  }): Promise<TermRegistration> {
    throw new ApiError('Course registration API is not available yet.', 501, 'NOT_IMPLEMENTED');
  }

  /** Not yet implemented on the backend — student-scoped marks list */
  async getStudentMarks(_studentId?: string): Promise<StudentMark[]> {
    throw new ApiError('Student marks API is not available yet.', 501, 'NOT_IMPLEMENTED');
  }

  /** Not yet implemented on the backend — reserved for finance module */
  async initiateMpesaPayment(_payload: {
    invoice_id: string;
    phone_number: string;
    amount: number;
  }): Promise<{ status: string; checkout_request_id: string; message: string }> {
    throw new ApiError('M-Pesa payment API is not available yet.', 501, 'NOT_IMPLEMENTED');
  }

  async request<T = unknown>(config: AxiosRequestConfig): Promise<T> {
    const res = await this.client.request<T>(config);
    return res.data;
  }
}

export const api = new MemaApiClient();

export * from './mock-data';
