@extends('layouts.app')
@section('title', 'Permintaan Barang')
@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        @foreach ($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Permintaan Barang</h3>
        <div class="card-tools">
            <a href="{{ route('stock-requests.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Permintaan
            </a>
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
                        @if(Auth::user()->branch_id == $req->to_branch_id && $req->status == 'pending')
                            <div class="btn-group" role="group">
                                <form action="{{ route('stock-requests.approve', $req->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Yakin ingin menyetujui permintaan ini?')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('stock-requests.reject', $req->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Permintaan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <strong>Detail Permintaan:</strong><br>
                                                    Dari: {{ $req->fromBranch->name }}<br>
                                                    Produk: {{ $req->product->name }}<br>
                                                    Jumlah: {{ $req->qty }}
                                                </div>
                                                <div class="mb-3">
                                                    <label for="reason{{ $req->id }}" class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                                                    <textarea name="reason" id="reason{{ $req->id }}" class="form-control" rows="3" required placeholder="Masukkan alasan penolakan..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Tolak Permintaan</button>
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
            <a href="{{ route('stock-requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Permintaan Pertama
            </a>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
