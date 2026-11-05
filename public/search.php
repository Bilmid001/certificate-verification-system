<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$results = [];
$searchTerm = '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    
    if (!empty($searchTerm)) {
        global $db;
        
        $stmt = $db->prepare("
            SELECT c.*, i.name as institution_name 
            FROM certificates c
            JOIN institutions i ON c.institution_id = i.id
            WHERE c.student_name LIKE ? OR c.certificate_number LIKE ?
            ORDER BY c.issue_date DESC
            LIMIT 50
        ");
        
        $searchParam = "%$searchTerm%";
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!-- Include Bootstrap and Animate.css -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    body {
        background: linear-gradient(to right, #eef2f3, #cfd9df);
        font-family: 'Segoe UI', sans-serif;
        animation: fadeInBody 1s ease-in;
    }

    @keyframes fadeInBody {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .search-card {
        border-radius: 15px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        animation: fadeInUp 1s ease;
    }

    @keyframes fadeInUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .card-header {
        background: linear-gradient(45deg, #007bff, #6610f2);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .form-control:focus {
        border-color: #6610f2;
        box-shadow: 0 0 0 0.25rem rgba(102, 16, 242, 0.2);
    }

    .btn-primary {
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .table tr:hover {
        background-color: #f2f2f2;
    }

    .alert {
        animation: fadeIn 0.6s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-md-10 col-lg-8 mx-auto">
            <div class="card search-card">
                <div class="card-header text-center">
                    <h3 class="mb-0">Search Certificates</h3>
                </div>

                <div class="card-body p-4">
                    <form method="GET" class="mb-4">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Enter student name or certificate number"
                                   value="<?php echo htmlspecialchars($searchTerm); ?>" required>
                            <button type="submit" class="btn btn-primary px-4">Search</button>
                        </div>
                    </form>

                    <?php if (!empty($searchTerm)): ?>
                        <div class="search-results">
                            <h5 class="mb-3">Search Results for "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"</h5>

                            <?php if (empty($results)): ?>
                                <div class="alert alert-info text-center">
                                    No certificates found matching your search criteria.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Certificate No.</th>
                                                <th>Student Name</th>
                                                <th>Institution</th>
                                                <th>Issue Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $certificate): ?>
                                            <tr>
                                                <td><?php echo $certificate['certificate_number']; ?></td>
                                                <td><?php echo htmlspecialchars($certificate['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($certificate['institution_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($certificate['issue_date'])); ?></td>
                                                <td>
                                                    <a href="verify.php?certificate=<?php echo $certificate['certificate_number']; ?>"
                                                       class="btn btn-sm btn-outline-primary">
                                                        Verify
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

