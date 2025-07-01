@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Tambah Barang Keluar</div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form id="sales-form" method="POST" action="{{ route('sales.store') }}">
            @csrf

            <div class="form-group">
                <label for="imei">Scan / Masukkan IMEI</label>
                <input type="text" id="imei-input" class="form-control" placeholder="Masukkan IMEI lalu tekan Enter" autocomplete="off">
                <small class="text-muted">Tekan Enter untuk mencari produk berdasarkan IMEI</small>
            </div>

            <!-- Area untuk menampilkan produk yang ditemukan -->
            <div id="product-preview" class="mt-3" style="display: none;">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Produk Ditemukan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>IMEI:</strong> <span id="preview-imei"></span><br>
                                <strong>Produk:</strong> <span id="preview-product"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale-price">Harga Jual (Rp)</label>
                                    <input type="text" id="sale-price" class="form-control" placeholder="0">
                                    <small class="text-muted">Harga default: <span id="default-price"></span></small>
                                </div>
                                <button type="button" class="btn btn-success" id="add-item-btn">Tambah ke Keranjang</button>
                                <button type="button" class="btn btn-secondary" id="cancel-btn">Batal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <table class="table mt-3" id="produk-table">
                <thead>
                    <tr>
                        <th>IMEI</th>
                        <th>Nama Produk</th>
                        <th>Harga Jual</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr>
                        <th colspan="2">Total</th>
                        <th id="total-price">Rp 0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary" id="submit-btn" disabled>
                    Simpan Barang Keluar
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let selectedItems = [];
    let totalPrice = 0;
    let currentInventory = null;

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    // Fungsi untuk memformat angka dengan separator koma
    function formatNumberWithComma(value) {
        // Hapus semua karakter non-digit
        let numericValue = value.toString().replace(/\D/g, '');

        // Format dengan separator koma
        return numericValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Fungsi untuk mengubah format koma menjadi angka
    function parseFormattedNumber(value) {
        return parseInt(value.toString().replace(/,/g, '')) || 0;
    }

    document.getElementById('imei-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let imei = this.value.trim();
            if (!imei) return alert('IMEI tidak boleh kosong');
            if (selectedItems.find(item => item.imei === imei)) {
                alert('IMEI ini sudah ditambahkan!');
                this.value = '';
                return;
            }
            searchProductByImei(imei);
        }
    });

    function searchProductByImei(imei) {
        fetch(`/search-by-imei?imei=${encodeURIComponent(imei)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentInventory = data.inventory;
                    showProductPreview(data.inventory);
                } else {
                    alert(data.message || 'IMEI tidak ditemukan');
                    document.getElementById('imei-input').value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
                document.getElementById('imei-input').value = '';
            });
    }

    function showProductPreview(inventory) {
        const brandName = inventory.product.brand?.name || '';
        const productName = inventory.product.name || '';
        const defaultPrice = inventory.product.price ?? 0;

        document.getElementById('preview-imei').textContent = inventory.imei;
        document.getElementById('preview-product').textContent = `${brandName} ${productName}`;
        document.getElementById('default-price').textContent = formatRupiah(defaultPrice);
        document.getElementById('sale-price').value = formatNumberWithComma(defaultPrice);

        document.getElementById('product-preview').style.display = 'block';
        document.getElementById('imei-input').disabled = true;
        document.getElementById('sale-price').focus();
        document.getElementById('sale-price').select();
    }

    // Event listener untuk format input harga saat mengetik
    document.getElementById('sale-price').addEventListener('input', function(e) {
        let value = e.target.value;
        let cursorPosition = e.target.selectionStart;
        let originalLength = value.length;

        // Format dengan koma
        let formattedValue = formatNumberWithComma(value);

        // Update nilai input
        e.target.value = formattedValue;

        // Sesuaikan posisi cursor
        let newLength = formattedValue.length;
        let lengthDiff = newLength - originalLength;
        e.target.setSelectionRange(cursorPosition + lengthDiff, cursorPosition + lengthDiff);
    });

    // Event listener untuk membatasi input hanya angka dan koma
    document.getElementById('sale-price').addEventListener('keypress', function(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }

        // Hanya izinkan angka (0-9)
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }

        // Handle Enter key untuk add item
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('add-item-btn').click();
        }
    });

    document.getElementById('add-item-btn').addEventListener('click', function() {
        if (!currentInventory) return;

        const salePriceFormatted = document.getElementById('sale-price').value;
        const salePrice = parseFormattedNumber(salePriceFormatted);

        if (!salePrice || salePrice <= 0) return alert('Harga jual tidak valid');

        addItemToCart(currentInventory, salePrice);
        resetForm();
    });

    document.getElementById('cancel-btn').addEventListener('click', function() {
        resetForm();
    });

    function addItemToCart(inventory, salePrice) {
        const item = {
            imei: inventory.imei,
            product: inventory.product,
            price: salePrice
        };

        selectedItems.push(item);

        let tbody = document.querySelector('#produk-table tbody');
        let row = document.createElement('tr');
        row.setAttribute('data-imei', inventory.imei);
        row.innerHTML = `
            <td>${inventory.imei}</td>
            <td>${inventory.product.brand?.name || ''} ${inventory.product.name || ''}</td>
            <td>${formatRupiah(salePrice)}</td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem('${inventory.imei}')">Hapus</button></td>
        `;
        tbody.appendChild(row);

        updateTotal();
        updateHiddenInputs();
    }

    function removeItem(imei) {
        selectedItems = selectedItems.filter(item => item.imei !== imei);
        document.querySelector(`tr[data-imei="${imei}"]`).remove();
        updateTotal();
        updateHiddenInputs();
    }

    function resetForm() {
        document.getElementById('product-preview').style.display = 'none';
        document.getElementById('imei-input').disabled = false;
        document.getElementById('imei-input').value = '';
        document.getElementById('sale-price').value = '';
        document.getElementById('imei-input').focus();
        currentInventory = null;
    }

    function updateTotal() {
        totalPrice = selectedItems.reduce((sum, item) => sum + item.price, 0);
        document.getElementById('total-price').textContent = formatRupiah(totalPrice);
        document.getElementById('submit-btn').disabled = selectedItems.length === 0;
    }

    function updateHiddenInputs() {
        document.querySelectorAll('input[name^="items"]').forEach(input => input.remove());

        let form = document.getElementById('sales-form');
        selectedItems.forEach((item, index) => {
            let imeiInput = document.createElement('input');
            imeiInput.type = 'hidden';
            imeiInput.name = `items[${index}][imei]`;
            imeiInput.value = item.imei;
            form.appendChild(imeiInput);

            let priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = `items[${index}][price]`;
            priceInput.value = item.price;
            form.appendChild(priceInput);
        });
    }

    document.getElementById('sales-form').addEventListener('submit', function(e) {
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Tambahkan minimal satu item');
            return false;
        }

        for (let item of selectedItems) {
            if (!item.price || item.price <= 0) {
                e.preventDefault();
                alert('Semua item harus memiliki harga valid');
                return false;
            }
        }

        return true;
    });
</script>
@endpush
