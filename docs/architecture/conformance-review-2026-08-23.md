# Architecture Conformance Review — 23 August 2026

**Scope:** repository scaffold against accepted ADRs and Phase 0 entry criteria.  
**Disposition:** Not implementation-ready for business modules; foundation remediation is required first.

## Findings

### HIGH — H-01: Backend runtime constraint permits unsupported PHP versions

`apps/api/composer.json` requires PHP `^8.2` while ADR-001 requires PHP 8.4.x. Composer can therefore install and CI can pass on PHP 8.2 or 8.3, allowing behavior that differs from the production baseline.

**Required remediation:** constrain runtime to `~8.4.0`, keep Composer's platform PHP at the approved 8.4 patch baseline, and execute CI on PHP 8.4. Owner: Laravel Backend/DevOps Engineer.

### HIGH — H-02: Domain routes are loaded as mandatory files before module completeness is proven

`apps/api/routes/api.php` unconditionally requires IAM, Curriculum, Course, Enrollment, and Examination route files. The architecture requires mechanically enforced module boundaries and independently testable module registration; unconditional route-file coupling makes an absent/disabled module an application boot failure and bypasses module discovery.

**Required remediation:** select and document the module registration mechanism, make module providers own route registration, and add architecture tests preventing cross-module model/table access. Owner: Laravel Backend Engineer; approval: System Architect.

### MEDIUM — M-01: Frontend deployable set is incomplete

ADR-004 defines seven applications. The repository currently contains `website`, `applicant`, `student`, and `admin`; `lecturer`, `staff`, and `management` are absent.

**Required remediation:** scaffold them in Phase 0 or explicitly stage their creation with a superseding execution-plan decision. Owner: Frontend Platform Engineer.

### MEDIUM — M-02: Tailwind baseline is inconsistent

The four Next.js applications and `packages/ui` declare Tailwind 3.4, while ADR-005 requires Tailwind 4. The Laravel Vite package declares Tailwind 4, creating two styling baselines.

**Required remediation:** either migrate the frontend workspace to Tailwind 4 and test all shared components, or supersede ADR-005 with explicit compatibility reasoning. Owner: Frontend Platform Engineer.

### MEDIUM — M-03: Repository layout differs from the accepted plan

The Laravel application is under `apps/api`; `PLAN/02-REPOSITORY-STRUCTURE.md` specifies `backend/`. Either layout can support the architecture, but undocumented divergence breaks scripts, onboarding guidance, and future boundary checks.

**Required remediation:** record an ADR/amend the repository plan before moving or expanding backend code. Do not perform a mechanical move without checking Docker, CI, Composer, and deployment references. Owner: System Architect and DevOps Engineer.

### MEDIUM — M-04: OpenAPI contract is a governance scaffold, not an implemented contract

`docs/api/openapi.yaml` intentionally contains no operation paths. ADR-006 requires generated, committed endpoints, breaking-change detection, and generated clients. Current manual mock types are not evidence of contract conformance.

**Required remediation:** choose the Laravel OpenAPI generator, generate the health and first secured operation, validate the spec, and generate the TypeScript client/Zod schemas in CI. Owner: API Engineer.

### LOW — L-01: Documentation control hierarchy needs CI enforcement

The repository has a detailed SRSD, planning set, and new governed control documents. Existing audit D-03/D-04/D-08 shows that links and traceability have drifted before.

**Required remediation:** add Markdown link checking, OpenAPI linting, requirement-ID uniqueness, and RTM validation to CI. Owner: Documentation/QA Engineer.

## Release recommendation

Complete H-01 and H-02 before accepting backend module work. Resolve or formally schedule M-01 through M-04 during Phase 0. Do not treat the presence of scaffold screens or routes as satisfying Gate 0.

## Review limitations

This review covered structure, manifests, and API bootstrapping, not a complete source-level security or database review. The next review should inspect migrations, policies, middleware, module providers, tests, environment configuration, and secret history before any production-like data is used.
