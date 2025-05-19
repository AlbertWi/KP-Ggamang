@extends('layouts.app')

@section('title', 'Dashboard Kepala Toko')

@section('content')
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
                <h3>{{ $purchaseCount }}</h3>
                <p>Total Pembelian</p>
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
                <h3>{{ $transferCount }}</h3>
                <p>Transfer Stok</p>
            </div>
            <div class="icon">
                <i class="fas fa-random"></i>
            </div>
        </div>
    </div>
</div>
@endsection
