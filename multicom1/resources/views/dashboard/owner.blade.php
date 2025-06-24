{{-- resources/views/dashboard/owner.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Owner')

@section('content')
<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $totalStock ?? 0 }}</h3>
                <p>Total Stok Seluruh Cabang</p>
            </div>
            <div class="icon">
                <i class="fas fa-boxes"></i>
            </div>
            <a href="#" class="small-box-footer disabled" onclick="return false;">Data Gabungan <i class="fas fa-info-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalBranches ?? 0 }}</h3>
                <p>Jumlah Cabang</p>
            </div>
            <div class="icon">
                <i class="fas fa-store-alt"></i>
            </div>
            <a href="{{ route('branches.index') }}" class="small-box-footer">Lihat Cabang <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalAdmins ?? 0 }}</h3>
                <p>Total Admin & Kepala Toko</p>
            </div>
            <div class="icon">
                <i class="fas fa-users-cog"></i>
            </div>
            <a href="{{ route('users.index') }}" class="small-box-footer">Kelola User <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title">📉 Stok Cabang yang Menipis</h5>
    </div>
    <div class="card-body">
        @if($lowStocks->count())
            @foreach($lowStocks as $branchData)
                <h6><i class="fas fa-store"></i> {{ $branchData['branch'] }}</h6>
                <table class="table table-bordered table-sm mb-4">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branchData['low_stocks'] as $item)
                            <tr>
                                <td>{{ $item['product'] }}</td>
                                <td><span class="badge bg-danger">{{ $item['qty'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            <p class="text-muted">Tidak ada stok yang menipis.</p>
        @endif
    </div>
</div>
</div>
@endsection
