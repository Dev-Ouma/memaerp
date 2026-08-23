# Non-Functional Requirements

**Status:** Draft; capacity assumptions require confirmation under D-005.

| ID            | Category          | Verifiable requirement                                                                                                                                                           |
| ------------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| NFR-PERF-001  | API latency       | Ordinary interactive API operations shall meet p95 ≤ 500 ms and p99 ≤ 1 s under expected load, excluding declared asynchronous jobs.                                             |
| NFR-PERF-002  | Registration      | Registration shall meet p95 < 1 s at 5,000 concurrent students with zero section oversubscription.                                                                               |
| NFR-PERF-003  | Web               | Public and student-facing pages shall meet the budgets in `PLAN/11-DESIGN-SYSTEM-AND-UX.md`; performance shall be measured in CI and production.                                 |
| NFR-SCALE-001 | Initial scale     | The design shall support the planning assumption of 10,000 students, 800 staff, 1–3 campuses, and 5,000 peak concurrent registrations.                                           |
| NFR-SCALE-002 | Growth            | Stateless web/API nodes and isolated queue workers shall scale horizontally without changing domain code.                                                                        |
| NFR-AVL-001   | Availability      | Production target is 99.9% monthly availability, excluding approved maintenance, pending contractual confirmation.                                                               |
| NFR-DR-001    | Recovery          | Database RPO ≤ 5 minutes and service RTO ≤ 4 hours; immutable documents RPO ≤ 24 hours unless classified financial or academic evidence.                                         |
| NFR-DR-002    | Restore proof     | Backups are not accepted until a restore has been completed, timed, reconciled, and documented.                                                                                  |
| NFR-SEC-001   | Transport/storage | Confidential data shall be encrypted in transit and at rest; restricted fields require application-level encryption and governed key rotation.                                   |
| NFR-SEC-002   | Access            | MFA is mandatory for privileged users; all endpoints enforce permission plus scope; sensitive exports and reads are audited.                                                     |
| NFR-SEC-003   | Assurance         | No Critical or High security finding may remain open at a production gate without documented risk acceptance by the accountable executive and security owner.                    |
| NFR-PRV-001   | Privacy           | Processing shall comply with the Kenya Data Protection Act, including purpose limitation, data-subject requests, retention, breach response, and DPIAs for high-risk processing. |
| NFR-AUD-001   | Auditability      | Financial, grade, access-control, and approval events shall be attributable, timestamped, correlated, append-only, and retained according to approved policy.                    |
| NFR-ACC-001   | Accessibility     | User interfaces shall conform to WCAG 2.2 AA; keyboard, screen-reader, contrast, zoom, and focus behavior are release criteria.                                                  |
| NFR-OBS-001   | Observability     | Requests, jobs, and integrations shall share a correlation ID; structured logs, metrics, traces, health checks, and actionable alerts are required.                              |
| NFR-MNT-001   | Maintainability   | Module dependency checks, static analysis, formatting, contract checks, tests, and security scans shall block non-conforming merges.                                             |
| NFR-INT-001   | Integrity         | Money, enrollment, and grade operations shall be transactional, idempotent where replayable, and protected from concurrent lost updates.                                         |
| NFR-COMP-001  | Compatibility     | Supported browsers are the current and previous major versions of Chrome, Edge, Firefox, and Safari; mobile layouts support 360 px and wider.                                    |
| NFR-TEN-001   | Tenant readiness  | Every domain row, unique constraint, index, cache key, object path, event, job, and audit record shall carry or derive institution context.                                      |

## Targets requiring stakeholder confirmation

Availability/SLA exclusions, retention periods, data residency, peak load, annual growth, hosting budget, support hours, browser/device constraints, and final RPO/RTO are assumptions until approved. Their owners and deadlines are tracked in `docs/requirements/assumptions.md`.
