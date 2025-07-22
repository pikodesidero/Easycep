<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Ana Panel";
$userRole = $_SESSION['role'] ?? 'personel';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - <?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Gerekli Kütüphaneler -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="page">
    <!-- KENAR ÇUBUĞU (SIDEBAR) -->
    <aside class="sidebar">
        <div class="sidebar-header"><a href="index.php" class="logo"><span>Piksel</span>Pro</a></div>
       <ul class="nav flex-column">
    <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-desktop fa-fw me-2"></i><span>Ana Panel</span></a></li>
    <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie fa-fw me-2"></i><span>Raporlar</span></a></li>
    <li class="nav-item"><a href="tedarikciler.php" class="nav-link"><i class="fas fa-truck fa-fw me-2"></i><span>Tedarikçiler</span></a></li>
    
    <!-- YENİ EKLENEN LİNK -->
    <li class="nav-item"><a href="giderler.php" class="nav-link"><i class="fas fa-wallet fa-fw me-2"></i><span>Giderler</span></a></li>
    
    <?php if ($userRole === 'admin'): ?>
    <li class="nav-item"><a href="logs.php" class="nav-link"><i class="fas fa-clipboard-list fa-fw me-2"></i><span>Aktivite Kayıtları</span></a></li>
    <?php endif; ?>
</ul>

    </aside>