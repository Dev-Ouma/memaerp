# PHASE 05 — INTELLIGENCE, INTEGRATION & ADVANCED PLATFORM SERVICES

9 modules. Everything here depends on four years of clean data produced by Phases 01–04. Built earlier, these
modules would analyse and expose nothing of value.

| Module | Name | Spec |
|---|---|---|
| MOD-05-01 | Universal Integration & API Gateway | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-01-Universal-Integration-and-API-Gateway.md) |
| MOD-05-02 | Enterprise Data Warehouse & ETL | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-02-Enterprise-Data-Warehouse-and-ETL.md) |
| MOD-05-03 | Institutional Analytics & Executive BI | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-03-Institutional-Analytics-and-Executive-BI.md) |
| MOD-05-04 | Student Retention & Early Warning | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-04-Student-Retention-and-Early-Warning-System.md) |
| MOD-05-05 | AI Student Assistant & Predictive Intelligence | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-05-AI-Student-Assistant-and-Predictive-Intelligence.md) |
| MOD-05-06 | Universal Notification Engine | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-06-Universal-Notification-Engine.md) |
| MOD-05-07 | Public Digital Verification Portal | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-07-Public-Digital-Verification-Portal.md) |
| MOD-05-08 | University Mobile Application | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-08-University-Mobile-Application.md) |
| MOD-05-09 | Business Continuity & Disaster Recovery | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-05/05-09-Business-Continuity-and-Disaster-Recovery.md) |

> **Sequencing note.** MOD-05-06 (notifications) is built as a shared service in **Phase 0**, not Phase 5 —
> every module from Sprint 5 onward needs to send email and SMS. What Phase 5 adds is the full multi-channel
> engine: preferences, templates at scale, push, WhatsApp and campaign management. The same is true in part of
> MOD-05-09: backup and restore exist from Phase 0 (Gate 0 requires a timed restore); Phase 5 adds the tested
> full-institution DR capability.

## Phase dependency graph

```mermaid
flowchart TB
    A[(All prior phases —<br/>four years of clean data)] --> B[05-02 Data Warehouse and ETL]
    B --> C[05-03 Executive BI]
    B --> D[05-04 Retention Early Warning]
    D --> E[Interventions back into MOD-02-03 advising]
    B --> F[05-05 AI Assistant]
    A --> G[05-01 API Gateway]
    G --> H[Partner and government integrations]
    A --> I[05-06 Notification Engine — extended]
    A --> J[05-07 Public Verification]
    A --> K[05-08 Mobile App]
    G --> K
    I --> K
    A --> L[05-09 Business Continuity and DR]
    style A fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

---

## MOD-05-01 — Universal Integration & API Gateway

```mermaid
flowchart TB
    subgraph CONSUMERS["API CONSUMERS"]
        A[Mobile applications]
        B[Partner institutions]
        C[Government systems]
        D[Employer verification services]
        E[Third-party tools]
    end
    CONSUMERS --> F[Public API edge]
    F --> G{Authentication}
    G --> H["OAuth 2.0 client credentials<br/>MOD-00-05"]
    G --> I[Signed webhooks for callbacks]
    H --> J{Authorisation}
    J --> K["Scope-limited tokens —<br/>a partner sees only its own data"]
    K --> L{Rate limit and quota}
    L -->|exceeded| M[429 with retry-after]
    L -->|within| N[Request routed]
    N --> O["Versioned API surface<br/>/api/v1 · /api/v2"]
    O --> P[Same policy layer as internal calls]
    P --> Q[Response]
    subgraph OUTBOUND["OUTBOUND INTEGRATION"]
        R[Domain event occurs] --> S[(Outbox table<br/>same transaction)]
        S --> T[Relay worker]
        T --> U{Subscriber registered?}
        U -->|yes| V[Webhook delivery with HMAC signature]
        V --> W{Delivered?}
        W -->|no| X["Retry with exponential backoff<br/>then dead letter"]
        W -->|yes| Y[Acknowledged]
        X --> Z[Alert after threshold]
    end
    subgraph ADAPTERS["INTEGRATION ADAPTERS"]
        AA[Payment providers]
        AB[SMS and email]
        AC[Moodle]
        AD[Koha]
        AE[HELB and KUCCPS]
        AF[KRA · NSSF · SHIF]
        AG[CUE reporting]
    end
    ADAPTERS --> AH["Interface per capability —<br/>swap provider without touching callers"]
    subgraph RESILIENCE["RESILIENCE"]
        AI[Circuit breaker per integration]
        AJ[Timeout and retry budget]
        AK[Bulkhead — one slow partner cannot starve the rest]
        AL[Health board MOD-00-04]
    end
    ADAPTERS --> RESILIENCE
    style S fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style K fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

The outbox pattern is what makes outbound events trustworthy: the event row is written in the **same
transaction** as the business change, so an event can never describe something that did not happen, and a
committed change can never fail to produce its event.

---

## MOD-05-02 — Enterprise Data Warehouse & ETL

```mermaid
flowchart LR
    subgraph SOURCE["SOURCE"]
        A[(PostgreSQL primary)]
        A --> B[(Read replica)]
    end
    subgraph ETL["ETL PIPELINE — nightly"]
        B --> C[Extract with watermark]
        C --> D[(Staging — raw)]
        D --> E[Validate and profile]
        E --> F{Quality gate}
        F -->|fail| G[Load halted and alerted]
        F -->|pass| H[Transform and conform]
        H --> I[Surrogate key assignment]
        I --> J[Slowly changing dimension logic]
        J --> K[Load facts and dimensions]
        K --> L[Aggregate build]
        L --> M[Publish and mark run complete]
    end
    M --> N[(Data warehouse)]
    style G fill:#FEE2E2,stroke:#B91C1C
    style N fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

```mermaid
erDiagram
    FACT_ENROLLMENT }o--|| DIM_STUDENT : has
    FACT_ENROLLMENT }o--|| DIM_COURSE : for
    FACT_ENROLLMENT }o--|| DIM_TERM : in
    FACT_ENROLLMENT }o--|| DIM_PROGRAMME : under
    FACT_ENROLLMENT }o--|| DIM_ORG_UNIT : owned_by
    FACT_RESULT }o--|| DIM_STUDENT : achieved_by
    FACT_RESULT }o--|| DIM_COURSE : in
    FACT_RESULT }o--|| DIM_TERM : during
    FACT_RESULT }o--|| DIM_LECTURER : taught_by
    FACT_FEE_TRANSACTION }o--|| DIM_STUDENT : billed_to
    FACT_FEE_TRANSACTION }o--|| DIM_DATE : posted_on
    FACT_FEE_TRANSACTION }o--|| DIM_FEE_ITEM : for
    FACT_ATTENDANCE }o--|| DIM_STUDENT : by
    FACT_ATTENDANCE }o--|| DIM_COURSE : in
    FACT_APPLICATION }o--|| DIM_PROGRAMME : applied_to
    FACT_APPLICATION }o--|| DIM_DATE : received_on
    FACT_STAFF_COST }o--|| DIM_EMPLOYEE : for
    FACT_STAFF_COST }o--|| DIM_ORG_UNIT : charged_to
```

Dimensions are Type-2 slowly changing: when a student transfers faculty, historical enrolments still report
under the faculty they were in at the time. Otherwise last year's faculty statistics silently rewrite
themselves every time someone transfers.

---

## MOD-05-03 — Institutional Analytics & Executive BI

```mermaid
flowchart TB
    A[(Data warehouse)] --> B[Semantic layer — governed metric definitions]
    B --> C{Audience}
    C --> D[Council and VC]
    C --> E[DVC Academic]
    C --> F[DVC Finance and Administration]
    C --> G[Deans and HoDs]
    C --> H[Registrar]
    subgraph EXEC["EXECUTIVE VIEW"]
        D --> I[Enrolment trend and target variance]
        D --> J[Financial position and cash runway]
        D --> K[Fee collection rate]
        D --> L[Staff cost as percentage of income]
        D --> M[Research income and outputs]
        D --> N[Graduation and completion rates]
        D --> O[Student to staff ratio]
    end
    subgraph ACADEMIC["ACADEMIC VIEW"]
        E --> P[Pass rates by programme and course]
        E --> Q[Progression and repeat rates]
        E --> R[Course evaluation scores]
        E --> S[Capacity utilisation]
        E --> T[Time to completion]
    end
    subgraph FINANCE["FINANCE VIEW"]
        F --> U[Budget vs actual by vote]
        F --> V[Debtor ageing]
        F --> W[Payroll cost trend]
        F --> X[Procurement spend analysis]
    end
    subgraph OPS["OPERATIONAL VIEW"]
        G --> Y[Departmental scorecard]
        H --> Z[Registration completion tracker]
    end
    B --> AA["Row-level security —<br/>a Dean sees their faculty only"]
    AA --> C
    I --> AB[Drill from summary to detail]
    AB --> AC[Export to Excel and PDF]
    B --> AD[Scheduled report distribution]
    B --> AE{Threshold alerts}
    AE --> AF[Metric outside band notifies owner]
    style B fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AA fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

A governed semantic layer exists so "enrolment" means exactly one thing. Without it, Finance and Registry
bring different enrolment numbers to the same Council meeting — the most common failure of university BI.

---

## MOD-05-04 — Student Retention & Early Warning

```mermaid
flowchart TB
    subgraph SIGNALS["RISK SIGNALS"]
        A["Attendance decline<br/>MOD-02-02"]
        B["CA scores below threshold<br/>MOD-01-10"]
        C["Fee arrears and payment stalling<br/>MOD-01-09"]
        D["Failed to register in window<br/>MOD-01-07"]
        E["GPA trend downward<br/>MOD-01-11"]
        F["LMS inactivity<br/>MOD-02-01"]
        G["Library non-engagement<br/>MOD-02-06"]
        H["Prior probation history"]
        I["Demographic and entry factors"]
    end
    SIGNALS --> J[Weighted risk model]
    J --> K[Risk score per student per week]
    K --> L{Risk band}
    L --> M[Low — monitor]
    L --> N[Medium — advisor notified]
    L --> O[High — intervention required]
    L --> P[Critical — escalation to Dean]
    N --> Q[Advisor caseload queue MOD-02-03]
    O --> Q
    P --> R[Multi-party case conference]
    Q --> S{Intervention type}
    S --> T[Academic support and tutoring]
    S --> U[Financial aid referral MOD-02-10]
    S --> V[Counselling referral MOD-02-07]
    S --> W[Course load adjustment]
    S --> X[Deferment guidance]
    T --> Y[Intervention recorded with owner]
    U --> Y
    V --> Y
    W --> Y
    X --> Y
    Y --> Z[Outcome tracked at next term]
    Z --> AA{Retained?}
    AA -->|yes| AB[Success recorded]
    AA -->|no| AC[Exit reason captured]
    AB --> AD[Model feedback]
    AC --> AD
    AD --> J
    subgraph GOVERNANCE["MODEL GOVERNANCE"]
        AE["Scores are advisory —<br/>never an automated adverse action"]
        AF[Factors shown to the advisor, not hidden]
        AG[Bias review across demographic groups]
        AH[Model version recorded with every score]
    end
    J --> GOVERNANCE
    style K fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AE fill:#FEE2E2,stroke:#B91C1C
```

**No student is ever disadvantaged by a model output.** A risk score opens a conversation with an advisor; it
never blocks registration, alters a grade, or reduces support. The intervention log is what proves the
institution acted, and it is what makes the model improvable.

---

## MOD-05-05 — AI Student Assistant & Predictive Intelligence

```mermaid
flowchart TB
    A[Student or staff question] --> B[Assistant interface]
    B --> C{Intent classification}
    C --> D[Informational — policy and procedure]
    C --> E[Personal data — my fees, my results]
    C --> F[Transactional — do something for me]
    C --> G[Out of scope]
    D --> H[Retrieval over approved knowledge base]
    H --> I["Policies · handbooks · FAQs ·<br/>calendar · programme information"]
    I --> J[Grounded answer with source citation]
    E --> K{Authenticated and authorised?}
    K -->|no| L[Refuse and prompt to sign in]
    K -->|yes| M["Same API, same policy layer —<br/>no privileged data path"]
    M --> N[Scoped answer about the caller only]
    F --> O[Proposed action presented]
    O --> P{User confirms?}
    P -->|no| Q[Cancelled]
    P -->|yes| R["Executed through normal API<br/>with full audit"]
    G --> S[Handoff to helpdesk MOD-04-07]
    J --> T[Answer delivered]
    N --> T
    R --> T
    T --> U[Feedback — helpful or not]
    U --> V[Quality review queue]
    V --> W[Knowledge base improvement]
    subgraph GUARDRAILS["GUARDRAILS"]
        X["No answer without a source —<br/>refuse rather than guess"]
        Y[No access to another person's data, ever]
        Z[No autonomous writes without confirmation]
        AA[Full conversation audit]
        AB[Escalation path to a human always visible]
        AC["Never gives grade, fee or eligibility<br/>rulings — it points to the record"]
    end
    B --> GUARDRAILS
    style M fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style GUARDRAILS fill:#FEE2E2,stroke:#B91C1C
```

The assistant is a **front door to existing endpoints**, not a new privileged reader of the database. If a
student cannot see something in the portal, the assistant cannot see it either — the same policy gate runs.

---

## MOD-05-06 — Universal Notification Engine

```mermaid
flowchart TB
    subgraph TRIGGERS["EVENT SOURCES"]
        A[Domain events from every module]
        B[Scheduled reminders]
        C[Threshold alerts]
        D[Manual campaigns]
    end
    TRIGGERS --> E[Notification request]
    E --> F[Template resolution with locale]
    F --> G[Personalisation from record data]
    G --> H{Recipient preferences}
    H --> I[Channel opt-in and opt-out]
    H --> J[Quiet hours]
    H --> K[Digest vs immediate]
    I --> L{Channel selection}
    L --> M[Email — SMTP]
    L --> N[SMS — gateway]
    L --> O[Push — mobile MOD-05-08]
    L --> P[In-app inbox]
    L --> Q[WhatsApp if enabled]
    M --> R[Queued for delivery]
    N --> R
    O --> R
    P --> R
    Q --> R
    R --> S{Delivery attempt}
    S -->|success| T[Delivered and receipted]
    S -->|soft failure| U[Retry with backoff]
    S -->|hard failure| V[Channel fallback]
    V --> W{Alternative channel?}
    W -->|yes| R
    W -->|no| X[Undeliverable — flagged]
    U --> S
    T --> Y[(Notification history<br/>per recipient)]
    X --> Y
    Y --> Z["Proof of notice —<br/>what was sent, when, to where"]
    subgraph CONTROLS["CONTROLS"]
        AA["Critical notices cannot be opted out<br/>fee deadlines · exam changes · safety"]
        AB[Rate limiting per recipient — no flooding]
        AC[Cost control on SMS with budget alerts]
        AD[Test mode in non-production — no real sends]
        AE[Bounce and complaint handling]
    end
    H --> CONTROLS
    style Z fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AD fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

"Proof of notice" matters more than it sounds. When a student disputes a deregistration for non-payment, the
institution must show exactly what was sent, to which number, and when.

---

## MOD-05-07 — Public Digital Verification Portal

```mermaid
sequenceDiagram
    autonumber
    participant E as Employer / Verifier
    participant P as Public Portal
    participant V as Verification Service
    participant D as ERP Records
    participant L as Audit Log

    E->>P: Scan QR or enter serial number
    P->>P: Rate limit and bot check
    P->>V: Verification request
    V->>V: Validate serial format and checksum
    V->>D: Look up credential by serial
    alt Not found
        D-->>V: No record
        V-->>P: Not verified
        P-->>E: This credential could not be verified
    else Found and valid
        D-->>V: Credential record
        V->>V: Check revocation status
        V-->>P: Verified result
        P-->>E: Name · award · class · date · status
    else Found but revoked
        V-->>P: Revoked
        P-->>E: Credential revoked — contact registrar
    end
    V->>L: Log verification attempt (who, when, which serial)
```

```mermaid
flowchart TB
    A[Credential issued MOD-01-12] --> B[Unique serial generated]
    B --> C[QR encoding serial plus signature]
    C --> D[Printed on certificate and transcript]
    B --> E[(Verification index)]
    F[Verification request] --> E
    E --> G{Status}
    G --> H[Valid]
    G --> I["Revoked — fraud or error"]
    G --> J[Not found]
    subgraph DISCLOSURE["MINIMAL DISCLOSURE"]
        K["Shows only: name as awarded, award,<br/>classification, conferment date, status"]
        L["Never shows: marks, GPA, fees,<br/>contact details, national ID"]
        M[No bulk enumeration — serial required]
        N[No search by name]
    end
    H --> DISCLOSURE
    E --> O[Verification analytics]
    O --> P[Detect abnormal patterns]
    style K fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style L fill:#FEE2E2,stroke:#B91C1C
```

A verification portal that lets anyone search by name is a data-protection incident waiting to happen. The
holder must supply the serial from their own document — verification confirms a claim, it does not disclose a
record.

---

## MOD-05-08 — University Mobile Application

```mermaid
flowchart TB
    A[Mobile app] --> B{Audience build}
    B --> C[Student]
    B --> D[Staff and lecturer]
    subgraph STUDENT["STUDENT FEATURES"]
        C --> E[Fee balance and pay by M-Pesa]
        C --> F[Timetable with today view]
        C --> G[Results and GPA]
        C --> H[Attendance QR scan]
        C --> I[Registration status]
        C --> J[Notifications and announcements]
        C --> K[Digital student ID]
        C --> L[Library and hostel status]
        C --> M[Helpdesk tickets]
    end
    subgraph STAFF["STAFF FEATURES"]
        D --> N[Class list and attendance capture]
        D --> O[Approvals inbox]
        D --> P[Payslip]
        D --> Q[Leave request and balance]
        D --> R[Teaching timetable]
    end
    A --> S{Connectivity}
    S -->|online| T[Live API calls]
    S -->|offline| U["Cached read data with<br/>staleness indicator"]
    U --> V[Queued actions]
    V --> W{Reconnected?}
    W -->|yes| X[Sync with conflict handling]
    A --> Y[Authentication]
    Y --> Z[OAuth 2.0 with PKCE MOD-00-05]
    Z --> AA[Biometric unlock for return sessions]
    Y --> AB[Device registration and revocation]
    A --> AC[Push notifications MOD-05-06]
    subgraph CONSTRAINTS["DESIGN CONSTRAINTS"]
        AD["Data-light — most students are<br/>on metered mobile data"]
        AE[Works on low-end Android devices]
        AF["Attendance capture must work<br/>in a lecture hall with poor signal"]
        AG[No business logic in the client]
    end
    A --> CONSTRAINTS
    style U fill:#E8F5EC,stroke:#1E8449
    style AG fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

---

## MOD-05-09 — Business Continuity & Disaster Recovery

```mermaid
flowchart TB
    subgraph BACKUP["BACKUP REGIME"]
        A[PostgreSQL base backup — daily] --> B[(Off-site object storage)]
        C[WAL archiving — continuous] --> B
        D[Object storage replication] --> E[(Secondary region)]
        F[Configuration and secrets backup] --> B
        G[Infrastructure as code in Git] --> H[(Repository)]
    end
    B --> I{Restore test}
    I --> J["Scheduled restore drill —<br/>timed and documented"]
    J --> K{RTO and RPO met?}
    K -->|no| L[Remediation and re-drill]
    K -->|yes| M[Signed-off capability]
    subgraph SCENARIOS["DR SCENARIOS"]
        N[Database corruption] --> O["Point-in-time recovery<br/>to before the event"]
        P[Server failure] --> Q[Rebuild from IaC plus restore]
        R[Ransomware] --> S["Immutable backup copies —<br/>restore to clean infrastructure"]
        T[Data centre loss] --> U[Secondary region activation]
        V[Accidental mass deletion] --> O
        W[Integration provider outage] --> X[Degraded mode with queue]
    end
    subgraph CONTINUITY["BUSINESS CONTINUITY"]
        Y[Business impact analysis] --> Z{Criticality tier}
        Z --> AA["Tier 1 — registration, results, payments"]
        Z --> AB[Tier 2 — portals, LMS sync]
        Z --> AC[Tier 3 — reporting, analytics]
        AA --> AD[Shortest RTO and RPO]
        AE[Manual fallback procedures documented]
        AF[Communication plan and holding statements]
        AG[Crisis team roles and contacts]
    end
    subgraph RUNBOOK["OPERATIONAL READINESS"]
        AH[Runbook per scenario]
        AI[Annual full DR exercise]
        AJ[Post-incident review feeds improvements]
        AK[Contact tree kept current]
    end
    M --> RUNBOOK
    style J fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style S fill:#FEE2E2,stroke:#B91C1C
    style M fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

**A backup that has never been restored is not a backup.** Gate 0 requires a timed restore before any student
data enters the system, and this module makes that a scheduled, evidenced, repeating exercise rather than a
one-off.
