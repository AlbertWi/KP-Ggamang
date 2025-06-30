@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Pembelian</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</p>
        <p><strong>Cabang:</strong> {{ $purchase->branch->name ?? 'N/A' }}</p>
    </div>

    @if($purchase->items->sum(fn($item) => $item->inventoryItems->count()) > 0)
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
                        @foreach ($item->inventoryItems as $inventory)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>1 unit</td>
                                <td>
                                    <input type="text" name="imeis[{{ $inventory->id }}]" 
                                           value="{{ old("imeis.$inventory->id", $inventory->imei) }}"
                                           class="form-control" placeholder="Masukkan IMEI">
                                </td>
                                <td>
                                    <span class="badge bg-{{ $inventory->status == 'in_stock' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $inventory->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
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
