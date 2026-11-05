<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isInstitutionLoggedIn()) {
    header("Location: login.php");
    exit();
}

global $db;
$institutionId = $_SESSION['institution_id'];
$error = '';
$success = '';

// Get institution data
$stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
$stmt->bind_param("i", $institutionId);
$stmt->execute();
$institution = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        
        // Validate inputs
        if (empty($name) || empty($email)) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email exists for another institution
            $stmt = $db->prepare("SELECT id FROM institutions WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $institutionId);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'An institution with this email already exists.';
            } else {
                // Update institution
                $stmt = $db->prepare("UPDATE institutions 
                                    SET name = ?, email = ?, address = ?, phone = ?
                                    WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $email, $address, $phone, $institutionId);
                
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully!';
                    // Refresh institution data
                    $stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
                    $stmt->bind_param("i", $institutionId);
                    $stmt->execute();
                    $institution = $stmt->get_result()->fetch_assoc();
                    $_SESSION['institution_name'] = $institution['name'];
                } else {
                    $error = 'Error updating profile: ' . $db->error;
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $currentPassword = trim($_POST['current_password']);
        $newPassword = trim($_POST['new_password']);
        $confirmPassword = trim($_POST['confirm_password']);
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please fill in all password fields.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } elseif (!password_verify($currentPassword, $institution['password'])) {
            $error = 'Current password is incorrect.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE institutions SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $institutionId);
            
            if ($stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Error changing password: ' . $db->error;
            }
        }
    }
}

require_once '../includes/insheader.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Institution Settings</h3>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" 
                                    data-bs-target="#profile" type="button" role="tab">
                                Profile
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" 
                                    data-bs-target="#password" type="button" role="tab">
                                Change Password
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="settingsTabsContent">
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Institution Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($institution['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($institution['email']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($institution['address']); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($institution['phone']); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" 
                                               value="<?php echo htmlspecialchars($institution['api_key']); ?>" readonly>
                                        <button class="btn btn-outline-secondary copy-btn" type="button" 
                                                data-text="<?php echo htmlspecialchars($institution['api_key']); ?>">
                                            Copy
                                        </button>
                                    </div>
                                    <small class="text-muted">Use this key for API authentication</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                        
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form method="POST">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>