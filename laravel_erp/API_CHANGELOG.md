# Admission API changelog

## 2026-08-25 — v1 foundation

- Published public programme-offering list/detail endpoints with cache and cursor semantics.
- Published applicant registration, login and token revocation.
- Published owned application create/list/detail/draft-update endpoints.
- Published idempotent payment-attempt initiation and submission endpoints.
- Standardised success envelopes, RFC 7807-style problems, correlation IDs and bearer-token authentication.
- Submission remains server-gated by complete declarations, required evidence and an authoritative KES 1,000 `PAID`/authorised `WAIVED` state.

This is additive. No prior `/api/v1` admission operation was removed or changed.
