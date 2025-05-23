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
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->created_at->format('d-m-Y') }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ number_format($purchase->items->sum('price'), 0, ',', '.') }}</td>
                        <td>
                            <ul>
                                @foreach($purchase->items as $item)
                                    <li>{{ $item->product->name }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td>
                            <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-info">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada data pembelian</td>
                    </tr>
                @endforelse
            </tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection
