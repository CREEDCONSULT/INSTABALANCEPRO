<?php
/**
 * Unfollow Queue - Show pending unfollows and execution controls
 * @var array $queue
 * @var string $status
 * @var array $stats
 * @var array $rateLimit
 */
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">
                <i class="fas fa-clock"></i> Unfollow Queue
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <div class="alert mb-0 py-2 px-3" role="alert" class="alert-<?php echo $rateLimit['allowed'] ? 'info' : 'warning'; ?>">
                <strong><?php echo $rateLimit['remaining']; ?></strong> unfollows available (24h)
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-primary"><?php echo $stats['pending']; ?></h3>
                    <p class="card-text mb-0">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-warning"><?php echo $stats['processing']; ?></h3>
                    <p class="card-text mb-0">Processing</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-success"><?php echo $stats['completed']; ?></h3>
                    <p class="card-text mb-0">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h3 class="text-danger"><?php echo $stats['failed']; ?></h3>
                    <p class="card-text mb-0">Failed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card mb-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" 
                       href="?status=pending" role="tab">
                        <i class="fas fa-hourglass-start"></i> Pending (<?php echo $stats['pending']; ?>)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $status === 'processing' ? 'active' : ''; ?>" 
                       href="?status=processing" role="tab">
                        <i class="fas fa-spinner"></i> Processing (<?php echo $stats['processing']; ?>)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $status === 'completed' ? 'active' : ''; ?>" 
                       href="?status=completed" role="tab">
                        <i class="fas fa-check-circle"></i> Completed (<?php echo $stats['completed']; ?>)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?php echo $status === 'failed' ? 'active' : ''; ?>" 
                       href="?status=failed" role="tab">
                        <i class="fas fa-exclamation-circle"></i> Failed (<?php echo $stats['failed']; ?>)
                    </a>
                </li>
            </ul>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Score</th>
                        <th>Category</th>
                        <th>Followers</th>
                        <th>Queued</th>
                        <th>Reason</th>
                        <th style="width: 80px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($queue)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox"></i> No unfollows in <?php echo htmlspecialchars($status); ?> status
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($queue as $item): ?>
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
                                $statusColor = match($item['status']) {
                                    'pending' => 'info',
                                    'processing' => 'warning',
                                    'completed' => 'success',
                                    'failed' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusColor; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill">
                                    <?php echo round($item['engagement_score'] ?? 0); ?>
                                </span>
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
                            <td><?php echo number_format($item['followers_count'] ?? 0); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('M d H:i', strtotime($item['queued_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($item['reason'] ?? 'N/A'); ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($status === 'pending'): ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFromQueue(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php elseif ($status === 'failed'): ?>
                                <button class="btn btn-sm btn-outline-primary" onclick="retryUnfollow(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-redo"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <?php if ($status === 'pending' && $stats['pending'] > 0): ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Pending Actions</h5>
            <button class="btn btn-success btn-lg me-2" onclick="executeQueue()" <?php echo !$rateLimit['allowed'] ? 'disabled' : ''; ?>>
                <i class="fas fa-play"></i> Execute Queue (<?php echo $stats['pending']; ?> unfollows)
            </button>
            <button class="btn btn-danger btn-lg" onclick="clearPending()">
                <i class="fas fa-trash"></i> Clear All
            </button>
            <p class="text-muted mt-3 mb-0">
                <small><i class="fas fa-info-circle"></i> Unfollows are processed gradually over 24 hours to comply with Instagram's rate limits.</small>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="/accounts/ranked" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ranked List
        </a>
        <a href="/unfollows/statistics" class="btn btn-info">
            <i class="fas fa-chart-bar"></i> View Statistics
        </a>
    </div>
</div>

<!-- Execution Progress Modal -->
<div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="progressModalLabel">Processing Unfollows...</h5>
            </div>
            <div class="modal-body">
                <div class="progress mb-3">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                <p><span id="progressText">Starting...</span></p>
                <small id="progressDetail" class="text-muted"></small>
            </div>
        </div>
    </div>
</div>

<script>
async function executeQueue() {
    if (!confirm('Execute pending unfollows? This will start processing your queue.')) {
        return;
    }

    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    progressModal.show();

    try {
        const response = await fetch('/unfollows/execute', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to execute queue'));
            progressModal.hide();
            return;
        }

        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressText').textContent = 'Completed: ' + data.data.executed + ' unfollows';
        document.getElementById('progressDetail').textContent = data.message;

        setTimeout(() => {
            progressModal.hide();
            location.reload();
        }, 2000);

    } catch (error) {
        alert('Error: ' + error.message);
        progressModal.hide();
    }
}

async function removeFromQueue(queueId) {
    if (!confirm('Remove this unfollow from queue?')) {
        return;
    }

    try {
        const response = await fetch('/unfollows/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                queue_ids: [queueId],
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to remove item'));
            return;
        }

        location.reload();

    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function clearPending() {
    if (!confirm('Clear all pending unfollows? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('/unfollows/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to clear queue'));
            return;
        }

        alert('Queue cleared: ' + data.data.cleared + ' items removed');
        location.reload();

    } catch (error) {
        alert('Error: ' + error.message);
    }
}

async function retryUnfollow(queueId) {
    if (!confirm('Retry this unfollow?')) {
        return;
    }

    try {
        const response = await fetch('/unfollows/retry', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                queue_id: queueId,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to retry'));
            return;
        }

        location.reload();

    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
