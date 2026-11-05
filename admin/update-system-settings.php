<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName = trim($_POST['site_name']);
    $siteUrl = trim($_POST['site_url']);
    
    // Validate inputs
    if (empty($siteName) || empty($siteUrl)) {
        $_SESSION['error'] = 'Please fill in all required fields.';
    } else {
        // Update config file
        $configFile = '../includes/config.php';
        $configContent = file_get_contents($configFile);
        
        // Update site name
        $configContent = preg_replace(
            "/define\('SITE_NAME', '.*?'\);/",
            "define('SITE_NAME', '" . addslashes($siteName) . "');",
            $configContent
        );
        
        // Update site URL
        $configContent = preg_replace(
            "/define\('SITE_URL', '.*?'\);/",
            "define('SITE_URL', '" . addslashes($siteUrl) . "');",
            $configContent
        );
        
        if (file_put_contents($configFile, $configContent) !== false) {
            $_SESSION['success'] = 'System settings updated successfully!';
        } else {
            $_SESSION['error'] = 'Error updating system settings.';
        }
    }
}

header("Location: settings.php");
exit();
?>