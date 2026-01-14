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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_app_status'])) {
    header('Content-Type: application/json');
    $id = intval($_POST['app_id']);
    $status = trim($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE isc_applications SET status = ? WHERE isc_applications_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['status' => 'success']);
    exit;
}

$sql = "SELECT * FROM isc_applications ORDER BY isc_applications_id DESC";
$result = $conn->query($sql);
$applications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'ISC Application Sync';
include 'includes/header.php';
include 'includes/admin_navbar.php';
?>

<div class="container py-5">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>

    <div class="mb-4">
        <h2 class="fw-bold">ISC Applications</h2>
        <p class="text-muted">Manage all synchronized member applications</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Applicant Details</th>
                        <th>Contact</th>
                        <th>Dept/Section</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open d-block mb-2 fa-2x"></i>
                                No applications found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>#<?php echo $app['isc_applications_id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($app['phone']); ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($app['department']); ?></span><br>
                                    <small><?php echo htmlspecialchars($app['section']); ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        $st = strtolower(trim($app['status']));
                                        echo ($st === 'approved') ? 'bg-success' : (($st === 'rejected') ? 'bg-danger' : 'bg-warning'); 
                                    ?>">
                                        <?php echo strtoupper($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-id="<?php echo $app['isc_applications_id']; ?>">
                                        <option value="pending" <?php echo (strtolower(trim($app['status'])) === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo (strtolower(trim($app['status'])) === 'approved') ? 'selected' : ''; ?>>Approve</option>
                                        <option value="rejected" <?php echo (strtolower(trim($app['status'])) === 'rejected') ? 'selected' : ''; ?>>Reject</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const formData = new FormData();
        formData.append('update_app_status', '1');
        formData.append('app_id', this.dataset.id);
        formData.append('status', this.value);
        
        fetch('admin_isc_sync.php', {
            method: 'POST',
            body: formData
        }).then(() => location.reload());
    });
});
</script>

<?php include 'includes/footer.php'; ?>