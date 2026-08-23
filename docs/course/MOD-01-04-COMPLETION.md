# MOD-01-04 — Course Master Catalogue & Semester Offerings Completion Evidence

**Status:** Functionally complete end to end  
**Verified:** 23 August 2026  
**Web route:** `http://localhost:3005/courses`

## Delivered scope

| Capability | Database | Backend/API | Admin web | Automated evidence |
|---|---|---|---|---|
| Master course catalogue | Statused courses with contact hours, syllabus and unique tenant codes | Draft, update, search, filter, `ERR-CRS-001` on duplicate codes | Registry, create form and syllabus download | Feature and Chromium workflows |
| Catalogue prerequisites | Institution-level requirements | Cycle detection (`ERR-CUR-CYCLE`) | Dependency editor | Feature and browser cycle rejection |
| Department → school approval | Review steps on each draft | Submit and sequential approve | Two-stage approval board | Feature HOD/registrar flows |
| Semester offerings | Campus/term/section uniqueness, capacity, waitlist and close state | Open, list scoped offerings, close enrollment | Section table and open-section form | Feature and Chromium offering create |
| Lecturer allocation | Primary/assistant allocations and workload credits | Assign endpoint and queued notification | Assign control on each section | Notification assertion and browser assign |
| Capacity and waitlist | `enrolled_count` ledger and waitlist queue | Enrollment observer increments seats; waitlist when full | Capacity badges | AC-3 increment test and waitlist create |
| Reports | Live catalogue and offering records | Catalogue PDF/CSV, section PDF/CSV, syllabus PDF | Authenticated download actions | Content type and `%PDF`/CSV assertions |

## API contract

The OpenAPI 3.1 contract in `docs/api/openapi.yaml` documents catalogue, offering, allocation, waitlist and report operations.

## Acceptance criteria

- **AC-1 — Configure master courses and map prerequisites:** Passed.
- **AC-2 — Create campus sections and bind lecturers:** Passed.
- **AC-3 — Enrollment count increments on student enrollment:** Passed via `OfferingCapacity` on `CourseEnrollment` create.

## Principal implementation files

- `apps/api/database/migrations/2026_08_23_002700_complete_course_catalogue.php`
- `apps/api/app/Modules/Course/Http/Controllers/CourseController.php`
- `apps/api/app/Modules/Course/Services/OfferingCapacityService.php`
- `apps/admin/src/app/(portal)/courses/page.tsx`
- `apps/api/tests/Feature/Course/CourseCatalogueTest.php`
- `e2e/course-admin.spec.ts`
