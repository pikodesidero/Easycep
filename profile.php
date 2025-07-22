<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$pageTitle = "Personel Profili";
$userRole = $_SESSION['role'] ?? 'personel';
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - <?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page">
    <!-- KENAR ÇUBUĞU -->
   <?php include 'sidebar.php'; ?>
    <!-- ANA İÇERİK -->
    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800" id="profile-page-title">Profilim</h1>
            <div class="user-menu d-flex align-items-center">
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#leaveRequestModal"><i class="fas fa-plane-departure me-2"></i>İzin Talebi</button>
                <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#advanceRequestModal"><i class="fas fa-hand-holding-usd me-2"></i>Avans Talebi</button>
                <a href="api.php?action=logout" class="btn btn-danger ms-2" title="Çıkış Yap"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <!-- Profil Kartı ve KPI'lar -->
        <div class="row mb-4">
            <div class="col-lg-4">
                <div class="card profile-card text-center h-100">
                    <div class="card-body">
                        <img src="https://placehold.co/100x100/6c5ffc/FFFFFF?text=<?php echo substr($_SESSION['name'], 0, 1); ?>" class="rounded-circle mb-3" alt="Profil Resmi">
                        <h4 class="card-title mb-0"><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                        <p class="text-muted" id="user-position">Yükleniyor...</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="row">
                    <div class="col-md-4 mb-3"><div class="summary-card card-primary h-100"><div class="card-body"><h5 class="card-title" id="kpi-hak-edilen">0,00 ₺</h5><p class="card-text">Hak Edilen Prim</p><i class="fas fa-award"></i></div></div></div>
                    <div class="col-md-4 mb-3"><div class="summary-card card-danger h-100"><div class="card-body"><h5 class="card-title" id="kpi-odenen">0,00 ₺</h5><p class="card-text">Ödenen Prim</p><i class="fas fa-money-check-alt"></i></div></div></div>
                    <div class="col-md-4 mb-3"><div class="summary-card card-success h-100"><div class="card-body"><h5 class="card-title" id="kpi-kalan">0,00 ₺</h5><p class="card-text">Kalan Prim</p><i class="fas fa-wallet"></i></div></div></div>
                </div>
            </div>
        </div>

        <!-- Detay Sekmeleri -->
        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" data-bs-toggle="tab" href="#performans" role="tab">Performans</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#maas" role="tab">Maaş & Avans</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link" data-bs-toggle="tab" href="#izin" role="tab">İzin Geçmişi</a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content p-2" id="profileTabContent">
                    <div class="tab-pane fade show active" id="performans" role="tabpanel">
                        <h5>Satış ve Alım Geçmişi</h5>
                        <table id="performanceTable" class="table table-sm" style="width:100%"></table>
                    </div>
                    <div class="tab-pane fade" id="maas" role="tabpanel">
                        <h5>Avans Geçmişi</h5>
                        <table id="avansTable" class="table table-sm" style="width:100%"></table>
                    </div>
                    <div class="tab-pane fade" id="izin" role="tabpanel">
                        <h5>İzin Talepleri Geçmişi</h5>
                        <table id="izinTable" class="table table-sm" style="width:100%"></table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modallar -->
<?php include 'modals.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function() {
    function currencyFormatter(data) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0); }
    function showToast(title, message, icon = 'success') { Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' }); }

    let performanceTable, avansTable, izinTable;

    async function loadProfileData() {
        try {
            const response = await fetch('api.php?action=fetch_my_profile_data');
            const result = await response.json();
            if (result.status === 'success') {
                const data = result.data;
                // KPI Kartlarını Doldur
                $('#user-position').text(data.user.pozisyon || 'Personel');
                $('#kpi-hak-edilen').text(currencyFormatter(data.kpi.toplam_hak_edilen));
                $('#kpi-odenen').text(currencyFormatter(data.kpi.toplam_odenen));
                $('#kpi-kalan').text(currencyFormatter(data.kpi.kalan_prim));

                // Tabloları Doldur
                renderTable('#performanceTable', performanceTable, data.performance, [
                    { title: 'İşlem', data: 'type' }, { title: 'Tarih', data: 'tarih' },
                    { title: 'Cihaz', data: 'marka_model' }, { title: 'Prim', data: 'prim', render: currencyFormatter }
                ]);
                renderTable('#avansTable', avansTable, data.avanslar, [
                    { title: 'Talep Tarihi', data: 'talep_tarihi' }, { title: 'Tutar', data: 'tutar', render: currencyFormatter },
                    { title: 'Açıklama', data: 'aciklama' }, { title: 'Durum', data: 'durum' }
                ]);
                renderTable('#izinTable', izinTable, data.izinler, [
                    { title: 'Tür', data: 'izin_turu' }, { title: 'Başlangıç', data: 'baslangic_tarihi' },
                    { title: 'Bitiş', data: 'bitis_tarihi' }, { title: 'Gün', data: 'gun_sayisi' }, { title: 'Durum', data: 'durum' }
                ]);
            } else {
                showToast('Hata', result.message, 'error');
            }
        } catch (error) { console.error("Profil verileri alınamadı:", error); }
    }

    function renderTable(selector, tableInstance, data, columns) {
        if ($.fn.DataTable.isDataTable(selector)) {
            tableInstance.clear().rows.add(data).draw();
        } else {
            tableInstance = $(selector).DataTable({
                data: data, columns: columns,
                paging: false, searching: false, info: false, ordering: false,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
            });
        }
    }
    
    // Form Gönderme
    $('#leaveRequestForm, #advanceRequestForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const action = form.attr('id') === 'leaveRequestForm' ? 'request_leave' : 'request_advance';
        const formData = form.serialize() + '&action=' + action;
        const modal = bootstrap.Modal.getInstance(form.closest('.modal'));

        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                modal.hide(); form[0].reset();
                showToast('Başarılı', response.message);
                loadProfileData(); // Verileri yenile
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    loadProfileData();
});
</script>
</body>
</html>
