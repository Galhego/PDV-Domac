<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seu Coxinha - PDV</title>
    <script src="https://cdn.tailwindcss.com "></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css ">
    <style>
        .sidebar {
            transition: all 0.3s;
        }
        .sidebar.collapsed {
            width: 70px;
        }
        .sidebar.collapsed .nav-text {
            display: none;
        }
        .sidebar.collapsed .logo-text {
            display: none;
        }
        .sidebar.collapsed .nav-item {
            justify-content: center;
        }
        .main-content {
            transition: all 0.3s;
        }
        .main-content.expanded {
            margin-left: 70px;
        }
        .active-nav {
            background-color: #fbbf24;
            color: #991b1b !important;
        }
        .active-nav i {
            color: #991b1b !important;
        }
        .table-row-hover:hover {
            background-color: #fef2f2;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php 
    // Define activePage se não estiver definido
    $activePage = $activePage ?? 'dashboard';
    include 'sidebar.php'; 
    ?>
    
    <!-- Main Content -->
    <div class="main-content ml-64 p-6" id="main-content">