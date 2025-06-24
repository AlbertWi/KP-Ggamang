@extends('layouts.app')

@section('title', 'Daftar Brand')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Brand</h1>

    <a href="{{ route('brands.create') }}" class="btn btn-primary mb-3">+ Tambah Brand</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

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

                <!-- Modal Edit -->
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
                                    <div class="mb-3">
                                        <label for="name{{ $brand->id }}" class="form-label">Nama Brand</label>
                                        <input type="text" class="form-control" id="name{{ $brand->id }}" name="name" value="{{ $brand->name }}" required>
                                    </div>
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
@endsection
