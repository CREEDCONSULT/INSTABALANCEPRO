<?php
/**
 * Kanban Board - Workflow for unfollow management
 * @var array $toReview - Accounts pending review
 * @var array $readyToUnfollow - User-approved accounts
 * @var array $unfollowed - Successfully unfollowed
 * @var array $notNow - Review later
 */
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">
        <i class="fas fa-kanban"></i> Unfollow Workflow
    </h1>

    <!-- Kanban Board -->
    <div class="kanban-board mt-4">
        <!-- To Review Column -->
        <div class="kanban-column">
            <div class="kanban-header">
                <h5 class="mb-0">
                    <i class="fas fa-hourglass-start"></i> To Review
                    <span class="badge bg-warning"><?php echo count($toReview); ?></span>
                </h5>
            </div>
            <div class="kanban-body" data-status="to_review" ondrop="drop(event)" ondragover="allowDrop(event)">
                <?php foreach ($toReview as $card): ?>
                    <?php echo $this->renderCard($card); ?>
                <?php endforeach; ?>
                <?php if (empty($toReview)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> No accounts to review
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ready to Unfollow Column -->
        <div class="kanban-column">
            <div class="kanban-header">
                <h5 class="mb-0">
                    <i class="fas fa-check"></i> Ready to Unfollow
                    <span class="badge bg-success"><?php echo count($readyToUnfollow); ?></span>
                </h5>
            </div>
            <div class="kanban-body" data-status="ready_to_unfollow" ondrop="drop(event)" ondragover="allowDrop(event)">
                <?php foreach ($readyToUnfollow as $card): ?>
                    <?php echo $this->renderCard($card); ?>
                <?php endforeach; ?>
                <?php if (empty($readyToUnfollow)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> Drag accounts here
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Unfollowed Column -->
        <div class="kanban-column">
            <div class="kanban-header">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle"></i> Unfollowed
                    <span class="badge bg-info"><?php echo count($unfollowed); ?></span>
                </h5>
            </div>
            <div class="kanban-body" data-status="unfollowed" ondrop="drop(event)" ondragover="allowDrop(event)">
                <?php foreach ($unfollowed as $card): ?>
                    <?php echo $this->renderCard($card, true); ?>
                <?php endforeach; ?>
                <?php if (empty($unfollowed)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> No unfollows yet
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Not Now Column -->
        <div class="kanban-column">
            <div class="kanban-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock"></i> Not Now
                    <span class="badge bg-secondary"><?php echo count($notNow); ?></span>
                </h5>
            </div>
            <div class="kanban-body" data-status="not_now" ondrop="drop(event)" ondragover="allowDrop(event)">
                <?php foreach ($notNow as $card): ?>
                    <?php echo $this->renderCard($card); ?>
                <?php endforeach; ?>
                <?php if (empty($notNow)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i> No cards
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bulk Actions (when cards selected) -->
    <div id="bulkActionsBar" class="bulk-actions-bar mt-4" style="display: none;">
        <div class="d-flex align-items-center gap-3">
            <span id="selectedCount">0 selected</span>
            <button class="btn btn-sm btn-success" onclick="doBulkAction('move_to_ready')">
                <i class="fas fa-arrow-right"></i> Move to Ready
            </button>
            <button class="btn btn-sm btn-warning" onclick="doBulkAction('move_to_not_now')">
                <i class="fas fa-clock"></i> Move to Not Now
            </button>
            <button class="btn btn-sm btn-secondary" onclick="clearSelection()">
                Clear Selection
            </button>
        </div>
    </div>

    <!-- Back Link -->
    <div class="mt-4">
        <a href="/accounts/ranked" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ranked List
        </a>
    </div>
</div>

<!-- Card editing modal -->
<div class="modal fade" id="cardModal" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cardModalLabel">Account Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="cardDetails"></div>
                <div class="mt-3">
                    <label for="cardNotes" class="form-label">Notes</label>
                    <textarea class="form-control" id="cardNotes" rows="3" placeholder="Add notes about this account..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveCardNotes()">Save Notes</button>
            </div>
        </div>
    </div>
</div>

<style>
.kanban-board {
    display: flex;
    gap: 20px;
    overflow-x: auto;
    padding: 20px 0;
}

.kanban-column {
    flex: 0 0 350px;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.kanban-header {
    background: #e9ecef;
    padding: 15px;
    border-bottom: 1px solid #dee2e6;
}

.kanban-body {
    flex: 1;
    overflow-y: auto;
    max-height: 600px;
    padding: 10px;
    min-height: 100px;
}

.kanban-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: move;
    transition: all 0.2s;
}

.kanban-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.kanban-card.dragging {
    opacity: 0.5;
}

.kanban-card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.kanban-card-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.kanban-card-title {
    font-weight: 600;
    font-size: 14px;
    flex: 1;
}

.kanban-card-score {
    font-size: 12px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 12px;
}

.kanban-card-body {
    font-size: 12px;
    color: #666;
}

.kanban-card-meta {
    display: flex;
    gap: 4px;
    margin-top: 8px;
    font-size: 11px;
    color: #999;
}

.bulk-actions-bar {
    position: sticky;
    bottom: 0;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    padding: 15px;
    border-radius: 8px;
}
</style>

<script>
let currentCard = null;

function allowDrop(ev) {
    ev.preventDefault();
    ev.target.closest('.kanban-body')?.classList.add('drag-over');
}

function drop(ev) {
    ev.preventDefault();
    const body = ev.target.closest('.kanban-body');
    body?.classList.remove('drag-over');
    
    const cardId = ev.dataTransfer.getData('cardId');
    const newStatus = body?.dataset.status;
    
    if (cardId && newStatus) {
        moveCard(cardId, newStatus);
    }
}

function dragStart(ev, cardId) {
    ev.dataTransfer.effectAllowed = 'move';
    ev.dataTransfer.setData('cardId', cardId);
    ev.currentTarget.classList.add('dragging');
}

function dragEnd(ev) {
    ev.currentTarget.classList.remove('dragging');
}

async function moveCard(cardId, newStatus) {
    try {
        const response = await fetch('/kanban/card/' + cardId + '/move', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: newStatus,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to move card'));
            return;
        }

        location.reload();

    } catch (error) {
        alert('Error: ' + error.message);
    }
}

function openCardEditor(cardId, username, score, category, explanation, notes) {
    currentCard = cardId;
    
    const details = `
        <div class="d-flex align-items-center gap-2 mb-3">
            <strong>@${username}</strong>
            <span class="badge bg-secondary">${score}</span>
            <span class="badge bg-${category === 'Safe' ? 'success' : 'warning'}">${category}</span>
        </div>
        <p class="text-muted small">${explanation}</p>
    `;
    
    document.getElementById('cardDetails').innerHTML = details;
    document.getElementById('cardNotes').value = notes || '';
    
    new bootstrap.Modal(document.getElementById('cardModal')).show();
}

async function saveCardNotes() {
    const notes = document.getElementById('cardNotes').value;
    
    try {
        const response = await fetch('/kanban/card/' + currentCard + '/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notes: notes,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert('Error: ' + (data.message || 'Failed to save notes'));
            return;
        }

        bootstrap.Modal.getInstance(document.getElementById('cardModal')).hide();
        alert('Notes saved');

    } catch (error) {
        alert('Error: ' + error.message);
    }
}

function doBulkAction(action) {
    const selected = getSelectedCards();
    if (selected.length === 0) {
        alert('No cards selected');
        return;
    }
    
    // Implement bulk action
    console.log('Bulk action:', action, selected);
}

function getSelectedCards() {
    return Array.from(document.querySelectorAll('.kanban-card.selected'))
        .map(el => el.dataset.cardId);
}

function clearSelection() {
    document.querySelectorAll('.kanban-card.selected').forEach(el => {
        el.classList.remove('selected');
    });
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const selected = getSelectedCards().length;
    const bar = document.getElementById('bulkActionsBar');
    bar.style.display = selected > 0 ? 'block' : 'none';
    if (selected > 0) {
        document.getElementById('selectedCount').textContent = selected + ' selected';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Setup card selection
    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.ctrlKey || e.metaKey) {
                this.classList.toggle('selected');
                updateBulkActionsBar();
            }
        });
    });
});
</script>

<?php
/**
 * Render a kanban card
 */
function renderCard($card, $isUnfollowed = false) {
    $scoreColor = match($card['category'] ?? 'Safe') {
        'Safe' => 'success',
        'Caution' => 'warning',
        'High Priority' => 'info',
        'Verified' => 'purple',
        'Inactive 90d+' => 'danger',
        default => 'secondary'
    };
    
    $onclick = "openCardEditor(
        {$card['id']}, 
        '{$card['username']}', 
        " . intval($card['engagement_score'] ?? 0) . ", 
        '{$card['category'] ?? 'Unknown'}', 
        '" . addslashes($card['explanation'] ?? '') . "',
        '" . addslashes($card['kanban_notes'] ?? '') . "'
    )";
    
    return <<<HTML
    <div class="kanban-card" data-card-id="{$card['id']}" draggable="true" 
         ondragstart="dragStart(event, {$card['id']})" ondragend="dragEnd(event)" 
         onclick="$onclick">
        <div class="kanban-card-header">
            <img src="{$card['profile_picture_url']}" alt="{$card['username']}" class="kanban-card-avatar">
            <div class="kanban-card-title">@{$card['username']}</div>
            <span class="kanban-card-score badge bg-{$scoreColor}">{$card['engagement_score']}</span>
        </div>
        <div class="kanban-card-body">
            {$card['followers_count']} followers
        </div>
        <div class="kanban-card-meta">
            <span class="badge bg-sm">{$card['category']}</span>
        </div>
        {$card['kanban_notes'] ? '<p class="text-muted small mt-2">📝 ' . htmlspecialchars($card['kanban_notes']) . '</p>' : ''}
    </div>
    HTML;
}
?>
