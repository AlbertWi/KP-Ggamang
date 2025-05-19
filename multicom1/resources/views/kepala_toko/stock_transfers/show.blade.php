@extends('layouts.app')

@section('title', 'Detail Transfer Stok')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Transfer Stok #{{ $stockTransfer->id }}</h3>
    </div>
    <div class="card-body">
        <p><strong>Dari Cabang:</strong> {{ $stockTransfer->fromBranch->name }}</p>
        <p><strong>Ke Cabang:</strong> {{ $stockTransfer->toBranch->name }}</p>
        <p><strong>Tanggal:</strong> {{ $stockTransfer->created_at->format('d-m-Y H:i') }}</p>

        <h5 class="mt-4">Produk yang Ditransfer</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockTransfer->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
