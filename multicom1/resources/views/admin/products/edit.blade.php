@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Produk</h3>
    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label class="form-label">Brand</label>
            <p class="form-control-plaintext">{{ $product->brand->name }}</p>
        </div>

        <div class="mb-3">
            <label class="form-label">Type</label>
            <p class="form-control-plaintext">{{ $product->type->name }}</p>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" name="name" id="name" value="{{ $product->name }}" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
