<?php
require_once 'config.php';
?>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CertVerify - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    

    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
            --light: #ecf0f1;
            --dark: #34495e;
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }
        
        /* Top Navigation */
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            background: white;
            padding: 0.8rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary);
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        /* Sidebar */
        .sidebar {
            background: var(--primary);
            color: white;
            height: calc(100vh - 70px);
            width: var(--sidebar-width);
            position: fixed;
            top: 70px;
            left: 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 900;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin: 5px 15px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: calc(100vh - 70px);
            transition: all 0.3s ease;
        }
        
        .dashboard-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .page-title {
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--secondary);
            border-radius: 2px;
        }
        
        /* Stats Cards */
        .stat-card {
            text-align: center;
            padding: 25px 15px;
            border-radius: 12px;
            color: white;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-card .number {
            font-size: 2.2rem;
            font-weight: 700;
            margin: 5px 0;
        }
        
        .stat-card .label {
            font-size: 1rem;
            opacity: 0.9;
            letter-spacing: 0.5px;
        }
        
        .widget-primary { background: linear-gradient(135deg, #3498db, #2c3e50); }
        .widget-success { background: linear-gradient(135deg, #27ae60, #219653); }
        .widget-info { background: linear-gradient(135deg, #00cec9, #0984e3); }
        .widget-warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: white;
            padding: 15px 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        /* Table styling */
        .table-container {
            padding: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        table th {
            background-color: #f8f9fa;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 1px solid #dee2e6;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover td {
            background-color: #f8f9fa;
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge-valid { background-color: var(--success); }
        .badge-invalid { background-color: var(--danger); }
        .badge-revoked { background-color: var(--warning); }
        
        /* Buttons */
        .btn-action {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
        }
        
        /* Progress bars */
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-top: 5px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
            }
        }
        
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
    
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>Certificate<span>Verify</span>
            </a>
            
            <button class="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=3498db&color=fff" class="rounded-circle me-2" width="36" height="36">
                            <span>Admin User</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../admin/settings.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <!-- <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li> -->
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage-institutions.php">
                    <i class="fas fa-university"></i> Institutions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage-certificates.php">
                    <i class="fas fa-certificate"></i> Certificates
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage-users.php">
                    <i class="fas fa-users"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-cog"></i> logout
                </a>
            </li>
        </ul>
    </div>
 
    <!-- Main Content -->
    <div class="main-content">


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Animation for stats cards
        document.addEventListener('DOMContentLoaded', function() {
            const stats = document.querySelectorAll('.stat-card');
            stats.forEach((stat, index) => {
                stat.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>