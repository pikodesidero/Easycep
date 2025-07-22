<?php
/**
 * create_admin.php
 *
 * BU DOSYA SADECE İLK KURULUM İÇİN KULLANILMALIDIR.
 *
 * Bu script, 'users' tablosunda hiç kullanıcı yoksa, varsayılan bir yönetici
 * hesabı oluşturur. Eğer sistemde zaten bir kullanıcı varsa, güvenlik
 * nedeniyle hiçbir işlem yapmaz.
 *
 * KULLANIM:
 * 1. Bu dosyayı projenizin ana dizinine yükleyin.
 * 2. Tarayıcınızdan bu dosyayı çalıştırın (örn: http://siteadresiniz.com/create_admin.php).
 * 3. Yönetici oluşturulduktan sonra BU DOSYAYI SUNUCUDAN MUTLAKA SİLİN!
 */

// Veritabanı bağlantısını ve temel ayarları dahil et.
require_once 'config.php';

// Kullanıcıya görsel geri bildirim sağlamak için basit bir HTML yapısı.
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Oluşturma Scripti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .container { max-width: 600px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h4>PikselPro - İlk Yönetici Kurulumu</h4>
            </div>
            <div class="card-body">
                <?php
                try {
                    // 1. ADIM: Sistemde zaten bir kullanıcı var mı diye kontrol et.
                    $stmt = $pdo->query("SELECT id FROM users LIMIT 1");

                    if ($stmt->fetch()) {
                        // Eğer en az bir kullanıcı varsa, hiçbir işlem yapma ve uyar.
                        echo '<div class="alert alert-warning" role="alert">';
                        echo '<strong>İşlem Durduruldu!</strong><br>';
                        echo 'Sistemde zaten bir veya daha fazla kullanıcı mevcut. Bu script güvenlik nedeniyle yalnızca boş bir veritabanında çalışır.';
                        echo '</div>';
                    } else {
                        // 2. ADIM: Hiç kullanıcı yoksa, varsayılan yöneticiyi oluştur.
                        $admin_name = 'Admin';
                        $admin_username = 'admin';
                        $admin_password = 'password123'; // Lütfen giriş yaptıktan sonra bu şifreyi değiştirin.

                        // Şifreyi güvenli bir şekilde hash'le.
                        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

                        // Veritabanına yöneticiyi ekle.
                        $sql = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, 'admin')";
                        $insert_stmt = $pdo->prepare($sql);
                        $insert_stmt->execute([$admin_name, $admin_username, $hashed_password]);

                        echo '<div class="alert alert-success" role="alert">';
                        echo '<strong>Başarılı!</strong><br>';
                        echo 'Yönetici hesabı başarıyla oluşturuldu.<br><br>';
                        echo '<strong>Kullanıcı Adı:</strong> ' . htmlspecialchars($admin_username) . '<br>';
                        echo '<strong>Şifre:</strong> ' . htmlspecialchars($admin_password) . '<br>';
                        echo '</div>';

                        echo '<div class="alert alert-danger mt-4" role="alert">';
                        echo '<strong>ÇOK ÖNEMLİ GÜVENLİK UYARISI!</strong><br>';
                        echo 'Lütfen bu dosyayı (<code>create_admin.php</code>) sunucunuzdan <strong>HEMEN SİLİN!</strong>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    // Veritabanı hatası olursa kullanıcıya genel bir mesaj göster.
                    echo '<div class="alert alert-danger" role="alert">';
                    echo '<strong>Hata!</strong><br>';
                    echo 'Veritabanı işlemi sırasında bir hata oluştu. Lütfen veritabanı bağlantı bilgilerinizi (config.php) ve tablo yapınızı kontrol edin.';
                    // Gerçek hata mesajını loglamak daha güvenlidir: error_log($e->getMessage());
                    echo '</div>';
                }
                ?>
            </div>
            <div class="card-footer text-muted text-center">
                Bu script yalnızca bir kez çalıştırılmalıdır.
            </div>
        </div>
    </div>
</body>
</html>
