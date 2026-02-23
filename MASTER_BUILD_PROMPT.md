# MASTER BUILD PROMPT — Full-Stack App/Website PRD Generator
**Version:** 1.0 | **Framework:** InstaBAlancePRO Reference Architecture
**Purpose:** Feed this prompt to any LLM to generate a complete, ready-to-build PRD from a raw idea.

---

## HOW TO USE THIS TEMPLATE

1. Fill in the `[BRACKETED FIELDS]` in **Section 0** with your idea.
2. Leave everything else as-is — the LLM will use it as instruction.
3. Paste the entire document into your LLM of choice (Claude, GPT-4, etc.).
4. The LLM will output a complete PRD covering all sections below.
5. Iterate on any section by asking follow-up questions.

---

---

# ============================================================
# SECTION 0 — YOUR IDEA INPUT (FILL THIS IN)
# ============================================================

```
APP NAME:        [Your app name]
ONE-LINE PITCH:  [What does it do in one sentence?]
PROBLEM:         [What specific problem does it solve?]
TARGET USER:     [Who is this for? Be specific — age, profession, behaviour]
CORE ACTION:     [What is the ONE primary thing a user does in this app?]
MONETISATION:    [How does it make money? Subscriptions / ads / one-time / freemium]
PLATFORM:        [Web app / mobile / desktop / all]
INSPIRATION:     [2-3 apps this is similar to or inspired by]
DIFFERENTIATOR:  [What makes this different or better than existing solutions?]
SCALE TARGET:    [Personal project / startup MVP / enterprise SaaS]
TECH PREFERENCE: [Any preferred language/framework, or leave blank for recommendation]
```

---

---

# ============================================================
# MASTER PROMPT — INSTRUCTIONS TO THE LLM
# ============================================================

You are a **senior product architect, systems engineer, and business strategist**. Your task is to take the app idea described in Section 0 above and produce a **complete, production-ready Product Requirements Document (PRD)**.

This is not a surface-level overview. Every section must be **detailed, specific, and actionable**. A software development team should be able to pick up this document and begin building immediately, with no ambiguity.

Work through each section below **sequentially and exhaustively**. Do not skip sections. Do not summarise where depth is required. Reference the app idea from Section 0 throughout.

---

## PART 1 — CONCEPT ANALYSIS & VALIDATION

### 1.1 Problem Statement (Deep Analysis)
- Articulate the core problem in precise terms.
- Who experiences this problem? How often? How painfully?
- What do people currently do to solve it (workarounds, competitors, manual effort)?
- What is the cost of the problem — time, money, frustration, missed opportunity?
- Is this a *vitamin* (nice to have) or an *aspirin* (must have)?

### 1.2 Solution Definition
- Define the proposed solution in one paragraph.
- How does the app eliminate or significantly reduce the problem?
- What is the core value proposition — what does the user *gain* by using it?
- What is the app's primary "magic moment" — the moment a new user first feels real value?

### 1.3 Assumptions & Risks
- List 5–8 key assumptions this idea depends on being true.
- For each assumption, assess: likely / uncertain / risky.
- List 3–5 critical risks that could kill the product.
- What would need to be validated first before full build? (MVP hypothesis)

---

## PART 2 — MARKET RESEARCH & COMPETITIVE ANALYSIS

### 2.1 Market Sizing
- Define the Total Addressable Market (TAM): the broadest relevant market.
- Define the Serviceable Addressable Market (SAM): the segment you can realistically reach.
- Define the Serviceable Obtainable Market (SOM): realistic capture in year 1–2.
- Is this market growing, stable, or declining? What are the tailwinds?

### 2.2 Competitive Landscape
Identify and analyse **5–8 direct and indirect competitors**. For each:
- Name and one-line description
- Pricing model
- Key strengths (what they do well)
- Key weaknesses (where they fall short)
- Estimated user base / market position

Present this as a comparison table where appropriate.

### 2.3 Competitive Positioning
- Where does this app sit in the market relative to competitors?
- Complete this matrix: [Price axis: Low ↔ High] × [Feature axis: Simple ↔ Complex]
- What is the **defensible moat** — what makes this hard to copy once established?
- Blue ocean opportunities: underserved niches or combinations of features no one offers.

### 2.4 Go-To-Market Strategy
- Primary acquisition channel (SEO, paid ads, product-led growth, community, partnerships)
- Secondary channels
- Launch strategy (waitlist / ProductHunt / beta program / direct outreach)
- Estimated customer acquisition cost (CAC) range
- Target payback period on CAC

---

## PART 3 — USER RESEARCH & PERSONAS

### 3.1 Primary User Persona
Define the PRIMARY user in full detail:
- **Name & archetype** (give them a name)
- **Demographics:** Age, occupation, income level, location
- **Psychographics:** Goals, values, frustrations, motivations
- **Behaviour patterns:** How they currently solve the problem, tools they use, time of day, device
- **Technical sophistication:** Tech-savvy / average / non-technical
- **Quote:** A sentence this person might actually say about their problem
- **Job to be done:** When [situation], I want to [motivation], so I can [outcome].

### 3.2 Secondary User Persona(s)
Define 1–2 secondary personas using the same structure. These are users who matter but are not the primary focus.

### 3.3 Anti-Persona
Define explicitly who this app is **NOT** for and why. This sharpens product focus.

### 3.4 User Journey Map
Map the full journey of the primary persona:

```
AWARENESS → CONSIDERATION → SIGN UP → ONBOARDING → FIRST VALUE →
REGULAR USE → UPGRADE/RETENTION → ADVOCACY / CHURN
```

For each stage:
- What is the user thinking/feeling?
- What action do they take?
- What is the touchpoint (email, UI, notification)?
- What friction or drop-off risk exists?
- What is the success metric for this stage?

---

## PART 4 — PRODUCT DEFINITION

### 4.1 Core Feature Set (MVP)
List every feature required for a **Minimum Viable Product** — the smallest version that delivers the core value proposition.

For each feature:
- **Feature name**
- **User story:** As a [persona], I want to [action], so that [outcome].
- **Acceptance criteria:** Specific, testable conditions that define "done".
- **Priority:** P0 (launch blocker) / P1 (important) / P2 (nice to have)
- **Complexity estimate:** Low / Medium / High

### 4.2 Full Feature Set (V1.0)
Expand beyond MVP. List all features planned for the full V1.0 release, using the same structure.

### 4.3 Future Roadmap (V2.0+)
List 8–12 features or capabilities planned for future versions. Group by:
- Short-term (3–6 months post launch)
- Medium-term (6–12 months)
- Long-term / strategic (12+ months)

### 4.4 Feature Exclusions (Explicitly Out of Scope)
List 5–8 features that are explicitly NOT being built and why. This prevents scope creep.

### 4.5 Business Rules & Logic
Define every business rule that governs how the app behaves. Be exhaustive. Examples:
- Quota limits per subscription tier
- What triggers an upgrade prompt
- How data is calculated, scored, or ranked
- Edge cases in user flows (what happens if X fails, or Y is missing)
- Rate limiting rules
- Refund / cancellation policies reflected in the system

---

## PART 5 — UI/UX DESIGN SPECIFICATION

### 5.1 Design Principles
List 4–6 core design principles that will guide every UI/UX decision for this app. Each principle should have:
- A name
- A one-line definition
- A practical example of how it applies

### 5.2 Information Architecture
Define the full site/app structure as a hierarchy:

```
[App Name]
├── Public / Marketing
│   ├── Home
│   ├── Pricing
│   ├── Features
│   └── About
├── Authentication
│   ├── Register
│   ├── Login
│   ├── Forgot Password
│   └── 2FA Verification
└── Application (Authenticated)
    ├── Dashboard
    ├── [Core Feature 1]
    ├── [Core Feature 2]
    ├── Settings
    └── Billing
```

Extend this fully for your specific app. Every page/screen must appear here.

### 5.3 Page-by-Page UI Specification
For each major page/screen, define:
- **Purpose:** What is the user trying to accomplish here?
- **Key components:** Exact UI elements visible on this screen.
- **Primary action:** The single most important action on this screen.
- **Data displayed:** What data is shown? Where does it come from?
- **Empty state:** What does the user see if there is no data yet?
- **Error states:** What errors can occur and how are they shown?
- **ASCII wireframe:** A rough text-based layout of the screen.

Example for Dashboard:
```
┌─────────────────────────────────────────────────┐
│  [Logo]    Dashboard    [Notifications] [Avatar] │
├──────────┬──────────────────────────────────────┤
│          │  Welcome back, [Name]                │
│  NAV     │  ┌────────┐ ┌────────┐ ┌────────┐   │
│  • Home  │  │ Stat 1 │ │ Stat 2 │ │ Stat 3 │   │
│  • ...   │  └────────┘ └────────┘ └────────┘   │
│  • ...   │                                      │
│          │  [Recent Activity Feed]              │
│          │  [Quick Action Buttons]              │
└──────────┴──────────────────────────────────────┘
```

### 5.4 Navigation & Wayfinding
- Define the primary navigation structure (sidebar / topbar / bottom nav / tabs).
- Define breadcrumb rules (when shown, what depth).
- Define mobile navigation pattern.
- Active state and current location indicators.

### 5.5 Design System Specification
Define the visual design language:

**Typography:**
- Heading font (name, weights used)
- Body font (name, weights used)
- Monospace font (if applicable — for data/code)
- Type scale (h1 → h6, body, small, caption — sizes in px/rem)

**Colour Palette:**
- Primary brand colour (hex)
- Secondary colour (hex)
- Accent / CTA colour (hex)
- Success / Warning / Error / Info states (hex each)
- Background, surface, and border colours (hex each)
- Dark mode variants (if applicable)

**Spacing System:**
- Base unit (4px or 8px)
- Spacing scale (4, 8, 12, 16, 24, 32, 48, 64...)

**Component Library:**
- List every UI component needed: buttons, inputs, modals, toasts, tables, cards, badges, tabs, dropdowns, etc.
- For each: variants, states (default, hover, active, disabled, error)

**Motion & Animation:**
- Define transitions (duration, easing function)
- Loading states (skeleton screens vs spinners)
- Micro-interaction guidelines

### 5.6 Responsive Behaviour
- Breakpoints defined (mobile / tablet / desktop / wide)
- Layout changes per breakpoint for each major page
- Touch-specific interactions on mobile

### 5.7 Accessibility Requirements
- WCAG compliance target (2.1 AA minimum recommended)
- Keyboard navigation requirements
- Screen reader compatibility notes
- Colour contrast requirements
- Focus indicator standards

---

## PART 6 — TECHNICAL ARCHITECTURE

### 6.1 Technology Stack Selection
For each layer of the stack, provide:
- **Chosen technology**
- **Version**
- **Rationale** (why this over alternatives)
- **Key alternatives considered and why rejected**

Cover:
- Backend language & runtime
- Web framework (or custom MVC)
- Database (primary + any secondary)
- Caching layer
- Job/queue system
- Search (if applicable)
- File storage
- Email service
- Frontend framework / approach
- CSS framework
- Build tools / bundler
- Package manager
- Version control
- Containerisation
- CI/CD
- Hosting / deployment target
- Monitoring & error tracking
- Analytics

### 6.2 System Architecture Overview
Describe the overall architecture pattern:
- Monolith / Modular monolith / Microservices / Serverless — which and why
- Component diagram showing how all system parts connect
- External services and third-party integrations map

Provide a full ASCII architecture diagram.

### 6.3 Application Layers
Define each layer of the application and its responsibilities:

**Presentation Layer (Views/Templates)**
- Rendering approach (server-side / client-side / hybrid)
- Templating engine or framework
- State management approach
- Real-time updates approach (WebSockets / SSE / polling / htmx)

**Application Layer (Controllers/Routes)**
- Routing strategy and library
- Middleware pipeline (list each middleware and its purpose)
- Request/response lifecycle
- Input validation approach
- Error handling strategy

**Business Logic Layer (Services)**
- List every Service class with its responsibility
- How services interact with each other
- How services interact with external APIs

**Data Access Layer (Models/ORM)**
- ORM or raw queries — which and why
- Repository pattern (if used)
- Connection pooling strategy
- Query optimisation approach

**Infrastructure Layer**
- Environment configuration management
- Secrets management
- Logging strategy

### 6.4 Directory Structure
Define the complete, annotated directory structure for the project. Every directory and key file should be explained.

```
PROJECT_ROOT/
├── public/              # Web root
├── src/                 # Application source
│   ├── Controllers/
│   ├── Services/
│   ├── Models/
│   ├── Middleware/
│   └── Views/
├── config/
├── database/
├── tests/
└── ...
```

### 6.5 Request Lifecycle
Trace a complete HTTP request from browser to response, step by step. Include:
- DNS / TCP / TLS (brief)
- Web server handling
- Application bootstrap sequence
- Routing and middleware execution
- Controller → Service → Database flow
- Response construction and rendering
- Client-side rendering / hydration (if applicable)

### 6.6 Authentication & Session Architecture
- Authentication method (session-based / JWT / OAuth)
- Session storage (cookie / database / Redis)
- Token lifecycle (creation, refresh, expiry, revocation)
- Multi-device session handling
- "Remember me" implementation
- OAuth provider integrations (list each with flow)
- 2FA implementation details
- Password reset flow (step by step)

---

## PART 7 — DATABASE DESIGN

### 7.1 Database Schema (Complete DDL)
Write the **full SQL schema** for every table. For each table include:
- Complete `CREATE TABLE` statement
- Every column with type, constraints, and default
- All indexes (primary, unique, composite, foreign key)
- Comments explaining non-obvious columns
- Engine and charset declaration

Group tables by domain (auth, core features, billing, audit, etc.).

### 7.2 Entity Relationship Diagram
Describe the relationships between all tables:
- One-to-one relationships
- One-to-many relationships
- Many-to-many relationships (with junction tables)

Provide as ASCII ERD or structured list.

### 7.3 Data Dictionary
For every table, provide a data dictionary:

| Column | Type | Nullable | Default | Description |
|--------|------|----------|---------|-------------|

### 7.4 Query Patterns & Indexes
Identify the 10–15 most frequent or performance-critical queries. For each:
- The query in SQL
- Which indexes support it
- Estimated execution pattern
- Any denormalisation decisions made to support it

### 7.5 Data Lifecycle & Retention
- What data is soft-deleted vs hard-deleted?
- Data retention periods (for audit logs, activity, etc.)
- GDPR/data portability considerations (export requirements)
- Backup strategy

---

## PART 8 — API DESIGN

### 8.1 Internal API Routes
List every route in the application. For each:

| Method | Path | Auth Required | Middleware | Controller@Action | Description |
|--------|------|--------------|------------|-------------------|-------------|

Group by: Public, Auth-required, Admin, Webhooks, AJAX/API endpoints.

### 8.2 External API Integrations
For each third-party API integration:
- **API name and version**
- **Purpose** in the application
- **Authentication method** (API key / OAuth / webhook secret)
- **Endpoints used** (list each with method, path, purpose)
- **Rate limits** and how they are handled
- **Error handling** (retry logic, fallback behaviour)
- **Data mapping** (API response fields → database columns)
- **Webhook events** handled (if applicable)

### 8.3 Webhook Architecture (if applicable)
- Incoming webhook endpoints
- Signature validation method
- Idempotency handling (duplicate event protection)
- Retry and failure handling
- Event logging

---

## PART 9 — SECURITY ARCHITECTURE

### 9.1 Threat Model
Identify the top 10 security threats relevant to this application using the OWASP Top 10 as a framework. For each threat:
- Description of the specific risk for this app
- Mitigation implemented
- Residual risk level (Low / Medium / High)

### 9.2 Authentication Security
- Password policy (minimum length, complexity, breach checking)
- Hashing algorithm and parameters
- Brute force protection (rate limiting, lockout, CAPTCHA)
- Secure password reset flow
- Session fixation prevention
- Concurrent session handling

### 9.3 Data Security
- Encryption at rest (which fields, which algorithm)
- Encryption in transit (TLS version, HSTS)
- Sensitive data handling (PII, payment data, tokens)
- What is NEVER logged (passwords, tokens, card numbers)
- PCI-DSS compliance considerations (if taking payments)
- GDPR compliance requirements (if applicable)

### 9.4 Application Security Controls
- CSRF protection mechanism
- XSS prevention strategy
- SQL injection prevention
- Input validation rules (server-side — client-side is not enough)
- Output encoding rules
- File upload security (if applicable)
- Security headers (complete list with values)
- Content Security Policy (CSP) definition

### 9.5 Infrastructure Security
- Environment variable management
- Secret rotation strategy
- Dependency vulnerability scanning
- Docker security hardening (if using containers)
- Access control (who can deploy, who has DB access)

---

## PART 10 — BUSINESS LOGIC SPECIFICATION

### 10.1 Core Algorithm(s)
If the app has a core algorithm (scoring, matching, ranking, recommending, calculating):
- Define it in precise mathematical or pseudocode terms
- Explain each input variable
- Explain each weighting or factor
- Define the output and its range
- Provide worked examples with sample data
- Edge cases and how they are handled

### 10.2 Subscription / Pricing Logic
- Define every subscription tier with exact feature inclusions
- Quota enforcement rules (hard limits vs soft warnings)
- Upgrade/downgrade flow (proration, immediate vs end of period)
- Trial period rules
- Cancellation and grace period rules
- What happens to user data when subscription lapses

### 10.3 Notification & Communication Logic
- All emails the system sends (trigger, recipient, content summary)
- In-app notification rules (what triggers, how displayed, how dismissed)
- Push notification rules (if mobile)
- Email preferences / opt-out handling

### 10.4 Data Processing Workflows
For each background or asynchronous workflow:
- Trigger (user action / cron / event)
- Step-by-step process
- Error handling and retry logic
- Success and failure notifications
- Performance expectations (timeout, expected duration)

### 10.5 Rate Limiting & Quota Rules
Define every rate limit in the system:
- Endpoint-level rate limits (requests per minute/hour)
- Feature-level quotas (actions per day/month per tier)
- Global system limits
- How limits are tracked and enforced (Redis counters, DB columns)
- User-facing messaging when limits are hit

---

## PART 11 — PERFORMANCE REQUIREMENTS

### 11.1 Performance Targets
Define specific, measurable performance targets:
- Page load time (target: < X ms for P95)
- API response time (target: < X ms for P95)
- Database query time (target: < X ms for P95)
- Concurrent users supported at launch
- Target uptime SLA (99.9% = 8.7 hours downtime/year)

### 11.2 Caching Strategy
For each type of data that should be cached:
- Cache key structure
- TTL (time to live)
- Invalidation strategy (event-driven / TTL expiry / manual)
- Cache storage (in-memory / Redis / CDN)

### 11.3 Database Optimisation Plan
- Connection pooling configuration
- Slow query threshold and monitoring
- Index strategy (beyond what's in schema)
- Read replicas (when to introduce)
- Query result caching

---

## PART 12 — TESTING STRATEGY

### 12.1 Test Coverage Plan
Define the testing approach for each layer:
- **Unit tests:** What classes/functions need unit tests? Target coverage %?
- **Integration tests:** What service-to-service interactions need testing?
- **End-to-end tests:** What critical user journeys must be tested end-to-end?
- **Performance tests:** What load tests are needed pre-launch?
- **Security tests:** OWASP ZAP scan, dependency audit cadence?

### 12.2 Critical Test Scenarios
List the 15–20 most important test cases. For each:
- Test name
- Given / When / Then format
- Expected result
- Priority (P0 must pass before deploy / P1 should pass / P2 regression)

### 12.3 Test Data Strategy
- Seed data required for testing
- Anonymisation rules for production data in test environments
- Test account credentials policy

---

## PART 13 — DEPLOYMENT & INFRASTRUCTURE

### 13.1 Environment Strategy
Define each environment:
- **Local:** Developer machines — setup steps, tools required
- **Staging:** Pre-production — mirrors production, used for QA
- **Production:** Live environment — all config, no debug

### 13.2 Deployment Architecture
- Hosting provider and rationale
- Server/container specification (CPU, RAM, storage)
- Load balancing (when needed, which approach)
- CDN setup for static assets
- Database hosting (same host / managed service)
- File storage (local / S3-compatible)

### 13.3 CI/CD Pipeline
Define the full pipeline:
```
git push → [trigger] → Build → Test → Security scan →
Staging deploy → Smoke test → Production deploy → Health check
```
- What tool (GitHub Actions / GitLab CI / other)
- Each stage and what it does
- Deployment strategy (blue/green / rolling / canary)
- Rollback procedure

### 13.4 Docker Configuration
- `Dockerfile` — base image, installed extensions, configuration
- `docker-compose.yml` — services, volumes, networks, health checks
- Environment variable injection method
- Production vs development compose differences

### 13.5 Monitoring & Alerting
- Error tracking (Sentry / Bugsnag — events tracked)
- Application Performance Monitoring (New Relic / Datadog)
- Uptime monitoring (pingdom / betterstack)
- Log aggregation (where logs go, retention period)
- Alert thresholds and who gets paged for what

---

## PART 14 — LAUNCH CHECKLIST

### 14.1 Pre-Launch Technical Checklist
Generate a complete checklist covering:
- [ ] Security hardening (HTTPS, headers, env vars secured)
- [ ] Performance (load tested, caching configured)
- [ ] Database (backups automated, indexes verified)
- [ ] Monitoring (error tracking, uptime monitoring, alerts)
- [ ] Compliance (privacy policy, cookie consent, GDPR)
- [ ] Email deliverability (SPF, DKIM, DMARC configured)
- [ ] Error pages (custom 404, 500 pages)
- [ ] All placeholder copy replaced with real content
- [ ] All test/debug code removed
- [ ] All environment variables set in production

### 14.2 Post-Launch Monitoring Plan
- What metrics to watch in the first 24 hours
- What metrics to watch in the first 7 days
- Definition of a successful launch
- Definition of a failed launch (rollback trigger)

---

## PART 15 — METRICS & SUCCESS DEFINITION

### 15.1 Business Metrics (KPIs)
Define the North Star Metric — the single metric that best captures value delivered to users.

Then define supporting metrics:
- **Acquisition:** Signups/day, conversion rate from visitor to user
- **Activation:** % of users who reach the "magic moment" in first session
- **Retention:** Day 1 / Day 7 / Day 30 retention rates
- **Revenue:** MRR, ARPU, churn rate, LTV, CAC, LTV:CAC ratio
- **Referral:** NPS score, viral coefficient

For each metric:
- How is it measured?
- What is the target at 30 / 90 / 180 days post-launch?
- What dashboard or tool tracks it?

### 15.2 Technical Metrics (Operational KPIs)
- Uptime %
- Error rate (errors per 1,000 requests)
- Average response time
- P95 response time
- Database query time (average and P95)
- Failed background job rate
- API integration success rate

---

## PART 16 — DOCUMENTATION DELIVERABLES

Upon completing all sections above, generate the following documents:

### 16.1 README.md
A complete project README including:
- Project description and key features
- Prerequisites and system requirements
- Local setup guide (step by step)
- Environment variables reference (every variable explained)
- Available commands (start, test, build, deploy)
- Project structure overview
- Contributing guidelines

### 16.2 API Reference
For every route defined in Part 8, produce full API documentation:
- Method, path, description
- Request headers required
- Request body schema (with types and validation rules)
- Response body schema (success and error)
- Example request (curl)
- Example success response (JSON)
- Example error responses

### 16.3 Build Prompt Sequence
Break the full build into **numbered sequential prompts** that can be fed to an LLM one at a time to generate the actual code. Each prompt should:
- Reference only what was built in previous prompts
- Produce a specific, complete, runnable piece of the application
- Include exactly what files to create and what each should contain
- Specify test instructions to verify the prompt output works

Format:
```
PROMPT 1: [Title]
Objective: ...
Files to create: ...
Dependencies: ...
Acceptance test: ...
---
PROMPT 2: ...
```

Generate enough prompts to build the entire application from scratch.

### 16.4 Environment Setup Guide
A dedicated guide covering:
- Exact software versions required
- Step-by-step installation for Windows / macOS / Linux
- Database setup and schema import
- First-run checklist
- Common setup errors and fixes

---

## OUTPUT FORMAT INSTRUCTIONS

Structure your response as a **single continuous document** with:
- Clear H1 / H2 / H3 headings matching the sections above
- Tables for comparisons and specifications
- Code blocks for SQL, code, commands, and ASCII diagrams
- Numbered lists for sequential steps
- Checkbox lists for checklists
- Bold text for key terms, decisions, and critical information

The document should be **complete enough that a developer with 2 years of experience could build the entire application from it without asking a single clarifying question.**

Begin with a one-page **Executive Summary** that covers:
- App name and tagline
- Core problem and solution
- Target market and size
- Revenue model and projections
- Tech stack summary
- Build timeline estimate

Then proceed through every section in order.

**Total expected output length:** 8,000–15,000 words. Do not truncate. Do not summarise where detail is requested. If you run out of context, state "CONTINUED — request next section" and continue when prompted.

---

---

# ============================================================
# REFERENCE: EXAMPLE OUTPUT QUALITY BENCHMARK
# ============================================================

The following is a reference example drawn from a real build (InstaBAlancePRO)
to illustrate the expected depth and quality of output for each section.

## Example: Scoring Algorithm Specification (Part 10.1)

```
Algorithm: Unfollow Priority Scoring

Purpose:
  Rank followed Instagram accounts by their "safe to unfollow" score.
  Higher scores = accounts that are lower risk / less value to keep following.

Inputs per account:
  - days_since_last_post    (integer, 0–3650)
  - engagement_rate         (float, 0.0–1.0)
  - follower_following_ratio (float, 0.0–500.0)
  - account_age_days        (integer, 0–5000)
  - account_type            (enum: regular | business | creator | verified)

Scoring Formula:
  raw_score =
    (normalise(days_since_last_post, 0, 365) × 40) +
    (normalise(1 - engagement_rate, 0, 1) × 35) +
    (normalise(1 / max(ratio, 0.01), 0, 10) × 15) +
    (normalise(account_age_days, 0, 1825) × 10)

  where normalise(value, min, max) = clamp((value - min) / (max - min), 0, 1) × 100

Type modifier (subtracted from raw_score):
  verified  → -50
  creator   → -40
  business  → -30
  regular   → 0

final_score = clamp(raw_score + modifier, 0, 100)

Output categories:
  0–25   → "Safe"           (probably fine to keep)
  26–50  → "Caution"        (review manually)
  51–75  → "High Priority"  (likely safe to unfollow)
  76–100 → "Critical"       (strong unfollow candidate)
  Special overrides:
    days_since_last_post > 90  → "Inactive 90d+"
    engagement_rate < 0.01     → "Low Engagement"

Worked Example:
  Account: @example_user
  - Last post: 120 days ago     → normalised = 0.33 → 0.33 × 40 = 13.2
  - Engagement rate: 0.02       → 1-0.02=0.98 → 0.98 × 35 = 34.3
  - Ratio: 0.5 (500 following, 250 followers) → 1/0.5=2.0, normalised=0.2 → 0.2×15=3.0
  - Account age: 730 days       → 730/1825=0.4 → 0.4×10=4.0
  - Type: regular               → modifier = 0
  raw_score = 13.2 + 34.3 + 3.0 + 4.0 = 54.5
  final_score = 54.5 → Category: "High Priority"
  Override check: 120 days > 90 → final category: "Inactive 90d+"
```

## Example: Database Table Specification (Part 7.1)

```sql
/**
 * users — Core SaaS user accounts
 * Central table. All user data and subscription state lives here.
 * Soft-deleted via deleted_at to support account recovery.
 */
CREATE TABLE users (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    email                   VARCHAR(255) UNIQUE NOT NULL,
    password_hash           VARCHAR(255) NOT NULL,        -- Argon2ID
    display_name            VARCHAR(128),
    subscription_tier       ENUM('free','pro','premium') DEFAULT 'free',
    is_active               BOOLEAN DEFAULT TRUE,
    is_admin                BOOLEAN DEFAULT FALSE,
    email_verified_at       DATETIME,                     -- NULL = unverified
    two_fa_enabled          BOOLEAN DEFAULT FALSE,
    two_fa_secret           VARCHAR(32),                  -- TOTP base32 secret
    recovery_codes          TEXT,                         -- JSON array, 8 codes
    failed_login_attempts   INT DEFAULT 0,
    locked_until            DATETIME,                     -- NULL = not locked
    last_login_at           DATETIME,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at              DATETIME,                     -- NULL = active account

    INDEX idx_email              (email),
    INDEX idx_subscription_tier  (subscription_tier),
    INDEX idx_is_active          (is_active),
    INDEX idx_deleted_at         (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

*This template was built from the InstaBAlancePRO reference architecture.*
*Repository: github.com/CREEDCONSULT/INSTABALANCEPRO*
*Reuse freely. Customise Section 0 for each new project.*
