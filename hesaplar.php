<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Kasa ve Banka Hesapları";
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
                <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal" data-type="Gelir"><i class="fas fa-plus me-2"></i>Gelir Ekle</button>
                <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal" data-type="Gider"><i class="fas fa-minus me-2"></i>Gider Ekle</button>
                <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#transferModal"><i class="fas fa-exchange-alt me-2"></i>Transfer Yap</button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal"><i class="fas fa-university me-2"></i>Yeni Hesap</button>
            </div>
        </header>

        <!-- Hesap Özet Kartları -->
        <div class="row mb-4" id="accounts-summary-cards">
            <!-- Bu alan JavaScript ile doldurulacak -->
        </div>

        <!-- Son Hesap Hareketleri Tablosu -->
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="m-0 font-weight-bold">Son Hesap Hareketleri</h6></div>
            <div class="card-body">
                <table id="transactionsTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th><th>Tarih</th><th>Açıklama</th><th>İşlem Türü</th><th>Kaynak Hesap</th><th>Hedef Hesap</th><th>Tutar</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let transactionsTable;

    function currencyFormatter(data) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0); }
    function showToast(title, message, icon = 'success') { Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' }); }

    function loadPageData() {
        loadAccounts();
        if ($.fn.DataTable.isDataTable('#transactionsTable')) {
            transactionsTable.ajax.reload();
        } else {
            initializeTransactionsTable();
        }
    }

    async function loadAccounts() {
        try {
            const response = await fetch('api.php?action=fetch_accounts');
            const result = await response.json();
            if (result.status === 'success') {
                const summaryContainer = $('#accounts-summary-cards');
                const accountSelects = $('.account-select');
                summaryContainer.empty();
                accountSelects.empty().append('<option value="">Seçiniz...</option>');
                
                let totalBalance = 0;
                result.data.forEach(acc => {
                    totalBalance += parseFloat(acc.bakiye);
                    const cardColor = acc.hesap_turu === 'Nakit Kasa' ? 'bg-success' : 'bg-primary';
                    const icon = acc.hesap_turu === 'Nakit Kasa' ? 'fa-wallet' : 'fa-university';
                    const cardHtml = `
                        <div class="col-md-4 mb-4">
                            <div class="card text-white ${cardColor} h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="card-title">${acc.hesap_adi}</h6>
                                        <i class="fas ${icon} fa-2x opacity-50"></i>
                                    </div>
                                    <h4 class="display-6 fw-bold">${currencyFormatter(acc.bakiye)}</h4>
                                    <small>${acc.hesap_turu}</small>
                                </div>
                            </div>
                        </div>`;
                    summaryContainer.append(cardHtml);
                    accountSelects.append(`<option value="${acc.id}">${acc.hesap_adi}</option>`);
                });
            }
        } catch (error) { console.error("Hesaplar yüklenemedi:", error); }
    }

    function initializeTransactionsTable() {
        transactionsTable = $('#transactionsTable').DataTable({
            processing: true,
            ajax: { url: 'api.php?action=fetch_transactions', dataSrc: 'data' },
            columns: [
                { data: 'id' }, { data: 'tarih' }, { data: 'aciklama' },
                { data: 'islem_turu' }, { data: 'kaynak_hesap_adi' }, { data: 'hedef_hesap_adi' },
                { data: 'tutar', render: currencyFormatter }
            ],
            order: [[0, 'desc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
        });
    }

    // Modal ve Form İşlemleri
    const addAccountModal = new bootstrap.Modal(document.getElementById('addAccountModal'));
    $('#add-account-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=add_account';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                addAccountModal.hide(); $(this)[0].reset(); loadPageData();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    const addTransactionModal = new bootstrap.Modal(document.getElementById('addTransactionModal'));
    $('#addTransactionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const type = button.data('type');
        const modal = $(this);
        modal.find('.modal-title').text(type + ' Ekle');
        modal.find('#transaction_type').val(type);
        if (type === 'Gelir') {
            modal.find('.kaynak-grup').hide();
            modal.find('.hedef-grup').show();
        } else { // Gider
            modal.find('.kaynak-grup').show();
            modal.find('.hedef-grup').hide();
        }
    });
    $('#add-transaction-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=add_transaction';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                addTransactionModal.hide(); $(this)[0].reset(); loadPageData();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });
    
    const transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
    $('#transfer-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=add_transaction&islem_turu=Transfer';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                transferModal.hide(); $(this)[0].reset(); loadPageData();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    loadPageData();
});
</script>
</body>
</html>
