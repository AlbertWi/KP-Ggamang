@extends('layouts.app')

@section('title', 'Daftar Transfer Stok')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Transfer Stok</h3>
            <div class="card-tools">
                <a href="{{ route('stock-transfers.create') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Tambah Transfer Stok
                </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Dari Cabang</th>
                        <th>Ke Cabang</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockTransfers as $transfer)
                        <tr>
                            <td>{{ $transfer->id }}</td>
                            <td>{{ $transfer->fromBranch->name }}</td>
                            <td>{{ $transfer->toBranch->name }}</td>
                            <td>{{ $transfer->created_at->format('d-m-Y') }}</td>
                            <td>
                                <a href="{{ route('stock-transfers.show', $transfer->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
