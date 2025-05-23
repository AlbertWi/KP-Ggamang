@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Supplier</h3>
    </div>
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>

            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="phone" class="form-control" required value="{{ old('phone') }}">
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address" class="form-control" rows="3" required>{{ old('address') }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
