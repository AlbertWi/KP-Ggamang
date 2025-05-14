@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penjualan</h3>
                    <div class="card-tools">
                        <a href="{{ route('sales.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Penjualan
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="saleTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Customer</th>
                                <th>Tanggal</th>
                                <th>Total Harga</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $index => $sale)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $sale->customer_name }}</td>
                                    <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                                    <td>{{ number_format($sale->total_price) }}</td>
                                    <td>
                                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-primary btn-sm">
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
