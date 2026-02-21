# Survvy — Software Requirements Specification

## 1. Overview

**Survvy** is a multi-tenant SaaS web application designed for educational institutions to create, manage, and distribute surveys — primarily instructor evaluation and classroom experience surveys. Surveys are delivered to students and faculty through their existing Learning Management System (LMS) via LTI 1.3, with first-class support for Moodle.

**Domain:** servvy.co
**Stack:** Laravel 12 · Inertia.js · Vue 3 · PostgreSQL · Redis · Laravel Horizon

---

## 2. Problem Statement

Educational institutions need a structured way to collect feedback about instructors and classroom experiences. Existing survey tools (Google Forms, SurveyMonkey) are generic and disconnected from the LMS ecosystem. Faculty and students are forced to leave Moodle to fill out surveys, leading to low response rates and fragmented data. Survvy solves this by embedding surveys directly inside Moodle through LTI, while giving institution admins a full-featured dashboard to build, schedule, and analyze surveys.

---

## 3. Target Users

| Role | Description |
|---|---|
| **Institution Admin** | Creates the institution account, manages users, configures LTI, builds surveys |
| **Instructor** | Can view surveys assigned to them and see their own evaluation results |
| **Student / Respondent** | Accesses and submits surveys through Moodle (no Survvy account required) |
| **Super Admin** | Survvy platform owner — manages institution accounts and billing (future) |

---

## 4. Core Features

### 4.1 Institution Accounts (Multi-Tenancy)
- An educational institution registers and creates an account on Survvy
- Each institution is a fully isolated tenant with its own data, users, surveys, and LTI credentials
- Institution admin manages their own users and roles
- Tenant isolation is enforced at the database level

### 4.2 Survey Builder
- Admins can create surveys from scratch
- Two primary survey types:
  - **Instructor Evaluation Survey** — evaluates a specific instructor's teaching performance
  - **Classroom Experience Survey** — evaluates the overall course or classroom environment
- Survey structure is flexible: multiple question types supported (rating scale, multiple choice, open text, Likert scale)
- Survey data is stored as JSONB in PostgreSQL for maximum flexibility without rigid schema migrations
- Surveys can be duplicated, versioned, or used as templates

### 4.3 Survey Activation & Scheduling
- Surveys can be activated for a defined time window (start date / end date)
- Automatic activation and deactivation via queue jobs (Laravel Horizon + Redis)
- Admins can manually open or close a survey at any time
- Survey status: Draft → Scheduled → Active → Closed

### 4.4 Survey Assignment
- Surveys are assigned to specific courses, instructors, or groups
- An assigned survey generates a unique LTI-compatible URL
- Multiple surveys can be active simultaneously for different assignments

### 4.5 LTI 1.3 Integration
- Each institution can generate LTI 1.3 credentials from their dashboard
- The system produces a ready-to-use credential package containing:
  - `client_id`
  - Platform OIDC Authorization URL
  - JWK Set (public key) endpoint URL
  - Tool Launch URL (`servvy.co/lti/launch`)
  - Deep Link URL (for Moodle activity setup)
- Institution admins paste these into Moodle's External Tool configuration — no manual back-and-forth with Survvy support
- Each tenant stores its own JWK keypair in the database
- LTI launch flow: Moodle initiates OIDC login → Survvy validates JWT → resolves tenant → maps LMS user to respondent → loads assigned survey inside Moodle iframe
- Respondents (students/faculty) never need a Survvy account — they are identified by LTI claims

### 4.6 Built-in Reporting & Analytics

The dashboard includes a full reporting system so institution admins and instructors can analyze survey results without ever needing to export a file. Exports (Section 5.6) are an addition to this, not a replacement.

**Report views available:**
- **Overview Report** — high-level summary for a survey: response rate, completion rate, average scores per section
- **Question-level Report** — per-question breakdown with response distribution (e.g. 40% Strongly Agree, 30% Agree...)
- **Instructor Report** — aggregated scores across all evaluations for a specific instructor, across multiple surveys or time periods
- **Course Report** — classroom experience results grouped by course or section
- **Trend Report** — how scores for an instructor or course evolve across semesters or time windows

**Filtering & segmentation:**
- Filter by survey, date range, course, instructor, department, or academic period
- Compare two time periods side by side (e.g. Fall 2024 vs Spring 2025)
- Filter by respondent group (if captured via LTI claims — e.g. student role vs faculty)

**Visualizations (Vue + charting library — Apache ECharts or Chart.js):**
- Bar charts for rating/Likert question distributions
- Line charts for trend reports over time
- Donut/pie charts for multiple choice breakdowns
- Score cards (large number + trend indicator) for key metrics
- Response rate progress bar per active survey

**Access control:**
- Institution admin sees all reports across all surveys and instructors
- Instructor sees only their own evaluation reports (admin can toggle this off)
- No respondent-level data is shown — all reports are aggregated to protect anonymity

**Export from within reports:**
- Any report view can be exported to Excel or PDF directly from the UI
- Export includes the same filters currently applied on screen
- Generated files are stored on S3 and served via signed URL (see Section 5.6)

---

## 5. Technical Architecture

### 5.1 Repository Structure
Single GitHub repository. Frontend (Vue 3) lives inside Laravel under `resources/js/`. No separate frontend repo.

```
survvy/
├── app/
│   ├── Http/Controllers/
│   │   ├── Web/                  ← Inertia controllers (dashboard, surveys)
│   │   └── Api/                  ← JSON API controllers (LTI, integrations)
│   ├── Models/
│   └── Services/
│       └── Lti/                  ← LTI 1.3 logic isolated here
├── resources/
│   └── js/
│       ├── Pages/                ← Vue 3 page components
│       └── Components/           ← Shared Vue components
├── routes/
│   ├── web.php                   ← Inertia / dashboard routes
│   └── api.php                   ← LTI launch + JSON API routes
├── docker-compose.yml            ← Local dev: Postgres + Redis only
└── SRS.md
```

### 5.2 Key Dependencies

**Backend (PHP / Laravel)**
| Package | Purpose |
|---|---|
| `inertiajs/inertia-laravel` | Server-side Inertia adapter |
| `imsglobal/lti-1p3-tool` | LTI 1.3 core implementation |
| `spatie/laravel-multitenancy` | Tenant isolation |
| `spatie/laravel-permission` | Role management per tenant |
| `laravel/horizon` | Queue monitoring and management |
| `maatwebsite/excel` | Survey result exports |

**Frontend (JS / Vue)**
| Package | Purpose |
|---|---|
| `@inertiajs/vue3` | Client-side Inertia adapter |
| `vue` | UI framework |
| `vite` | Asset bundler (comes with Laravel) |

### 5.3 Database
- **PostgreSQL** (Docker locally, managed DB on AWS in production)
- Survey definitions stored as JSONB columns — flexible structure, no per-question-type migrations
- Multi-tenant schema isolation via spatie/laravel-multitenancy

### 5.4 Queues & Scheduling
- **Redis** for queue backend
- **Laravel Horizon** for queue monitoring
- Jobs: survey auto-activation, auto-deactivation, result aggregation, email notifications (future)

### 5.5 Authentication
- Institution admins and internal users authenticate via Laravel Sanctum (session-based)
- Survey respondents are authenticated exclusively through LTI 1.3 OIDC — no Survvy account required

### 5.6 File Storage (Amazon S3)

When a user exports survey results, Laravel generates an Excel or CSV file using `maatwebsite/excel`. That file needs to be stored somewhere. Storing it on the server's local disk is fragile — files can be lost on restart, and if the app ever runs on more than one server they won't share the same disk.

Instead, generated files are uploaded to **Amazon S3** (already in the same AWS ecosystem as Lightsail). Laravel then returns a **pre-signed URL** — a temporary, expiring download link — so the user can download the file directly from S3 without it passing through the app server again.

**How it works in practice:**
1. Admin clicks "Export Results" on the dashboard
2. Laravel dispatches a background job (Horizon queue)
3. The job generates the file and uploads it to S3 under the tenant's folder: `exports/{tenant_id}/{filename}.xlsx`
4. Laravel generates a signed URL valid for 15 minutes and returns it to the frontend
5. The user's browser downloads directly from S3

**Why a background job and not instant download?**
Large surveys with hundreds of respondents can take several seconds to generate. A queue job keeps the UI responsive and lets the user continue working while the export is prepared.

**Local development:**
S3 is not needed locally. Laravel's filesystem abstraction allows swapping S3 for the local disk driver in `.env`. No code changes required — just a config difference between environments.

```
# .env (local)
FILESYSTEM_DISK=local

# .env (production)
FILESYSTEM_DISK=s3
AWS_BUCKET=survvy-exports
AWS_DEFAULT_REGION=us-east-1
```

**S3 bucket structure:**
```
survvy-exports/
└── exports/
    └── {tenant_id}/
        └── survey-results-{survey_id}-{date}.xlsx
```

Files older than 30 days are automatically deleted via an S3 lifecycle policy — no manual cleanup needed.

---

## 6. Local Development Environment

**What runs in Docker:**
- PostgreSQL 16
- Redis 7

**What runs natively:**
- Laravel (`php artisan serve`)
- Vite / Vue (`npm run dev`)
- Horizon (`php artisan horizon`)

**Commands to start local dev:**
```bash
docker-compose up -d      # start Postgres + Redis
php artisan serve         # Laravel on localhost:8000
npm run dev               # Vite hot reload
php artisan horizon       # queue worker
```

---

## 7. Deployment (Production — AWS Lightsail Bitnami LAMP)

- **Server:** AWS Lightsail, Bitnami LAMP (PHP 8.x, Apache)
- **Domain:** servvy.co
- **Deploy flow:** `git pull` from GitHub → `composer install` → `npm run build` → `php artisan migrate`
- A `deploy.sh` script in the repo root wraps all deploy steps
- Future: GitHub Actions SSH deploy on push to `main`

**Build for production:**
```bash
npm run build   # compiles Vue into public/build/ — no Vite process on server
```

---

## 8. LTI Registration Flow (Institution Perspective)

1. Institution admin logs into Survvy dashboard
2. Goes to **Settings → LTI Integration**
3. Clicks **Generate LTI 1.3 Credentials**
4. Survvy generates and displays:
   - Tool URL: `https://servvy.co/lti/launch`
   - Login Initiation URL: `https://servvy.co/lti/login`
   - JWKS URL: `https://servvy.co/lti/jwks/{tenant}`
   - Client ID (unique per institution)
5. Admin copies these into Moodle: **Site Admin → Plugins → External Tool → Manage Tools → Add**
6. Moodle registers the tool — connection is live
7. Instructor adds the Survvy tool to a course activity and selects a survey via Deep Linking

---

## 9. Survey Lifecycle

```
[Admin creates survey]
        ↓
    [Draft]
        ↓ schedule or manually activate
   [Scheduled]
        ↓ start date reached (queue job)
    [Active]  ←── students access via Moodle LTI
        ↓ end date reached or manually closed
    [Closed]
        ↓
  [Results available]
```

---

## 10. Out of Scope (v1)

- Mobile app
- Public API for third-party integrations
- LMS platforms other than Moodle (architecture supports it, not prioritized)
- Billing / subscription management (multi-tenant accounts created manually for now)
- AI-generated survey questions
- Real-time response streaming

---

## 11. Open Questions

- [ ] Will institutions pay per seat, per survey, or flat monthly fee? (affects data model)
- [ ] Should instructors be able to create their own surveys or only admins?
- [ ] Do we need anonymity guarantees for respondents? (affects LTI claim storage)
- [ ] What languages/locales need to be supported at launch?
- [ ] Should the survey UI inside Moodle match the institution's branding?
