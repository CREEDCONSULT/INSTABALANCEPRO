# Tech Stack — Instagram Unfollower Application

## Overview
A server-rendered web application that allows users to identify and manage Instagram accounts they follow who do not follow them back. Deployed on a traditional **LAMP stack** with a modern, lightweight frontend layer.

---

## Infrastructure: LAMP Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| **OS** | Linux (Ubuntu 22.04 LTS recommended) | Standard server environment |
| **Web Server** | Apache 2.4 | mod_rewrite enabled for clean URLs; `.htaccess` support required |
| **Database** | MySQL 8.0+ | Stores user sessions, cached follow/follower data, sync history, engagement metrics |
| **Backend** | PHP 8.2+ | Core application logic, Instagram API integration, session handling, scoring engine |

### Apache Configuration Notes
- Enable `mod_rewrite` for front-controller routing (`RewriteEngine On`)
- Enable `mod_headers` for cache and CORS control
- Set `AllowOverride All` on the document root directory
- Use a single entry point: `public/index.php`

### PHP Configuration Notes
- Use Composer for dependency management
- Recommended libraries:
  - `guzzlehttp/guzzle` — HTTP client for Instagram Graph API calls (including engagement insights)
  - `vlucas/phpdotenv` — Environment variable management (`.env` file)
  - A lightweight PHP router (e.g., `bramus/router` or `nikic/fast-route`)
- Session-based authentication; store Instagram OAuth tokens server-side only
- PDO with prepared statements for all MySQL queries (no raw query strings)
- **Note on Engagement Metrics:** Instagram's Graph API has limited public engagement data. Implement a fallback strategy: (1) if API provides engagement insights, use real interaction data; (2) otherwise, estimate engagement via heuristics (follower-to-following ratio, post count, last post date)

---

## Frontend Stack

### CSS Framework — Bootstrap 5.3
- **Source:** [https://getbootstrap.com](https://getbootstrap.com)
- Load via Bootstrap's official CDN as documented on getbootstrap.com
- Use Bootstrap's component library: cards, modals, tables, badges, toasts, and spinners
- Leverage Bootstrap utility classes for spacing, color, and layout (avoid custom CSS where Bootstrap utilities suffice)
- Bootstrap 5.3 includes a built-in dark/light color mode — implement a theme toggle

### JavaScript Dependencies

#### jQuery (Bootstrap dependency)
- Bootstrap 5 does **not** require jQuery for its own components, but since the project uses it, load jQuery before Bootstrap's JS bundle
- Use jQuery for any DOM manipulation helpers and AJAX calls not handled by htmx
- Load from a CDN (e.g., cdnjs or jQuery's official CDN)

#### htmx (latest stable)
- **CDN:** `https://unpkg.com/htmx.org@latest` or pin to the latest release tag
- Use htmx for all dynamic page updates: loading follower lists, triggering unfollow actions, paginating results — **without writing custom fetch/XHR JavaScript**
- Key htmx attributes used throughout the app:
  - `hx-get` / `hx-post` — trigger PHP endpoint requests
  - `hx-target` — swap specific DOM regions (e.g., the results table)
  - `hx-swap` — use `outerHTML`, `innerHTML`, or `beforeend` as appropriate
  - `hx-indicator` — show Bootstrap spinners during requests
  - `hx-confirm` — confirm dialogs before unfollow actions
  - `hx-push-url` — maintain browser history on navigation
- PHP endpoints return **HTML partials** (not JSON) for htmx consumption
- Include the `htmx-ext-response-targets` extension for handling HTTP error codes gracefully

#### Alpine.js (latest stable)
- **CDN:** `https://cdn.jsdelivr.net/npm/alpinejs@latest/dist/cdn.min.js`
- Load with `defer` attribute; must be loaded **before** htmx in the `<head>`
- Use Alpine.js for **client-side UI state only** — things that don't require a server round-trip:
  - Toggle states (show/hide filters, expand/collapse rows)
  - Checkbox selection state for bulk unfollow operations
  - Toast/alert dismissal logic
  - Real-time client-side filtering/searching of already-loaded data
  - Form validation feedback before submission
- Alpine.js and htmx coexist well; use `x-data` for component state and `hx-*` for server communication

---

## Page / Feature Structure

```
public/
├── index.php              # Front controller / entry point
├── .htaccess              # Apache rewrite rules

src/
├── Controllers/
│   ├── AuthController.php         # Instagram OAuth login/logout
│   ├── DashboardController.php    # Main dashboard + KPIs
│   ├── UnfollowController.php     # Ranked list view + bulk operations
│   └── SettingsController.php     # Settings including scoring preferences
├── Services/
│   ├── InstagramApiService.php    # Graph API wrapper (followers, following, unfollow, verified badge)
│   ├── SyncService.php            # Cache & diff followers vs. following
│   ├── EngagementService.php      # Fetch engagement metrics & interaction history
│   ├── ScoringService.php         # Compute unfollow_priority_score with configurable weights
│   └── UnfollowQueueService.php   # Queue management & rate limiting
├── Models/
│   ├── User.php
│   ├── Following.php
│   ├── CreatorFlag.php
│   └── UserScoringPreferences.php
└── Views/
    ├── layouts/
    │   └── main.php               # Base HTML layout with CDN links
    ├── partials/
    │   ├── ranked-table.php       # Ranked unfollower list with scoring
    │   ├── account-row.php        # Single account row with category badges
    │   ├── bulk-approve-modal.php # Pre-unfollow review modal
    │   ├── scoring-tooltip.php    # Tooltip explaining unfollow score
    │   └── toast.php              # Success/error notifications
    └── pages/
        ├── unfollowers.php        # Ranked list page
        ├── settings.php           # Settings with scoring preferences tab
        └── dashboard.php           # Main dashboard
```

---

## CDN Load Order (in `<head>` / before `</body>`)

```html
<!-- In <head> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.x/dist/css/bootstrap.min.css">

<!-- Alpine.js must be deferred and loaded early -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@latest/dist/cdn.min.js"></script>

<!-- htmx -->
<script src="https://unpkg.com/htmx.org@latest"></script>

<!-- Before </body> -->
<script src="https://code.jquery.com/jquery-3.x.x.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.x/dist/js/bootstrap.bundle.min.js"></script>
```

> **Note:** Replace `@latest` and `@5.3.x` / `3.x.x` with pinned version numbers before production deployment to avoid breaking changes from upstream updates.

---

## Database Schema (MySQL)

```sql
-- Stores authenticated users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instagram_user_id VARCHAR(64) UNIQUE NOT NULL,
    username VARCHAR(128) NOT NULL,
    access_token TEXT NOT NULL,         -- encrypted at rest
    token_expires_at DATETIME,
    last_sync_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Cached snapshot of who the user follows
CREATE TABLE following (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    username VARCHAR(128),
    profile_picture_url TEXT,
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Cached snapshot of who follows the user
CREATE TABLE followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    username VARCHAR(128),
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Security Requirements

- Store Instagram OAuth tokens **encrypted** in the database (use `openssl_encrypt` in PHP)
- Never expose access tokens to the frontend/JavaScript layer
- Use CSRF tokens on all POST actions (unfollow, bulk unfollow)
- Rate-limit unfollow actions to comply with Instagram API limits
- All SQL via PDO prepared statements — no raw interpolation
- HTTPS only in production (enforce via Apache redirect)
- Session tokens use `HttpOnly` and `Secure` cookie flags

---

## Environment Configuration (`.env`)

```ini
APP_ENV=production
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_NAME=instagram_unfollower
DB_USER=dbuser
DB_PASS=strongpassword

INSTAGRAM_APP_ID=your_app_id
INSTAGRAM_APP_SECRET=your_app_secret
INSTAGRAM_REDIRECT_URI=https://yourdomain.com/auth/callback

ENCRYPTION_KEY=32_byte_random_key_here
```

---

## Development vs. Production Notes

- Use `APP_ENV=development` to enable verbose PHP error display and htmx request logging
- In production, disable PHP `display_errors` and log to file
- Consider a MySQL query cache or Redis (optional) to reduce Instagram API calls during development/testing
- Use Instagram's **sandbox mode** and test accounts during development — the Graph API has strict rate limits on unfollowing

---

## Key Constraints & Conventions for Claude Code

1. **No frontend build step** — no webpack, npm, or node_modules. All JS/CSS loaded from CDN.
2. **PHP renders HTML, not JSON** — htmx endpoints return HTML partials, not API responses.
3. **Alpine.js = UI state only** — no business logic in Alpine; keep it to reactive display state.
4. **htmx = server communication** — prefer htmx attributes over custom jQuery AJAX.
5. **Bootstrap components only** — do not introduce a second CSS framework or custom component library.
6. **Single entry point** — all requests route through `public/index.php` via Apache rewrite.
7. **Follow PSR-4 autoloading** via Composer for all PHP classes under `src/`.
