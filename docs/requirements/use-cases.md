# Cross-Module Use Cases

Detailed module use cases are maintained in the SRSD. These journeys define cross-module behavior and integration points.

## UC-01: Applicant to active student

**Actors:** Applicant, Admissions Officer, approvers, payment provider, Registrar.  
**Preconditions:** Programme/intake and eligibility rules are published.  
**Flow:** create identity → save application → upload scanned documents → pay fee → submit → assess → approve/reject → issue offer → accept → matriculate → assign student number.  
**Controls:** self-only access, malware scanning, payment idempotency, approval scopes, duplicate-person detection, complete audit.  
**Outcome:** one person record and one governed student record; no duplicate identity.

## UC-02: Semester registration

**Actors:** Student, advisor/Registrar, Finance, Enrollment service.  
**Flow:** resolve eligible curriculum → validate status, prerequisites, credit load, clashes, holds, fee clearance, and capacity → lock capacity → commit registration and ledger consequences atomically → queue notifications/LMS sync.  
**Exceptions:** closed window, unmet prerequisite, hold, capacity race, concurrent retry, approved override.  
**Outcome:** registration is immediately authoritative; no oversubscription.

## UC-03: Assessment to published result

**Actors:** Lecturer, Moderator, Exam Officer, Senate authority, Student.  
**Flow:** enter marks → validate assessment composition → submit and hash → lock → moderate → verify → approve → publish → calculate progression → expose to student.  
**Exceptions:** incomplete marks, conflict of duties, late mark, special exam, approved amendment.  
**Outcome:** published results are frozen; amendments reverse rather than overwrite.

## UC-04: Payment to student ledger

**Actors:** Student/payer, provider, Finance Officer.  
**Flow:** initiate/reference payment → receive signed callback → deduplicate → confirm independently with provider → post balanced ledger entry → issue receipt → reconcile settlement.  
**Exceptions:** duplicate callback, amount/reference mismatch, timeout, chargeback, approved reversal.  
**Outcome:** provider evidence, ledger, and reconciliation status remain traceable.

## UC-05: Procure to pay

**Actors:** Requester, budget owner, procurement, evaluator, approver, storekeeper, AP, payment approver.  
**Flow:** requisition → budget reservation → sourcing/evaluation → approval → PO → receipt → three-way match → invoice → payment → GL posting.  
**Controls:** thresholds, segregation of duties, immutable evidence, no payment without configured exception workflow.

## UC-06: Employee to payroll posting

**Actors:** HR, Payroll Officer, approvers, Finance.  
**Flow:** maintain contract and pay inputs → calculate payroll with effective-dated rules → validate exceptions → approve → create bank/statutory outputs → post GL → issue payslips.  
**Controls:** two-cycle parallel run, maker-checker, encrypted bank/tax identifiers, closed-period controls.

## UC-07: Sensitive record access

**Actors:** Clinician/counsellor, data owner, audited break-glass authority.  
**Flow:** authenticate with MFA → evaluate explicit functional permission and assignment → decrypt minimum fields → audit the read → alert abnormal access.  
**Outcome:** ERP/platform administration alone never grants content access.

## UC-08: Authorized export/report

**Actors:** Authorized staff member, report worker.  
**Flow:** authorize fields and row scope → record purpose → queue generation → re-check authorization at execution/download → create short-lived signed URL → audit download.  
**Exceptions:** access revoked, oversized result, sensitive export approval missing.
