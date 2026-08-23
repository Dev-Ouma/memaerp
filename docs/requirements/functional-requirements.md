# Functional Requirements Baseline

**Status:** Draft for stakeholder validation  
**Owner:** Requirements Architect  
**Sources:** `docs/UNIVERSITY-ERP-SRSD/`, `PLAN/00-EXECUTION-PLAN.md`, and `PLAN/13-SRSD-GAP-AUDIT.md`

## Governance

This file is the capability-level baseline. Detailed behavior remains in the module SRSD files. A requirement may only become implementation-ready when it has an owner, business rules, authorization policy, acceptance criteria, and traceability to a module specification. Unknown policy is recorded as an open decision; developers must not select a rule silently.

## Platform capabilities

| ID         | Requirement                                                                                                                                                  | Primary owner    | Phase |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------ | ---------------- | ----- |
| FR-PLT-001 | The system shall maintain one canonical person and identity record across applicant, student, staff, lecturer, and alumnus lifecycles.                       | Registrar / HR   | 0–1   |
| FR-PLT-002 | The system shall authenticate supported identifiers and provide secure session, device, recovery, and MFA management.                                        | ICT Director     | 0     |
| FR-PLT-003 | The system shall authorize every operation by permission and organizational scope, denying access by default.                                                | Security Officer | 0     |
| FR-PLT-004 | The system shall support configurable approvals, delegation, escalation, rejection, and separation of duties.                                                | Process owners   | 0     |
| FR-PLT-005 | The system shall create immutable, searchable audit records for mutations and security-sensitive reads.                                                      | Internal Audit   | 0     |
| FR-PLT-006 | The system shall provide configuration, feature flags, file handling, notifications, jobs, monitoring, and operational controls as shared platform services. | ICT Director     | 0     |
| FR-PLT-007 | The system shall isolate all domain data by `institution_id`, even during the initial single-university deployment.                                          | Data owner       | 0–5   |

## Public site and admissions

| ID         | Requirement                                                                                                                                          | Primary owner | Phase |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- | ------------- | ----- |
| FR-ADM-001 | Authorized content staff shall manage public pages, navigation, programmes, news, events, staff profiles, documents, media, forms, and SEO metadata. | Marketing     | 1     |
| FR-ADM-002 | Prospective students shall discover programmes and submit, save, pay for, and track applications online.                                             | Admissions    | 1     |
| FR-ADM-003 | Admissions staff shall validate documents, assess eligibility, execute approvals, issue offers, and record acceptances.                              | Admissions    | 1     |
| FR-ADM-004 | Accepted applicants shall be matriculated without duplicating their person identity and shall receive a governed student number.                     | Registrar     | 1     |

## Student and academic lifecycle

| ID         | Requirement                                                                                                                                               | Primary owner                | Phase |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------- | ----- |
| FR-ACA-001 | Authorized staff shall maintain versioned programmes, curricula, courses, prerequisites, offerings, sections, calendars, and graduation rules.            | Academic Registrar           | 1     |
| FR-ACA-002 | Students shall register and add/drop courses subject to configured status, prerequisite, credit, capacity, timetable, and financial-clearance rules.      | Registrar                    | 1     |
| FR-ACA-003 | The system shall prevent room, lecturer, invigilator, and student timetable conflicts.                                                                    | Timetabling Office           | 1     |
| FR-ACA-004 | Authorized academic staff shall enter, moderate, verify, approve, publish, and formally amend assessments and results.                                    | Examinations Office / Senate | 1     |
| FR-ACA-005 | The system shall calculate GPA, progression, degree completion, transcripts, and credentials using the rule version applicable to each cohort and period. | Senate / Registrar           | 1     |
| FR-ACA-006 | Students shall access their authorized profile, registration, finance, timetable, results, requests, documents, and notifications through one portal.     | Student Affairs              | 1–2   |
| FR-ACA-007 | The system shall support advising, attendance, attachment, work-study, accommodation, welfare, discipline, elections, clearance, and aid workflows.       | Respective process owners    | 2     |

## Finance, HR, and operations

| ID         | Requirement                                                                                                                                                                                  | Primary owner     | Phase |
| ---------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------- | ----- |
| FR-FIN-001 | The system shall configure fee structures, invoice students, maintain an immutable ledger, accept and reconcile payments, process approved reversals/refunds, and issue receipts/statements. | Bursar            | 1     |
| FR-FIN-002 | The system shall maintain a balanced general ledger and integrate student finance, receivables, payables, payroll, bank/cash, procurement, stores, and assets.                               | Finance Director  | 3     |
| FR-HR-001  | The system shall maintain employee records, organization structures, contracts, leave, appraisal, training, payroll, and staff self-service.                                                 | HR Director       | 3     |
| FR-OPS-001 | The system shall support requisition-to-procure, supplier governance, receiving, inventory, asset lifecycle, facilities, fleet, security incidents, and helpdesk operations.                 | Operations owners | 3–4   |

## Research, governance, and intelligence

| ID         | Requirement                                                                                                                                                            | Primary owner                         | Phase |
| ---------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------- | ----- |
| FR-RGI-001 | The system shall manage research, grants, ethics review, postgraduate milestones, theses, examinations, and awards.                                                    | Research / Graduate School            | 4     |
| FR-RGI-002 | The system shall manage committees, agendas, controlled papers, minutes, resolutions, and action tracking.                                                             | University Secretary                  | 4     |
| FR-RGI-003 | Restricted clinic, counselling, and disciplinary data shall be managed within dedicated access boundaries.                                                             | Medical / Student Affairs data owners | 2–4   |
| FR-RGI-004 | The system shall provide governed reporting, statutory returns, a warehouse, executive analytics, retention interventions, credential verification, and mobile access. | Management / Registrar                | 5     |

## Integrations

| ID         | Requirement                                                                                                | Source of truth                                  |
| ---------- | ---------------------------------------------------------------------------------------------------------- | ------------------------------------------------ |
| FR-INT-001 | Integrate payments through provider-neutral adapters with signed, idempotent callbacks and reconciliation. | ERP ledger after provider confirmation           |
| FR-INT-002 | Synchronize courses and rosters to Moodle and controlled grade inputs back where approved.                 | ERP for identity, enrollment, and final grades   |
| FR-INT-003 | Synchronize library patrons and clearance status through an adapter.                                       | ERP for identity; library system for circulation |
| FR-INT-004 | Support notification providers without embedding provider rules in business modules.                       | ERP notification log                             |
| FR-INT-005 | Expose versioned partner APIs and signed webhooks only through approved contracts.                         | Owning ERP module                                |

## Known specification gaps

Full module specifications remain required for CMS/public website, academic staff workload, staff promotions, and budgeting/commitment control before their implementation gates. See `PLAN/13-SRSD-GAP-AUDIT.md`.
