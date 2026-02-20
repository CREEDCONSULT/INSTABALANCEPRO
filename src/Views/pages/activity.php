<?php
/**
 * Activity Calendar - Heatmap visualization of user activity
 * @var int $year
 * @var int $month
 * @var string $monthName
 * @var array $activityData - Date => activity count
 * @var array $monthActivity - Month statistics
 * @var array $yearActivity - Year heatmap
 */

// Generate calendar
$firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
$daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
$daysInPrevMonth = date('t', mktime(0, 0, 0, $month - 1, 1, $year));
?>

<div class="container-fluid mt-4">
    <h1 class="mb-4">
        <i class="fas fa-calendar-alt"></i> Activity Calendar
    </h1>

    <!-- Month/Year Navigation -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Events</h5>
                    <h2 class="text-primary"><?php echo $monthActivity['total_events'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Unfollows</h5>
                    <h2 class="text-success"><?php echo $monthActivity['unfollows'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Queued</h5>
                    <h2 class="text-warning"><?php echo $monthActivity['queued'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">Active Days</h5>
                    <h2 class="text-info"><?php echo $monthActivity['active_days'] ?? 0; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?php echo $monthName . ' ' . $year; ?></h5>
                <div>
                    <a href="?year=<?php echo $year; ?>&month=<?php echo $month === 1 ? 12 : $month - 1; ?>&year=<?php echo $month === 1 ? $year - 1 : $year; ?>" 
                       class="btn btn-sm btn-outline-secondary me-2">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <a href="?year=<?php echo $year; ?>&month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>" 
                       class="btn btn-sm btn-outline-secondary me-2">
                        Today
                    </a>
                    <a href="?year=<?php echo $year; ?>&month=<?php echo $month === 12 ? 1 : $month + 1; ?>&year=<?php echo $month === 12 ? $year + 1 : $year; ?>" 
                       class="btn btn-sm btn-outline-secondary">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Day headers -->
            <div class="calendar-grid">
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                <!-- Previous month's days (grayed out) -->
                <?php for ($i = $firstDay - 1; $i >= 0; $i--): ?>
                    <div class="calendar-day disabled">
                        <?php echo $daysInPrevMonth - $i; ?>
                    </div>
                <?php endfor; ?>

                <!-- Current month's days -->
                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php 
                        $activity = $activityData[$day] ?? null;
                        $count = $activity['count'] ?? 0;
                        $intensity = min(5, max(1, ceil($count / 3))); // 1-5 intensity
                        $dateStr = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                    ?>
                    <div class="calendar-day ${count > 0 ? 'has-activity' : ''} activity-level-${intensity}" 
                         onclick="showDayEvents('<?php echo $dateStr; ?>')">
                        <div class="day-number"><?php echo $day; ?></div>
                        <?php if ($count > 0): ?>
                        <div class="day-activity">
                            <i class="fas fa-circle"></i>
                            <span class="badge bg-primary"><?php echo $count; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>

                <!-- Next month's days (grayed out) -->
                <?php $totalCells = ceil(($firstDay + $daysInMonth) / 7) * 7; ?>
                <?php for ($i = 1; $i <= ($totalCells - $firstDay - $daysInMonth); $i++): ?>
                    <div class="calendar-day disabled">
                        <?php echo $i; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Year Heatmap -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Year Overview - <?php echo $year; ?></h5>
        </div>
        <div class="card-body">
            <div class="year-heatmap">
                <?php foreach ($yearActivity as $monthName => $count): ?>
                    <?php 
                        $index = array_search($monthName, ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) + 1;
                        $intensity = min(5, max(0, ceil($count / 10))); // 0-5 intensity
                    ?>
                    <div class="heatmap-cell activity-level-<?php echo $intensity; ?>" title="<?php echo $monthName; ?>: <?php echo $count; ?> events">
                        <div class="heatmap-label"><?php echo $monthName; ?></div>
                        <div class="heatmap-value"><?php echo $count; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3">Activity Intensity</h6>
            <div class="d-flex gap-3">
                <span><span class="badge activity-level-0"></span> None</span>
                <span><span class="badge activity-level-1"></span> Low</span>
                <span><span class="badge activity-level-2"></span> Medium</span>
                <span><span class="badge activity-level-3"></span> High</span>
                <span><span class="badge activity-level-4"></span> Very High</span>
                <span><span class="badge activity-level-5"></span> Extreme</span>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="/accounts/ranked" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ranked List
        </a>
    </div>
</div>

<!-- Day Events Modal -->
<div class="modal fade" id="dayEventsModal" tabindex="-1" aria-labelledby="dayEventsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayEventsModalLabel">Activity for <span id="selectedDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="eventsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}

.calendar-day-header {
    text-align: center;
    font-weight: 600;
    padding: 10px;
    color: #666;
    font-size: 14px;
}

.calendar-day {
    aspect-ratio: 1;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.calendar-day:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.calendar-day.disabled {
    background: #f8f9fa;
    color: #ccc;
    cursor: default;
}

.calendar-day.has-activity {
    font-weight: 600;
}

.calendar-day.activity-level-1 { background-color: #c6e48b; }
.calendar-day.activity-level-2 { background-color: #7bc96f; }
.calendar-day.activity-level-3 { background-color: #239a3b; }
.calendar-day.activity-level-4 { background-color: #196127; }
.calendar-day.activity-level-5 { background-color: #0d3922; color: white; }

.day-number {
    font-weight: 600;
}

.day-activity {
    position: absolute;
    bottom: 4px;
    right: 4px;
    font-size: 10px;
}

.year-heatmap {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 8px;
}

.heatmap-cell {
    padding: 12px;
    border-radius: 6px;
    text-align: center;
    border: 1px solid #dee2e6;
    cursor: pointer;
    transition: all 0.2s;
}

.heatmap-cell:hover {
    transform: scale(1.05);
}

.heatmap-cell.activity-level-0 { background: white; }
.heatmap-cell.activity-level-1 { background-color: #c6e48b; }
.heatmap-cell.activity-level-2 { background-color: #7bc96f; }
.heatmap-cell.activity-level-3 { background-color: #239a3b; }
.heatmap-cell.activity-level-4 { background-color: #196127; }
.heatmap-cell.activity-level-5 { background-color: #0d3922; color: white; }

.heatmap-label {
    font-weight: 600;
    font-size: 12px;
}

.heatmap-value {
    font-size: 18px;
    font-weight: 700;
    margin-top: 4px;
}

.activity-level-0 { background-color: white !important; }
.activity-level-1 { background-color: #c6e48b !important; }
.activity-level-2 { background-color: #7bc96f !important; }
.activity-level-3 { background-color: #239a3b !important; }
.activity-level-4 { background-color: #196127 !important; }
.activity-level-5 { background-color: #0d3922 !important; color: white !important; }
</style>

<script>
async function showDayEvents(dateStr) {
    try {
        const response = await fetch('/api/activity/events?date=' + dateStr);
        const data = await response.json();

        if (!response.ok) {
            alert('Error loading events');
            return;
        }

        document.getElementById('selectedDate').textContent = dateStr;
        
        const eventsList = data.data.events.map(event => `
            <div class="card mb-2">
                <div class="card-body p-3">
                    <h6 class="card-title mb-1">${event.description}</h6>
                    <small class="text-muted">${new Date(event.created_at).toLocaleTimeString()}</small>
                </div>
            </div>
        `).join('');

        document.getElementById('eventsList').innerHTML = eventsList || '<p class="text-muted">No events</p>';
        
        new bootstrap.Modal(document.getElementById('dayEventsModal')).show();

    } catch (error) {
        alert('Error: ' + error.message);
    }
}
</script>
