<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/database_isc.php';

define('PAYPAL_CLIENT_ID', 'AZNZcSt6oBVtF_YQjayebc9QPKUhpDQU6kMohIuGnMgV1RAnUHoqF7owROrCSKmC8iXUeUuHY0IHcudX');
define('PAYPAL_CURRENCY', 'PHP');
define('SHARED_API_KEY', 'campuswearkey123');
define('SITE_NAME', 'CampusWear');
define('SITE_TAGLINE', 'University Merchandise Store');

$conn->query("CREATE TABLE IF NOT EXISTS otp_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT 0,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(10) NOT NULL,
    status ENUM('pending', 'verified', 'expired') DEFAULT 'pending',
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_code (code),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    otp VARCHAR(10) NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_otp (phone, otp),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

?>