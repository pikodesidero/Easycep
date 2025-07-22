<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Eğer kullanıcı zaten giriş yapmışsa, ana sayfaya yönlendir.
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - Yönetim Paneli Girişi</title>
    <!-- Gerekli Kütüphaneler -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
      <main class="main-content">
             <div class="row mb-4">
    <div class="auth-wrapper">
        <div class="auth-card">
            <!-- Sol Panel (Marka/Görsel Alanı) -->
            <div class="auth-aside d-none d-md-flex">
                <div>
                    <i class="fas fa-rocket fa-3x mb-4"></i>
                    <h2>PikselPro'ya Tekrar Hoş Geldiniz!</h2>
                    <p>Envanterinizi yönetmek, kârınızı takip etmek ve operasyonlarınızı kolaylaştırmak için giriş yapın.</p>
                </div>
            </div>
            <!-- Sağ Panel (Giriş Formu) -->
            <div class="auth-form-container">
                <a href="index.php" class="logo">PikselPro</a>
                <h5 class="auth-title">Giriş Yap</h5>
                <p class="auth-subtitle">Devam etmek için bilgilerinizi girin.</p>
                <form id="login-form">
                    <div id="login-error-message" class="alert alert-danger d-none p-2 mb-3 text-center"></div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Giriş Yap</button>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <p class="text-muted">Hesabınız yok mu? <a href="register.php">Hemen oluşturun</a></p>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script>
        $(document).ready(function() {
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'login' });

                $.post('api.php', $.param(formData), function(response) {
                    if (response.status === 'success') {
                        // Giriş başarılı olduğunda index.php'ye yönlendir.
                        window.location.href = 'index.php';
                    } else {
                        $('#login-error-message').text(response.message).removeClass('d-none');
                    }
                }, 'json').fail(function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Giriş sırasında bir sunucu hatası oluştu.';
                    $('#login-error-message').text(errorMessage).removeClass('d-none');
                });
            });
        });
    </script>
</body>
</html>
