<?php
require_once 'config/config.php';

$pageTitle = 'Feedback & Reports';
include 'includes/header.php';
include 'includes/navbar.php';

$message = "";
$messageType = "";

$message = "";
$messageType = "";
?>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header text-white rounded-top-4 py-4" style="background: var(--primary-color);">
                    <h2 class="mb-0 fw-bold"><i class="fas fa-comments me-2"></i>Feedback & Reports</h2>
                    <p class="mb-0 mt-2 opacity-75">We value your input! Share your thoughts, report issues, or suggest improvements.</p>
                </div>
                <div class="card-body p-5">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="api/shared/feedback.php?action=submit" class="needs-validation" novalidate id="feedbackForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?php echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" name="mobile_number" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Body (Content) <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Website Name <span class="text-danger">*</span></label>
                            <input type="text" name="website_name" class="form-control" value="CampusWear" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <select name="category_name" class="form-select" required>
                                <option value="">Select Category</option>
                                <option value="feedback">Feedback</option>
                                <option value="complaint">Complaint</option>
                                <option value="report">Report</option>
                            </select>
                        </div>

                        <input type="hidden" name="status" value="open">

                        <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold">
                            <i class="fas fa-paper-plane me-2"></i>Submit Report
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-4 shadow border-0 rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2" style="color: var(--primary-color);"></i>What happens next?</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Your feedback will be reviewed by our team</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>We'll respond via email if follow-up is needed</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Reports are prioritized and addressed promptly</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Your suggestions help us improve CampusWear</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.getElementById('feedbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        this.classList.add('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('api/shared/feedback.php?action=submit', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch {
                    throw new Error(text || 'Network error');
                }
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'error') {
            alert('Error: ' + data.message);
        } else {
            alert('Thank you! Your report has been submitted successfully.');
            this.reset();
            this.classList.remove('was-validated');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        console.error('Error:', error);
    });
});
</script>
