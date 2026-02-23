-- ============================================================================
-- UnfollowIQ — Complete Database Schema (MySQL 8.0+)
-- Version: 1.0
-- Date: 2026-02-20
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- SECTION 1: AUTHENTICATION & USER MANAGEMENT
-- ============================================================================

/**
 * users — Core SaaS user accounts
 * Stores application registration, subscription tier, and global settings
 */
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(128),
    profile_picture_url TEXT,
    subscription_tier ENUM('free', 'pro', 'premium') DEFAULT 'free',
    is_active BOOLEAN DEFAULT TRUE,
    is_admin BOOLEAN DEFAULT FALSE,
    email_verified_at DATETIME,
    two_fa_enabled BOOLEAN DEFAULT FALSE,
    two_fa_secret VARCHAR(32),
    recovery_codes TEXT,  -- JSON array of 8 single-use codes
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at DATETIME,
    
    INDEX idx_email (email),
    INDEX idx_subscription_tier (subscription_tier),
    INDEX idx_is_active (is_active),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * email_verifications — Temporary email verification tokens
 * Tokens are single-use and expire after 24 hours
 */
CREATE TABLE email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * password_resets — Temporary password reset tokens
 * Tokens are single-use and expire after 1 hour
 */
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    reset_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 2: INSTAGRAM OAUTH & CONNECTIONS
-- ============================================================================

/**
 * instagram_connections — OAuth token storage and Instagram profile data
 * Stores encrypted access tokens and basic Instagram profile information
 * One connection per user (v1.0)
 */
CREATE TABLE instagram_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    instagram_user_id VARCHAR(64) UNIQUE NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    profile_picture_url TEXT,
    biography TEXT,
    website TEXT,
    is_verified BOOLEAN DEFAULT FALSE,  -- Instagram verified badge (blue checkmark)
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    media_count INT DEFAULT 0,
    access_token TEXT NOT NULL,  -- AES-256 encrypted at rest
    token_expires_at DATETIME,
    refresh_token TEXT,  -- AES-256 encrypted (if available)
    scopes TEXT,  -- Comma-separated list of granted OAuth scopes
    last_synced_at DATETIME,
    sync_status ENUM('idle', 'in_progress', 'failed') DEFAULT 'idle',
    sync_error_message TEXT,
    connected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    disconnected_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_instagram_user_id (instagram_user_id),
    INDEX idx_instagram_username (instagram_username),
    INDEX idx_last_synced_at (last_synced_at),
    INDEX idx_sync_status (sync_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 3: FOLLOWER/FOLLOWING DATA & ENGAGEMENT
-- ============================================================================

/**
 * following — Cached list of accounts the user follows
 * Synced from Instagram API; used to compute unfollower calculations and engagement scores
 */
CREATE TABLE following (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    display_name VARCHAR(128),
    profile_picture_url TEXT,
    biography TEXT,
    website TEXT,
    is_verified BOOLEAN DEFAULT FALSE,  -- Instagram verified badge
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    media_count INT DEFAULT 0,
    is_private BOOLEAN DEFAULT FALSE,
    followed_at DATETIME,  -- Date user started following this account
    unfollowed_at DATETIME,  -- Date user unfollowed this account
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    kanban_status ENUM('to_review', 'ready_to_unfollow', 'unfollowed', 'not_now') DEFAULT 'to_review',
    kanban_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account (user_id, instagram_account_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_instagram_username (instagram_username),
    INDEX idx_followed_at (followed_at),
    INDEX idx_unfollowed_at (unfollowed_at),
    INDEX idx_synced_at (synced_at),
    INDEX idx_is_verified (is_verified),
    INDEX idx_kanban_status (kanban_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * followers — Cached list of accounts that follow the user
 * Synced from Instagram API; used to determine who does/doesn't follow back
 */
CREATE TABLE followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    display_name VARCHAR(128),
    profile_picture_url TEXT,
    biography TEXT,
    website TEXT,
    is_verified BOOLEAN DEFAULT FALSE,  -- Instagram verified badge
    followers_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    media_count INT DEFAULT 0,
    is_private BOOLEAN DEFAULT FALSE,
    synced_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account (user_id, instagram_account_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_instagram_username (instagram_username),
    INDEX idx_synced_at (synced_at),
    INDEX idx_is_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * account_insights (NEW) — Engagement metrics and interaction history
 * Stores historical engagement data used for scoring unfollower priority
 * Updated during each sync; tracks engagement gap, last post, follower trends
 */
CREATE TABLE account_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    following_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    
    -- Engagement metrics (last interaction with this account)
    engagement_gap_days INT,  -- Days since last like/comment; NULL if never engaged
    last_interaction_at DATETIME,  -- Timestamp of last like/comment
    last_post_date DATETIME,  -- Date of account's most recent post (NULL if no posts)
    days_without_post INT,  -- Days since last post
    
    -- Follower trend history (for trend detection)
    followers_count_30d_ago INT,
    followers_count_current INT,
    followers_change_30d INT,
    followers_change_percent DECIMAL(5,2),
    
    -- Account status flags
    is_deleted BOOLEAN DEFAULT FALSE,
    is_suspended BOOLEAN DEFAULT FALSE,
    is_restricted BOOLEAN DEFAULT FALSE,
    
    insights_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account_insight (user_id, following_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES following(id) ON DELETE CASCADE,
    INDEX idx_engagement_gap_days (engagement_gap_days),
    INDEX idx_last_interaction_at (last_interaction_at),
    INDEX idx_is_deleted (is_deleted),
    INDEX idx_insights_updated_at (insights_updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * creator_flags (NEW) — Verified badge and creator account status
 * Denormalizes Instagram creator/business account flags for quick filtering
 * Used to protect verified/creator accounts from being unfollowed
 */
CREATE TABLE creator_flags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    following_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    
    -- Instagram creator flags
    is_verified BOOLEAN DEFAULT FALSE,  -- Blue checkmark
    is_creator BOOLEAN DEFAULT FALSE,   -- "Creator Account" label
    is_business BOOLEAN DEFAULT FALSE,  -- Business account
    is_influencer BOOLEAN DEFAULT FALSE, -- High follower count indicator
    
    -- Custom user flags
    is_whitelisted BOOLEAN DEFAULT FALSE,       -- User marked as "keep forever"
    is_unfollowed BOOLEAN DEFAULT FALSE,        -- User unfollowed (soft delete)
    unfollowed_at DATETIME,
    
    flagged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account_flag (user_id, following_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES following(id) ON DELETE CASCADE,
    INDEX idx_is_verified (is_verified),
    INDEX idx_is_whitelisted (is_whitelisted),
    INDEX idx_is_unfollowed (is_unfollowed),
    INDEX idx_is_creator (is_creator),
    INDEX idx_is_business (is_business)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * user_scoring_preferences (NEW) — Customizable unfollow scoring weights
 * Stores per-user algorithm configuration for score calculation
 * Allows users to adjust which factors matter most for their unfollow strategy
 */
CREATE TABLE user_scoring_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    
    -- Scoring algorithm weights (as percentages; should sum to 100)
    inactivity_weight INT DEFAULT 40,    -- % weight for engagement gap (days without interaction)
    engagement_weight INT DEFAULT 35,    -- % weight for days without post
    ratio_weight INT DEFAULT 15,         -- % weight for follower/following ratio
    age_weight INT DEFAULT 10,           -- % weight for account age (days followed)
    
    -- Scoring protection thresholds
    verified_badge_penalty INT DEFAULT 50,  -- Points to subtract if verified (can set to 0 to allow unfollowing verified)
    creator_account_penalty INT DEFAULT 40, -- Points to subtract for creator/business accounts
    
    -- Category thresholds (score values mapping)
    safe_threshold INT DEFAULT 30,          -- Score 0-30: "Safe" (low priority)
    caution_threshold INT DEFAULT 65,       -- Score 31-65: "Caution" (medium priority)
    -- Score 66-100: "High Priority" (unfollowed soon)
    
    -- Minimum engagement gap before flagging (days)
    min_engagement_gap_days INT DEFAULT 90,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 4: UNFOLLOW QUEUE & WHITELIST
-- ============================================================================

/**
 * unfollow_queue — Queue of pending unfollow operations
 * Tracks bulk unfollow requests awaiting processing via background cron job
 * Respects Instagram API rate limits (100 unfollows per session, etc.)
 */
CREATE TABLE unfollow_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    following_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    
    -- Queue management
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    priority INT DEFAULT 0,  -- Higher priority int unfollow first
    attempt_count INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    error_message TEXT,
    
    -- Rate limiting
    queued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME,
    completed_at DATETIME,
    expires_at DATETIME,  -- Queue item auto-expires after 30 days
    
    created_by ENUM('user', 'bulk_action') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES following(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_status (status),
    INDEX idx_queued_at (queued_at),
    INDEX idx_expires_at (expires_at),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * whitelist — Accounts protected from unfollowing
 * Users can permanently whitelist important accounts to prevent accidental unfollows
 * Capacity: 100 accounts per user on Free tier, 500 on Pro/Premium
 */
CREATE TABLE whitelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    following_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    display_name VARCHAR(128),
    reason TEXT,  -- User's note for why this account is whitelisted
    
    whitelisted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account_whitelist (user_id, following_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES following(id) ON DELETE CASCADE,
    INDEX idx_user_whitelisted (user_id, whitelisted_at),
    INDEX idx_instagram_username (instagram_username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 5: KANBAN BOARD
-- ============================================================================

/**
 * kanban_cards — Kanban board state for visual workflow management
 * Allows users to drag/drop accounts between Review, Queued, Unfollowed, Whitelisted columns
 */
CREATE TABLE kanban_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    following_id INT NOT NULL,
    instagram_account_id VARCHAR(64) NOT NULL,
    instagram_username VARCHAR(128) NOT NULL,
    
    -- Kanban column/status
    `column` ENUM('review', 'queued', 'unfollowed', 'whitelisted') DEFAULT 'review',
    order_index INT DEFAULT 0,  -- Position within column for sorting
    
    -- Metadata snapshot (denormalized from account_insights for display)
    unfollow_priority_score INT,  -- Cache of score
    category VARCHAR(32),  -- Cached category label (e.g., "Inactive 90d+", "Low Engagement")
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_account_kanban (user_id, following_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES following(id) ON DELETE CASCADE,
    INDEX idx_user_column (user_id, `column`),
    INDEX idx_column_order (`column`, order_index)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 6: ACTIVITY & AUDIT LOGS
-- ============================================================================

/**
 * activity_log — User action audit trail
 * Tracks login, unfollow, sync, whitelist, and other user actions
 * Used for activity feed display and compliance auditing
 */
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(64) NOT NULL,  -- 'login', 'logout', 'sync_start', 'sync_complete', 'unfollow', 'bulk_unfollow', 'whitelist_add', 'whitelist_remove', etc.
    action_description TEXT,
    related_instagram_account_id VARCHAR(64),  -- If action targets a specific account
    related_instagram_username VARCHAR(128),
    ip_address VARCHAR(45),  -- IPv4 or IPv6
    user_agent TEXT,
    http_method VARCHAR(10),
    request_path VARCHAR(255),
    http_status_code INT,
    response_time_ms INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_action (user_id, action_type),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    INDEX idx_related_username (related_instagram_username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * admin_log — Administrative action audit trail
 * Tracks admin panel actions: user suspension, tier changes, account deletions, etc.
 * Critical for compliance and fraud detection
 */
CREATE TABLE admin_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT,
    target_user_id INT,
    action_type VARCHAR(64) NOT NULL,  -- 'user_suspend', 'user_activate', 'tier_change', 'delete_account', 'reset_quotas', etc.
    action_description TEXT,
    old_value TEXT,  -- Previous state (JSON)
    new_value TEXT,  -- New state (JSON)
    ip_address VARCHAR(45),
    reason TEXT,  -- Why the action was taken
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_target_user (target_user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * sync_jobs — Background sync history and monitoring
 * Tracks all follower/following sync operations for debugging and rate limiting
 */
CREATE TABLE sync_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Sync operation metadata
    status ENUM('pending', 'in_progress', 'paused', 'completed', 'failed') DEFAULT 'pending',
    sync_type ENUM('full', 'incremental') DEFAULT 'incremental',
    
    -- Results
    followers_fetched INT DEFAULT 0,
    following_fetched INT DEFAULT 0,
    new_followers INT DEFAULT 0,
    lost_followers INT DEFAULT 0,
    not_following_back INT DEFAULT 0,
    unfollowed_me_30d INT DEFAULT 0,
    mutual_followers INT DEFAULT 0,
    
    -- API metrics
    api_calls_made INT DEFAULT 0,
    api_calls_limit INT,
    api_calls_remaining INT,
    api_reset_timestamp INT,
    rate_limit_hit BOOLEAN DEFAULT FALSE,
    error_message TEXT,
    
    -- Timestamps
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paused_at DATETIME,
    resumed_at DATETIME,
    completed_at DATETIME,
    next_sync_at DATETIME,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_started_at (started_at),
    INDEX idx_completed_at (completed_at),
    INDEX idx_next_sync_at (next_sync_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * monthly_usage — Usage quota tracking per billing period
 * Resets on billing cycle; tracks unfollow count and API quota consumption
 */
CREATE TABLE monthly_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Billing period
    billing_month DATE NOT NULL,  -- First day of month (e.g., 2026-02-01)
    
    -- Usage counters
    unfollows_count INT DEFAULT 0,
    api_calls_count INT DEFAULT 0,
    storage_bytes INT DEFAULT 0,
    
    -- Quotas (based on subscription tier)
    unfollows_quota INT,  -- 50 for Free, 500 for Pro, Unlimited for Premium
    api_calls_quota INT,  -- 1000 for Free, 10000 for Pro, Unlimited for Premium
    
    -- Overage flags
    exceeded_quota BOOLEAN DEFAULT FALSE,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_month (user_id, billing_month),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_billing_month (billing_month),
    INDEX idx_exceeded_quota (exceeded_quota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 7: BILLING & SUBSCRIPTIONS
-- ============================================================================

/**
 * subscriptions — Active subscription records
 * Tracks user's current plan tier, Stripe customer ID, and billing dates
 */
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    
    -- Stripe integration
    stripe_customer_id VARCHAR(255) UNIQUE NOT NULL,
    stripe_subscription_id VARCHAR(255) UNIQUE,
    
    -- Subscription details
    tier ENUM('free', 'pro', 'premium') DEFAULT 'free',
    status ENUM('active', 'past_due', 'canceled', 'unpaid') DEFAULT 'active',
    
    -- Billing cycle
    current_period_start DATE,
    current_period_end DATE,
    renewal_date DATE,  -- Next automatic renewal
    
    -- Pricing
    price_per_month DECIMAL(8,2),  -- NULL for free tier
    billing_interval ENUM('monthly', 'yearly'),
    
    -- Trial period (if applicable)
    trial_ends_at DATETIME,
    trial_cancelled_at DATETIME,
    
    -- Cancellation
    cancel_reason TEXT,
    cancellation_scheduled_at DATETIME,
    canceled_at DATETIME,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_stripe_customer_id (stripe_customer_id),
    INDEX idx_tier (tier),
    INDEX idx_status (status),
    INDEX idx_renewal_date (renewal_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/**
 * invoices — Payment receipts and billing records
 * Issued for all paid subscriptions; integrated with Stripe webhooks
 */
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    
    -- Stripe integration
    stripe_invoice_id VARCHAR(255) UNIQUE NOT NULL,
    stripe_payment_intent_id VARCHAR(255),
    
    -- Invoice details
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('draft', 'open', 'paid', 'void', 'uncollectible') DEFAULT 'open',
    
    -- Period
    subscription_period_start DATE,
    subscription_period_end DATE,
    
    -- Issue & due dates
    issued_at DATETIME NOT NULL,
    due_at DATETIME,
    paid_at DATETIME,
    
    -- Metadata
    description TEXT,
    receipt_url TEXT,
    invoice_url TEXT,
    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_stripe_invoice_id (stripe_invoice_id),
    INDEX idx_user_status (user_id, status),
    INDEX idx_issued_at (issued_at),
    INDEX idx_paid_at (paid_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 8: INDEXES FOR COMMON QUERIES
-- ============================================================================

-- Composite indexes for common report queries
ALTER TABLE following ADD INDEX idx_user_not_in_followers (user_id, instagram_account_id);
ALTER TABLE followers ADD INDEX idx_user_account_quick_check (user_id, instagram_account_id);
ALTER TABLE activity_log ADD INDEX idx_user_created_action (user_id, created_at, action_type);
ALTER TABLE unfollow_queue ADD INDEX idx_user_status_priority (user_id, status, priority);
ALTER TABLE kanban_cards ADD INDEX idx_user_column_score (user_id, `column`, unfollow_priority_score);

-- ============================================================================
-- DATA INITIALIZATION
-- ============================================================================

-- Create default user_scoring_preferences rows for each subscription tier
-- (These serve as defaults; users can customize their own)

-- ============================================================================
-- FOREIGN KEY CONSTRAINTS
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- Ensure all foreign keys are properly enforced
ALTER TABLE following ADD CONSTRAINT fk_following_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE followers ADD CONSTRAINT fk_followers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE whitelist ADD CONSTRAINT fk_whitelist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE kanban_cards ADD CONSTRAINT fk_kanban_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
ALTER TABLE unfollow_queue ADD CONSTRAINT fk_queue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================

/**
 * Migration Run Instructions:
 *
 * 1. Create database:
 *    mysql> CREATE DATABASE instabalancepro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 *
 * 2. Import this schema:
 *    $ mysql -u root -p instabalancepro < database/schema.sql
 *
 * 3. Verify tables created:
 *    $ mysql -u root -p instabalancepro -e "SHOW TABLES;"
 *
 * 4. Test connectivity from app:
 *    $ php -r "require 'config/app.php'; $pdo = new PDO('mysql:host=...', ...); echo 'Connected!';"
 */
