@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content')
<div class="container">
    <h1 class="mb-4">Detail Penjualan #{{ $sale->id }}</h1>

    <div class="mb-3">
        <strong>Tanggal:</strong> {{ $sale->created_at->format('d-m-Y H:i') }}
    </div>

    <div class="mb-4">
        <strong>Total Item:</strong> {{ $sale->items->count() }}
    </div>

    <h4>Daftar Item</h4>
    <table class="table table-bordered">
        <thead class="table-secondary">
            <tr>
                <th>ID Produk</th>
                <th>Nama Produk</th>
                <th>IMEI</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->id ?? '-' }}</td>
                    <td>{{ $item->product->brand ?? '' }} {{ $item->product->model ?? '' }}</td>
                    <td>{{ $item->imei }}</td>
                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Tidak ada item dalam penjualan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('sales.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Penjualan</a>
</div>
@endsection
