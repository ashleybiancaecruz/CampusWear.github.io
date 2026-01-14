<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php?redirect=isc_application');
    exit;
}

if (isset($conn) && $conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json; charset=UTF-8");
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid request data"]);
        exit;
    }

    $user_id = $_SESSION['user_id'] ?? null;

    try {
        $sql = "INSERT INTO isc_applications 
                (user_id, first_name, last_name, middle_name, suffix, salutation, pronoun, birth_date, department, section, institution, email, phone, source, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isssssssssssss",
            $user_id,
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'],
            $data['suffix'],
            $data['salutation'],
            $data['pronoun'],
            $data['birth_date'],
            $data['department'],
            $data['section'],
            $data['institution'],
            $data['email'],
            $data['phone'],
            $data['source']
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Application submitted successfully.", "id" => $conn->insert_id]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

$pageTitle = 'ISC Membership Application';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<main class="container py-5">
    <a href="javascript:history.back()" class="btn-back mb-3">
        <i class="fas fa-arrow-left"></i>Back
    </a>
    <div id="application-status"></div>
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-dark text-white p-4">
            <h3 class="mb-0 fw-bold">ISC Registration Form</h3>
        </div>
        <div class="card-body p-4 bg-light">
            <form id="iscApplicationForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Salutation</label>
                    <select name="salutation" class="form-select">
                        <option value="Mr.">Mr.</option>
                        <option value="Ms.">Ms.</option>
                        <option value="Mx.">Mx.</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold">Suffix</label>
                    <input type="text" name="suffix" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Preferred Pronoun</label>
                    <select name="pronoun" class="form-select">
                        <option value="He/Him">He/Him</option>
                        <option value="She/Her">She/Her</option>
                        <option value="They/Them">They/Them</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Phone Number</label>
                    <input type="text" name="phone" class="form-control" placeholder="+63...">
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control"
                        value="<?php echo $_SESSION['email'] ?? ''; ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Institution</label>
                    <input type="text" name="institution" class="form-control" value="Campus Wear" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Department</label>
                    <select name="department" class="form-select">
                        <option value="IT">IT</option>
                        <option value="Engineering">Engineering</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Section</label>
                    <input type="text" name="section" class="form-control">
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill shadow">Submit
                        Application</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/application.js"></script>