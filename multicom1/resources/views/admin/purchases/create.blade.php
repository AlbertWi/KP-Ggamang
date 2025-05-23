@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container">
    <h1>Tambah Pembelian</h1>

    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="supplier_id" class="form-label">Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Pilih Supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="purchase_date" class="form-label">Tanggal Pembelian</label>
            <input type="date" name="purchase_date" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <hr>
        <h5>Produk</h5>
        <div id="product-list">
            <div class="row g-3 align-items-end product-item">
                <div class="col-md-4">
                    <label class="form-label">Produk</label>
                    <select name="items[0][product_id]" class="form-select" required>
                        <option value="">Pilih Produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="items[0][qty]" class="form-control" min="1" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga Satuan</label>
                    <input type="number" name="items[0][price]" class="form-control" min="0" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove">Hapus</button>
                </div>
            </div>
        </div>

        <button type="button" id="add-product" class="btn btn-secondary mt-3">+ Tambah Produk</button>
        <br><br>

        <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let productIndex = 1;

    document.getElementById('add-product').addEventListener('click', function () {
        const productList = document.getElementById('product-list');
        const newItem = document.querySelector('.product-item').cloneNode(true);

        // Reset input value
        newItem.querySelectorAll('input, select').forEach(input => {
            input.value = '';
        });

        // Update name attribute index
        newItem.querySelectorAll('input, select').forEach(input => {
            const name = input.getAttribute('name');
            const newName = name.replace(/\d+/, productIndex);
            input.setAttribute('name', newName);
        });

        productList.appendChild(newItem);
        productIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove')) {
            const items = document.querySelectorAll('.product-item');
            if (items.length > 1) {
                e.target.closest('.product-item').remove();
            } else {
                alert('Minimal satu produk harus diinput.');
            }
        }
    });
</script>
@endsection
