# Platform governance administration

## Admin Setup mapping

| Admin area | Database authority | Operational consumer |
|---|---|---|
| Retention rule versions | `retention_rules` | `RecycleBinService` deletion and purge eligibility |
| Legal holds | `legal_holds` | `RecycleBinService` purge gate |
| Purge checker queue | `deletion_action_requests` | Independent permanent-purge approval |
| Audit trail | `audit_events` | Compliance investigation and evidence-chain verification |

## Permission matrix

| Operation | Current web enforcement | Target granular permission |
|---|---|---|
| View governance workspace | `platform.audit.view` | Auditor, DPO or System Administrator role assignment |
| Publish retention version | `platform.retention.execute` | Data Protection Officer assignment |
| Place/release legal hold | `platform.retention.execute` | Data Protection Officer assignment |
| Approve purge | `platform.retention.execute` plus maker-checker separation | Data Protection Officer assignment |
| View audit trail | `platform.audit.view` | Auditor, DPO or System Administrator assignment |

Web and API authorization now use the persisted deny-by-default catalogue. The former global legacy-administrator bypass was removed. Existing active legacy administrators receive only the non-segregated System Administrator role during controlled RBAC seeding; retention execution must be granted separately to another user.

## Audit-event catalogue

| Event | Trigger | Classification |
|---|---|---|
| `retention_rule.version_published` | A new effective-dated retention version is published | confidential |
| `legal_hold.placed` | A hold is placed on a deleted record | restricted |
| `legal_hold.released` | An active hold is released with a reason | restricted |
| `record.soft_deleted` | A governed record enters the Recycle Bin | confidential |
| `record.restored` | A deleted record is restored | confidential |
| `record.permanently_purged` | A different checker approves permanent purge | restricted |

## Rollback

Migration rollback is safe while each retention code has only one version. If multiple versions exist, rollback stops deliberately rather than deleting or flattening policy history. Application rollback should retain the additive governance columns and tables until evidence has been exported and reviewed.
