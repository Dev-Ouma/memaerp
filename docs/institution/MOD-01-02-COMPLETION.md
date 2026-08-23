# MOD-01-02 — Institutional Administration & Master Data Completion Evidence

**Status:** Functionally complete end to end  
**Verified:** 23 August 2026  
**Web route:** `http://localhost:3005/institution`

## Delivered scope

| Capability | Database | Backend/API | Admin web | Automated evidence |
|---|---|---|---|---|
| Campus → faculty/school/centre → department → unit hierarchy | Tenant-scoped UUID tables, unique codes, foreign keys, soft deletes, one-main-campus index | Search, filter, pagination, create, governed update, approve and archive endpoints | Creation forms, hierarchy directory, approval references and archive controls | Feature and Chromium workflows |
| Academic years and terms | Date checks and partial unique indexes for one current year and one current term per study mode | Transactional activation with row locking and `ERR-CAL-001` overlap rejection | Draft, Senate approval, activation, date gates and current-state display | Activation and overlap tests at API and browser layers |
| Study modes and intakes | Effective tenant-scoped tables and date constraints | List/create/update/deactivate modes; list/create/update/archive intakes | Configuration forms and active intake view | Feature and browser workflows |
| Holidays, deadlines and events | Audited calendar-event table with year/term relationships | Filtered list and validated creation endpoint | Critical deadline and calendar-event publisher | Feature and browser workflows |
| Universal master lookups | JSON metadata, effective dates and unique type/code constraint | Redis cache, write invalidation, create/update/archive | Lookup type selector, create/archive controls and retrieval timing | Cache presence/invalidation and sub-50ms assertion |
| Governance and audit | All governed models use the append-only audit recorder; hierarchy history is archive-only | Tenant isolation, permission checks, resolution references and workflow states | Status badges and explicit approval/archive actions | Permission-denial and governed lifecycle tests |
| Semester broadcasts | Durable database notifications table | Queued in-app and email notification to active institutional users | Existing portal notification channel can consume the database record | Notification dispatch assertion |
| Official reports and exports | Reports use live governed records | Academic calendar PDF; directory PDF, CSV and JSON | Authenticated download actions | Content type, filename and `%PDF`/CSV assertions plus real browser downloads |

## API contract

The validated OpenAPI 3.1 contract is [openapi.yaml](../api/openapi.yaml). It documents all MOD-01-02 hierarchy, calendar, lookup, workflow and report operations. The contract uses the deployed Sanctum cookie name and passes Redocly validation with zero warnings.

## Acceptance criteria

- **AC-1 — Multi-campus hierarchy:** Passed. Campuses, faculties/schools/centres, departments and units are tenant-linked and are configurable through the API and web UI.
- **AC-2 — No overlapping active standard terms:** Passed. Transactional service validation rejects an overlap for the same study mode with `ERR-CAL-001`; partial unique indexes protect current flags.
- **AC-3 — Redis-cached lookup retrieval below 50ms:** Passed in the automated integration environment. The test warms and verifies the cache, checks invalidation after a write, and asserts the endpoint-reported retrieval time is below 50ms.

## Verification record

| Gate | Result |
|---|---|
| Laravel feature tests | **9 passed, 63 assertions** |
| PHPStan | **0 errors** |
| Laravel Pint | **passed** |
| API-client TypeScript | **passed** |
| Admin portal TypeScript | **passed** |
| Admin portal ESLint | **passed, 0 warnings** |
| OpenAPI 3.1 / Redocly | **valid, 0 warnings** |
| Playwright Chromium | **8 passed** with four workers |
| Responsive browser gate | **375 × 812 passed**, no document-level horizontal overflow |
| Report browser gate | **Directory and calendar PDF downloads passed** |

The 5,000-concurrent-user and 99.95% availability targets remain deployment-environment capacity/SRE gates; they cannot be certified by a local development server. The module is instrumented and cache-backed for those later Phase 5 load and reliability gates.

## Principal implementation files

- `apps/api/database/migrations/2026_08_23_002000_complete_institution_master_data.php`
- `apps/api/database/migrations/2026_08_23_002100_complete_institution_governance.php`
- `apps/api/app/Modules/Institution/Http/Controllers/InstitutionAdminController.php`
- `apps/api/app/Modules/Institution/Services/AcademicCalendarService.php`
- `apps/api/app/Modules/Institution/Services/InstitutionReportService.php`
- `apps/admin/src/app/(portal)/institution/page.tsx`
- `packages/api-client/src/index.ts`
- `apps/api/tests/Feature/Institution/InstitutionMasterDataTest.php`
- `e2e/institution-admin.spec.ts`
