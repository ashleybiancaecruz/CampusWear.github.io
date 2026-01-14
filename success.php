<?php
session_start();
$pageTitle = 'Payment Successful';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4 p-5">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success fa-5x"></i>
                </div>
                <h2 class="fw-bold mb-3">Thank You for Your Purchase!</h2>
                <p class="text-muted mb-4">
                    Your payment has been processed successfully. A confirmation message will be sent to your verified phone number shortly.
                </p>
                
                <div class="bg-light p-3 rounded-3 mb-4 text-start">
                    <h6 class="fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Pick-up Instructions:</h6>
                    <p class="small mb-0">Please visit the <strong>COMSOC Office</strong> (Room 304, 3rd Floor) during school hours. Bring your student ID for verification.</p>
                </div>

                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-primary btn-lg rounded-pill">Continue Shopping</a>
                    <a href="profile.php#orders" class="btn btn-outline-secondary rounded-pill">View My Orders</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>