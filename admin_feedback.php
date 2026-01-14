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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    $status = trim($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE feedback SET status = ? WHERE feedback_id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['status' => 'success']);
    exit;
}

$sql = "SELECT * FROM feedback ORDER BY created_at DESC";
$result = $conn->query($sql);
$feedback = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$pageTitle = 'Feedback Management';
include 'includes/header.php';
include 'includes/admin_navbar.php';
?>

<div class="container py-5">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>

    <div class="mb-4">
        <h2 class="fw-bold">Feedback Management</h2>
        <p class="text-muted">Viewing all user submissions</p>
    </div>

    <div class="row g-4">
        <?php if (empty($feedback)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">No feedback records found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($feedback as $item): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($item['name'] ?? 'Anonymous'); ?></h5>
                                    <p class="small text-muted mb-2">
                                        <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($item['email']); ?>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($item['category_name'] ?? 'Feedback'); ?>
                                    </p>
                                </div>
                                <span class="badge bg-<?php 
                                    $st = strtolower(trim($item['status']));
                                    echo ($st === 'resolved') ? 'success' : (($st === 'reviewed') ? 'info' : 'warning'); 
                                ?>">
                                    <?php echo strtoupper($item['status']); ?>
                                </span>
                            </div>
                            
                            <div class="p-3 bg-light rounded my-3 border-start border-primary border-4">
                                <?php echo nl2br(htmlspecialchars($item['body'] ?? 'No content provided.')); ?>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Submitted: <?php echo date('M d, Y h:i A', strtotime($item['created_at'])); ?>
                                </small>
                                
                                <select class="form-select form-select-sm status-select" style="width: auto;" data-id="<?php echo $item['feedback_id']; ?>">
                                    <option value="pending" <?php echo (strtolower(trim($item['status'])) === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="reviewed" <?php echo (strtolower(trim($item['status'])) === 'reviewed') ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="resolved" <?php echo (strtolower(trim($item['status'])) === 'resolved') ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const formData = new FormData();
        formData.append('update_status', '1');
        formData.append('id', this.dataset.id);
        formData.append('status', this.value);
        
        fetch('admin_feedback.php', {
            method: 'POST',
            body: formData
        }).then(() => location.reload());
    });
});
<?php include 'includes/footer.php'; ?>