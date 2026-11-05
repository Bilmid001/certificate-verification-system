<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM institutions WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $institution = $result->fetch_assoc();
        
        if (password_verify($password, $institution['password'])) {
            if ($institution['is_active']) {
                $_SESSION['institution_logged_in'] = true;
                $_SESSION['institution_id'] = $institution['id'];
                $_SESSION['institution_name'] = $institution['name'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Your account has been deactivated. Please contact the administrator.";
            }
        }
    }
    
    if (!isset($error)) {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Institution Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin: 0;
            color: white;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .nav-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn-nav {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s, transform 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-nav:hover {
            background-color: #3498db;
            transform: translateY(-2px);
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 15px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            padding: 30px;
        }
        .card-header {
            background: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        .card-header h3 {
            color: #fff;
            margin: 0;
        }
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .logo span {
            color: #3498db;
        }

        .form-label {
            color: #fff;
            font-weight: 500;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 10px;
            padding: 12px 15px;
            color: white;
            width: 100%;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
            color: white;
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
            width: 100%;
            cursor: pointer;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #2c3e50, #3498db);
        }
        .alert-danger {
            background: rgba(231, 76, 60, 0.15);
            border-left: 4px solid #e74c3c;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: #fff;
        }
        .bottom-links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 0.95rem;
        }
        .bottom-links a,
        .mt-3 a {
            color: #3498db;
            text-decoration: underline;
        }
        .bottom-links a:hover,
        .mt-3 a:hover {
            text-decoration: none;
        }
        .mt-3 {
            margin-top: 15px;
        }
        .footer {
            margin-top: 25px;
            font-size: 0.9rem;
            opacity: 0.7;
            text-align: center;
        }

    </style>
</head>
<body>

<div class="login-container">

    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="../admin/login.php" class="btn-nav">Admin Login</a>
        <a href="login.php" class="btn-nav">Institution Login</a>
    </div>

    <!-- Your Existing Institution Login Card (UNCHANGED PHP and HTML below) -->
    <div class="logo">
        <i class="fas fa-user-shield"></i> Institution<span>Login</span>
    </div>
        <div class="card-body">

            <?php if (isset($error)): ?>
                <div class="alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required />
                </div>
                <br>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required />
                </div>
                <br>
                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="mt-3">
                <!-- <a href="../public/verify.php">Verify a certificate instead</a> -->
            </div>
        </div>
    <!-- Footer -->
    <div class="footer">
        &copy; <?php echo date('Y'); ?> Certificate Verification - Institution Portal
    </div>

    </div>

    <!-- Bottom Links -->
    <!-- <div class="bottom-links">
        <a href="search-certificate.php">Click to search certificate</a>
        <a href="verify-certificate.php">Click to verify certificate</a>
    </div> -->
                
</div>

</body>
</html>
