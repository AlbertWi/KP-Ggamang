@extends('layouts.app')

@section('title', 'Stok - ' . $branch->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Stok Barang di Cabang {{ $branch->name }}</h3>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary btn-sm float-right">Kembali</a>
    </div>
    <div class="card-body">
        @if($inventory->isEmpty())
            <div class="alert alert-warning">Tidak ada stok barang di cabang ini.</div>
        @else
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>IMEI</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventory as $item)
                    <tr>
                        <td>{{ $item->product->name }} {{ $item->product->name }}</td>
                        <td>{{ $item->imei ?? '-' }}</td>
                        <td>{{ $item->qty }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
