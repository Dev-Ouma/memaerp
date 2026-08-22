# MEMA ERP — SRSD GAP & DEFECT AUDIT

**Document:** `PLAN/13-SRSD-GAP-AUDIT.md` · **Version:** 1.0.0-PLAN · **Date:** 22 August 2026
**Audited:** `docs/UNIVERSITY-ERP-SRSD/` — 59 files, 844 KB, 4 index documents + 55 module specifications

---

## Verdict

The requirements set is **genuinely strong**: a uniform 31-section structure across all 55 modules, real
depth on business rules, schemas, approval workflows and acceptance criteria, and a complete requirements
traceability matrix. It is a good foundation and this plan builds on it rather than replacing it.

It also contains **eight defects**, three of them material. All were found by verifying the documents against
the filesystem and against each other rather than by reading them — each one reads as authoritative in isolation.

| # | Defect | Severity | Status |
|---|---|---|---|
| D-01 | Architecture document specifies a different technology stack than the client brief | **Critical** | Resolved by ADR-001, pending client confirmation |
| D-02 | No CMS or public website module exists among the 55 | **High** | Resolved — MOD-01-14 added |
| D-03 | Master index Phase-03 module set diverges from the files on disk | **High** | Documented; index rewrite required |
| D-04 | 43 of 55 master index links point at non-existent files | Medium | Documented; index rewrite required |
| D-05 | PostgreSQL 18 specified; 17 recommended | Low | Resolved by ADR-003 |
| D-06 | File `01-01-Identity-and-Access-Management.md` actually contains the MOD-00 platform umbrella | Medium | Documented; rename recommended |
| D-07 | Roadmap phase/sprint numbering does not reconcile with the module catalogue | Low | Resolved by this plan's sequencing |
| D-08 | Modules referenced in the RTM that do not exist as files | Medium | Documented |

---

## D-01 · Conflicting technology stack — **Critical**

`00-SYSTEM-ARCHITECTURE.md` §2 baselines **NestJS + Fastify on Node.js 22**, **"Vanilla CSS Tokens + CSS
Modules (Zero Tailwind Overhead)"**, **Kubernetes**, **Kafka**, **GraphQL for BI** and **ClickHouse**.

The client brief specifies **Laravel / PHP 8.4**, **Tailwind + shadcn/ui**, **Docker + Nginx**, and explicitly
excludes Kubernetes, Kafka, GraphQL and MongoDB.

Neither document references the other. Both read as settled. A team starting from the SRSD would build a
Node.js system; a team starting from the brief would build a PHP one.

**Detection.** Not visible from reading either document. Found by sweeping every candidate technology name
across the whole doc set and counting hits: `NestJS` 3, `Fastify` 2, `Laravel` **0**, `PHP` **0**.

**Resolution.** ADR-001 adopts Laravel and supersedes the SRSD baseline. Confirmation required (D-001 in
[`12-OPEN-DECISIONS.md`](12-OPEN-DECISIONS.md)).

**Remediation.** Rewrite `00-SYSTEM-ARCHITECTURE.md` §2 and §3 once confirmed. The 55 module specifications
need **no change** — their 31 sections are functional, not platform-specific.

---

## D-02 · No CMS or public website module — **High**

Searching all 59 files for "CMS", "content management" and "public website" returns **zero matches**. The 55
modules cover the ERP thoroughly and omit the public-facing web estate entirely.

The brief treats both as core: a public website with 22 named page types, and a CMS managing pages, menus,
news, events, announcements, programmes, schools, departments, staff, research, documents, media, forms, SEO
and site settings.

**Impact.** Two to four sprints of unplanned work, on the critical path — the admissions funnel starts at the
public website, so the applicant portal has no front door without it.

**Resolution.** Added as **MOD-01-14 · CMS & Public Website**, scheduled at Sprint 8 (before Admissions at
Sprint 9). A full 31-section SRSD specification for it is outstanding — see "Outstanding work" below.

---

## D-03 · Master index diverges from the files on disk — **High**

`00-MASTER-INDEX.md` lists a Phase-03 module set that does not match what exists:

| Index claims | Actually on disk |
|---|---|
| MOD-03-02 Academic Staff Workload Management | MOD-03-02 Organizational Structure & Departments |
| MOD-03-05 Staff Promotions Management | MOD-03-05 Training & Professional Development |
| MOD-03-08 Budgeting & Commitment Control | MOD-03-08 Accounts Payable |
| MOD-03-09 Accounts Payable & Receivable | MOD-03-09 Accounts Receivable & Revenue |
| MOD-03-10 Procurement & Supply Chain | MOD-03-10 Bank & Cash Management |
| MOD-03-11 Stores Inventory & Fixed Assets | MOD-03-11 Procurement & Stores |

This is not filename drift — six modules differ in subject matter. **Three specified capabilities have no
specification file at all:** academic staff workload management, staff promotions, and budgeting/vote-book
commitment control. All three are standard requirements for a Kenyan public university, and the last is
usually a statutory expectation.

**Impact.** Roughly a fifth of Phase 3 would have been mis-scoped by anyone planning from the index.

**Resolution.** This plan's Phase 3 (§5 of [`00-EXECUTION-PLAN.md`](00-EXECUTION-PLAN.md)) is built from
**the files on disk**, which are real. The three missing capabilities are listed as outstanding work below.

---

## D-04 · Broken index links — Medium

43 of the 55 relative links in `00-MASTER-INDEX.md` point at files that do not exist — mostly a missing
phase prefix (`PHASE-01/02-Institutional-…` instead of `PHASE-01/01-02-Institutional-…`).

Mechanical to fix, but it makes the index unusable for navigation, which is its only purpose.

---

## D-05 · PostgreSQL version — Low

The SRSD specifies PostgreSQL 18. ADR-003 recommends **17** — a mature operational track record and broad
managed-provider support matter more here than the newest feature set. No application impact; upgrading later
is a Phase 5 operational task.

---

## D-06 · Filename/content mismatch — Medium

`PHASE-01/01-01-Identity-and-Access-Management.md` is titled and indexed as an IAM module, but actually
contains **MOD-00: Platform Administration, Configuration & Governance** — a five-part umbrella covering IAM,
the workflow engine, system configuration, the operations control centre, and network/OAuth governance.

The content is excellent and considerably broader than its name. But anyone scoping "IAM" from the filename
would badly underestimate it — this file alone is the whole of Phase 0.

**Resolution.** This plan treats it correctly as MOD-00 and schedules it as Phase 0 (four sprints).
**Recommendation:** rename to `00-00-Platform-Administration-and-Governance.md` and index it as MOD-00.

---

## D-07 · Roadmap numbering — Low

`00-IMPLEMENTATION-ROADMAP.md` describes 72 sprints across 24 months (3 per month), implying one-week sprints
while describing month-long increments; sprint-to-module mapping does not reconcile with the catalogue in
several places.

**Resolution.** This plan uses 48 two-week sprints with an explicit module mapping, and is the operative
schedule.

---

## D-08 · RTM references modules that do not exist — Medium

The traceability matrix maps requirements to `MOD-03-02 Academic Staff Workload`, `MOD-03-05 Staff
Promotions` and `MOD-03-08 Budgeting & Vote-Book` — the three modules from D-03 with no specification file.

Those source requirements are therefore **traced to nothing**, which defeats the purpose of the matrix.
The RTM must be regenerated after D-03 is resolved.

---

## Outstanding specification work

Before the relevant phase begins, the following need full 31-section SRSD treatment:

| Module | Needed before | Why |
|---|---|---|
| **MOD-01-14 · CMS & Public Website** | Sprint 8 | Does not exist; on the Phase 1 critical path |
| **Academic Staff Workload Management** | Sprint 23 | Referenced in the RTM, no file; feeds timetabling and overload claims |
| **Staff Promotions Management** | Sprint 25 | Referenced in the RTM, no file; standard for a Kenyan public university |
| **Budgeting & Commitment Control (vote-book)** | Sprint 28 | Referenced in the RTM, no file; usually a statutory requirement, and the GL is incomplete without it |

**Recommendation:** these are four modules of specification work. MOD-01-14 is urgent (Phase 1); the other
three are needed by Phase 3 and can be written during Phase 2.

---

## Recommended remediation sequence

| Order | Action | Prerequisite |
|---|---|---|
| 1 | Confirm the backend platform | Client decision D-001 |
| 2 | Rewrite `00-SYSTEM-ARCHITECTURE.md` §2–§3 to match | Step 1 |
| 3 | Regenerate `00-MASTER-INDEX.md` from the filesystem — correct names, correct links, MOD-00 and MOD-01-14 added | — |
| 4 | Rename `01-01-Identity-and-Access-Management.md` to reflect MOD-00 | — |
| 5 | Write the MOD-01-14 CMS specification | Client decision D-006 |
| 6 | Write the three missing Phase-3 specifications | Before Phase 2 ends |
| 7 | Regenerate the RTM against the corrected catalogue | Steps 3, 5, 6 |
| 8 | Add a CI link-checker over `docs/` | — |

Step 8 is worth doing regardless. A documentation set this large drifts silently; a link-checker in CI would
have caught D-04 the day it appeared, and would have surfaced D-03 shortly after.

---

## Method note

Every defect here was found by **verifying documents against the filesystem and against each other**, not by
reading them. Each document is internally coherent and reads as authoritative; the contradictions only exist
*between* documents, where nothing points.

The three checks that found everything, in order of value:

1. **Term-frequency sweep** — counting each candidate technology name across the whole set. Found D-01, D-02.
2. **Index-vs-filesystem diff, in both directions** — listed-but-missing and present-but-unlisted. Found D-03, D-04.
3. **First-heading spot check** — comparing each file's actual title against what the index claims. Found D-06.

Together these took under two minutes and changed the scope of the programme.
