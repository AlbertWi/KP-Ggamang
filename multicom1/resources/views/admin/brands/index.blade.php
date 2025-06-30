@extends('layouts.app')

@section('title', 'Daftar Brand')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Brand</h1>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createBrandModal">
        + Tambah Brand
    </button>
    <!-- Modal Tambah Brand -->
    <div class="modal fade" id="createBrandModal" tabindex="-1" aria-labelledby="createBrandLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('brands.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createBrandLabel">Tambah Brand Baru</h5>
                    </div>
                    <div class="modal-body">
                        @if ($errors->any() && !old('_method'))
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Brand</label>
                            <input type="text" class="form-control @error('name') @if(!old('_method')) is-invalid @endif @enderror" 
                                id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama brand">
                            @error('name')
                                @if(!old('_method'))
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @endif
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- TABEL DATA BRAND -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Brand</th>
                <th>Dibuat Pada</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($brands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td>{{ $brand->name }}</td>
                    <td>{{ $brand->created_at->format('d-m-Y H:i') }}</td>
                    <td>
                        <!-- Tombol Edit -->
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editBrandModal{{ $brand->id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <!-- Modal Edit Brand -->
                <div class="modal fade" id="editBrandModal{{ $brand->id }}" tabindex="-1" aria-labelledby="editBrandLabel{{ $brand->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('brands.update', $brand->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editBrandLabel{{ $brand->id }}">Edit Brand</h5>
                                </div>
                                <div class="modal-body">
                                    @if ($errors->any() && old('_method') == 'PUT' && old('brand_id') == $brand->id)
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label for="name{{ $brand->id }}" class="form-label">Nama Brand</label>
                                        <input type="text" class="form-control @error('name') @if(old('_method') == 'PUT' && old('brand_id') == $brand->id) is-invalid @endif @enderror" 
                                            id="name{{ $brand->id }}" name="name" value="{{ old('name', $brand->name) }}">
                                        @error('name')
                                            @if(old('_method') == 'PUT' && old('brand_id') == $brand->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @endif
                                        @enderror
                                    </div>
                                    <!-- Hidden field untuk identifikasi brand -->
                                    <input type="hidden" name="brand_id" value="{{ $brand->id }}">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            @empty
                <tr>
                    <td colspan="4">Belum ada data Brand.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->any())
        @if(old('_method') == 'PUT' && old('brand_id'))
            var editModal = new bootstrap.Modal(document.getElementById('editBrandModal{{ old("brand_id") }}'));
            editModal.show();
        @else
            var createModal = new bootstrap.Modal(document.getElementById('createBrandModal'));
            createModal.show();
        @endif
    @endif
});
</script>
@endsection