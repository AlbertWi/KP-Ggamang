<?php

namespace App\Http\Controllers;

use App\Models\StockRequest;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockRequestController extends Controller
{
    public function index()
    {
        $branchId = Auth::user()->branch_id;

        $requests = StockRequest::with(['fromBranch', 'toBranch', 'product'])
                    ->where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $products = Product::all();
        $branches = Branch::where('id', '!=', $branchId)->get();

        return view('kepala_toko.stock_requests.index', compact('requests', 'products', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ],[
            'to_branch_id.required' => 'Cabang harus dipilih.',
            'product_id.required' => 'Produk harus dipilih.',
            'qty.required' => 'Jumlah harus diisi.',
        ]);

        if ($request->to_branch_id == Auth::user()->branch_id) {
            return redirect()->back()->withErrors([
                'to_branch_id' => 'Tidak bisa mengirim permintaan ke cabang sendiri'
            ]);
        }

        StockRequest::create([
            'from_branch_id' => Auth::user()->branch_id,
            'to_branch_id' => $request->to_branch_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'status' => 'pending',
        ]);

        return redirect()->route('stock-requests.index')->with('success', 'Permintaan barang berhasil dikirim.');
    }

    public function approve($id)
    {
        $stockRequest = StockRequest::findOrFail($id);

        if ($stockRequest->to_branch_id != Auth::user()->branch_id) {
            return redirect()->back()->withErrors(['error' => 'Akses ditolak']);
        }

        $stockRequest->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Permintaan disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string'
        ]);

        $stockRequest = StockRequest::findOrFail($id);

        if ($stockRequest->to_branch_id != Auth::user()->branch_id) {
            return redirect()->back()->withErrors(['error' => 'Akses ditolak']);
        }

        $stockRequest->update([
            'status' => 'rejected',
            'reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Permintaan ditolak.');
    }

    // Optional untuk dashboard atau notifikasi
    public function getPendingRequestsCount()
    {
        return StockRequest::where('to_branch_id', Auth::user()->branch_id)
                            ->where('status', 'pending')
                            ->count();
    }

    public function getPendingRequests()
    {
        return StockRequest::where('to_branch_id', Auth::user()->branch_id)
                            ->where('status', 'pending')
                            ->with(['fromBranch', 'product'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
    }
}
