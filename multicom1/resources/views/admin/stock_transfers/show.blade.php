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
            <p><strong>Tanggal:</strong> {{ $stockTransfer->created_at->format('d-m-Y') }}</p>

            <h5 class="mt-4">Daftar Barang</h5>
            <ul>
                @foreach ($stockTransfer->items as $item)
                    <li>{{ $item->inventoryItem->product->name }} - IMEI: {{ $item->inventoryItem->imei }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
