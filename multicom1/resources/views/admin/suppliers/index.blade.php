@extends('layouts.app')

@section('title', 'Data Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 d-flex justify-content-between align-items-center">
            Data Supplier
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSupplierModal">
            + Tambah Supplier
        </button>
        </h5>
    </div>
    <div class="card-body p-0">
        @if ($errors->any())
            <div class="alert alert-danger rounded-0 mb-0">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <table class="table table-bordered m-0">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>No. Telepon</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->address }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                            data-bs-target="#editSupplierModal{{ $supplier->id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editSupplierModal{{ $supplier->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Supplier</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Nama</label>
                                        <input type="text" name="name" class="form-control" value="{{ $supplier->name }}">
                                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label>No. Telepon</label>
                                        <input type="text" name="phone" class="form-control" value="{{ $supplier->phone }}">
                                    </div>
                                    <div class="mb-3">
                                        <label>Alamat</label>
                                        <textarea name="address" class="form-control">{{ $supplier->address }}</textarea>
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

                @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data supplier</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="createSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('suppliers.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Supplier</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label>No. Telepon</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="address" class="form-control">{{ old('address') }}</textarea>
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
@endsection
