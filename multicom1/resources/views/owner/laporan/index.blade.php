@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="container">
    <h4 class="mb-4">Laporan Penjualan Cabang</h4>

    <form method="GET" class="row mb-3">
        <div class="col-md-4">
            <label>Tanggal Awal</label>
            <input type="date" name="tanggal_awal" class="form-control" value="{{ $tanggalAwal }}">
        </div>
        <div class="col-md-4">
            <label>Tanggal Akhir</label>
            <input type="date" name="tanggal_akhir" class="form-control" value="{{ $tanggalAkhir }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">Tampilkan</button>
        </div>
    </form>

    @if ($penjualan->count())
    <div class="mb-3">
        <strong>Total Pendapatan:</strong> Rp{{ number_format($totalPendapatan, 0, ',', '.') }}<br>
        <strong>Total Laba:</strong> Rp{{ number_format($totalLaba, 0, ',', '.') }}
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Produk</th>
                <th>IMEI</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>Laba</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penjualan as $sale)
                @foreach ($sale->items as $item)
                    @php
                        $hargaJual = $item->price;
                        $hargaBeli = $item->inventoryItem->purchaseItem->price ?? 0;
                        $laba = $hargaJual - $hargaBeli;
                    @endphp
                    <tr>
                        <td>{{ $sale->created_at->format('d-m-Y') }}</td>
                        <td>{{ $sale->branch->name ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->imei }}</td>
                        <td>Rp{{ number_format($hargaBeli, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($hargaJual, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($laba, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @else
        <p class="text-muted">Tidak ada data penjualan untuk rentang tanggal ini.</p>
    @endif
</div>
@endsection
