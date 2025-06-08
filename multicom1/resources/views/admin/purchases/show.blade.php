@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Pembelian</h4>

    <div class="mb-4">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</p>
        <p><strong>Cabang:</strong> {{ $purchase->branch->name ?? 'N/A' }}</p>
    </div>

    {{-- Debug info - hapus setelah masalah teratasi --}}
    <div class="alert alert-info">
        <p>Debug Info:</p>
        <p>Total Items: {{ $purchase->items->count() }}</p>
        @foreach ($purchase->items as $item)
            <p>{{ $item->product->name }}: {{ $item->inventoryItems->count() }} inventory items</p>
        @endforeach
    </div>

    @if($purchase->items->sum(function($item) { return $item->inventoryItems->count(); }) > 0)
        <form action="{{ route('purchases.save_imei', $purchase->id) }}" method="POST">
            @csrf
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>IMEI</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        @if($item->inventoryItems->count() > 0)
                            @foreach ($item->inventoryItems as $inventory)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>1 unit</td>
                                    <td>
                                        <input type="text"
                                               name="imeis[{{ $inventory->id }}]"
                                               value="{{ $inventory->imei }}"
                                               class="form-control"
                                               placeholder="Masukkan IMEI"
                                               required>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $inventory->status == 'in_stock' ? 'success' : 'warning' }}">
                                            {{ ucfirst(str_replace('_', ' ', $inventory->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada inventory items untuk produk {{ $item->product->name }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan IMEI
                </button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </form>
    @else
        <div class="alert alert-warning">
            <h5>Tidak ada inventory items</h5>
            <p>Belum ada inventory items yang dibuat untuk pembelian ini. Pastikan proses pembelian telah selesai dengan benar.</p>
            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    @endif
</div>
@endsection
