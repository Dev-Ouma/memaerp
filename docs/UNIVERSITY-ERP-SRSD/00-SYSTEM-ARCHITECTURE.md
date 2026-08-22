# UNIVERSITY ERP / UMIS — SYSTEM ARCHITECTURE & ENGINEERING BASELINE

- **System:** Integrated University Enterprise Resource Planning & Student Information Management System (MEMA ERP)
- **Version:** 1.0.0-PROD-SPEC
- **Document Type:** System Architecture & Technical Baseline Specification

---

## 1. Architectural Style & Design Principles

The MEMA ERP architecture is designed as a **Modular Monolith with Domain-Driven Design (DDD)** bounded contexts. This architecture provides maximum transactional integrity and domain coherence while allowing seamless evolutionary extraction of high-scale sub-domains into microservices when required.

### Core Principles:
1. **Transactional Truth over Asynchronous Convenience:** All financial ledgers, grades, registrations, and academic records execute under strict ACID relational guarantees in PostgreSQL 18.
2. **Canonical Single Source of Truth:** A single immutable `Person` record links `Applicant`, `Student`, `Staff`, and `Alumni` identities without duplicate profiles.
3. **Configuration & Versioned Rules over Hardcoded Logic:** Degree requirements, grading scales, fee structures, and approval workflows are versioned in database tables, never hardcoded.
4. **Zero-Trust Security & Tamper-Proof Auditing:** All state modifications generate append-only audit records recording actor, timestamp, IP, reason code, and before/after JSON diffs.

---

## 2. Technology Stack Baseline

```text
┌────────────────────────────────────────────────────────────────────────┐
│ FRONTEND TIER                                                          │
│ Framework: Next.js 15 (React 19, TypeScript, App Router)               │
│ Styling: Vanilla CSS Tokens + CSS Modules (Zero Tailwind Overhead)    │
│ Design Tokens: Primary #0A3E50, Secondary #1E8449, Canvas #F8FAFC     │
│ State & Query: TanStack Query (React Query) + Zustand                  │
├────────────────────────────────────────────────────────────────────────┤
│ BACKEND APPLICATION TIER                                               │
│ Runtime: Node.js 22 LTS / NestJS + Fastify HTTP Adapter                │
│ Language: TypeScript (Strict Mode)                                     │
│ API Protocols: RESTful (OpenAPI 3.1 Specification) + GraphQL for BI    │
│ Validation: Zod & Class-Validator with DTO Transformation             │
├────────────────────────────────────────────────────────────────────────┤
│ DATA PLATFORM & STORAGE                                                │
│ Primary Relational DB: PostgreSQL 18 (Partitioned, Connection Pooled)  │
│ Caching & Ephemeral Locks: Redis 7.4 (Redlock, Session Store)          │
│ Asynchronous Queues: BullMQ / Redis Queue -> Apache Kafka Event Stream │
│ Object Storage: S3-Compatible Storage (MinIO / AWS S3) with KMS       │
├────────────────────────────────────────────────────────────────────────┤
│ DEVOPS & INFRASTRUCTURE                                                │
│ Containerization: Docker (Multi-stage minimal distroless images)       │
│ Orchestration: Kubernetes (EKS / Self-Hosted K8s with HPA)             │
│ Reverse Proxy / Ingress: NGINX / Traefik with TLS 1.3 Termination      │
│ Observability: OpenTelemetry, Prometheus, Grafana, Jaeger Tracing      │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Logical Reference Architecture

```text
                              INTERNET / CLIENTS
                                       │
                  ┌────────────────────┴────────────────────┐
                  │ Web Browser (Next.js) / Mobile App (iOS/Android)
                  └────────────────────┬────────────────────┘
                                       │ HTTPS / TLS 1.3
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │ Cloudflare WAF / Reverse Proxy Ingress  │
                  └────────────────────┬────────────────────┘
                                       │
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │ API Gateway / Kong / Traefik            │
                  │ Rate Limiting · Auth Verification · CORS│
                  └────────────────────┬────────────────────┘
                                       │
                                       ▼
                  ┌─────────────────────────────────────────┐
                  │ NESTJS MODULAR MONOLITH CORE            │
                  │                                         │
                  │ ┌───────────────┐  ┌──────────────────┐ │
                  │ │ IAM & Auth    │  │ Student Lifecycle│ │
                  │ └───────────────┘  └──────────────────┘ │
                  │ ┌───────────────┐  ┌──────────────────┐ │
                  │ │ Academics &   │  │ Exams & Grading  │ │
                  │ │ Curriculum    │  │ Engine           │ │
                  │ └───────────────┘  └──────────────────┘ │
                  │ ┌───────────────┐  ┌──────────────────┐ │
                  │ │ Finance &     │  │ HR, Payroll &    │ │
                  │ │ Billing       │  │ Procurement      │ │
                  │ └───────────────┘  └──────────────────┘ │
                  │ ┌───────────────┐  ┌──────────────────┐ │
                  │ │ Workflow &    │  │ Notifications &  │ │
                  │ │ Rules Engine  │  │ Audit Logger     │ │
                  │ └───────────────┘  └──────────────────┘ │
                  └────────────┬───────────────────┬────────┘
                               │                   │
                     SQL Query │                   │ Redis / S3
                               ▼                   ▼
                  ┌────────────────────┐   ┌────────────────────┐
                  │ PostgreSQL 18 DB   │   │ Redis 7.4 / S3     │
                  │ (Primary + Replica)│   │ Cache, Locks, Docs │
                  └────────────┬───────┘   └────────────────────┘
                               │
                      CDC / Debezium
                               ▼
                  ┌────────────────────┐
                  │ Enterprise DWH     │
                  │ (ClickHouse/Star)  │
                  └────────────────────┘
```

---

## 4. PostgreSQL 18 Database Architecture

The relational database is architected into explicit schema domains:

1. **`iam`**: Authentication, users, roles, permissions, MFA tokens, session logs.
2. **`institution`**: Universities, campuses, faculties, departments, academic years, semesters, calendars.
3. **`curriculum`**: Programmes, versions, curricula, course structures, prerequisites, graduation rules.
4. **`course`**: Master courses, semester course offerings, class sections, lecturer allocations.
5. **`admission`**: Prospects, applications, qualifications, evaluations, admission letters.
6. **`student`**: Persons, student master files, cohorts, student statuses, document repository.
7. **`enrollment`**: Term registrations, course enrollments, add/drops, capacity locks.
8. **`finance`**: Fee structures, student bills/invoices, payments, receipts, GL journals, accounts payable/receivable.
9. **`examination`**: Assessments, CATs, exam sessions, marks submissions, moderation, results, GPAs, progression.
10. **`graduation`**: Degree audits, clearance records, graduation rolls, transcripts, certificates.
11. **`hr`**: Staff records, workload, leave, appraisals, promotions, payroll, statutory deductions.
12. **`procurement`**: Suppliers, requisitions, tenders, purchase orders, stores inventory, fixed assets.
13. **`research`**: Proposals, grants, ethics reviews, publications, postgraduate thesis milestones.
14. **`audit`**: Immutable audit logs, change data capture, security event records.
