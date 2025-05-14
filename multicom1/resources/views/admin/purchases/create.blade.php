@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tambah Pembelian</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('purchases.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Tanggal Pembelian</label>
                <input type="date" name="purchase_date" class="form-control" required>
            </div>

            <hr>
            <h6>Produk</h6>
            <div id="product-items">
                <div class="row mb-2">
                    <div class="col-md-5">
                        <select name="products[]" class="form-control" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="quantities[]" class="form-control" placeholder="Qty" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="prices[]" class="form-control" placeholder="Harga" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                    </div>
                </div>
            </div>
            <button type="button" id="add-product-row" class="btn btn-sm btn-secondary mt-2">+ Tambah Produk</button>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('add-product-row').addEventListener('click', function () {
        const row = `
        <div class="row mb-2">
            <div class="col-md-5">
                <select name="products[]" class="form-control" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="quantities[]" class="form-control" placeholder="Qty" required>
            </div>
            <div class="col-md-3">
                <input type="number" name="prices[]" class="form-control" placeholder="Harga" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
            </div>
        </div>`;
        document.getElementById('product-items').insertAdjacentHTML('beforeend', row);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.row').remove();
        }
    });
</script>
@endsection
