<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isInstitutionLoggedIn()) {
    header("Location: login.php");
    exit();
}

global $db;
$institutionId = $_SESSION['institution_id'];

// Get stats
$certificatesCount = $db->query("SELECT COUNT(*) as count FROM certificates WHERE institution_id = $institutionId")->fetch_assoc()['count'];
$verificationsCount = $db->query("SELECT COUNT(*) as count FROM verifications v 
                                JOIN certificates c ON v.certificate_id = c.id 
                                WHERE c.institution_id = $institutionId")->fetch_assoc()['count'];
$recentCertificates = $db->query("SELECT * FROM certificates 
                                 WHERE institution_id = $institutionId 
                                 ORDER BY issue_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/insheader.php';
?>

<div class="container-fluid mt-4">
    <h1 class="h3 mb-4">Institution Dashboard - <?php echo $_SESSION['institution_name']; ?></h1>
    
    <div class="row">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Certificates Issued</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $certificatesCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Verifications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $verificationsCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recently Issued Certificates</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Certificate No.</th>
                                    <th>Student Name</th>
                                    <th>Course</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCertificates as $certificate): ?>
                                <tr>
                                    <td><?php echo substr($certificate['certificate_number'], 0, 10) . '...'; ?></td>
                                    <td><?php echo $certificate['student_name']; ?></td>
                                    <td><?php echo $certificate['course_name']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($certificate['issue_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="issue-certificate.php" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Issue Certificate
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="certificate-list.php" class="btn btn-success btn-block">
                                <i class="fas fa-list"></i> View Certificates
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="../public/verify.php" class="btn btn-info btn-block">
                                <i class="fas fa-check"></i> Verify Certificate
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="settings.php" class="btn btn-warning btn-block">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>