# Design Notes — Instagram Unfollower Application

## Design Philosophy

**Aesthetic Direction:** Refined dark-mode dashboard — utilitarian precision with a social-media-native personality. Think analytics tool meets Instagram's visual language: clean data surfaces, bold accent colors, confident typography, and purposeful motion. The UI should feel like a professional power tool, not a toy.

**Core Feeling:** In control. Data-rich but never cluttered. Every element earns its space.

**Color Palette (CSS Variables)**
```css
:root {
  --ig-gradient-start: #833ab4;   /* Instagram purple */
  --ig-gradient-mid:   #fd1d1d;   /* Instagram red */
  --ig-gradient-end:   #fcb045;   /* Instagram gold */

  --surface-bg:        #0d0d0d;   /* App background */
  --surface-card:      #1a1a1a;   /* Card backgrounds */
  --surface-elevated:  #242424;   /* Modals, dropdowns */
  --surface-border:    #2e2e2e;   /* Borders and dividers */

  --text-primary:      #f5f5f5;
  --text-secondary:    #a0a0a0;
  --text-muted:        #606060;

  --accent-primary:    #c13584;   /* Primary action color */
  --accent-success:    #26de81;
  --accent-danger:     #fc5c65;
  --accent-warning:    #fed330;
  --accent-info:       #45aaf2;
}
```

**Typography**
- Display / Headings: `Syne` (Google Fonts) — geometric, confident, modern
- Body / UI Text: `DM Sans` (Google Fonts) — legible, neutral, pairs well with Syne
- Monospace / Numbers: `JetBrains Mono` — for follower counts, dates, stats

```html
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
```

---

## Bootstrap 5.3 Theme Customization

Override Bootstrap's default theme tokens in a `custom.scss` or with CSS variables after the Bootstrap CDN link:

```css
/* After Bootstrap CSS CDN link */
[data-bs-theme="dark"] {
  --bs-body-bg:           #0d0d0d;
  --bs-body-color:        #f5f5f5;
  --bs-card-bg:           #1a1a1a;
  --bs-card-border-color: #2e2e2e;
  --bs-border-color:      #2e2e2e;
  --bs-secondary-bg:      #242424;
  --bs-tertiary-bg:       #1a1a1a;
  --bs-primary-rgb:       193, 53, 132;   /* accent-primary */
}
```

Set the theme on the `<html>` tag:
```html
<html lang="en" data-bs-theme="dark">
```

Bootstrap 5.3's native dark mode means most components (cards, tables, modals, navbars) render correctly without additional overrides.

---

## Layout: Application Shell

### Sidebar + Main Content (offcanvas-on-mobile pattern)

Use Bootstrap's **`d-flex`** layout with a fixed-width sidebar and a scrollable main area. On mobile, the sidebar becomes a Bootstrap **Offcanvas** component.

```
┌──────────────┬─────────────────────────────────────┐
│   Sidebar    │         Main Content Area           │
│   (260px)    │  (flex-grow-1, overflow-y: auto)    │
│              │                                     │
│  - Logo      │  ┌─────────────────────────────┐   │
│  - Nav links │  │  Page Header + Breadcrumb   │   │
│  - User info │  └─────────────────────────────┘   │
│              │  ┌─────────────────────────────┐   │
│              │  │  Page Content               │   │
│              │  └─────────────────────────────┘   │
└──────────────┴─────────────────────────────────────┘
```

**Sidebar HTML Structure (Bootstrap Nav):**
```html
<nav id="sidebar" class="d-flex flex-column flex-shrink-0 p-3" style="width: 260px; min-height: 100vh; background: var(--surface-card); border-right: 1px solid var(--surface-border);">

  <!-- Logo / Brand -->
  <a href="/" class="d-flex align-items-center mb-4 text-decoration-none">
    <span class="fs-5 fw-bold" style="font-family: 'Syne', sans-serif; background: linear-gradient(135deg, var(--ig-gradient-start), var(--ig-gradient-end)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
      UnfollowIQ
    </span>
  </a>

  <!-- Navigation -->
  <ul class="nav nav-pills flex-column mb-auto gap-1">
    <li class="nav-item">
      <a href="/dashboard" class="nav-link active" aria-current="page">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
      </a>
    </li>
    <li>
      <a href="/unfollowers" class="nav-link text-secondary">
        <i class="bi bi-person-x me-2"></i> Unfollowers
      </a>
    </li>
    <li>
      <a href="/kanban" class="nav-link text-secondary">
        <i class="bi bi-kanban me-2"></i> Manage Board
      </a>
    </li>
    <li>
      <a href="/calendar" class="nav-link text-secondary">
        <i class="bi bi-calendar3 me-2"></i> Activity Calendar
      </a>
    </li>
    <li>
      <a href="/whitelist" class="nav-link text-secondary">
        <i class="bi bi-shield-check me-2"></i> Whitelist
      </a>
    </li>
    <li>
      <a href="/settings" class="nav-link text-secondary">
        <i class="bi bi-gear me-2"></i> Settings
      </a>
    </li>
  </ul>

  <!-- User Avatar + Dropdown (bottom of sidebar) -->
  <hr style="border-color: var(--surface-border);">
  <div class="dropdown">
    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
      <img src="{{ user.avatar }}" alt="" width="36" height="36" class="rounded-circle me-2">
      <span class="text-truncate" style="max-width: 140px; font-size: .875rem;">{{ user.username }}</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
      <li><a class="dropdown-item" href="/settings">Settings</a></li>
      <li><a class="dropdown-item" href="/sync">Sync Now</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="/logout">Sign out</a></li>
    </ul>
  </div>
</nav>
```

**Bootstrap icons** (`bootstrap-icons` CDN) are used throughout for consistent iconography.

---

## Page: Dashboard

The dashboard is the first screen after login. It summarizes account health at a glance.

### Stat Cards (top row)
Use Bootstrap **Cards** in a responsive grid. Each card displays one KPI.

```html
<div class="row g-3 mb-4">

  <!-- Stat Card -->
  <div class="col-12 col-sm-6 col-xl-3">
    <div class="card h-100" style="background: var(--surface-card); border-color: var(--surface-border);">
      <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <span class="text-secondary" style="font-size: .8rem; text-transform: uppercase; letter-spacing: .08em;">Not Following Back</span>
          <span class="badge rounded-pill" style="background: rgba(252,92,101,.15); color: var(--accent-danger);">
            <i class="bi bi-arrow-up-short"></i> 12
          </span>
        </div>
        <div>
          <div class="display-6 fw-bold mb-1" style="font-family: 'JetBrains Mono'; color: var(--accent-danger);">{{ count }}</div>
          <p class="text-secondary mb-0" style="font-size:.8rem;">accounts identified</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Repeat for: Following, Followers, Mutual, Ghosters -->
</div>
```

### Instagram Gradient Divider Accent
Add a 3px gradient bar below any hero section or page header:
```html
<div style="height: 3px; background: linear-gradient(90deg, var(--ig-gradient-start), var(--ig-gradient-mid), var(--ig-gradient-end)); border-radius: 2px;" class="mb-4"></div>
```

### Recent Activity Feed
Use Bootstrap's **List Group** (`list-group-flush`) inside a card with a fixed-height scrollable area:
```html
<div class="card" style="background: var(--surface-card); border-color: var(--surface-border);">
  <div class="card-header d-flex justify-content-between align-items-center" style="border-color: var(--surface-border);">
    <span style="font-family: 'Syne';">Recent Activity</span>
    <span class="badge bg-secondary">Today</span>
  </div>
  <div style="max-height: 340px; overflow-y: auto;">
    <ul class="list-group list-group-flush">
      <li class="list-group-item d-flex align-items-center gap-3" style="background: transparent; border-color: var(--surface-border);">
        <img src="{{ avatar }}" class="rounded-circle" width="36" height="36">
        <div class="flex-grow-1">
          <div class="fw-medium">@username</div>
          <div class="text-secondary" style="font-size:.8rem;">Unfollowed 2 hours ago</div>
        </div>
        <span class="badge" style="background: rgba(252,92,101,.15); color: var(--accent-danger);">Unfollowed</span>
      </li>
    </ul>
  </div>
</div>
```

### Sync Progress
Use Bootstrap **Progress** bar with a striped animated variant during live syncing:
```html
<!-- Shown via htmx when sync is in progress -->
<div class="progress mb-3" style="height: 6px; background: var(--surface-elevated);">
  <div class="progress-bar progress-bar-striped progress-bar-animated"
       style="background: linear-gradient(90deg, var(--ig-gradient-start), var(--ig-gradient-end)); width: {{ percent }}%">
  </div>
</div>
```

---

## Page: Unfollowers List

The core feature page — a sortable, filterable table of accounts not following back.

### Toolbar (Filters + Bulk Actions)
```html
<div class="d-flex flex-wrap gap-2 align-items-center mb-3">

  <!-- Search -->
  <div class="input-group" style="max-width: 260px;">
    <span class="input-group-text" style="background: var(--surface-elevated); border-color: var(--surface-border);">
      <i class="bi bi-search text-secondary"></i>
    </span>
    <input type="text" class="form-control" placeholder="Search username…"
           style="background: var(--surface-elevated); border-color: var(--surface-border); color: var(--text-primary);"
           x-model="search">
  </div>

  <!-- Filter Dropdown -->
  <div class="dropdown">
    <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
      <i class="bi bi-funnel me-1"></i> Filter
    </button>
    <ul class="dropdown-menu dropdown-menu-dark">
      <li><a class="dropdown-item" hx-get="/unfollowers?filter=never-followed" hx-target="#results-table">Never followed back</a></li>
      <li><a class="dropdown-item" hx-get="/unfollowers?filter=recently-unfollowed" hx-target="#results-table">Recently unfollowed</a></li>
      <li><a class="dropdown-item" hx-get="/unfollowers?filter=inactive" hx-target="#results-table">Inactive accounts</a></li>
    </ul>
  </div>

  <!-- Bulk Unfollow (Alpine-toggled, only shows when items selected) -->
  <button class="btn btn-danger ms-auto" x-show="selectedCount > 0"
          hx-post="/unfollow/bulk" hx-include="[name='selected_ids']"
          hx-confirm="Unfollow selected accounts?"
          hx-target="#results-table" hx-swap="outerHTML">
    <i class="bi bi-person-x me-1"></i> Unfollow Selected (<span x-text="selectedCount"></span>)
  </button>

</div>
```

### Results Table
Use Bootstrap's **Table** with `table-hover` and custom row styling:
```html
<div class="table-responsive" id="results-table" x-data="{ selectedCount: 0 }">
  <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: var(--surface-elevated); color: var(--text-primary); border-color: var(--surface-border);">
    <thead style="border-color: var(--surface-border);">
      <tr style="font-size:.75rem; text-transform: uppercase; letter-spacing: .08em; color: var(--text-secondary);">
        <th style="width:40px;"><input type="checkbox" class="form-check-input"></th>
        <th>Account</th>
        <th>Followers</th>
        <th>Following</th>
        <th>Ratio</th>
        <th>Since</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <!-- htmx partial: unfollower-row.php -->
      <tr>
        <td><input type="checkbox" class="form-check-input" name="selected_ids" value="{{ id }}"></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <img src="{{ avatar }}" class="rounded-circle" width="36" height="36">
            <div>
              <div class="fw-medium">@{{ username }}</div>
              <div class="text-secondary" style="font-size:.75rem;">{{ full_name }}</div>
            </div>
          </div>
        </td>
        <td style="font-family:'JetBrains Mono'; font-size:.875rem;">{{ followers }}</td>
        <td style="font-family:'JetBrains Mono'; font-size:.875rem;">{{ following }}</td>
        <td>
          <span class="badge" style="background: rgba(38,222,129,.12); color: var(--accent-success);">{{ ratio }}</span>
        </td>
        <td class="text-secondary" style="font-size:.8rem;">{{ since }}</td>
        <td>
          <div class="d-flex gap-1 justify-content-end">
            <button class="btn btn-sm btn-outline-secondary" title="Add to whitelist"
                    hx-post="/whitelist/add/{{ id }}" hx-swap="none">
              <i class="bi bi-shield-plus"></i>
            </button>
            <button class="btn btn-sm btn-danger" title="Unfollow"
                    hx-post="/unfollow/{{ id }}"
                    hx-confirm="Unfollow @{{ username }}?"
                    hx-target="closest tr" hx-swap="outerHTML swap:300ms">
              <i class="bi bi-person-x"></i>
            </button>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</div>
```

### Pagination
Use Bootstrap's **Pagination** component with htmx:
```html
<nav aria-label="Page navigation" class="mt-3">
  <ul class="pagination pagination-sm justify-content-center">
    <li class="page-item"><a class="page-link" hx-get="/unfollowers?page={{ prev }}" hx-target="#results-table">Previous</a></li>
    <li class="page-item active"><a class="page-link">{{ current }}</a></li>
    <li class="page-item"><a class="page-link" hx-get="/unfollowers?page={{ next }}" hx-target="#results-table">Next</a></li>
  </ul>
</nav>
```

---

## Page: Kanban Board (Manage Board)

The Kanban board lets users organize accounts into workflow stages: **Review → Queued → Unfollowed → Whitelisted**.

### Column Layout
Use Bootstrap's **`d-flex`** with horizontal scroll and fixed-width columns. Each column is a card.

```html
<div class="d-flex gap-3 pb-3" style="overflow-x: auto; min-height: calc(100vh - 200px);">

  <!-- Kanban Column -->
  <div class="flex-shrink-0" style="width: 300px;">
    <div class="card h-100" style="background: var(--surface-card); border-color: var(--surface-border);">

      <!-- Column Header -->
      <div class="card-header d-flex justify-content-between align-items-center" style="border-color: var(--surface-border);">
        <div class="d-flex align-items-center gap-2">
          <span class="rounded-circle d-inline-block" style="width:10px; height:10px; background: var(--accent-warning);"></span>
          <span style="font-family:'Syne'; font-size:.9rem;">Review</span>
        </div>
        <span class="badge bg-secondary">{{ count }}</span>
      </div>

      <!-- Column Cards -->
      <div class="card-body d-flex flex-column gap-2 overflow-y-auto" style="max-height: calc(100vh - 280px);"
           id="column-review">

        <!-- Account Card (Kanban item) -->
        <div class="card" style="background: var(--surface-elevated); border-color: var(--surface-border); cursor: grab;">
          <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 mb-2">
              <img src="{{ avatar }}" class="rounded-circle" width="32" height="32">
              <div class="flex-grow-1 overflow-hidden">
                <div class="fw-medium text-truncate" style="font-size:.85rem;">@{{ username }}</div>
                <div class="text-secondary text-truncate" style="font-size:.7rem;">{{ follower_count }} followers</div>
              </div>
            </div>
            <!-- Move actions -->
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-secondary flex-grow-1" style="font-size:.7rem;"
                      hx-post="/kanban/move/{{ id }}" hx-vals='{"column": "queued"}'
                      hx-target="#kanban-board" hx-swap="outerHTML">
                Queue
              </button>
              <button class="btn btn-sm flex-grow-1" style="font-size:.7rem; background: var(--accent-danger); border: none; color: #fff;"
                      hx-post="/unfollow/{{ id }}" hx-target="#kanban-board">
                Unfollow
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Repeat columns: Queued, Unfollowed, Whitelisted -->
</div>
```

**Column Color Coding:**
| Column | Accent Color |
|--------|-------------|
| Review | `--accent-warning` (amber) |
| Queued | `--accent-info` (blue) |
| Unfollowed | `--accent-danger` (red) |
| Whitelisted | `--accent-success` (green) |

---

## Page: Ranked List (Unfollowers with Scoring & Categories)

The primary workflow for identifying and approving accounts to unfollow. Each account is scored (0–100) based on engagement, inactivity, and follower ratio. Users review ranked accounts with transparent category labels and approve bulk unfollows through a preview modal.

### Account Row: Scoring & Category Badges

Each row now includes:
- **Unfollow Priority Score** (0–100): Color-coded bar (green 0–30, yellow 31–65, red 66–100)
- **Category Badge**: `[Verified]` `[Inactive 90d+]` `[Low Engagement]` `[Whitelisted]` (or none)
- **Hover Tooltip**: Explains score calculation (e.g., "Verified creator (protected) + 0 engagement in 180 days (–25 pts) + high follow ratio (–10 pts) = Score: 35")

```html
<tr>
  <td><input type="checkbox" class="form-check-input" name="selected_ids" value="{{ account_id }}"></td>
  <td>
    <div class="d-flex align-items-center gap-2">
      <img src="{{ profile_picture_url }}" class="rounded-circle" width="36" height="36" alt="@{{ username }}">
      <div>
        <div class="fw-medium">@{{ username }}</div>
        <div class="text-secondary" style="font-size:.75rem;">{{ full_name }}</div>
      </div>
    </div>
  </td>

  <!-- Category Badge -->
  <td>
    {% if is_verified %}
      <span class="badge rounded-pill" style="background: rgba(76,175,80,.2); color: var(--accent-success);"
            data-bs-toggle="tooltip" title="Instagram verified creator — automatically protected from unfollow.">
        <i class="bi bi-patch-check-fill me-1"></i> Verified
      </span>
    {% elseif category == "inactive" %}
      <span class="badge rounded-pill" style="background: rgba(128,128,128,.2); color: var(--text-muted);"
            data-bs-toggle="tooltip" title="No posts in 90+ days.">
        <i class="bi bi-calendar-x me-1"></i> Inactive 90d+
      </span>
    {% elseif category == "low_engagement" %}
      <span class="badge rounded-pill" style="background: rgba(252,92,101,.15); color: var(--accent-danger);"
            data-bs-toggle="tooltip" title="You have never engaged with this account's posts.">
        <i class="bi bi-chat-left-x me-1"></i> Low Engagement
      </span>
    {% elseif is_whitelisted %}
      <span class="badge rounded-pill" style="background: rgba(69,170,242,.15); color: var(--accent-info);"
            data-bs-toggle="tooltip" title="User-protected account. Excluded from all unfollow operations.">
        <i class="bi bi-shield-check me-1"></i> Whitelisted
      </span>
    {% endif %}
  </td>

  <!-- Unfollow Priority Score with Color Bar -->
  <td>
    <div class="d-flex align-items-center gap-2">
      <div style="width: 80px;">
        <div style="background: var(--surface-elevated); height: 6px; border-radius: 3px; overflow: hidden;">
          <div style="height: 100%;
                     width: {{ (score / 100 * 100) }}%;
                     background: {% if score < 30 %}var(--accent-success){% elseif score < 65 %}var(--accent-warning){% else %}var(--accent-danger){% endif %};
                     transition: width 200ms ease;"
               data-bs-toggle="tooltip"
               title="{{ tooltip_text }}">
          </div>
        </div>
      </div>
      <span style="font-family:'JetBrains Mono'; font-weight: bold; font-size:.9rem;">{{ score }}</span>
    </div>
  </td>

  <!-- Last Interaction -->
  <td class="text-secondary" style="font-size:.8rem;">
    {% if engagement_gap_days is null %}
      Never
    {% else %}
      {{ engagement_gap_days }} days ago
    {% endif %}
  </td>

  <!-- Actions -->
  <td>
    <div class="d-flex gap-1 justify-content-end">
      <button class="btn btn-sm btn-outline-secondary" title="Add to whitelist"
              hx-post="/whitelist/add/{{ account_id }}" hx-swap="none">
        <i class="bi bi-shield-plus"></i>
      </button>
      <button class="btn btn-sm btn-outline-danger" title="Unfollow"
              hx-post="/unfollow/single/{{ account_id }}"
              hx-confirm="Unfollow @{{ username }}?"
              hx-target="closest tr" hx-swap="outerHTML swap:300ms">
        <i class="bi bi-person-x"></i>
      </button>
    </div>
  </td>
</tr>
```

### Tooltip Content (Score Explanation)

Example tooltip for an account with score 35:
```
Ranking Summary:
✓ Verified creator (protected, –50 pts)
✗ No engagement in 180 days (–25 pts)
✗ You follow 2.5× more than they follow you (–10 pts)
→ Total Score: 35 (Review Recommended)
```

Customize the tooltip per account using the scoring algorithm output:
- Base score 100
- Deduct if verified: –50
- Deduct for inactivity: (engagement_gap_days / 360) × 30, up to 30 pts
- Deduct for low commitment (follower ratio): up to 20 pts
- Deduct for old follows: up to 10 pts
- Final: capped 0–100

### Category Filter Tabs

Replace the dropdown with tab buttons at the top of the table for quick filtering:

```html
<ul class="nav nav-pills mb-3">
  <li class="nav-item">
    <a class="nav-link active" hx-get="/unfollowers?category=all" hx-target="#results-table" href="#">
      All <span class="badge bg-secondary ms-1">{{ total_count }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" hx-get="/unfollowers?category=inactive" hx-target="#results-table" href="#">
      Inactive 90d+ <span class="badge bg-secondary ms-1">{{ inactive_count }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" hx-get="/unfollowers?category=low-engagement" hx-target="#results-table" href="#">
      Low Engagement <span class="badge bg-secondary ms-1">{{ low_engagement_count }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" hx-get="/unfollowers?category=verified" hx-target="#results-table" href="#">
      Verified (Protected) <span class="badge bg-secondary ms-1">{{ verified_count }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" hx-get="/unfollowers?category=whitelisted" hx-target="#results-table" href="#">
      Whitelisted <span class="badge bg-secondary ms-1">{{ whitelisted_count }}</span>
    </a>
  </li>
</ul>
```

### Bulk Unfollow Preview Modal

Before bulk action, show a review modal with account breakdown:

```html
<!-- Modal triggered when user clicks "Unfollow Selected (N)" button -->
<div class="modal fade" id="bulkUnfollowModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background: var(--surface-card); border-color: var(--surface-border);">

      <div class="modal-header" style="border-color: var(--surface-border);">
        <h5 class="modal-title">Review & Approve Unfollow</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <p class="mb-3">You're about to unfollow <strong>{{ selected_count }}</strong> accounts. Review the breakdown:</p>

        <!-- Summary by Category -->
        <div class="mb-4">
          <h6 style="font-family:'Syne'; font-size:.9rem; text-transform: uppercase; color: var(--text-secondary);">Summary</h6>
          <div class="row g-2">
            <div class="col-6 col-md-3">
              <div class="card p-3" style="background: var(--surface-elevated); border-color: var(--surface-border); text-align: center;">
                <div style="font-size: .75rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">Inactive</div>
                <div style="font-family:'JetBrains Mono'; font-size: 1.5rem; font-weight: bold;">{{ inactive_breakdown }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card p-3" style="background: var(--surface-elevated); border-color: var(--surface-border); text-align: center;">
                <div style="font-size: .75rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">Low Engagement</div>
                <div style="font-family:'JetBrains Mono'; font-size: 1.5rem; font-weight: bold;">{{ low_engagement_breakdown }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card p-3" style="background: var(--surface-elevated); border-color: var(--surface-border); text-align: center;">
                <div style="font-size: .75rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">Avg Score</div>
                <div style="font-family:'JetBrains Mono'; font-size: 1.5rem; font-weight: bold;">{{ average_score }}</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card p-3" style="background: var(--surface-elevated); border-color: var(--surface-border); text-align: center;">
                <div style="font-size: .75rem; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 0.5rem;">At Risk</div>
                <div style="font-family:'JetBrains Mono'; font-size: 1.5rem; font-weight: bold; color: var(--accent-danger);">{{ verified_protected }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- List of accounts to be unfollowed -->
        <div class="mb-3">
          <h6 style="font-family:'Syne'; font-size:.9rem; text-transform: uppercase; color: var(--text-secondary);">Accounts</h6>
          <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--surface-border); border-radius: 0.375rem; padding: 0.75rem;">
            <div class="list-group list-group-flush">
              <!-- Loop through selected accounts -->
              {% for account in selected_accounts %}
              <div class="list-group-item d-flex align-items-center gap-2 py-2" style="background: transparent; border-color: var(--surface-border);">
                <img src="{{ account.profile_picture_url }}" class="rounded-circle" width="24" height="24">
                <div class="flex-grow-1" style="font-size: .85rem;">
                  <span class="fw-medium">@{{ account.username }}</span>
                  <span class="text-secondary"> — Score: {{ account.score }}</span>
                </div>
                <span class="badge" style="background: rgba(252,92,101,.15); color: var(--accent-danger);">{{ account.category }}</span>
              </div>
              {% endfor %}
            </div>
          </div>
        </div>

        <!-- Explicit Approval Checkbox -->
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="approvalCheckbox" required>
          <label class="form-check-label" for="approvalCheckbox">
            I have reviewed these accounts and approve unfollowing them
          </label>
        </div>

      </div>

      <div class="modal-footer" style="border-color: var(--surface-border);">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <form id="bulkUnfollowForm" hx-post="/unfollow/bulk/approved" hx-target="#results-table" hx-include="input[name='selected_ids']">
          <button type="submit" id="confirmUnfollowBtn" class="btn btn-danger" disabled>
            <i class="bi bi-person-x me-1"></i> Confirm Unfollow
          </button>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- Alpine.js: Enable button only when checkbox is checked -->
<script>
  document.getElementById('approvalCheckbox').addEventListener('change', function() {
    document.getElementById('confirmUnfollowBtn').disabled = !this.checked;
  });
</script>
```

---
````

Shows daily unfollow/sync activity in a monthly calendar grid.

### Calendar Grid Layout
Bootstrap's **grid** system (`row` + `col`) builds a 7-column calendar:

```html
<div class="card" style="background: var(--surface-card); border-color: var(--surface-border);">
  <div class="card-header d-flex justify-content-between align-items-center" style="border-color: var(--surface-border);">
    <button class="btn btn-sm btn-outline-secondary" hx-get="/calendar?month={{ prev_month }}" hx-target="#calendar-grid">
      <i class="bi bi-chevron-left"></i>
    </button>
    <span style="font-family:'Syne'; font-size:1rem;">{{ month_name }} {{ year }}</span>
    <button class="btn btn-sm btn-outline-secondary" hx-get="/calendar?month={{ next_month }}" hx-target="#calendar-grid">
      <i class="bi bi-chevron-right"></i>
    </button>
  </div>

  <div class="card-body p-3" id="calendar-grid">

    <!-- Day Headers -->
    <div class="row g-0 mb-2">
      <div class="col text-center text-secondary" style="font-size:.7rem; text-transform: uppercase; letter-spacing:.06em;">Sun</div>
      <!-- Mon, Tue, Wed, Thu, Fri, Sat -->
    </div>

    <!-- Calendar Days (6 rows x 7 cols) -->
    <div class="row g-1">
      <div class="col" style="aspect-ratio: 1;">
        <div class="h-100 rounded-2 d-flex flex-column align-items-center justify-content-center position-relative"
             style="background: var(--surface-elevated); cursor: pointer; border: 1px solid var(--surface-border);"
             data-bs-toggle="tooltip" title="{{ activity_summary }}">
          <span style="font-size:.8rem; font-family:'DM Sans';">{{ day }}</span>
          <!-- Activity indicator dots -->
          <div class="d-flex gap-1 mt-1">
            <span class="rounded-circle" style="width:5px;height:5px;background:var(--accent-danger);" title="Unfollowed"></span>
            <span class="rounded-circle" style="width:5px;height:5px;background:var(--accent-success);" title="New followers"></span>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
```

**Activity heatmap** — days with more unfollows get progressively deeper background tones using inline style from PHP:
```php
$intensity = min(1, $unfollowCount / 20);
$alpha = 0.1 + ($intensity * 0.5);
echo "background: rgba(193,53,132, {$alpha});";
```

### Day Detail Modal
When a calendar day is clicked, a Bootstrap **Modal** opens (triggered via htmx partial):
```html
<div class="modal fade" id="dayDetailModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background: var(--surface-elevated); border-color: var(--surface-border);">
      <div class="modal-header" style="border-color: var(--surface-border);">
        <h6 class="modal-title" style="font-family:'Syne';">Activity — {{ date }}</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- htmx loads this partial -->
        <div hx-get="/calendar/day/{{ date }}" hx-trigger="revealed" hx-swap="innerHTML">
          <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-secondary"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
```

---

## Page: Whitelist

A protected list of accounts the user never wants to unfollow.

### Empty State
```html
<div class="text-center py-5">
  <i class="bi bi-shield-check display-4 mb-3" style="color: var(--accent-success);"></i>
  <h5 style="font-family:'Syne';">Your whitelist is empty</h5>
  <p class="text-secondary">Add accounts you never want to unfollow.</p>
</div>
```

Use Bootstrap's **`badge`** + **`list-group`** to render whitelisted accounts in the same visual style as the unfollowers list.

---

## Page: Settings — Scoring Preferences Tab

Allow users to customize the unfollow priority scoring algorithm by adjusting weights for each factor.

### Scoring Weights Form

```html
<div class="card p-4" style="background: var(--surface-card); border-color: var(--surface-border);">
  <h6 style="font-family:'Syne'; font-size:.95rem; text-transform: uppercase; margin-bottom:1.5rem;">
    Customize Ranking Algorithm
  </h6>

  <p class="text-secondary mb-3" style="font-size:.85rem;">
    Adjust the importance of each factor when calculating unfollow priority scores. Total must equal 100%.
  </p>

  <form hx-post="/settings/scoring-preferences" hx-target="#scoring-form">

    <!-- Inactivity Weight -->
    <div class="mb-4">
      <label for="inactivityWeight" class="form-label">
        Inactivity Weight
        <span class="text-secondary" style="font-size:.8rem;">(<span id="inactivityPercentage">40</span>%)</span>
      </label>
      <input type="range" class="form-range" id="inactivityWeight" name="inactivity_weight"
             min="0" max="100" value="40" step="5"
             x-model="weights.inactivity"
             @input="updateTotal()">
      <div class="form-text">Days since account's last post or your last interaction</div>
    </div>

    <!-- Engagement Weight -->
    <div class="mb-4">
      <label for="engagementWeight" class="form-label">
        Engagement Weight
        <span class="text-secondary" style="font-size:.8rem;">(<span id="engagementPercentage">35</span>%)</span>
      </label>
      <input type="range" class="form-range" id="engagementWeight" name="engagement_weight"
             min="0" max="100" value="35" step="5"
             x-model="weights.engagement"
             @input="updateTotal()">
      <div class="form-text">Whether you've ever engaged (liked/commented) on their posts</div>
    </div>

    <!-- Follower Ratio Weight -->
    <div class="mb-4">
      <label for="ratioWeight" class="form-label">
        Follower Ratio Weight
        <span class="text-secondary" style="font-size:.8rem;">(<span id="ratioPercentage">15</span>%)</span>
      </label>
      <input type="range" class="form-range" id="ratioWeight" name="ratio_weight"
             min="0" max="100" value="15" step="5"
             x-model="weights.ratio"
             @input="updateTotal()">
      <div class="form-text">You follow them but they don't follow you as much back</div>
    </div>

    <!-- Follow Age Weight -->
    <div class="mb-4">
      <label for="ageWeight" class="form-label">
        Follow Age Weight
        <span class="text-secondary" style="font-size:.8rem;">(<span id="agePercentage">10</span>%)</span>
      </label>
      <input type="range" class="form-range" id="ageWeight" name="age_weight"
             min="0" max="100" value="10" step="5"
             x-model="weights.age"
             @input="updateTotal()">
      <div class="form-text">How long you've been following them</div>
    </div>

    <!-- Total Weight Display -->
    <div class="alert alert-info d-flex align-items-center gap-2 mb-3" style="background: rgba(69,170,242,.1); border-color: rgba(69,170,242,.2); color: var(--text-primary);">
      <i class="bi bi-info-circle"></i>
      <span>Total Weight: <strong id="totalPercentage">100</strong>%</span>
    </div>

    <!-- Creator Protection Threshold -->
    <div class="mb-4">
      <label for="creatorThreshold" class="form-label">
        Creator Follower Threshold
        <span class="text-secondary" style="font-size:.8rem;">({{ creator_threshold:0 }} followers)</span>
      </label>
      <input type="number" class="form-control" id="creatorThreshold" name="creator_threshold"
             value="{{ creator_threshold }}" min="1000" step="1000"
             style="background: var(--surface-elevated); border-color: var(--surface-border); color: var(--text-primary);">
      <div class="form-text">Accounts with at least this many followers are automatically protected as creators (unless verified)</div>
    </div>

    <!-- Save Button -->
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-check-circle me-1"></i> Save Preferences
    </button>

  </form>

</div>

<!-- Alpine.js for weight updates -->
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('scoringForm', () => ({
      weights: {
        inactivity: 40,
        engagement: 35,
        ratio: 15,
        age: 10
      },
      updateTotal() {
        const total = this.weights.inactivity + this.weights.engagement + this.weights.ratio + this.weights.age;
        document.getElementById('inactivityPercentage').textContent = this.weights.inactivity;
        document.getElementById('engagementPercentage').textContent = this.weights.engagement;
        document.getElementById('ratioPercentage').textContent = this.weights.ratio;
        document.getElementById('agePercentage').textContent = this.weights.age;
        document.getElementById('totalPercentage').textContent = total;

        // Optional: disable submit if total !== 100
        if (total !== 100) {
          document.querySelector('button[type="submit"]').classList.add('disabled');
        } else {
          document.querySelector('button[type="submit"]').classList.remove('disabled');
        }
      }
    }))
  })
</script>
```

### Preview: How Your Scores Will Change

After weight adjustments, show a preview table with 5 sample accounts and their new calculated scores:

```html
<div class="card mt-4" style="background: var(--surface-card); border-color: var(--surface-border);">
  <div class="card-header" style="border-color: var(--surface-border);">
    <h6 style="font-family:'Syne'; margin:0;">Score Preview (Sample Accounts)</h6>
  </div>
  <table class="table table-sm table-hover mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: var(--surface-elevated); color: var(--text-primary); border-color: var(--surface-border);">
    <thead style="border-color: var(--surface-border);">
      <tr style="font-size:.75rem; text-transform: uppercase; color: var(--text-secondary);">
        <th>Account</th>
        <th>Inactivity</th>
        <th>Engagement</th>
        <th>Ratio</th>
        <th>Follow Age</th>
        <th>New Score</th>
      </tr>
    </thead>
    <tbody>
      <!-- Populate with example accounts from user's current following list -->
      {% for sample in preview_samples %}
      <tr>
        <td class="fw-medium">@{{ sample.username }}</td>
        <td><small style="color: var(--text-secondary);">{{ sample.inactivity_pts }} pts</small></td>
        <td><small style="color: var(--text-secondary);">{{ sample.engagement_pts }} pts</small></td>
        <td><small style="color: var(--text-secondary);">{{ sample.ratio_pts }} pts</small></td>
        <td><small style="color: var(--text-secondary);">{{ sample.age_pts }} pts</small></td>
        <td>
          <span class="badge" style="background: {% if sample.new_score < 30 %}var(--accent-success){% elseif sample.new_score < 65 %}var(--accent-warning){% else %}var(--accent-danger){% endif %};">
            {{ sample.new_score }}
          </span>
        </td>
      </tr>
      {% endfor %}
    </tbody>
  </table>
</div>
```

---

## Global UI Components

### Toast Notifications (htmx responses)
All server actions return an htmx out-of-band toast partial. Place an empty container in the layout:
```html
<!-- In layout.php, always present -->
<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <!-- htmx injects toasts here via hx-swap-oob -->
</div>
```

Toast partial (`partials/toast.php`):
```html
<div id="toast-container" hx-swap-oob="beforeend">
  <div class="toast align-items-center border-0 show"
       style="background: var(--surface-elevated); color: var(--text-primary);"
       role="alert" x-data x-init="setTimeout(() => $el.remove(), 4000)">
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill" style="color: var(--accent-success);"></i>
        {{ message }}
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>
```

### Loading Spinner (htmx indicator)
```html
<!-- Global indicator: shows during any htmx request -->
<div id="global-spinner" class="htmx-indicator position-fixed top-0 start-0 w-100" style="z-index:9998;">
  <div style="height:3px; background: linear-gradient(90deg, var(--ig-gradient-start), var(--ig-gradient-end));"
       class="progress-bar-animated"></div>
</div>
```

CSS:
```css
.htmx-indicator { opacity: 0; transition: opacity 200ms; }
.htmx-request .htmx-indicator,
.htmx-request.htmx-indicator { opacity: 1; }
```

### Confirmation Modal (reusable)
Avoid using `hx-confirm` browser alerts for destructive actions — use a Bootstrap Modal instead:

```html
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="background: var(--surface-elevated); border-color: var(--surface-border);">
      <div class="modal-body text-center p-4">
        <i class="bi bi-exclamation-triangle display-6 mb-3" style="color: var(--accent-warning);"></i>
        <p class="mb-0" x-text="confirmMessage"></p>
      </div>
      <div class="modal-footer border-0 justify-content-center gap-2">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-danger" id="confirmAction">Confirm</button>
      </div>
    </div>
  </div>
</div>
```

### Badges for Account Status
```html
<!-- Not following back -->
<span class="badge rounded-pill" style="background:rgba(252,92,101,.15); color:var(--accent-danger);">Ghost</span>

<!-- Whitelisted -->
<span class="badge rounded-pill" style="background:rgba(38,222,129,.15); color:var(--accent-success);">Protected</span>

<!-- Inactive account -->
<span class="badge rounded-pill" style="background:rgba(160,160,160,.15); color:var(--text-secondary);">Inactive</span>
```

---

## Login / OAuth Page

A centered, minimal card — intentionally simple compared to the dashboard's density.

```html
<div class="min-vh-100 d-flex align-items-center justify-content-center"
     style="background: var(--surface-bg);">
  <div class="text-center" style="max-width: 380px; width: 100%;">

    <!-- Instagram gradient wordmark -->
    <h1 class="mb-2" style="font-family:'Syne'; font-weight:800; font-size:2rem;
        background: linear-gradient(135deg, var(--ig-gradient-start), var(--ig-gradient-mid), var(--ig-gradient-end));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
      UnfollowIQ
    </h1>
    <p class="text-secondary mb-4">See who isn't following you back.</p>

    <div class="card p-4" style="background: var(--surface-card); border-color: var(--surface-border);">
      <a href="/auth/instagram" class="btn btn-lg w-100 fw-medium"
         style="background: linear-gradient(135deg, var(--ig-gradient-start), var(--ig-gradient-mid), var(--ig-gradient-end)); border: none; color: #fff;">
        <i class="bi bi-instagram me-2"></i> Connect with Instagram
      </a>
      <p class="text-secondary mt-3 mb-0" style="font-size:.75rem;">
        We only request read + unfollow permissions. We never post on your behalf.
      </p>
    </div>

  </div>
</div>
```

---

## Responsive Breakpoints

| Breakpoint | Behaviour |
|-----------|-----------|
| `< 768px` (mobile) | Sidebar hidden → offcanvas drawer; stat cards stack to 1 col; table horizontal scroll; kanban columns scroll horizontally; calendar shows compact dots |
| `768px–992px` (tablet) | Sidebar collapses to icon-only (64px); stat cards 2 col; kanban visible |
| `> 992px` (desktop) | Full sidebar (260px); stat cards 4 col; full kanban & calendar |

### Mobile Sidebar Toggle
```html
<!-- In mobile topbar -->
<button class="btn btn-outline-secondary d-lg-none" type="button"
        data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
  <i class="bi bi-list"></i>
</button>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar"
     style="background: var(--surface-card); border-color: var(--surface-border);">
  <!-- Same sidebar nav content -->
</div>
```

---

## Micro-interactions & Animation

```css
/* Smooth card hover lift */
.card { transition: transform 150ms ease, box-shadow 150ms ease; }
.card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.4); }

/* Row removal animation (after unfollow) */
tr.htmx-swapping { opacity: 0; transform: translateX(20px); transition: all 300ms ease; }

/* Kanban card drag-ready cursor */
[data-kanban-item]:active { cursor: grabbing; opacity: .85; }

/* Nav link active indicator */
.nav-pills .nav-link.active {
  background: linear-gradient(135deg, rgba(131,58,180,.3), rgba(252,176,69,.15));
  border-left: 3px solid var(--accent-primary);
}
```

---

## Bootstrap Components Reference Map

| UI Element | Bootstrap Component | Docs Path |
|-----------|-------------------|-----------|
| Stat cards | `card`, `card-body` | `/docs/5.3/components/card/` |
| Data table | `table table-hover` | `/docs/5.3/content/tables/` |
| Sidebar nav | `nav nav-pills flex-column` | `/docs/5.3/components/navs-tabs/` |
| Kanban columns | `d-flex`, `flex-shrink-0` | `/docs/5.3/utilities/flex/` |
| Mobile nav | `offcanvas` | `/docs/5.3/components/offcanvas/` |
| Notifications | `toast` | `/docs/5.3/components/toasts/` |
| Confirm dialogs | `modal` | `/docs/5.3/components/modal/` |
| Status labels | `badge rounded-pill` | `/docs/5.3/components/badge/` |
| Sync progress | `progress-bar-striped progress-bar-animated` | `/docs/5.3/components/progress/` |
| Filters | `dropdown`, `dropdown-menu-dark` | `/docs/5.3/components/dropdowns/` |
| Tooltips | `data-bs-toggle="tooltip"` | `/docs/5.3/components/tooltips/` |
| Pagination | `pagination pagination-sm` | `/docs/5.3/components/pagination/` |
| Loading spinner | `spinner-border` | `/docs/5.3/components/spinners/` |
| Search input | `input-group` | `/docs/5.3/forms/input-group/` |
| Theme toggle | `data-bs-theme` attribute | `/docs/5.3/customize/color-modes/` |
