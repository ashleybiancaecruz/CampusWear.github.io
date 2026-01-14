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

$pageTitle = 'About Us';
include 'includes/header.php';
include 'includes/admin_navbar.php';
?>

<div class="container py-5">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>

    <div class="text-center mb-5">
        <h2 class="fw-bold">About CampusWear</h2>
        <p class="text-muted">Defining our purpose and direction</p>
        <hr class="mx-auto" style="width: 60px; height: 4px; background-color: #800000; border: none;">
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4" style="color: #800000;">
                        <i class="fas fa-eye fa-4x"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Our Vision</h3>
                    <p class="text-muted fs-5">
                        To be the leading provider of quality university merchandise that fosters campus pride and unity within the student body across all campuses.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4" style="color: #800000;">
                        <i class="fas fa-bullseye fa-4x"></i>
                    </div>
                    <h3 class="fw-bold mb-3">Our Mission</h3>
                    <p class="text-muted fs-5">
                        We are committed to delivering comfortable, stylish, and affordable campus apparel while maintaining high standards of customer service and integrity.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-5">
                    <h4 class="fw-bold text-center mb-4">Our Core Values</h4>
                    <div class="row text-center g-4">
                        <div class="col-md-4">
                            <i class="fas fa-check-circle mb-2" style="color: #800000;"></i>
                            <h5 class="fw-bold">Quality</h5>
                            <p class="small text-muted">Excellence in every stitch and print.</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-heart mb-2" style="color: #800000;"></i>
                            <h5 class="fw-bold">Integrity</h5>
                            <p class="small text-muted">Honesty in our service and transactions.</p>
                        </div>
                        <div class="col-md-4">
                            <i class="fas fa-users mb-2" style="color: #800000;"></i>
                            <h5 class="fw-bold">Community</h5>
                            <p class="small text-muted">Building pride within our university.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>