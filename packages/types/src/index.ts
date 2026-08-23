/**
 * MEMA ERP TypeScript Domain Definitions
 */

// ==========================================
// Base & Identity
// ==========================================
export type UUID = string;

export interface Timestamps {
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
}

export type UserType = 'SUPERADMIN' | 'ADMIN' | 'LECTURER' | 'STAFF' | 'STUDENT' | 'APPLICANT';

/** Profile returned by GET /api/v1/auth/me */
export interface AuthRoleAssignment {
  role_code: string | null;
  role_name: string | null;
  family: string | null;
  scope_type: string | null;
  scope_id: string | null;
}

export interface AuthPersonIdentity {
  type: string;
  identifier: string;
  status: string;
}

export interface AuthPersonSummary {
  id: UUID;
  full_name: string;
  given_name: string;
  family_name: string;
  primary_email: string | null;
  identities?: AuthPersonIdentity[];
}

export interface AuthUserProfile {
  id: UUID;
  email: string;
  username: string;
  is_active: boolean;
  must_change_password: boolean;
  last_login_at: string | null;
  person: AuthPersonSummary | null;
  institution: { id: UUID; code: string; name: string } | null;
  roles: AuthRoleAssignment[];
  permissions: string[];
}

export interface User extends Timestamps {
  id: UUID;
  institution_id: UUID;
  person_id: UUID;
  username: string;
  email: string;
  user_type: UserType;
  student_id_number?: string | null;
  staff_id_number?: string | null;
  is_active: boolean;
  access_version: number;
  mfa_enabled?: boolean;
  last_login_at?: string | null;
  person?: Person;
  roles?: Role[];
  permissions?: string[];
}

export type Gender = 'MALE' | 'FEMALE' | 'OTHER' | 'PREFER_NOT_TO_SAY';

export interface Person extends Timestamps {
  id: UUID;
  institution_id: UUID;
  first_name: string;
  middle_name?: string | null;
  last_name: string;
  national_id_number?: string | null;
  passport_number?: string | null;
  birth_certificate_number?: string | null;
  date_of_birth?: string | null;
  gender: Gender;
  nationality?: string | null;
  personal_email?: string | null;
  phone_number?: string | null;
  avatar_url?: string | null;
  user?: User;
}

// ==========================================
// IAM & Security
// ==========================================
export interface Permission extends Timestamps {
  id: UUID;
  name: string;
  module: string;
  resource: string;
  action: string;
  description?: string | null;
}

export interface Role extends Timestamps {
  id: UUID;
  institution_id: UUID;
  name: string;
  display_name: string;
  description?: string | null;
  is_system: boolean;
  permissions?: Permission[];
}

export interface RoleAssignment extends Timestamps {
  id: UUID;
  user_id: UUID;
  role_id: UUID;
  campus_id?: UUID | null;
  faculty_id?: UUID | null;
  department_id?: UUID | null;
  role?: Role;
}

// ==========================================
// Institution & Master Data
// ==========================================
export interface Institution extends Timestamps {
  id: UUID;
  name: string;
  code: string;
  motto?: string | null;
  logo_url?: string | null;
  primary_color?: string | null;
  domain?: string | null;
}

export interface Campus extends Timestamps {
  id: UUID;
  institution_id: UUID;
  name: string;
  code: string;
  location?: string | null;
  is_main: boolean;
  is_active: boolean;
}

export interface Faculty extends Timestamps {
  id: UUID;
  institution_id: UUID;
  name: string;
  code: string;
  description?: string | null;
  dean_id?: UUID | null;
  departments?: Department[];
}

export interface Department extends Timestamps {
  id: UUID;
  institution_id: UUID;
  faculty_id: UUID;
  name: string;
  code: string;
  hod_id?: UUID | null;
  faculty?: Faculty;
}

export interface AcademicYear extends Timestamps {
  id: UUID;
  institution_id: UUID;
  code: string;
  name: string;
  start_date: string;
  end_date: string;
  is_current: boolean;
  terms?: Term[];
}

export interface Term extends Timestamps {
  id: UUID;
  institution_id: UUID;
  academic_year_id: UUID;
  code: string;
  name: string;
  term_type: 'SEMESTER' | 'TRIMESTER' | 'QUARTER';
  sequence_number: number;
  start_date: string;
  end_date: string;
  registration_start_date: string;
  registration_end_date: string;
  is_current: boolean;
  academic_year?: AcademicYear;
}

// ==========================================
// Academic & Curriculum
// ==========================================
export type AwardLevel = 'CERTIFICATE' | 'DIPLOMA' | 'BACHELORS' | 'POSTGRADUATE_DIPLOMA' | 'MASTERS' | 'DOCTORATE';

export interface Programme extends Timestamps {
  id: UUID;
  institution_id: UUID;
  department_id: UUID;
  code: string;
  /** API field */
  name?: string;
  /** Legacy mock / display alias */
  title?: string;
  award_level: AwardLevel;
  duration_years: number;
  /** API field */
  total_credits_required?: number;
  /** Legacy mock field */
  credit_units_required?: number;
  is_active: boolean;
  department?: Department;
  curriculum_versions?: CurriculumVersion[];
  versions?: CurriculumVersion[];
}

export interface CurriculumVersion extends Timestamps {
  id: UUID;
  programme_id: UUID;
  version_code: string;
  effective_academic_year_id: UUID;
  is_approved: boolean;
  total_credit_units: number;
  programme?: Programme;
  curriculum_courses?: CurriculumCourse[];
}

export interface Course extends Timestamps {
  id: UUID;
  institution_id: UUID;
  department_id: UUID;
  code: string;
  title: string;
  credit_units: number;
  lecture_hours: number;
  practical_hours: number;
  description?: string | null;
  is_active: boolean;
  department?: Department;
  prerequisites?: CoursePrerequisite[];
}

export interface CoursePrerequisite {
  id: UUID;
  course_id: UUID;
  prerequisite_course_id: UUID;
  is_mandatory: boolean;
  prerequisite_course?: Course;
}

export interface CurriculumCourse {
  id: UUID;
  curriculum_version_id: UUID;
  course_id: UUID;
  year_level: number;
  term_sequence: number;
  is_core: boolean;
  course?: Course;
}

export interface CourseOffering extends Timestamps {
  id: UUID;
  institution_id: UUID;
  course_id: UUID;
  term_id: UUID;
  campus_id: UUID;
  lecturer_id?: UUID | null;
  section_code: string;
  capacity: number;
  enrolled_count: number;
  room?: string | null;
  schedule_slot?: string | null;
  course?: Course;
  term?: Term;
  campus?: Campus;
  lecturer?: User;
}

// ==========================================
// Student & Admissions
// ==========================================
export type ApplicationStatus = 'DRAFT' | 'SUBMITTED' | 'UNDER_REVIEW' | 'SHORTLISTED' | 'ADMITTED' | 'ACCEPTED' | 'REJECTED' | 'MATRICULATED';

export interface Application extends Timestamps {
  id: UUID;
  institution_id: UUID;
  person_id: UUID;
  programme_id: UUID;
  campus_id: UUID;
  academic_year_id: UUID;
  application_number: string;
  intake_code: string;
  status: ApplicationStatus;
  submission_date?: string | null;
  decision_date?: string | null;
  decision_notes?: string | null;
  person?: Person;
  programme?: Programme;
  campus?: Campus;
}

export type StudentStatus = 'PROSPECT' | 'APPLICANT' | 'ADMITTED' | 'ACTIVE' | 'ON_LEAVE' | 'SUSPENDED' | 'DISCONTINUED' | 'GRADUATED' | 'ALUMNUS';

export interface Student extends Timestamps {
  id: UUID;
  institution_id: UUID;
  person_id: UUID;
  programme_id: UUID;
  campus_id: UUID;
  curriculum_version_id?: UUID | null;
  student_number: string;
  admission_term_id: UUID;
  current_year_level: number;
  current_term_sequence: number;
  status: StudentStatus;
  cumulative_gpa: number;
  cumulative_credits_earned: number;
  person?: Person;
  programme?: Programme;
  campus?: Campus;
}

// ==========================================
// Enrollment & Examination
// ==========================================
export type RegistrationStatus = 'PENDING' | 'SUBMITTED' | 'APPROVED' | 'REJECTED' | 'CANCELLED';

export interface TermRegistration extends Timestamps {
  id: UUID;
  student_id: UUID;
  term_id: UUID;
  registration_date: string;
  status: RegistrationStatus;
  total_credits: number;
  student?: Student;
  term?: Term;
  course_enrollments?: CourseEnrollment[];
}

export type EnrollmentStatus = 'ENROLLED' | 'DROPPED' | 'WITHDRAWN' | 'AUDIT';

export interface CourseEnrollment extends Timestamps {
  id: UUID;
  term_registration_id: UUID;
  student_id: UUID;
  course_offering_id: UUID;
  status: EnrollmentStatus;
  student?: Student;
  course_offering?: CourseOffering;
  mark?: StudentMark;
}

export interface StudentMark extends Timestamps {
  id: UUID;
  course_enrollment_id: UUID;
  cat_score?: number | null;
  exam_score?: number | null;
  total_score?: number | null;
  grade_letter?: string | null;
  grade_points?: number | null;
  is_passed: boolean;
  is_locked: boolean;
  course_enrollment?: CourseEnrollment;
}

export interface TermGpa extends Timestamps {
  id: UUID;
  student_id: UUID;
  term_id: UUID;
  term_credits_attempted: number;
  term_credits_earned: number;
  term_gpa: number;
  cumulative_credits_attempted: number;
  cumulative_credits_earned: number;
  cumulative_gpa: number;
  standing: 'GOOD_STANDING' | 'PROBATION' | 'SUSPENDED' | 'DISCONTINUED';
  student?: Student;
  term?: Term;
}

// ==========================================
// Finance
// ==========================================
export interface FeeStructure extends Timestamps {
  id: UUID;
  institution_id: UUID;
  programme_id: UUID;
  academic_year_id: UUID;
  year_level: number;
  term_sequence: number;
  total_amount: number;
  currency: string;
  programme?: Programme;
}

export type InvoiceStatus = 'PENDING' | 'PARTIALLY_PAID' | 'PAID' | 'CANCELLED';

export interface Invoice extends Timestamps {
  id: UUID;
  institution_id: UUID;
  person_id: UUID;
  fee_structure_id?: UUID | null;
  term_id?: UUID | null;
  invoice_number: string;
  total_amount: number;
  paid_amount: number;
  balance_amount: number;
  status: InvoiceStatus;
  due_date: string;
  person?: Person;
  fee_structure?: FeeStructure;
  term?: Term;
  payments?: Payment[];
}

export type PaymentMethod = 'MPESA' | 'BANK_TRANSFER' | 'CREDIT_CARD' | 'CHEQUE' | 'CASH';
export type PaymentStatus = 'PENDING' | 'COMPLETED' | 'FAILED' | 'RECONCILED';

export interface Payment extends Timestamps {
  id: UUID;
  institution_id: UUID;
  person_id: UUID;
  invoice_id?: UUID | null;
  reference_number: string;
  payment_method: PaymentMethod;
  amount: number;
  currency: string;
  status: PaymentStatus;
  transaction_date: string;
  notes?: string | null;
  person?: Person;
  invoice?: Invoice;
}

// ==========================================
// Audit & Activity
// ==========================================
export interface ActivityLog {
  id: UUID;
  institution_id: UUID;
  actor_id?: UUID | null;
  actor_name?: string | null;
  module: string;
  action: string;
  subject_type: string;
  subject_id: string;
  ip_address?: string | null;
  user_agent?: string | null;
  before_payload?: Record<string, unknown> | null;
  after_payload?: Record<string, unknown> | null;
  created_at: string;
}

// ==========================================
// API Response Wrappers
// ==========================================
export interface ApiResponse<T> {
  data: T;
  message?: string;
  meta?: {
    total?: number;
    page?: number;
    per_page?: number;
    last_page?: number;
  };
}

export interface ApiErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
  status_code?: number;
}
