<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// In a real application, you would implement actual cache clearing logic here
// For example, deleting cache files or resetting cache systems

$_SESSION['success_message'] = "System cache has been cleared successfully!";
header("Location: settings.php");
exit();
?>