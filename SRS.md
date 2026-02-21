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

### 4.6 Results & Reporting
- Institution admins can view aggregated survey results
- Instructors can view their own evaluation results (configurable by admin)
- Results exportable to Excel/CSV (maatwebsite/excel)
- Charts and summaries on the dashboard (Vue + charting library TBD)

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
