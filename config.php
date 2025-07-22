<?php
/**
 * db.php
 *
 * Bu dosya, projede kullanılacak veritabanı bağlantısını kurar.
 * PDO (PHP Data Objects) kullanılarak daha güvenli ve esnek bir yapı sağlanmıştır.
 *
 * @package PikselPro
 */

// --- Veritabanı Bağlantı Bilgileri ---
// Bu bilgileri kendi sunucu ayarlarınıza göre düzenleyebilirsiniz.
$host = 'localhost';     // Genellikle 'localhost' olarak kalır.
$dbname = 'sap';         // Veritabanı adınız.
$username = 'root';      // Veritabanı kullanıcı adınız.
$password = '';          // Veritabanı şifreniz.
$charset = 'utf8mb4';    // Türkçe karakter desteği için en iyi karakter seti.

// --- DSN (Data Source Name) Oluşturma ---
// DSN, PDO'ya hangi veritabanı sürücüsünü kullanacağını ve nasıl bağlanacağını söyler.
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// --- PDO Bağlantı Seçenekleri ---
$options = [
    // Hata modunu istisna (exception) olarak ayarla. Bu, hataları yakalamamızı sağlar.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Veritabanından gelen sonuçların varsayılan olarak nasıl alınacağını belirle (ilişkisel dizi).
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Hazırlanmış ifadelerin (prepared statements) emülasyonunu kapat. Bu, gerçek prepared statement'ları
    // kullanarak SQL Injection'a karşı güvenliği artırır.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- Bağlantıyı Kurma ---
// try-catch bloğu, bağlantı sırasında bir hata olursa programın çökmesini engeller
// ve hatayı kontrollü bir şekilde yönetmemizi sağlar.
try {
    // Yeni bir PDO nesnesi oluşturarak veritabanına bağlan.
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // Eğer bir bağlantı hatası olursa...
    // Hatayı bir log dosyasına yazmak en iyi pratiktir (canlı sunucuda).
    // error_log($e->getMessage());

    // Kullanıcıya genel bir hata mesajı göster ve programı sonlandır.
    // Asla gerçek hata mesajını ( $e->getMessage() ) kullanıcıya göstermeyin, bu bir güvenlik açığıdır.
    http_response_code(500); // Sunucu Hatası kodu gönder
    die("Sistemde bir sorun oluştu. Lütfen daha sonra tekrar deneyin.");
}

// Bu dosyayı diğer PHP dosyalarına 'require_once' ile dahil ettiğimizde,
// $pdo değişkeni o dosya içinde kullanılabilir olacaktır.
?>
