<?php
// O anki sayfanın dosya adını alıyoruz. Örn: "faturalar.php"
$currentPage = basename($_SERVER['PHP_SELF']);

// Muhasebe ve İK alt menülerinin sayfalarını bir dizide topluyoruz.
// Bu, ana menü başlığının da aktif kalmasını sağlar.
$muhasebePages = ['gelirler.php', 'giderler.php', 'raporlar.php'];
$ikPages = ['personel_listesi.php', 'izin_yonetimi.php', 'maas_bordrolari.php'];
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" class="logo"><span>Piksel</span>Pro</a>
    </div>

    <ul class="nav flex-column" id="sidebar-menu">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-home fa-fw me-2"></i>
                <span>Ana Sayfa</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="faturalar.php" class="nav-link <?php echo in_array($currentPage, ['faturalar.php', 'fatura_olustur.php', 'fatura_goruntule.php']) ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar fa-fw me-2"></i>
                <span>Faturalar</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="tedarikciler.php" class="nav-link <?php echo ($currentPage == 'tedarikciler.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-tie fa-fw me-2"></i>
                <span>Tedarikçiler</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo in_array($currentPage, $muhasebePages) ? 'active' : ''; ?>" href="#muhasebeSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($currentPage, $muhasebePages) ? 'true' : 'false'; ?>" aria-controls="muhasebeSubmenu">
                <i class="fas fa-calculator fa-fw me-2"></i>
                <span>Muhasebe</span>
                <i class="fas fa-chevron-down float-end fa-xs mt-1"></i>
            </a>
            <div class="collapse submenu <?php echo in_array($currentPage, $muhasebePages) ? 'show' : ''; ?>" id="muhasebeSubmenu">
                <ul class="nav flex-column ps-3">
                    <li class="nav-item"><a class="nav-link <?php echo ($currentPage == 'hesaplar.php') ? 'active' : ''; ?>" href="hesaplar.php">Hesaplar</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($currentPage == 'faturalar.php') ? 'active' : ''; ?>" href="faturalar.php">Faturalar</a></li>
                    
                </ul>
            </div>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo in_array($currentPage, $ikPages) ? 'active' : ''; ?>" href="#ikSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($currentPage, $ikPages) ? 'true' : 'false'; ?>" aria-controls="ikSubmenu">
                <i class="fas fa-users fa-fw me-2"></i>
                <span>İnsan Kaynakları</span>
                <i class="fas fa-chevron-down float-end fa-xs mt-1"></i>
            </a>
            <div class="collapse submenu <?php echo in_array($currentPage, $ikPages) ? 'show' : ''; ?>" id="ikSubmenu">
                <ul class="nav flex-column ps-3">
                    <li class="nav-item"><a class="nav-link <?php echo ($currentPage == 'admin_hr.php') ? 'active' : ''; ?>" href="admin_hr.php">Personel Listesi</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo ($currentPage == 'reports.php') ? 'active' : ''; ?>" href="reports.php">Performans</a></li>
                  </a></li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item mt-auto">
            <a href="ayarlar.php" class="nav-link <?php echo ($currentPage == 'ayarlar.php') ? 'active' : ''; ?>">
                <i class="fas fa-cog fa-fw me-2"></i>
                <span>Ayarlar</span>
            </a>
        </li>
    </ul>
</aside>