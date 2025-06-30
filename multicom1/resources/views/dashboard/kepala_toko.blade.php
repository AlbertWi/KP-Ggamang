@extends('layouts.app')

@section('title', 'Dashboard Kepala Toko')

@section('content')
<!-- Notifikasi Permintaan Masuk -->
@if($pendingRequestsCount > 0)
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-bell"></i>
    <strong>Notifikasi:</strong> Anda memiliki {{ $pendingRequestsCount }} permintaan barang yang menunggu persetujuan.
    <a href="{{ route('stock-requests.index') }}" class="alert-link">Lihat semua permintaan</a>
</div>
@endif

<!-- Row 1: Main Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $productCount }}</h3>
                <p>Jumlah Produk</p>
            </div>
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalPurchases }}</h3>
                <p>Pembelian Cabang</p>
            </div>
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $supplierCount }}</h3>
                <p>Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fas fa-truck"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalTransfersIn + $totalTransfersOut }}</h3>
                <p>Transfer Stok</p>
            </div>
            <div class="icon">
                <i class="fas fa-random"></i>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Stock Request Stats -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $pendingRequestsCount }}</h3>
                <p>Permintaan Masuk</p>
            </div>
            <div class="icon">
                <i class="fas fa-inbox"></i>
            </div>
            <a href="{{ route('stock-requests.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ $totalStockRequestsOut }}</h3>
                <p>Permintaan Terkirim</p>
            </div>
            <div class="icon">
                <i class="fas fa-paper-plane"></i>
            </div>
            <a href="{{ route('stock-requests.index') }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-6">
        <div class="small-box bg-dark">
            <div class="inner">
                <h3>{{ $totalStockRequestsIn + $totalStockRequestsOut }}</h3>
                <p>Total Request</p>
            </div>
            <div class="icon">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <a href="{{ route('stock-requests.create') }}" class="small-box-footer">
                Buat Request <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>
</div>

<!-- Card untuk Permintaan Pending -->
@if($pendingRequestsCount > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock text-warning"></i>
                    Permintaan Barang Menunggu Persetujuan ({{ $pendingRequestsCount }})
                </h3>
                <div class="card-tools">
                    <a href="{{ route('stock-requests.index') }}" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Dari Cabang</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $req)
                        <tr>
                            <td>
                                <strong>{{ $req->fromBranch->name }}</strong>
                            </td>
                            <td>{{ $req->product->name }}</td>
                            <td>
                                <span class="badge bg-info">{{ $req->qty }}</span>
                            </td>
                            <td>{{ $req->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <form action="{{ route('stock-requests.approve', $req->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Yakin ingin menyetujui permintaan ini?')">
                                        @csrf
                                        <button class="btn btn-success btn-sm" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Modal Reject -->
                                <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('stock-requests.reject', $req->id) }}">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Alasan Penolakan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label>Permintaan dari: <strong>{{ $req->fromBranch->name }}</strong></label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label>Produk: <strong>{{ $req->product->name }}</strong></label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="reason{{ $req->id }}">Alasan Penolakan:</label>
                                                        <textarea name="reason" id="reason{{ $req->id }}" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@else
<div class="row mt-4">
    <div class="col-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Status Permintaan</span>
                <span class="info-box-number">Tidak ada permintaan pending</span>
                <div class="progress">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                </div>
                <span class="progress-description">Semua permintaan telah diproses</span>
            </div>
        </div>
    </div>
</div>
@endif
@endsection