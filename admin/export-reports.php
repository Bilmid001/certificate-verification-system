<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isAdminLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Get parameters
$type = isset($_GET['type']) ? $_GET['type'] : 'csv';
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

// Export based on type
switch ($type) {
    case 'csv':
        exportCSV($verifications, $startDate, $endDate);
        break;
    case 'excel':
        exportExcel($verifications, $startDate, $endDate);
        break;
    case 'pdf':
        exportPDF($verifications, $startDate, $endDate);
        break;
    default:
        die("Invalid export type");
}

function exportCSV($data, $startDate, $endDate) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="verification_reports_' . $startDate . '_to_' . $endDate . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Header
    fputcsv($output, array('Verification Date', 'IP Address', 'Status', 'Certificate Number', 'Student Name', 'Course', 'Institution'));
    
    // Data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

function exportExcel($data, $startDate, $endDate) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="verification_reports_' . $startDate . '_to_' . $endDate . '.xls"');
    
    echo '<table border="1">';
    echo '<tr><th>Verification Date</th><th>IP Address</th><th>Status</th><th>Certificate Number</th><th>Student Name</th><th>Course</th><th>Institution</th></tr>';
    
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $row['verification_date'] . '</td>';
        echo '<td>' . $row['verifier_ip'] . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>' . $row['certificate_number'] . '</td>';
        echo '<td>' . $row['student_name'] . '</td>';
        echo '<td>' . $row['course_name'] . '</td>';
        echo '<td>' . $row['institution_name'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    exit();
}

function exportPDF($data, $startDate, $endDate) {
    // We'll use a simple PDF generation with a library like FPDF in a real app
    // For this example, we'll just show a message and provide a link to a PDF generation tutorial
    // In practice, you would install FPDF or TCPDF and generate the PDF
    
    // For now, let's just show a message
    die("PDF export is not implemented in this example. Please install FPDF or TCPDF to generate PDF files.");
}
?>