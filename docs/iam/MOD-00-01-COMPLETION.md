# MOD-00-01 Identity, Authentication, Authorization & Security

## Completion statement

This implementation closes **MOD-00-01 (sub-modules 00.01 through 00.10)** from
`PHASE-01/01-01-Identity-and-Access-Management.md`. The source document is a MOD-00 umbrella
specification: its MOD-00-02 through MOD-00-05 requirements are separate modules and are not
claimed by this completion record.

The implementation is end-to-end across PostgreSQL persistence, Laravel API enforcement, the
administration web portal, automated feature tests, and Chromium browser tests.

## Requirement traceability

| Requirement | Implemented behavior | Primary verification |
|---|---|---|
| 00.01 Accounts and provisioning | Institution-bound user creation, person creation, applicant/student/employee/alumni identity linking, initial `PENDING` state and forced password change | Protected API, administrator web form, feature and Chromium tests |
| 00.02 Profile governance | Credential and human-record separation through `iam.users`, `student.persons`, and `student.person_identities`; protected live IAM user projection | Login by institutional identity and protected web security view |
| 00.03 Account states | `PENDING`, `ACTIVE`, `LOCKED`, `SUSPENDED`, `DEACTIVATED`; five-failure timed lock; audited administrative state changes; token/session revocation on disablement | Administrator web form, lockout and authorization tests |
| 00.04 Multi-identifier login | Case-normalized email, username, and active person identity identifiers such as employee/student number | `EMP-000001` API and Chromium login tests |
| 00.05 Password policy | Argon2id, 12–128 characters, uppercase/lowercase/digit/symbol requirements, and five-password history | Reset test verifies `argon2id` hash and history row |
| 00.06 Password reset | Generic anti-enumeration response, cryptographically random hashed token, 15-minute expiry, single use, password-policy enforcement, and global session revocation | API feature test and Chromium reset test |
| 00.07 TOTP MFA | RFC 6238 TOTP, encrypted secret, locally rendered enrollment QR, manual key, enrollment confirmation, login challenge, expiry and attempt limiting | Full browser enrollment/disable flow and setup → confirm → login → verify feature test |
| 00.08 Recovery codes | Ten randomly generated codes, stored only as password hashes and consumed once | MFA enrollment/verification feature coverage |
| 00.09 Account recovery | Permission-protected, reason-required, audited administrator MFA reset; pending challenges, tokens and sessions are revoked | Administrator recovery web flow and feature test |
| 00.10 Sessions | CSPRNG opaque identifiers, tracked devices/IP/user agent, idle and absolute role-based expiry, login/MFA rotation, global `session_version`, exact browser-session and global revoke, `__Host-ERPSESSION` production cookie contract | Session middleware/feature tests and credentialed SPA browser flows |
| RBAC | 55 protected enterprise roles normalized into the 11 specified families, 68 atomic permissions, hierarchy/MFA/default-scope metadata, time-bounded scoped assignments and fail-closed gates | Catalogue, least-privilege, expiry and scope tests |
| Privileged access | Mandatory MFA policy for privileged roles and immediate session/token revocation after role elevation | Mandatory-MFA feature test and session manager enforcement |

## Security decisions

- Authentication establishes the session only after password and, where configured, MFA success.
- Privileged users without an enrolled factor receive `MFA_ENROLLMENT_REQUIRED` when mandatory MFA
  enforcement is enabled. Production defaults to enabled.
- SPA requests use credentialed CORS and send the XSRF header. Production uses a Secure, HttpOnly,
  SameSite=Lax, host-only cookie.
- Password-reset responses are identical for known and unknown accounts.
- Authorization is permission-and-scope based. Empty or expired grants deny access; role names are
  not embedded in business controllers.
- Password changes, global logout, administrator MFA recovery, account suspension and role elevation
  invalidate outstanding access as appropriate.

## API surface

Public/throttled authentication endpoints:

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/mfa/verify`
- `POST /api/v1/auth/password/forgot`
- `POST /api/v1/auth/password/reset`

Authenticated self-service endpoints:

- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout` and `POST /api/v1/auth/logout-all`
- `POST /api/v1/auth/password/change`
- `POST /api/v1/auth/mfa/setup`, `POST /api/v1/auth/mfa/confirm`, `DELETE /api/v1/auth/mfa`
- `GET /api/v1/auth/sessions`, `DELETE /api/v1/auth/sessions/{session}`

Permission-protected administration endpoints:

- `GET|POST /api/v1/iam/users`
- `PATCH /api/v1/iam/users/{user}/status`
- `POST /api/v1/iam/users/{user}/mfa-reset`
- `POST /api/v1/iam/users/{user}/roles`
- `GET /api/v1/iam/roles`

## Web verification

The permanent Playwright specification is `e2e/iam-admin.spec.ts`. It uses Chromium against the
real Next.js portal and Laravel/PostgreSQL API to verify:

1. invalid credentials are rejected without account disclosure;
2. an employee-number login establishes a credentialed session and renders live protected users,
   role assignments and the enterprise role directory at `/security`;
3. an administrator provisions and activates a user, assigns a scoped role, and completes audited
   MFA recovery through the web interface;
4. a user enrolls TOTP from a rendered QR code, receives ten recovery codes, and disables MFA;
5. tracked devices are listed, an individual browser session is revoked, and logout-everywhere
   invalidates all access;
6. password change enforces policy and invalidates the current session;
7. password-reset requests display the generic anti-enumeration response.

The administrator workflows live at `/security`. Password, MFA, recovery-code, and session
self-service workflows live at `/account-security`.

Run it with:

```bash
pnpm exec playwright test --config=playwright.iam.config.ts
```

The API must use the portal host in `SANCTUM_STATEFUL_DOMAINS` and `CORS_ALLOWED_ORIGINS`. Local
browser verification may use `CACHE_STORE=array`; deployed environments use the configured Redis
service.

## Verification record (2026-08-23)

- Laravel Pint: passed.
- PHPStan: passed with zero errors.
- Laravel suite: 77 tests passed, 321 assertions.
- Migration fresh/seed: passed with 68 permissions and 55 roles.
- Latest IAM migration rollback and forward migration: passed.
- Admin ESLint: passed with zero warnings/errors.
- Admin TypeScript: passed.
- Admin production build: passed; `/login`, `/mfa`, `/reset-password`, `/security`, and
  `/account-security` generated.
- Chromium E2E: 8 tests passed in parallel.
- Database used for certification: PostgreSQL 16.14. Production should also run the same suite on
  the deployment's PostgreSQL version before release.

## Operational release checks

Before production deployment:

1. set a valid `APP_KEY`, `HASH_DRIVER=argon2id`, `IAM_ENFORCE_MANDATORY_MFA=true`, UTC database
   timezone, and production database/Redis credentials;
2. set the exact first-party portal hosts in Sanctum and CORS configuration;
3. use `SESSION_COOKIE=__Host-ERPSESSION`, `SESSION_SECURE_COOKIE=true`, `SESSION_PATH=/`, no cookie
   domain, HttpOnly and SameSite=Lax;
4. run migrations, seed the permission/role catalogues, and do not seed demo accounts;
5. execute the Laravel, static-analysis, build and Playwright commands above in CI.
