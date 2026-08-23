# Business Rules Register

Rules marked **Pending** are not implementation authority. They require owner approval and effective dating where outcomes may need historical reproduction.

| ID          | Rule                                                                                                                                                                            | Owner                       | Status                |
| ----------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------- | --------------------- |
| BR-ID-001   | One natural person has one canonical person record; lifecycle roles attach to it. Duplicate candidates require governed merge, never silent deletion.                           | Registrar / HR              | Approved architecture |
| BR-TEN-001  | Every domain record belongs to exactly one institution; institution context is mandatory and cannot be client-selected without authorization.                                   | Data Governance             | Approved architecture |
| BR-AUTH-001 | Absence of explicit permission and valid scope denies access. UI visibility is not authorization.                                                                               | Security Officer            | Approved architecture |
| BR-AUTH-002 | Platform administration does not imply access to academic, financial, HR, clinic, or counselling content.                                                                       | Data owners                 | Approved architecture |
| BR-WFL-001  | Approval matrices, thresholds, delegations, and conflict rules are versioned configuration owned by the relevant process owner.                                                 | Process owners              | Approved architecture |
| BR-ACA-001  | Curriculum, grading, fee, payroll, and statutory rules are effective-dated; historical results reproduce using the rule active at the relevant event.                           | Senate / Finance / HR       | Approved architecture |
| BR-REG-001  | Registration succeeds only when all configured academic, status, capacity, timetable, hold, and financial gates pass or an authorized override exists. Exact rules are pending. | Registrar / Senate / Bursar | Pending D-009/D-010   |
| BR-GRD-001  | Published results are immutable. Corrections use a reasoned, approved amendment preserving the original.                                                                        | Senate                      | Approved architecture |
| BR-GRD-002  | Entry, moderation, verification, approval, and publication conflicts are enforced by transaction, not merely by role labels.                                                    | Senate                      | Pending exact matrix  |
| BR-FIN-001  | Posted ledger entries are never edited or deleted; corrections use linked reversal and replacement entries. Debits equal credits for every posting batch.                       | Finance Director            | Approved architecture |
| BR-PAY-001  | A payment callback is evidence, not truth, until authenticated, deduplicated, and confirmed under the provider contract.                                                        | Bursar                      | Approved architecture |
| BR-PROC-001 | Procurement and payment stages enforce configured maker-checker and value thresholds; emergency exceptions retain evidence and retrospective review.                            | Procurement / Finance       | Pending D-010         |
| BR-RET-001  | Retention and disposal are class-specific, legally reviewed, automated, suspended by legal hold, and evidenced.                                                                 | DPO / Records Officer       | Pending schedule      |
| BR-INT-001  | Each integrated datum has one named system of record; retries never create duplicate business effects.                                                                          | Integration owner           | Approved architecture |
| BR-DEL-001  | A module may be scheduled only after the modules whose authoritative data it consumes have passed their gate.                                                                   | Architecture Board          | Approved architecture |

## Prohibited developer assumptions

Do not hard-code grading bands, GPA/retake rules, fee-clearance thresholds, approval chains, procurement limits, payroll rates, number formats, retention periods, or provider-specific behavior before the accountable owner approves them.
