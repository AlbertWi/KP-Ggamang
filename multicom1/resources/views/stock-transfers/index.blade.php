@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Transfer Stok</h3>
                    <div class="card-tools">
                        <a href="{{ route('stock-transfers.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Tambah Transfer Stok
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <table id="stockTransferTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Dari Cabang</th>
                                <th>Ke Cabang</th>
                                <th>Tanggal</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stockTransfers as $index => $transfer)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $transfer->fromBranch->name }}</td>
                                    <td>{{ $transfer->toBranch->name }}</td>
                                    <td>{{ $transfer->created_at->format('d-m-Y') }}</td>
                                    <td>
                                        <a href="{{ route('stock-transfers.show', $transfer->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
