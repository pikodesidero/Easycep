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
    <?php include 'sidebar.php'; ?>


    <!-- ANA İÇERİK -->
    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="user-menu d-flex align-items-center">
                <div class="form-check form-switch me-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="darkModeToggle">
                    <label class="form-check-label" for="darkModeToggle"><i class="fas fa-moon"></i></label>
                </div>
                <span class="me-3 d-none d-sm-inline">Hoş geldin, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>!</span>
                <button class="btn btn-primary" id="add-new-record-btn"><i class="fas fa-plus me-2"></i>Yeni Kayıt</button>
                <a href="api.php?action=logout" class="btn btn-danger ms-2" title="Çıkış Yap"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card shadow-sm h-100"><div class="card-header"><h6 class="m-0 font-weight-bold">Aylık Kar Analizi</h6></div><div class="card-body"><canvas id="profitChart"></canvas></div></div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-12 mb-4"><div class="summary-card card-primary"><div class="card-body"><h5 class="card-title" id="total-records">0</h5><p class="card-text">Toplam Kayıt (Filtreli)</p><i class="fas fa-archive"></i></div></div></div>
                    <div class="col-12 mb-4"><div class="summary-card card-success"><div class="card-body"><h5 class="card-title" id="sold-receivable">0,00 ₺</h5><p class="card-text">Satılan Alacak</p><i class="fas fa-hand-holding-usd"></i></div></div></div>
                    <div class="col-12"><div class="summary-card card-info"><div class="card-body"><h5 class="card-title" id="paid-receivable">0,00 ₺</h5><p class="card-text">Ödenen Alacak</p><i class="fas fa-check-double"></i></div></div></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                 <h6 class="m-0 font-weight-bold">Filtreleme</h6>
            </div>
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3"><label for="date_type_filter" class="form-label">Tarih Türü</label><select id="date_type_filter" name="date_type" class="form-select"><option value="tarih" selected>Alış Tarihi</option><option value="starihi">Satış Tarihi</option><option value="otarihi">Ödeme Tarihi</option></select></div>
                    <div class="col-md-3"><label for="start_date" class="form-label">Başlangıç Tarihi</label><input type="date" id="start_date" name="start_date" class="form-control"></div>
                    <div class="col-md-3"><label for="end_date" class="form-label">Bitiş Tarihi</label><input type="date" id="end_date" name="end_date" class="form-control"></div>
                    <div class="col-md-3"><label for="imei_filter" class="form-label">IMEI</label><input type="text" id="imei_filter" name="imei" class="form-control" placeholder="IMEI ile ara..."></div>
                    <div class="col-md-3"><label for="durum_filter" class="form-label">Durum</label><select id="durum_filter" name="durum" class="form-select"><option value="">Tümü</option><option value="Kargo">Kargo</option><option value="Yenilenecek">Yenilenecek</option><option value="Tamir">Tamir</option><option value="Satıldı">Satıldı</option><option value="Ödendi">Ödendi</option><option value="İade">İade</option><option value="M1">M1</option><option value="Optimum">Optimum</option></select></div>
                    <div class="col-md-3"><label for="alan_filter" class="form-label">Alan Personel</label><input type="text" id="alan_filter" name="alanpersonel" class="form-control" placeholder="Alan personel..."></div>
                    <div class="col-md-3"><label for="satan_filter" class="form-label">Satan Personel</label><input type="text" id="satan_filter" name="satanpersonel" class="form-control" placeholder="Satan personel..."></div>
                    <div class="col-md-3 d-flex align-items-end"><button type="reset" class="btn btn-secondary w-100" id="reset-filter"><i class="fas fa-times me-2"></i>Temizle</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="mb-3 d-flex gap-2">
                    <?php if ($userRole === 'admin'): ?>
                        <button class="btn btn-danger" id="bulk-delete-btn" disabled><i class="fas fa-trash-alt me-2"></i>Seçilenleri Sil</button>
                        <div class="dropdown">
                            <button class="btn btn-info dropdown-toggle" type="button" id="bulk-status-btn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                <i class="fas fa-sync-alt me-2"></i>Durum Değiştir
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="bulk-status-btn">
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Satıldı">Satıldı</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Ödendi">Ödendi</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Kargo">Kargo</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Tamir">Tamir</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Yenilenecek">Yenilenecek</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="İade">İade</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="M1">M1</a></li>
                                <li><a class="dropdown-item bulk-status-update" href="#" data-status="Optimum">Optimum</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
                <table id="buybackTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-checkbox"></th>
                            <th>ID</th><th>Alış Tarihi</th><th>Cari</th><th>Marka/Model</th>
                            <th>IMEI</th><th>Maliyet</th><th>Kar</th><th>Kalan Alacak</th><th>Durum</th><th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Modallar -->
<?php include 'modals.php'; ?>

<!-- Gerekli JavaScript Kütüphaneleri -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
