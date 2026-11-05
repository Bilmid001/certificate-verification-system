<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Update last login
            $stmt = $db->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->bind_param("i", $admin['id']);
            $stmt->execute();
            
            header("Location: dashboard.php");
            exit();
        }
    }
    
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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
            margin-bottom: 6px;
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

        .btn-login {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
            width: 100%;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(135deg, #2c3e50, #3498db);
        }

        .alert {
            background: rgba(231, 76, 60, 0.15);
            border-left: 4px solid #e74c3c;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: #fff;
        }

        .footer {
            margin-top: 25px;
            font-size: 0.9rem;
            opacity: 0.7;
            text-align: center;
        }

        /* Navigation Buttons */
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

        /* Bottom Links */
        .bottom-links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 0.95rem;
        }

        .bottom-links a {
            color: #3498db;
            text-decoration: underline;
        }

        .bottom-links a:hover {
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-container">

    <!-- Navigation Buttons -->
    <div class="nav-buttons">
        <a href="login.php" class="btn-nav">Admin Login</a>
        <a href="../institution/login.php" class="btn-nav">Institution Login</a>
    </div>

    <!-- Logo -->
    <div class="logo">
        <i class="fas fa-user-shield"></i> Admin<span>Login</span>
    </div>

    <!-- <h3 class="text-center mb-4">Welcome Back, Admin</h3> -->

    <?php if (isset($error)): ?>
        <div class="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt me-2"></i> Login
        </button>
    </form>

    <!-- Bottom Links -->
    <!-- <div class="bottom-links">
        <a href="../public/search.php">Click to search certificate</a>
        <a href="../public/verify.php">Click to verify certificate</a>
    </div> -->

    <!-- Footer -->
    <div class="footer">
        &copy; <?php echo date('Y'); ?> Certificate Verification - Admin Portal
    </div>

</div>

</body>
</html>
