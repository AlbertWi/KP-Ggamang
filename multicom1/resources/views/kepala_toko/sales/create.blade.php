@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Tambah Penjualan</div>
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
                                <strong>Produk:</strong> <span id="preview-product"></span><br>
                                <strong>Deskripsi:</strong> <span id="preview-description"></span>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sale-price">Harga Jual (Rp)</label>
                                    <input type="number" id="sale-price" class="form-control" min="0" step="1000">
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
                    Simpan Penjualan
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

    // Format rupiah
    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(value);
    }

    // Parse rupiah string ke number
    function parseRupiah(rp) {
        return parseInt(rp.toString().replace(/[^\d]/g, '')) || 0;
    }

    // Event listener untuk input IMEI
    document.getElementById('imei-input').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let imei = this.value.trim();

            if (!imei) {
                alert('IMEI tidak boleh kosong');
                return;
            }

            // Cek apakah IMEI sudah pernah dimasukkan
            if (selectedItems.find(item => item.imei === imei)) {
                alert('IMEI ini sudah ditambahkan!');
                this.value = '';
                return;
            }

            // Cari produk berdasarkan IMEI
            searchProductByImei(imei);
        }
    });

    // Fungsi untuk mencari produk berdasarkan IMEI
    function searchProductByImei(imei) {
        const url = `/search-by-imei?imei=` + encodeURIComponent(imei);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentInventory = data.inventory;
                    showProductPreview(data.inventory);
                } else {
                    alert(data.message || 'IMEI tidak ditemukan atau tidak tersedia.');
                    document.getElementById('imei-input').value = '';
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Terjadi kesalahan saat mencari produk: ' + error.message);
                document.getElementById('imei-input').value = '';
            });
    }

    // Tampilkan preview produk
    function showProductPreview(inventory) {
        // Reset previous values
        document.getElementById('sale-price').value = '';
        
        document.getElementById('preview-imei').textContent = inventory.imei;
        document.getElementById('preview-product').textContent = `${inventory.product.brand || ''} ${inventory.product.model || inventory.product.name}`;
        document.getElementById('preview-description').textContent = inventory.product.description || 'Tidak ada deskripsi';
        document.getElementById('default-price').textContent = formatRupiah(inventory.product.price);
        document.getElementById('sale-price').value = inventory.product.price;
        
        document.getElementById('product-preview').style.display = 'block';
        document.getElementById('imei-input').disabled = true;
        
        // Focus ke input harga
        document.getElementById('sale-price').focus();
        document.getElementById('sale-price').select();
    }

    // Event listener untuk tombol tambah ke keranjang
    document.getElementById('add-item-btn').addEventListener('click', function() {
        if (!currentInventory) return;

        const salePrice = parseInt(document.getElementById('sale-price').value);
        if (!salePrice || salePrice < 0) {
            alert('Harga jual harus diisi dan tidak boleh negatif');
            return;
        }

        // Tambah ke keranjang
        addItemToCart(currentInventory, salePrice);
        
        // Reset form
        resetForm();
    });

    // Event listener untuk tombol batal
    document.getElementById('cancel-btn').addEventListener('click', function() {
        resetForm();
    });

    // Event listener untuk Enter di input harga
    document.getElementById('sale-price').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('add-item-btn').click();
        }
    });

    // Fungsi untuk menambah item ke keranjang
    function addItemToCart(inventory, salePrice) {
        const item = {
            imei: inventory.imei,
            product: inventory.product,
            price: salePrice
        };

        selectedItems.push(item);

        // Tambah ke tabel
        let tbody = document.querySelector('#produk-table tbody');
        let row = document.createElement('tr');
        row.setAttribute('data-imei', inventory.imei);
        row.innerHTML = `
            <td>${inventory.imei}</td>
            <td>${inventory.product.brand || ''} ${inventory.product.model || inventory.product.name}</td>
            <td>${formatRupiah(salePrice)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeItem('${inventory.imei}')">Hapus</button>
            </td>
        `;
        tbody.appendChild(row);

        // Update total dan form
        updateTotal();
        updateHiddenInputs();
    }

    // Fungsi untuk menghapus item
    function removeItem(imei) {
        // Hapus dari array
        selectedItems = selectedItems.filter(item => item.imei !== imei);
        
        // Hapus baris dari tabel
        document.querySelector(`tr[data-imei="${imei}"]`).remove();
        
        // Update total dan form
        updateTotal();
        updateHiddenInputs();
    }

    // Reset form
    function resetForm() {
        document.getElementById('product-preview').style.display = 'none';
        document.getElementById('imei-input').disabled = false;
        document.getElementById('imei-input').value = '';
        document.getElementById('sale-price').value = '';
        document.getElementById('imei-input').focus();
        currentInventory = null;
    }

    // Update total harga
    function updateTotal() {
        totalPrice = selectedItems.reduce((sum, item) => sum + item.price, 0);
        document.getElementById('total-price').textContent = formatRupiah(totalPrice);
        document.getElementById('submit-btn').disabled = selectedItems.length === 0;
    }

    // Update hidden inputs untuk form submission
    function updateHiddenInputs() {
        // Hapus input hidden yang lama
        document.querySelectorAll('input[name^="items"]').forEach(input => input.remove());
        
        // Tambah input hidden untuk setiap item
        let form = document.getElementById('sales-form');
        selectedItems.forEach((item, index) => {
            // Input untuk IMEI
            let imeiInput = document.createElement('input');
            imeiInput.type = 'hidden';
            imeiInput.name = `items[${index}][imei]`;
            imeiInput.value = item.imei;
            form.appendChild(imeiInput);

            // Input untuk harga
            let priceInput = document.createElement('input');
            priceInput.type = 'hidden';
            priceInput.name = `items[${index}][price]`;
            priceInput.value = item.price;
            form.appendChild(priceInput);
        });
    }

    // Validasi form sebelum submit
    document.getElementById('sales-form').addEventListener('submit', function(e) {
        if (selectedItems.length === 0) {
            e.preventDefault();
            alert('Silakan tambahkan minimal satu item untuk dijual');
            return false;
        }
        
        // Pastikan semua harga valid
        for (let item of selectedItems) {
            if (!item.price || item.price <= 0) {
                e.preventDefault();
                alert('Semua item harus memiliki harga yang valid');
                return false;
            }
        }
        
        return true;
    });
</script>
@endpush