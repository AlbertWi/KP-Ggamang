@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Daftar Type Produk</h3>

    <!-- Tombol Tambah Type (Modal) -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createTypeModal">
        + Tambah Type
    </button>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Type</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($types as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td>{{ $type->name }}</td>
                    <td>
                        <!-- Tombol Edit Type (Modal) -->
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editTypeModal{{ $type->id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <!-- Modal Edit Type -->
                <div class="modal fade" id="editTypeModal{{ $type->id }}" tabindex="-1" aria-labelledby="editTypeLabel{{ $type->id }}" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('types.update', $type->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editTypeLabel{{ $type->id }}">Edit Type</h5>
                                </div>
                                <div class="modal-body">
                                    @if ($errors->any() && old('_method') == 'PUT' && old('type_id') == $type->id)
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <div class="mb-3">
                                        <label for="typeName{{ $type->id }}" class="form-label">Nama Type</label>
                                        <input type="text" class="form-control @error('name') @if(old('_method') == 'PUT' && old('type_id') == $type->id) is-invalid @endif @enderror" 
                                            id="typeName{{ $type->id }}" name="name" value="{{ old('name', $type->name) }}">
                                        @error('name')
                                            @if(old('_method') == 'PUT' && old('type_id') == $type->id)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @endif
                                        @enderror
                                    </div>
                                    <!-- Hidden field untuk identifikasi type -->
                                    <input type="hidden" name="type_id" value="{{ $type->id }}">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Modal Tambah Type -->
<div class="modal fade" id="createTypeModal" tabindex="-1" aria-labelledby="createTypeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('types.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTypeLabel">Tambah Type Baru</h5>
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
                        <label for="name" class="form-label">Nama Type</label>
                        <input type="text" class="form-control @error('name') @if(!old('_method')) is-invalid @endif @enderror" 
                            id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama type">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->any())
        @if(old('_method') == 'PUT' && old('type_id'))
            // Buka modal edit yang sesuai
            var editModal = new bootstrap.Modal(document.getElementById('editTypeModal{{ old("type_id") }}'));
            editModal.show();
        @else
            // Buka modal create
            var createModal = new bootstrap.Modal(document.getElementById('createTypeModal'));
            createModal.show();
        @endif
    @endif
});
</script>
@endsection