<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Sadece adminlerin bu sayfayı görmesini sağla
if ($_SESSION['role'] !== 'admin') {
    die('Bu sayfayı görüntüleme yetkiniz yok.');
}

$pageTitle = "Aktivite Kayıtları";
$userRole = $_SESSION['role'];
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
            <div class="card-body">
                <table id="logsTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kullanıcı</th>
                            <th>İşlem</th>
                            <th>Kayıt ID</th>
                            <th>Detaylar</th>
                            <th>Zaman Damgası</th>
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
<script>
$(document).ready(function() {
    $('#logsTable').DataTable({
        processing: true,
        ajax: { url: 'api.php?action=fetch_logs', dataSrc: 'data' },
        columns: [
            { data: 'id' },
            { data: 'user_name' },
            { data: 'action' },
            { data: 'record_id' },
            { data: 'details' },
            { data: 'timestamp' }
        ],
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' },
        responsive: true
    });

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
