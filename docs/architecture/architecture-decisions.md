# Architecture Decision Index

The canonical decision details are in `PLAN/01-ARCHITECTURE-DECISIONS.md`. Decisions are immutable records: changes require a superseding ADR with migration and rollback consequences.

| ADR     | Decision                                                                    | Status   | Revisit trigger                                                          |
| ------- | --------------------------------------------------------------------------- | -------- | ------------------------------------------------------------------------ |
| ADR-001 | Laravel 12 / PHP 8.4 backend                                                | Accepted | Superseding client/architecture decision                                 |
| ADR-002 | Modular monolith                                                            | Accepted | Incompatible load profile or independently versioned institution/service |
| ADR-003 | PostgreSQL 17, single DB, domain schemas                                    | Accepted | Proven operational/data-isolation need                                   |
| ADR-004 | Seven Next.js 15 apps in pnpm/Turborepo                                     | Accepted | Audience/deployment model materially changes                             |
| ADR-005 | Tailwind 4 and first-party shadcn/Radix design system                       | Accepted | Accessibility/ownership goals cannot be met                              |
| ADR-006 | REST `/api/v1`, OpenAPI 3.1, generated clients                              | Accepted | Third-party ecosystem genuinely needs flexible queries                   |
| ADR-007 | Sanctum SPA cookie sessions; mobile tokens; OIDC later                      | Accepted | Institutional SSO requirement activates                                  |
| ADR-008 | Permission plus organizational scope                                        | Accepted | Superseding authorization model passes threat review                     |
| ADR-009 | Mandatory negative authorization testing                                    | Accepted | No planned reversal                                                      |
| ADR-010 | Queues carry side effects, never truth                                      | Accepted | No planned reversal                                                      |
| ADR-011 | Defer K8s, Kafka, microservices, GraphQL, OpenSearch, separate DWH, gateway | Accepted | Individual triggers in canonical ADR                                     |
| ADR-012 | Single tenant now, mandatory institution-shaped data                        | Accepted | Second tenant requires isolation-model ADR                               |

## ADR process

An ADR includes status, context, decision, alternatives, consequences, security/privacy/operations impact, migration, rollback or reversal trigger, owner, approvers, and references. Architecture Board approval is required for domain boundaries, data ownership, public contracts, identity/security, cross-module dependencies, infrastructure platforms, and destructive migrations.
