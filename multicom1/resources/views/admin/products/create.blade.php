@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Produk</h3>
    <form action="{{ route('products.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nama Produk</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="brand_id">Brand</label>
            <select name="brand_id" id="brand_id" class="form-control" required>
                @foreach ($brands as $brand)
            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
</div>

        <div class="form-group">
            <label for="type_id">Type</label>
            <select name="type_id" id="type_id" class="form-control" required>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
