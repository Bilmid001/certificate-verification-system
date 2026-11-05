<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isInstitutionLoggedIn()) {
    header("Location: login.php");
    exit();
}

global $db;
$institutionId = $_SESSION['institution_id'];

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "WHERE institution_id = ?";
$searchParams = [$institutionId];
$searchTypes = 'i';

if (!empty($search)) {
    $where .= " AND (certificate_number LIKE ? OR student_name LIKE ? OR course_name LIKE ?)";
    $searchTerm = "%$search%";
    $searchParams = array_merge($searchParams, [$searchTerm, $searchTerm, $searchTerm]);
    $searchTypes .= 'sss';
}

// =============================
// 1. Get total certificate count (for pagination)
// =============================
$countQuery = "SELECT COUNT(*) as count FROM certificates $where";
$countStmt = $db->prepare($countQuery);

// Bind parameters only if needed
if (!empty($searchParams)) {
    $countStmt->bind_param($searchTypes, ...$searchParams);
}

$countStmt->execute();
$totalCertificates = $countStmt->get_result()->fetch_assoc()['count'];
$totalPages = ceil($totalCertificates / $perPage);

// =============================
// 2. Get certificates with pagination
// =============================
$dataQuery = "SELECT * FROM certificates $where ORDER BY issue_date DESC LIMIT ? OFFSET ?";
$dataStmt = $db->prepare($dataQuery);

// Add LIMIT and OFFSET to parameters
$dataParams = array_merge($searchParams, [$perPage, $offset]);
$dataTypes = $searchTypes . 'ii';

$dataStmt->bind_param($dataTypes, ...$dataParams);
$dataStmt->execute();
$certificates = $dataStmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/insheader.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Certificate List</h2>
        <a href="issue-certificate.php" class="btn btn-primary">Issue New Certificate</a>
    </div>
    
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
                    <a href="certificate-list.php" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Certificate No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
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
                            <td><?php echo date('M j, Y', strtotime($certificate['issue_date'])); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $certificate['is_revoked'] ? 'danger' : 'success'; ?>">
                                    <?php echo $certificate['is_revoked'] ? 'Revoked' : 'Active'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../public/verify.php?certificate=<?php echo $certificate['certificate_number']; ?>" 
                                       class="btn btn-outline-primary" target="_blank">View</a>
                                    <a href="revoke-certificate.php?id=<?php echo $certificate['id']; ?>" 
                                       class="btn btn-outline-<?php echo $certificate['is_revoked'] ? 'success' : 'danger'; ?>">
                                        <?php echo $certificate['is_revoked'] ? 'Restore' : 'Revoke'; ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
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

<?php require_once '../includes/footer.php'; ?>