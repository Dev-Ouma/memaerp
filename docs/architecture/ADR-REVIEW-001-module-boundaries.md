# Architectural Review Request: Eloquent Cross-Module Boundaries

**Status:** REVIEW REQUIRED  
**Raised:** 23 August 2026  
**Affected decision:** `PLAN/02-REPOSITORY-STRUCTURE.md` module boundary rule  
**Evidence:** `apps/api/deptrac.yaml`

## Problem

The accepted repository rule says that a module may reference another module only through classes in the other module's `Contracts/` directory. The existing Laravel implementation models the university data spine with ordinary Eloquent relationships whose return types and related classes directly reference models owned by other modules.

Running the required boundary check reports 117 violations. Representative dependencies include:

- Admission → Student `Person`, Curriculum `Programme`, and Institution master data.
- Student → Curriculum `Programme`, Institution master data, and Enrollment registrations.
- Finance → Student `Person`, Institution terms, and Curriculum programmes.
- IAM → Institution hierarchy and Student `Person`.
- Platform request context → IAM `User`.

This is structural, not a one-file defect. Suppressing or baselining the violations would make the documented quality gate misleading.

## Resolution options

### Option A — enforce the current rule literally

Replace cross-module Eloquent relationships and direct model queries with module contracts, query ports, identifiers, and module-owned DTOs. This provides the strongest isolation and future extraction boundary, but introduces substantial mapping and orchestration overhead throughout the shared relational data spine.

### Option B — refine the rule for a modular monolith

Permit explicitly directed, read-only model relationships in the persistence/model layer according to the approved domain dependency graph, while continuing to require contracts for services, actions, commands, and cross-module business behavior. Deptrac would enforce the allowed dependency direction and continue rejecting cycles and service-layer coupling.

This retains useful Eloquent relationships while making the actual extraction boundary the application contract rather than every ORM association.

## Engineering recommendation

Approve Option B for the single-database modular monolith, with all of the following constraints:

1. Allowed model dependencies must follow the dependency order in `PLAN/00-EXECUTION-PLAN.md`.
2. Cross-module writes and business operations must use contracts; relationships are not authorization or mutation APIs.
3. Cyclic module dependencies remain prohibited.
4. Controllers may depend only on their module's application layer and shared HTTP/platform abstractions.
5. Deptrac rules must encode the approved dependency graph and run as a blocking CI gate.
6. Any future service extraction replaces the relevant relationship with a contract-backed projection before separation.

## Decision needed

Architecture governance must approve Option A or Option B before the 117 violations can be resolved honestly. Until then, `composer stan`, Pint, frontend lint, frontend type-checking, and frontend production builds can remain blocking gates; Deptrac remains intentionally red as evidence of this unresolved decision.
