<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

global $db;

try {
    $db->begin_transaction();
    
    // Reset to default values
    $db->query("UPDATE system_settings SET setting_value = CASE 
        WHEN setting_key = 'site_name' THEN 'CertVerify'
        WHEN setting_key = 'site_email' THEN 'admin@certverify.com'
        WHEN setting_key = 'timezone' THEN 'UTC'
        WHEN setting_key = 'maintenance_mode' THEN '0'
        WHEN setting_key = 'verification_expiry' THEN '365'
        WHEN setting_key = 'allow_public_verification' THEN '1'
        WHEN setting_key = 'smtp_host' THEN ''
        WHEN setting_key = 'smtp_port' THEN '587'
        WHEN setting_key = 'smtp_username' THEN ''
        WHEN setting_key = 'smtp_password' THEN ''
        WHEN setting_key = 'smtp_encryption' THEN 'tls'
        ELSE setting_value
    END");
    
    $db->commit();
    $_SESSION['success_message'] = "Settings have been reset to default values!";
} catch (Exception $e) {
    $db->rollback();
    $_SESSION['error_message'] = "Error resetting settings: " . $e->getMessage();
}

header("Location: settings.php");
exit();
?>