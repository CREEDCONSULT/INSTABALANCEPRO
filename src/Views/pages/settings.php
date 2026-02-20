<?php
/**
 * Settings Page - User account settings and preferences
 * @var array $user
 * @var array $preferences
 * @var array $sessions
 */
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">
        <i class="fas fa-cog"></i> Account Settings
    </h1>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="profile-tab" href="#profile" data-bs-toggle="tab" role="tab">
                                <i class="fas fa-user"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="security-tab" href="#security" data-bs-toggle="tab" role="tab">
                                <i class="fas fa-lock"></i> Security
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="scoring-tab" href="#scoring" data-bs-toggle="tab" role="tab">
                                <i class="fas fa-sliders-h"></i> Scoring
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="data-tab" href="#data" data-bs-toggle="tab" role="tab">
                                <i class="fas fa-download"></i> Data
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="danger-tab" href="#danger" data-bs-toggle="tab" role="tab">
                                <i class="fas fa-exclamation-triangle"></i> Danger Zone
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Profile Information</h5>
                                    <form id="profileForm">
                                        <div class="mb-3">
                                            <label for="displayName" class="form-label">Display Name</label>
                                            <input type="text" class="form-control" id="displayName" 
                                                   value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <div class="input-group">
                                                <input type="email" class="form-control" id="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                                <button class="btn btn-outline-secondary" type="button" onclick="editEmail()">Change</button>
                                            </div>
                                            <?php if (!$user['email_verified_at']): ?>
                                            <div class="alert alert-warning mt-2" role="alert">
                                                <i class="fas fa-exclamation-triangle"></i> Email not verified
                                                <button class="btn btn-sm btn-warning ms-2" onclick="resendVerification()">Verify Now</button>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <button type="button" class="btn btn-primary" onclick="saveProfile()">
                                            <i class="fas fa-save"></i> Save Changes
                                        </button>
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Account Status</h5>
                                    <div class="list-group">
                                        <div class="list-group-item">
                                            <strong>Subscription Tier</strong>
                                            <div class="badge bg-<?php echo match($user['subscription_tier']) {
                                                'pro' => 'primary',
                                                'premium' => 'danger',
                                                default => 'secondary'
                                            } ?> float-end">
                                                <?php echo ucfirst($user['subscription_tier']); ?>
                                            </div>
                                        </div>
                                        <div class="list-group-item">
                                            <strong>Account Status</strong>
                                            <div class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?> float-end">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </div>
                                        </div>
                                        <div class="list-group-item">
                                            <strong>Member Since</strong>
                                            <div><?php echo date('M d, Y', strtotime($user['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Tab -->
                    <div class="tab-pane fade" id="security" role="tabpanel">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Change Password</h5>
                                    <form id="passwordForm">
                                        <div class="mb-3">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="currentPassword" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" required>
                                            <small class="text-muted">At least 8 characters</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" required>
                                        </div>

                                        <button type="button" class="btn btn-primary" onclick="changePassword()">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </form>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Active Sessions</h5>
                                    <div class="list-group">
                                        <?php foreach ($sessions as $session): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo htmlspecialchars($session['device']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($session['ip']); ?></small>
                                                <br><small class="text-muted"><?php echo $session['last_activity']; ?></small>
                                            </div>
                                            <?php if ($session['is_current']): ?>
                                            <span class="badge bg-success">Current</span>
                                            <?php else: ?>
                                            <button class="btn btn-sm btn-outline-danger">Sign Out</button>
                                            <?php endif; ?>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scoring Tab -->
                    <div class="tab-pane fade" id="scoring" role="tabpanel">
                        <div class="card-body">
                            <h5 class="mb-3">Algorithm Weights</h5>
                            <p class="text-muted">Adjust how much each factor influences your unfollow score:</p>

                            <form id="scoringForm">
                                <div class="mb-4">
                                    <label for="inactivityWeight" class="form-label">
                                        <strong>Inactivity Weight</strong>
                                        <span class="float-end"><span id="inactivityValue"><?php echo ($preferences['inactivity_weight'] ?? 40); ?></span>%</span>
                                    </label>
                                    <input type="range" class="form-range" id="inactivityWeight" min="0" max="100" 
                                           value="<?php echo ($preferences['inactivity_weight'] ?? 40); ?>"
                                           oninput="updateWeights()">
                                    <small class="text-muted">Impact of days since last activity</small>
                                </div>

                                <div class="mb-4">
                                    <label for="engagementWeight" class="form-label">
                                        <strong>Engagement Weight</strong>
                                        <span class="float-end"><span id="engagementValue"><?php echo ($preferences['engagement_weight'] ?? 35); ?></span>%</span>
                                    </label>
                                    <input type="range" class="form-range" id="engagementWeight" min="0" max="100" 
                                           value="<?php echo ($preferences['engagement_weight'] ?? 35); ?>"
                                           oninput="updateWeights()">
                                    <small class="text-muted">Impact of engagement quality</small>
                                </div>

                                <div class="mb-4">
                                    <label for="ratioWeight" class="form-label">
                                        <strong>Ratio Weight</strong>
                                        <span class="float-end"><span id="ratioValue"><?php echo ($preferences['ratio_weight'] ?? 15); ?></span>%</span>
                                    </label>
                                    <input type="range" class="form-range" id="ratioWeight" min="0" max="100" 
                                           value="<?php echo ($preferences['ratio_weight'] ?? 15); ?>"
                                           oninput="updateWeights()">
                                    <small class="text-muted">Impact of follower/following ratio</small>
                                </div>

                                <div class="mb-4">
                                    <label for="ageWeight" class="form-label">
                                        <strong>Account Age Weight</strong>
                                        <span class="float-end"><span id="ageValue"><?php echo ($preferences['age_weight'] ?? 10); ?></span>%</span>
                                    </label>
                                    <input type="range" class="form-range" id="ageWeight" min="0" max="100" 
                                           value="<?php echo ($preferences['age_weight'] ?? 10); ?>"
                                           oninput="updateWeights()">
                                    <small class="text-muted">Impact of account maturity</small>
                                </div>

                                <div class="alert alert-info" role="alert">
                                    <strong>Total Weight:</strong> <span id="totalWeight">100</span>%
                                </div>

                                <button type="button" class="btn btn-primary" onclick="saveScoringPreferences()">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetWeights()">
                                    Reset to Default
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Data Tab -->
                    <div class="tab-pane fade" id="data" role="tabpanel">
                        <div class="card-body">
                            <h5 class="mb-3">Export Your Data</h5>
                            <p class="text-muted">Download a copy of all your data in your preferred format.</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">JSON Format</h6>
                                            <p class="card-text text-muted">Complete structured data export</p>
                                            <button class="btn btn-primary btn-sm" onclick="exportData('json')">
                                                <i class="fas fa-download"></i> Export as JSON
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title">CSV Format</h6>
                                            <p class="card-text text-muted">Spreadsheet-compatible format</p>
                                            <button class="btn btn-info btn-sm" onclick="exportData('csv')">
                                                <i class="fas fa-download"></i> Export as CSV
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone Tab -->
                    <div class="tab-pane fade" id="danger" role="tabpanel">
                        <div class="card-body">
                            <h5 class="mb-3 text-danger">Danger Zone</h5>

                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> These actions are irreversible. Proceed with caution!
                            </div>

                            <div class="card border-danger mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-danger">Delete Account Permanently</h6>
                                    <p class="card-text text-muted">
                                        This will permanently delete your account and all associated data. This action cannot be undone.
                                    </p>
                                    <button class="btn btn-danger btn-sm" onclick="showDeleteModal()">
                                        <i class="fas fa-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="/dashboard" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger" role="alert">
                    <strong>This action is permanent!</strong> All your data will be deleted immediately.
                </div>

                <p>To confirm deletion, please enter your password:</p>
                <input type="password" class="form-control" id="deletePassword" placeholder="Your password">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
function saveProfile() {
    const displayName = document.getElementById('displayName').value;

    fetch('/settings/profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ display_name: displayName }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Profile updated');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function changePassword() {
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;

    if (newPass !== confirm) {
        alert('Passwords do not match');
        return;
    }

    fetch('/settings/password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            current_password: current,
            new_password: newPass,
            confirm_password: confirm,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Password changed');
            document.getElementById('passwordForm').reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function updateWeights() {
    const inactivity = parseInt(document.getElementById('inactivityWeight').value);
    const engagement = parseInt(document.getElementById('engagementWeight').value);
    const ratio = parseInt(document.getElementById('ratioWeight').value);
    const age = parseInt(document.getElementById('ageWeight').value);

    document.getElementById('inactivityValue').textContent = inactivity;
    document.getElementById('engagementValue').textContent = engagement;
    document.getElementById('ratioValue').textContent = ratio;
    document.getElementById('ageValue').textContent = age;

    const total = inactivity + engagement + ratio + age;
    document.getElementById('totalWeight').textContent = total;
}

function saveScoringPreferences() {
    const data = {
        inactivity_weight: parseInt(document.getElementById('inactivityWeight').value),
        engagement_weight: parseInt(document.getElementById('engagementWeight').value),
        ratio_weight: parseInt(document.getElementById('ratioWeight').value),
        age_weight: parseInt(document.getElementById('ageWeight').value),
    };

    fetch('/settings/scoring-preferences', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Preferences saved');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}

function resetWeights() {
    document.getElementById('inactivityWeight').value = 40;
    document.getElementById('engagementWeight').value = 35;
    document.getElementById('ratioWeight').value = 15;
    document.getElementById('ageWeight').value = 10;
    updateWeights();
}

function exportData(format) {
    fetch('/settings/export-data', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ format: format }),
    })
    .then(r => r.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'export.' + format;
        a.click();
    })
    .catch(e => alert('Error: ' + e.message));
}

function showDeleteModal() {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmDeleteAccount() {
    const password = document.getElementById('deletePassword').value;

    if (!password) {
        alert('Please enter your password');
        return;
    }

    fetch('/settings/delete-account', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ password: password }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Account deleted');
            window.location.href = '/';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>
