<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title><?= isset($title) ? $title : 'Dashboard Pajak'; ?></title>
    
    <!-- Pastikan file CSS SB Admin Pro ada di folder css/styles.css -->
    <link href="css/styles.css" rel="stylesheet" />
    
    <!-- Font Awesome & Feather Icons -->
    <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
</head>
<body class="nav-fixed">
    <!-- Top Navigation -->
    <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
        <!-- Tombol Toggle Sidebar -->
        <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
            <i data-feather="menu"></i>
        </button>
        <!-- Logo / Nama Brand -->
        <a class="navbar-brand pe-3 ps-4 ps-lg-2 text-primary fw-bold" href="index.php">
            <i data-feather="layers"></i> YASPA
        </a>
    </nav>