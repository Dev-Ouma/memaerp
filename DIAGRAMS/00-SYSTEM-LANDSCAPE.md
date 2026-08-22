# SYSTEM LANDSCAPE DIAGRAMS

## 1. System context — who uses it and what it talks to

```mermaid
flowchart TB
    subgraph EXTERNAL_USERS["EXTERNAL"]
        PROSPECT[Prospective Students]
        PARENT[Parents and Sponsors]
        PUBLIC[Public and Employers]
        PARTNER[Partner Institutions]
    end
    subgraph INTERNAL_USERS["INTERNAL"]
        STUDENT[Students]
        LECTURER[Lecturers]
        ADMIN_U[Registry · Finance · HR]
        EXEC_U[VC · DVC · Council]
        ICT[ICT Operations]
    end
    ERP["MEMA ERP / UMIS<br/>57 modules · one platform"]
    subgraph EXT_SYS["EXTERNAL SYSTEMS"]
        MPESA[M-Pesa Daraja]
        BANK[Banks]
        MOODLE[Moodle LMS]
        KOHA[Koha Library]
        HELB[HELB]
        KUCCPS[KUCCPS]
        KRA[KRA · NSSF · SHIF]
        CUE[CUE]
        SMSG[SMS Gateway]
        MAILG[Email Provider]
    end
    EXTERNAL_USERS --> ERP
    INTERNAL_USERS --> ERP
    ERP <--> MPESA
    ERP <--> BANK
    ERP <--> MOODLE
    ERP <--> KOHA
    ERP <--> HELB
    ERP <--> KUCCPS
    ERP --> KRA
    ERP --> CUE
    ERP --> SMSG
    ERP --> MAILG
    style ERP fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

## 2. Container view

```mermaid
flowchart TB
    subgraph CLIENT["CLIENT TIER"]
        B[Web Browsers]
        M[Mobile Apps]
    end
    subgraph EDGE["EDGE"]
        CF["Cloudflare<br/>WAF · CDN · DNS"]
        NG["Nginx<br/>TLS · proxy · rate limit"]
    end
    subgraph WEB["WEB TIER — Next.js 15"]
        W1[website] 
        W2[applicant]
        W3[student]
        W4[lecturer]
        W5[staff]
        W6[admin]
        W7[management]
    end
    subgraph APP["APPLICATION TIER — Laravel 12 / PHP 8.4"]
        API["REST API /api/v1<br/>OpenAPI 3.1"]
        MODS["57 Modules<br/>Services · Policies · Actions"]
        WORK["Horizon + 6 Queues"]
        SCHED[Scheduler]
    end
    subgraph DATA["DATA TIER"]
        PG[(PostgreSQL 17<br/>16 schemas)]
        RP[(Read Replica)]
        RD[(Redis 7)]
        S3[(S3 Storage)]
    end
    B --> CF
    M --> CF
    CF --> NG
    NG --> WEB
    NG --> API
    WEB --> API
    API --> MODS
    MODS --> WORK
    SCHED --> WORK
    MODS --> PG
    MODS --> RD
    MODS --> S3
    WORK --> PG
    WORK --> S3
    PG --> RP
    RP --> MODS
    style API fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style DATA fill:#E8F5EC,stroke:#1E8449
```

## 3. Request lifecycle — every authenticated write

```mermaid
sequenceDiagram
    participant U as User
    participant N as Next.js
    participant C as Cloudflare
    participant X as Nginx
    participant L as Laravel
    participant P as Policy
    participant S as Service
    participant D as PostgreSQL
    participant Q as Queue

    U->>N: Action
    N->>C: POST /api/v1/... (cookie + CSRF + Idempotency-Key)
    C->>C: WAF · rate limit
    C->>X: Forward
    X->>L: Proxy
    L->>L: Session · CSRF · correlation ID
    L->>L: Form Request validation
    L->>P: authorize(user, action, resource)
    P->>P: permission AND scope
    P-->>L: allow
    L->>S: execute
    S->>D: BEGIN
    S->>D: business writes
    S->>D: audit row (append-only)
    S->>D: COMMIT
    S->>Q: dispatch side effects
    S-->>L: result
    L-->>N: 200 + data + request_id
    Q->>Q: email · SMS · PDF · LMS sync
```

Note the ordering: authorization before any work, audit inside the same transaction as the change, side
effects only after commit.

## 4. Security layers

```mermaid
flowchart LR
    A["Cloudflare<br/>WAF · DDoS · bot"] --> B["Nginx<br/>TLS 1.3 · headers · IP rules"]
    B --> C["Session<br/>__Host- cookie · MFA · device"]
    C --> D["Policy<br/>permission + scope · deny default"]
    D --> E["Application<br/>CSRF · allow-lists · encoding"]
    E --> F["Database<br/>constraints · least privilege · encryption"]
    F --> G["Audit<br/>append-only · immutable"]
    G --> H["Monitoring<br/>anomaly · alert · runbook"]
    style D fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style G fill:#E8F5EC,stroke:#1E8449
```

## 5. Money data flow

```mermaid
flowchart TB
    A[Fee Structure] --> B[Student Charges]
    B --> C[Invoice]
    C --> D[(Student Ledger<br/>append-only)]
    E[M-Pesa] --> F{Reconciliation}
    G[Bank] --> F
    H[Sponsor / HELB] --> F
    F -->|matched| I[Allocation]
    F -->|unmatched| J[Exception Queue]
    I --> D
    I --> K[Receipt]
    D --> L[Balance — derived]
    L --> M{Fee clearance?}
    M -->|yes| N[Registration permitted]
    M -->|no| O[Registration blocked]
    D --> P[GL Journal — double entry]
    style D fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style J fill:#FEE2E2,stroke:#B91C1C
    style L fill:#E8F5EC,stroke:#1E8449
```

## 6. Academic data flow

```mermaid
flowchart TB
    A[Curriculum] --> B[Course Offering]
    B --> C[Enrollment]
    C --> D[Attendance]
    C --> E[Continuous Assessment]
    C --> F[Examination]
    E --> G[Composite Mark]
    F --> G
    G --> H[Moderation]
    H --> I[Senate Approval]
    I --> J[Published Result]
    J --> K[Term GPA]
    K --> L[Cumulative GPA]
    L --> M{Progression}
    M -->|proceed| N[Next Level]
    M -->|probation| O[Advising Intervention]
    M -->|complete| P[Degree Audit]
    P --> Q[Clearance]
    Q --> R[Graduation List]
    R --> S[Transcript + Certificate]
    S --> T[Public Verification]
    D --> U[Retention Risk Engine]
    G --> U
    style J fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style S fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```
