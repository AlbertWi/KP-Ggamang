@extends('layouts.app')

@section('title', 'Tambah Transfer Stok')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Form Tambah Transfer Stok</h3>
        </div>
        <form method="POST" action="{{ route('stock-transfers.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Dari Cabang</label>
                    <select name="from_branch_id" class="form-control" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Ke Cabang</label>
                    <select name="to_branch_id" class="form-control" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Produk</label>
                    <select name="product_id" class="form-control" required>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->brand }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah</label>
                    <input type="number" name="quantity" class="form-control" required min="1">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
@endsection
