# Admission Module — Database Migration Plan

**Database:** PostgreSQL 16 (local) / 17 (target)
**Application:** `laravel_erp`
**Rule:** additive only. No `DROP TABLE`, no `DROP COLUMN`, no destructive type narrowing.

---

## 1. Migration set

| Order | File | Purpose |
|---|---|---|
| 1 | `2026_08_25_000100_create_platform_foundation_tables` | `permissions`, `roles`, `role_permissions`, `user_roles`, `api_tokens`, `number_sequences`, `idempotency_keys`, `outbox_events`, `audit_events`, `consent_records`, `legal_holds`, `retention_rules`, `email_verification_tokens`, `login_attempts` |
| 2 | `2026_08_25_000200_create_institution_setup_tables` | `institutions`, `campuses`, `faculties`, `departments`, `programmes`, `counties`, and additive columns on `courses`, `admission_intakes`, `programme_offerings` |
| 3 | `2026_08_25_000300_create_people_and_applicant_tables` | `people`, `contact_points`, `addresses`, additive columns on `applicant_profiles` |
| 4 | `2026_08_25_000400_extend_application_core_tables` | additive columns on `admission_applications`, `application_versions`, `application_status_history`; new `application_section_drafts`, `education_history`, `employment_history`, `referee_requests`, `referee_responses` |
| 5 | `2026_08_25_000500_create_document_governance_tables` | `document_requirements`, `document_verifications`, `document_access_logs`, additive columns on `application_documents` |
| 6 | `2026_08_25_000600_create_payment_tables` | `payment_fee_setups`, `payment_transactions`, `payment_provider_events`, `payment_receipts`, `payment_reconciliations`, `payment_waivers`, additive columns on `application_payment_attempts` |
| 7 | `2026_08_25_000700_create_workflow_and_review_tables` | `workflow_definitions`, `workflow_instances`, `workflow_steps`, `review_assignments`, `review_checklists`, `scoring_rubrics`, `scoring_criteria`, `review_scores`, `decisions`, `decision_conditions`, `approval_steps` |
| 8 | `2026_08_25_000800_create_offer_and_roll_tables` | `offer_responses`, `generated_documents`, `qr_verification_tokens`, `admission_rolls`, `admission_roll_entries`, `student_conversions`, additive columns on `admission_offers` |
| 9 | `2026_08_25_000900_create_communication_and_reporting_tables` | `notification_templates`, `communications`, `report_definitions`, `export_jobs` |
| 10 | `2026_08_25_001000_create_admission_integrity_constraints` | PostgreSQL-only: partial unique indexes, check constraints, append-only audit trigger, updated-at guards |

## 2. Critical constraints introduced

| Constraint | Table | Form |
|---|---|---|
| One authoritative paid/waived application fee per application | `payment_transactions` | partial unique index on `(admission_application_id)` where `is_authoritative_fee AND status IN ('PAID','WAIVED')` |
| Unique provider event | `payment_provider_events` | unique `(provider, provider_event_id)` |
| Unique provider transaction reference | `payment_transactions` | partial unique `(provider, provider_transaction_ref)` where ref is not null |
| Unique receipt number | `payment_receipts` | unique `receipt_number` |
| One active application per applicant + offering | `admission_applications` | partial unique `(applicant_profile_id, programme_offering_id)` where `deleted_at is null and status <> 'WITHDRAWN'` and `duplicate_authorised = false` |
| One conversion per application | `student_conversions` | unique `admission_application_id`, unique `idempotency_key` |
| Frozen rolls immutable | `admission_rolls` / `admission_roll_entries` | `BEFORE UPDATE/DELETE` trigger raising `42501` when parent roll `frozen_at is not null` |
| Audit append-only | `audit_events` | `BEFORE UPDATE OR DELETE` trigger raising `42501` |
| Submitted versions immutable | `application_versions` | `BEFORE UPDATE OR DELETE` trigger raising `42501` |
| Fee amount positive, currency ISO-4217 | `payment_fee_setups` | check constraints |
| Application version monotonic | `application_versions` | unique `(admission_application_id, version)` |

## 3. Rollback

Every migration implements `down()` that drops only objects it created and drops only the columns it
added. `down()` on migrations 1–9 is safe on a database where the module has not yet accumulated data;
on a populated database the runbook (`docs/admission/RUNBOOKS.md`) requires an approved backup and a
row-count reconciliation before rollback, because dropping the new tables destroys admission evidence.

Migration 10 is fully reversible — it only creates indexes, checks and triggers.

## 4. Backfill

| Backfill | Description | Reconciliation query |
|---|---|---|
| Default institution | Creates the single `MEMA College` institution row and points existing campuses/faculties at it | `select count(*) from institutions` = 1 |
| Legacy offerings | Links existing `programme_offerings` to a generated `programmes` row derived from `courses` | `select count(*) from programme_offerings where programme_id is null` = 0 |
| Legacy applications | Sets `payment_status`, `current_version`, `institution_id`, `correlation_id` on existing rows | `select count(*) from admission_applications where payment_status is null` = 0 |
| Legacy payments | Projects existing `application_payment_attempts` rows with status `PAID` into `payment_transactions` and `payment_receipts` | `select count(*) from application_payment_attempts a where a.status='PAID' and not exists (select 1 from payment_transactions t where t.application_payment_attempt_id = a.id)` = 0 |

Backfills run inside the same migration as the schema change only because the module is pre-production
on this deployment and the row counts are small (< 1,000). For a production cutover the runbook
requires them to be split into a separate release, per `docs/database/SCHEMA-ARCHITECTURE.md` §10.3.

## 5. Verification

```bash
php artisan migrate:fresh --database=pgsql_testing   # clean database
php artisan migrate                                   # against representative data
php artisan migrate:rollback --step=10 && php artisan migrate
php artisan test --testsuite=Migration
```

`tests/Migration/SchemaIntegrityTest.php` asserts every critical constraint above actually exists in
`pg_indexes` / `pg_constraint` / `pg_trigger` and that the destructive operations are rejected.
