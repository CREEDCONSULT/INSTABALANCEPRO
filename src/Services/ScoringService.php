<?php

namespace App\Services;

use App\Database;

/**
 * Scoring Service
 * 
 * Advanced multi-factor scoring algorithm for Instagram accounts
 * - Weighted scoring based on engagement, inactivity, ratio, age
 * - Account type detection (verified, creator, business, regular)
 * - Dynamic category assignment
 * - Score explanation generation for user understanding
 */
class ScoringService
{
    private $db;
    
    // Scoring weights (total = 100%)
    private const WEIGHT_INACTIVITY = 40;         // 40% - Days since last activity
    private const WEIGHT_ENGAGEMENT = 35;         // 35% - Engagement quality
    private const WEIGHT_RATIO = 15;              // 15% - Follower/following ratio
    private const WEIGHT_ACCOUNT_AGE = 10;        // 10% - Account maturity

    // Account type scoring modifiers
    private const MODIFIER_VERIFIED = -50;        // Verified accounts: -50 points (safer)
    private const MODIFIER_CREATOR = -40;         // Creator accounts: -40 (safer, professional)
    private const MODIFIER_BUSINESS = -30;        // Business accounts: -30 (safer, accountable)

    // Score thresholds
    private const THRESHOLD_SAFE = 25;            // 0-25: Safe to unfollow
    private const THRESHOLD_CAUTION = 50;         // 26-50: Use caution
    private const THRESHOLD_PRIORITY = 75;        // 51-75: High priority
    private const THRESHOLD_CRITICAL = 100;       // 76-100: Critical/Very valuable

    // Category names
    public const CATEGORY_SAFE = 'Safe';
    public const CATEGORY_CAUTION = 'Caution';
    public const CATEGORY_HIGH_PRIORITY = 'High Priority';
    public const CATEGORY_VERIFIED = 'Verified Account';
    public const CATEGORY_INACTIVE_90D = 'Inactive 90d+';
    public const CATEGORY_LOW_ENGAGEMENT = 'Low Engagement';

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate comprehensive score for an account
     * 
     * @param int $userId User ID
     * @param int $followingId Following record ID
     * @return array ['score' => 0-100, 'category' => '...', 'factors' => [...], 'explanation' => '...']
     */
    public function scoreAccount($userId, $followingId)
    {
        // Get account data with engagement metrics
        $stmt = $this->db->prepare("
            SELECT 
                f.*,
                ai.engagement_score,
                ai.engagement_gap_days,
                ai.follower_ratio
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.id = ? AND f.unfollowed_at IS NULL
        ");
        $stmt->execute([$userId, $followingId]);
        $account = $stmt->fetch();

        if (!$account) {
            throw new \Exception('Account not found');
        }

        return $this->calculateScore($account);
    }

    /**
     * Calculate scores for all following accounts
     * 
     * @param int $userId User ID
     * @return array ['processed' => N, 'errors' => []]
     */
    public function scoreAllFollowing($userId)
    {
        // Get all accounts with engagement data
        $stmt = $this->db->prepare("
            SELECT 
                f.id, f.user_id, f.instagram_user_id, f.username, f.followers_count, f.follows_count, f.verified,
                ai.engagement_score,
                ai.engagement_gap_days,
                ai.follower_ratio
            FROM following f
            LEFT JOIN account_insights ai ON f.id = ai.following_id
            WHERE f.user_id = ? AND f.unfollowed_at IS NULL
            ORDER BY f.id ASC
        ");
        $stmt->execute([$userId]);
        $accounts = $stmt->fetchAll();

        $processed = 0;
        $errors = [];

        foreach ($accounts as $account) {
            try {
                $scoreData = $this->calculateScore($account);
                $this->storeScore($userId, $account['id'], $scoreData);
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
    }

    /**
     * Calculate score for a single account
     * 
     * @param array $account Account data with engagement metrics
     * @return array Score breakdown
     */
    private function calculateScore(array $account): array
    {
        // Extract values with defaults
        $engagementScore = (int)($account['engagement_score'] ?? 50);
        $engagementGapDays = (int)($account['engagement_gap_days'] ?? 30);
        $followerRatio = (float)($account['follower_ratio'] ?? 1.0);
        $verified = (bool)($account['verified'] ?? false);
        $followerCount = (int)($account['followers_count'] ?? 0);
        $followingCount = (int)($account['follows_count'] ?? 0);

        // 1. Calculate inactivity factor (0-100 scale)
        $inactivityFactor = $this->calculateInactivityFactor($engagementGapDays);

        // 2. Calculate engagement factor (0-100 scale)
        $engagementFactor = $engagementScore; // Use computed engagement_score directly

        // 3. Calculate ratio factor (0-100 scale)
        $ratioFactor = $this->calculateRatioFactor($followerRatio);

        // 4. Calculate account age factor (0-100 scale)
        $ageFactor = $this->calculateAgeFactor($followerCount, $followingCount);

        // 5. Weighted score calculation
        $baseScore = (
            ($inactivityFactor * self::WEIGHT_INACTIVITY / 100) +
            ($engagementFactor * self::WEIGHT_ENGAGEMENT / 100) +
            ($ratioFactor * self::WEIGHT_RATIO / 100) +
            ($ageFactor * self::WEIGHT_ACCOUNT_AGE / 100)
        );

        // 6. Apply modifiers based on account type
        $score = $this->applyModifiers($baseScore, $verified, $followerCount, $followingCount);

        // 7. Determine category
        $category = $this->determineCategory($score, $engagementGapDays, $verified);

        // 8. Generate explanation
        $explanation = $this->generateExplanation($account, $score, $inactivityFactor, $engagementFactor, $ratioFactor, $ageFactor);

        return [
            'score' => max(0, min(100, $score)), // Clamp to 0-100
            'category' => $category,
            'factors' => [
                'inactivity' => round($inactivityFactor, 1),
                'engagement' => round($engagementFactor, 1),
                'ratio' => round($ratioFactor, 1),
                'age' => round($ageFactor, 1),
                'base_weighted' => round($baseScore, 1),
            ],
            'explanation' => $explanation,
            'account_type' => $this->getAccountType($verified, $followerCount, $followingCount),
        ];
    }

    /**
     * Calculate inactivity factor (0-100)
     * Higher value = more active = better score
     * 
     * @param int $engagementGapDays Days since last activity
     * @return float 0-100 scale
     */
    private function calculateInactivityFactor($engagementGapDays): float
    {
        // Linear decay from 100 (brand new) to 0 (1000+ days)
        if ($engagementGapDays <= 7) {
            return 100;           // Very active
        } elseif ($engagementGapDays <= 30) {
            return 100 - (($engagementGapDays - 7) * 1.5);  // 100 to 86
        } elseif ($engagementGapDays <= 90) {
            return 86 - (($engagementGapDays - 30) * 0.875); // 86 to 34
        } elseif ($engagementGapDays <= 180) {
            return 34 - (($engagementGapDays - 90) * 0.5);   // 34 to 9
        } else {
            return max(0, 9 - (($engagementGapDays - 180) * 0.02)); // 9 to 0
        }
    }

    /**
     * Calculate engagement factor (0-100)
     * Uses engagement_score from EngagementService
     * 
     * @param float $engagementScore Pre-calculated engagement score
     * @return float 0-100
     */
    private function calculateEngagementFactor($engagementScore): float
    {
        // Already on 0-100 scale
        return min(100, max(0, $engagementScore));
    }

    /**
     * Calculate ratio factor (0-100)
     * Measures follower/following ratio
     * 
     * @param float $followerRatio followers / following
     * @return float 0-100 scale
     */
    private function calculateRatioFactor($followerRatio): float
    {
        // Higher ratio (followers > following) = better engagement
        // Logarithmic scale to handle wide range
        if ($followerRatio <= 0) {
            return 0;
        } elseif ($followerRatio <= 0.5) {
            return $followerRatio * 20;           // 0-10
        } elseif ($followerRatio <= 1) {
            return 10 + (($followerRatio - 0.5) * 20); // 10-20
        } elseif ($followerRatio <= 2) {
            return 20 + (($followerRatio - 1) * 20);   // 20-40
        } elseif ($followerRatio <= 5) {
            return 40 + (($followerRatio - 2) * 12);   // 40-76
        } elseif ($followerRatio <= 10) {
            return 76 + (($followerRatio - 5) * 4);    // 76-96
        } else {
            return 100;                           // Perfect ratio
        }
    }

    /**
     * Calculate account age factor (0-100)
     * Newer accounts = higher risk, lower score
     * Established accounts = higher score
     * 
     * @param int $followerCount Current followers
     * @param int $followingCount Current following
     * @return float 0-100
     */
    private function calculateAgeFactor($followerCount, $followingCount): float
    {
        // Heuristic: Estimate account age from follower count
        // Real implementation would use created_at timestamp

        // Very new accounts (< 200 followers) are higher risk
        if ($followerCount < 50) {
            return 30; // Brand new, likely spam risk
        } elseif ($followerCount < 200) {
            return 50; // New account
        } elseif ($followerCount < 1000) {
            return 70; // Developing account
        } elseif ($followerCount < 10000) {
            return 85; // Established
        } else {
            return 100; // Well-established account
        }
    }

    /**
     * Apply modifiers based on account type
     * 
     * @param float $baseScore Base weighted score (0-100)
     * @param bool $verified Is account verified
     * @param int $followerCount Follower count
     * @param int $followingCount Following count
     * @return float Modified score
     */
    private function applyModifiers($baseScore, $verified, $followerCount, $followingCount): float
    {
        $score = $baseScore;

        // Verified accounts are safer (subtract points, lower risk)
        if ($verified) {
            $score -= self::MODIFIER_VERIFIED; // -50: Much safer
            return $score;
        }

        // Creator/business account detection (heuristic)
        $isCreatorOrBusiness = $this->isCreatorOrBusiness($followerCount, $followingCount);
        if ($isCreatorOrBusiness) {
            if ($followerCount > 50000) {
                $score -= self::MODIFIER_CREATOR; // -40
            } else {
                $score -= self::MODIFIER_BUSINESS; // -30
            }
        }

        return $score;
    }

    /**
     * Determine category based on score and account characteristics
     * 
     * @param float $score Final score (0-100)
     * @param int $engagementGapDays Days since activity
     * @param bool $verified Is verified
     * @return string Category name
     */
    private function determineCategory($score, $engagementGapDays, $verified): string
    {
        // Override categories based on specific conditions
        if ($verified) {
            return self::CATEGORY_VERIFIED; // Always safe if verified
        }

        if ($engagementGapDays > 90) {
            return self::CATEGORY_INACTIVE_90D; // Dormant
        }

        // Score-based categories
        if ($score <= self::THRESHOLD_SAFE) {
            return self::CATEGORY_SAFE;
        } elseif ($score <= self::THRESHOLD_CAUTION) {
            return self::CATEGORY_CAUTION;
        } elseif ($score <= self::THRESHOLD_PRIORITY) {
            return self::CATEGORY_HIGH_PRIORITY;
        } else {
            return self::CATEGORY_LOW_ENGAGEMENT; // Very risky
        }
    }

    /**
     * Generate human-readable explanation of score
     * 
     * @param array $account Account data
     * @param float $score Calculated score
     * @param float $inactivityFactor Factor 0-100
     * @param float $engagementFactor Factor 0-100
     * @param float $ratioFactor Factor 0-100
     * @param float $ageFactor Factor 0-100
     * @return string Explanation text
     */
    private function generateExplanation($account, $score, $inactivityFactor, $engagementFactor, $ratioFactor, $ageFactor): string
    {
        $parts = [];

        // Inactivity assessment
        $days = $account['engagement_gap_days'] ?? 0;
        if ($days > 90) {
            $parts[] = "Dormant for {$days} days - no recent activity";
        } elseif ($days > 30) {
            $parts[] = "Inactive for {$days} days - minimal engagement";
        } elseif ($days > 7) {
            $parts[] = "Moderately active - last seen {$days} days ago";
        } else {
            $parts[] = "Very active - engaged within 7 days";
        }

        // Engagement assessment
        if ($engagementFactor >= 75) {
            $parts[] = "High engagement quality";
        } elseif ($engagementFactor >= 50) {
            $parts[] = "Moderate engagement";
        } else {
            $parts[] = "Low engagement quality";
        }

        // Ratio assessment
        $ratio = $account['follower_ratio'] ?? 1.0;
        if ($ratio > 5) {
            $parts[] = "Excellent follower/following ratio ({$ratio:.1f}x)";
        } elseif ($ratio > 2) {
            $parts[] = "Good engagement ratio ({$ratio:.1f}x)";
        } elseif ($ratio >= 1) {
            $parts[] = "Balanced ratio ({$ratio:.1f}x)";
        } else {
            $parts[] = "Follows more than followers - may be growth-focused";
        }

        // Account type
        if ($account['verified']) {
            $parts[] = "✓ Verified account - lower risk";
        } else {
            $accountType = $this->getAccountType($account['verified'] ?? false, $account['followers_count'] ?? 0, $account['follows_count'] ?? 0);
            if ($accountType !== 'Regular') {
                $parts[] = "{$accountType} account";
            }
        }

        // Score interpretation
        if ($score <= 25) {
            $parts[] = "Safe candidate for unfollowing";
        } elseif ($score <= 50) {
            $parts[] = "Use caution before unfollowing";
        } elseif ($score <= 75) {
            $parts[] = "High value - recommend keeping";
        } else {
            $parts[] = "Critical value - definitely keep";
        }

        return implode(" • ", $parts);
    }

    /**
     * Detect if account is creator or business type
     * 
     * @param int $followerCount Followers
     * @param int $followingCount Following
     * @return bool True if likely creator/business
     */
    private function isCreatorOrBusiness($followerCount, $followingCount): bool
    {
        // Heuristic: Accounts with many followers and few following
        if ($followerCount > 5000 && $followingCount < 500) {
            return true;
        }

        // Accounts with engagement (followers > following)
        if ($followerCount > 0 && $followingCount > 0 && $followerCount / $followingCount > 10) {
            return true;
        }

        return false;
    }

    /**
     * Get account type description
     * 
     * @param bool $verified Is verified
     * @param int $followerCount Followers
     * @param int $followingCount Following
     * @return string Account type
     */
    private function getAccountType($verified, $followerCount, $followingCount): string
    {
        if ($verified) {
            if ($followerCount > 100000) {
                return 'Verified Celebrity';
            } elseif ($followerCount > 10000) {
                return 'Verified Influencer';
            } else {
                return 'Verified Creator';
            }
        }

        if ($followerCount > 1000000) {
            return 'Celebrity';
        } elseif ($followerCount > 100000) {
            return 'Major Influencer';
        } elseif ($followerCount > 10000) {
            return 'Micro-Influencer';
        } elseif ($followerCount > 1000) {
            return 'Emerging Creator';
        } else {
            return 'Regular';
        }
    }

    /**
     * Store calculated score in database
     * 
     * @param int $userId User ID
     * @param int $followingId Following record ID
     * @param array $scoreData Score calculation result
     */
    private function storeScore($userId, $followingId, $scoreData): void
    {
        $stmt = $this->db->prepare("
            UPDATE account_insights
            SET
                category = ?,
                explanation = ?,
                account_type = ?,
                updated_at = NOW()
            WHERE user_id = ? AND following_id = ?
        ");

        $stmt->execute([
            $scoreData['category'],
            substr($scoreData['explanation'], 0, 500), // Limit explanation length
            $scoreData['account_type'],
            $userId,
            $followingId,
        ]);
    }

    /**
     * Get category color for UI display
     * 
     * @param string $category Category name
     * @return string Bootstrap color class
     */
    public static function getCategoryColor($category): string
    {
        return match ($category) {
            self::CATEGORY_SAFE => 'success',           // Green
            self::CATEGORY_CAUTION => 'warning',        // Yellow
            self::CATEGORY_HIGH_PRIORITY => 'info',     // Blue
            self::CATEGORY_VERIFIED => 'primary',       // Blue (highlight)
            self::CATEGORY_INACTIVE_90D => 'danger',    // Red
            self::CATEGORY_LOW_ENGAGEMENT => 'danger',  // Red
            default => 'secondary',                      // Gray
        };
    }

    /**
     * Get recommendation based on score and category
     * 
     * @param string $category Category name
     * @param int $score Score 0-100
     * @return string Recommendation
     */
    public static function getRecommendation($category, $score): string
    {
        return match ($category) {
            self::CATEGORY_SAFE => 'Low risk - good candidate for unfollowing',
            self::CATEGORY_CAUTION => 'Moderate risk - review engagement before unfollowing',
            self::CATEGORY_HIGH_PRIORITY => 'High value - recommend keeping',
            self::CATEGORY_VERIFIED => 'Verified account - always safe to keep',
            self::CATEGORY_INACTIVE_90D => 'Potentially bot or abandoned account',
            self::CATEGORY_LOW_ENGAGEMENT => 'Very low value - strong candidate for unfollowing',
            default => 'Review account engagement',
        };
    }
}
