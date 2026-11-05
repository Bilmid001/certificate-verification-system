<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'bilmid');
define('DB_PASS', 'bilmid12345');
define('DB_NAME', 'certificate_verification');

// Site configuration
define('SITE_URL', 'http://localhost/certificate-verification-system');
define('SITE_NAME', 'Automated Certificate Verification System');

// Security configuration
define('SECRET_KEY', 'your-secret-key-here');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
?>