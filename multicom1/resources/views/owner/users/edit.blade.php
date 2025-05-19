@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit User</h3>
    </div>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $user->name) }}">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email', $user->email) }}">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" class="form-control" required>
                    <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="kepala_toko" {{ $user->role == 'kepala_toko' ? 'selected' : '' }}>Kepala Toko</option>
                </select>
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
