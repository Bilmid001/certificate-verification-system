<?php
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function isInstitutionLoggedIn() {
    return isset($_SESSION['institution_logged_in']) && $_SESSION['institution_logged_in'] === true;
}

function adminLogin($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Update last login
            $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $admin['id']);
            $stmt->execute();
            
            return true;
        }
    }
    
    return false;
}

function institutionLogin($email, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM institutions WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $institution = $result->fetch_assoc();
        
        if (password_verify($password, $institution['password'])) {
            if ($institution['is_active']) {
                $_SESSION['institution_logged_in'] = true;
                $_SESSION['institution_id'] = $institution['id'];
                $_SESSION['institution_name'] = $institution['name'];
                return true;
            }
        }
    }
    
    return false;
}

function logout() {
    session_unset();
    session_destroy();
    session_start();
}
?>