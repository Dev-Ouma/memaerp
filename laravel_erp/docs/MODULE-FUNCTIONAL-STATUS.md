# Module Functional Status & Remediation Timeline

**Prepared:** 3 September 2026
**Scope:** every module routed in `routes/web.php`
**Question answered:** which modules run on real data and real logic, which are mockups, and when each remaining one reaches functional parity.

---

## 1. How each module was classified

No module was graded by looking at a screen. Three structural probes were run against the
codebase, each of which is objective and reproducible:

| Probe | What it proves | Command |
|---|---|---|
| **Write-route count** | A module with zero POST/PUT/PATCH/DELETE routes is *structurally incapable* of persisting anything. No amount of UI work changes that. | `php artisan route:list --json`, grouped by route-name prefix |
| **Controller DB-call count** | A controller with zero ORM/`DB::` calls is returning literals. Its screens cannot reflect a database. | `grep -cE '::(query\|where\|find\|create\|…)\(\|DB::'` per controller |
| **Button-to-form ratio** | Buttons vastly outnumbering forms/submit-actions means most controls post nowhere. | `<button>` vs `<form>`/`<x-pg.action>` counts per view directory |

A module is **only** graded green if it additionally survives an end-to-end test that creates a
record over HTTP, drives it through every valid workflow state over HTTP, and reads the resulting
state back out of PostgreSQL. Rendering is not evidence.

---

## 2. Findings

### 2.1 Verified real — end-to-end, this cycle

| Module | Read routes | Write routes | Tables | Evidence |
|---|---|---|---|---|
| **PG Research** (incl. **PG Appeal**) | 17 | 43 | 21 | `tests/Feature/PgResearchLifecycleTest.php` — 11 tests, 455 assertions, all passing |

This is the only module that currently meets all five acceptance criteria. What it now does:

- 21 domain tables, 21 Eloquent models, one `PgResearchWorkflow` service that owns every state
  transition and refuses invalid ones with an explicit error.
- Every mutation writes an append-only row to `pg_research_events`. The full-lifecycle test asserts
  **12 distinct event types** land for a single candidate.
- All 16 screens rewired: `triggerActionAlert` (the fake-success handler) count is **0**. Row actions
  are state-branched — a terminal record shows a status label, not a button the backend would reject.
- 98 buttons across the module are backed by 46 real form/action elements.
- Two dashboard tiles (**flagged AI content**, **seminar attendance rate**) had no column behind them.
  Rather than relabel or fabricate the numbers, migration `2026_09_03_090000_add_pg_research_metric_columns`
  adds `pg_plagiarism_scans.ai_index` / `.ai_threshold` and `pg_seminars.attendance_count`, with
  PostgreSQL CHECK constraints and real form fields that capture them.

The lifecycle test reaches every state **only through the HTTP routes the screens post to**. Nothing is
constructed directly through the ORM to fake a state. If a state cannot be reached through the UI's own
routes, that surfaces as a test failure rather than being papered over with a fixture.

### 2.2 Real backend, not re-certified this cycle

These have real tables, real write routes and passing automated tests. They are **not** mockups. They
have not, however, been driven through the full five-criteria audit this cycle, so they are reported as
*amber*, not *passed*.

| Module | Read | Write | Automated tests |
|---|---|---|---|
| Account / self-service | 5 | 29 | `AccountModuleTest` |
| Admissions | 29 | 26 | `AdmissionModuleTest`, `AdmissionWorkspaceTest`, `AdmissionToStudentJourneyTest`, `AdmissionApiTest` |
| Admin setups (incl. governance, recycle bin, system maintenance, load balancer — all namespaced `admin.setups.*`) | 13 | 24 | `AdminSetupTest`, `GovernanceAdminTest`, `RecycleBinTest`, `SystemMaintenanceTest`, `LoadBalancerTest` |
| Curriculum | 15 | 12 | `CurriculumSchoolCrudTest`, `CurriculumCourseUnitTemplateTest` |
| Examination | 19 | 5 | `ExaminationDatabaseIntegrationTest` |
| SMHR | 12 | 5 | `SmhrModuleTest` |
| Task management | 4 | 4 | `TaskManagementModuleTest` |
| Budgeting | 3 | 4 | `BudgetingModuleTest` |
| Cohort | 6 | 3 | `ErpWorkflowTest` |
| Courses / Students | 2 | 5 | `ErpWorkflowTest`, `RoleAssignmentTest` |

Known residue to clear during their re-certification slot: `curriculum` (6 of 13 views), `smhr`
(6 of 11) and `admissions` (2 of 6) still contain client-only `alert()` handlers or `onclick`
stubs, and `admin` views contain 9 bare `alert()` calls. Those are individual unwired controls
inside otherwise real modules — a different defect class from §2.3.

### 2.3 Mockups — cannot persist anything

Nine modules have **zero write routes** and controllers with **zero database calls**. Every figure
on their screens is a PHP literal in the controller. Every button on them is decorative.

| Module | Read routes | Write routes | Controller | Lines | DB calls | Buttons | Forms |
|---|---|---|---|---|---|---|---|
| Reports | 31 | **0** | `ReportsController` | 501 | **0** | 22 | 1 |
| Registration | 16 | **0** | `RegistrationController` | 790 | **0** | 30 | 0 |
| Graduation | 14 | **0** | `GraduationController` | 398 | **0** | 26 | 0 |
| Service providers | 12 | **0** | `ServiceProvidersController` | 194 | **0** | 19 | 0 |
| LMS | 10 | **0** | `LmsController` | 488 | **0** | 19 | 0 |
| Fees | 8 | **0** | `FeesController` | 357 | **0** | 14 | 0 |
| Imprest | 7 | **0** | `ImprestController` | 402 | **0** | 12 | 0 |
| Work-study | 7 | **0** | `WorkStudyController` | 464 | **0** | 12 | 0 |
| Transfers & exemptions | 5 | **0** | `StudentTransferController` | 580 | **0** | 87 | 1 |

**241 buttons. 2 forms. 0 write routes. 0 database calls.**

`ReportsController` alone contains 178 lines that are literal table rows. The four `transfers` views
contain 23 calls to `triggerActionAlert()` — a JavaScript function whose entire job is to pop a success
toast for an operation that never happened.

None of these nine may be presented as passed.

### 2.4 A test that certifies a mockup

`tests/Feature/StudentTransfersTest.php` passes, and proves nothing. It asserts on the controller's own
hardcoded literals:

```php
$response->assertSee('1,683');            // a constant in StudentTransferController
$response->assertSee('DANIEL KIBET');     // a constant in StudentTransferController
$response->assertSee('158 unassigned');   // a constant in StudentTransferController
```

This is a green tick guarding a mockup, and it is exactly the "static fixture as evidence of
functionality" failure mode. It is scheduled for deletion and replacement in the Transfers slot below.
Any similar assert-the-literal tests found during each module's slot are replaced the same way.

---

## 3. Definition of done

A module moves from red to green only when **all seven** hold. This is the gate, applied per module,
with no partial credit:

1. Backing tables exist with real constraints (FKs, CHECKs, uniqueness) — not a JSON blob standing in.
2. The read controller has zero hardcoded display rows; every figure traces to a query.
3. Every action in the UI has a write route, and every write route runs through a service method that
   validates preconditions and refuses invalid transitions with an explicit error.
4. Every mutation writes an audit event.
5. Row actions are state-branched: no control is offered that the backend would reject.
6. An end-to-end feature test creates a record over HTTP, drives it through every valid state over
   HTTP, and asserts the resulting rows in PostgreSQL — constructing no state directly through the ORM.
7. All the module's Blade templates compile (`blade.compiler` → `php -l`) and the full suite is green.

---

## 4. Remediation timeline

**Assumption, stated because it drives the plan:** the next UAT cycle date has not been given to me.
The schedule below is built as the shortest responsible sequence for a single builder starting
Monday 7 September 2026, and lands the last module on **Monday 26 October 2026**. If UAT is earlier
than that, see §5 — the sequence is the lever, not the standard.

Sequencing is dependency-driven, not size-driven. Registration comes first because enrolment records
are what Fees, Transfers, Graduation and Work-Study all key off. Reports comes last because it is a
*derived* module — it can only report real numbers once its sources are real.

| # | Module | Start | Finish | Days | Why here |
|---|---|---|---|---|---|
| 1 | **Registration & enrolment** | Mon 07 Sep | Fri 11 Sep | 5 | Foundational. Four downstream modules depend on the enrolment record. Largest screen count (15). |
| 2 | **Student fees & billing** | Mon 14 Sep | Thu 17 Sep | 4 | Reuses existing `payment_transactions` / `payment_receipts` / `payment_fee_setups` infrastructure; needs enrolment from #1 to bill against. |
| 3 | **Transfers, exemptions & credit transfer** | Fri 18 Sep | Wed 23 Sep | 4 | Needs enrolment (#1) and curriculum. Includes deleting and rewriting `StudentTransfersTest` (§2.4). |
| 4 | **Graduation & clearance** | Thu 24 Sep | Tue 29 Sep | 4 | Needs enrolment (#1), fee clearance (#2) and examination results. |
| 5 | **LMS** | Wed 30 Sep | Mon 05 Oct | 4 | Needs curriculum and enrolment (#1). |
| 6 | **Work-study** | Tue 06 Oct | Thu 08 Oct | 3 | Needs student records and a finance path (#2). |
| 7 | **Imprest & petty cash** | Fri 09 Oct | Tue 13 Oct | 3 | Largely self-contained finance workflow. |
| 8 | **Service providers** | Wed 14 Oct | Fri 16 Oct | 3 | Self-contained vendor registry; smallest surface (194 controller lines). |
| 9 | **Reports** | Mon 19 Oct | Wed 21 Oct | 3 | Derived. Every source module is real by this point, so its 178 hardcoded rows become queries. |
| 10 | **Cross-module regression + amber re-certification + UAT dry run** | Thu 22 Oct | Fri 23 Oct | 2 | Clears the §2.2 residue, re-runs the full suite, walks the UAT script end to end against a fresh database. |

**Functional parity across all modules: Monday 26 October 2026.**

Each module's slot delivers, in order: migrations → models → workflow service → action controller +
write routes → read controller converted to queries → views rewired → end-to-end lifecycle test. That
is the same sequence that took PG Research from 0 write routes to 43 with a passing lifecycle suite,
so the estimates are calibrated against actual measured work rather than guessed.

---

## 5. If UAT lands before 26 October

Do not compress by lowering the gate in §3 — that reproduces the current situation. Two honest levers:

- **Parallelise.** Slots 6, 7 and 8 (Work-Study, Imprest, Service Providers) have no dependency on each
  other and none of the others depend on them. A second builder taking those three in parallel from
  06 October pulls the finish date to **Wed 14 October**.
- **Cut scope explicitly.** Take modules 1–4 (Registration, Fees, Transfers, Graduation) to green by
  **Tue 29 September** and put the remaining five in front of UAT flagged, in writing, as
  not-for-sign-off. A shorter list of genuinely working modules is testable; a longer list with
  mockups mixed in is not.

The one thing that should not happen is a UAT cycle in which any module from §2.3 is presented for
sign-off. Those nine modules are 110 screens and 241 buttons, and not one of those buttons writes a row.

---

## 6. Current build state

```
php artisan test  →  144 tests, 144 passed, 1245 assertions
```

Full suite green as of 3 September 2026. Two defects were found and fixed while establishing this
baseline, both unrelated to PG Research:

- `LegalAndCookieConsentTest` referenced `App\Models\AcademicIntake` and `App\Models\AcademicOffering`,
  neither of which exists — the models are `AdmissionIntake` and `ProgrammeOffering`. The test had been
  written against a superseded schema and had never run green. Rewritten against the real models.
- `resources/views/components/cookie-consent.blade.php` emitted a bare `&` in the banner heading.
  Escaped to `&amp;`.
