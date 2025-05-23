@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('content')
<div class="card">
    <div class="card-header">
        <h5>Detail Pembelian</h5>
    </div>
    <div class="card-body">
        <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
        <p><strong>Tanggal:</strong> {{ $purchase->created_at->format('d-m-Y') }}</p>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>IMEI</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->imei }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
