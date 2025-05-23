@extends('layouts.app')

@section('title', 'Daftar Merek')

@section('content')
<div class="container">
    <h1 class="mb-4">Daftar Merek</h1>

    <a href="{{ route('brands.create') }}" class="btn btn-primary mb-3">+ Tambah Merek</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama Merek</th>
                <th>Dibuat Pada</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($brands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td>{{ $brand->name }}</td>
                    <td>{{ $brand->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Belum ada data merek.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
