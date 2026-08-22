# PHASE 04 — RESEARCH, POSTGRADUATE & INSTITUTIONAL GOVERNANCE

11 modules. Research income and postgraduate outcomes are what distinguish a university from a college; this
phase also carries the governance, service and facilities layer that keeps the institution running and
auditable.

| Module | Name | Spec |
<!-- |---|---|---| -->
| MOD-04-01 | Research Grants & Projects | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-01-Research-Grants-and-Projects-Management.md) |
| MOD-04-02 | Postgraduate Lifecycle & Thesis Tracking | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-02-Postgraduate-Lifecycle-and-Thesis-Tracking.md) |
| MOD-04-03 | Research Ethics Review & Compliance | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-03-Research-Ethics-Review-and-Compliance.md) |
| MOD-04-04 | Quality Assurance & Course Evaluation | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-04-Quality-Assurance-and-Course-Evaluation.md) |
| MOD-04-05 | Senate, Council & Committee Governance | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-05-Senate-Council-and-Committee-Governance.md) |
| MOD-04-06 | Enterprise Document Management | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-06-Enterprise-Document-Management-System.md) |
| MOD-04-07 | Helpdesk & ICT Service Management | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-07-Helpdesk-and-ICT-Service-Management.md) |
| MOD-04-08 | Facilities, Estates & Fleet | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-08-Facilities-Estates-and-Fleet-Management.md) |
| MOD-04-09 | Campus Security & Incidents | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-09-Campus-Security-and-Incident-Management.md) |
| MOD-04-10 | University Health & Clinic Services | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-10-University-Health-and-Clinic-Services.md) |
| MOD-04-11 | Alumni Relations & Tracer Studies | [spec](../docs/UNIVERSITY-ERP-SRSD/PHASE-04/04-11-Alumni-Relations-and-Tracer-Studies.md) |

## Phase dependency graph

```mermaid
flowchart TB
    A[04-03 Ethics] --> B[04-01 Research Grants]
    B --> C[Grant income to MOD-03-09]
    B --> D[Grant expenditure to MOD-03-08]
    E[(MOD-01 spine)] --> F[04-02 Postgraduate]
    A --> F
    B --> F
    E --> G[04-04 Quality Assurance]
    G --> H[Appraisals MOD-03-04]
    I[04-05 Governance] --> J[Approvals feed curriculum and graduation]
    K[04-06 DMS] --> I
    K --> B
    K --> F
    L[04-07 Helpdesk]
    M[04-08 Facilities and Fleet] --> N[Hostel maintenance MOD-02-08]
    O[04-09 Security]
    P[04-10 Health] --> Q[Sick leave and special exams]
    E --> R[04-11 Alumni]
    F --> R
    style E fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

---

## MOD-04-01 — Research Grants & Projects Management

```mermaid
flowchart TB
    subgraph PRE["PRE-AWARD"]
        A[Funding call registered] --> B[Researcher expresses interest]
        B --> C[Concept note]
        C --> D[Full proposal development]
        D --> E{Ethics required?}
        E -->|yes| F[Ethics clearance MOD-04-03]
        E -->|no| G[Internal review]
        F --> G
        G --> H[Budget preparation with overheads]
        H --> I[Institutional endorsement]
        I --> J[Submission to funder]
        J --> K{Outcome}
        K -->|unsuccessful| L[Feedback archived for reuse]
        K -->|successful| M[Award notification]
    end
    subgraph POST["POST-AWARD"]
        M --> N[Grant agreement signed]
        N --> O["Project account opened<br/>MOD-03-07 fund segment"]
        O --> P[Budget lines by category]
        P --> Q[Milestone and deliverable schedule]
        Q --> R[Expenditure against grant]
        R --> S{Within grant budget line?}
        S -->|no| T[Blocked — virement needed]
        S -->|yes| U[Committed and spent]
        U --> V[Grant expenditure reports]
        V --> W[Funder financial reporting]
        Q --> X[Technical progress reports]
        R --> Y[Research staff on grant payroll]
        Y --> Z[Contract end tied to grant end]
    end
    subgraph OUT["OUTPUTS"]
        AA[Publications registry] --> AB[DOI and indexing]
        AB --> AC[Citation tracking]
        AC --> AD[Promotion criteria MOD-03-13]
        AA --> AE[Institutional research profile]
        AF[Patents and IP]
        AG[Conference presentations]
    end
    Q --> AA
    N --> AH[Project closeout and final report]
    AH --> AI[Audit certificate if required]
    style O fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style T fill:#FEE2E2,stroke:#B91C1C
```

Grant money is **restricted** money. It cannot be pooled with general funds, cannot be spent outside its
approved budget lines, and must be reportable to the funder at any moment. That is why it gets its own fund
segment in the chart of accounts rather than a memo field.

---

## MOD-04-02 — Postgraduate Lifecycle & Thesis Tracking

```mermaid
stateDiagram-v2
    [*] --> Admitted
    Admitted --> Registered: coursework or research registration
    Registered --> CourseworkComplete: taught units passed
    CourseworkComplete --> SupervisorAllocated
    Registered --> SupervisorAllocated: research-only mode
    SupervisorAllocated --> ProposalDevelopment
    ProposalDevelopment --> ProposalDefence: proposal submitted
    ProposalDefence --> ProposalApproved: passed
    ProposalDefence --> ProposalDevelopment: revisions required
    ProposalApproved --> EthicsClearance
    EthicsClearance --> Fieldwork: cleared
    Fieldwork --> ThesisWriting
    ThesisWriting --> ProgressReview: periodic milestones
    ProgressReview --> ThesisWriting: on track
    ProgressReview --> AtRisk: milestones missed
    AtRisk --> ThesisWriting: remediation agreed
    AtRisk --> Discontinued: no progress
    ThesisWriting --> SimilarityCheck: thesis submitted
    SimilarityCheck --> ExaminerAppointment: within threshold
    SimilarityCheck --> ThesisWriting: exceeds threshold
    ExaminerAppointment --> Examination
    Examination --> VivaVoce
    VivaVoce --> MinorCorrections
    VivaVoce --> MajorCorrections
    VivaVoce --> Failed
    MinorCorrections --> CorrectionsApproved
    MajorCorrections --> Examination: resubmission
    CorrectionsApproved --> SenateApproval
    SenateApproval --> Graduated
    Graduated --> [*]
    Discontinued --> [*]
    Failed --> [*]
```

```mermaid
flowchart TB
    A[Supervisor allocation] --> B{Capacity check}
    B --> C["Maximum candidates per supervisor<br/>by grade and policy"]
    C -->|at capacity| D[Blocked — reassign]
    C -->|available| E[Allocation confirmed]
    E --> F[Main and co-supervisor]
    F --> G[Supervision agreement signed]
    G --> H[Scheduled supervision meetings]
    H --> I[Meeting log with agreed actions]
    I --> J[Progress report per term]
    J --> K{Satisfactory?}
    K -->|no| L[Board of postgraduate studies review]
    K -->|yes| M[Continue and extend registration]
    N[Registration clock] --> O{Maximum duration}
    O -->|approaching| P[Extension request with justification]
    O -->|exceeded| Q[Deregistration review]
    E --> R[Supervision load to MOD-03-12 workload]
    I --> S[Supervisor performance input MOD-03-04]
    style C fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

---

## MOD-04-03 — Research Ethics Review & Compliance

```mermaid
flowchart TB
    A[Researcher submits protocol] --> B{Risk triage}
    B -->|no human or animal subjects| C[Exempt — recorded]
    B -->|minimal risk| D[Expedited review]
    B -->|greater than minimal| E[Full committee review]
    D --> F[Designated reviewers]
    E --> G[IREC full sitting]
    F --> H{Decision}
    G --> H
    H -->|approved| I["Approval certificate<br/>with validity period"]
    H -->|conditional| J[Conditions to be met]
    H -->|revise| K[Revise and resubmit]
    H -->|rejected| L[Rejected with reasons]
    J --> M[Conditions verified]
    M --> I
    K --> A
    I --> N[Research may commence]
    N --> O{Approval expiring?}
    O -->|yes| P[Continuing review or renewal]
    P --> Q{Renewed?}
    Q -->|no| R["Research must halt<br/>flagged to grants module"]
    N --> S[Protocol amendment request]
    S --> T[Amendment review]
    N --> U[Adverse event reporting]
    U --> V{Serious?}
    V -->|yes| W[Immediate committee notification]
    W --> X[Possible suspension]
    subgraph CONFLICT["INTEGRITY"]
        Y[Conflict of interest declarations]
        Z["Reviewer recusal enforced<br/>cannot review own protocol"]
        AA[Misconduct allegations]
        AA --> AB[Investigation panel]
    end
    G --> CONFLICT
    I --> AC[Prerequisite for grant release MOD-04-01]
    I --> AD[Prerequisite for PG fieldwork MOD-04-02]
    style Z fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style R fill:#FEE2E2,stroke:#B91C1C
```

---

## MOD-04-04 — Quality Assurance & Course Evaluation

```mermaid
flowchart TB
    subgraph EVAL["COURSE EVALUATION"]
        A[Evaluation window opens near term end] --> B[Eligible respondents = enrolled students]
        B --> C["Anonymous response<br/>respondent marked, response unlinked"]
        C --> D{Minimum response threshold?}
        D -->|below| E["Results suppressed —<br/>too few to protect anonymity"]
        D -->|met| F[Aggregated scores]
        F --> G[Released after results published]
        G --> H[Lecturer sees own scores and comments]
        G --> I[HoD sees departmental comparison]
        G --> J[Dean and QA see institutional view]
        H --> K[Improvement plan for low scores]
        F --> L[Appraisal input MOD-03-04]
    end
    subgraph AUDIT["QUALITY AUDITS"]
        M[Audit schedule] --> N{Audit type}
        N --> O[Internal academic audit]
        N --> P[Accreditation self-assessment]
        N --> Q[External CUE inspection]
        O --> R[Evidence collection]
        P --> R
        Q --> R
        R --> S[Findings and non-conformities]
        S --> T[Corrective action plan with owners]
        T --> U[Action tracking to closure]
        U --> V{Closed by due date?}
        V -->|no| W[Escalation]
        V -->|yes| X[Verification and closure]
    end
    subgraph ACCRED["ACCREDITATION"]
        Y[Programme accreditation register] --> Z[Status and expiry]
        Z --> AA{Expiring within 12 months?}
        AA -->|yes| AB[Renewal process triggered]
        Z --> AC[Compliance evidence pack from ERP data]
        AC --> AD["Enrolment · staffing ratios ·<br/>facilities · outcomes"]
    end
    style C fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style E fill:#E8F5EC,stroke:#1E8449
```

Evaluation anonymity uses the same split-structure technique as student elections: the system knows who
responded (to close the window and chase non-respondents) and knows the aggregate, with no path between them.

---

## MOD-04-05 — Senate, Council & Committee Governance

```mermaid
flowchart TB
    subgraph SETUP["COMMITTEE REGISTRY"]
        A[Committee defined] --> B[Terms of reference]
        A --> C[Membership with terms of office]
        A --> D[Quorum rules]
        A --> E[Meeting schedule]
    end
    subgraph CYCLE["MEETING CYCLE"]
        F[Agenda items submitted] --> G{Deadline met?}
        G -->|no| H[Deferred to next meeting]
        G -->|yes| I[Papers uploaded]
        I --> J[Agenda compiled and approved by chair]
        J --> K["Notice and papers circulated<br/>N days before"]
        K --> L[Meeting convened]
        L --> M{Quorum present?}
        M -->|no| N[Adjourned and recorded]
        M -->|yes| O[Items deliberated]
        O --> P{Decision}
        P --> Q[Resolution passed]
        P --> R[Deferred with reason]
        P --> S[Rejected]
        Q --> T[Minutes drafted]
        T --> U[Chair review]
        U --> V[Circulated for confirmation]
        V --> W[Confirmed at next meeting]
        W --> X[(Minute book — immutable)]
    end
    subgraph ACTION["RESOLUTION TRACKING"]
        Q --> Y[Action with owner and due date]
        Y --> Z[Progress updates]
        Z --> AA{Complete?}
        AA -->|no| AB[Reported to next meeting]
        AA -->|yes| AC[Closed with evidence]
    end
    subgraph EFFECT["DOWNSTREAM EFFECTS"]
        Q --> AD[Programme approval MOD-01-03]
        Q --> AE[Graduation list approval MOD-01-12]
        Q --> AF[Results ratification MOD-01-11]
        Q --> AG[Promotions approval MOD-03-13]
        Q --> AH[Budget approval MOD-03-14]
        Q --> AI[Policy publication MOD-04-06]
    end
    style X fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
```

Senate resolutions are not administrative notes — they are the legal authority behind graduations, programme
changes and results. The link between a resolution and the record it authorises is stored, so any record can
answer "under whose authority?".

---

## MOD-04-06 — Enterprise Document Management System

```mermaid
flowchart TB
    subgraph CAPTURE["CAPTURE"]
        A[Upload] --> D[Document ingested]
        B[Scan with OCR] --> D
        C["System-generated<br/>invoices · transcripts · letters"] --> D
    end
    D --> E[Metadata and classification]
    E --> F{Document class}
    F --> G[Student file]
    F --> H[Employee file]
    F --> I[Financial record]
    F --> J[Governance and policy]
    F --> K[Contract]
    F --> L[Research record]
    E --> M[Retention schedule assigned]
    E --> N[Access control by class and scope]
    D --> O[Version 1 stored]
    O --> P{Revised?}
    P -->|yes| Q["New version<br/>prior versions retained"]
    D --> R[Full-text index]
    R --> S[Search with permission filter]
    subgraph SIGN["E-SIGNATURE"]
        T[Document routed for signature] --> U[Signatory authenticated]
        U --> V[Signature applied with timestamp]
        V --> W[Tamper-evident seal]
        W --> X[Signed copy locked]
    end
    D --> SIGN
    subgraph LIFECYCLE["RETENTION"]
        M --> Y{Retention period elapsed?}
        Y -->|no| Z[Active or archived]
        Y -->|yes| AA{Disposition}
        AA --> AB[Permanent — archival]
        AA --> AC[Review before destruction]
        AC --> AD[Approved destruction with certificate]
    end
    N --> AE[Every access logged for sensitive classes]
    style W fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AE fill:#E8F5EC,stroke:#1E8449
```

---

## MOD-04-07 — Helpdesk & ICT Service Management

```mermaid
flowchart TB
    subgraph INTAKE["INTAKE CHANNELS"]
        A[Portal self-service]
        B[Email to service desk]
        C[Phone logged by agent]
        D[Walk-in]
        E[Automated from monitoring]
    end
    INTAKE --> F[Ticket created]
    F --> G[Categorisation and priority]
    G --> H{Impact x Urgency}
    H --> I[P1 — critical]
    H --> J[P2 — high]
    H --> K[P3 — normal]
    H --> L[P4 — low]
    I --> M["SLA clock starts<br/>response and resolution targets"]
    J --> M
    K --> M
    L --> M
    F --> N{Known error?}
    N -->|yes| O[Knowledge base solution applied]
    N -->|no| P[Assignment to team or agent]
    O --> Q[Resolved]
    P --> R[Investigation and diagnosis]
    R --> S{Resolvable at this tier?}
    S -->|no| T[Escalation to next tier]
    S -->|yes| U[Fix applied]
    T --> R
    U --> Q
    Q --> V[User confirmation]
    V --> W{Confirmed?}
    W -->|no| X[Reopened]
    W -->|yes| Y[Closed with resolution code]
    Y --> Z[Satisfaction survey]
    M --> AA{SLA breach imminent?}
    AA -->|yes| AB[Escalation alert to manager]
    subgraph ITIL["RELATED PROCESSES"]
        AC[Incident] --> AD{Recurring pattern?}
        AD -->|yes| AE[Problem record]
        AE --> AF[Root cause analysis]
        AF --> AG[Change request]
        AG --> AH[Change advisory board]
        AH --> AI{Approved?}
        AI -->|yes| AJ[Scheduled change window]
        AJ --> AK[Implementation and verification]
        AK --> AL[Post-implementation review]
        AI -->|no| AM[Rejected or deferred]
        AN[Service catalogue] --> AO[Service request fulfilment]
        AP[Configuration items register]
    end
    style M fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AB fill:#FEE2E2,stroke:#B91C1C
```

---

## MOD-04-08 — Facilities, Estates & Fleet Management

```mermaid
flowchart TB
    subgraph ESTATE["ESTATE REGISTER"]
        A[Land and title records] --> B[Buildings]
        B --> C[Floors and spaces]
        C --> D{Space use}
        D --> E[Teaching — feeds timetable MOD-01-08]
        D --> F[Office]
        D --> G[Residential — feeds MOD-02-08]
        D --> H[Laboratory and specialised]
        B --> I[Building services and plant]
    end
    subgraph MAINT["MAINTENANCE"]
        J[Work request] --> K{Source}
        K --> L[Hostel maintenance MOD-02-08]
        K --> M[Staff or department request]
        K --> N[Planned preventive schedule]
        K --> O[Inspection finding]
        J --> P[Triage and priority]
        P --> Q{Safety critical?}
        Q -->|yes| R[Emergency response]
        Q -->|no| S[Scheduled work order]
        R --> T[Assignment to team or contractor]
        S --> T
        T --> U{In-house or outsourced?}
        U -->|outsourced| V[Procurement MOD-03-11]
        U -->|in-house| W[Materials from stores]
        T --> X[Work executed]
        X --> Y[Completion and sign-off by requester]
        Y --> Z[Cost charged to cost centre]
        N --> AA[Asset maintenance history]
    end
    subgraph FLEET["FLEET"]
        AB[Vehicle register] --> AC[Licensing and insurance expiry]
        AC --> AD{Expiring?}
        AD -->|yes| AE[Renewal alert]
        AB --> AF[Driver assignment]
        AG[Trip request] --> AH[Approval by authority]
        AH --> AI[Vehicle and driver allocated]
        AI --> AJ{Vehicle available?}
        AJ -->|no| AK[Alternative or hire]
        AJ -->|yes| AL[Trip authorised]
        AL --> AM[Fuel issue and log]
        AL --> AN[Odometer and trip record]
        AN --> AO[Cost per kilometre analysis]
        AB --> AP[Service schedule by mileage]
        AP --> AQ[Service work order]
        AB --> AR[Accident and incident record]
        AR --> AS[Insurance claim]
    end
    subgraph UTIL["UTILITIES"]
        AT[Meter readings] --> AU[Consumption by building]
        AU --> AV[Utility cost allocation]
        AU --> AW{Abnormal consumption?}
        AW -->|yes| AX[Leak or fault investigation]
    end
    style E fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

---

## MOD-04-09 — Campus Security & Incident Management

```mermaid
flowchart TB
    subgraph ACCESS["ACCESS CONTROL"]
        A[Identity from MOD-01-01] --> B[Access credential]
        B --> C{Credential type}
        C --> D[Student ID card]
        C --> E[Staff card]
        C --> F[Visitor pass]
        C --> G[Contractor pass with expiry]
        B --> H[Zone permissions]
        H --> I[Gate and door events]
        I --> J[(Access log)]
        K{Status change} -->|suspended or exited| L[Credential revoked immediately]
    end
    subgraph VISITOR["VISITOR MANAGEMENT"]
        M[Visitor arrives] --> N[Host confirmation]
        N --> O[Identity recorded]
        O --> P[Pass issued with validity]
        P --> Q[Check-out]
        Q --> R{Overdue?}
        R -->|yes| S[Alert to security]
    end
    subgraph INCIDENT["INCIDENT MANAGEMENT"]
        T[Incident reported] --> U{Channel}
        U --> V[Guard patrol]
        U --> W[Student or staff report]
        U --> X[Panic or emergency button]
        U --> Y[CCTV observation]
        T --> Z[Severity classification]
        Z --> AA{Severity}
        AA --> AB["Critical — immediate response<br/>and management notification"]
        AA --> AC[Major — response team]
        AA --> AD[Minor — logged and handled]
        AB --> AE[Emergency services engaged if needed]
        AB --> AF[Incident command and log]
        AC --> AF
        AD --> AF
        AF --> AG[Investigation]
        AG --> AH[Statements and evidence]
        AH --> AI{Outcome}
        AI --> AJ[Disciplinary referral MOD-02-07]
        AI --> AK[Police referral]
        AI --> AL[Insurance claim]
        AI --> AM[No further action]
        AG --> AN[Preventive recommendations]
        AN --> AO[Action tracking]
    end
    subgraph OPS["SECURITY OPERATIONS"]
        AP[Guard duty roster] --> AQ[Patrol schedule]
        AQ --> AR[Patrol checkpoint scans]
        AR --> AS{Missed checkpoints?}
        AS -->|yes| AT[Supervisor alert]
        AU[Lost and found register]
        AV[Key and asset control]
    end
    style L fill:#1E8449,stroke:#1E8449,color:#FFFFFF
    style AB fill:#FEE2E2,stroke:#B91C1C
```

---

## MOD-04-10 — University Health & Clinic Services

```mermaid
flowchart TB
    A[Patient presents] --> B{Identity}
    B --> C[Student — from MOD-01-06]
    B --> D[Staff — from MOD-03-01]
    B --> E[Dependant]
    C --> F{Eligibility}
    D --> F
    E --> F
    F --> G["Cover check —<br/>medical levy paid or scheme member"]
    G --> H[Registration and triage]
    H --> I{Urgency}
    I --> J[Emergency — immediate]
    I --> K[Urgent]
    I --> L[Routine — queue]
    J --> M[Consultation]
    K --> M
    L --> M
    M --> N[(Clinical record<br/>strictly access-controlled)]
    M --> O{Orders}
    O --> P[Laboratory request]
    O --> Q[Pharmacy prescription]
    O --> R[Referral to external facility]
    O --> S[Admission to sick bay]
    P --> T[Results to record]
    Q --> U[Dispensing and stock deduction]
    U --> V[Pharmacy inventory]
    V --> W{Below reorder?}
    W -->|yes| X[Requisition MOD-03-11]
    M --> Y{Documentation needed?}
    Y --> Z["Sick leave certificate<br/>to MOD-03-03"]
    Y --> AA["Special exam medical grounds<br/>to MOD-01-10 — decision only, no diagnosis"]
    M --> AB[Billing]
    AB --> AC{Payer}
    AC --> AD[Covered by levy]
    AC --> AE[Insurance scheme claim]
    AC --> AF[Charge to student ledger MOD-01-09]
    N --> AG["Read access logged<br/>every single view"]
    subgraph PRIVACY["PRIVACY BOUNDARY"]
        AH["No clinical detail crosses<br/>into academic or HR modules"]
        AI["Only the decision travels:<br/>fit or unfit, dates, certifying officer"]
    end
    N --> PRIVACY
    style N fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style PRIVACY fill:#FEE2E2,stroke:#B91C1C
```

**The privacy boundary is the design.** A Head of Department approving sick leave sees "certified unfit,
3 days, by Dr X" — never the diagnosis. Health data lives in its own schema with its own role family and
read-auditing on every view.

---

## MOD-04-11 — Alumni Relations & Tracer Studies

```mermaid
flowchart TB
    A["Graduation MOD-01-12"] --> B[Alumni record auto-created]
    B --> C["Same person row —<br/>academic history intact"]
    C --> D[Alumni profile]
    D --> E[Contact details maintenance]
    E --> F{Contactable?}
    F -->|no| G[Trace via cohort networks]
    D --> H[Employment and career updates]
    subgraph TRACER["TRACER STUDIES"]
        I[Study designed per cohort] --> J{Interval}
        J --> K[6 months after graduation]
        J --> L[2 years]
        J --> M[5 years]
        K --> N[Survey distributed]
        L --> N
        M --> N
        N --> O[Responses collected]
        O --> P{Response rate}
        P -->|low| Q[Follow-up campaign]
        P -->|adequate| R[Analysis]
        R --> S[Employment rate by programme]
        R --> T[Time to first employment]
        R --> U[Relevance of training]
        R --> V[Further studies uptake]
        R --> W[Employer satisfaction survey]
        S --> X[Accreditation evidence MOD-04-04]
        U --> Y[Curriculum review input MOD-01-03]
    end
    D --> TRACER
    subgraph ENGAGE["ENGAGEMENT"]
        Z[Alumni chapters and cohorts]
        AA[Events and reunions]
        AB[Mentorship — alumni to students]
        AC[Guest lectures and industry links]
        AD[Attachment placements MOD-02-04]
        AE[Giving and endowment]
        AE --> AF[Donation recorded]
        AF --> AG[Receipt and acknowledgement]
        AG --> AH[Fund allocation MOD-03-09]
        AH --> AI[Scholarship funding MOD-02-10]
    end
    D --> ENGAGE
    D --> AJ[Alumni services]
    AJ --> AK[Transcript requests MOD-02-09]
    AJ --> AL[Verification requests MOD-05-07]
    AJ --> AM[Library alumni access MOD-02-06]
    style C fill:#0A3E50,stroke:#0A3E50,color:#FFFFFF
    style AI fill:#1E8449,stroke:#1E8449,color:#FFFFFF
```

The person spine closes the loop: an alumnus who donates to a scholarship that funds a student who later
graduates and donates is **one continuous chain of records** in the same database, not four disconnected
systems.
