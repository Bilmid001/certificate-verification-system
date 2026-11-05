<?php
// fraud-detection.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

global $db;

// Date range filtering
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get fraud statistics
$fraudStats = $db->query("
    SELECT 
        COUNT(*) as total_suspicious,
        SUM(status = 'revoked') as total_revoked,
        COUNT(DISTINCT certificate_id) as unique_suspicious_certs,
        COUNT(DISTINCT verifier_ip) as suspicious_ips
    FROM verifications
    WHERE (status = 'invalid' OR status = 'revoked')
    AND verification_date BETWEEN '$startDate' AND '$endDate 23:59:59'
")->fetch_assoc();

// Get suspicious certificates
$suspiciousCerts = $db->query("
    SELECT 
        c.certificate_number,
        c.student_name,
        i.name as institution_name,
        COUNT(v.id) as verification_attempts,
        SUM(v.status = 'invalid') as invalid_count,
        SUM(v.status = 'revoked') as revoked_count,
        MAX(v.verification_date) as last_attempt
    FROM verifications v
    JOIN certificates c ON v.certificate_id = c.id
    JOIN institutions i ON c.institution_id = i.id
    WHERE v.verification_date BETWEEN '$startDate' AND '$endDate 23:59:59'
    GROUP BY c.id
    HAVING invalid_count > 0 OR revoked_count > 0
    ORDER BY verification_attempts DESC
    LIMIT 50
")->fetch_all(MYSQLI_ASSOC);

// Get suspicious IPs
$suspiciousIPs = $db->query("
    SELECT 
        verifier_ip,
        COUNT(*) as attempt_count,
        COUNT(DISTINCT certificate_id) as certs_attempted,
        SUM(status = 'invalid') as invalid_count,
        SUM(status = 'revoked') as revoked_count
    FROM verifications
    WHERE verification_date BETWEEN '$startDate' AND '$endDate 23:59:59'
    GROUP BY verifier_ip
    HAVING invalid_count > 0 OR revoked_count > 0
    ORDER BY attempt_count DESC
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container-fluid mt-4">
    <div class="dashboard-header">
        <h1 class="h3 mb-0">Fraud Detection Dashboard</h1>
        <p class="text-muted mb-0">Identify and manage suspicious certificate activities</p>
    </div>
    
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Filter Reports</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4 mb-2">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="<?php echo $startDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 mb-2">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="<?php echo $endDate; ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-4 mb-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                    <a href="fraud-detection.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
            <p class="mt-2 mb-0 text-muted">
                Showing fraud data from <?php echo date('F j, Y', strtotime($startDate)); ?> to <?php echo date('F j, Y', strtotime($endDate)); ?>
            </p>
        </div>
    </div>
    
    <!-- Fraud Summary Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Suspicious Activities</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($fraudStats['total_suspicious']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php 
                        echo number_format((float)($fraudStats['total_revoked'] ?? 0)); 
                        ?>
                    </div>

                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Suspicious Certificates</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($fraudStats['unique_suspicious_certs']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Suspicious IPs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($fraudStats['suspicious_ips']); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-network-wired fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Suspicious Certificates -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Suspicious Certificates</h6>
                    <small>Top 50 by verification attempts</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Certificate</th>
                                    <th>Student</th>
                                    <th>Institution</th>
                                    <th>Attempts</th>
                                    <th>Invalid</th>
                                    <th>Revoked</th>
                                    <th>Last Attempt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suspiciousCerts as $cert): ?>
                                <tr class="<?php echo $cert['revoked_count'] > 0 ? 'table-danger' : 'table-warning'; ?>">
                                    <td><?php echo substr($cert['certificate_number'], 0, 8) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars($cert['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['institution_name']); ?></td>
                                    <td><?php echo $cert['verification_attempts']; ?></td>
                                    <td><?php echo $cert['invalid_count']; ?></td>
                                    <td><?php echo $cert['revoked_count']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($cert['last_attempt'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious IPs -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">Suspicious IP Addresses</h6>
                    <small>Top 20 by attempt count</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Attempts</th>
                                    <th>Certs Attempted</th>
                                    <th>Invalid</th>
                                    <th>Revoked</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suspiciousIPs as $ip): ?>
                                <tr class="<?php echo $ip['revoked_count'] > 0 ? 'table-danger' : 'table-warning'; ?>">
                                    <td><?php echo htmlspecialchars($ip['verifier_ip']); ?></td>
                                    <td><?php echo $ip['attempt_count']; ?></td>
                                    <td><?php echo $ip['certs_attempted']; ?></td>
                                    <td><?php echo $ip['invalid_count']; ?></td>
                                    <td><?php echo $ip['revoked_count']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger block-ip-btn" data-ip="<?php echo htmlspecialchars($ip['verifier_ip']); ?>">
                                            <i class="fas fa-ban"></i> Block
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fraud Patterns Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Fraud Pattern Analysis</h6>
                    <small>Detection trends</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="fraudTrendChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="fraudTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIpModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Block IP Address</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="blockIpForm">
                    <input type="hidden" name="ip_address" id="ip_address">
                    <div class="form-group">
                        <label>IP Address to Block</label>
                        <input type="text" class="form-control" id="display_ip" readonly>
                    </div>
                    <div class="form-group">
                        <label>Reason for Blocking</label>
                        <textarea class="form-control" name="reason" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Block Duration</label>
                        <select class="form-control" name="duration" required>
                            <option value="1">1 Day</option>
                            <option value="7">1 Week</option>
                            <option value="30">1 Month</option>
                            <option value="365">1 Year</option>
                            <option value="0" selected>Permanent</option>
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-ban mr-2"></i>Confirm Block
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Handle IP blocking
    $('.block-ip-btn').click(function() {
        var ip = $(this).data('ip');
        $('#ip_address').val(ip);
        $('#display_ip').val(ip);
        $('#blockIpModal').modal('show');
    });
    
    // Submit block form
    $('#blockIpForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'block-ip.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    $('#blockIpModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            }
        });
    });
    
    // Fraud Trend Chart
    const trendCtx = document.getElementById('fraudTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Suspicious Activities',
                data: [65, 59, 80, 81, 56, 55, 40],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                tension: 0.3,
                fill: true
            }, {
                label: 'Revoked Certificates',
                data: [28, 48, 40, 19, 86, 27, 90],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Fraud Trends'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Fraud Type Chart
    const typeCtx = document.getElementById('fraudTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Forged Certificates', 'Stolen Credentials', 'Duplicate Submissions', 'System Exploits'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    '#e74c3c',
                    '#f39c12',
                    '#3498db',
                    '#2ecc71'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Fraud Type Distribution'
                },
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>