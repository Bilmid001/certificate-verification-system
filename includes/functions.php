<?php
require_once 'db.php';

function generateCertificateNumber($institutionId) {
    $prefix = "CERT-" . str_pad($institutionId, 4, '0', STR_PAD_LEFT);
    $random = strtoupper(bin2hex(random_bytes(4)));
    return $prefix . "-" . $random;
}

function generateQRCode($certificateNumber) {
    // In a real implementation, use a QR code library
    return "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . 
           urlencode(SITE_URL . "/public/verify.php?certificate=" . $certificateNumber);
}

function verifyCertificate($certificateNumber) {
    global $db;
    
    $stmt = $db->prepare("SELECT c.*, i.name AS institution_name 
                         FROM certificates c 
                         JOIN institutions i ON c.institution_id = i.id 
                         WHERE c.certificate_number = ?");
    $stmt->bind_param("s", $certificateNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $certificate = $result->fetch_assoc();
        
        // Log verification attempt
        $ip = $_SERVER['REMOTE_ADDR'];
        $details = json_encode([
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
            'HTTP_REFERER' => $_SERVER['HTTP_REFERER'] ?? ''
        ]);
        
        $status = $certificate['is_revoked'] ? 'revoked' : 'valid';
        
        $stmt = $db->prepare("INSERT INTO verifications 
                             (certificate_id, verifier_ip, verifier_details, status) 
                             VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $certificate['id'], $ip, $details, $status);
        $stmt->execute();
        
        return [
            'status' => 'success',
            'certificate' => $certificate,
            'is_valid' => !$certificate['is_revoked']
        ];
    }
    
    return ['status' => 'error', 'message' => 'Certificate not found'];
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function isInstitutionLoggedIn() {
    return isset($_SESSION['institution_logged_in']) && $_SESSION['institution_logged_in'] === true;
}
?>