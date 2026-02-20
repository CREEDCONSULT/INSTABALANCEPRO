<?php
/**
 * Main Layout Template - src/Views/layouts/main.php
 * 
 * Master template for all authenticated pages
 * Includes: Bootstrap 5.3, htmx, Alpine.js
 * Provides: Sidebar, flash messages, user menu
 */
?><!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <!-- Meta Tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>UnfollowIQ</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts: Syne (headings), DM Sans (body), JetBrains Mono (data) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    
    <!-- Alpine.js (defer, must load before htmx) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@latest/dist/cdn.min.js"></script>
    
    <!-- htmx for AJAX requests -->
    <script src="https://unpkg.com/htmx.org@latest"></script>
    
    <style>
        :root {
            --bs-body-font-family: 'DM Sans', sans-serif;
            --bs-heading-font-family: 'Syne', sans-serif;
            --bs-monospace-font-family: 'JetBrains Mono', monospace;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Sidebar Navigation */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 1rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-nav-item {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .sidebar-nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar-nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: 600;
        }
        
        .sidebar-nav-icon {
            width: 24px;
            text-align: center;
        }
        
        /* Main Content Area -->
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }
        
        .topbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        .topbar-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .page-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        /* Responsive: Mobile Navigation -->
        .mobile-nav-toggle {
            display: none;
            background: none;
            border: none;
            color: #667eea;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                height: 100vh;
                transition: left 0.3s;
                z-index: 1040;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .main-content {
                width: 100%;
            }
            
            .mobile-nav-toggle {
                display: block;
            }
            
            .topbar {
                padding: 1rem;
            }
            
            .page-content {
                padding: 1rem;
            }
        }
        
        /* Flash Messages / Toasts -->
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9050;
        }
        
        /* Spinner / Loading -->
        .htmx-request.htmx-indicator {
            display: inline-block;
        }
        
        .htmx-indicator {
            display: none;
        }
        
        /* Forms -->
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6b3d7c 100%);
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-graph-up"></i>
            UnfollowIQ
        </div>
        
        <?php include ROOT_PATH . '/src/Views/partials/navigation.php'; ?>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar">
            <div>
                <button class="mobile-nav-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
            </div>
            <h1 class="topbar-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
            <div class="topbar-actions">
                <?php include ROOT_PATH . '/src/Views/partials/user-menu.php'; ?>
            </div>
        </div>
        
        <!-- Flash Messages / Toasts -->
        <?php include ROOT_PATH . '/src/Views/partials/toast.php'; ?>
        
        <!-- Page Content -->
        <div class="page-content">
            <?php echo $pageContent ?? ''; ?>
        </div>
    </div>
    
    <!-- jQuery (Bootstrap dependency) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle JS (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/assets/js/app.js"></script>
    
    <script>
        // Configure htmx
        htmx.config.defaultIndicatorStyle = "spinner";
        htmx.config.refreshOnHistoryMiss = true;
        
        // Dark mode toggle
        document.addEventListener('DOMContentLoaded', function() {
            const htmlEl = document.documentElement;
            
            // Check for saved preference or default to light
            const theme = localStorage.getItem('theme') || 'light';
            htmlEl.setAttribute('data-bs-theme', theme);
            
            // Listen for theme changes
            window.toggleTheme = function() {
                const current = htmlEl.getAttribute('data-bs-theme');
                const newTheme = current === 'light' ? 'dark' : 'light';
                htmlEl.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
            };
        });
    </script>
</body>
</html>
