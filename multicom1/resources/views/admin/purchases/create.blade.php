@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="container">
    <h4 class="mb-4">Tambah Pembelian</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchases.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="supplier_id" class="form-label"><strong>Supplier</strong></label>
            <select name="supplier_id" id="supplier_id" class="form-control">
                <option value="">-- Pilih Supplier --</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Tanggal</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::now()->format('d-m-Y') }}" readonly>
        </div>

        <hr>
        <h5 class="mb-3">Produk</h5>

        <div id="product-wrapper">
            <div class="row mb-2 product-row">
                <div class="col-md-4">
                    <select name="items[0][product_id]" class="form-control">
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[0][qty]" class="form-control" placeholder="Qty" min="1">
                </div>
                <div class="col-md-3">
                    <input type="text" name="items[0][price]" class="form-control price-input" placeholder="Harga Satuan">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-row">Hapus</button>
                </div>
            </div>
        </div>

        <button type="button" id="add-product" class="btn btn-secondary mt-2">+ Tambah Produk</button>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Pembelian</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let index = 1;

    document.getElementById('add-product').addEventListener('click', function () {
        const wrapper = document.getElementById('product-wrapper');

        const row = document.createElement('div');
        row.classList.add('row', 'mb-2', 'product-row');

        row.innerHTML = `
            <div class="col-md-4">
                <select name="items[${index}][product_id]" class="form-control">
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${index}][qty]" class="form-control" placeholder="Qty" min="1">
            </div>
            <div class="col-md-3">
                <input type="text" name="items[${index}][price]" class="form-control price-input" placeholder="Harga Satuan">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-row">Hapus</button>
            </div>
        `;

        wrapper.appendChild(row);
        index++;
    });

    document.getElementById('product-wrapper').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.product-row').remove();
        }
    });

    // Format angka harga saat input
    function formatPriceInput(input) {
        input.value = input.value
            .replace(/[^0-9]/g, '') // hanya angka
            .replace(/\B(?=(\d{3})+(?!\d))/g, ","); // tambahkan koma
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('price-input')) {
            formatPriceInput(e.target);
        }
    });

    // Bersihkan koma sebelum form dikirim
    document.querySelector('form').addEventListener('submit', function() {
        document.querySelectorAll('.price-input').forEach(function(input) {
            input.value = input.value.replace(/,/g, '');
        });
    });
</script>
@endpush
