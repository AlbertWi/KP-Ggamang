@extends('layouts.app')

@section('title', 'Data Pembelian')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Pembelian</h5>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">+ Tambah Pembelian</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered m-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->purchase_date }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>Rp{{ number_format($purchase->total, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">Detail</a>
                    </td>
                </tr>
                @endforeach
                @if($purchases->isEmpty())
                <tr>
                    <td colspan="4" class="text-center">Belum ada data pembelian</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
