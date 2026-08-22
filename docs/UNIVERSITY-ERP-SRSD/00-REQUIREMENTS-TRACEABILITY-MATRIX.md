# UNIVERSITY ERP / UMIS — REQUIREMENTS TRACEABILITY MATRIX (RTM)

- **System:** Integrated University Enterprise Resource Planning & Student Information Management System (MEMA ERP)
- **Document Type:** Authoritative End-to-End Requirements Traceability Matrix
- **Coverage:** 100% of all requirements extracted across all source specification blueprints.

---

## 1. Traceability Mapping Table

| Source Document Reference | Source Requirement ID / Section | Target Module ID | Target Module Name | Target SRSD Section | Implementation Phase |
|---|---|---|---|---|---|
| `software_requirements_specification.md` | FR-CORE-001 (Authentication) | **MOD-01-01** | Identity & Access Management | §9 Functional Reqs, §21 Security | Phase 01 |
| `software_requirements_specification.md` | FR-CORE-002 (Role-Based Access) | **MOD-01-01** | Identity & Access Management | §5 Actors, §6 RBAC Matrix | Phase 01 |
| `software_requirements_specification.md` | FR-CORE-003 (Audit Trail) | **MOD-01-01** | Identity & Access Management | §20 Audit Trails & Logging | Phase 01 |
| `software_requirements_specification.md` | FR-CORE-004 (Workflow Engine) | **MOD-01-02** / All | Institutional Admin & Workflows | §15 Approval Workflows | Phase 01 |
| `software_requirements_specification.md` | FR-CORE-005 (Notifications) | **MOD-05-06** | Universal Notification Engine | §16 Notifications & Alerts | Phase 05 |
| `software_requirements_specification.md` | FR-CORE-006 (Document DMS) | **MOD-04-06** | Enterprise DMS | §24 System Generated Docs | Phase 04 |
| `software_requirements_specification.md` | FR-INS-001 to FR-INS-013 | **MOD-01-02** | Institutional Administration | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | FR-PGM-001 to FR-PGM-005 | **MOD-01-03** | Programme & Curriculum | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | FR-CUR-001 to FR-CUR-006 | **MOD-01-03** | Programme & Curriculum | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | FR-CRS-001 to FR-CRS-006 | **MOD-01-04** | Course Catalogue & Offering | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | Module 5 (Recruitment) | **MOD-01-05** | Recruitment & Admissions | §10 Sub-Modules & Features | Phase 01 |
| `software_requirements_specification.md` | FR-ADM-001 to FR-ADM-013 | **MOD-01-05** | Recruitment & Admissions | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | Module 7 (Onboarding) | **MOD-01-06** | Student Onboarding & Records | §7 Workflows, §9 FRs | Phase 01 |
| `software_requirements_specification.md` | Module 8 (Student Records) | **MOD-01-06** | Student Onboarding & Records | §12 Data Entities, §13 Schema | Phase 01 |
| `software_requirements_specification.md` | FR-REG-001 to FR-REG-005 | **MOD-01-07** | Student Registration & Enrollment | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | Module 10 (Course Enrollment) | **MOD-01-07** | Student Registration & Enrollment | §11 Business Rules & Logic | Phase 01 |
| `software_requirements_specification.md` | Module 12 & 13 (Timetable) | **MOD-01-08** | Timetable & Scheduling | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | Module 21 & FR-PAY-001 to 006 | **MOD-01-09** | Student Finance & Billing | §9 Functional Reqs, §11 Rules | Phase 01 |
| `software_requirements_specification.md` | Module 15, 16 & 17 (Exams) | **MOD-01-10** | Continuous Assessment & Exams | §7 Workflows, §9 FRs | Phase 01 |
| `software_requirements_specification.md` | FR-EXM-001 to FR-EXM-008 | **MOD-01-10** | Continuous Assessment & Exams | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | FR-EXM-009 to FR-EXM-012 | **MOD-01-11** | Grading, GPA & Progression | §9 Functional Requirements | Phase 01 |
| `software_requirements_specification.md` | Module 18, 19, 20 (Progression) | **MOD-01-11** | Grading, GPA & Progression | §11 Business Rules & Logic | Phase 01 |
| `software_requirements_specification.md` | Module 31, 32, 33 (Graduation) | **MOD-01-12** | Graduation & Transcripts | §9 Functional Requirements | Phase 01 |
| `Student Portal Spec.docx` | Sections 1–99 (Complete Spec) | **MOD-01-13** | Unified Student Portal | All Sections §1–31 | Phase 01 |
| `software_requirements_specification.md` | Module 14 (LMS Integration) | **MOD-02-01** | LMS Integration Hub | §22 Integrations / APIs | Phase 02 |
| `software_requirements_specification.md` | Module 15 (Attendance) | **MOD-02-02** | Class & Staff Attendance | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 11 (Advising & Audit) | **MOD-02-03** | Academic Advising & Audit | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 29 & Portal Sec 48–50 | **MOD-02-04** | Industrial Attachment | §7 Workflows, §9 FRs | Phase 02 |
| `software_requirements_specification.md` | Module 30 (Work-Study) | **MOD-02-05** | Work-Study Programme | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 28 (Library Integration) | **MOD-02-06** | Library Management Integration | §22 Integrations / APIs | Phase 02 |
| `software_requirements_specification.md` | Module 25, 26 & Portal Sec 44–47 | **MOD-02-07** | Student Affairs & Elections | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 27 (Hostels) | **MOD-02-08** | Accommodation & Hostels | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 24, 74 & Portal Sec 61–62 | **MOD-02-09** | Student Requests & Clearance | §7 Workflows, §9 FRs | Phase 02 |
| `software_requirements_specification.md` | Module 23 (Financial Aid) | **MOD-02-10** | Scholarships & Financial Aid | §9 Functional Requirements | Phase 02 |
| `software_requirements_specification.md` | Module 67 (Portals - Staff) | **MOD-02-11** | Lecturer & Staff Portals | §8 User Actions, §17 Dashboards | Phase 02 |
| `software_requirements_specification.md` | Module 35, 36 (HR & Recruitment)| **MOD-03-01** | Human Resource Management | §9 Functional Requirements | Phase 03 |
| `software_requirements_specification.md` | Module 23 (Staff Workload) | **MOD-03-02** | Academic Staff Workload | §11 Business Rules & Logic | Phase 03 |
| `software_requirements_specification.md` | Module 38 (Leave Management) | **MOD-03-03** | Leave & Staff Attendance | §7 Workflows, §15 Approvals | Phase 03 |
| `software_requirements_specification.md` | Module 39 (Staff Appraisal) | **MOD-03-04** | Staff Performance & Appraisal | §9 Functional Requirements | Phase 03 |
| `software_requirements_specification.md` | Module 37 (Promotions) | **MOD-03-05** | Staff Promotions Management | §15 Approval Workflows | Phase 03 |
| `software_requirements_specification.md` | Module 40 (Payroll Engine) | **MOD-03-06** | Payroll & Statutory Returns | §11 Business Rules & Schema | Phase 03 |
| `software_requirements_specification.md` | Module 41 (General Ledger) | **MOD-03-07** | General Ledger & Accounts | §12 Data Entities & Schema | Phase 03 |
| `software_requirements_specification.md` | Module 41 (Budgeting) | **MOD-03-08** | Budgeting & Vote-Book | §11 Rules, §15 Approvals | Phase 03 |
| `software_requirements_specification.md` | Module 41 (AP & AR) | **MOD-03-09** | Accounts Payable & Receivable | §7 Workflows, §9 FRs | Phase 03 |
| `software_requirements_specification.md` | Module 42 (Procurement) | **MOD-03-10** | Procurement & Supply Chain | §7 Workflows, §9 FRs | Phase 03 |
| `software_requirements_specification.md` | Module 43, 44 (Inventory/Assets) | **MOD-03-11** | Stores Inventory & Assets | §9 Functional Requirements | Phase 03 |
| `software_requirements_specification.md` | Module 45 (Research Grants) | **MOD-04-01** | Research Grants & Projects | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 46 (Postgraduate) | **MOD-04-02** | Postgraduate & Thesis Tracking | §7 Workflows, §9 FRs | Phase 04 |
| `software_requirements_specification.md` | Module 45 (Ethics Review) | **MOD-04-03** | Research Ethics Review | §15 Approval Workflows | Phase 04 |
| `software_requirements_specification.md` | Module 47 (Quality Assurance) | **MOD-04-04** | Quality Assurance & Audits | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 48 (Senate & Council) | **MOD-04-05** | Senate & Governance | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 49 (Document DMS) | **MOD-04-06** | Enterprise Document System | §24 System Documents | Phase 04 |
| `software_requirements_specification.md` | Module 51, 52 (ICT Helpdesk) | **MOD-04-07** | Helpdesk & ITIL Services | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 53, 54 (Facilities/Fleet)| **MOD-04-08** | Facilities & Fleet Management | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 55 (Campus Security) | **MOD-04-09** | Campus Security & Incidents | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 56 (Health Services) | **MOD-04-10** | Health Center & EHR | §21 Security & Privacy | Phase 04 |
| `software_requirements_specification.md` | Module 34, 58 (Alumni & Tracer)| **MOD-04-11** | Alumni Relations & Tracer | §9 Functional Requirements | Phase 04 |
| `software_requirements_specification.md` | Module 63 (API Gateway) | **MOD-05-01** | Universal API Gateway | §22 API Specifications | Phase 05 |
| `software_requirements_specification.md` | Module 61 (Data Warehouse) | **MOD-05-02** | Data Warehouse & ETL | §12 Dimensional Schema | Phase 05 |
| `software_requirements_specification.md` | Module 59 (Executive BI) | **MOD-05-03** | Institutional Analytics & BI | §17 Dashboards & Widgets | Phase 05 |
| `software_requirements_specification.md` | Module 60 (Retention Engine) | **MOD-05-04** | Student Retention Early Warning| §11 Risk Scoring Rules | Phase 05 |
| `software_requirements_specification.md` | Module 62 (AI Intelligence) | **MOD-05-05** | Student AI Assistant & NLP | §9 Functional Requirements | Phase 05 |
| `software_requirements_specification.md` | Module 50, 75 (Notifications) | **MOD-05-06** | Universal Notification Engine | §16 Notifications & Alerts | Phase 05 |
| `software_requirements_specification.md` | Module 69 (Digital Verification)| **MOD-05-07** | Public Digital Verification | §22 Public Endpoints | Phase 05 |
| `software_requirements_specification.md` | Module 68 & Portal Sec 69 | **MOD-05-08** | University Mobile App | §27 Usability & Offline | Phase 05 |
| `software_requirements_specification.md` | Module 71 & FR-DR-001 to 005 | **MOD-05-09** | Business Continuity & DR | §28 Performance & DR SLOs | Phase 05 |
