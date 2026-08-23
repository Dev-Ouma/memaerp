# MOD-01-03 — Programme Structure & Curriculum Engine Completion Evidence

**Status:** Functionally complete end to end  
**Verified:** 23 August 2026  
**Web route:** `http://localhost:3005/programmes`

## Delivered scope

| Capability | Database | Backend/API | Admin web | Automated evidence |
|---|---|---|---|---|
| Programme registry | Tenant-scoped programmes with award levels, credits, residency and accreditation | Search, filter, create and governed update | Registry, create form and accreditation warning | Feature and Chromium workflows |
| Immutable curriculum versions | Version table, structure hash, lock timestamp and immutability trigger | Draft create, grid edits, lock on Senate approval (`ERR-CUR-002`) | Version switcher, lock banner and hash display | Approval lock and delete-rejection tests |
| Semester grid and elective clusters | Curriculum courses and elective groups | Add, update, remove grid entries and clusters | Semester tables and cluster editor | Feature and browser mapping |
| Prerequisite graph | Version-scoped requirements with cycle guard | `ERR-CUR-CYCLE` on cyclic edges | Dependency editor | Feature and Chromium cycle rejection |
| HOD → Senate workflow | Review steps and approval ledger | Submit and sequential approve, notification on Senate lock | Four-stage approval board | Feature and browser approval chain |
| Cohort assignment | Student `curriculum_version_id` | Assign only unassigned students in a year | Cohort assign control | Repeat-assign returns zero |
| Reports | Live governed records | PDF handbook and CSV matrix | Authenticated download actions | `%PDF`/CSV assertions and browser downloads |

## Acceptance criteria

- **AC-1 — Multi-year curriculum with core and electives:** Passed.
- **AC-2 — Cyclic prerequisite rejection:** Passed (`ERR-CUR-CYCLE`).
- **AC-3 — Approved versions are read-only:** Passed (`ERR-CUR-002` and database trigger).

## Principal implementation files

- `apps/api/app/Modules/Curriculum/Http/Controllers/ProgrammeController.php`
- `apps/api/app/Modules/Curriculum/Services/CurriculumWorkflowService.php`
- `apps/admin/src/app/(portal)/programmes/page.tsx`
- `apps/api/tests/Feature/Curriculum/CurriculumEngineTest.php`
- `e2e/curriculum-admin.spec.ts`
