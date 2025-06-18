@extends('layouts.app')
@section('title', 'Buat Permintaan Barang')
@section('content')
<div class="card">
    <div class="card-header">Buat Permintaan</div>
    <div class="card-body">
        <form method="POST" action="{{ route('stock-requests.store') }}">
            @csrf
            <div class="mb-3">
                <label>Cabang Tujuan</label>
                <select name="to_branch_id" class="form-control" required>
                    <option value="">Pilih Cabang</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Produk</label>
                <select name="product_id" class="form-control" required>
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label>Jumlah</label>
                <input type="number" name="qty" class="form-control" required min="1">
            </div>
            <button type="submit" class="btn btn-primary">Kirim</button>
            <a href="{{ route('stock-requests.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@endsection