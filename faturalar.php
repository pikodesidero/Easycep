<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }

$pageTitle = "Fatura Yönetimi";
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
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="user-menu d-flex align-items-center">
                 <a href="fatura_olustur.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Yeni Fatura Oluştur</a>
            </div>
        </header>

        <div class="card shadow-sm">
            <div class="card-header"><h6 class="m-0 font-weight-bold">Tüm Faturalar</h6></div>
            <div class="card-body">
                <table id="invoicesTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Fatura No</th><th>Müşteri</th><th>Fatura Tarihi</th><th>Vade Tarihi</th><th>Genel Toplam</th><th>Durum</th><th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    function currencyFormatter(data) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(data || 0); }
    
    $('#invoicesTable').DataTable({
        processing: true,
        ajax: { url: 'api.php?action=fetch_invoices', dataSrc: 'data' },
        columns: [
            { data: 'fatura_no' }, { data: 'musteri_adi' }, { data: 'fatura_tarihi' },
            { data: 'vade_tarihi' }, { data: 'genel_toplam', render: currencyFormatter },
            { data: 'durum' },
            { data: 'id', render: function(data) {
                return `<a href="fatura_goruntule.php?id=${data}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> Görüntüle</a>`;
            }}
        ],
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
    });
});
</script>
</body>
</html>
