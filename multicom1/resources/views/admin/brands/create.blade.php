@extends('layouts.app')

@section('title', 'Tambah Merek')

@section('content')
<div class="container">
    <h1 class="mb-4">Tambah Merek Baru</h1>

    <form action="{{ route('brands.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="name">Nama Merek</label>
            <input type="text" name="name" id="name" class="form-control" required>
            @error('name')
                <small class="text-danger d-block mt-1">{{ $message }}</small>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
