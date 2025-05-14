@extends('layouts.app')

@section('title', 'Stok Cabang')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pilih Cabang untuk Melihat Stok</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nama Cabang</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->address }}</td>
                    <td>
                        <a href="{{ route('inventory.show', $branch->id) }}" class="btn btn-info btn-sm">
                            Lihat Stok
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
