# Admissions Frontend API Gaps

The authoritative `docs/api/openapi.yaml` contract contains no Admissions-tagged operations as of 2026-08-25. The frontend therefore uses a typed development-only adapter in `resources/js/admissions/api.ts`; production methods fail explicitly rather than inventing endpoint behavior.

Required contract areas:

- Public programme offerings and offering detail, including filters, publication state, eligibility, deadlines and required documents.
- Applicant registration, email verification, password recovery and authenticated session behavior.
- Applicant profile, draft creation, section-level autosave, optimistic-lock version and missing-field summary.
- Private document upload sessions, progress, cancellation, malware status, verification and rejection reasons.
- Idempotent payment initiation, authoritative status refresh, receipt download, delayed callbacks, reversal and Finance-authorised waiver.
- Idempotent application submission, immutable receipt, timeline and information-request responses.
- Offer letter PDF, QR verification, acceptance, rejection, withdrawal and deferral.
- Staff queues, saved filters, assignment, review, scoring, approvals, decisions, communication and audit history.
- Analytics dimensions/KPIs, report definitions, asynchronous exports and download history.
- Permission and scope payloads for all applicant and staff actors.

The current Laravel web controllers provide a limited local implementation, but they are not represented in OpenAPI and must not be treated as the agreed REST contract.
