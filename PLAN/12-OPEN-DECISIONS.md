# MEMA ERP — OPEN DECISIONS REQUIRED

**Document:** `PLAN/12-OPEN-DECISIONS.md` · **Version:** 1.0.0-PLAN · **Date:** 22 August 2026

Every item below is currently proceeding on a **stated assumption**. Work is not blocked — but each
assumption has a point after which reversing it becomes expensive, recorded as "cost of late reversal".

**Decision SLA: 5 working days.** Reported at every steering meeting; escalated to the Executive Sponsor
automatically past the due date.

---

## Tier 1 — Blocking before Sprint 1

### D-001 · Backend platform — Laravel or NestJS? ⚠ **MOST IMPORTANT**

**The conflict.** The client engineering brief (22 Aug 2026) specifies **Laravel / PHP 8.4**. The existing
`docs/…/00-SYSTEM-ARCHITECTURE.md` §2 specifies **NestJS + Fastify on Node.js 22**. These are incompatible
and neither document acknowledges the other. Related conflicts travel with it: Tailwind vs "Zero Tailwind
Overhead" CSS Modules, and Kubernetes/Kafka/GraphQL (assumed by the SRSD, explicitly excluded by the brief).

**Proceeding on:** Laravel 12 / PHP 8.4, per ADR-001 — the brief is the later, more deliberate statement, the
SRSD is 96% stack-agnostic, Laravel's built-ins cover a large share of ERP plumbing, and PHP/Laravel is
substantially easier for the client to staff after handover.

**Cost of late reversal:** low before Sprint 1 (~15% of this plan), **severe after Sprint 4** — the entire
Phase 0 platform would be rebuilt.

**Needed from:** Mema University ICT Director + Engineering Lead. **A one-line confirmation is sufficient.**

---

### D-002 · Who owns the system after handover?

Determines how much operational complexity is defensible. If the client's ICT team will run it, the
two-server Docker topology (ADR-011) is right. If a managed provider will run it, managed PostgreSQL, managed
Redis and a PaaS runtime may be worth their cost.

**Proceeding on:** client-owned ICT operation with a support agreement.
**Cost of late reversal:** medium — changes infrastructure work in Phase 0 and the Phase 5 handover plan.

---

### D-003 · Financial year end

The general ledger must cut over at a clean financial-period boundary. This constraint outranks the sprint
calendar and may move Phase 3 finance work by up to a quarter.

**Proceeding on:** 30 June (common for Kenyan public universities) — **please confirm the actual date.**
**Cost of late reversal:** high if discovered in Phase 3 — a full quarter of schedule.
**Needed from:** Finance Officer / Bursar.

---

### D-004 · Legacy systems inventory and access

What exists today for student records, finance, HR and library; which are databases and which are
spreadsheets; who owns each; can we get read-only access and schema documentation in Phase 0?

Profiling must start in Phase 0 because **cleansing is the client's work on the client's timeline**, and it
is always slower than expected (see [`09-DATA-MIGRATION-AND-CUTOVER.md`](09-DATA-MIGRATION-AND-CUTOVER.md) §6).

**Proceeding on:** a legacy SIS plus finance system plus spreadsheets, with 3–5 years of history to migrate
and full academic history for transcript reissue.
**Cost of late reversal:** **very high** — the single most common cause of ERP go-live failure.

---

### D-005 · Institutional scale

Current and projected student population, staff headcount, campuses, programmes, peak concurrent registration.
Drives infrastructure sizing, load-test targets and licence costs.

**Proceeding on:** ~10,000 students, ~800 staff, 1–3 campuses, ~5,000 peak concurrent registrations.
**Cost of late reversal:** medium — infrastructure resizing plus re-running performance gates.

---

## Tier 2 — Needed before the relevant phase

### D-006 · Public website and CMS — is this in scope, and what does it replace? *(before Sprint 8)*

**The SRSD's 55 modules contain no CMS and no public website module** — but the brief treats both as core
deliverables. This plan adds them as **MOD-01-14** in Phase 1, since the admissions funnel depends on the
public site.

Needed: does a website exist today, and is it being replaced or kept? Who authors content? How many pages and
content types? Is content migration in scope? Multilingual?

**Proceeding on:** a new Next.js site over a Laravel-backed CMS, replacing the current one, with content
migration excluded from scope until confirmed.
**Cost of late reversal:** medium — 3–4 sprints of work either way.

---

### D-007 · Moodle — existing instance or new? *(before Sprint 15)*

Version, hosting, current enrollment method, whether historical course data must migrate, and whether Moodle
or the ERP holds final grades.

**Proceeding on:** an existing Moodle 4.x that the ERP will sync into; **the ERP holds grades of record**
(see [`06-INTEGRATIONS.md`](06-INTEGRATIONS.md) §4 — importing final grades from Moodle would put the
transcript's source of truth in a system with weaker approval and audit controls).
**Cost of late reversal:** medium.

---

### D-008 · Payment providers *(before Sprint 11)*

Which M-Pesa product (Paybill, Till, B2C for refunds)? Which banks and do they offer APIs or only statement
files? Are card payments required? Who holds the Daraja credentials, and can we get sandbox access in Phase 0?

**Proceeding on:** M-Pesa Paybill with STK Push plus one bank via statement import; no card payments in
Phase 1.
**Cost of late reversal:** medium — the provider abstraction (ADR / §1 of Integrations) limits the blast
radius by design.

---

### D-009 · Grading scales, GPA formula and progression rules *(before Sprint 14)*

Current scale and boundaries; GPA weighting; how retakes, supplementaries and special exams affect GPA;
progression, probation and discontinuation thresholds; **and the historical versions of all of the above**,
because a 2015 graduate's transcript must reproduce with 2015's rules.

**Proceeding on:** a standard Kenyan university scale; retakes capped at the pass mark for GPA; historical
schemes to be supplied during migration.
**Cost of late reversal:** **high** — this is core examination logic and it affects migrated history.
**Needed from:** Academic Registrar / Senate.

---

### D-010 · Approval hierarchies *(before Sprint 3)*

Who approves marks, fee waivers, leave, procurement and payments — and at what value thresholds? Delegation
rules during absence?

**Proceeding on:** a configurable approval matrix (MOD-00-02) so these are data, not code — which is why this
is Tier 2 rather than Tier 1. The engine ships in Phase 0; the configuration can follow.
**Cost of late reversal:** low, by design.

---

### D-011 · Payroll statutory configuration *(before Sprint 26)*

Current PAYE, NHIF/SHIF, NSSF and housing levy rates and bands; pension schemes; union deductions; salary
scales; bank file formats; whether the existing payroll is parallel-run for two cycles (strongly recommended).

**Proceeding on:** current Kenyan statutory rates as configuration (never code), with two parallel cycles
before cutover.
**Cost of late reversal:** high — payroll errors are severe and highly visible.

---

### D-012 · Domain names and email/SMS providers *(before Sprint 4)*

Subdomain scheme (this plan assumes `www` / `apply` / `student` / `lecturer` / `staff` / `admin` / `exec`
under `mema.ac.ke`); who controls DNS; is Cloudflare acceptable; which email and SMS providers, and expected
monthly volumes.

**Proceeding on:** the subdomain scheme above; Cloudflare for DNS/CDN/WAF; provider selection deferred behind
the notification abstraction.
**Cost of late reversal:** low.

---

## Tier 3 — Confirm, but not schedule-critical

| # | Decision | Proceeding on |
|---|---|---|
| **D-013** | Branding — is `#0A3E50` / `#1E8449` Mema's actual palette? Logo, seal, typography, brand guidelines? | The SRSD palette, tokenised so a rebrand is a token change |
| **D-014** | Languages — English only, or Kiswahili too? | English only; i18n scaffolding present from Phase 0 so adding a language is not a refactor |
| **D-015** | Student/staff number formats — existing schemes and check digits? | Configurable generator; existing formats preserved on migration |
| **D-016** | Biometric hardware — which devices, and do they exist already? | ZKTeco-class ADMS devices, adapter-abstracted |
| **D-017** | SSO — is Microsoft 365 or Google Workspace in use institution-wide? | Local authentication in Phase 1; OIDC in Phase 5 |
| **D-018** | Library — Koha, or something else? | Koha via its API |
| **D-019** | Data residency — must data remain in Kenya? | Assumed yes; drives hosting and backup-region choices |
| **D-020** | Accessibility conformance — is WCAG 2.2 AA a contractual requirement? | Treated as mandatory regardless |

---

## Decision log

| # | Decision | Owner | Due | Status |
|---|---|---|---|---|
| D-001 | Backend platform | ICT Director | Before Sprint 1 | **OPEN — highest priority** |
| D-002 | Post-handover ownership | ICT Director | Before Sprint 1 | OPEN |
| D-003 | Financial year end | Bursar | Before Sprint 1 | OPEN |
| D-004 | Legacy inventory + access | ICT Director | Phase 0 week 1 | OPEN |
| D-005 | Institutional scale | Registrar | Before Sprint 1 | OPEN |
| D-006 | Website/CMS scope | Marketing + ICT | Before Sprint 8 | OPEN |
| D-007 | Moodle | ICT + Academic | Before Sprint 15 | OPEN |
| D-008 | Payment providers | Bursar | Before Sprint 11 | OPEN |
| D-009 | Grading and progression rules | Academic Registrar | Before Sprint 14 | OPEN |
| D-010 | Approval hierarchies | Module owners | Before Sprint 3 | OPEN |
| D-011 | Payroll statutory config | HR + Finance | Before Sprint 26 | OPEN |
| D-012 | Domains and providers | ICT Director | Before Sprint 4 | OPEN |
| D-013 – D-020 | See Tier 3 | Various | Phase-dependent | OPEN |
