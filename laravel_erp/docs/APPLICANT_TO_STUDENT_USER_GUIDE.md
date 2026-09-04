# MEMA ERP — End-to-End User Guide: Applicant-to-Student Lifecycle

**Document ID:** `MEMA-UG-ADM-001`  
**Version:** `2.4.0 (Enterprise Release)`  
**Target Audience:** Admissions Officers, Academic Registrars, Department Chairs, Finance Clerks, System Administrators, and Prospective Students  
**System Base URL:** `http://127.0.0.1:8000`  
**Scope:** Complete operational journey from public intake application, document verification, departmental review, offer letter issuance, binding acceptance, student profile conversion, cohort mapping, course unit registration, to classroom/LMS participation.

---

## Table of Contents

1. [Executive Summary & Lifecycle Philosophy](#1-executive-summary--lifecycle-philosophy)
2. [Actor Roles & Access Control Matrix](#2-actor-roles--access-control-matrix)
3. [End-to-End State Machine Reference](#3-end-to-end-state-machine-reference)
4. [Stage 1: Public Intake & Application Submission](#stage-1-public-intake--application-submission)
5. [Stage 2: Admissions Office Triage & Document Verification](#stage-2-admissions-office-triage--document-verification)
6. [Stage 3: Departmental Scoring, Review & Shortlisting](#stage-3-departmental-scoring-review--shortlisting)
7. [Stage 4: Institutional Approvals & Offer Letter Issuance](#stage-4-institutional-approvals--offer-letter-issuance)
8. [Stage 5: Offer Response, Acceptance & Legal Commitment](#stage-5-offer-response-acceptance--legal-commitment)
9. [Stage 6: Finance Clearance & Fee Reconciliation](#stage-6-finance-clearance--fee-reconciliation)
10. [Stage 7: Student Conversion & Permanent Identity Materialization](#stage-7-student-conversion--permanent-identity-materialization)
11. [Stage 8: Cohort Mapping, Stream Allocation & Unit Enrollment](#stage-8-cohort-mapping-stream-allocation--unit-enrollment)
12. [Stage 9: Classroom Readiness, LMS Sync & Academic Attendance](#stage-9-classroom-readiness-lms-sync--academic-attendance)
13. [Auditing, Forensics & Compliance Monitoring](#13-auditing-forensics--compliance-monitoring)
14. [Exception Handling & Troubleshooting Directory](#14-exception-handling--troubleshooting-directory)

---

## 1. Executive Summary & Lifecycle Philosophy

MEMA ERP executes the applicant-to-student journey through a **zero-data-loss, cryptographically sealed funnel**. Rather than treating admissions as disconnected paperwork, the system binds every application step to PostgreSQL database transactions:

* **Identity Continuity:** When an applicant is admitted and enrolled, their existing login account (`User`), authentication tokens, and audit trail are upgraded to the `student` role. No secondary redundant accounts or orphaned profiles are created.
* **Cryptographic Tamper-Evidence:** On submission, the full application payload is hashed using **SHA-256** (`ApplicationVersion`). Acceptance of admission offers captures legal evidence (IP address, client user agent, timestamp, terms version hash).
* **Idempotent Conversion:** The transition from applicant to student is mediated by the `StudentConversionService`. Concurrent conversion calls are locked at the database row level, preventing race conditions or duplicate student numbers.

```
┌─────────────────┐       ┌─────────────────┐       ┌──────────────────┐
│   PROSPECTIVE   │       │   ADMISSIONS    │       │   DEPARTMENTAL   │
│    APPLICANT    │──────▶│     TRIAGE      │──────▶│      REVIEW      │
│  (Draft & Pay)  │       │ (Doc Verify)    │       │ (Scoring Rubric) │
└─────────────────┘       └─────────────────┘       └──────────────────┘
                                                              │
                                                              ▼
┌─────────────────┐       ┌─────────────────┐       ┌──────────────────┐
│     STUDENT     │       │     OFFER &     │       │   SENATE/DEAN    │
│   CONVERSION    │◀──────│   ACCEPTANCE    │◀──────│    APPROVALS     │
│ (Reg. Number)   │       │ (Legal Evidence)│       │ (Formal Offer)   │
└─────────────────┘       └─────────────────┘       └──────────────────┘
        │
        ▼
┌─────────────────┐       ┌─────────────────┐       ┌──────────────────┐
│ COHORT / STREAM │       │   COURSE UNIT   │       │    CLASSROOM     │
│   ALLOCATION    │──────▶│  REGISTRATION   │──────▶│   LMS & TIMETABLE│
│(Academic Session│       │(Academic Records│       │ (Attendance/Exam)│
└─────────────────┘       └─────────────────┘       └──────────────────┘
```

---

## 2. Actor Roles & Access Control Matrix

| Role Key | Portal / Interface Path | Key Responsibilities in Lifecycle |
|:---|:---|:---|
| **Applicant** | `/admissions/my-application`<br>`/applicant/dashboard` | Creates application, uploads certificates, pays application fee, tracks triage, signs offer letter. |
| **Admissions Clerk** | `/admissions/work-queues`<br>`/admissions/document-verification` | Checks submitted files against national registries, marks documents valid/invalid, requests corrections. |
| **Department Reviewer** | `/admissions/reviews`<br>`/admissions/shortlists` | Evaluates academic qualifications, enters rubric scores, recommends shortlist/waitlist. |
| **Dean / Registrar** | `/admissions/approvals`<br>`/admissions/offers` | Formal authorization of admission batches, signs off letters, sets acceptance deadlines. |
| **Finance Officer** | `/fees/payment-receipt`<br>`/admissions/payments` | Confirms payment clearance, reconciles bank/MPESA transactions, processes waivers. |
| **Academic Registrar** | `/admissions/student-conversions`<br>`/registration/student-registrations` | Executes conversion pipeline, assigns registration numbers, maps cohorts and streams. |
| **Lecturer / Faculty** | `/curriculum/instructor-mapping`<br>`/lms/live-lectures` | Delivers curriculum, registers class attendance, submits continuous assessment and exam marks. |

---

## 3. End-to-End State Machine Reference

Every application transitions through the deterministic state machine enforced by `AdmissionWorkflow.php`:

```
 [ DRAFT ]
    │
    ▼ (Fee Confirmed + Docs Uploaded + Hashed Snapshot)
 [ SUBMITTED ] ──────────────────────────┐
    │                                    │ (Deficiency Found)
    ▼ (Triage Assigned)                  ▼
 [ UNDER_REVIEW ] ────────────▶ [ RETURNED_FOR_CORRECTION ] / [ INFO_REQUESTED ]
    │                                    │
    ▼ (Documents Verified)               ▼ (Re-submitted)
 [ VERIFIED ]                     [ SUBMITTED ]
    │
    ▼ (Rubric Scored)
 [ SHORTLISTED ] ─────────────▶ [ WAITLISTED ] ──▶ (Auto-Promoted) ──┐
    │                                                                │
    ▼ (Senate Ladder)                                                │
 [ APPROVAL_PENDING ]                                                │
    │                                                                │
    ▼ (Dean Authorization)                                           │
 [ ADMITTED ] ◀──────────────────────────────────────────────────────┘
    │
    ▼ (Applicant Signs Offer with Forensic Evidence)
 [ ACCEPTED ] ──▶ [ READY_TO_ENROL ]
    │
    ▼ (Finance Cleared + Conversion Service Executed)
 [ ENROLLED ] ──▶ [ ACTIVE STUDENT IN CLASSROOM ]
```

---

## Stage 1: Public Intake & Application Submission

### 1.1 Account Provisioning & Intake Selection
1. The prospective candidate navigates to the public portal at `/admissions/my-application`.
2. The user signs up with their full name, email address, phone number, and national ID/passport number.
3. System assigns the `applicant` stakeholder role (`user_stakeholder_types`).
4. The applicant selects an open academic intake (e.g., *September 2026 Main Intake*) and desired academic programme (e.g., *Bachelor of Science in Computer Science* via `programme_offerings`).

### 1.2 Multi-Step Application Completion
The applicant completes all mandatory sections on `/applicant/dashboard`:
* **Personal Biodata:** Legal names, gender, date of birth, nationality, county of origin, emergency contacts.
* **Academic History:** Primary and secondary schooling, KCSE index number/year, previous tertiary institutions.
* **Document Repository:** Digital uploads of National ID, KCSE Certificate/Result Slip, Passport Photo, and Leaving Certificate (`ApplicationDocument`).
* **Declarations:** Acceptance of institutional rules and academic honesty pledge.

### 1.3 Application Fee Payment & Cryptographic Submission
1. The system generates a fee invoice for **KES 1,000**.
2. Payment is executed via MPESA STK Push or designated banking slip.
3. Once the payment status confirms as `PAID` (`ApplicationPaymentAttempt`), the `Submit Application` button activates.
4. On submission:
   * System generates an immutable JSON snapshot of all fields and files.
   * Computes a **SHA-256 checksum** stored in `application_versions`.
   * Assigns a formal receipt number: `MC-REC-YYYY-XXXXX`.
   * State transitions: `DRAFT` $\rightarrow$ `SUBMITTED`.
   * Auto-assigns the record to the Triage Work Queue (`AdmissionPipeline::STAGE_TRIAGE`).

---

## Stage 2: Admissions Office Triage & Document Verification

**Primary Workspace:** `/admissions/document-verification` & `/admissions/work-queues`

### 2.1 Desk Assignment
* The Admissions Triage Officer opens the live work queue.
* The system displays all newly submitted applications sorted by intake priority and submission timestamp.
* Clicking **Claim File** locks the application to the officer's desk to prevent dual-processing.

### 2.2 Forensic Document Verification
1. The officer clicks **Inspect Dossier** to view uploaded certificates alongside applicant biodata.
2. For each document:
   * **Verify Authenticity:** Cross-check KCSE mean grade and subject cluster points against programme prerequisites (e.g., Mathematics `B+`, Physics `B`, English `C+`).
   * Click **Mark Verified** (records verification timestamp and officer ID in `document_verifications`).
3. **Handling Discrepancies:**
   * If a document is blurry or wrong, the officer selects **Request Correction** or **Request Additional Info**.
   * Status transitions to `RETURNED_FOR_CORRECTION` or `INFO_REQUESTED`.
   * System fires an automated notification to the applicant. The applicant modifies the draft and re-submits.
4. **Advancement:**
   * Once all mandatory documents satisfy institutional standards, the officer clicks **Verify Application**.
   * Status transitions: `SUBMITTED` / `UNDER_REVIEW` $\rightarrow$ `VERIFIED`.
   * The pipeline automatically moves the file to **Departmental Review**.

---

## Stage 3: Departmental Scoring, Review & Shortlisting

**Primary Workspace:** `/admissions/reviews` & `/admissions/shortlists`

### 3.1 Faculty Rubric Scoring
* The Head of Department (HOD) or designated Academic Committee opens `/admissions/reviews`.
* The committee scores candidates against the Senate-approved **Scoring Rubric** (`ScoringRubric`):
  * Academic Weight (Mean Grade & Cluster Subjects): *0 – 50 Points*
  * Prerequisite Subject Alignment: *0 – 30 Points*
  * Special Talents / Affirmative Action / Diversity: *0 – 20 Points*
* System calculates total composite score.

### 3.2 Shortlisting & Waitlisting
1. **Shortlist Action:** Candidates meeting or exceeding the intake cutoff score are moved to `SHORTLISTED`.
2. **Waitlist Action:** Candidates who qualify academically but exceed current programme seat capacity are assigned to `WAITLISTED` with a ranked queue position (`/admissions/waitlists`).
3. If an admitted candidate subsequently declines their offer, the Admissions Officer clicks **Auto-Promote Waitlist** (`/admissions/waitlists/auto-promote`) to advance the top waitlisted applicant to `SHORTLISTED`.

---

## Stage 4: Institutional Approvals & Offer Letter Issuance

**Primary Workspace:** `/admissions/approvals` & `/admissions/offers`

### 4.1 Senate Approval Ladder
1. The Dean of Faculty and Academic Registrar access the Approval Board interface at `/admissions/approvals`.
2. Applications are presented in batch schedules grouped by School and Programme.
3. The authorizing official clicks **Authorize Batch** or **Sign-Off Application**.
4. Status transitions: `SHORTLISTED` $\rightarrow$ `APPROVAL_PENDING` $\rightarrow$ `ADMITTED`.

### 4.2 Automated Admission Letter & Token Generation
Upon transitioning to `ADMITTED`, MEMA ERP automatically executes `issueOffer()`:
* **Offer Number Allocation:** Generates unique institutional code (e.g. `MC/ADM/202609/A8F19B20`).
* **Cryptographic QR Token:** 48-character high-entropy token (`verification_token`) and SHA-256 checksum.
* **Formal Letter:** Renders PDF Admission Letter containing reporting dates, fee payable schedule, medical forms, and campus orientation details.
* **Expiry Clock:** System locks the deadline according to `AdmissionIntake::acceptance_deadline`.

---

## Stage 5: Offer Response, Acceptance & Legal Commitment

**Primary Workspace:** `/applicant/applications/{id}/offer`

### 5.1 Applicant Offer Review
1. The applicant logs in and receives an on-screen banner: *"Congratulations! You have received an Admission Offer"*.
2. The applicant downloads and reviews the official Admission Letter and Fee Schedule.

### 5.2 Forensic Acceptance Signing
1. The applicant clicks **Accept Offer** (or *Decline* / *Request Deferral*).
2. The system presents the formal Student Code of Conduct and Fee Payment Agreement.
3. Upon confirming acceptance, the system captures **non-repudiation forensic evidence**:
   * Legal terms version agreed to (`terms_version: 2026.1`)
   * Originating client IP address (e.g., `197.232.14.88`)
   * Browser User-Agent string
   * Exact UTC timestamp
   * SHA-256 evidence hash stored in `offer_responses`
4. The application transitions: `ADMITTED` $\rightarrow$ `ACCEPTED` $\rightarrow$ `READY_TO_ENROL`.

---

## Stage 6: Finance Clearance & Fee Reconciliation

**Primary Workspace:** `/fees/payment-accounts`, `/fees/pending-payments` & `/admissions/payments`

### 6.1 Semester Tuition Invoicing
* When the application reaches `READY_TO_ENROL`, the Finance Module generates the Year 1 Semester 1 student fee invoice based on the programme fee template (`PaymentFeeSetup`).
* Fees include: Tuition, Caution Money, Student ID Card, Library Levy, Medical Examination, and Activity Fee.

### 6.2 Payment Execution & Verification
1. The applicant/sponsor pays the required minimum first-installment deposit (e.g., 50% or 100% of semester fee) via University Bank Accounts or Paybill.
2. The Finance Officer validates the transaction on `/fees/payment-receipt`.
3. For sponsored or scholarship students, the Finance Officer uploads the sponsorship letter and issues a Fee Waiver (`/admissions/payments/waiver`).
4. Once cleared, the finance status reflects **Cleared for Academic Registration**.

---

## Stage 7: Student Conversion & Permanent Identity Materialization

**Primary Workspace:** `/admissions/student-conversions` or Action `/admissions/applications/{id}/convert`

This is the critical architectural bridge where an external candidate becomes an official university student.

```
       ┌─────────────────────────────────────────────────────────┐
       │                ADMISSION APPLICATION                    │
       │  (Application No: MC-APP-2026-0812 | Status: READY)     │
       └─────────────────────────────────────────────────────────┘
                                    │
                                    ▼
       ┌─────────────────────────────────────────────────────────┐
       │             StudentConversionService::convert()         │
       │  1. Row lock on application & student_conversions       │
       │  2. Fetch linked User account (ID: 412)                 │
       │  3. Increment Course next_student_serial atomically     │
       │  4. Format Admission Number: BCS/042/2026               │
       │  5. Insert into students table                          │
       │  6. Elevate User role: 'applicant' -> 'student'         │
       │  7. Add 'student' to user_stakeholder_types             │
       │  8. Audit log conversion completion & payload           │
       └─────────────────────────────────────────────────────────┘
                                    │
                                    ▼
       ┌─────────────────────────────────────────────────────────┐
       │                     PERMANENT STUDENT                   │
       │  Name: Jane Doe | Reg No: BCS/042/2026 | Role: Student  │
       └─────────────────────────────────────────────────────────┘
```

### 7.1 Automated Conversion Steps
1. The Registrar clicks **Complete Enrolment** (or triggers batch conversion).
2. `StudentConversionService` locks the database row to guarantee idempotency.
3. System fetches the course sequence serial and constructs the **Permanent Student Admission Number**:
   $$\text{Registration No} = \text{Course Code} + \text{"/"} + \text{Serial (3 Digits)} + \text{"/"} + \text{Year}$$
   *(Example: `BCS/042/2026` for Bachelor of Computer Science, Candidate #42, Year 2026)*.
4. A new record is inserted into `students` table, linking `user_id`, `course_id`, `admission_number`, and current `academic_session_id`.
5. The `User` record is updated:
   * Role upgraded: `role = 'student'`
   * Stakeholder type mapped: `user_stakeholder_types` $\rightarrow$ `student`
   * Student portal credentials activated.
6. Application status transitions to `ENROLLED`.

---

## Stage 8: Cohort Mapping, Stream Allocation & Unit Enrollment

**Primary Workspace:** `/cohort/programme-cohort-mapping`, `/curriculum/student-specialization-mapping` & `/registration/student-registrations`

### 8.1 Cohort & Stream Allocation
1. The Academic Registrar navigates to `/cohort/programme-cohort-mapping`.
2. The newly converted student is mapped to the active academic cohort:
   * **Academic Year:** `2026/2027`
   * **Stage / Year of Study:** `Year 1 Semester 1 (Y1S1)`
   * **Class Stream / Section:** Stream `A` (Main Campus).
3. If the programme includes specializations (e.g. *Software Engineering* vs *Networks*), mapping is finalized in `/curriculum/student-specialization-mapping`.

### 8.2 Course Unit Registration
1. The student logs into their newly minted Student Portal or the Registrar opens `/registration/student-registrations`.
2. System loads the approved curriculum for Y1S1 (`AcademicCourseUnit`):
   * `BCS 111` — Introduction to Programming (*3.0 Credit Hours*)
   * `BCS 112` — Discrete Mathematics (*3.0 Credit Hours*)
   * `BCS 113` — Computer Architecture & Organization (*3.0 Credit Hours*)
   * `BCS 114` — Communication Skills for IT (*2.0 Credit Hours*)
   * `BCS 115` — Fundamentals of Information Systems (*3.0 Credit Hours*)
3. Student clicks **Confirm Unit Registration**.
4. The system validates prerequisites, checks fee compliance threshold, and locks unit enrollment for the semester.

---

## Stage 9: Classroom Readiness, LMS Sync & Academic Attendance

**Primary Workspace:** `/registration/moodle-sync`, `/lms/course-shells`, `/lms/live-lectures` & `/examination/exam-schedule`

### 9.1 LMS & Virtual Learning Provisioning
1. The System Administrator opens `/registration/moodle-sync` or automated cron fires.
2. The synchronization engine creates the student account in the Learning Management System (LMS) with single-sign-on (SSO) credentials.
3. The student is enrolled into the active course shells (`/lms/course-shells`) corresponding to registered units.
4. The student gains instant access to:
   * Lecture Notes, Syllabi & E-Resources (`/lms/e-resources`)
   * Assignment Submission Portals (`/lms/assignments`)
   * Virtual Lecture Video Links (`/lms/live-lectures`)

### 9.2 Lecture Attendance & Timetable Integration
1. The timetable is published with allocated lecture halls, lab sessions, and assigned lecturers (`/curriculum/instructor-mapping`).
2. Lecturers take attendance per session (`AttendanceRecord`).
3. Students must achieve the minimum institutional attendance requirement (typically $\ge 75\%$) to be eligible for final semester examinations.

### 9.3 Semester Assessment & Examination
1. At the close of the teaching period, the Examination Office publishes the timetable (`/examination/exam-schedule`).
2. Exam center seat allocations and digital exam cards are generated for students in good standing (`/examination/exam-center`).
3. Lecturers capture continuous assessment (CAT) and final exam marks on `/examination/marks-capture`.
4. Results undergo departmental board approval (`/examination/marks-approval`), Senate ratification, and official transcript generation (`/examination/academic-transcript`).

---

## 13. Auditing, Forensics & Compliance Monitoring

Every transaction across this 9-stage lifecycle is permanently logged in the PostgreSQL enterprise audit ledger:

* **Live Audit Trail:** Viewable at `/reports/audit-trail-user` and `/admissions/audit`.
* **Captured Forensic Metadata:**
  * Exact User ID & Stakeholder Role (`actor_user_id`, `actor_role`)
  * Originating IP Address & Client Device User-Agent
  * Execution Channel (`Web Portal`, `Admin Workspace`, `REST API`, `CLI Artisan`)
  * SHA-256 Before/After state change hashes
  * Tamper verification status (`Integrity PASS`)

---

## 14. Exception Handling & Troubleshooting Directory

| Symptom / Issue | Root Cause | Resolution Action |
|:---|:---|:---|
| **Applicant cannot submit application** | Payment unconfirmed or completion $< 100\%$ | Verify on `/admissions/payments` that KES 1,000 payment has `PAID` status. Ensure all required documents are attached. |
| **Admission offer expired** | Applicant did not respond before `acceptance_deadline` | Admissions Officer opens `/admissions/offers`, selects applicant, and updates `expires_at` date or re-issues offer letter. |
| **Student Conversion Fails with Error** | Missing programme course mapping or user conflict | Navigate to `/admissions/student-conversions`. Inspect `failure_reason`. Ensure programme offering has valid `course_id`. Click **Retry Conversion**. |
| **Duplicate Student ID Risk** | Concurrent enrolment attempts | The system uses database `lockForUpdate()` and idempotent keys (`student-conversion:{app_id}`). Safe to re-run. |
| **Student cannot see course units** | Cohort mapping missing or units not published | Go to `/cohort/programme-cohort-mapping` and link course to current academic year. Then run unit registration. |
| **Student cannot access LMS** | Moodle Sync queue pending | Go to `/registration/moodle-sync` and click **Trigger Manual Sync** to provision course shells immediately. |

---

*MEMA ERP Enterprise Academic & Governance Platform — Kenya's Premier Higher Education Management Suite.*
