@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Edit Supplier</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Supplier</label>
                <input type="text" name="name" class="form-control" value="{{ $supplier->name }}" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="tel" name="phone" class="form-control" value="{{ $supplier->phone }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" minlength="8" maxlength="15">
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <textarea name="address" class="form-control" required>{{ $supplier->address }}</textarea>
            </div>
            <button type="submit" class="btn btn-success mt-2">Update</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary mt-2">Batal</a>
        </form>
    </div>
</div>
@endsection
