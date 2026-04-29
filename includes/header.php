<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi KERATON</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="<?= BASE_URL ?>assets/css/style.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="<?= BASE_URL ?>assets/img/logo atr bpn.png" type="image/x-icon">
    <style>
        #toastContainer {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        }
        .bg-warning-light {
            background-color: #fff3cd !important;
            border: 1px solid #ffc107;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div id="toastContainer"></div>
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>dashboard.php">
                <img src="<?= BASE_URL ?>assets/img/Keraton_1.png" alt="Logo" class="navbar-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>dashboard.php" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>modules/bon/index.php" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'modules/bon') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-file-alt me-1"></i> Data Warkah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>modules/panduan/index.php" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'modules/panduan') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-book me-1"></i> Panduan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>modules/laporan/index.php" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'modules/laporan') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-chart-line me-1"></i> Laporan
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cog me-1"></i> Admin
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/bon/approval-list.php">
                                <i class="fas fa-clipboard-check me-2"></i> Persetujuan Edit
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/users/index.php">
                                <i class="fas fa-users me-2"></i> Manajemen User
                            </a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>modules/audit/index.php">
                                <i class="fas fa-history me-2"></i> Audit Trail
                            </a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?= $_SESSION['nama_lengkap'] ?? 'User' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Content will be inserted here -->