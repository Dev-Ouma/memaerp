# PHASE 00 — PLATFORM ADMINISTRATION, CONFIGURATION & GOVERNANCE

`MOD-00` is the umbrella every other module stands on. Nothing in Phases 01–05 can be built correctly until
these five sub-modules exist, because they own identity, approvals, configuration, operations and audit.

Specification: [`../docs/UNIVERSITY-ERP-SRSD/PHASE-01/01-01-Identity-and-Access-Management.md`](../docs/UNIVERSITY-ERP-SRSD/PHASE-01/01-01-Identity-and-Access-Management.md)
(that filename is wrong — see defect D-06 in [`../PLAN/13-SRSD-GAP-AUDIT.md`](../PLAN/13-SRSD-GAP-AUDIT.md)).

---

## MOD-00 — Platform umbrella

```mermaid
flowchart TB
    subgraph MOD00["MOD-00 PLATFORM SERVICES"]
        A["MOD-00-01<br/>Identity · Auth · Access Control"]
        B["MOD-00-02<br/>Workflow · Tasks · Approvals"]
        C["MOD-00-03<br/>Configuration · Governance · Branding"]
        D["MOD-00-04<br/>Control Centre · Ops · Audit"]
        E["MOD-00-05<br/>Network Security · Impersonation · OAuth"]
    end
    subgraph CONSUMERS["EVERY OTHER MODULE"]
        P1[Phase 01 — 14 modules]
        P2[Phase 02 — 11 modules]
        P3[Phase 03 — 11 modules]
        P4[Phase 04 — 11 modules]
        P5[Phase 05 — 9 modules]
    end
    A -->|who am I · what may I do| CONSUMERS
    B -->|route · approve · delegate| CONSUMERS
    C -->|calendars · rules · branding| CONSUMERS
    D -->|health · jobs · audit trail| CONSUMERS
    E -->|network trust · SSO| CONSUMERS
    style MOD00 fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

---

## MOD-00-01 — Identity, Authentication & Access Control

```mermaid
flowchart TB
    subgraph IDENTITY["IDENTITY"]
        P["persons<br/>canonical spine"] --> U[users]
        U --> UI1[applicant identity]
        U --> UI2[student identity]
        U --> UI3[employee identity]
        U --> UI4[alumnus identity]
    end
    subgraph AUTHN["AUTHENTICATION"]
        L[Login] --> PW["Argon2id verify"]
        PW --> MFA{MFA required?}
        MFA -->|yes| TOTP[TOTP challenge]
        MFA -->|no| SESS
        TOTP --> SESS["Session<br/>__Host- cookie"]
        SESS --> DEV[Device register]
        SESS --> LOG[Login audit]
    end
    subgraph AUTHZ["AUTHORIZATION — three dimensions"]
        PERM["Permission<br/>module.resource.action"]
        ROLE["Role<br/>bundle of permissions"]
        SCOPE["Scope<br/>institution · campus · faculty<br/>department · self"]
        PERM --> GATE
        ROLE --> GATE
        SCOPE --> GATE["Policy Gate<br/>deny by default"]
    end
    U --> L
    SESS --> GATE
    GATE -->|allow| ACT[Action proceeds]
    GATE -->|deny| REJ["403 or 404<br/>no existence leak"]
    style P fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style GATE fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style REJ fill:#FEE2E2,stroke:#B91C1C
```

**The rule that matters:** permission alone never grants access. A Head of Department holding
`examination.marks.approve` may approve marks *only* for offerings inside their department scope. Scope is
applied as a query filter, not a post-fetch check — otherwise the data has already left the database.

---

## MOD-00-02 — Workflow Engine, Tasks & Approval Matrix

```mermaid
flowchart TB
    A[Business event] --> B["Workflow instance<br/>from versioned definition"]
    B --> C{Approval matrix lookup}
    C --> D["Tier 1 approver<br/>by role + scope"]
    D --> E{Decision}
    E -->|approve| F{More tiers?}
    E -->|reject| G[Terminated with reason]
    E -->|return| H[Back to originator]
    F -->|yes| I[Tier N approver]
    F -->|no| J[Workflow complete]
    I --> E
    D -.->|absent| K["Delegation<br/>time-boxed · audited"]
    K --> E
    D -.->|no action in SLA| L[Escalation]
    L --> M[Next tier or supervisor]
    B --> N[(Task inbox)]
    N --> O[Notification]
    J --> P[Downstream effect fires]
    G --> Q[Audit record]
    style B fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style P fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

Approval chains are **data, not code**. Changing who signs off a fee waiver is a configuration change with
its own audit record, never a deployment. Definitions are versioned: an in-flight workflow keeps running on
the version it started under.

---

## MOD-00-03 — System Configuration, Governance & Branding

```mermaid
flowchart LR
    subgraph CONFIG["CONFIGURATION DOMAINS"]
        A["Institution<br/>legal identity · registration"]
        B["Hierarchy<br/>campus · faculty · department"]
        C["Academic calendar<br/>years · terms · key dates"]
        D["Numbering<br/>student · staff · invoice · receipt"]
        E["Lookups<br/>statuses · types · reasons"]
        F["Branding<br/>logo · palette · letterhead · footer"]
        G["Feature flags"]
        H["Maintenance and lockdown modes"]
    end
    CONFIG --> V["Versioned config store<br/>effective-dated"]
    V --> X[Runtime resolution with cache]
    X --> Y[All modules]
    V --> Z[Audit of every change]
    C --> AA["Gates registration windows,<br/>marks windows, fee due dates"]
    D --> AB[Gapless sequence generator]
    F --> AC[PDF documents and portals]
    style V fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

Effective-dating is non-negotiable: a 2024 transcript must render under the 2024 grading scale and the 2024
letterhead, not today's. Configuration rows are never updated in place.

---

## MOD-00-04 — System Control Centre, Operations & Audit Governance

```mermaid
flowchart TB
    subgraph SOURCES["SIGNALS"]
        A[Application logs]
        B[Queue and job state]
        C[Database metrics]
        D[Integration health]
        E[Security events]
        F[Audit stream]
    end
    subgraph CENTRE["CONTROL CENTRE"]
        G[Live system health]
        H["Job monitor<br/>retry · release · fail"]
        I[Integration status board]
        J[Active session registry]
        K[Audit explorer]
        L[Scheduled task board]
    end
    SOURCES --> CENTRE
    CENTRE --> M{Threshold breached?}
    M -->|yes| N["Alert<br/>severity-routed"]
    N --> O[Runbook]
    F --> P[(audit.activity_log<br/>append-only · partitioned)]
    P --> Q["BEFORE UPDATE/DELETE trigger<br/>raises exception"]
    P --> R[Retention 7 years]
    K --> S["Read-audit on<br/>sensitive records"]
    style P fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style Q fill:#FEE2E2,stroke:#B91C1C
```

The audit table has no UPDATE or DELETE path — a database trigger raises an exception. Not a convention
enforced by discipline; enforced by the engine.

---

## MOD-00-05 — Network Security, Impersonation & OAuth 2.0 Server

```mermaid
flowchart TB
    subgraph NET["NETWORK TRUST"]
        A[Request] --> B{Source IP evaluated}
        B -->|admin subnet| C[Elevated actions permitted]
        B -->|campus network| D[Standard access]
        B -->|internet| E["Standard access<br/>step-up for sensitive"]
        B -->|blocked range| F[Reject]
    end
    subgraph IMP["BREAK-GLASS IMPERSONATION"]
        G[Support request] --> H{Authorised role?}
        H -->|no| F
        H -->|yes| I[Reason + ticket mandatory]
        I --> J[Time-boxed session]
        J --> K["Banner visible<br/>to impersonator"]
        J --> L["Every action tagged<br/>actor + on-behalf-of"]
        J --> M["Write actions restricted<br/>on financial and grade data"]
        J --> N[Auto-expire and notify subject]
    end
    subgraph OAUTH["OAUTH 2.0 / OIDC"]
        O[Client app] --> P[Authorization code + PKCE]
        P --> Q[Consent]
        Q --> R[Token issue]
        R --> S["Scoped access<br/>Moodle · mobile · partners"]
        R --> T[Refresh and revocation]
    end
    subgraph CERT["ACCESS CERTIFICATION"]
        U[Quarterly cycle] --> V[Manager reviews reports]
        V --> W{Still required?}
        W -->|no| X[Auto-revoke]
        W -->|yes| Y[Attest with date]
    end
    style L fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style X fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

Impersonation is the single most abusable feature in any ERP. It is built with the constraints above from day
one, not added after the first incident.
