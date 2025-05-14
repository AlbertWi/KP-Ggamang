@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Produk</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('products.update', $product->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Nama Produk</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $product->name }}" required>
            </div>
            <div class="form-group">
                <label for="brand">Merek</label>
                <input type="text" name="brand" id="brand" class="form-control" value="{{ $product->brand }}" required>
            </div>
            <div class="form-group">
                <label for="price">Harga</label>
                <input type="number" name="price" id="price" class="form-control" value="{{ $product->price }}" required>
            </div>
            <button type="submit" class="btn btn-success mt-2">Update</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary mt-2">Batal</a>
        </form>
    </div>
</div>
@endsection
