<!-- Cihaz Ekleme Modalı -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addModalLabel">Yeni Cihaz Kaydı</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="add-form">
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="addTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#add-cari" type="button">Cari Bilgileri</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#add-cihaz" type="button">Cihaz Bilgileri</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#add-finans" type="button">Finansal</button></li>
                    </ul>
                    <div class="tab-content pt-3" id="addTabContent">
                        <div class="tab-pane fade show active" id="add-cari" role="tabpanel"><div class="row g-3">
                            <div class="col-md-4"><label class="form-label">İsim</label><input type="text" name="isim" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Soyisim</label><input type="text" name="soyisim" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">TC Kimlik No</label><input type="text" name="tc" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Telefon</label><input type="text" name="telefon" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">IBAN</label><input type="text" name="iban" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Şehir</label><input type="text" name="şehir" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">İlçe</label><input type="text" name="ilçe" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Mahalle/Adres</label><input type="text" name="mahalle" class="form-control"></div>
                        </div></div>
                        <div class="tab-pane fade" id="add-cihaz" role="tabpanel"><div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Alış Tarihi</label><input type="date" name="tarih" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Marka</label><input type="text" name="marka" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Model</label><input type="text" name="model" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">IMEI</label><input type="text" name="imei" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Renk</label><input type="text" name="renk" class="form-control"></div>
                            <div class="col-md-3"><label class="form-label">Hafıza</label><select name="hafiza" class="form-select" required><option value="">Seçiniz...</option><option>32 GB</option><option>64 GB</option><option>128 GB</option><option>256 GB</option><option>512 GB</option><option>1 TB</option></select></div>
                            <div class="col-md-3"><label class="form-label">Kozmetik</label><select name="kozmetik" class="form-select" required><option value="">Seçiniz...</option><option>Mükemmel</option><option>Çok İyi</option><option>İyi</option><option>Outlet</option></select></div>
                            <div class="col-md-3"><label class="form-label">Durum</label><select name="durum" class="form-select" required><option value="">Seçiniz...</option><option>Kargo</option><option>Yenilenecek</option><option>Tamir</option></select></div>
                        </div></div>
                        <div class="tab-pane fade" id="add-finans" role="tabpanel"><div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Maliyet</label><input type="number" step="0.01" name="maliyet" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Kar</label><input type="number" step="0.01" name="kar" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Alan Personel</label><input type="text" name="alanpersonel" class="form-control" required></div>
                            <div class="col-md-3"><label class="form-label">Ödeme Türü</label><select name="odeme" class="form-select"><option value="">Seçiniz...</option><option>Nakit</option><option>Kredi</option></select></div>
                        </div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Cihaz Düzenleme Modalı -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="editModalLabel">Kaydı Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="edit-form">
                <div class="modal-body">
                    <input type="hidden" id="edit_buyback_id" name="id">
                    <ul class="nav nav-tabs" id="editTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#edit-cari" type="button">Cari</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-cihaz" type="button">Cihaz</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-finans" type="button">Finansal</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-personel" type="button">Personel/Prim</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#edit-tedarik" type="button">Tedarikçi</button></li>
                    </ul>
                    <div class="tab-content pt-3" id="editTabContent">
                        <div class="tab-pane fade show active" id="edit-cari" role="tabpanel"><div class="row g-3">
                            <div class="col-md-4"><label class="form-label">İsim</label><input type="text" name="isim" class="form-control" required></div><div class="col-md-4"><label class="form-label">Soyisim</label><input type="text" name="soyisim" class="form-control" required></div><div class="col-md-4"><label class="form-label">TC Kimlik No</label><input type="text" name="tc" class="form-control"></div><div class="col-md-4"><label class="form-label">Telefon</label><input type="text" name="telefon" class="form-control"></div><div class="col-md-4"><label class="form-label">IBAN</label><input type="text" name="iban" class="form-control"></div><div class="col-md-4"><label class="form-label">E-mail</label><input type="email" name="email" class="form-control"></div><div class="col-md-4"><label class="form-label">Şehir</label><input type="text" name="şehir" class="form-control"></div><div class="col-md-4"><label class="form-label">İlçe</label><input type="text" name="ilçe" class="form-control"></div><div class="col-md-4"><label class="form-label">Mahalle/Adres</label><input type="text" name="mahalle" class="form-control"></div>
                        </div></div>
                        <div class="tab-pane fade" id="edit-cihaz" role="tabpanel"><div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Alış Tarihi</label><input type="date" name="tarih" class="form-control" required></div><div class="col-md-3"><label class="form-label">Marka</label><input type="text" name="marka" class="form-control" required></div><div class="col-md-3"><label class="form-label">Model</label><input type="text" name="model" class="form-control" required></div><div class="col-md-3"><label class="form-label">IMEI</label><input type="text" name="imei" class="form-control" required></div><div class="col-md-3"><label class="form-label">Renk</label><input type="text" name="renk" class="form-control"></div><div class="col-md-3"><label class="form-label">Hafıza</label><select name="hafiza" class="form-select" required><option value="">Seçiniz...</option><option>32 GB</option><option>64 GB</option><option>128 GB</option><option>256 GB</option><option>512 GB</option><option>1 TB</option></select></div><div class="col-md-3"><label class="form-label">Kozmetik</label><select name="kozmetik" class="form-select" required><option value="">Seçiniz...</option><option>Mükemmel</option><option>Çok İyi</option><option>İyi</option><option>Outlet</option></select></div><div class="col-md-3"><label class="form-label">Durum</label><select name="durum" class="form-select" required><option value="">Seçiniz...</option><option>Satıldı</option><option>Ödendi</option><option>Kargo</option><option>Tamir</option><option>Yenilenecek</option><option>İade</option></select></div>
                        </div></div>
                       <div class="tab-pane fade" id="edit-finans" role="tabpanel">
    <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Maliyet</label><input type="number" step="0.01" name="maliyet" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Kar</label><input type="number" step="0.01" name="kar" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Gelen Ödeme</label><input type="number" step="0.01" name="gelenodeme" class="form-control"></div>
        
        <!-- YENİ EKLENDİ: Ödemenin hangi hesaba yapılacağını seçmek için. -->
        <div class="col-md-3">
            <label class="form-label">Ödeme Hesabı</label>
            <select name="odeme_hesap_id" class="form-select account-select">
                <!-- Bu alan app.js tarafından doldurulacak -->
            </select>
        </div>

        <div class="col-md-3"><label class="form-label">Ödeme Türü</label><select name="odeme" class="form-select"><option value="">Seçiniz...</option><option>Nakit</option><option>Kredi</option></select></div>
        <div class="col-md-3"><label class="form-label">Kredi No</label><input type="text" name="kredino" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Satış Tarihi</label><input type="date" name="starihi" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Ödeme Tarihi</label><input type="date" name="otarihi" class="form-control"></div>
    </div>
</div>
                        <div class="tab-pane fade" id="edit-personel" role="tabpanel"><div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Alan Personel</label><input type="text" name="alanpersonel" class="form-control"></div><div class="col-md-3"><label class="form-label">Satan Personel</label><input type="text" name="satanpersonel" class="form-control"></div><div class="col-md-3"><label class="form-label">Satış Primi</label><input type="number" step="0.01" name="satisprimi" class="form-control"></div><div class="col-md-3"><label class="form-label">Alış Prim Ödendi mi?</label><select name="aprimodeme" class="form-select"><option value="">Seçiniz</option><option>Evet</option><option>Hayır</option></select></div><div class="col-md-3"><label class="form-label">Satış Prim Ödendi mi?</label><select name="sprimodeme" class="form-select"><option value="">Seçiniz</option><option>Evet</option><option>Hayır</option></select></div><div class="col-md-3"><label class="form-label">Alış Prim Ödeme Tarihi</label><input type="date" name="aprimodemetarihi" class="form-control"></div><div class="col-md-3"><label class="form-label">Satış Prim Ödeme Tarihi</label><input type="date" name="sprimodemetarihi" class="form-control"></div>
                        </div></div>
                        <div class="tab-pane fade" id="edit-tedarik" role="tabpanel"><div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Tedarikçi Ürünü mü?</label><select name="tedarik" class="form-select"><option value="">Seçiniz</option><option>Evet</option><option>Hayır</option></select></div><div class="col-md-9"><label class="form-label">Tedarikçi Adı</label><input type="text" name="tedarikci" class="form-control"></div>
                        </div></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Kayıt Görüntüleme Modalı -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="viewModalLabel">Kayıt Detayları</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body view-modal-body"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button></div>
        </div>
    </div>
</div>

<!-- Notlar Modalı -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="notesModalLabel">Kayıt Notları</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="notes-form">
                <div class="modal-body">
                    <input type="hidden" id="notes_record_id" name="id">
                    <div class="mb-3">
                        <label for="notes_textarea" class="form-label">Notlar</label>
                        <textarea class="form-control" id="notes_textarea" name="notlar" rows="8"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Notları Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Yeni Hesap Ekleme Modalı -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addAccountModalLabel">Yeni Kasa/Banka Hesabı Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="add-account-form">
                <div class="modal-body">
                    <div class="mb-3"><label for="hesap_adi" class="form-label">Hesap Adı</label><input type="text" name="hesap_adi" class="form-control" required></div>
                    <div class="mb-3"><label for="hesap_turu" class="form-label">Hesap Türü</label><select name="hesap_turu" class="form-select" required><option value="Nakit Kasa">Nakit Kasa</option><option value="Banka Hesabı">Banka Hesabı</option></select></div>
                    <div class="mb-3"><label for="banka_adi" class="form-label">Banka Adı (varsa)</label><input type="text" name="banka_adi" class="form-control"></div>
                    <div class="mb-3"><label for="iban" class="form-label">IBAN (varsa)</label><input type="text" name="iban" class="form-control"></div>
                    <div class="mb-3"><label for="bakiye" class="form-label">Açılış Bakiyesi</label><input type="number" step="0.01" name="bakiye" class="form-control" value="0" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Hesabı Oluştur</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Gelir/Gider Ekleme Modalı -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="addTransactionModalLabel">İşlem Ekle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="add-transaction-form">
                <input type="hidden" id="transaction_type" name="islem_turu">
                <div class="modal-body">
                    <div class="mb-3 kaynak-grup"><label class="form-label">Kaynak Hesap</label><select name="kaynak_hesap_id" class="form-select account-select"></select></div>
                    <div class="mb-3 hedef-grup"><label class="form-label">Hedef Hesap</label><select name="hedef_hesap_id" class="form-select account-select"></select></div>
                    <div class="mb-3"><label class="form-label">Tutar (₺)</label><input type="number" step="0.01" name="tutar" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Açıklama</label><input type="text" name="aciklama" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Tarih</label><input type="date" name="tarih" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">İşlemi Kaydet</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Transfer Modalı -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="transferModalLabel">Hesaplar Arası Transfer</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="transfer-form">
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Kaynak Hesap</label><select name="kaynak_hesap_id" class="form-select account-select" required></select></div>
                    <div class="mb-3"><label class="form-label">Hedef Hesap</label><select name="hedef_hesap_id" class="form-select account-select" required></select></div>
                    <div class="mb-3"><label class="form-label">Tutar (₺)</label><input type="number" step="0.01" name="tutar" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Açıklama</label><input type="text" name="aciklama" class="form-control" value="Hesaplar arası virman" required></div>
                    <div class="mb-3"><label class="form-label">Tarih</label><input type="date" name="tarih" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Transferi Gerçekleştir</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: İzin Talep Modalı -->
<div class="modal fade" id="leaveRequestModal" tabindex="-1" aria-labelledby="leaveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="leaveRequestModalLabel">İzin Talebi Oluştur</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="leaveRequestForm">
                <div class="modal-body">
                    <div class="mb-3"><label for="izin_turu" class="form-label">İzin Türü</label><select name="izin_turu" class="form-select" required><option value="Yıllık İzin">Yıllık İzin</option><option value="Mazeret İzni">Mazeret İzni</option><option value="Raporlu">Raporlu</option></select></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="baslangic_tarihi" class="form-label">Başlangıç Tarihi</label><input type="date" name="baslangic_tarihi" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label for="bitis_tarihi" class="form-label">Bitiş Tarihi</label><input type="date" name="bitis_tarihi" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label for="aciklama" class="form-label">Açıklama (Opsiyonel)</label><textarea name="aciklama" class="form-control" rows="3"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Talep Gönder</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Avans Talep Modalı -->
<div class="modal fade" id="advanceRequestModal" tabindex="-1" aria-labelledby="advanceRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="advanceRequestModalLabel">Avans Talebi Oluştur</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="advanceRequestForm">
                <div class="modal-body">
                    <div class="mb-3"><label for="tutar" class="form-label">Talep Edilen Tutar (₺)</label><input type="number" step="0.01" name="tutar" class="form-control" required></div>
                    <div class="mb-3"><label for="aciklama_avans" class="form-label">Açıklama</label><textarea name="aciklama" id="aciklama_avans" class="form-control" rows="3" required></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button><button type="submit" class="btn btn-primary">Talep Gönder</button></div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Personel Düzenleme Modalı -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="editUserModalLabel">Personeli Düzenle</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <form id="edit-user-form">
                <input type="hidden" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Ad Soyad</label><input type="text" name="name" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Kullanıcı Adı</label><input type="text" name="username" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Rol</label><select name="role" class="form-select" required><option value="personel">Personel</option><option value="admin">Admin</option></select></div>
                        <div class="col-md-6"><label class="form-label">Pozisyon</label><input type="text" name="pozisyon" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Maaş (₺)</label><input type="number" step="0.01" name="maas" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">İşe Giriş Tarihi</label><input type="date" name="ise_giris_tarihi" class="form-control"></div>
                        <div class="col-12"><hr><p class="text-muted">Şifre değiştirmek için yeni şifreyi girin. Boş bırakırsanız şifre değişmez.</p></div>
                        <div class="col-md-6"><label class="form-label">Yeni Şifre</label><input type="password" name="password" class="form-control" placeholder="Değiştirmek için doldurun"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Prim Ödeme Modalı -->
<div class="modal fade" id="payPrimModal" tabindex="-1" aria-labelledby="payPrimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payPrimModalLabel">Personel Prim Ödemesi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pay-prim-form">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="prim_user_id">
                    <div class="text-center mb-3">
                        <h6 id="prim_personel_adi" class="mb-1"></h6>
                        <small class="text-muted">Kalan Prim Hakkı: <strong id="prim_kalan_bakiye" class="text-success">0,00 ₺</strong></small>
                    </div>
                    <div class="mb-3">
                        <label for="prim_tutar" class="form-label">Ödenecek Tutar (₺)</label>
                        <input type="number" step="0.01" name="tutar" id="prim_tutar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="prim_odeme_hesabi" class="form-label">Ödeme Kasası/Bankası</label>
                        <select name="odeme_hesap_id" id="prim_odeme_hesabi" class="form-select account-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label for="prim_aciklama" class="form-label">Açıklama</label>
                        <input type="text" name="aciklama" id="prim_aciklama" class="form-control" value="Personel prim ödemesi" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                    <button type="submit" class="btn btn-success">Ödemeyi Yap ve Muhasebeleştir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- YENİ EKLENDİ: Avans Ödeme ve Onaylama Modalı -->
<div class="modal fade" id="approveAdvanceModal" tabindex="-1" aria-labelledby="approveAdvanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveAdvanceModalLabel">Avans Ödemesini Onayla ve Muhasebeleştir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approve-advance-form">
                <div class="modal-body">
                    <input type="hidden" name="request_id" id="avans_request_id">
                    <input type="hidden" name="user_id" id="avans_user_id">
                    <div class="mb-3">
                        <label for="avans_tutar" class="form-label">Ödenecek Tutar (₺)</label>
                        <input type="number" step="0.01" name="tutar" id="avans_tutar" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="avans_odeme_hesabi" class="form-label">Ödemenin Yapılacağı Kasa/Banka</label>
                        <select name="odeme_hesap_id" id="avans_odeme_hesabi" class="form-select account-select" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-success">Onayla ve Ödemeyi Yap</button>
                </div>
            </form>
        </div>
    </div>
</div>


