@extends('layouts.app')

@section('content')
<div class="container">
    <h4><strong>Stok Cabang</strong></h4>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Qty</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($stocks as $stock)
                <tr>
                    <td>{{ $stock->product->name }}</td>
                    <td>{{ $stock->qty }}</td>
                    <td>
                        <a href="{{ route('stocks.imei', $stock->product_id) }}" class="btn btn-sm btn-primary">
                            Detail
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
