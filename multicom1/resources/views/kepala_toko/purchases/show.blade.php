@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Detail Pembelian #{{ $purchase->id }}</h3>
    </div>
    <div class="card-body">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ $purchase->created_at->format('d-m-Y H:i') }}</p>

        <h5 class="mt-4">Produk yang Dibeli</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
