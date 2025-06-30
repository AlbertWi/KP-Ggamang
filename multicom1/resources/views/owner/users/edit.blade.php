@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card">
    <div class="card-header">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Terjadi kesalahan:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <h3 class="card-title">Edit User</h3>
    </div>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
            </div>
            <div class="form-group">
                <label for="password">Password (Biarkan kosong jika tidak diganti)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
