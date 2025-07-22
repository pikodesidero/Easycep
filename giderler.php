<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Gider Yönetimi";
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
    <aside class="sidebar">
        <div class="sidebar-header"><a href="index.php" class="logo"><span>Piksel</span>Pro</a></div>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-desktop fa-fw me-2"></i><span>Ana Panel</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie fa-fw me-2"></i><span>Raporlar</span></a></li>
            <li class="nav-item"><a href="tedarikciler.php" class="nav-link"><i class="fas fa-truck fa-fw me-2"></i><span>Tedarikçiler</span></a></li>
            <!-- YENİ EKLENDİ -->
            <li class="nav-item"><a href="giderler.php" class="nav-link active"><i class="fas fa-wallet fa-fw me-2"></i><span>Giderler</span></a></li>
            <?php if ($userRole === 'admin'): ?>
            <li class="nav-item"><a href="logs.php" class="nav-link"><i class="fas fa-clipboard-list fa-fw me-2"></i><span>Aktivite Kayıtları</span></a></li>
            <?php endif; ?>
        </ul>
    </aside>

    <!-- ANA İÇERİK -->
    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="user-menu d-flex align-items-center">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal"><i class="fas fa-plus me-2"></i>Yeni Gider Ekle</button>
            </div>
        </header>

        <!-- Giderler Tablosu -->
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="m-0 font-weight-bold">Gider Listesi</h6></div>
            <div class="card-body">
                <table id="expensesTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th><th>Tarih</th><th>Açıklama</th><th>Kategori</th><th>Ödeme Yöntemi</th><th>Tutar</th><th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Yeni Gider Ekleme Modalı -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addExpenseModalLabel">Yeni Gider Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="add-expense-form">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label for="gider_aciklama" class="form-label">Açıklama</label><input type="text" id="gider_aciklama" name="aciklama" class="form-control" required></div>
                        <div class="col-md-6"><label for="gider_kategori" class="form-label">Kategori</label><select id="gider_kategori" name="kategori" class="form-select" required><option value="">Seçiniz...</option><option>Kira</option><option>Fatura</option><option>Maaş</option><option>Reklam</option><option>Yemek</option><option>Ulaşım</option><option>Diğer</option></select></div>
                        <div class="col-md-4"><label for="gider_tutar" class="form-label">Tutar (₺)</label><input type="number" step="0.01" id="gider_tutar" name="tutar" class="form-control" required></div>
                        <div class="col-md-4"><label for="gider_odeme_yontemi" class="form-label">Ödeme Yöntemi</label><select id="gider_odeme_yontemi" name="odeme_yontemi" class="form-select"><option>Nakit Kasa</option><option>Banka Hesabı</option><option>Kredi Kartı</option></select></div>
                        <div class="col-md-4"><label for="gider_tarih" class="form-label">Tarih</label><input type="date" id="gider_tarih" name="tarih" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Gideri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
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
    function currencyFormatter(data) {
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0);
    }
    
    function showToast(title, message, icon = 'success') {
        Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' });
    }

    const expensesTable = $('#expensesTable').DataTable({
        processing: true,
        ajax: { url: 'api.php?action=fetch_expenses', dataSrc: 'data' },
        columns: [
            { data: 'id' }, { data: 'tarih' }, { data: 'aciklama' }, { data: 'kategori' },
            { data: 'odeme_yontemi' }, { data: 'tutar', render: currencyFormatter },
            { data: 'id', orderable: false, searchable: false, render: function(data) {
                return `<button class="btn btn-outline-danger btn-sm delete-expense-btn" data-id="${data}" title="Sil"><i class="fas fa-trash"></i></button>`;
            }}
        ],
        order: [[1, 'desc']],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" + "<'row'<'col-sm-12 mt-2'B>>",
        buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
    });

    const addExpenseModal = new bootstrap.Modal(document.getElementById('addExpenseModal'));

    $('#add-expense-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=add_expense';
        $.post('api.php', formData, function(response) {
            if (response.status === 'success') {
                addExpenseModal.hide();
                $('#add-expense-form')[0].reset();
                expensesTable.ajax.reload();
                showToast('Başarılı', response.message);
            } else {
                showToast('Hata', response.message, 'error');
            }
        }, 'json').fail(() => showToast('Hata', 'İşlem sırasında bir hata oluştu.', 'error'));
    });
    
    $('#expensesTable tbody').on('click', '.delete-expense-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?', text: `ID:${id} nolu gider kaydı kalıcı olarak silinecektir!`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Evet, sil!', cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api.php', { action: 'delete_expense', id: id }, (response) => {
                    if (response.status === 'success') {
                        expensesTable.ajax.reload();
                        showToast('Başarılı', response.message);
                    } else { showToast('Hata', response.message, 'error'); }
                }, 'json');
            }
        });
    });

    // Dark Mode (Diğer sayfalarla tutarlılık için)
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
