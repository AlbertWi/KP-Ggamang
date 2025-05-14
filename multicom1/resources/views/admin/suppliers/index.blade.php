@extends('layouts.app')

@section('title', 'Data Supplier')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0 d-flex justify-content-between align-items-center">
            Data Supplier
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">+ Tambah Supplier</a>
        </h5>
    </div>
    <div class="card-body p-0">
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
                @foreach ($suppliers as $supplier)
                <tr>
                    <td>{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone }}</td>
                    <td>{{ $supplier->address }}</td>
                    <td>
                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($suppliers->isEmpty())
                <tr>
                    <td colspan="4" class="text-center">Tidak ada data supplier</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
