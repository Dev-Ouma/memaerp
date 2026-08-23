# Scalability and Capacity Strategy

## Initial profile

Design against the unconfirmed D-005 assumption: 10,000 students, 800 staff, 1–3 campuses, and 5,000 concurrent registration users. Measure production baselines before adding infrastructure.

| Layer              | Initial strategy                                              | Scale trigger/action                                                            |
| ------------------ | ------------------------------------------------------------- | ------------------------------------------------------------------------------- |
| Edge/web           | CDN/ISR for public content; independently scaled Next.js apps | Add replicas by audience traffic                                                |
| API                | Stateless PHP-FPM containers behind Nginx                     | Add nodes based on p95 latency, CPU, and saturation                             |
| Database           | PostgreSQL HA, PgBouncer, correct composite/partial indexes   | Tune/query budgets, partition high-volume audit, add read replica for reporting |
| Cache/coordination | Redis with bounded TTLs and institution-prefixed keys         | HA Redis and workload isolation when memory/latency warrants                    |
| Jobs               | Named queues and independently sized workers                  | Scale payments/notifications/reports/LMS/ETL independently by age/backlog       |
| Files              | S3-compatible object storage and CDN for public assets        | Lifecycle tiers and provider scaling                                            |
| Search             | PostgreSQL full-text search                                   | OpenSearch only past documented corpus/facet trigger                            |
| Analytics          | Read replica plus star schema/ETL                             | Separate engine only when OLTP isolation and tuning fail                        |

## Peak protection

Registration uses short transactions, deterministic lock order, row locks/atomic capacity updates, idempotency, and load shedding. Reports/exports are asynchronous. Cache is never the only record of capacity, money, enrollment, or grades. Load tests run at twice expected peak and validate correctness, not only latency.

## Multi-tenancy evolution

The first deployment uses shared schemas with `institution_id`. Before onboarding a second institution, choose shared-schema, schema-per-tenant, or database-per-tenant via a new ADR based on regulatory isolation, customization, scale, and operating model. Automated two-tenant isolation tests must pass before any second-tenant data is accepted.
