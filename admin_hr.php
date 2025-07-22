<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$pageTitle = "İK Yönetimi";
$userRole = $_SESSION['role'];
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
            <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="user-menu d-flex align-items-center">
                 <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Yeni Personel Ekle</a>
            </div>
        </header>

        <div class="card shadow-sm">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="hrTab" role="tablist">
                    <li class="nav-item" role="presentation"><a class="nav-link active" data-bs-toggle="tab" href="#personel-listesi" role="tab">Personel Listesi</a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link position-relative" data-bs-toggle="tab" href="#izin-talepleri" role="tab">İzin Talepleri <span id="izin-badge" class="badge rounded-pill bg-danger d-none position-absolute top-0 start-100 translate-middle"></span></a></li>
                    <li class="nav-item" role="presentation"><a class="nav-link position-relative" data-bs-toggle="tab" href="#avans-talepleri" role="tab">Avans Talepleri <span id="avans-badge" class="badge rounded-pill bg-danger d-none position-absolute top-0 start-100 translate-middle"></span></a></li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content p-2" id="hrTabContent">
                    <div class="tab-pane fade show active" id="personel-listesi" role="tabpanel">
                        <table id="usersTable" class="table table-hover" style="width:100%">
                             <thead>
                                <tr>
                                    <th>Personel</th>
                                    <th>Pozisyon</th>
                                    <th>Hak Edilen Prim</th>
                                    <th>Ödenen Prim</th>
                                    <th>Kalan Prim</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="izin-talepleri" role="tabpanel">
                        <table id="leaveRequestsTable" class="table table-hover" style="width:100%"></table>
                    </div>
                    <div class="tab-pane fade" id="avans-talepleri" role="tabpanel">
                        <table id="advanceRequestsTable" class="table table-hover" style="width:100%"></table>
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

<script>
$(document).ready(function() {
    // --- YARDIMCI FONKSİYONLAR ---
    function currencyFormatter(data) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0); }
    function showToast(title, message, icon = 'success') { Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' }); }
    
    async function loadAccounts() {
        try {
            const response = await fetch('api.php?action=fetch_accounts');
            const result = await response.json();
            if (result.status === 'success') {
                const accountSelects = $('.account-select');
                accountSelects.empty().append('<option value="">Hesap Seçiniz...</option>');
                result.data.forEach(acc => {
                    accountSelects.append(`<option value="${acc.id}">${acc.hesap_adi}</option>`);
                });
            }
        } catch (error) { console.error("Hesaplar yüklenemedi:", error); }
    }

    // --- DATATABLE İNİTİALİZE ---
    let usersTable, leaveRequestsTable, advanceRequestsTable;

    usersTable = $('#usersTable').DataTable({
        processing: true,
        ajax: { url: 'api.php?action=fetch_users_with_prim', dataSrc: 'data' },
        columns: [
            { data: 'name', render: function(data, type, row) {
                let notificationBadge = '';
                if (row.bekleyen_talep_sayisi > 0) {
                    notificationBadge = `<span class="badge rounded-pill bg-warning text-dark ms-2" title="${row.bekleyen_talep_sayisi} adet bekleyen talep">${row.bekleyen_talep_sayisi}</span>`;
                }
                return `${data}${notificationBadge}`;
            }},
            { data: 'pozisyon' },
            { data: 'toplam_hak_edilen', render: currencyFormatter },
            { data: 'toplam_odenen', render: currencyFormatter },
            { data: 'kalan_prim', render: (data) => `<strong class="text-success">${currencyFormatter(data)}</strong>` },
            { data: null, orderable: false, searchable: false, render: function(data, type, row) {
                const editBtn = `<button class="btn btn-outline-primary btn-sm edit-user-btn" data-id="${row.id}" title="Düzenle"><i class="fas fa-user-edit"></i></button>`;
                const payBtn = `<button class="btn btn-outline-success btn-sm pay-prim-btn" data-id="${row.id}" data-name="${row.name}" data-kalan="${row.kalan_prim}" title="Prim Öde"><i class="fas fa-hand-holding-usd"></i></button>`;
                const deleteBtn = row.id == <?php echo $_SESSION['user_id']; ?> ? '' : `<button class="btn btn-outline-danger btn-sm delete-user-btn" data-id="${row.id}" title="Sil"><i class="fas fa-user-times"></i></button>`;
                return `<div class="btn-group">${editBtn}${payBtn}${deleteBtn}</div>`;
            }}
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
    });

    function loadPendingRequests() {
        $.get('api.php?action=fetch_pending_requests', function(response) {
            if (response.status === 'success') {
                const totalPending = response.izinler.length + response.avanslar.length;
                if (totalPending > 0) {
                    $('#hr-notification-badge').text(totalPending).removeClass('d-none');
                } else {
                    $('#hr-notification-badge').addClass('d-none');
                }
                
                $('#izin-badge').text(response.izinler.length > 0 ? response.izinler.length : '').toggleClass('d-none', response.izinler.length === 0);
                $('#avans-badge').text(response.avanslar.length > 0 ? response.avanslar.length : '').toggleClass('d-none', response.avanslar.length === 0);

                renderTable('#leaveRequestsTable', leaveRequestsTable, response.izinler, [
                    { title: 'Personel', data: 'name' }, { title: 'Tür', data: 'izin_turu' },
                    { title: 'Başlangıç', data: 'baslangic_tarihi' }, { title: 'Bitiş', data: 'bitis_tarihi' },
                    { title: 'Gün', data: 'gun_sayisi' }, { title: 'İşlemler', data: 'id', render: (id) => renderActionButtons(id, 'izin') }
                ]);
                renderTable('#advanceRequestsTable', advanceRequestsTable, response.avanslar, [
                    { title: 'Personel', data: 'name' }, { title: 'Talep Tarihi', data: 'talep_tarihi' },
                    { title: 'Tutar', data: 'tutar', render: currencyFormatter }, { title: 'Açıklama', data: 'aciklama' },
                    { title: 'İşlemler', data: 'id', render: (id, type, row) => renderActionButtons(id, 'avans', row) }
                ]);
            }
        });
    }

    function renderTable(selector, tableInstance, data, columns) {
        if ($.fn.DataTable.isDataTable(selector)) {
            if(tableInstance) tableInstance.clear().rows.add(data).draw();
        } else {
            tableInstance = $(selector).DataTable({ 
                data: data, 
                columns: columns, 
                paging: false, 
                searching: false, 
                info: false, 
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' } 
            });
        }
    }

    function renderActionButtons(id, type, rowData = {}) {
        const approveBtn = `<button class="btn btn-success btn-sm approve-request-btn" data-id="${id}" data-type="${type}" data-user-id="${rowData.user_id}" data-amount="${rowData.tutar}" title="Onayla"><i class="fas fa-check"></i></button>`;
        const rejectBtn = `<button class="btn btn-danger btn-sm reject-request-btn" data-id="${id}" data-type="${type}" title="Reddet"><i class="fas fa-times"></i></button>`;
        return `<div class="btn-group">${approveBtn}${rejectBtn}</div>`;
    }

    // --- MODAL VE FORM İŞLEMLERİ ---
    const editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    const payPrimModal = new bootstrap.Modal(document.getElementById('payPrimModal'));
    const approveAdvanceModal = new bootstrap.Modal(document.getElementById('approveAdvanceModal'));
    
    // Prim Ödeme Butonu ve Formu
    $('#usersTable tbody').on('click', '.pay-prim-btn', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const kalan = $(this).data('kalan');
        $('#prim_user_id').val(id);
        $('#prim_personel_adi').text(name);
        $('#prim_kalan_bakiye').text(currencyFormatter(kalan));
        $('#prim_tutar').attr('max', kalan).val(kalan > 0 ? kalan : 0);
        loadAccounts().then(() => payPrimModal.show());
    });

    $('#pay-prim-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=pay_prim';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                payPrimModal.hide();
                usersTable.ajax.reload();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    // Kullanıcı Düzenleme Butonu ve Formu
    $('#usersTable tbody').on('click', '.edit-user-btn', function() {
        const id = $(this).data('id');
        $.get('api.php', { action: 'get_user_details', id: id }, (response) => {
            if (response.status === 'success') {
                const form = $('#edit-user-form');
                form[0].reset();
                $.each(response.data, (key, value) => {
                    form.find(`[name="${key}"]`).val(value);
                });
                $('#editUserModalLabel').text(`Personeli Düzenle: ${response.data.name}`);
                editUserModal.show();
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    $('#edit-user-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=update_user';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                editUserModal.hide();
                usersTable.ajax.reload();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    // Kullanıcı Silme Butonu
    $('#usersTable tbody').on('click', '.delete-user-btn', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Emin misiniz?', text: `ID:${id} nolu kullanıcı kalıcı olarak silinecektir!`,
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33',
            confirmButtonText: 'Evet, sil!', cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api.php', { action: 'delete_user', id: id }, (response) => {
                    if (response.status === 'success') {
                        usersTable.ajax.reload();
                        showToast('Başarılı', response.message);
                    } else { showToast('Hata', response.message, 'error'); }
                }, 'json');
            }
        });
    });

    // Talep Onaylama/Reddetme
    $('#hrTabContent').on('click', '.approve-request-btn, .reject-request-btn', function() {
        const id = $(this).data('id');
        const type = $(this).data('type');
        const status = $(this).hasClass('approve-request-btn') ? 'Onaylandı' : 'Reddedildi';

        if(type === 'avans' && status === 'Onaylandı') {
            $('#avans_request_id').val(id);
            $('#avans_user_id').val($(this).data('user-id'));
            $('#avans_tutar').val($(this).data('amount'));
            loadAccounts().then(() => approveAdvanceModal.show());
        } else {
            processRequest(type, id, status);
        }
    });
    
    $('#approve-advance-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#avans_request_id').val();
        const odeme_hesap_id = $('#avans_odeme_hesabi').val();
        processRequest('avans', id, 'Onaylandı', odeme_hesap_id);
    });

    function processRequest(type, id, status, accountId = null) {
        $.post('api.php', { action: 'process_hr_request', request_type: type, request_id: id, new_status: status, odeme_hesap_id: accountId }, (response) => {
            if (response.status === 'success') {
                showToast('Başarılı', response.message);
                loadPendingRequests();
                usersTable.ajax.reload(); // Personel listesindeki bildirimleri de güncelle
                if(approveAdvanceModal._isShown) approveAdvanceModal.hide();
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    }

    // Sayfa ilk yüklendiğinde talep listesini de yükle
    loadPendingRequests();
});
</script>
</body>
</html>
