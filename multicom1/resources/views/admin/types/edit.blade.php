@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Edit Tipe Produk</h3>
    <form action="{{ route('types.update', $type->id) }}" method="POST">
        @csrf @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nama Tipe</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $type->name }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('types.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
