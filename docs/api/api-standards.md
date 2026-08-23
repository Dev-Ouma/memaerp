# API Standards

**Canonical detailed source:** `PLAN/04-API-STANDARDS.md`  
**Contract format:** OpenAPI 3.1, committed and CI-validated

## Resource contract

- Base path is `/api/v1`; plural kebab-case nouns identify resources.
- Workflow transitions use explicit subresources such as `/submit`, `/approve`, `/publish`, and `/reverse`.
- Nesting is limited to two levels. `PATCH` is partial update; `PUT` is complete replacement only.
- Every response includes a request/correlation ID. Errors expose stable machine codes, safe messages, field errors, and trace ID without internals.
- Cursor pagination defaults to 25 and caps at 100. Filters, sorts, includes, and sparse fields are endpoint allow-lists; unknown values return `422`.
- Out-of-scope sensitive resources return `404`; known-but-forbidden actions return `403` only where existence is not sensitive.

## Mutation safety

Money, enrollment, grade, import, and externally retried mutations require idempotency. `Idempotency-Key` is bound to institution, authenticated principal, route, and request hash; reuse with different input returns `409`. Responses are replayable for the documented retention period.

Optimistic concurrency uses a version/ETag and `If-Match` where overwrite risk exists. Capacity and ledger invariants use database transactions/locking. Jobs longer than five seconds return `202` and an authorized job resource; download links are short-lived and authorization is rechecked.

## Security

Each operation declares authentication, permission, scope behavior, field exposure, rate-limit class, audit event, validation, idempotency/concurrency, and abuse controls. Cookie-authenticated mutations require CSRF. CORS uses explicit trusted origins. Uploads use the governed file service. List authorization happens before filtering, counting, or serialization.

## Contract lifecycle

Backend annotations generate `openapi.v1.yaml`; the artifact is committed, linted, and diffed in CI. Breaking changes require a versioned contract and an approved deprecation/migration plan. Generated TypeScript types, Zod schemas, and client code are the only frontend API types. Every endpoint has contract, validation, positive, negative authorization, and failure-path tests.
