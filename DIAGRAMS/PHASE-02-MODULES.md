# PHASE 02 — ACADEMIC SERVICES & STUDENT AFFAIRS

11 modules. Phase 01 made the record correct; Phase 02 makes the student's life work — classrooms, advising,
placements, welfare, accommodation and the staff tools that support all of it.

| Module | Name | Spec |
|---|---|---|
| MOD-02-01 | LMS Integration & E-Learning | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-01-LMS-Integration-and-E-Learning.md) |
| MOD-02-02 | Class & Lecturer Attendance | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-02-Class-and-Lecturer-Attendance.md) |
| MOD-02-03 | Academic Advising & Degree Audit | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-03-Academic-Advising-and-Degree-Audit.md) |
| MOD-02-04 | Industrial Attachment & Practicum | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-04-Industrial-Attachment-and-Practicum.md) |
| MOD-02-05 | Work-Study Programme | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-05-Work-Study-Programme.md) |
| MOD-02-06 | Library Management Integration | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-06-Library-Management-Integration.md) |
| MOD-02-07 | Student Affairs, Welfare & Elections | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-07-Student-Affairs-Welfare-and-Elections.md) |
| MOD-02-08 | Accommodation & Hostel Management | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-08-Accommodation-and-Hostel-Management.md) |
| MOD-02-09 | Student Request & Paperless Clearance | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-09-Student-Request-and-Paperless-Clearance.md) |
| MOD-02-10 | Scholarships & Financial Aid | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-10-Scholarships-and-Financial-Aid.md) |
| MOD-02-11 | Lecturer & Staff Portals | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-02/02-11-Lecturer-and-Staff-Portals.md) |

## Phase dependency graph

```mermaid
flowchart TB
    P1[(Phase 01 spine)] --> A[02-01 LMS]
    P1 --> B[02-02 Attendance]
    P1 --> C[02-03 Advising]
    P1 --> D[02-04 Attachment]
    P1 --> E[02-05 Work-Study]
    P1 --> F[02-06 Library]
    P1 --> G[02-07 Student Affairs]
    P1 --> H[02-08 Accommodation]
    P1 --> I[02-09 Requests and Clearance]
    P1 --> J[02-10 Financial Aid]
    A --> B
    B --> C
    C --> K[Retention signals to MOD-05-04]
    B --> K
    F --> I
    H --> I
    J --> L[Credits to student ledger MOD-01-09]
    E --> L
    A --> M[Grades of record stay in MOD-01-10]
    P1 --> N[02-11 Staff Portals]
    A --> N
    B --> N
    C --> N
    style P1 fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

---

## MOD-02-01 — LMS Integration & E-Learning

```mermaid
flowchart TB
    subgraph ERP["ERP — SOURCE OF TRUTH"]
        A[Course offerings]
        B[Enrollments]
        C[Lecturer allocations]
        D[Users and identities]
        E[Grades of record]
    end
    subgraph SYNC["SYNC ENGINE"]
        F["Outbound queue<br/>idempotent operations"]
        G[Moodle Web Services client]
        H["Nightly drift detection"]
        I[Reconciliation report]
    end
    subgraph MOODLE["MOODLE"]
        J[Courses]
        K[Enrolments and groups]
        L[Teacher assignments]
        M[Activities and gradebook]
    end
    A --> F
    B --> F
    C --> F
    D --> F
    F --> G
    G --> J
    G --> K
    G --> L
    M -->|"gradebook pull<br/>advisory only"| N[CA component import]
    N --> O{Lecturer confirms?}
    O -->|yes| E
    O -->|no| P[Discarded]
    J --> H
    K --> H
    H --> I
    I --> Q{Drift found?}
    Q -->|yes| R[Auto-heal or ticket]
    D --> S["OIDC SSO<br/>ERP is identity provider"]
    S --> MOODLE
    style ERP fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style E fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

**Direction of authority is one-way.** Moodle never creates a student, never changes an enrollment, and never
owns a grade of record. Marks pulled from the Moodle gradebook are a convenience import that a lecturer must
confirm through the normal marks-submission workflow.

---

## MOD-02-02 — Class & Lecturer Attendance

```mermaid
flowchart TB
    A[Published timetable] --> B["Sessions auto-generated<br/>per class meeting"]
    B --> C{Capture method}
    C --> D["QR code<br/>rotating, time-boxed"]
    C --> E[Lecturer manual register]
    C --> F[Biometric or card reader]
    C --> G[LMS activity for online mode]
    D --> H{Validation}
    E --> H
    F --> H
    G --> H
    H --> I["Enrolled in this section?"]
    I -->|no| J[Rejected]
    I -->|yes| K["Within session window<br/>and location if enforced?"]
    K -->|no| L[Flagged for review]
    K -->|yes| M[Attendance recorded]
    M --> N[Student attendance percentage]
    N --> O{Below threshold?}
    O -->|yes| P[Warning to student and advisor]
    P --> Q{Persistently below?}
    Q -->|yes| R["Exam eligibility flag<br/>to MOD-01-10"]
    B --> S[Lecturer delivery record]
    S --> T{Session held?}
    T -->|no| U[Missed class report to HoD]
    S --> V[Teaching hours to workload and part-time claims]
    N --> W[Retention risk signal MOD-05-04]
    style M fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style R fill:#FEE2E2,stroke:#B91C1C
```

Attendance is the earliest reliable predictor of dropout — three weeks of absence shows up here long before it
shows up in results. That is why the signal feeds the retention engine directly.

---

## MOD-02-03 — Academic Advising & Degree Audit

```mermaid
flowchart TB
    A[Student] --> B[Advisor allocation]
    B --> C[Advisor caseload dashboard]
    subgraph AUDIT["DEGREE AUDIT ENGINE"]
        D[Curriculum version requirements]
        E[Courses passed]
        F[Courses in progress]
        G[Transfer and exemption credits]
        D --> H[Requirement matching]
        E --> H
        F --> H
        G --> H
        H --> I["Completed · in progress · outstanding"]
        I --> J[Projected completion term]
        I --> K[What-if analysis for programme change]
    end
    A --> AUDIT
    I --> L[Student self-service view]
    I --> C
    C --> M{Risk indicators}
    M --> N[Low GPA]
    M --> O[Poor attendance]
    M --> P[Fee arrears]
    M --> Q[Failed prerequisites]
    M --> R[Off-track for completion]
    M --> S[Advising session scheduled]
    S --> T[Session notes and agreed actions]
    T --> U[Follow-up task with due date]
    U --> V{Actions completed?}
    V -->|no| W[Escalate to HoD or Dean]
    V -->|yes| X[Case closed with outcome]
    T --> Y[(Confidential advising record)]
    style AUDIT fill:#E8F5EC,stroke:#1E8449
    style Y fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

The same audit engine that advises a student in year two produces the graduation eligibility decision in year
four. One implementation, one answer — a student is never told they are on track and then refused at
clearance.

---

## MOD-02-04 — Industrial Attachment & Practicum

```mermaid
sequenceDiagram
    autonumber
    participant S as Student
    participant C as Coordinator
    participant O as Host Organisation
    participant U as University Supervisor
    participant E as ERP

    E->>E: Eligibility check (level, prerequisites, fees)
    S->>E: Apply with preferred placements
    C->>E: Approve or assign placement
    E->>O: Introduction letter (PDF)
    O->>E: Acceptance confirmation
    E->>E: Attachment period opens
    S->>E: Weekly logbook entries
    O->>E: Host supervisor endorsement
    C->>U: Assign university supervisor
    U->>E: Site visit report
    S->>E: Final report upload
    O->>E: Host evaluation form
    U->>E: Academic assessment
    E->>E: Composite attachment grade
    E->>E: Post to results as graded course unit
```

```mermaid
flowchart LR
    A[Placement registry] --> B[Organisation profiles]
    B --> C[Capacity per intake]
    B --> D[Historical evaluation quality]
    D --> E{Suitable for future placements?}
    E -->|no| F[Flagged and excluded]
    E -->|yes| A
    G[Insurance and MOU status] --> H{Valid?}
    H -->|no| I[Placement blocked]
    H -->|yes| A
    style I fill:#FEE2E2,stroke:#B91C1C
```

---

## MOD-02-05 — Work-Study Programme

```mermaid
flowchart TB
    A[Department posts work-study position] --> B[Budget allocation check]
    B --> C{Funds available?}
    C -->|no| D[Rejected]
    C -->|yes| E[Position published]
    E --> F[Student applies]
    F --> G{Eligibility}
    G --> H[Financial need assessment]
    G --> I["Academic standing minimum"]
    G --> J["Hours cap — must not harm studies"]
    H --> K[Selection and offer]
    I --> K
    J --> K
    K --> L[Placement active]
    L --> M[Timesheet submission]
    M --> N[Supervisor approval]
    N --> O{Within approved hours?}
    O -->|no| P[Rejected with reason]
    O -->|yes| Q[Payment computation]
    Q --> R{Payment mode}
    R -->|fee offset| S["Credit to student ledger<br/>MOD-01-09"]
    R -->|cash| T[Payment run via finance]
    L --> U[Performance evaluation]
    U --> V{Renew next term?}
    V -->|yes| L
    V -->|no| W[Placement closed]
    style S fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

---

## MOD-02-06 — Library Management Integration

```mermaid
flowchart TB
    subgraph ERP["ERP"]
        A[Student and staff identities]
        B[Enrollment status]
        C[Programme and level]
        D[Student ledger]
    end
    subgraph KOHA["KOHA LIBRARY"]
        E[Patron records]
        F[Circulation]
        G[Holds and renewals]
        H[Fines]
    end
    A --> I["Patron provisioning<br/>category from role and level"]
    B --> I
    C --> I
    I --> E
    B --> J{Status change}
    J -->|suspended or discontinued| K[Borrowing privileges revoked]
    J -->|graduated| L[Alumni patron category]
    F --> M[Loans outstanding]
    H --> N[Fines outstanding]
    M --> O["Library clearance check<br/>MOD-02-09"]
    N --> O
    N --> P{Post fines to student account?}
    P -->|per policy| D
    E --> Q[Nightly reconciliation]
    A --> Q
    Q --> R[Orphan and mismatch report]
    S[E-resources] --> T["SSO via OIDC<br/>entitlement by programme"]
    A --> T
    style O fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

---

## MOD-02-07 — Student Affairs, Welfare & Elections

```mermaid
flowchart TB
    subgraph WELFARE["WELFARE & SUPPORT"]
        A[Counselling appointment] --> B["Confidential case file<br/>restricted access role"]
        B --> C[Session notes]
        C --> D{Referral needed?}
        D -->|yes| E[Health services or external]
        F[Disability support] --> G["Accommodations registry<br/>exam time · venue · format"]
        G --> H[Flows to MOD-01-10 exam setup]
        I[Chaplaincy and peer support]
        J[Sports and clubs registry] --> K[Membership and participation]
    end
    subgraph DISCIPLINE["DISCIPLINE"]
        L[Incident report] --> M[Investigation]
        M --> N[Disciplinary committee]
        N --> O{Finding}
        O -->|upheld| P["Sanction<br/>warning · suspension · expulsion"]
        P --> Q[Student status update MOD-01-06]
        O -->|dismissed| R[Record closed]
        P --> S[Appeal window]
    end
    subgraph ELECTIONS["STUDENT ELECTIONS"]
        T[Election configured] --> U[Voter roll from active students]
        U --> V[Candidate nomination and vetting]
        V --> W[Campaign period]
        W --> X[Voting window opens]
        X --> Y{One vote per eligible voter}
        Y --> Z["Ballot recorded<br/>voter marked as voted<br/>ballot NOT linked to voter"]
        Z --> AA[Tally on close]
        AA --> AB[Results published with audit hash]
        AB --> AC[Dispute window]
    end
    style Z fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style B fill:#FEE2E2,stroke:#B91C1C
```

**Ballot secrecy is architectural.** The system records *that* a student voted and *what* the tallies are, in
two separate structures with no join between them. An administrator with full database access cannot
reconstruct how any individual voted.

---

## MOD-02-08 — Accommodation & Hostel Management

```mermaid
flowchart TB
    subgraph INVENTORY["INVENTORY"]
        A[Hostels] --> B[Blocks and floors]
        B --> C[Rooms with type and capacity]
        C --> D[Beds]
        D --> E{Bed state}
        E --> F[Available]
        E --> G[Allocated]
        E --> H[Out of service]
    end
    subgraph ALLOCATION["ALLOCATION"]
        I[Application window opens] --> J[Student applies with preferences]
        J --> K{Eligibility and priority}
        K --> L[First year guarantee]
        K --> M[Distance and need]
        K --> N[Special needs and accessibility]
        K --> O[Returning student conduct record]
        L --> P[Allocation run]
        M --> P
        N --> P
        O --> P
        P --> Q{Bed reserved atomically}
        Q -->|success| R[Offer issued with deadline]
        Q -->|none available| S[Waitlist]
        R --> T{Accepted and paid?}
        T -->|no| U["Offer lapses<br/>bed returns to pool"]
        U --> S
        T -->|yes| V[Occupancy active]
    end
    V --> W[Check-in with room condition record]
    W --> X[Maintenance requests]
    X --> Y[Work order to MOD-04-08]
    V --> Z[Room transfer request]
    V --> AA[Check-out and damage assessment]
    AA --> AB{Damages?}
    AB -->|yes| AC[Charge to student ledger]
    AA --> AD[Hostel clearance MOD-02-09]
    R --> AE[Hostel fee charge MOD-01-09]
    style Q fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

Bed allocation uses the same locked-row reservation pattern as course registration. Two students allocated the
same bed is the accommodation equivalent of oversubscription, and it is prevented the same way.

---

## MOD-02-09 — Student Request & Paperless Clearance

```mermaid
flowchart TB
    subgraph REQUESTS["REQUEST DESK"]
        A[Student raises request] --> B{Request type}
        B --> C[Transcript or letter]
        B --> D[Deferment or leave]
        B --> E[Programme or campus transfer]
        B --> F[Name or details correction]
        B --> G[Fee waiver or extension]
        B --> H[Retake or special exam]
        B --> I[ID card replacement]
        C --> J{Fee payable?}
        J -->|yes| K[Pay then proceed]
        J -->|no| L[Routing]
        K --> L
        L --> M["Approval chain from MOD-00-02<br/>by request type"]
        M --> N{Decision}
        N -->|approved| O[Fulfilment action]
        N -->|rejected| P[Reason to student]
        N -->|more info| Q[Returned to student]
        O --> R[Document generated or record changed]
    end
    subgraph CLEARANCE["CLEARANCE ENGINE"]
        S{Clearance trigger} --> T[Graduation]
        S --> U[Withdrawal or transfer]
        S --> V[End of hostel occupancy]
        T --> W[Desks activated in parallel]
        U --> W
        V --> W
        W --> X[Finance]
        W --> Y[Library]
        W --> Z[Hostel]
        W --> AA[Department]
        W --> AB[ICT]
        W --> AC[Sports]
        X --> AD{All desks cleared?}
        Y --> AD
        Z --> AD
        AA --> AD
        AB --> AD
        AC --> AD
        AD -->|no| AE["Blocked — live checklist<br/>shows what remains"]
        AD -->|yes| AF[Clearance certificate issued]
    end
    style AF fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style AE fill:#FEE2E2,stroke:#B91C1C
```

Desks clear **in parallel, not in sequence**. A student should never queue at six offices in a fixed order —
and each desk pulls its own status automatically where the data already exists in the system.

---

## MOD-02-10 — Scholarships & Financial Aid

```mermaid
flowchart TB
    subgraph SCHEMES["SCHEMES"]
        A[Scheme definition] --> B[Funding source and budget]
        B --> C{Source type}
        C --> D[Institutional bursary]
        C --> E[Donor or endowment]
        C --> F[Government — HELB]
        C --> G[Corporate sponsor]
        A --> H[Eligibility criteria]
        A --> I[Award value and coverage rules]
        A --> J[Renewal conditions]
    end
    subgraph APPLY["APPLICATION"]
        K[Student applies] --> L[Need assessment documents]
        L --> M[Verification]
        M --> N[Scoring and ranking]
        N --> O[Committee review]
        O --> P{Award decision}
        P -->|awarded| Q[Award record created]
        P -->|declined| R[Notified with reason]
    end
    Q --> S{Budget remaining?}
    S -->|no| T["Cannot exceed scheme budget<br/>hard stop"]
    S -->|yes| U[Commitment recorded]
    U --> V["Credit to student ledger<br/>MOD-01-09"]
    V --> W[Balance reduced]
    subgraph HELB["HELB / SPONSOR FLOW"]
        X[Sponsor batch file] --> Y[Match to students]
        Y --> Z{Matched?}
        Z -->|no| AA[Exception queue]
        Z -->|yes| AB[Allocate to invoices]
        AB --> V
        AC[Disbursement reconciliation] --> AD[Report back to sponsor]
    end
    Q --> AE[Renewal review at term end]
    AE --> AF{Conditions met?}
    AF -->|no| AG[Award suspended or withdrawn]
    AF -->|yes| AH[Renewed]
    style V fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style T fill:#FEE2E2,stroke:#B91C1C
```

---

## MOD-02-11 — Lecturer & Staff Portals

```mermaid
flowchart TB
    subgraph LECTURER["LECTURER APP"]
        A[Login] --> B[Teaching dashboard]
        B --> C[My courses this term]
        C --> D[Class list with photos]
        C --> E[Attendance capture]
        C --> F["Marks entry<br/>keyboard-optimised grid"]
        C --> G[Assessment plan]
        C --> H[Course materials to LMS]
        B --> I[My timetable]
        B --> J[Advisees and caseload]
        B --> K[Supervision — projects and theses]
        B --> L[Pending approvals]
        B --> M[Workload summary]
        B --> N[Leave requests]
    end
    subgraph STAFF["STAFF APP"]
        O[Login] --> P[Role-shaped dashboard]
        P --> Q[Registry desk]
        P --> R[Finance desk]
        P --> S[Hostel desk]
        P --> T[Library desk]
        P --> U[Admissions desk]
        P --> V[Task inbox from MOD-00-02]
        P --> W[Reports and exports]
    end
    subgraph DESIGN["DESIGN CONSTRAINTS"]
        X["Marks grid: no mouse required<br/>tab to advance · autosave draft"]
        Y[Every list exportable to Excel and PDF]
        Z[Bulk actions where volume demands]
        AA[Offline-tolerant attendance capture]
    end
    F --> X
    E --> AA
    W --> Y
    Q --> Z
    style F fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

Lecturers judge the entire ERP by the marks-entry screen. If entering 400 marks takes longer than the
spreadsheet they used before, they will keep using the spreadsheet and upload at the deadline — and the
integrity chain in MOD-01-10 loses its value.
