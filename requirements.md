# Requirements Document — Instagram Bulk Unfollower SaaS

**Project Name:** UnfollowIQ
**Version:** 1.0
**Date:** 2026-02-20
**Stack Reference:** See `tech-stack.md` and `design-notes.md`

---

## 1. Project Overview

UnfollowIQ is a Software-as-a-Service web application that enables Instagram users to identify, manage, and bulk-unfollow accounts that do not follow them back. The application is deployed on a LAMP stack and monetized via tiered subscription plans. It provides a dashboard, a Kanban management board, an activity calendar, whitelist protection, and full account management — all behind a secure, multi-user authentication system.

### 1.1 Goals

- Allow users to connect their Instagram account via OAuth and retrieve their follower/following data
- Identify accounts the user follows that do not reciprocate
- Enable bulk and individual unfollow actions within Instagram API rate limits
- Provide subscription-gated feature tiers to support SaaS monetization
- Maintain a secure, performant multi-tenant architecture on a LAMP stack

### 1.2 Out of Scope (v1.0)

- Mobile native apps (iOS / Android)
- Support for platforms other than Instagram
- AI-powered account recommendations
- Team / agency multi-seat accounts
- White-label reseller licensing

---

## 2. User Roles

| Role | Description |
|------|-------------|
| **Guest** | Unauthenticated visitor; can view marketing/landing page and pricing only |
| **Free User** | Authenticated; limited feature access per free tier quotas |
| **Pro User** | Authenticated; paying subscriber with expanded quotas |
| **Premium User** | Authenticated; highest-tier subscriber with full feature access |
| **Admin** | Internal staff; access to admin panel, user management, billing oversight |

---

## 3. Functional Requirements

### 3.1 Authentication & User Accounts

#### 3.1.1 Application Authentication (SaaS Account)
- **REQ-AUTH-001** — Users must be able to register a SaaS account using email and password
- **REQ-AUTH-002** — Registration must require email verification before account activation
- **REQ-AUTH-003** — Users must be able to log in with email + password
- **REQ-AUTH-004** — Passwords must be hashed using `password_hash()` with `PASSWORD_BCRYPT` (cost factor ≥ 12)
- **REQ-AUTH-005** — "Forgot Password" flow must send a time-limited (1 hour) reset link via email
- **REQ-AUTH-006** — Users must be able to update their email address (requires re-verification)
- **REQ-AUTH-007** — Users must be able to update their password (requires current password confirmation)
- **REQ-AUTH-008** — Users must be able to delete their account and all associated data (GDPR right to erasure)
- **REQ-AUTH-009** — Sessions must expire after 30 days of inactivity (configurable per environment)
- **REQ-AUTH-010** — Concurrent session support: users may be logged in on multiple devices simultaneously
- **REQ-AUTH-011** — "Remember Me" option extends session cookie lifetime to 30 days
- **REQ-AUTH-012** — Failed login attempts must be rate-limited: lock account for 15 minutes after 5 consecutive failures

#### 3.1.2 Two-Factor Authentication (2FA)
- **REQ-2FA-001** — Users may optionally enable TOTP-based 2FA (Google Authenticator compatible)
- **REQ-2FA-002** — Recovery codes (8 single-use codes) must be generated and shown at 2FA setup
- **REQ-2FA-003** — 2FA must be enforced on the login form when enabled for the account

#### 3.1.3 Instagram OAuth Connection
- **REQ-OAUTH-001** — Users must connect their Instagram account via Instagram's official OAuth 2.0 flow (Instagram Basic Display API or Graph API)
- **REQ-OAUTH-002** — The application must request only the minimum required scopes: `user_profile`, `user_media`, and the unfollow permission
- **REQ-OAUTH-003** — Instagram access tokens must be stored encrypted (AES-256) in the database — never in cookies or localStorage
- **REQ-OAUTH-004** — Token refresh must be handled automatically before expiry
- **REQ-OAUTH-005** — Users must be able to disconnect their Instagram account from Settings (revokes token, deletes cached data)
- **REQ-OAUTH-006** — One SaaS account may connect exactly one Instagram account (v1.0)
- **REQ-OAUTH-007** — If Instagram's API returns an auth error, the user must be prompted to re-connect rather than shown a generic error

---

### 3.2 Dashboard

- **REQ-DASH-001** — Dashboard must display the following KPI cards: total Following, total Followers, Not Following Back count, New Unfollowers Since Last Sync, Mutual Followers count
- **REQ-DASH-002** — Each KPI card must show the delta (increase/decrease) compared to the previous sync
- **REQ-DASH-003** — Dashboard must show a "Last Synced" timestamp and a manual "Sync Now" button
- **REQ-DASH-004** — A recent activity feed must list the last 20 unfollow/follow events with timestamps
- **REQ-DASH-005** — Dashboard must display current subscription plan and a prompt to upgrade if on Free tier
- **REQ-DASH-006** — A sync progress bar must be shown in real time (via htmx polling) during active sync operations
- **REQ-DASH-007** — Dashboard data must refresh automatically every 60 seconds if a sync is in progress

---

### 3.3 Unfollowers List

- **REQ-LIST-001** — Display all accounts the user follows that do not follow back, in a paginated table (25 rows per page, configurable)
- **REQ-LIST-002** — Each row must show: profile picture, username, display name, follower count, following count, follow ratio, date the user started following them
- **REQ-LIST-003** — Table must be sortable by: username (A–Z), follower count, follow ratio, date followed
- **REQ-LIST-004** — Table must be filterable by: Never Followed Back, Unfollowed Me Recently (within 30 days), Inactive Account (no posts in 90 days)
- **REQ-LIST-005** — A search field must allow real-time client-side filtering by username (Alpine.js)
- **REQ-LIST-006** — Each row must have individual actions: Unfollow (with confirmation), Add to Whitelist
- **REQ-LIST-007** — Checkbox selection must support bulk operations: Bulk Unfollow, Bulk Add to Whitelist
- **REQ-LIST-008** — "Select All on Page" and "Select All Results" options must be available
- **REQ-LIST-009** — Bulk unfollow must respect the per-session unfollow rate limit (see REQ-API section)
- **REQ-LIST-010** — After an unfollow action, the row must be removed from the table with a smooth animation without a full page reload (htmx swap)
- **REQ-LIST-011** — All table interactions (pagination, filter, sort, unfollow) must use htmx partial page updates — no full page reloads

---

### 3.4 Kanban Board

- **REQ-KANBAN-001** — Kanban board must have four fixed columns: **Review**, **Queued for Unfollow**, **Unfollowed**, **Whitelisted**
- **REQ-KANBAN-002** — Each card must show: profile picture, username, follower count, date added to board
- **REQ-KANBAN-003** — Users must be able to move cards between columns using on-card action buttons (htmx POST)
- **REQ-KANBAN-004** — Cards in the "Queued" column must be processed by the background unfollow queue (see REQ-QUEUE)
- **REQ-KANBAN-005** — Column headers must display a live count of cards in that column
- **REQ-KANBAN-006** — Users must be able to remove a card from the board entirely (returns account to the unfollowers list)
- **REQ-KANBAN-007** — Board state must persist in the database between sessions
- **REQ-KANBAN-008** — Moving a card to "Whitelisted" must simultaneously add the account to the Whitelist (REQ-WHITE)

---

### 3.5 Activity Calendar

- **REQ-CAL-001** — Display a monthly calendar grid showing daily unfollow and follow-gain activity
- **REQ-CAL-002** — Each day cell must show activity indicator dots: red (unfollows), green (new followers)
- **REQ-CAL-003** — Day cells must use a heatmap intensity (opacity) proportional to the volume of activity that day
- **REQ-CAL-004** — Clicking a day cell must open a modal showing the full activity log for that day (htmx modal partial)
- **REQ-CAL-005** — Month navigation (previous/next) must use htmx partial updates — no full page reload
- **REQ-CAL-006** — Calendar must display up to 12 months of historical activity
- **REQ-CAL-007** — A summary row below the calendar must show monthly totals: total unfollowed, total new followers, net change

---

### 3.6 Whitelist

- **REQ-WHITE-001** — Users must be able to maintain a whitelist of accounts that are permanently protected from unfollow actions
- **REQ-WHITE-002** — Whitelisted accounts must be excluded from the Unfollowers List, Kanban queue, and all bulk operations
- **REQ-WHITE-003** — Users must be able to add accounts to the whitelist from: the Unfollowers List row action, the Kanban board, and the Whitelist page directly (by username)
- **REQ-WHITE-004** — Users must be able to remove accounts from the whitelist
- **REQ-WHITE-005** — Whitelist must display: profile picture, username, date added, reason (optional free-text note)
- **REQ-WHITE-006** — Whitelist capacity limits apply per subscription tier (see Section 5)

---

### 3.7 Categorization & Engagement Scoring

#### 3.7.1 Verified Badge Detection & Creator Protection
- **REQ-CAT-001** — During each Instagram sync, the system must fetch the `is_verified` flag for each followed account and store it in the `following` table
- **REQ-CAT-002** — Any account with Instagram's verified badge must be automatically flagged as a \"creator\" and assigned a protective score bonus (-50 penalty toward unfollow eligibility)
- **REQ-CAT-003** — Verified creator accounts must be visibly marked in the Ranked List UI with a `[Verified]` badge
- **REQ-CAT-004** — Explicit exclusion logic: accounts with `is_verified=1` must never appear in bulk unfollow suggestions, even if user selects them
- **REQ-CAT-005** — If Instagram's Graph API does not expose `is_verified`, fallback to a configurable follower count threshold (default: 10,000 followers); users may adjust this threshold in Settings > Scoring Preferences

#### 3.7.2 Engagement Gap Calculation
- **REQ-CAT-006** — For each followed account, the system must calculate `engagement_gap_days`: the number of days since the user last liked or commented on any of that account's posts
- **REQ-CAT-007** — If the user has never engaged with a followed account's posts, `engagement_gap_days` must be set to the account's age (time since user started following them)
- **REQ-CAT-008** — Engagement data must be synced during the same sync window as follower/following data; updates to `engagement_gap_days` happen every sync (not continuously)
- **REQ-CAT-009** — Accounts that have not posted in the last 90 days must be flagged with an \"Inactive (90d+)\" category label in the Ranked List UI

#### 3.7.3 Unfollow Priority Scoring
- **REQ-CAT-010** — Every account in the `following` table must have an `unfollow_priority_score` (0–100 integer) computed using the following algorithm:
  ```
  score = (is_verified ? -50 : 0)                            // Protected if verified
        + min((engagement_gap_days / 360) * 30, 30)          // Inactivity: 0–30 pts
        + min((follower_ratio_deviation) * 20, 20)           // Low commitment: 0–20 pts
        + min((days_following / 1000) * 10, 10)              // Older follows: 0–10 pts
        capped 0–100
  where follower_ratio_deviation = max(0, (following_count - follower_count) / following_count)
  ```
- **REQ-CAT-011** — Scores must be recalculated after every sync to reflect changes in follower counts and engagement history
- **REQ-CAT-012** — Users must be able to adjust the weighting factors (inactivity_weight, engagement_weight, ratio_weight, age_weight) in Settings > Scoring Preferences; the algorithm must dynamically apply these weights (default: inactivity=40%, engagement=35%, ratio=15%, age=10%)
- **REQ-CAT-013** — Score distribution must be visualized in the Ranked List UI with a color bar: green (0–30, \"safe to unfollow\"), yellow (31–65, \"review recommended\"), red (66–100, \"high priority for unfollow\")

#### 3.7.4 Category Labels & Ranking UI
- **REQ-CAT-014** — Each account in the Ranked List must display a category label badge based on its score and metadata:
  - `[Verified]` — Instagram verified account (always green, always protected)
  - `[Inactive 90d+]` — No posts in last 90 days (greyed out)
  - `[Low Engagement]` — Never interacted by user (red)
  - `[Whitelisted]` — User-protected account (blue)
  - No badge if score < 30 (\"safe, mutual follower or recent follow\")
- **REQ-CAT-015** — Hovering over the unfollow priority score or category badge must display a tooltip explaining which factors contributed to the ranking: e.g., \"Ranking: Verified creator (protected), no posts in 120 days (-25 pts), you follow 1.5x more than they follow you (-10 pts). Total score: 35.\"

---

### 3.8 Sync Engine

- **REQ-SYNC-001** — On first connect, the application must perform a full sync: fetch all following and all followers via the Instagram API
- **REQ-SYNC-002** — Subsequent syncs must be incremental where the API supports it, fetching only changes since the last sync
- **REQ-SYNC-003** — Manual sync must be triggerable from the Dashboard at any time (subject to rate limits)
- **REQ-SYNC-004** — Automated background sync must run on a schedule per plan tier: Free (every 24h), Pro (every 6h), Premium (every 1h)
- **REQ-SYNC-005** — Sync status (in progress, completed, failed) must be visible on the Dashboard
- **REQ-SYNC-006** — If a sync fails due to an API error, the system must retry up to 3 times with exponential backoff before marking as failed
- **REQ-SYNC-007** — Sync history must be logged: timestamp, duration, accounts fetched, errors encountered

---

### 3.9 Unfollow Queue & Rate Limiting

- **REQ-QUEUE-001** — All unfollow actions (individual and bulk) must be processed through a rate-limited queue to comply with Instagram API limits
- **REQ-QUEUE-002** — The unfollow rate must not exceed **60 unfollows per hour** and **150 unfollows per day** per Instagram account (conservative limits — adjust based on current Instagram API TOS)
- **REQ-QUEUE-003** — Queue status must be visible to the user: position in queue, estimated completion time
- **REQ-QUEUE-004** — Users must be able to pause and resume their unfollow queue
- **REQ-QUEUE-005** — Users must be able to clear the queue (cancel all pending unfollows)
- **REQ-QUEUE-006** — If an unfollow action fails (API error, rate limit hit), it must be retried automatically after a cooldown period
- **REQ-QUEUE-007** — Completed, failed, and cancelled queue items must be logged to the activity feed

---

### 3.10 Notifications

- **REQ-NOTIF-001** — In-app toast notifications must appear for: successful unfollow, sync completion, sync failure, rate limit warning
- **REQ-NOTIF-002** — Email notifications must be sent for: sync failure (after all retries exhausted), subscription renewal, subscription expiry warning (7 days before), account deletion confirmation
- **REQ-NOTIF-003** — Users must be able to configure which email notifications they receive in Settings
- **REQ-NOTIF-004** — All emails must be sent via SMTP (configurable); use PHPMailer or a similar library

---

### 3.11 Settings Page

- **REQ-SETTINGS-001** — Settings page must be organized into tabs: Account, Instagram Connection, Notifications, Subscription, Danger Zone
- **REQ-SETTINGS-002** — Account tab: update name, email, password, enable/disable 2FA
- **REQ-SETTINGS-003** — Instagram Connection tab: show connected account info, last sync time, manual sync trigger, disconnect button
- **REQ-SETTINGS-004** — Notifications tab: toggles for each email notification type
- **REQ-SETTINGS-005** — Subscription tab: show current plan, usage stats (unfollows this month vs. limit), upgrade/downgrade options, billing history link
- **REQ-SETTINGS-006** — Danger Zone tab: Export My Data (GDPR), Delete Account

---

### 3.12 Admin Panel

- **REQ-ADMIN-001** — Admin panel must be accessible only to users with the `admin` role, at a protected route (e.g., `/admin`)
- **REQ-ADMIN-002** — User management: list all users, view user details, manually activate/deactivate accounts, reset passwords
- **REQ-ADMIN-003** — Subscription management: view and override user plan tier, view billing status
- **REQ-ADMIN-004** — System stats: total users, active subscriptions by tier, total unfollows processed today/this month, API error rate
- **REQ-ADMIN-005** — Sync queue monitor: view global queue depth, stuck jobs, failed jobs
- **REQ-ADMIN-006** — Admin actions must be logged with timestamp and acting admin user ID

---

## 4. Non-Functional Requirements

### 4.1 Security

- **REQ-SEC-001** — All pages must be served over HTTPS; HTTP must redirect to HTTPS via Apache
- **REQ-SEC-002** — CSRF tokens must be validated on all POST, PUT, and DELETE requests
- **REQ-SEC-003** — All database queries must use PDO prepared statements — no raw string interpolation
- **REQ-SEC-004** — Instagram access tokens must be encrypted at rest using AES-256-CBC with a key stored in `.env`, never in the database in plaintext
- **REQ-SEC-005** — Session cookies must use `HttpOnly`, `Secure`, and `SameSite=Lax` flags
- **REQ-SEC-006** — User-supplied input must be sanitized before rendering (use `htmlspecialchars()` for output escaping)
- **REQ-SEC-007** — Sensitive routes (`/admin`, `/settings`, `/api/*`) must require authentication; unauthenticated requests redirect to `/login`
- **REQ-SEC-008** — Rate limiting must be applied to: login endpoint (5 attempts / 15 min), password reset endpoint (3 requests / hour), sync trigger endpoint (1 per 5 min for Free, 1 per 1 min for Pro/Premium)
- **REQ-SEC-009** — Dependency security: run `composer audit` as part of any deployment process
- **REQ-SEC-010** — `.env` file must never be committed to version control; a `.env.example` template must be maintained instead

### 4.2 Performance

- **REQ-PERF-001** — Dashboard page must load in under 2 seconds on a standard VPS (2 vCPU, 4GB RAM) for a user with up to 10,000 following
- **REQ-PERF-002** — Unfollowers list must paginate server-side; do not fetch all records into memory at once
- **REQ-PERF-003** — All database tables must have appropriate indexes on foreign keys, frequently-filtered columns (`user_id`, `instagram_account_id`, `synced_at`)
- **REQ-PERF-004** — Instagram API responses must be cached in MySQL for the duration of the sync interval; do not re-fetch on every page load
- **REQ-PERF-005** — Apache must serve static assets (CSS, JS from CDN; app-specific CSS/JS from disk) with appropriate cache headers

### 4.3 Reliability

- **REQ-REL-001** — The application must handle Instagram API rate limit responses (HTTP 429) gracefully: pause the queue, display a user-facing message, retry automatically when the window resets
- **REQ-REL-002** — Unfollow queue jobs must be idempotent: if a job runs twice (e.g., after a server restart), it must not produce duplicate unfollow calls
- **REQ-REL-003** — Database transactions must be used for multi-step operations (e.g., unfollow + remove from following table + log to activity feed)
- **REQ-REL-004** — PHP errors in production must be logged to file (`error_log`) and must not be displayed to end users

### 4.4 Scalability

- **REQ-SCALE-001** — The data model must be multi-tenant from day one: every table must have a `user_id` foreign key; cross-user data leakage must be architecturally impossible
- **REQ-SCALE-002** — Background jobs (sync, unfollow queue) must be designed to run as PHP CLI scripts invoked by cron, so they can later be moved to a dedicated worker process without application changes
- **REQ-SCALE-003** — The application must support at least 500 concurrent registered users on a single LAMP server without architectural changes

### 4.5 Accessibility

- **REQ-A11Y-001** — All pages must meet WCAG 2.1 Level AA compliance
- **REQ-A11Y-002** — All interactive elements must be keyboard-navigable
- **REQ-A11Y-003** — All images must have descriptive `alt` attributes; icon-only buttons must have `aria-label` attributes
- **REQ-A11Y-004** — Color must not be the only means of conveying state (e.g., status badges must include text, not just color)
- **REQ-A11Y-005** — Toast notifications must use `role="alert"` so screen readers announce them

### 4.6 Browser Support

| Browser | Minimum Version |
|---------|----------------|
| Chrome | 108+ |
| Firefox | 110+ |
| Safari | 16+ |
| Edge | 108+ |
| Mobile Safari (iOS) | 16+ |
| Chrome for Android | 108+ |

---

## 5. Subscription Tiers & Feature Gates

| Feature | Free | Pro | Premium |
|---------|------|-----|---------|
| Instagram accounts connected | 1 | 1 | 1 |
| Unfollows per month | 50 | 500 | Unlimited |
| Bulk unfollow batch size | 5 | 50 | 200 |
| Whitelist capacity | 10 accounts | 100 accounts | Unlimited |
| Auto-sync frequency | Every 24h | Every 6h | Every 1h |
| Activity calendar history | 30 days | 6 months | 12 months |
| Kanban board | ✗ | ✓ | ✓ |
| CSV export | ✗ | ✓ | ✓ |
| Priority email support | ✗ | ✗ | ✓ |
| API access (future) | ✗ | ✗ | ✓ |

- **REQ-TIER-001** — Feature gates must be enforced server-side, not just in the UI
- **REQ-TIER-002** — When a user hits a quota limit, a clear in-app message must appear with a link to upgrade
- **REQ-TIER-003** — Monthly usage counters (unfollows) must reset on the user's billing anniversary date, not on the 1st of the month
- **REQ-TIER-004** — Downgrading a plan must not immediately remove access; access continues until the end of the current billing period

---

## 6. Billing & Payments

- **REQ-BILL-001** — Payment processing must be handled by **Stripe** (Stripe Checkout or Stripe Billing)
- **REQ-BILL-002** — The application must never store raw credit card data — all card handling is delegated to Stripe
- **REQ-BILL-003** — Subscription lifecycle events (created, renewed, cancelled, payment failed) must be handled via **Stripe Webhooks**
- **REQ-BILL-004** — On successful payment, the user's plan tier in the database must be updated immediately via the webhook handler
- **REQ-BILL-005** — On payment failure, the user must receive an email notification and be given a grace period of 3 days before being downgraded to Free
- **REQ-BILL-006** — Users must be able to cancel their subscription from the Settings page (cancels at end of billing period)
- **REQ-BILL-007** — A basic billing history page must show invoice date, amount, status, and a link to the Stripe-hosted invoice PDF
- **REQ-BILL-008** — All prices must be stored in cents/smallest currency unit in the database; display formatting is handled in the view layer

---

## 7. Data & Privacy

- **REQ-DATA-001** — A Privacy Policy page must be accessible to all visitors (unauthenticated)
- **REQ-DATA-002** — A Terms of Service page must be accessible to all visitors
- **REQ-DATA-003** — Users must accept ToS and Privacy Policy at registration (checkbox, with timestamp stored)
- **REQ-DATA-004** — **GDPR / Data Export:** Users must be able to request a full export of their data (account info, Instagram connection, whitelist, activity log) as a downloadable JSON or CSV file, available within 30 seconds of request
- **REQ-DATA-005** — **GDPR / Right to Erasure:** Deleting an account must permanently remove all user data from the database within 30 days; a confirmation email must be sent
- **REQ-DATA-006** — Instagram follower/following data is cached for operational purposes only and must be deleted when the user disconnects their Instagram account or deletes their SaaS account
- **REQ-DATA-007** — A cookie consent banner must be shown to EU visitors on first visit

---

## 8. Email System

- **REQ-EMAIL-001** — All transactional emails must be sent via SMTP using PHPMailer (or equivalent)
- **REQ-EMAIL-002** — SMTP credentials must be stored in `.env`, never hardcoded
- **REQ-EMAIL-003** — All emails must include a plain-text fallback alongside the HTML version
- **REQ-EMAIL-004** — All emails must include an unsubscribe link for marketing/notification emails (CAN-SPAM / GDPR compliance)

| Email Trigger | Subject |
|--------------|---------|
| Registration | Verify your UnfollowIQ account |
| Password Reset | Reset your password |
| 2FA Enabled | Two-factor authentication enabled |
| Account Deletion | Your account has been deleted |
| Sync Failure | ⚠️ Your Instagram sync failed |
| Subscription Renewal | Your subscription has renewed |
| Payment Failed | Action required: payment failed |
| Subscription Cancelled | Your subscription has been cancelled |
| Plan Expiry Warning | Your Pro plan expires in 7 days |
| Data Export Ready | Your data export is ready |

---

## 9. Database Schema Overview

**New tables for categorization & engagement:**
- `account_insights` — Engagement metrics: last post date, engagement gap, follower/post count snapshots
- `creator_flags` — Verified badge and other creator-protection flags
- `user_scoring_preferences` — Per-user weights for the ranking algorithm

**Core tables:**
users                      — SaaS accounts (email, password, plan, 2FA)
instagram_connections      — OAuth tokens per user (encrypted)
following                  — Cached list of accounts the user follows (with scoring fields)
followers                  — Cached list of user's followers
account_insights           — Engagement metrics: last post date, interaction history
creator_flags              — Verified badges and creator-protection flags
unfollower_snapshots       — Point-in-time diff records for activity tracking
unfollow_queue             — Pending/processing/completed unfollow jobs
whitelist                  — Protected accounts per user
kanban_cards               — Kanban board state per user
activity_log               — Immutable event log (unfollows, syncs, logins)
user_scoring_preferences   — Per-user algorithm weights & thresholds
subscriptions              — Stripe subscription data per user
invoices                   — Billing history records
email_verifications        — Pending email verification tokens
password_resets            — Pending password reset tokens
admin_log                  — Admin action audit trail
```

Full schema DDL is defined in `database/schema.sql`.

---

## 10. API Endpoints (Internal — PHP + htmx)

All endpoints are server-rendered and return HTML partials for htmx, except where noted.

| Method | Route | Description | Auth Required |
|--------|-------|-------------|---------------|
| GET | `/` | Landing / marketing page | No |
| GET | `/login` | Login form | No |
| POST | `/login` | Process login | No |
| GET | `/register` | Registration form | No |
| POST | `/register` | Process registration | No |
| GET | `/logout` | Destroy session | Yes |
| GET | `/auth/instagram` | Redirect to Instagram OAuth | Yes |
| GET | `/auth/callback` | Handle Instagram OAuth callback | Yes |
| GET | `/dashboard` | Dashboard page | Yes |
| GET | `/dashboard/sync-status` | htmx poll: sync progress bar partial | Yes |
| POST | `/sync` | Trigger manual sync | Yes |
| GET | `/unfollowers` | Unfollowers list page + table partial | Yes |
| POST | `/unfollow/{id}` | Unfollow single account | Yes |
| POST | `/unfollow/bulk` | Bulk unfollow selected accounts | Yes |
| GET | `/kanban` | Kanban board page | Yes |
| POST | `/kanban/move/{id}` | Move card to column | Yes |
| GET | `/calendar` | Activity calendar page | Yes |
| GET | `/calendar/day/{date}` | htmx partial: day detail modal | Yes |
| GET | `/whitelist` | Whitelist page | Yes |
| POST | `/whitelist/add/{id}` | Add account to whitelist | Yes |
| DELETE | `/whitelist/{id}` | Remove from whitelist | Yes |
| GET | `/settings` | Settings page | Yes |
| POST | `/settings/account` | Update account info | Yes |
| POST | `/settings/password` | Update password | Yes |
| POST | `/settings/2fa/enable` | Enable 2FA | Yes |
| POST | `/settings/2fa/disable` | Disable 2FA | Yes |
| GET | `/settings/export` | Generate + download data export | Yes |
| POST | `/settings/delete-account` | Delete account | Yes |
| GET | `/billing/checkout/{plan}` | Redirect to Stripe Checkout | Yes |
| GET | `/billing/portal` | Redirect to Stripe Customer Portal | Yes |
| POST | `/webhooks/stripe` | Handle Stripe webhook events | No (Stripe sig) |
| GET | `/admin` | Admin dashboard | Admin |
| GET | `/admin/users` | User management | Admin |

---

## 11. File & Directory Structure

```
/var/www/html/unfollowiq/
├── public/                     # Apache document root
│   ├── index.php               # Front controller
│   ├── .htaccess               # Rewrite rules
│   └── assets/
│       ├── css/app.css         # Custom styles (non-Bootstrap)
│       └── js/app.js           # Minimal custom JS
├── src/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   │   ├── InstagramApiService.php
│   │   ├── SyncService.php
│   │   ├── UnfollowQueueService.php
│   │   ├── StripeService.php
│   │   └── MailService.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── AdminMiddleware.php
│   │   └── CsrfMiddleware.php
│   └── Views/
│       ├── layouts/
│       ├── partials/
│       └── pages/
├── database/
│   ├── schema.sql
│   └── migrations/
├── cron/
│   ├── sync.php                # Background sync worker (called by cron)
│   └── queue.php               # Unfollow queue processor (called by cron)
├── config/
│   └── app.php                 # App config loader
├── .env                        # Environment variables (not in VCS)
├── .env.example                # Template (in VCS)
├── composer.json
└── vendor/
```

---

## 12. Cron Jobs

```bash
# /etc/crontab entries

# Run unfollow queue processor every minute
* * * * * www-data php /var/www/html/unfollowiq/cron/queue.php >> /var/log/unfollowiq/queue.log 2>&1

# Run background sync scheduler every 5 minutes (script internally checks per-user intervals)
*/5 * * * * www-data php /var/www/html/unfollowiq/cron/sync.php >> /var/log/unfollowiq/sync.log 2>&1
```

---

## 13. Third-Party Dependencies

| Dependency | Purpose | Notes |
|-----------|---------|-------|
| `guzzlehttp/guzzle` | Instagram API HTTP client | Via Composer |
| `vlucas/phpdotenv` | `.env` loading | Via Composer |
| `phpmailer/phpmailer` | Transactional email | Via Composer |
| `stripe/stripe-php` | Stripe billing integration | Via Composer |
| `nikic/fast-route` | URL routing | Via Composer |
| `pragmarx/google2fa` | TOTP 2FA | Via Composer |
| `paragonie/constant_time_encoding` | Secure token encoding | Via Composer |
| Bootstrap 5.3 | UI framework | CDN (getbootstrap.com) |
| htmx (latest) | Server-driven interactivity | CDN (unpkg.com) |
| Alpine.js (latest) | Client-side UI state | CDN (jsdelivr.net) |
| jQuery 3.x | Bootstrap companion + DOM utils | CDN |
| Bootstrap Icons | Icon library | CDN |
| Google Fonts (Syne, DM Sans, JetBrains Mono) | Typography | CDN |

**Services to implement for categorization:**
- `src/Services/EngagementService.php` — Fetches engagement metrics (engagement gap, last post date, follower counts) for scoring
- `src/Services/ScoringService.php` — Computes unfollow_priority_score and applies user-configurable weights

---

## 14. Acceptance Criteria Summary

| # | Criterion |
|---|-----------|
| AC-01 | A new user can register, verify their email, log in, connect Instagram, and see their unfollowers list — all within one session |
| AC-02 | A user can unfollow a single account; the row disappears from the table without a page reload |
| AC-03 | A user can select 10 accounts and bulk unfollow them; all 10 are processed via the queue and removed from the list |
| AC-04 | A whitelisted account never appears in the unfollowers list or Kanban queue, even after a fresh sync |
| AC-05 | A Free user who hits the 50 unfollow/month limit sees an upgrade prompt and cannot unfollow further until they upgrade or the month resets |
| AC-06 | A Stripe subscription webhook correctly upgrades the user's plan tier in real time |
| AC-07 | Requesting account deletion removes all user data; the user cannot log back in afterward |
| AC-08 | The admin panel displays correct user counts and allows deactivating a user account |
| AC-09 | An Instagram API rate limit response (429) causes the queue to pause automatically and resume after the reset window |
| AC-10 | The application is fully usable on a 375px-wide mobile screen with the sidebar in offcanvas mode |
