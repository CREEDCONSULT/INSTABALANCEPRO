<?php
/**
 * Toast Partial - src/Views/partials/toast.php
 * 
 * Display flash messages as Bootstrap toasts
 * Supports: success, error, warning, info
 */
?>

<div class="toast-container" id="toastContainer">
    <?php 
    $messages = [
        'success' => $_SESSION['flash_success'] ?? [],
        'error' => $_SESSION['flash_error'] ?? [],
        'warning' => $_SESSION['flash_warning'] ?? [],
        'info' => $_SESSION['flash_info'] ?? [],
    ];
    
    // Clear flash messages after displaying
    unset($_SESSION['flash_success']);
    unset($_SESSION['flash_error']);
    unset($_SESSION['flash_warning']);
    unset($_SESSION['flash_info']);
    
    // Handle single message or array
    foreach ($messages as $type => $content) {
        if ($content) {
            $messageArray = is_array($content) ? $content : [$content];
            
            foreach ($messageArray as $message) {
                $bgClass = match($type) {
                    'success' => 'bg-success',
                    'error' => 'bg-danger',
                    'warning' => 'bg-warning',
                    'info' => 'bg-info',
                    default => 'bg-secondary'
                };
                
                $icon = match($type) {
                    'success' => '<i class="bi bi-check-circle"></i>',
                    'error' => '<i class="bi bi-exclamation-circle"></i>',
                    'warning' => '<i class="bi bi-exclamation-triangle"></i>',
                    'info' => '<i class="bi bi-info-circle"></i>',
                    default => '<i class="bi bi-bell"></i>'
                };
    ?>
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
        <div class="toast-header <?php echo $bgClass; ?> text-white">
            <span class="me-2"><?php echo $icon; ?></span>
            <strong class="me-auto">
                <?php echo ucfirst($type); ?>
            </strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
    <?php 
            }
        }
    }
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show all toasts
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toastEl => {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
        
        // Remove empty container
        if (toasts.length === 0) {
            const container = document.getElementById('toastContainer');
            if (container) container.style.display = 'none';
        }
    });
</script>
