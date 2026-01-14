<?php
require_once 'config/config.php';

if (!isset($_SESSION['user'])) {
    header('Location: auth.php');
    exit;
}

$username = $_SESSION['user'];

if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'save') {
        $merch_id = intval($_POST['merch_id']);
        $quantity = intval($_POST['quantity']);

        $merch = $conn->query("SELECT m.*, o.name as org_name 
                              FROM merchandise m 
                              JOIN organizations o ON m.org_id = o.organizations_id 
                              WHERE m.merchandise_id = $merch_id")->fetch_assoc();

        if (!$merch) {
            echo json_encode(['status' => 'error', 'message' => 'Merchandise not found']);
            exit;
        }

        $price = floatval($merch['price']);
        $total_amount = $price * $quantity;

        $sql = "INSERT INTO purchases (username, merch_id, merch_name, org_name, quantity, price, total_amount, payment_status) 
                VALUES ('$username', $merch_id, '" . $conn->real_escape_string($merch['name']) . "', 
                        '" . $conn->real_escape_string($merch['org_name']) . "', $quantity, $price, $total_amount, 'completed')";

        if ($conn->query($sql)) {
            $conn->query("UPDATE merchandise SET stock = stock - $quantity WHERE merchandise_id = $merch_id");
            echo json_encode(['status' => 'success', 'message' => 'Purchase completed']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit;
    }

    if ($action === 'list') {
        $result = $conn->query("SELECT * FROM purchases WHERE username = '$username' ORDER BY created_at DESC");
        $purchases = [];
        while ($row = $result->fetch_assoc()) {
            $purchases[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $purchases]);
        exit;
    }

    if ($action === 'delete') {
        $id = intval($_POST['id']);
        $sql = "DELETE FROM purchases WHERE purchases_id = $id AND username = '$username'";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Purchase removed']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
        exit;
    }
}

$pageTitle = 'Purchase History';
include 'includes/header.php';
include 'includes/navbar.php';

$historyResult = $conn->query("SELECT * FROM purchases WHERE username = '$username' ORDER BY created_at DESC");
?>

<style>
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    main {
        flex: 1;
    }
    .history-card {
        background: #fff;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        transition: transform 0.2s;
        height: 100%;
    }
    .history-card:hover {
        transform: translateY(-5px);
    }
</style>

<main class="container py-5">
    <div class="row">
        <div class="col-12">
            <button onclick="history.back()" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left me-2"></i>Back
            </button>
            <h2 class="fw-bold mb-4"><i class="fas fa-receipt me-2"></i>Purchase History</h2>
        </div>
    </div>

    <?php if ($historyResult->num_rows === 0): ?>
        <div class="text-start py-5">
            <div class="mb-3">
                <i class="fas fa-box-open fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted">No purchases yet</h4>
            <p class="text-muted mb-4">Start shopping to see your orders here.</p>
            <a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold">Shop Merchandise</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php while ($row = $historyResult->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="history-card" id="card-<?php echo $row['purchases_id']; ?>">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['merch_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($row['org_name'] ?? ''); ?></div>
                            </div>
                            <span class="badge bg-light text-dark border"><?php echo strtoupper($row['payment_status']); ?></span>
                        </div>
                        <hr class="my-2 opacity-25">
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Quantity</span>
                            <span class="fw-bold"><?php echo (int)$row['quantity']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span>Price</span>
                            <span>₱<?php echo number_format($row['price'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between small mb-3">
                            <span>Total</span>
                            <span class="fw-bold text-danger">₱<?php echo number_format($row['total_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <span class="small text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteHistory(<?php echo $row['purchases_id']; ?>)">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<script>
function deleteHistory(id) {
    if(confirm('Are you sure you want to remove this from your history?')) {
        const formData = new FormData();
        formData.append('id', id);
        fetch('purchase.php?action=delete', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                const card = document.getElementById('card-' + id).parentElement;
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    if(document.querySelectorAll('.history-card').length === 0) {
                        location.reload();
                    }
                }, 300);
            }
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>