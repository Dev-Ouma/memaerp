# Assumptions and Open Decisions

An assumption permits planning, not irreversible implementation. The linked decision register in `PLAN/12-OPEN-DECISIONS.md` remains authoritative.

| Decision                | Working assumption                                                                                                                            | Owner              | Needed by                     | Reversal risk |
| ----------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | ----------------------------- | ------------- |
| D-002 Operations        | University ICT operates a two-server Docker deployment with support agreement.                                                                | ICT Director       | Phase 0                       | Medium        |
| D-003 Financial year    | Financial year closes 30 June.                                                                                                                | Bursar             | Before GL planning            | High          |
| D-004 Legacy estate     | SIS, finance, and spreadsheets; 3–5 years transactional history plus complete academic history.                                               | ICT Director       | Phase 0 week 1                | Very high     |
| D-005 Scale             | 10,000 students, 800 staff, 1–3 campuses, 5,000 peak registration users.                                                                      | Registrar          | Performance design            | Medium        |
| D-006 Website           | New CMS-backed website replaces current site; content migration excluded until assessed.                                                      | Marketing / ICT    | Before Sprint 8               | Medium        |
| D-007 Moodle            | Existing Moodle 4.x; ERP owns enrollment and final grades.                                                                                    | Academic / ICT     | Before Sprint 15              | Medium        |
| D-008 Payments          | M-Pesa Paybill/STK plus one bank statement import; no Phase-1 cards.                                                                          | Bursar             | Before Sprint 11              | Medium        |
| D-009 Academic rules    | Rules will be supplied as effective-dated Senate-approved configurations. Placeholder scales are test fixtures only.                          | Academic Registrar | Before Sprint 14              | High          |
| D-010 Approvals         | Approval engine is configurable; exact matrices are supplied by each owner.                                                                   | Process owners     | Before Sprint 3 configuration | Low by design |
| D-011 Payroll           | Current Kenyan statutory configuration, with two parallel payroll cycles.                                                                     | HR / Finance       | Before Sprint 26              | High          |
| D-012 Domains/providers | `www`, `apply`, `student`, `lecturer`, `staff`, `admin`, `exec`; Cloudflare; providers TBD.                                                   | ICT Director       | Before Sprint 4               | Low           |
| D-013–020               | Existing palette; English with i18n readiness; configurable IDs; adapter-based biometrics; OIDC Phase 5; Koha; Kenyan residency; WCAG 2.2 AA. | Listed owners      | Phase-dependent               | Varies        |

## Additional NFR decisions required

| ID        | Decision                                            | Proposed default                    | Owner                 |
| --------- | --------------------------------------------------- | ----------------------------------- | --------------------- |
| NFR-D-001 | Contractual availability and maintenance exclusions | 99.9% monthly                       | Project Board / ICT   |
| NFR-D-002 | Final RPO/RTO by data class                         | DB 5 min / 4 h                      | ICT / data owners     |
| NFR-D-003 | Record retention schedule                           | No default; legal schedule required | DPO / Records Officer |
| NFR-D-004 | Hosting and operational budget                      | No default                          | Sponsor / ICT         |
| NFR-D-005 | Support coverage and incident SLAs                  | Business hours plus P1 on-call      | ICT Director          |

Decisions are due within five working days of formal request. Overdue high-risk decisions escalate to the Project Board.
