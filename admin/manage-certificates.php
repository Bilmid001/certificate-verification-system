<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

global $db;

// Handle actions
$action = isset($_GET['action']) ? $_GET['action'] : '';
$certificateId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action && $certificateId) {
    switch ($action) {
        case 'revoke':
            $stmt = $db->prepare("UPDATE certificates SET is_revoked = 1 WHERE id = ?");
            $stmt->bind_param("i", $certificateId);
            $stmt->execute();
            $_SESSION['success'] = "Certificate revoked successfully";
            break;
            
        case 'restore':
            $stmt = $db->prepare("UPDATE certificates SET is_revoked = 0 WHERE id = ?");
            $stmt->bind_param("i", $certificateId);
            $stmt->execute();
            $_SESSION['success'] = "Certificate restored successfully";
            break;
            
        case 'delete':
            $stmt = $db->prepare("DELETE FROM certificates WHERE id = ?");
            $stmt->bind_param("i", $certificateId);
            $stmt->execute();
            $_SESSION['success'] = "Certificate deleted successfully";
            break;
    }
    header("Location: manage-certificates.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where = "WHERE c.certificate_number LIKE ? OR c.student_name LIKE ? OR c.course_name LIKE ? OR i.name LIKE ?";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
    $types = 'ssss';
}

// Get certificates count
$countQuery = "SELECT COUNT(*) as count FROM certificates c JOIN institutions i ON c.institution_id = i.id $where";
$stmt = $db->prepare($countQuery);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$totalCertificates = $stmt->get_result()->fetch_assoc()['count'];
$totalPages = ceil($totalCertificates / $perPage);

// Get certificates
$query = "SELECT c.*, i.name as institution_name 
          FROM certificates c 
          JOIN institutions i ON c.institution_id = i.id 
          $where 
          ORDER BY c.issue_date DESC 
          LIMIT ? OFFSET ?";
$stmt = $db->prepare($query);

if (!empty($params)) {
    $params[] = $perPage;
    $params[] = $offset;
    $types .= 'ii';
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}

$stmt->execute();
$certificates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Certificate Management</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertificateModal">
                <i class="fas fa-plus me-2"></i>Add Certificate
            </button>
        </div>
    </div>
    
    <!-- Search and Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-8 mb-2">
                    <input type="text" class="form-control" name="search" placeholder="Search certificates..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="manage-certificates.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Certificates Table -->
    <div class="card">
        <div class="card-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Certificate No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Institution</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $certificate): ?>
                        <tr>
                            <td><?php echo $certificate['certificate_number']; ?></td>
                            <td><?php echo htmlspecialchars($certificate['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($certificate['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($certificate['institution_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($certificate['issue_date'])); ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?php echo $certificate['is_revoked'] ? 'danger' : 'success'; ?>">
                                    <?php echo $certificate['is_revoked'] ? 'Revoked' : 'Active'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../public/verify.php?certificate=<?php echo $certificate['certificate_number']; ?>" 
                                       class="btn btn-outline-primary" target="_blank" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-warning edit-certificate-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editCertificateModal"
                                            data-id="<?php echo $certificate['id']; ?>"
                                            data-number="<?php echo $certificate['certificate_number']; ?>"
                                            data-student="<?php echo htmlspecialchars($certificate['student_name']); ?>"
                                            data-course="<?php echo htmlspecialchars($certificate['course_name']); ?>"
                                            data-institution="<?php echo $certificate['institution_id']; ?>"
                                            data-date="<?php echo $certificate['issue_date']; ?>"
                                            data-expiry="<?php echo $certificate['expiry_date']; ?>"
                                            data-revoked="<?php echo $certificate['is_revoked']; ?>"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($certificate['is_revoked']): ?>
                                        <a href="manage-certificates.php?action=restore&id=<?php echo $certificate['id']; ?>" 
                                           class="btn btn-outline-success" title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="manage-certificates.php?action=revoke&id=<?php echo $certificate['id']; ?>" 
                                           class="btn btn-outline-danger" title="Revoke">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="manage-certificates.php?action=delete&id=<?php echo $certificate['id']; ?>" 
                                       class="btn btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this certificate?')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Add Certificate Modal -->
<div class="modal fade" id="addCertificateModal" tabindex="-1" aria-labelledby="addCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addCertificateModalLabel">Add New Certificate</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process-certificate.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="student_name" class="form-label">Student Name *</label>
                            <input type="text" class="form-control" id="student_name" name="student_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="course_name" class="form-label">Course/Program *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="institution_id" class="form-label">Institution *</label>
                            <select class="form-select" id="institution_id" name="institution_id" required>
                                <option value="">Select Institution</option>
                                <?php
                                $institutions = $db->query("SELECT id, name FROM institutions ORDER BY name");
                                while ($institution = $institutions->fetch_assoc()): ?>
                                    <option value="<?php echo $institution['id']; ?>"><?php echo htmlspecialchars($institution['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="certificate_number" class="form-label">Certificate Number</label>
                            <input type="text" class="form-control" id="certificate_number" name="certificate_number" readonly>
                            <button type="button" id="generate-certificate-number" class="btn btn-sm btn-outline-secondary mt-2">Generate Number</button>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="issue_date" class="form-label">Issue Date *</label>
                            <input type="date" class="form-control" id="issue_date" name="issue_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="expiry_date" class="form-label">Expiry Date (Optional)</label>
                            <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="certificate_file" class="form-label">Upload Certificate (Optional)</label>
                        <input type="file" class="form-control" id="certificate_file" name="certificate_file" accept=".pdf,.jpg,.png">
                        <div class="form-text">Upload a digital copy of the certificate (PDF or image)</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Certificate Modal -->
<div class="modal fade" id="editCertificateModal" tabindex="-1" aria-labelledby="editCertificateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCertificateModalLabel">Edit Certificate</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process-certificate.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_student_name" class="form-label">Student Name *</label>
                            <input type="text" class="form-control" id="edit_student_name" name="student_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_course_name" class="form-label">Course/Program *</label>
                            <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_institution_id" class="form-label">Institution *</label>
                            <select class="form-select" id="edit_institution_id" name="institution_id" required>
                                <option value="">Select Institution</option>
                                <?php
                                $institutions = $db->query("SELECT id, name FROM institutions ORDER BY name");
                                while ($institution = $institutions->fetch_assoc()): ?>
                                    <option value="<?php echo $institution['id']; ?>"><?php echo htmlspecialchars($institution['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_certificate_number" class="form-label">Certificate Number</label>
                            <input type="text" class="form-control" id="edit_certificate_number" name="certificate_number" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_issue_date" class="form-label">Issue Date *</label>
                            <input type="date" class="form-control" id="edit_issue_date" name="issue_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_expiry_date" class="form-label">Expiry Date (Optional)</label>
                            <input type="date" class="form-control" id="edit_expiry_date" name="expiry_date">
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="edit_is_revoked" name="is_revoked">
                        <label class="form-check-label" for="edit_is_revoked">Revoke this certificate</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate certificate number
    document.getElementById('generate-certificate-number').addEventListener('click', function() {
        const institutionId = document.getElementById('institution_id').value;
        if (!institutionId) {
            alert('Please select an institution first');
            return;
        }
        
        const prefix = "CERT-" + institutionId.toString().padStart(4, '0');
        const random = Math.random().toString(36).substring(2, 10).toUpperCase();
        document.getElementById('certificate_number').value = prefix + "-" + random;
    });
    
    // Handle edit modal data
    const editCertificateModal = document.getElementById('editCertificateModal');
    if (editCertificateModal) {
        editCertificateModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_certificate_number').value = button.getAttribute('data-number');
            document.getElementById('edit_student_name').value = button.getAttribute('data-student');
            document.getElementById('edit_course_name').value = button.getAttribute('data-course');
            document.getElementById('edit_institution_id').value = button.getAttribute('data-institution');
            document.getElementById('edit_issue_date').value = button.getAttribute('data-date');
            document.getElementById('edit_expiry_date').value = button.getAttribute('data-expiry');
            document.getElementById('edit_is_revoked').checked = button.getAttribute('data-revoked') === '1';
        });
    }
});
</script>
<?php if (isset($_SESSION['success'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastEl = document.getElementById('certificateSuccessToast');
        const toast = new bootstrap.Toast(toastEl, {
            delay: 5000 // 5 seconds
        });
        toast.show();
    });
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>


<?php require_once '../includes/footer.php'; ?>