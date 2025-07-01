@extends('layouts.app')

@section('title', 'Detail Barang Keluar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Detail Barang Keluar #{{ $sale->id }}</h1>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">← Kembali ke Daftar Barang Keluar</a>
    </div>

    <div class="mb-2">
        <strong>Tanggal:</strong> {{ $sale->created_at->format('d-m-Y H:i') }}
    </div>

    <div class="mb-2">
        <strong>Cabang:</strong> {{ $sale->branch->name ?? '-' }}
    </div>

    <div class="mb-2">
        <strong>Jumlah Item:</strong> {{ $sale->items->count() }}
    </div>

    <div class="mb-3">
        <strong>Total Harga:</strong> Rp{{ number_format($sale->total, 0, ',', '.') }}
    </div>

    <h4 class="mt-4">Daftar Item Terjual</h4>
    <table class="table table-bordered table-striped">
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
                    <td>
                        {{ $item->product->brand->name ?? '' }}
                        {{ $item->product->model ?? $item->product->name }}
                    </td>
                    <td>{{ $item->imei }}</td>
                    <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada item dalam Barang Keluar ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
