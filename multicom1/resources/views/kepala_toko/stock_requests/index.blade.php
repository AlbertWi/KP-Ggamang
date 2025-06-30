@extends('layouts.app')
@section('title', 'Permintaan Barang')
@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan Barang</h3>
        <div class="card-tools">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
            <a> 
                <i class="fas fa-plus"></i> Tambah Permintaan 
            </a>
        </button>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        @if($requests->count() > 0)
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Produk</th>
                    <th>Dari Cabang</th>
                    <th>Ke Cabang</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Alasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                <tr>
                    <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ $req->product->name }}</strong></td>
                    <td><span class="badge bg-info">{{ $req->fromBranch->name }}</span></td>
                    <td><span class="badge bg-secondary">{{ $req->toBranch->name }}</span></td>
                    <td><span class="badge bg-primary">{{ $req->qty }}</span></td>
                    <td>
                        @if($req->status == 'pending') 
                            <span class="badge bg-warning"><i class="fas fa-clock"></i> Menunggu</span>
                        @elseif($req->status == 'accepted') 
                            <span class="badge bg-success"><i class="fas fa-check"></i> Disetujui</span>
                        @else 
                            <span class="badge bg-danger"><i class="fas fa-times"></i> Ditolak</span>
                        @endif
                    </td>
                    <td>
                        @if($req->status == 'rejected' && $req->reason)
                            <small class="text-muted">{{ Str::limit($req->reason, 30) }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        {{-- aksi tolak/terima --}}
                        @if(Auth::user()->branch_id == $req->to_branch_id && $req->status == 'pending')
                            <div class="btn-group" role="group">
                                <form action="{{ route('stock-requests.approve', $req->id) }}" method="POST" style="display:inline">
                                    @csrf @method('POST')
                                    <button class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Yakin ingin menyetujui permintaan ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Modal Tolak -->
                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('stock-requests.reject', $req->id) }}">
                                        @csrf @method('POST')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Permintaan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>{{ $req->product->name }}</strong> ({{ $req->qty }}) dari <strong>{{ $req->fromBranch->name }}</strong></p>
                                                <textarea name="reason" class="form-control" placeholder="Alasan penolakan..." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif(Auth::user()->branch_id == $req->from_branch_id)
                            @if($req->status == 'pending')
                                <small class="text-muted"><i class="fas fa-paper-plane"></i> Terkirim</small>
                            @elseif($req->status == 'accepted')
                                <small class="text-success"><i class="fas fa-check-circle"></i> Disetujui</small>
                            @else
                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ $req->reason }}">
                                    <i class="fas fa-info-circle"></i> Alasan
                                </button>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada permintaan barang</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Permintaan -->
<!-- Modal Tambah Permintaan -->
<div class="modal fade {{ $errors->any() ? 'show d-block' : '' }}" id="createRequestModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('stock-requests.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Permintaan Barang</h5>
                </div>
                <div class="modal-body">
                    {{-- Tampilkan notifikasi error jika ada --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-2">
                        <label>Cabang Tujuan</label>
                        <select name="to_branch_id" class="form-control">
                            <option value="">-- Pilih Cabang --</option>
                            @foreach($branches as $branch)
                                @if($branch->id != Auth::user()->branch_id)
                                    <option value="{{ $branch->id }}" {{ old('to_branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('to_branch_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label>Produk</label>
                        <select name="product_id" class="form-control">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label>Jumlah</label>
                        <input type="number" name="qty" class="form-control" min="1" placeholder="Masukkan jumlah" value="{{ old('qty') }}">
                        @error('qty')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                </div>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
@if($errors->any())
    <script>
        var myModal = new bootstrap.Modal(document.getElementById('createRequestModal'));
        window.addEventListener('DOMContentLoaded', () => {
            myModal.show();
        });
    </script>
@endif

@endsection
