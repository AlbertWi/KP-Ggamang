@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Daftar Produk</h3>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('products.create') }}" class="btn btn-success">+ Tambah Produk</a>

        <!-- Form pencarian -->
        <form method="GET" action="{{ route('products.index') }}" class="d-flex" style="max-width: 300px;">
            <input type="text" name="q" class="form-control me-2" placeholder="Cari nama produk..." value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Brand</th>
                <th>Type</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->brand->name ?? '-' }}</td>
                    <td>{{ $product->type->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tidak ada produk ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
