<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Oturum kontrolü
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

// Veritabanı bağlantısı (PDO)
require_once 'config.php'; 

// 1. URL'den fatura ID'sini al ve doğrula
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: faturalar.php");
    exit();
}
$fatura_id = intval($_GET['id']);

// === Veritabanı işlemleri PDO'ya göre güncellendi ===

// 2. Fatura detaylarını çek
$sql = "SELECT 
            id, fatura_no, musteri_adi, fatura_tarihi, vade_tarihi, 
            ara_toplam, kdv_tutari, genel_toplam, durum, notlar
        FROM faturalar
        WHERE id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$fatura_id]); // Parametre doğrudan execute içinde gönderilir

$fatura = $stmt->fetch(PDO::FETCH_ASSOC); // Veri PDO formatında çekilir

if (!$fatura) {
    // Fatura bulunamazsa listeye geri yönlendir.
    header("Location: faturalar.php");
    exit();
}

// 3. Faturaya ait ürün/hizmet kalemlerini çek
$sql_items = "SELECT urun_aciklama, miktar, birim_fiyat, kdv_orani, toplam 
              FROM fatura_kalemleri 
              WHERE fatura_id = ?";
              
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$fatura_id]);
// $items_result değişkenine gerek kalmadı, döngüde doğrudan $stmt_items kullanılacak

$pageTitle = "Fatura Detayı: #" . htmlspecialchars($fatura['fatura_no']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - <?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Yazdırma stilleri */
        @media print {
            body { background-color: #fff; }
            .page > .sidebar, .page > .main-content > .header .user-menu { display: none; }
            .page > .main-content { width: 100%; margin: 0; padding: 0; }
            .card { box-shadow: none; border: 1px solid #dee2e6; }
        }
    </style>
</head>
<body>
<div class="page">
 <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle; ?></h1>
            <div class="user-menu d-flex align-items-center">
                 <a href="faturalar.php" class="btn btn-outline-secondary me-2"><i class="fas fa-arrow-left me-2"></i>Geri Dön</a>
                 <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i>Yazdır</button>
            </div>
        </header>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-6">
                        <h2 class="mb-1">PikselPro</h2>
                        <p class="text-muted">Firma Adresiniz<br>Vergi No: 1234567890</p>
                    </div>
                    <div class="col-6 text-end">
                        <h3 class="mb-1">FATURA</h3>
                        <p class="mb-0"><strong>Fatura No:</strong> <?php echo htmlspecialchars($fatura['fatura_no']); ?></p>
                        <p>
                            <strong>Durum:</strong>
                            <?php
                                $durum = htmlspecialchars($fatura['durum'] ?? 'Belirsiz');
                                $badge_class = 'bg-secondary';
                                if ($durum == 'Ödendi') $badge_class = 'bg-success';
                                elseif ($durum == 'Gecikti') $badge_class = 'bg-danger';
                                elseif ($durum == 'Bekliyor') $badge_class = 'bg-warning';
                                echo "<span class='badge $badge_class'>$durum</span>";
                            ?>
                        </p>
                    </div>
                </div>

                <div class="row bg-light p-3 rounded mb-4">
                    <div class="col-md-6">
                        <strong>MÜŞTERİ BİLGİLERİ</strong>
                        <p class="mb-1 mt-2">
                            <strong><?php echo htmlspecialchars($fatura['musteri_adi']); ?></strong>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong>FATURA TARİHLERİ</strong>
                        <p class="mb-1 mt-2">
                            <strong>Fatura Tarihi:</strong> <?php echo date('d.m.Y', strtotime($fatura['fatura_tarihi'])); ?><br>
                            <strong>Vade Tarihi:</strong> <?php echo date('d.m.Y', strtotime($fatura['vade_tarihi'])); ?>
                        </p>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width:5%;">#</th>
                            <th scope="col">Ürün / Açıklama</th>
                            <th scope="col" class="text-center" style="width:10%;">Miktar</th>
                            <th scope="col" class="text-end" style="width:15%;">Birim Fiyat</th>
                            <th scope="col" class="text-center" style="width:10%;">KDV</th>
                            <th scope="col" class="text-end" style="width:15%;">Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while($item = $stmt_items->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <th scope="row"><?php echo $i++; ?></th>
                            <td><?php echo htmlspecialchars($item['urun_aciklama']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($item['miktar']); ?></td>
                            <td class="text-end"><?php echo number_format($item['birim_fiyat'], 2, ',', '.'); ?> ₺</td>
                            <td class="text-center"><?php echo htmlspecialchars($item['kdv_orani']); ?>%</td>
                            <td class="text-end"><?php echo number_format($item['toplam'], 2, ',', '.'); ?> ₺</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <?php if(!empty($fatura['notlar'])): ?>
                            <strong>Notlar:</strong>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($fatura['notlar'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless text-end">
                            <tbody>
                                <tr>
                                    <td><strong>Ara Toplam:</strong></td>
                                    <td><?php echo number_format($fatura['ara_toplam'], 2, ',', '.'); ?> ₺</td>