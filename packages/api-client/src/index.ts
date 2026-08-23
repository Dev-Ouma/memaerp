import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';
import type {
  User,
  Student,
  Programme,
  Course,
  CourseOffering,
  TermRegistration,
  StudentMark,
  TermGpa,
  Invoice,
  Payment,
  ApiResponse,
} from '@mema/types';

export const API_BASE_URL =
  (typeof process !== 'undefined' && process.env?.NEXT_PUBLIC_API_URL) ||
  'http://localhost:8000/api/v1';

export class MemaApiClient {
  private client: AxiosInstance;

  constructor(baseURL: string = API_BASE_URL) {
    this.client = axios.create({
      baseURL,
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    });

    this.client.interceptors.response.use(
      (response) => response,
      (error) => {
        // Handle 401 unauthenticated
        if (error.response?.status === 401 && typeof window !== 'undefined') {
          // Custom event or redirection if needed
        }
        return Promise.reject(error);
      }
    );
  }

  // Auth & Session
  async getCsrfCookie() {
    const rootUrl = API_BASE_URL.replace('/api/v1', '');
    return axios.get(`${rootUrl}/sanctum/csrf-cookie`, { withCredentials: true });
  }

  async login(credentials: { identifier: string; password: string }) {
    await this.getCsrfCookie().catch(() => null);
    const rootUrl = API_BASE_URL.replace('/api/v1', '');
    return axios.post(`${rootUrl}/login`, credentials, { withCredentials: true });
  }

  async logout() {
    const rootUrl = API_BASE_URL.replace('/api/v1', '');
    return axios.post(`${rootUrl}/logout`, {}, { withCredentials: true });
  }

  async getCurrentUser(): Promise<User> {
    const response = await this.client.get<User>('/user');
    return response.data;
  }

  // Academics
  async getProgrammes(): Promise<Programme[]> {
    const res = await this.client.get<ApiResponse<Programme[]>>('/programmes');
    return res.data.data;
  }

  async getCourses(): Promise<Course[]> {
    const res = await this.client.get<ApiResponse<Course[]>>('/courses');
    return res.data.data;
  }

  async getOfferings(params?: { term_id?: string; campus_id?: string }): Promise<CourseOffering[]> {
    const res = await this.client.get<ApiResponse<CourseOffering[]>>('/courses/offerings', { params });
    return res.data.data;
  }

  // Student & Enrollment
  async getStudentProfile(studentId?: string): Promise<Student> {
    const url = studentId ? `/students/${studentId}` : '/student/me';
    const res = await this.client.get<ApiResponse<Student>>(url);
    return res.data.data;
  }

  async getTermRegistrations(studentId?: string): Promise<TermRegistration[]> {
    const url = studentId ? `/students/${studentId}/registrations` : '/student/registrations';
    const res = await this.client.get<ApiResponse<TermRegistration[]>>(url);
    return res.data.data;
  }

  async registerCourses(payload: { term_id: string; offering_ids: string[] }): Promise<TermRegistration> {
    const res = await this.client.post<ApiResponse<TermRegistration>>('/enrollment/register', payload);
    return res.data.data;
  }

  // Exams & Results
  async getStudentMarks(studentId?: string): Promise<StudentMark[]> {
    const url = studentId ? `/students/${studentId}/marks` : '/student/marks';
    const res = await this.client.get<ApiResponse<StudentMark[]>>(url);
    return res.data.data;
  }

  async getTermGpas(studentId?: string): Promise<TermGpa[]> {
    const url = studentId ? `/students/${studentId}/gpas` : '/student/gpas';
    const res = await this.client.get<ApiResponse<TermGpa[]>>(url);
    return res.data.data;
  }

  // Finance
  async getInvoices(studentId?: string): Promise<Invoice[]> {
    const url = studentId ? `/students/${studentId}/invoices` : '/student/invoices';
    const res = await this.client.get<ApiResponse<Invoice[]>>(url);
    return res.data.data;
  }

  async getPayments(studentId?: string): Promise<Payment[]> {
    const url = studentId ? `/students/${studentId}/payments` : '/student/payments';
    const res = await this.client.get<ApiResponse<Payment[]>>(url);
    return res.data.data;
  }

  async initiateMpesaPayment(payload: {
    invoice_id: string;
    phone_number: string;
    amount: number;
  }): Promise<{ status: string; checkout_request_id: string; message: string }> {
    const res = await this.client.post('/finance/mpesa/stk-push', payload);
    return res.data;
  }

  // Generic request
  async request<T = any>(config: AxiosRequestConfig): Promise<T> {
    const res = await this.client.request<T>(config);
    return res.data;
  }
}

export const api = new MemaApiClient();

// Re-export mock fixtures for demo and offline fallbacks
export * from './mock-data';
