# Frontend Backend Blockers (Codex)

**Updated:** 23 August 2026  
**Owner:** Codex (backend/API)  
**Consumer:** Frontend platform team

These items block full Phase 1 frontend completion. The frontend must not invent endpoints for them.

## Contract & platform

| ID | Blocker | Impact | Required deliverable |
|---|---|---|---|
| B-01 | OpenAPI has `paths: {}` | No generated clients/types/Zod | Annotate Laravel routes; commit generated `openapi.v1.yaml`; CI diff |
| B-02 | Hand-edited `@mema/api-client` / `@mema/types` | Contract drift | Replace with generated artifacts in CI |
| B-03 | JSON login returns bearer token | Breaks ADR-007 cookie-only SPA model | Sanctum cookie session for SPA; no token in JSON responses |

## Auth & security

| ID | Blocker | Impact | Required deliverable |
|---|---|---|---|
| B-04 | No MFA verification routes | MFA page is a shell | `POST /auth/mfa/verify`, enrollment, backup codes |
| B-05 | No password reset routes | Reset page is a shell | Request + confirm reset with enumeration-safe responses |
| B-06 | `/auth/me` shape differs from ADR envelope | Inconsistent error/meta handling | Standard `{ data, meta }` envelope + stable error codes |

## Module APIs still missing

| Module | Missing endpoints | Frontend pages blocked |
|---|---|---|
| MOD-01-05 Admissions | Application submit, upload, review queue | `applicant`, `admin/admissions` |
| MOD-01-07 Registration | `POST` course registration, add/drop | `student/registration` |
| MOD-01-08 Timetable | Student/admin timetable APIs | `student/timetable` |
| MOD-01-09 Finance | Invoices, payments, M-Pesa STK | `student/finance`, `admin/finance` |
| MOD-01-10 Exams | Student-scoped marks list | `student/results` detail rows |
| MOD-01-12 Graduation | Clearance workflow API | `student/clearance` |
| MOD-01-01 IAM | Role/assignment/audit list APIs | `admin/security` |
| MOD-01-02 Master data | Campuses, faculties, calendar admin | Institution setup UI |

## Already available (frontend wired or ready)

- `POST/GET /auth/login`, `/auth/logout`, `/auth/me`
- `GET /curriculum/programmes`
- `GET /courses/`, `/courses/offerings/active`
- `GET /enrollment/students`
- `GET /exams/term-gpas`

## Frontend completion gate

Frontend can declare Phase 1 UI **integration-complete** only when:

1. B-01 through B-03 are closed
2. Every Phase 1 page either calls a documented endpoint or shows an explicit unsupported state
3. Auth flows work end-to-end with cookies on all seven app ports
4. Generated clients are the only API types in the monorepo
