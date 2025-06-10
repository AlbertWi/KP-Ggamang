@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Tambah Tipe Produk</h3>
    <form action="{{ route('types.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nama Tipe</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: 5G, Foldable, Flagship" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('types.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
