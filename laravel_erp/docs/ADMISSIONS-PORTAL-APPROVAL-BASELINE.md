# Admissions Portal Approval Baseline

**Prepared:** 2026-09-04  
**Decision:** Approval required before production-completion implementation  
**Current environment:** Local Laravel application on PostgreSQL; debug enabled; email transport set to log; private local filesystem; database queue

## Executive decision

The current ERP contains a working, database-backed admissions vertical slice. In the local development environment an applicant can register, save an application, upload a document, record a sandbox payment, submit, pass through staff review statuses, receive and accept an offer, and be converted idempotently into a student record. The live Admissions Command Centre is available to the seeded registrar.

The portal is **not production-ready** and must not yet process real applicants or real payments. The current applicant web payment controller creates a `PAID` transaction directly and labels it sandbox-confirmed; it does not initiate or verify an M-Pesa or Stripe transaction. Several required identity-verification, notification, document-security, offer-acceptance, PDF, external provisioning, operational-security, and disaster-recovery controls also remain incomplete.

## Proposed technology stack

- **Application:** Laravel 13 on PHP 8.5, using server-rendered Blade views and versioned REST APIs.
- **Database:** PostgreSQL as the system of record, with database-enforced uniqueness and transactional workflow changes.
- **Queues/cache/sessions:** Redis in production; database drivers are acceptable only for local development.
- **Documents:** Private S3-compatible object storage, server-side encryption, short-lived signed downloads, checksum validation, and an asynchronous malware-scanning quarantine.
- **Payments:** Provider adapters behind a common payment service; M-Pesa Daraja and Stripe webhooks are the only authority for automatic payment confirmation. Manual bank payments use a maker-checker finance queue.
- **Notifications:** Queued email, SMS, and in-portal notifications through an outbox so business transactions and delivery retries remain consistent.
- **PDF and verification:** Server-generated, versioned admission-letter PDFs with a public minimal-data verification endpoint, revocable verification token, and QR code.
- **Deployment:** Containerized application, scheduler, queue worker, PostgreSQL, Redis, object storage, reverse proxy/TLS, centralized logs, metrics, alerting, encrypted backups, and restore drills.

## Proposed architecture

```text
Applicant / Staff Browser
          |
     TLS + WAF
          |
Laravel Web + Versioned API
  |       |        |       |
RBAC   Workflow  Payments  Notifications
  |       |        |       |
PostgreSQL   Outbox/Queue   Provider adapters
  |             |          |-- M-Pesa Daraja
Audit chain     Workers     |-- Stripe
  |             |          |-- SMS / Email
Private object storage      `-- SIS / LMS / Identity APIs
  `-- Quarantine -> Malware scan -> Released documents
```

Business status changes, payment-event claims, generated-document versions, and student-conversion claims must be transactional and idempotent. Provider retries must resolve to the same local record rather than create duplicates.

## Core database design

The existing schema already establishes most required aggregates: users and role grants; applicant profiles and contact details; programme offerings and intakes; applications, section drafts, immutable submitted versions and status history; requirements, documents, verifications and access logs; payment attempts, transactions, provider events, reconciliations and receipts; review assignments, scores, decisions and approval steps; offers, responses, generated documents and verification tokens; admission rolls; communications/outbox records; student conversions; students; setup definitions/versions/usages; and append-only audit events.

Before production, the following invariants must be enforced and tested on PostgreSQL:

1. Verified email, phone and identity document values are normalized and unique for active applicant identities.
2. Applicant, application, receipt, offer, admission, and registration numbers use concurrency-safe database sequences and configurable formats.
3. Every provider event and external provisioning request has a unique provider/idempotency key.
4. A submitted application points to an immutable checksum-protected version.
5. A document is not usable until its malware status is clean; every read is authorized and logged.
6. A final decision is published only after the configured approval ladder and confirmation step.
7. One application can produce at most one active student conversion and one student identity.
8. Previously issued letter versions and their download history are retained according to policy.

## Roles and authorization boundary

Approve these operational roles as distinct database-driven assignments:

| Role | Required scope |
|---|---|
| Applicant | Own profile, applications, documents, payments, offers and credentials only |
| Admissions Officer | Assigned/all permitted applications, document checks, correction requests and non-final workflow actions |
| College Registrar | Final approval/publishing, admission numbers, letters and admission rolls |
| HoD/Dean Reviewer | Department/programme-scoped reviews, scores and recommendations; no finance powers |
| Finance Officer | Payment/reconciliation/refund/waiver records; no academic decision powers |
| System Administrator | Platform configuration and account administration; no automatic operational-role bypass |

Staff MFA, scoped and expiring grants, separation of duties, and audit evidence are release gates.

## Canonical application statuses

The UI labels in the requirement map to the persisted workflow as follows:

| User-facing status | Persisted status |
|---|---|
| Draft | `DRAFT` |
| Awaiting Documents | derived from incomplete required-document checks |
| Awaiting Payment | derived from unpaid mandatory fee |
| Submitted | `SUBMITTED` |
| Under Review | `UNDER_REVIEW` |
| Additional Information Required | `INFO_REQUESTED` or `RETURNED_FOR_CORRECTION` |
| Approved | `APPROVAL_PENDING` / approved decision not yet published |
| Rejected | `REJECTED` |
| Waitlisted | `WAITLISTED` |
| Admitted | `ADMITTED` |
| Credentials Issued | successful provision plus activation issued; add an explicit persisted milestone |

Derived statuses must come from server-side requirement/payment/document checks rather than manual display-only flags.

## Payment flow for approval

1. Resolve the effective fee and enabled channel from versioned configuration.
2. Create a `PENDING` payment attempt with an idempotency key and applicant/application references.
3. Initiate M-Pesa STK or Stripe Checkout/Payment Intent server-side, or present Paybill/Till/bank instructions.
4. Never mark the attempt paid from a browser redirect or applicant-entered transaction code.
5. Verify the signed callback/webhook, atomically claim the provider event, validate amount/currency/reference, and transition the attempt.
6. Record status history for completed, failed, cancelled, timed out, reversed, refunded and disputed outcomes.
7. Reconcile unmatched Paybill/Till/bank events automatically where possible and through a maker-checker finance queue otherwise.
8. Generate an immutable receipt only after confirmed payment or an authorized waiver.

Pochi la Biashara must remain disabled until Safaricom confirms a supported, lawful merchant API and reconciliation contract for the institution.

## Admissions and provisioning workflow for approval

```text
Registration -> email/phone verification -> permanent applicant reference
-> programme/intake draft -> required-document validation -> confirmed fee
-> declaration + immutable submission -> triage assignment
-> document verification -> department review -> registrar approval confirmation
-> publish admitted/rejected/waitlisted decision
-> versioned letter + notifications -> applicant accepts/declines
-> acceptance fee/enrolment documents/clearance gates
-> idempotent SIS/ERP/LMS/email provisioning
-> one-time activation -> Credentials Issued
```

Provisioning must use a durable outbox, stable external correlation ID, retryable error classification, per-system response ledger, administrator alerting, and compensation/manual-recovery procedures. Permanent passwords must never be generated for delivery; issue a short-lived, single-use activation token instead.

## Requirement coverage verdict

| Area | Current verdict | Production gap |
|---|---|---|
| Roles/RBAC | Partial | Required named roles are not consistently enforced as distinct operational scopes; staff MFA release gate remains |
| Registration/authentication | Partial | Web registration omits identity number at account creation; verified email/phone and duplicate prevention across normalized verified identifiers are incomplete |
| Applicant dashboard | Partial | Core application/status/offer data exists; full action-item, document-requirement, notification, credential and canonical progress coverage remains |
| Programme application | Partial | Catalogue, offering selection, draft save, declaration and submission exist; complete multi-section data model/UX, configurable requirements and printable summary remain |
| Documents | Partial | Private local upload, validation, hash, staff download and verification exist; configurable full requirements, replacement/version history, quarantine/malware scan, progress and applicant preview remain |
| Submission | Partial | Server-side paid/completion/document checks, immutable snapshot and submitted timestamp exist; requirement-level validation, acknowledgement download and real multi-channel confirmation remain |
| Payments | Prototype only | Current web path self-confirms a sandbox payment; real adapters, signature verification, callbacks/webhooks, reversals/refunds/disputes, reconciliation and receipt PDF remain |
| Admissions review | Partial | Database-backed workspaces, filters, assignment, review, status actions, exports and audit exist; stricter role/programme scoping, publish confirmation, correction communication and controlled batch safeguards remain |
| Decision/offer | Partial | Offers, waitlist/rejection/admit transitions and public token page exist; acceptance evidence (IP/declaration), expiry enforcement/reminders, appeal workflow and privacy-reviewed public verification remain |
| Admission letter | Partial | HTML letter exists; secure PDF generation, template/version retention, QR generation, signatures and download history are incomplete |
| Acceptance/enrolment | Partial | Accept/decline and student conversion exist; configurable clearance gates, acceptance payment/documents and acknowledgement PDF are incomplete |
| Credentials/provisioning | Local-only partial | Local student promotion is idempotent and retryable; SIS/LMS/email adapters, response ledger per system, one-time activation and credential-expiry UX remain |
| Administration/reports | Partial | Versioned setup catalogue and many reports exist; all runtime services do not yet resolve and record effective setup versions |
| Notifications | Foundation only | Communication schema/outbox foundation exists; real email/SMS/in-portal templates, transports, retries, privacy filters and event coverage remain |
| Security/compliance | Partial | Hashing, CSRF, validation, authorization, idempotency and audit foundations exist; production TLS/WAF, staff MFA, rate-limit coverage, malware scanning, secrets, retention execution, backup/restore and monitoring evidence remain |
| UX/accessibility | Partial | Responsive branded Blade UI exists; formal WCAG audit, low-bandwidth budget, upload progress, end-to-end autosave and real-time provider status remain |
| Delivery/operations | Partial | Migrations, seeders, tests, OpenAPI slice and environment template exist; provider configuration, Docker production topology, runbooks, backup restore evidence, alerting and full API documentation remain |

## External integration decisions required

Approval must identify:

- M-Pesa Daraja business short code(s), passkey/certificates, callback host, transaction types, settlement/reversal policy, and sandbox/production owners.
- Stripe account, currencies, allowed methods, refund/dispute owners, webhook endpoint and signing secret custody.
- SMS provider, sender ID approval, delivery-report callback, opt-out/compliance rules, and cost controls.
- Transactional email provider, verified domain, DKIM/SPF/DMARC ownership and bounce/complaint handling.
- Private object-storage provider, region/residency, retention class, encryption keys and malware-scanner service.
- Target SIS/ERP, LMS and institutional email APIs, schemas, service accounts, idempotency/correlation support and failure SLAs.
- Institution logo/name/signatories, approved letter/rejection templates, privacy notice/consent text, retention schedule and support contacts.
- Production domain, hosting region, TLS/WAF, backup vault, monitoring/alerting destinations and incident owners.

## Delivery plan after approval

1. **Release-blocking truth and security:** remove self-confirming payments; complete identity verification/uniqueness; enforce named roles, staff MFA and decision confirmation; normalize status presentation.
2. **Documents and submissions:** configurable requirement engine, quarantined uploads, malware scanning, replacement/version/audit flows, submission acknowledgements and notifications.
3. **Real payments:** M-Pesa and Stripe adapters, signed event handlers, reconciliation, finance controls, receipt PDFs and failure/refund/reversal handling.
4. **Offers and letters:** configurable approval ladder, applicant-safe decisions, expiry/appeal flows, versioned signed PDFs, QR verification and download evidence.
5. **Provisioning:** clearance-policy engine, outbox-driven SIS/LMS/email provisioning, one-time activation, retry/alert/recovery console.
6. **Production operations:** container deployment, secrets, queues, scheduler, HTTPS/WAF, backups and restore drill, centralized telemetry, security/accessibility/performance testing and release rehearsal.

## Verification performed for this baseline

- PostgreSQL migration status: all current migrations ran successfully.
- Targeted admissions suite: 41 tests, 330 assertions, all passed.
- Full Laravel suite: 158 tests, 1,394 assertions, all passed.
- Production frontend asset build: passed.
- Admissions controllers and services formatting check: passed.
- Live browser login as the seeded registrar: passed.
- Live `/admissions/dashboard`: rendered database-backed pipeline metrics and work links.

Passing tests establish the implemented local behavior; they do not certify unimplemented provider, infrastructure, security, accessibility, privacy or recovery controls.

## Approval requested

Approve the proposed stack, architecture, schema invariants, role boundary, status mapping, payment flow, admissions workflow, provisioning design, external-integration decisions, and six-phase delivery plan above. Until approval and production integrations are completed, use the current portal only with synthetic data and sandbox/manual demonstration transactions.

## Approved implementation progress — 2026-09-04

The baseline was approved and the first release-blocking slice was implemented:

- Applicant payment actions now resolve the effective database fee and create a provider-neutral attempt. Outside the explicit test-only switch, the attempt remains `INITIATED`; no ledger transaction or receipt is created and submission remains blocked.
- M-Pesa STK initiation requires a phone number, payer phone data is masked, and every attempt receives independent idempotency and correlation identifiers.
- `PAYMENT_SANDBOX_AUTO_CONFIRM` is documented as test/demo-only and defaults to false in the environment template.
- Applicant application lookup now groups ID/reference alternatives beneath the ownership scope, closing an `OR`-condition record-disclosure path.
- Staff no longer bypass ownership checks on applicant-only update, upload, payment, offer-response, or enrolment endpoints.
- Offer acceptance now requires an explicit declaration, rejects expired offers, and writes an immutable response record containing timestamp, IP address, user agent, policy versions, correlation ID, and an evidence hash.
- Three new regression tests cover truthful pending payments, applicant-endpoint ownership, and tamper-evident acceptance evidence.

Verification after this slice: **161 tests passed with 1,408 assertions**, targeted formatting passed, and the production asset build passed.

The remaining production blockers are real M-Pesa/Stripe adapters and signed callbacks, identity/contact verification and duplicate prevention, malware scanning/object storage, configurable clearance gates, generated PDF/QR evidence, external SIS/LMS/email provisioning, staff MFA, and production infrastructure credentials.
