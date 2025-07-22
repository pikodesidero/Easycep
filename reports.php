<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Personel Performans Raporları";
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
                <a href="api.php?action=logout" class="btn btn-danger ms-2" title="Çıkış Yap"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="card shadow-sm">
            <div class="card-header">
                 <h6 class="m-0 font-weight-bold">Raporlama Kriterleri</h6>
            </div>
            <div class="card-body">
                <form id="report-filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4"><label for="report_start_date" class="form-label">Başlangıç Tarihi</label><input type="date" id="report_start_date" name="start_date" class="form-control"></div>
                    <div class="col-md-4"><label for="report_end_date" class="form-label">Bitiş Tarihi</label><input type="date" id="report_end_date" name="end_date" class="form-control"></div>
                    <div class="col-md-4 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-cogs me-2"></i>Raporu Oluştur</button></div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <table id="reportsTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Personel</th>
                            <th>Toplam Alım Adedi</th>
                            <th>Toplam Alım Maliyeti</th>
                            <th>Toplam Satış Adedi</th>
                            <th>Toplam Satış Karı</th>
                            <th>Hak Edilen Alış Primi</th>
                            <th>Hak Edilen Satış Primi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

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
<script>
$(document).ready(function() {
    let reportsTable;

    function currencyFormatter(data) {
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0);
    }

    function initializeReportsTable(filters = {}) {
        const queryParams = new URLSearchParams(filters).toString();
        const apiUrl = `api.php?action=fetch_personnel_report&${queryParams}`;

        if ($.fn.DataTable.isDataTable('#reportsTable')) {
            reportsTable.ajax.url(apiUrl).load();
            return;
        }

        reportsTable = $('#reportsTable').DataTable({
            processing: true,
            ajax: { url: apiUrl, dataSrc: 'data', error: (xhr) => console.error("Rapor AJAX Hatası:", xhr.responseText) },
            columns: [
                { data: 'personel' },
                { data: 'toplam_alim_adedi' },
                { data: 'toplam_alim_maliyeti', render: currencyFormatter },
                { data: 'toplam_satis_adedi' },
                { data: 'toplam_satis_kari', render: currencyFormatter },
                { data: 'toplam_alis_primi', render: currencyFormatter },
                { data: 'toplam_satis_primi', render: currencyFormatter }
            ],
            dom: "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 mt-2'B>>",
            buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' },
            responsive: true,
            paging: false,
            searching: false,
            info: false
        });
    }

    $('#report-filter-form').on('submit', function(e) {
        e.preventDefault();
        const filters = Object.fromEntries(new URLSearchParams($(this).serialize()));
        initializeReportsTable(filters);
    });

    // İlk yüklemede son 30 günün raporunu getir
    const today = new Date();
    const thirtyDaysAgo = new Date(new Date().setDate(today.getDate() - 30));
    $('#report_start_date').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#report_end_date').val(today.toISOString().split('T')[0]);
    $('#report-filter-form').trigger('submit');

    // Dark Mode
    $('#darkModeToggle').on('change', function() {
        $('body').toggleClass('dark-mode', this.checked);
        localStorage.setItem('darkMode', this.checked ? 'enabled' : 'disabled');
    });
    if (localStorage.getItem('darkMode') === 'enabled') {
        $('#darkModeToggle').prop('checked', true).trigger('change');
    }
});
</script>
</body>
</html>
