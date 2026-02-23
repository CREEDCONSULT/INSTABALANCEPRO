# ENTERPRISE BUILD PROMPT — Full-Stack Application PRD Generator
**Version:** 2.0 (Enterprise Edition)
**Built from:** InstaBAlancePRO Architecture + Enterprise Readiness Assessment
**Purpose:** Generate a complete, enterprise-grade PRD from any app idea — production-ready,
scalable, observable, compliant, and maintainable by a professional engineering team.

---

## DIFFERENCE FROM STANDARD BUILD PROMPT

This enterprise version adds:
- Observability-first design (logging, tracing, monitoring from day one)
- Horizontal scaling architecture (stateless, Redis sessions, load-balanced)
- Full test strategy with coverage gates
- Database migration system (not dump files)
- CI/CD pipeline with security gates
- Secrets management (not .env files)
- API versioning and OpenAPI contract
- Multi-tenancy isolation audit
- Infrastructure-level rate limiting
- Compliance framework (GDPR, SOC2, PCI-DSS as applicable)
- Job queue architecture for async work
- SLA/SLO definitions
- Disaster recovery plan
- Cost estimation
- Team operating model

---

## HOW TO USE

1. Fill in `[BRACKETED FIELDS]` in **Section 0** only.
2. Paste the entire document into Claude, GPT-4, or any frontier LLM.
3. The LLM will output a complete enterprise PRD.
4. Each Part can be regenerated independently by referencing its number.
5. Use the Build Prompt Sequence (Part 18) to generate code prompt-by-prompt.

---

---

# ============================================================
# SECTION 0 — YOUR IDEA INPUT (FILL THIS IN)
# ============================================================

```
APP NAME:            [Your app name]
TAGLINE:             [One sentence — what it does and for whom]
CORE PROBLEM:        [The specific, painful problem this solves]
TARGET MARKET:       [Industry / persona / company size]
PRIMARY USER:        [Who uses it daily — job title, context, pain]
SECONDARY USERS:     [Admins, managers, API consumers, etc.]
CORE ACTION:         [The ONE thing users do most in the app]
MONETISATION:        [SaaS subscriptions / usage-based / enterprise licence / marketplace]
PRICING MODEL:       [Free tier + paid / all-paid / per-seat / per-API-call / hybrid]
PLATFORM:            [Web / mobile / desktop / API-first / all]
INTEGRATION TARGETS: [Key third-party systems it must connect to]
DATA SENSITIVITY:    [Public / internal business data / PII / financial / health / government]
COMPLIANCE TARGETS:  [GDPR / SOC2 / HIPAA / PCI-DSS / ISO27001 / none stated]
SCALE EXPECTATION:   [Users at launch / 6 months / 2 years — rough estimates]
TEAM SIZE:           [Solo / 2-5 / 5-20 / 20+ engineers]
TECH PREFERENCE:     [Preferred languages/frameworks or leave blank for recommendation]
GEOGRAPHY:           [Markets — US / EU / Global / specific region]
INSPIRATION APPS:    [2-3 reference products for UX/feature benchmarking]
DIFFERENTIATOR:      [Unique angle — what makes this defensibly better]
```

---

---

# ============================================================
# MASTER PROMPT — INSTRUCTIONS TO THE LLM
# ============================================================

You are a **Principal Engineer, Enterprise Architect, and Head of Product** with 15 years of
experience building and scaling SaaS platforms from 0 to millions of users.

Your task: take the idea in Section 0 and produce a **complete, enterprise-grade Product
Requirements Document (PRD)**. This document must be immediately actionable by a senior
engineering team. No ambiguity. No hand-waving. No "this depends on requirements" deflections.

Make concrete decisions. Where trade-offs exist, pick a direction, explain why, and note the
alternative. A team must be able to start building on day one from this document alone.

Work through all parts sequentially. Do not skip. Do not summarise where depth is demanded.
Reference the idea from Section 0 throughout every section.

---

## PART 1 — EXECUTIVE SUMMARY

Produce a one-page executive summary containing:
- App name, tagline, and one-paragraph description
- The problem (quantified where possible — time lost, money wasted, error rate)
- The solution and its core value proposition
- Target market with sizing (TAM / SAM / SOM in $ or user count)
- Revenue model with Year 1–3 projections (conservative estimates)
- Technology stack summary (5 bullets)
- Team and timeline estimate (phases with durations)
- Key risks and mitigations (top 3)
- Success definition (what does winning look like at 12 months?)

---

## PART 2 — CONCEPT ANALYSIS & MARKET VALIDATION

### 2.1 Problem Statement
- State the problem with precision. Who has it, how often, at what cost?
- Distinguish between the **surface complaint** (what users say) and the **root cause** (why it happens)
- Quantify: hours lost per week, dollar value of inefficiency, error rate, churn cost
- What is the current workaround? Why is it inadequate?
- Is this a **vitamin** (nice-to-have) or **aspirin** (must-have)?
- What is the cost of doing nothing? (Why would someone not just keep the status quo?)

### 2.2 Solution & Value Proposition
- One paragraph: exactly what the app does and why it works
- Core value proposition (measurable): "Users save X hours / reduce Y errors / increase Z by N%"
- The **magic moment**: the first moment a new user feels undeniable value (define precisely)
- What does the user do differently after adopting this product?
- Elevator pitch (30 seconds, non-technical)

### 2.3 Assumptions Register
List every assumption the business model rests on. Format as a register:

| ID | Assumption | Confidence | Risk if Wrong | Validation Method |
|----|------------|------------|---------------|-------------------|

Minimum 8 assumptions. Cover: market size, willingness to pay, technical feasibility,
regulatory landscape, user behaviour, competitor response.

### 2.4 Risk Register
| ID | Risk | Likelihood (H/M/L) | Impact (H/M/L) | Mitigation |
|----|------|-------------------|----------------|------------|

Minimum 10 risks across: market, technical, regulatory, competitive, team, financial.

---

## PART 3 — MARKET RESEARCH & COMPETITIVE INTELLIGENCE

### 3.1 Market Sizing (Bottom-Up and Top-Down)
- **Top-down:** Industry analyst reports, total market value
- **Bottom-up:** Number of potential buyers × average contract value × realistic capture rate
- Market growth rate (CAGR) and key drivers
- Geographic distribution of the market

### 3.2 Competitive Landscape
Analyse **6–10 competitors** (direct and indirect). For each produce a full profile:

**Competitor Profile Template:**
- Name, founding year, funding raised, employee count
- Product description (what exactly it does)
- Target customer (ICP — Ideal Customer Profile)
- Pricing model and price points
- Key strengths (top 3, be specific)
- Key weaknesses (top 3, be specific and honest)
- Technology approach (notable tech choices)
- Estimated monthly active users / revenue (public data or estimate)
- Recent strategic moves (new features, acquisitions, funding rounds)

Summarise in a feature comparison matrix.

### 3.3 Competitive Positioning
- 2×2 positioning matrix (define your two axes carefully — not generic)
- Unique positioning statement (Geoffrey Moore crossing the chasm template)
- Defensible moat analysis: network effects / data advantage / switching costs /
  brand / regulatory / scale economics — which apply and how strong?
- Where are competitors weakest that you can exploit first?
- What would it take for a well-funded competitor to copy you in 12 months?

### 3.4 Ideal Customer Profile (ICP)
For B2B: Define the ideal company to sell to:
- Industry / vertical
- Company size (employees, revenue)
- Tech stack (compatibility matters)
- Budget authority (who signs the contract)
- Trigger events (what causes them to start looking for a solution)
- Disqualifiers (companies that are NOT a fit)

### 3.5 Go-To-Market Strategy
- Primary and secondary acquisition channels with rationale
- Content/SEO strategy (target keywords, search intent mapping)
- Product-led growth loops (if applicable)
- Sales motion (self-serve / inside sales / enterprise sales)
- Partnership opportunities
- Launch sequence (waitlist → beta → public)
- First 100 customers plan (how specifically do you get them?)
- CAC target per channel, LTV:CAC ratio target

---

## PART 4 — USER RESEARCH & PERSONAS

### 4.1 Primary Persona (Detailed)
- **Name, role, company type**
- **Demographics:** Age range, education, income, location
- **Psychographics:** Goals, fears, motivations, values, personality
- **A day in their life:** Morning to evening, how the problem manifests
- **Current tools used** (specific products, not categories)
- **Tech sophistication:** Scale 1–5 with justification
- **Decision-making:** Do they buy themselves or need approval? Budget?
- **Quote:** Something this exact person would say about their problem
- **Jobs to be done:** When [situation], I want to [action], so I can [outcome]
- **Success state:** What does their life look like after solving this problem?

### 4.2 Secondary Personas
Define 2–3 secondary personas using the same structure.
Include: admin/operator persona, executive/buyer persona, API consumer/developer persona
(as applicable to your product).

### 4.3 Anti-Persona
Define who this is explicitly NOT for. Sharpens positioning and prevents feature bloat.

### 4.4 User Journey Map (Full Lifecycle)

Map each stage with: Touchpoint | User Emotion | User Action | System Trigger | Drop-off Risk | Success Metric

```
STAGE 1: AWARENESS
  How they discover the product → what they feel → what they do next

STAGE 2: CONSIDERATION
  Research phase → competitor comparison → trust signals they look for

STAGE 3: SIGN UP / TRIAL
  Registration flow → first impressions → time to first value

STAGE 4: ONBOARDING
  Setup steps → integration → first meaningful output

STAGE 5: ACTIVATION (MAGIC MOMENT)
  The moment value is undeniable → what triggers it → how to accelerate

STAGE 6: HABIT FORMATION
  Regular usage pattern → triggers → rewards → routine

STAGE 7: EXPANSION
  Upgrade triggers → additional seats → new feature adoption

STAGE 8: ADVOCACY
  Referral behaviour → reviews → word of mouth → NPS

STAGE 9: CHURN RISK
  Warning signals → intervention triggers → save attempts

STAGE 10: OFFBOARDING
  Cancellation flow → data export → win-back sequence
```

---

## PART 5 — PRODUCT DEFINITION

### 5.1 MVP Feature Set
Only features required to prove the core hypothesis and deliver the magic moment.

For each feature:
- **Feature name**
- **User story:** As a [persona], I want to [action], so that [outcome]
- **Acceptance criteria:** Numbered, testable, unambiguous conditions
- **Priority:** P0 (launch blocker) / P1 (important) / P2 (nice to have)
- **Complexity:** S / M / L / XL (story points or T-shirt sizing)
- **Dependencies:** Other features or systems it requires
- **Edge cases:** What unusual states must it handle?

### 5.2 V1.0 Full Feature Set
Complete feature list beyond MVP. Same structure as 5.1.

### 5.3 Product Roadmap (18 months)
Organise into quarterly releases:
- Q1: MVP launch — core loop working
- Q2: Retention features — habit formation
- Q3: Expansion features — growth levers
- Q4: Enterprise features — larger deal sizes
- Q5–Q6: Platform / ecosystem / integrations

For each quarter: theme, feature list, success metric, go-to-market motion.

### 5.4 Explicitly Out of Scope (V1.0)
List 8–10 features with reason excluded. Prevents scope creep.

### 5.5 Business Rules & Logic (Complete)
Every rule that governs application behaviour. Be exhaustive:

**Quota & Limits:**
- What are the hard limits per tier?
- What happens when a limit is reached? (Block / warn / charge overage)
- How are limits tracked? (Real-time counter / daily reset / monthly reset)
- Who can override limits? (Admin only / self-serve upgrade)

**Calculations & Algorithms:**
- Every formula used in the product (scoring, pricing, ranking, recommendations)
- Defined in pseudocode or mathematical notation
- Input ranges, output ranges, edge case handling

**State Machines:**
- Every entity that has states (subscription, job, order, ticket, etc.)
- All valid state transitions and their triggers
- Invalid transitions (what is explicitly not allowed)

**Pricing & Billing Logic:**
- Proration rules (upgrade mid-cycle / downgrade mid-cycle)
- Trial period behaviour
- Failed payment handling (grace period / feature degradation / suspension)
- Cancellation and data retention post-cancellation

**Notifications & Communications:**
- Every email the system sends (trigger, recipient, subject, purpose)
- In-app notification rules (trigger, display duration, dismissal)
- Push notification rules (if applicable)
- Notification preferences and opt-out handling
- Transactional vs marketing email classification

---

## PART 6 — UI/UX DESIGN SPECIFICATION

### 6.1 Design Principles
5–6 principles with name, definition, and practical example for this product.

### 6.2 Information Architecture
Complete site map — every page, modal, and drawer:
```
[App Name]
├── Marketing / Public
│   ├── Home (/)
│   ├── Features (/features)
│   ├── Pricing (/pricing)
│   ├── About (/about)
│   ├── Blog (/blog)
│   ├── Legal
│   │   ├── Privacy Policy
│   │   ├── Terms of Service
│   │   └── Cookie Policy
│   └── Status Page (/status)
├── Authentication
│   ├── Register (/register)
│   ├── Login (/login)
│   ├── Forgot Password
│   ├── Reset Password
│   ├── Email Verification
│   └── 2FA Verification
├── Onboarding (authenticated, first-time only)
│   ├── Step 1: Profile setup
│   ├── Step 2: Integration connect
│   └── Step 3: First action guided
├── Application (authenticated)
│   ├── Dashboard (/)
│   ├── [Feature 1]
│   ├── [Feature 2]
│   ├── [Feature N]
│   ├── Settings
│   │   ├── Profile
│   │   ├── Security (password, 2FA)
│   │   ├── Notifications
│   │   ├── Integrations
│   │   ├── Team / Members
│   │   ├── Billing
│   │   └── Data (export, delete)
│   └── Help / Support
└── Admin Panel (/admin) — internal only
    ├── Users
    ├── Subscriptions
    ├── System Health
    ├── Feature Flags
    └── Audit Log
```

### 6.3 Page-by-Page Specification
For each page/screen define:
- **Purpose:** What is the user trying to accomplish?
- **Entry points:** How does a user reach this page?
- **Components:** Complete list of UI elements
- **Primary CTA:** The single most important action
- **Data sources:** What API calls populate this page?
- **Loading state:** Skeleton screen or spinner approach
- **Empty state:** First-time user or no-data state (with copy)
- **Error states:** All error conditions and how displayed
- **Responsive behaviour:** How layout changes mobile → desktop
- **ASCII wireframe:** Text representation of the layout

### 6.4 Onboarding Flow
Enterprise-grade onboarding is critical for activation:
- Progressive disclosure (don't overwhelm on day 1)
- Checklist / progress indicator
- Each step: purpose, UI, skip option, completion trigger
- "Aha moment" path — fastest route to magic moment
- Onboarding email sequence (timing and content of each email)
- Re-engagement if onboarding stalled

### 6.5 Design System Specification

**Brand Identity:**
- Primary, secondary, and accent colours (hex + WCAG contrast ratio)
- Dark mode palette (all above colours in dark variant)
- Error, warning, success, info semantic colours
- Background, surface, border, divider colours

**Typography:**
- Heading font (name, CDN source, weights)
- Body font (name, CDN source, weights)
- Monospace font (for code/data)
- Type scale (every size from display → caption, in rem)
- Line height and letter spacing for each level

**Spacing & Grid:**
- Base unit (4px or 8px)
- Full spacing scale
- Grid columns (12-column, gutters, margins at each breakpoint)
- Breakpoints (xs, sm, md, lg, xl, 2xl in px)

**Component Library:**
For each component: variants, sizes, states (default/hover/active/focus/disabled/error/loading)
- Buttons (primary, secondary, ghost, danger, icon, loading)
- Inputs (text, email, password, number, textarea, select, checkbox, radio, toggle, date)
- Cards (default, interactive, elevated, outlined)
- Tables (with sort, filter, pagination, empty state, loading state)
- Modals (small, medium, large, fullscreen, confirmation pattern)
- Navigation (sidebar, topbar, tabs, breadcrumbs, pagination)
- Feedback (toast/snackbar, inline alert, banner, progress, skeleton)
- Data visualisation (charts, stats cards, progress bars, heatmaps)
- Forms (layout, validation patterns, error display, submission states)

**Motion & Animation:**
- Duration scale (instant: 0ms, fast: 100ms, normal: 200ms, slow: 300ms)
- Easing functions for enter, exit, and in-place transitions
- Skeleton loading pattern (shimmer specification)
- Page transition approach

### 6.6 Accessibility Standards
- WCAG 2.1 AA minimum — specify areas targeting AAA
- Keyboard navigation map (Tab order for each major page)
- Screen reader announcements (live regions, aria-labels)
- Focus indicator design (visible, 3:1 contrast ratio minimum)
- Form field labelling standards
- Image alt text policy
- Motion sensitivity (prefers-reduced-motion support)
- Touch target minimum sizes (44×44px)

---

## PART 7 — TECHNOLOGY STACK SELECTION

For each decision, provide: Chosen technology | Version | Rationale | Alternatives considered | Why rejected.

### 7.1 Backend
- Language and runtime
- Web framework (battle-tested framework strongly preferred for enterprise)
- ORM / query builder
- Authentication library
- Validation library
- HTTP client (for external API calls)
- Email library
- Queue/job system
- Cache driver
- Search engine (if applicable)
- PDF/export generation (if applicable)
- File processing (images, documents — if applicable)

### 7.2 Database Layer
- Primary database (type, vendor, version)
- Read replica strategy
- Connection pooling (PgBouncer / ProxySQL / built-in)
- Migration tool
- Backup strategy
- Secondary database (Redis for cache/sessions/queues)
- Search database (Elasticsearch / Typesense — if search is a core feature)
- Object storage (S3-compatible — for files/assets)

### 7.3 Frontend
- Framework or approach (vanilla / React / Vue / htmx — justify)
- State management (if SPA)
- CSS framework
- Build tooling / bundler
- Icon library
- Chart/data viz library
- Date handling library
- Form handling library
- Component testing library

### 7.4 Infrastructure
- Hosting platform (cloud provider and rationale)
- Container orchestration (Docker Compose → Kubernetes migration path)
- CDN provider
- Load balancer
- DNS management
- SSL/TLS certificate management
- Secret management platform
- Log aggregation platform
- Error tracking platform
- APM / metrics platform
- Uptime monitoring
- CI/CD platform
- Container registry
- Vulnerability scanning tool

### 7.5 Development Tools
- Version control (GitHub / GitLab / Bitbucket)
- Code review process
- Linter and formatter (per language)
- Pre-commit hooks
- API documentation (OpenAPI / Swagger)
- Project management tool
- Internal documentation (Notion / Confluence / wiki)
- Feature flag system
- A/B testing framework (if applicable)

---

## PART 8 — TECHNICAL ARCHITECTURE (ENTERPRISE GRADE)

### 8.1 Architecture Pattern
Justify the chosen pattern:
- **Monolith:** Justified for teams < 10 engineers, single domain, fast iteration priority
- **Modular Monolith:** Logical separation without network overhead; migration path to services later
- **Microservices:** Only if team > 20 engineers, multiple independent deployment domains, proven need
- **Serverless:** For event-driven, spiky workloads only; not recommended for CRUD SaaS core

For this application: state the chosen pattern and provide the full rationale.

### 8.2 System Components Diagram
Full ASCII diagram showing every component and how they connect:

```
┌─────────────────────────────────────────────────────────────────┐
│                         CLIENT TIER                             │
│  [Browser/Mobile]  ←→  [CDN (Static Assets)]                   │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTPS
┌────────────────────────────▼────────────────────────────────────┐
│                       EDGE / GATEWAY TIER                       │
│  [Cloudflare WAF + DDoS]  →  [Load Balancer]                   │
│  [Rate Limiter]  [IP Allowlist]  [SSL Termination]             │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                      APPLICATION TIER                           │
│  [App Server 1]   [App Server 2]   [App Server N]              │
│       └──────────────────┬──────────────────┘                  │
│                    [Shared Session Store]                       │
│                        (Redis)                                  │
└──────┬─────────────────┬─────────────────┬──────────────────────┘
       │                 │                 │
┌──────▼──────┐  ┌───────▼──────┐  ┌──────▼──────────────────┐
│  DATA TIER  │  │  CACHE TIER  │  │     ASYNC TIER          │
│ [DB Primary]│  │   [Redis]    │  │  [Queue: Redis/RabbitMQ] │
│ [DB Replica]│  │              │  │  [Worker Processes]      │
│ [Migrations]│  │              │  │  [Scheduled Jobs (Cron)] │
└─────────────┘  └──────────────┘  └─────────────────────────┘
       │
┌──────▼──────────────────────────────────────────────────────────┐
│                    EXTERNAL SERVICES                             │
│  [Third-party API 1]  [Payment Gateway]  [Email Service]        │
│  [Object Storage]     [SMS Gateway]      [OAuth Providers]      │
└─────────────────────────────────────────────────────────────────┘
       │
┌──────▼──────────────────────────────────────────────────────────┐
│                    OBSERVABILITY STACK                           │
│  [Structured Logs → ELK/Datadog]  [Metrics → Prometheus/Grafana]│
│  [Traces → OpenTelemetry]         [Errors → Sentry]            │
│  [Uptime → BetterStack]           [Alerts → PagerDuty]         │
└─────────────────────────────────────────────────────────────────┘
```

Extend and customise this diagram for the specific app. Every box must be justified.

### 8.3 Application Layer Architecture

**Directory Structure (Complete):**
```
PROJECT_ROOT/
├── public/                    # Web root ONLY — nothing else is public
│   ├── index.php              # Front controller
│   └── assets/                # Compiled, versioned static files only
├── src/ (or app/)             # All application code
│   ├── Http/
│   │   ├── Controllers/       # Thin — delegate to Services immediately
│   │   ├── Middleware/        # Auth, CSRF, Rate limit, Tenant, Logging
│   │   ├── Requests/          # Input validation objects (one per endpoint)
│   │   └── Resources/         # API response transformers/serialisers
│   ├── Services/              # All business logic — framework-agnostic
│   ├── Models/                # Eloquent models or equivalent
│   ├── Jobs/                  # Async job classes (one per job type)
│   ├── Events/                # Domain events
│   ├── Listeners/             # Event handlers
│   ├── Notifications/         # Email/SMS/push notification classes
│   ├── Policies/              # Authorisation policies
│   └── Exceptions/            # Custom exception classes
├── config/                    # All configuration — no business logic
├── database/
│   ├── migrations/            # Sequential, versioned migration files
│   ├── seeders/               # Test and demo data
│   └── factories/             # Model factories for testing
├── tests/
│   ├── Unit/                  # Pure unit tests — no DB, no HTTP
│   ├── Feature/               # Integration tests — real DB, mocked HTTP
│   └── Browser/               # E2E tests (Playwright)
├── resources/
│   ├── views/                 # Templates
│   ├── css/                   # Source CSS
│   └── js/                    # Source JS
├── routes/
│   ├── web.php                # Browser routes
│   ├── api.php                # API routes (versioned)
│   └── console.php            # CLI commands
├── storage/
│   ├── logs/                  # Application logs (local dev only)
│   └── framework/             # Framework cache (gitignored)
├── docker/                    # All Docker-related files
│   ├── php/                   # PHP-FPM config + Dockerfile
│   ├── nginx/                 # nginx config
│   └── supervisor/            # Worker process management
├── .github/
│   ├── workflows/             # CI/CD pipeline definitions
│   └── PULL_REQUEST_TEMPLATE.md
├── docker-compose.yml         # Development environment
├── docker-compose.prod.yml    # Production overrides
└── Makefile                   # Developer convenience commands
```

### 8.4 Request Lifecycle (Annotated)

Trace every step from browser to response. Include timing expectations at each step:

```
1.  DNS resolution (client, cached)
2.  TCP + TLS handshake (CDN edge)
3.  CDN cache check (HIT → return asset; MISS → continue)
4.  WAF inspection (Cloudflare — block known threats)
5.  Rate limit check (nginx: per-IP, per-user)
6.  Load balancer routes to available app server
7.  nginx receives request, passes to PHP-FPM via fastcgi
8.  PHP-FPM worker picks up request
9.  Application bootstrap:
    a. Autoloader initialisation
    b. .env / secrets loaded
    c. Service container bound
    d. Session initialised (read from Redis)
10. Router matches URI + method → controller + middleware stack
11. Middleware pipeline executes (in order):
    a. RequestLogging (log incoming request with trace ID)
    b. Authentication (validate session or Bearer token)
    c. Authorisation (check permission for this action)
    d. TenantScope (inject tenant context into all queries)
    e. CSRF validation (POST/PUT/DELETE only)
    f. RateLimit (per-user action limits)
12. Controller method called
    a. Request object validates and sanitises input
    b. Service method called (business logic)
    c. Service queries database (scoped to tenant)
    d. Service may dispatch async job (push to queue)
    e. Service returns result
13. Controller builds response (View or JSON Resource)
14. ResponseLogging middleware logs response time, status
15. PHP-FPM returns response to nginx
16. nginx sends response to client
17. Async: Job picked up by worker → processed → logged

Target: Steps 9–16 complete in < 200ms (P95) for read operations
        < 500ms (P95) for write operations
```

### 8.5 Caching Architecture
Define every cache layer:

| Layer | Technology | What is Cached | TTL | Invalidation Strategy |
|-------|------------|---------------|-----|----------------------|
| Database query | Redis | Expensive read queries | 5 min | Event-driven on write |
| Session | Redis | User session data | 24 hrs | On logout / expiry |
| Rate limit counters | Redis | Per-user action counts | 1 hr rolling | Auto-expiry |
| Full page (optional) | CDN | Public marketing pages | 1 hr | Purge on deploy |
| Asset | CDN | CSS, JS, images | 1 year | Cache-busted by hash |
| API response | Redis | Third-party API responses | Per API SLA | TTL only |

### 8.6 Asynchronous Job Architecture
Every background task must be a formal job class:

For each job:
- **Job name and class**
- **Trigger** (user action / event / schedule)
- **Queue name** (default / high-priority / low-priority / scheduled)
- **Estimated duration**
- **Max retries and backoff strategy**
- **Timeout**
- **Idempotency key** (how is re-processing of duplicates prevented?)
- **Failure handling** (dead letter queue / alert / user notification)
- **Concurrency limit** (how many can run simultaneously?)

### 8.7 Event-Driven Architecture (If Applicable)
If the app has significant internal decoupling needs:
- Domain events list (UserRegistered, SubscriptionUpgraded, DataSynced, etc.)
- Event payload definition
- Listeners per event
- Synchronous vs asynchronous listener decision per case

---

## PART 9 — DATABASE DESIGN (ENTERPRISE GRADE)

### 9.1 Schema Design Principles
Before writing tables, state the design principles:
- Normalisation level (3NF baseline, denormalise with justification)
- Naming conventions (snake_case, plural tables, FK naming)
- Timestamp standards (all tables have created_at, updated_at)
- Soft delete strategy (deleted_at or separate archive tables)
- UUID vs auto-increment IDs (justify the choice)
- Multi-tenancy strategy (shared schema with tenant_id / separate schemas / separate DBs)

### 9.2 Complete Schema (Full DDL)
Write every `CREATE TABLE` statement. For every table:
- All columns with type, nullable/not null, default, and inline comment
- Primary key definition
- All foreign keys with ON DELETE / ON UPDATE behaviour
- All indexes (explain each — why is this index needed?)
- Unique constraints
- Check constraints (where applicable)
- Table-level comment describing purpose

Group into domains:
- Auth & Identity
- Core Business Entities
- Workflow & State
- Billing & Subscriptions
- Analytics & Audit
- Configuration & Settings

### 9.3 Migration Strategy
Define the migration system:
- Tool selection (Phinx / Doctrine / framework native / custom)
- Naming convention for migration files (timestamp + descriptive name)
- Up and down migration requirement (all migrations must be reversible)
- Zero-downtime migration rules:
  - Never rename a column directly (add new → backfill → switch → drop old)
  - Never add NOT NULL without a default (add nullable → backfill → make not null)
  - Never drop a column without first removing all references
  - Large table migrations must use batching, not single-transaction ALTER

### 9.4 Entity Relationship Diagram
Full ERD as ASCII or structured description. Every table and its relationships:
- Cardinality (1:1, 1:N, M:N)
- FK column names
- Cascade behaviour

### 9.5 Data Dictionary
For every table, a complete data dictionary:

Table: [name]
Purpose: [one sentence]

| Column | Type | Nullable | Default | Index | Description |
|--------|------|----------|---------|-------|-------------|

### 9.6 Query Patterns & Index Justification
Identify the 15 most frequent/critical queries:
- SQL statement
- Execution plan (expected)
- Index(es) used
- Expected row count / selectivity
- Caching eligibility (yes/no and why)

### 9.7 Data Retention & Compliance
- Retention periods per data category (user data / audit logs / billing / analytics)
- Deletion cascade rules
- GDPR right-to-erasure implementation (what is deleted vs anonymised vs retained for legal)
- Data export format (GDPR data portability — format and completeness)
- Backup schedule, retention, and tested restore procedure

---

## PART 10 — API DESIGN (VERSIONED, DOCUMENTED)

### 10.1 API Conventions
Before listing routes, define the standards:
- Base URL structure: `https://api.[domain].com/v1/`
- Versioning strategy: URL path versioning (`/v1/`, `/v2/`)
- Authentication: Bearer token (JWT or opaque) — specify format
- Response envelope format:
  ```json
  {
    "data": { ... },
    "meta": { "page": 1, "per_page": 25, "total": 150 },
    "errors": null
  }
  ```
- Error response format:
  ```json
  {
    "data": null,
    "errors": [
      { "code": "VALIDATION_ERROR", "field": "email", "message": "Invalid email format" }
    ]
  }
  ```
- HTTP status code usage policy (which codes, when, and what they mean)
- Pagination approach (cursor-based for large datasets, offset for small)
- Filtering, sorting, and field selection conventions
- Rate limit headers (`X-RateLimit-Limit`, `X-RateLimit-Remaining`, `Retry-After`)
- CORS policy
- Idempotency key support (for POST operations on payment/order endpoints)

### 10.2 Complete Route Inventory
For every endpoint:

| Version | Method | Path | Auth | Rate Limit | Controller | Description |
|---------|--------|------|------|------------|------------|-------------|

Group by resource. Include all CRUD + custom actions.

### 10.3 Endpoint Specifications (Full)
For each endpoint, produce:
- **Method + Path**
- **Purpose**
- **Authentication required** (and permission scope)
- **Request headers** (required and optional)
- **Path parameters** (type, validation rules)
- **Query parameters** (type, validation rules, defaults)
- **Request body schema** (full JSON Schema or TypeScript interface)
- **Validation rules** (every field — type, required, min/max, pattern)
- **Success response** (HTTP code + full response body example)
- **Error responses** (all possible error codes and when they occur)
- **Side effects** (what else happens — emails sent, jobs queued, etc.)
- **Example curl request**

### 10.4 OpenAPI 3.0 Specification Structure
Define the OpenAPI spec structure. Identify:
- All security schemes
- All reusable components (schemas, responses, parameters)
- All tags (grouping for Swagger UI)
- Servers (development, staging, production)

### 10.5 Webhook Design (Outbound, If Applicable)
If the app sends webhooks to customers:
- Webhook event types and payload schemas
- Delivery mechanism (HTTP POST to customer URL)
- Signature validation (HMAC-SHA256 header)
- Retry policy (exponential backoff, max attempts)
- Delivery log and customer dashboard
- Customer registration flow (URL, secret, event selection)

### 10.6 External API Integrations
For each third-party API:
- **API name, provider, version**
- **Purpose in the application**
- **Authentication method and credential storage**
- **Endpoints used** (method, path, purpose, request/response summary)
- **Rate limits** and how the application tracks and respects them
- **Retry strategy** (which errors are retried, backoff algorithm)
- **Circuit breaker** (if unavailable, what degrades gracefully vs fails hard)
- **Timeout configuration**
- **Data mapping** (API fields → database columns)
- **Webhook events** processed (if applicable)
- **Cost** (API call pricing if metered)
- **Failover** (alternative if this API is unavailable)

---

## PART 11 — SECURITY ARCHITECTURE (ENTERPRISE GRADE)

### 11.1 Security Principles
State 5 security principles guiding all decisions. Example:
- **Defence in depth:** Multiple layers — never rely on a single control
- **Least privilege:** Every component has only the access it needs
- **Secure by default:** Features are secure without configuration
- **Fail secure:** On error, deny access rather than grant it
- **Zero trust:** Never assume a request is safe because of origin

### 11.2 Threat Model (STRIDE)
Apply the STRIDE framework to this application:

| Threat | Category | Attack Scenario | Mitigation | Residual Risk |
|--------|----------|-----------------|-----------|---------------|

Cover: Spoofing, Tampering, Repudiation, Information Disclosure,
Denial of Service, Elevation of Privilege.

Minimum 15 threats specific to this application.

### 11.3 Authentication Architecture
- Password policy (length, complexity, breach database check via HaveIBeenPwned API)
- Hashing: Argon2ID with explicit parameters (memory: 64MB, iterations: 3, parallelism: 1)
- Session security: httpOnly, Secure, SameSite=Strict (or Lax with justification)
- Session storage: Redis (not filesystem — prevents session fixation on multi-server)
- Session rotation: Regenerate session ID on privilege level change
- Account lockout: Progressive delay (not hard lockout — prevents DoS)
- 2FA: TOTP (RFC 6238), FIDO2/WebAuthn for enterprise tier
- OAuth providers: List each, scope requested, data stored
- JWT (if used): Algorithm (RS256 not HS256), expiry, refresh token rotation, revocation
- API keys: Format (prefix + random), hashed storage, last-used tracking, rotation

### 11.4 Authorisation Model
Define the authorisation approach:
- **RBAC** (Role-Based): Define all roles and their permissions
- **ABAC** (Attribute-Based): For fine-grained resource ownership
- **Policy classes**: One policy per resource type

Permission matrix:

| Resource | Guest | Free User | Pro User | Admin |
|----------|-------|-----------|----------|-------|
| Read X   |       | ✅        | ✅       | ✅    |
| Write X  |       |           | ✅       | ✅    |

### 11.5 Data Security
- Data classification (Public / Internal / Confidential / Restricted)
- Encryption at rest: which fields, which algorithm, key rotation
- Encryption in transit: TLS 1.2 minimum, TLS 1.3 preferred
- HSTS: `max-age=31536000; includeSubDomains; preload`
- Database encryption: transparent data encryption (if cloud-managed)
- Backup encryption
- What is NEVER stored (plaintext passwords, card numbers, SSNs)
- What is NEVER logged (passwords, tokens, PII in query params)
- PII inventory (what personal data is collected, where stored, how protected)

### 11.6 Application Security Controls
- CSRF: Synchroniser token pattern (not Double Submit Cookie for API)
- XSS: Context-aware output encoding; Content-Security-Policy header
- SQL injection: Parameterised queries only (no string interpolation in SQL)
- SSRF (Server-Side Request Forgery): URL allowlist for any server-side HTTP requests
- Path traversal: Absolute path resolution for all file operations
- Mass assignment: Explicit fillable/guarded field lists on all models
- File upload security: Type validation, size limits, virus scan, object storage (not web root)
- Dependency security: `composer audit` / `npm audit` in CI; Dependabot alerts
- Security headers (complete set):
  ```
  Content-Security-Policy: [full policy]
  X-Frame-Options: DENY
  X-Content-Type-Options: nosniff
  Referrer-Policy: strict-origin-when-cross-origin
  Permissions-Policy: [minimal permissions]
  Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
  ```

### 11.7 Infrastructure Security
- Principle of least privilege for all IAM roles
- No root/admin credentials in application code
- Secrets in vault (not .env committed to git)
- Network segmentation (app servers cannot directly access internet)
- Database not exposed to public internet (private subnet)
- Audit logging for all infrastructure access
- Immutable infrastructure (containers not patched in place, replaced)
- Vulnerability scanning: Trivy for containers, OWASP ZAP for web

### 11.8 Incident Response Plan
- Severity classification (P0: data breach / P1: service down / P2: degraded / P3: minor)
- For each severity: detection method, response SLA, escalation path, communication plan
- Security incident runbook (step-by-step)
- Data breach notification timeline (GDPR: 72 hours to authority)
- Post-incident review process

---

## PART 12 — OBSERVABILITY ARCHITECTURE

This section is mandatory. Enterprise systems are observable by design.

### 12.1 Three Pillars of Observability

**Logs (Structured, not text):**
- Format: JSON with consistent fields across all services
- Required fields on every log entry:
  ```json
  {
    "timestamp": "ISO8601",
    "level": "info|warning|error|critical",
    "trace_id": "UUID — same for entire request",
    "user_id": "integer or null",
    "action": "resource.verb (e.g. user.login)",
    "duration_ms": 42,
    "environment": "production",
    "message": "human readable",
    "context": { ... }
  }
  ```
- Log levels and when each is used
- What is logged (all requests, all errors, significant business events)
- What is NOT logged (PII, passwords, secrets — explicit list)
- Log storage: shipped to ELK / Datadog / CloudWatch
- Retention period per log category

**Metrics (Time-series):**
- System metrics: CPU, memory, disk, network (from infrastructure)
- Application metrics (custom, emitted in code):
  - Request rate (requests/second per endpoint)
  - Error rate (errors/total requests by status code)
  - Response time (P50, P95, P99 per endpoint)
  - Queue depth (jobs waiting per queue)
  - Job processing time (P95 per job type)
  - Cache hit rate per cache key pattern
  - External API call rate, error rate, latency
  - Active sessions count
  - Business metrics (signups/hour, upgrades/day, revenue/day)
- Dashboard definition (what graphs are on the main ops dashboard)

**Traces (Distributed):**
- Instrument with OpenTelemetry
- Trace every incoming HTTP request (parent span)
- Child spans for: DB queries, Redis calls, outbound HTTP, queue publish
- Trace sampling rate (100% in development, 10% in production, 100% for errors)
- Trace storage: Jaeger / Datadog APM / Honeycomb

### 12.2 Health Check Endpoints
- `GET /health` — liveness probe (app is running: HTTP 200)
- `GET /health/ready` — readiness probe (can accept traffic: checks DB, Redis)
- `GET /health/deep` — deep health (all dependencies: DB, Redis, queue, external APIs)

Response format:
```json
{
  "status": "healthy|degraded|unhealthy",
  "checks": {
    "database": { "status": "healthy", "latency_ms": 2 },
    "redis": { "status": "healthy", "latency_ms": 1 },
    "queue": { "status": "healthy", "depth": 12 },
    "instagram_api": { "status": "healthy", "latency_ms": 142 }
  },
  "version": "1.4.2",
  "uptime_seconds": 86400
}
```

### 12.3 Alerting Rules
Define every alert. Format:

| Alert Name | Condition | Severity | Response |
|------------|-----------|----------|----------|
| High error rate | >5% 5xx in 5min | P1 | Page on-call |
| DB slow queries | P95 > 1000ms | P2 | Slack notify |
| Queue backing up | Depth > 1000 jobs | P2 | Slack notify |
| Disk filling | >80% used | P2 | Slack notify |
| SSL expiry | < 30 days | P3 | Ticket created |

---

## PART 13 — TESTING STRATEGY (ENTERPRISE GRADE)

### 13.1 Testing Philosophy
State the testing approach (example: "Test behaviour, not implementation.
Unit test services. Integration test controllers. E2E test critical user paths.")

### 13.2 Test Coverage Requirements
| Layer | Coverage Target | Tool |
|-------|----------------|------|
| Services (business logic) | 90% | PHPUnit / Jest |
| Controllers | 80% | Feature tests |
| Models | 70% | Unit tests |
| Critical user journeys | 100% (E2E) | Playwright |

Coverage gate: CI pipeline fails if coverage drops below targets.

### 13.3 Test Categories

**Unit Tests:**
- Test each Service method in isolation
- Mock all external dependencies (DB, HTTP, queue)
- Fast (< 1ms per test), no I/O
- Every branch of every business logic function
- All edge cases and error conditions

**Integration Tests (Feature Tests):**
- Test full HTTP request → response cycle
- Use real database (test database, rolled back after each test)
- Mock external APIs (Guzzle mock, Stripe mock)
- Test auth and permission boundaries
- Test every API endpoint with valid and invalid inputs

**End-to-End Tests:**
- Playwright (or Cypress) against a running staging environment
- Cover the top 10 critical user journeys
- Run in CI before production deploy

**Contract Tests:**
- Verify API responses match OpenAPI spec
- Catch breaking changes before they reach consumers

**Performance Tests:**
- k6 or Apache JMeter load tests
- Define: peak concurrent users, ramp rate, acceptable response time
- Run against staging before major releases

**Security Tests:**
- `composer audit` / `npm audit` in CI
- OWASP ZAP baseline scan in CI
- Annual penetration test (external)

### 13.4 Critical Test Cases
List the 20 most important test scenarios. For each:
- Test ID and name
- Given / When / Then format
- Priority (P0 = must pass before any deploy)

### 13.5 Test Data Management
- Factories for every model
- Seeder sets: minimal (unit tests), realistic (feature tests), stress (load tests)
- No production data in test environments
- PII anonymisation rules if production snapshots are used

---

## PART 14 — PERFORMANCE SPECIFICATION

### 14.1 Performance Budgets (Non-Negotiable Targets)
| Metric | Target | Measurement Method |
|--------|--------|-------------------|
| Time to First Byte (TTFB) | < 200ms P95 | Synthetic monitoring |
| Largest Contentful Paint (LCP) | < 2.5s P75 | Real User Monitoring |
| API response time (read) | < 150ms P95 | APM |
| API response time (write) | < 400ms P95 | APM |
| DB query time | < 50ms P95 | Query log |
| Job processing time | < 30s P95 | Queue metrics |
| Page size (initial load) | < 500KB compressed | Bundle analysis |

### 14.2 Scaling Thresholds
Define when each component needs scaling:
- App servers: add instance when CPU > 70% sustained 5 min
- Database: add read replica when read latency > 100ms P95
- Redis: scale up when memory > 75%
- Queue workers: add worker when queue depth > 500 jobs sustained

### 14.3 Capacity Planning
Given the scale expectations from Section 0:
- Estimate requests/second at target user count
- Estimate database row counts at 1 year / 3 years
- Estimate storage requirements
- Estimate monthly infrastructure cost at each scale milestone

---

## PART 15 — COMPLIANCE & LEGAL

### 15.1 GDPR (If Applicable)
- Lawful basis for each type of data processing
- Privacy policy requirements (complete list of disclosures)
- Cookie consent implementation (categories: necessary / functional / analytics / marketing)
- Data subject rights implementation:
  - Right to access: `GET /api/v1/me/data-export` — full JSON export
  - Right to erasure: account deletion → anonymise PII, retain audit logs anonymised
  - Right to rectification: profile update endpoints
  - Right to portability: data export in machine-readable format
  - Right to object: marketing opt-out
- Data Protection Officer requirement (check threshold)
- Data Processing Agreements with third-party processors
- Cross-border transfer mechanisms (Standard Contractual Clauses)
- Data breach notification procedure (72-hour rule)

### 15.2 SOC 2 Type II (If B2B Target)
Five Trust Service Criteria requirements:
- **Security:** Access controls, encryption, vulnerability management
- **Availability:** Uptime targets, incident response, disaster recovery
- **Processing Integrity:** Complete and accurate processing, error handling
- **Confidentiality:** Data classification, access controls, encryption
- **Privacy:** GDPR/CCPA alignment, consent management

Evidence collection requirements:
- Access log retention (1 year minimum)
- Change management logs
- Vendor security assessments
- Annual employee security training records
- Penetration test results

### 15.3 PCI-DSS (If Handling Payments)
If using Stripe with hosted checkout:
- Scope: SAQ A (minimal) — Stripe handles all card data
- Requirements: HTTPS everywhere, no card data in logs, annual self-assessment
- If using Stripe Elements: SAQ A-EP — server receives card data briefly
- Prohibited: Never log or store raw card numbers anywhere

### 15.4 Accessibility (WCAG 2.1)
- Compliance target: Level AA minimum
- Testing tool: axe-core integrated in test suite
- Screen reader testing protocol (VoiceOver + NVDA)
- Keyboard navigation audit checklist
- Colour contrast audit requirement

---

## PART 16 — DEPLOYMENT & INFRASTRUCTURE

### 16.1 Environment Definitions
For each environment (local / development / staging / production):
- Purpose and who uses it
- Infrastructure spec
- Configuration differences from production
- Access controls (who can deploy, who can access)
- Data policy (no real user data in non-production)

### 16.2 CI/CD Pipeline (Complete)

```
TRIGGER: Pull Request opened / pushed to
│
├── [Parallel]
│   ├── Lint (PHP CS Fixer, ESLint, Prettier check)
│   ├── Static Analysis (PHPStan level 8 / TypeScript strict)
│   └── Dependency audit (composer audit, npm audit)
│
├── [Sequential on lint pass]
│   ├── Unit tests (with coverage report)
│   ├── Feature/integration tests
│   └── Coverage gate (fail if below threshold)
│
├── [On PR merge to main]
│   ├── Build Docker image (tagged with git SHA)
│   ├── Security scan (Trivy container scan)
│   ├── Push to container registry
│   ├── Deploy to staging (automated)
│   ├── Run database migrations on staging
│   ├── Run smoke tests (E2E critical paths)
│   └── Notify team: "Staging ready for QA"
│
└── [Manual approval → Production deploy]
    ├── Blue/green deploy (traffic shifted gradually)
    ├── Run migrations on production (with rollback ready)
    ├── Health check verification (all /health/* endpoints)
    ├── Synthetic monitor checks (10 min)
    └── [Auto-rollback if health checks fail]
```

Tools: GitHub Actions / GitLab CI — specify.
Deployment strategy: Blue/green (zero downtime) or rolling (simpler).

### 16.3 Docker Architecture

**Dockerfile (PHP application):**
- Base image: `php:8.2-fpm-alpine` (minimal, auditable)
- Multi-stage build (builder stage + runtime stage)
- Non-root user for the runtime stage
- All PHP extensions listed and justified
- OPcache configured for production
- php.ini production settings

**docker-compose.yml (development):**
Services: app (PHP-FPM), nginx, mysql, redis, worker, scheduler, mailhog
Volumes: source code mounted (hot reload), named volumes for DB/Redis data
Health checks on all stateful services

**docker-compose.prod.yml (production overrides):**
No source code mounts, environment variables from vault, resource limits defined,
restart policies, log driver configured

### 16.4 Kubernetes (If Applicable)
If Kubernetes is the target (team > 10, high availability required):
- Deployments for: app, worker, scheduler
- Services and Ingress definitions
- ConfigMaps and Secrets (sealed secrets)
- HorizontalPodAutoscaler rules
- PodDisruptionBudget for zero-downtime deploys
- ResourceQuotas and LimitRanges
- Liveness and readiness probes

### 16.5 Disaster Recovery
- **RTO** (Recovery Time Objective): maximum acceptable downtime
- **RPO** (Recovery Point Objective): maximum acceptable data loss window
- Database backup: automated, encrypted, tested restore quarterly
- Point-in-time recovery capability
- Cross-region backup replication
- Runbook: step-by-step recovery procedure from full database loss
- Runbook: step-by-step recovery procedure from full infrastructure loss
- Failover procedure for each critical dependency

---

## PART 17 — SLA, SLO & ERROR BUDGETS

### 17.1 Service Level Objectives (SLOs)
| Indicator | Objective | Measurement Window |
|-----------|-----------|-------------------|
| Availability | 99.9% (≤ 8.7h downtime/year) | Rolling 30 days |
| API latency P95 | < 300ms | Rolling 7 days |
| Error rate | < 0.5% of requests | Rolling 24 hours |
| Job success rate | > 99% | Rolling 24 hours |

### 17.2 Error Budgets
- Calculate error budget per SLO per month
- Define what happens when error budget is exhausted:
  - Feature work paused
  - Reliability improvements prioritised
  - Stakeholder communication required

### 17.3 SLA (Customer-Facing)
- Published uptime commitment per pricing tier
- Credits/remedies for SLA breaches
- Excluded downtime (scheduled maintenance, force majeure)

---

## PART 18 — COST ESTIMATION

### 18.1 Infrastructure Costs (Monthly)
Estimate at three scales: Launch (100 users) / Growth (10K users) / Scale (100K users)

| Component | Launch | Growth | Scale |
|-----------|--------|--------|-------|
| App servers | | | |
| Database (primary + replica) | | | |
| Redis | | | |
| Object storage | | | |
| CDN | | | |
| Load balancer | | | |
| Monitoring stack | | | |
| **Total infrastructure** | | | |

### 18.2 Third-Party Service Costs
| Service | Pricing Model | Est. Monthly at Each Scale |
|---------|--------------|---------------------------|
| Error tracking (Sentry) | Events/month | |
| APM (Datadog) | Hosts | |
| Email (SendGrid/SES) | Emails sent | |
| Stripe | % of revenue | |

### 18.3 Unit Economics
- Cost per active user (infrastructure + services ÷ users)
- Gross margin per subscription tier
- Infrastructure cost as % of MRR (target: < 15%)

---

## PART 19 — TEAM & OPERATING MODEL

### 19.1 Recommended Team Structure
For the specified team size, define:
- Roles required (with responsibilities)
- Who owns what (engineering / product / design / DevOps)
- Decision-making process (RFC process for architectural decisions)
- On-call rotation requirements

### 19.2 Development Workflow
- Branching strategy (GitHub Flow / GitFlow — recommend and justify)
- PR requirements (reviewer count, test pass, lint pass, changelog entry)
- PR template (define required sections)
- Commit message convention (Conventional Commits)
- Changelog format (Keep a Changelog)
- Release cadence (weekly / biweekly sprints)
- Feature flag usage (how features are developed behind flags before release)

### 19.3 Code Quality Standards
- Language-specific style guide
- Linter configuration (PHP CS Fixer / ESLint ruleset)
- Static analysis level (PHPStan level 8 / TypeScript strict)
- Complexity limits (cyclomatic complexity max per function)
- Function/method length limits
- Mandatory code review for all production code
- Architecture decision records (ADR) for major decisions

---

## PART 20 — DOCUMENTATION DELIVERABLES

### 20.1 README.md
Complete project README:
- What it is, what it does (2 paragraphs)
- Architecture overview (3 bullets)
- Prerequisites (exact versions)
- Quick start (copy-paste commands to go from zero to running in < 5 min)
- Full environment variables reference (every variable: name, type, required, description, example)
- Available `make` commands
- Testing commands
- Deployment instructions
- Troubleshooting (top 10 common errors + solutions)
- Contributing guide link
- License

### 20.2 API Reference (Complete)
For every endpoint in Part 10, the full documentation:
- Method, path, description
- Authentication requirement
- Request headers
- Request body (JSON Schema + annotated example)
- Response (success + all error cases, with examples)
- Example curl command (copy-paste ready)

### 20.3 Architecture Decision Records (ADRs)
For each major technical decision, write an ADR:
```
Title: ADR-001: Use Redis for session storage
Date: [date]
Status: Accepted
Context: [why this decision was needed]
Decision: [what was decided]
Rationale: [why this was the right choice]
Alternatives considered: [what else was evaluated]
Consequences: [what this means for the project]
```

Produce ADRs for minimum 10 key decisions.

### 20.4 Runbooks
Operational runbooks for:
- Deploying a new release
- Rolling back a failed deployment
- Scaling up application servers
- Responding to a database issue
- Responding to a security incident
- Onboarding a new engineer (day 1 setup guide)
- Rotating secrets and credentials

### 20.5 Sequential Build Prompts
Break the entire build into numbered, sequential prompts for LLM-assisted development.
Each prompt:
- Builds only on what previous prompts produced
- Is scoped to one complete, testable concern
- Specifies exact files to create
- Includes acceptance test to verify it works
- Is independently runnable in isolation

Format each prompt as:
```
═══════════════════════════════════════════
PROMPT [N]: [Title]
═══════════════════════════════════════════
Context: What has been built so far
Objective: What this prompt builds
Files to create/modify:
  - path/to/file.php (create) — [purpose]
  - path/to/existing.php (modify) — [what changes]
Dependencies: Packages/services needed
Acceptance test:
  1. Run: [command]
  2. Expected: [specific output or behaviour]
  3. Verify: [how to confirm it worked]
Notes: [edge cases, gotchas, decisions made]
═══════════════════════════════════════════
```

Generate prompts for the entire application — do not stop until the full build is covered.

---

## OUTPUT FORMAT REQUIREMENTS

- Single continuous document with clear H1/H2/H3 hierarchy
- Every table formatted in Markdown table syntax
- All code in fenced code blocks with language specified
- All diagrams in ASCII art within fenced code blocks
- Numbered lists for sequential steps, bullet lists for non-ordered items
- **Bold** for decisions, critical information, and key terms
- Total length: 15,000–25,000 words — do not truncate
- If context limit is reached: output "--- DOCUMENT CONTINUES: REQUEST PART [N] ---"
  and complete the remaining parts when prompted

The finished document must meet this bar:
**A senior engineer who has never heard of this product reads the PRD on Monday morning
and begins writing production code by Monday afternoon.**

Begin immediately with the Executive Summary, then proceed through all 20 parts in order.

---

---

# ============================================================
# REFERENCE ARCHITECTURE: INSTABALANCEPRO (BENCHMARK)
# ============================================================

The following excerpts from InstaBAlancePRO illustrate expected output quality.
Use as a quality benchmark — your output should meet or exceed this depth.

## Reference: Scoring Algorithm (Part 5 Business Logic)

```
ALGORITHM: Unfollow Priority Score
Output range: 0 (keep) to 100 (safe to unfollow)

Formula:
  score = (inactivity_score × 0.40)
        + (engagement_score × 0.35)
        + (ratio_score     × 0.15)
        + (age_score       × 0.10)
        + account_type_modifier

Where each sub-score = clamp(normalise(raw_value, min, max) × 100, 0, 100)

  inactivity_score  = normalise(days_since_last_post, 0, 365)
  engagement_score  = normalise(1 - engagement_rate, 0, 1)
  ratio_score       = normalise(1 / max(follower_ratio, 0.01), 0, 10)
  age_score         = normalise(account_age_days, 0, 1825)

account_type_modifier:
  verified  → -50  (high social capital, don't unfollow)
  creator   → -40
  business  → -30
  regular   →  0

final_score = clamp(score + modifier, 0, 100)

Category assignment:
  0–25   → Safe
  26–50  → Caution
  51–75  → High Priority
  76–100 → Critical
  Override: if days_since_last_post > 90 → "Inactive 90d+"
  Override: if engagement_rate < 0.01    → "Low Engagement"
```

## Reference: Database Table (Part 9 Schema)

```sql
/**
 * following — Instagram accounts the user is following
 * Core data table. Populated by sync. Scored by ScoringService.
 * Never hard-deleted — unfollowed_at marks removal from Instagram.
 */
CREATE TABLE following (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    user_id                  INT NOT NULL,
    instagram_account_id     VARCHAR(64) NOT NULL,
    username                 VARCHAR(128) NOT NULL,
    display_name             VARCHAR(256),
    profile_picture_url      TEXT,
    follower_count           INT DEFAULT 0,
    following_count          INT DEFAULT 0,
    is_verified              BOOLEAN DEFAULT FALSE,
    account_type             ENUM('regular','business','creator','verified') DEFAULT 'regular',
    unfollow_priority_score  INT DEFAULT 0,        -- 0–100, higher = safer to unfollow
    category                 VARCHAR(64),           -- Human-readable score category
    is_whitelisted           BOOLEAN DEFAULT FALSE, -- Never suggest for unfollowing
    kanban_status            ENUM('review','queued','unfollowed','not_now') DEFAULT 'review',
    followed_back            BOOLEAN DEFAULT FALSE, -- They follow you back?
    unfollowed_at            DATETIME,              -- When you unfollowed them
    last_synced_at           DATETIME,
    created_at               DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at               DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_user_account (user_id, instagram_account_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_score     (user_id, unfollow_priority_score DESC),
    INDEX idx_user_category  (user_id, category),
    INDEX idx_user_kanban    (user_id, kanban_status),
    INDEX idx_unfollowed     (user_id, unfollowed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

*Enterprise Build Prompt v2.0*
*Derived from InstaBAlancePRO Architecture + Enterprise Readiness Assessment*
*Repository: github.com/CREEDCONSULT/INSTABALANCEPRO*
