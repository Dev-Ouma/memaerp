# Development and Quality Governance

## Required sequence and ownership

| Stage           | Primary executor                                     | Required output                                                       | Approver                 |
| --------------- | ---------------------------------------------------- | --------------------------------------------------------------------- | ------------------------ |
| 1. Requirements | Requirements Architect with university process owner | IDs, workflows, rules, exceptions, acceptance criteria                | Process owner            |
| 2. Architecture | System/Security Architect                            | boundaries, threat model, ADRs, failure modes                         | Architecture Board       |
| 3. Database     | Database/Migration Engineer                          | reviewed schema, constraints, indexes, migration/rollback             | Data Architect           |
| 4. API contract | Backend/API Engineer                                 | OpenAPI change and examples                                           | API + Security reviewers |
| 5. Backend      | Laravel Backend Engineer                             | use cases, policies, transactions, audit, tests                       | Senior reviewer          |
| 6. Frontend     | Next.js Frontend Engineer                            | accessible workflows using generated client                           | UX + API reviewers       |
| 7. Testing      | QA/Test Engineer                                     | unit, integration, contract, authorization, E2E, performance evidence | QA Lead                  |
| 8. Security     | Security Reviewer                                    | threat/control verification and severity findings                     | Security Architect       |
| 9. Integration  | Integration/DevOps Engineer                          | environment, provider contract, telemetry, recovery                   | Service owner            |
| 10. UAT         | University product/process owner                     | signed scenarios and reconciliation                                   | Sponsor/delegate         |
| 11. Deployment  | DevOps/SRE                                           | change record, backup, rollout, rollback, monitoring                  | Change authority         |

No downstream stage cures a missing upstream decision. Parallel work is permitted only where contracts and ownership are stable.

## Review severity

| Severity   | Meaning                                                                                                  | Gate effect                                       |
| ---------- | -------------------------------------------------------------------------------------------------------- | ------------------------------------------------- |
| CRITICAL   | Active compromise, irreversible integrity/privacy loss, or systemic authorization failure                | Stop release; immediate escalation                |
| HIGH       | Likely serious breach, financial/academic corruption, destructive migration, or missing critical control | Blocks merge/release                              |
| MEDIUM     | Material reliability, maintainability, performance, or limited security weakness                         | Fix before feature gate unless formally scheduled |
| LOW        | Localized defect or standards deviation with limited impact                                              | Track and fix normally                            |
| SUGGESTION | Optional improvement with clear trade-off                                                                | Non-blocking                                      |

## Change control

Business-rule changes require owner approval and effective dates. Schema/data-ownership changes require an ADR or design record. Destructive migrations require explicit approval and recovery evidence. Security exceptions include owner, rationale, compensating control, expiry, and review date. A feature is complete only when all criteria in `docs/requirements/acceptance-criteria.md` are evidenced.
