<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: certificates-list.php");
    exit();
}

$certificateId = intval($_GET['id']);

global $db;

// Get current status
$stmt = $db->prepare("SELECT is_revoked FROM certificates WHERE id = ?");
$stmt->bind_param("i", $certificateId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: certificates-list.php");
    exit();
}

$certificate = $result->fetch_assoc();

// Toggle revocation status
$newStatus = $certificate['is_revoked'] ? 0 : 1;
$stmt = $db->prepare("UPDATE certificates SET is_revoked = ? WHERE id = ?");
$stmt->bind_param("ii", $newStatus, $certificateId);
$stmt->execute();

header("Location: certificates-list.php");
exit();
?>