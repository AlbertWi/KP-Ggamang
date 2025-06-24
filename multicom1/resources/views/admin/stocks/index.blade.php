@extends('layouts.app')

@section('title', 'Stok Cabang')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Stok Produk Cabang</h3>
        <div class="card-tools">
            <form method="GET" action="{{ route('kepala.stok-cabang') }}">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" name="q" class="form-control float-right" placeholder="Cari produk..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body">
    @foreach($branches as $branch)
    <h5 class="mt-4"><i class="fas fa-store"></i> {{ $branch->name }}</h5>

    @php
        $grouped = $branch->inventoryItems->groupBy('product_id');

        if ($query) {
            $grouped = $grouped->filter(function ($items, $productId) use ($query) {
                return stripos($items->first()->product->name, $query) !== false;
            });
        }
    @endphp
            @if($grouped->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grouped as $productId => $items)
                                <tr>
                                <td>{{ $items->first()->product->name ?? '-' }}</td>
                                <td><span class="badge bg-success">{{ $items->count() }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted">Tidak ada stok produk tersedia.</p>
            @endif
        @endforeach
    </div>
</div>
@endsection
