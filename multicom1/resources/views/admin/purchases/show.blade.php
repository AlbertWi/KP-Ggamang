@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detail Pembelian</h5>
    </div>
    <div class="card-body">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ $purchase->purchase_date }}</p>
        <hr>
        <h6>Produk Dibeli:</h6>
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
                    <td>Rp{{ number_format($item->quantity * $item->price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-right"><strong>Total: Rp{{ number_format($purchase->total, 0, ',', '.') }}</strong></p>
        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
