@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Tambah Supplier Baru</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Supplier</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary mt-2">Kembali</a>
        </form>
    </div>
</div>
@endsection
