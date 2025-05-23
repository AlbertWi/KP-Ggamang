@extends('layouts.app')

@section('title', 'Tambah Transfer Stok')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Transfer Stok</h3>
    </div>
    <form method="POST" action="{{ route('kepala_toko.stock_transfers.store') }}">
        @csrf
        <div class="card-body">
            {{-- Tampilkan nama cabang user yang login --}}
            <div class="form-group">
                <label>Dari Cabang</label>
                <input type="text" class="form-control" value="{{ auth()->user()->branch->name }}" readonly>
                <input type="hidden" name="from_branch_id" value="{{ auth()->user()->branch_id }}">
            </div>
            <div class="form-group">
                <label>Ke Cabang</label>
                <select name="to_branch_id" class="form-control" required>
                    <option value="">-- Pilih Cabang Tujuan --</option>
                    @foreach ($branches as $branch)
                        @if ($branch->id != auth()->user()->branch_id)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div id="products-wrapper">
                <div class="product-item mb-3 border p-3 rounded">
                    <div class="form-group">
                        <label>Produk</label>
                        <select name="products[0][product_id]" class="form-control" required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->brand }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah</label>
                        <input type="number" name="products[0][quantity]" class="form-control" required min="1">
                    </div>
                </div>
            </div>

            <button type="button" id="add-product" class="btn btn-secondary btn-sm mb-3">
                + Tambah Produk
            </button>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<script>
    let index = 1;
    document.getElementById('add-product').addEventListener('click', function () {
        const wrapper = document.getElementById('products-wrapper');
        const newItem = document.querySelector('.product-item').cloneNode(true);
        newItem.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            const newName = name.replace(/\[\d+\]/, `[${index}]`);
            el.setAttribute('name', newName);
            if (el.tagName === 'INPUT') el.value = '';
        });
        wrapper.appendChild(newItem);
        index++;
    });
</script>
@endsection
