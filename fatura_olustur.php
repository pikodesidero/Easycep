<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$pageTitle = "Yeni Fatura Oluştur";
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="page">
    <?php include 'sidebar.php'; ?>
    <main class="main-content">
        <header class="header">
            <h1 class="h3 mb-0 text-gray-800"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <a href="faturalar.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Fatura Listesine Dön</a>
        </header>

        <form id="invoice-form">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header"><h6 class="m-0">Fatura Kalemleri</h6></div>
                        <div class="card-body">
                            <table class="table" id="invoice-items-table">
                                <thead>
                                    <tr>
                                        <th>Ürün/Hizmet Açıklaması</th>
                                        <th style="width: 100px;">Miktar</th>
                                        <th style="width: 150px;">Birim Fiyat</th>
                                        <th style="width: 120px;">KDV (%)</th>
                                        <th style="width: 150px;">Toplam</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Kalemler buraya eklenecek -->
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-primary" id="add-item-btn"><i class="fas fa-plus"></i> Kalem Ekle</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header"><h6 class="m-0">Müşteri ve Fatura Bilgileri</h6></div>
                        <div class="card-body">
                            <div class="mb-3"><label class="form-label">Müşteri Adı</label><input type="text" name="musteri_adi" class="form-control" required></div>
                            <div class="mb-3"><label class="form-label">Fatura No</label><input type="text" name="fatura_no" class="form-control" required></div>
                            <div class="row">
                                <div class="col-6"><label class="form-label">Fatura Tarihi</label><input type="date" name="fatura_tarihi" class="form-control" value="<?php echo date('Y-m-d'); ?>" required></div>
                                <div class="col-6"><label class="form-label">Vade Tarihi</label><input type="date" name="vade_tarihi" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm">
                        <div class="card-header"><h6 class="m-0">Fatura Özeti</h6></div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-6">Ara Toplam:</dt><dd class="col-6 text-end" id="subtotal">0,00 ₺</dd>
                                <dt class="col-6">KDV Toplamı:</dt><dd class="col-6 text-end" id="tax-total">0,00 ₺</dd>
                                <hr class="my-2">
                                <dt class="col-6 fs-5">Genel Toplam:</dt><dd class="col-6 text-end fs-5 fw-bold" id="grand-total">0,00 ₺</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-2"></i>Faturayı Kaydet</button>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    function currencyFormatter(num) { return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(num || 0); }
    function showToast(title, message, icon = 'success') { Swal.fire({ title, text: message, icon, timer: 3000, timerProgressBar: true, showConfirmButton: false, toast: true, position: 'top-end' }); }

    let itemIndex = 0;

    function addInvoiceItem() {
        const itemHtml = `
            <tr class="invoice-item">
                <td><input type="text" name="items[${itemIndex}][aciklama]" class="form-control form-control-sm" required></td>
                <td><input type="number" name="items[${itemIndex}][miktar]" class="form-control form-control-sm item-calc" value="1" step="1"></td>
                <td><input type="number" name="items[${itemIndex}][birim_fiyat]" class="form-control form-control-sm item-calc" step="0.01"></td>
                <td><select name="items[${itemIndex}][kdv_orani]" class="form-select form-select-sm item-calc"><option value="20">20%</option><option value="10">10%</option><option value="1">1%</option><option value="0">0%</option></select></td>
                <td><input type="text" class="form-control form-control-sm item-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-item-btn"><i class="fas fa-trash"></i></button></td>
            </tr>`;
        $('#invoice-items-table tbody').append(itemHtml);
        itemIndex++;
    }

    function calculateTotals() {
        let subtotal = 0;
        let taxTotal = 0;
        $('.invoice-item').each(function() {
            const row = $(this);
            const qty = parseFloat(row.find('.item-calc[name*="miktar"]').val()) || 0;
            const price = parseFloat(row.find('.item-calc[name*="birim_fiyat"]').val()) || 0;
            const taxRate = parseFloat(row.find('.item-calc[name*="kdv_orani"]').val()) || 0;
            
            const lineTotal = qty * price;
            const taxAmount = lineTotal * (taxRate / 100);
            
            subtotal += lineTotal;
            taxTotal += taxAmount;
            
            row.find('.item-total').val(currencyFormatter(lineTotal + taxAmount));
        });
        
        $('#subtotal').text(currencyFormatter(subtotal));
        $('#tax-total').text(currencyFormatter(taxTotal));
        $('#grand-total').text(currencyFormatter(subtotal + taxTotal));
    }

    $('#add-item-btn').on('click', addInvoiceItem);
    $('#invoice-items-table').on('click', '.remove-item-btn', function() { $(this).closest('tr').remove(); calculateTotals(); });
    $('#invoice-items-table').on('input', '.item-calc', calculateTotals);

    $('#invoice-form').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=save_invoice';
        $.post('api.php', formData, (response) => {
            if (response.status === 'success') {
                showToast('Başarılı!', response.message, 'success');
                setTimeout(() => { window.location.href = 'faturalar.php'; }, 1500);
            } else {
                showToast('Hata!', response.message, 'error');
            }
        }, 'json');
    });

    addInvoiceItem(); // Sayfa açıldığında bir boş satır ekle
});
</script>
</body>
</html>
