<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

global $db;

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] === 'toggle') {
        $stmt = $db->prepare("UPDATE admins SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    header("Location: manage-users.php");
    exit();
}

// Get all admin users
$users = $db->query("SELECT * FROM admins ORDER BY username ASC")->fetch_all(MYSQLI_ASSOC);
require_once '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Users</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- FontAwesome CDN for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <!-- Your Custom CSS -->
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        /* Container and main content */
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin-left: 120px;
        }

        /* Header */
        .dashboard-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 0;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary);
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }

        /* Content card */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: white;
            padding: 15px 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            color: var(--dark);
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        thead th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid #dee2e6;
        }

        tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background-color: #f8f9fa;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .badge-valid {
            background-color: var(--success);
            color: white;
        }
        .badge-invalid {
            background-color: var(--danger);
            color: white;
        }
        .badge-revoked {
            background-color: var(--warning);
            color: white;
        }

        /* Buttons */
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        /* Modal header */
        .modal-header {
            background: var(--secondary);
            color: white;
            font-weight: 600;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.4rem;
            }
            .dashboard-header {
                padding: 20px;
            }
            .content-card {
                margin: 10px;
            }
            .btn-group {
                flex-wrap: wrap;
                gap: 5px;
            }
            .btn-action {
                flex: 1 1 auto;
            }
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h2 class="page-title">Manage Users</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-2"></i> Add New User
            </button>
        </div>

        <div class="content-card">
            <div class="card-header">
                All Admin Users
            </div>
            <div class="table-container p-3">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Last Login</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo $user['last_login'] ? date('M j, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                <td>
                                    <span class="badge <?php echo $user['is_active'] ? 'badge-valid' : 'badge-invalid'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="User actions">
                                        <button 
                                            class="btn btn-action btn-outline-primary edit-user-btn"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUserModal"
                                            data-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                            data-fullname="<?php echo htmlspecialchars($user['full_name']); ?>"
                                            data-status="<?php echo $user['is_active']; ?>">
                                            Edit
                                        </button>
                                        <a href="?action=toggle&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-action btn-outline-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>">
                                            <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-action btn-outline-danger" 
                                           onclick="return confirm('Are you sure?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No users found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="process-user.php" method="POST" class="modal-content">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" name="username" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" name="confirm_password" required />
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked />
                        <label class="form-check-label" for="is_active">Active User</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="process-user.php" method="POST" class="modal-content">
                <input type="hidden" name="action" value="edit" />
                <input type="hidden" id="edit_id" name="id" />
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" class="form-control" id="edit_username" name="username" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_fullname" name="full_name" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" />
                        <div class="form-text">Leave blank to keep current password</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" />
                        <label class="form-check-label" for="edit_is_active">Active User</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (Popper + Bootstrap) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                document.getElementById('edit_id').value = button.getAttribute('data-id');
                document.getElementById('edit_username').value = button.getAttribute('data-username');
                document.getElementById('edit_fullname').value = button.getAttribute('data-fullname');
                document.getElementById('edit_is_active').checked = button.getAttribute('data-status') === '1';
            });
        }
    });
    </script>

</body>
</html>
