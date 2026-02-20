<?php

namespace App\Services;

use App\Database;
use App\Models\User;

/**
 * Engagement Service
 * 
 * Calculates engagement metrics for Instagram accounts
 * - Engagement gap (days since last interaction)
 * - Engagement score (0-100 scale)
 * - Account status (active, inactive, dormant)
 * - Follower/following ratio analysis
 */
class EngagementService
{
    private $db;
    
    // Thresholds for engagement levels (in days)
    private const INACTIVE_THRESHOLD = 30;        // Hasn't interacted in 30 days
    private const DORMANT_THRESHOLD = 90;         // No activity in 90 days
    private const VERY_DORMANT_THRESHOLD = 180;   // No activity in 180 days

    // Engagement score thresholds
    private const SCORE_LOW = 25;                 // Low engagement
    private const SCORE_MEDIUM = 50;              // Medium engagement
    private const SCORE_HIGH = 75;                // High engagement

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate engagement metrics for all following accounts
     * 
     * @param int $userId User ID
     * @return array ['processed' => N, 'errors' => []]
     */
    public function calculateForAllFollowing($userId)
    {
        try {
            // Get all following accounts with their current follower counts
            $stmt = $this->db->prepare("
                SELECT f.id, f.instagram_user_id, f.followers_count, f.follows_count, f.verified, f.created_at
                FROM following f
                WHERE f.user_id = ? AND f.unfollowed_at IS NULL
                ORDER BY f.id ASC
            ");
            $stmt->execute([$userId]);
            $accounts = $stmt->fetchAll();

            $processed = 0;
            $errors = [];

            // Process each account
            foreach ($accounts as $account) {
                try {
                    $this->calculateForAccount($userId, $account['id'], $account);
                    $processed++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'account_id' => $account['id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return [
                'processed' => $processed,
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            throw new \Exception('Failed to calculate engagement metrics: ' . $e->getMessage());
        }
    }

    /**
     * Calculate engagement metrics for a single account
     * 
     * @param int $userId User ID
     * @param int $followingId Record ID in following table
     * @param array $accountData Account data with followers_count, follows_count, etc.
     */
    public function calculateForAccount($userId, $followingId, $accountData = null)
    {
        // Get account data if not provided
        if (!$accountData) {
            $stmt = $this->db->prepare("
                SELECT f.id, f.instagram_user_id, f.followers_count, f.follows_count, f.verified, f.created_at
                FROM following f
                WHERE f.user_id = ? AND f.id = ?
            ");
            $stmt->execute([$userId, $followingId]);
            $accountData = $stmt->fetch();

            if (!$accountData) {
                throw new \Exception('Account not found');
            }
        }

        // Calculate engagement metrics
        $metrics = $this->calculateMetrics($accountData);

        // Store in account_insights
        $stmt = $this->db->prepare("
            INSERT INTO account_insights (
                user_id, following_id, 
                last_interaction_at, engagement_gap_days, engagement_score,
                follower_count, following_count, follower_ratio,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                last_interaction_at = VALUES(last_interaction_at),
                engagement_gap_days = VALUES(engagement_gap_days),
                engagement_score = VALUES(engagement_score),
                follower_count = VALUES(follower_count),
                following_count = VALUES(following_count),
                follower_ratio = VALUES(follower_ratio),
                updated_at = NOW()
        ");

        $stmt->execute([
            $userId,
            $followingId,
            $metrics['last_interaction_at'],
            $metrics['engagement_gap_days'],
            $metrics['engagement_score'],
            $metrics['follower_count'],
            $metrics['following_count'],
            $metrics['follower_ratio'],
        ]);
    }

    /**
     * Calculate all engagement metrics for an account
     * 
     * @param array $accountData Account data from following table
     * @return array Calculated metrics
     */
    private function calculateMetrics(array $accountData): array
    {
        $followerCount = (int)$accountData['followers_count'] ?? 0;
        $followingCount = (int)$accountData['follows_count'] ?? 0;
        $verified = (bool)($accountData['verified'] ?? false);
        $createdAt = $accountData['created_at'];

        // 1. Calculate last interaction timestamp
        // For now, estimate based on follower growth rate (real data would come from API)
        $lastInteractionAt = $this->estimateLastInteraction($followerCount, $createdAt);

        // 2. Calculate days since last interaction
        $engagementGapDays = $this->calculateEngagementGap($lastInteractionAt);

        // 3. Calculate engagement score (0-100)
        $engagementScore = $this->calculateEngagementScore($engagementGapDays, $followerCount, $followingCount, $verified);

        // 4. Calculate follower ratio
        $followerRatio = $followingCount > 0 ? $followerCount / $followingCount : 0;

        return [
            'last_interaction_at' => $lastInteractionAt,
            'engagement_gap_days' => $engagementGapDays,
            'engagement_score' => $engagementScore,
            'follower_count' => $followerCount,
            'following_count' => $followingCount,
            'follower_ratio' => round($followerRatio, 2),
        ];
    }

    /**
     * Estimate last interaction time based on follower growth
     * This is a heuristic since Instagram API doesn't provide exact activity timestamps
     * 
     * @param int $followerCount Current follower count
     * @param string $createdAtTimestamp Account creation date
     * @return string ISO 8601 timestamp of estimated last interaction
     */
    private function estimateLastInteraction($followerCount, $createdAtTimestamp): string
    {
        // For actual implementation, you would:
        // 1. Track follower count changes over time
        // 2. Analyze rate of growth
        // 3. Estimate activity based on growth patterns
        // 4. Use Instagram's native insights API if account is verified

        // For now, use a heuristic:
        // - Accounts with recent follower growth: recently active
        // - Accounts with stagnant followers: inactive
        // - Accounts with declining followers: less active

        // This would ideally be calculated by comparing follower counts across sync jobs
        $now = new \DateTime();
        
        // Heuristic: Assume last interaction was 15 days ago (placeholder)
        // Real implementation would compare with previous sync data
        $estimate = new \DateTime();
        $estimate->modify('-15 days');

        return $estimate->format('Y-m-d H:i:s');
    }

    /**
     * Calculate days since last interaction
     * 
     * @param string $lastInteractionAt ISO timestamp
     * @return int Days since last interaction
     */
    private function calculateEngagementGap($lastInteractionAt): int
    {
        $now = new \DateTime();
        $lastInteraction = new \DateTime($lastInteractionAt);
        
        $diff = $now->diff($lastInteraction);
        return (int)$diff->days;
    }

    /**
     * Calculate engagement score (0-100)
     * Higher score = more likely to be an active account we should keep following
     * 
     * Logic:
     * - Base score 100
     * - Subtract points based on inactivity
     * - Subtract points for high follower count (accounts with millions of followers are harder to engage)
     * - Add points for follower ratio (accounts with high engagement relative to followers)
     * 
     * @param int $engagementGapDays Days since last activity
     * @param int $followerCount Number of followers
     * @param int $followingCount Number of following
     * @param bool $verified Is account verified
     * @return int Score 0-100
     */
    private function calculateEngagementScore($engagementGapDays, $followerCount, $followingCount, $verified): int
    {
        $score = 100;

        // 1. Deduct for inactivity (max -70 points)
        if ($engagementGapDays > self::VERY_DORMANT_THRESHOLD) {
            $score -= 70; // Very dormant (6+ months)
        } elseif ($engagementGapDays > self::DORMANT_THRESHOLD) {
            $score -= 50; // Dormant (3-6 months)
        } elseif ($engagementGapDays > self::INACTIVE_THRESHOLD) {
            $score -= 30; // Inactive (1-3 months)
        } elseif ($engagementGapDays > 14) {
            $score -= 10; // Moderate inactivity (2-4 weeks)
        }

        // 2. Deduct for extremely large accounts (hard to convert)
        // Accounts with 1M+ followers are harder to engage with
        if ($followerCount > 1000000) {
            $score -= 25;
        } elseif ($followerCount > 100000) {
            $score -= 15;
        } elseif ($followerCount > 50000) {
            $score -= 10;
        }

        // 3. Add bonus for good follower ratio
        // Accounts with more followers than following (loyal audience)
        if ($followingCount > 0) {
            $ratio = $followerCount / $followingCount;
            if ($ratio > 5) {
                $score += 15; // Excellent ratio
            } elseif ($ratio > 2) {
                $score += 10; // Good ratio
            } elseif ($ratio > 1) {
                $score += 5; // Decent ratio
            }
        }

        // 4. Add bonus for verified accounts (higher value to follow)
        if ($verified) {
            $score += 10;
        }

        // 5. Micro-influencers (1k-50k followers) get slight bonus
        if ($followerCount >= 1000 && $followerCount <= 50000) {
            $score += 5;
        }

        // Clamp score to 0-100 range
        return max(0, min(100, $score));
    }

    /**
     * Get engagement level category
     * 
     * @param int $engagementScore Score 0-100
     * @param int $engagementGapDays Days since interaction
     * @return string Category name
     */
    public function getEngagementLevel($engagementScore, $engagementGapDays): string
    {
        if ($engagementGapDays > self::DORMANT_THRESHOLD) {
            return 'Inactive 90d+';
        }

        if ($engagementScore >= self::SCORE_HIGH) {
            return 'High Engagement';
        } elseif ($engagementScore >= self::SCORE_MEDIUM) {
            return 'Medium Engagement';
        } elseif ($engagementScore >= self::SCORE_LOW) {
            return 'Low Engagement';
        } else {
            return 'Very Low Engagement';
        }
    }

    /**
     * Get color class for engagement score (for UI display)
     * 
     * @param int $engagementScore Score 0-100
     * @return string Bootstrap color class (e.g., 'success', 'warning', 'danger')
     */
    public function getScoreColorClass($engagementScore): string
    {
        if ($engagementScore >= 75) {
            return 'success'; // Green - high engagement
        } elseif ($engagementScore >= 50) {
            return 'info';    // Blue - medium engagement
        } elseif ($engagementScore >= 25) {
            return 'warning'; // Yellow - low engagement
        } else {
            return 'danger';  // Red - very low engagement
        }
    }

    /**
     * Get engagement summary statistics for user
     * 
     * @param int $userId User ID
     * @return array Summary statistics
     */
    public function getSummaryStats($userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN engagement_gap_days > ? THEN 1 ELSE 0 END) as dormant_90d,
                SUM(CASE WHEN engagement_gap_days > ? THEN 1 ELSE 0 END) as dormant_30d,
                AVG(engagement_score) as avg_engagement_score,
                MIN(engagement_score) as min_engagement_score,
                MAX(engagement_score) as max_engagement_score,
                AVG(follower_ratio) as avg_follower_ratio
            FROM account_insights
            WHERE user_id = ?
        ");
        $stmt->execute([
            self::DORMANT_THRESHOLD,
            self::INACTIVE_THRESHOLD,
            $userId,
        ]);
        return $stmt->fetch();
    }

    /**
     * Get top low-engagement accounts that should be considered for unfollowing
     * 
     * @param int $userId User ID
     * @param int $limit Number of accounts to return
     * @return array Array of account insights
     */
    public function getLowestEngagementAccounts($userId, $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ai.*, 
                f.username, f.name, f.profile_picture_url, 
                f.followers_count, f.follows_count, f.verified
            FROM account_insights ai
            JOIN following f ON ai.following_id = f.id
            WHERE ai.user_id = ? AND f.unfollowed_at IS NULL
            ORDER BY ai.engagement_score ASC, ai.engagement_gap_days DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get newly active accounts (good to keep following)
     * 
     * @param int $userId User ID
     * @param int $limit Number of accounts
     * @return array Array of account insights
     */
    public function getMostActiveMostEngaged($userId, $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                ai.*, 
                f.username, f.name, f.profile_picture_url, 
                f.followers_count, f.follows_count
            FROM account_insights ai
            JOIN following f ON ai.following_id = f.id
            WHERE ai.user_id = ? AND f.unfollowed_at IS NULL
            AND ai.engagement_gap_days < 14
            ORDER BY ai.engagement_score DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Batch update engagement status for multiple accounts
     * 
     * @param int $userId User ID
     * @param array $accountIds Array of following table IDs
     * @param array $metricsData Array of metrics to update
     */
    public function batchUpdate($userId, $accountIds, $metricsData): void
    {
        if (empty($accountIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($accountIds), '?'));
        
        // Build query to update multiple records
        // In practice, this would be more nuanced
        $stmt = $this->db->prepare("
            UPDATE account_insights 
            SET updated_at = NOW()
            WHERE user_id = ? AND following_id IN ($placeholders)
        ");

        $params = array_merge([$userId], $accountIds);
        $stmt->execute($params);
    }
}
