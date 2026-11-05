<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Get parameters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate date format
if (!validateDate($startDate) || !validateDate($endDate)) {
    die("Invalid date format");
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

global $db;

// Get verification data
$query = "SELECT 
            v.verification_date,
            v.verifier_ip,
            v.status,
            c.certificate_number,
            c.student_name,
            c.course_name,
            i.name as institution_name
          FROM verifications v
          JOIN certificates c ON v.certificate_id = c.id
          JOIN institutions i ON c.institution_id = i.id
          WHERE v.verification_date BETWEEN ? AND ?
          ORDER BY v.verification_date DESC";

$stmt = $db->prepare($query);
$endDateFull = $endDate . ' 23:59:59';
$stmt->bind_param("ss", $startDate, $endDateFull);
$stmt->execute();
$result = $stmt->get_result();
$verifications = $result->fetch_all(MYSQLI_ASSOC);

// Get summary statistics
$statsQuery = "SELECT 
                COUNT(*) as total,
                SUM(status = 'valid') as valid,
                SUM(status = 'invalid') as invalid,
                SUM(status = 'revoked') as revoked
              FROM verifications
              WHERE verification_date BETWEEN ? AND ?";

$stmt = $db->prepare($statsQuery);
$stmt->bind_param("ss", $startDate, $endDateFull);
$stmt->execute();
$statsResult = $stmt->get_result();
$stats = $statsResult->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Reports - <?php echo SITE_NAME; ?></title>
    <link href="<?php echo SITE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .print-header h1 {
            margin: 0;
        }
        .print-header p {
            margin: 5px 0;
        }
        .summary-stats {
            margin-bottom: 20px;
        }
        .summary-stats .card {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer-note {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-header">
            <h1>Verification Report</h1>
            <p>From <?php echo date('F j, Y', strtotime($startDate)); ?> to <?php echo date('F j, Y', strtotime($endDate)); ?></p>
            <p>Generated on: <?php echo date('F j, Y H:i'); ?></p>
        </div>

        <div class="summary-stats">
            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Verifications</h5>
                            <p class="card-text"><?php echo $stats['total']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Valid</h5>
                            <p class="card-text"><?php echo $stats['valid']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Invalid</h5>
                            <p class="card-text"><?php echo $stats['invalid']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Revoked</h5>
                            <p class="card-text"><?php echo $stats['revoked']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Verification Date</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Certificate Number</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Institution</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($verifications as $verification): ?>
                <tr>
                    <td><?php echo $verification['verification_date']; ?></td>
                    <td><?php echo $verification['verifier_ip']; ?></td>
                    <td><?php echo ucfirst($verification['status']); ?></td>
                    <td><?php echo $verification['certificate_number']; ?></td>
                    <td><?php echo htmlspecialchars($verification['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($verification['course_name']); ?></td>
                    <td><?php echo htmlspecialchars($verification['institution_name']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="footer-note">
            <p>This report was generated automatically by <?php echo SITE_NAME; ?> on <?php echo date('F j, Y \a\t H:i'); ?></p>
        </div>

        <div class="no-print text-center mt-4">
            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
            <a href="reports.php" class="btn btn-secondary">Back to Reports</a>
        </div>
    </div>
</body>
</html>