@extends('layouts.app')

@section('title', 'Tambah Transfer Stok')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Transfer Stok</h3>
    </div>

    <form method="POST" action="{{ route('stock-transfers.store') }}">
        @csrf
        <div class="card-body">
            {{-- Notifikasi Error Umum --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Dari Cabang (readonly) --}}
            <div class="form-group">
                <label>Dari Cabang</label>
                <input type="text" class="form-control" value="{{ auth()->user()->branch->name }}" readonly>
                <input type="hidden" name="from_branch_id" value="{{ auth()->user()->branch_id }}">
            </div>

            {{-- Ke Cabang --}}
            <div class="form-group">
                <label>Ke Cabang</label>
                <select name="to_branch_id" class="form-control" required>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('to_branch_id')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>

            {{-- Input IMEI --}}
            <div class="form-group">
                <label>Input IMEI</label>
                <div id="imei-input-list">
                    @if(old('imeis'))
                        @foreach(old('imeis') as $imei)
                            <div class="input-group mb-2">
                                <input type="text" name="imeis[]" class="form-control" placeholder="Masukkan IMEI" value="{{ $imei }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-danger remove-imei">Hapus</button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="input-group mb-2">
                            <input type="text" name="imeis[]" class="form-control" placeholder="Masukkan IMEI">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger remove-imei">Hapus</button>
                            </div>
                        </div>
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-secondary mt-1" id="add-imei">+ Tambah IMEI</button>
            </div>
        </div>

        <div class="card-footer">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('stock-transfers.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('add-imei').addEventListener('click', function () {
        const newInput = `
            <div class="input-group mb-2">
                <input type="text" name="imeis[]" class="form-control" placeholder="Masukkan IMEI">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger remove-imei">Hapus</button>
                </div>
            </div>`;
        document.getElementById('imei-input-list').insertAdjacentHTML('beforeend', newInput);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-imei')) {
            e.target.closest('.input-group').remove();
        }
    });
</script>
@endpush
