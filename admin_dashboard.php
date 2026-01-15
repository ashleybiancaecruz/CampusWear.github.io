<?php
require_once 'config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}

$userRole = strtolower(trim($_SESSION['role'] ?? 'user'));
if ($userRole !== 'admin') {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Admin Dashboard';
include 'includes/header.php';
include 'includes/admin_navbar.php';

$totalOrders = $conn->query("SELECT COUNT(*) as count FROM purchases")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM purchases WHERE payment_status = 'completed'")->fetch_assoc()['total'] ?? 0;
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM purchases WHERE payment_status = 'pending'")->fetch_assoc()['count'];
$lowStock = $conn->query("SELECT COUNT(*) as count FROM merchandise WHERE stock > 0 AND stock <= 10")->fetch_assoc()['count'];
$outOfStock = $conn->query("SELECT COUNT(*) as count FROM merchandise WHERE stock = 0")->fetch_assoc()['count'];
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalMerchandise = $conn->query("SELECT COUNT(*) as count FROM merchandise")->fetch_assoc()['count'];

$recentOrders = $conn->query("SELECT p.*, m.name as merch_full_name, m.stock as current_stock 
                              FROM purchases p 
                              LEFT JOIN merchandise m ON p.merch_id = m.merchandise_id 
                              ORDER BY p.created_at DESC 
                              LIMIT 10")->fetch_all(MYSQLI_ASSOC);

$topSelling = $conn->query("SELECT merch_name, org_name, SUM(quantity) as total_sold, SUM(total_amount) as revenue 
                           FROM purchases 
                           WHERE payment_status = 'completed' 
                           GROUP BY merch_id, merch_name, org_name 
                           ORDER BY total_sold DESC 
                           LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$pendingFeedback = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'")->fetch_assoc()['count'];

$iscApplications = $conn->query("SELECT COUNT(*) as count FROM isc_applications WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <img src="assets/images/logo.png" alt="CampusWear Logo" style="height: 60px; width: auto;" onerror="this.style.display='none'">
            <h2 class="fw-bold mb-0" style="color: var(--primary-color);"><i class="fas fa-chart-line me-2"></i>Admin Dashboard</h2>
        </div>
    </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="admin-stat-card primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Revenue</div>
                            <h3 class="mb-0 fw-bold" style="font-size: 1.75rem;">₱<?php echo number_format($totalRevenue, 2); ?></h3>
                        </div>
                        <div class="admin-icon" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Orders</div>
                            <h3 class="mb-0 fw-bold" style="font-size: 1.75rem;"><?php echo $totalOrders; ?></h3>
                        </div>
                        <div class="admin-icon" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pending Orders</div>
                            <h3 class="mb-0 fw-bold" style="font-size: 1.75rem;"><?php echo $pendingOrders; ?></h3>
                        </div>
                        <div class="admin-icon" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-2" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Stock Alerts</div>
                            <h3 class="mb-0 fw-bold" style="font-size: 1.75rem;"><?php echo $lowStock + $outOfStock; ?></h3>
                        </div>
                        <div class="admin-icon" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="text-center">
                        <div class="admin-icon mb-3 mx-auto">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="mb-1 fw-bold" style="color: var(--primary-color);"><?php echo $totalUsers; ?></h3>
                        <small class="text-muted" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">Total Users</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="text-center">
                        <div class="admin-icon mb-3 mx-auto">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="mb-1 fw-bold" style="color: var(--primary-color);"><?php echo $totalMerchandise; ?></h3>
                        <small class="text-muted" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">Total Merchandise</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="text-center">
                        <div class="admin-icon mb-3 mx-auto">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="mb-1 fw-bold" style="color: var(--primary-color);"><?php echo $pendingFeedback; ?></h3>
                        <small class="text-muted" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">Pending Feedback</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="admin-stat-card">
                    <div class="text-center">
                        <div class="admin-icon mb-3 mx-auto">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h3 class="mb-1 fw-bold" style="color: var(--primary-color);"><?php echo $iscApplications; ?></h3>
                        <small class="text-muted" style="font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">ISC Applications</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--primary-color);">
                            <i class="fas fa-list me-2"></i>Recent Orders
                        </h5>
                        <span class="admin-badge badge-info">Last 10</span>
                    </div>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                                            <div>No orders yet</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><strong>#<?php echo $order['purchases_id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td>
                                                <div class="admin-badge badge-info mb-1" style="font-size: 0.7rem;"><?php echo htmlspecialchars($order['org_name']); ?></div><br>
                                                <small><?php echo htmlspecialchars($order['merch_name']); ?></small>
                                            </td>
                                            <td><?php echo $order['quantity']; ?></td>
                                            <td class="fw-bold" style="color: var(--primary-color);">₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <?php if ($order['payment_status'] === 'completed'): ?>
                                                    <span class="admin-badge badge-success">Completed</span>
                                                <?php elseif ($order['payment_status'] === 'pending'): ?>
                                                    <span class="admin-badge badge-warning">Pending</span>
                                                <?php else: ?>
                                                    <span class="admin-badge badge-danger">Failed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><small><?php echo date('M d, Y', strtotime($order['created_at'])); ?></small></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

            <div class="col-lg-5">
                <div class="admin-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--primary-color);">
                            <i class="fas fa-trophy me-2"></i>Top Selling Items
                        </h5>
                        <span class="admin-badge badge-success">Top 5</span>
                    </div>
                    <div>
                        <?php if (empty($topSelling)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-chart-bar fa-2x mb-2 d-block opacity-50"></i>
                                <div>No sales data yet</div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($topSelling as $index => $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom" style="border-color: #e9ecef;">
                                    <div>
                                        <div class="fw-bold mb-1" style="color: #374151;"><?php echo htmlspecialchars($item['merch_name']); ?></div>
                                        <div class="admin-badge badge-info mb-1" style="font-size: 0.7rem;"><?php echo htmlspecialchars($item['org_name']); ?></div>
                                        <div class="small text-muted mt-1">
                                            <i class="fas fa-shopping-bag me-1"></i><?php echo $item['total_sold']; ?> sold
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold" style="color: var(--primary-color); font-size: 1.1rem;">₱<?php echo number_format($item['revenue'], 2); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

        <div class="row g-4 mt-2">
            <div class="col-12">
                <div class="admin-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold" style="color: var(--primary-color);">
                            <i class="fas fa-exclamation-triangle me-2" style="color: #f59e0b;"></i>Stock Alerts
                        </h5>
                        <span class="admin-badge badge-warning">Action Required</span>
                    </div>
                    <div>
                    <?php
                    $lowStockItems = $conn->query("SELECT * FROM merchandise WHERE stock > 0 AND stock <= 10 ORDER BY stock ASC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
                    $outStockItems = $conn->query("SELECT * FROM merchandise WHERE stock = 0 ORDER BY name ASC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
                    ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="admin-alert alert-warning mb-3">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <div>
                                        <strong>Low Stock (≤10 items)</strong>
                                        <div class="small">Items that need restocking soon</div>
                                    </div>
                                </div>
                                <?php if (empty($lowStockItems)): ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block opacity-50"></i>
                                        <div>All items have sufficient stock</div>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="admin-table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Stock</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($lowStockItems as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                        <td><span class="admin-badge badge-warning"><?php echo $item['stock']; ?></span></td>
                                                        <td>
                                                            <a href="admin.php" class="admin-btn admin-btn-edit">
                                                                <i class="fas fa-edit"></i> Restock
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <div class="admin-alert alert-error mb-3">
                                    <i class="fas fa-times-circle"></i>
                                    <div>
                                        <strong>Out of Stock</strong>
                                        <div class="small">Items that need immediate attention</div>
                                    </div>
                                </div>
                                <?php if (empty($outStockItems)): ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-check-circle fa-2x mb-2 d-block opacity-50"></i>
                                        <div>No items out of stock</div>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="admin-table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Price</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($outStockItems as $item): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                                        <td class="fw-bold" style="color: var(--primary-color);">₱<?php echo number_format($item['price'], 2); ?></td>
                                                        <td>
                                                            <a href="admin.php" class="admin-btn admin-btn-edit">
                                                                <i class="fas fa-edit"></i> Restock
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
