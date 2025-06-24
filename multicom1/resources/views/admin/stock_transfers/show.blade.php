@extends('layouts.app')

@section('title', 'Detail Transfer Stok')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Transfer Stok #{{ $stockTransfer->id }}</h3>
    </div>
    <div class="card-body">
        <table class="table table-sm table-bordered w-50">
            <tr>
                <th>Dari Cabang</th>
                <td>{{ $stockTransfer->fromBranch->name }}</td>
            </tr>
            <tr>
                <th>Ke Cabang</th>
                <td>{{ $stockTransfer->toBranch->name }}</td>
            </tr>
            <tr>
                <th>Tanggal</th>
                <td>{{ $stockTransfer->created_at->format('d-m-Y') }}</td>
            </tr>
        </table>

        <h5 class="mt-4">Daftar Barang</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>IMEI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stockTransfer->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->inventoryItem->product->name }}</td>
                            <td>{{ $item->inventoryItem->imei }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
