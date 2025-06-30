@extends('layouts.app')

@section('title', 'Cabang Toko')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Daftar Cabang Toko</h3>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createBranchModal">
            + Tambah Cabang
        </button>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                    <tr>
                        <td>{{ $branch->name }}</td>
                        <td>{{ $branch->address }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                data-bs-target="#editBranchModal{{ $branch->id }}">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Edit Cabang -->
                    <div class="modal fade" id="editBranchModal{{ $branch->id }}" tabindex="-1"
                        aria-labelledby="editBranchModalLabel{{ $branch->id }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <form action="{{ route('branches.update', $branch) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editBranchModalLabel{{ $branch->id }}">Edit Cabang</h5>
                                    </div>
                                    <div class="modal-body">
                                        @if ($errors->any() && old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="mb-3">
                                            <label>Nama Cabang</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name', $branch->name) }}">
                                            @error('name')
                                                @if(old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @endif
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label>Alamat</label>
                                            <textarea name="address" class="form-control" rows="3"
                                                required>{{ old('address', $branch->address) }}</textarea>
                                            @error('address')
                                                @if(old('_method') == 'PUT' && old('branch_id') == $branch->id)
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @endif
                                            @enderror
                                        </div>
                                        <!-- Hidden field untuk identifikasi branch -->
                                        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Cabang -->
<div class="modal fade" id="createBranchModal" tabindex="-1" aria-labelledby="createBranchModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('branches.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createBranchModalLabel">Tambah Cabang Baru</h5>
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
                        <label>Nama Cabang</label>
                        <input type="text" name="name" class="form-control">
                        @error('name')
                            @if(!old('_method'))
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                        @error('address')
                            @if(!old('_method'))
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @endif
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if ($errors->any())
        @if(old('_method') == 'PUT' && old('branch_id'))
            // Buka modal edit yang sesuai
            var editModal = new bootstrap.Modal(document.getElementById('editBranchModal{{ old("branch_id") }}'));
            editModal.show();
        @else
            // Buka modal create
            var createModal = new bootstrap.Modal(document.getElementById('createBranchModal'));
            createModal.show();
        @endif
    @endif
});
</script>
@endsection