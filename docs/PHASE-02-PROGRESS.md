# PHASE 02 — Academic Delivery & Integration (Progress)

**Status:** Complete · 23 August 2026

## Completed modules

| Module | Backend | Admin UI | Student/Lecturer UI | Tests |
|--------|---------|----------|---------------------|-------|
| MOD-02-01 LMS sync | `/api/v1/lms/*` | `/lms` | Student `/lms` SSO launch | `LmsSyncTest` |
| MOD-02-02 Attendance | `/api/v1/attendance/*` | `/attendance` | Lecturer `/attendance`, Student `/attendance` | `AttendanceEngineTest` |
| MOD-02-03 Academic Advising | `/api/v1/advising/*` | `/advising` | Student `/advising`, Lecturer `/advisees` | `AdvisingEngineTest` |
| MOD-02-04 Industrial Attachment | `/api/v1/attachment/*` | `/attachment` | Student `/attachment`, Lecturer `/supervision` | `AttachmentEngineTest` |
| MOD-02-05 Work-Study Programme | `/api/v1/work-study/*` | `/work-study` | Student `/work-study`, Lecturer `/work-study-supervision` | `WorkStudyEngineTest` |
| MOD-02-06 Library / Koha | `/api/v1/library/*` | `/library` | Student `/library` | `LibraryEngineTest` |
| MOD-02-07 Student Affairs & Elections | `/api/v1/student-affairs/*` | `/student-affairs` | Student `/student-affairs` | `StudentAffairsEngineTest` |
| MOD-02-08 Accommodation & Hostels | `/api/v1/accommodation/*` | `/accommodation` | Student `/accommodation` | `AccommodationEngineTest` |
| MOD-02-09 Request Hub & Clearance | `/api/v1/requests/*` + graduation clearance | `/requests` | Student `/clearance`, Staff `/requests` | `StudentRequestEngineTest`, `GraduationClearanceQueueTest` |
| MOD-02-10 Scholarships & Aid | `/api/v1/financial-aid/*` | `/financial-aid` | Student `/financial-aid` | `FinancialAidEngineTest` |
| MOD-02-11 Lecturer & Staff Portals | `/api/v1/staff-portal/*` | — | Lecturer `/leave` `/research`, Staff `/leave` `/profile` | `StaffPortalEngineTest` |

## Portal coverage

| App | Port | Key routes |
|-----|------|------------|
| Admin | 3005 | `/`, `/lms`, `/attendance`, `/advising`, `/attachment`, `/work-study`, `/library`, `/financial-aid`, `/requests`, `/accommodation`, `/student-affairs`, `/courses`, … |
| Student | 3002 | `/`, `/registration`, `/timetable`, `/attendance`, `/advising`, `/attachment`, `/work-study`, `/library`, `/financial-aid`, `/accommodation`, `/student-affairs`, `/lms`, `/clearance` |
| Lecturer | 3003 | `/`, `/offerings`, `/marks`, `/attendance`, `/advisees`, `/supervision`, `/work-study-supervision`, `/leave`, `/research` |
| Staff | 3004 | `/`, `/requests`, `/leave`, `/profile` |
| Applicant | 3001 | `/`, `/login`, `/status` |
| Management | — | Executive KPI dashboard |

## Module highlights

### MOD-02-07 Student Affairs
- Clubs/societies with membership
- Confidential welfare/counselling cases
- Disciplinary hearings with sanctions & appeal window
- Anonymous elections: voter mark separate from ballot; publishable results hash

### MOD-02-08 Accommodation
- Blocks/rooms/beds inventory with atomic allocation
- Booking → offer → accept → occupancy → check-out → HST clearance

### MOD-02-09 Request Hub
- Service requests + unified clearance desks (FIN/LIB/REG/HST/ICT)

### MOD-02-11 Staff portals
- Leave applications & approval queue
- Research publication self-entry
- Workload + payslip stub dashboard (HR payroll Phase 03)

## Verification

```bash
cd apps/api
php artisan migrate --force
php artisan db:seed --class=App\\Modules\\Iam\\Database\\Seeders\\PermissionSeeder --force
php artisan db:seed --class=App\\Modules\\Iam\\Database\\Seeders\\RoleSeeder --force
php artisan test tests/Feature/StudentAffairs tests/Feature/StaffPortal tests/Feature/Accommodation

make e2e-portals
```

## Phase 02 status

**All 11 Phase 02 modules are implemented.** Next roadmap work is Phase 03 (HR, payroll, procurement).
