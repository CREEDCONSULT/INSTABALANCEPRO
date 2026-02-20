<?php
/**
 * Unfollow Statistics - Show history and analytics
 * @var array $stats
 * @var array $history
 */
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">
        <i class="fas fa-chart-bar"></i> Unfollow Statistics
    </h1>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-primary"><?php echo $stats['total_unfollowed'] ?? 0; ?></h2>
                    <p class="card-text mb-0">Total Unfollowed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-success"><?php echo $stats['this_week'] ?? 0; ?></h2>
                    <p class="card-text mb-0">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-info"><?php echo $stats['this_month'] ?? 0; ?></h2>
                    <p class="card-text mb-0">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-warning"><?php echo (int)(($stats['average_per_day'] ?? 0) * 100) / 100; ?></h2>
                    <p class="card-text mb-0">Per Day Avg</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown by Category -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Unfollows by Category</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['by_category'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_category'] as $category => $count): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $catColor = match($category) {
                                            'Safe' => 'success',
                                            'Caution' => 'warning',
                                            'High Priority' => 'info',
                                            'Verified' => 'purple',
                                            'Inactive 90d+' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $catColor; ?>">
                                            <?php echo htmlspecialchars($category); ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><strong><?php echo $count; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">No unfollows yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Breakdown by Account Type -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Unfollows by Account Type</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['by_account_type'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th class="text-end">Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['by_account_type'] as $type => $count): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($type); ?></td>
                                    <td class="text-end"><strong><?php echo $count; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">No data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Unfollows History -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Unfollows</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Category</th>
                        <th>Score</th>
                        <th>Followers</th>
                        <th>Unfollowed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox"></i> No unfollow history yet
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($history as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars($item['profile_picture_url'] ?? 'https://via.placeholder.com/32'); ?>" 
                                         alt="<?php echo htmlspecialchars($item['username']); ?>"
                                         class="rounded-circle" width="32" height="32">
                                    <strong>@<?php echo htmlspecialchars($item['username']); ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php
                                $catColor = match($item['category'] ?? 'Unknown') {
                                    'Safe' => 'success',
                                    'Caution' => 'warning',
                                    'High Priority' => 'info',
                                    'Verified' => 'purple',
                                    'Inactive 90d+' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $catColor; ?>">
                                    <?php echo htmlspecialchars($item['category'] ?? 'Unknown'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill">
                                    <?php echo round($item['engagement_score'] ?? 0); ?>
                                </span>
                            </td>
                            <td><?php echo number_format($item['followers_count'] ?? 0); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('M d, Y H:i', strtotime($item['unfollowed_at'])); ?>
                                </small>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="/accounts/ranked" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ranked List
        </a>
    </div>
</div>
