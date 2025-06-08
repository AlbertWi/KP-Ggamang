@extends('layouts.app')

@section('title', 'Data Pembelian')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Pembelian</h5>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary btn-sm">+ Tambah Pembelian</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered m-0">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    @php
                        $isComplete = true;
                        foreach ($purchase->items as $item) {
                            foreach ($item->inventoryItems as $inv) {
                                if (is_null($inv->imei)) {
                                    $isComplete = false;
                                    break 2;
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->items->sum('qty') }}</td>
                        <td>
                            <ul class="mb-0">
                                @foreach($purchase->items as $item)
                                    <li>{{ $item->product->name }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <ul class="mb-0">
                                @foreach($purchase->items as $item)
                                    <li>Rp{{ number_format($item->price, 0, ',', '.') }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}"
                               class="btn btn-sm {{ $isComplete ? 'btn-success' : 'btn-danger' }}">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data pembelian</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
