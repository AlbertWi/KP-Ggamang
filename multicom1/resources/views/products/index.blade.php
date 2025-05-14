@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Master Stock</h3>
                    <div class="card-tools">
                        <a href="{{ route('products.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="productTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Merek</th>
                                <th>Type</th>
                                <th>Harga</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product->brand }}</td>
                                    <td>{{ $product->type }}</td>
                                    <td>{{ number_format($product->price) }}</td>
                                    <td>
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
