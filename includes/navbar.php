<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$navCartCount = 0;

if (isset($_SESSION['user_id']) && isset($conn)) {
    $stmt = $conn->prepare("SELECT COUNT(*) as unique_items FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $navCartCount = (int)($result['unique_items'] ?? 0);
    $stmt->close();
}
?>

<head>
    <style>
        .navbar-brand img { height: 60px; width: auto; margin-right: 12px; object-fit: contain; }
        .nav-link.small { font-size: 0.85rem; letter-spacing: 0.5px; }
        #cartCount { transition: transform 0.2s ease-in-out; }
        .badge-update { transform: scale(1.2); }
    </style>
</head>

<header class="bg-white shadow-sm sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container py-2">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="Logo" onerror="this.style.display='none';">
                <span class="h5 mb-0 fw-bold" style="color: var(--primary-color);">
                    <?= defined('SITE_NAME') ? SITE_NAME : 'CampusWear'; ?>
                </span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                    <li class="nav-item"><a href="index.php" class="nav-link fw-bold small">HOME</a></li>
                    <li class="nav-item"><a href="feedback.php" class="nav-link fw-bold small">FEEDBACK</a></li>

                    <?php if (isset($_SESSION['user'])): ?>
                        <li class="nav-item">
                            <a href="cart.php" class="nav-link fw-bold small">
                                CART <span id="cartCount" class="badge bg-primary rounded-pill"><?= $navCartCount ?></span>
                            </a>
                        </li>
                        <li class="nav-item"><a href="purchases.php" class="nav-link fw-bold small">HISTORY</a></li>

                        <?php if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                            <li class="nav-item"><a href="admin_dashboard.php" class="nav-link fw-bold small">DASHBOARD</a></li>
                        <?php endif; ?>

                        <li class="nav-item d-lg-block d-none">
                            <span class="fw-bold border-start ps-3" style="color: var(--primary-color);">
                                Hi, <?= htmlspecialchars($_SESSION['user']); ?>!
                            </span>
                        </li>
                        <li class="nav-item mt-2 mt-lg-0">
                            <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill">Logout</a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item"><a href="#about-section" class="nav-link fw-bold small">ABOUT US</a></li>
                        <li class="nav-item mt-2 mt-lg-0">
                            <a href="auth.php" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">LOGIN</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script>
function refreshCartBadge() {
    const badge = document.getElementById('cartCount');
    if (!badge) return;

    fetch('api/cart.php') 
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const total = res.data.reduce((acc, item) => acc + parseInt(item.quantity), 0);
                badge.innerText = total;
                
                badge.classList.add('badge-update');
                setTimeout(() => badge.classList.remove('badge-update'), 200);
            }
        })
        .catch(err => console.error('Badge update failed:', err));
}
</script>