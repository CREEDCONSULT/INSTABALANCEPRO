# InstaBAlancePRO — Technical Architecture Document

**Version:** 1.0
**Date:** February 2026
**Audience:** Systems Engineers, Technical Reviewers

---

## 1. Executive Summary

InstaBAlancePRO is a multi-tenant SaaS web application built on a custom PHP 8.2 MVC framework. It integrates with the Instagram Graph API to collect follower/following data, processes that data through a multi-factor scoring algorithm, and presents actionable workflow tools (ranked lists, Kanban board, activity calendar) to help users manage their Instagram following intelligently. Monetisation is handled via Stripe subscription billing with three service tiers.

---

## 2. Technology Stack

### 2.1 Backend

| Component         | Technology                          | Version   |
|-------------------|-------------------------------------|-----------|
| Language          | PHP                                 | 8.2       |
| Web Server        | Apache (with mod_rewrite)           | 2.4       |
| Database          | MySQL / MariaDB                     | 8.0 / 10.4|
| HTTP Routing      | nikic/FastRoute                     | 1.3       |
| HTTP Client       | GuzzleHTTP                          | 7.x       |
| Payment Gateway   | Stripe PHP SDK                      | 13.x      |
| Email             | PHPMailer                           | 6.8       |
| 2FA               | pragmarx/google2fa (TOTP)           | 8.0       |
| Environment       | vlucas/phpdotenv                    | 5.5       |
| Dependency Mgmt   | Composer                            | 2.x       |

### 2.2 Frontend

| Component         | Technology                          | Notes                        |
|-------------------|-------------------------------------|------------------------------|
| CSS Framework     | Bootstrap                           | 5.3, CDN-loaded              |
| Interactivity     | htmx                                | AJAX without writing JS      |
| UI Behaviour      | Alpine.js + Vanilla JS              | Drag-drop, modals, calendar  |
| Icons             | Bootstrap Icons / Font Awesome      | 6.x                          |
| Fonts             | Google Fonts (Syne, DM Sans, JetBrains Mono) | CDN-loaded          |

### 2.3 Security Libraries

| Concern                   | Implementation                            |
|---------------------------|-------------------------------------------|
| Password hashing          | Argon2ID (PHP `password_hash`)            |
| Token encryption at rest  | AES-256-CBC via `EncryptionService`       |
| CSRF protection           | Synchroniser token pattern (`CsrfMiddleware`) |
| SQL injection prevention  | PDO prepared statements throughout        |
| 2FA                       | RFC 6238 TOTP (Google Authenticator compatible) |
| Session security          | httpOnly, SameSite=Lax, configurable Secure flag |

### 2.4 Infrastructure

| Component        | Technology                          |
|------------------|-------------------------------------|
| Containerisation | Docker + Docker Compose (3 services) |
| Local Dev        | XAMPP (PHP + MariaDB) + PHP built-in server |
| Version Control  | Git / GitHub                        |
| Deployment       | 000webhost / any shared Apache host |

---

## 3. System Architecture

### 3.1 Architectural Pattern

The application follows a **custom MVC (Model-View-Controller)** pattern with a **Service Layer** separating business logic from HTTP concerns. There is no third-party framework — all framework code (Router, Controller base, Database wrapper, Middleware pipeline) is written in-house.

```
┌─────────────────────────────────────────────────────┐
│                   HTTP Request                      │
└─────────────────────┬───────────────────────────────┘
                      │
              public/index.php
              (Front Controller)
                      │
              ┌───────▼────────┐
              │   Router.php   │  ← FastRoute dispatcher
              │  (src/Router)  │
              └───────┬────────┘
                      │  Route matched
              ┌───────▼────────┐
              │  Middleware    │  ← Auth, Admin, CSRF
              │   Pipeline     │
              └───────┬────────┘
                      │  Passed
              ┌───────▼────────┐
              │  Controller    │  ← Handles request/response
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │   Service(s)   │  ← Business logic
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │  Database.php  │  ← PDO wrapper
              │  (src/Database)│
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │  MySQL / Maria │
              └────────────────┘
```

### 3.2 Request Lifecycle

1. Apache (or PHP built-in server) receives all HTTP requests.
2. `.htaccess` rewrites every non-file request to `public/index.php`.
3. `index.php` bootstraps the application: loads `.env`, initialises config, starts session, instantiates `Database` and `Router`.
4. `Router` dispatches the request URI and HTTP method against registered FastRoute rules.
5. Before executing the controller action, the middleware pipeline runs (e.g., `AuthMiddleware` checks session, redirects to login if unauthenticated).
6. The matched `Controller` method runs, calls one or more `Service` classes.
7. Services interact with the database via `Database.php` (PDO wrapper).
8. Controller renders a PHP view template and writes the HTTP response.

---

## 4. Directory Structure

```
INSTABALANCEPRO/
│
├── public/                   # Web root — only publicly accessible directory
│   ├── index.php             # Front controller (single entry point)
│   ├── .htaccess             # URL rewriting, security headers, HTTPS enforcement
│   └── assets/
│       ├── css/style.css     # Custom styles on top of Bootstrap
│       └── js/app.js         # Vanilla JS (drag-drop, calendar, UI behaviour)
│
├── src/                      # Application source — never publicly accessible
│   ├── Router.php            # HTTP router (wraps FastRoute)
│   ├── Routes.php            # Route definitions (all 40+ routes in one file)
│   ├── Controller.php        # Base controller (view rendering, redirects, JSON)
│   ├── Database.php          # PDO wrapper (prepared statements, transactions)
│   ├── Model.php             # Base model
│   ├── Middleware.php        # Base middleware
│   │
│   ├── Controllers/          # HTTP layer — one class per feature domain
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── UnfollowController.php
│   │   ├── KanbanController.php
│   │   ├── ActivityController.php
│   │   ├── BillingController.php
│   │   ├── SettingsController.php
│   │   ├── WebhookController.php
│   │   ├── HomeController.php
│   │   ├── Admin/            # Admin-only controllers
│   │   └── API/              # JSON API controllers (htmx endpoints)
│   │
│   ├── Services/             # Business logic — framework-agnostic
│   │   ├── InstagramApiService.php    # OAuth + Graph API calls
│   │   ├── EngagementService.php      # Engagement metric calculation
│   │   ├── ScoringService.php         # 4-factor scoring algorithm
│   │   ├── SyncService.php            # Background data sync
│   │   ├── UnfollowQueueService.php   # Queue + rate limiting
│   │   └── EncryptionService.php      # AES-256 token encryption
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php         # Session authentication check
│   │   ├── AdminMiddleware.php        # Admin role gate
│   │   └── CsrfMiddleware.php         # CSRF token validation
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── ActivityLog.php
│   │   └── SyncJob.php
│   │
│   └── Views/                # PHP template files
│       ├── layouts/main.php  # Base layout (nav, Bootstrap scaffold)
│       ├── pages/            # 13 full-page templates
│       └── partials/         # Reusable components (nav, toast, user menu)
│
├── config/
│   └── app.php               # Reads .env, returns config array
│
├── database/
│   └── schema.sql            # Full DDL — 19 tables, indexes, foreign keys
│
├── cron/
│   └── sync.php              # CLI script for scheduled background sync
│
├── .env                      # Environment variables (gitignored)
├── .env.example              # Template for environment setup
├── composer.json             # PHP dependency definitions + autoload config
├── router.php                # PHP built-in dev server router script
├── Dockerfile                # Production container (PHP 8.2 + Apache)
└── docker-compose.yml        # Orchestration: web + MySQL + phpMyAdmin
```

---

## 5. Database Design

### 5.1 Schema Overview (19 Tables)

```
┌──────────────────────────────────────────────────────────┐
│  AUTHENTICATION & USERS                                  │
│  users · email_verifications · password_resets           │
├──────────────────────────────────────────────────────────┤
│  INSTAGRAM INTEGRATION                                   │
│  instagram_connections · following · followers           │
│  account_insights · creator_flags                        │
├──────────────────────────────────────────────────────────┤
│  WORKFLOW & QUEUING                                      │
│  unfollow_queue · kanban_cards · whitelist               │
├──────────────────────────────────────────────────────────┤
│  ANALYTICS & AUDIT                                       │
│  activity_log · admin_log · sync_jobs · monthly_usage    │
├──────────────────────────────────────────────────────────┤
│  BILLING                                                 │
│  subscriptions · invoices                                │
└──────────────────────────────────────────────────────────┘
```

### 5.2 Key Relationships

```
users (1) ──────── (1) instagram_connections
users (1) ──────── (N) following
users (1) ──────── (N) followers
following (1) ───── (1) account_insights
following (1) ───── (1) kanban_cards
users (1) ──────── (N) unfollow_queue
users (1) ──────── (N) activity_log
users (1) ──────── (1) subscriptions
```

### 5.3 Design Decisions

- **UTF8MB4** charset on all tables — full Unicode including emoji.
- **Soft deletes** on `users` (`deleted_at` column) — account recovery possible.
- **Composite indexes** on high-traffic query patterns (e.g., `user_id + status`, `user_id + created_at`).
- **ENUM types** for bounded value sets (subscription_tier, kanban status, sync status).
- **AES-256 encrypted** Instagram OAuth access tokens in `instagram_connections`.
- **Foreign key cascades** — deleting a user removes all their associated data.

---

## 6. Core Feature Workflows

### 6.1 Authentication Flow

```
Register ──► Email Verification ──► Login ──► (Optional) 2FA TOTP
                                        │
                                        ▼
                               Instagram OAuth Connect
                               (OAuth 2.0 Authorization Code Flow)
                                        │
                                        ▼
                               Access token encrypted (AES-256)
                               and stored in instagram_connections
```

### 6.2 Instagram Data Sync Flow

```
User triggers sync
        │
        ▼
InstagramApiService
  ├── GET /me/following  (paginated, 200 req/hr limit tracked)
  ├── GET /me/followers
  └── GET /me/media      (for engagement data)
        │
        ▼
SyncService
  ├── Upsert following/followers tables
  ├── Calculate deltas (new follows, unfollows)
  └── Create sync_jobs record (status: pending → running → complete)
        │
        ▼
EngagementService
  └── Calculate per-account engagement scores
      ├── Engagement rate (likes + comments / followers)
      ├── Activity gap (days since last post)
      └── Follower/following ratio
        │
        ▼
ScoringService
  └── Run 4-factor algorithm → write to account_insights
```

### 6.3 Scoring Algorithm

The scoring engine produces a **0–100 unfollow priority score** per followed account. Higher scores indicate accounts that are safer or more beneficial to unfollow.

```
Score = (Inactivity × 0.40)
      + (Engagement × 0.35)
      + (Ratio      × 0.15)
      + (AccountAge × 0.10)
      + AccountTypeModifier
```

| Factor          | Weight | Description                                |
|-----------------|--------|--------------------------------------------|
| Inactivity      | 40%    | Days since account last posted             |
| Engagement      | 35%    | Quality of interactions with your content  |
| Follower Ratio  | 15%    | Their followers ÷ following (growth signal)|
| Account Age     | 10%    | Maturity of the account                    |

**Account Type Modifiers (subtracted from score):**

| Type       | Modifier | Rationale                                  |
|------------|----------|--------------------------------------------|
| Verified   | −50      | Public figures — high social risk          |
| Creator    | −40      | Content professionals — intentional follow |
| Business   | −30      | Accountable entities — lower risk          |

**Score Categories:**

| Range  | Category        |
|--------|-----------------|
| 0–25   | Safe            |
| 26–50  | Caution         |
| 51–75  | High Priority   |
| 76–100 | Critical        |
| Special| Inactive 90d+   |
| Special| Low Engagement  |

### 6.4 Unfollow Queue & Rate Limiting

```
User selects accounts from ranked list
        │
        ▼
UnfollowQueueService
  ├── Adds accounts to unfollow_queue (status: pending)
  ├── Enforces quota: 100 unfollows per 24 hours (per user)
  └── Checks whitelist before queuing
        │
        ▼
Execute Queue
  ├── Calls Instagram API to unfollow (DELETE /me/following/{id})
  ├── Updates following.unfollowed_at
  ├── Logs to activity_log
  └── Updates monthly_usage counters
```

### 6.5 Kanban Workflow

Four-column board for visual account management:

```
[ To Review ] → [ Ready to Unfollow ] → [ Unfollowed ] → [ Not Now ]
```

Drag-and-drop state is persisted to the `kanban_cards` table in real time via htmx `POST` calls. Each card is linked to a `following` record.

### 6.6 Billing & Subscription Flow

```
User selects plan (Free / Pro $9.99 / Premium $29.99)
        │
        ▼
BillingController → Stripe Checkout Session (hosted page)
        │
  Success/Cancel redirect
        │
        ▼
WebhookController (POST /webhooks/stripe)
  ├── Validates Stripe-Signature header
  ├── Handles: checkout.session.completed
  │           customer.subscription.updated
  │           customer.subscription.deleted
  │           invoice.payment_succeeded
  └── Updates users.subscription_tier + subscriptions table
```

---

## 7. API Integration

### 7.1 Instagram Graph API

- **Version:** v20.0
- **Auth:** OAuth 2.0 Authorization Code Flow
- **Token lifetime:** 60 days (long-lived token), auto-refreshed
- **Rate limiting:** 200 API calls per hour tracked in `instagram_connections.api_calls_this_hour`
- **Endpoints used:**
  - `GET /me` — profile metadata
  - `GET /me/following` — paginated following list
  - `GET /me/followers` — paginated followers list
  - `GET /me/media` — recent media for engagement calculation
  - `DELETE /me/following/{id}` — unfollow action

### 7.2 Stripe API

- **Version:** v13.x PHP SDK
- **Integration type:** Stripe Checkout (hosted payment page)
- **Webhook events handled:** `checkout.session.completed`, `customer.subscription.*`, `invoice.payment_succeeded`
- **Webhook security:** HMAC-SHA256 signature validation (`STRIPE_WEBHOOK_SECRET`)

---

## 8. Security Architecture

### 8.1 Authentication & Session

- Passwords hashed with **Argon2ID** (PHP default, memory-hard, resistant to GPU cracking).
- Sessions use `cookie_httponly=true`, `cookie_samesite=Lax`, `cookie_secure=true` in production.
- Account lockout after repeated failed login attempts (`failed_login_attempts` + `locked_until`).
- Optional **TOTP 2FA** (Google Authenticator) with 8 single-use recovery codes.
- Email verification required before full access.

### 8.2 API Token Security

Instagram OAuth tokens are sensitive long-lived credentials. They are:
1. Received from Instagram OAuth callback.
2. Immediately encrypted using **AES-256-CBC** via `EncryptionService`.
3. Stored encrypted in `instagram_connections.access_token`.
4. Decrypted in memory only when an API call is needed — never logged or exposed.

### 8.3 Web Security

| Threat                  | Mitigation                                              |
|-------------------------|---------------------------------------------------------|
| SQL Injection           | All queries use PDO prepared statements                 |
| CSRF                    | Synchroniser token on all POST/PUT/DELETE forms         |
| XSS                     | `htmlspecialchars()` in all view output                 |
| Clickjacking            | `X-Frame-Options: SAMEORIGIN` header                   |
| MIME sniffing           | `X-Content-Type-Options: nosniff` header               |
| Sensitive file exposure | `.htaccess` denies access to `.env`, `composer.json`   |
| Brute force             | Login rate limiting + account lockout                  |

---

## 9. Middleware Pipeline

Middleware runs between routing and controller execution. It is applied per-route or per-route-group.

```
Route definition:
$router->group(['middleware' => ['auth']], function($r) { ... });

Execution order:
Request → Router match → AuthMiddleware::handle() → Controller::action()
```

| Middleware       | Trigger         | Behaviour                                             |
|------------------|-----------------|-------------------------------------------------------|
| `AuthMiddleware` | All auth routes | Checks `$_SESSION['user_id']`; redirects to `/auth/login` if absent |
| `AdminMiddleware`| Admin routes    | Checks `users.is_admin`; returns 403 if false         |
| `CsrfMiddleware` | POST routes     | Validates `csrf_token` field against session token    |

---

## 10. Subscription Tiers & Quota System

| Feature                     | Free  | Pro ($9.99/mo) | Premium ($29.99/mo) |
|-----------------------------|-------|----------------|---------------------|
| Followed accounts tracked   | 500   | 5,000          | Unlimited           |
| Unfollows per day           | 10    | 100            | 500                 |
| Sync frequency              | Manual| Daily auto     | Hourly auto         |
| Export (CSV/JSON)           | No    | Yes            | Yes                 |
| Algorithm customisation     | No    | No             | Yes                 |
| Kanban board                | No    | Yes            | Yes                 |
| Priority support            | No    | No             | Yes                 |

Quotas are enforced in `UnfollowQueueService` and tracked per-month in `monthly_usage`.

---

## 11. Deployment Architecture

### 11.1 Local Development

```
PHP built-in server (port 8080)
  └── router.php (static file passthrough + front controller)
      └── public/index.php
XAMPP MariaDB (port 3306)
```

### 11.2 Docker (Staging / Production)

```
┌─────────────────────────────────┐
│      docker-compose.yml         │
│                                 │
│  ┌──────────┐  ┌─────────────┐  │
│  │ web      │  │  db         │  │
│  │ PHP 8.2  │  │  MySQL 8.0  │  │
│  │ Apache   │  │             │  │
│  │ :80/:443 │  │  :3306      │  │
│  └────┬─────┘  └──────┬──────┘  │
│       │               │         │
│  ┌────▼───────────────▼──────┐  │
│  │     phpMyAdmin (:8081)    │  │
│  └───────────────────────────┘  │
└─────────────────────────────────┘
```

### 11.3 Shared Hosting (000webhost / cPanel)

Standard deployment to any PHP 8.2 + MySQL shared host:
- Upload all files except `vendor/` (or run `composer install` on server)
- Import `database/schema.sql` via phpMyAdmin
- Set `.env` credentials via file manager
- Point document root to `/public`

---

## 12. Known Limitations & Engineering Considerations

| Area                | Current State                       | Recommended Improvement              |
|---------------------|-------------------------------------|--------------------------------------|
| Caching             | No caching layer                    | Add Redis for session + query cache  |
| Background jobs     | `cron/sync.php` exists, not scheduled | Integrate with system cron or queue (e.g., Beanstalkd) |
| Test coverage       | Zero automated tests                | Add PHPUnit unit + integration tests |
| N+1 queries         | Some service loops hit DB per record| Batch queries / eager load           |
| Error tracking      | PHP error_log only                  | Integrate Sentry or Bugsnag          |
| CI/CD               | Manual push                         | Add GitHub Actions pipeline          |
| Static assets       | Unminified CSS/JS                   | Add Vite or simple build step        |
| Missing controllers | PricingController, FeaturesController, AboutController referenced but not implemented | Implement or stub with placeholder views |

---

## 13. Data Flow Diagram (End-to-End)

```
[User Browser]
     │
     │ HTTPS
     ▼
[Apache / PHP Dev Server]
     │
     ├─► Static assets served directly (CSS, JS, images)
     │
     └─► All other requests → public/index.php
              │
              ├─► Session started
              ├─► Config loaded from .env
              ├─► Database connection established (PDO)
              │
              ├─► Router dispatches request
              │        │
              │        ├─► Middleware pipeline (Auth / CSRF / Admin)
              │        │
              │        └─► Controller method called
              │                 │
              │                 ├─► Service layer (business logic)
              │                 │        │
              │                 │        ├─► Instagram Graph API (outbound HTTP via Guzzle)
              │                 │        ├─► Stripe API (outbound HTTP)
              │                 │        └─► Database read/write (PDO → MySQL)
              │                 │
              │                 └─► View rendered (PHP template → HTML)
              │
              └─► HTTP Response sent to browser
                       │
                       └─► htmx intercepts AJAX requests
                            └─► Partial HTML swapped in DOM
                                (no full page reload for interactive features)
```

---

*Document prepared for technical review of InstaBAlancePRO v1.0*
*Repository: github.com/CREEDCONSULT/INSTABALANCEPRO*
