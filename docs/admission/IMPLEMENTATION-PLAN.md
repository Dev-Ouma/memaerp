# Admission Module — Backend Implementation Plan

**Owner:** Backend / data / integration / backend-security
**Application:** `laravel_erp` (Laravel 13, PHP 8.3+, PostgreSQL 16/17)
**Status:** in delivery

---

## 1. Stack decision

The repository already contains a running backend. Per the brief ("preserve the current backend and
ORM stack when one exists") the module is built on the existing stack rather than a new NestJS service:

| Concern | Decision |
|---|---|
| Framework | Laravel 13 (existing) |
| Language | PHP 8.3+ (CI 8.4, local 8.5) |
| ORM | Eloquent (existing) |
| Database | PostgreSQL (already configured — `DB_CONNECTION=pgsql`) |
| Cache / locks | Laravel cache store (database locally, Redis in production via `CACHE_STORE=redis`) |
| Queue | Laravel queue (`database` locally, Redis/SQS in production) |
| Object storage | Laravel filesystem — private `admissions` disk; S3-compatible in production |
| Contract | OpenAPI 3.1 (`docs/api/admission-openapi.yaml`) |
| Tests | PHPUnit 12 against **PostgreSQL** (production parity) |

No new Composer dependency is introduced. PDF, XLSX and CSV writers are implemented in-tree so that
document generation and exports do not depend on packages that may be unavailable in a locked
environment. This is recorded as a deliberate trade-off in `BACKEND_GAPS.md`.

## 2. Schema strategy — evolve, never fork

`laravel_erp` already owns `admission_applications`, `applicant_profiles`, `programme_offerings`,
`admission_intakes`, `application_documents`, `application_payment_attempts`, `application_versions`,
`application_status_history`, `application_reviews` and `admission_offers` in the `public` schema, and a
Blade portal reads them.

Creating a parallel `admission.*` schema would fork the source of truth for a live table set and break the
existing portal. Therefore the plan is **additive evolution**:

1. Existing tables are extended with nullable/defaulted columns — never dropped, never renamed.
2. The ~50 new domain tables are created alongside them in `public`.
3. Legacy columns that are superseded (for example `application_payment_attempts.status` as the sole
   payment truth) stay in place and are kept consistent by the new services, following expand/contract.

## 3. Module boundaries

```
app/Modules/Platform/      cross-cutting capability owned by no single domain
  Api/                     envelope, problem details, cursor pagination
  Auth/                    API bearer tokens, guard, rate limiting policy
  Rbac/                    permission catalogue, scoped authorisation
  Audit/                   append-only audit recorder
  Outbox/                  transactional outbox + relay
  Numbering/               atomic, collision-safe human identifiers
  Idempotency/             Idempotency-Key store and replay
  Storage/                 private object store, hashing, malware scanning
  Documents/               PDF / XLSX / CSV writers

app/Modules/Admission/     the admission domain
  Models/                  Eloquent persistence
  Domain/                  state machines, enums, business rules (no framework I/O)
  Services/                use-case services
  Payments/                provider-neutral gateway + adapters
  Analytics/               governed metric registry
  Reports/                 report definitions + export runners
  Http/                    controllers (transport only), requests, resources
  Events/  Jobs/  Notifications/
```

Rule: controllers do transport, services do use cases, `Domain/` holds rules with no database access,
models hold persistence. Cross-module communication is by event, never by reaching into another
module's models.

## 4. Delivery order

| # | Slice | Output |
|---|---|---|
| 1 | Platform foundation | RBAC, tokens, audit, outbox, numbering, idempotency, API envelope |
| 2 | Setups + catalogue | institutions → programmes → offerings → intakes, public catalogue API |
| 3 | Identity | registration, verification, login, reset, sessions, profile |
| 4 | Draft application | sections, education/employment/references, autosave, concurrency |
| 5 | Documents | private upload, hash, scan, quarantine, verification |
| 6 | Payments | fee setup, attempts, adapters, webhooks, receipts, reconciliation, waivers |
| 7 | Submission | atomic gate, immutable version, workflow instance, receipts |
| 8 | Review + decisions | assignments, checklists, rubrics, approvals, maker-checker |
| 9 | Offers | letters, QR verification, responses, deferral, appeal |
| 10 | Rolls + conversion | admission rolls, freeze, idempotent student conversion |
| 11 | Analytics + reports | metric registry, trends, export jobs |
| 12 | Hardening | security tests, migration tests, docs, runbooks |

## 5. Non-negotiable invariants

1. Submission requires an authoritative `PAID` (or authorised `WAIVED`) KES 1,000 application fee —
   enforced in the submission transaction **and** by a database partial unique index.
2. A user-entered transaction code is never proof of payment; only a verified provider event is.
3. Application state changes only through the central state machine, always writing status history.
4. Payment state is a separate machine from application state.
5. Frozen admission rolls and submitted application versions are immutable.
6. Student conversion is idempotent on `(application_id)` plus an idempotency key.
7. QR tokens are opaque random values carrying no personal data.
8. Deny by default: no permission means no access, including on list endpoints.
9. System administrators never receive final-decision or restricted-support-data permissions.
