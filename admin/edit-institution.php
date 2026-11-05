<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage-institutions.php");
    exit();
}

$institutionId = intval($_GET['id']);
$error = '';
$success = '';

global $db;

// Get institution data
$stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
$stmt->bind_param("i", $institutionId);
$stmt->execute();
$institution = $stmt->get_result()->fetch_assoc();

if (!$institution) {
    header("Location: manage-institutions.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
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
                                SET name = ?, email = ?, address = ?, phone = ?, is_active = ?
                                WHERE id = ?");
            $stmt->bind_param("ssssii", $name, $email, $address, $phone, $isActive, $institutionId);
            
            if ($stmt->execute()) {
                $success = 'Institution updated successfully!';
                // Refresh institution data
                $stmt = $db->prepare("SELECT * FROM institutions WHERE id = ?");
                $stmt->bind_param("i", $institutionId);
                $stmt->execute();
                $institution = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Error updating institution: ' . $db->error;
            }
        }
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Edit Institution</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
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
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                  <?php echo $institution['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active Institution</label>
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
                        
                        <button type="submit" class="btn btn-primary">Update Institution</button>
                        <a href="manage-institutions.php" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>