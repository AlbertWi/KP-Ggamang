@extends('layouts.app')

@section('title', 'Data Pembelian')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pembelian</h3>
        <div class="card-tools">
            <a href="{{ route('kepala_toko.purchases.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Pembelian
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->id }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                    <td>
                        <a href="{{ route('kepala_toko.purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
