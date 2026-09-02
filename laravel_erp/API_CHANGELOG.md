# Admission API changelog

## 2026-08-25 — v1 foundation

- Published public programme-offering list/detail endpoints with cache and cursor semantics.
- Published applicant registration, login and token revocation.
- Published owned application create/list/detail/draft-update endpoints.
- Published idempotent payment-attempt initiation and submission endpoints.
- Standardised success envelopes, RFC 7807-style problems, correlation IDs and bearer-token authentication.
- Submission remains server-gated by complete declarations, required evidence and an authoritative KES 1,000 `PAID`/authorised `WAIVED` state.

This is additive. No prior `/api/v1` admission operation was removed or changed.

## 2026-08-29 — Recycle Bin governance

- Replaced entity-type/id restore routes with deletion-record identifiers.
- Replaced immediate permanent purge with purge-request and independent approval operations.
- Added retention, legal-hold and conflict checks plus mandatory reasons.
- Disabled unsafe bulk purge and bulk restore operations; the legacy routes now return explicit denial responses.

## 2026-08-25 — Admin Setups foundation

- Added 47 authoritative setup definitions with effective-dated immutable versions.
- Added secure setup catalogue, detail, version creation and publication endpoints.
- Added cache invalidation, overlap validation, audit events and historical usage references.
- Payment initiation now consumes versioned application-fee and payment-channel setup records.
