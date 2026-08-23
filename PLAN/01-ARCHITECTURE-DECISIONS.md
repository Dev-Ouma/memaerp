# MEMA ERP — ARCHITECTURE DECISION RECORDS

**Document:** `PLAN/01-ARCHITECTURE-DECISIONS.md` · **Version:** 1.0.0-PLAN · **Date:** 22 August 2026

Each ADR states a decision, its context, its consequences, the alternatives rejected, and — where relevant —
the **trigger condition** that would justify revisiting it. An ADR is superseded, never edited.

---

## ADR-001 — Backend platform: Laravel 12 / PHP 8.4

**Status:** **ACCEPTED** — confirmed by the client on 23 August 2026, before Sprint 1
**Supersedes:** the NestJS/Node.js baseline in `docs/UNIVERSITY-ERP-SRSD/00-SYSTEM-ARCHITECTURE.md` §2

### Context — there is a genuine conflict in the record

Two authoritative-looking sources specify different backends for this system:

| Source | Backend | Frontend styling | Infrastructure |
|---|---|---|---|
| `docs/…/00-SYSTEM-ARCHITECTURE.md` (existing SRSD baseline) | **NestJS + Fastify, Node.js 22, TypeScript** | Vanilla CSS tokens + CSS Modules, explicitly "Zero Tailwind Overhead" | Kubernetes, Kafka, GraphQL for BI, ClickHouse DWH |
| Client engineering brief, 22 Aug 2026 | **Laravel, PHP 8.4** | Tailwind CSS + shadcn/ui | Docker + Nginx; Kubernetes, Kafka and GraphQL **explicitly excluded** |

This is not a wording difference. These are incompatible baselines that would produce entirely different
codebases. It was not flagged in either document — each reads as settled.

### Decision

Adopt **Laravel 12 on PHP 8.4** as the backend platform. The NestJS baseline is superseded.

### Reasoning

1. **The brief is the later and more deliberate statement.** It is dated, reasoned, assigned a confidence
   figure by its author, and explicitly argues against the very infrastructure the SRSD assumes.
2. **The SRSD is 96% stack-agnostic.** Its 31 sections per module cover actors, workflows, business rules,
   data entities, schemas, validation, approvals, audit and acceptance criteria — all of which survive a
   backend change unchanged. Only §2 of the architecture document is platform-specific. Realigning costs one
   document rewrite, not 57.
3. **Laravel's batteries fit this problem exactly.** Queues + Horizon, policies/gates, Eloquent, migrations,
   scheduler, Excel, PDF, Sanctum and the mature Kenyan M-Pesa ecosystem cover a large fraction of the ERP's
   non-differentiating work. On NestJS, a meaningful share of Phase 0 would be spent rebuilding these.
4. **Regional hiring and handover.** For a Kenyan university handover, PHP/Laravel maintainers are
   substantially easier to recruit and retain than NestJS specialists. A platform the client cannot staff
   after handover is a failed platform regardless of technical merit.
5. **The comparable reference product runs Laravel 12** at 2,000+ institutions, which is corroborating
   evidence that the stack carries this workload class.

### Consequences

- `docs/…/00-SYSTEM-ARCHITECTURE.md` §2, §3 must be rewritten to match. Tracked in
  [`13-SRSD-GAP-AUDIT.md`](13-SRSD-GAP-AUDIT.md) as defect **D-01**.
- PostgreSQL, Redis, S3, the domain schemas and every functional requirement carry over untouched.
- Type safety at the API boundary is no longer free. Compensated by ADR-006 (contract-first OpenAPI with
  generated TypeScript clients), which arguably gives stronger guarantees than a shared-language monorepo,
  because the contract is enforced rather than assumed.

### Confirmation

The client confirmed Laravel 12 / PHP 8.4 on **23 August 2026**, before any application code was written, so
the reversal cost described below was never incurred. This ADR is now binding: changing the backend platform
requires a superseding ADR, not an edit to this one.

*Recorded for the history:* had the answer been NestJS, the impact would have been contained — phases, module
ordering, gates, data architecture, RBAC model, integration design and delivery governance all hold
independently of the backend. Only ADR-001/002/003, the backend half of
[`02-REPOSITORY-STRUCTURE.md`](02-REPOSITORY-STRUCTURE.md) and the PHP-specific tooling in
[`07-DEVOPS-AND-ENVIRONMENTS.md`](07-DEVOPS-AND-ENVIRONMENTS.md) would have changed — roughly 15% of the plan
at this point, rising to severe after Sprint 4.

### Version pinning

| Component | Pinned version | Note |
|---|---|---|
| PHP | **8.4.x** | Docker images pin 8.4. Local machines may run 8.5; CI builds against 8.4 so 8.5-only syntax cannot land. |
| Laravel | **12.x** | |
| PostgreSQL | **17.x** | ADR-003. The SRSD said 18; 17 is the current stable major with pgvector and PgBouncer support proven. |
| Redis | **7.x** | |
| Node (frontend toolchain only) | **22 LTS** | Not a backend runtime. |

---

## ADR-002 — Modular monolith, not microservices

**Status:** ACCEPTED

**Context.** 57 modules spanning admissions, finance, HR, payroll, examinations and research, for a single
institution, delivered by one team.

**Decision.** One Laravel application, one deployment, one database — internally partitioned into strictly
bounded modules under `app/Modules/`, each owning its models, services, policies, routes, migrations and tests.

**Reasoning.** Microservices would impose distributed transactions on operations that are inherently atomic.
A student registration touches enrollment, capacity, fee ledger and audit; a payroll run touches HR, payroll
and the general ledger. Under ACID these are single transactions. Split across services they become sagas
with compensating actions — a large increase in complexity and failure modes, in exchange for independent
scaling that a single university's load does not require.

**Boundary enforcement — the part that actually matters.** A modular monolith degrades into a big ball of mud
unless boundaries are mechanically enforced. Therefore:

- A module may reference another module **only** through its published service interface — never by importing
  another module's Eloquent models or querying its tables directly.
- Cross-module reads go through service classes; cross-module writes go through service classes or domain events.
- This is enforced in CI by a static dependency check (Deptrac or equivalent) that **fails the build** on a
  boundary violation.

Without that CI check this ADR is a wish. With it, extraction later is mechanical.

**Extraction path.** When a domain outgrows the monolith, its published interface already exists; extraction
means replacing the in-process implementation with an HTTP client and moving its schema. Expected first
candidates: Examinations (peak seasonality) and the Data Warehouse (analytical isolation).

**Revisit when:** any single module exceeds ~15% of total request volume with a load profile incompatible
with the rest, or a second institution requires an independently versioned deployment.

---

## ADR-003 — PostgreSQL 17, single database, schema-per-domain

**Status:** ACCEPTED

**Decision.** One PostgreSQL 17 database with 14 domain schemas (`iam`, `institution`, `curriculum`, `course`,
`admission`, `student`, `enrollment`, `finance`, `examination`, `graduation`, `hr`, `procurement`, `research`,
`audit`), plus `cms` and `analytics`.

**Reasoning.** Schemas give namespace separation, per-schema permissions and a natural extraction seam, while
preserving cross-domain foreign keys and single-transaction integrity. Separate databases per domain would
sacrifice both immediately and buy nothing at this scale.

PostgreSQL over MySQL for: partial and expression indexes (sparse ERP predicates), `jsonb` with GIN indexing
(audit diffs, configuration, form payloads), native partitioning (audit logs, enrollment history), CTEs and
window functions (GPA, ranking, ageing, degree audit), exclusion constraints (room and invigilator
double-booking prevention at the database level), full-text search sufficient to defer OpenSearch, and
transactional DDL for safe migrations.

**Version.** PostgreSQL **17** rather than 18, since 17 is the version with a mature operational track record
and broad managed-provider support at the time of writing. Upgrade to 18 is a Phase 5 operational task with
no application impact. *(The existing SRSD names PostgreSQL 18 — reconcile in* [`13-SRSD-GAP-AUDIT.md`](13-SRSD-GAP-AUDIT.md) *, defect D-05.)*

**Consequences.** `citext` for case-insensitive identifiers; `pgcrypto` for hashing; `pg_stat_statements`
mandatory from day one; connection pooling via PgBouncer in transaction mode.

---

## ADR-004 — Next.js 15 monorepo, seven applications

**Status:** ACCEPTED

**Decision.** One Turborepo/pnpm monorepo containing seven Next.js 15 applications — `website`, `applicant`,
`student`, `lecturer`, `staff`, `admin`, `management` — over shared packages.

**Reasoning.** One application serving all seven audiences would ship the entire ERP administration bundle to
a prospective applicant, and would couple the public website's release cadence to the admin console's. Seven
independent applications give per-audience bundles, independent deploys, blast-radius isolation and
audience-appropriate rendering strategies:

| App | Rendering | Why |
|---|---|---|
| `website` | Static + ISR | SEO-critical, high traffic, content changes hourly not per-request |
| `applicant` | SSR + client | Long stateful forms, authenticated |
| `student`, `lecturer`, `staff` | Client-heavy SPA in App Router | Authenticated, data-dense, no SEO value |
| `admin` | Client-heavy SPA | Very data-dense, table-driven, no SEO value |
| `management` | SSR shell + client charts | Small audience, heavy visualisation |

**Consequences.** Turborepo remote caching is required or CI times degrade badly by Phase 3. Shared packages
must be versioned internally and cannot import from apps. A shared component's change affects seven apps —
hence `packages/ui` requires Storybook and visual regression tests from Phase 0.

---

## ADR-005 — Tailwind CSS 4 + shadcn/ui

**Status:** ACCEPTED · **Supersedes** the SRSD's "Vanilla CSS Tokens + CSS Modules (Zero Tailwind Overhead)"

**Decision.** Tailwind CSS 4 with shadcn/ui components over Radix primitives, wrapped in a first-party
`packages/ui` design system carrying Mema University's tokens.

**Reasoning.** The "zero Tailwind overhead" argument does not survive contact with an ERP admin console
containing hundreds of dense table, form, filter and dialog screens. Hand-written CSS Modules at that volume
produce more total CSS, not less, plus a large bespoke component library the team must build and maintain.
Tailwind's JIT output scales with distinct utility combinations, not with screen count. shadcn/ui components
are copied into the repository rather than imported as a dependency, so they are fully ownable and themeable —
which addresses the usual objection to component libraries.

Radix underneath gives WCAG 2.2 AA keyboard and screen-reader behaviour for dialogs, menus, comboboxes and
tabs — behaviour that is expensive and error-prone to hand-roll, and that is a hard acceptance criterion in
SRSD §28 of every module.

**Consequences.** Brand tokens (`#0A3E50` primary, `#1E8449` secondary, `#F8FAFC` canvas) are defined once in
`packages/ui` and consumed by all seven apps. Arbitrary-value utilities (`w-[137px]`) are rejected at review;
if a token does not exist, add the token.

---

## ADR-006 — Contract-first REST API with generated TypeScript clients

**Status:** ACCEPTED

**Decision.** REST/JSON at `/api/v1`, described by an OpenAPI 3.1 specification generated from Laravel
attributes, from which a typed TypeScript client and Zod schemas are generated into `packages/api-client`
and `packages/types` in CI.

**Reasoning.** This is the compensating control for ADR-001's loss of a shared language. The generated client
means a backend response-shape change breaks the frontend build immediately rather than at runtime in
production — a stronger guarantee than a manually-shared type. Zod schemas generated from the same source
give frontend validation that cannot drift from backend validation.

**Consequences.** The spec must be generated in CI and diffed; an undocumented endpoint fails the build.
Breaking changes require `/api/v2` and a documented deprecation window. Client generation runs on every
backend merge to main.

**Rejected: GraphQL.** For an ERP whose consumers are all first-party and whose access control is deeply
row- and field-scoped, GraphQL moves authorization into resolvers where it is far harder to audit, and
introduces N+1 and query-cost problems that need their own mitigation. REST with purpose-built endpoints
matches the actual consumption pattern.

---

## ADR-007 — Laravel Sanctum SPA cookie sessions, OIDC deferred

**Status:** ACCEPTED

**Decision.** Sanctum in **SPA cookie mode** (not token mode) for the seven first-party web apps, with
`__Host-` prefixed, `HttpOnly`, `Secure`, `SameSite=Lax` cookies. Sanctum **personal access tokens** for the
mobile apps. OIDC/SAML institutional SSO deferred to Phase 5 (MOD-05-01).

**Reasoning.** Cookie mode keeps the session token out of JavaScript reach entirely, which removes the largest
XSS impact vector. Token-in-localStorage is convenient and materially less safe; for a system holding grades,
health records and payroll, that trade is not available.

**Consequences.** Frontends must be same-site or use a properly configured CORS + `withCredentials` setup with
a strict origin allow-list. CSRF protection is mandatory on all state-changing routes. Session rotation on
privilege elevation. MFA is compulsory for every privileged role from Phase 0, not retrofitted.

---

## ADR-008 — Granular permissions with scope filters

**Status:** ACCEPTED

**Decision.** Permissions named `module.resource.action` (e.g. `examination.marks.moderate`), aggregated into
roles, with an orthogonal **scope** dimension (institution / campus / faculty / department / self).

**Reasoning.** Roles alone cannot express a university's actual access rules. "Head of Department" is not one
permission set — it is the same permission set bounded to a different department for each holder. Encoding
scope in role names (`hod_computing`, `hod_nursing`) produces a combinatorial explosion that becomes
unmaintainable within a year.

**Consequences.** Every Policy evaluates permission **and** scope. Every list endpoint applies a scope filter
at the query level, never in the response layer — filtering after fetching leaks row counts and is one
mistake away from leaking rows. Negative authorization tests are mandatory per ADR-009.

---

## ADR-009 — Test strategy: mandatory negative authorization testing

**Status:** ACCEPTED

**Decision.** Pest for backend unit and feature tests, Vitest + React Testing Library for frontend, Playwright
for end-to-end journeys. Every endpoint carries at least one test asserting that an unauthorised role is
**denied**.

**Reasoning.** Positive tests prove a feature works. In a system where a student must never see another
student's grades, an ordinary lecturer must never publish results, and no administrator may read clinic
records, the negative tests are the ones carrying institutional risk. Untested authorization is the single
most common source of catastrophic ERP breaches, and it always passes positive testing.

**Consequences.** A shared test helper enumerates roles and asserts the deny matrix per endpoint. Coverage
gates: ≥ 85% on business-rule services, 100% on money, grade and permission paths.

---

## ADR-010 — Queues carry side effects, never truth

**Status:** ACCEPTED

**Decision.** Redis-backed Laravel queues via Horizon, with six named queues (`default`, `notifications`,
`payments`, `reports`, `lms-sync`, `etl`) on isolated worker pools. The system of record is always written
synchronously inside the request transaction; queues carry only derived work.

**Reasoning.** A student who registers must see themselves registered, and a payment that is confirmed must be
in the ledger before the response returns. Deferring those writes creates a window where the truth is
ambiguous — which in a fee or grade context is a support incident and an audit finding. Email, SMS, PDF
rendering, LMS synchronisation and report generation may all safely lag.

**Consequences.** Every job is idempotent and safe to retry. Explicit backoff and dead-letter handling with a
human replay UI. Queue isolation means a slow LMS sync cannot delay a payment confirmation — which single-queue
designs suffer badly during peak periods.

---

## ADR-011 — Deliberate exclusions and their reversal triggers

**Status:** ACCEPTED

Each exclusion is a considered trade with a named condition that reverses it. Recording the trigger is what
distinguishes a decision from an omission.

| Excluded | Why now | Reversal trigger |
|---|---|---|
| **Kubernetes** | Two servers do not need an orchestrator. K8s adds a full-time operational discipline the client must staff after handover. | ≥ 5 app nodes, or multi-tenant SaaS with per-tenant isolation |
| **Kafka** | Redis queues plus an outbox table cover every current async need. Kafka is a distributed system to operate in its own right. | Cross-service event streaming after a real service extraction, or CDC-driven real-time analytics |
| **Microservices** | See ADR-002. | Per ADR-002 |
| **GraphQL** | See ADR-006. | A third-party consumer ecosystem needing flexible query shapes |
| **MongoDB** | Every domain here is relational and transactional. `jsonb` covers semi-structured needs inside ACID. | A genuinely document-shaped, non-transactional workload |
| **OpenSearch** | PostgreSQL FTS is sufficient at this corpus size and removes a service. | Corpus > ~5M documents, or cross-module fuzzy/faceted search becomes a first-class feature |
| **Separate DWH engine (ClickHouse)** | A read replica plus star-schema tables serve a single institution's BI. | Analytical queries degrade OLTP despite replica isolation, or multi-institution analytics |
| **Kong / dedicated API gateway** | Nginx plus Laravel middleware covers rate limiting, auth and CORS. | Phase 5 public partner API at scale (MOD-05-01) |

---

## ADR-012 — Single-tenant deployment, multi-tenant-shaped schema

**Status:** ACCEPTED

**Decision.** Deploy for Mema University only. Every domain table nevertheless carries a non-null
`institution_id` from the first migration, and every query is scoped through a global scope that resolves it.

**Reasoning.** Retrofitting tenancy into a mature ERP is one of the most expensive refactors available — it
touches every table, every query, every index, every export and every report, and it is very easy to leave a
leak. Carrying the column from day one costs a few bytes per row and one global scope; it is the cheapest
option on the SaaS future the brief describes, and it costs almost nothing if that future never arrives.

**Consequences.** Composite indexes lead with `institution_id`. The global scope is enforced by a base model.
CI includes a check that no domain migration creates a table lacking `institution_id`. Choice of
shared-schema vs schema-per-tenant vs database-per-tenant is deferred until a second institution is real —
this ADR only preserves the option.
