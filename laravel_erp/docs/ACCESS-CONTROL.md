# Platform access control

## Canonical catalogue

`PermissionCatalogue` is the reviewed source for permissions, system roles and segregation rules. `RbacCatalogueSeeder` persists it idempotently into `permissions`, `roles` and `role_permissions`. Deployment must run:

```bash
php artisan db:seed --class=Database\\Seeders\\RbacCatalogueSeeder --force
```

The seeder updates canonical metadata and role membership without changing user grants. Existing active users with the legacy `admin` role receive a System Administrator assignment only when that exact assignment is absent.

## Assignment workflow

Role grants store user, role, scope type, optional scope identifier, grantor, grant date, optional expiry and mandatory reason. Expired grants are ignored by `AccessControl`. Grants and revocations create tamper-evident `role.assignment.granted` and `role.assignment.revoked` events.

Controls:

- Only `platform.role.manage` holders may grant or revoke roles.
- Inactive users cannot receive new grants.
- Duplicate active assignments are rejected.
- Narrow scopes require a scope identifier.
- Expiry must be in the future.
- Users cannot revoke their own System Administrator assignment.
- System administration and any role containing segregated permissions cannot be combined on one user.
- The permission catalogue contains no wildcard or implicit administrator bypass.

## Bootstrap and recovery

The canonical seeder is the bootstrap mechanism. If every System Administrator grant is lost, use an audited database recovery procedure to insert one institution-scoped `user_roles` row for the reviewed System Administrator role and an active named user. Record the incident and immediately verify the audit trail and all privileged assignments.
