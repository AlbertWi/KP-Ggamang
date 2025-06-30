@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Daftar Produk</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createProductModal">+ Tambah Produk</button>

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
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal{{ $product->id }}">Edit</button>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('products.update', $product->id) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Produk</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Nama Produk</label>
                                        <input type="text" name="name" class="form-control" value="{{ $product->name }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Brand</label>
                                        <input type="text" class="form-control" value="{{ $product->brand->name }}" disabled>
                                        <input type="hidden" name="brand_id" value="{{ $product->brand_id }}">
                                    </div>
                                    <div class="mb-2">
                                        <label>Type</label>
                                        <input type="text" class="form-control" value="{{ $product->type->name }}" disabled>
                                        <input type="hidden" name="type_id" value="{{ $product->type_id }}">
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
                    <td colspan="5">Tidak ada produk ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modal Tambah Produk -->
<div class="modal fade" id="createProductModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('products.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>Brand</label>
                        <select name="brand_id" id="brandSelect" class="form-control">
                            <option value="">-- Pilih Brand --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Type</label>
                        <select name="type_id" id="typeSelect" class="form-control">
                            <option value="">-- Pilih Type --</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" data-name="{{ $type->name }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Nama Produk</label>
                        <input type="text" name="name" id="productName" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const brandSelect = document.getElementById('brandSelect');
        const typeSelect = document.getElementById('typeSelect');
        const productName = document.getElementById('productName');

        function updateProductName() {
            const brandOption = brandSelect.options[brandSelect.selectedIndex];
            const typeOption = typeSelect.options[typeSelect.selectedIndex];

            const brandName = brandOption.dataset.name || '';
            const typeName = typeOption.dataset.name || '';

            if (brandName && typeName) {
                productName.value = brandName + ' ' + typeName;
            } else {
                productName.value = '';
            }
        }

        brandSelect.addEventListener('change', updateProductName);
        typeSelect.addEventListener('change', updateProductName);
    });
</script>
@endsection
