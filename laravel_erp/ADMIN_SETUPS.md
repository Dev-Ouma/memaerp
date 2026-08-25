# Admission Admin Setups

`Admin Setups` is the authoritative configuration registry for the Admission Module. The complete key catalogue, category, operational consumer and fail-closed behaviour live in `SetupCatalogue`.

## Contract

- Definitions identify a stable configuration key; versions contain the configuration payload.
- Versions move through `DRAFT`, `ACTIVE`, `INACTIVE` and `ARCHIVED`.
- Active resolution requires the requested date to fall inside `effective_from`/`effective_to`.
- Publishing rejects overlapping active effective periods.
- Operational records retain the exact version through `admin_setup_usages`.
- Versions referenced by historical transactions cannot be archived or deleted.
- Newly published versions affect only subsequent resolver calls. Historical usage is never recalculated.
- Missing active configuration raises `CONFIGURATION_MISSING`; operational code must not fall back silently.
- Active values are cached for ten minutes by key/date and invalidated on publish or status change.
- Management requires platform configuration authority; web management is restricted to administrators.
- Lifecycle events are written to the append-only audit chain.

## Interfaces

- Web catalogue: `/admissions/setups`
- Dedicated setup page: `/admissions/setups/{setup}`
- API catalogue: `GET /api/v1/admin/setups`
- API detail/history: `GET /api/v1/admin/setups/{setup}`
- Create version: `POST /api/v1/admin/setups/{setup}/versions`
- Publish version: `POST /api/v1/admin/setups/versions/{version}/publish`

## Current operational wiring

Payment initiation consumes `payment.application_fee` and `payment.channels_providers`, records both version IDs, and rejects unconfigured channels. The application stores the resolved amount/currency used by the subsequent submission gate.

Remaining operational consumers are tracked in `BACKEND_GAPS.md`; they must use `SetupResolver::use()` as each workflow is published.
