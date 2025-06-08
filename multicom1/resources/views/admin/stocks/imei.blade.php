@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Daftar IMEI untuk Produk: <strong>{{ $product->name }}</strong></h4>
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>No</th>
                <th>IMEI</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inventoryItems as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->imei }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Belum ada data IMEI.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection
