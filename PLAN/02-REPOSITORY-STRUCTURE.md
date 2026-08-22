# MEMA ERP — REPOSITORY & CODE STRUCTURE

**Document:** `PLAN/02-REPOSITORY-STRUCTURE.md` · **Version:** 1.0.0-PLAN

---

## 1. Repository strategy

**One monorepo.** Frontend, backend, integrations, infrastructure and docs in a single Git repository.

An ERP where a backend endpoint and its consuming screen are always changed together is exactly the case
monorepos serve: one PR, one review, one CI run, one atomic deploy, one revert. Polyrepo would force
version-coordination overhead on every feature for no benefit at this team size.

```
memaerp/
├── apps/                     Next.js applications (7)
├── packages/                 Shared frontend libraries (8)
├── backend/                  Laravel modular monolith
├── integrations/             Integration contract tests and fixtures
├── infrastructure/           Docker, Nginx, deployment, monitoring
├── docs/                     SRSD requirements set
├── PLAN/                     Delivery plan (this directory)
├── DIAGRAMS/                 Mermaid diagram set
├── turbo.json
├── pnpm-workspace.yaml
├── Makefile
└── docker-compose.yml
```

---

## 2. Frontend — `apps/` and `packages/`

```
apps/
├── website/          www.mema.ac.ke        Public site      Static + ISR    MOD-01-14
├── applicant/        apply.mema.ac.ke      Admissions       SSR             MOD-01-05
├── student/          student.mema.ac.ke    Student portal   SPA             MOD-01-13
├── lecturer/         lecturer.mema.ac.ke   Teaching         SPA             MOD-02-11
├── staff/            staff.mema.ac.ke      Staff services   SPA             MOD-02-11
├── admin/            admin.mema.ac.ke      ERP admin        SPA             All modules
└── management/       exec.mema.ac.ke       Executive BI     SSR + charts    MOD-05-03

packages/
├── ui/               Design system — shadcn/ui + Radix, Mema tokens, Storybook
├── api-client/       GENERATED typed client from OpenAPI — never hand-edited
├── types/            GENERATED domain types + Zod schemas
├── auth/             Session hooks, route guards, permission gates, MFA flows
├── forms/            RHF + Zod field components, wizards, autosave, file upload
├── tables/           TanStack Table wrapper — server pagination, filters, column state, export
├── charts/           ECharts wrappers, institutional theme, accessible fallbacks
└── config/           ESLint, TypeScript, Tailwind, Prettier base configs
```

### Rules

1. `packages/api-client` and `packages/types` are **build artifacts**. Editing them by hand is a review
   rejection — the fix belongs in the backend's OpenAPI annotations.
2. Packages never import from `apps/`. Enforced by ESLint boundary rules in CI.
3. Every `packages/ui` component ships with a Storybook story. Visual regression runs on `ui` changes,
   because one component change touches seven applications.
4. A component used by two or more apps moves to `packages/ui`. A component used by one stays in that app.
   Premature sharing is as costly as duplication.

### Per-app structure

```
apps/student/
├── src/app/                  App Router — route groups by domain
│   ├── (auth)/               login, mfa, reset — unauthenticated
│   └── (portal)/             dashboard, registration, fees, results, …
├── src/features/             Feature modules — mirrors backend module names
│   └── registration/         components · hooks · api · schemas
├── src/components/           App-specific composites only
├── src/lib/                  App-local utilities
└── src/middleware.ts         Session + permission edge guard
```

`src/features/` deliberately mirrors backend module names. When someone asks "where does course registration
live", the answer is the same word on both sides of the stack.

---

## 3. Backend — `backend/`

```
backend/
├── app/
│   ├── Modules/                        ← all business logic lives here
│   ├── Support/                        Cross-cutting: base classes, traits, casts
│   └── Providers/
├── bootstrap/ · config/ · public/
├── database/
│   ├── migrations/                     Platform-level only
│   └── seeders/                        Institution reference data
├── routes/
│   ├── api.php                         Loads per-module route files
│   └── channels.php
├── tests/
│   ├── Architecture/                   Boundary + convention enforcement
│   └── Integration/                    Cross-module journeys
├── composer.json · phpstan.neon · pint.json · deptrac.yaml
```

### Module layout — identical for all 57

```
app/Modules/Registration/
├── Http/
│   ├── Controllers/Api/V1/             Thin — validate, delegate, respond
│   ├── Requests/                       Form request validation
│   ├── Resources/                      API response transformation
│   └── Middleware/
├── Models/                             Eloquent, module-owned tables only
├── Services/                           ← business logic lives here
├── Actions/                            Single-purpose invokable use cases
├── Contracts/                          ← THE PUBLIC INTERFACE of this module
├── Events/ · Listeners/ · Jobs/
├── Policies/                           Authorization
├── Rules/                              Custom validation
├── Exceptions/
├── Database/
│   ├── Migrations/                     Module-owned tables only
│   ├── Factories/ · Seeders/
├── Tests/
│   ├── Feature/ · Unit/
├── routes/api.php
├── config/registration.php
└── RegistrationServiceProvider.php
```

### The boundary rule

> A module may reference another module **only** through classes in that module's `Contracts/` directory.
> Importing another module's `Models/`, `Services/` or querying its tables directly is prohibited.

```php
// ✗ REJECTED — reaches into another module's internals
use App\Modules\Finance\Models\StudentInvoice;
$cleared = StudentInvoice::where('student_id', $id)->sum('balance') <= 0;

// ✓ CORRECT — consumes the published contract
use App\Modules\Finance\Contracts\FeeClearanceChecker;
$cleared = $this->feeClearance->isClearedForRegistration($studentId, $termId);
```

The first version welds Registration to Finance's table structure; a Finance schema change silently breaks
registration, and Finance's own clearance rules (waivers, sponsor commitments, payment plans, grace periods)
are duplicated and drift. The second survives all of it.

**This is enforced mechanically, not socially.** `deptrac.yaml` defines each module as a layer with declared
allowed dependencies; `composer analyse` fails the build on violation. A boundary rule without a CI check
is decoration — by Phase 3 it will have been violated dozens of times.

### Layer responsibilities

| Layer | Does | Must not |
|---|---|---|
| Controller | Validate input, call a service, return a Resource | Contain business rules or query the DB |
| Form Request | Shape and type validation, authorization entry | Cross-entity business rules |
| Service | Business rules, orchestration, transactions | Know about HTTP |
| Action | One use case, invokable, testable in isolation | Grow into a god class |
| Model | Persistence, relationships, scopes, casts | Contain workflow logic |
| Policy | Permission + scope decisions | Be bypassed anywhere |
| Job | Idempotent side effects | Own the record of truth (ADR-010) |

Controllers stay under ~20 lines. When one grows, the logic belongs in a Service or Action.

---

## 4. `integrations/` and `infrastructure/`

```
integrations/                Contract tests + recorded fixtures per external system
├── mpesa/ · banks/ · moodle/ · sms/ · email/ · helb/ · kuccps/ · koha/ · sso/

infrastructure/
├── docker/                  Dockerfiles per service, multi-stage
├── nginx/                   Site configs, TLS, security headers, rate limits
├── deployment/              Deploy scripts, zero-downtime release, rollback
├── monitoring/              Prometheus, Grafana dashboards, alert rules, Loki
└── backup/                  Backup + verified-restore scripts
```

Adapter *implementations* live in `app/Modules/Integrations/`. The `integrations/` directory holds the
**contract tests and fixtures** — nightly runs against each provider's sandbox that fail loudly when an
external API changes shape. For M-Pesa in particular, learning about a contract change from a failing nightly
test rather than from students unable to pay fees is the entire point.

---

## 5. Conventions

| Concern | Convention |
|---|---|
| Branching | Trunk-based; short-lived `feature/MOD-01-07-course-capacity-lock` branches |
| Commits | Conventional Commits, module ID in scope: `feat(MOD-01-07): add capacity lock` |
| PR size | Under ~400 changed lines; larger requires justification |
| Review | One approval minimum; two for money, grades or permissions |
| Migrations | Always reversible; rollback exercised in CI |
| DB naming | `snake_case`, plural tables, `{singular}_id` FKs, `_at` timestamps |
| PHP | PSR-12 via Pint, `declare(strict_types=1)`, PHPStan level 8 |
| TypeScript | `strict: true`, no `any`, no non-null assertion without comment |
| API | Plural kebab-case resources: `/api/v1/course-registrations` |
| Secrets | Never in the repo; managed store, injected at runtime; `git-secrets` pre-commit hook |

---

## 6. Local development

```bash
git clone <repo> && cd memaerp
make setup     # env files, deps, containers, migrate, seed, keys
make up        # full stack
make test      # backend + frontend + e2e
make fresh     # rebuild database with demo data
```

| Service | URL | Purpose |
|---|---|---|
| API | http://localhost:8000 | Laravel |
| API docs | http://localhost:8000/docs | OpenAPI/Scalar |
| Website / Applicant / Student / Lecturer / Staff / Admin / Management | :3000 – :3006 | The seven apps |
| Horizon | http://localhost:8000/horizon | Queue dashboard |
| Mailpit | http://localhost:8025 | Captured dev email |
| MinIO | http://localhost:9001 | S3 console |
| PostgreSQL / Redis | :5432 / :6379 | |

**Gate 0 criterion:** clean clone to working stack in **under 10 minutes**. This is measured on a new machine
and treated as a real requirement — onboarding friction compounds across a 24-month programme with staff
turnover.
