# Acceptance Criteria Baseline

These criteria supplement module-level SRSD criteria. Evidence is required; a verbal assertion is insufficient.

| ID          | Given / when / then acceptance criterion                                                                                                                                                                    | Required evidence                       |
| ----------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------- |
| AC-AUTH-001 | Given a caller without permission or outside scope, when any list/detail/mutation/export endpoint is called, then the API returns the approved denial response and reveals no out-of-scope record or count. | Automated negative tests per role/scope |
| AC-DATA-001 | Given an existing person match, when a new applicant/student/staff lifecycle begins, then the existing person is linked or a review is raised; no duplicate is silently created.                            | Integration and concurrency tests       |
| AC-ADM-001  | Given a partially completed application, when the applicant resumes from another authenticated session, then all committed fields and documents are restored and unauthorized users cannot access them.     | E2E and authorization tests             |
| AC-REG-001  | Given concurrent attempts for the last section place, when requests commit, then at most one succeeds, capacity is not exceeded, and retries are idempotent.                                                | 2× peak load test and DB assertions     |
| AC-GRD-001  | Given published results, when any user attempts direct modification, then it fails; an authorized amendment preserves the original and produces a complete approval/audit chain.                            | Feature, DB, and tamper tests           |
| AC-FIN-001  | Given duplicate or reordered provider callbacks, when processed, then exactly one ledger effect is posted and reconciliation remains balanced.                                                              | Contract and replay tests               |
| AC-WFL-001  | Given an approved workflow configuration change, when activated, then new cases follow the new version and in-flight cases remain reproducible under their original version.                                | Workflow integration tests              |
| AC-AUD-001  | Given a protected event, when completed or denied, then actor/service identity, authority, scope, reason, timestamp, request ID, and permitted before/after data are queryable and tampering is detected.   | Audit inspection and adversarial test   |
| AC-CMS-001  | Given approved content, when published, then it is accessible, versioned, reversible, cache-invalidated, and meets WCAG/SEO rules without exposing drafts.                                                  | E2E, accessibility, and cache tests     |
| AC-OPS-001  | Given a transient integration/job failure, when retry/replay occurs, then backoff is applied, correlation is retained, and no duplicate business effect occurs.                                             | Failure-injection test                  |
| AC-PRV-001  | Given a verified data-subject request, when fulfilled, then authorized personal data is produced/redacted within policy and the fulfillment is audited without violating other subjects' rights.            | UAT and DPO approval                    |
| AC-REP-001  | Given a certified report value, when drilled through, then its source, transformation version, refresh time, and quality status are identifiable.                                                           | Data lineage and reconciliation test    |
| AC-TEN-001  | Given two seeded institution contexts, when automated isolation tests run, then reads, writes, caches, jobs, files, search, reports, and uniqueness constraints cannot cross institutions.                  | Automated isolation suite               |
| AC-DR-001   | Given loss of the primary database, when the restore runbook is executed, then service is restored within RTO and reconciled within RPO.                                                                    | Timed restore report                    |

## Feature quality gate

- Requirements, business rules, acceptance criteria, and owner approval are linked.
- Schema, contracts, authorization, validation, transactions, audit, and failure behavior conform.
- Unit, integration, negative-authorization, contract, and risk-appropriate E2E tests pass.
- Security, accessibility, performance, operational readiness, migration, and rollback evidence exists as applicable.
- User and operational documentation are current; no undocumented architectural exception remains.
