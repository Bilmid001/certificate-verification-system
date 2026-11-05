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
        // Add new user
        $username = trim($_POST['username']);
        $fullName = trim($_POST['full_name']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate inputs
        if (empty($username) || empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all required fields.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            // Check if username exists
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Username already exists.';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $db->prepare("INSERT INTO admins (username, password, full_name, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $username, $hashedPassword, $fullName, $isActive);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'User added successfully!';
                    header("Location: manage-users.php");
                    exit();
                } else {
                    $error = 'Error adding user: ' . $db->error;
                }
            }
        }
    } elseif ($action === 'edit') {
        // Edit existing user
        $userId = intval($_POST['id']);
        $username = trim($_POST['username']);
        $fullName = trim($_POST['full_name']);
        $password = trim($_POST['password']);
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate inputs
        if (empty($username)) {
            $error = 'Username is required.';
        } else {
            // Check if username exists for another user
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $userId);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'Username already exists for another user.';
            } else {
                // Update user
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE admins SET username = ?, password = ?, full_name = ?, is_active = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $username, $hashedPassword, $fullName, $isActive, $userId);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET username = ?, full_name = ?, is_active = ? WHERE id = ?");
                    $stmt->bind_param("ssii", $username, $fullName, $isActive, $userId);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'User updated successfully!';
                    header("Location: manage-users.php");
                    exit();
                } else {
                    $error = 'Error updating user: ' . $db->error;
                }
            }
        }
    }
}

// If we get here, there was an error
$_SESSION['error'] = $error;
header("Location: manage-users.php");
exit();
?>