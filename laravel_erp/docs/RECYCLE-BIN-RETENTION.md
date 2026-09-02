# Recycle Bin and retention policy

## Delivered scope

The governed Recycle Bin currently covers curriculum master data: schools, departments, programmes and course units. Deletion is a soft delete and creates an immutable operational snapshot in `deletion_records` with the actor, active role, reason, original UI location, deletion time and retention deadline.

The active retention rule is stored in `retention_rules` as `CURRICULUM-MASTER-DATA`. It is seeded with a one-month retention period and managed through Admin Setups → Data Governance. New versions are effective-dated; the prior version is closed the day before the new version begins. Every deletion stores both its calculated `purge_after` date and `retention_rule_id`, so historical records retain the exact policy version used.

## Controls

- Recycle Bin and deletion mutations are restricted to authenticated administrators.
- A deletion reason of 10–500 characters is mandatory.
- Restore checks that the soft-deleted model still exists and that no active record uses the same governed code.
- Permanent purge cannot be requested before `purge_after`.
- An active `legal_holds` row blocks purge.
- Purge uses maker-checker: the requester cannot approve the request.
- Soft delete, restore and permanent purge write tamper-evident `audit_events`.
- Bulk purge is disabled. Bulk restore is disabled because every record requires an individual conflict check.

## Automatic purge

Automatic purge is intentionally not enabled yet. A scheduler must submit eligible records through the same approval policy; it must never bypass legal holds or maker-checker approval. Until that workflow exists, elapsed retention makes a record eligible but does not destroy it.

## Recovery and rollback

Application rollback: deploy the previous release while leaving the two governance tables intact so deletion evidence is not lost.

Schema rollback in a non-production recovery exercise: run `php artisan migrate:rollback --step=1`. This drops `deletion_action_requests` and `deletion_records` and removes only the seeded `CURRICULUM-MASTER-DATA` retention rule. Do not run that rollback in production after governed deletions have been recorded; export and retain those records first.

## Known gaps

- Remaining ERP modules are not yet registered with the service.
- Dependency checks currently enforce uniqueness conflicts; relationship-specific restore checks must be added as each module is integrated.
- Role administration must be expanded so Data Protection Officers and auditors can use granular platform permissions through the web UI instead of the current administrator gate.
- Private uploaded-file restoration/purge orchestration is pending.
