# 🎓 MEMA ERP — Enterprise Higher Education ERP Platform

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-15%2B-4169E1?style=for-the-badge&logo=postgresql)](https://postgresql.org)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v4-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![Build & Tests](https://img.shields.io/badge/Tests-217%20Passed%20(2045%20assertions)-success?style=for-the-badge&logo=checkmarx)](tests)
[![Platform License](https://img.shields.io/badge/License-Proprietary%20Enterprise-0A3E50?style=for-the-badge)](LICENSE)

> **MEMA ERP** is a full-stack, enterprise-grade Higher Education Management & Operations Platform designed specifically for multi-faculty universities, constituent colleges, and Open, Distance & e-Learning (ODeL) institutions. It powers the complete academic, administrative, human capital, financial, and digital governance lifecycle.

---

## 📑 Table of Contents

1. [System Overview & Key Features](#-system-overview--key-features)
2. [High-Level System Architecture](#-high-level-system-architecture)
3. [Network & Infrastructure Topology](#-network--infrastructure-topology)
4. [Lifecycle Data Flow: Applicant to Student](#-lifecycle-data-flow-applicant-to-student)
5. [Load Balancer & Traffic Routing Design](#-load-balancer--traffic-routing-design)
6. [Multi-Format Export Engine (Excel, CSV, PDF)](#-multi-format-export-engine)
7. [Installation & Setup Guide](#-installation--setup-guide)
8. [Environment Configuration & Security](#-environment-configuration--security)
9. [Default Access & User Credentials](#-default-access--user-credentials)
10. [Automated Testing Suite](#-automated-testing-suite)

---

## 🌟 System Overview & Key Features

* **Admissions & Intake Pipeline**: Public course catalogue (`/programmes/apply`), KCSE grade matcher, online applications, document verification, Senate approvals, instant QR-verified offer letters, and automatic student conversion.
* **Curriculum & Academic Registry**: Hierarchical management of Faculties, Departments, Degree Programmes, Syllabi, Course Units, and Grading Matrices.
* **Human Capital & SMHR**: Kenyan statutory salary engine complying with KRA Income Tax Act Cap 470 PAYE, NSSF (Tier 1 & 2), SHA (2.75%), Affordable Housing Levy (1.5%), P9A tax cards, and staff directory.
* **Postgraduate Research (SPGS)**: Thesis defense management, proposal reader allocation, viva voce panel scoring, and progress milestones.
* **Examinations & Graduation**: Mark entry, provisional transcripts, pass list validation, graduation clearance checklists, and alumni database.
* **Finance & Fee Reconciliation**: Real-time M-Pesa Daraja 2.0 integration, receipt generation, vote-head budgeting, and expenditure governance.
* **System-Wide Load Balancer**: Dynamic FIFO, LIFO, Weighted Round Robin (WRR), Least Connections, and Priority Fair Queuing algorithms with live node telemetry.
* **Platform Governance**: Recycle bin with soft-delete retention, maintenance lockdown, audit forensics, and multi-format reporting engine.

---

## 🏛 High-Level System Architecture

```mermaid
graph TD
    subgraph Clients ["1. Users & Entry Portals"]
        U1["Applicants<br/>(Admissions & Apply)"]
        U2["Students<br/>(Academic & Fees Portal)"]
        U3["Faculty & Staff<br/>(Lecturer & HR Desk)"]
        U4["Administrators<br/>(Executive OpsCenter)"]
    end

    subgraph Ingress ["2. Gateway & Traffic Control"]
        WAF["HTTPS / TLS / Reverse Proxy"]
        LB["MEMA Intelligent Load Balancer<br/>(FIFO · LIFO · WRR · LeastConn · PFQ)"]
        SEC["Security & RBAC Middleware"]
        WAF --> LB --> SEC
    end

    subgraph Core ["3. Enterprise Modules (Laravel 12 Engine)"]
        M1["Admissions & Enrollment"]
        M2["Curriculum & Faculties"]
        M3["SMHR & Statutory Payroll"]
        M4["Finance & Fee Payments"]
        M5["Exams & Certification"]
        M6["Dynamic Documents & Reports"]
    end

    subgraph Data ["4. Persistence & External Services"]
        DB[("PostgreSQL 15+ Primary")]
        CACHE[("Redis Cache & Sessions")]
        VAULT[("Encrypted File Storage")]
        EXT["Safaricom M-Pesa · KRA iTax · KUCCPS"]
    end

    Clients --> WAF
    SEC --> Core
    Core --> DB
    Core --> CACHE
    Core --> VAULT
    Core --> EXT
```

---

## 🌐 Network & Infrastructure Topology

```mermaid
graph LR
    subgraph Public ["Public Network"]
        Users["Applicants · Students<br/>Lecturers · Administrators"]
    end

    subgraph DMZ ["Edge & Ingress Layer"]
        Proxy["Nginx / Reverse Proxy<br/>(TLS 1.3 / Port :443)"]
    end

    subgraph Cluster ["Application Cluster (:8000)"]
        App1["App Node 1 (Core ERP)"]
        App2["App Node 2 (Academic)"]
        App3["App Node 3 (Admissions)"]
    end

    subgraph DataTier ["Data & Storage Tier"]
        Postgres[("PostgreSQL 15+ Database")]
        RedisStore[("Redis Session & Cache")]
        S3Storage[("Document Vault Storage")]
    end

    subgraph ThirdParty ["External Gateways"]
        MPesaApi["M-Pesa Daraja 2.0"]
        KRAApi["KRA Statutory Portal"]
    end

    Users --> Proxy
    Proxy --> App1
    Proxy --> App2
    Proxy --> App3
    App1 --> Postgres
    App2 --> Postgres
    App3 --> Postgres
    App1 --> RedisStore
    App1 --> S3Storage
    App1 --> MPesaApi
    App1 --> KRAApi
```

---

## 🔄 Lifecycle Data Flow: Applicant to Student

```mermaid
sequenceDiagram
    autonumber
    actor App as Applicant
    participant Portal as Admissions Portal (/programmes/apply)
    participant Mpesa as M-Pesa / Finance Engine
    participant Senate as Admissions & Senate Desk
    participant Student as Student Registry & LMS

    App->>Portal: 1. Select Programme & Submit Online Application
    App->>Portal: 2. Upload Identification & Academic Transcripts
    App->>Mpesa: 3. Pay Application Fee via M-Pesa STK / Paybill
    Mpesa-->>Portal: 4. Fee Cleared & Official Receipt Issued
    Portal->>Senate: 5. Routed to Verification & Scoring Queue
    Senate->>Senate: 6. Dean & Senate Board Sign-off
    Senate->>App: 7. Dispatch Official QR-Verified Offer Letter (PDF)
    App->>Portal: 8. Accept Admission Offer & Matriculation Oath
    Portal->>Student: 9. Issue Student Registration No. & Course Enrolment
    Student-->>App: 10. Student LMS & Student Profile Active
```

---

## ⚡ Load Balancer & Traffic Routing Design

The system includes a fully functional load balancing core located at [`app/Services/LoadBalancerService.php`](file:///Users/wabwire/Dev/memaerp/laravel_erp/app/Services/LoadBalancerService.php) and management interface at [`/admin-setups/load-balancer`](http://127.0.0.1:8000/admin-setups/load-balancer).

```mermaid
graph TD
    REQ["Incoming HTTP Request"] --> LB_ENGINE{"Load Balancer Service"}

    LB_ENGINE -->|Strategy 1| FIFO["First-In, First-Out (FIFO)"]
    LB_ENGINE -->|Strategy 2| LIFO["Last-In, First-Out (LIFO)"]
    LB_ENGINE -->|Strategy 3| WRR["Weighted Round Robin (WRR)"]
    LB_ENGINE -->|Strategy 4| LC["Least Active Connections"]
    LB_ENGINE -->|Strategy 5| PFQ["Priority Fair Queue (VIP & Admin)"]

    FIFO --> NODES["Healthy Server Node Fleet"]
    LIFO --> NODES
    WRR --> NODES
    LC --> NODES
    PFQ --> NODES

    NODES --> RESP["Attach Telemetry Headers (X-MEMA-LB-*) & Respond"]
```

---

## 📊 Multi-Format Export Engine

MEMA ERP incorporates a high-performance export architecture allowing instant extraction of all live database records into **Excel**, **CSV**, and **Printable PDF**:

1. **Excel Export (`.xlsx`)**:
   - Genuine Microsoft OpenXML Spreadsheet document (`.xlsx`), generated with cell formatting, auto-fit columns, frozen header row, and brand-styled header banners.
2. **CSV Export (`.csv`)**:
   - RFC 4180 standard comma-delimited export with UTF-8 BOM (`\xEF\xBB\xBF`) for instant native rendering in Excel without character encoding issues.
3. **Printable PDF Export (`.pdf`)**:
   - Formatted publication report utilizing the system typography **Quicksand** throughout.
   - Styled with the official institutional color palette:
     - **Primary Dark Teal**: `#0A3E50` (Header row background with bold white text `#FFFFFF`)
     - **Secondary Teal**: `#007A8C`
     - **Accent Orange**: `#E67E22` (Callout tags & KPI summaries)
     - **Borders & Dividers**: `#CBD5E1` & alternating row backgrounds `#F8FAFC`
   - Complete with institutional letterhead, report metadata, and digital audit seal.

---

## 🚀 Installation & Setup Guide

### 1. Prerequisites

Ensure your development or production server has the following installed:
* **PHP 8.3 or higher** (with extensions: `pdo_pgsql`, `pgsql`, `zip`, `mbstring`, `xml`, `curl`, `bcmath`, `fileinfo`)
* **PostgreSQL 15+**
* **Composer 2.7+**
* **Node.js 20+ & npm**
* **Git**

### 2. Clone the Repository

```bash
git clone https://github.com/Dev-Ouma/memaerp.git
cd memaerp/laravel_erp
```

### 3. Configure Environment File

Create your local `.env` configuration from `.env.example`:

```bash
cp .env.example .env
```

Open `.env` and set your PostgreSQL database credentials:

```ini
APP_NAME="MEMA ERP"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mema_erp
DB_USERNAME=postgres
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### 4. Install Dependencies

```bash
# Install PHP Composer dependencies
composer install

# Install Node modules
npm install
```

### 5. Generate Application Key & Symlink Storage

```bash
php artisan key:generate
php artisan storage:link
```

### 6. Run Database Migrations & Seeders

```bash
php artisan migrate --seed
```

### 7. Compile Frontend Assets

```bash
# Production bundle build
npm run build

# Or development live reload server
npm run dev
```

### 8. Start Local Development Server

```bash
php artisan serve --port=8000
```

Access the system at **`http://127.0.0.1:8000`** in your browser.

---

## 🔐 Default Access & User Credentials

When seeded with default test fixtures, the system provides pre-configured role accounts:

| Role Account | Email Address | Default Password | Workspace Permissions |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@mema.ac.ke` | `password` | Complete university-wide administrative access |
| **Registrar / Dean** | `dean.science@mema.ac.ke` | `password` | Faculty, curriculum & academic admissions |
| **Academic Staff / Lecturer**| `lecturer@mema.ac.ke` | `password` | Course units, LMS, exams & payslip portal |
| **Student** | `student@mema.ac.ke` | `password` | Coursework, registration & exam results |
| **Applicant** | `applicant@mema.ac.ke` | `password` | Admission application & letter download |

---

## 📁 Repository Structure & Directory Map

```text
memaerp/
├── laravel_erp/                       # Primary Laravel 12 ERP Application
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/           # Module Controllers (Admissions, SMHR, Curriculum, etc.)
│   │   │   └── Middleware/            # Security, Maintenance Lockdown, Load Balancer Middleware
│   │   ├── Models/                    # Eloquent Database Models (UUID & BigInt compatible)
│   │   ├── Modules/Admission/         # Modular Admission Pipeline & Workspaces
│   │   └── Services/                  # Business Logic (DataExportService, LoadBalancer, RecycleBin)
│   ├── config/                        # Framework & Module Configuration
│   ├── database/
│   │   ├── migrations/                # Schema DDL (Institutions, Students, Staff, PGR, LoadBalancer)
│   │   └── seeders/                   # Initial Database Seeders & Demo Fixtures
│   ├── public/                        # Compiled Assets, CSS, Fonts, and Favicons
│   ├── resources/
│   │   ├── css/ & js/                 # Design System & Interactive Processing Scripts
│   │   └── views/                     # Blade Templates (Dashboards, SMHR, Curriculum, Reports)
│   ├── routes/                        # Web & Console Routes (routes/web.php)
│   └── tests/                         # Feature & Unit Automated Test Suite (126 Tests)
├── .gitignore                         # Strict Root Privacy & Secrets Protection Rules
└── README.md                          # Platform Architectural & Setup Documentation
```

---

## 🔒 Environment Security & Data Privacy

* **Zero Hardcoded Secrets**: All keys, passwords, API tokens, and database credentials are fully parameterized in `.env`.
* **Repository Sanitation**: The `.gitignore` prevents tracking of `.env`, `auth.json`, encryption keys (`storage/*.key`), database backups (`*.dump`, `*.sql.gz`), session data, and framework caches.
* **Auditability**: All state-mutating actions, fee waivers, admissions approvals, and staff updates are logged with audit timestamps and user IDs.
* **Recycle Bin Protection**: Critical records (schools, programmes, applications) utilize soft-deletes with configurable retention governance and administrative restoration.

---

## 🧪 Automated Testing Suite

The ERP is backed by an automated test suite verifying all critical workflows:

```bash
# Run the entire test suite
php artisan test

# Run specific feature tests
php artisan test tests/Feature/DashboardMetricsAndExportTest.php
php artisan test tests/Feature/SmhrModuleTest.php
php artisan test tests/Feature/CurriculumSchoolCrudTest.php
php artisan test tests/Feature/LoadBalancerTest.php
```

---

## 📄 License & Attribution

Copyright © 2026 MEMA University College. All rights reserved.  
Proprietary software for authorized institutional deployment.
