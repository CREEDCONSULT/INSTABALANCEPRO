# Build Prompts — UnfollowIQ
## Claude Code Prompt Sequence (Prompts 1–10)

These prompts are designed to be run sequentially in Claude Code. Each prompt builds on the previous one. Before starting, ensure `tech-stack.md`, `design-notes.md`, and `requirements.md` are present in the project root.

---

## PROMPT 1 — Project Scaffold & Environment

```
Read tech-stack.md, design-notes.md, and requirements.md in full before doing anything else.

Set up the complete project scaffold for UnfollowIQ exactly as defined in the requirements doc Section 11 (File & Directory Structure). 

Tasks:
1. Create the full directory tree under the current working directory
2. Initialize Composer and install all PHP dependencies listed in requirements.md Section 13:
   - guzzlehttp/guzzle
   - vlucas/phpdotenv
   - phpmailer/phpmailer
   - stripe/stripe-php
   - nikic/fast-route
   - pragmarx/google2fa
   - paragonie/constant_time_encoding
3. Create .env.example with every variable defined in tech-stack.md Section "Environment Configuration" plus these additions:
   STRIPE_SECRET_KEY=
   STRIPE_PUBLISHABLE_KEY=
   STRIPE_WEBHOOK_SECRET=
   MAIL_HOST=
   MAIL_PORT=587
   MAIL_USERNAME=
   MAIL_PASSWORD=
   MAIL_FROM_ADDRESS=noreply@unfollowiq.com
   MAIL_FROM_NAME=UnfollowIQ
   APP_KEY=
4. Create a .gitignore that excludes: .env, vendor/, node_modules/, /public/uploads/, *.log
5. Create public/.htaccess with:
   - RewriteEngine On routing all requests to public/index.php
   - HTTPS enforcement redirect
   - Security headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy)
6. Create config/app.php that loads .env and returns a configuration array
7. Create public/index.php as the front controller that bootstraps the app, loads Composer autoload, initializes .env, and routes requests through fast-route

Do not create any HTML views or CSS yet. Focus only on the skeleton and the bootstrap chain.
After completing, show me the full directory tree.
```

---

## PROMPT 2 — Database Schema & Migration System

```
Read requirements.md Section 9 (Database Schema Overview) and tech-stack.md.

Create the complete MySQL database schema for UnfollowIQ.

Tasks:
1. Create database/schema.sql with CREATE TABLE statements for ALL tables listed in requirements.md Section 9. For each table apply:
   - Auto-increment primary keys
   - Appropriate VARCHAR lengths based on the data (Instagram IDs: VARCHAR(64), usernames: VARCHAR(128), URLs: TEXT, tokens: TEXT)
   - DEFAULT CURRENT_TIMESTAMP on created_at columns
   - ON DELETE CASCADE on all user_id foreign keys
   - Indexes on: all foreign keys, instagram_account_id, status columns, synced_at/created_at columns used for filtering
   - Appropriate UNIQUE constraints (e.g., users.email, instagram_connections.user_id)

2. The full set of tables must be:
   - users (id, name, email, password, plan_tier ENUM('free','pro','premium'), email_verified_at, totp_secret, tos_accepted_at, created_at, updated_at)
   - instagram_connections (id, user_id, instagram_user_id, username, profile_picture_url, access_token_encrypted, token_expires_at, last_sync_at, created_at)
   - following (id, user_id, instagram_account_id, username, full_name, profile_picture_url, synced_at)
   - followers (id, user_id, instagram_account_id, username, full_name, profile_picture_url, synced_at)
   - unfollower_snapshots (id, user_id, instagram_account_id, username, profile_picture_url, follower_count, following_count, detected_at)
   - unfollow_queue (id, user_id, instagram_account_id, username, status ENUM('pending','processing','completed','failed','cancelled'), attempts, scheduled_at, processed_at, error_message, created_at)
   - whitelist (id, user_id, instagram_account_id, username, profile_picture_url, notes, created_at)
   - kanban_cards (id, user_id, instagram_account_id, username, profile_picture_url, follower_count, column_name ENUM('review','queued','unfollowed','whitelisted'), position, created_at, updated_at)
   - activity_log (id, user_id, event_type VARCHAR(64), instagram_account_id, instagram_username, metadata JSON, created_at)
   - subscriptions (id, user_id, stripe_subscription_id, stripe_customer_id, plan_tier, status, current_period_start, current_period_end, cancelled_at, created_at, updated_at)
   - invoices (id, user_id, stripe_invoice_id, amount_cents, currency, status, invoice_url, created_at)
   - email_verifications (id, user_id, token VARCHAR(128), expires_at, created_at)
   - password_resets (id, email, token VARCHAR(128), expires_at, created_at)
   - admin_log (id, admin_user_id, action VARCHAR(128), target_user_id, metadata JSON, created_at)
   - sync_jobs (id, user_id, status ENUM('pending','running','completed','failed'), started_at, completed_at, accounts_fetched, error_message, created_at)
   - monthly_usage (id, user_id, year SMALLINT, month TINYINT, unfollow_count INT DEFAULT 0, UNIQUE KEY year_month_user (user_id, year, month))

3. Create database/migrations/ directory and a naming convention doc: migrations are numbered 001_, 002_, etc.
4. Create a simple PHP migration runner: database/migrate.php that executes all .sql files in database/migrations/ in order and tracks which have run in a schema_migrations table
5. Move the schema.sql CREATE statements into database/migrations/001_initial_schema.sql

Show the complete schema.sql content when done.
```

---

## PROMPT 3 — Core PHP Architecture (Router, Middleware, Base Classes)

```
Read tech-stack.md and requirements.md Sections 10 and 11.

Build the core PHP application architecture. No views yet — just the plumbing.

Tasks:

1. src/Core/Router.php — wrap nikic/fast-route to:
   - Register routes with GET/POST/DELETE methods
   - Support route groups with shared prefixes
   - Dispatch to Controller@method strings
   - Return 404 / 405 responses for unknown routes

2. src/Core/Request.php — wrapper around $_GET, $_POST, $_FILES, $_SERVER:
   - get(key, default) for query params
   - post(key, default) for POST data
   - isPost(), isGet(), isHtmx() (checks HX-Request header)
   - ip() for client IP (handles proxies)
   - input(key) — checks POST then GET

3. src/Core/Response.php:
   - html(string $content, int $status = 200)
   - json(array $data, int $status = 200)
   - redirect(string $url, int $status = 302)
   - htmxRedirect(string $url) — sets HX-Redirect header
   - partial(string $view, array $data = []) — renders a view partial and returns HTML

4. src/Core/View.php — simple PHP template renderer:
   - render(string $template, array $data = []) — includes PHP file from src/Views/, extracts $data into scope
   - layout(string $template, string $layout, array $data = []) — wraps template in a layout file
   - partial(string $template, array $data = []) — renders without layout

5. src/Core/Database.php — PDO singleton:
   - getInstance() returns a shared PDO connection
   - Connection uses MySQL, utf8mb4, PDO::ERRMODE_EXCEPTION
   - query(string $sql, array $params = []) — prepares and executes, returns PDOStatement
   - fetchAll / fetchOne / execute convenience methods

6. src/Core/Session.php:
   - start() with secure cookie settings (HttpOnly, Secure, SameSite=Lax)
   - set/get/has/delete/flash methods
   - regenerate() — regenerates session ID (call after login)
   - destroy()

7. src/Middleware/AuthMiddleware.php:
   - handle() — checks Session for authenticated user_id; redirects to /login if not found
   - Sets current user object on a global App context

8. src/Middleware/AdminMiddleware.php:
   - Extends AuthMiddleware; additionally checks user.role === 'admin'; returns 403 otherwise

9. src/Middleware/CsrfMiddleware.php:
   - generateToken() — stores in session
   - validateToken(string $token) — compares with session token using hash_equals()
   - Automatically validate on all POST requests; return 419 on mismatch

10. src/Core/App.php — application bootstrap class:
    - Registers all routes (import route definitions from config/routes.php)
    - Applies middleware pipeline
    - Dispatches request
    - Handles exceptions: catch \Throwable, log to error_log, show appropriate error response

11. config/routes.php — define ALL routes from requirements.md Section 10, mapping to Controller@method strings. Group authenticated routes under the AuthMiddleware. Group /admin routes under AdminMiddleware.

Show the complete src/Core/App.php and config/routes.php when done.
```

---

## PROMPT 4 — Authentication System (Register, Login, Email Verification, Password Reset, 2FA)

```
Read requirements.md Section 3.1 fully before starting.

Build the complete authentication system for UnfollowIQ.

Tasks:

1. src/Models/User.php:
   - findById(int $id): ?array
   - findByEmail(string $email): ?array
   - create(array $data): int (returns new user ID)
   - update(int $id, array $data): void
   - delete(int $id): void
   - verifyPassword(string $plain, string $hash): bool
   - hashPassword(string $plain): string — uses PASSWORD_BCRYPT, cost 12

2. src/Controllers/AuthController.php with methods:
   - showLogin() — render login form
   - login() — validate credentials, check email verified, check 2FA, regenerate session, redirect to /dashboard; enforce rate limiting (REQ-AUTH-012: lock after 5 failures / 15 min, track in session or DB)
   - showRegister() — render registration form
   - register() — validate input, check unique email, hash password, create user, create email_verification token, send verification email, redirect to /login with flash message
   - logout() — destroy session, redirect to /login
   - showVerifyEmail($token) — find token, verify not expired (24h), activate user, delete token, redirect to /login with success flash
   - showForgotPassword() — render forgot password form
   - sendResetLink() — find user by email, create password_reset token (1h expiry), send email, always show same success message (prevent user enumeration)
   - showResetPassword($token) — validate token not expired, render new password form
   - resetPassword($token) — validate token, update password, delete all reset tokens for user, redirect to /login
   - show2FA() — render TOTP input form
   - verify2FA() — verify TOTP code using pragmarx/google2fa; on success complete login

3. src/Services/MailService.php:
   - Uses PHPMailer with SMTP config from .env
   - sendVerificationEmail(User $user, string $token)
   - sendPasswordResetEmail(User $user, string $token)
   - sendGenericEmail(string $to, string $subject, string $htmlBody, string $textBody)
   - All methods log failures to error_log, never throw to the user

4. src/Views/pages/login.php — full login page using the design from design-notes.md "Login / OAuth Page" section:
   - Centered card layout, Instagram gradient wordmark
   - Email + password fields using Bootstrap form-control
   - "Remember Me" checkbox
   - Link to /forgot-password
   - Link to /register
   - CSRF hidden input
   - Flash message display (Bootstrap alert)

5. src/Views/pages/register.php:
   - Name, email, password, confirm password fields
   - ToS + Privacy Policy checkbox (required) per REQ-DATA-003
   - CSRF hidden input
   - Validation error display inline under each field

6. src/Views/pages/forgot-password.php and reset-password.php — clean minimal forms

7. src/Views/pages/verify-email.php — status page showing success or expired-token error

8. src/Views/pages/2fa.php — 6-digit TOTP input form

Security requirements from REQ-SEC-001 through REQ-SEC-008 must all be satisfied.
Use hash_equals() for token comparisons. Never leak whether an email exists in the forgot-password flow.

Show the complete AuthController.php when done.
```

---

## PROMPT 5 — Application Layout & Navigation Shell

```
Read design-notes.md in full. This prompt is entirely about the HTML/CSS/JS shell that wraps every authenticated page.

Build the main application layout and navigation.

Tasks:

1. src/Views/layouts/main.php — the master layout that all authenticated pages extend:
   - DOCTYPE, <html data-bs-theme="dark">, <head> with all CDN links in the correct order from design-notes.md:
     1. Google Fonts (Syne, DM Sans, JetBrains Mono)
     2. Bootstrap 5.3 CSS (CDN from getbootstrap.com)
     3. Bootstrap Icons CSS (CDN)
     4. Custom app CSS link (/assets/css/app.css)
     5. Alpine.js (defer, from jsdelivr)
     6. htmx (from unpkg)
   - CSS variables block from design-notes.md "Color Palette" section in a <style> tag
   - Bootstrap dark theme overrides from design-notes.md
   - The full sidebar HTML from design-notes.md "Sidebar HTML Structure" section — dynamic active state on nav links based on current URL
   - A main content area: <main class="flex-grow-1 overflow-y-auto p-4">
   - Mobile topbar (visible only on <lg): hamburger button + offcanvas sidebar trigger + app wordmark
   - Offcanvas sidebar for mobile (same nav content as desktop sidebar)
   - Toast container div (id="toast-container") for htmx OOB toasts
   - Global htmx loading indicator bar (from design-notes.md "Loading Spinner" section)
   - jQuery CDN script
   - Bootstrap JS bundle CDN script
   - A <?= $content ?> yield slot for page content
   - Bootstrap Tooltips initialization script

2. src/Views/layouts/auth.php — minimal layout for login/register/forgot-password pages:
   - Same <head> CDN links
   - Dark background full-height centered container
   - No sidebar, no navbar
   - <?= $content ?> slot

3. src/Views/layouts/guest.php — landing page layout:
   - Standard Bootstrap navbar with logo, "Log In" and "Sign Up" buttons
   - <?= $content ?> slot
   - Footer with links to Privacy Policy, Terms of Service

4. public/assets/css/app.css — custom styles only (not Bootstrap overrides, those go inline in layout):
   - Sidebar nav-link active state gradient from design-notes.md "Micro-interactions" section
   - .htmx-indicator opacity CSS
   - tr.htmx-swapping animation
   - Card hover lift transition
   - Kanban card cursor styles
   - Instagram gradient utility class: .ig-gradient-text
   - Any scrollbar styling for dark mode

5. src/Views/partials/toast.php — the OOB toast partial from design-notes.md, accepting $message and $type (success/danger/warning)

6. src/Views/partials/csrf.php — a single-line partial that outputs the CSRF hidden input

7. src/Views/pages/dashboard.php — a STUB page (real content in Prompt 6) that just extends main layout and outputs "Dashboard — coming soon" so we can verify the shell renders correctly

8. Update public/index.php to render the dashboard stub for authenticated GET /dashboard requests

At the end, confirm: does the page render with sidebar, correct fonts, dark background, and Instagram gradient wordmark? List any CDN links that need version pinning.
```

---

## PROMPT 6 — Dashboard Page (KPI Cards, Activity Feed, Sync Controls)

```
Read requirements.md Section 3.2 and design-notes.md "Page: Dashboard" section in full.

Build the complete Dashboard page. The Instagram connection and real API calls don't exist yet — use realistic placeholder/mock data passed from the controller for now. We will wire up real data in a later prompt.

Tasks:

1. src/Controllers/DashboardController.php:
   - index() — fetch the following data from the database for the logged-in user (or use mock data if no Instagram connection exists), pass to view:
     - $stats: array with keys: following_count, followers_count, not_following_back, new_unfollowers_since_last_sync, mutual_count, last_sync_at, sync_in_progress (bool)
     - $activity: array of last 20 activity_log rows for the user
     - $plan: current plan tier from users table
     - $usage: array with unfollow_count (this month) and plan limit
   - syncStatus() — htmx polling endpoint: returns the sync progress bar partial only; checks sync_jobs table for an in-progress job for this user

2. src/Views/pages/dashboard.php — full dashboard implementing design-notes.md exactly:
   - Page header: "Dashboard" h1 + "Last synced X minutes ago" subtitle + "Sync Now" button
   - Instagram gradient divider accent (3px bar from design-notes.md)
   - 5 KPI stat cards in a responsive Bootstrap grid (col-12 col-sm-6 col-xl): Following, Followers, Not Following Back (red accent), New Unfollowers (warning), Mutual Followers (green)
   - Each card shows the delta badge (up/down arrow + number) as per design-notes.md
   - Sync progress bar (hidden by default, shown when sync_in_progress is true):
     - Bootstrap striped animated progress bar with Instagram gradient
     - htmx auto-poll: hx-get="/dashboard/sync-status" hx-trigger="every 3s" hx-target="#sync-progress-wrap" — only active when in_progress
   - "Sync Now" button: hx-post="/sync" hx-swap="none" — triggers a sync and shows a toast
   - Plan usage card: shows "X / Y unfollows used this month" with a Bootstrap progress bar; upgrade CTA if on free tier
   - Recent Activity feed: Bootstrap card with list-group-flush, each item shows avatar, username, event description, timestamp — from design-notes.md "Recent Activity Feed" section
   - Empty state for activity feed: centered icon + "No activity yet. Sync your account to get started."

3. src/Views/partials/sync-progress.php — the htmx partial returned by syncStatus() endpoint; outputs just the progress bar div with current percentage

4. src/Views/partials/activity-item.php — single activity feed row partial

Requirement: All number formatting (follower counts) must use number_format() with commas. All timestamps must use human-relative formatting ("2 hours ago", "Yesterday"). 

The "Sync Now" button must be disabled with a spinner while a sync is in progress (Alpine.js x-data for button state).

Show the complete dashboard.php view when done.
```

---

## PROMPT 7 — Instagram OAuth Connection & Sync Engine

```
Read requirements.md Sections 3.1.3, 3.7.1, and 3.8 fully. Read tech-stack.md.

Build the Instagram OAuth connection flow and the sync engine. Use the Instagram Basic Display API (or Graph API — use whichever is current and supports follower/following data reads).

IMPORTANT: Instagram's API is the most sensitive part of this app. Handle all API errors gracefully. Never crash the app on an API failure.

Tasks:

1. src/Services/InstagramApiService.php:
   - Constructor accepts an encrypted access token; decrypts it internally using the APP_KEY from .env
   - getUser(): array — fetch authenticated user's basic profile (id, username, profile_picture_url)
   - getFollowing(string $userId, ?string $cursor = null): array — returns ['data' => [...accounts with is_verified flag], 'next_cursor' => ?string]
     - IMPORTANT (REQ-CAT-001): Each account MUST include an 'is_verified' field (bool). If Graph API does not expose this, set to false (fallback handled in ScoringService later)
   - getFollowers(string $userId, ?string $cursor = null): array — same structure
   - unfollow(string $targetUserId): bool — returns true on success
   - Handles 429 rate limit responses: throws a RateLimitException with retry_after seconds
   - Handles 401 responses: throws a TokenExpiredException
   - Handles all other API errors: logs and throws a InstagramApiException
   - All API calls use Guzzle with a 15-second timeout and proper User-Agent header

2. src/Services/EncryptionService.php:
   - encrypt(string $plaintext): string — AES-256-CBC, returns base64 encoded ciphertext + IV
   - decrypt(string $ciphertext): string — reverse
   - Uses APP_KEY from .env, padded/hashed to 32 bytes

3. src/Controllers/AuthController.php — add Instagram OAuth methods:
   - connectInstagram() — redirects to Instagram OAuth authorization URL with correct scopes and state parameter (CSRF protection)
   - instagramCallback() — validates state, exchanges code for access token via Guzzle POST, fetches user profile, encrypts token, saves to instagram_connections table, redirects to /dashboard with success flash; handle errors gracefully

4. src/Services/SyncService.php:
   - syncUser(int $userId): void — full sync orchestration:
     a. Create a sync_jobs row with status='running'
     b. Fetch all pages of following (paginate until no next_cursor), storing each account's is_verified status
     c. Fetch all pages of followers
     d. Upsert following and followers tables with fresh data: INSERT OR REPLACE, including is_verified column on following
     e. (CATEGORIZATION PREP) Loop through following data: for each account, calculate engagement_gap_days as null for now (will be filled by EngagementService in Prompt 7A)
     f. Diff: identify accounts in following but NOT in followers (excluding whitelist)
     g. Insert new unfollower_snapshots rows for any newly detected unfollowers
     h. Log to activity_log: event_type='sync_completed'
     i. Update sync_jobs row: status='completed', completed_at, accounts_fetched
     j. Update instagram_connections.last_sync_at
     - On RateLimitException: mark sync_jobs as 'failed' with error message, re-throw so the cron job can handle backoff
     - On any other exception: mark as 'failed', log error, do not re-throw (fail silently to the user)

5. cron/sync.php — CLI script:
   - Queries users who are due for a sync based on their plan tier interval (Free: 24h, Pro: 6h, Premium: 1h) and last_sync_at
   - For each due user, instantiates SyncService and calls syncUser()
   - Skips users with no instagram_connections row
   - Logs results to stdout (captured by cron to /var/log/unfollowiq/sync.log)

6. src/Controllers/SyncController.php:
   - trigger() — POST /sync: checks user is authenticated and has an Instagram connection, checks rate limit (REQ-SEC-008: 1 per 5min for Free), creates a sync_jobs row with status='pending', returns htmx response that shows the sync progress bar

7. Add a Settings page section (stub, full settings in Prompt 9) showing:
   - Connected Instagram account: avatar, username, connected date
   - "Disconnect Instagram" button: POST /auth/instagram/disconnect — deletes the connection, clears following/followers tables for user

Show the complete SyncService.php when done.
```

---

## PROMPT 7A — Engagement Metrics & Scoring Service

```
Read requirements.md Section 3.7 (Categorization & Engagement Scoring) fully. Read tech-stack.md notes on engagement metrics.

Build the EngagementService to fetch engagement metrics and the ScoringService to compute unfollow priority scores. These services power the ranked list categorization UI.

Tasks:

1. src/Services/EngagementService.php:
   - Constructor accepts InstagramApiService instance
   - getAccountInsights(string $instagramAccountId, int $userId): array — attempts to fetch engagement metrics for a single account:
     - If Graph API provides media insights: extract last_post_date, engagement count (likes + comments)
     - If not available: return null values (fallback: ScoringService will use heuristics)
     - Returns array with keys: last_post_date, engagement_gap_days, follower_count_snapshot, post_count_snapshot
   - syncUserEngagementMetrics(int $userId): void — for a user's entire following list:
     - Query the following table to get all accounts for the user
     - For each account, call getAccountInsights() (batch API calls if possible to respect rate limits)
     - Upsert into account_insights table (create table if needed): id, user_id, instagram_account_id, last_post_date, engagement_gap_days, follower_count_snapshot, post_count_snapshot, created_at, updated_at
     - Handle API rate limits: on 429, log and skip remaining accounts (don't crash the sync)
   - calculateEngagementGap(int $userId, string $instagramAccountId): int — compute engagement_gap_days:
     - Query user's own posts or comments via Graph API (if available) to find interaction with this account
     - Return days since last interaction; if never interacted, return days since user started following (from following table)
     - If API unavailable: estimate from account age and post frequency

2. Extend database schema (create migration or update schema.sql):
   - Table account_insights: id, user_id, instagram_account_id, last_post_date DATETIME, engagement_gap_days INT, follower_count_snapshot INT, post_count_snapshot INT, created_at TIMESTAMP, updated_at TIMESTAMP
   - Table creator_flags: id, user_id, instagram_account_id, is_verified BOOLEAN, reason VARCHAR(255), flagged_at DATETIME, created_at TIMESTAMP
   - Columns added to following table: is_verified BOOLEAN DEFAULT FALSE, engagement_gap_days INT, unfollow_priority_score INT, category VARCHAR(64)
   - Table user_scoring_preferences: user_id, inactivity_weight FLOAT DEFAULT 0.40, engagement_weight FLOAT DEFAULT 0.35, ratio_weight FLOAT DEFAULT 0.15, age_weight FLOAT DEFAULT 0.10, created_timestamp, updated_at TIMESTAMP

3. Modify SyncService to call EngagementService:
   - After syncing follower/following data, instantiate EngagementService and call syncUserEngagementMetrics()
   - This ensures engagement metrics are refreshed with every sync

Show the complete EngagementService.php when done.
```

---

## PROMPT 7B — Scoring Algorithm & Category Tagging

```
Read requirements.md Section 3.7 fully. Build on work from Prompt 7A.

The ScoringService computes the unfollow_priority_score for each account and assigns category labels based on the algorithm.

Tasks:

1. src/Services/ScoringService.php:
   - Constructor accepts user_id and optional weights (or fetches from user_scoring_preferences table)
   - calculateScore(array $accountData): int — computes a single account's unfollow_priority_score (0–100) using:
     ```
     score = (is_verified ? -50 : 0)                                          // Creator protection
           + min((engagement_gap_days / 360) * inactivity_weight * 100, 30)   // Inactivity
           + min((follower_ratio_deviation) * ratio_weight * 100, 20)         // Low commitment
           + min((days_following / 1000) * age_weight * 100, 10)              // Follow age
           capped to 0–100
     where follower_ratio_deviation = max(0, (following_count - follower_count) / following_count)
     and days_following = floor((Now - account.follow_start_date) / 86400)
     ```
   - assignCategory(int $score, array $accountData): string — returns one of: 'verified', 'inactive', 'low_engagement', 'whitelisted', 'safe' (score < 30, no category)
     - 'verified': is_verified = true (always protected)
     - 'inactive': engagement_gap_days > 90 (no posts in 90+ days)
     - 'low_engagement': engagement_gap_days is null or > 180 (never engaged or long time)
     - 'whitelisted': account in whitelist table (user-protected)
     - 'safe': score < 30 (low risk)
   - generateTooltip(int $score, array $accountData, array $weights): string — generates human-readable explanation of score:
     - Example: "Verified creator (protected, –50 pts) + 0 engagement in 180 days (–25 pts) + you follow 1.5× more than they follow you (–10 pts) = Score: 35 (Review Recommended)"
   - scoreUserFollowing(int $userId): void — batch score all accounts for a user:
     - Query following table + account_insights + user_scoring_preferences for the user
     - For each account, call calculateScore() and assignCategory()
     - UPDATE following table: set unfollow_priority_score=X, category=Y for each account
     - Log to activity_log: event_type='scoring_completed'

2. Update SyncService to call ScoringService:
   - After EngagementService completes, instantiate ScoringService and call scoreUserFollowing()
   - This ensures all ranking scores are fresh after each sync

3. src/Models/FollowingModel.php (new model):
   - getListRanked(int $userId, array $options = []): array — paginated query of following accounts sorted by unfollow_priority_score DESC; supports options:
     - filter: 'all', 'inactive', 'low_engagement', 'verified', 'whitelisted'
     - sort: 'score' (default), 'username', 'engagement_gap', 'follower_count'
     - direction: 'DESC' (default), 'ASC'
     - page, per_page
     - Returns: ['data' => [...], 'total' => int, 'pages' => int, 'average_score' => float]
   - countByCategory(int $userId): array — returns counts: ['inactive' => N, 'low_engagement' => N, 'verified' => N, 'whitelisted' => N]

Show the complete ScoringService.php when done.
```

---

## PROMPT 8 — Unfollowers List with Ranked Scoring & Bulk Unfollow Preview

```
Read requirements.md Sections 3.3, 3.7.4, and 3.9 fully. Read design-notes.md "Page: Ranked List (Unfollowers with Scoring & Categories)" section.

Build the unfollowers ranked list page with unfollow priority scores, category badges, and a bulk unfollow preview modal that requires explicit user approval.

Tasks:

1. src/Controllers/UnfollowController.php:
   - index() — render full ranked list page; passes data for first page sorted by score DESC + category filter options
   - list() — htmx partial endpoint: GET /unfollowers with query params (filter, sort, page); returns just the table partial (design-notes.md "Account Row: Scoring & Category Badges")
   - unfollow() — POST /unfollow/single/{id}: validates CSRF + auth + plan limits, marks for unfollow, removes from list, returns htmx row removal + toast OOB
   - bulkUnfollowPreview() — POST /unfollow/bulk/preview: accepts array of instagram_account_ids, returns the bulk preview modal content (not submitted yet)
     - Calculates: total count, breakdown by category, average score, any "at risk" accounts (verified that user somehow selected — should prevent this)
   - bulkUnfollowApproved() — POST /unfollow/bulk/approved: validates CSRF + explicit approval_confirmation=1 checkbox, queues all accounts, returns htmx toast + table refresh
   - Queuing Logic: respects per-session rate limits (60/hour, 150/day), per-plan batch size limits (Free: 5, Pro: 50, Premium: 200)

2. src/Models/FollowingModel.php (created in Prompt 7B):
   - getListRanked() — must return additional fields per account:
     - unfollow_priority_score (0–100)
     - category (verified | inactive | low_engagement | whitelisted | safe)
     - engagement_gap_days (null if never engaged)
     - is_verified (bool)
     - tooltip_text (generated by ScoringService::generateTooltip())
   - Client-side filtering by category using category filter tabs (Alpine.js x-model, no server round-trip for category switching within a loaded page)

3. src/Services/UnfollowQueueService.php (extend from earlier work):
   - bulkQueue(int $userId, array $instagramAccountIds): int — validates:
     - None of the accounts are verified (is_verified = 1) or in whitelist
     - Bulk batch size respects plan tier limit
     - Monthly unfollow limit not exceeded
     - Hourly and daily rate limits (60/hr, 150/day)
     - Throws VerifiedAccountException if user tries to unfollow a creator
     - Throws CapacityExceededException if batch too large
     - Throws RateLimitedException if rate limits hit
     - On success: INSERT rows into unfollow_queue, deduct from monthly_usage, log to activity_log with approval_user_id and approval_timestamp
   - processNext() — every cron run, process ONE job per user per minute after rate checks

4. src/Views/pages/unfollowers.php — full ranked list page (design-notes.md "Page: Ranked List"):
   - Page header with total count badge: "{{ total_count }} accounts to review"
   - Category filter tabs (All, Inactive 90d+, Low Engagement, Verified [Protected], Whitelisted)
   - Toolbar: search input (Alpine x-model for client-side username filtering), sort dropdown (score/username/engagement_gap), bulk action button
   - Table with columns:
     - Checkbox (Alpine-tracked for selectedCount)
     - Avatar + username
     - Category badge (populated dynamically based on category field)
     - Unfollow priority score with color bar (green 0–30, yellow 31–65, red 66–100)
     - Last interaction ("N days ago" or "Never")
     - Action buttons (Whitelist + Unfollow)
   - Select-all checkbox + "Select all N results" link
   - Empty state if no unfollowers found
   - Pagination (Bootstrap, htmx)
   - Queue status bar at top: "X accounts in unfollow queue. Estimated Y minutes."

5. src/Views/partials/bulk-unfollow-modal.php — htmx-triggered modal (from design-notes.md "Bulk Unfollow Preview Modal"):
   - Summary stats: # Inactive, # Low Engagement, Avg Score, # At Risk (verified, should be 0)
   - List of selected accounts (scrollable, showing score + category)
   - Explicit approval checkbox: "I have reviewed these accounts and approve unfollowing them"
   - Confirm button DISABLED until checkbox checked (Alpine.js)
   - Form action: POST /unfollow/bulk/approved with CSRF token + hidden input list of selected IDs

6. src/Views/partials/ranked-account-row.php — single TR partial with:
   - Checkbox, avatar + username, category badge (with tooltip on hover), score bar (with tooltip on hover), last interaction, actions

7. Update cron/queue.php:
   - After processing each unfollow, update queue status logging to activity_log with event_type='unfollow_processed'
   - Ensure rate limits are checked BEFORE processing (respect both 60/hr and 150/day)

CRITICAL SAFETY CHECKS:
- Server-side: verify no verified accounts (is_verified=1) or whitelisted accounts in the bulk list before queuing — reject if any found
- Server-side: check batch size against plan tier limit — reject if exceeded
- Client-side (UX only, not security): disable checkbox for verified/whitelisted accounts in the list to prevent accidental selection

Show the complete UnfollowQueueService.php when done.
```

---

## PROMPT 9 — Kanban Board & Activity Calendar

```
Read requirements.md Sections 3.4 and 3.5 fully. Read design-notes.md "Page: Kanban Board" and "Page: Activity Calendar" sections.

Build the Kanban board page and the Activity Calendar page.

KANBAN TASKS:

1. src/Models/KanbanModel.php:
   - getBoard(int $userId): array — returns all cards grouped by column_name, each with position ordering
   - moveCard(int $cardId, int $userId, string $newColumn): void — validates column name, updates column, logs to activity_log; if newColumn='whitelisted' also calls WhitelistModel::add()
   - addCard(int $userId, string $instagramAccountId, string $username, string $profilePictureUrl, int $followerCount): int
   - removeCard(int $cardId, int $userId): void
   - getColumnCount(int $userId): array — returns ['review'=>N, 'queued'=>N, 'unfollowed'=>N, 'whitelisted'=>N]

2. src/Controllers/KanbanController.php:
   - index() — render full kanban board page
   - moveCard() — POST /kanban/move/{id}: validate CSRF + auth + card ownership, call KanbanModel::moveCard(), if column='queued' also call UnfollowQueueService::enqueue(); return htmx response refreshing the board
   - removeCard() — POST /kanban/remove/{id}: remove from board, return htmx response

3. src/Views/pages/kanban.php — full Kanban board:
   - 4 columns from design-notes.md with correct accent colors per column (amber=Review, blue=Queued, red=Unfollowed, green=Whitelisted)
   - Each column: header with colored dot + column name (Syne font) + card count badge
   - Cards per design-notes.md: avatar, username, follower count, move buttons
   - "Queue for Unfollow" button on Review cards: moves to Queued column + enqueues
   - "Unfollow Now" button on Review/Queued cards: immediate unfollow (bypasses queue for single action)
   - Column empty state: dashed border placeholder "Drop accounts here"
   - Pro-gate: show upgrade CTA overlay if user is on Free plan (Kanban is Pro+ per requirements.md Section 5)
   - All card moves use htmx hx-post + hx-target="#kanban-board" hx-swap="outerHTML" to refresh the full board

CALENDAR TASKS:

4. src/Models/ActivityModel.php:
   - getMonthActivity(int $userId, int $year, int $month): array — returns day-keyed array ['2026-02-14' => ['unfollows' => 3, 'new_followers' => 1, ...]]
   - getDayLog(int $userId, string $date): array — returns all activity_log rows for that day
   - getMonthTotals(int $userId, int $year, int $month): array — ['total_unfollowed' => N, 'total_new_followers' => N, 'net_change' => N]

5. src/Controllers/CalendarController.php:
   - index() — render calendar page for current month
   - month() — htmx partial endpoint: GET /calendar?month=YYYY-MM — returns just the calendar grid partial
   - dayDetail() — htmx partial: GET /calendar/day/{date} — returns modal body partial with that day's log

6. src/Views/pages/calendar.php — calendar page:
   - Page header: "Activity Calendar"
   - Month navigation (prev/next) using htmx hx-get targeting #calendar-grid
   - Calendar grid implementing design-notes.md exactly: 7-col day headers, day cells with aspect-ratio:1
   - PHP-generated heatmap intensity using inline style opacity based on unfollow count (from design-notes.md example formula)
   - Activity dots: red for unfollows, green for new followers
   - Today highlighted with a border accent
   - Clicking a day cell triggers a Bootstrap modal with htmx-loaded day detail
   - Monthly totals summary row below the grid
   - Calendar history gate: Free users see only 30 days of history; older months are shown greyed out with upgrade prompt

7. src/Views/partials/calendar-grid.php — htmx partial: just the grid rows
8. src/Views/partials/calendar-day-detail.php — modal body partial: list of activity events for a day

Show the complete calendar.php view when done.
```

---

## PROMPT 10 — Subscription Billing (Stripe), Settings Page & Whitelist

```
Read requirements.md Sections 3.6, 3.9, 3.10, 6, and 7 fully.

Build the Stripe billing integration, the Settings page, and the Whitelist page.

STRIPE BILLING TASKS:

1. src/Services/StripeService.php:
   - createCheckoutSession(int $userId, string $planTier): string — creates a Stripe Checkout session for the given plan, returns the checkout URL; use Price IDs from .env (STRIPE_PRICE_PRO, STRIPE_PRICE_PREMIUM)
   - createBillingPortalSession(int $userId): string — returns Stripe Customer Portal URL for managing subscription
   - getOrCreateCustomer(int $userId): string — returns stripe_customer_id; creates in Stripe if not exists, stores in subscriptions table
   - handleWebhook(string $payload, string $sigHeader): void — verify signature using STRIPE_WEBHOOK_SECRET, dispatch to handler methods based on event type:
     - customer.subscription.created / updated: upsert subscriptions table, update users.plan_tier
     - customer.subscription.deleted: set plan_tier='free', update subscriptions.status='cancelled'
     - invoice.paid: insert into invoices table
     - invoice.payment_failed: send payment failed email, start grace period (set a flag/timestamp)

2. src/Controllers/BillingController.php:
   - checkout() — GET /billing/checkout/{plan}: validate plan param, call StripeService::createCheckoutSession(), redirect to Stripe
   - portal() — GET /billing/portal: call StripeService::createBillingPortalSession(), redirect
   - webhook() — POST /webhooks/stripe: raw body capture (do not use $_POST), call StripeService::handleWebhook(); return 200 immediately; NEVER return non-2xx to Stripe unless signature validation fails

SETTINGS PAGE TASKS:

3. src/Controllers/SettingsController.php with all methods from REQ-SETTINGS:
   - index() — render settings page with 6 Bootstrap tabs
   - updateAccount() — POST: update name, email (if changed: create new verification token, send email, set email_verified_at=null); validate input
   - updatePassword() — POST: verify current password, validate new password (min 8 chars), hash and save
   - enable2FA() — GET: generate TOTP secret, show QR code (use a QR code library or Google Charts API URL), show recovery codes
   - confirm2FA() — POST: verify the TOTP code to confirm setup, save secret to users.totp_secret, save hashed recovery codes
   - disable2FA() — POST: require password confirmation, clear totp_secret
   - updateNotifications() — POST: save notification preferences to a user_preferences table (or JSON column on users)
   - updateScoringPreferences() — POST (NEW from REQ-CAT-012): update user_scoring_preferences table with new weights (inactivity_weight, engagement_weight, ratio_weight, age_weight, creator_threshold); validate that weights sum to 100%; validate creator_threshold >= 1000; trigger re-scoring of all user's accounts
   - exportData() — GET: generate JSON export of all user data (profile, whitelist, activity_log last 90 days), stream as download
   - deleteAccount() — POST: require password + "DELETE" typed confirmation, soft-delete or hard-delete per REQ-DATA-005, send confirmation email, destroy session

4. src/Views/pages/settings.php — full settings page:
   - Bootstrap tabs (Account / Instagram / Notifications / Subscription / Scoring Preferences / Danger Zone) — tab state managed by Alpine.js (x-data with active tab)
   - Account tab: name field, email field, change password section (current + new + confirm)
   - 2FA section: if disabled — "Enable 2FA" button that expands a setup panel; if enabled — "Disable 2FA" button
   - Instagram tab: connected account card (avatar, username, follower/following counts, last sync), Sync Now button, Disconnect button
   - Notifications tab: toggle switches (Bootstrap form-check form-switch) for each notification type from requirements.md Section 8
   - Subscription tab: current plan badge, monthly usage bar, plan feature comparison table, upgrade/manage buttons
   - Scoring Preferences tab (NEW from design-notes.md "Page: Settings — Scoring Preferences Tab"):
     - Range sliders for each weight factor (inactivity, engagement, ratio, age) with live percentage display
     - Total weight indicator (must equal 100% to save)
     - Creator follower threshold input (default 10,000)
     - Sample accounts preview table showing how scores will change with new weights
     - Save button
   - Danger Zone tab: red-bordered Bootstrap card, Export My Data button, Delete Account form with "type DELETE to confirm" input

WHITELIST TASKS:

5. src/Models/WhitelistModel.php:
   - getList(int $userId): array — paginated whitelist entries
   - add(int $userId, string $instagramAccountId, string $username, string $profilePictureUrl, ?string $notes = null): void — check plan capacity limit before inserting (REQ-WHITE-006); throw CapacityExceededException if over limit
   - remove(int $whitelistId, int $userId): void
   - isWhitelisted(int $userId, string $instagramAccountId): bool
   - getCount(int $userId): int

6. src/Controllers/WhitelistController.php:
   - index() — render whitelist page
   - add() — POST /whitelist/add/{id}: validate ownership, call WhitelistModel::add(), return htmx toast + partial refresh
   - remove() — DELETE /whitelist/{id}: validate ownership, remove, return htmx partial

7. src/Views/pages/whitelist.php:
   - Page header with count badge
   - "Add by username" form at top: text input + submit button, POST to /whitelist/add-by-username
   - List of whitelisted accounts using Bootstrap list-group-flush (same visual style as unfollowers table: avatar, username, follower count, date added, optional notes, remove button)
   - Empty state from design-notes.md "Whitelist" section
   - Capacity usage bar: "X / Y whitelist slots used" with upgrade CTA for Free users

FINAL INTEGRATION CHECK:
After all tasks are complete:
- Verify the full user journey works end-to-end: Register → Verify Email → Login → Connect Instagram → Dashboard → Unfollowers List → Unfollow → Kanban → Calendar → Settings → Billing
- Confirm all htmx endpoints return proper HTML partials (not full pages)
- Confirm all POST endpoints validate CSRF tokens
- Confirm plan tier gates are enforced server-side on: unfollow quota, bulk batch size, whitelist capacity, Kanban access, calendar history depth
- List any .env variables that still need real values before the app can run

Show the complete StripeService.php webhook handler when done.
```

---

## Prompt Execution Notes

### Sequencing Rules
- Run prompts **strictly in order** — each prompt depends on code from the previous
- If a prompt fails partway through, fix the error before moving to the next prompt
- After each prompt, do a quick smoke test: can PHP parse the new files without fatal errors?

### Between Prompts — Checkpoint Questions to Ask Claude Code
After Prompt 3: *"List all registered routes and confirm the middleware chain is correct."*
After Prompt 4: *"Walk me through what happens step by step when a new user registers."*
After Prompt 7: *"What happens if the Instagram API returns a 429 during a sync? Trace the full error path."*
After Prompt 8: *"What prevents a Free user from unfollowing more than 50 accounts per month? Show me the exact server-side check."*
After Prompt 10: *"What happens when Stripe sends a subscription.deleted webhook? Trace the full code path from webhook receipt to user plan downgrade."*

### Subsequent Prompts (11+) Should Cover
- **Prompt 11:** Admin panel (user list, stats, queue monitor, admin log)
- **Prompt 12:** Email templates (HTML + plain text for all 10 triggers from requirements.md Section 8)
- **Prompt 13:** Landing / marketing page (pricing table, feature grid, hero section)
- **Prompt 14:** Security hardening (rate limiting implementation review, SQL injection audit, CSRF audit, header audit)
- **Prompt 15:** Testing & seed data (PHP test scripts, database seed with realistic mock data for UI review)
