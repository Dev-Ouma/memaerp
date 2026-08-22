# MOD-00: PLATFORM ADMINISTRATION, CONFIGURATION & GOVERNANCE — SOFTWARE REQUIREMENTS SPECIFICATION

- **Module Identifier:** `MOD-00` (Platform Master Umbrella)
  - `MOD-00-01`: Identity, Authentication, Authorization & Security
  - `MOD-00-02`: Workflow Engine, Task Management & Approval Matrix
  - `MOD-00-03`: System Configuration, Governance & UI Branding
  - `MOD-00-04`: System Control Centre, Operations & Audit Governance
  - `MOD-00-05`: Network Governance, Impersonation & OAuth 2.0 Authorization Server
- **Official Name:** Platform Administration, Configuration & Governance
- **Implementation Phase:** `PHASE 01 - Foundation & Core Student Lifecycle`
- **System:** MEMA ERP
- **Document Version:** 6.0.0-ENTERPRISE-MAXIMUM
- **Status:** Approved Baseline / Ready for Implementation

---

## 1. Module Number, Identifier and Domain
- **Module ID:** `MOD-00` (Platform Master Root)
- **Official Name:** Platform Administration, Configuration & Governance
- **Domain:** Foundation & Global Platform Infrastructure — Sits **above** all 55+ university operational modules.

```
                                  MEMA ERP
                                     │
            ┌────────────────────────┴────────────────────────┐
            │                                                 │
   MOD-00 PLATFORM ADMINISTRATION                        ERP MODULES
   & GOVERNANCE UMBRELLA                               (Admissions, Courses, Exams,
   ├── MOD-00-01: Identity & Security                   Finance, HR, Library, etc.)
   ├── MOD-00-02: Workflow & Task Engine                       │
   ├── MOD-00-03: System Config & Governance                   │
   ├── MOD-00-04: Operations & System Control                   │
   └── MOD-00-05: Network, OAuth2 & Impersonation              │
            │                                                 │
            └────────────────────────┬────────────────────────┘
                                     │
                              CENTRAL PLATFORM
                              SERVICES & RBAC
                                     ↓
                            ENFORCED EXECUTION
```

## 2. Phase & Implementation Order
- **Phase:** PHASE 01 - Foundation & Core Student Lifecycle
- **Implementation Sequence:** Step 00 / Critical Path — All 55+ university operational modules consume identity, RBAC, workflows, configurations, templates, security policies, OAuth 2.0 servers, and audit telemetry from `MOD-00`.

## 3. Purpose and Objectives
Provides a centralized, cross-cutting platform foundation for identity governance, multi-identifier authentication, fine-grained Role-Based Access Control (RBAC), multi-tenant organisational scoping, session management (`__Host-ERPSESSION`), open-redirect protection, workflow and task engines, configurable multi-tier approval matrices, dynamic institutional setup, academic calendar configuration, general system settings, dynamic UI branding with automatic year update (`© {CURRENT_YEAR} {INSTITUTION_NAME}`), versioning, maintenance and emergency lockdown modes, template engine, central audit telemetry, OAuth 2.0 / OIDC Authorization Server, Network IP Whitelisting, Administrative Break-Glass Impersonation, WebAuthn/FIDO2 hardware keys, periodic access recertification, and the System Control Centre.

> **Key Architectural Principle:** Platform administration, IAM, workflows, system configurations, and security governance are **centrally managed in MOD-00**. Individual operational modules (Admissions, Finance, Exams) consume these centralized platform services rather than independently building isolated settings or role checkers.

---

## 4. Master Module Structure (MOD-00-01 to MOD-00-05)

`MOD-00` is structured into 5 core administrative modules comprising 50 dedicated sub-modules:

```
MOD-00 PLATFORM ADMINISTRATION, CONFIGURATION & GOVERNANCE

┌─────────────────────────────────────────────────────────────────────────┐
│ MOD-00-01: IDENTITY, AUTHENTICATION, AUTHORIZATION & SECURITY          │
├─────────────────────────────────────────────────────────────────────────┤
│ 00.01 User Accounts & Provisioning (Applicant, Student, Staff, Alumni)  │
│ 00.02 User Profile Governance (Bio-data linking, credential slips)       │
│ 00.03 Account Status Management (Pending, Active, Locked, Suspended)     │
│ 00.04 Multi-Identifier Authentication (Student/Staff No, Email, User)   │
│ 00.05 Password Policy & Argon2id Engine (12-char min, history)          │
│ 00.06 Password Reset Workflow (OWASP generic response, 15-min token)    │
│ 00.07 MFA / Google Authenticator (RFC 6238 TOTP, QR generation)         │
│ 00.08 Emergency Backup Codes (10 hashed single-use codes)              │
│ 00.09 Account Recovery Engine (Audited MFA reset, verification)         │
│ 00.10 Session Management & Cookies (__Host-ERPSESSION, rotation, kill)  │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ MOD-00-02: WORKFLOW ENGINE, TASK MANAGEMENT & APPROVAL MATRIX          │
├─────────────────────────────────────────────────────────────────────────┤
│ 00.11 Centralized Task Engine (Queues, Assignments, SLAs, Comments)    │
│ 00.12 Workflow Engine (Sequential, Parallel, Conditional, Rejections)   │
│ 00.13 Configurable Approval Matrix (Admissions, Finance, Marks, Exams)  │
│ 00.14 Delegation Management (Acting appointments, auto-expiry)         │
│ 00.15 Task History & Escalation Telemetry (Audit logs, SLA breaches)    │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ MOD-00-03: SYSTEM CONFIGURATION, GOVERNANCE & UI BRANDING               │
├─────────────────────────────────────────────────────────────────────────┤
│ 00.16 University / Institution Setup (Main Campus, Schools, Depts)      │
│ 00.17 Organisational Structure & Cost Centres                           │
│ 00.18 Academic Calendar Configuration (Years, Semesters, Intakes)       │
│ 00.19 General System Settings (Timezones, Currencies, Support Emails)   │
│ 00.20 Dynamic Branding & UI Configuration (Logos, Colors, Seals)        │
│ 00.21 Dynamic Footer Engine (© {CURRENT_YEAR} {INSTITUTION_NAME})       │
│ 00.22 System & Build Versioning (ERP Version, Database/API versions)    │
│ 00.23 Configuration Versioning & Academic Rule Change Tracking          │
│ 00.24 System Maintenance Mode (Full & Selective Module Maintenance)    │
│ 00.25 Emergency System Lockdown & Examination Data Lock                 │
│ 00.26 Central Notification & Template Engine (Email, SMS, Handlebars)   │
│ 00.27 Feature Flags & Secrets Management (AES-256 encrypted secrets)    │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ MOD-00-04: SYSTEM CONTROL CENTRE, OPERATIONS & AUDIT GOVERNANCE         │
├─────────────────────────────────────────────────────────────────────────┤
│ 00.28 System Control Centre (Super Admin Dashboard & Controls)         │
│ 00.29 Central Audit Center & Telemetry Viewer (Filterable audit logs)   │
│ 00.30 System Health Telemetry (CPU, DB, Redis, M-PESA, LMS, SSO)        │
│ 00.31 Backup & Recovery Configuration (RPO/RTO, scheduled DB backups)   │
│ 00.32 Data Import & Export Engine (Bulk CSV/Excel validation, preview)  │
│ 00.33 Integration Registry & API Gateway (LMS, KUCCPS, CUE, Biometrics) │
│ 00.34 Scheduled CRON Job Manager (Job logs, execution rules)            │
│ 00.35 Asynchronous Queue Engine (Email, SMS, Payment queues)            │
│ 00.36 Global Search Engine (RBAC-aware instant university search)       │
│ 00.37 System Documentation, Info & About Page                           │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│ MOD-00-05: NETWORK SECURITY, IMPERSONATION & OAUTH2 AUTHORIZATION      │
├─────────────────────────────────────────────────────────────────────────┤
│ 00.38 IP Security & Network Subnet Governance (Campus IP Whitelisting)  │
│ 00.39 Break-Glass Admin Impersonation ("Log in as User" Audit Protocol) │
│ 00.40 OAuth 2.0 & OpenID Connect Authorization Server (PKCE & Scopes)   │
│ 00.41 WebAuthn / FIDO2 Hardware Security Key Gateway (YubiKey/TouchID)  │
│ 00.42 Automated Periodic Access Recertification Engine (90-day decay)  │
│ 00.43 Data Residency & Security Headers (KDPA 2019, CSP Level 3, HSTS)  │
│ 00.44 Emergency Offline Cashier Mode Protocol (POS offline sync engine) │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 5. MOD-00-01: Identity, Authentication & Access Control

### 5.1 Architectural Separation: Modules vs Roles vs Permissions vs Scopes
To avoid embedding hard-coded role checks inside business modules, MEMA ERP strictly decouples four distinct concepts:

```
USER ──(assigned to)──► ROLE ──(scoped by)──► ORGANISATIONAL SCOPE
                          │
                     (contains)
                          │
                          ▼
                     PERMISSIONS ──(defined as)──► MODULE + RESOURCE + ACTION
```

### 5.2 Standard Permission Actions
Every module exposes resources that support a standardized set of actions:

| Action Code | Action Name | Description |
|---|---|---|
| `VIEW` | Read / Inspect | View records, lists, dashboards, or details |
| `CREATE` | Create / Initiate | Draft new record or transaction |
| `EDIT` | Modify / Update | Edit existing unsubmitted records |
| `DELETE` | Soft Delete / Remove | Soft-delete records (subject to audit) |
| `SUBMIT` | Submit for Review | Forward drafted record to next workflow stage |
| `VERIFY` | Verify / Check | Quality check or preliminary verification |
| `APPROVE` | Formal Approval | Stage approval (HoD, Dean, Registrar, Director) |
| `REJECT` | Return / Reject | Reject submission with formal audit reason |
| `PUBLISH` | Final Publish | Make visible to students/public (e.g. results, timetables) |
| `EXPORT` | Data Export | Export records to CSV, Excel, or JSON |
| `PRINT` | Official Print | Generate official watermarked PDF slips/reports |
| `CONFIGURE` | System Setup | Set academic policies, rules, and parameters |

### 5.3 Role Families & Normalized Role Directory
Roles are centrally managed in `MOD-00-01` and normalized into 11 **Role Families**:

| Role Code | Role Name | Role Family | Default Hierarchy | Mandatory MFA | Default Scope Level |
|---|---|---|---|---|---|
| `VC` | Vice Chancellor | Executive | Level 1 | YES | `GLOBAL` |
| `VC_DESIGNEE` | Vice Chancellor Designee | Executive | Level 1 | YES | `GLOBAL` |
| `DVC_ACADEMIC` | Deputy Vice Chancellor (Academic & Student Affairs) | Executive | Level 2 | YES | `GLOBAL` |
| `DVC_FINANCE` | Deputy Vice Chancellor (Finance & Administration) | Executive | Level 2 | YES | `GLOBAL` |
| `DVC_RESEARCH` | Deputy Vice Chancellor (Research & Innovation) | Executive | Level 2 | YES | `GLOBAL` |
| `DEAN` | Dean of Faculty / School | Executive | Level 3 | YES | `FACULTY` |
| `DEPUTY_DEAN` | Deputy Dean | Executive | Level 4 | YES | `FACULTY` |
| `DIRECTOR` | Institute / Campus Director | Executive | Level 3 | YES | `CAMPUS` |
| `REGISTRAR` | Academic Registrar | Academic Admin | Level 2 | YES | `GLOBAL` |
| `DEPUTY_REGISTRAR` | Deputy Academic Registrar | Academic Admin | Level 3 | YES | `GLOBAL` |
| `ADMISSIONS_OFFICER` | Admissions Officer | Academic Admin | Level 5 | YES | `GLOBAL` |
| `GRAD_SCHOOL_ADMIN` | Graduate School Administrator | Academic Admin | Level 4 | YES | `GLOBAL` |
| `HOD` | Head of Department (HoD) | Academic Admin | Level 4 | YES | `DEPARTMENT` |
| `PROG_COORDINATOR` | Programme Coordinator | Academic Admin | Level 5 | NO | `PROGRAMME` |
| `ACAD_ADVISOR` | Academic Advisor / Mentor | Academic Admin | Level 6 | NO | `PROGRAMME` |
| `INSTRUCTOR` | Academic Lecturer / Instructor | Academic Admin | Level 6 | NO | `COURSE` |
| `TRAINER` | Skills & Workshop Trainer | Academic Admin | Level 7 | NO | `COURSE` |
| `EXAM_OFFICER` | University Examination Officer | Examination | Level 3 | YES | `GLOBAL` |
| `EXAM_ADMIN` | Examination Administrator | Examination | Level 4 | YES | `GLOBAL` |
| `EXAM_COORDINATOR` | Faculty/Dept Exam Coordinator | Examination | Level 5 | YES | `FACULTY` / `DEPARTMENT` |
| `EXAM_EXAMINER` | Internal / External Examiner | Examination | Level 6 | YES | `COURSE` |
| `MARKS_PROCESSOR` | Central Marks Processing Officer | Examination | Level 5 | YES | `GLOBAL` |
| `RESULTS_OFFICER` | Results Compilation & Transcripts Officer | Examination | Level 5 | YES | `GLOBAL` |
| `EXAM_BOARD_SEC` | Senate Examination Board Secretary | Examination | Level 4 | YES | `GLOBAL` |
| `HEAD_OF_FINANCE` | Chief Financial Officer / Head of Finance | Finance | Level 2 | YES | `GLOBAL` |
| `FINANCE_OFFICER` | Senior Finance Officer | Finance | Level 3 | YES | `GLOBAL` |
| `STUDENT_FIN_ACCT` | Student Finance Accountant | Finance | Level 5 | YES | `GLOBAL` |
| `PAYMENTS_ACCT` | Payments & Disbursements Accountant | Finance | Level 5 | YES | `GLOBAL` |
| `BUDGET_ACCT` | Budget & Planning Accountant | Finance | Level 5 | YES | `GLOBAL` |
| `BUDGET_OFFICER` | Budget Control Officer | Finance | Level 5 | YES | `GLOBAL` |
| `FINANCE_EXAMINER` | Internal Financial Auditor / Examiner | Finance | Level 4 | YES | `GLOBAL` |
| `CASHIER` | University Cashier / Point of Sale | Finance | Level 7 | YES | `CAMPUS` |
| `PROCUREMENT_MGR` | Head of Procurement & Supply Chain | Procurement | Level 3 | YES | `GLOBAL` |
| `PROCUREMENT_OFFICER`| Procurement / Purchasing Officer | Procurement | Level 5 | YES | `GLOBAL` |
| `TENDER_COMMITTEE` | Tender & Evaluation Committee Member | Procurement | Level 4 | YES | `GLOBAL` |
| `APPLICANT` | Prospective Applicant | Student Lifecycle | Level 10 | NO | `SELF` |
| `STUDENT` | Enrolled Undergraduate / Postgrad Student | Student Lifecycle | Level 10 | NO | `SELF` |
| `GRADUATE` | Graduating Candidate | Student Lifecycle | Level 10 | NO | `SELF` |
| `ALUMNI` | University Alumni | Student Lifecycle | Level 10 | NO | `SELF` |
| `DEAN_OF_STUDENTS` | Dean of Students | Student Affairs | Level 3 | YES | `GLOBAL` |
| `STUDENT_AFFAIRS_OFF`| Student Affairs & Welfare Officer | Student Affairs | Level 5 | NO | `GLOBAL` |
| `ACCOMMODATION_OFF` | Hostels & Accommodation Officer | Student Affairs | Level 6 | NO | `CAMPUS` |
| `COUNSELLING_OFF` | Guidance & Counselling Officer | Student Affairs | Level 6 | NO | `GLOBAL` |
| `LIBRARIAN` | University Librarian | Library | Level 3 | YES | `GLOBAL` |
| `ASST_LIBRARIAN` | Assistant Librarian / Cataloguer | Library | Level 5 | NO | `CAMPUS` |
| `ELECTION_COMM` | University Election Commissioner | Governance | Level 3 | YES | `GLOBAL` |
| `RETURNING_OFFICER` | Student Union Election Returning Officer | Governance | Level 4 | YES | `CAMPUS` |
| `SENATE_MEMBER` | Senate Committee Member | Governance | Level 3 | YES | `GLOBAL` |
| `PDC_COORDINATOR` | Professional Development Centre Coordinator | Continuing Ed | Level 4 | YES | `GLOBAL` |
| `SUPER_ADMIN` | Root System & Infrastructure Admin | System Admin | Level 1 | YES | `GLOBAL` |
| `ICT_SECURITY` | ICT Security Officer | System Admin | Level 2 | YES | `GLOBAL` |
| `USER_SUPPORT` | ICT Helpdesk & User Support | System Admin | Level 7 | NO | `GLOBAL` |

### 5.4 Cryptographic Session Management & Token Architecture
- **Opaque Token Standard:** Generated via CSPRNG with at least 128 bits of entropy.
- **Prohibition of Token Data & URL Keys:** Session IDs **must never** contain plain-text user IDs or roles, and **must never be transmitted in URL parameters**.
- **`__Host-` Cookie Standard:**
  ```http
  Set-Cookie: __Host-ERPSESSION=<64-char-random-token>; Secure; HttpOnly; SameSite=Lax; Path=/
  ```
- **Mandatory Session Rotation:** Session ID is invalidated and regenerated after Login, MFA, Password Change, and Role Elevation.
- **Role-Based Idle & Absolute Timeouts:**
  * Super Admin / System Admin / Finance / Exam: **10–15 min idle / 4h max**
  * Academic Registrar / Deans / HoDs: **20 min idle / 8h max**
  * Lecturers / Students / Applicants: **30 min idle / 12h max**
- **Global Session Versioning (`users.session_version`):** Incremented on password change or *"Sign Out Everywhere"* (`POST /api/v1/iam/auth/logout-all`), instantly invalidating all existing sessions.

---

## 6. MOD-00-02: Workflow Engine, Task Management & Approval Matrix

### 6.1 Centralized Task Engine (`00.11`)
Provides a unified task queue for staff and administrators:
- Task fields: `id`, `title`, `assignee_id`, `role_code`, `module_code`, `priority`, `sla_deadline`, `status`, `comments`, `attachments`.

### 6.2 Configurable Multi-Tier Approval Matrix (`00.13`)

| Operational Action | Level 1 Approver | Level 2 Approver | Level 3 Approver | Mandatory Conditions |
|---|---|---|---|---|
| **Student Admission** | Admissions Officer | Academic Registrar | — | Document verification clean |
| **Mark Change / Grade Edit** | Lecturer / Instructor | Head of Department | Academic Registrar | Audit reason required |
| **Results Publication** | Dept Exam Coordinator | Faculty Exam Board | Senate Board / Registrar | Step-up MFA required |
| **Fee Waiver / Concession** | Student Fin Accountant | Head of Finance | Vice Chancellor | Dual sign-off > $1,000 |
| **Graduation List** | School Board / Dean | Academic Registrar | Senate Approval | Fee clearance verified |
| **Procurement Requisition** | Requisitioning Officer | Head of Department | Procurement Manager / Tender Comm | Budget check passed |

### 6.3 Delegation Management (`00.14`)
Allows staff (e.g. Dean on leave) to delegate approval authority to a designated deputy:
- Fields: `delegator_id`, `delegate_id`, `start_date`, `end_date`, `permitted_modules`, `reason`, `status`.
- **Mandatory Policy:** Delegations must have an automatic expiration timestamp and emit audit events on exercise.

---

## 7. MOD-00-03: System Configuration, Governance & UI Branding

### 7.1 Institution & Organisational Setup (`00.16`, `00.17`)
Supports multi-campus hierarchical organizational structure:
$$\text{University} \rightarrow \text{Campus} \rightarrow \text{School / Faculty} \rightarrow \text{Department} \rightarrow \text{Programme} \rightarrow \text{Unit}$$

### 7.2 Academic Calendar Configuration (`00.18`)
Central management of Academic Years, Semesters, Trimesters, Intakes, and Operational Windows.

### 7.3 Dynamic Branding & Footer Engine (`00.20`, `00.21`)
- Configurable University Logos, Primary/Secondary Accent Colors, Official Watermarks, and Seals.
- **Dynamic Footer Engine:** Standard copyright footer uses dynamic template interpolation:
  $$\text{Footer Text} = \text{"© \{CURRENT\_YEAR\} \{INSTITUTION\_NAME\}. All Rights Reserved."}$$
  The year automatically updates on January 1st without requiring code changes or deployments.

### 7.4 System Maintenance & Emergency Lockdown Modes (`00.24`, `00.25`)

| Mode | Allowed User Actions | Restricted Functions | Target Audience |
|---|---|---|---|
| **Normal Operational Mode** | Full CRUD per RBAC | None | All Users |
| **Selective Module Maintenance** | Operational in active modules | Maintenance mode on target module (e.g., Finance under maintenance) | Module Users |
| **Restricted Read-Only Mode** | View, Download, Print, Export | `CREATE`, `EDIT`, `DELETE`, `APPROVE`, `PUBLISH` blocked | All End Users |
| **Examination Lockdown** | View published results | Mark entry frozen for Lecturers; HoDs review; Registrar approves | Lecturers / Staff |
| **Emergency System Lockdown** | ❌ All access blocked | Entire application locked | Super Admin Only (Break-Glass) |

---

## 8. MOD-00-04: System Control Centre, Operations & Audit Governance

### 8.1 System Control Centre Dashboard (`00.28`)

```
┌────────────────────────────────────────────────────────────────────────┐
│ MEMA ERP — SYSTEM CONTROL CENTRE                                      │
├────────────────────────────────────────────────────────────────────────┤
│ System Status:      ● OPERATIONAL        Environment:  PRODUCTION      │
│ ERP Version:        v6.0.0-ENTERPRISE    Build ID:     20260821.01     │
│ Active Users:       24,531               Active Sessions: 3,284        │
│                                                                        │
│ System Health:      Database ● OK        Redis Queue ● OK              │
│                     M-PESA   ● OK        LMS Integration ● OK          │
│                     Email    ● OK        SMS Gateway ● OK              │
├────────────────────────────────────────────────────────────────────────┤
│ QUICK EMERGENCY CONTROLS:                                              │
│ [ Enable Maintenance ]  [ Restrict Writes (Read-Only) ]               │
│ [ Exam Marks Lockdown ] [ Emergency Break-Glass Lockdown ]            │
│ [ Kill All Sessions ]   [ View Audit Telemetry ]                       │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 9. MOD-00-05: Network Security, Impersonation & OAuth 2.0 Authorization Server

### 9.1 IP Security & Network Subnet Governance (`00.38`)
High-privileged roles (`HEAD_OF_FINANCE`, `EXAM_OFFICER`, `SUPER_ADMIN`, `CASHIER`) can be configured with strict **IP Subnet Whitelisting** & **Campus VPN Binding**:
- Access from outside designated university subnets or encrypted campus VPNs triggers mandatory Step-Up MFA or explicit denial.

### 9.2 Administrative Impersonation & Break-Glass Protocol (`00.39`)
Allows authorized support engineers or Registrars to troubleshoot issues by temporarily viewing the system as a target user ("Log in as User"):
- **Break-Glass Rules:**
  1. Requires dual-control sign-off from ICT Security Officer or Registrar.
  2. Requires explicit ticket reason (e.g. `TICK-849201`).
  3. Automatically sends real-time email/SMS notification to target user.
  4. Session automatically terminates after **15 minutes**.
  5. Every single action taken during impersonation is logged with a special `IMPERSONATED_BY: <admin_username>` tag.

### 9.3 OAuth 2.0 & OpenID Connect Authorization Server (`00.40`)
MEMA ERP includes a built-in RFC 6749 OAuth 2.0 Authorization Server:
- **Grant Types:** `authorization_code` with PKCE (Mobile Apps), `client_credentials` (System Integrations like LMS, M-PESA, KUCCPS).
- **API Scopes:** `read:student_records`, `write:marks`, `approve:finance`, `read:catalogue`.
- Token Introspection (`RFC 7662`) and Token Revocation (`RFC 7009`) endpoints.

### 9.4 Automated Periodic Access Certification (`00.42`)
- Every **90 days**, Deans and Heads of Department must conduct a digital recertification campaign to approve or revoke active staff role assignments.
- Roles not explicitly certified within 14 days of campaign initiation automatically decay to `FLAG_REVIEW` status.

---

## 10. Comprehensive Relational Database Schema Specification

```sql
-- ============================================================
-- MEMA ERP - MODULE 00: PLATFORM MASTER SCHEMA
-- Schema: iam & platform
-- Version: 6.0.0-ENTERPRISE-MAXIMUM
-- ============================================================

CREATE SCHEMA IF NOT EXISTS iam;
CREATE SCHEMA IF NOT EXISTS platform;

-- 1. System General Settings
CREATE TABLE IF NOT EXISTS platform.system_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    setting_group VARCHAR(50) NOT NULL,
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by UUID REFERENCES iam.users(id),
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 2. System Maintenance & Lockdown State
CREATE TABLE IF NOT EXISTS platform.system_state (
    id INT PRIMARY KEY DEFAULT 1,
    maintenance_mode BOOLEAN DEFAULT FALSE,
    read_only_mode BOOLEAN DEFAULT FALSE,
    lockdown_mode BOOLEAN DEFAULT FALSE,
    exam_lockdown_mode BOOLEAN DEFAULT FALSE,
    maintenance_message TEXT,
    allowed_roles JSONB,
    updated_by UUID REFERENCES iam.users(id),
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT single_row CHECK (id = 1)
);

-- 3. Network IP Whitelist Rules
CREATE TABLE IF NOT EXISTS platform.ip_whitelist (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    role_code VARCHAR(50) NOT NULL,
    ip_subnet VARCHAR(45) NOT NULL, -- e.g. 197.232.0.0/16
    description VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 4. Break-Glass Impersonation Logs
CREATE TABLE IF NOT EXISTS platform.impersonation_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    admin_id UUID NOT NULL REFERENCES iam.users(id),
    target_user_id UUID NOT NULL REFERENCES iam.users(id),
    ticket_reference VARCHAR(100) NOT NULL,
    reason TEXT NOT NULL,
    approved_by UUID REFERENCES iam.users(id),
    started_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMPTZ NOT NULL,
    ended_at TIMESTAMPTZ
);

-- 5. OAuth 2.0 Registered Clients
CREATE TABLE IF NOT EXISTS platform.oauth_clients (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    client_id VARCHAR(100) UNIQUE NOT NULL,
    client_secret_hash VARCHAR(255) NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    grant_types JSONB NOT NULL, -- ["authorization_code", "client_credentials"]
    redirect_uris JSONB NOT NULL,
    scopes JSONB NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 6. Workflow Definitions & Approval Steps
CREATE TABLE IF NOT EXISTS platform.workflows (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    module_code VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS platform.approval_steps (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    workflow_id UUID REFERENCES platform.workflows(id) ON DELETE CASCADE,
    step_order INT NOT NULL,
    step_name VARCHAR(100) NOT NULL,
    approver_role_code VARCHAR(50) NOT NULL,
    requires_step_up_mfa BOOLEAN DEFAULT FALSE,
    auto_escalate_hours INT DEFAULT 48,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 7. Centralized Task Queue
CREATE TABLE IF NOT EXISTS platform.tasks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    title VARCHAR(255) NOT NULL,
    workflow_id UUID REFERENCES platform.workflows(id),
    module_code VARCHAR(50) NOT NULL,
    assignee_id UUID REFERENCES iam.users(id),
    target_role_code VARCHAR(50),
    priority VARCHAR(20) DEFAULT 'MEDIUM',
    sla_deadline TIMESTAMPTZ,
    status VARCHAR(30) DEFAULT 'PENDING',
    payload JSONB,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 8. Role Delegations Table
CREATE TABLE IF NOT EXISTS platform.delegations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    delegator_id UUID NOT NULL REFERENCES iam.users(id),
    delegate_id UUID NOT NULL REFERENCES iam.users(id),
    role_code VARCHAR(50) NOT NULL,
    start_date TIMESTAMPTZ NOT NULL,
    end_date TIMESTAMPTZ NOT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(30) DEFAULT 'ACTIVE',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 9. Role Families Master Table
CREATE TABLE IF NOT EXISTS iam.role_families (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 10. System Roles Table
CREATE TABLE IF NOT EXISTS iam.roles (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    family_id UUID REFERENCES iam.role_families(id) ON DELETE SET NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    hierarchy_level INT DEFAULT 10,
    parent_role_id UUID REFERENCES iam.roles(id),
    is_system BOOLEAN DEFAULT TRUE,
    is_mfa_mandatory BOOLEAN DEFAULT FALSE,
    default_scope_type VARCHAR(30) DEFAULT 'GLOBAL',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 11. Users Security Master Table
CREATE TABLE IF NOT EXISTS iam.users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type VARCHAR(50) NOT NULL DEFAULT 'STUDENT',
    status VARCHAR(30) NOT NULL DEFAULT 'ACTIVE',
    session_version INT NOT NULL DEFAULT 1,
    email_verified_at TIMESTAMPTZ,
    is_active BOOLEAN DEFAULT TRUE,
    failed_login_attempts INT DEFAULT 0,
    locked_until TIMESTAMPTZ,
    last_login_at TIMESTAMPTZ,
    password_changed_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 12. Active User Sessions Table
CREATE TABLE IF NOT EXISTS iam.sessions (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES iam.users(id) ON DELETE CASCADE,
    session_hash VARCHAR(255) UNIQUE NOT NULL,
    session_version INT NOT NULL DEFAULT 1,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    device_name VARCHAR(255),
    mfa_verified BOOLEAN DEFAULT FALSE,
    risk_score INT DEFAULT 0,
    status VARCHAR(30) DEFAULT 'ACTIVE',
    idle_expires_at TIMESTAMPTZ NOT NULL,
    absolute_expires_at TIMESTAMPTZ NOT NULL,
    last_activity_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    revoked_at TIMESTAMPTZ,
    revoked_reason VARCHAR(100),
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);
```

---

## 11. Summary of Architectural Compliance

| Governance Requirement | Platform Implementation Standard |
|---|---|
| **Zero Code Deletions / Full Expansion** | Preserved all prior IAM, RBAC, Session, Config & Governance specs, expanding to 50 sub-modules. |
| **Network IP Subnet Governance** | IP Whitelisting & Campus Subnet Binding (`MOD-00.38`) for Finance/Exams/Admin roles. |
| **Administrative Impersonation** | Break-Glass Protocol (`MOD-00.39`) with dual-control, 15-min auto-kill, and target user alerts. |
| **OAuth 2.0 / OIDC Auth Server** | Built-in Authorization Server (`MOD-00.40`) with PKCE and Client Credentials grants. |
| **WebAuthn / FIDO2 Gateway** | Hardware security key support (`MOD-00.41`) for YubiKey / TouchID authentication. |
| **Periodic Access Recertification** | Automated 90-day role certification campaigns (`MOD-00.42`) with privilege decay. |
