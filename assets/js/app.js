$(document).ready(function() {
    let dataTable;
    let profitChart;

    // --- YARDIMCI FONKSİYONLAR ---
    function currencyFormatter(data, def = '0,00 ₺') {
        const value = parseFloat(data);
        if (isNaN(value)) return def;
        return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value);
    }

    function showToast(title, message, icon = 'success') {
        Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' });
    }
    
    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function getStatusClass(status) {
        const classes = { 'Satıldı': 'warning', 'Ödendi': 'success', 'İade': 'danger', 'Kargo': 'info', 'Tamir': 'secondary', 'Yenilenecek': 'primary', 'M1': 'dark', 'Optimum': 'light text-dark' };
        return classes[status] || 'light';
    }

    async function loadAllAccountsIntoSelects() {
        try {
            const response = await fetch('api.php?action=fetch_accounts');
            const result = await response.json();
            if (result.status === 'success') {
                const accountSelects = $('.account-select');
                accountSelects.empty().append('<option value="">Hesap Seçiniz...</option>');
                result.data.forEach(acc => {
                    accountSelects.append(`<option value="${acc.id}">${acc.hesap_adi} (${currencyFormatter(acc.bakiye)})</option>`);
                });
            }
        } catch (error) {
            console.error("Tüm hesaplar yüklenemedi:", error);
        }
    }

    // --- ANA YENİLEME FONKSİYONLARI ---
    function refreshDashboard(filters = {}) {
        const queryParams = new URLSearchParams(filters).toString();
        const tableApiUrl = `api.php?action=fetch&${queryParams}`;

        fetchSummaryData(queryParams);
        fetchChartData(queryParams);

        if ($.fn.DataTable.isDataTable('#buybackTable')) {
            dataTable.ajax.url(tableApiUrl).load();
        } else {
            initializeDataTable(tableApiUrl);
        }
    }

    async function fetchSummaryData(queryParams) {
        try {
            const response = await fetch(`api.php?action=fetch_summary&${queryParams}`);
            const result = await response.json();
            if (result.status === 'success') {
                $('#total-records').text(result.summary.total_records || 0);
                $('#sold-receivable').text(currencyFormatter(result.summary.sold_receivable));
                $('#paid-receivable').text(currencyFormatter(result.summary.paid_receivable));
            }
        } catch (error) { console.error("Özet verileri alınamadı:", error); }
    }
    
    async function fetchChartData(queryParams) {
        try {
            const response = await fetch(`api.php?action=fetch_chart_data&${queryParams}`);
            const result = await response.json();
            if (result.status === 'success') {
                const labels = result.data.map(item => item.month);
                const data = result.data.map(item => item.total_profit);
                renderProfitChart(labels, data);
            }
        } catch(error) { console.error("Grafik verileri alınamadı:", error); }
    }

    function renderProfitChart(labels, data) {
        const ctx = document.getElementById('profitChart').getContext('2d');
        if(profitChart) { profitChart.destroy(); }
        profitChart = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Aylık Toplam Kar', data: data, backgroundColor: 'rgba(108, 95, 252, 0.6)', borderColor: 'rgba(108, 95, 252, 1)', borderWidth: 1 }] },
            options: { scales: { y: { beginAtZero: true } } }
        });
    }

    function initializeDataTable(apiUrl) {
        dataTable = $('#buybackTable').DataTable({
            processing: true,
            ajax: { url: apiUrl, dataSrc: 'data', error: (xhr) => showToast('Hata', xhr.responseJSON?.message || 'Veri yüklenemedi.', 'error') },
            columns: [
                { data: null, orderable: false, searchable: false, render: (data, type, row) => `<input type="checkbox" class="row-checkbox" value="${row.id}">` },
                { data: 'id' }, 
                { data: 'tarih' },
                { data: 'cari', title: 'Cari' },
                { data: 'marka_model', title: 'Marka/Model' },
                { data: 'imei' }, 
                { data: 'maliyet', render: currencyFormatter }, 
                { data: 'kar', render: currencyFormatter },
                { data: 'kalan_alacak', render: currencyFormatter }, 
                { data: 'durum', render: (data) => `<span class="badge bg-${getStatusClass(data)}">${data || 'Belirsiz'}</span>` },
                {
                    data: 'id', title: 'İşlemler', orderable: false, searchable: false,
                    render: function(data) {
                        const viewBtn = `<button class="btn btn-outline-secondary btn-sm view-btn" data-id="${data}" title="Görüntüle"><i class="fas fa-eye"></i></button>`;
                        const editBtn = `<button class="btn btn-outline-primary btn-sm edit-btn" data-id="${data}" title="Düzenle"><i class="fas fa-edit"></i></button>`;
                        const notesBtn = `<button class="btn btn-outline-dark btn-sm notes-btn" data-id="${data}" title="Notlar"><i class="fas fa-sticky-note"></i></button>`;
                        const deleteBtn = `<button class="btn btn-outline-danger btn-sm delete-btn" data-id="${data}" title="Sil"><i class="fas fa-trash"></i></button>`;
                        
                        const statusOptions = ['Satıldı', 'Ödendi', 'Kargo', 'Tamir', 'Yenilenecek', 'İade', 'M1', 'Optimum'];
                        let statusDropdownItems = statusOptions.map(s => `<li><a class="dropdown-item status-update" href="#" data-id="${data}" data-status="${s}">${s}</a></li>`).join('');
                        
                        const statusBtn = `
                            <div class="btn-group">
                              <button type="button" class="btn btn-outline-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Durum Değiştir">
                                <i class="fas fa-sync-alt"></i>
                              </button>
                              <ul class="dropdown-menu">${statusDropdownItems}</ul>
                            </div>`;

                        return `<div class="btn-group">${viewBtn}${editBtn}${notesBtn}${statusBtn}${deleteBtn}</div>`;
                    }
                }
            ],
            order: [[1, 'desc']],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>" + "<'row'<'col-sm-12 mt-2'B>>",
            buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ].map(type => ({ extend: type, text: `<i class="fas fa-file-${type === 'print' ? 'print' : (type === 'copy' ? 'copy' : 'alt')}"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}`, className: 'btn-sm' })),
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/tr.json' }
        });
    }

    // --- MODAL VE FORM İŞLEMLERİ ---
    const addModal = new bootstrap.Modal(document.getElementById('addModal'));
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
    const notesModal = new bootstrap.Modal(document.getElementById('notesModal'));

    $('#add-new-record-btn').on('click', () => { $('#add-form')[0].reset(); addModal.show(); });

    $('#add-form, #edit-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const action = form.attr('id') === 'add-form' ? 'add_record' : 'edit_record';
        const formData = form.serialize() + '&action=' + action;
        const currentModal = bootstrap.Modal.getInstance(form.closest('.modal'));

        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                if (currentModal) currentModal.hide();
                showToast('Başarılı', response.message);
                dataTable.ajax.reload(null, false);
                fetchSummaryData(new URLSearchParams($('#filter-form').serialize()).toString());
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json').fail((xhr) => showToast('Hata', xhr.responseJSON?.message || 'Sunucu hatası.', 'error'));
    });
    
    $('#notes-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=update_notes';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                notesModal.hide();
                showToast('Başarılı', response.message);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json').fail((xhr) => showToast('Hata', xhr.responseJSON?.message || 'Sunucu hatası.', 'error'));
    });

    function handleEditRecord(id) {
        $.get('api.php', { action: 'get_single', id }, (response) => {
            if (response.status === 'success' && response.data) {
                const form = $('#edit-form');
                form[0].reset();
                loadAllAccountsIntoSelects().then(() => {
                    $.each(response.data, (key, value) => {
                        const field = form.find(`[name="${key}"]`);
                        if (field.length) field.val(value);
                    });
                });
                $('#editModalLabel').text(`Kaydı Düzenle (ID: ${id})`);
                editModal.show();
            } else { showToast('Hata', 'Kayıt bulunamadı.', 'error'); }
        }, 'json').fail(() => showToast('Hata', 'Kayıt bilgileri alınamadı.', 'error'));
    }

    function handleViewRecord(id) {
        $.get('api.php', { action: 'get_single', id }, (response) => {
            if (response.status === 'success' && response.data) {
                const d = response.data;
                const modalBody = $('#viewModal .view-modal-body');

                const tabs = `
                    <ul class="nav nav-tabs" id="viewTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#view-finans" type="button">Finansal</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#view-cihaz" type="button">Cihaz</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#view-cari" type="button">Cari</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#view-personel" type="button">Personel & Prim</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#view-notlar" type="button">Notlar</button></li>
                    </ul>`;

                const tabContent = `
                    <div class="tab-content pt-3" id="viewTabContent">
                        <div class="tab-pane fade show active" id="view-finans" role="tabpanel">
                            <dl class="row">
                                <dt class="col-sm-3">Maliyet</dt><dd class="col-sm-3">${currencyFormatter(d.maliyet)}</dd>
                                <dt class="col-sm-3">Alış Kâr Oranı</dt><dd class="col-sm-3">${d.moran || '-'}</dd>
                                <dt class="col-sm-3">Kar</dt><dd class="col-sm-3">${currencyFormatter(d.kar)}</dd>
                                <dt class="col-sm-3">Satış Kâr Oranı</dt><dd class="col-sm-3">${d.soran || '-'}</dd>
                                <dt class="col-sm-3">Satış Fiyatı</dt><dd class="col-sm-3">${currencyFormatter(d.sfiyat)}</dd>
                                <dt class="col-sm-3">Gelen Ödeme</dt><dd class="col-sm-3">${currencyFormatter(d.gelenodeme)}</dd>
                                <dt class="col-sm-3"><strong>Kalan Alacak</strong></dt><dd class="col-sm-9"><strong>${currencyFormatter(d.kalan_alacak)}</strong></dd>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="view-cihaz" role="tabpanel">
                            <dl class="row">
                                <dt class="col-sm-3">Marka/Model</dt><dd class="col-sm-9">${d.marka_model || '-'}</dd>
                                <dt class="col-sm-3">IMEI</dt><dd class="col-sm-9">${d.imei || '-'}</dd>
                                <dt class="col-sm-3">Hafıza / Renk</dt><dd class="col-sm-9">${d.hafiza || '-'} / ${d.renk || '-'}</dd>
                                <dt class="col-sm-3">Kozmetik</dt><dd class="col-sm-9">${d.kozmetik || '-'}</dd>
                                <dt class="col-sm-3">Alış Tarihi</dt><dd class="col-sm-9">${d.tarih || '-'}</dd>
                                <dt class="col-sm-3">Satış Tarihi</dt><dd class="col-sm-9">${d.starihi || '-'}</dd>
                                <dt class="col-sm-3">Durum</dt><dd class="col-sm-9"><span class="badge bg-${getStatusClass(d.durum)}">${d.durum || '-'}</span></dd>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="view-cari" role="tabpanel">
                            <dl class="row">
                                <dt class="col-sm-3">İsim Soyisim</dt><dd class="col-sm-9">${d.cari || '-'}</dd>
                                <dt class="col-sm-3">TC Kimlik No</dt><dd class="col-sm-9">${d.tc || '-'}</dd>
                                <dt class="col-sm-3">Telefon</dt><dd class="col-sm-9">${d.telefon || '-'}</dd>
                                <dt class="col-sm-3">Adres</dt><dd class="col-sm-9">${(d.mahalle || '')} ${(d.ilçe || '')} ${(d.şehir || '')}</dd>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="view-personel" role="tabpanel">
                            <dl class="row">
                                <dt class="col-sm-3">Alan Personel</dt><dd class="col-sm-9">${d.alanpersonel || '-'}</dd>
                                <dt class="col-sm-3">Satan Personel</dt><dd class="col-sm-9">${d.satanpersonel || '-'}</dd>
                                <dt class="col-sm-3">Alış Primi</dt><dd class="col-sm-9">${currencyFormatter(d.alisprimi)}</dd>
                                <dt class="col-sm-3">Satış Primi</dt><dd class="col-sm-9">${currencyFormatter(d.satisprimi)}</dd>
                            </dl>
                        </div>
                        <div class="tab-pane fade" id="view-notlar" role="tabpanel">
                            <p class="p-2 rounded" style="white-space: pre-wrap; background-color: #f8f9fa;">${d.notlar || 'Bu kayıt için not bulunmamaktadır.'}</p>
                        </div>
                    </div>`;

                modalBody.html(tabs + tabContent);
                $('#viewModalLabel').text(`Kayıt Detayları (ID: ${id})`);
                viewModal.show();
            } else { showToast('Hata', 'Kayıt detayları alınamadı.', 'error'); }
        }, 'json');
    }

    function handleNotesModal(id) {
        $.get('api.php', { action: 'get_single', id: id }, (response) => {
            if (response.status === 'success' && response.data) {
                $('#notes_record_id').val(id);
                $('#notes_textarea').val(response.data.notlar);
                $('#notesModalLabel').text(`Notlar (Kayıt ID: ${id})`);
                notesModal.show();
            } else { showToast('Hata', 'Notlar için kayıt bilgisi alınamadı.', 'error'); }
        }, 'json');
    }

    // --- OLAY DİNLEYİCİLERİ (EVENT LISTENERS) ---
    const debouncedFilter = debounce(() => $('#filter-form').trigger('submit'), 500);
    $('#filter-form').on('input change', 'input, select', debouncedFilter);
    $('#filter-form').on('submit', (e) => { e.preventDefault(); refreshDashboard(Object.fromEntries(new FormData(e.target))); });
    $('#reset-filter').on('click', () => { $('#filter-form')[0].reset(); refreshDashboard(); });

    $('#buybackTable tbody').on('click', '.edit-btn', function() { handleEditRecord($(this).data('id')); });
    $('#buybackTable tbody').on('click', '.view-btn', function() { handleViewRecord($(this).data('id')); });
    $('#buybackTable tbody').on('click', '.notes-btn', function() { handleNotesModal($(this).data('id')); });
    $('#buybackTable tbody').on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        Swal.fire({ title: 'Emin misiniz?', text: `ID:${id} nolu kayıt kalıcı olarak silinecektir!`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Evet, sil!', cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api.php', { action: 'delete', id }, (response) => {
                    if (response.status === 'success') {
                        showToast('Başarılı', response.message);
                        dataTable.ajax.reload(null, false);
                    } else { showToast('Hata', response.message, 'error'); }
                }, 'json');
            }
        });
    });
    
    $('#buybackTable tbody').on('click', '.status-update', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const status = $(this).data('status');
        $.post('api.php', { action: 'bulk_status_update', ids: [id], status: status }, (response) => {
            if (response.status === 'success') {
                showToast('Başarılı', `ID:${id} durumu güncellendi.`);
                dataTable.ajax.reload(null, false);
            } else { showToast('Hata', response.message, 'error'); }
        }, 'json');
    });

    // Toplu İşlemler
    function updateBulkActionButtons() {
        const anyChecked = $('.row-checkbox:checked').length > 0;
        $('#bulk-delete-btn, #bulk-status-btn').prop('disabled', !anyChecked);
    }
    $('#select-all-checkbox').on('change', function() { $('.row-checkbox').prop('checked', this.checked).trigger('change'); });
    $('#buybackTable').on('change', '.row-checkbox', updateBulkActionButtons);

    function handleBulkAction(action, data) {
        Swal.fire({ title: 'Emin misiniz?', text: `${data.ids.length} kayıt üzerinde işlem yapılacak!`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Evet, onayla!', cancelButtonText: 'İptal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api.php', { action, ...data }, (response) => {
                    if (response.status === 'success') {
                        showToast('Başarılı', response.message);
                        $('#select-all-checkbox').prop('checked', false);
                        dataTable.ajax.reload(null, false);
                        updateBulkActionButtons();
                    } else { showToast('Hata', response.message, 'error'); }
                }, 'json');
            }
        });
    }
    $('#bulk-delete-btn').on('click', () => {
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if(ids.length > 0) handleBulkAction('bulk_delete', { ids });
    });
    $('.bulk-status-update').on('click', function(e) {
        e.preventDefault();
        const status = $(this).data('status');
        const ids = $('.row-checkbox:checked').map(function() { return $(this).val(); }).get();
        if(ids.length > 0) handleBulkAction('bulk_status_update', { ids, status });
    });

    // Karanlık Mod
    const darkModeToggle = $('#darkModeToggle');
    function applyDarkMode(isDarkMode) {
        $('body').toggleClass('dark-mode', isDarkMode);
    }
    
    if (localStorage.getItem('darkMode') === 'enabled') {
        darkModeToggle.prop('checked', true);
        applyDarkMode(true);
    }

    darkModeToggle.on('change', function() {
        applyDarkMode(this.checked);
        localStorage.setItem('darkMode', this.checked ? 'enabled' : 'disabled');
    });

    // Sayfa ilk yüklendiğinde paneli başlat
    loadAllAccountsIntoSelects();
    refreshDashboard();
});
