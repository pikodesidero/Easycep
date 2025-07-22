<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
require_once 'config.php';

// --- YARDIMCI FONKSİYONLAR ---
function check_auth($role_needed = 'personel') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Lütfen giriş yapın.']);
        exit();
    }
    if ($role_needed === 'admin' && $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.']);
        exit();
    }
}

function log_activity($pdo, $action, $record_id = null, $details = '') {
    $user_id = $_SESSION['user_id'] ?? 0;
    $username = $_SESSION['username'] ?? 'Sistem';
    $sql = "INSERT INTO activity_logs (user_id, username, action, record_id, details) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $username, $action, $record_id, $details]);
}

function buildWhereClause($filters) {
    $sql = ""; $params = [];
    if (!empty($filters['date_type']) && !empty($filters['start_date']) && !empty($filters['end_date'])) {
        $allowed_date_columns = ['tarih', 'starihi', 'otarihi'];
        if (in_array($filters['date_type'], $allowed_date_columns)) {
            $sql .= " AND {$filters['date_type']} BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }
    }
    if (!empty($filters['imei'])) { $sql .= " AND imei LIKE ?"; $params[] = '%' . $filters['imei'] . '%'; }
    if (!empty($filters['durum'])) { $sql .= " AND durum = ?"; $params[] = $filters['durum']; }
    if (!empty($filters['alanpersonel'])) { $sql .= " AND alanpersonel LIKE ?"; $params[] = '%' . $filters['alanpersonel'] . '%'; }
    if (!empty($filters['satanpersonel'])) { $sql .= " AND satanpersonel LIKE ?"; $params[] = '%' . $filters['satanpersonel'] . '%'; }
    return ['sql' => $sql, 'params' => $params];
}

function processAndValidateData($post_data) {
    $allowed_fields = [
        'id', 'isim', 'soyisim', 'tc', 'telefon', 'iban', 'email', 'şehir', 'ilçe', 'mahalle',
        'tarih', 'marka', 'model', 'imei', 'renk', 'hafiza', 'kozmetik', 'durum', 'maliyet', 'kar',
        'gelenodeme', 'odeme', 'kredino', 'kreditarih', 'starihi', 'otarihi', 'gecikme',
        'alanpersonel', 'satanpersonel', 'satisprimi', 'aprimodeme', 'sprimodeme',
        'aprimodemetarihi', 'sprimodemetarihi', 'tedarik', 'tedarikci', 'notlar', 'odeme_hesap_id'
    ];
    
    $data = [];
    foreach ($allowed_fields as $field) {
        if (isset($post_data[$field])) {
            $data[$field] = $post_data[$field] === '' ? null : $post_data[$field];
        }
    }

    $data['cari'] = trim(($data['isim'] ?? '') . ' ' . ($data['soyisim'] ?? ''));
    $data['marka_model'] = trim(($data['marka'] ?? '') . ' ' . ($data['model'] ?? ''));
    
    $maliyet = (float)($data['maliyet'] ?? 0);
    $kar = (float)($data['kar'] ?? 0);
    $gelenodeme = (float)($data['gelenodeme'] ?? 0);
    $sfiyat = $maliyet + $kar;

    $data['sfiyat'] = $sfiyat;
    $data['kalan_alacak'] = $sfiyat - $gelenodeme;
    $data['alisprimi'] = $kar * 0.05;
    $data['moran'] = ($maliyet > 0) ? '%' . number_format(($kar / $maliyet) * 100, 2) : '%0.00';
    $data['soran'] = ($sfiyat > 0) ? '%' . number_format(($kar / $sfiyat) * 100, 2) : '%0.00';

    return $data;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if (!in_array($action, ['login'])) { check_auth(); }

    switch ($action) {
        // --- KULLANICI İŞLEMLERİ ---
        case 'login':
            $username = $_POST['username'] ?? ''; $password = $_POST['password'] ?? '';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['name'] = $user['name']; $_SESSION['role'] = $user['role'];
                log_activity($pdo, 'login_success');
                echo json_encode(['status' => 'success', 'message' => 'Giriş başarılı!']);
            } else {
                log_activity($pdo, 'login_fail', null, "Kullanıcı: $username");
                throw new Exception("Geçersiz kullanıcı adı veya şifre.");
            }
            break;

        case 'logout':
            log_activity($pdo, 'logout'); session_destroy(); header('Location: login.php'); exit();
            break;
            
        case 'register':
            check_auth('admin');
            $name = $_POST['name'] ?? ''; $username = $_POST['username'] ?? ''; $password = $_POST['password'] ?? ''; $role = $_POST['role'] ?? 'personel';
            if (empty($name) || empty($username) || empty($password)) { throw new Exception("Tüm alanlar zorunludur."); }
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) { throw new Exception("Bu kullanıcı adı zaten alınmış."); }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $username, $hashed_password, $role]);
            log_activity($pdo, 'register_success', null, "Yeni kullanıcı: $username");
            echo json_encode(['status' => 'success', 'message' => 'Yeni kullanıcı başarıyla oluşturuldu.']);
            break;

        // --- ENVANTER (BUYBACKS) İŞLEMLERİ ---
        case 'fetch':
            $whereClause = buildWhereClause($_GET);
            $sql = "SELECT * FROM buybacks WHERE 1=1" . $whereClause['sql'] . " ORDER BY id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($whereClause['params']);
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;

        case 'get_single':
            $id = $_GET['id'] ?? 0;
            if(empty($id)) { throw new Exception("Kayıt ID'si belirtilmedi."); }
            $stmt = $pdo->prepare("SELECT * FROM buybacks WHERE id = ?");
            $stmt->execute([$id]);
            $record = $stmt->fetch();
            if(!$record) { http_response_code(404); throw new Exception("Kayıt bulunamadı."); }
            echo json_encode(['status' => 'success', 'data' => $record]);
            break;

        case 'add_record':
            $data = processAndValidateData($_POST);
            if (empty($data)) { throw new Exception("Gönderilecek veri boş."); }
            unset($data['id']);
            $columns = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO buybacks ($columns) VALUES ($placeholders)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            $lastId = $pdo->lastInsertId();
            log_activity($pdo, 'add_record', $lastId, "Model: " . ($data['marka_model'] ?? 'N/A'));
            echo json_encode(['status' => 'success', 'message' => 'Kayıt başarıyla eklendi.']);
            break;

        case 'edit_record':
            check_auth('admin');
            $pdo->beginTransaction();
            $id = $_POST['id'] ?? 0;
            if (empty($id)) { throw new Exception('Güncellenecek ID bulunamadı.'); }
            $stmt_onceki = $pdo->prepare("SELECT gelenodeme FROM buybacks WHERE id = ?");
            $stmt_onceki->execute([$id]);
            $onceki_odeme = (float)$stmt_onceki->fetchColumn();
            $data = processAndValidateData($_POST);
            unset($data['id']);
            $set_parts = [];
            foreach (array_keys($data) as $key) { $set_parts[] = "$key = ?"; }
            $sql = "UPDATE buybacks SET " . implode(', ', $set_parts) . " WHERE id = ?";
            $params = array_values($data);
            $params[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $yeni_odeme = (float)($data['gelenodeme'] ?? 0);
            $odeme_farki = $yeni_odeme - $onceki_odeme;
            $odeme_hesap_id = !empty($_POST['odeme_hesap_id']) ? $_POST['odeme_hesap_id'] : null;
            if ($odeme_farki > 0 && !is_null($odeme_hesap_id)) {
                $aciklama = "ID: $id - " . ($data['marka_model'] ?? '') . " cihaz ödemesi";
                $hareket_sql = "INSERT INTO hesap_hareketleri (hedef_hesap_id, islem_turu, tutar, aciklama, tarih) VALUES (?, 'Gelir', ?, ?, ?)";
                $hareket_stmt = $pdo->prepare($hareket_sql);
                $hareket_stmt->execute([$odeme_hesap_id, $odeme_farki, $aciklama, date('Y-m-d')]);
                $bakiye_sql = "UPDATE hesaplar SET bakiye = bakiye + ? WHERE id = ?";
                $bakiye_stmt = $pdo->prepare($bakiye_sql);
                $bakiye_stmt->execute([$odeme_farki, $odeme_hesap_id]);
                log_activity($pdo, 'payment_received', $id, "Hesap ID: $odeme_hesap_id, Tutar: $odeme_farki");
            }
            $pdo->commit();
            log_activity($pdo, 'edit_record', $id);
            echo json_encode(['status' => 'success', 'message' => 'Kayıt başarıyla güncellendi.']);
            break;
            
        case 'update_notes':
            check_auth(); 
            $id = $_POST['id'] ?? 0;
            $notes = $_POST['notlar'] ?? '';
            if (empty($id)) { throw new Exception('Güncellenecek kayıt ID\'si bulunamadı.'); }
            $sql = "UPDATE buybacks SET notlar = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$notes, $id]);
            log_activity($pdo, 'update_notes', $id);
            echo json_encode(['status' => 'success', 'message' => 'Notlar başarıyla güncellendi.']);
            break;

        case 'delete':
            check_auth('admin');
            $id = $_POST['id'] ?? 0;
            if (empty($id)) { throw new Exception("Silinecek ID belirtilmedi."); }
            log_activity($pdo, 'delete_record', $id);
            $stmt = $pdo->prepare("DELETE FROM buybacks WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Kayıt başarıyla silindi.']);
            break;

        case 'bulk_delete':
        case 'bulk_status_update':
            check_auth('admin');
            $ids = $_POST['ids'] ?? [];
            if (empty($ids) || !is_array($ids)) { throw new Exception("İşlem için kayıt seçilmedi."); }
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            if ($action === 'bulk_delete') {
                $sql = "DELETE FROM buybacks WHERE id IN ($placeholders)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($ids);
                log_activity($pdo, 'bulk_delete', null, "Silinen ID'ler: " . implode(', ', $ids));
                echo json_encode(['status' => 'success', 'message' => count($ids) . ' kayıt başarıyla silindi.']);
            } else { // bulk_status_update
                $status = $_POST['status'] ?? '';
                if (empty($status)) { throw new Exception("Yeni durum bilgisi eksik."); }
                $set_sql = "durum = ?"; $params = [$status];
                $today = date('Y-m-d');
                if ($status === 'Satıldı') { $set_sql .= ", starihi = ?"; $params[] = $today; }
                elseif ($status === 'Ödendi') { $set_sql .= ", otarihi = ?"; $params[] = $today; }
                $sql = "UPDATE buybacks SET $set_sql WHERE id IN ($placeholders)";
                $params = array_merge($params, $ids);
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                log_activity($pdo, 'bulk_status_update', null, "Yeni durum: $status, ID'ler: " . implode(', ', $ids));
                echo json_encode(['status' => 'success', 'message' => count($ids) . ' kaydın durumu güncellendi.']);
            }
            break;

        // --- RAPORLAMA VE DASHBOARD ---
        case 'fetch_summary':
            $whereClause = buildWhereClause($_GET);
            $sql = "SELECT COUNT(*) as total_records, COALESCE(SUM(kalan_alacak), 0) as sold_receivable, COALESCE(SUM(gelenodeme), 0) as paid_receivable FROM buybacks WHERE 1=1" . $whereClause['sql'];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($whereClause['params']);
            echo json_encode(['status' => 'success', 'summary' => $stmt->fetch()]);
            break;
            
        case 'fetch_chart_data':
            $whereClause = buildWhereClause($_GET);
            $sql = "SELECT DATE_FORMAT(tarih, '%Y-%m') as month, SUM(kar) as total_profit FROM buybacks WHERE durum IN ('Satıldı', 'Ödendi')" . $whereClause['sql'] . " GROUP BY month ORDER BY month ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($whereClause['params']);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;
        
        case 'fetch_personnel_report':
            $whereClause = buildWhereClause($_GET);
            $sql = "SELECT personel, SUM(CASE WHEN type = 'alis' THEN 1 ELSE 0 END) as toplam_alim_adedi, SUM(CASE WHEN type = 'alis' THEN maliyet ELSE 0 END) as toplam_alim_maliyeti, SUM(CASE WHEN type = 'satis' THEN 1 ELSE 0 END) as toplam_satis_adedi, SUM(CASE WHEN type = 'satis' THEN kar ELSE 0 END) as toplam_satis_kari, SUM(CASE WHEN type = 'alis' THEN alisprimi ELSE 0 END) as toplam_alis_primi, SUM(CASE WHEN type = 'satis' THEN satisprimi ELSE 0 END) as toplam_satis_primi FROM ( SELECT alanpersonel as personel, 'alis' as type, maliyet, kar, alisprimi, 0 as satisprimi FROM buybacks WHERE alanpersonel != ''" . $whereClause['sql'] . " UNION ALL SELECT satanpersonel as personel, 'satis' as type, 0, kar, 0, satisprimi FROM buybacks WHERE satanpersonel != '' AND durum IN ('Satıldı', 'Ödendi')" . $whereClause['sql'] . " ) as combined WHERE personel IS NOT NULL AND personel != '' GROUP BY personel";
            $stmt = $pdo->prepare($sql);
            $params = array_merge($whereClause['params'], $whereClause['params']);
            $stmt->execute($params);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;

        case 'fetch_logs':
            check_auth('admin');
            $sql = "SELECT l.*, u.name as user_name, l.created_at as timestamp FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.id DESC LIMIT 500";
            $stmt = $pdo->query($sql);
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;
            
        // --- MUHASEBE MODÜLÜ ---
        case 'fetch_accounts':
            $stmt = $pdo->query("SELECT * FROM hesaplar ORDER BY hesap_turu, hesap_adi");
            echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
            break;
        case 'fetch_transactions':
            $sql = "SELECT h.*, kaynak.hesap_adi as kaynak_hesap_adi, hedef.hesap_adi as hedef_hesap_adi FROM hesap_hareketleri h LEFT JOIN hesaplar kaynak ON h.kaynak_hesap_id = kaynak.id LEFT JOIN hesaplar hedef ON h.hedef_hesap_id = hedef.id ORDER BY h.tarih DESC, h.id DESC LIMIT 100";
            $stmt = $pdo->query($sql);
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;
        case 'add_account':
            check_auth('admin');
            $sql = "INSERT INTO hesaplar (hesap_adi, hesap_turu, banka_adi, iban, bakiye) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['hesap_adi'], $_POST['hesap_turu'], $_POST['banka_adi'], $_POST['iban'], $_POST['bakiye']]);
            log_activity($pdo, 'add_account', $pdo->lastInsertId(), "Hesap: ".$_POST['hesap_adi']);
            echo json_encode(['status' => 'success', 'message' => 'Yeni hesap başarıyla oluşturuldu.']);
            break;
        case 'add_transaction':
            check_auth('admin');
            $islem_turu = $_POST['islem_turu'];
            $tutar = (float)$_POST['tutar'];
            $kaynak_id = !empty($_POST['kaynak_hesap_id']) ? $_POST['kaynak_hesap_id'] : null;
            $hedef_id = !empty($_POST['hedef_hesap_id']) ? $_POST['hedef_hesap_id'] : null;
            if (($islem_turu === 'Gider' || $islem_turu === 'Transfer') && is_null($kaynak_id)) { throw new Exception("Gider veya Transfer işlemi için Kaynak Hesap seçilmelidir."); }
            if (($islem_turu === 'Gelir' || $islem_turu === 'Transfer') && is_null($hedef_id)) { throw new Exception("Gelir veya Transfer işlemi için Hedef Hesap seçilmelidir."); }
            $pdo->beginTransaction();
            $sql = "INSERT INTO hesap_hareketleri (kaynak_hesap_id, hedef_hesap_id, islem_turu, tutar, aciklama, tarih) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$kaynak_id, $hedef_id, $islem_turu, $tutar, $_POST['aciklama'], $_POST['tarih']]);
            if ($islem_turu === 'Gelir') {
                $stmt_update = $pdo->prepare("UPDATE hesaplar SET bakiye = bakiye + ? WHERE id = ?");
                $stmt_update->execute([$tutar, $hedef_id]);
            } elseif ($islem_turu === 'Gider') {
                $stmt_update = $pdo->prepare("UPDATE hesaplar SET bakiye = bakiye - ? WHERE id = ?");
                $stmt_update->execute([$tutar, $kaynak_id]);
            } elseif ($islem_turu === 'Transfer') {
                $stmt_kaynak = $pdo->prepare("UPDATE hesaplar SET bakiye = bakiye - ? WHERE id = ?");
                $stmt_kaynak->execute([$tutar, $kaynak_id]);
                $stmt_hedef = $pdo->prepare("UPDATE hesaplar SET bakiye = bakiye + ? WHERE id = ?");
                $stmt_hedef->execute([$tutar, $hedef_id]);
            }
            $pdo->commit();
            log_activity($pdo, 'add_transaction', $pdo->lastInsertId(), "Tutar: $tutar, Tür: $islem_turu");
            echo json_encode(['status' => 'success', 'message' => 'İşlem başarıyla kaydedildi.']);
            break;

        // --- İK MODÜLÜ ---
        case 'fetch_my_profile_data':
            $user_id = $_SESSION['user_id'];
            $user_name = $_SESSION['name'];
            $user_stmt = $pdo->prepare("SELECT pozisyon, maas FROM users WHERE id = ?");
            $user_stmt->execute([$user_id]);
            $user_data = $user_stmt->fetch();
            $prim_sql = "SELECT (SELECT COALESCE(SUM(prim), 0) FROM ( SELECT alisprimi as prim FROM buybacks WHERE alanpersonel = :name1 UNION ALL SELECT satisprimi as prim FROM buybacks WHERE satanpersonel = :name2 AND durum IN ('Satıldı', 'Ödendi') ) as primler) as toplam_hak_edilen, (SELECT COALESCE(SUM(tutar), 0) FROM personel_prim_odemeleri WHERE user_id = :user_id) as toplam_odenen";
            $prim_stmt = $pdo->prepare($prim_sql);
            $prim_stmt->execute(['name1' => $user_name, 'name2' => $user_name, 'user_id' => $user_id]);
            $prim_data = $prim_stmt->fetch();
            $kalan_prim = ($prim_data['toplam_hak_edilen'] ?? 0) - ($prim_data['toplam_odenen'] ?? 0);
            $avans_stmt = $pdo->prepare("SELECT SUM(tutar) as aylik_avans FROM personel_avanslar WHERE user_id = ? AND durum = 'Onaylandı' AND MONTH(talep_tarihi) = MONTH(CURDATE()) AND YEAR(talep_tarihi) = YEAR(CURDATE())");
            $avans_stmt->execute([$user_id]);
            $aylik_avans = $avans_stmt->fetchColumn();
            $izin_stmt = $pdo->prepare("SELECT SUM(gun_sayisi) FROM personel_izinler WHERE user_id = ? AND durum = 'Onaylandı' AND izin_turu = 'Yıllık İzin'");
            $izin_stmt->execute([$user_id]);
            $kullanilan_izin = $izin_stmt->fetchColumn();
            $kalan_yillik_izin = 14 - ($kullanilan_izin ?: 0);
            $perf_stmt = $pdo->prepare("(SELECT 'Alım' as type, tarih, marka_model, alisprimi as prim FROM buybacks WHERE alanpersonel = ?) UNION ALL (SELECT 'Satış' as type, starihi as tarih, marka_model, satisprimi as prim FROM buybacks WHERE satanpersonel = ? AND durum IN ('Satıldı', 'Ödendi')) ORDER BY tarih DESC");
            $perf_stmt->execute([$user_name, $user_name]);
            $performance_data = $perf_stmt->fetchAll();
            $avanslar_stmt = $pdo->prepare("SELECT * FROM personel_avanslar WHERE user_id = ? ORDER BY talep_tarihi DESC");
            $avanslar_stmt->execute([$user_id]);
            $izinler_stmt = $pdo->prepare("SELECT * FROM personel_izinler WHERE user_id = ? ORDER BY talep_tarihi DESC");
            $izinler_stmt->execute([$user_id]);
            echo json_encode(['status' => 'success', 'data' => [ 'user' => $user_data, 'kpi' => [ 'toplam_hak_edilen' => $prim_data['toplam_hak_edilen'] ?: 0, 'toplam_odenen' => $prim_data['toplam_odenen'] ?: 0, 'kalan_prim' => $kalan_prim, 'aylik_avans' => $aylik_avans ?: 0, 'kalan_yillik_izin' => $kalan_yillik_izin ], 'performance' => $performance_data, 'avanslar' => $avanslar_stmt->fetchAll(), 'izinler' => $izinler_stmt->fetchAll() ]]);
            break;
        case 'request_leave':
            $start = new DateTime($_POST['baslangic_tarihi']);
            $end = new DateTime($_POST['bitis_tarihi']);
            $end->modify('+1 day');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($start, $interval, $end);
            $gun_sayisi = 0;
            foreach($dateRange as $date){ $gun_sayisi++; }
            $sql = "INSERT INTO personel_izinler (user_id, izin_turu, baslangic_tarihi, bitis_tarihi, gun_sayisi, aciklama) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $_POST['izin_turu'], $_POST['baslangic_tarihi'], $_POST['bitis_tarihi'], $gun_sayisi, $_POST['aciklama']]);
            log_activity($pdo, 'request_leave', $pdo->lastInsertId());
            echo json_encode(['status' => 'success', 'message' => 'İzin talebiniz başarıyla gönderildi.']);
            break;
        case 'request_advance':
            $sql = "INSERT INTO personel_avanslar (user_id, tutar, aciklama, talep_tarihi) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $_POST['tutar'], $_POST['aciklama'], date('Y-m-d')]);
            log_activity($pdo, 'request_advance', $pdo->lastInsertId(), "Tutar: ".$_POST['tutar']);
            echo json_encode(['status' => 'success', 'message' => 'Avans talebiniz başarıyla gönderildi.']);
            break;

         case 'fetch_invoices':
            $stmt = $pdo->query("SELECT id, fatura_no, musteri_adi, fatura_tarihi, vade_tarihi, genel_toplam, durum FROM faturalar ORDER BY id DESC");
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;

        case 'save_invoice':
            check_auth('admin');
            $pdo->beginTransaction();

            $ara_toplam = 0;
            $kdv_tutari = 0;
            $items = $_POST['items'] ?? [];

            foreach ($items as $item) {
                $miktar = (float)$item['miktar'];
                $birim_fiyat = (float)$item['birim_fiyat'];
                $kdv_orani = (int)$item['kdv_orani'];
                $kalem_toplam = $miktar * $birim_fiyat;
                $ara_toplam += $kalem_toplam;
                $kdv_tutari += $kalem_toplam * ($kdv_orani / 100);
            }
            $genel_toplam = $ara_toplam + $kdv_tutari;

            $sql_fatura = "INSERT INTO faturalar (fatura_no, musteri_adi, fatura_tarihi, vade_tarihi, ara_toplam, kdv_tutari, genel_toplam) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_fatura = $pdo->prepare($sql_fatura);
            $stmt_fatura->execute([$_POST['fatura_no'], $_POST['musteri_adi'], $_POST['fatura_tarihi'], $_POST['vade_tarihi'], $ara_toplam, $kdv_tutari, $genel_toplam]);
            $fatura_id = $pdo->lastInsertId();

            $sql_kalem = "INSERT INTO fatura_kalemleri (fatura_id, urun_aciklama, miktar, birim_fiyat, kdv_orani, toplam) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_kalem = $pdo->prepare($sql_kalem);

            foreach ($items as $item) {
                 $miktar = (float)$item['miktar'];
                 $birim_fiyat = (float)$item['birim_fiyat'];
                 $kdv_orani = (int)$item['kdv_orani'];
                 $kalem_toplam = $miktar * $birim_fiyat;
                 $stmt_kalem->execute([$fatura_id, $item['aciklama'], $miktar, $birim_fiyat, $kdv_orani, $kalem_toplam]);
            }
            
            $pdo->commit();
            log_activity($pdo, 'create_invoice', $fatura_id, "Tutar: $genel_toplam");
            echo json_encode(['status' => 'success', 'message' => 'Fatura başarıyla oluşturuldu.']);
            break;
            
        case 'fetch_users':
            check_auth('admin');
            $stmt = $pdo->query("SELECT id, name, username, role, pozisyon, maas, ise_giris_tarihi FROM users ORDER BY id ASC");
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;
        case 'get_user_details':
            check_auth('admin');
            $id = $_GET['id'] ?? 0;
            if(empty($id)) { throw new Exception("Kullanıcı ID'si belirtilmedi."); }
            $stmt = $pdo->prepare("SELECT id, name, username, role, pozisyon, maas, ise_giris_tarihi FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if(!$user) { throw new Exception("Kullanıcı bulunamadı."); }
            echo json_encode(['status' => 'success', 'data' => $user]);
            break;
        case 'update_user':
            check_auth('admin');
            $id = $_POST['id'] ?? 0;
            if(empty($id)) { throw new Exception("Kullanıcı ID'si bulunamadı."); }
            $sql = "UPDATE users SET name = ?, username = ?, role = ?, pozisyon = ?, maas = ?, ise_giris_tarihi = ? WHERE id = ?";
            $params = [$_POST['name'] ?? null, $_POST['username'] ?? null, $_POST['role'] ?? 'personel', $_POST['pozisyon'] ?? null, $_POST['maas'] ?? null, $_POST['ise_giris_tarihi'] ?? null, $id];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if (!empty($_POST['password'])) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pass_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pass_stmt->execute([$hashed_password, $id]);
            }
            log_activity($pdo, 'update_user', $id, "Kullanıcı: ".$_POST['username']);
            echo json_encode(['status' => 'success', 'message' => 'Personel bilgileri başarıyla güncellendi.']);
            break;
        case 'delete_user':
            check_auth('admin');
            $id = $_POST['id'] ?? 0;
            if(empty($id)) { throw new Exception("Silinecek kullanıcı ID'si bulunamadı."); }
            if ($id == $_SESSION['user_id']) { throw new Exception("Güvenlik nedeniyle kendi hesabınızı silemezsiniz."); }
            log_activity($pdo, 'delete_user', $id);
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Personel başarıyla silindi.']);
            break;
        case 'fetch_users_with_prim':
            check_auth('admin');
            // PERFORMANS OPTİMİZASYONU: Yavaş olan alt sorgular yerine JOIN'ler kullanıldı.
            $sql = "
                SELECT
                    u.id,
                    u.name,
                    u.pozisyon,
                    COALESCE(primler.toplam_hak_edilen, 0) as toplam_hak_edilen,
                    COALESCE(odemeler.toplam_odenen, 0) as toplam_odenen,
                    (COALESCE(primler.toplam_hak_edilen, 0) - COALESCE(odemeler.toplam_odenen, 0)) as kalan_prim
                FROM
                    users u
                LEFT JOIN (
                    SELECT
                        personel,
                        SUM(prim) as toplam_hak_edilen
                    FROM (
                        SELECT alanpersonel as personel, alisprimi as prim FROM buybacks WHERE alanpersonel IS NOT NULL AND alanpersonel != ''
                        UNION ALL
                        SELECT satanpersonel as personel, satisprimi as prim FROM buybacks WHERE satanpersonel IS NOT NULL AND satanpersonel != '' AND durum IN ('Satıldı', 'Ödendi')
                    ) as prim_kaynaklari
                    GROUP BY personel
                ) as primler ON u.name = primler.personel
                LEFT JOIN (
                    SELECT
                        user_id,
                        SUM(tutar) as toplam_odenen
                    FROM
                        personel_prim_odemeleri
                    GROUP BY user_id
                ) as odemeler ON u.id = odemeler.user_id
                ORDER BY u.id ASC
            ";
            $stmt = $pdo->query($sql);
            echo json_encode(['data' => $stmt->fetchAll()]);
            break;
        case 'pay_prim':
            check_auth('admin');
            $user_id = $_POST['user_id'];
            $tutar = (float)$_POST['tutar'];
            $odeme_hesap_id = $_POST['odeme_hesap_id'];
            $aciklama = $_POST['aciklama'];
            if (empty($user_id) || empty($tutar) || empty($odeme_hesap_id)) { throw new Exception("Tüm alanlar zorunludur."); }
            $pdo->beginTransaction();
            $sql_prim = "INSERT INTO personel_prim_odemeleri (user_id, tutar, odeme_hesap_id, aciklama, odeme_tarihi) VALUES (?, ?, ?, ?, ?)";
            $stmt_prim = $pdo->prepare($sql_prim);
            $stmt_prim->execute([$user_id, $tutar, $odeme_hesap_id, $aciklama, date('Y-m-d')]);
            $sql_gider = "INSERT INTO hesap_hareketleri (kaynak_hesap_id, islem_turu, tutar, aciklama, tarih) VALUES (?, 'Gider', ?, ?, ?)";
            $stmt_gider = $pdo->prepare($sql_gider);
            $stmt_gider->execute([$odeme_hesap_id, $tutar, $aciklama, date('Y-m-d')]);
            $sql_bakiye = "UPDATE hesaplar SET bakiye = bakiye - ? WHERE id = ?";
            $stmt_bakiye = $pdo->prepare($sql_bakiye);
            $stmt_bakiye->execute([$tutar, $odeme_hesap_id]);
            $pdo->commit();
            log_activity($pdo, 'pay_prim', $user_id, "Tutar: $tutar, Hesap ID: $odeme_hesap_id");
            echo json_encode(['status' => 'success', 'message' => 'Prim ödemesi başarıyla yapıldı ve muhasebeleştirildi.']);
            break;

             case 'fetch_pending_requests':
            check_auth('admin');
            $izin_sql = "SELECT i.*, u.name FROM personel_izinler i JOIN users u ON i.user_id = u.id WHERE i.durum = 'Onay Bekliyor' ORDER BY i.talep_tarihi ASC";
            $izin_stmt = $pdo->query($izin_sql);
            $avans_sql = "SELECT a.*, u.name FROM personel_avanslar a JOIN users u ON a.user_id = u.id WHERE a.durum = 'Onay Bekliyor' ORDER BY a.talep_tarihi ASC";
            $avans_stmt = $pdo->query($avans_sql);
            echo json_encode([ 'status' => 'success', 'izinler' => $izin_stmt->fetchAll(), 'avanslar' => $avans_stmt->fetchAll() ]);
            break;
        case 'process_hr_request':
            check_auth('admin');
            $request_type = $_POST['request_type'];
            $request_id = $_POST['request_id'];
            $new_status = $_POST['new_status'];
            if (empty($request_type) || empty($request_id) || empty($new_status)) { throw new Exception("Eksik parametre."); }
            $pdo->beginTransaction();
            if ($request_type === 'izin') {
                $stmt = $pdo->prepare("UPDATE personel_izinler SET durum = ? WHERE id = ?");
                $stmt->execute([$new_status, $request_id]);
                log_activity($pdo, 'process_leave_request', $request_id, "Yeni Durum: $new_status");
            } elseif ($request_type === 'avans') {
                $stmt = $pdo->prepare("UPDATE personel_avanslar SET durum = ? WHERE id = ?");
                $stmt->execute([$new_status, $request_id]);
                if ($new_status === 'Onaylandı') {
                    $odeme_hesap_id = $_POST['odeme_hesap_id'] ?? null;
                    if (empty($odeme_hesap_id)) { throw new Exception("Avans ödemesi için kaynak hesap seçilmelidir."); }
                    $avans_stmt = $pdo->prepare("SELECT a.tutar, u.name FROM personel_avanslar a JOIN users u ON a.user_id = u.id WHERE a.id = ?");
                    $avans_stmt->execute([$request_id]);
                    $avans_data = $avans_stmt->fetch();
                    $tutar = $avans_data['tutar'];
                    $personel_adi = $avans_data['name'];
                    $aciklama = "$personel_adi personele avans ödemesi";
                    $gider_sql = "INSERT INTO hesap_hareketleri (kaynak_hesap_id, islem_turu, tutar, aciklama, tarih) VALUES (?, 'Gider', ?, ?, ?)";
                    $gider_stmt = $pdo->prepare($gider_sql);
                    $gider_stmt->execute([$odeme_hesap_id, $tutar, $aciklama, date('Y-m-d')]);
                    $bakiye_sql = "UPDATE hesaplar SET bakiye = bakiye - ? WHERE id = ?";
                    $bakiye_stmt = $pdo->prepare($bakiye_sql);
                    $bakiye_stmt->execute([$tutar, $odeme_hesap_id]);
                }
                log_activity($pdo, 'process_advance_request', $request_id, "Yeni Durum: $new_status");
            }
            $pdo->commit();
            echo json_encode(['status' => 'success', 'message' => 'Talep başarıyla işlendi.']);
            break;

        default:
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Geçersiz API endpointi.']);
            break;
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    if (http_response_code() < 400) { http_response_code(400); }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

