@extends('layouts.app')

@section('title', 'Tambah Penjualan')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Penjualan</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.store') }}" method="POST">
        @csrf

        <div id="items-wrapper">
            <div class="card mb-3 item-block">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Produk</label>
                            <select name="items[0][product_id]" class="form-control" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->brand }} {{ $product->model }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>IMEI</label>
                            <input type="text" name="items[0][imei]" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Harga Jual</label>
                            <input type="number" name="items[0][price]" class="form-control" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-item">-</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="button" id="add-item" class="btn btn-secondary mb-3">+ Tambah Item</button>
        <br>
        <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
    </form>
</div>

@endsection

@push('scripts')
<script>
    let itemIndex = 1;

    document.getElementById('add-item').addEventListener('click', function () {
        const wrapper = document.getElementById('items-wrapper');

        const html = `
        <div class="card mb-3 item-block">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label>Produk</label>
                        <select name="items[${itemIndex}][product_id]" class="form-control" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->brand }} {{ $product->model }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>IMEI</label>
                        <input type="text" name="items[${itemIndex}][imei]" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Harga Jual</label>
                        <input type="number" name="items[${itemIndex}][price]" class="form-control" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item">-</button>
                    </div>
                </div>
            </div>
        </div>`;

        wrapper.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.item-block').remove();
        }
    });
</script>
@endpush
