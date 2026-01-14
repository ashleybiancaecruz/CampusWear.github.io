<?php
require_once 'config/config.php';

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$messageType = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT users_id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($pass, $row['password'])) {
            $_SESSION['user'] = $row['username'];
            $_SESSION['user_id'] = $row['users_id'];
            $userRole = strtolower(trim($row['role'] ?? 'user'));
            $_SESSION['role'] = $userRole;
            $_SESSION['email'] = $row['email'] ?? '';
            
            if ($userRole === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                if (isset($_GET['redirect']) && $_GET['redirect'] === 'isc_application') {
                    header("Location: isc_application.php?source=discount_notice&from=login");
                    exit;
                }
                header("Location: index.php");
                exit;
            }
        } else {
            $message = "Wrong password!";
            $messageType = "danger";
        }
    } else {
        $message = "User not found!";
        $messageType = "danger";
    }
    $stmt->close();
}

if (isset($_POST['signup'])) {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $check = $conn->prepare("SELECT users_id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $user, $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "Error: Username or Email is already taken.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $email, $pass);
        
        if ($stmt->execute()) { 
            $message = "Account created successfully! A welcome email has been sent."; 
            $messageType = "success";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ashleybiancacruz@gmail.com'; 
                $mail->Password   = 'qzcjsejcmwqhjezg';           
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->SMTPOptions = array(
                    'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
                );

                $mail->setFrom('ashleybiancacruz@gmail.com', 'CampusWear Team');
                $mail->addAddress($email, $user); 

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to CampusWear!';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; border: 1px solid #8B0000; padding: 20px; border-radius: 10px;'>
                        <h2 style='color: #8B0000;'>Welcome, $user!</h2>
                        <p>Your account has been successfully created at <strong>CampusWear</strong>.</p>
                        <p>We are glad to have you with us! You can now log in and explore our latest campus styles.</p>
                        <br>
                        <p>Best regards,<br>CampusWear Management</p>
                    </div>";

                $mail->send();
            } catch (Exception $e) {
                $message .= " (Email notification failed, but your account is ready.)";
            }

        } else { 
            $message = "Database Error: " . $conn->error; 
            $messageType = "danger";
        }
        $stmt->close();
    }
    $check->close();
}

if (isset($_POST['reset'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");

    if ($check->num_rows > 0) {
        $conn->query("UPDATE users SET password='$new_pass' WHERE email='$email'");
        $message = "Password updated successfully! You can now log in.";
        $messageType = "success";
    } else {
        $message = "Email not found.";
        $messageType = "danger";
    }
}

$showLogin = !isset($_GET['page']) || $_GET['page'] === 'login';
$showSignup = isset($_GET['page']) && $_GET['page'] === 'signup';
$showReset = isset($_GET['page']) && $_GET['page'] === 'reset';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> | <?php echo $showSignup ? 'Sign Up' : 'Login'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .auth-wrapper {
            max-width: 450px;
            width: 100%;
        }

        .auth-card {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .auth-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .auth-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2rem;
        }

        .auth-header p {
            color: #6c757d;
            font-size: 1rem;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            font-size: 1.1rem;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateX(-5px);
        }

        .switch-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e2e8f0;
        }

        .switch-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .switch-link a:hover {
            text-decoration: underline;
            color: #A52A2A;
        }

        .brand-logo {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .input-group-text {
            background: #f8f9fa;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            border: none;
            padding: 12px;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #A52A2A 0%, #8B0000 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        @media (max-width: 768px) {
            .auth-card {
                padding: 40px 30px;
            }

            .auth-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
</head>

<body>
    <a href="index.php" class="back-btn">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>

    <div class="auth-wrapper">
        <?php if ($showSignup): ?>
            <div class="auth-card">
                <div class="auth-header">
                    <div class="brand-logo"><i class="fas fa-tshirt"></i></div>
                    <h1>Create Account</h1>
                    <p>Join CampusWear and start shopping</p>
                </div>

                <?php if ($message && $showSignup): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Create a password" required minlength="6">
                        </div>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    <button type="submit" name="signup" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mb-3">
                        <i class="fas fa-user-plus me-2"></i>Create Account
                    </button>
                </form>

                <div class="switch-link">
                    <p class="text-muted mb-0">Already have an account? <a href="auth.php">Login here</a></p>
                </div>
            </div>
        <?php else: ?>
            <div class="auth-card">
                <div class="auth-header">
                    <div class="brand-logo"><i class="fas fa-tshirt"></i></div>
                    <h1>Welcome Back</h1>
                    <p>Sign in to your CampusWear account</p>
                </div>

                <?php if ($message && $showLogin): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                        </div>
                    </div>
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label small" for="rememberMe">Remember me</label>
                        </div>
                        <a href="auth.php?page=reset" class="text-decoration-none small" style="color: var(--primary-color);">Forgot password?</a>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 rounded-pill fw-bold py-2 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                <div class="switch-link">
                    <p class="text-muted mb-0">Don't have an account? <a href="auth.php?page=signup">Sign up here</a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($showReset): ?>
        <div class="modal fade show" id="resetModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header text-white rounded-top-4" style="background: var(--primary-color);">
                        <h5 class="modal-title fw-bold"><i class="fas fa-key me-2"></i>Reset Password</h5>
                        <a href="auth.php" class="btn-close btn-close-white"></a>
                    </div>
                    <div class="modal-body p-4">
                        <?php if ($message && $showReset): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted small mb-4">Enter your email and a new password below.</p>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="new_password" class="form-control" placeholder="Enter new password" required minlength="6">
                                </div>
                            </div>
                            <button type="submit" name="reset" class="btn btn-warning w-100 rounded-pill fw-bold mb-2">
                                <i class="fas fa-save me-2"></i>Update Password
                            </button>
                            <a href="auth.php" class="btn btn-link w-100 text-decoration-none">
                                <i class="fas fa-arrow-left me-2"></i>Back to Login
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>