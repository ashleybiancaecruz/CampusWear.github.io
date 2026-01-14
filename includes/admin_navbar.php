<?php
$userRole = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : 'user';

if (!isset($pendingFeedback)) {
    require_once __DIR__ . '/../config/config.php';
    $pendingFeedback = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
}
?>
<header class="admin-header bg-white shadow-sm sticky-top border-bottom border-2" style="border-color: var(--primary-color);">
    <div class="container-fluid px-4 py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="admin_dashboard.php" class="d-flex align-items-center text-decoration-none">
                    <img src="assets/images/logo.png" alt="CampusWear Logo" style="height: 60px; width: auto; margin-right: 10px; object-fit: contain;" onerror="this.style.display='none';">
                    <span class="h4 mb-0 fw-bold" style="color: var(--primary-color);">
                        <?php echo defined('SITE_NAME') ? SITE_NAME : 'CampusWear'; ?> Admin
                    </span>
                </a>
            </div>
            
            <nav class="d-flex align-items-center gap-3">
                <a href="admin_dashboard.php" class="admin-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line me-1"></i>Dashboard
                </a>
                <a href="admin.php" class="admin-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">
                    <i class="fas fa-box me-1"></i>Merchandise
                </a>
                <a href="admin_feedback.php" class="admin-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_feedback.php') ? 'active' : ''; ?>">
                    <i class="fas fa-comments me-1"></i>Feedback
                    <?php if (isset($pendingFeedback) && $pendingFeedback > 0): ?>
                        <span class="badge bg-danger ms-1"><?php echo $pendingFeedback; ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin_isc_sync.php" class="admin-nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_isc_sync.php') ? 'active' : ''; ?>">
                    <i class="fas fa-sync-alt me-1"></i>ISC Sync
                </a>
                
                <div class="border-start ps-3 ms-2 d-flex align-items-center gap-3">
                    <div class="text-end d-none d-md-block">
                        <div class="small text-muted">Logged in as</div>
                        <div class="fw-bold" style="color: var(--primary-color);"><?php echo htmlspecialchars($_SESSION['user']); ?></div>
                    </div>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </nav>
        </div>
    </div>
</header>

<style>
.admin-header {
    background: linear-gradient(to bottom, #ffffff 0%, #f8f9fa 100%);
    border-bottom: 2px solid var(--primary-color) !important;
}

.admin-nav-link {
    text-decoration: none;
    color: #495057;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 8px 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    position: relative;
}

.admin-nav-link:hover {
    background: rgba(139, 0, 0, 0.1);
    color: var(--primary-color);
    transform: translateY(-1px);
}

.admin-nav-link.active {
    background: var(--primary-color);
    color: white;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.admin-nav-link.active:hover {
    background: var(--primary-dark);
    color: white;
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .admin-header .container-fluid {
        flex-direction: column;
        gap: 15px;
    }
    
    .admin-header nav {
        flex-wrap: wrap;
        justify-content: center;
    }
}
</style>
