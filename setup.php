<?php
session_start();
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>CampusWear Setup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        body { padding: 40px; background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%); min-height: 100vh; }
        .card { max-width: 700px; margin: 0 auto; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .status-item { padding: 15px; margin: 10px 0; border-radius: 10px; }
    </style>
</head>
<body>
<div class='card p-5 shadow-lg bg-white'>
    <h2 class='mb-4' style='color: var(--primary-color);'><i class='fas fa-cog me-2'></i>CampusWear Setup & Configuration</h2>";

$result = $conn->query("SHOW DATABASES LIKE 'campuswear_db'");

if ($result->num_rows == 0) {
    echo "<div class='alert alert-warning'>Database 'campuswear_db' does not exist. Please run database_schema.sql first.</div>";
    echo "</div></body></html>";
    exit;
}

$tables = ['organizations', 'merchandise', 'users', 'purchases', 'favorites'];
$missing = [];

foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows == 0) {
        $missing[] = $table;
    }
}

if (!empty($missing)) {
    echo "<div class='alert alert-warning'>Missing tables: " . implode(', ', $missing) . "</div>";
    echo "<p>Please run database_schema.sql to create the tables.</p>";
    echo "</div></body></html>";
    exit;
}

$orgCount = $conn->query("SELECT COUNT(*) as count FROM organizations")->fetch_assoc()['count'];

if ($orgCount == 0) {
    echo "<div class='status-item alert alert-info'><i class='fas fa-info-circle me-2'></i>Organizations table is empty. Please run database_merchandise_data.sql to populate data.</div>";
} else {
    echo "<div class='status-item alert alert-success'><i class='fas fa-check-circle me-2'></i>Found $orgCount organizations</div>";
}

$merchCount = $conn->query("SELECT COUNT(*) as count FROM merchandise")->fetch_assoc()['count'];

if ($merchCount == 0) {
    echo "<div class='status-item alert alert-info'><i class='fas fa-info-circle me-2'></i>Merchandise table is empty. Please run database_merchandise_data.sql to populate data.</div>";
} else {
    echo "<div class='status-item alert alert-success'><i class='fas fa-check-circle me-2'></i>Found $merchCount merchandise items</div>";
}

$adminCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
if ($adminCount > 0) {
    echo "<div class='status-item alert alert-warning'><i class='fas fa-user-shield me-2'></i>Found $adminCount admin user(s) in the system.</div>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        echo "<div class='alert alert-warning'>User with this email already exists.</div>";
    } else {
        $sql = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', 'admin')";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-success'>✓ Admin user created successfully!</div>";
            echo "<p><a href='auth.php' class='btn btn-primary'>Go to Login</a></p>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    }
} else {
    echo "<hr class='my-4'>";
    echo "<h5 class='mt-4 mb-3'><i class='fas fa-user-shield me-2' style='color: var(--primary-color);'></i>Create Admin Account</h5>";
    echo "<p class='text-muted mb-4'>Create the first admin account to access the admin dashboard and manage the system.</p>";
    echo "<form method='POST'>
        <div class='mb-3'>
            <label class='form-label fw-bold'><i class='fas fa-user me-2'></i>Username</label>
            <input type='text' name='username' class='form-control' placeholder='Enter admin username' required>
        </div>
        <div class='mb-3'>
            <label class='form-label fw-bold'><i class='fas fa-envelope me-2'></i>Email Address</label>
            <input type='email' name='email' class='form-control' placeholder='admin@campuswear.com' required>
        </div>
        <div class='mb-3'>
            <label class='form-label fw-bold'><i class='fas fa-lock me-2'></i>Password</label>
            <input type='password' name='password' class='form-control' placeholder='Enter secure password' required minlength='6'>
            <small class='text-muted'>Minimum 6 characters</small>
        </div>
        <div class='d-flex gap-2'>
            <button type='submit' name='create_admin' class='btn btn-primary rounded-pill px-4'>
                <i class='fas fa-user-plus me-2'></i>Create Admin Account
            </button>
            <a href='auth.php' class='btn btn-outline-secondary rounded-pill px-4'>
                <i class='fas fa-arrow-left me-2'></i>Back to Login
            </a>
        </div>
    </form>";
}

echo "</div></body></html>";
?>
