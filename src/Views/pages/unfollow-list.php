<?php
/**
 * Ranked List - Show all following accounts sorted by engagement score
 * @var array $accounts
 * @var array $categories
 * @var array $summary
 * @var array $queueStats
 * @var array $rateLimit
 * @var array $filters
 * @var int $totalResults
 * @var int $totalPages
 * @var int $currentPage
 */
?>

<div class="container-fluid mt-4">
    <!-- Header with Queue Status -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="mb-0">
                <i class="fas fa-list-check"></i> Ranked List
                <small class="text-muted"><?php echo number_format($totalResults); ?> accounts</small>
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <?php if ($rateLimit['allowed']): ?>
                <div class="alert alert-info mb-0 py-2 px-3" role="alert">
                    <strong><?php echo $rateLimit['remaining']; ?></strong> unfollows available (24h)
                </div>
            <?php else: ?>
                <div class="alert alert-warning mb-0 py-2 px-3" role="alert">
                    <strong>Rate limit reached</strong> — Try again tomorrow
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Queue Status Cards -->
    <?php if ($queueStats['total'] > 0): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Unfollow Queue Status</h5>
                    <div class="row text-center">
                        <div class="col">
                            <div class="h4 text-primary"><?php echo $queueStats['pending']; ?></div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col">
                            <div class="h4 text-warning"><?php echo $queueStats['processing']; ?></div>
                            <small class="text-muted">Processing</small>
                        </div>
                        <div class="col">
                            <div class="h4 text-success"><?php echo $queueStats['completed']; ?></div>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col">
                            <div class="h4 text-danger"><?php echo $queueStats['failed']; ?></div>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($queueStats['pending'] > 0): ?>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Queue Actions</h5>
                    <button class="btn btn-success btn-sm me-2" id="btnExecuteQueue" 
                            onclick="executeQueue()" <?php echo !$rateLimit['allowed'] ? 'disabled' : ''; ?>>
                        <i class="fas fa-play"></i> Execute Queue
                    </button>
                    <button class="btn btn-danger btn-sm" id="btnClearQueue" onclick="clearQueue()">
                        <i class="fas fa-trash"></i> Clear Queue
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Filter and Sort Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" id="filterForm" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Username, name..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                </div>

                <!-- Sort -->
                <div class="col-md-4">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="score_desc" <?php echo $filters['sort'] === 'score_desc' ? 'selected' : ''; ?>>Score (Highest)</option>
                        <option value="score_asc" <?php echo $filters['sort'] === 'score_asc' ? 'selected' : ''; ?>>Score (Lowest)</option>
                        <option value="followers_desc" <?php echo $filters['sort'] === 'followers_desc' ? 'selected' : ''; ?>>Followers (High)</option>
                        <option value="followers_asc" <?php echo $filters['sort'] === 'followers_asc' ? 'selected' : ''; ?>>Followers (Low)</option>
                        <option value="username_asc" <?php echo $filters['sort'] === 'username_asc' ? 'selected' : ''; ?>>Username (A-Z)</option>
                        <option value="inactive_asc" <?php echo $filters['sort'] === 'inactive_asc' ? 'selected' : ''; ?>>Most Inactive</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-4">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                    <?php echo $filters['category'] === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Score Range -->
                <div class="col-md-6">
                    <label for="scoreRange" class="form-label">Score Range</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number" class="form-control form-control-sm" name="score_min" 
                               value="<?php echo $filters['score_min']; ?>" min="0" max="100" placeholder="Min">
                        <span>to</span>
                        <input type="number" class="form-control form-control-sm" name="score_max" 
                               value="<?php echo $filters['score_max']; ?>" min="0" max="100" placeholder="Max">
                    </div>
                </div>

                <!-- Follower Range -->
                <div class="col-md-6">
                    <label for="followerRange" class="form-label">Followers</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="number" class="form-control form-control-sm" name="follower_min" 
                               value="<?php echo $filters['follower_min']; ?>" min="0" placeholder="Min">
                        <span>to</span>
                        <input type="number" class="form-control form-control-sm" name="follower_max" 
                               value="<?php echo $filters['follower_max']; ?>" min="0" placeholder="Max">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <a href="/accounts/ranked" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Accounts Table with Bulk Selection -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input" onclick="toggleSelectAll(this)">
                        </th>
                        <th>Username</th>
                        <th class="text-center">Followers</th>
                        <th class="text-center">Score</th>
                        <th>Category</th>
                        <th class="text-center">Inactive</th>
                        <th>Explanation</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox"></i> No accounts found
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $account): ?>
                        <tr class="account-row">
                            <td>
                                <input type="checkbox" class="form-check-input account-checkbox" 
                                       data-account-id="<?php echo $account['id']; ?>"
                                       onchange="updateSelectAll()">
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo htmlspecialchars($account['profile_picture']); ?>" 
                                         alt="<?php echo htmlspecialchars($account['username']); ?>"
                                         class="rounded-circle" width="32" height="32">
                                    <div>
                                        <strong>@<?php echo htmlspecialchars($account['username']); ?></strong>
                                        <?php if ($account['name']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($account['name']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php echo number_format($account['followers']); ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $account['score_color']; ?> rounded-pill">
                                    <?php echo round($account['score']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php 
                                    $catColor = match($account['category']) {
                                        'Safe' => 'success',
                                        'Caution' => 'warning',
                                        'High Priority' => 'info',
                                        'Verified' => 'purple',
                                        'Inactive 90d+' => 'danger',
                                        default => 'secondary'
                                    };
                                    echo $catColor;
                                ?>">
                                    <?php echo htmlspecialchars($account['category']); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <small class="text-muted">
                                    <?php echo $account['gap_days'] ?? 'N/A'; ?> days
                                </small>
                            </td>
                            <td>
                                <small class="text-muted d-inline-block" style="max-width: 200px;">
                                    <?php echo htmlspecialchars(substr($account['explanation'], 0, 50)); ?>...
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="queueSingle(<?php echo $account['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=1&<?php echo http_build_query($filters); ?>">First</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&<?php echo http_build_query($filters); ?>">Previous</a>
            </li>
            <?php endif; ?>

            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&<?php echo http_build_query($filters); ?>">Next</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>&<?php echo http_build_query($filters); ?>">Last</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Queue for Unfollow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to queue <strong id="selectedCount">0</strong> accounts for unfollowing.</p>
                
                <div class="alert alert-info" role="alert">
                    <strong>Category Breakdown:</strong>
                    <div id="categoryBreakdown" class="mt-2"></div>
                </div>

                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle"></i>
                    <strong>Important:</strong> Unfollows will be processed gradually over 24 hours to avoid Instagram rate limits.
                    You can cancel queued unfollows at any time.
                </div>

                <form id="queueForm">
                    <div class="mb-3">
                        <label for="unfollowReason" class="form-label">Reason (optional)</label>
                        <select class="form-select" id="unfollowReason">
                            <option value="low_engagement">Low engagement</option>
                            <option value="spam">Spam/Bot account</option>
                            <option value="inactive">Inactive 90+ days</option>
                            <option value="manual">Manual selection</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmUnfollow" required>
                        <label class="form-check-label" for="confirmUnfollow">
                            I confirm I want to unfollow these accounts
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmQueue()">Queue Unfollows</button>
            </div>
        </div>
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
let selectedAccountIds = [];

// Toggle select all
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.account-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectAll();
}

// Update select all checkbox state
function updateSelectAll() {
    const allCheckboxes = document.querySelectorAll('.account-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.account-checkbox:checked');
    document.getElementById('selectAll').checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
}

// Queue single account
function queueSingle(accountId) {
    const checkboxes = document.querySelectorAll('.account-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    
    document.querySelector(`[data-account-id="${accountId}"]`).checked = true;
    updateSelectAll();
    
    showApprovalModal();
}

// Show approval modal
function showApprovalModal() {
    selectedAccountIds = Array.from(document.querySelectorAll('.account-checkbox:checked'))
        .map(cb => parseInt(cb.dataset.accountId));
    
    if (selectedAccountIds.length === 0) {
        alert('Please select at least one account');
        return;
    }

    document.getElementById('selectedCount').textContent = selectedAccountIds.length;
    
    // Build category breakdown (mock for now)
    const breakdown = '<div class="small">Breakdown will be shown here</div>';
    document.getElementById('categoryBreakdown').innerHTML = breakdown;
    
    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    modal.show();
}

// Confirm and submit queue
async function confirmQueue() {
    if (!document.getElementById('confirmUnfollow').checked) {
        alert('Please confirm before continuing');
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('approvalModal'));
    modal.hide();

    try {
        const response = await fetch('/unfollows/queue', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                account_ids: selectedAccountIds,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to queue accounts'));
            return;
        }

        // Success - redirect to queue page
        setTimeout(() => {
            window.location.href = data.data.redirect;
        }, 1500);

    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Execute pending unfollows
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

        // Update progress
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

// Clear queue
async function clearQueue() {
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

// Bulk queue selected accounts
function queueSelected() {
    showApprovalModal();
}

document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to filter form to reset pagination
    document.getElementById('filterForm').addEventListener('submit', function() {
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/accounts/ranked?' + params.toString();
    });
});
</script>
