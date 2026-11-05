<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$result = null;

if (isset($_GET['certificate'])) {
    $certificateNumber = trim($_GET['certificate']);
    $result = verifyCertificate($certificateNumber);
}
// require_once '../includes/insheader.php';
?>

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

    .verification-card {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        border-radius: 15px;
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

    .table tr {
        transition: background-color 0.3s ease;
    }

    .table tr:hover {
        background-color: #f2f2f2;
    }

    .qr-code-img {
        max-width: 200px;
        padding: 10px;
        border-radius: 10px;
        border: 2px dashed #0d6efd;
        background: #ffffff;
        animation: popIn 0.6s ease-in-out;
    }

    @keyframes popIn {
        from {
            transform: scale(0.8);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .spinner-border {
        display: none;
        width: 1.5rem;
        height: 1.5rem;
    }
</style>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card verification-card">
                <div class="card-header text-center">
                    <h3 class="mb-0">Certificate Verification</h3>
                </div>
                <div class="card-body p-4">
                    <form method="GET" class="mb-4" onsubmit="showLoader()">
                        <div class="input-group input-group-lg">
                            <input type="text" class="form-control" name="certificate"
                                placeholder="Enter Certificate Number"
                                value="<?php echo $_GET['certificate'] ?? ''; ?>" required>
                            <button type="submit" class="btn btn-primary px-4">
                                <span class="spinner-border spinner-border-sm me-2" id="loadingSpinner"></span>
                                Verify
                            </button>
                        </div>
                    </form>

                    <?php if ($result): ?>
                        <div class="verification-result mt-4">
                            <?php if ($result['status'] === 'success'): ?>
                                <div class="alert alert-<?php echo $result['is_valid'] ? 'success' : 'danger'; ?> text-center mb-4 animate__animated animate__fadeIn">
                                    <h4 class="alert-heading mb-2">
                                        <?php echo $result['is_valid'] ? '✅ Valid Certificate' : '❌ Invalid Certificate'; ?>
                                    </h4>
                                    <p class="mb-0">
                                        <?php echo $result['is_valid']
                                            ? 'This certificate is authentic and verified.'
                                            : 'This certificate has been revoked by the institution.'; ?>
                                    </p>
                                </div>

                                <div class="certificate-details animate__animated animate__fadeInUp">
                                    <h5 class="text-primary mb-3">📄 Certificate Details</h5>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>Certificate Number</th>
                                            <td>
                                                <?php echo htmlspecialchars($result['certificate']['certificate_number']); ?>
                                                <span class="badge bg-<?php echo $result['is_valid'] ? 'success' : 'danger'; ?> ms-2">
                                                    <?php echo $result['is_valid'] ? 'VALID' : 'REVOKED'; ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Student Name</th>
                                            <td><?php echo htmlspecialchars($result['certificate']['student_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Course Name</th>
                                            <td><?php echo htmlspecialchars($result['certificate']['course_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Institution</th>
                                            <td><?php echo htmlspecialchars($result['certificate']['institution_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Issue Date</th>
                                            <td><?php echo date('F j, Y', strtotime($result['certificate']['issue_date'])); ?></td>
                                        </tr>
                                    </table>

                                    <?php if (!empty($result['certificate']['qr_code'])): ?>
                                        <div class="text-center mt-4">
                                            <img src="<?php echo $result['certificate']['qr_code']; ?>" alt="QR Code" class="qr-code-img">
                                            <p class="text-muted mt-2">Scan QR code to verify</p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            <?php else: ?>
                                <div class="alert alert-danger text-center animate__animated animate__shakeX">
                                    <h4 class="alert-heading">⚠️ Certificate Not Found</h4>
                                    <p>The certificate number you entered was not found in our database.</p>
                                    <p class="text-muted">Please double-check the number or contact the institution for help.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showLoader() {
        document.getElementById('loadingSpinner').style.display = 'inline-block';
    }
</script>
