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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $productId => $items)
                            @php $product = $items->first()->product; @endphp
                            <tr>
                                <td>{{ $product->name ?? '-' }}</td>
                                <td><span class="badge bg-success">{{ $items->count() }}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" 
                                            data-toggle="modal"
                                            data-target="#modal-imei-{{ $branch->id }}-{{ $productId }}">
                                        Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Detail IMEI -->
                            <div class="modal fade" id="modal-imei-{{ $branch->id }}-{{ $productId }}" tabindex="-1" role="dialog" aria-labelledby="modalLabel{{ $branch->id }}-{{ $productId }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel{{ $branch->id }}-{{ $productId }}">
                                                Daftar IMEI - {{ $product->name }} ({{ $branch->name }})
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group">
                                                @foreach($items as $item)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $item->imei }}
                                                        <span class="badge badge-secondary">{{ $item->status }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
