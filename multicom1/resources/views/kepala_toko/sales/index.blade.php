@extends('layouts.app')

@section('title', 'Daftar Barang Keluar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Daftar Barang Keluar</h1>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ Tambah Barang Keluar</a>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tanggal</th>
                <th>Jumlah Item</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $sale->items->count() }}</td>
                    <td>
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada data Barang Keluar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
