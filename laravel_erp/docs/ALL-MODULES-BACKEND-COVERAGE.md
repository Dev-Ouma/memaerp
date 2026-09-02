# All-Modules Backend Coverage

Audit date: 2026-09-02

This report is a completion gate, not a list of pages that happen to render. A
module is marked operational only when its visible write controls persist data,
server-side authorization and validation are enforced, and automated tests cover
the workflow.

## Current baseline

- Framework: Laravel 13.26.1, PHP 8.5.8, PostgreSQL.
- Database: all 28 versioned migrations are applied.
- Surface: 204 Blade views and 394 registered routes.
- Automated baseline: 110 tests and 609 assertions passed before this audit.
- Admission draft autosave now uses the authenticated Laravel endpoint and
  PostgreSQL; the development mock API has been removed.

## Database-to-frontend verification (2026-09-02)

The integration standard used in this audit is stronger than a successful GET:
each covered workflow writes or seeds a distinctive value in PostgreSQL, loads
the related route through Laravel, and asserts that the same value is rendered
in the response. Empty-state dashboard tests also ensure fabricated fallback
figures do not appear.

| Verified surface | Database-to-response evidence | Automated test |
|---|---|---|
| Executive and role dashboards | Student, staff, admissions, attendance, results and empty-state metrics are read through Eloquent/Query Builder | `ErpWorkflowTest`, `SmhrModuleTest` |
| Admissions dashboard and workspaces | Persisted application numbers, offers, payments, reviews and conversions appear in their pages | `AdmissionModuleTest`, `AdmissionWorkspaceTest`, `AdmissionToStudentJourneyTest` |
| Curriculum | A newly persisted school and course unit are rendered after creation | `CurriculumSchoolCrudTest`, `CurriculumCourseUnitTemplateTest` |
| Academic records | A newly persisted student, course, admission number, results and marks reappear in the frontend | `ErpWorkflowTest`, `ExaminationDatabaseIntegrationTest` |
| Budgeting | Persisted proposal references and descriptions are rendered by the proposals page | `BudgetingModuleTest` |
| Task management | A persisted task reference and title are visible to its database-backed assignee | `TaskManagementModuleTest` |
| SMHR dashboard | Persisted staff department/name and pending leave records render in the dashboard | `SmhrModuleTest` |
| Platform governance and access | Persisted retention, deletion, legal-hold, audit and role-assignment records render in admin pages | `GovernanceAdminTest`, `RecycleBinTest`, `RoleAssignmentTest` |
| System maintenance | Persisted maintenance configuration is rendered and enforced by middleware | `SystemMaintenanceTest` |
| Load balancing | Persisted algorithms, configuration and node identity render in the operations page | `LoadBalancerTest` |

The modules marked **Not backend-complete** below cannot pass this
database-to-response contract yet because they do not have persistent domain
tables or write workflows. Their existing page-level tests prove only route and
template availability and must not be interpreted as database integration.

## Module status

| Module | Persistent write surface | Server authorization | Automated coverage | Status |
|---|---|---|---|---|
| Authentication and account | Profile, password, sessions, files, preferences, reports | Auth middleware, ownership checks | Account feature suite | Operational; external MFA/WebAuthn/Google adapters still require real providers |
| Admissions and applicant portal | Draft, uploads, payments, submission, review, transitions, offers, conversion, enrolment | Ownership, admission permissions, controlled workflow | Admission unit, feature, journey and workspace suites | Operational core; production payment/email/malware adapters require environment integration |
| Curriculum | Schools, departments, programmes, course units | Auth, module middleware, admin delete checks | CRUD feature tests | Partially operational; remaining curriculum screens are client-side prototypes |
| Cohort | Academic-year CRUD | Auth and module middleware | ERP workflow tests | Partially operational; mapping, finance publication and transfer screens are read-only prototypes |
| Academic records | Student and programme create/delete, sequence updates | Auth and admin gates | ERP workflow tests | Partially operational; subjects/results lack write workflows |
| SMHR | Staff, onboarding and leave actions | Auth and module middleware | SMHR feature tests | Partially operational; payroll, appraisal, workload and discipline actions are prototypes |
| Platform governance | Retention, legal holds, recycle-bin actions, audit | Permission gates and dual-control rules | Governance/recycle-bin tests | Operational for implemented controls |
| Access control | Role assignments | Admin/permission gates | Role assignment tests | Operational for implemented controls |
| System maintenance | Lockdown, cache, optimize, backup, rollback, broadcast | Admin system route group | Maintenance tests | Operational for implemented controls; infrastructure adapters remain environment-dependent |
| Load balancing | Strategy, nodes, health checks and simulation | Admin system route group | Load-balancer tests | Operational for implemented controls |
| Student transfers | None | Auth and module middleware only | Page-level feature tests | Not backend-complete |
| Budgeting | Submitter grants, proposal creation and controlled approvals | Ownership checks and administrator approval gates | Budgeting feature suite | Operational for implemented controls |
| Examination | Centers, sessions, schedules, grade scales and marks capture | Administrator configuration and lecturer subject ownership | Examination database integration suite | Partially operational; approval, publication, transcript and senate screens still require persisted workflows |
| Fees | None | Auth only | None | Not backend-complete |
| Graduation | None | Auth only | None | Not backend-complete |
| Imprest | None | Auth only | None | Not backend-complete |
| LMS | None | Auth only | None | Not backend-complete |
| PG Research | None | Auth and module middleware only | None | Not backend-complete |
| Registration | None | Auth only | None | Not backend-complete |
| Enterprise reports | Browser-only filters/alerts | Auth only | None | Not backend-complete |
| Service providers | None | Auth only | None | Not backend-complete |
| Task management | Role templates, SLA bindings, task assignment and controlled status transitions | Administrator configuration plus assignee ownership checks | Task Management feature suite | Operational for implemented controls |
| Work study | None | Auth only | None | Not backend-complete |

## Known non-production controls

The incomplete modules above contain controller-supplied hardcoded arrays,
buttons that only mutate the DOM, and success/export alerts without a write
endpoint. They must not be described as connected merely because the associated
GET page returns HTTP 200.

The account module also contains placeholder implementations for TOTP secrets,
WebAuthn public keys, Google Calendar tokens, and support-ticket delivery. These
need provider-backed implementations before production use.

## Completion sequence

Implement each incomplete module vertically: schema and constraints, model and
service, request validation, policy/permission, routes and controller, existing
Blade control wiring, audit events, feature tests, then end-to-end role tests.
This order preserves the completed UI while preventing another layer of mock or
browser-only behaviour.

No incomplete module in this report should be promoted to operational until all
of those gates pass against PostgreSQL.
