<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

global $db;

$action = isset($_POST['action']) ? $_POST['action'] : '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        // Add new certificate
        $studentName = trim($_POST['student_name']);
        $courseName = trim($_POST['course_name']);
        $institutionId = intval($_POST['institution_id']);
        $certificateNumber = trim($_POST['certificate_number']);
        $issueDate = trim($_POST['issue_date']);
        $expiryDate = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
        
        // Validate inputs
        if (empty($studentName) || empty($courseName) || empty($institutionId) || empty($certificateNumber) || empty($issueDate)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Handle file upload if present
            $filePath = null;
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/certificates/';
                $fileName = $certificateNumber . '_' . basename($_FILES['certificate_file']['name']);
                $targetPath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetPath)) {
                    $filePath = $targetPath;
                }
            }
            
            // Insert certificate into database
            $stmt = $db->prepare("INSERT INTO certificates 
                                 (institution_id, certificate_number, student_name, course_name, 
                                  issue_date, expiry_date, file_path) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssss", $institutionId, $certificateNumber, 
                             $studentName, $courseName, $issueDate, $expiryDate, $filePath);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Certificate issued successfully! Certificate Number: ' . $certificateNumber;
                header("Location: manage-certificates.php");
                exit();
            } else {
                $error = 'Error issuing certificate: ' . $db->error;
            }
        }
    } elseif ($action === 'edit') {
        // Edit existing certificate
        $certificateId = intval($_POST['id']);
        $studentName = trim($_POST['student_name']);
        $courseName = trim($_POST['course_name']);
        $institutionId = intval($_POST['institution_id']);
        $certificateNumber = trim($_POST['certificate_number']);
        $issueDate = trim($_POST['issue_date']);
        $expiryDate = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
        $isRevoked = isset($_POST['is_revoked']) ? 1 : 0;
        
        // Validate inputs
        if (empty($studentName) || empty($courseName) || empty($institutionId) || empty($certificateNumber) || empty($issueDate)) {
            $error = 'Please fill in all required fields.';
        } else {
            // Update certificate in database
            $stmt = $db->prepare("UPDATE certificates 
                                 SET institution_id = ?, student_name = ?, course_name = ?, 
                                     issue_date = ?, expiry_date = ?, is_revoked = ?
                                 WHERE id = ?");
            $stmt->bind_param("isssssi", $institutionId, $studentName, $courseName, 
                             $issueDate, $expiryDate, $isRevoked, $certificateId);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Certificate updated successfully!';
                header("Location: manage-certificates.php");
                exit();
            } else {
                $error = 'Error updating certificate: ' . $db->error;
            }
        }
    }
}

// If we get here, there was an error
$_SESSION['error'] = $error;
header("Location: manage-certificates.php");
exit();
?>