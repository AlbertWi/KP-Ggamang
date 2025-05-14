@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pembelian Barang</h3>
                    <div class="card-tools">
                        <a href="{{ route('purchases.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Pembelian
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="purchaseTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Supplier</th>
                                <th>Tanggal</th>
                                <th>Total Harga</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchases as $index => $purchase)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $purchase->supplier->name }}</td>
                                    <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                                    <td>{{ number_format($purchase->total_price) }}</td>
                                    <td>
                                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
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
