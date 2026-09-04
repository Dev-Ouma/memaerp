# Platform access control

## Canonical catalogue

`PermissionCatalogue` is the reviewed source for permissions, system roles and segregation rules. `RbacCatalogueSeeder` persists it idempotently into `permissions`, `roles` and `role_permissions`. Deployment must run:

```bash
php artisan db:seed --class=Database\\Seeders\\RbacCatalogueSeeder --force
```

The seeder updates canonical metadata and role membership without changing user grants. Existing active users with the legacy `admin` role receive a System Administrator assignment only when that exact assignment is absent.

## Assignment workflow

Role grants store user, role, scope type, optional scope identifier, grantor, grant date, optional expiry and mandatory reason. Expired grants are ignored by `AccessControl`. Grants and revocations create tamper-evident `role.assignment.granted` and `role.assignment.revoked` events.

Controls:

- Only `platform.role.manage` holders may grant or revoke roles.
- Inactive users cannot receive new grants.
- Duplicate active assignments are rejected.
- Narrow scopes require a scope identifier.
- Expiry must be in the future.
- Users cannot revoke their own System Administrator assignment.
- System administration and any role containing segregated permissions cannot be combined on one user.
- The permission catalogue contains no wildcard or implicit administrator bypass.

## Bootstrap and recovery

The canonical seeder is the bootstrap mechanism. If every System Administrator grant is lost, use an audited database recovery procedure to insert one institution-scoped `user_roles` row for the reviewed System Administrator role and an active named user. Record the incident and immediately verify the audit trail and all privileged assignments.

Named desk accounts (created by `StakeholderSeeder` / institutional data seeders) receive catalogue roles from `RbacCatalogueSeeder` when present:

| Email | Role |
|---|---|
| `registrar@mema.ac.ke` | `registrar` |
| `admissions.officer@mema.ac.ke` | `admissions_officer` |
| `finance.officer@mema.ac.ke` / `bursar@mema.ac.ke` | `finance_officer` |
| `curriculum.manager@mema.ac.ke` | `curriculum_manager` |
| `hr.officer@mema.ac.ke` | `hr_officer` |
| `dpo@mema.ac.ke` | `data_protection_officer` |
| `registration.officer@mema.ac.ke` | `registration_officer` |
| `transfers.officer@mema.ac.ke` | `transfers_officer` |
| `lms.manager@mema.ac.ke` | `lms_manager` |
| `graduation.officer@mema.ac.ke` | `graduation_officer` |
| `student.affairs@mema.ac.ke` | `student_affairs_officer` |

Desk grants are skipped when the user already holds `system_administrator` (segregation of duties). System administrators may manage curriculum and SMHR records, but not admission decisions.

Operational desks write through dedicated module POSTs (not a shared `module_records` shim). Each desk route below requires its catalogue manage permission.

Registration → Fees domain (not `module_records`):

| Action | Route | Permission |
|---|---|---|
| Open registration period | `POST registration/course-registration-periods` | `registration.manage` |
| Enrol student in period | `POST registration/course-enrolments` | `registration.manage` |
| Create fee structure | `POST fees/fee-setup` | `fees.manage` |
| Add collection account | `POST fees/payment-accounts` | `fees.manage` |
| Add payment type | `POST fees/payment-types` | `fees.manage` |
| Add funding source | `POST fees/payment-source` | `fees.manage` |
| Record tuition payment | `POST fees/fee-payments` | `fees.manage` |
| Confirm pending payment | `POST fees/fee-payments/{payment}/confirm` | `fees.manage` |

Enrolment with financial gating auto-issues a `fee_invoices` row from the matching active `fee_structures` for the student’s programme.

Registration desk domain writes (`registration.manage`):

| Action | Route |
|---|---|
| KUCCPS placement | `POST registration/kuccps-registration` |
| Promotion decision | `POST registration/promotions` |
| CPD enrolment | `POST registration/professional-development-users` |
| Moodle sync log | `POST registration/moodle-sync` |
| Student info update | `POST registration/student-info-update` |
| Reminder campaign | `POST registration/reminders` |

Transfers desk domain (not `module_records`; requires `transfers.manage`):

| Action | Route |
|---|---|
| Transfer window | `POST transfers/dates-setup` |
| Faculty transfer | `POST transfers/inter-intra` |
| Faculty status | `PATCH transfers/inter-intra/{transfer}/status` |
| Credit transfer | `POST transfers/credit-transfers` |
| Credit status | `PATCH transfers/credit-transfers/{credit}/status` |
| Exemption | `POST transfers/exemptions` |
| Exemption status | `PATCH transfers/exemptions/{exemption}/status` |

Graduation desk domain (not `module_records`; requires `graduation.manage`):

| Action | Route |
|---|---|
| Criteria | `POST graduation/criteria` |
| Clearance node | `POST graduation/clearance-checklist` |
| Finance clearance | `POST graduation/finance-clearance` |
| Grade list entry | `POST graduation/grade-list` |
| Generate list batch | `POST graduation/generate-list` |
| Validate list | `POST graduation/validate-list` |
| Publish list | `POST graduation/publish-list` |
| List report | `POST graduation/list-report` |
| Summary | `POST graduation/summary-list` |
| Certificate template | `POST graduation/certification-setup` |
| Alumni | `POST graduation/alumni-list` |
| Ceremony | `POST graduation/ceremony` |
| Ceremony report | `POST graduation/ceremony-report` |

LMS desk domain (not `module_records`; requires `lms.manage`):

| Action | Route |
|---|---|
| Course shell | `POST lms/course-shells` |
| Lecturer assignment | `POST lms/lecturer-assignments` |
| Live lecture | `POST lms/live-lectures` |
| E-resource | `POST lms/e-resources` |
| Assignment | `POST lms/assignments` |
| Student analytic | `POST lms/student-analytics` |
| Discussion thread | `POST lms/discussion-forums` |
| Online quiz | `POST lms/online-quizzes` |
| Gradebook sync | `POST lms/gradebook-sync` |

Imprest desk domain (not `module_records`; requires `imprest.manage`, e.g. `finance_officer`):

| Action | Route |
|---|---|
| Permission tier | `POST imprest/permissions` |
| Claim approval matrix | `POST imprest/claim-approval-permission` |
| Surrender rule | `POST imprest/imprest-surrender-permission` |
| Requisition | `POST imprest/requisitions` |
| Surrender | `POST imprest/surrenders` |
| Audit ledger | `POST imprest/audit-ledger` |

Work-study desk domain (not `module_records`; requires `student_affairs.manage`):

| Action | Route |
|---|---|
| Period | `POST work-study/period-setup` |
| Position | `POST work-study/positions` |
| Application | `POST work-study/applications` |
| Allocation | `POST work-study/allocations` |
| Timesheet | `POST work-study/timesheets` |
| Claim | `POST work-study/claims` |

Service providers desk domain (not `module_records`; requires `service_providers.manage`):

| Action | Route |
|---|---|
| Tax | `POST service-providers/taxes` |
| Item | `POST service-providers/items` |
| Provider group | `POST service-providers/provider-groups` |
| Provider | `POST service-providers/providers` |
| Vendor approval | `POST service-providers/vendor-approval` |
| Invoice permission | `POST service-providers/invoice-permissions` |
| Bill | `POST service-providers/bills` |
| Payment permission | `POST service-providers/payment-permissions` |
| Payment | `POST service-providers/payments` |
| Debit note | `POST service-providers/debit-notes` |
| Credit note | `POST service-providers/credit-notes` |

SMHR desk domain (not `module_records`; requires `smhr.staff.manage` except leave actions):

| Action | Route | Permission |
|---|---|---|
| Staff directory | `POST smhr/staff-directory` | `smhr.staff.manage` |
| Onboarding | `POST smhr/onboarding` | `smhr.staff.manage` |
| Workload | `POST smhr/workload-allocation` | `smhr.staff.manage` |
| Appraisal | `POST smhr/performance-appraisals` | `smhr.staff.manage` |
| Payroll row | `POST smhr/payroll-register` | `smhr.staff.manage` |
| Variance report | `POST smhr/reports` | `smhr.staff.manage` |
| Disciplinary | `POST smhr/disciplinary-records` | `smhr.staff.manage` |
| Leave submit | `POST smhr/leave-management` | `smhr.leave.submit` |
| Leave approve/reject | `POST smhr/leave-management/{id}/approve\|reject` | `smhr.leave.approve` |

Cohort creation persists to `institution_cohorts` via `POST cohort/cohort-creation` (authenticated cohort module access). Mapping / publish-finance / transfer screens render empty until dedicated domain tables exist.
