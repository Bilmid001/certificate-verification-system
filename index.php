<?php require_once 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <h1>Welcome to <?php echo SITE_NAME; ?></h1>
            <p class="lead">A secure platform for issuing and verifying academic certificates.</p>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Verify a Certificate</h5>
                    <form action="public/verify.php" method="GET">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="Enter Certificate Number" name="certificate" required>
                            <button class="btn btn-primary" type="submit">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <img src="assets/images/certificate-demo.jpg" alt="Certificate Demo" class="img-fluid rounded">
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>