
@extends('layouts.app')

@section('title', 'Tambah Cabang')

@section('content')
    <div class="card">
        <div class="card-header">Tambah Cabang Baru</div>
        <div class="card-body">
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Nama Cabang</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="address" class="form-control" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
