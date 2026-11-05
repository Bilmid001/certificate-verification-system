<?php
// block-ip.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = trim($_POST['ip_address']);
    $reason = trim($_POST['reason']);
    $duration = intval($_POST['duration']);
    $adminId = $_SESSION['admin_id'];
    
    // Calculate expiration date
    $expiresAt = $duration > 0 ? date('Y-m-d H:i:s', strtotime("+$duration days")) : null;
    
    try {
        global $db;
        $stmt = $db->prepare("
            INSERT INTO blocked_ips 
            (ip_address, reason, expires_at, blocked_by, created_at) 
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            reason = VALUES(reason),
            expires_at = VALUES(expires_at),
            blocked_by = VALUES(blocked_by)
        ");
        $stmt->bind_param("sssi", $ip, $reason, $expiresAt, $adminId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>