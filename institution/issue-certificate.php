<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isInstitutionLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentName = trim($_POST['student_name']);
    $courseName = trim($_POST['course_name']);
    $issueDate = trim($_POST['issue_date']);
    $expiryDate = !empty($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
    
    // Validate inputs
    if (empty($studentName) || empty($courseName) || empty($issueDate)) {
        $error = 'Please fill in all required fields.';
    } else {
        global $db;
        
        // Generate certificate number
        $certificateNumber = generateCertificateNumber($_SESSION['institution_id']);
        
        // Generate QR code
        $qrCode = generateQRCode($certificateNumber);
        
        // Handle file upload if present
        $filePath = null;
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/certificates/';
            $fileName = $certificateNumber . '_' . basename($_FILES['certificate_file']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['certificate_file']['tmp_name'], $targetPath)) {
                $filePath = $targetPath;
            }
        }
        
        // Insert certificate into database
        $stmt = $db->prepare("INSERT INTO certificates 
                             (institution_id, certificate_number, student_name, course_name, 
                              issue_date, expiry_date, qr_code, file_path) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $_SESSION['institution_id'], $certificateNumber, 
                         $studentName, $courseName, $issueDate, $expiryDate, $qrCode, $filePath);
        
        if ($stmt->execute()) {
            $success = 'Certificate issued successfully! Certificate Number: ' . $certificateNumber;
        } else {
            $error = 'Error issuing certificate: ' . $db->getConnection()->error;
        }
    }
}

require_once '../includes/insheader.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">Issue New Certificate</h3>
                </div>
                
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="student_name" class="form-label">Student Name *</label>
                            <input type="text" class="form-control" id="student_name" name="student_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="course_name" class="form-label">Course/Program Name *</label>
                            <input type="text" class="form-control" id="course_name" name="course_name" required>
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
                        
                        <button type="submit" class="btn btn-primary">Issue Certificate</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>