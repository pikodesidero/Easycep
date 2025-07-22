<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Tedarikçi Yönetimi";
$userRole = $_SESSION['role'] ?? 'personel';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - <?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Gerekli Kütüphaneler (Projenizle Aynı) -->
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

        <!-- Filtreleme ve Özet Kartları -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header"><h6 class="m-0 font-weight-bold">Tedarikçi Seçimi</h6></div>
                    <div class="card-body">
                        <label for="supplier-select" class="form-label">Raporlamak için bir tedarikçi seçin:</label>
                        <select id="supplier-select" class="form-select"></select>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card card-warning h-100">
                    <div class="card-body">
                        <h5 class="card-title" id="total-supplier-devices">0</h5>
                        <p class="card-text">Toplam Alınan Cihaz</p>
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card card-info h-100">
                    <div class="card-body">
                        <h5 class="card-title" id="total-supplier-cost">0,00 ₺</h5>
                        <p class="card-text">Toplam Alım Tutarı</p>
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tedarikçi Detay Tablosu -->
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="m-0 font-weight-bold" id="supplier-table-title">Lütfen Bir Tedarikçi Seçin</h6></div>
            <div class="card-body">
                <table id="supplierDetailsTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th><th>Alış Tarihi</th><th>Marka/Model</th><th>IMEI</th><th>Maliyet</th><th>Durum</th>
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
    let supplierTable;

    function currencyFormatter(data) {
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0);
    }

    // Tedarikçi listesini çek ve dropdown'ı doldur
    $.get('api.php?action=fetch_suppliers', function(response) {
        if (response.status === 'success') {
            const select = $('#supplier-select');
            select.append('<option value="">Seçiniz...</option>');
            response.data.forEach(supplier => {
                select.append(`<option value="${supplier.tedarikci}">${supplier.tedarikci}</option>`);
            });
        }
    }, 'json');

    // Dropdown değiştiğinde raporu güncelle
    $('#supplier-select').on('change', function() {
        const supplierName = $(this).val();
        if (supplierName) {
            $('#supplier-table-title').text(supplierName + ' - Alınan Ürünler');
            initializeSupplierTable(supplierName);
        } else {
            $('#supplier-table-title').text('Lütfen Bir Tedarikçi Seçin');
            if (supplierTable) {
                supplierTable.clear().draw();
            }
            $('#total-supplier-devices').text('0');
            $('#total-supplier-cost').text('0,00 ₺');
        }
    });

    function initializeSupplierTable(supplierName) {
        const apiUrl = `api.php?action=fetch_supplier_details&tedarikci=${encodeURIComponent(supplierName)}`;

        if ($.fn.DataTable.isDataTable('#supplierDetailsTable')) {
            supplierTable.ajax.url(apiUrl).load(updateSummary);
            return;
        }

        supplierTable = $('#supplierDetailsTable').DataTable({
            processing: true,
            ajax: { 
                url: apiUrl, 
                dataSrc: 'details',
                dataFilter: function(data) {
                    // API'den gelen tam veriyi alıp özet kartlarını güncelle
                    var json = jQuery.parseJSON(data);
                    updateSummary(json.summary);
                    return JSON.stringify(json);
                }
            },
            columns: [
                { data: 'id' },
                { data: 'tarih' },
                { data: 'marka_model' },
                { data: 'imei' },
                { data: 'maliyet', render: currencyFormatter },
                { data: 'durum' }
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
    
    function updateSummary(summary) {
        if(summary) {
            $('#total-supplier-devices').text(summary.total_devices || 0);
            $('#total-supplier-cost').text(currencyFormatter(summary.total_cost));
        }
    }

    // Dark Mode
    const darkModeToggle = $('#darkModeToggle');
    if (localStorage.getItem('darkMode') === 'enabled') {
        $('body').addClass('dark-mode');
        darkModeToggle.prop('checked', true);
    }
    darkModeToggle.on('change', function() {
        $('body').toggleClass('dark-mode', this.checked);
        localStorage.setItem('darkMode', this.checked ? 'enabled' : 'disabled');
    });
});
</script>
</body>
</html>
