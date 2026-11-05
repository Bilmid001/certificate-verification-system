<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

global $db;

// Handle institution actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($_GET['action'] === 'toggle') {
        $stmt = $db->prepare("UPDATE institutions SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($_GET['action'] === 'delete') {
        $stmt = $db->prepare("DELETE FROM institutions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    header("Location: manage-institutions.php");
    exit();
}

// Get all institutions
$institutions = $db->query("SELECT * FROM institutions ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Manage Institutions</h2>
        <a href="add-institution.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Institution
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>API Key</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($institutions as $institution): ?>
                        <tr>
                            <td><?php echo $institution['id']; ?></td>
                            <td><?php echo htmlspecialchars($institution['name']); ?></td>
                            <td><?php echo htmlspecialchars($institution['email']); ?></td>
                            <td>
                                <code><?php echo $institution['api_key'] ?: 'Not generated'; ?></code>
                                <?php if ($institution['api_key']): ?>
                                    <button class="btn btn-sm btn-outline-secondary copy-btn" data-text="<?php echo $institution['api_key']; ?>">
                                        Copy
                                    </button>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $institution['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $institution['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit-institution.php?id=<?php echo $institution['id']; ?>" class="btn btn-outline-primary">Edit</a>
                                    <a href="?action=toggle&id=<?php echo $institution['id']; ?>" class="btn btn-outline-<?php echo $institution['is_active'] ? 'warning' : 'success'; ?>">
                                        <?php echo $institution['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $institution['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy API key to clipboard
    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.getAttribute('data-text');
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            });
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>