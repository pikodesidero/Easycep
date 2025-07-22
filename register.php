<?php
// Bu sayfa, yeni kullanıcıların kaydedilmesi için bir arayüz sağlar.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PikselPro - Yeni Kullanıcı Kaydı</title>
    <!-- Gerekli kütüphaneler (login.php ile aynı) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <a href="index.php" class="logo">PikselPro</a>
                <h5 class="mt-2">Yeni Personel Hesabı Oluştur</h5>
                <p class="text-muted">Lütfen aşağıdaki bilgileri doldurun.</p>
            </div>
            <div class="login-body">
                <form id="register-form">
                    <div id="register-message" class="d-none"></div>
                    <div class="mb-3">
                        <label for="register_name" class="form-label">Ad Soyad</label>
                        <input type="text" class="form-control" id="register_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="register_username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="register_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="register_password" class="form-label">Şifre</label>
                        <input type="password" class="form-control" id="register_password" name="password" required>
                    </div>
                     <div class="mb-4">
                        <label for="register_role" class="form-label">Kullanıcı Rolü</label>
                        <select class="form-select" id="register_role" name="role">
                            <option value="personel">Personel</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Hesap Oluştur</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php">Giriş ekranına dön</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#register-form').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serializeArray();
                formData.push({ name: 'action', value: 'register' });

                $.post('api.php', $.param(formData), function(response) {
                    const messageDiv = $('#register-message');
                    if (response.status === 'success') {
                        messageDiv.removeClass('alert-danger').addClass('alert alert-success').text(response.message).removeClass('d-none');
                        $('#register-form')[0].reset();
                        // İsteğe bağlı olarak kullanıcıyı giriş sayfasına yönlendirebilirsiniz.
                        // setTimeout(() => { window.location.href = 'index.php'; }, 2000);
                    } else {
                        messageDiv.removeClass('alert-success').addClass('alert alert-danger').text(response.message).removeClass('d-none');
                    }
                }, 'json').fail(function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'Kayıt sırasında bir sunucu hatası oluştu.';
                    $('#register-message').removeClass('alert-success').addClass('alert alert-danger').text(errorMessage).removeClass('d-none');
                });
            });
        });
    </script>
</body>
</html>
