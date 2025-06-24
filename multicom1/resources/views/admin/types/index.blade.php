@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Daftar Type Produk</h3>
    <a href="{{ route('types.create') }}" class="btn btn-success mb-3">+ Tambah Type</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Type</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($types as $type)
                <tr>
                    <td>{{ $type->id }}</td>
                    <td>{{ $type->name }}</td>
                    <td>
                        <a href="{{ route('types.edit', $type->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
